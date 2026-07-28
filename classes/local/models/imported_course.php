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

/**
 * This class represents an imported course to the moodle system.
 * An object of this class represents an entity in the database table
 * @package   local_lsf_unification
 * @copyright 2026 Tamaro Walter
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class imported_course {
    /** @var string Name of the entities table in the moodle database */
    const DB_TABLE_NAME = "local_lsf_unification_imported_courses";

    /** @var int Unique identifier */
    public readonly int $id;

    /**
     * Constructor. creates db entity on the fly if no id is passed.
     * @param int $moodleid
     * @param int $courseid
     * @param int|null $id
     * @throws dml_exception
     */
    public function __construct(
        /** @var int Moodle course id */
        public readonly int $moodleid,
        /** @var int abstract course id */
        public readonly int $courseid,
        ?int $id = null,
    ) {
        global $DB;
        $this->id = $id ?? $DB->insert_record(self::DB_TABLE_NAME, (object) [
            'moodleid' => $this->moodleid,
            'courseid' => $this->courseid,
        ]);
    }

    /**
     * Builds object from row id.
     * @param int $id
     * @return imported_course
     * @throws dml_exception
     */
    public static function construct_from_id(int $id): imported_course {
        global $DB;
        return self::construct_from_record(
            $DB->get_record(self::DB_TABLE_NAME, ['id' => $id], '*', MUST_EXIST)
        );
    }

    /**
     * Buids object from db record.
     * @param object $record
     * @return imported_course
     * @throws dml_exception
     */
    public static function construct_from_record(object $record): imported_course {
        return new self($record->moodleid, $record->courseid, $record->id);
    }
}
