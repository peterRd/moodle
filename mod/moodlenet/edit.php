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
 * MoodleNet edit page
 *
 * @package    mod_moodlenet
 * @copyright  2020 Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once('lib.php');

$course = required_param('course', PARAM_INT);            // Course ID.
$section   = optional_param('sr', 0, PARAM_INT);           // Section Id.

if (!$course = $DB->get_record('course', array('id' => $course))) {
    print_error('coursemisconf');
}

require_login($course, false);

$tool = core_plugin_manager::instance()->get_plugin_info('tool_moodlenet');

if ($tool) {
    // TODO: Get the tool's redirect URL.
    $action = 'https://www.google.com.au/';
} else {
    // TODO: Throw some error here or redirect to the default moodlenet site.
    $action = 'https://www.amazon.com.au/';
}

redirect($action);