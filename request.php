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
 * Main page of lsf_unification.
 *
 * @package   local_lsf_unification
 * @copyright 2025 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use local_lsf_unification\output\first_overview;

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $DB, $PAGE, $SESSION, $OUTPUT;

require_once($CFG->dirroot . '/local/lsf_unification/lib_features.php');
require_once($CFG->dirroot . '/local/lsf_unification/request_form.php');
require_once($CFG->dirroot . '/lib/outputlib.php');

$veranstid = optional_param('veranstid', null, PARAM_INT);
$questionsanswered = optional_param('questionsanswered', null, PARAM_INT);
$courseid = optional_param('courseid', 1, PARAM_INT);
$teachername = optional_param('teachername', "", PARAM_ALPHANUMEXT);
$accept = optional_param('accept', null, PARAM_INT);
$answer = optional_param('answer', null, PARAM_INT);
$requestid = optional_param('requestid', null, PARAM_INT);

if (!empty($answer) && !empty($requestid)) {
    $PAGE->set_url('/local/lsf_unification/request.php', ["answer" => $answer, "requestid" => $requestid]);
} else if (!empty($answer) && !empty($veranstid)) {
    $PAGE->set_url('/local/lsf_unification/request.php', ["answer" => $answer, "veranstid" => $veranstid]);
} else {
    $PAGE->set_url('/local/lsf_unification/request.php');
}

// Where we came from. Used in a number of redirects.
$returnurl = $CFG->wwwroot . '/course/index.php';


// Check permissions.
require_login();
if (isguestuser()) {
    throw new moodle_exception('guestsarenotallowed', '', $returnurl);
}
if (empty($CFG->enablecourserequests)) {
    throw new moodle_exception('courserequestdisabled', '', $returnurl);
}
$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_secondary_navigation(false);
require_capability('moodle/course:request', $context);

$strtitle = get_string('courserequest');
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->navbar->add($strtitle);
echo $OUTPUT->header();

if (!empty($requestid)) {
    if (($request = $DB->get_record("local_lsf_course", ["id" => $requestid])) && ($request->requeststate == 1)) {
        $veranstid = $request->veranstid;
    }
}

/**
 * Print first overview that user sees when interacting with the plugin.
 * @return void
 * @throws \core\exception\coding_exception
 */
function print_first_overview() {
    global $PAGE;
    global $USER;
    $courselist = array_filter(
        get_teachers_course_list($USER->username, true),
        function ($course) {
            return !course_exists($course->veranstid);
        }
    );
    $output = $PAGE->get_renderer('core');
    echo $output->render(new first_overview($courselist));
}

/**
 * Print helptext.
 * @param $t
 * @param $s
 * @return void
 * @throws coding_exception
 */
function print_helptext($t, $s = null) {
    $answerstr = get_string('answer_' . $t, 'local_lsf_unification');
    $infostr = get_string('info_' . $t, 'local_lsf_unification', $s, true);
    echo "<u>" . $answerstr . "</u><br>" . $infostr;
    echo "<br><a href='request.php'>" . get_string('back', 'local_lsf_unification') . "</a>";
}

/**
 * Print the courses the user can see.
 * @return void
 * @throws coding_exception
 */
function print_courseselection() {
    global $USER, $answer;
    echo "<form name='input' action='request.php' method='post'><input type='hidden' name='answer' value='" . $answer . "'>";
    print_coursetable($USER->username);
    echo "<input type='submit' value='" . get_string('select', 'local_lsf_unification') . "'/></form>";
    echo "<br><a href='request.php'>" . get_string('back', 'local_lsf_unification') . "</a>";
}

/**
 * Print the course table for a teacher.
 * @param $teacher
 * @param $appendix
 * @return void
 * @throws coding_exception
 */
function print_coursetable($teacher, $appendix = "") {
    echo "<table><tr><td colspan='2'><b>" . get_string('choose_course', 'local_lsf_unification') . "</b></td></tr>";
    foreach (get_teachers_course_list($teacher, true) as $course) {
        if (!course_exists($course->veranstid)) {
            $veranstid = $course->veranstid;
            echo "<tr>
                <td>
                    <input type='radio' name='veranstid' id='veranstid_" . ($veranstid) . "' value='" . ($veranstid) . "'>
                </td>
                <td>
                    <label for='veranstid_" . ($veranstid) . "'>" . ($course->info) . "</label>
                </td>
            </tr>";
        }
    }
    echo $appendix . "</table>";
}

/**
 * Final print.
 * @return void
 * @throws coding_exception
 */
function print_final() {
    global $OUTPUT, $CFG, $courseid;
    $nextsteps = get_string('next_steps', 'local_lsf_unification');
    $linktext = [
        'users' => get_string('linktext_users', 'local_lsf_unification'),
        'content' => get_string('linktext_content', 'local_lsf_unification'),
        'course' => get_string('linktext_course', 'local_lsf_unification'),
    ];
    $href = [
        0 => $CFG->wwwroot . "/user/index.php?id=" . ($courseid),
        1 => $CFG->wwwroot . "/backup/import.php?id=" . ($courseid),
        2 => $CFG->wwwroot . "/course/view.php?id=" . ($courseid),
    ];
    echo $OUTPUT->box("<b>" . $nextsteps . ":</b><br><a href='" . $href[0] . "'>" . $linktext['users'] . "</a><br>
    <a href='" . $href[1] . "'>" . $linktext['content'] . "</a><br>
    <a href='" . $href[2] . "'>" . $linktext['course'] . "</a><br>&nbsp;");
}

/**
 * Print result selection.
 * @return void
 * @throws coding_exception
 * @throws dml_exception
 */
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
    } else if (!empty($templatefiles)) {
        $alternativecounter = 1;

        // Continue with the course template.
        if (get_config('local_lsf_unification', 'restore_templates') && !empty($templatefiles)) {
            $cats = [];
            $i = 0;
            foreach ($templatefiles as $id => $fileinfo) {
                $cats[$fileinfo->category][$id] = $fileinfo;
            }
            // If there are items without a category move them to the end.
            $catkeys = array_keys($cats);
            if (!empty($cats) && array_pop($catkeys) == "") {
                array_unshift($cats, array_pop($cats));
            }
            // Render.
            $pretemplate = get_string('pre_template', 'local_lsf_unification', $alternativecounter++);
            echo "<b>" . $pretemplate . '</b><ul style="list-style-type:none">';
            foreach ($cats as $name => $catfiles) {
                if (!empty($name)) {
                    // LEARNWEB-TODO: move the two js-lines to a new file.
                    $jscheck = "(document.getElementById('reslist" . $i . "').style.display == 'none') ? 'block' : 'none'";
                    $js = "document.getElementById('reslist" . ($i) . "').style.display=(" . $jscheck . ");";

                    $exp = "'url(" . $OUTPUT->image_url("t/expanded")->out() . ")'";
                    $col = "'url(" . $OUTPUT->image_url("t/collapsed")->out() . ")'";
                    $js2check = "(document.getElementById('reslist" . $i . "').style.display == 'none') ? " . $col . ":" . $exp;
                    $js2 = "document.getElementById('reslistselector" . ($i) . "').style.listStyleImage=(" . $js2check . ");";
                    $alement = '<a onclick="' . $js  . $js2 . '" style="cursor:default">[<b>' . $name . '</b>]</a>';
                    $reslist = '</b><ul id="reslist' . $i . '" style="display:none">';
                    echo '<li style="list-style-image: url(' . $col . ')" id="reslistselector' . ++$i . '">' . $alement . $reslist;
                }
                foreach ($catfiles as $id => $fileinfo) {
                    $lines = explode("\n", trim($fileinfo->info, " \t\r\n"), 2);
                    $text = mb_convert_encoding($lines[0], 'UTF-8', 'ISO-8859-1');
                    $imgnavitem = $OUTPUT->image_url("i/navigationitem")->out();
                    $href = "duplicate_course.php?courseid=" . $courseid . "&filetype=t&fileid=" . $id;
                    echo '<li style="list-style-image: url(' . $imgnavitem . ')"><a href="' . $href . '">' . $text . "</a>";
                    if (count($lines) == 2) {
                        echo "<br/>" . mb_convert_encoding($lines[1], 'UTF-8', 'ISO-8859-1');
                    }
                    echo "</li>";
                }
                if (!empty($name)) {
                    echo "</ul></li>";
                }
            }
            echo "</ul>";
        }

        // Continue with a blank course.
        $notemplate = get_string('no_template', 'local_lsf_unification', $alternativecounter++);
        $emptycourse = get_string('continue_with_empty_course', 'local_lsf_unification');
        $href = "request.php?courseid=" . $courseid . "&answer=7";
        echo "<b>" . $notemplate . "</b><ul><li><a href=" . $href . ">" . $emptycourse . "</a></li></ul>";

        // Duplicate course from the course .
        if (get_config('local_lsf_unification', 'restore_old_courses') && !empty($backupfiles)) {
            echo "<b>" . get_string('template_from_course', 'local_lsf_unification', $alternativecounter++) . "</b><ul>";
            // Sortiere die Backups alphabetisch.
            uasort($backupfiles, function ($a, $b) {
                return strcmp($a->course->fullname, $b->course->fullname);
            });
            foreach ($backupfiles as $id => $fileinfo) {
                $href = "duplicate_course.php?courseid=" .  $courseid . "&filetype=b&fileid=" . $id;
                echo "<li><a href='" . $href . "'>" . $fileinfo->course->fullname . " (" . $fileinfo->datetime . ")</a></li>";
            }
            echo "</ul>";
        }
    }
}

/**
 * Prints the remote course creation process.
 * @return void
 * @throws coding_exception
 * @throws dml_exception
 */
function print_remote_creation() {
    global $USER, $answer, $teachername, $veranstid;
    if (!get_config('local_lsf_unification', 'remote_creation')) {
        return;
    }
    if (empty($veranstid)) {
        echo "<form name='input' action='request.php' method='post'><input type='hidden' name='answer' value='" . $answer . "'>";
        if (empty($teachername)) {
            $chooseteacher = get_string('choose_teacher', 'local_lsf_unification');
            echo "<b>" . $chooseteacher . "</b><input type='text' name='teachername' size='20' value='" . $teachername . "'>";
        } else {
            echo "<input type='hidden' name='teachername' value='" . $teachername . "'>";
            $answercourselsf = get_string('answer_course_in_lsf_but_invisible', 'local_lsf_unification', $teachername);
            $appendix = "
                <tr>
                    <td>
                        <input type='radio' name='veranstid' id='veranstid_' value='" . (-1) . "'>
                    </td>
                    <td>
                        <label for='veranstid_'>" . $answercourselsf . "</label>
                    </td>
                </tr>";
            print_coursetable($teachername, $appendix);
        }
        $select = get_string('select', 'local_lsf_unification');
        $back = get_string('back', 'local_lsf_unification');
        echo "<input type='submit' value='" . $select . "'/></form><br><a href='request.php'>" . $back . "</a>";
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

/**
 * Prints the course creation process.
 * @return void
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function print_coursecreation() {
    global $CFG, $USER, $DB, $OUTPUT, $answer, $teachername, $veranstid, $courseid;
    $editform = new lsf_course_request_form(null, ['veranstid' => $veranstid]);
    if (!($editform->is_cancelled()) && ($data = $editform->get_data())) {
        // Dbenrolment enrolment can only be enabled if it is globally enabled.
        $extenabledglobal = get_config('local_lsf_unification', 'enable_enrol_ext_db') == true;
        $enabledbenrolment = $extenabledglobal ? $data->dbenrolment == 1 : false;
        // Enable self enrolment if dbenrolment is disabled globally.
        $enableselfenrolment = !$extenabledglobal ? true : ($data->selfenrolment == 1);

        $result = create_lsf_course(
            $veranstid,
            $data->fullname,
            $data->shortname,
            $data->summary,
            $data->startdate,
            $enabledbenrolment,
            $enableselfenrolment,
            empty($data->enrolment_key) ? "" : ($data->enrolment_key),
            $data->category
        );
        $courseid = $result['course']->id;
        $event = \local_lsf_unification\event\course_imported::create([
            'objectid' => $courseid,
            'context' => context_system::instance(0, IGNORE_MISSING),
            'other' => $veranstid,
        ]);
        $event->trigger();
        if (!empty($data->category_wish)) {
            if (!empty($CFG->supportemail)) {
                $emailsucc = "\n" . get_string('email_success', 'local_lsf_unification');
                $emailerr = "\n" . get_string('email_error', 'local_lsf_unification');
                $result['warnings'] .= (send_support_mail($result['course'], $data->category_wish) ? ($emailsucc) : ($emailerr));
            }
        }
        // Update customfield.
        $coursecontext = \context_course::instance($courseid);
        if ($field = $DB->get_record('customfield_field', ['shortname' => 'semester', 'type' => 'semester'])) {
            $fieldcontroller = \core_customfield\field_controller::create($field->id);
            $datacontroller = \core_customfield\data_controller::create(0, null, $fieldcontroller);
            $datacontroller->set('instanceid', $courseid);
            $datacontroller->set('contextid', $coursecontext->id);
            $datacontroller->instance_form_save($data);
        }
        $warnings = get_string('warnings', 'local_lsf_unification');
        $out = "<p>" . $OUTPUT->box("<b>" . $warnings . "</b><br>" . "<pre>" . $result["warnings"] . "<pre>") . "</p>";
        echo (!empty($result["warnings"])) ? $out : "";
        print_final($result["course"]->id);
    } else {
        $editform->display();
    }
}

/**
 * Shows the request handler.
 * @return void
 * @throws coding_exception
 * @throws dml_exception
 */
function print_request_handler() {
    global $CFG, $DB, $answer, $request, $veranstid, $accept;
    $course = get_course_by_veranstid($veranstid);
    $requester = $DB->get_record("user", ["id" => $request->requesterid]);
    if (empty($accept)) {
        echo get_string('remote_request_select_alternative', 'local_lsf_unification');
        $params = new stdClass();
        $params->a = $requester->firstname . " " . $requester->lastname;
        $params->b = mb_convert_encoding($course->titel, 'UTF-8', 'ISO-8859-1');
        $href = $CFG->wwwroot . "/local/lsf_unification/request.php?answer=" . $answer . "&requestid=" . $request->id;
        $remoteaccept = get_string('remote_request_accept', 'local_lsf_unification', $params);
        $remotedeclined = get_string('remote_request_decline', 'local_lsf_unification', $params);
        echo '<p>' .
            "<a href='" . $href . "&accept=1'>" . $remoteaccept . "</a>" . "<br>";
        echo "<a href='" . $href . "&accept=2'>" . $remotedeclined . "</a>" . "<br>";
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


// Handle Course-Request.

if (establish_secondary_DB_connection() === true) {
    if (empty($answer)) {
        print_first_overview(); // Task Selection.
    } else if ($answer == 1) {
        if (empty($veranstid)) {
            print_courseselection(); // Extern Course Selection.
        } else {
            if (has_course_import_rights($veranstid, $USER)) { // Validate veranstid, user.
                print_coursecreation(); // Request neccessary details and create course.
            }
        }
    } else if ($answer == 2) {
        print_helptext('course_not_created_yet');
    } else if ($answer == 3) {
        print_helptext('course_in_lsf_and_visible', $USER->username);
    } else if ($answer == 6) {
        print_helptext('goto_old_requestform');
    } else if ($answer == 7) {
        print_final(); // Goto Course.
    } else if ($answer == 11) {
        print_remote_creation(); // Remote Course Creation Starter.
    } else if ($answer == 12) {
        if (!is_course_of_teacher($veranstid, $USER->username)) { // Validate veranstid, user.
            // The user isn't a teacher of this course, so he shouldn't get here.
            die("Course request not existing, already handled or none of your business");
        }
        print_request_handler(); // Remote Course Creation Request Handler.
    }
    close_secondary_DB_connection();
} else {
    echo get_string('db_not_available', 'local_lsf_unification');
}
echo $OUTPUT->footer();
