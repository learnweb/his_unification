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

namespace local_lsf_unification\local\service;

use Exception;
use coding_exception;
use core\context\system;
use core_user;
use local_lsf_unification\local\models\course_request;
use local_lsf_unification\local\dto\course_dto;
use local_lsf_unification\local\dto\request_dto;
use moodle_url;

/**
 * Service class that handles requests of the dashboard that are not cms specific.
 *
 * @package   local_lsf_unification
 * @copyright 2026 Tamaro Walter
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dashboard_service {
    /**
     * Returns all imported or requested courses from the user that are shown on the dashboard.
     * @return array
     */
    public static function get_dashboard_courses(): array {
        global $DB, $USER;
        $mintime = time() - (get_config('local_lsf_unification', 'max_import_age') * DAYSECS);
        // Get Requested courses.
        $sql = "SELECT request.id AS requestid, course.*, request.state AS state
                FROM {local_lsf_unification_course_requests} request
                JOIN {local_lsf_unification_courses} course ON course.id = request.courseid
                WHERE request.requesterid = :userid
                AND course.created > :mintime
                ORDER BY course.created DESC";
        $requests = $DB->get_records_sql($sql, ['userid' => $USER->id, 'mintime' => $mintime]);

        // Get imported courses.
        $sql = "SELECT course.*, import.moodleid AS moodleid
                FROM {local_lsf_unification_imported_courses} import
                JOIN {local_lsf_unification_courses} course ON course.id = import.courseid
                WHERE course.teacher = :username
                AND course.created > :mintime
                ORDER BY course.created DESC";
        $imported = $DB->get_records_sql($sql, ['username' => $USER->username, 'mintime' => $mintime]);

        $courses = array_merge($requests, $imported);

        // Return course dto and set the moodle id or state if its set.
        return array_map(
            fn(int $i, object $course) => new course_dto(
                $i,
                $course->cms,
                $course->instance,
                $course->url,
                $course->title,
                $course->shorttitle,
                '',
                $course->teacher,
                $course->semester,
                $course->created,
                $course->id,
                $course->moodleid ?? 0,
                $course->state ?? 0,
                !empty($course->moodleid) ? (new moodle_url('/course/view.php', ['id' => $course->moodleid]))->out() : '',
            ),
            array_keys($courses),
            $courses,
        );
    }

    /**
     * Returns the requests that users made for a course where the current user is the teacher of.
     * @return array
     */
    public static function get_course_requests(): array {
        global $DB, $USER;
        $mintime = time() - (get_config('local_lsf_unification', 'max_import_age') * DAYSECS);
        // Get courses that were requested on the users behalf.
        $sql = "SELECT request.id AS requestid, request.state AS requeststate, request.created AS requestcreated, course.*,
                u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename
                FROM {local_lsf_unification_course_requests} request
                JOIN {local_lsf_unification_courses} course ON course.id = request.courseid
                JOIN {user} u ON u.id = request.requesterid
                WHERE course.teacher = :username
                AND course.created > :mintime
                AND request.state = :pendingstate
                ORDER BY course.created DESC";
        $params = ['username' => $USER->username, 'mintime' => $mintime, 'pendingstate' => course_request::REQUEST_PENDING];
        $records = $DB->get_records_sql($sql, $params);

        return array_map(
            fn(object $record) => new request_dto(
              $record->requestid,
              $record->title,
              core_user::get_fullname(
                  (object) [
                    'firstname'         => $record->firstname,
                    'lastname'          => $record->lastname,
                    'firstnamephonetic' => $record->firstnamephonetic,
                    'lastnamephonetic'  => $record->lastnamephonetic,
                    'middlename'        => $record->middlename,
                    'alternatename'     => $record->alternatename,
                  ],
                  system::instance()
              ),
              $record->requeststate,
              $record->requestcreated,
            ),
            array_values($records),
        );
    }

    public static function update_request(int $id, string $action): bool {
        global $DB;

        // 1. Step: Validations: Check that the request really exists. Nothing is updated if it does not.
        if (empty($DB->get_record(course_request::DB_TABLE_NAME, ["id" => $id]))) {
            return false;
        }

        try {
            $transaction = $DB->start_delegated_transaction();
            $param = ['id' => $id];
            match ($action) {
                'approve' => $DB->set_field(course_request::DB_TABLE_NAME, 'state', course_request::REQUEST_ACCEPTED, $param),
                'reject' => $DB->set_field(course_request::DB_TABLE_NAME, 'state', course_request::REQUEST_DECLINED, $param),
                default => throw new coding_exception('Wrong action parameter given from the frontend'),
            };
            $transaction->allow_commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollback($e);
        }
        return false;
    }

    /**
     * Returns all existing Moodle course categories as a flat list. Each entry has the
     * category id and its display name (the full path, e.g. "Faculty / Institute"), which
     * is what the wizard shows in the category dropdown.
     * @return array
     */
    public static function get_categories(): array {
        $list = \core_course_category::make_categories_list();
        return array_map(
            fn(int $id, string $name) => ['id' => $id, 'name' => $name],
            array_keys($list),
            $list,
        );
    }
}
