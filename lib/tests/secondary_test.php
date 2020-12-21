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
     * Tests the secondary initialisation function in different contexts
     *
     * @param string $context The context we will be looking for e.g. system, course etc
     * @param string $expectedrootnode The node identifier that we expect in this particular context.
     * @param string $expectednodetype The node type that we expect in this particular context
     * @dataProvider secondary_initialise_context_feeder
     */
    public function test_secondary_initialise_with_context_check($context, $expectedrootnode, $expectednodetype) {
        global $PAGE;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = null;
        $url = "/";
        $urlparams = [];
        switch ($context) {
            case "system":
                $contextrecord = context_system::instance();
                $url = '/admin/index.php';
                break;
            case "course":
            case "module":
                $course = $this->getDataGenerator()->create_course();
                $contextrecord = context_course::instance($course->id, MUST_EXIST);
                if ($context == "module") {
                    $forum = $this->getDataGenerator()->create_module('forum', ['course' => $course->id]);
                    $cm = get_coursemodule_from_id('forum', $forum->cmid);
                    $contextrecord = context_module::instance($cm->id);
                    $PAGE->set_cm($cm);
                    $url = '/mod/forum/view.php';
                    $urlparams['id'] = $cm->instance;
                }
                break;
        }

        $PAGE->set_url($url, $urlparams);
        $PAGE->set_context($contextrecord);
        if ($course) {
            $PAGE->set_course($course);
        }

        $secondary = new secondary($PAGE);
        $secondary->initialise();
        $node = $secondary->find($expectedrootnode, $expectednodetype);
        $this->assertTrue($node instanceof navigation_node);
        $this->assertTrue($node->has_children());
    }

    /**
     * Data provider for test_secondary_initialise_with_context_check
     *
     * @return array
     */
    public function secondary_initialise_context_feeder() {
        return [
            "Initialise with the system context" => [
                "system", "root", secondary::TYPE_SITE_ADMIN
            ],
            "Initialise with the module context" => [
                "module", "modulesettings", secondary::TYPE_SETTING
            ],
            "Initialise with the course context" => [
                "course", "courseadmin", secondary::TYPE_COURSE
            ],
        ];
    }
}
