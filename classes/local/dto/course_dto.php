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

namespace local_lsf_unification\local\dto;

use local_lsf_unification\local\models\course;
use moodle_url;

/**
 * DTO for a course that gets shown in the user dashboard.
 *
 * @package   local_lsf_unification
 * @copyright 2026 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_dto {
    /**
     * Constructor.
     */
    public function __construct(
        /** @var int unique id of the abstracted course */
        public readonly int $id,
        /** @var string cms the course originates from */
        public readonly string $cms,
        /** @var int id of the course in the cms */
        public readonly int $cmsinstance,
        /** @var string url to the course in the cms */
        public readonly string $cmsurl,
        /** @var string title of the course */
        public readonly string $title,
        /** @var string short title of the course */
        public readonly string $shorttitle,
        /** @var string description of the course */
        public readonly string $description,
        /** @var string user name of the main responsible teacher */
        public readonly string $teacher,
        /** @var string semester the course belongs to */
        public readonly string $semester,
        /** @var int timestamp of when the course was created in the cms */
        public readonly int $created,
        /** @var int abstract course id */
        public readonly int $courseid,
        /** @var int moodle course id, 0 when not yet created */
        public readonly int $moodleid = 0,
        /** @var int request state of the user, if there is no request then the state is 0 */
        public readonly int $requeststate = 0,
        /** @var string url to the course in moodle */
        public readonly string $moodleurl = '',
    ) {
    }

    /**
     * Builds a course dto from a course entity.
     *
     * @param course $course
     * @return self
     */
    public static function from_course(course $course, int $moodleid = 0, int $requeststate = 0): self {
        return new self(
            $course->id,
            $course->cms,
            $course->instance,
            $course->url,
            $course->title,
            $course->shorttitle,
            $course->description,
            $course->teacher,
            $course->semester,
            $course->created,
            $course->id,
            $moodleid,
            $requeststate,
            $moodleid != 0 ? (new moodle_url('/course/view.php', ['id' => $moodleid]))->out() : '',
        );
    }

    /**
     * Returns the wire format (JSON shape) of this dashboard course.
     *
     * @return array
     */
    public function to_array(): array {
        return [
            'id' => $this->id,
            'cms' => $this->cms,
            'cmsinstance' => $this->cmsinstance,
            'cmsurl' => $this->cmsurl,
            'title' => $this->title,
            'shorttitle' => $this->shorttitle,
            'description' => $this->description,
            'teacher' => $this->teacher,
            'semester' => $this->semester,
            'created' => $this->created,
            'courseid' => $this->courseid,
            'moodleid' => $this->moodleid,
            'requeststate' => $this->requeststate,
            'moodleurl' => $this->moodleurl,
        ];
    }
}
