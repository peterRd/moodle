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
 * Empty glossary of it's contents.
 *
 * @package    mod_glossary
 * @category   test
 * @copyright  2019 Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("../../config.php");
require_once("lib.php");

$id       = required_param('id', PARAM_INT);          // Course module ID.
$confirm  = optional_param('confirm', 0, PARAM_INT);  // Commit the operation?

$url = new moodle_url('/mod/glossary/emptyglossary.php', array('id' => $id));
if ($confirm !== 0) {
    $url->param('confirm', $confirm);
}

$PAGE->set_url($url);

$strglossary   = get_string("modulename", "glossary");
$strglossaries = get_string("modulenameplural", "glossary");
$stredit       = get_string("edit");

if (! $cm = get_coursemodule_from_id('glossary', $id)) {
    print_error("invalidcoursemodule");
}

if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
    print_error('coursemisconf');
}

if (! $glossary = $DB->get_record("glossary", array("id" => $cm->instance))) {
    print_error('invalidid', 'glossary');
}

require_login($course, false, $cm);
$context = context_module::instance($cm->id);
$managecourse = has_capability('moodle/course:manageactivities', $context);
$strareyousuredelete = get_string("areyousureemptygenericglossary", "glossary");
$stremptyglossary = get_string("areyousureemptyglossary", "glossary", $glossary->name);

if (!$managecourse) { // Guest id is never matched, no need for special check here.
    print_error('nopermissiontodelentry');
}

// If data submitted, then process and store.
if ($confirm and confirm_sesskey()) { // the operation was confirmed.
    if (!glossary_delete_instance($glossary->id, true)) {
        print_error('cannotdeletedir', 'error',
            '', $glossary->name
        );
    }
    redirect("view.php?id=$cm->id");

} else {        // The operation has not been confirmed yet so ask the user to do so.
    $PAGE->navbar->add(get_string('delete'));
    $PAGE->set_title($glossary->name);
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
    $areyousure = "<b>".$stremptyglossary."</b><p>$strareyousuredelete</p>";
    $linkyes    = 'emptyglossary.php';
    $linkno     = 'view.php';
    $optionsyes = array('id' => $cm->id, 'confirm' => 1, 'sesskey' => sesskey());
    $optionsno  = array('id' => $cm->id);

    echo $OUTPUT->confirm($areyousure, new moodle_url($linkyes, $optionsyes), new moodle_url($linkno, $optionsno));

    echo $OUTPUT->footer();
}
