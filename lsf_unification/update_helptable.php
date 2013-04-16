<?php
include("../../config.php");
include("./class_pg_lite.php");
include("./lib.php");
include("./lib_features.php");
/// Check permissions.
require_login();

set_time_limit(15*60);

echo "<p>! = unknown category found, ? = unknown linkage found, x = according helptable entry written<br><i>if x is missing, probably the information about the element wasn't complete</i></p>";

$pgDB = new pg_lite();
echo "<p>Verbindung: ".($pgDB->connect()?"ja":"nein")." (".$pgDB->connection.")</p>"; 

insert_missing_helptable_entries(true);

$pgDB->dispose();
echo "<p>Verbindung geschlossen: ".(($pgDB->connection==NULL)?"ja":"nein")."</p>"; 