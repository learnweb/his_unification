<?php
/**
 * Functions that are specific to HIS database, format and helptables containing his-formatted data
 */
defined('MOODLE_INTERNAL') || die();
require_once ($CFG->dirroot . '/local/lsf_unification/class_pg_lite_sap.php');

define("SAP_GRUPPE",            "public.ovv_e_title");
define("SAP_GRUPPE_V",          "public.ovv_e_klvl");
define("SAP_V_GRUPPE",          "public.ovv_klvl_e");
define("SAP_GRUPPE_P",          "public.ovv_e_p");
define("SAP_VERANST",           "public.ovv_klvl_title");
define("SAP_VERANST_DETAILS",   "public.ovv_klvl_periods");
define("SAP_VERANST_KOMMENTAR", "public.ovv_klvl_comment");
define("SAP_PERSONAL",          "public.ovv_lehrende");
define("SAP_PERSONAL_LOGIN",    "public.ovv_lehr_email");
define("SAP_VER_PO",      	"public.ovv_klvl_po");


/**
 * establish_secondary_DB_connection is a required function for the lsf_unification plugin
 */
function establish_secondary_DB_connection_sap() {
    global $pgDB;
    if (!empty($pgDB) && !empty($pgDB->connection))
        return;
    $pgDB = new pg_lite_sap();
    if (!($pgDB->connect() === true))
        return false;
    return true;
}

/**
 * close_secondary_DB_connection is a required function for the lsf_unification plugin
 */
function close_secondary_DB_connection_sap() {
    global $pgDB;
    if (empty($pgDB) || empty($pgDB->connection))
        return;
    $pgDB->dispose();
}

function setupHisSoap_sap() {
    global $CFG, $hislsf_soapclient;
    if (!get_config('local_lsf_unification', 'his_deeplink_via_soap'))
        return false;
    if (empty($hislsf_soapclient)) {
        try {
            $hislsf_soapclient = new SoapClient(get_config('local_lsf_unification', 'soapwsdl'));
            $result = $hislsf_soapclient->auth(get_config('local_lsf_unification', 'soapuser'),
                    get_config('local_lsf_unification', 'soappass'));
            $his_moodle_url = get_config('local_lsf_unification', 'moodle_url');
            $result = $result &&
                     $hislsf_soapclient->configureMoodleWKZ(
                            $his_moodle_url . "/course/view.php?id=MOODLEID");
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    return true;
}


/**
 * get_teachers_pid returns the pid (personen-id) connected to a specific username
 *
 * @param $username the teachers username
 * @return $sapid the teachers sapid (personen-id)
 */
function get_teachers_pid_sap($username) {
    global $pgDB;
    $q = pg_query($pgDB->connection,
            "SELECT sapid FROM " . SAP_PERSONAL_LOGIN . " WHERE (login = '" . strtoupper($username) . "')");
    if ($sap_teacher = pg_fetch_object($q)) {
        return $sap_teacher->sapid;
    }
    return null;
}


function get_course_by_veranstid_sap($veranstid) {
    $result = get_courses_by_veranstids_sap(array($veranstid
    ));
    return $result[$veranstid];
}

function get_veranstids_by_teacher_sap($pid) {
    global $pgDB;
    $q = pg_query($pgDB->connection,
            "select * from " . SAP_VER_PO ." where sapid =" . $pid . "and (CURRENT_DATE - CAST(begda_o AS date)) < " . get_config('local_lsf_unification', 'max_import_age_sap') . "order by peryr, perid");
    $courses = array();
    while ($veranst = pg_fetch_object($q)) {
        array_push($courses, $veranst);
    }
    return $courses;
}

function username_to_mail_sap($username) {
    return $username . "@uni-muenster.de";
}

function get_courses_by_veranstids_sap($veranstids) {
    global $pgDB;

    // if veranstids is empty, no need to make a db request. return empty list
    if (empty($veranstids))
        return array();

    $veranstids_string = implode(',', $veranstids);
    // funfact cast(begda as date) - xxx subtracts days from a given yyyy-mm-dd format.
    $q = pg_query($pgDB->connection,
        "SELECT v.objid, d.peryr, d.perid, d.category, v.tabnr, v.tabseqnr, v.tline
        FROM " .
        SAP_VERANST . " as v JOIN" . SAP_VERANST_DETAILS . " as d on v.objid = d.objid
                     where v.objid in (" . $veranstids_string .
        ") AND " . "(CURRENT_DATE - CAST(v.begda AS date)) < " .
        get_config('local_lsf_unification', 'max_import_age_sap') .
        "order by v.begda,v.tline;");
    $result_list = array();
    while ($course = pg_fetch_object($q)) {
        $result = new stdClass();
        $result->veranstid = $course->objid;
        $result->semester = $course->peryr . $course->perid[-1];
        if($course->perid[-1] === "1"){
            $semester = "SoSe";
        } else if($course->perid[-1] === "2") {
            $semester = "WiSe";
        }
        $result->semestertxt = $semester . " " . $course->peryr;
        $result->veranstaltungsart = $course->category;
        $result->titel = $course->tline;
        //$result->urlveranst = $course->urlveranst; TODO
        $result_list[$course->veranstid] = $result;
    }
    return $result_list;
}

function gen_url($course) {
    global $pgDB;
    // TODO make url param, better way to get objid?.
    $baseurl = 'https://service.uni-muenster.de/sap/bc/ui5_ui5/nvias/ccatalog/index.html#/details/' . $course->peryr . '/' . $course->perid . '/';
    $q = pg_query($pgDB->connection,
            "select objid_e, otype_e from " . SAP_V_GRUPPE ." where objid =" . $course->objid);

$group = pg_fetch_object($q);
    //var_dump($group);
    return $baseurl . $group->otype_e . "/" . $group->objid_e;
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
function get_teachers_course_list_sap($username, $longinfo = false) {
    global $pgDB;
    $courselist = array();
    $pid = get_teachers_pid_sap($username);
    //var_dump($pid);
    if (empty($pid)) {
        return $courselist;
    }
    $veranst = get_veranstids_by_teacher_sap($pid);
    foreach ($veranst as $id => $course) {
        $result = new stdClass();
	$url = gen_url($course);
        $result->veranstid = $course->objid;
        $result->info = ($course->stext) . " (" . ($course->perid == 1? "SoSe " : "WiSe ") . $course->peryr . ",<a target='_blank' href=" . $url . "> Link - " . $course->objid . "</a>" . ")";
        // TODO URL und Optional - beschreibung, früher shorttext oder so.
        $courselist[$course->short] = $result;
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
function is_course_of_teacher_sap($veranstid, $username) {
    $courses = get_teachers_course_list_sap($username, false, true);
    return !empty($courses[$veranstid]);
}

