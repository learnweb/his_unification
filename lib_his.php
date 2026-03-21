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

use local_lsf_unification\event\matchingtable_updated;
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
 * @param int $veranstid
 * @param int $mdlid
 * @return bool
 * @throws dml_exception
 */
function sethislink(int $veranstid, int $mdlid): bool {
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
 * @param int $veranstid
 * @return bool
 * @throws dml_exception
 */
function removehislink(int $veranstid): bool {
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
 * @param int $mtknr
 * @return array
 */
function get_students_stdp_terminids(int $mtknr): array {
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
 * @param string $username the teachers username
 * @param bool $checkhis
 * @return int|null the teachers pid (personen-id)
 */
function get_teachers_pid(string $username, bool $checkhis = false): int|null {
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
 * @param array $veranstids
 * @return array the courses
 * @throws dml_exception
 */
function get_courses_by_veranstids(array $veranstids) {
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
 * @param int $veranstid
 * @return object the course
 * @throws dml_exception
 */
function get_course_by_veranstid(int $veranstid): object {
    $result = get_courses_by_veranstids([$veranstid]);
    return $result[$veranstid];
}

/**
 * Get all veranstids from a teacher.
 * @param int $pid
 * @return array
 */
function get_veranstids_by_teacher(int $pid): array {
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
 * @param string $username
 * @return string
 */
function username_to_mail(string $username): string {
    return $username . "@uni-muenster.de";
}

/**
 * Creates a list of courses assigned to a teacher
 * get_teachers_course_list is a required function for the lsf_unification plugin
 *
 * @param string $username the teachers username
 * @param bool $longinfo level of detail
 * @return array an array containing objects consisting of veranstid and info
 */
function get_teachers_course_list(string $username, bool $longinfo = false): array {
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
 * @param int $veranstid idnumber/veranstid
 * @param string $username the teachers username
 * @return bool
 */
function is_course_of_teacher(int $veranstid, string $username): bool {
    $courses = get_teachers_course_list($username, false, true);
    return !empty($courses[$veranstid]);
}

/**
 * Find_origin_category is NOT a required function for the lsf_unification plugin, it is used
 * internally only
 *
 * @param int $quellid
 * @return int $origin
 */
function find_origin_category(int $quellid): int {
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
 * @param int $veranstid idnumber/veranstid
 * @return array $sortedresult sorted array of teacher objects
 */
function get_teachers_of_course(int $veranstid): array {
    global $pgdb;
    establish_secondary_DB_connection();
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
    close_secondary_DB_connection();
    return $sortedresult;
}

/**
 * returns the default fullname according to a given veranstid
 * get_default_fullname is a required function for the lsf_unification plugin
 *
 * @param object $lsfcourse
 * @return string $fullname
 */
function get_default_fullname(object $lsfcourse): string {
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
 * @param object $lsfcourse
 * @param bool $long
 * @return string $shortname
 */
function get_default_shortname(object $lsfcourse, bool $long = false): string {
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
 * @param object $lsfcourse idnumber/veranstid
 * @return string $summary
 */
function get_default_summary(object $lsfcourse): string {
    global $pgdb;
    establish_secondary_DB_connection();
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
    close_secondary_DB_connection();
    return $summary;
}

/**
 * returns the default startdate according to a given veranstid
 * get_default_startdate is a required function for the lsf_unification plugin
 *
 * @param object $lsfcourse
 * @return int
 */
function get_default_startdate(object $lsfcourse): int {
    $semester = $lsfcourse->semester . '';
    $year = substr($semester, 0, 4);
    $month = (substr($semester, -1) == "1") ? 4 : 10;
    return mktime(0, 0, 0, $month, 1, $year);
}

/**
 * returns if a course is already imported
 * course_exists is a required function for the lsf_unification plugin
 *
 * @param int $veranstid idnumber/veranstid
 * @return bool $is_course_existing
 */
function course_exists(int $veranstid): bool {
    global $DB;
    if (
        $DB->record_exists("local_lsf_unification_course", ["veranstid" => ($veranstid)]) &&
             !($DB->record_exists("local_lsf_unification_course", ["veranstid" => ($veranstid), "mdlid" => 0]) ||
            $DB->record_exists("local_lsf_unification_course", ["veranstid" => ($veranstid), "mdlid" => 1]))
    ) {
        if (!$DB->record_exists("course", ["idnumber" => ($veranstid)])) {
            $DB->delete_records("local_lsf_unification_course", ["veranstid" => ($veranstid)]);
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
 * @param object $lsfcourse
 * @param string $shortname shortname
 * @return bool
 */
function is_shortname_valid(object $lsfcourse, string $shortname): bool {
    $string = get_default_shortname_ending($lsfcourse);
    return (substr($shortname, -strlen($string)) == $string);
}

/**
 * Return short name of a course.
 * @param object $lsfcourse
 * @return string
 */
function get_default_shortname_ending(object $lsfcourse): string {
    return "-" . substr($lsfcourse->semester, 0, 4) . "_" . substr($lsfcourse->semester, -1);
}

/**
 * returns if a shortname hint, if it is invalid
 * shortname_hint is a required function for the lsf_unification plugin
 *
 * @param object $lsfcourse
 * @return string
 */
function shortname_hint(object $lsfcourse): string {
    $string = "-" . substr($lsfcourse->semester, 0, 4) . "_" . substr($lsfcourse->semester, -1);
    return $string;
}

/**
 * enroles teachers to a freshly created course
 * enrole_teachers is a required function for the lsf_unification plugin
 *
 * @param int $veranstid idnumber/veranstid
 * @param int $courseid id of moodle course
 * @return string $warnings
 */
function enrole_teachers(int $veranstid, int $courseid): string {
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
 * @param int $veranstid idnumber/veranstid
 * @param int $courseid id of moodle course
 * @return void
 */
function set_course_created(int $veranstid, int $courseid): void {
    global $DB;
    if ($courseentry = $DB->get_record("local_lsf_unification_course", ["veranstid" => $veranstid])) {
        $courseentry->mdlid = $courseid;
        $courseentry->timestamp = time();
        $DB->update_record('local_lsf_unification_course', $courseentry);
    } else {
        $courseentry = new stdClass();
        $courseentry->veranstid = $veranstid;
        $courseentry->mdlid = $courseid;
        $courseentry->timestamp = time();
        $DB->insert_record("local_lsf_unification_course", $courseentry);
    }
}

/**
 * Get record from moodle_db.
 * LEARNWEB-TODO: this does not save lines, use the DB query directly instead of this function.
 * @param int $rid
 * @return false|stdClass
 * @throws dml_exception
 */
function get_course_request(int $rid): false|stdClass {
    global $DB;
    return $DB->get_record("local_lsf_unification_course", ["id" => $rid, "mdlid" => 0]);
}

/**
 * Get record from moodle_db.
 * LEARNWEB-TODO: this does not save lines, use the DB query directly instead of this function.
 * @return array
 * @throws dml_exception
 */
function get_course_requests(): array {
    global $DB;
    return $DB->get_records("local_lsf_unification_course", ["mdlid" => 0], "id");
}

/**
 * Update a course record and set it as requested.
 * @param int $veranstid
 * @return bool|int|null
 * @throws dml_exception
 */
function set_course_requested(int $veranstid): bool|int|null {
    global $DB, $USER;
    if ($courseentry = $DB->get_record("local_lsf_unification_course", ["veranstid" => $veranstid])) {
        return null;
    } else {
        $courseentry = new stdClass();
        $courseentry->veranstid = $veranstid;
        $courseentry->mdlid = 0;
        $courseentry->requeststate = 1;
        $courseentry->timestamp = time();
        $courseentry->requesterid = $USER->id;
        return $DB->insert_record("local_lsf_unification_course", $courseentry);
    }
}

/**
 * Set a course as accepted from an authorized user.
 * @param int $veranstid
 * @return int|null
 * @throws dml_exception
 */
function set_course_accepted(int $veranstid): int|null {
    global $DB, $USER;
    if ($courseentry = $DB->get_record("local_lsf_unification_course", ["veranstid" => $veranstid])) {
        $courseentry->requeststate = 2;
        $courseentry->timestamp = time();
        $courseentry->acceptorid = $USER->id;
        $DB->update_record('local_lsf_unification_course', $courseentry);
        return $courseentry->id;
    }
    return null;
}

/**
 * Set a course as declined from an authorized user.
 * @param int $veranstid
 * @return void
 * @throws dml_exception
 */
function set_course_declined(int $veranstid): void {
    global $DB, $USER;
    if ($courseentry = $DB->get_record("local_lsf_unification_course", ["veranstid" => $veranstid])) {
        $DB->delete_records("local_lsf_unification_course", ["veranstid" => ($veranstid)]);
    }
}

/**
 * returns mapped categories for a specified course
 * get_courses_categories is a required function for the lsf_unification plugin
 *
 * @param int $veranstid idnumber/veranstid
 * @param bool $updatehelptablesifnecessary
 * @return array
 */
function get_courses_categories(int $veranstid, bool $updatehelptablesifnecessary = true): array {
    global $pgdb, $DB, $CFG;
    establish_secondary_DB_connection();
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
    $otherueidssql = "SELECT parent FROM {local_lsf_unification_categoryparenthood} WHERE child in (" . $ueids . ")";
    $originssql = "SELECT origin FROM {local_lsf_unification_category} WHERE ueid in (" .
             $otherueidssql . ") OR ueid in (" . $ueids . ")";
    $origins = implode(", ", array_map($helpfuntion1, $DB->get_records_sql($originssql)));
    if (!empty($origins)) {
        $categoriessql = "SELECT DISTINCT lsfcat.mdlid, coursecat.name
            FROM {local_lsf_unification_category} lsfcat
            JOIN {course_categories} coursecat ON lsfcat.mdlid = coursecat.id
            WHERE lsfcat.ueid in (" . $origins . ")
            ORDER BY coursecat.sortorder";
        if (get_config('local_lsf_unification', 'subcategories')) {
            $maincourses = implode(
                ", ",
                array_map($helpfuntion3, $DB->get_records_sql($categoriessql))
            );
            if (empty($maincourses)) {
                $maincourses = get_config('local_lsf_unification', 'defaultcategory');
            }
            $categoriessqlmain = "SELECT id, name
                                  FROM {course_categories}
                                  WHERE id in (" . $maincourses . ") ORDER BY sortorder";
            $categories = array_map($helpfuntion2, $DB->get_records_sql($categoriessqlmain));
            $categoriessqlchild = "SELECT id, name
                                   FROM {course_categories}
                                   WHERE parent in (" . $maincourses . ") ORDER BY sortorder";
            $categorieschild = $DB->get_records_sql($categoriessqlchild);
            $categories = $categories + array_map($helpfuntion2, $categorieschild);
            foreach ($categorieschild as $child) {
                if (!str_contains($child->name, 'Archiv')) {
                    $categoriessqliterative = "SELECT id, name
                                               FROM {course_categories}
                                               WHERE path like '%" . $child->id . "/%' ORDER BY sortorder";
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
    close_secondary_DB_connection();
    return $categories;
}

/**
 * updates the helptables
 * insert_missing_helptable_entries is a required function for the lsf_unification plugin
 *
 * @param bool $debugoutput
 * @param bool $tryeverything If every parent-child combination should be reevaluated.
 * @return void
 */
function insert_missing_helptable_entries(bool $debugoutput = false, bool $tryeverything = false): void {
    // LEARNWEB-TODO: Please refactor this horrible function.
    global $pgdb, $DB, $CFG;
    require_once($CFG->dirroot . '/local/lsf_unification/class_pg_lite.php');
    require_once($CFG->dirroot . '/local/lsf_unification/lib_features.php');

    // Build db connection.
    $pgdb = new pg_lite();
    $pgdb->connect();

    // Get current categories and relationships between categories (parent-child) as recordsets.
    $records1 = $DB->get_recordset('local_lsf_unification_category', null, '', 'ueid');
    $records2 = $DB->get_recordset('local_lsf_unification_categoryparenthood', null, '', 'child, parent');
    $knowncat = [];
    $knownrelation = [];

    // Create lookup arrays.
    foreach ($records1 as $record1) {
        // Save already known categories in lsf_unification.
        $knowncat[$record1->ueid] = true;
    }
    $records1->close();
    foreach ($records2 as $record2) {
        // Save already known child-parent relationships (by category ueid).
        $knownrelation[$record2->child][$record2->parent] = ($tryeverything === false);
    }
    $records2->close();

    // Get every category (parents and childs) from the lsf_view and iterate over it.
    $sql = "SELECT ueid, uebergeord, uebergeord, quellid, txt, zeitstempel FROM " . HIS_UEBERSCHRIFT . ";";
    $qmain = pg_query($pgdb->connection, $sql);
    $lsfcategories = pg_fetch_all($qmain) ?: [];
    foreach ($lsfcategories as $hislsftitle) {
        $categoryunkown = !isset($knowncat[$hislsftitle->ueid]);
        $relationunknown = !isset($knownrelation[$hislsftitle->ueid][$hislsftitle->uebergeord]);

        if ($categoryunkown) {
            // Create match-table-entry if not existing.
            $entry = (object) [
                'ueid' => $hislsftitle->ueid,
                'parent' => empty($hislsftitle->uebergeord) ? ($hislsftitle->ueid) : ($hislsftitle->uebergeord),
                'origin' => find_origin_category($hislsftitle->ueid),
                'mdlid' => 0,
                'timestamp' => isset($hislsftitle->zeitstempel) ? strtotime($hislsftitle->zeitstempel) : null,
                'txt' => mb_convert_encoding($hislsftitle->txt, 'UTF-8', 'ISO-8859-1'),
            ];
            try {
                $DB->insert_record("local_lsf_unification_category", $entry, true);
                $knowncat[$hislsftitle->ueid] = true;
            } catch (Exception $e) {
                if ($debugoutput) {
                    mtrace("FEHLER1 " . var_export($e, true) . var_export($DB->get_last_error(), true));
                }
            }
        }
        // LEARNWEB-TODO: Bis hierhin wurde schon bearbeitet.
        if ($relationunknown || $knownrelation[$hislsftitle->ueid][$hislsftitle->uebergeord] != true) {
            // Create parenthood-table-entry if not existing.
            $child = $hislsftitle->ueid;
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
                    if (!empty($parent) && !isset($knownrelation[$child][$parent])) {
                        try {
                            $entry = new stdClass();
                            $entry->child = $child;
                            $entry->parent = $parent;
                            $entry->distance = $distance;
                            $DB->insert_record("local_lsf_unification_categoryparenthood", $entry, true);
                        } catch (Exception $e) {
                            if ($debugoutput) {
                                mtrace("FEHLER2 " . var_export($e, true) . var_export($DB->get_last_error(), true), '');
                            }
                        }
                    }
                    $knownrelation[$child][$parent] = true;
                }
            } while (!empty($parent) && ($ueid != $parent));
            $entry = $DB->get_record('local_lsf_unification_category', ["ueid" => $hislsftitle->ueid]);
            $entry->txt2 = mb_convert_encoding($fullname, 'UTF-8', 'ISO-8859-1');
            try {
                $DB->update_record('local_lsf_unification_category', $entry, true);
            } catch (Exception $e) {
                try {
                    $entry->txt2 = delete_bad_chars($entry->txt2);
                    $DB->update_record('local_lsf_unification_category', $entry, true);
                } catch (Exception $e) {
                    if ($debugoutput) {
                        mtrace("FEHLER2 " . var_export($e, true) . var_export($DB->get_last_error(), true), '');
                    }
                }
            }
        }
    }
    $pgdb->dispose();
}

/**
 * delete_bad_chars is NOT a required function for the lsf_unification plugin, it is used internally
 * only
 *
 * @param string $str
 * @return string $str
 */
function delete_bad_chars(string $str): string {
    return strtr(mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1'), [
                    "\xc2\x96" => "", // EN DASH.
                    "\xc2\x97" => "", // EM DASH.
                    "\xc2\x84" => "", // DOUBLE LOW-9 QUOTATION MARK.
    ]);
}

/**
 * returns a list of (newest copies of) children to a parents (and the parent's copies)
 * @param string|int $origins
 * @return array
 */
function get_newest_sublevels(string|int $origins): array {
    global $DB, $CFG;
    $helpfuntion1 = function ($arrayel) {
        return $arrayel->ueid;
    };
    // Get all copies of current category.
    $originssql = "SELECT ueid FROM {local_lsf_unification_category} WHERE origin in (" . $origins . ")";
    $copies = implode(", ", array_map($helpfuntion1, $DB->get_records_sql($originssql)));
    // Get all their childcategories.
    $sublevelsallsql = "SELECT *
            FROM (
                SELECT max(ueid) as max_ueid, origin
                FROM {local_lsf_unification_category}
                WHERE parent in (" . $copies . ") AND ueid not in (" . $origins . ") GROUP BY origin
                ) AS a
            JOIN {local_lsf_unification_category} lsfcat ON a.max_ueid = lsfcat.ueid
            ORDER BY lsfcat.txt";
    $result = $DB->get_records_sql($sublevelsallsql);
    return $result;
}

/**
 * returns if a category has children
 * @param string|int $origins
 * @return bool
 */
function has_sublevels(string|int $origins): bool {
    global $CFG, $DB;
    $sublevelssql = "SELECT id
        FROM {local_lsf_unification_category}
        WHERE parent in (" . $origins . ") AND ueid not in (" . $origins . ")";
    return (count($DB->get_records_sql($sublevelssql)) > 0);
}

/**
 * returns the newest copy to a given id
 * @param int $id
 * @return false|stdClass
 */
function get_newest_element(int $id): false|stdClass {
    global $CFG, $DB;
    $origins = $DB->get_record("local_lsf_unification_category", ["ueid" => $id,
    ], "origin")->origin;
    $sublevelssql = "SELECT max(ueid) as max_ueid, origin
        FROM {local_lsf_unification_category}
        WHERE origin in (" . $origins . ") GROUP BY origin";
    $sublevels = $DB->get_records_sql($sublevelssql);
    $ueid = array_shift($sublevels)->max_ueid;
    return $DB->get_record("local_lsf_unification_category", ["ueid" => $ueid,
    ]);
}

/**
 * returns the parent of the newest copy to the given id
 * @param int $id
 * @return false|stdClass
 */
function get_newest_parent(int $id): false|stdClass {
    global $CFG, $DB;
    $parent = get_newest_element($id)->parent;
    return $DB->get_record("local_lsf_unification_category", ["ueid" => $parent,
    ]);
}

/**
 * returns the moodle-id given to a lsf-id
 * @param int $id
 * @return int
 */
function get_mdlid(int $id): int {
    global $CFG, $DB;
    $origin = $DB->get_record("local_lsf_unification_category", ["ueid" => $id], "origin")->origin;
    $mdlid = $DB->get_record("local_lsf_unification_category", ["ueid" => $origin], "mdlid")->mdlid;
    return $mdlid;
}

/**
 * returns the moodle-name given to a lsf-id
 * @param int $id
 * @return string
 */
function get_mdlname(int $id): string {
    global $CFG, $DB;
    $origin = $DB->get_record("local_lsf_unification_category", ["ueid" => $id], "origin")->origin;
    $mdlid = $DB->get_record("local_lsf_unification_category", ["ueid" => $origin], "mdlid")->mdlid;
    $cat = $DB->get_record("course_categories", ["id" => $mdlid], "name");
    return $cat->name;
}

/**
 * sets a category-mapping
 * @param int $ueid
 * @param int $mdlid
 * @return void
 */
function set_cat_mapping(int $ueid, int $mdlid): void {
    global $DB, $SITE;
    $obj = $DB->get_record("local_lsf_unification_category", ["ueid" => $ueid]);
    $event = \local_lsf_unification\event\matchingtable_updated::create([
            'objectid' => $obj->id,
            'context' => context_system::instance(0, IGNORE_MISSING),
            'other' => ['mappingold' => $obj->mdlid, 'mappingnew' => $mdlid, 'originid' => $ueid],
    ]);
    $event->trigger();
    $obj->mdlid = $mdlid;
    $DB->update_record("local_lsf_unification_category", $obj);
}

/**
 * returns a list of the topmost elements in the lsf-category hierarchy
 * @return array
 */
function get_his_toplevel_originids(): array {
    global $DB, $CFG;
    $helpfuntion1 = function ($arrayel) {
        return $arrayel->origin;
    };
    $originssql = "SELECT origin FROM {local_lsf_unification_category} WHERE ueid = origin AND parent = ueid";
    return array_map($helpfuntion1, $DB->get_records_sql($originssql));
}

/**
 * returns a list of the topmost elements in the mdl-category hierarchy
 * @return array
 */
function get_mdl_toplevels(): array {
    global $DB, $CFG;
    $maincategoriessql = "SELECT id, name FROM {course_categories} WHERE parent=0 ORDER BY sortorder";
    return $DB->get_records_sql($maincategoriessql);
}

/**
 * returns a list of children to a given parent.id in the mdl-category hierarchy
 * @param int $mainid
 * @return array
 */
function get_mdl_sublevels(int $mainid): array {
    global $DB, $CFG;
    $subcatssql = "SELECT id, name, path FROM {course_categories} WHERE path LIKE '/" . $mainid . "/%' OR id=" . $mainid .
             " ORDER BY sortorder";
    return $DB->get_records_sql($subcatssql);
}
