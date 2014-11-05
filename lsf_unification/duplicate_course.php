<?php
require_once("../../config.php");
require_once("$CFG->libdir/adminlib.php");
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

$strtitle = get_string('courserequest');
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->navbar->add($strtitle);
echo $OUTPUT->header();

$courseid   = required_param('courseid', PARAM_INT);
$acceptorid = get_course_acceptor($courseid);
$files_backups = get_backup_files($acceptorid);
$files_templates = get_template_files();
$filetype  	= optional_param('filetype', null, PARAM_RAW);
$fileid  	= optional_param('fileid', null, PARAM_RAW);
$fileinfo	= null;
if (!in_array($courseid,get_my_courses_as_teacher()) && !is_course_imported_by($courseid, $USER)) die("context not found");
$course = $DB->get_record('course', array("id"=>$courseid));
if (time() - $course->timecreated > 60 * 60 * get_config('local_lsf_unification', 'duplication_timeframe')) {
    echo "<b>".get_string('duplication_timeframe_error','local_lsf_unification',get_config('local_lsf_unification', 'duplication_timeframe'))."</b><br>";
} else {
    if (!empty($fileid)) {
        // get rights
        $creatorroleid = $DB->get_record('role', array('shortname' => 'lsfunificationcourseimporter'))->id;
        $context = context_course::instance($courseid, MUST_EXIST);
        if (!enrol_try_internal_enrol($course->id, $USER->id, $creatorroleid)){
            die("error ##");
        }
        // do backup
        if ($filetype == "t" && get_config('local_lsf_unification', 'restore_templates')) {
            if (empty($files_templates[$fileid])) die("error #0");
            $fileinfo = $files_templates[$fileid];
        } elseif ($filetype == "b" && get_config('local_lsf_unification', 'restore_old_courses')) {
            if (empty($files_backups[$fileid])) die("error #0");
            $fileinfo = $files_backups[$fileid];
        } else {
            die("error #x");
        }
        $tmpdir = $CFG->tempdir . '/backup';
        $foldername = restore_controller::get_tempdir_name(empty($fileinfo->course)?42:$fileinfo->course->id, $USER->id);
        $pathname = $tmpdir.'/'.$foldername;
        if (is_dir($pathname)) die("error #1");
        if (!mkdir($pathname,0777,true)) die("error #2: ".$pathname);
        if (!copy($fileinfo->path."/".$fileinfo->name, $pathname."/".$fileinfo->name)) die("error #3");
        if (!lsf_unification_unzip($pathname."/".$fileinfo->name, $pathname)) die("error #4");
        restore_dbops::delete_course_content($courseid, array("keep_roles_and_enrolments" => true));
        //log is deleted by restore_dbops::delete_course_content
        $event = \local_lsf_unification\event\course_duplicated::create(array(
                'objectid' => $courseid,
                'context' => context_system::instance(0, IGNORE_MISSING),
                'other' => empty($fileinfo->course)?('template_'.$fileinfo->name):$fileinfo->course->id
        ));
        $event->trigger();
        duplicate_course($courseid, $foldername);
        // dump rights
        role_unassign($creatorroleid, $USER->id, $context->id);
    }
}
echo "<a href='request.php?courseid=".$courseid."&answer=7'>".get_string('continue')."</a><br>";

echo $OUTPUT->footer();