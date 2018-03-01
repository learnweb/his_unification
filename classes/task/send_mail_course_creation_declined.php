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
 * The ad hoc task for sending a email that a request to create a course was declined. The mail is send to the
 * user who requested the course.
 *
 * @package    his_unification
 * @copyright  2018 Nina Herrmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_lsf_unification\task;

/**
 * Class send_mail_course_creation_declined
 * @package local_lsf_unification\task
 */
class send_mail_course_creation_declined extends \core\task\adhoc_task {
    /**
     * Execute the ad-hoc task.
     * @throws \coding_exception
     */
    public function execute() {
        $jsondata = $this->get_custom_data();
        $data = json_decode($jsondata, true);

        $userid = $data['userid'];
        $userarray = user_get_users_by_id(array($userid => $userid));

        if (empty($userarray[$userid])) {
            // TODO: discuss what is necessary to happen (log?)
        }
        $user = $userarray[$userid];

        $content = get_string('email4','local_lsf_unification', $data['params']);

        email_to_user($user, get_string('email_from','local_lsf_unification')." 
        (by ".$data['userfirstname']." ".$data['userlastname'].")",
            get_string('config_category_wish','local_lsf_unification'), $content);
    }
}