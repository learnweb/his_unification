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

namespace local_lsf_unification\route\schema;

use core\exception\coding_exception;
use core\param;
use core\router\schema\objects\schema_object;
use core\router\schema\objects\scalar_type;

/**
 * Schema of the request dto.
 *
 * @package   local_lsf_unification
 * @copyright 2026 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class request_schema extends schema_object {
    /**
     * Constructor.
     * @throws coding_exception
     */
    public function __construct() {
        parent::__construct(
            content: [
                'id' => new scalar_type(param::INT),
                'title' => new scalar_type(param::RAW),
                'requester' => new scalar_type(param::RAW),
                'requeststate' => new scalar_type(param::INT),
            ]
        );
    }
}