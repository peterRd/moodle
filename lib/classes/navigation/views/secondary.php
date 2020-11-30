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

    /**
     * secondary_navigation_view constructor.
     * @param \moodle_page $page
     */
    public function __construct(\moodle_page &$page) {
        global $FULLME;

        if (during_initial_install()) {
            return false;
        }

        $this->page = $page;
        $this->context = $this->page->context;

        // Not all pages override the active url. Do it now.
        if ($this->page->has_set_url()) {
            self::override_active_url(new \moodle_url($this->page->url));
        } else {
            self::override_active_url(new \moodle_url($FULLME));
        }

        $this->children = new navigation_node_collection();
    }

    /**
     * Defines the default structure for the secondary nav in a course context
     *
     * @return array
     */
    protected function get_default_course_mapping() {
        $nodes = [];
        $nodes['settings'] = [
            self::TYPE_CONTAINER => [
                'coursereports' => 3,
                'questionbank' => 4
            ],
            self::TYPE_SETTING => [
                'editsettings' => 0,
                'coursecompletion' => 6,
                'gradebooksetup' => 2.1,
                'outcomes' => 2.2
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

        return $nodes;
    }

    /**
     * Defines the default structure for the secondary nav in a module context.
     *
     * @return array
     */
    protected function get_default_module_mapping() {
        return [
            self::TYPE_SETTING => [
                'modedit' => 1,
                'roleoverride' => 3,
                'logreport' => 4,
                'filtermanage' => 8,
                'backup' => 9,
                'rolecheck' => 3.1,
                'restore' => 10,
                'competencybreakdown' => 11,
                "mod_{$this->page->activityname}_useroverrides" => 5, // Overrides are module specific.
                "mod_{$this->page->activityname}_groupoverrides" => 6,
                'roleassign' => 7,
            ],
            self::TYPE_CUSTOM => [
                'advgrading' => 2,
            ]
        ];
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

        // Search and set the active node.
        $this->search_for_active_node();
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
        $course = $this->page->course;

        // Initialise the main navigation and settings nav.
        // It is important that this is done before we try anything.
        $settingsnav = $this->page->settingsnav;
        $navigation = $this->page->navigation;

        $url = new \moodle_url('/course/view.php', ['id' => $course->id, 'sesskey' => sesskey()]);
        $this->add(get_string('coursepage', 'admin'), $url, self::TYPE_COURSE, null, 'coursehome');

        $nodes = $this->get_default_course_mapping();
        $nodesordered = $this->get_leaf_nodes($settingsnav, $nodes['settings']);
        $nodesordered += $this->get_leaf_nodes($navigation, $nodes['navigation']);
        ksort($nodesordered);

        foreach ($nodesordered as $key => $node) {
            // If the key is a string then we are assuming this is a nested element.
            if (is_string($key)) {
                $parentnode = $nodesordered[floor($key)] ?? null;
                if ($parentnode) {
                    $parentnode->add_node($node);
                }
            } else {
                $this->add_node($node);
            }
        }

        // All additional nodes will be available under the 'Course admin' page.
        $text = get_string('courseadministration');
        $url = new \moodle_url('/course/admin.php', array('courseid' => $this->page->course->id));
        $this->add($text, $url, null, null, 'courseadmin', new \pix_icon('t/edit', $text));
    }

    /**
     * Get the leaf nodes for the nav view
     *
     * @param navigation_node $source The settingsnav OR navigation object
     * @param array $nodes An array of nodes to fetch from the source which specifies the node type and final order
     * @return array $nodesordered The fetched nodes ordered based on final specification.
     */
    protected function get_leaf_nodes(navigation_node $source, array $nodes) {
        $nodesordered = [];
        foreach ($nodes as $type => $leaves) {
            foreach ($leaves as $leaf => $location) {
                if ($node = $source->find($leaf, $type)) {
                    $nodesordered["$location"] = $node;
                }
            }
        }

        return $nodesordered;
    }

    /**
     * Get the module's secondary navigation. This is based on settings_nav and would include plugin nodes added via
     * '_extend_settings_navigation'.
     * It populates the tree based on the nav mockup
     *
     * If nodes change, we will have to explicitly call the callback again.
     */
    protected function load_module_navigation() {
        $settingsnav = $this->page->settingsnav;
        $mainnode = $settingsnav->find('modulesettings', self::TYPE_SETTING);
        $nodes = $this->get_default_module_mapping();

        if ($mainnode) {
            $this->add(get_string('module', 'course'), $this->page->url, null, null, 'modulepage');
            // Add the initial nodes.
            $nodesordered = $this->get_leaf_nodes($mainnode, $nodes);
            ksort($nodesordered);
            foreach ($nodesordered as $key => $node) {
                // If the key is a string then we are assuming this is a nested element.
                if (is_string($key)) {
                    $parentnode = $nodesordered[floor($key)] ?? null;
                    if ($parentnode) {
                        $parentnode->add_node($node);
                    }
                } else {
                    $this->add_node($node);
                }
            }

            // We have finished inserting the initial structure.
            // Populate the menu with the rest of the nodes available.
            $this->load_remaining_nodes($mainnode, $nodes);
        }
    }

    /**
     * Load the site admin navigation
     */
    protected function load_admin_navigation() {
        $settingsnav = $this->page->settingsnav;
        $node = $settingsnav->find('root', self::TYPE_SITE_ADMIN);
        if ($node) {
            $siteadminnode = $this->add($node->text, "#link$node->key", null, null, 'siteadminnode');
            foreach ($node->children as $child) {
                if ($child->display && !$child->is_short_branch()) {
                    $this->add_node($child);
                } else {
                    $siteadminnode->add_node($child);
                }
            }
        }
    }

    /**
     * Find the remaining nodes that need to be loaded into secondary based on the current context
     *
     * @param navigation_node $completenode The original node that we are sourcing information from
     * @param array           $nodesmap The map used to populate secondary nav in the given context
     */
    protected function load_remaining_nodes(navigation_node $completenode, array $nodesmap) {
        $flattenednodes = [];
        foreach (array_values($nodesmap) as $nodecontainer) {
            $flattenednodes = array_merge(array_keys($nodecontainer), $flattenednodes);
        }

        $populatedkeys = $this->get_children_key_list();
        $existingkeys = $completenode->get_children_key_list();
        $leftover = array_diff($existingkeys, $populatedkeys);
        foreach ($leftover as $key) {
            if (!in_array($key, $flattenednodes) && $leftovernode = $completenode->get($key)) {
                $this->add_node($leftovernode);
            }
        }
    }
}
