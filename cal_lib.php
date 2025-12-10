<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Functions for creating hislsf calendar instances
 */
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    // It must be included from a Moodle page
}

define("HIS_CAL_IDENTIFIER", "hislsf");

require_once($CFG->dirroot . '/calendar/lib.php');
require_once($CFG->libdir . '/bennu/bennu.inc.php');

/**
 * Returns the CURRENT (because of the event ids instead of the user id being appended this URL changes when users subscribe to new events) userspecific ICal-URL
 * @param unknown_type $userid
 */
function his_get_ical_url($userid) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/local/lsf_unification/lib_his.php');
    $user = $DB->get_record("user", ["id" => $userid]);
    $terminidstring = implode(",", get_students_stdp_terminids($user->idnumber));
    return get_config('local_lsf_unification', 'icalurl') . $terminidstring;
}

/**
 * returns a default hislsf calendar instance
 * @return stdClass
 */
function create_default_his_subscription() {
    global $CFG;
    $sub = new stdClass();
    $sub->name = get_string('importcalendar', 'local_lsf_unification');
    $sub->eventtype = 'user';
    $sub->url = HIS_CAL_IDENTIFIER;
    $sub->pollinterval = 604800;
    return $sub;
}

/**
 * Checks if a hislsf calendar instance already exists for the current user. If it doesn't find one, then it tries to create a new one.
 */
function his_add_cal() {
    global $PAGE;
    if (his_already_imported_cal()) {
        return;
    }
    $sub = create_default_his_subscription();
    $subscriptionid = calendar_add_subscription($sub);
    try {
        $importresults = calendar_update_subscription_events($subscriptionid);
    } catch (moodle_exception $e) {
        // Delete newly added subscription and show invalid url error.
        calendar_delete_subscription($subscriptionid);
        print_error($e->errorcode, $e->module, $PAGE->url);
    }
}

/**
 * Returns if a hislsf calendar instance already exists for the current user
 * @return boolean
 */
function his_already_imported_cal() {
    global $USER, $DB;
    return $DB->record_exists('event_subscriptions', ["userid" => $USER->id, "url" => HIS_CAL_IDENTIFIER]);
}

/**
 * Adds a link to "cal_import.php" to the form
 * @param $mform
 */
function his_print_cal_import_form($mform) {
    global $PAGE, $CFG;
    if (his_already_imported_cal()) {
        return;
    }
    if (getSSONo() < 0) {
        return;
    }
    $mform->addElement('header', 'addhissubscriptionform', get_string('importcalendar', 'local_lsf_unification'));
    $mform->addElement('html', '<a class="btn" href="' . $CFG->wwwroot . '/local/lsf_unification/cal_import.php?back=' . urlencode($PAGE->url) . '">' . get_string('importical', 'local_lsf_unification') . '</a>');
}
