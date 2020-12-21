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
 * Unit tests for lib/classes/navigation/secondary.php.
 *
 * @package   core
 * @copyright 2020 Peter Dias
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use core\navigation\views\secondary;

/**
 * Class core_secondary_testcase
 *
 * Unit test for the secondary nav view.
 */
class core_secondary_testcase extends advanced_testcase {
    /**
     * Test the get_leaf_nodes function
     * @param $siteorder
     * @param $courseorder
     * @param $moduleorder
     * @dataProvider leaf_nodes_order_provider
     */
    public function test_get_leaf_nodes($siteorder, $courseorder, $moduleorder) {
        global $PAGE;

        // Create a secondary navigation and populate with some dummy nodes.
        $secondary = new mock_secondary_navigation($PAGE);
        $secondary->add('Site Admin', '#', secondary::TYPE_SETTING, null, 'siteadmin');
        $secondary->add('Course Admin', '#', secondary::TYPE_CUSTOM, null, 'courseadmin');
        $secondary->add('Module Admin', '#', secondary::TYPE_SETTING, null, 'moduleadmin');
        $nodes = [
            navigation_node::TYPE_SETTING => [
                'siteadmin' => $siteorder,
                'moduleadmin' => $courseorder,
            ],
            navigation_node::TYPE_CUSTOM => [
                'courseadmin' => $moduleorder,
            ]
        ];
        $expectednodes = [
            "$siteorder" => 'siteadmin',
            "$courseorder" => 'moduleadmin',
            "$moduleorder" => 'courseadmin',
        ];

        $sortednodes = $secondary->get_leaf_nodes($secondary, $nodes);
        foreach($sortednodes as $order => $node) {
            $this->assertEquals($expectednodes[$order], $node->key);
        }
    }

    /**
     * Data provider for test_get_leaf_nodes
     * @return array
     */
    public function leaf_nodes_order_provider() {
        return [
            'Initialise the order with whole numbers' =>  [3, 2, 1],
            'Initialise the order with a mix of whole and float numbers' =>  [2.1, 2, 1]
        ];
    }
}

/**
 * Class mock_secondary_navigation
 * Mocks the secondary view so we can access and test protected functions
 */
class mock_secondary_navigation extends secondary {
    /**
     * @param object $source
     * @param array $nodes
     * @return array
     */
    public function get_leaf_nodes($source, $nodes) {
        return parent::get_leaf_nodes($source, $nodes);
    }
}
