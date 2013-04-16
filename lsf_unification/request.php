<?php


require_once(dirname(__FILE__) . '/../../config.php');
//require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/local/lsf_unification/lib_features.php');
require_once($CFG->dirroot . '/local/lsf_unification/request_form.php');

$PAGE->set_url('/local/lsf_unification/request.php');

/// Where we came from. Used in a number of redirects.
$returnurl = $CFG->wwwroot . '/course/index.php';


/// Check permissions.
require_login();
if (isguestuser()) {
    print_error('guestsarenotallowed', '', $returnurl);
}
if (empty($CFG->enablecourserequests)) {
    print_error('courserequestdisabled', '', $returnurl);
}
$context = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($context);
require_capability('moodle/course:request', $context);

$strtitle = get_string('courserequest');
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->navbar->add($strtitle);
echo $OUTPUT->header();


$answer = optional_param('answer', null, PARAM_INT);
$veranstid = optional_param('veranstid', null, PARAM_INT);
$questionsanswered = optional_param('questionsanswered', null, PARAM_INT);
$courseid = optional_param('courseid', 1, PARAM_INT);

if (establish_secondary_DB_connection()===true) {
	$username = $USER->username;
	if (empty($answer)) {
		// Ask user if he finds the course he wants to create
		$courselist = "<ul>";
		foreach (get_teachers_course_list($username,true) as $course) {
			if (!course_exists($course->veranstid)) {
				$courselist .= "<li>".$course->info."</li>";
			}
		}
		$courselist .= "</ul>";
		echo "<p>".get_string('notice','local_lsf_unification')."</p>";
		echo "<form name='input' action='request.php' method='post'><table><tr><td colspan='2'><b>".get_string('question','local_lsf_unification')."</b>";
		echo "</td></tr>";
		echo ($courselist != "<ul></ul>")?("<tr><td style='vertical-align:top;'><input type='radio' name='answer' id='answer1' value='1'></td><td><label for='answer1'>".get_string('answer_course_found','local_lsf_unification')."".$courselist."</label></td></tr>"):"";
		
		echo "<tr><td><input type='radio' name='answer' id='answer3' value='3'></td><td><label for='answer3'>".get_string('answer_course_already_created1','local_lsf_unification')."</label></td></tr>";
		echo "<tr><td><input type='radio' name='answer' id='answer6' value='6'></td><td><label for='answer6'>".get_string('answer_goto_old_requestform','local_lsf_unification')."</label></td></tr>";
		echo "<tr><td>&nbsp;</td><td><input type='submit' value='".get_string('select','local_lsf_unification')."'/></td></tr></table></form>";
	} elseif ($answer == 2) {
		// Help Text 1
		echo "<u>".get_string('answer_course_not_created_yet','local_lsf_unification')."</u><br>".get_string('info_course_not_created_yet','local_lsf_unification');
		echo "<br><a href='request.php'>".get_string('back','local_lsf_unification')."</a>";
	} elseif ($answer == 3) {
		// Help Text 2
		echo "<u>".get_string('answer_course_already_created1','local_lsf_unification')."</u><br>".get_string('info_course_already_created1','local_lsf_unification', $username, true);
		echo "<br><a href='request.php'>".get_string('back','local_lsf_unification')."</a>";
	} elseif ($answer == 6) {
		// Help Text 5
		echo "<u>".get_string('answer_goto_old_requestform','local_lsf_unification')."</u><br>".get_string('info_goto_old_requestform','local_lsf_unification');
		echo "<br><a href='request.php'>".get_string('back','local_lsf_unification')."</a>";
	} elseif ($answer == 1) {
		// Course existing in LSF-System -> proceed in process
		if (empty($veranstid)) {
			// Let the user select a specific course
			echo "<form name='input' action='request.php' method='post'><input type='hidden' name='answer' value='".$answer."'><table><tr><td colspan='2'><b>".get_string('choose_course','local_lsf_unification')."</b></td></tr>";
			foreach (get_teachers_course_list($username,true) as $course) {
				if (!course_exists($course->veranstid)) {
					echo "<tr><td><input type='radio' name='veranstid' id='veranstid_".($course->veranstid)."' value='".($course->veranstid)."'></td><td><label for='veranstid_".($course->veranstid)."'>".($course->info)."</label></td></tr>";
				}
			}
			echo "<tr><td>&nbsp;</td><td><input type='submit' value='".get_string('select','local_lsf_unification')."'/></td></tr></table></form>";
			echo "<br><a href='request.php'>".get_string('back','local_lsf_unification')."</a>";
		} else {
			// Check veranstid
			if (!is_veranstid_valid($veranstid, $username)) die("Course cannot be requested.");
			$editform = new lsf_course_request_form(NULL, array('veranstid'=>$veranstid));
			if (!($editform->is_cancelled()) && ($data = $editform->get_data())) {
				$answer = create_lsf_course($veranstid,$data->fullname,$data->shortname,$data->summary,$data->startdate,$data->enrolment_key,$data->category);
				if (!empty($data->category_wish)) $answer['warnings'] .= (send_support_mail($answer['course'], $data->category_wish)?("\n".get_string('email_success','local_lsf_unification')):("\n".get_string('email_error','local_lsf_unification')));
				echo (!empty($answer["warnings"]))?("<p>".$OUTPUT->box("<b>".get_string('warnings','local_lsf_unification')."</b><br>"."<pre>".$answer["warnings"]."<pre>")."</p>"):"";
				$x = get_backup_files();
				if (empty($x)) {
					$courseid = $answer["course"]->id;
					$answer = 7;
				} else {
					echo $OUTPUT->box("<b>".get_string('course_duplication_question','local_lsf_unification')."</b><br><a href='".$CFG->wwwroot."/local/lsf_unification/duplicate_course.php?courseid=".($answer["course"]->id)."'>[".get_string('yes','local_lsf_unification')."]</a><br><a href='".$CFG->wwwroot."/local/lsf_unification/request.php?answer=7&courseid=".($answer["course"]->id)."'>[".get_string('no','local_lsf_unification')."]</a>");
				}
			} else {
				$editform->display();
			}
		}
	}
	if ($answer == 7) {
		echo $OUTPUT->box("<b>".get_string('next_steps','local_lsf_unification').":</b><br><a href='".$CFG->wwwroot."/enrol/users.php?id=".($courseid)."'>".get_string('linktext_users','local_lsf_unification')."</a><br><a href='".$CFG->wwwroot."/course/view.php?id=".($courseid)."'>".get_string('linktext_course','local_lsf_unification')."</a><br>&nbsp;<br><a href='request.php'>".get_string('new_request','local_lsf_unification')."</a>");
	}
	close_secondary_DB_connection();
} else {
	echo get_string('db_not_available','local_lsf_unification');
}
echo $OUTPUT->footer();
