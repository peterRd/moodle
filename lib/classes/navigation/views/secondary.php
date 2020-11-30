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

namespace core\navigation\views;

use navigation_node;
use navigation_cache;
use navigation_node_collection;

defined('MOODLE_INTERNAL') || die();

/**
 * Class secondary_navigation_view.
 *
 * The secondary navigation view is a stripped down tweaked version of the
 * settings_navigation/navigation
 *
 * @package     core
 * @category    navigation
 * @copyright   2020 onwards Peter Dias
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class secondary extends navigation_node {
    /** @var stdClass the current context */
    protected $context;
    /** @var moodle_page the moodle page that the navigation belongs to */
    protected $page;
    /** @var bool A switch to see if the navigation node is initialised */
    protected $initialised = false;
    /** @var navigation_cache **/
    protected $cache;

    /**
     * secondary_navigation_view constructor.
     * @param \moodle_page $page
     */
    public function __construct(\moodle_page &$page) {
        if (during_initial_install()) {
            return false;
        }

        $this->page = $page;
        $this->context = $this->page->context;
        // Initialise the navigation cache.
        $this->cache = new navigation_cache(NAVIGATION_CACHE_NAME);
        $this->children = new navigation_node_collection();
    }

    /**
     * Initialise the view based navigation based on the current context.
     *
     * As part of the initial restructure, the secondary nav is only considered for the following pages:
     * 1 - Site admin settings
     * 2 - Course page - Does not include front_page which has the same context.
     * 3 - Module page
     */
    public function initialise() {
        global $SITE;

        if (during_initial_install()) {
            return false;
        } else if ($this->initialised) {
            return true;
        }
        $this->id = 'secondary_navigation';
        $context = $this->context;

        switch ($context->contextlevel) {
            case CONTEXT_COURSE:
                if ($this->page->course->id != $SITE->id) {
                    $this->load_course_navigation();
                }
                break;
            case CONTEXT_MODULE:
                $this->load_module_navigation();
                break;
            case CONTEXT_SYSTEM:
                $this->load_admin_navigation();
                break;
        }

        $this->initialised = true;
    }

    /**
     * Load the course secondary navigation. Since we are sourcing all the info from existing objects that already do
     * the relevant checks, we don't do it again here.
     *
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    protected function load_course_navigation() {
        global $CFG;
        require_once($CFG->dirroot . '/course/lib.php');

        $course = $this->page->course;
        $coursecontext = \context_course::instance($course->id);

        // Initialise the main navigation and settings nav.
        // It is most important that this is done before we try anything.
        $settingsnav = $this->page->settingsnav;
        $navigation = $this->page->navigation;

        $url = new \moodle_url('/course/view.php', ['id' => $course->id, 'sesskey' => sesskey()]);
        $mainnode = $this->add(get_string('courseadministration'), null, self::TYPE_COURSE, null, 'courseadmin');
        $mainnode->add(get_string('coursepage', 'admin'), $url, self::TYPE_COURSE, null, 'coursehome');

        $nodes['settings'] = [
            self::TYPE_CONTAINER => [
                'coursereports' => 3,
                'questionbank' => 4
            ],
            self::TYPE_SETTING => [
                'editsettings' => 0,
                'coursecompletion' => 6
            ]
        ];
        $nodes['navigation'] = [
            self::TYPE_CONTAINER => [
                'participants' => 1,
            ],
            self::TYPE_SETTING => [
                'badgesview' => 7,
                'competencies' => 8,
                'grades' => 2,
            ],
            self::TYPE_CUSTOM => [
                'contentbank' => 5
            ]
        ];

        $nodesordered = $this->get_leaf_nodes($settingsnav, $nodes['settings']);
        $nodesordered += $this->get_leaf_nodes($navigation, $nodes['navigation']);
        ksort($nodesordered);

        foreach ($nodesordered as $node) {
            $mainnode->add_node($node);
        }

        // We have finished getting the initial nodes. Let plugins hook into course navigation.
        $pluginsfunction = get_plugins_with_function('extend_navigation_course', 'lib.php');
        foreach ($pluginsfunction as $plugintype => $plugins) {
            // Ignore the report plugin as it was already loaded above.
            if ($plugintype == 'report') {
                continue;
            }
            foreach ($plugins as $pluginfunction) {
                $pluginfunction($mainnode, $course, $coursecontext);
            }
        }
    }

    /**
     * Get the leaf nodes for the nav view
     *
     * @param object $source The settingsnav OR navigation object
     * @param array $nodes An array of nodes to fetch from the source which specifies the node type and final order
     * @return array $nodesordered The fetched nodes ordered based on final specification.
     */
    protected function get_leaf_nodes($source, $nodes) {
        $nodesordered = [];
        foreach ($nodes as $type => $leaves) {
            foreach ($leaves as $leaf => $order) {
                if ($node = $source->find($leaf, $type)) {
                    $nodesordered[$order] = $node;
                }
            }
        }

        return $nodesordered;
    }

    /**
     * Get the module's secondary navigation. This is based on settings_nav and would include plugin nodes added via
     * '_extend_settings_navigation'.
     *
     * If nodes change, we will have to explicitly call the callback again.
     */
    protected function load_module_navigation() {
        $settingsnav = $this->page->settingsnav;
        $node = $settingsnav->find('modulesettings', self::TYPE_SETTING);
        if ($node) {
            $this->add_node($node);
        }
    }

    /**
     * Load the site admin navigation
     */
    protected function load_admin_navigation() {
        $settingsnav = $this->page->settingsnav;
        $node = $settingsnav->find('root', self::TYPE_SITE_ADMIN);
        if ($node) {
            $this->add_node($node);
        }
    }
}
