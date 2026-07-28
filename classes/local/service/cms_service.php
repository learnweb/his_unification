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
use local_lsf_unification\local\cms\his_lsf;
use local_lsf_unification\local\cms\sap;
use local_lsf_unification\local\dto\course_dto;
use local_lsf_unification\local\models\course;
use local_lsf_unification\local\models\course_request;
use local_lsf_unification\local\models\imported_course;

/**
 * Service class that handles requests that concerns the cms classes.
 *
 * @package   local_lsf_unification
 * @copyright 2026 Tamaro Walter
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cms_service {
    /**
     * Returns every course the teacher has in every cms as an array of course_dto dto's.
     * @param ?object $teacher You can pass a certain teacher, otherwise $USER is used.
     * @param bool $isrequest If the call comes from a request on behalf of a teacher.
     * @return array
     */
    public static function get_courses_from_teacher(object $teacher = null, bool $isrequest = false): array {
        global $USER, $DB;
        $teacher = $teacher ?? $USER;
        $lsf = new his_lsf(get_config('local_lsf_unification'));
        $sap = new sap();
        $courses = array_merge($lsf->get_courses_of_teacher($teacher), $sap->get_courses_of_teacher($teacher));

        // Filter out already imported courses and courses that the current user requested. A user can not request a course twice.
        $importedids = $DB->get_fieldset(imported_course::DB_TABLE_NAME, 'courseid');
        $requestids = $isrequest ? $DB->get_fieldset(course_request::DB_TABLE_NAME, 'courseid', ['requesterid' => $USER->id]) : [];

        // Combine the ids and then flip keys and values to make an array lookup instead of value scanning.
        $excluded = array_flip(array_merge($importedids, $requestids));
        $courses = array_values(array_filter($courses, fn(course $course) => !isset($excluded[$course->id])));

        // Create the DTO's.
        return array_map(
            fn(course $course) => course_dto::from_course($course),
            $courses,
        );
    }

    /**
     * Return an array of teachers that have courses in the cms.
     * @return array
     */
    public static function get_teachers(): array {
        $lsf = new his_lsf(get_config('local_lsf_unification'));
        $sap = new sap();
        // LEARNWEB-TODO: Keep in mind that the same teacher can be in sap and lsf (but they can have different username).
        return array_merge($lsf->get_teachers(), $sap->get_teachers());
    }

    /**
     * Saves a course request in the database.
     * @return bool
     */
    public static function add_request(object $course): bool {
        global $DB, $USER;
        // 1. Step: Validations: Check that the course really exists.
        if (empty($DB->get_record(course::DB_TABLE_NAME, ["id" => $course->id]))) {
            return false;
        }
        // LEARNWEB-TODO: What happens if there is already a request for that course? I think there could be many pending requests.
        // LEARNWEB-TODO: but if a techer accepts a requests all pending ones get declined. On decline only the one gets deleted.

        // 2. Step: Create a request entry. The requester is the current user.
        $request = (object) ['requesterid' => $USER->id, 'courseid' => $course->id, 'state' => course_request::REQUEST_PENDING];
        $result = (bool) $DB->insert_record(course_request::DB_TABLE_NAME, $request);

        // 3. Sent an email.
        return $result;
    }

    /**
     * Imports a cms course into Moodle: creates the real Moodle course, enrols the importing user
     * as its teacher and records that the cms course now has a Moodle counterpart.
     *
     * @param object $course The course the wizard submitted, in the shape of a course_dto.
     * @param object $category The Moodle category the course is created in.
     * @return bool Whether the import went through.
     */
    public static function import_course(object $course, object $category): bool {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . '/course/lib.php');

        // 1. Step: Validations: Check that the course really exists. Nothing is created if it does not.
        if (empty($DB->get_record(course::DB_TABLE_NAME, ["id" => $course->id]))) {
            return false;
        }

        try {
            $transaction = $DB->start_delegated_transaction();

            // Create a unique shortname.
            $shortname = "{$course->shorttitle}_{$course->semester}";
            if ($DB->record_exists('course', ['shortname' => $shortname])) {
                $shortname .= "_{$course->cmsinstance}";
            }

            // 2. Create the moodle course.
            $moodlecourse = create_course((object) [
                'category' => $category->id,
                'fullname' => $course->title,
                'shortname' => $shortname,
                'idnumber' => "{$course->cms}_{$course->cmsinstance}",
                'summary' => $course->description,
                'summaryformat' => FORMAT_HTML,
                'startdate' => $course->created,
            ]);

            // 3. Step: Enrol the teacher into the course.
            $roleid = $DB->get_field('role', 'id', ['shortname' => 'editingteacher'], MUST_EXIST);
            enrol_try_internal_enrol($moodlecourse->id, $USER->id, $roleid);

            // 4. Mark all requests to this course as "imported".
            $DB->set_field(course_request::DB_TABLE_NAME, 'state', course_request::REQUEST_IMPORTED, ['courseid' => $course->id]);

            // 5. Step. Create a course import entry.
            $import = new imported_course($moodlecourse->id, $course->id);

            $transaction->allow_commit();
            return !empty($import->id);
        } catch (Exception $e) {
            $transaction->rollback($e);
        }
        return false;
    }
}
