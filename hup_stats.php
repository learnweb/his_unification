<?php
include("../../config.php");
include("./class_pg_lite.php");
include("./lib.php");
include("./lib_features.php");

// Konstanten für Fachbereiche
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

/// Check permissions.
require_login();
if (!has_capability('moodle/site:config', context_system::instance())) {
    die("no access");
}

$reqSem         = optional_param('semester', null, PARAM_INT);       // his category origin id

set_time_limit(30*60);


function create_aggregate() {
    global $pgDB;
    pg_query($pgDB->connection, "DROP AGGREGATE textcat_all(text);");
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
        $hup_stats_sem_table = array();
        $q_main = pg_query($pgDB->connection, "SELECT ueid, semester FROM ". HIS_UEBERSCHRIFT);
        while ($hislsf_title = pg_fetch_object($q_main)) {
            $hup_stats_sem_table[$hislsf_title->ueid] = $hislsf_title->semester;
        }
    }
    return isset($hup_stats_sem_table[$ueid])?$hup_stats_sem_table[$ueid]:null;
}

function get_cat_veranstids_and_count($ueids) {
    global $pgDB;
    $hup_stats_veranstcount_table = [];
    if(!empty($ueids)){
       $q_main = pg_query($pgDB->connection, "SELECT textcat_all(DISTINCT ".HIS_UEBERSCHRIFT.".veranstid || ',') as veranstids, COUNT(DISTINCT ".HIS_UEBERSCHRIFT.".veranstid) as c FROM ".HIS_UEBERSCHRIFT." JOIN ".HIS_VERANSTALTUNG." on ".HIS_UEBERSCHRIFT.".veranstid = ".HIS_VERANSTALTUNG.".veranstid WHERE ueid IN (".$ueids.")");
    	while ($hislsf_title = pg_fetch_object($q_main)) {
               $hup_stats_veranstcount_table = ["veranstids" => explode(",",
                $hislsf_title->veranstids), "count" => $hislsf_title->c];
    	}
    }
    return $hup_stats_veranstcount_table;
}


$pgDB = new pg_lite();
echo "<p>Verbindung: ".($pgDB->connect()?"ja":"nein")."</p>";
create_aggregate();

echo "<p><pre>";
// Root-Knoten herausfinden
$toplevel_origins = get_his_toplevel_originids();
//echo "TOPLEVEL_IDs = ".print_r($toplevel_origins,true)."\n\n";
// Kategorien herausfinden
$secondlevel_orinins = get_newest_sublevels(implode(", ", $toplevel_origins));
foreach ($secondlevel_orinins as $secondndlevel) {
    // Kategoriekopien herausfinden
    $secondndlevel->copies = $DB->get_records("local_lsf_category",array("origin" => $secondndlevel->origin),null,"ueid");
    foreach($secondndlevel->copies as $secondlevel_copy) {
        // Semester bestimmen
        $secondlevel_copy->semester = get_cat_sem($secondlevel_copy->ueid);
        if (empty($reqSem) || ($reqSem == $secondlevel_copy->semester)) {
            // Alle Unterkategorien der jeweiligen Kategoriekopien sammeln
            $secondlevel_copy->subs = array_keys($DB->get_records("local_lsf_categoryparenthood",array("parent" => $secondlevel_copy->ueid),null,"child"));
            // Bestimme die Veranstatlungstypen und Anazahlen der jeweiligen Kategoriekopien
           $secondlevel_copy->veranstcount = get_cat_veranstids_and_count(implode(",",$secondlevel_copy->subs));
           // Semesterarray erstellen (daten umformatieren)
            if (!isset($sem_stats[$secondlevel_copy->semester])) {
                $sem_stats[$secondlevel_copy->semester] = array();
            }
            if (!isset($sem_stats[$secondlevel_copy->semester][$secondndlevel->txt])) {
                $sem_stats[$secondlevel_copy->semester][$secondndlevel->txt] = array("imported" => 0 ,"existing" => 0, "veranstids" => array());
           }
           if(!empty($secondlevel_copy->veranstcount) && key_exists("veranstids",
                    $secondlevel_copy->veranstcount) &&key_exists("count",
                   $secondlevel_copy->veranstcount)) {
               $sem_stats[$secondlevel_copy->semester][$secondndlevel->txt]["veranstids"] = array_filter(array_merge($sem_stats[$secondlevel_copy->semester][$secondndlevel->txt]["veranstids"],$secondlevel_copy->veranstcount["veranstids"]));
               $sem_stats[$secondlevel_copy->semester][$secondndlevel->txt]["existing"] += $secondlevel_copy->veranstcount["count"];
               // zaehle bestehende kurse
               foreach ($sem_stats[$secondlevel_copy->semester][$secondndlevel->txt]["veranstids"] as $veranstid) {
                       $sem_stats[$secondlevel_copy->semester][$secondndlevel->txt]["imported"] += $DB->record_exists("course", array("idnumber" => $veranstid))?1:0;
               }
           }
            $transformedstats = transform_fbs($sem_stats);
        }
    }
}
//echo "2ndLEVEL_IDs = ".print_r($secondlevel_orinins,true)."\n\n";
//echo "SEM_STATSs = ".print_r($sem_stats,true)."\n\n";

// write CSV
echo "Semester;Kategorie;AnzahlGesamt;AnzahlImportiert\n";
foreach ($transformedstats as $sem => $stats) {
    foreach ($stats as $cat => $catstats) {
        if (!empty($catstats)) {
               echo $sem.";".$cat.";".$catstats["existing"].";".$catstats["imported"]."\n";
        }
    }
    flush();
}

echo "</pre></p>";

$pgDB->dispose();
echo "<p>Verbindung geschlossen: ".(($pgDB->connection==NULL)?"ja":"nein")."</p>";

function transform_fbs($semesterstats){
       $transformedstats = [];
       foreach ($semesterstats as $sem => $stats) {
           $transformedstats[$sem] = [];
           foreach ($stats as $cat => $catstats) {
               switch ($cat){
                       case 'Evangelische Theologie': $transformedstats[$sem][FB1] = $catstats; break;
                       case 'Katholische Theologie': $transformedstats[$sem][FB2] = $catstats; break;
                       case "Rechtswissenschaften": $transformedstats[$sem][FB3] = $catstats; break;
                       case "Wirtschaftswissenschaften": $transformedstats[$sem][FB4] = $catstats; break;
                       case "Medizin": $transformedstats[$sem][FB5] = $catstats; break;
                       case "Sonderpädagogische Fachrichtungen":
                       case "Erziehungswissenschaft und Sozialwissenschaften":
                           if(key_exists(FB6,$transformedstats[$sem])){
                                $transformedstats[$sem][FB6] = merge_stats($transformedstats[$sem][FB6], $catstats);
                            } else {
                                $transformedstats[$sem][FB6] = $catstats;
                            } break;
                       case "Psychologie und Sportwissenschaft": $transformedstats[$sem][FB7] = $catstats; break;
                       case "Geschichte/Philosophie": $transformedstats[$sem][FB8] = $catstats; break;
                       case "Deutsch für Schülerinnen und Schüler mit Zuwanderungsgeschichte (DAZ)":
                       case "Philologie":
                           if(key_exists(FB9,$transformedstats[$sem])){
                                $transformedstats[$sem][FB9] = merge_stats($transformedstats[$sem][FB9], $catstats);
                            } else {
                                $transformedstats[$sem][FB9] = $catstats;
                            } break;
                       case "Mathematik und Informatik": $transformedstats[$sem][FB10] = $catstats; break;
                       case "Physik": $transformedstats[$sem][FB11] = $catstats; break;
                       case "Chemie und Pharmazie": $transformedstats[$sem][FB12] = $catstats; break;
                       case "Biologie": $transformedstats[$sem][FB13] = $catstats; break;
                       case "Geowissenschaften": $transformedstats[$sem][FB14] = $catstats; break;
                       case "Musikhochschule": $transformedstats[$sem][FB15] = $catstats; break;
                       case "Lehrveranstaltungen":break;
                       default:
                           if(!empty($transformedstats[$sem]) && key_exists(FUV,$transformedstats[$sem])){
                               $transformedstats[$sem][FUV] = merge_stats($transformedstats[$sem][FUV], $catstats);
                           } else {
                               $transformedstats[$sem][FUV] = $catstats;
                           } break;
                       }
               }
               ksort($transformedstats[$sem]);
       }
       return $transformedstats;
}

function merge_stats($catstats, $catstats_add){
    $output = [];
    foreach ($catstats as $key => $stats){
        if(!is_array($stats)) {
            $output[$key] = $catstats[$key] + $catstats_add[$key];
        } else {
            $output[$key] = array_merge($stats, $catstats_add[$key]);
        }
    }
    return $output;
}
