<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_lsf_unification\task\emails;
use core_user;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/lsf_unification/locallib.php');

/**
 * The ad hoc task for sending a email that a requested course was accepted to be created. The mail is send to the
 * user who requested the course.
 *
 * @package    local_lsf_unification
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class request_accepted extends \core\task\adhoc_task {
    #[\Override]
    public function execute() {
        global $DB, $OUTPUT;
        // Get data that was cached when the task was initialized.
        $data = $this->get_custom_data();

        $teacher = $DB->get_record('user', ['id' => $data->teacherid], '*', MUST_EXIST);
        $requester = $DB->get_record('user', ['id' => $data->requesterid], '*', MUST_EXIST);
        $continueurl = new moodle_url('/local/lsf_unification/request.php', ['answer' => 1, 'veranstid' => $data->veranstid]);
        $contenthtml = get_string('mail_request_accepted_content_html', 'local_lsf_unification', ['name' => $data->coursename]);
        $contenttext = get_string('mail_request_accepted_content_text', 'local_lsf_unification', ['name' => $data->coursename]);
        $maildata = [
            'subject' => get_string('mail_request_accepted_subject', 'local_lsf_unification'),
            'greeting' => get_string('mail_greeting', 'local_lsf_unification', ['name' => fullname($requester, true)]),
            'teacherurl' => (new moodle_url('/user/view.php', ['id' => $teacher->id]))->out(),
            'teachername' => fullname($teacher, true),
            'content_html' => $contenthtml,
            'content_text' => $contenttext,
            'continueurl' => $continueurl,
            'button' => get_string('mail_request_accepted_button', 'local_lsf_unification'),
            'helptext' => get_string('mail_button_help', 'local_lsf_unification'),
        ];

        $mustachedata = array_merge($maildata, lsf_unification_basic_mail_data());
        $textcontent = $OUTPUT->render_from_template('local_lsf_unification/emails/request_accepted_text', $mustachedata);
        $htmlcontent = $OUTPUT->render_from_template('local_lsf_unification/emails/request_accepted', $mustachedata);

        $wassent = email_to_user(
            $requester,
            core_user::get_noreply_user(),
            get_string('mail_request_accepted_subject', 'local_lsf_unification'),
            $textcontent,
            $htmlcontent
        );
        if (!$wassent) {
            throw new \moodle_exception(get_string(
                'ad_hoc_task_failed',
                'local_lsf_unification',
                'send_mail_course_creation_accepted'
            ));
        }
    }
}
