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

$questiontypeswithfeedback = array('ddmarker', 'ddimageortext', 'gapselect', 'match', 'multichoice');

class tool_questionaddfeedback_question_converter_list extends tool_questionaddfeedback_question_list {
    protected function new_list_item($stringidentifier, $link, $record) {
        return new tool_questionaddfeedback_question_converter_list_item($stringidentifier, $link, $record, $this, $this->categorylist);
    }
}
class tool_questionaddfeedback_question_converter_list_item extends tool_questionaddfeedback_question_list_item {
    public function process($renderer) {
        parent::process($renderer);//outputs progress message
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

    $sql = 'SELECT q.qtype, COUNT(1) '.$from.$where.' GROUP BY q.qtype';

    $qtypecounts = $DB->get_records_sql_menu($sql, $params);

    $cofirmedurl = new moodle_url($PAGE->url, compact('categoryid', 'contextid', 'questionid') + array('confirm'=>1));
    $mform = new tool_questionaddfeedback_form($cofirmedurl, $applicableqtypes, $qtypecounts, count($questions));
    $mform->set_data();

    $questionsselected = (bool) (!$mform->is_cancelled()) && ($categoryid || $qcontextid || $questionid);

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
    $contextlist = new tool_questionaddfeedback_context_list($pagestate, $link, array_unique($contextids));
    $categorylist = new tool_questionaddfeedback_category_list($pagestate, $link, $contextids, $contextlist);
    $questionlist = new tool_questionaddfeedback_question_converter_list($pagestate, $link, $questions, $categorylist);

    foreach ($questions as $question) {
        $questionlist->leaf_node($question->id, 1);
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
    switch ($pagestate) {
        case 'listall' :
            echo $renderer->render_tool_questionaddfeedback_list($top);
            break;
        case 'form' :
            echo $renderer->render_tool_questionaddfeedback_list($top);
            $qtypes = question_bank::get_creatable_qtypes();
            $renderer->box_start('generalbox');
            $mform->display();
            $renderer->box_end();
            break;
        case 'processing' :
            echo '<ul>';
            $top->process($renderer);
            echo '</ul>';
            break;
        default :
            break;
    }
}
// Footer.
echo $renderer->footer();
