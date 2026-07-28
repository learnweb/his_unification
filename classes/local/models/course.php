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

namespace local_lsf_unification\local\models;

use dml_exception;
use local_lsf_unification\local\cms\campus_management;

/**
 * This class represents an abstracted course from any cms. Is used as preparation to create a moodle course. This class is not
 * Moodle course itself.
 *
 * @package   local_lsf_unification
 * @copyright 2026 Tamaro Walter
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course {
    /** @var string Name of the entities table in the moodle database */
    const DB_TABLE_NAME = "local_lsf_unification_courses";

    /** @var int unique ID in course table */
    public readonly int $id;

    /** @var array all users of the course including their role */
    private array $users;

    /** @var array timestamps of all dates the course have */
    private array $dates;

    /**
     * Course constructor. This either returns an existing course or create a new one when no ID is passed. If you already have the
     * course please use the static from_record() function to work with courses.
     * @throws dml_exception
     */
    public function __construct(
        /** @var string which cms the course originates from */
        public readonly string $cms,
        /** @var int id of the course in the cms */
        public readonly int $instance,
        /** @var string title of the course */
        public readonly string $title,
        /** @var string short title of the course */
        public readonly string $shorttitle,
        /** @var string description of the course */
        public readonly string $description,
        /** @var string user name of the main responsible teacher of the course */
        public readonly string $teacher,
        /** @var string which semester the course belongs to */
        public readonly string $semester,
        /** @var int timestamp of when the course was created in the cms */
        public readonly int $created,
        /** @var string url to the course in the cms */
        public readonly string $url,
        ?int $id = null,
    ) {
        global $DB;
        if ($id !== null) {
            $this->id = $id;
        } else {
            $existing = $DB->get_record(self::DB_TABLE_NAME, ['cms' => $this->cms, 'instance' => $this->instance]);
            $this->id = $existing->id ?? $DB->insert_record(self::DB_TABLE_NAME, (object) [
                'cms'         => $this->cms,
                'instance'    => $this->instance,
                'title'       => $this->title,
                'shorttitle'  => $this->shorttitle,
                'description' => $this->description,
                'teacher'     => $this->teacher,
                'semester'    => $this->semester,
                'created'     => $this->created,
                'url'         => $this->url,
            ]);
        }
    }

    /**
     * Returns a course from a course id.
     * @param int $id
     * @return course
     */
    public static function construct_from_id(int $id): course {
        global $DB;
        return self::construct_from_record($DB->get_record(self::DB_TABLE_NAME, ['id' => $id], '*', MUST_EXIST));
    }

    /**
     * Returns a course from a course id.
     * @param int $id
     * @return course
     */
    public static function construct_from_record(object $record): course {
        return new self(
            $record->cms,
            $record->instance,
            $record->title,
            $record->shorttitle,
            $record->description,
            $record->teacher,
            $record->semester,
            $record->created,
            $record->url,
            $record->id,
        );
    }

    /**
     * Loads and caches the users of this course from the given campus management system.
     *
     * @param campus_management $cms
     * @return void
     */
    public function load_users_of_course(campus_management $cms): void {
        $this->users = $cms->get_users_of_course($this);
    }

    /**
     * Loads and caches the dates of this course from the given campus management system.
     *
     * @param campus_management $cms
     * @return void
     */
    public function load_dates_of_course(campus_management $cms): void {
        $this->dates = $cms->get_course_dates($this);
    }
}
