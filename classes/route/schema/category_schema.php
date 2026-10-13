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

use core\param;
use core\router\schema\objects\schema_object;
use core\router\schema\objects\scalar_type;

/**
 * Schema of a single Moodle course category.
 *
 * Only the id and the display name are exposed; the id is what the wizard stores
 * and submits, the name is what the teacher sees in the dropdown.
 *
 * @package   local_lsf_unification
 * @copyright 2026 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class category_schema extends schema_object {
    /**
     * Constructor.
     *
     * @param bool $required Whether every field must be non-null. Only has an effect on
     *                       request bodies; responses are never validated against their schema.
     * @throws \core\exception\coding_exception
     */
    public function __construct(bool $required = false) {
        parent::__construct(
            content: [
                'id' => new scalar_type(param::INT, required: $required),
                'name' => new scalar_type(param::RAW, required: $required),
            ],
        );
    }

    /**
     * Validate the data against the schema.
     *
     * The wizard submits its whole cache, so the slots the current branch does not fill arrive
     * as null. A schema_object cannot express "nullable" the way a scalar_type can and iterates
     * the value unguarded, so a null is caught here and handed on untouched. The controller is
     * what decides whether the branch it got actually needed this key.
     *
     * @param mixed $data
     * @return mixed
     */
    #[\Override]
    public function validate_data(mixed $data) {
        return $data === null ? null : parent::validate_data($data);
    }
}
