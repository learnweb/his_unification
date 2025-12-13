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
 * File with different statistics.
 *
 * @package   local_lsf_unification
 * @copyright 2025 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_lsf_unification\pg_lite;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . "/config.php");
require_once(dirname(__FILE__) . "/class_pg_lite.php");
require_once(dirname(__FILE__) . ".lib.php");
require_once(dirname(__FILE__) . "/lib_features.php");
// Check permissions.
require_login();
if (!has_capability('moodle/site:config', context_system::instance())) {
    die("no access");
}

// HIS category origin id.
$reqsem = optional_param('semester', null, PARAM_INT);

set_time_limit(30 * 60);


/**
 * Create aggregate in lsf_view Database.
 * @return void
 */
function create_aggregate() {
    global $pgdb;
    pg_query($pgdb->connection, "DROP AGGREGATE IF EXISTS textcat_all(text);");
    pg_query($pgdb->connection, "CREATE AGGREGATE textcat_all(
            basetype    = text,
            sfunc       = textcat,
            stype       = text,
            initcond    = ''
    );");
}

/**
 * Retrieves and saves all title-id (ueid) and semester from the learnweb_ueberschrift table.
 *
 * @return mixed|null the semester from a given title-id (ueid))
 */
function get_cat_sem($ueid) {
    // LEARNWEB-TODO: filter the right row directly in the query, then return it. Dont save all semester and search for it.
    global $pgdb, $hupstatssemtable;
    // Read or (if not existing) create array.
    if (!isset($hupstatssemtable)) {
        $hupstatssemtable = [];
        $qmain = pg_query($pgdb->connection, "SELECT ueid, semester FROM " . HIS_UEBERSCHRIFT);
        while ($hislsftitle = pg_fetch_object($qmain)) {
            $hupstatssemtable[$hislsftitle->ueid] = $hislsftitle->semester;
        }
    }
    return isset($hupstatssemtable[$ueid]) ? $hupstatssemtable[$ueid] : null;
}

/**
 * Get an array that lists the veranstid and count for every type of 'veranstaltung'.
 *
 * @param $ueids
 * @return array
 */
function get_cat_veranstids_and_count($ueids) {
    global $pgdb;
    $hupstatsveranstcounttable = [];
    if (!empty($ueids)) {
        $sql = "SELECT veranstaltungsart,
                       textcat_all(DISTINCT " . HIS_UEBERSCHRIFT . ".veranstid || ',') as veranstids,
                       COUNT(DISTINCT " . HIS_UEBERSCHRIFT . ".veranstid) as c
                FROM " . HIS_UEBERSCHRIFT . "
                JOIN " . HIS_VERANSTALTUNG . "
                ON " . HIS_UEBERSCHRIFT . ".veranstid = " . HIS_VERANSTALTUNG . ".veranstid
                WHERE ueid IN (" . $ueids . ")
                GROUP BY veranstaltungsart;";
        $qmain = pg_query($pgdb->connection, $sql);
        while ($hislsftitle = pg_fetch_object($qmain)) {
            $veranstaltungsart = ["veranstids" => explode(",", $hislsftitle->veranstids), "count" => $hislsftitle->c];
            $hupstatsveranstcounttable[$hislsftitle->veranstaltungsart] = $veranstaltungsart;
        }
    }
    return $hupstatsveranstcounttable;
}


$pgdb = new pg_lite();
echo "<p>Verbindung: " . ($pgdb->connect() ? "ja" : "nein") . " (" . $pgdb->connection . ")</p>";
create_aggregate();

echo "<p><pre>";
// Root-Knoten herausfinden.
$toplevelorigins = get_his_toplevel_originids();

// Kategorien herausfinden.
$secondlevelorinins = get_newest_sublevels(implode(", ", $toplevelorigins));
foreach ($secondlevelorinins as $secondndlevel) {
    // Kategoriekopien herausfinden.
    $secondndlevel->txt = mb_convert_encoding($secondndlevel->txt, 'UTF-8', 'ISO-8859-1');
    $secondndlevel->copies = $DB->get_records("local_lsf_category", ["origin" => $secondndlevel->origin], null, "ueid");
    foreach ($secondndlevel->copies as $secondlevelcopy) {
        // Semester bestimmen.
        $secondlevelcopy->semester = get_cat_sem($secondlevelcopy->ueid);
        if (empty($reqsem) || ($reqsem == $secondlevelcopy->semester)) {
            // Alle Unterkategorien der jeweiligen Kategoriekopien sammeln.
            $records = $DB->get_records("local_lsf_categoryparenthood", ["parent" => $secondlevelcopy->ueid], null, "child");
            $secondlevelcopy->subs = array_keys($records);
            // Bestimme die Veranstatlungstypen und Anazahlen der jeweiligen Kategoriekopien.
            $secondlevelcopy->veranstcount = get_cat_veranstids_and_count(implode(",", $secondlevelcopy->subs));
            // Semesterarray erstellen (daten umformatieren).
            if (!isset($semstats[$secondlevelcopy->semester])) {
                $semstats[$secondlevelcopy->semester] = [];
            }
            if (!isset($semstats[$secondlevelcopy->semester][$secondndlevel->txt])) {
                $semstats[$secondlevelcopy->semester][$secondndlevel->txt] = [];
            }
            foreach ($secondlevelcopy->veranstcount as $typ => $veranstidsandcount) {
                if (!isset($semstats[$secondlevelcopy->semester][$secondndlevel->txt][$typ])) {
                    $arr = ["imported" => 0, "existing" => 0, "veranstids" => []];
                    $semstats[$secondlevelcopy->semester][$secondndlevel->txt][$typ] = $arr;
                }
                $veranstids = array_merge(
                    $semstats[$secondlevelcopy->semester][$secondndlevel->txt][$typ]["veranstids"],
                    $veranstidsandcount["veranstids"]
                );
                $semstats[$secondlevelcopy->semester][$secondndlevel->txt][$typ]["veranstids"] = $veranstids;
                $semstats[$secondlevelcopy->semester][$secondndlevel->txt][$typ]["existing"] += $veranstidsandcount["count"];
                // Zaehle bestehende kurse.
                foreach ($semstats[$secondlevelcopy->semester][$secondndlevel->txt][$typ]["veranstids"] as $veranstid) {
                    $isimported = $DB->record_exists("course", ["idnumber" => $veranstid]) ? 1 : 0;
                    $semstats[$secondlevelcopy->semester][$secondndlevel->txt][$typ]["imported"] += $isimported;
                }
            }
        }
    }
}

// Write CSV.
echo "Semester;Kategorie;Typ;AnzahlGesamt;AnzahlImportiert\n";
foreach ($semstats as $sem => $stats) {
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

$pgdb->dispose();
echo "<p>Verbindung geschlossen: " . (($pgdb->connection == null) ? "ja" : "nein") . "</p>";
