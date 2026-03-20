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
    if (($request = $DB->get_record("local_lsf_unification_course", ["id" => $requestid])) && ($request->requeststate == 1)) {
        $veranstid = $request->veranstid;
    }
}

/**
 * Print first overview that user sees when interacting with the plugin.
 * @return void
 * @throws \core\exception\coding_exception
 */
function print_first_overview(): void {
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
 * @param string $t
 * @param string|null $s
 * @return void
 * @throws coding_exception
 */
function print_helptext(string $t, string|null $s = null): void {
    global $OUTPUT;
    $mustachedata = [
        'answerstr' => get_string('answer_' . $t, 'local_lsf_unification'),
        'infostr' => get_string('info_' . $t, 'local_lsf_unification', $s),
    ];
    echo $OUTPUT->render_from_template('local_lsf_unification/courserequest/helptext', $mustachedata);
}

/**
 * Print the course table for a teacher.
 * @param string $teacher
 * @return void
 * @throws coding_exception
 */
function print_coursetable(string $teacher, array $options = []): void {
    global $OUTPUT, $answer;
    $mustachedata = [
        'courses' => array_values(get_teachers_course_list($teacher, true)),
        'answer' => $answer,
    ];
    $mustachedata = array_merge($mustachedata, $options);
    echo $OUTPUT->render_from_template('local_lsf_unification/courserequest/coursetable', $mustachedata);
}

/**
 * Final print.
 * @return void
 * @throws coding_exception
 */
function print_final() {
    global $OUTPUT, $CFG, $courseid;
    $mustachedata = [
        'userlink' => new moodle_url("/user/index.php", ['id' => $courseid]),
        'backuplink' => new moodle_url("/backup/import.php", ['id' => $courseid]),
        'courselink' => new moodle_url("/course/view.php", ['id' => $courseid]),
    ];
    echo $OUTPUT->render_from_template('local_lsf_unification/courserequest/final', $mustachedata);
}


/**
 * Prints the remote course creation process.
 * @return void
 * @throws coding_exception
 * @throws dml_exception
 */
function print_remote_creation() {
    global $teachername, $veranstid;
    if (!get_config('local_lsf_unification', 'remote_creation')) {
        return;
    }
    if (empty($veranstid)) {
        $mustachedata = [
            'emptyteacher' => empty($teachername),
            'remote' => true,
            'teachername' => $teachername,
            'appendix' => true,
        ];
        print_coursetable($teachername, $mustachedata);
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
        print_final();
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
    global $DB, $answer, $request, $veranstid, $accept, $OUTPUT;
    $course = get_course_by_veranstid($veranstid);
    $requester = $DB->get_record("user", ["id" => $request->requesterid]);
    $basepath = '/local/lsf_unification/request.php';
    $mustachedata = [
        'emptyaccept' => empty($accept),
        'link1' => new moodle_url($basepath, ['answer' => $answer, 'requestid' => $request->id, 'accept' => 1]),
        'link2' => new moodle_url($basepath, ['answer' => $answer, 'requestid' => $request->id, 'accept' => 2]),
        'param_a' => $requester->firstname . " " . $requester->lastname,
        'param_b' => mb_convert_encoding($course->titel, 'UTF-8', 'ISO-8859-1'),
    ];
    if (!empty($accept)) {
        if ($accept == 1) {
            set_course_accepted($veranstid);
            send_course_creation_mail($requester, $course);
        } else {
            set_course_declined($veranstid);
            send_sorry_mail($requester, $course);
        }
    }
    echo $OUTPUT->render_from_template('local_lsf_unification/courserequest/request_handler', $mustachedata);
}

// Handle Course-Request.
if (establish_secondary_DB_connection() === true) {
    if (empty($answer)) {
        print_first_overview(); // Task Selection.
    } else if ($answer == 1) {
        if (empty($veranstid)) {
            print_coursetable($USER->username);
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
