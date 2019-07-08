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
 * A system for displaying notifications to users from the session.
 *
 * Wrapper for the YUI M.core.notification class. Allows us to
 * use the YUI version in AMD code until it is replaced.
 *
 * @module     core_course/modchooser
 * @package    core_course
 * @copyright  2015 Damyon Wiese <damyon@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      2.9
 */
define(
    [
        'core/yui',
        'jquery',
        'core/activity_chooser_dialogue',
        'core/pubsub',
        'core/activity_chooser_events',
    ],
    function(
        Y,
        $,
        chooserDialogue,
        PubSub,
        ActivityChooserEvents
    ) {

    var CSS = {
        PAGECONTENT: 'body',
        SECTION: null,
        SECTIONMODCHOOSER: 'span.section-modchooser-link',
        SITEMENU: '.block_site_main_menu',
        SITETOPIC: 'div.sitetopic'
    };

    /**
     * The current section ID.
     *
     * @property sectionid
     * @private
     * @type Number
     * @default null
     */
    var sectionid = null;

    /**
     * Display the module chooser
     *
     * @method display_mod_chooser
     * @param {EventFacade} e Triggering Event
     */
    var displayModChooser = function(e) {
        // Set the section for this version of the dialogue
        if ($(e.currentTarget).parents(CSS.SITETOPIC).length) {
            // The site topic has a sectionid of 1
            sectionid = 1;
        } else if ($(e.currentTarget).parents(CSS.SECTION).length) {
            var section = $(e.currentTarget).parents(CSS.SECTION);
            sectionid = section.attr('id').replace('section-', '');
        } else if ($(e.currentTarget).parents(CSS.SITEMENU).length) {
            // The block site menu has a sectionid of 0
            sectionid = 0;
        }

        chooserDialogue.displayChooser(e);
    };

    /**
     * Update any section areas within the scope of the specified
     * selector with AJAX equivalents
     *
     * @method _setup_for_section
     * @private
     * @param baseselector The selector to limit scope to
     */
    var _setupForSection = function(section) {
        var chooserspan = $(section).find(CSS.SECTIONMODCHOOSER);
        if (!chooserspan.length) {
            return;
        }
        var chooserlink = "<a href='#' class='chooser-link' />";
        $(chooserspan).children().wrapAll(chooserlink);

        $('.chooser-link').on('click', function(e) {
            e.preventDefault();
            displayModChooser(e);
        });
    };

     /**
     * Update any section areas within the scope of the specified
     * selector with AJAX equivalents
     *
     * @method setup_for_section
     * @param baseselector The selector to limit scope to
     */
    var setupForSection = function(baseselector) {
        if (!baseselector) {
            baseselector = CSS.PAGECONTENT;
        }
        // Setup for site topics
        $(baseselector).find(CSS.SITETOPIC).each(function() {
            _setupForSection(this);
        });
        // Setup for standard course topics
        if (CSS.SECTION) {
            $(baseselector).find(CSS.SECTION).each(function() {
                _setupForSection(this);
            });
        }

        // Setup for the block site menu
        $(baseselector).find(CSS.SITEMENU).each(function() {
            _setupForSection(this);
        });

        $('.testtt').on('click', function(e) {
            e.preventDefault();
            displayModChooser(e);
        });
    };

    /**
     * Helper function to set the value of a hidden radio button when a
     * selection is made.
     *
     * @method option_selected
     * @param {String} thisoption The selected option value
     * @private
     */
    var optionSelected = function(thisoption) {
        // Add the sectionid to the URL.
        chooserDialogue.updateHiddenRadioValue('jump', thisoption.value + '&section=' + sectionid);
    };

    /**
     * Set up the activity chooser.
     *
     * @method initializer
     */
    var initializer = function() {
        Y.use('moodle-course-coursebase', function() {
            var sectionclass = M.course.format.get_sectionwrapperclass();
            if (sectionclass) {
                CSS.SECTION = '.' + sectionclass;
            }

            var dialogue = $('.chooserdialoguebody');
            var header = $('.choosertitle');
            var params = {};
            chooserDialogue.setupChooserDialogue(dialogue, header, params);

            // Initialize existing sections and register for dynamically created sections
            setupForSection();
        });

        PubSub.subscribe(ActivityChooserEvents.OPTION_SELECTED, optionSelected);
    };

    return /** @alias module:core/notification */{
        init: initializer
    };
});
