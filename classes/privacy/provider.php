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
 * Privacy Subsystem implementation for lsf_unification.
 *
 * @package    lsf_unification
 * @copyright  2018  Nina Herrmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lsf_unification\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem for lsf_unification implementing metadata\provider.
 * Remark: this Plugin does not need to provide a export to external sources since data from the HisLSF system
 * is only read not exported
 * @copyright  2018 Nina Herrmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_privacy\local\request\writer;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;

class provider implements \core_privacy\local\metadata\provider, \core_privacy\local\request\plugin\provider {
    use \core_privacy\local\legacy_polyfill;

    public static function _get_metadata($collection)  {
        // The mod uses files and grades.
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
     * @param    int            $userid               The user to search.
     * @return   contextlist    $contextlist          The list of contexts used in this plugin.
     * @throws \dml_exception
     */
    public static function _get_contexts_for_userid($userid) {
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
            // user_context is the only way since declined courses do not belong to a course or a category in Moodle.
            // Therefore course_context or category_context can not be used.
            $contextlist->add_user_context($userid);
        }
        $contextlist->add_user_context($userid);

        return $contextlist;
    }

    /**
     * Export the user data to a given context.
     * @param $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        $user = $contextlist->get_user();
        // Minimal example which does not work. -- I tried both contexts.
        $context = \context_user::instance($contextlist->get_user()->id, MUST_EXIST);
        $context = \context_system::instance();
        $subcontext = [
            get_string('pluginname', 'local_lsf_unification')
        ];
        $dummydata = (object)['dummy' => 'data'];
        writer::with_context($context)->export_data($subcontext, $dummydata);
        /*$contexts = $contextlist->get_contexts();

        // Get all contextids used.
        $contextidsused = $contextlist->get_contextids();
        $sql = "SELECT *
                  FROM {local_lsf_course}
                 WHERE (requesterid = :userid)";
        $contextparams['userid'] = $contextlist->get_user()->id;*/

        /*$coursesrequested = $DB->get_recordset_sql($sql, $contextparams);
        $sql = "SELECT *
                  FROM {local_lsf_course}
                 WHERE ( acceptorid = :userid)";
        $coursesaccepted = $DB->get_recordset_sql($sql, $contextparams);
        foreach ($coursesrequested as $courserequest) {
            $status = '';
            switch ($courserequest->requeststate) {
                case 0 : $status = "is declined or not requested"; break;
                case 1 : $status = "is waiting"; break;
                case 2 : $status = "is granted"; break;
            }

            $data = (object)[
                'timestamp'=> date('m/d/Y H:i:s', $courserequest->timestamp),
                'type' => 'requested',
                'username' => $user->username,
                'status' => $status
            ];
            $contextdatatowrite[] = $data;
        }
        foreach ($coursesaccepted as $courserequest) {
            $status = '';
            switch ($courserequest->requeststate) {
                case 0 : $status = "is declined or not requested"; break;
                case 1 : $status = "is waiting"; break;
                case 2 : $status = "is granted"; break;
            }

            $data = (object)[
                'timestamp'=> date('m/d/Y H:i:s', $courserequest->timestamp),
                'type' => 'reponsible to accept or decline',
                'username' => $user->username,
                'status' => $status
            ];
            $contextdatatowrite[] = $data;
        }
        $subcontext = [
            get_string('pluginname', 'local_lsf_unification')
        ];
        $dummydata = (object)['dummy' => 'data'];
        writer::with_context($context)->export_data($subcontext, $dummydata);*/
    }

    public static function _delete_data_for_all_users_in_context($context) {
        global $DB;
        if (!$context instanceof \context_user) {
            return;
        }
    }

    public static function _delete_data_for_user($contextlist) {
        global $DB;
        $user = $contextlist->get_user();
        $DB->delete_records("local_lsf_course", array('acceptorid' => $user->id));
        $DB->delete_records("local_lsf_course", array('requestorid' => $user->id));
    }
}