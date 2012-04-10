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

$string['applytotheseqtypes'] = 'Add feedback to questions of the following types only';
$string['confirmaddfeedback'] = 'Add feedback to above questions';

$string['defaultcorrectfeedback'] = 'Your answer is correct';
$string['defaultpartiallycorrectfeedback'] = 'Your answer is partially correct';
$string['defaultincorrectfeedback'] = 'Your answer is incorrect';

$string['listitemformcategory'] = 'About to add feedback to questions in category "{$a->name}" ({$a->qtypecounts} = Total {$a->total} questions)';
$string['listitemformcontext'] = 'About to add feedback to questions in context "{$a->name}" ({$a->qtypecounts} = Total {$a->total} questions)';
$string['listitemformquestion'] = 'About to add feedback to question "{$a->name}"';
$string['listitemlistallcategory'] = 'Select questions in category "{$a->name}" ({$a->qtypecounts} = Total {$a->total} questions)';
$string['listitemlistallcontext'] = 'Select questions in context "{$a->name}" ({$a->qtypecounts} = Total {$a->total} questions)';
$string['listitemlistallquestion'] = 'Select question "{$a->name}"';
$string['listitemprocessingcategory'] = 'Added feedback to questions in category "{$a->name}" ({$a->qtypecounts} = Total {$a->total} questions)';
$string['listitemprocessingcontext'] = 'Added feedback to questions in context "{$a->name}" ({$a->qtypecounts} = Total {$a->total} questions)';
$string['listitemprocessingquestion'] = 'Added feedback to question "{$a->name}"';
$string['listitemnotprocessingquestion'] = 'Did not add feedback to question "{$a->name}" as it was not of a type selected to have feedback added to';

$string['noquestionsfound'] = 'No questions found here.';
$string['pluginname'] = 'Batch add feedback to questions';
$string['qtypecheckboxlabel'] = '"{$a->name}" ({$a->qtypecount} out of {$a->totalcount} selected questions)';
