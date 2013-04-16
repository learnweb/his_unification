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
$context = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($context);
require_capability('moodle/course:request', $context);

$strtitle = get_string('courserequest');
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->navbar->add($strtitle);
echo $OUTPUT->header();

$files      = get_backup_files();
$courseid   = required_param('courseid', PARAM_INT);
$fileid  	= optional_param('fileid', null, PARAM_RAW);
if (!in_array($courseid,get_my_courses_as_teacher())) die("context not found");
if (empty($fileid)) {
	echo "<b>".get_string('course_duplication_selection','local_lsf_unification')."</b><br>";
	foreach ($files as $id => $fileinfo) {
		echo "<a href='?courseid=".$courseid."&fileid=".$id."'>".$fileinfo->course->fullname." (".$fileinfo->datetime.")</a><br>";
	}
	echo "<a href='request.php?courseid=".$courseid."&answer=7'>".get_string('skip','local_lsf_unification')."</a><br>";
} else {
	if (empty($files[$fileid])) die("error #0");
	$fileinfo = $files[$fileid];
	$tmpdir = $CFG->tempdir . '/backup';
	$foldername = restore_controller::get_tempdir_name($fileinfo->course->id, $USER->id);
	$pathname = $tmpdir.'/'.$foldername;
	if (is_dir($pathname)) die("error #1");
	if (!mkdir($pathname)) die("error #2");
	if (!copy($fileinfo->path."/".$fileinfo->name, $pathname."/".$fileinfo->name)) die("error #3");
	if (!unzip($pathname."/".$fileinfo->name)) die("error #4");
	restore_dbops::delete_course_content($courseid, array("keep_roles_and_enrolments" => true));
	duplicate_course($courseid, $foldername);
	echo "<a href='request.php?courseid=".$courseid."&answer=7'>".get_string('continue')."</a><br>";
}

echo $OUTPUT->footer();