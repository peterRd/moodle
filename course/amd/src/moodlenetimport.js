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
 * Handle the moodlenet import
 *
 * @module     core_course/moodlenetimport
 * @package    core_course
 * @copyright  2020 Peter Dias <peter@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as Repository from 'core_course/local/activitychooser/repository';
import CustomEvents from 'core/custom_interaction_events';
import Pending from 'core/pending';

/**
 * Set up the custom js for the import notification.
 *
 * @method init
 * @param {String} customclass Course ID to use later on in fetchModules()
 */
export const init = (customclass) => {
    const pendingPromise = new Pending();

    registerListenerEvents(customclass);

    pendingPromise.resolve();
};

/**
 * Once a custom moodlenet notification has been displayed, hookup the cancel link
 *
 * @param {String} customclass
 * @method registerListenerEvents
 */
const registerListenerEvents = (customclass) => {
    const events = [
        'click',
        CustomEvents.events.activate,
        CustomEvents.events.keyboardActivate
    ];
    CustomEvents.define(document, events);
    const notification = document.querySelector('#user-notifications').getElementsByClassName(customclass)[0];
    events.forEach((event) => {
        notification.addEventListener(event, async(e) => {
            if (e.target == notification.querySelector('a')) {
                e.preventDefault();
                await Repository.stopImportProcess();
                notification.remove();
            }
        });
    });
};

