<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This page lets admin add feedback to questions
 *
 * @package    tool
 * @subpackage questionaddfeedback
 * @copyright  2012 Jamie Pratt <me@jamiep.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(dirname(__FILE__).'/questionlists.php');
require_once(dirname(__FILE__).'/feedback_form.php');

$questiontypeswithfeedback = array('qtype_ddmarker' => 'ddmarker',
                                    'qtype_ddimageortext' => 'ddimageortext',
                                    'question_gapselect' => 'gapselect',
                                    'question_match' => 'match',
                                    'question_multichoice' => 'multichoice');

$questionidcolumnnames = array('ddmarker' => 'questionid',
                                    'ddimageortext' => 'questionid',
                                    'gapselect' => 'questionid',
                                    'match' => 'question',
                                    'multichoice' => 'question');

class tool_questionaddfeedback_processor_question_list extends tool_questionaddfeedback_question_list {
    protected function new_list_item($record) {
        return new tool_questionaddfeedback_processor_question_list_item($record, $this, $this->categorylist);
    }
}
class tool_questionaddfeedback_processor_question_list_item extends tool_questionaddfeedback_question_list_item {
    public function process($renderer, $pagestate, $link, $questiontypeswithfeedback, $questionidcolumnnames, $formdata) {
        global $DB;
        if (isset($formdata->qtype) && isset($formdata->qtype[$this->record->qtype])) {
            $qtypetable = array_search($this->record->qtype, $questiontypeswithfeedback);
            $qtype = question_bank::get_qtype($this->record->qtype);
            $questionidclumnname = $questionidcolumnnames[$this->record->qtype];
            $options = $DB->get_record($qtypetable, array($questionidclumnname => $this->record->id));
            $fileoptions = array('subdirs' => 1, 'maxfiles' => -1, 'maxbytes' => 0);
            //unfortunately cannot use $qtype->save_combined_feedback_helper as it is protected. So :
            foreach (array('correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback') as $feedbackname) {
                $field = $formdata->{$feedbackname};
                $options->{$feedbackname.'format'} = $field['format'];
                $draftitemid = file_get_submitted_draft_itemid($feedbackname);
                $options->{$feedbackname} =
                                file_save_draft_area_files($draftitemid, $this->record->contextid, 'question',
                                                            $feedbackname, $this->record->id, $fileoptions, trim($field['text']));
            }
            $DB->update_record($qtypetable, $options);
        } else {
            $pagestate = 'not'.$pagestate;
        }
        parent::process($renderer, $pagestate, $link);//outputs progress message (no children of question items)
    }
}

$categoryid = optional_param('categoryid', 0, PARAM_INT);
$qcontextid = optional_param('contextid', 0, PARAM_INT);
$questionid = optional_param('questionid', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
// Check the user is logged in.
require_login();
$context = get_context_instance(CONTEXT_SYSTEM);
require_capability('moodle/question:config', $context);

admin_externalpage_setup('questionaddfeedback');

// Header.
$renderer = $PAGE->get_renderer('tool_questionaddfeedback');
echo $renderer->header();
echo $renderer->heading(get_string('pluginname', 'tool_questionaddfeedback'), 2);


$qtypes = question_bank::get_creatable_qtypes();
$applicableqtypes = array();//installed and with combined feedback etc.
foreach ($qtypes as $qtypecode => $qtype) {
    if (true === in_array($qtypecode, $questiontypeswithfeedback)) {
         $applicableqtypes[$qtypecode] = $qtype;
    }
}

list($qtypesql, $params) = $DB->get_in_or_equal(array_keys($applicableqtypes), SQL_PARAMS_NAMED, 'qt');

$from = 'FROM {question_categories} cat, {question} q';
$where = " WHERE q.category =  cat.id AND q.qtype $qtypesql ";

if ($qcontextid) {
    $qcontext = get_context_instance_by_id($qcontextid, MUST_EXIST);
    $from  .= ', {context} context';
    $where .= 'AND cat.contextid = context.id AND (context.path LIKE :path OR context.id = :id) ';
    $params['path'] = $qcontext->path.'/%';
    $params['id'] = $qcontext->id;
} else if ($categoryid) {
    //fetch all questions from this cats context
    $from  .= ', {question_categories} cat2';
    $where .= 'AND cat.contextid = cat2.contextid AND cat2.id = :categoryid ';
    $params['categoryid'] = $categoryid;
} else if ($questionid) {
    //fetch all questions from this cats context
    $where .= 'AND q.id = :questionid ';
    $params['questionid'] = $questionid;
}
$sql = 'SELECT q.*, cat.contextid '.$from.$where.'ORDER BY cat.id, q.name';

$questions = $DB->get_records_sql($sql, $params);
if (!count($questions)) {
    echo html_writer::tag('div', get_string('noquestionsfound', 'tool_questionaddfeedback'));
} else {
    $contextids = array();
    foreach ($questions as $question) {
        $contextids[] = $question->contextid;
    }


    $contextlist = new tool_questionaddfeedback_context_list(array_unique($contextids));
    $categorylist = new tool_questionaddfeedback_category_list($contextids, $contextlist);
    $questionlist = new tool_questionaddfeedback_processor_question_list($questions, $categorylist);

    foreach ($questions as $question) {
        $questionlist->leaf_node($question->id);
    }
    if ($questionid) {
        $top = $questionlist->get_instance($questionid);
    } else if ($categoryid) {
        $top = $categorylist->get_instance($categoryid);
    } else if ($qcontextid) {
        $top = $contextlist->get_instance($qcontextid);
    } else {
        $top = $contextlist->root_node();
    }
    $qtypecounts = $top->get_q_counts();
    $cofirmedurl = new moodle_url($PAGE->url, compact('categoryid', 'contextid', 'questionid') + array('confirm'=>1));
    $mform = new tool_questionaddfeedback_form($cofirmedurl, $applicableqtypes, $qtypecounts);
    $mform->set_data();

    $questionsselected = (bool) (!$mform->is_cancelled()) && ($categoryid || $qcontextid || $questionid);

    if ($mform->is_cancelled()) {
        $top = $contextlist->root_node();
    }

    if ($feedbackfromform = $mform->get_data()) {
        $pagestate = 'processing';
    } else {
        if (!$questionsselected) {
            $pagestate = 'listall';
        } else {
            $pagestate = 'form';
        }
    }
    $link = ($pagestate == 'listall');

    switch ($pagestate) {
        case 'listall' :
            echo $renderer->render_tool_questionaddfeedback_list($top, $pagestate, $link);
            break;
        case 'form' :
            echo $renderer->render_tool_questionaddfeedback_list($top, $pagestate, $link);
            $renderer->box_start('generalbox');
            $mform->display();
            $renderer->box_end();
            break;
        case 'processing' :
            echo '<ul>';
            $top->process($renderer, $pagestate, $link, $questiontypeswithfeedback, $questionidcolumnnames, $feedbackfromform);
            echo '</ul>';
            break;
        default :
            break;
    }
}
// Footer.
echo $renderer->footer();
