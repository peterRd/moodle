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
//
// This file is part of BasicLTI4Moodle
//
// BasicLTI4Moodle is an IMS BasicLTI (Basic Learning Tools for Interoperability)
// consumer for Moodle 1.9 and Moodle 2.0. BasicLTI is a IMS Standard that allows web
// based learning tools to be easily integrated in LMS as native ones. The IMS BasicLTI
// specification is part of the IMS standard Common Cartridge 1.1 Sakai and other main LMS
// are already supporting or going to support BasicLTI. This project Implements the consumer
// for Moodle. Moodle is a Free Open source Learning Management System by Martin Dougiamas.
// BasicLTI4Moodle is a project iniciated and leaded by Ludo(Marc Alier) and Jordi Piguillem
// at the GESSI research group at UPC.
// SimpleLTI consumer for Moodle is an implementation of the early specification of LTI
// by Charles Severance (Dr Chuck) htp://dr-chuck.com , developed by Jordi Piguillem in a
// Google Summer of Code 2008 project co-mentored by Charles Severance and Marc Alier.
//
// BasicLTI4Moodle is copyright 2009 by Marc Alier Forment, Jordi Piguillem and Nikolas Galanis
// of the Universitat Politecnica de Catalunya http://www.upc.edu
// Contact info: Marc Alier Forment granludo @ gmail.com or marc.alier @ upc.edu.

/**
 * This file contains a library of functions and constants for the lti module
 *
 * @package mod_moodlenet
 * @copyright  2020 Peter Dias
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Return aliases of this activity. Just return a single element that redirects to the edit_form.php
 *
 * @param stdClass $defaultitem default item that would be added to the activity chooser if this callback was not present.
 *     It has properties: archetype, name, title, help, icon, link
 * @return array An array of aliases for this activity. Each element is an object with same list of properties as $defaultitem,
 *     plus an additional property, helplink.
 *     Properties title and link are required
 **/
function moodlenet_get_shortcuts($defaultitem) {
    global $CFG, $COURSE, $OUTPUT;
    $section = $defaultitem->link->param('sr');
    $courseid = $COURSE->id;

    $type = new stdClass();
    $type->modclass = MOD_CLASS_ACTIVITY;
    $type->name     = 'mod_moodletnet';
    // Clean the name. We don't want tags here.
    $type->title    = get_string('moodlenet', 'moodlenet');
    $trimmeddescription = get_string('moodlenet_desc', 'moodlenet');
    if ($trimmeddescription != '') {
        // Clean the description. We don't want tags here.
        $type->help     = clean_param($trimmeddescription, PARAM_NOTAGS);
        $type->helplink = get_string('moodlenet_shortcut_link', 'moodlenet');
    }
    $type->icon = $OUTPUT->pix_icon('icon', '', 'lti', array('class' => 'icon'));
    $type->link = new moodle_url('/mod/moodlenet/edit.php', array('add' => 'moodlenet', 'return' => 0, 'course' => $courseid,
        'sr' => $section));
    $types[] = $type;
    return $types;
}