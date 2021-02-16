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
use navigation_node_collection;

defined('MOODLE_INTERNAL') || die();

/**
 * Class secondary_navigation_view.
 *
 * The primary navigation view is a combination of few components - navigation, output->navbar,
 *
 * @package     core
 * @category    navigation
 * @copyright   2020 onwards Peter Dias
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class primary extends navigation_node {
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
     * Defines the default structure for the secondary nav in a module context.
     *
     * @return array
     */
    protected function get_default_navbar_mapping() {
        return [
            self::TYPE_SYSTEM => [
                'myhome' => 2,
                'home' => 1,
                'mycourse' => 3
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
        global $SITE, $OUTPUT, $CFG;

        if (during_initial_install()) {
            return false;
        } else if ($this->initialised) {
            return true;
        }
        $this->id = 'primary_navigation';
        $context = $this->context;

        $this->add($SITE->fullname, $CFG->wwwroot, null,null, 'brandnode', new icon());
        $nodes = $this->get_leaf_nodes($this->page->navigation, $this->get_default_navbar_mapping());
        foreach ($nodes as $leafnode) {
            // Recreate the children as we don't want the additional information about it's children.
            $this->add($leafnode->text, $leafnode->action(), $leafnode->type, $leafnode->key, $leafnode->icon);
        }

        // Add the site admin node. We are using the settingsnav so as to avoid rechecking permissions again.
        $settingsnav = $this->page->settingsnav;
        $node = $settingsnav->find('root', self::TYPE_SITE_ADMIN);
        if ($node) {
            // We don't need everything from the node just the initial link.
            $this->add($node->text, $node->action(), null, null, 'siteadminnode', $node->icon);
        }

        // Search and set the active node.
        $this->search_for_active_node();
        $this->initialised = true;
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
}
