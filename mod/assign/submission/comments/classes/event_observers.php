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
 * Add event handlers for the assignsubmission_comments
 *
 * @package assignsubmission_comments
 * @copyright  2019 Peter Dias<peter@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace assignsubmission_comments;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/assign/locallib.php');

/**
 * Class event_observers
 *
 * @package assignsubmission_comments
 * @copyright  2019 Peter Dias<peter@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class event_observers {

    /**
     * Event handler for comment created event.
     *
     * @param object $event Event object
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function comment_created($event) {
        global $DB, $USER;

        $params = array('contextid' => $event->contextid, 'userid' => $event->userid, 'id' => $event->objectid);
        $comment = $DB->get_record('comments', $params);
        // TODO: reduce this to a single query.
        $submission = $DB->get_record( 'assign_submission', ['id' => $comment->itemid]);

        $author = $USER;
        $emailto = [];
        // If the person who submitted the assignment is the same person
        // adding the comment, then every other teacher would need to be notified.
        if ($submission->userid == $event->userid) {
            list($insql, $inparams) = $DB->get_in_or_equal(['teacher', 'editingteacher'], SQL_PARAMS_NAMED);
            $roles = $DB->get_records_select('role', "shortname $insql", $inparams);
            $roleids = array_map(function($element) {
                return $element->id;
            }, $roles);
            $roleids = array_unique($roleids);
            if (!$roleids) {
                return;
            }
            $emailto = get_role_users($roleids, $event->get_context(), true, "ra.id, u.*");
        } else {
            // Else notify the student that a new comment has been added.
            // Should this be changed to notify other teachers as well?
            $emailto[] = $DB->get_record('user', ['id' => $submission->userid]);
        }

        foreach ($emailto as $receipient) {
            $message = new \core\message\message();
            $message->component = 'mod_assign';
            $message->name = 'assign_notification';
            $message->userfrom = $author;
            $message->userto = $receipient;
            $message->subject = $event->get_description();
            $message->fullmessage = $event->get_email_message();
            $message->fullmessageformat = FORMAT_MARKDOWN;
            $message->fullmessagehtml = "<p>{$event->get_email_message()}</p>";
            $message->notification = 1;
            $message->contexturl = $event->get_url();
            $message->contexturlname = $event->component;
            $message->courseid = $event->courseid;

            message_send($message);
        }
    }
}