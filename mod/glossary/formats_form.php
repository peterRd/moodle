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
 * Formats form. Form to handle editing of the glossary formats
 *
 * @package    mod_glossary
 * @copyright  2019 onwards Peter Dias {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    // It must be included from a Moodle page.
}

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * Class mod_glossary_formats_form. Generate form for add/edit glossary formats
 *
 * @copyright  2019 onwards Peter Dias {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_glossary_formats_form extends moodleform {

    /**
     * Implemented abstract function.
     */
    public function definition() {
        $mform = $this->_form;
        $currententry      = $this->_customdata['current'];

        // -------------------------------------------------------------------------------
        $mform->addElement('html', "<h3><strong>" .
            get_string('displayformat' . $currententry->name, 'glossary') .
            "</strong></h3>"
        );

        // ----- Add the popup format dropdown ------ //
        // Get and update available formats.
        $recformats = glossary_get_available_formats();
        $formats = array();

        // Take names.
        foreach ($recformats as $format) {
            $formats[$format->name] = get_string("displayformat$format->name", "glossary");
        }
        // Sort it.
        asort($formats);
        $mform->addElement('select', 'popupformatname', get_string('popupformat', 'glossary'), $formats);
        $mform->addRule('popupformatname', null, 'required', null, 'client');
        $mform->addHelpButton('popupformatname', "cnfrelatedview", "glossary");

        // ----- Add the default mode dropdown ------ //
        $modeoptions = [
            GLOSSARY_DISPLAY_MODE_LETTER => get_string('letter', 'glossary'),
            GLOSSARY_DISPLAY_MODE_CATEGORY => get_string('category', 'glossary'),
            GLOSSARY_DISPLAY_MODE_AUTHOR => get_string('author', 'glossary'),
            GLOSSARY_DISPLAY_MODE_DATE => get_string('date', 'glossary'),
        ];
        $mform->addElement('select', 'defaultmode', get_string('defaultmode', 'glossary'), $modeoptions);
        $mform->addRule('defaultmode', null, 'required', null, 'client');
        $mform->addHelpButton('defaultmode', "cnfdefaultmode", "glossary");

        if ($currententry->popupformatname) {
            $mform->setDefault('defaultmode', $currententry->popupformatname);
        } else {
            $mform->setDefault('defaultmode', GLOSSARY_DISPLAY_MODE_LETTER);
        }

        // ----- Add the hook dropdown ---- //
        $hooks = [
            'ALL' => get_string("allentries", "glossary"),
            'SPECIAL' => get_string("special", "glossary"),
            '0' => get_string("allcategories", "glossary"),
            '-1' => get_string("notcategorised", "glossary"),
        ];
        $mform->addElement('select', 'defaulthook', get_string('defaulthook', 'glossary'), $hooks);
        $mform->addRule('defaulthook', null, 'required', null, 'client');
        $mform->addHelpButton('defaulthook', "cnfdefaulthook", "glossary");

        // ----- Add the sort key dropdown ---- //
        $options = [
            'CREATION' => get_string("sortbycreation", "glossary"),
            'UPDATE' => get_string("sortbylastupdate", "glossary"),
            'FIRSTNAME' => get_string("firstname"),
            'LASTNAME' => get_string("lastname"),
        ];
        $mform->addElement('select', 'sortkey', get_string('defaultsortkey', 'glossary'), $options);
        $mform->addRule('sortkey', null, 'required', null, 'client');
        $mform->addHelpButton('sortkey', "cnfsortkey", "glossary");

        // ----- Add the sort order dropdown ---- //
        $options = [
            'asc' => get_string("ascending", "glossary"),
            'desc' => get_string("descending", "glossary"),
        ];
        $mform->addElement('select', 'sortorder', get_string('defaultsortorder', 'glossary'), $options);
        $mform->addRule('sortorder', null, 'required', null, 'client');
        $mform->addHelpButton('sortorder', "cnfsortorder", "glossary");

        // ----- Add the group breaks dropdown ---- //
        $options = [
            '1' => get_string("yes"),
            '0' => get_string("no"),
        ];
        $mform->addElement('select', 'includegroupbreaks', get_string('defaultsortorder', 'glossary'), $options);
        $mform->addRule('includegroupbreaks', null, 'required', null, 'client');
        $mform->addHelpButton('includegroupbreaks', "cnfshowgroup", "glossary");

        $glossarytabs = glossary_get_all_tabs();
        $visibletabs = glossary_get_visible_tabs($currententry);
        $currententry->visibletabs = $visibletabs;
        $mform->addElement('select', 'showtabs', get_string('visibletabs', 'glossary'), $glossarytabs);
        $mform->addRule('showtabs', null, 'required', null, 'client');
        $mform->addHelpButton('showtabs', "cnftabs", "glossary");
        $mform->getElement('showtabs')->setMultiple(true);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_INT);
        $mform->addElement('hidden', 'mode', 'edit');
        $mform->setType('mode', PARAM_TEXT);

        // -------------------------------------------------------------------------------
        $this->add_action_buttons();

        // -------------------------------------------------------------------------------
        $this->set_data($currententry);
    }

    /**
     * Get the data stored in the form.
     * Additionally parses any value in showtabs into a comma separated string.
     *
     * @return object
     */
    public function get_data() {
        $data = parent::get_data();
        $data->showtabs = GLOSSARY_STANDARD.','. implode(',', $data->showtabs);

        return $data;
    }
}

