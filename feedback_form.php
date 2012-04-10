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
 * @package    tool
 * @subpackage questionaddfeedback
 * @copyright  2012 Jamie Pratt <me@jamiep.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once("$CFG->dirroot/question/type/edit_question_form.php");


class tool_questionaddfeedback_form extends question_edit_form {

    protected $qtypes;
    protected $qtypecounts;

    public function __construct(moodle_url $url, array $qtypes, array $qtypecounts) {
        //skip the question_edit_form constructor as it does stuff we don't require
        $this->editoroptions = array('subdirs' => 1, 'maxfiles' => EDITOR_UNLIMITED_FILES,
                                    'context' => context_system::instance());
        $this->fileoptions = array('subdirs' => 1, 'maxfiles' => -1, 'maxbytes' => -1);
        $this->qtypes = $qtypes;
        $this->qtypecounts = $qtypecounts;
        $this->context = context_system::instance();
        moodleform::__construct($url);
    }

    public function set_data() {
        $fakequestion = new object();
        $fakequestion->options = new object();
        $feedbackfields = array('correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback');
        foreach ($feedbackfields as $feedbackname) {
            $fakequestion->options->$feedbackname = '';
            $feedbackformat = $feedbackname . 'format';
            $fakequestion->options->$feedbackformat = FORMAT_HTML;
        }
        $fakequestion = $this->data_preprocessing($fakequestion);
        moodleform::set_data($fakequestion);
    }

    public function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_combined_feedback($question);
        return $question;
    }

    protected function definition() {
        global $CFG;
        $mform =& $this->_form;

        $mform->addElement('header', 'questiontypes', get_string('applytotheseqtypes', 'tool_questionaddfeedback'));
        foreach ($this->qtypes as $qtypecode => $applicableqtype) {
            if (isset($this->qtypecounts[$qtypecode])) {
                $a = new stdClass();
                $a->name = question_bank::get_qtype_name($qtypecode);
                $a->qtypecount = $this->qtypecounts[$qtypecode];
                $a->totalcount = array_sum($this->qtypecounts);
                $label = get_string('qtypecheckboxlabel', 'tool_questionaddfeedback', $a);
                $mform->addElement('checkbox', "qtype[{$qtypecode}]", '', $label, array('group' => 1));
                $mform->setDefault("qtype[{$qtypecode}]", 1);
            }
        }
        $this->add_checkbox_controller(1);

        $this->add_combined_feedback_fields();

        $this->add_action_buttons(true, get_string('confirmaddfeedback', 'tool_questionaddfeedback'));
    }
    public function validation($data, $files) {
        return array();
    }
    public function qtype() {
        debugging('This method should not be called it is here because we have to override parent abstract function');
        return null;
    }
}