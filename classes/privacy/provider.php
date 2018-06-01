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
 * Privacy Implementation for lsf_unification.
 *
 * @package    lsf_unification
 * @copyright  2018  Nina Herrmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lsf_unification\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\request\writer;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
/**
 * Privacy provider for lsf_unification implementing metadata\provider and plugin\provider.
 * Remark: This Plugin does not need to provide a export to external sources since data from the HisLSF system
 * is only read not exported. (https://docs.moodle.org/dev/Privacy_API#Indicating_that_you_export_data_to_an_external_location)
 *
 * @copyright  2018 Nina Herrmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\provider, \core_privacy\local\request\plugin\provider {
    use \core_privacy\local\legacy_polyfill;

    /**
     * Provides a description of the data saved.
     * @param $collection
     * @return mixed
     */
    public static function _get_metadata ($collection) {
        $collection->add_database_table(
            'local_lsf_course',
            [
                'veranstid' => 'privacy:metadata:local_lsf_unification:veranstid',
                'mdlid' => 'privacy:metadata:local_lsf_unification:mdlid',
                'timestamp' => 'privacy:metadata:local_lsf_unification:timestamp',
                'requeststate' => 'privacy:metadata:local_lsf_unification:requeststate',
                'requesterid' => 'privacy:metadata:local_lsf_unification:requesterid',
                'acceptorid' => 'privacy:metadata:local_lsf_unification:acceptorid'

            ],
            'privacy:metadata:local_lsf_unification'
        );
        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     * @param int $userid The user to search.
     * @return contextlist $contextlist The list of contexts for a specific user.
     * @throws \dml_exception
     */
    public static function _get_contexts_for_userid ($userid) {
        global $DB;
        $contextlist = new contextlist();

        $sql = "SELECT *
                  FROM {local_lsf_course}
                 WHERE (requesterid = :requesterid OR
                 acceptorid = :acceptorid)";
        $contextparams['requesterid'] = $userid;
        $contextparams['acceptorid'] = $userid;
        $requests = $DB->get_recordset_sql($sql, $contextparams);

        if (!empty($requests)) {
            // Course_context or category_context can not be used, since declined courses are never created in the Moodle Database.
            $contextlist->add_user_context($userid);
        }

        return $contextlist;
    }

    /**
     * Export the user data to a given contextlist.
     * @param approved_contextlist $contextlist
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function _export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();
        $context = \context_user::instance($user->id);
        $contexts = $contextlist->get_contexts();
        foreach ($contexts as $specificcontext) {
            // Sanity check that context is at the user context level.
            if ($specificcontext->contextlevel !== CONTEXT_USER) {
                continue;
            }
            $sql = "SELECT *
                  FROM {local_lsf_course}
                 WHERE (requesterid = :userid)";
            $contextparams['userid'] = $user->id;

            $coursesrequested = $DB->get_recordset_sql($sql, $contextparams);
            $sql = "SELECT *
                  FROM {local_lsf_course}
                 WHERE (acceptorid = :userid)";
            $coursesaccepted = $DB->get_recordset_sql($sql, $contextparams);

            if (empty($courserequested) && empty($coursesaccepted)) {
                return;
            }

            foreach ($coursesrequested as $courserequest) {
                $contextdatatowrite[] = self::create_data_to_write('requester', $courserequest, $user->username);
            }
            foreach ($coursesaccepted as $courserequest) {
                $contextdatatowrite[] = self::create_data_to_write('acceptor', $courserequest, $user->username);
            }
        }
        $subcontext = [
            get_string('pluginname', 'local_lsf_unification')
        ];
        if (!empty($contextdatatowrite)) {
            writer::with_context($context)->export_data($subcontext, (object)$contextdatatowrite);
        }
    }


    /**
     * Helper Function to build the data for a given courserequest.
     * @param $action \string requester or acceptor
     * @param $courserequest
     * @param $username
     * @return object
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function create_data_to_write($action, $courserequest, $username) {
        global $DB;
        $status = '';
        switch ($courserequest->requeststate) {
            case 0 :
                $status = "is declined or not requested";
                break;
            case 1 :
                $status =
                    "is waiting";
                break;
            case 2 :
                $status =
                    "is granted";
                break;
        }
        $data = (object)[
            'action' => get_string('privacy:local_lsf_unification:' . $action, 'local_lsf_unification'),
            'timestamp' => date('c', $courserequest->timestamp),
            'username' => $username,
            'status' => $status
        ];
        // In case the course exist we add the name to the information.
        $course = $DB->get_record('course', array('id' => $courserequest->mdlid));
        if (!empty($course)) {
            $data->coursename = $course->fullname;
        }
        return $data;
    }

    /**
     * Deletes data from the table for all users in all contexts.
     * @param $context
     * @throws \dml_exception
     */
    public static function _delete_data_for_all_users_in_context($context) {
        global $DB;

        // Sanity check that context is at the System context level.
        if ($context->contextlevel == CONTEXT_SYSTEM) {
            $DB->delete_records("local_lsf_course");
            return;
        }
        // Sanity check that context is at the User context level.
        if ($context->contextlevel == CONTEXT_USER ) {
            $userid = $context->instanceid;
            $DB->delete_records("local_lsf_course", array('acceptorid' => $userid));
            $DB->delete_records("local_lsf_course", array('requestorid' => $userid));
        }
    }

    /**
     * Deletes all records belonging to a user of a contextlist.
     * @param $contextlist
     * @throws \dml_exception
     */
    public static function _delete_data_for_user($contextlist) {
        global $DB;
        $contexts = $contextlist->get_contexts();
        if (empty($contexts)) {
            return;
        }

        foreach ($contexts as $context) {

            // Sanity check that context is at the User context level, then get the userid.
            if ($context->contextlevel !== CONTEXT_USER) {
                continue;
            }

            $userid = $context->instanceid;
            $DB->delete_records("local_lsf_course", array('acceptorid' => $userid));
            $DB->delete_records("local_lsf_course", array('requestorid' => $userid));
        }
    }
}