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
require_login();
if (!has_capability('moodle/site:config', context_system::instance())) {
    die("no access");
}

$reqSem         = optional_param('semester', null, PARAM_INT);       // his category origin id

set_time_limit(30 * 60);


function create_aggregate() {
    global $pgDB;
    pg_query($pgDB->connection, "DROP AGGREGATE IF EXISTS textcat_all(text);");
    pg_query($pgDB->connection, "CREATE AGGREGATE textcat_all(
            basetype    = text,
            sfunc       = textcat,
            stype       = text,
            initcond    = ''
    );");
}

function get_cat_sem($ueid) {
    global $pgDB, $hup_stats_sem_table;
    // read or (if not existing) create array
    if (!isset($hup_stats_sem_table)) {
        $hup_stats_sem_table = [];
        $q_main = pg_query($pgDB->connection, "SELECT ueid, semester FROM " . HIS_UEBERSCHRIFT);
        while ($hislsf_title = pg_fetch_object($q_main)) {
            $hup_stats_sem_table[$hislsf_title->ueid] = $hislsf_title->semester;
        }
    }
    return isset($hup_stats_sem_table[$ueid]) ? $hup_stats_sem_table[$ueid] : null;
}

function get_cat_veranstids_and_count($ueids) {
    global $pgDB;
    $hup_stats_veranstcount_table = [];
    if (!empty($ueids)) {
        $q_main = pg_query($pgDB->connection, "SELECT veranstaltungsart, textcat_all(DISTINCT " . HIS_UEBERSCHRIFT . ".veranstid || ',') as veranstids, COUNT(DISTINCT " . HIS_UEBERSCHRIFT . ".veranstid) as c FROM " . HIS_UEBERSCHRIFT . " JOIN " . HIS_VERANSTALTUNG . " on " . HIS_UEBERSCHRIFT . ".veranstid = " . HIS_VERANSTALTUNG . ".veranstid WHERE ueid IN (" . $ueids . ") GROUP BY veranstaltungsart");
        while ($hislsf_title = pg_fetch_object($q_main)) {
            $hup_stats_veranstcount_table[$hislsf_title->veranstaltungsart] = ["veranstids" => explode(",", $hislsf_title->veranstids), "count" => $hislsf_title->c];
        }
    }
    return $hup_stats_veranstcount_table;
}


$pgDB = new pg_lite();
echo "<p>Verbindung: " . ($pgDB->connect() ? "ja" : "nein") . " (" . $pgDB->connection . ")</p>";
create_aggregate();

echo "<p><pre>";
// Root-Knoten herausfinden
$toplevel_origins = get_his_toplevel_originids();
// echo "TOPLEVEL_IDs = ".print_r($toplevel_origins,true)."\n\n";
// Kategorien herausfinden
$secondlevel_orinins = get_newest_sublevels(implode(", ", $toplevel_origins));
foreach ($secondlevel_orinins as $secondndlevel) {
    // Kategoriekopien herausfinden
    $secondndlevel->txt = mb_convert_encoding($secondndlevel->txt, 'UTF-8', 'ISO-8859-1');
    $secondndlevel->copies = $DB->get_records("local_lsf_category", ["origin" => $secondndlevel->origin], null, "ueid");
    foreach ($secondndlevel->copies as $secondlevel_copy) {
        // Semester bestimmen
        $secondlevel_copy->semester = get_cat_sem($secondlevel_copy->ueid);
        if (empty($reqSem) || ($reqSem == $secondlevel_copy->semester)) {
            // Alle Unterkategorien der jeweiligen Kategoriekopien sammeln
            $secondlevel_copy->subs = array_keys($DB->get_records("local_lsf_categoryparenthood", ["parent" => $secondlevel_copy->ueid], null, "child"));
            // Bestimme die Veranstatlungstypen und Anazahlen der jeweiligen Kategoriekopien
            $secondlevel_copy->veranstcount = get_cat_veranstids_and_count(implode(",", $secondlevel_copy->subs));
            // Semesterarray erstellen (daten umformatieren)
            if (!isset($sem_stats[$secondlevel_copy->semester])) {
                $sem_stats[$secondlevel_copy->semester] = [];
            }
            if (!isset($sem_stats[$secondlevel_copy->semester][$secondndlevel->txt])) {
                $sem_stats[$secondlevel_copy->semester][$secondndlevel->txt] = [];
            }
            foreach ($secondlevel_copy->veranstcount as $typ => $veranstids_and_count) {
                if (!isset($sem_stats[$secondlevel_copy->semester][$secondndlevel->txt][$typ])) {
                    $sem_stats[$secondlevel_copy->semester][$secondndlevel->txt][$typ] = ["imported" => 0, "existing" => 0, "veranstids" => []];
                }
                $sem_stats[$secondlevel_copy->semester][$secondndlevel->txt][$typ]["veranstids"] = array_filter(array_merge($sem_stats[$secondlevel_copy->semester][$secondndlevel->txt][$typ]["veranstids"], $veranstids_and_count["veranstids"]));
                $sem_stats[$secondlevel_copy->semester][$secondndlevel->txt][$typ]["existing"] += $veranstids_and_count["count"];
                // zaehle bestehende kurse
                foreach ($sem_stats[$secondlevel_copy->semester][$secondndlevel->txt][$typ]["veranstids"] as $veranstid) {
                    $sem_stats[$secondlevel_copy->semester][$secondndlevel->txt][$typ]["imported"] += $DB->record_exists("course", ["idnumber" => $veranstid]) ? 1 : 0;
                }
            }
        }
    }
}
// echo "2ndLEVEL_IDs = ".print_r($secondlevel_orinins,true)."\n\n";
// echo "SEM_STATSs = ".print_r($sem_stats,true)."\n\n";

// write CSV
echo "Semester;Kategorie;Typ;AnzahlGesamt;AnzahlImportiert\n";
foreach ($sem_stats as $sem => $stats) {
    foreach ($stats as $cat => $catstats) {
        if (!empty($catstats)) {
            foreach ($catstats as $typ => $count) {
                echo $sem . ";" . $cat . ";" . $typ . ";" . $count["existing"] . ";" . $count["imported"] . "\n";
            }
        }
    }
    flush();
}

echo "</pre></p>";

$pgDB->dispose();
echo "<p>Verbindung geschlossen: " . (($pgDB->connection == null) ? "ja" : "nein") . "</p>";
