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

/**
 * The ad hoc task for sending a email that a requested course was accepted to be created. The mail is send to the
 * user who requested the course.
 *
 * @package    his_unification
 * @copyright  2018 Nina Herrmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_lsf_unification\task;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/local/lsf_unification/lib_features.php');

/**
 * Class send_mail_course_creation_accepted
 * (Task in moodle will be retried after 1 minute automatically when they throw an exception.
 * See: https://docs.moodle.org/dev/Task_API#Failures )
 * @package local_lsf_unification\task
 */
class send_mail_course_creation_accepted extends \core\task\adhoc_task {
    /**
     * Execute the ad-hoc task.
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function execute() {
        global $CFG;
        $data = $this->get_custom_data();

        $recipientid = $data->recipientid;
        $userarray = user_get_users_by_id(array($recipientid));

        // In case no recipient can be found the task is aborted and deleted.
        if (empty($userarray[$recipientid])) {
            return;
        }
        $user = $userarray[$recipientid];
        $data->params->requesturl = get_remote_creation_continue_link($data->veranstid);
        $data->params->userurl = $CFG->wwwroot.'/user/view.php?id=' . $data->acceptorid;
        // Expected params of $data->params are:
        // A) a -> (string) firstname,
        // B) userurl-> (string) the url to the user page,
        // C) c-> the (string) coursename, and
        // D) requesturl-> the url to answer the request.
        $content = get_string('email3', 'local_lsf_unification', $data->params);

        $wassent = email_to_user($user, get_string('email_from', 'local_lsf_unification')
            ." (by ".$data->acceptorfirstname." ".$data->acceptorlastname.")",
            get_string('email3_title', 'local_lsf_unification'), $content);
        if (!$wassent) {
            throw new \moodle_exception(get_string('ad_hoc_task_failed',
                'local_lsf_unification', 'send_mail_course_creation_accepted'));
        }
    }
}