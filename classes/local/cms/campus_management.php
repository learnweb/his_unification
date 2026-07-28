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

namespace local_lsf_unification\local\cms;

use moodle_url;

/**
 * Interface to campus management systems (CMS).
 *
 * @package   local_lsf_unification
 * @copyright 2026 Daniel Meißner <daniel.meissner@uni-muenster.de>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface campus_management {
    /**
     * Query the CMS for a list of courses of a given teacher.
     *
     * @param object $teacher The user record of the teacher
     * @return array A list of course objects
     */
    public function get_courses_of_teacher(object $teacher): array;

    /**
     * Query the CMS for all users that should be enrolled in the course with their respective role
     * @param object $course
     * @return array
     */
    public function get_users_of_course(object $course): array;

    /**
     * Add the link to the new existing moodle course to the course page in the cms.
     * @param object $course
     * @param moodle_url $url
     * @return void
     */
    public function set_moodle_link(object $course, moodle_url $url): void;

    /**
     * Get all single dates on which the course occurs. Can be used to import dates in the moodle calendar.
     * @param object $course
     * @return array
     */
    public function get_course_dates(object $course): array;

    /**
     * Return all teachers registered in the cms that have courses in the configured timespan. Can be used to search for
     * teachers when doing a course request.
     * @return array
     */
    public function get_teachers(): array;
}
