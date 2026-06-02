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

namespace local_lsf_unification;

use stdClass;

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
     * @param stdClass $teacher The user record of the teacher
     * @return array A list of course objects
     */
    public function courses_of_teacher(stdClass $teacher): array;
}
