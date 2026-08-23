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

/**
 * DTO for a course request that gets shown in the request manager.
 *
 * @package   local_lsf_unification
 * @copyright 2026 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class request_dto {

    /**
     * Constructor.
     */
    public function __construct(
        /** @var int unique id of the request */
        public readonly int $id,
        /** @var string title of the course */
        public readonly string $title,
        /** @var string user fullname of the person who requested the course */
        public readonly string $requester,
        /** @var int request state*/
        public readonly int $requestsstate,
        /** @var int created date*/
        public readonly int $created,
    ) {}

    /**
     * Return the dto in JSON shape.
     * @return array
     */
    public function to_array(): array {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'requester' => $this->requester,
            'requestsstate' => $this->requestsstate,
            'created' => $this->created,
        ];
    }
}