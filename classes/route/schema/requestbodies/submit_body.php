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
use local_lsf_unification\route\schema\category_schema;
use local_lsf_unification\route\schema\course_schema;
use local_lsf_unification\route\schema\teacher_schema;

/**
 * Request body specification of the wizard cache that is sent when the wizard is submitted.
 *
 * Shared by both branches: the wizard always submits its whole cache, so the request submit
 * sends a teacher and a null category and the import submit sends a category and a null
 * teacher. Only the branch and the course are common to both, so the teacher and the category
 * are declared optional here and the controller checks per branch which of them it needs.
 *
 * The schema doubles as an allowlist: any key that is not declared here is stripped
 * from the parsed body before the controller sees it.
 *
 * @package   local_lsf_unification
 * @copyright 2026 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submit_body extends request_body {
    /**
     * Constructor.
     * @throws \core\exception\coding_exception
     */
    public function __construct() {
        parent::__construct(
            description: 'The wizard cache: the branch the user took plus the chosen course, ' .
                'the teacher (request branch) and the category (import branch).',
            content: new payload_response_type(
                schema: new schema_object(
                    content: [
                        'branch' => new scalar_type(param::ALPHA, required: true),
                        'teacher' => new teacher_schema(),
                        'course' => new course_schema(required: true),
                        'category' => new category_schema(),
                    ],
                ),
            ),
            required: true,
        );
    }
}
