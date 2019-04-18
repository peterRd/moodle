<?php

/// This file allows to manage the default behaviour of the display formats

require_once("../../config.php");
require_once($CFG->libdir.'/adminlib.php');
require_once("lib.php");
require_once("formats_form.php");

$id   = required_param('id', PARAM_INT);
$mode = optional_param('mode', '', PARAM_ALPHANUMEXT);

$url = new moodle_url('/mod/glossary/formats.php', array('id'=>$id));
if ($mode !== '') {
    $url->param('mode', $mode);
}
$PAGE->set_url($url);

admin_externalpage_setup('managemodules'); // this is hacky, tehre should be a special hidden page for it

if ( !$displayformat = $DB->get_record("glossary_formats", array("id"=>$id))) {
    print_error('invalidglossaryformat', 'glossary');
}
$redirecturl = new moodle_url("/$CFG->admin/settings.php", ['section' => 'modsettingglossary']);
$redirecturl->set_anchor('glossary_formats_header');

$mform = new mod_glossary_formats_form(null, ['current' => $displayformat]);
$forceredirect = false || $mform->is_cancelled();
if ( $mode == 'visible' and confirm_sesskey()) {
    if ( $displayformat ) {
        if ( $displayformat->visible ) {
            $displayformat->visible = 0;
        } else {
            $displayformat->visible = 1;
        }
        $DB->update_record("glossary_formats",$displayformat);
    }
    $forceredirect = true;
} else if ($mform->is_submitted() and !$mform->is_cancelled() and confirm_sesskey() and $data = $mform->get_data()) {
    $DB->update_record("glossary_formats", $data);
    $forceredirect = true;
}

if ($forceredirect) {
    redirect($redirecturl->out(false));
    die;
}

$strmodulename = get_string("modulename", "glossary");
$strdisplayformats = get_string("displayformats","glossary");

echo $OUTPUT->header();

echo $OUTPUT->heading($strmodulename . ': ' . get_string("displayformats","glossary"));

echo $OUTPUT->box(get_string("configwarning", 'admin'), "generalbox boxaligncenter boxwidthnormal");
echo "<br />";

echo $mform->display();
echo $OUTPUT->footer();
?>