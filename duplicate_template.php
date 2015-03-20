<?php
require_once("../../config.php");
require_once("$CFG->libdir/adminlib.php");
require_once("$CFG->dirroot/backup/util/includes/backup_includes.php");
require_once("$CFG->dirroot/backup/util/includes/restore_includes.php");

require_once("./lib_features.php");
require_login();


$PAGE->set_url('/local/lsf_unification/duplicate_course.php');

/// Where we came from. Used in a number of redirects.
$returnurl = $CFG->wwwroot . '/course/index.php';

/// Check permissions.
require_login();
if (isguestuser()) {
    print_error('guestsarenotallowed', '', $returnurl);
}
$context = context_system::instance();
$PAGE->set_context($context);
require_capability('moodle/course:request', $context);

establish_secondary_DB_connection();

$strtitle = get_string('courserequest');
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->navbar->add($strtitle);
echo $OUTPUT->header();

$courseid   = required_param('courseid', PARAM_INT);
$templateid   = required_param('templateid', PARAM_INT);
$templates = get_template_courses();
$creatorroleid = $DB->get_record('role', array('shortname' => 'lsfunificationcourseimporter'))->id;

if (!in_array($courseid,get_my_courses_as_teacher()) && !is_course_imported_by($courseid, $USER)) die("context not found");
$course = $DB->get_record('course', array("id"=>$courseid));
if (time() - $course->timecreated > 60 * 60 * get_config('local_lsf_unification', 'duplication_timeframe')) {
    echo "<b>".get_string('duplication_timeframe_error','local_lsf_unification',get_config('local_lsf_unification', 'duplication_timeframe'))."</b><br>";
} else {
    if (!get_config('local_lsf_unification', 'restore_templates')) {
        die("error #x");
    }
    // get Template
    if (empty($templates[$templateid])) die("error #0");
    $fileinfo = $templates[$templateid];
    // create temp folder
    $tmpdir = $CFG->tempdir . '/backup';
    $foldername = "template_".$fileinfo->courseid."_".$fileinfo->lastupdate;
    $pathname = $tmpdir.'/'.$foldername;
    $backupfile = $pathname.".backup";
    // create backup
    if (!file_exists($backupfile)) {
        try {
            // get role
            if (!enrol_try_internal_enrol($fileinfo->courseid, $USER->id, $creatorroleid)){
                die("error ##2");
            }
            $context_tempalte = context_course::instance($courseid, MUST_EXIST);
            // do backup  //backup::MODE_SAMESITE better performance
            $bc = new backup_controller(backup::TYPE_1COURSE, $fileinfo->courseid, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $USER->id);
            $bc->execute_plan();
            $results = $bc->get_results();
            $results['backup_destination']->copy_content_to($backupfile);
            // cleanup backupcontroller and role
            $bc->destroy();
            unset($bc);
            // final block is supported from php 5.5 onwards
            role_unassign($creatorroleid, $USER->id, $context_tempalte->id);
        } catch (Exception $e) {
            // dump rights
            role_unassign($creatorroleid, $USER->id, $context_tempalte->id);
        }
    }
    // unpack backup
    $foldername = restore_controller::get_tempdir_name($courseid, $USER->id);
    $pathname = $tmpdir.'/'.$foldername;
    lsf_unification_unzip($backupfile,$pathname);
    // get rights
    try {
        $context = context_course::instance($courseid, MUST_EXIST);
        if (!enrol_try_internal_enrol($course->id, $USER->id, $creatorroleid)){
            die("error ##");
        }
        // restore backup
        restore_dbops::delete_course_content($courseid, array("keep_roles_and_enrolments" => true));
        duplicate_course($courseid, $foldername);
        // trigger event
        $event = \local_lsf_unification\event\course_duplicated::create(array(
                'objectid' => $courseid,
                'context' => context_system::instance(0, IGNORE_MISSING),
                'other' => $foldername
        ));
        $event->trigger();
        // final block is supported from php 5.5 onwards
        role_unassign($creatorroleid, $USER->id, $context->id);
    } catch (Exception $e) {
        // dump rights
        role_unassign($creatorroleid, $USER->id, $context->id);
    }
}
echo "<a href='request.php?courseid=".$courseid."&answer=7'>".get_string('continue')."</a><br>";

echo $OUTPUT->footer();
close_secondary_DB_connection();