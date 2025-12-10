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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_lsf_unification\output;

use core\output\renderable;
use core\output\renderer_base;
use core\output\templatable;
use stdClass;

/**
 * First overview dialog for the request page.
 *
 * @package    local_lsf_unification
 * @copyright  2025 Daniel Meißner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class first_overview implements renderable, templatable {

    /**
     * Constructor for first_overview.
     *
     * @param array $courses An array of courses as returned by get_teachers_course_list
     */
    public function __construct(
        /** @var array An array of courses as returned by get_teachers_course_list */
        public readonly array $courses
    ) {
    }

    #[\Override]
    public function export_for_template(renderer_base $output): stdClass {
        $courselist = array_map(function($course) {
            return (object) ["title" => $course->info];
        }, array_values($this->courses));
        $showremotecreation = get_config("local_lsf_unification", "remote_creation");

        return (object) [
            "courselist" => $courselist,
            "showremotecreation" => $showremotecreation,
        ];
    }
}
