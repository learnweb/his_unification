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

include("../../config.php");
include("./class_pg_lite.php");
include("./lib.php");
include("./lib_features.php");
/// Check permissions.
require_admin();

$tryeverything         = optional_param('tryeverything', false, PARAM_INT);       // his category origin id

set_time_limit(30 * 60);

echo "<p>! = unknown category found, ? = unknown linkage found<br><a href='?tryeverything=100000'>TryEverything?</a> <i>(set tryeverything to a value x, to only check ids greater then x)</i></p>";

$pgdb = new pg_lite();
echo "<p>Verbindung: " . ($pgdb->connect() ? "ja" : "nein") . " (" . $pgdb->connection . ")</p>";

flush();

insert_missing_helptable_entries(true, $tryeverything);

$pgdb->dispose();
echo "<p>Verbindung geschlossen: " . (($pgdb->connection == null) ? "ja" : "nein") . "</p>";
