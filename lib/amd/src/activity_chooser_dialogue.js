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
 * @module     core/activity_chooser_dialogue
 * @package    core
 * @copyright  2019 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.7
 */
define(['core/yui', 'jquery'],
function(Y, $) {

     // The panel widget
    var panel = null;

    // The submit button - we disable this until an element is set
    var submitbutton = null;

    // The chooserdialogue container
    var container = null;

    var options = null;

    // Any event listeners we may need to cancel later
    var listenevents = [];

    var bodycontent = null;
    var headercontent = null;
    var instanceconfig = null;

    // The hidden field storing the disabled element values for submission.
    var hiddenRadioValue = null;

    var sectionid = null;

    var ATTRS = {
        /**
         * The minimum height (in pixels) before resizing is prevented and scroll
         * locking disabled.
         *
         * @attribute minheight
         * @type Number
         * @default 300
         */
        minheight: 300,

        /**
         * The base height??
         *
         * @attribute baseheight
         * @type Number
         * @default 400
         */
        baseheight: 400,

        /**
         * The maximum height (in pixels) at which we stop resizing.
         *
         * @attribute maxheight
         * @type Number
         * @default 300
         */
        maxheight: 660,

        /**
         * The title of the close button.
         *
         * @attribute closeButtonTitle
         * @type String
         * @default 'Close'
         */
        closeButtonTitle: {
            validator: Y.Lang.isString,
            value: 'Close'
        }
    };

    var setupChooserDialogue = function(body, header, config) {
        bodycontent = body;
        headercontent = header;
        instanceconfig = config;
    };

    /**
     * Cancel any listen events in the listenevents queue
     *
     * Several locations add event handlers which should only be called before the form is submitted. This provides
     * a way of cancelling those events.
     *
     * @method cancel_listenevents
     */
    var cancelListenevents = function() {
        // Detach all listen events to prevent duplicate triggers
        var thisevent;
        while (listenevents.length) {
            thisevent = listenevents.shift();
            $(thisevent).detach();
        }
    };

    var hide = function() {
        // Cancel all listen events
        cancelListenevents();
        // container.detach();
        container.remove();
        panel.hide();
    };

    var cancelPopup = function(e) {
        // Prevent normal form submission before hiding
        e.preventDefault();
        hide();
    };

      /**
     * Return an array of class names prefixed with 'chooserdialogue-' and
     * the name of the type of dialogue.
     *
     * Note: Class name are converted to lower-case.
     *
     * If an array of arguments is supplied, each of these is prefixed and
     * lower-cased also.
     *
     * If no arguments are supplied, then the prefix is returned on it's
     * own.
     *
     * @method _getClassNames
     * @param {Array} [args] Any additional names to prefix and lower-case.
     * @return {Array}
     * @private
     */
    var _getClassNames = function(args) {
        var prefix = 'chooserdialogue-' + name,
            results = [];

        results.push(prefix.toLowerCase());
        if (args) {
            var arg;
            for (arg in args) {
                results.push((prefix + '-' + arg).toLowerCase());
            }
        }

        return results;
    };

    var prepareChooser = function() {
        if (panel) {
            return;
        }

        // Ensure that we're showing the JS version of the chooser.
        $('body').addClass('jschooser');
        // Set Default options
        var paramkey,
            params = {
                bodyContent: bodycontent.html(),
                headerContent: headercontent.html(),
                width: '540px',
                draggable: true,
                visible: false, // Hide by default
                zindex: 100, // Display in front of other items
                modal: true, // This dialogue should be modal.
                shim: true,
                closeButtonTitle: ATTRS.closeButtonTitle,
                focusOnPreviousTargetAfterHide: true,
                render: false,
                extraClasses: _getClassNames()
            };

        // Override with additional options
        for (paramkey in instanceconfig) {
          params[paramkey] = instanceconfig[paramkey];
        }

        // Create the panel
        panel = new M.core.dialogue(params);

        // Remove the template for the chooser
        bodycontent.remove();
        headercontent.remove();

        // Hide and then render the panel
        panel.hide();
        panel.render();

        // Set useful links.
        //container = $('.choosercontainer');
        container = $(panel.get('boundingBox').one('.choosercontainer').getDOMNode());
        options = container.find('.option input[type=radio]');

        // The hidden form element we use when submitting.
        hiddenRadioValue = '<input type="hidden" value="" />';
        //container.one('form').appendChild(hiddenRadioValue);
        container.find('form').append(hiddenRadioValue);

        // Add the chooserdialogue class to the container for styling
        panel.get('boundingBox').addClass('chooserdialogue');
    };

    /**
      * Calculate the optimum height of the chooser dialogue
      *
      * This tries to set a sensible maximum and minimum to ensure that some options are always shown, and preferably
      * all, whilst fitting the box within the current viewport.
      *
      * @method center_dialogue
      * @param Node {dialogue} Y.Node The dialogue
      */
    var centerDialogue = function(dialogue) {
        var bb = panel.get('boundingBox'),
            winheight = bb.get('winHeight'),
            newheight, totalheight;

        if (panel.shouldResizeFullscreen()) {
            // No custom sizing required for a fullscreen dialog.
            return;
        }

        // Try and set a sensible max-height -- this must be done before setting the top
        // Set a default height of 640px
        newheight = ATTRS.maxheight;
        if (winheight <= newheight) {
            // Deal with smaller window sizes
            if (winheight <= ATTRS.minheight) {
                newheight = ATTRS.minheight;
            } else {
                newheight = winheight;
            }
        }

        // If the dialogue is larger than a reasonable minimum height, we
        // disable the page scrollbars.
        if (newheight > ATTRS.minheight) {
            // Disable the page scrollbars.
            if (panel.lockScroll && !panel.lockScroll.isActive()) {
                panel.lockScroll.enableScrollLock(true);
            }
        } else {
            // Re-enable the page scrollbars.
            if (panel.lockScroll && panel.lockScroll.isActive()) {
                panel.lockScroll.disableScrollLock();
            }
        }

        // Take off 15px top and bottom for borders, plus 40px each for the title and button area before setting the
        // new max-height
        totalheight = newheight;
        newheight = newheight - (15 + 15 + 40 + 40);
        $(dialogue).css('maxHeight', newheight + 'px');

        var dialogueheight = bb.getStyle('height');
        if (dialogueheight.match(/.*px$/)) {
            dialogueheight = dialogueheight.replace(/px$/, '');
        } else {
            dialogueheight = totalheight;
        }

        if (dialogueheight < ATTRS.baseheight) {
            dialogueheight = ATTRS.baseheight;
            $(dialogue).css('height', dialogueheight + 'px');
        }

        panel.centerDialogue();
    };

    var handleKeyPress = function(e) {
        if (e.keyCode === 27) {
            cancelPopup(e);
        }
    };

    var optionSelected = function(thisoption) {
        // Set a hidden input field with the value and name of the radio button.  When we submit the form, we
        // disable the radios to prevent duplicate submission. This has the result however that the value is never
        // submitted so we set this value to a hidden field instead
        // hiddenRadioValue.setAttrs({
        //     value: e.get('value'),
        //     name: e.get('name')
        // });
    };

    var checkOptions = function() {
        // Check which options are set, and change the parent class
        // to show/hide help as required
        options.each(function() {
            var optiondiv = $(this).parent().parent();
            if ($(this).is(':checked')) {
                $(optiondiv).addClass('selected');

                // Trigger any events for this option
                optionSelected(this);

                // Ensure that the form may be submitted
                submitbutton.removeAttr('disabled');

                // Ensure that the radio remains focus so that keyboard navigation is still possible
                $(this).focus();
            } else {
                optiondiv.removeClass('selected');
            }
        }, this);
    };

    /**
      * Display the module chooser
      *
      * @method display_chooser
      * @param {EventFacade} e Triggering Event
      */
    var displayChooser = function(e, sectionid1) {
        var bb, dialogue, thisevent;
        prepareChooser();

        sectionid = sectionid1;

        // Stop the default event actions before we proceed
        e.preventDefault();

        bb = panel.get('boundingBox');
        dialogue = container.find('.alloptions');
        // This will detect a change in orientation and retrigger centering
        thisevent = $(document).on('orientationchange', function() {
            centerDialogue(dialogue);
        });
        listenevents.push(thisevent);

        // Detect window resizes (most browsers)
        thisevent = $(window).on('resize', function() {
            centerDialogue(dialogue);
        });
        listenevents.push(thisevent);

        // These will trigger a check_options call to display the correct help
        thisevent = container.on('click', function() {
            checkOptions();
        });
        listenevents.push(thisevent);
        thisevent = container.on('key_up', function() {
            checkOptions();
        });
        listenevents.push(thisevent);
        thisevent = container.on('dblclick', function(e) {
            if ($(e.target).parents('div.option').length) {
                checkOptions();

                // Prevent duplicate submissions
                submitbutton.attr('disabled', 'disabled');
                options.attr('disabled', 'disabled');
                cancelListenevents();

                container.find('form').submit();
            }
        });
        listenevents.push(thisevent);

        container.find('form').on('submit', function() {
            // Prevent duplicate submissions on submit
            submitbutton.attr('disabled', 'disabled');
            options.attr('disabled', 'disabled');
            cancelListenevents();
        });

        // Hook onto the cancel button to hide the form
        thisevent = container.find('.addcancel').on('click', function(e) {
            cancelPopup(e);
        });
        listenevents.push(thisevent);

        // Hide will be managed by cancel_popup after restoring the body overflow
        thisevent = bb.one('button.closebutton').on('click', function(e) {
            cancelPopup(e);
        });
        listenevents.push(thisevent);

        // Grab global keyup events and handle them
        thisevent = $(document).on('keydown', handleKeyPress, this);
        listenevents.push(thisevent);

        // Add references to various elements we adjust
        submitbutton = container.find('.submitbutton');

        // Disable the submit element until the user makes a selection
        submitbutton.attr('disabled', 'true');

        // Ensure that the options are shown
        options.each(function() {
            $(this).removeAttr('disabled');
        });

        // Display the panel
        panel.show(e);

        // Re-centre the dialogue after we've shown it.
        centerDialogue(dialogue);

        // Finally, focus the first radio element - this enables form selection via the keyboard
        container.find('.option input[type=radio]').focus();

        // Trigger check_options to set the initial jumpurl
        checkOptions();
    };

    return /** @alias module:core/activity_chooser_dialogue */{

        /**
         * Poll the server for any new notifications.
         *
         * @method fetchNotifications
         */
        setupChooserDialogue: setupChooserDialogue,

        /**
         * Add a notification to the page.
         *
         * Note: This does not cause the notification to be added to the session.
         *
         * @method addNotification
         * @param {Object}  notification                The notification to add.
         * @param {string}  notification.message        The body of the notification
         * @param {string}  notification.type           The type of notification to add (error, warning, info, success).
         * @param {Boolean} notification.closebutton    Whether to show the close button.
         * @param {Boolean} notification.announce       Whether to announce to screen readers.
         */
        displayChooser: displayChooser
    };
});
