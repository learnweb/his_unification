<?php
/**
 * Functions that are used by request.php
 **/
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/local/lsf_unification/lib.php');
require_once($CFG->dirroot . '/local/lsf_unification/lib_his.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/course/format/lib.php');

/**
 * creates a course
 * source code very close to course/lib.php: create_course()
 *
 * @param $veranstid idnumber
 * @param $fullname
 * @param $shortname
 * @param $summary
 * @param $startdate
 * @param $database_enrol
 * @param $self_enrol
 * @param $password
 * @param $category
 * @throws moodle_exception
 * @return array consisting of the course-object and warnings
*/
function create_lsf_course($veranstid, $fullname, $shortname, $summary, $startdate, $database_enrol, $self_enrol, $password, $category) {
    global $DB, $USER, $CFG;
    $transaction = $DB->start_delegated_transaction();
    $warnings = "";
    if (course_exists($veranstid)) {
        die("course already exists");
    }
    // create course
    $course = get_default_course($fullname, $veranstid, $summary, $shortname);
    $course->category = empty($category)?(find_or_create_category("HISLSF",null)->id):($category);
    $course->startdate = $startdate;
    // TODO: In future versions, better use `create_course()` in course/lib.php instead of several of the following lines.
    $numsections = isset($course->numsections) ? $course->numsections : 0;
    $course->id = $DB->insert_record('course', $course);
    if ($course->id == false)
        throw new moodle_exception('course not created: '.$DB->get_last_error());
    $course = $DB->get_record("course", array("id" => $course->id));
    // create context
    $context = context_course::instance($course->id);
    // setup default blocks
    blocks_add_default_course_blocks($course);
    // Create default section and initial sections if specified (unless they've already been created earlier).
    // We do not want to call course_create_sections_if_missing() because to avoid creating course cache.
    $existingsections = $DB->get_fieldset_sql('SELECT section from {course_sections} WHERE course = ?', [$course->id]);
    $newsections = array_diff(range(0, $numsections), $existingsections);
    foreach ($newsections as $sectionnum) {
        course_create_section($course->id, $sectionnum, true);
    }

    // enable enrollment
    enable_manual_enrolment($course);

    // enrole creator
    enrol_try_internal_enrol($course->id, $USER->id, get_config('local_lsf_unification', 'roleid_teacher'));
    //enrol_try_internal_enrol($course->id, $USER->id, get_config('local_lsf_unification', 'roleid_teacher'), time() - 1, time() + 60 * 60 * get_config('local_lsf_unification', 'duplication_timeframe'));

    // enrole teachers
    $warnings .= enrole_teachers($veranstid, $course->id);

    // create guest-enrolment
    create_guest_enrolment($course, $enable = FALSE);
    
    // enable enrolment-plugins
    if ($database_enrol) {
        enable_database_enrolment($course);
    }
    if ($self_enrol) {
        enable_self_enrolment($course, $password);
    }

    // create course in helptable
    set_course_created($veranstid, $course->id);
    
    // create deeplink
    if (get_config('local_lsf_unification', 'his_deeplink_via_soap'))
        $warnings .= setHisLink($veranstid,$course->id)? "" : ( (empty($warnings) ? "" : "\n")."Deeplink-Error");

    $transaction->allow_commit();
    return array("course"=>$course,"warnings"=>$warnings);
}

/**
 * sends mail to support regarding category moving wishes
 *
 * @param $course
 * @param $text
 * @return bool
 */
function send_support_mail($course, $text) {
    global $USER;
    $supportuser = get_or_create_support_user();
    $params = new stdClass();
    $params->a = $USER->firstname." ".$USER->lastname;
    $params->b = $USER->id;
    $params->c = utf8_encode($course->fullname);
    $params->d = $course->id;
    $params->e = $text;

    $adhocdata = array('supportuserid' => $supportuser->id, 'requesterfirstname' => $USER->firstname,
        'requesterlastname' => $USER->lastname, 'params' => $params);
    $sendemail = new \local_lsf_unification\task\send_mail_category_wish();
    $sendemail->set_custom_data($adhocdata);
    \core\task\manager::queue_adhoc_task($sendemail);
    return true;
}

/**
 * Queues an adhoc task to send a request for a course creation.
 * @param $recipientusername
 * @param $course
 * @param $requestid
 * @return bool
 */
function send_course_request_mail($recipientusername, $course, $requestid) {
    global $USER;
    $email = username_to_mail($recipientusername);
    $user = get_or_create_user($recipientusername, $email);
    $params = new stdClass();
    $params->a = $USER->firstname." ".$USER->lastname;
    $params->c = utf8_encode($course->titel);

    $data = array('recipientid' => $user->id, 'requesterid' => $USER->id, 'requesterfirstname' => $USER->firstname,
        'requesterlastname' => $USER->lastname, 'requestid' => $requestid, 'params' => $params);
    $sendemail = new \local_lsf_unification\task\send_mail_request_teacher_to_create_course();
    $sendemail->set_custom_data($data);
    \core\task\manager::queue_adhoc_task($sendemail);
    return true;
}

function get_remote_creation_continue_link($veranstid) {
    global $CFG;
    return $CFG->wwwroot.'/local/lsf_unification/request.php?answer=1&veranstid='.$veranstid;
}

/**
 * Queues an adhoc task to send a mail that a requested course was accepted.
 * @param $recipient
 * @param $course
 * @return bool
 */
function send_course_creation_mail($recipient, $course) {
    global $USER;
    $params = new stdClass();
    $params->a = $USER->firstname." ".$USER->lastname;
    $params->c = utf8_encode($course->titel);

    $data = array('recipientid' => $recipient->id, 'acceptorid' => $USER->id, 'acceptorfirstname' => $USER->firstname,
        'acceptorlastname' => $USER->lastname, 'veranstid' => $course->veranstid, 'params' => $params);
    $sendemail = new \local_lsf_unification\task\send_mail_course_creation_accepted();
    $sendemail->set_custom_data($data);
    \core\task\manager::queue_adhoc_task($sendemail);
    return true;
}

/**
 * Queues an adhoc task to send a mail that a requested course was declined.
 * @param $recipient
 * @param $course
 * @return bool
 */
function send_sorry_mail($recipient, $course) {
    global $USER;
    $params = new stdClass();
    $params->a = $USER->firstname." ".$USER->lastname;
    $params->c = utf8_encode($course->titel);

    $data = array('recipientid' => $recipient->id, 'acceptorid' => $USER->id, 'acceptorfirstname' => $USER->firstname,
        'acceptorlastname' => $USER->lastname, 'params' => $params);
    $sendemail = new \local_lsf_unification\task\send_mail_course_creation_declined();
    $sendemail->set_custom_data($data);
    \core\task\manager::queue_adhoc_task($sendemail);
    return true;
}



/*
 * return an array of course's ids where $USER is teacher
*/
function get_my_courses_as_teacher($additionalid = null) {
    global $DB, $USER, $CFG;
    $helpfuntion1 = function($array_el) {
        return $array_el->instanceid;
    };
    $addsql = empty($additionalid)?"":"OR ".$CFG->prefix."role_assignments.userid=$additionalid";
    $sql = "SELECT ".$CFG->prefix."role_assignments.id, instanceid, roleid FROM ".$CFG->prefix."role_assignments JOIN ".$CFG->prefix."context ON ".$CFG->prefix."role_assignments.contextid = ".$CFG->prefix."context.id WHERE ".$CFG->prefix."role_assignments.roleid=".$CFG->creatornewroleid." AND ( ".$CFG->prefix."role_assignments.userid=$USER->id ".$addsql." ) AND ".$CFG->prefix."context.contextlevel=50";
    return array_map($helpfuntion1, $DB->get_records_sql($sql));
}

/*
 * return an array of fileinfo-objects that lists automated backup files of courses tought by $USER
*/
function get_backup_files($additionalid = null) {
    global $DB, $USER;
    //disable restore feature temporarily
    $backuppath = get_config('backup','backup_auto_destination').'';
    $result = array();
    $copies = implode("|", get_my_courses_as_teacher($additionalid));
    if (!($handle = opendir($backuppath))) return $result;
    while (false !== ($entry = readdir($handle))) {
        $matches = array();
        if (preg_match('/^sicherung-moodle2-course-('.$copies.')-(\d{4})(\d{2})(\d{2})-(\d{2})(\d{2})\.mbz$/mi',$entry,$matches)) {
            $file = new stdClass();
            $file->name = $entry;
            $file->path = $backuppath;
            $file->datetime = "$matches[5]:$matches[6] $matches[4].$matches[3].$matches[2]";
            $file->course = $DB->get_record("course", array("id"=>$matches[1]), "id, fullname, shortname");
            $result[md5($entry."_".$USER->id)] = $file;
        }
    }
    closedir($handle);
    return $result;
}

/*
 * return an array of fileinfo-objects that lists template files
*/
function get_template_files() {
    global $DB, $USER;
    //disable restore feature temporarily
    $backuppath = get_config('backup','backup_auto_destination').'/templates';
    $result = array();
    $files = array();
    if (!($handle = opendir($backuppath))) return $result;
    // read filetree
    $filenames = array();
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != ".." && is_dir($backuppath."/".$entry)) {
            $handle2 = opendir($backuppath."/".$entry);
            while (false !== ($entry2 = readdir($handle2))) {
                $filenames[] = $entry."/".$entry2;
            }
        } else {
            $filenames[] = $entry;
        }
    }
    // build file-info objects
    foreach ($filenames as $entry) {
        $matches = array();
        if (preg_match('/^((.+)\/)?template(\d{1,})\.mbz$/mi',$entry,$matches)) {
            $file = new stdClass();
            $file->name = $entry;
            $file->path = $backuppath;
            $file->info = "no info available";
            $file->category = isset($matches[2])?$matches[2]:"";
            $txt_file = $file->path."/".substr($file->name,0,-3)."txt";
            if (file_exists($txt_file)) {
                $file->info = file_get_contents($txt_file);
            }
            $files[$entry] = $file;
        }
    }
    closedir($handle);
    //sort files and prepare output
    ksort($files);
    foreach ($files as $file) {
        $result[md5($file->name."_".$USER->id)] = $file;
    }
    return $result;
}

/*
 * Restores a some course data into a newly created course.
*
* SECURITY WARNING: For the time of the restore process (and only in the context of the target course) the user will be assigned to a role that has the restoring-capability.
*
* @param $courseid target course
* @param $foldername unziped backupfiles
*/
function duplicate_course($courseid, $foldername) {
    global $DB, $USER;
     
    $transaction = $DB->start_delegated_transaction();
     
    try {
        // Get required capability by temporarily assigning a role
       	//$context = context_course::instance($courseid);
        //$roleid = 14;// array_shift(get_roles_with_capability("moodle/restore:restorecourse", CAP_ALLOW ,$context))->id;
        //enrol_try_internal_enrol($courseid, $USER->id, $roleid);

        $USER->access = NULL;
         
        // Init Restore Process
        $controller = new restore_controller($foldername, $courseid,
            backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $USER->id,
            backup::TARGET_EXISTING_ADDING);

        // Restore bachup into course
        $restoresettings = array (
                        'role_assignments' => 0,    // Include user role assignments (default = 1)
                        'activities' => 1,          // Include activities (default = 1)
                        'blocks' => 1,              // Include blocks (default = 1)
                        'filters' => 1,             // Include filters (default = 1)
                        'comments' => 0,            // Include comments (default = 1)
                        'userscompletion' => 0,     // Include user completion details (default = 1)
                        'logs' => 0,                // Include course logs (default = 0)
                        'grade_histories' => 0,      // Include grade history (default = 0)
                        'users' => 0                // Include user data (default = 0)
        );

        foreach ($controller->get_plan()->get_tasks() as $taskindex => $task) {
            if ($taskindex == 0) {
                foreach ($restoresettings as $key => $value) {
                    $settings = $task->get_settings();
                    foreach ($settings as $settingindex => $setting) {
                        if ($setting->get_name() == $key && $setting->get_value() != $value) {
                            $setting->set_value($value);
                        }
                    }
                }
            }
        }
        if ($controller->get_status() == backup::STATUS_REQUIRE_CONV) {
            $controller->convert();
        }
        $controller->execute_precheck();
        $controller->execute_plan();

        // Delete temporary assignment and force capability cache to reload
        $USER->access = NULL;

        // Update SectionCount
        $format = $DB->get_record("course",array("id" => $courseid),"id, format")->format;
        if ($format == "topics" || $format == "weeks") {
            $sectioncount = $DB->count_records("course_sections", array("course" => $courseid));
            $format = course_get_format($courseid);
            $format->update_course_format_options(array("numsections" => ($sectioncount-1)));
        }
        
        // Restore Course Summary
        $DB->update_record("course", (object) array("id" => $courseid, "summaryformat" => 1, "summary" => get_default_summary(get_course_by_veranstid($DB->get_record("course",array("id" => $courseid),"id, idnumber")->idnumber))));

        // Commit
        $transaction->allow_commit();
    } catch (Exception $e) {
        // Oops
        $transaction->rollback($e);

        // Delete temporary assignment and force capability cache to reload
        $USER->access = NULL;
    }
}


function lsf_unification_unzip($zipfile, $destination = '', $showstatus_ignored = true) {
    global $CFG, $USER;
    $fb = get_file_packer('application/vnd.moodle.backup');
    $result = $fb->extract_to_pathname($zipfile,
            $destination, null, null);
    return $result != false;
    return true;
}