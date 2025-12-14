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
 * Functions that are specific to HIS database, format and helptables containing his-formatted data
 *
 * @package   local_lsf_unification
 * @copyright 2025 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_lsf_unification\pg_lite;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/local/lsf_unification/class_pg_lite.php');

define("HIS_PERSONAL", "public.learnweb_personal");
define("HIS_VERANSTALTUNG", "public.learnweb_veranstaltung");
define("HIS_PERSONAL_VERANST", "public.learnweb_personal_veranst");
define("HIS_UEBERSCHRIFT", "public.learnweb_ueberschrift");
define("HIS_STDP", "public.learnweb_stdp");
define("HIS_VERANST_KOMMENTAR", "public.learnweb_veranst_kommentar");

/**
 * establish_secondary_DB_connection is a required function for the lsf_unification plugin
 * @package local_lsf_unification
 */
function establish_secondary_db_connection() {
    global $pgdb;
    if (!empty($pgdb) && !empty($pgdb->connection)) {
        return;
    }
    $pgdb = new pg_lite();
    if (!($pgdb->connect() === true)) {
        return false;
    }
    return true;
}

/**
 * close_secondary_DB_connection is a required function for the lsf_unification plugin
 * @package local_lsf_unification
 */
function close_secondary_db_connection() {
    global $pgdb;
    if (empty($pgdb) || empty($pgdb->connection)) {
        return;
    }
    $pgdb->dispose();
}

/**
 * Setup function for soap.
 *
 * @return bool
 * @throws dml_exception
 */
function setuphissoap() {
    global $CFG, $hislsfsoapclient;
    if (!get_config('local_lsf_unification', 'his_deeplink_via_soap')) {
        return false;
    }
    if (empty($hislsfsoapclient)) {
        try {
            $hislsfsoapclient = new SoapClient(get_config('local_lsf_unification', 'soapwsdl'));
            $result = $hislsfsoapclient->auth(
                get_config('local_lsf_unification', 'soapuser'),
                get_config('local_lsf_unification', 'soappass')
            );
            $hismoodleurl = get_config('local_lsf_unification', 'moodle_url');
            $result = $result &&
                     $hislsfsoapclient->configureMoodleWKZ(
                         $hismoodleurl . "/course/view.php?id=MOODLEID"
                     );
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    return true;
}

/**
 * Set his link in soap client..
 *
 * @param $veranstid
 * @param $mdlid
 * @return bool
 * @throws dml_exception
 */
function sethislink($veranstid, $mdlid) {
    global $hislsfsoapclient;
    if (!setupHisSoap()) {
        return false;
    }
    $hislsfsoapclient->setMoodleLink($veranstid, $mdlid);
    return true;
}

/**
 * Remove his link in soap client.
 *
 * @param $veranstid
 * @return bool
 * @throws dml_exception
 */
function removehislink($veranstid) {
    global $hislsfsoapclient;
    if (!setupHisSoap()) {
        return false;
    }
    $hislsfsoapclient->removeMoodleLink($veranstid);
    return true;
}

/**
 * Gets all terminids for a given mtknr
 *
 * @param $mtknr
 * @return array
 */
function get_students_stdp_terminids($mtknr) {
    global $pgdb;
    establish_secondary_DB_connection();
    $q = pg_query(
        $pgdb->connection,
        "SELECT terminid FROM " . HIS_STDP .
        " WHERE mtknr = $mtknr and terminid is not null group by terminid order by terminid;"
    );
    $return = [];
    while ($terminid = pg_fetch_object($q)) {
        array_push($return, $terminid->terminid);
    }
    close_secondary_DB_connection();
    return $return;
}

/**
 * get_teachers_pid returns the pid (personen-id) connected to a specific username
 *
 * @param $username the teachers username
 * @return $pid the teachers pid (personen-id)
 * @package local_lsf_unification
 */
function get_teachers_pid($username, $checkhis = false) {
    global $pgdb;
    $emailcheck = $checkhis ? (" OR (login = '" . $username . "')") : "";
    $q = pg_query(
        $pgdb->connection,
        "SELECT pid FROM " . HIS_PERSONAL . " WHERE (zivk = '" . $username . "')" . $emailcheck
    );
    if ($hislsfteacher = pg_fetch_object($q)) {
        return $hislsfteacher->pid;
    }
    if (!$checkhis) {
        return get_teachers_pid($username, true);
    }
    return null;
}

/**
 * Returns all courses that have apply to one of the veranstids.
 *
 * @param $veranstids
 * @return array
 * @throws dml_exception
 */
function get_courses_by_veranstids($veranstids) {
    global $pgdb;

    // If veranstids is empty, no need to make a db request. return empty list.
    if (empty($veranstids)) {
        return [];
    }

    $veranstidsstring = implode(',', $veranstids);
    $maxage = get_config('local_lsf_unification', 'max_import_age');

    $sql = "
        SELECT
          veranstid,
          veranstnr,
          semester,
          semestertxt,
          veranstaltungsart,
          titel,
          urlveranst
        FROM " . HIS_VERANSTALTUNG . " as veranst
        WHERE
          veranstid in ($veranstidsstring)
          AND (CURRENT_DATE - CAST(veranst.zeitstempel AS date)) < $maxage
        ORDER BY semester, titel;";

    $q = pg_query($pgdb->connection, $sql);
    $resultlist = [];
    while ($course = pg_fetch_object($q)) {
        $result = new stdClass();
        $result->veranstid = $course->veranstid;
        $result->veranstnr = $course->veranstnr;
        $result->semester = $course->semester;
        $result->semestertxt = $course->semestertxt;
        $result->veranstaltungsart = $course->veranstaltungsart;
        $result->titel = $course->titel;
        $result->urlveranst = $course->urlveranst;
        $resultlist[$course->veranstid] = $result;
    }
    return $resultlist;
}

/**
 * Get course that applies to single veranstid.
 * @param $veranstid
 * @return mixed
 * @throws dml_exception
 */
function get_course_by_veranstid($veranstid) {
    $result = get_courses_by_veranstids([$veranstid,
    ]);
    return $result[$veranstid];
}

/**
 * Get all veranstids from a teacher.
 * @param $pid
 * @return array
 */
function get_veranstids_by_teacher($pid) {
    global $pgdb;
    $q = pg_query(
        $pgdb->connection,
        "SELECT veranstid FROM " . HIS_PERSONAL_VERANST .
        " WHERE pid = $pid and veranstid is not null group by veranstid order by veranstid;"
    );
    $return = [];
    while ($veranstid = pg_fetch_object($q)) {
        array_push($return, $veranstid->veranstid);
    }
    return $return;
}

/**
 * Get the uni muenster mail from a user.
 * @param $username
 * @return string
 */
function username_to_mail($username) {
    return $username . "@uni-muenster.de";
}

/**
 * Creates a list of courses assigned to a teacher
 * get_teachers_course_list is a required function for the lsf_unification plugin
 *
 * @param $username the teachers username
 * @param $longinfo level of detail
 * @param $checkmail not intended for manual setting, just for recursion
 * @return $courselist an array containing objects consisting of veranstid and info
 * @package local_lsf_unification
 */
function get_teachers_course_list($username, $longinfo = false) {
    global $pgdb;
    $courselist = [];
    $pid = get_teachers_pid($username);
    if (empty($pid)) {
        return $courselist;
    }
    $veranstids = get_veranstids_by_teacher($pid);
    $courses = get_courses_by_veranstids($veranstids);
    foreach ($courses as $veranstid => $course) {
        $result = new stdClass();
        $result->veranstid = $course->veranstid;
        $result->info = mb_convert_encoding($course->titel, 'UTF-8', 'ISO-8859-1') .
                 ($longinfo ? ("&nbsp;&nbsp;(" . $course->semestertxt .
                 ((!empty($course->urlveranst)) ? (", <a href='" . $course->urlveranst .
                 "'> KVV-Nr. " . $course->veranstnr . "</a>") : "") . ")") : "");
        $courselist[$course->veranstid] = $result;
    }
    return $courselist;
}

/**
 * Returns true if a idnumber/veranstid assigned to a specific teacher
 * is_veranstid_valid is a required function for the lsf_unification plugin
 *
 * @param $veranstid idnumber/veranstid
 * @param $username the teachers username
 * @return $is_valid
 * @package local_lsf_unification
 */
function is_course_of_teacher($veranstid, $username) {
    $courses = get_teachers_course_list($username, false, true);
    return !empty($courses[$veranstid]);
}

/**
 * Find_origin_category is NOT a required function for the lsf_unification plugin, it is used
 * internally only
 *
 * @param $quellid
 * @return $origin
 * @package local_lsf_unification
 */
function find_origin_category($quellid) {
    global $pgdb;
    $origin = $quellid;
    do {
        $quellid = $origin;
        $q = pg_query(
            $pgdb->connection,
            "SELECT quellid FROM " . HIS_UEBERSCHRIFT . " WHERE ueid = '" . $quellid . "'"
        );
        if ($hislsftitle = pg_fetch_object($q)) {
            $q2 = pg_query(
                $pgdb->connection,
                "SELECT quellid FROM " . HIS_UEBERSCHRIFT . " WHERE ueid = '" .
                ($hislsftitle->quellid) . "'"
            );
            if ($hislsftitle2 = pg_fetch_object($q2)) {
                $origin = $hislsftitle->quellid;
            }
        }
    } while (!empty($origin) && $quellid != $origin);
    return $origin;
}

/**
 * get_teachers_of_course returns the teacher objects of a course sorted by their relevance
 *
 * @param $veranstid idnumber/veranstid
 * @return $sortedresult sorted array of teacher objects
 * @package local_lsf_unification
 */
function get_teachers_of_course($veranstid) {
    global $pgdb;
    // Get sorted (by relevance) pids of teachers.
    $pidstring = "";
    $pids = [];
    $q1 = pg_query(
        $pgdb->connection,
        "SELECT DISTINCT pid, sort FROM " . HIS_PERSONAL_VERANST . " WHERE veranstid = " .
        $veranstid . " ORDER BY sort ASC"
    );
    while ($person = pg_fetch_object($q1)) {
        $pidstring .= (empty($pidstring) ? "" : ",") . $person->pid;
        $pids[] = $person->pid;
    }
    if (empty($pids)) {
        return [];
    }
    // Get personal info.
    $result = [];
    $q2 = pg_query(
        $pgdb->connection,
        "SELECT vorname, nachname, zivk, login, pid FROM " . HIS_PERSONAL . " WHERE pid IN (" .
        $pidstring . ")"
    );
    while ($person = pg_fetch_object($q2)) {
        $result[$person->pid] = $person;
    }
    // Sort by relevance.
    $sortedresult = [];
    foreach ($pids as $pid) {
        $sortedresult[] = $result[$pid];
    }
    return $sortedresult;
}

/**
 * returns the default fullname according to a given veranstid
 * get_default_fullname is a required function for the lsf_unification plugin
 *
 * @param $veranstid idnumber/veranstid
 * @return $fullname
 * @package local_lsf_unification
 */
function get_default_fullname($lsfcourse) {
    $personen = "";
    foreach (get_teachers_of_course($lsfcourse->veranstid) as $person) {
        $personen .= ", " . trim($person->vorname) . " " . trim($person->nachname);
    }
    return mb_convert_encoding(($lsfcourse->titel) . " " . trim($lsfcourse->semestertxt) . $personen, 'UTF-8', 'ISO-8859-1');
}

/**
 * returns the default shortname according to a given veranstid
 * get_default_shortname is a required function for the lsf_unification plugin
 *
 * @param $veranstid idnumber/veranstid
 * @return $shortname
 * @package local_lsf_unification
 */
function get_default_shortname($lsfcourse, $long = false) {
    global $DB;
    $i = "";
    foreach (explode(" ", $lsfcourse->titel) as $word) {
        $i .= strtoupper($word[0]) . (($long && !empty($word[1])) ? $word[1] : "");
    }
    $str = $i . "-" . substr($lsfcourse->semester, 0, 4) . "_" . substr($lsfcourse->semester, -1);
    $name = mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
    if (
        !$long && $DB->record_exists('course', ['shortname' => $name,
        ])
    ) {
        return get_default_shortname($lsfcourse, true);
    }
    return $name;
}

/**
 * returns the default summary according to a given veranstid
 * get_default_summary is a required function for the lsf_unification plugin
 *
 * @param $veranstid idnumber/veranstid
 * @return $summary
 * @package local_lsf_unification
 */
function get_default_summary($lsfcourse) {
    global $pgdb;
    $summary = '';
    $q = pg_query(
        $pgdb->connection,
        "SELECT kommentar FROM " . HIS_VERANST_KOMMENTAR . " WHERE veranstid = '" .
        $lsfcourse->veranstid . "'"
    );
    while ($sumobject = pg_fetch_object($q)) {
        if (!empty($sumobject->kommentar) && strpos($summary, $sumobject->kommentar) === false) {
            $summary .= '<p>' . $sumobject->kommentar . '</p>';
        }
    }
    $summary = mb_convert_encoding($summary, 'UTF-8', 'ISO-8859-1') . '<p><a href="' . $lsfcourse->urlveranst .
             '">Kurs im HIS-LSF</a></p>';
    return $summary;
}

/**
 * returns the default startdate according to a given veranstid
 * get_default_startdate is a required function for the lsf_unification plugin
 *
 * @param $veranstid idnumber/veranstid
 * @return $startdate
 * @package local_lsf_unification
 */
function get_default_startdate($lsfcourse) {
    $semester = $lsfcourse->semester . '';
    $year = substr($semester, 0, 4);
    $month = (substr($semester, -1) == "1") ? 4 : 10;
    return mktime(0, 0, 0, $month, 1, $year);
}

/**
 * returns if a course is already imported
 * course_exists is a required function for the lsf_unification plugin
 *
 * @param $veranstid idnumber/veranstid
 * @return $is_course_existing
 * @package local_lsf_unification
 */
function course_exists($veranstid) {
    global $DB;
    if (
        $DB->record_exists("local_lsf_course", ["veranstid" => ($veranstid)]) &&
             !($DB->record_exists("local_lsf_course", ["veranstid" => ($veranstid), "mdlid" => 0]) ||
            $DB->record_exists("local_lsf_course", ["veranstid" => ($veranstid), "mdlid" => 1]))
    ) {
        if (!$DB->record_exists("course", ["idnumber" => ($veranstid)])) {
            $DB->delete_records("local_lsf_course", ["veranstid" => ($veranstid)]);
        } else {
            return true;
        }
    } else if ($DB->record_exists("course", ["idnumber" => ($veranstid)])) {
        return true;
    }
    return false;
}

/**
 * returns if a shortname is valid
 * is_shortname_valid is a required function for the lsf_unification plugin
 *
 * @param $veranstid idnumber/veranstid
 * @param $shortname shortname
 * @return $is_shortname_valid
 * @package local_lsf_unification
 */
function is_shortname_valid($lsfcourse, $shortname) {
    $string = get_default_shortname_ending($lsfcourse);
    return (substr($shortname, -strlen($string)) == $string);
}

/**
 * Return short name of a course.
 * @param $lsfcourse
 * @return string
 */
function get_default_shortname_ending($lsfcourse) {
    return "-" . substr($lsfcourse->semester, 0, 4) . "_" . substr($lsfcourse->semester, -1);
}

/**
 * returns if a shortname hint, if it is invalid
 * shortname_hint is a required function for the lsf_unification plugin
 *
 * @param $veranstid idnumber/veranstid
 * @return $hint
 * @package local_lsf_unification
 */
function shortname_hint($lsfcourse) {
    $string = "-" . substr($lsfcourse->semester, 0, 4) . "_" . substr($lsfcourse->semester, -1);
    return $string;
}

/**
 * enroles teachers to a freshly created course
 * enrole_teachers is a required function for the lsf_unification plugin
 *
 * @param $veranstid idnumber/veranstid
 * @param $courseid id of moodle course
 * @return $warnings
 * @package local_lsf_unification
 */
function enrole_teachers($veranstid, $courseid) {
    global $DB, $CFG;
    $warnings = "";
    foreach (get_teachers_of_course($veranstid) as $lsfuser) {
        unset($teacher);
        if (!empty($lsfuser->zivk)) {
            $teacher = $DB->get_record("user", ["username" => $lsfuser->zivk]);
        }
        // If user cannot be found by zivk try to find user by login that is manually set in his.
        if (empty($teacher) && !empty($lsfuser->login)) {
            $teacher = $DB->get_record("user", ["username" => $lsfuser->login]);
        }
        if (
            empty($teacher) ||
                 !enrol_try_internal_enrol(
                     $courseid,
                     $teacher->id,
                     get_config('local_lsf_unification', 'roleid_teacher')
                 )
        ) {
            $warnings = $warnings . "\n" .
             get_string('warning_cannot_enrol_other', 'local_lsf_unification') . " (" .
             $lsfuser->zivk . ", " . $lsfuser->login . " " . $lsfuser->vorname . " " .
             $lsfuser->nachname . ")";
        }
    }
    return $warnings;
}

/**
 * sets timestamp for course-import
 * set_course_created is a required function for the lsf_unification plugin
 *
 * @param $veranstid idnumber/veranstid
 * @param $courseid id of moodle course
 * @package local_lsf_unification
 */
function set_course_created($veranstid, $courseid) {
    global $DB;
    if ($courseentry = $DB->get_record("local_lsf_course", ["veranstid" => $veranstid])) {
        $courseentry->mdlid = $courseid;
        $courseentry->timestamp = time();
        $DB->update_record('local_lsf_course', $courseentry);
    } else {
        $courseentry = new stdClass();
        $courseentry->veranstid = $veranstid;
        $courseentry->mdlid = $courseid;
        $courseentry->timestamp = time();
        $DB->insert_record("local_lsf_course", $courseentry);
    }
}

/**
 * Get record from moodle_db.
 * LEARNWEB-TODO: this does not save lines, use the DB query directly instead of this function.
 * @param $rid
 * @return false|mixed|stdClass
 * @throws dml_exception
 */
function get_course_request($rid) {
    global $DB;
    return $DB->get_record("local_lsf_course", ["id" => $rid, "mdlid" => 0]);
}

/**
 * Get record from moodle_db.
 * LEARNWEB-TODO: this does not save lines, use the DB query directly instead of this function.
 * @return array
 * @throws dml_exception
 */
function get_course_requests() {
    global $DB;
    return $DB->get_records("local_lsf_course", ["mdlid" => 0], "id");
}

/**
 * Update a course record and set it as requested.
 * @param $veranstid
 * @return bool|int|null
 * @throws dml_exception
 */
function set_course_requested($veranstid) {
    global $DB, $USER;
    if ($courseentry = $DB->get_record("local_lsf_course", ["veranstid" => $veranstid])) {
        return null;
    } else {
        $courseentry = new stdClass();
        $courseentry->veranstid = $veranstid;
        $courseentry->mdlid = 0;
        $courseentry->requeststate = 1;
        $courseentry->timestamp = time();
        $courseentry->requesterid = $USER->id;
        return $DB->insert_record("local_lsf_course", $courseentry);
    }
}

/**
 * Set a course as accepted from an authorized user.
 * @param $veranstid
 * @return void
 * @throws dml_exception
 */
function set_course_accepted($veranstid) {
    global $DB, $USER;
    if ($courseentry = $DB->get_record("local_lsf_course", ["veranstid" => $veranstid])) {
        $courseentry->requeststate = 2;
        $courseentry->timestamp = time();
        $courseentry->acceptorid = $USER->id;
        $DB->update_record('local_lsf_course', $courseentry);
        return $courseentry->id;
    }
}

/**
 * Set a course as declined from an authorized user.
 * @param $veranstid
 * @return void
 * @throws dml_exception
 */
function set_course_declined($veranstid) {
    global $DB, $USER;
    if ($courseentry = $DB->get_record("local_lsf_course", ["veranstid" => $veranstid])) {
        $DB->delete_records("local_lsf_course", ["veranstid" => ($veranstid)]);
    }
}

/**
 * returns mapped categories for a specified course
 * get_courses_categories is a required function for the lsf_unification plugin
 *
 * @param $veranstid idnumber/veranstid
 * @return $courselist
 * @package local_lsf_unification
 */
function get_courses_categories($veranstid, $updatehelptablesifnecessary = true) {
    global $pgdb, $DB, $CFG;
    $helpfuntion1 = function ($arrayel) {
        return $arrayel->origin;
    };
    $helpfuntion2 = function ($arrayel) {
        return $arrayel->name;
    };
    $helpfuntion3 = function ($arrayel) {
        return $arrayel->mdlid;
    };
    $q = pg_query(
        $pgdb->connection,
        "SELECT ueid FROM " . HIS_UEBERSCHRIFT . " WHERE veranstid=" . $veranstid . ""
    );
    $choices = [];
    $categories = [];
    while ($hislsftitle = pg_fetch_object($q)) {
        $ueids = (empty($ueids) ? "" : ($ueids . ", ")) . ("" . $hislsftitle->ueid . "");
    }
    $otherueidssql = "SELECT parent FROM " . $CFG->prefix .
             "local_lsf_categoryparenthood WHERE child in (" . $ueids . ")";
    $originssql = "SELECT origin FROM " . $CFG->prefix . "local_lsf_category WHERE ueid in (" .
             $otherueidssql . ") OR ueid in (" . $ueids . ")";
    $origins = implode(", ", array_map($helpfuntion1, $DB->get_records_sql($originssql)));
    if (!empty($origins)) {
        $categoriessql = "SELECT mdlid, name FROM (" . $CFG->prefix . "local_lsf_category JOIN " .
                 $CFG->prefix . "course_categories ON " . $CFG->prefix .
                 "local_lsf_category.mdlid = " . $CFG->prefix .
                 "course_categories.id) WHERE ueid in (" . $origins . ") ORDER BY sortorder";
        if (get_config('local_lsf_unification', 'subcategories')) {
            $maincourses = implode(
                ", ",
                array_map($helpfuntion3, $DB->get_records_sql($categoriessql))
            );
            if (empty($maincourses)) {
                $maincourses = get_config('local_lsf_unification', 'defaultcategory');
            }
            $categoriessqlmain = "SELECT id, name FROM " . $CFG->prefix .
                     "course_categories WHERE id in (" . $maincourses . ") ORDER BY sortorder";
            $categories = array_map($helpfuntion2, $DB->get_records_sql($categoriessqlmain));
            $categoriessqlchild = "SELECT id, name FROM " . $CFG->prefix .
                "course_categories WHERE parent in (" . $maincourses . ") ORDER BY sortorder";
            $categorieschild = $DB->get_records_sql($categoriessqlchild);
            $categories = $categories + array_map($helpfuntion2, $categorieschild);
            foreach ($categorieschild as $child) {
                if (!str_contains($child->name, 'Archiv')) {
                    $categoriessqliterative = "SELECT id, name FROM " . $CFG->prefix .
                        "course_categories WHERE path like '%" . $child->id . "/%' ORDER BY sortorder";
                    $categories = array_map($helpfuntion2, $DB->get_records_sql($categoriessqliterative)) + $categories;
                }
            }
            return $categories;
        }
        $categories = array_map($helpfuntion2, $DB->get_records_sql($categoriessql));
    }
    if ($updatehelptablesifnecessary && (count($categories) == 0)) {
        insert_missing_helptable_entries(false);
        return get_courses_categories($veranstid, false);
    }
    return $categories;
}

/**
 * updates the helptables
 * insert_missing_helptable_entries is a required function for the lsf_unification plugin
 *
 * @param $veranstid idnumber/veranstid
 * @return $courselist
 * @package local_lsf_unification
 */
function insert_missing_helptable_entries($debugoutput = false, $tryeverything = false) {
    $a = 1;
    global $pgdb, $DB;
    $list1 = "";
    $list2 = "";
    $records1 = $DB->get_recordset('local_lsf_category', null, '', 'ueid');
    $records2 = $DB->get_recordset('local_lsf_categoryparenthood', null, '', 'child, parent');
    $records1unique = [];
    $records2unique = [];
    foreach ($records1 as $record1) {
        $records1unique[$record1->ueid] = true;
    }
    foreach ($records2 as $record2) {
        $records2unique[$record2->child][$record2->parent] = ($tryeverything === false);
    }

    $qmain = pg_query(
        $pgdb->connection,
        "SELECT ueid, uebergeord, uebergeord, quellid, txt, zeitstempel FROM " . HIS_UEBERSCHRIFT .
                     " " .
        ((!empty($tryeverything)) ? ("WHERE ueid >= '" . $tryeverything . "'") : "")
    );
    while ($hislsftitle = pg_fetch_object($qmain)) {
        if (
            !isset($records1unique[$hislsftitle->ueid]) || (!isset(
                $records2unique[$hislsftitle->ueid][$hislsftitle->uebergeord]
            ) ||
                 $records2unique[$hislsftitle->ueid][$hislsftitle->uebergeord] != true)
        ) {
            $a++;
            echo $hislsftitle->ueid . " ";
        }
        if (!isset($records1unique[$hislsftitle->ueid])) {
            // Create match-table-entry if not existing.
            $entry = new stdClass();
            $entry->ueid = $hislsftitle->ueid;
            $entry->parent = empty($hislsftitle->uebergeord) ? ($hislsftitle->ueid) : ($hislsftitle->uebergeord);
            $entry->origin = find_origin_category($hislsftitle->ueid);
            $entry->mdlid = 0;
            $entry->timestamp = strtotime($hislsftitle->zeitstempel);
            $entry->txt = mb_convert_encoding($hislsftitle->txt, 'UTF-8', 'ISO-8859-1');
            if ($debugoutput) {
                echo "!";
            }
            try {
                $DB->insert_record("local_lsf_category", $entry, true);
                $records1unique[$hislsftitle->ueid] = true;
                if ($debugoutput) {
                    echo "x";
                }
            } catch (Exception $e) {
                try {
                    $entry->txt = mb_convert_encoding(delete_bad_chars($hislsftitle->txt), 'UTF-8', 'ISO-8859-1');
                    $DB->insert_record("local_lsf_category", $entry, true);
                    $records1unique[$hislsftitle->ueid] = true;
                    if ($debugoutput) {
                        echo "x";
                    }
                } catch (Exception $e) {
                    if ($debugoutput) {
                        print("<pre>FEHLER1 " . var_export($e, true) . "" . var_export($DB->get_last_error(), true));
                    }
                }
            }
        }
        if (
            !isset($records2unique[$hislsftitle->ueid][$hislsftitle->uebergeord]) ||
                 $records2unique[$hislsftitle->ueid][$hislsftitle->uebergeord] != true
        ) {
            // Create parenthood-table-entry if not existing.
            $child = $hislsftitle->ueid;
            $ueid = $hislsftitle->ueid;
            $parent = $hislsftitle->ueid;
            $fullname = "";
            $distance = 0;
            do {
                $ueid = $parent;
                $distance++;
                $q2 = pg_query(
                    $pgdb->connection,
                    "SELECT ueid, uebergeord, txt FROM " . HIS_UEBERSCHRIFT . " WHERE ueid = '" .
                    $ueid . "'"
                );
                if (($hislsftitle2 = pg_fetch_object($q2)) && ($hislsftitle2->uebergeord != $ueid)) {
                    $parent = $hislsftitle2->uebergeord;
                    $fullname = ($hislsftitle2->txt) . (empty($fullname) ? "" : ("/" . $fullname));
                    if (!empty($parent) && !isset($records2unique[$child][$parent])) {
                        try {
                            $entry = new stdClass();
                            $entry->child = $child;
                            $entry->parent = $parent;
                            $entry->distance = $distance;
                            $DB->insert_record("local_lsf_categoryparenthood", $entry, true);
                            if ($debugoutput) {
                                echo "?";
                            }
                        } catch (Exception $e) {
                            if ($debugoutput) {
                                mtrace(
                                    "<pre>FEHLER2 " . var_export($e, true) . "" .
                                    var_export($DB->get_last_error(), true),
                                    ''
                                );
                            }
                        }
                    }
                    $records2unique[$child][$parent] = true;
                }
            } while (!empty($parent) && ($ueid != $parent));
            $entry = $DB->get_record(
                'local_lsf_category',
                ["ueid" => $hislsftitle->ueid,
                ]
            );
            $entry->txt2 = mb_convert_encoding($fullname, 'UTF-8', 'ISO-8859-1');
            try {
                $DB->update_record('local_lsf_category', $entry, true);
            } catch (Exception $e) {
                try {
                    $entry->txt2 = delete_bad_chars($entry->txt2);
                    $DB->update_record('local_lsf_category', $entry, true);
                } catch (Exception $e) {
                    if ($debugoutput) {
                        mtrace(
                            "<pre>FEHLER2 " . var_export($e, true) . "" .
                            var_export($DB->get_last_error(), true),
                            ''
                        );
                    }
                }
            }
        }
        if ($debugoutput && (($a % 101) == 0)) {
            mtrace("<br>&nbsp;&nbsp;");
            $a++;
            flush();
        }
    }
}

/**
 * delete_bad_chars is NOT a required function for the lsf_unification plugin, it is used internally
 * only
 *
 * @param $str
 * @return $str
 * @package local_lsf_unification
 */
function delete_bad_chars($str) {
    return strtr(mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1'), [
                    "\xc2\x96" => "", // EN DASH.
                    "\xc2\x97" => "", // EM DASH.
                    "\xc2\x84" => "", // DOUBLE LOW-9 QUOTATION MARK.
    ]);
}

/**
 * returns a list of (newest copies of) children to a parents (and the parent's copies)
 * @package local_lsf_unification
 */
function get_newest_sublevels($origins) {
    global $DB, $CFG;
    $helpfuntion1 = function ($arrayel) {
        return $arrayel->ueid;
    };
    // Get all copies of current category.
    $originssql = "SELECT ueid FROM " . $CFG->prefix . "local_lsf_category WHERE origin in (" .
             $origins . ")";
    $copies = implode(", ", array_map($helpfuntion1, $DB->get_records_sql($originssql)));
    // Get all their childcategories, that newest copy is not older than 2 years.
    $sublevelsallsql = "SELECT * FROM (SELECT max(ueid) as max_ueid, origin FROM " . $CFG->prefix .
             "local_lsf_category WHERE parent in (" . $copies . ") AND ueid not in (" . $origins .
             ") GROUP BY origin) AS a JOIN " . $CFG->prefix . "local_lsf_category ON a.max_ueid = " .
             $CFG->prefix . "local_lsf_category.ueid ";
    $sublevelsyoungsql = $sublevelsallsql . "WHERE " . $CFG->prefix .
             "local_lsf_category.timestamp >= (" . (time() - 2 * 365 * 24 * 60 * 60) .
             ") ORDER BY txt";
    $result = $DB->get_records_sql($sublevelsyoungsql);
    // Get all their childcategories, if there is no childcategory with a copy, that is not older than 2 years.
    if (empty($result)) {
        $result = $DB->get_records_sql($sublevelsallsql . "ORDER BY txt");
    }
    return $result;
}

/**
 * returns if a category has children
 * @package local_lsf_unification
 */
function has_sublevels($origins) {
    global $CFG, $DB;
    $sublevelssql = "SELECT id FROM " . $CFG->prefix . "local_lsf_category WHERE parent in (" .
             $origins . ") AND ueid not in (" . $origins . ")";
    return (count($DB->get_records_sql($sublevelssql)) > 0);
}

/**
 * returns the newest copy to a given id
 * @package local_lsf_unification
 */
function get_newest_element($id) {
    global $CFG, $DB;
    $origins = $DB->get_record("local_lsf_category", ["ueid" => $id,
    ], "origin")->origin;
    $sublevelssql = "SELECT max(ueid) as max_ueid, origin FROM " . $CFG->prefix .
             "local_lsf_category WHERE origin in (" . $origins . ") GROUP BY origin";
    $sublevels = $DB->get_records_sql($sublevelssql);
    $ueid = array_shift($sublevels)->max_ueid;
    return $DB->get_record("local_lsf_category", ["ueid" => $ueid,
    ]);
}

/**
 * returns the parent of the newest copy to the given id
 * @package local_lsf_unification
 */
function get_newest_parent($id) {
    global $CFG, $DB;
    $parent = get_newest_element($id)->parent;
    return $DB->get_record("local_lsf_category", ["ueid" => $parent,
    ]);
}

/**
 * returns the moodle-id given to a lsf-id
 * @package local_lsf_unification
 */
function get_mdlid($id) {
    global $CFG, $DB;
    $origin = $DB->get_record("local_lsf_category", ["ueid" => $id], "origin")->origin;
    $mdlid = $DB->get_record("local_lsf_category", ["ueid" => $origin], "mdlid")->mdlid;
    return $mdlid;
}

/**
 * returns the moodle-name given to a lsf-id
 * @package local_lsf_unification
 */
function get_mdlname($id) {
    global $CFG, $DB;
    $origin = $DB->get_record("local_lsf_category", ["ueid" => $id], "origin")->origin;
    $mdlid = $DB->get_record("local_lsf_category", ["ueid" => $origin], "mdlid")->mdlid;
    $cat = $DB->get_record("course_categories", ["id" => $mdlid], "name");
    return $cat->name;
}

/**
 * sets a category-mapping
 * @package local_lsf_unification
 */
function set_cat_mapping($ueid, $mdlid) {
    global $DB, $SITE;
    $obj = $DB->get_record("local_lsf_category", ["ueid" => $ueid]);
    $event = \local_lsf_unification\event\matchingtable_updated::create([
            'objectid' => $obj->id,
            'context' => context_system::instance(0, IGNORE_MISSING),
            'other' => ['mappingold' => $obj->mdlid, 'mappingnew' => $mdlid, 'originid' => $ueid],
    ]);
    $event->trigger();
    $obj->mdlid = $mdlid;
    $DB->update_record("local_lsf_category", $obj);
}

/**
 * returns a list of the topmost elements in the lsf-category hierarchy
 * @package local_lsf_unification
 */
function get_his_toplevel_originids() {
    global $DB, $CFG;
    $helpfuntion1 = function ($arrayel) {
        return $arrayel->origin;
    };
    $originssql = "SELECT origin FROM " . $CFG->prefix .
             "local_lsf_category WHERE ueid = origin AND parent = ueid";
    return array_map($helpfuntion1, $DB->get_records_sql($originssql));
}

/**
 * returns a list of the topmost elements in the mdl-category hierarchy
 * @package local_lsf_unification
 */
function get_mdl_toplevels() {
    global $DB, $CFG;
    $maincategoriessql = "SELECT id, name FROM " . $CFG->prefix .
             "course_categories WHERE parent=0 ORDER BY sortorder";
    return $DB->get_records_sql($maincategoriessql);
}

/**
 * returns a list of children to a given parent.id in the mdl-category hierarchy
 * @package local_lsf_unification
 */
function get_mdl_sublevels($mainid) {
    global $DB, $CFG;
    $subcatssql = "SELECT id, name, path FROM " . $CFG->prefix .
             "course_categories WHERE path LIKE '/" . $mainid . "/%' OR id=" . $mainid .
             " ORDER BY sortorder";
    return $DB->get_records_sql($subcatssql);
}
