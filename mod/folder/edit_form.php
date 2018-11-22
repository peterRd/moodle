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
 * A moodle form to manage folder files
 *
 * @package   mod_folder
 * @copyright 2010 Dongsheng Cai <dongsheng@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class mod_folder_edit_form extends moodleform {
    function definition() {
        $mform = $this->_form;

        $data    = $this->_customdata['data'];
        $options = $this->_customdata['options'];

        $mform->addElement('hidden', 'id', $data->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'timemodified', $data->timemodified);
        $mform->setType('timemodified', PARAM_NUMBER);
        $mform->addElement('filemanager', 'files_filemanager', get_string('files'), null, $options);
        $submit_string = get_string('savechanges');
        $this->add_action_buttons(true, $submit_string);

        $this->set_data($data);
    }

    /**
     * Overrides parent::validation. Validate form values
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        $errors = array();
        foreach ($data as $key => $value) {
            switch($key) {
                // Don't allow updates to the form if the modified time has been updated.
                case 'timemodified':
                    $defaultvalue = $this->_form->_defaultValues[$key];
                    if ($value != $defaultvalue) {
                        $errors['files_filemanager'] = "Files have been externally updated. Please try again later.";
                    }
                    break;
            }
        }

        return $errors;
    }
}
