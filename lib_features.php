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
 * Functions that are used by request.php
 *
 * @package     local_lsf_unification
 * @copyright   2025 Tamaro Walter
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
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
 * @param int $veranstid
 * @param string $fullname
 * @param string $shortname
 * @param string $summary
 * @param int $startdate
 * @param bool $databaseenrol
 * @param bool $selfenrol
 * @param string $password
 * @param stdClass $category
 * @return array consisting of the course-object and warnings
 * @throws moodle_exception
 * @package local_lsf_unification
 */
function create_lsf_course(
    int $veranstid,
    string $fullname,
    string $shortname,
    string $summary,
    int $startdate,
    bool $databaseenrol,
    bool $selfenrol,
    string $password,
    stdClass $category
): array {
    global $DB, $USER, $CFG;
    $transaction = $DB->start_delegated_transaction();
    $warnings = "";
    if (course_exists($veranstid)) {
        die("course already exists");
    }
    // Create course.
    $course = get_default_course($fullname, $veranstid, $summary, $shortname);
    $course->category = empty($category) ? (find_or_create_category("HISLSF", null)->id) : ($category);
    $course->startdate = $startdate;
    // LEARNWEB-TODO: In the future, better use `create_course()` in course/lib.php instead of several of the following lines.
    $numsections = isset($course->numsections) ? $course->numsections : 0;
    $course->id = $DB->insert_record('course', $course);
    if ($course->id == false) {
        throw new moodle_exception('course not created: ' . $DB->get_last_error());
    }
    $course = $DB->get_record("course", ["id" => $course->id]);
    // Create context.
    $context = context_course::instance($course->id);
    // Setup default blocks.
    blocks_add_default_course_blocks($course);
    // Create default section and initial sections if specified (unless they've already been created earlier).
    // We do not want to call course_create_sections_if_missing() because to avoid creating course cache.
    $existingsections = $DB->get_fieldset_sql('SELECT section from {course_sections} WHERE course = ?', [$course->id]);
    $newsections = array_diff(range(0, $numsections), $existingsections);
    foreach ($newsections as $sectionnum) {
        course_create_section($course->id, $sectionnum, true);
    }

    // Enable enrollment.
    enable_manual_enrolment($course);

    // Enrole creator.
    enrol_try_internal_enrol($course->id, $USER->id, get_config('local_lsf_unification', 'roleid_teacher'));
    // Enrole teachers.
    $warnings .= enrole_teachers($veranstid, $course->id);

    // Create guest-enrolment.
    create_guest_enrolment($course, $enable = false);

    // Enable enrolment-plugins.
    if ($databaseenrol) {
        enable_database_enrolment($course);
    }
    if ($selfenrol) {
        enable_self_enrolment($course, $password);
    }

    // Create course in helptable.
    set_course_created($veranstid, $course->id);

    // Create deeplink.
    if (get_config('local_lsf_unification', 'his_deeplink_via_soap')) {
        $warnings .= setHisLink($veranstid, $course->id) ? "" : ( (empty($warnings) ? "" : "\n") . "Deeplink-Error");
    }

    $transaction->allow_commit();
    return ["course" => $course, "warnings" => $warnings];
}

/**
 * sends mail to support regarding category moving wishes
 *
 * @param object $course
 * @param string $text
 * @return bool
 */
function send_support_mail(object $course, string $text): bool {
    global $USER;
    $supportuser = get_or_create_support_user();
    $params = new stdClass();
    $params->a = $USER->firstname . " " . $USER->lastname;
    $params->b = $USER->id;
    $params->c = mb_convert_encoding($course->fullname, 'UTF-8', 'ISO-8859-1');
    $params->d = $course->id;
    $params->e = $text;

    $adhocdata = ['supportuserid' => $supportuser->id, 'requesterfirstname' => $USER->firstname,
        'requesterlastname' => $USER->lastname, 'params' => $params];
    $sendemail = new \local_lsf_unification\task\send_mail_category_wish();
    $sendemail->set_custom_data($adhocdata);
    \core\task\manager::queue_adhoc_task($sendemail);
    return true;
}

/**
 * Queues an adhoc task to send a request for a course creation.
 * @param string $recipientusername
 * @param object $course
 * @param int $requestid
 * @return bool
 */
function send_course_request_mail(string $recipientusername, object $course, int $requestid): bool {
    global $USER;
    $email = username_to_mail($recipientusername);
    $user = get_or_create_user($recipientusername, $email);
    $params = new stdClass();
    $params->a = $USER->firstname . " " . $USER->lastname;
    $params->c = mb_convert_encoding($course->titel, 'UTF-8', 'ISO-8859-1');

    $data = ['recipientid' => $user->id, 'requesterid' => $USER->id, 'requesterfirstname' => $USER->firstname,
        'requesterlastname' => $USER->lastname, 'requestid' => $requestid, 'params' => $params];
    $sendemail = new \local_lsf_unification\task\send_mail_request_teacher_to_create_course();
    $sendemail->set_custom_data($data);
    \core\task\manager::queue_adhoc_task($sendemail);
    return true;
}

/**
 * Creates link to request.php with a set veranstid.
 * @param int $veranstid
 * @return string
 *
 */
function get_remote_creation_continue_link(int $veranstid): string {
    global $CFG;
    return $CFG->wwwroot . '/local/lsf_unification/request.php?answer=1&veranstid=' . $veranstid;
}

/**
 * Queues an adhoc task to send a mail that a requested course was accepted.
 * @param object $recipient
 * @param object $course
 * @return bool
 */
function send_course_creation_mail(object $recipient, object $course): bool {
    global $USER;
    $params = new stdClass();
    $params->a = $USER->firstname . " " . $USER->lastname;
    $params->c = mb_convert_encoding($course->titel, 'UTF-8', 'ISO-8859-1');

    $data = ['recipientid' => $recipient->id, 'acceptorid' => $USER->id, 'acceptorfirstname' => $USER->firstname,
        'acceptorlastname' => $USER->lastname, 'veranstid' => $course->veranstid, 'params' => $params];
    $sendemail = new \local_lsf_unification\task\send_mail_course_creation_accepted();
    $sendemail->set_custom_data($data);
    \core\task\manager::queue_adhoc_task($sendemail);
    return true;
}

/**
 * Queues an adhoc task to send a mail that a requested course was declined.
 * @param object $recipient
 * @param object $course
 * @return bool
 */
function send_sorry_mail(object $recipient, object $course): bool {
    global $USER;
    $params = new stdClass();
    $params->a = $USER->firstname . " " . $USER->lastname;
    $params->c = mb_convert_encoding($course->titel, 'UTF-8', 'ISO-8859-1');

    $data = ['recipientid' => $recipient->id, 'acceptorid' => $USER->id, 'acceptorfirstname' => $USER->firstname,
        'acceptorlastname' => $USER->lastname, 'params' => $params];
    $sendemail = new \local_lsf_unification\task\send_mail_course_creation_declined();
    $sendemail->set_custom_data($data);
    \core\task\manager::queue_adhoc_task($sendemail);
    return true;
}



/**
 * Return an array of course's ids where $USER is teacher
 * @param int|null $additionalid
 * @return array
 */
function get_my_courses_as_teacher(int|null $additionalid = null): array {
    global $DB, $USER, $CFG;
    $helpfuntion1 = function ($arrayelement) {
        return $arrayelement->instanceid;
    };
    $addsql = empty($additionalid) ? "" : "OR " . $CFG->prefix . "role_assignments.userid=$additionalid";
    $sql = "SELECT " . $CFG->prefix . "role_assignments.id, instanceid, roleid
    		FROM " . $CFG->prefix . "role_assignments
    		JOIN " . $CFG->prefix . "context
    		ON " . $CFG->prefix . "role_assignments.contextid = " . $CFG->prefix . "context.id
    		WHERE " . $CFG->prefix . "role_assignments.roleid=" . $CFG->creatornewroleid . "
    		AND ( " . $CFG->prefix . "role_assignments.userid=$USER->id " . $addsql . " )
    		AND " . $CFG->prefix . "context.contextlevel=50";
    return array_map($helpfuntion1, $DB->get_records_sql($sql));
}

/**
 * return an array of fileinfo-objects that lists automated backup files of courses tought by $USER
 * @param int|null $additionalid
 * @return array
 */
function get_backup_files(int|null $additionalid = null): array {
    global $DB, $USER;
    // Disable restore feature temporarily.
    $backuppath = get_config('backup', 'backup_auto_destination') . '';
    $result = [];
    $copies = implode("|", get_my_courses_as_teacher($additionalid));
    if (!($handle = opendir($backuppath))) {
        return $result;
    }
    while (false !== ($entry = readdir($handle))) {
        $matches = [];
        $str = '/^sicherung-moodle2-course-(' . $copies . ')-(\d{4})(\d{2})(\d{2})-(\d{2})(\d{2})\.mbz$/mi';
        if (preg_match($str, $entry, $matches)) {
            $file = new stdClass();
            $file->name = $entry;
            $file->path = $backuppath;
            $file->datetime = "$matches[5]:$matches[6] $matches[4].$matches[3].$matches[2]";
            $file->course = $DB->get_record("course", ["id" => $matches[1]], "id, fullname, shortname");
            $result[md5($entry . "_" . $USER->id)] = $file;
        }
    }
    closedir($handle);
    return $result;
}

/**
 * return an array of fileinfo-objects that lists template files
 * @return array
 */
function get_template_files(): array {
    global $DB, $USER;
    // Disable restore feature temporarily.
    $backuppath = get_config('backup', 'backup_auto_destination') . '/templates';
    $result = [];
    $files = [];
    if (!($handle = opendir($backuppath))) {
        return $result;
    }
    // Read filetree.
    $filenames = [];
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != ".." && is_dir($backuppath . "/" . $entry)) {
            $handle2 = opendir($backuppath . "/" . $entry);
            while (false !== ($entry2 = readdir($handle2))) {
                $filenames[] = $entry . "/" . $entry2;
            }
        } else {
            $filenames[] = $entry;
        }
    }
    // Build file-info objects.
    foreach ($filenames as $entry) {
        $matches = [];
        if (preg_match('/^((.+)\/)?template(\d{1,})\.mbz$/mi', $entry, $matches)) {
            $file = new stdClass();
            $file->name = $entry;
            $file->path = $backuppath;
            $file->info = "no info available";
            $file->category = isset($matches[2]) ? $matches[2] : "";
            $txtfile = $file->path . "/" . substr($file->name, 0, -3) . "txt";
            if (file_exists($txtfile)) {
                $file->info = file_get_contents($txtfile);
            }
            $files[$entry] = $file;
        }
    }
    closedir($handle);
    // Sort files and prepare output.
    ksort($files);
    foreach ($files as $file) {
        $result[md5($file->name . "_" . $USER->id)] = $file;
    }
    return $result;
}

/**
 * Restores a some course data into a newly created course.
 * SECURITY WARNING: For the time of the restore process (and only in the context of the target course)
 * the user will be assigned to a role that has the restoring-capability.
 *
 * @param int $courseid target course
 * @param string $foldername unziped backupfiles
 * @return void
 */
function duplicate_course(int $courseid, string $foldername): void {
    global $DB, $USER;

    $transaction = $DB->start_delegated_transaction();

    try {
        $USER->access = null;

        // Init Restore Process.
        $controller = new restore_controller(
            $foldername,
            $courseid,
            backup::INTERACTIVE_NO,
            backup::MODE_SAMESITE,
            $USER->id,
            backup::TARGET_EXISTING_ADDING
        );

        // Restore bachup into course.
        $restoresettings = [
                        'role_assignments' => 0, // Include user role assignments (default is 1).
                        'activities' => 1, // Include activities (default is 1).
                        'blocks' => 1, // Include blocks (default is 1).
                        'filters' => 1, // Include filters (default is 1).
                        'comments' => 0, // Include comments (default is 1).
                        'userscompletion' => 0, // Include user completion details (default is 1).
                        'logs' => 0, // Include course logs (default is 0).
                        'grade_histories' => 0, // Include grade history (default is 0).
                        'users' => 0, // Include user data (default is 0).
        ];

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

        // Delete temporary assignment and force capability cache to reload.
        $USER->access = null;

        // Update SectionCount.
        $format = $DB->get_record("course", ["id" => $courseid], "id, format")->format;
        if ($format == "topics" || $format == "weeks") {
            $sectioncount = $DB->count_records("course_sections", ["course" => $courseid]);
            $format = course_get_format($courseid);
            $format->update_course_format_options(["numsections" => ($sectioncount - 1)]);
        }

        // Restore Course Summary.
        $veranstid = $DB->get_record("course", ["id" => $courseid], "id, idnumber")->idnumber;
        $obj = (object) [
            "id" => $courseid,
            "summaryformat" => 1,
            "summary" => get_default_summary(get_course_by_veranstid($veranstid)),
        ];
        $DB->update_record("course", $obj);

        // Commit.
        $transaction->allow_commit();
    } catch (Exception $e) {
        $transaction->rollback($e);

        // Delete temporary assignment and force capability cache to reload.
        $USER->access = null;
    }
}

/**
 * Unzips a zip file.
 *
 * @param stored_file|string $zipfile
 * @param string $destination
 * @return bool
 */
function lsf_unification_unzip(stored_file|string $zipfile, string $destination = ''): bool {
    global $CFG, $USER;
    $fb = get_file_packer('application/vnd.moodle.backup');
    $result = $fb->extract_to_pathname(
        $zipfile,
        $destination,
        null,
        null
    );
    return $result != false;
}
