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

/**
 * Page that shows the update_helptable process.
 *
 * @package local_lsf_unification
 * @copyright 2025 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_lsf_unification\pg_lite;

require_once("../../config.php");
global $CFG;
require_once(dirname(__FILE__) . "/class_pg_lite.php");
require_once(dirname(__FILE__) . "/lib.php");
require_once(dirname(__FILE__) . "/lib_features.php");

// Check permissions.
require_admin();

// HIS category origin id.
$tryeverything = optional_param('tryeverything', false, PARAM_INT);

set_time_limit(30 * 60);

$pgdb = new pg_lite();
$pgdb->connect();
insert_missing_helptable_entries(false, $tryeverything);
$pgdb->dispose();

$returnto = new moodle_url('/local/lsf_unification/helptablemanager.php');
redirect($returnto, get_string('update_helptable_notification', 'local_lsf_unification'));
