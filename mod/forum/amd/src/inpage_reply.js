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
 * This module is the highest level module for the calendar. It is
 * responsible for initialising all of the components required for
 * the calendar to run. It also coordinates the interaction between
 * components by listening for and responding to different events
 * triggered within the calendar UI.
 *
 * @module     mod_forum/posts_list
 * @package    mod_forum
 * @copyright  2018 Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
        'jquery',
        'core/templates',
        'core/notification',
        'mod_forum/repository',
        'mod_forum/selectors',
    ], function(
        $,
        Templates,
        Notification,
        Repository,
        Selectors
    ) {

    var DISPLAYCONSTANTS = {
        THREADED: 2,
        NESTED: 3,
        FLAT_OLDEST_FIRST: 1,
        FLAT_NEWEST_FIRST: -1
    };

    var registerEventListeners = function(root) {
        root.on('click', Selectors.post.inpage_submit_btn, function (e) {
            e.preventDefault();
            var form = $(e.currentTarget).parents(Selectors.post.inpage_reply_form).get(0);
            var message = form.elements.post.value;
            var postid = form.elements.reply.value;
            var subject = form.elements.subject.value;
            var currentRoot = $(e.currentTarget).parents(Selectors.post.forum_content);
            var mode = parseInt(root.find(Selectors.post.mode_select).get(0).value);

            Repository.addDiscussionPost(postid, subject, message)
                .then(function(context) {
                    var post = context.post;

                    switch(mode) {
                        case DISPLAYCONSTANTS.THREADED:
                            return Templates.render('mod_forum/forum_discussion_threaded_post', post);
                        case DISPLAYCONSTANTS.NESTED:
                            return Templates.render('mod_forum/forum_discussion_nested_post', post);
                        default:
                            return Templates.render('mod_forum/forum_discussion_post', post);
                    }
                })
                .then(function(html, js) {
                    var repliesnode;
                    if (mode == DISPLAYCONSTANTS.FLAT_OLDEST_FIRST || mode == DISPLAYCONSTANTS.FLAT_NEWEST_FIRST) {
                        repliesnode = currentRoot.parents(Selectors.post.replies_container).children().get(0);
                    } else {
                        repliesnode = currentRoot.siblings(Selectors.post.replies_container).children().get(0);
                    }

                    if (mode == DISPLAYCONSTANTS.FLAT_NEWEST_FIRST) {
                        return Templates.prependNodeContents(repliesnode, html, js);
                    } else {
                        return Templates.appendNodeContents(repliesnode, html, js);
                    }
                })
                .then(function() {
                    return currentRoot.find(Selectors.post.inpage_reply_content).hide();
                })
                .fail(Notification.exception);
        });
    };

    return {
        init: function(root) {
            registerEventListeners(root);

        }
    };
});
