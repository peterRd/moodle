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
 * Edit/add glossary category
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
 * Class mod_glossary_editcategories_form. Generate the form that handles add/edit of glossary categories.
 *
 * @copyright  2019 onwards Peter Dias {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_glossary_editcategories_form extends moodleform {

    /**
     * Overloaded abstract function function.
     */
    protected function definition() {
        $mform = $this->_form;
        $currententry      = $this->_customdata['current'];
        $action = $currententry['action'];

        // -------------------------------------------------------------------------------
        $mform->addElement('html', "<h3><strong>" .
            get_string(($action == 'add' ? 'addcategory' : 'editcategory'), 'glossary') .
            "</strong></h3>"
        );

        $mform->addElement('text', 'name', 'Name');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required');
        $mform->addElement('advcheckbox', 'usedynalink', get_string('dynalinkcategory', 'glossary'));

        $mform->addElement('hidden', 'id'); // Currently corresponds to cmid.
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'hook'); // Currently corresponds to category id.
        $mform->setType('hook', PARAM_INT);
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_INT);
        $mform->addElement('hidden', 'mode', 'edit');
        $mform->setType('mode', PARAM_TEXT);
        $mform->addElement('hidden', 'confirm');
        $mform->setType('confirm', PARAM_INT);
        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_TEXT);

        // -------------------------------------------------------------------------------
        $this->add_action_buttons();

        // -------------------------------------------------------------------------------
        $this->set_data($currententry);
    }
}

