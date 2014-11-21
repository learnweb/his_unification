<?php
/**
 * Functions that are specific to HIS database, format and helptables containing his-formatted data
 **/
defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/local/lsf_unification/class_pg_lite.php');

define("HIS_PERSONAL",         "public.learnweb_personal");
define("HIS_VERANSTALTUNG",    "public.learnweb_veranstaltung");
define("HIS_PERSONAL_VERANST", "public.learnweb_personal_veranst");
define("HIS_UEBERSCHRIFT",     "public.learnweb_ueberschrift");
define("HIS_STDP",             "public.learnweb_stdp");
define("HIS_VERANST_KOMMENTAR","public.learnweb_veranst_kommentar");

/**
 * establish_secondary_DB_connection is a required function for the lsf_unification plugin
*/
function establish_secondary_DB_connection() {
    global $pgDB;
    if (!empty($pgDB) && !empty($pgDB->connection)) return;
    $pgDB = new pg_lite();
    if (!($pgDB->connect()===true))
        return false;
    return true;
}

/**
 * close_secondary_DB_connection is a required function for the lsf_unification plugin
 */
function close_secondary_DB_connection() {
    global $pgDB;
    if (empty($pgDB) || empty($pgDB->connection)) return;
    $pgDB->dispose();
}

function setupHisSoap() {
	global $CFG, $hislsf_soapclient;
	if (!get_config('local_lsf_unification', 'his_deeplink_via_soap')) return false;
	if (empty($hislsf_soapclient)) {
		try {
			$hislsf_soapclient = new SoapClient(get_config('local_lsf_unification', 'soapwsdl'));
			$result = $hislsf_soapclient->auth(get_config('local_lsf_unification', 'soapuser'), get_config('local_lsf_unification', 'soappass'));
			$his_moodle_url = get_config('local_lsf_unification', 'moodle_url');
            $result = $result && $hislsf_soapclient->configureMoodleWKZ($his_moodle_url."/course/view.php?id=MOODLEID");
			return $result;
		} catch (Exception $e) {
			return false;
		}
	}
	return true;
}

function setHisLink($veranstid, $mdlid) {
	global $hislsf_soapclient;
	if (!setupHisSoap()) return false;
	$hislsf_soapclient->removeMoodleLink($veranstid); // to override the old value (if a link already is etablished) you have to remove the existing Link first
	$hislsf_soapclient->setMoodleLink($veranstid, $mdlid);
	return true;
}

function removeHisLink($veranstid) {
	global $hislsf_soapclient;
	if (!setupHisSoap()) return false;
	$hislsf_soapclient->removeMoodleLink($veranstid);
	return true;
}

function get_students_stdp_terminids($mtknr) {
    global $pgDB;
    establish_secondary_DB_connection();
    $q = pg_query($pgDB->connection,
        "SELECT terminid FROM ". HIS_STDP ." WHERE mtknr = $mtknr and terminid is not null group by terminid order by terminid;");
    $return = array();
    while ($terminid = pg_fetch_object($q)) {
        array_push($return, $terminid->terminid);
    }
    close_secondary_DB_connection();
    return $return;
}

/**
 * get_teachers_pid returns the pid (personen-id) connected to a specific username
 * @param $username the teachers username
 * @return $pid the teachers pid (personen-id)
 */
function get_teachers_pid($username, $checkhis=false) {
    global $pgDB;
    $emailcheck = $checkhis?(" OR (login = '".$username."')"):"";
    $q = pg_query($pgDB->connection, "SELECT pid FROM ". HIS_PERSONAL ." WHERE (zivk = '".$username."')".$emailcheck);
    if ($hislsf_teacher = pg_fetch_object($q)) {
        return $hislsf_teacher->pid;
    }
    if (!$checkhis) {
        return get_teachers_pid($username, true);
    }
    return null;
}

function get_courses_by_veranstids($veranstids) {
    global $pgDB;
    
    //if veranstids is empty, no need to make a db request. return empty list
    if(empty($veranstids))
        return array();
        
    $veranstids_string = implode(',',$veranstids);
    $q = pg_query($pgDB->connection,
        "SELECT veranstid, veranstnr, semester, semestertxt, veranstaltungsart, titel, urlveranst
        FROM ". HIS_VERANSTALTUNG ." as veranst where veranstid in (".$veranstids_string.") AND ".
        "(CURRENT_DATE - CAST(veranst.zeitstempel AS date)) < ".get_config('local_lsf_unification', 'max_import_age'). "order by semester,titel;");
    $result_list = array();
    while ($course = pg_fetch_object($q)) {
        $result = new stdClass();
        $result->veranstid = $course->veranstid;
        $result->veranstnr = $course->veranstnr;
        $result->semester = $course->semester;
        $result->semestertxt = $course->semestertxt;
        $result->veranstaltungsart = $course->veranstaltungsart;
        $result->titel = $course->titel;
        $result->urlveranst = $course->urlveranst;
        $result_list[$course->veranstid] = $result;
    }
    return $result_list;
}

function get_course_by_veranstid($veranstid) {
    $result = get_courses_by_veranstids(array($veranstid));
    return $result[$veranstid];
}

function get_veranstids_by_teacher($pid) {
    global $pgDB;
    $q = pg_query($pgDB->connection,
        "SELECT veranstid FROM ". HIS_PERSONAL_VERANST ." WHERE pid = $pid and veranstid is not null group by veranstid order by veranstid;");
    $return = array();
    while ($veranstid = pg_fetch_object($q)) {
        array_push($return, $veranstid->veranstid);
    }
    return $return;
}

function username_to_mail($username) {
    return $username."@uni-muenster.de";
}

/**
 * creates a list of courses assigned to a teacher
 * get_teachers_course_list is a required function for the lsf_unification plugin
 *
 * @param $username the teachers username
 * @param $longinfo level of detail
 * @param $checkmail not intended for manual setting, just for recursion
 * @return $courselist an array containing objects consisting of veranstid and info
 */
function get_teachers_course_list($username, $longinfo = false) {
    global $pgDB;
    $courselist = array();
    $pid = get_teachers_pid($username);
    if (empty($pid)) {
        return $courselist;
    }
    $veranstids = get_veranstids_by_teacher($pid);
    $courses = get_courses_by_veranstids($veranstids);
    foreach ($courses as $veranstid=>$course) {
        $result = new stdClass();
        $result->veranstid = $course->veranstid;
        $result->info = utf8_encode($course->titel).($longinfo?("&nbsp;&nbsp;(".$course->semestertxt.((!empty($course->urlveranst))?(" , <a href='".$course->urlveranst."'> KVV-Nr. ".$course->veranstnr."</a>"):"").")"):"");
        $courselist[$course->veranstid] = $result;
    }
    return $courselist;
}

/**
 * returns true if a idnumber/veranstid assigned to a specific teacher
 * is_veranstid_valid is a required function for the lsf_unification plugin
 *
 * @param $veranstid idnumber/veranstid
 * @param $username the teachers username
 * @return $is_valid
 */
function is_course_of_teacher($veranstid, $username) {
    $courses = get_teachers_course_list($username, false, true);
    return !empty($courses[$veranstid]);
}

/**
 * find_origin_category is NOT a required function for the lsf_unification plugin, it is used internally only
 *
 * @param $quellid
 * @return $origin
 */
function find_origin_category($quellid) {
    global $pgDB;
    $origin = $quellid;
    do {
        $quellid = $origin;
        $q = pg_query($pgDB->connection, "SELECT quellid FROM ". HIS_UEBERSCHRIFT ." WHERE ueid = '".$quellid."'");
        if ($hislsf_title = pg_fetch_object($q)) {
            $q2 = pg_query($pgDB->connection, "SELECT quellid FROM ". HIS_UEBERSCHRIFT ." WHERE ueid = '".($hislsf_title->quellid)."'");
            if ($hislsf_title2 = pg_fetch_object($q2)) {
                $origin = $hislsf_title->quellid;
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
 */
function get_teachers_of_course($veranstid) {
    global $pgDB;
    // get sorted (by relevance) pids of teachers
    $pidstring = "";
    $pids = array();
    $q1 = pg_query($pgDB->connection,
        "SELECT DISTINCT pid, sort FROM ". HIS_PERSONAL_VERANST .
        " WHERE veranstid = ".$veranstid.
        " ORDER BY sort ASC");
    while ($person = pg_fetch_object($q1)) {
        $pidstring .= (empty($pidstring)?"":",").$person->pid;
        $pids[] = $person->pid;
    }
    if (empty($pids)) return array();
    // get personal info
    $result = array();
    $q2 = pg_query($pgDB->connection,
        "SELECT vorname, nachname, zivk, login, pid FROM " . HIS_PERSONAL 
            . " WHERE pid IN (" . $pidstring . ")");
    while ($person = pg_fetch_object($q2)) {
        $result[$person->pid] = $person;
    }
    // sort by relevance
    $sortedresult = array();
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
 */
function get_default_fullname($lsf_course) {
    $personen = "";
    foreach (get_teachers_of_course($lsf_course->veranstid) as $person) {
        $personen .= ", ".trim($person->vorname)." ".trim($person->nachname);
    }
    return utf8_encode(($lsf_course->titel)." ".trim($lsf_course->semestertxt).$personen);
}

/**
 * returns the default shortname according to a given veranstid
 * get_default_shortname is a required function for the lsf_unification plugin
 *
 * @param $veranstid idnumber/veranstid
 * @return $shortname
 */
function get_default_shortname($lsf_course, $long=false) {
    global $DB;
    $i = "";
    foreach (explode(" ", $lsf_course->titel) as $word) {
        $i .= strtoupper($word[0]).(($long && !empty($word[1]))?$word[1]:"");
    }
    $name = utf8_encode($i."-".substr($lsf_course->semester,0,4)."_".substr($lsf_course->semester,-1));
    if (!$long && $DB->record_exists('course', array('shortname'=>$name))) {
        return get_default_shortname($lsf_course, true);
    }
    return $name;
}

/**
 * returns the default summary according to a given veranstid
 * get_default_summary is a required function for the lsf_unification plugin
 *
 * @param $veranstid idnumber/veranstid
 * @return $summary
 */
function get_default_summary($lsf_course) {
    global $pgDB;
    $summary = "(".$lsf_course->urlveranst.")";
    $q = pg_query($pgDB->connection, "SELECT kommentar FROM ". HIS_VERANST_KOMMENTAR ." WHERE veranstid = '".$lsf_course->veranstid."'");
    if ($sum_object = pg_fetch_object($q)) {
        if (!empty($sum_object->kommentar)) {
            $summary = $sum_object->kommentar." ".$summary;
        }
    }
    return utf8_encode($summary);
}

/**
 * returns the default startdate according to a given veranstid
 * get_default_startdate is a required function for the lsf_unification plugin
 *
 * @param $veranstid idnumber/veranstid
 * @return $startdate
 */
function get_default_startdate($lsf_course) {
    $semester = $lsf_course->semester.'';
    $year = substr($semester, 0, 4);
    $month = (substr($semester, -1) == "1")?4:10;
    return mktime(0, 0, 0, $month, 1, $year);
}

/**
 * returns if a course is already imported
 * course_exists is a required function for the lsf_unification plugin
 *
 * @param $veranstid idnumber/veranstid
 * @return $is_course_existing
 */
function course_exists($veranstid) {
    global $DB;
    if ($DB->record_exists("local_lsf_course",array("veranstid"=>($veranstid))) && !($DB->record_exists("local_lsf_course",array("veranstid"=>($veranstid), "mdlid"=>0)) || $DB->record_exists("local_lsf_course",array("veranstid"=>($veranstid), "mdlid"=>1)))) {
        if (!$DB->record_exists("course",array("idnumber"=>($veranstid)))) {
            $DB->delete_records("local_lsf_course",array("veranstid"=>($veranstid)));
        } else {
            return true;
        }
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
 */
function is_shortname_valid($lsf_course, $shortname) {
    $string = get_default_shortname_ending($lsf_course);
    return (substr($shortname,-strlen($string)) == $string);
}

function get_default_shortname_ending($lsf_course) {
    return "-".substr($lsf_course->semester,0,4)."_".substr($lsf_course->semester,-1);
}

/**
 * returns if a shortname hint, if it is invalid
 * shortname_hint is a required function for the lsf_unification plugin
 *
 * @param $veranstid idnumber/veranstid
 * @return $hint
 */
function shortname_hint($lsf_course) {
    $string = "-".substr($lsf_course->semester,0,4)."_".substr($lsf_course->semester,-1);
    return $string;
}

/**
 * enroles teachers to a freshly created course
 * enrole_teachers is a required function for the lsf_unification plugin
 *
 * @param $veranstid idnumber/veranstid
 * @param $courseid id of moodle course
 * @return $warnings
 */
function enrole_teachers($veranstid, $courseid) {
    global $DB, $CFG;
    $warnings = "";
    foreach (get_teachers_of_course($veranstid) as $lsf_user) {
        unset($teacher);
        if (!empty($lsf_user->zivk)) {
            $teacher = $DB->get_record("user", array("username" => $lsf_user->zivk));
        }
        //if user cannot be found by zivk try to find user by login that is manually set in his
        if (empty($teacher) && !empty($lsf_user->login)) {
            $teacher = $DB->get_record("user", array("username" => $lsf_user->login));
        }
        if (empty($teacher) || !enrol_try_internal_enrol($courseid, $teacher->id, get_config('local_lsf_unification', 'roleid_teacher'))) {
            $warnings = $warnings."\n".get_string('warning_cannot_enrol_other','local_lsf_unification')." (".$lsf_user->zivk.", ".$lsf_user->login." ".$lsf_user->vorname." ".$lsf_user->nachname.")";
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
 */
function set_course_created($veranstid, $courseid) {
    global $DB;
    if ($courseentry = $DB->get_record("local_lsf_course", array("veranstid" => $veranstid))) {
        $courseentry->mdlid = $courseid;
        $courseentry->timestamp = time();
        $DB->update_record('local_lsf_course', $courseentry);
    } else {
        $courseentry = new stdClass();
        $courseentry->veranstid = $veranstid;
        $courseentry->mdlid = $courseid;
        $courseentry->timestamp = time();
        $DB->insert_record("local_lsf_course",$courseentry);
    }
}

function get_course_request($rid) {
    global $DB;
    return $DB->get_record("local_lsf_course", array("id" => $rid, "mdlid" => 0));
}

function get_course_requests() {
    global $DB;
    return $DB->get_records("local_lsf_course", array("mdlid" => 0), "id");
}

function set_course_requested($veranstid) {
    global $DB, $USER;
    if ($courseentry = $DB->get_record("local_lsf_course", array("veranstid" => $veranstid))) {
        return NULL;
    } else {
        $courseentry = new stdClass();
        $courseentry->veranstid = $veranstid;
        $courseentry->mdlid = 0;
        $courseentry->requeststate = 1;
        $courseentry->timestamp = time();
        $courseentry->requesterid = $USER->id;
        return $DB->insert_record("local_lsf_course",$courseentry);
    }
}

function set_course_accepted($veranstid) {
    global $DB, $USER;
    if ($courseentry = $DB->get_record("local_lsf_course", array("veranstid" => $veranstid))) {
        $courseentry->requeststate = 2;
        $courseentry->timestamp = time();
        $courseentry->acceptorid = $USER->id;
        $DB->update_record('local_lsf_course', $courseentry);
        return $courseentry->id;
    }
}

function set_course_declined($veranstid) {
    global $DB, $USER;
    if ($courseentry = $DB->get_record("local_lsf_course", array("veranstid" => $veranstid))) {
        $DB->delete_records("local_lsf_course",array("veranstid"=>($veranstid)));
    }
}


/**
 * returns mapped categories for a specified course
 * get_courses_categories is a required function for the lsf_unification plugin
 *
 * @param $veranstid idnumber/veranstid
 * @return $courselist
 */
function get_courses_categories($veranstid, $update_helptables_if_necessary=true) {
    global $pgDB, $DB, $CFG;
    $helpfuntion1 = function($array_el) {
        return $array_el->origin;
    };
    $helpfuntion2 = function($array_el) {
        return $array_el->name;
    };
    $helpfuntion3 = function($array_el) {
        return $array_el->mdlid;
    };
    $q = pg_query($pgDB->connection, "SELECT ueid FROM ". HIS_UEBERSCHRIFT ." WHERE veranstid=".$veranstid."");
    $choices = array();
    $categories = array();
    while ($hislsf_title = pg_fetch_object($q)) $ueids = (empty($ueids)?"":($ueids.", ")).("".$hislsf_title->ueid."");
    $other_ueids_sql = "SELECT parent FROM ".$CFG->prefix."local_lsf_categoryparenthood WHERE child in (".$ueids.")";
    $origins_sql = "SELECT origin FROM ".$CFG->prefix."local_lsf_category WHERE ueid in (".$other_ueids_sql.") OR ueid in (".$ueids.")";
    $origins = implode(", ", array_map($helpfuntion1, $DB->get_records_sql($origins_sql)));
    if (!empty($origins)) {
        $categories_sql = "SELECT mdlid, name FROM (".$CFG->prefix."local_lsf_category JOIN ".$CFG->prefix."course_categories ON ".$CFG->prefix."local_lsf_category.mdlid = ".$CFG->prefix."course_categories.id) WHERE ueid in (".$origins.") ORDER BY sortorder";
        if (get_config('local_lsf_unification', 'subcategories')) {
            $maincourses = implode(", ", array_map($helpfuntion3, $DB->get_records_sql($categories_sql)));
            if (empty($maincourses)) {  $maincourses = get_config('local_lsf_unification', 'defaultcategory');  }
            $categories_sql = "SELECT id, name FROM ".$CFG->prefix."course_categories WHERE id in (".$maincourses.") OR parent in (".$maincourses.") ORDER BY sortorder";
        }
        $categories = array_map($helpfuntion2, $DB->get_records_sql($categories_sql));
    }
    if ($update_helptables_if_necessary && (count($categories) == 0)) {
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
 */
function insert_missing_helptable_entries($debugoutput=false, $tryeverything=false) {
    $a = 1;
    global $pgDB, $DB;
    $list1 = "";
    $list2 = "";
    $records1 = $DB->get_recordset('local_lsf_category', null, '', 'ueid');
    $records2 = $DB->get_recordset('local_lsf_categoryparenthood', null, '', 'child, parent');
    $records1_unique = array();
    $records2_unique = array();
    foreach ($records1 as $record1) $records1_unique[$record1->ueid]=true;
    foreach ($records2 as $record2) $records2_unique[$record2->child][$record2->parent]=($tryeverything === false);

    $q_main = pg_query($pgDB->connection, "SELECT ueid, uebergeord, uebergeord, quellid, txt, zeitstempel FROM ". HIS_UEBERSCHRIFT ." ".((!empty($tryeverything))?("WHERE ueid >= '".$tryeverything."'"):""));
    while ($hislsf_title = pg_fetch_object($q_main)) {
        if (!isset($records1_unique[$hislsf_title->ueid]) || 
                  (!isset($records2_unique[$hislsf_title->ueid][$hislsf_title->uebergeord]) || 
                        $records2_unique[$hislsf_title->ueid][$hislsf_title->uebergeord] != true)) {
            $a++;echo $hislsf_title->ueid." ";
        }
        if (!isset($records1_unique[$hislsf_title->ueid])) {
            // create match-table-entry if not existing
            $entry = new stdClass();
            $entry->ueid = $hislsf_title->ueid;
            $entry->parent = empty($hislsf_title->uebergeord)?($hislsf_title->ueid):($hislsf_title->uebergeord);
            $entry->origin = find_origin_category($hislsf_title->ueid);
            $entry->mdlid = 0;
            $entry->timestamp = strtotime($hislsf_title->zeitstempel);
            $entry->txt = utf8_encode($hislsf_title->txt);
            if ($debugoutput) echo "!";
            try {
                $DB->insert_record("local_lsf_category", $entry, true);
                $records1_unique[$hislsf_title->ueid] = true;
                if ($debugoutput) echo "x";
            } catch(Exception $e) {
                try {
                    $entry->txt = utf8_encode(delete_bad_chars($hislsf_title->txt));
                    $DB->insert_record("local_lsf_category", $entry, true);
                    $records1_unique[$hislsf_title->ueid] = true;
                    if ($debugoutput) echo "x";
                } catch(Exception $e) {
                    if ($debugoutput) print("<pre>FEHLER1 ".print_r($e,true)."".print_r($DB->get_last_error(),true));
                }
            }
        }
        if (!isset($records2_unique[$hislsf_title->ueid][$hislsf_title->uebergeord]) || $records2_unique[$hislsf_title->ueid][$hislsf_title->uebergeord] != true) {
            // create parenthood-table-entry if not existing
            $child = $hislsf_title->ueid;
            $ueid = $hislsf_title->ueid;
            $parent = $hislsf_title->ueid;
            $fullname = "";
            $distance = 0;
            do {
                $ueid = $parent;
                $distance++;
                $q2 = pg_query($pgDB->connection, "SELECT ueid, uebergeord, txt FROM ". HIS_UEBERSCHRIFT ." WHERE ueid = '".$ueid."'");
                if (($hislsf_title2 = pg_fetch_object($q2)) && ($hislsf_title2->uebergeord != $ueid)) {
                    $parent = $hislsf_title2->uebergeord;
                    $fullname = ($hislsf_title2->txt).(empty($fullname)?"":("/".$fullname));
                    if (!empty($parent) && !isset($records2_unique[$child][$parent])) {
                        try {
                            $entry = new stdClass();
                            $entry->child = $child;
                            $entry->parent = $parent;
                            $entry->distance = $distance;
                            $DB->insert_record("local_lsf_categoryparenthood", $entry, true);
                            if ($debugoutput) echo "?"; //((
                        } catch(Exception $e) {
                            if ($debugoutput) mtrace("<pre>FEHLER2 ".print_r($e,true)."".print_r($DB->get_last_error(),true),'');
                        }
                    }
                    $records2_unique[$child][$parent] = true;
                }
            } while (!empty($parent) && ($ueid != $parent));
            $entry = $DB->get_record('local_lsf_category', array("ueid"=>$hislsf_title->ueid));
            $entry->txt2 = utf8_encode($fullname);
            try {
                $DB->update_record('local_lsf_category', $entry, true);
            } catch(Exception $e) {
                try {
                    $entry->txt2 = delete_bad_chars($entry->txt2);
                    $DB->update_record('local_lsf_category', $entry, true);
                } catch(Exception $e) {
                    if ($debugoutput) mtrace("<pre>FEHLER2 ".print_r($e,true)."".print_r($DB->get_last_error(),true),'');
                }
            }
        }
        if ($debugoutput && (($a % 101) == 0)) {
            mtrace("<br>&nbsp;&nbsp;"); $a++;
            flush();
        }
    }
}


/**
 * delete_bad_chars is NOT a required function for the lsf_unification plugin, it is used internally only
 *
 * @param $str
 * @return $str
 */
function delete_bad_chars($str) {
    return strtr(utf8_encode($str), array(
                    "\xc2\x96" => "",	// EN DASH
                    "\xc2\x97" => "",	// EM DASH
                    "\xc2\x84" => ""	// DOUBLE LOW-9 QUOTATION MARK
    ));
}

/**
 * returns a list of (newest copies of) children to a parents (and the parent's copies)
 */
function get_newest_sublevels($origins) {
    global $DB, $CFG;
    $helpfuntion1 = function($array_el) {
        return $array_el->ueid;
    };
    // get all copies of current category
    $origins_sql = "SELECT ueid FROM ".$CFG->prefix."local_lsf_category WHERE origin in (".$origins.")";
    $copies = implode(", ", array_map($helpfuntion1, $DB->get_records_sql($origins_sql)));
    // get all their childcategories, that newest copy is not older than 2 years
    $sublevels_all_sql = "SELECT * FROM (SELECT max(ueid) as max_ueid, origin FROM ".$CFG->prefix."local_lsf_category WHERE parent in (".$copies.") AND ueid not in (".$origins.") GROUP BY origin) AS a JOIN ".$CFG->prefix."local_lsf_category ON a.max_ueid = ".$CFG->prefix."local_lsf_category.ueid ";
    $sublevels_young_sql = $sublevels_all_sql."WHERE ".$CFG->prefix."local_lsf_category.timestamp >= (".(time() - 2 * 365 * 24 * 60 * 60).") ORDER BY txt";
    $result = $DB->get_records_sql($sublevels_young_sql);
    // get all their childcategories, if there is no childcategory with a copy, that is not older than 2 years
    if (empty($result)) {
        $result = $DB->get_records_sql($sublevels_all_sql."ORDER BY txt");
    }
    return $result;
}

/**
 * returns if a category has children
 */
function has_sublevels($origins) {
    global $CFG, $DB;
    $sublevels_sql = "SELECT id FROM ".$CFG->prefix."local_lsf_category WHERE parent in (".$origins.") AND ueid not in (".$origins.")";
    return (count($DB->get_records_sql($sublevels_sql)) > 0);
}

/**
 * returns the newest copy to a given id
 */
function get_newest_element($id) {
    global $CFG, $DB;
    $origins = $DB->get_record("local_lsf_category", array("ueid"=>$id), "origin")->origin;
    $sublevels_sql = "SELECT max(ueid) as max_ueid, origin FROM ".$CFG->prefix."local_lsf_category WHERE origin in (".$origins.") GROUP BY origin";
    $sublevels = $DB->get_records_sql($sublevels_sql);
    $ueid = array_shift($sublevels)->max_ueid;
    return $DB->get_record("local_lsf_category", array("ueid"=>$ueid));
}

/**
 * returns the parent of the newest copy to the given id
 */
function get_newest_parent($id) {
    global $CFG, $DB;
    $parent = get_newest_element($id)->parent;
    return $DB->get_record("local_lsf_category", array("ueid"=>$parent));
}

/**
 * returns the moodle-id given to a lsf-id
 */
function get_mdlid($id) {
    global $CFG, $DB;
    $origin = $DB->get_record("local_lsf_category", array("ueid"=>$id), "origin")->origin;
    $mdlid = $DB->get_record("local_lsf_category", array("ueid"=>$origin), "mdlid")->mdlid;
    return $mdlid;
}

/**
 * returns the moodle-name given to a lsf-id
 */
function get_mdlname($id) {
    global $CFG, $DB;
    $origin = $DB->get_record("local_lsf_category", array("ueid"=>$id), "origin")->origin;
    $mdlid = $DB->get_record("local_lsf_category", array("ueid"=>$origin), "mdlid")->mdlid;
    $cat = $DB->get_record("course_categories", array("id"=>$mdlid), "name");
    return $cat->name;
}

/**
 * sets a category-mapping
 */
function set_cat_mapping($ueid, $mdlid) {
    global $DB, $SITE;
    $obj = $DB->get_record("local_lsf_category",array("ueid"=>$ueid));
    $event = \local_lsf_unification\event\matchingtable_updated::create(array(
            'objectid' => $obj->id,
            'context' => context_system::instance(0, IGNORE_MISSING),
            'other' => array('mappingold' => $obj->mdlid, 'mappingnew' => $mdlid, 'originid' => $ueid)
    ));
    $event->trigger();
    $obj->mdlid = $mdlid;
    $DB->update_record("local_lsf_category", $obj);
}

/**
 * returns a list of the topmost elements in the lsf-category hierarchy
 */
function get_his_toplevel_originids() {
    global $DB, $CFG;
    $helpfuntion1 = function($array_el) {
        return $array_el->origin;
    };
    $origins_sql = "SELECT origin FROM ".$CFG->prefix."local_lsf_category WHERE ueid = origin AND parent = ueid";
    return array_map($helpfuntion1, $DB->get_records_sql($origins_sql));
}

/**
 * returns a list of the topmost elements in the mdl-category hierarchy
 */
function get_mdl_toplevels() {
    global $DB, $CFG;
    $maincategories_sql = "SELECT id, name FROM ".$CFG->prefix."course_categories WHERE parent=0 ORDER BY sortorder";
    return $DB->get_records_sql($maincategories_sql);
}

/**
 * returns a list of children to a given parent.id in the mdl-category hierarchy
 */
function get_mdl_sublevels($mainid) {
    global $DB, $CFG;
    $subcats_sql = "SELECT id, name, path FROM ".$CFG->prefix."course_categories WHERE path LIKE '/".$mainid."/%' OR id=".$mainid." ORDER BY sortorder";
    return $DB->get_records_sql($subcats_sql);
}
