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

namespace local_lsf_unification\task;


use coding_exception;
use core\task\scheduled_task;
use local_lsf_unification\pg_lite;

/**
 * Scheduled task that updates the helptables by inserting missing entries.
 *
 * @package   local_lsf_unification
 * @copyright 2026 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class insert_missing_helptable_entries extends scheduled_task {
    #[\Override]
    public function get_name(): string {
        return get_string('task_missing_helptable_entries', 'local_lsf_unification');
    }

    #[\Override]
    public function execute(): void {
        global $CFG;
        require_once($CFG->dirroot . '/local/lsf_unification/lib_his.php');
        insert_missing_helptable_entries(true);
    }
}
