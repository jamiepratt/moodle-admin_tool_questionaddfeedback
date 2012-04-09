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

    public function render_tool_questionaddfeedback_list(tool_questionaddfeedback_list_item $top) {
        $list = html_writer::tag('ul', html_writer::tag('li', $this->render_tool_questionaddfeedback_list_item($top)));
        return $this->output->container($list, 'listofquestions');
    }
    public function render_tool_questionaddfeedback_list_item(tool_questionaddfeedback_list_item $listitem) {
        return $this->item($listitem).$this->children($listitem);
    }

    public function item(tool_questionaddfeedback_list_item $item) {
        global $PAGE;
        $a = new stdClass();
        $a->qcount = $item->get_q_count();
        $a->name = $item->item_name();
        $thisitem = get_string('listitem'.$item->get_string_identifier().$item->get_list_type(), 'tool_questionaddfeedback', $a);
        if ($item->get_linked()) {
            $actionurl = new moodle_url($PAGE->url, array($item->id_param_name() => $item->get_id()));
            $thisitem = html_writer::tag('a', $thisitem, array('href' => $actionurl));
        }
        return $thisitem;
    }

    protected function children(tool_questionaddfeedback_list_item $item) {
        $children = array();
        foreach ($item->get_children() as $child) {
            $children[] = $this->render_tool_questionaddfeedback_list_item($child);
        }
        return html_writer::alist($children);
    }
}