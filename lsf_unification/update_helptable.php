<?php
include("../../config.php");
include("./class_pg_lite.php");
include("./lib.php");
include("./lib_features.php");
/// Check permissions.
require_login();

$tryeverything         = optional_param('tryeverything', false, PARAM_INT);       // his category origin id

set_time_limit(30*60);

echo "<p>! = unknown category found, ? = unknown linkage found<br><a href='?tryeverything=100000'>TryEverything?</a> <i>(set tryeverything to a value x, to only check ids greater then x)</i></p>";

$pgDB = new pg_lite();
echo "<p>Verbindung: ".($pgDB->connect()?"ja":"nein")." (".$pgDB->connection.")</p>"; 

flush();

insert_missing_helptable_entries(true, $tryeverything);

$pgDB->dispose();
echo "<p>Verbindung geschlossen: ".(($pgDB->connection==NULL)?"ja":"nein")."</p>";
