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


class tool_questionaddfeedback_renderer extends plugin_renderer_base {

    public function render_tool_questionaddfeedback_list(tool_questionaddfeedback_list_item $top, $pagestate, $link) {
        $list = html_writer::tag('ul', html_writer::tag('li', $this->render_tool_questionaddfeedback_list_item($top, $pagestate, $link)));
        return $this->output->container($list, 'listofquestions');
    }
    public function render_tool_questionaddfeedback_list_item(tool_questionaddfeedback_list_item $listitem, $pagestate, $link) {
        return $this->item($listitem, $pagestate, $link).$this->children($listitem, $pagestate, $link);
    }

    public function item(tool_questionaddfeedback_list_item $item, $pagestate, $link) {
        global $PAGE;
        $a = new stdClass();
        $a->qtypecounts = '';
        $a->total = 0;
        foreach ($item->get_q_counts() as $qtypecode => $qtypecount) {
            if ($a->qtypecounts !== '') {
                $a->qtypecounts .= ' + ';
            }
            $a->qtypecounts .= $qtypecount ." ". question_bank::get_qtype_name($qtypecode);
            $a->total += $qtypecount;
        }
        $a->name = $item->item_name();
        $thisitem = get_string('listitem'.$pagestate.$item->get_list_type(), 'tool_questionaddfeedback', $a);
        if ($link) {
            $actionurl = new moodle_url($PAGE->url, array($item->id_param_name() => $item->get_id()));
            $thisitem = html_writer::tag('a', $thisitem, array('href' => $actionurl));
        }
        return $thisitem;
    }

    protected function children(tool_questionaddfeedback_list_item $item, $pagestate, $link) {
        $children = array();
        foreach ($item->get_children() as $child) {
            $children[] = $this->render_tool_questionaddfeedback_list_item($child, $pagestate, $link);
        }
        return html_writer::alist($children);
    }
}