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
 * The ad hoc task for sending a email to the support user with a wish for a different category.
 *
 * @package    local_lsf_unification
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class category_relocation extends \core\task\adhoc_task {
    /**
     * Execute the ad-hoc task.
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function execute() {
        global $DB, $OUTPUT;
        $data = $this->get_custom_data();
        $user = $DB->get_record('user', ['id' => $data->userid], '*', MUST_EXIST);
        $courseurl = (new moodle_url('/course/view.php', ['id' => $data->courseid]))->out();
        $coursename = $data->coursename;
        $messagetext = format_text_email($data->message, FORMAT_PLAIN);
        $paramstext = ['coursename' => $coursename, 'message' => $messagetext, 'courseurl' => $courseurl];

        $maildata = [
            'subject' => get_string('mail_category_wish_subject', 'local_lsf_unification'),
            'username' => fullname($user, true),
            'userurl' => (new moodle_url('/user/view.php', ['id' => $user->id]))->out(),
            'content_html' => get_string('mail_category_wish_content_html', 'local_lsf_unification', ['coursename' => $coursename]),
            'content_text' => get_string('mail_category_wish_content_text', 'local_lsf_unification', $paramstext),
            'message' => format_text_email($data->message, FORMAT_HTML),
            'coursename' => $coursename,
            'courseurl' => $courseurl,
            'button' => get_string('mail_category_wish_button', 'local_lsf_unification'),
            'helptext' => get_string('mail_button_help', 'local_lsf_unification'),
        ];
        $mustachedata = array_merge($maildata, lsf_unification_basic_mail_data());
        $textcontent = $OUTPUT->render_from_template('local_lsf_unification/emails/category_wish_text', $mustachedata);
        $htmlcontent = $OUTPUT->render_from_template('local_lsf_unification/emails/category_wish', $mustachedata);

        $supportuser = core_user::get_support_user();
        $supportuser->mailformat = 1;

        $wassent = email_to_user(
            $supportuser,
            core_user::get_noreply_user(),
            get_string('mail_category_wish_subject', 'local_lsf_unification'),
            $textcontent,
            $htmlcontent
        );

        if (!$wassent) {
            throw new \moodle_exception(get_string(
                'ad_hoc_task_failed',
                'local_lsf_unification',
                'send_mail_category_wish'
            ));
        }
    }
}
