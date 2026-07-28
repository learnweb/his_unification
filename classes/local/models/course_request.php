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

use coding_exception;
use dml_exception;

/**
 * This class represents pending request for a course, that was openend when a non teacher tried to import a course from a cms.
 * An object of this class represents an entity in the course_request table
 * @package   local_lsf_unification
 * @copyright 2026 Tamaro Walter
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_request {
    /** @var string Name of the entities table in the moodle database */
    const DB_TABLE_NAME = "local_lsf_unification_course_requests";

    /** Pending status. */
    const REQUEST_PENDING = 1;

    /** Accepted status.*/
    const REQUEST_ACCEPTED = 2;

    /** Declined status. */
    const REQUEST_DECLINED = 3;

    /** Imported status. The course was imported by the teacher after the request. */
    const REQUEST_IMPORTED = 4;

    /** @var int Unique ID in the database table*/
    public readonly int $id;

    /**
     * .Constructor
     * @throws dml_exception
     */
    public function __construct(
        /** @var int moodle id of the user that requested the course */
        public readonly int $requesterid,
        /** @var int abstract course id (not a moodle course id!) */
        public readonly int $courseid,
        /** @var int current state of the request */
        private int $state,
        /** @var int timestamp of the creation date  */
        private int $created,
        ?int $id = null,
    ) {
        global $DB;

        $this->id = $id ?? $DB->insert_record(self::DB_TABLE_NAME, (object) [
            'requesterid' => $this->requesterid,
            'courseid' => $this->courseid,
            'state' => $this->state,
            'created' => $this->created,
        ]);
    }

    /**
     * Returns a course reqeust from an id.
     * @param int $id
     * @return course_request
     */
    public static function construct_from_id(int $id): course_request {
        global $DB;
        return self::construct_from_record($DB->get_record(self::DB_TABLE_NAME, ['id' => $id], '*', MUST_EXIST));
    }

    /**
     * Returns a course reqeust from a record.
     * @param object $record
     * @return course_request
     * @throws coding_exception
     */
    public static function construct_from_record(object $record): course_request {
        return new self($record->requesterid, $record->courseid, $record->state, $record->id);
    }

    /**
     * Approves the request
     * @return void
     */
    public function approve(): void {
        global $DB;
        if ($this->state == self::REQUEST_PENDING) {
            $this->state = self::REQUEST_ACCEPTED;
            $DB->update_record(self::DB_TABLE_NAME, $this->get_db_object());
        }
    }

    /**
     * Declines the request
     * @return void
     */
    public function decline(): void {
        global $DB;
        if ($this->state == self::REQUEST_PENDING) {
            $this->state = self::REQUEST_DECLINED;
            $DB->update_record(self::DB_TABLE_NAME, $this->get_db_object());
        }
    }

    /**
     * Returns a plain database object representation of this request.
     * @return object
     */
    private function get_db_object(): object {
        return (object) [
            'id' => $this->id,
            'requesterid' => $this->requesterid,
            'courseid' => $this->courseid,
            'state' => $this->state,
            'created' => $this->created,
        ];
    }
}
