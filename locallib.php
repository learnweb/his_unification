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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Internal library of functions for lsf unification
 *
 * @package   local_lsf_unification
 * @copyright 2026 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Caches all language strings keys so react components can access them.
 * @return void
 */
function lsf_unification_cache_strings(): void {
    global $PAGE;
    $strings = get_string_manager()->load_component_strings('local_lsf_unification', current_language());
    $PAGE->requires->strings_for_js(array_keys($strings), 'local_lsf_unification');
}
