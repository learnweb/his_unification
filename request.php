<?php


require_once(dirname(__FILE__) . '/../../config.php');
//require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/local/lsf_unification/lib_features.php');
require_once($CFG->dirroot . '/local/lsf_unification/lib_sap.php');
require_once($CFG->dirroot . '/local/lsf_unification/request_form.php');
require_once($CFG->dirroot . '/local/lsf_unification/requestoverview_form.php');
require_once($CFG->dirroot . '/lib/outputlib.php');

$veranstid = optional_param('veranstid', null, PARAM_ALPHANUM);
$questionsanswered = optional_param('questionsanswered', null, PARAM_INT);
$courseid = optional_param('courseid', 1, PARAM_INT);
$teachername = optional_param('teachername', "", PARAM_ALPHANUMEXT);
$accept = optional_param('accept', null, PARAM_INT);
$answer = optional_param('answer', null, PARAM_INT);
$answer_sap = optional_param('answer_sap', false, PARAM_BOOL);
$requestid = optional_param('requestid', null, PARAM_INT);

if (!empty($answer) && !empty($requestid)) {
    $PAGE->set_url('/local/lsf_unification/request.php', array("answer" => $answer, "requestid" => $requestid));
} else if (!empty($answer) && !empty($veranstid)) {
    $PAGE->set_url('/local/lsf_unification/request.php', array("answer" => $answer, "veranstid" => $veranstid));
} else {
    $PAGE->set_url('/local/lsf_unification/request.php');
}

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
$context = context_system::instance();

$PAGE->set_context($context);
require_capability('moodle/course:request', $context);

$strtitle = get_string('courserequest');
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->navbar->add($strtitle);
echo $OUTPUT->header();

if (!empty($requestid)) {
    if (($request = $DB->get_record("local_lsf_course", array("id" => $requestid))) && ($request->requeststate == 1)) {
        $veranstid = $request->veranstid;
    }
}

function print_first_overview() {
    global $USER;
    if (establish_secondary_DB_connection() === true) {
        $courselist = "<ul>";
        foreach (get_teachers_course_list($USER->username, true) as $course) {
            if (!course_exists($course->veranstid)) {
                $courselist .= "<li>" . $course->info . "</li>";
            }
        }
        $courselist .= "</ul>";
        close_secondary_DB_connection();
    }
    if (establish_secondary_DB_connection_sap() === true) {
        $courselist_sap = "<ul>";
        foreach (get_teachers_course_list_sap($USER->username, true) as $course) {
            if (!course_exists($course->veranstid)) {
                $courselist_sap .= "<li>" . $course->info . "</li>";
            }
        }
        $courselist_sap .= "</ul>";
        close_secondary_DB_connection();
    }
    $editform = new course_overview_request_form(NULL, array('courselist' => $courselist, 'courselist_sap'=>$courselist_sap));
    if (!($editform->is_cancelled()) && ($data = $editform->get_data())) {
        var_dump($data);
    } else {
        $editform->display();
    }
    /*echo "<p>" . get_string('notice', 'local_lsf_unification') . "</p>";
    echo "<form name='input' action='request.php' method='post'><table><tr><td colspan='2'><b>" . get_string('question', 'local_lsf_unification') . "</b>";
    echo "</td></tr>";
    echo ($courselist != "<ul></ul>") ? ("<tr><td style='vertical-align:top;'><input type='radio' name='answer' id='answer1' value='1'></td><td><label for='answer1'>" . get_string('answer_course_found', 'local_lsf_unification') . "" . $courselist . "</label></td></tr>") : "";

    echo "<tr><td><input type='radio' name='answer' id='answer3' value='3'></td><td><label for='answer3'>" . get_string('answer_course_in_lsf_and_visible', 'local_lsf_unification') . "</label></td></tr>";
    if (get_config('local_lsf_unification', 'remote_creation')) {
        echo "<tr><td><input type='radio' name='answer' id='answer11' value='11'></td><td><label for='answer11'>" . get_string('answer_proxy_creation', 'local_lsf_unification') . "</label></td></tr>";
    }
    echo "<tr><td><input type='radio' name='answer' id='answer6' value='6'></td><td><label for='answer6'>" . get_string('answer_goto_old_requestform', 'local_lsf_unification') . "</label></td></tr>";
    echo "<tr><td>&nbsp;</td><td><input type='submit' value='" . get_string('select', 'local_lsf_unification') . "'/></td></tr></table></form>";*/
}

function print_first_overview_sap() {
    global $USER;
    $courselist = "<ul>";
    foreach (get_teachers_course_list_sap($USER->username, true) as $course) {
        if (!course_exists($course->veranstid)) {
            $courselist .= "<li>" . $course->info . "</li>";
        }
    }
    $courselist .= "</ul>";
    echo "<p>" . get_string('notice', 'local_lsf_unification') . "</p>";
    echo "<form name='input_sap' action='request.php' method='post'><table><tr><td colspan='2'><b>" . get_string('question', 'local_lsf_unification') . "</b>";
    echo "</td></tr>";
    echo ($courselist != "<ul></ul>") ? ("<tr><td style='vertical-align:top;'><input type='radio' name='answer_sap' id='answer1_sap' value='true'></td><td><label for='answer1'>" . get_string('answer_course_found', 'local_lsf_unification') . "" . $courselist . "</label></td></tr>") : "";

    //echo "<tr><td><input type='radio' name='answer_sap' id='answer3_sap' value='3'></td><td><label for='answer3_sap'>" . get_string('answer_course_in_lsf_and_visible', 'local_lsf_unification') . "</label></td></tr>";
    if (get_config('local_lsf_unification', 'remote_creation')) {
        echo "<tr><td><input type='radio' name='answer_sap' id='answer11_sap' value='11'></td><td><label for='answer11_sap'>" . get_string('answer_proxy_creation', 'local_lsf_unification') . "</label></td></tr>";
    }
    echo "<tr><td>&nbsp;</td><td><input type='submit' value='" . get_string('select', 'local_lsf_unification') . "'/></td></tr></table></form>";
}

function print_helptext($t, $s = null) {
    echo "<u>" . get_string('answer_' . $t, 'local_lsf_unification') . "</u><br>" . get_string('info_' . $t, 'local_lsf_unification', $s, true);
    echo "<br><a href='request.php'>" . get_string('back', 'local_lsf_unification') . "</a>";
}

function print_courseselection($sap=false) {
    global $USER, $answer, $answer_sap;
    if($sap) {
        echo "<form name='input' action='request.php' method='post'><input type='hidden' name='answer_sap' value='" . $answer_sap . "'>";
    } else {
        echo "<form name='input' action='request.php' method='post'><input type='hidden' name='answer' value='" . $answer . "'>";
    }
    print_coursetable($USER->username, $sap);
    echo "<input type='submit' value='" . get_string('select', 'local_lsf_unification') . "'/></form>";
    echo "<br><a href='request.php'>" . get_string('back', 'local_lsf_unification') . "</a>";
}

function print_coursetable($teacher, $sap=false, $appendix = "") {
    echo "<table><tr><td colspan='2'><b>" . get_string('choose_course', 'local_lsf_unification') . "</b></td></tr>";
    if($sap) {
        $courselist = get_teachers_course_list_sap($teacher, true);
    } else {
        $courselist = get_teachers_course_list($teacher, true);
    }
    foreach ($courselist as $course) {
	var_dump($course->veranstid);
        if (!course_exists($course->veranstid)) {
            echo "<tr><td><input type='radio' name='veranstid' id='veranstid_" . ($course->veranstid) . "' value='" . ($course->veranstid) . "'></td><td><label for='veranstid_" . ($course->veranstid) . "'>" . ($course->info) . "</label></td></tr>";
        }
    }

    echo $appendix . "</table>";
}

function print_final() {
    global $OUTPUT, $CFG, $courseid;
    echo $OUTPUT->box("<b>" . get_string('next_steps', 'local_lsf_unification') . ":</b><br><a href='" . $CFG->wwwroot . "/user/index.php?id=" . ($courseid) . "'>" . get_string('linktext_users', 'local_lsf_unification') . "</a><br>
    <a href='" . $CFG->wwwroot . "/backup/import.php?id=" . ($courseid) . "'>" . get_string('linktext_content', 'local_lsf_unification') . "</a><br>
    <a href='" . $CFG->wwwroot . "/course/view.php?id=" . ($courseid) . "'>" . get_string('linktext_course', 'local_lsf_unification') . "</a><br>&nbsp;");
}

function print_res_selection() {
    global $CFG, $OUTPUT, $courseid;

    $acceptorid = get_course_acceptor($courseid);
    if (get_config('local_lsf_unification', 'restore_old_courses')) {
        $backupfiles = get_backup_files($acceptorid);
    }
    if (get_config('local_lsf_unification', 'restore_templates')) {
        $templatefiles = get_template_files();
    }
    if (empty($backupfiles) && empty($templatefiles)) {
        print_final();
    } elseif (!empty($templatefiles)) {
        $alternative_counter = 1;

        // "Continue with the course template ..."
        if (get_config('local_lsf_unification', 'restore_templates') && !empty($templatefiles)) {
            $cats = array();
            $i = 0;
            foreach ($templatefiles as $id => $fileinfo)
                $cats[$fileinfo->category][$id] = $fileinfo;
            // if there are items without a category move them to the end
            $catkeys = array_keys($cats);
            if (!empty($cats) && array_pop($catkeys) == "")
                array_unshift($cats, array_pop($cats));
            // render
            echo "<b>" . get_string('pre_template', 'local_lsf_unification', $alternative_counter++) . '</b><ul style="list-style-type:none">';
            foreach ($cats as $name => $catfiles) {
                if (!empty($name)) {
                    echo '<li style="list-style-image: url(' . $OUTPUT->image_url("t/collapsed")->out(true) . ')" id="reslistselector' . ++$i . '"><a onclick="' . "document.getElementById('reslist" . ($i) . "').style.display=((document.getElementById('reslist" . $i . "').style.display == 'none') ? 'block' : 'none'); document.getElementById('reslistselector" . ($i) . "').style.listStyleImage=((document.getElementById('reslist" . $i . "').style.display == 'none') ? 'url(" . $OUTPUT->image_url("t/collapsed")->out(true) . ")' : 'url(" . $OUTPUT->image_url("t/expanded")->out(true) . ")');" . '" style="cursor:default">[<b>' . $name . '</b>]</a></b><ul id="reslist' . $i . '" style="display:none">';
                }
                foreach ($catfiles as $id => $fileinfo) {
                    $lines = explode("\n", trim($fileinfo->info, " \t\r\n"), 2);
                    echo '<li style="list-style-image: url(' . $OUTPUT->image_url("i/navigationitem")->out(true) . ')"><a href="duplicate_course.php?courseid=' . $courseid . "&filetype=t&fileid=" . $id . '">' . utf8_encode($lines[0]) . "</a>";
                    if (count($lines) == 2)
                        echo "<br/>" . utf8_encode($lines[1]);
                    echo "</li>";
                }
                if (!empty($name)) echo "</ul></li>";
            }
            echo "</ul>";
        }

        // "Continue with a blank course"
        echo "<b>" . get_string('no_template', 'local_lsf_unification', $alternative_counter++) . "</b><ul><li><a href='request.php?courseid=" . $courseid . "&answer=7'>" . get_string('continue_with_empty_course', 'local_lsf_unification') . "</a></li></ul>";

        // "Duplicate course from the course ..."
        if (get_config('local_lsf_unification', 'restore_old_courses') && !empty($backupfiles)) {
            echo "<b>" . get_string('template_from_course', 'local_lsf_unification', $alternative_counter++) . "</b><ul>";
            // Sortiere die Backups alphabetisch.
            uasort($backupfiles, function ($a, $b) {
                return strcmp($a->course->fullname, $b->course->fullname);
            });
            foreach ($backupfiles as $id => $fileinfo) {
                echo "<li><a href='duplicate_course.php?courseid=" . $courseid . "&filetype=b&fileid=" . $id . "'>" . $fileinfo->course->fullname . " (" . $fileinfo->datetime . ")</a></li>";
            }
            echo "</ul>";
        }
    }
}

function print_remote_creation() {
    global $USER, $answer, $teachername, $veranstid;
    if (!get_config('local_lsf_unification', 'remote_creation')) {
        return;
    }
    if (empty($veranstid)) {
        echo "<form name='input' action='request.php' method='post'><input type='hidden' name='answer' value='" . $answer . "'>";
        if (empty($teachername)) {
            echo "<b>" . get_string('choose_teacher', 'local_lsf_unification') . "</b><input type='text' name='teachername' size='20' value='" . $teachername . "'>";
        } else {
            echo "<input type='hidden' name='teachername' value='" . $teachername . "'>";
            print_coursetable($teachername, "<tr><td><input type='radio' name='veranstid' id='veranstid_' value='" . (-1) . "'></td><td><label for='veranstid_'>" . get_string('answer_course_in_lsf_but_invisible', 'local_lsf_unification', $teachername) . "</label></td></tr>");
        }
        echo "<input type='submit' value='" . get_string('select', 'local_lsf_unification') . "'/></form><br><a href='request.php'>" . get_string('back', 'local_lsf_unification') . "</a>";
    } else {
        if ($veranstid < 0) {
            echo get_string('his_info', 'local_lsf_unification');
        } else {
            $requestid = set_course_requested($veranstid);
            if (!empty($requestid)) {
                if (send_course_request_mail($teachername, get_course_by_veranstid($veranstid), $requestid)) {
                    echo get_string('request_sent', 'local_lsf_unification');
                } else {
                    echo "unkown error";
                }
            } else {
                echo get_string('already_requested', 'local_lsf_unification');
            }
        }
    }
}

function print_coursecreation() {
    global $CFG, $USER, $DB, $OUTPUT, $answer_sap, $teachername, $veranstid, $courseid;
    $editform = new lsf_course_request_form(NULL, array('veranstid' => $veranstid, 'answer_sap' => $answer_sap));
    if (!($editform->is_cancelled()) && ($data = $editform->get_data())) {
        // dbenrolment enrolment can only be enabled if it is globally enabled
        $ext_enabled_global = get_config('local_lsf_unification', 'enable_enrol_ext_db') == true;
        $enable_dbenrolment = $ext_enabled_global ? $data->dbenrolment == 1 : false;
        // enable self enrolment if dbenrolment is disabled globally
        $enable_self_enrolment = !$ext_enabled_global ? true : ($data->selfenrolment == 1);

        $result = create_lsf_course($veranstid, $data->fullname, $data->shortname, $data->summary, $data->startdate, $enable_dbenrolment, $enable_self_enrolment, empty($data->enrolment_key) ? "" : ($data->enrolment_key), $data->category);
        $courseid = $result['course']->id;
        $event = \local_lsf_unification\event\course_imported::create(array(
            'objectid' => $courseid,
            'context' => context_system::instance(0, IGNORE_MISSING),
            'other' => $veranstid
        ));
        $event->trigger();
        if (!empty($data->category_wish)) {
            if (!empty($CFG->supportemail)) {
                $result['warnings'] .= (send_support_mail($result['course'], $data->category_wish) ? ("\n" . get_string('email_success', 'local_lsf_unification')) : ("\n" . get_string('email_error', 'local_lsf_unification')));
            }
        }
        // Update customfield.
        $coursecontext = \context_course::instance($courseid);
        if ($field = $DB->get_record('customfield_field', array('shortname' => 'semester', 'type' => 'semester'))) {
            $fieldcontroller = \core_customfield\field_controller::create($field->id);
            $datacontroller = \core_customfield\data_controller::create(0, null, $fieldcontroller);
            $datacontroller->set('instanceid', $courseid);
            $datacontroller->set('contextid', $coursecontext->id);
            $datacontroller->instance_form_save($data);
        }

        echo (!empty($result["warnings"])) ? ("<p>" . $OUTPUT->box("<b>" . get_string('warnings', 'local_lsf_unification') . "</b><br>" . "<pre>" . $result["warnings"] . "<pre>") . "</p>") : "";
        print_res_selection($result["course"]->id);
    } else {
        $editform->display();
    }
}

function print_request_handler() {
    global $CFG, $DB, $answer, $request, $veranstid, $accept;
    $course = get_course_by_veranstid($veranstid);
    $requester = $DB->get_record("user", array("id" => $request->requesterid));
    if (empty($accept)) {
        echo get_string('remote_request_select_alternative', 'local_lsf_unification');
        $params = new stdClass();
        $params->a = $requester->firstname . " " . $requester->lastname;
        $params->b = utf8_encode($course->titel);
        echo '<p>' .
            '<a href="' . $CFG->wwwroot . '/local/lsf_unification/request.php?answer=' . $answer . '&requestid=' . $request->id . '&accept=1">' . get_string('remote_request_accept', 'local_lsf_unification', $params) . '</a>' .
            '<br>';
        echo '<a href="' . $CFG->wwwroot . '/local/lsf_unification/request.php?answer=' . $answer . '&requestid=' . $request->id . '&accept=2">' . get_string('remote_request_decline', 'local_lsf_unification', $params) . '</a>' .
            '</p>';
    } else {
        if ($accept == 1) {
            set_course_accepted($veranstid);
            send_course_creation_mail($requester, $course);
        } else {
            set_course_declined($veranstid);
            send_sorry_mail($requester, $course);
        }
        echo get_string('answer_sent', 'local_lsf_unification');
    }
}


// Handle Course-Request
        if (empty($answer)) {
            print_first_overview(); // Task Selection
        } elseif ($answer == 1) {
            if (empty($veranstid)) {
                    if (establish_secondary_DB_connection() === true) {
                        print_courseselection();
                    } else {
                        echo get_string('db_not_available', 'local_lsf_unification');
                    }
                 // Extern Course Selection
            } else {
                if (has_course_import_rights($veranstid, $USER)) { // Validate veranstid, user
                    print_coursecreation(); // Request neccessary details and create course
                }
            }
        } elseif ($answer == 2) {
            print_helptext('course_not_created_yet');
        } elseif ($answer == 3) {
            print_helptext('course_in_lsf_and_visible', $USER->username);
        } elseif ($answer == 6) {
            print_helptext('goto_old_requestform');
        } elseif ($answer == 7) {
            print_final(); // Goto Course
        } elseif ($answer == 11) {
            print_remote_creation(); // Remote Course Creation Starter
        } elseif ($answer == 12) {
            if (!is_course_of_teacher($veranstid, $USER->username)) { // Validate veranstid, user
                die("Course request not existing, already handled or none of your business"); // The user isn't a teacher of this course, so he shouldn't get here
            }
            print_request_handler(); // Remote Course Creation Request Handler
        } elseif ($answer_sap){
            if (establish_secondary_DB_connection_sap() === true) {
                if(empty($veranstid)) {
                    print_courseselection(true);
                } else {
                    // if (is_course_of_teacher_sap($veranstid, $USER)) { // Validate veranstid, user
                    print_coursecreation(); // Request neccessary details and create course
                    // }
                }
            } else {
                echo get_string('db_not_available', 'local_lsf_unification');
            }
    } else {
}
        close_secondary_DB_connection();


/*if(establish_secondary_DB_connection_sap() === true) {
    if (!$answer_sap) {
        print_first_overview_sap(); // Task Selection
    } elseif ($answer_sap) {
        if (empty($veranstid)) {
            print_courseselection(true); //
        } else {
            var_dump("in else");
              // if (is_course_of_teacher_sap($veranstid, $USER)) { // Validate veranstid, user
                   print_coursecreation(); // Request neccessary details and create course
              // }
            }
    }
    close_secondary_DB_connection_sap();
} else {
    echo "could not establish sap connection";
}*/
echo $OUTPUT->footer();
