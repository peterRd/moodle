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

namespace theme_boost\output;

use moodle_url;

defined('MOODLE_INTERNAL') || die;

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_boost
 * @copyright  2012 Bas Brands, www.basbrands.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \core_renderer {

    public function edit_button(moodle_url $url) {
        return '';
    }

    public function editing_button() {
        global $PAGE;
        $url = $PAGE->url;
        $url->param('sesskey', sesskey());
        if ($PAGE->user_is_editing()) {
            $url->param('edit', 'off');
            $edit = 'off';
            $adminedit = 0;
            $editstring = get_string('turneditingoff');
            $checked = 'checked';
        } else {
            $url->param('edit', 'on');
            $edit = 'on';
            $adminedit = 1;
            $editstring = get_string('turneditingon');
            $checked = '';
        }
        $button = new \single_button($url, $editstring, 'post', ['class' => 'btn btn-primary']);
        $switch = '
            <form method="post" action="' . $url . '">
            <input type="hidden" name="sesskey" value="' . sesskey() . '">
            <input type="hidden" name="edit" value="' . $edit . '">
            <input type="hidden" name="adminedit" value="' . $adminedit . '">
            <div class="custom-control custom-control-right custom-switch text-nowrap">
                <input type="checkbox" class="custom-control-input" ' . $checked . ' id="editingswitch">
                <label class="custom-control-label" for="editingswitch">
                    <span class="d-none d-sm-inline">Edit mode</span>
                </label>
            </div>
            </form>';
        if ($PAGE->user_allowed_editing()) {
            return $switch;
        }
    }
}
