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
use core\task\adhoc_task;
use core_user;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/lsf_unification/locallib.php');

/**
 * Task that sends a review email to teacher that a person wants to create a course on their behalf.
 * (Task in moodle will be retried after 1 minute automatically when they throw an exception.
 *
 * @package    local_lsf_unification
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_creation_request extends adhoc_task {
    #[\Override]
    public function execute() {
        global $OUTPUT, $DB;
        // Get data that was cached when the task was initialized.
        $data = $this->get_custom_data();

        $recipient = $DB->get_record('user', ['id' => $data->recipientid], '*', MUST_EXIST);
        $requester = $DB->get_record('user', ['id' => $data->requesterid], '*', MUST_EXIST);

        $requesturl = new moodle_url('/local/lsf_unification/request.php', ['answer' => 12, 'requestid' => $data->requestid]);

        $maildata = [
            'subject' => get_string('mail_courserequest_subject', 'local_lsf_unification'),
            'requestername' => fullname($requester, true),
            'requesterurl' => (new moodle_url('/user/view.php', ['id' => $data->requesterid]))->out(),
            'requesturl' => $requesturl->out(),
            'greeting' => get_string('mail_greeting', 'local_lsf_unification', ['name' => fullname($recipient, true)]),
            'content_text' => get_string('mail_courserequest_content_text', 'local_lsf_unification', ['name' => $data->coursename]),
        ];
        $mustachedata = array_merge($maildata, lsf_unification_basic_mail_data());
        $wassent = email_to_user(
            $recipient,
            core_user::get_noreply_user(),
            get_string('mail_courserequest_subject', 'local_lsf_unification'),
            $OUTPUT->render_from_template('local_lsf_unification/emails/course_request_text', $mustachedata)
        );

        if (!$wassent) {
            throw new \moodle_exception(get_string('ad_hoc_task_failed', 'local_lsf_unification', 'course_creation_request'));
        }
    }
}
