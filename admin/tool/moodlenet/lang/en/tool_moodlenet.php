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
 * Strings for the tool_moodlenet component.
 *
 * @package     tool_moodlenet
 * @category    string
 * @copyright   2020 Jake Dallimore <jrhdallimore@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addingaresource'] = 'Adding content from MoodleNet';
$string['aria:enterprofile'] = "Enter your MoodleNet profile URL";
$string['aria:footermessage'] = "Browse for content on MoodleNet";
$string['browsemoodlenet'] = "Moodle HQ MoodleNet";
$string['browsecontentmoodlenet'] = "Or browse for content on MoodleNet";
$string['clearsearch'] = "Clear search";
$string['connectandbrowse'] = "Connect to and browse:";
$string['defaultmoodlenet'] = "Link to Moodle HQ Moodlenet";
$string['defaultmoodlenetinfo'] = "The Moodle HQ run instance of MoodleNet";
$string['enablemoodlenet'] = 'Enable integration with MoodleNet instances';
$string['enablemoodlenetinfo'] = 'If enabled, and provided the MoodleNet plugin is installed, users can import content from MoodleNet into this site.';
$string['errorduringdownload'] = 'An error occurred while downloading the file: {$a}';
$string['forminfo'] = "It will be automatically saved on your moodle profile.";
$string['footermessage'] = "Or browse for content on";
$string['instancedescription'] = "MoodleNet is an open social media platform for educators, with a focus on the collaborative curation of collections of open resources. ";
$string['inputhelp'] = 'Or if you have a MoodleNet account already, enter your MoodleNet profile:';
$string['invalidmoodlenetprofile'] = '$userprofile is not correctly formatted';
$string['importconfirm'] = 'You are about to import the content "{$a->resourcename} ({$a->resourcetype})" into the course "{$a->coursename}". Are you sure you want to continue?';
$string['importconfirmnocourse'] = 'You are about to import the content "{$a->resourcename} ({$a->resourcetype})" into your site. Are you sure you want to continue?';
$string['importformatselectguidingtext'] = 'In which format would you like this content "{$a->name} ({$a->type})" to be added to your course?';
$string['importformatselectheader'] = 'Choose the content display format';
$string['instancepagetitle'] = 'Instance page';
$string['instancepageheader'] = 'Navigate to a MoodleNet instance';
$string['instanceplaceholder'] = '@yourprofile@moodle.net';
$string['missinginvalidpostdata'] = 'The resource information from MoodleNet is either missing, or is in an incorrect format.
If this happens repeatedly, please contact the site administrator.';
$string['mnetprofile'] = 'MoodleNet profile';
$string['mnetprofiledesc'] = '<p>Enter in your MoodleNet profile details here to be redirected to your profile while visiting MoodleNet. Do not delete.</p>';
$string['moodlenetnotenabled'] = 'The MoodleNet integration must be enabled before resource imports can be processed.
To enable this feature, see the \'enablemoodlenet\' setting.';
$string['notification'] = 'You are about to import the content "{$a->name} ({$a->type})" into your site. Select the course in which it should be added, or <a href="{$a->cancellink}">cancel</a>.';
$string['searchcourses'] = "Search courses";
$string['selectpagetitle'] = 'Select page';
$string['pluginname'] = 'MoodleNet';
$string['privacy:metadata'] = "The MoodleNet tool only facilitates communication with MoodleNet. It stores no data.";
$string['privacy:metadata:profilefieldpurpose'] = 'Information is stored in a custom user profile field.';
$string['profilevalidationerror'] = 'There was a problem trying to validate your profile';
$string['profilevalidationfail'] = 'Please enter a valid MoodleNet profile';
$string['profilevalidationpass'] = 'Looks good!';
$string['saveandgo'] = "Save and go";
$string['uploadlimitexceeded'] = 'The file size {$a->filesize} exceeds the user upload limit of {$a->uploadlimit} bytes.';
