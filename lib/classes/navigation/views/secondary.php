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
 * Secondary navigation view
 *
 * This file contains the new secondary nav class. It is a stripped down tweaked version of the
 * settings_navigation
 *
 * @package     core
 * @category    navigation
 * @copyright   2020 onwards Peter Dias
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_navigation\views;

defined('MOODLE_INTERNAL') || die();

/**
 * Class secondary_navigation_view.
 *
 * The secondary navigation view is a stripped down tweaked version of the
 * settings_navigation
 *
 * @package     core
 * @category    navigation
 * @copyright   2020 onwards Peter Dias
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class secondary_navigation_view extends \navigation_node {

    /**
     * secondary_navigation_view constructor.
     * @param \moodle_page $page
     */
    public function __construct(\moodle_page &$page) {
        // TODO: Modify params on a as needed basis
        // TODO: Do we need to initialise settingsnavigation here before proceeding?
    }

    /**
     * Initialise the view based navigation based on the current context.
     */
    public function initialise() {

    }
}
