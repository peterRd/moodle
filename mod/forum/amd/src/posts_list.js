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
        'mod_forum/selectors',
        'mod_forum/inpage_reply',
    ], function(
        $,
        Templates,
        Notification,
        Selectors,
        InPageReply
    ) {

    var registerEventListeners = function(root) {
        root.on('click', Selectors.post.inpage_reply_link, function(e) {
            e.preventDefault();
            var currentTarget = $(e.currentTarget).parents(Selectors.post.forum_core_content);
            var currentRoot = $(e.currentTarget).parents(Selectors.post.forum_content);
            var context = {
                postid: $(currentRoot).data('post-id'),
                reply_url: $(e.currentTarget).attr('href'),
                sesskey: M.cfg.sesskey
            };

            if (!currentTarget.find(Selectors.post.inpage_reply_content).length) {
                Templates.render('mod_forum/inpage_reply', context)
                    .then(function(html, js) {
                        return Templates.appendNodeContents(currentTarget, html, js);
                    })
                    .then(function() {
                        currentRoot.find(Selectors.post.inpage_reply_content).toggle();
                    })
                    .fail(Notification.exception);
            } else {
                currentRoot.find(Selectors.post.inpage_reply_content).toggle();
            }
        });
    };

    return {
        init: function(root) {
            registerEventListeners(root);
            InPageReply.init(root);
        }
    };
});
