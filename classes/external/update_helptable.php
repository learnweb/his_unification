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

namespace local_lsf_unification\external;

use external_api;
use external_function_parameters;
use external_value;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/externallib.php');
require_once($CFG->libdir . '/externallib.php');

/**
 * Class implementing the external API, esp. for AJAX functions.
 *
 * @package    local_lsf_unification
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_helptable extends external_api {
    /**
     * Parameters of update_helptable function.
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([]);
    }

    /**
     * Return value of update_helptable function.
     * @return external_value|null
     */
    public static function execute_returns(): ?external_value {
        return new external_value(PARAM_BOOL, 'success status');
    }

    /**
     * Calls the update_helptable function.
     * @return bool
     */
    public static function execute(): bool {
        global $CFG;
        require_once($CFG->dirroot . '/local/lsf_unification/lib_his.php');
        insert_missing_helptable_entries();
        return true;
    }
}
