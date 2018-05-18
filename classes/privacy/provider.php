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

namespace lsf_unification\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem for lsf_unification implementing metadata\provider.
 * Remark: this Plugin does not need to provide a export to external sources since data from the HisLSF system
 * is only read not exported
 * @copyright  2018 Nina Herrmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use \core_privacy\local\request\contextlist;
use core_privacy\local\request\writer;
use core_privacy\local\metadata\collection;

class provider implements \core_privacy\local\metadata\provider, \core_privacy\local\request\plugin\provider {
    use \core_privacy\local\legacy_polyfill;

    public static function _get_metadata($collection) {
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
        $collection->add_database_table(
            'local_lsf_category',
            [
                'ueid' => 'privacy:metadata:local_lsf_unification:ueid',
                'mdlid' => 'privacy:metadata:local_lsf_unification:mdlid',
                'origin' => 'privacy:metadata:local_lsf_unification:origin',
                'parent' => 'privacy:metadata:local_lsf_unification:parent',
                'txt' => 'privacy:metadata:local_lsf_unification:txt',
                'txt2' => 'privacy:metadata:local_lsf_unification:txt2',
                'timestamp' => 'privacy:metadata:local_lsf_unification:timestamp'
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
        // In case the user is not a teacher we can return null for the course_category table.
        $histeacherid = get_teachers_pid($userid);
        if (!empty($histeacherid)) {
            $params = [
                'modname'           => 'lsf_unification',
                'contextlevel'      => CONTEXT_COURSECAT,
                'ueid'  => $histeacherid,
            ];
            $sql = "SELECT c.id
                 FROM {context} c
           INNER JOIN {course_categories} co ON co.id = c.instanceid AND c.contextlevel = :contextlevel
           LEFT JOIN {local_lsf_category} lsfc ON lsfc.mdlid = co.id WHERE (
                lsfc.ueid        = :hisuserid
                )
        ";
            $contextlist->add_from_sql($sql, $params);
        }

        try {
            $hasrequest = $DB->get_record('local_lsf_course', array('requesterid' => $userid));
        } catch (dml_missing_record_exception $e) {}
        try {
            $hasaccepted = $DB->get_record('local_lsf_course', array('acceptorid' => $userid));
        } catch (dml_missing_record_exception $e) {}
        if (!empty($hasrequest) || !empty($hasaccepted) ) {
            // System_context is the only way since declined courses do not belong to a course or a category in Moodle.
            // Therefore course_context or category_context can not be used.
            $contextlist->add_system_context();
        }
        return $contextlist;
    }

    /**
     * Export the user data to a given context.
     * @param $contextlist
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function _export_user_data($contextlist) {
        global $DB;
        $contextused = $contextlist->get_contexts();
        if (empty($contextused)) {
            return;
        }
        $context = \context_user::instance($contextlist->get_user()->id);
        $user = $contextlist->get_user();
        if (in_array(CONTEXT_SYSTEM, $contextused)) {
            try {
                $requestedcourses = $DB->get_records('local_lsf_course', array('requesterid' => $user->id));
                if (!empty($requestedcourses)) {
                    foreach ($requestedcourses as $r => $requestedcourse) {
                        $status = '';
                        switch ($requestedcourse->requeststate) {
                            case 0 : $status = "is declined"; break;
                            case 1 : $status = "is waiting"; break;
                            case 2 : $status = "is accepted"; break;
                        }
                        try {
                            $course = $DB->get_record('course', array('id' => $requestedcourse->mdlid));
                            writer::with_context($context)->export_data([get_string('privacy:local_lsf_unification:requester_of_category',
                                'local_lsf_unification', array('coursename' => $course->fullname,
                                    'timestamp'=> date('m/d/Y H:i:s', $requestedcourse->timestamp), 'status' => $status))],
                                $requestedcourse);
                        } catch (\dml_missing_record_exception $e) {
                            writer::with_context($context)->export_data([get_string('privacy:local_lsf_unification:requester_of_category:notexist',
                                'local_lsf_unification', array('timestamp'=> date('m/d/Y H:i:s', $requestedcourse->timestamp)))],
                                $requestedcourse);
                        }

                    }
                }
            } catch (dml_missing_record_exception $e) {}
            try {
                $acceptedcourses = $DB->get_records('local_lsf_course', array('acceptorid' => $user->id));
                if (!empty($acceptedcourses)) {
                    foreach ($acceptedcourses as $r => $acceptedcourse) {
                        $status = '';
                        switch ($acceptedcourse->requeststate) {
                            case 0 : $status = "declined"; break;
                            case 1 : $status = "should manage"; break;
                            case 2 : $status = "accepted"; break;
                        }
                        try {
                            $course = $DB->get_record('course', array('id' => $acceptedcourse->mdlid));
                            writer::with_context($context)->export_data([get_string('privacy:local_lsf_unification:acceptor_of_category',
                                'local_lsf_unification', array('coursename' => $course->fullname,
                                    'timestamp'=> date('m/d/Y H:i:s', $acceptedcourse->timestamp), 'status' => $status))],
                                (object) $acceptedcourse);
                        } catch (\dml_missing_record_exception $e) {
                            writer::with_context($context)->export_data([get_string('privacy:local_lsf_unification:acceptor_of_category:notexist',
                                'local_lsf_unification', array('timestamp'=> date('m/d/Y H:i:s', $acceptedcourse->timestamp)))],
                                (object) $acceptedcourse);
                        }
                    }
                }
            } catch (dml_missing_record_exception $e) {}
        }
        // Check whether the current user manages a category.
        if (in_array(CONTEXT_COURSECAT, $contextused)) {
            $histeacherid = get_teachers_pid($user->id);
            $categorymanager = $DB->get_records('local_lsf_category', array('ueid' => $histeacherid));
            if ($categorymanager != null) {
                foreach ($categorymanager as $c => $acceptedcourse) {
                    writer::with_context($context)->export_data([get_string('privacy:local_lsf_unification:manager_of_category',
                        'local_lsf_unification', array('categoryname' => $acceptedcourse->txt2,
                            'timestamp'=> date('m/d/Y H:i:s', $acceptedcourse->timestamp)))],
                        (object) $categorymanager);
                }
            }
        }
    }

    public static function _delete_data_for_all_users_in_context($context) {
        // TODO: Implement delete_data_for_all_users_in_context() method.
    }

    public static function _delete_data_for_user($contextlist) {
        // TODO: Implement delete_data_for_user() method.
    }
}