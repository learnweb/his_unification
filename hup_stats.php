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

global $CFG, $DB;
require_once($CFG->dirroot . "/config.php");
require_once(dirname(__FILE__) . "/class_pg_lite.php");
require_once(dirname(__FILE__) . ".lib.php");
require_once(dirname(__FILE__) . "/lib_features.php");

// Konstanten für Fachbereiche.
define("FB1", "FB1: Evangelisch-Theologische Fakultät");
define("FB2", "FB2: Katholisch-Theologische Fakultät");
define("FB3", "FB3: Rechtswissenschaftliche Fakultät");
define("FB4", "FB4: Wirtschaftswissenschaftliche Fakultät");
define("FB5", "FB5: Medizinische Fakultät");
define("FB6", "FB6: Erziehungswissenschaften und Sozialwissenschaften");
define("FB7", "FB7: Psychologie und Sportwissenschaften");
define("FB8", "FB8: Geschichte und Philosophie");
define("FB9", "FB9: Philologie");
define("FB10", "FB10: Mathematik und Informatik");
define("FB11", "FB11: Physik");
define("FB12", "FB12: Chemie und Pharmazie");
define("FB13", "FB13: Biologie");
define("FB14", "FB14: Geowissenschaften");
define("FB15", "FB15: Musikhochschule");
define("FUV", "Fachbereichsunabhängige Veranstaltungen");

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
    pg_query($pgdb->connection, "DROP AGGREGATE textcat_all(text);");
    pg_query($pgdb->connection, "CREATE AGGREGATE textcat_all(
            basetype    = text,
            sfunc       = textcat,
            stype       = text,
            initcond    = ''
    );");
}

/**
 * Retrieves and saves all title-id (ueid) and semester from the learnweb_ueberschrift table.
 * @param int $ueid
 * @return int|null the semester from a given title-id (ueid))
 */
function get_cat_sem(int $ueid): int|null {
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
 * @param string $ueids
 * @return array
 */
function get_cat_veranstids_and_count(string $ueids): array {
    global $pgdb;
    $hupstatsveranstcounttable = [];
    if (!empty($ueids)) {
        $sql = "SELECT textcat_all(DISTINCT " . HIS_UEBERSCHRIFT . ".veranstid || ',') as veranstids,
                       COUNT(DISTINCT " . HIS_UEBERSCHRIFT . ".veranstid) as c
                FROM " . HIS_UEBERSCHRIFT . "
                JOIN " . HIS_VERANSTALTUNG . "
                ON " . HIS_UEBERSCHRIFT . ".veranstid = " . HIS_VERANSTALTUNG . ".veranstid
                WHERE ueid IN (" . $ueids . ");";
        $qmain = pg_query($pgdb->connection, $sql);
        while ($hislsftitle = pg_fetch_object($qmain)) {
            $hupstatsveranstcounttable = ["veranstids" => explode(",", $hislsftitle->veranstids), "count" => $hislsftitle->c];
        }
    }
    return $hupstatsveranstcounttable;
}


$pgdb = new pg_lite();
echo "<p>Verbindung: " . ($pgdb->connect() ? "ja" : "nein") . "</p>";
create_aggregate();

echo "<p><pre>";
// Root-Knoten herausfinden.
$toplevelorigins = get_his_toplevel_originids();

// Kategorien herausfinden.
$secondlevelorinins = get_newest_sublevels(implode(", ", $toplevelorigins));
foreach ($secondlevelorinins as $secondndlevel) {
    // Kategoriekopien herausfinden.
    $secondndlevel->copies = $DB->get_records("local_lsf_unification_category", ["origin" => $secondndlevel->origin], null, "ueid");
    foreach ($secondndlevel->copies as $secondlevelcopy) {
        // Semester bestimmen.
        $secondlevelcopy->semester = get_cat_sem($secondlevelcopy->ueid);
        if (empty($reqsem) || ($reqsem == $secondlevelcopy->semester)) {
            // Alle Unterkategorien der jeweiligen Kategoriekopien sammeln.
            $params = ["parent" => $secondlevelcopy->ueid];
            $records = $DB->get_records("local_lsf_unification_categoryparenthood", $params, null, "child");
            $secondlevelcopy->subs = array_keys($records);
            // Bestimme die Veranstatlungstypen und Anazahlen der jeweiligen Kategoriekopien.
            $secondlevelcopy->veranstcount = get_cat_veranstids_and_count(implode(",", $secondlevelcopy->subs));
            // Semesterarray erstellen (daten umformatieren).
            if (!isset($semstats[$secondlevelcopy->semester])) {
                $semstats[$secondlevelcopy->semester] = [];
            }
            if (!isset($semstats[$secondlevelcopy->semester][$secondndlevel->txt])) {
                $semstats[$secondlevelcopy->semester][$secondndlevel->txt] = ["imported" => 0, "existing" => 0, "veranstids" => []];
            }
            if (
                !empty($secondlevelcopy->veranstcount) && key_exists(
                    "veranstids",
                    $secondlevelcopy->veranstcount
                ) && key_exists(
                    "count",
                    $secondlevelcopy->veranstcount
                )
            ) {
                $array1 = $semstats[$secondlevelcopy->semester][$secondndlevel->txt]["veranstids"];
                $arrayfilter = array_filter(array_merge($array1, $secondlevelcopy->veranstcount["veranstids"]));
                $semstats[$secondlevelcopy->semester][$secondndlevel->txt]["veranstids"] = $arrayfilter;
                $semstats[$secondlevelcopy->semester][$secondndlevel->txt]["existing"] += $secondlevelcopy->veranstcount["count"];
                // Zaehle bestehende kurse.
                foreach ($semstats[$secondlevelcopy->semester][$secondndlevel->txt]["veranstids"] as $veranstid) {
                    $exists = $DB->record_exists("course", ["idnumber" => $veranstid]) ? 1 : 0;
                    $semstats[$secondlevelcopy->semester][$secondndlevel->txt]["imported"] += $exists;
                }
            }
            $transformedstats = transform_fbs($semstats);
        }
    }
}

// Write CSV.
echo "Semester;Kategorie;AnzahlGesamt;AnzahlImportiert\n";
foreach ($transformedstats as $sem => $stats) {
    foreach ($stats as $cat => $catstats) {
        if (!empty($catstats)) {
               echo $sem . ";" . $cat . ";" . $catstats["existing"] . ";" . $catstats["imported"] . "\n";
        }
    }
    flush();
}

echo "</pre></p>";

$pgdb->dispose();
echo "<p>Verbindung geschlossen: " . (($pgdb->connection == null) ? "ja" : "nein") . "</p>";

/**
 * Maps the semester statistics to the university faculties.
 * @param array $semesterstats
 * @return array
 */
function transform_fbs(array $semesterstats) {
       $transformedstats = [];
    foreach ($semesterstats as $sem => $stats) {
        $transformedstats[$sem] = [];
        foreach ($stats as $cat => $catstats) {
            switch ($cat) {
                case 'Evangelische Theologie':
                    $transformedstats[$sem][FB1] = $catstats;
                    break;
                case 'Katholische Theologie':
                    $transformedstats[$sem][FB2] = $catstats;
                    break;
                case "Rechtswissenschaften":
                    $transformedstats[$sem][FB3] = $catstats;
                    break;
                case "Wirtschaftswissenschaften":
                    $transformedstats[$sem][FB4] = $catstats;
                    break;
                case "Medizin":
                    $transformedstats[$sem][FB5] = $catstats;
                    break;
                case "Sonderpädagogische Fachrichtungen":
                case "Erziehungswissenschaft und Sozialwissenschaften":
                    if (key_exists(FB6, $transformedstats[$sem])) {
                             $transformedstats[$sem][FB6] = merge_stats($transformedstats[$sem][FB6], $catstats);
                    } else {
                         $transformedstats[$sem][FB6] = $catstats;
                    }
                    break;
                case "Psychologie und Sportwissenschaft":
                    $transformedstats[$sem][FB7] = $catstats;
                    break;
                case "Geschichte/Philosophie":
                    $transformedstats[$sem][FB8] = $catstats;
                    break;
                case "Deutsch für Schülerinnen und Schüler mit Zuwanderungsgeschichte (DAZ)":
                case "Philologie":
                    if (key_exists(FB9, $transformedstats[$sem])) {
                         $transformedstats[$sem][FB9] = merge_stats($transformedstats[$sem][FB9], $catstats);
                    } else {
                           $transformedstats[$sem][FB9] = $catstats;
                    }
                    break;
                case "Mathematik und Informatik":
                    $transformedstats[$sem][FB10] = $catstats;
                    break;
                case "Physik":
                    $transformedstats[$sem][FB11] = $catstats;
                    break;
                case "Chemie und Pharmazie":
                    $transformedstats[$sem][FB12] = $catstats;
                    break;
                case "Biologie":
                    $transformedstats[$sem][FB13] = $catstats;
                    break;
                case "Geowissenschaften":
                    $transformedstats[$sem][FB14] = $catstats;
                    break;
                case "Musikhochschule":
                    $transformedstats[$sem][FB15] = $catstats;
                    break;
                case "Lehrveranstaltungen":
                    break;
                default:
                    if (!empty($transformedstats[$sem]) && key_exists(FUV, $transformedstats[$sem])) {
                          $transformedstats[$sem][FUV] = merge_stats($transformedstats[$sem][FUV], $catstats);
                    } else {
                        $transformedstats[$sem][FUV] = $catstats;
                    }
                    break;
            }
        }
            ksort($transformedstats[$sem]);
    }
       return $transformedstats;
}

/**
 * Merges two statistic arrays.
 * @param array $catstats
 * @param array $catstatsadd
 * @return array
 */
function merge_stats(array $catstats, array $catstatsadd) {
    $output = [];
    foreach ($catstats as $key => $stats) {
        if (!is_array($stats)) {
            $output[$key] = $catstats[$key] + $catstatsadd[$key];
        } else {
            $output[$key] = array_merge($stats, $catstatsadd[$key]);
        }
    }
    return $output;
}
