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

namespace local_lsf_unification\route\schema\requestbodies;

use core\param;
use core\router\schema\objects\schema_object;
use core\router\schema\objects\scalar_type;
use core\router\schema\request_body;
use core\router\schema\response\content\payload_response_type;

/**
 * Request body for request action.
 * @package   local_lsf_unification
 * @copyright 2026 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class requestaction_body extends request_body{
    /**
     * Constructor.
     * @throws \core\exception\coding_exception
     */
    public function __construct() {
        parent::__construct(
            description: 'The request action: the id of the request that gets updated and the action (approved/rejected)',
            content: new payload_response_type(
                schema: new schema_object(
                    content: [
                        'id' => new scalar_type(param::INT, required: true),
                        'action' => new scalar_type(param::RAW, required: true),
                    ],
                ),
            ),
            required: true,
        );
    }
}
