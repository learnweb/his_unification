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

namespace local_lsf_unification;

/**
 * Generator for the local_lsf_unification plugin.
 *
 * @package    local_lsf_unification
 * @copyright  2018 Nina Herrmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_lsf_unification_generator extends testing_data_generator {
    /**
     * Creates a lsf_course.
     * @return stdClass
     */
    public function create_lsf_course() {
        $course = new stdClass();
        $course->titel = 'Some title';
        $course->veranstid = 1;
        return $course;
    }
    /**
     * Setting up the data for the different e-mail ad-hoc tasks.
     * The nested arrays look aweful but are later necessary to pass the data to the adhoc-task.
     * @param bool $categorywish Is the mail a category wish?
     * @param bool $request Should the params include the link for the answer of a request?
     * @param bool $answer Is the message the answer to a request?
     * @return array Data structure for returning all necessary data for the assertions
     */
    public function set_up_params($categorywish = true, $request = false, $answer = false) {
        $sender = $this->create_user();
        $recipient = $this->create_user();

        $data = [];
        // In case the request is an anwer we call the param acceptor else requester.
        if ($answer && !$categorywish) {
            $data['acceptorid'] = $sender->id;
            $data['acceptorfirstname'] = $sender->firstname;
            $data['acceptorlastname'] = $sender->lastname;
        } else {
            if (!$categorywish) {
                $data['requesterid'] = $sender->id;
            }
            $data['requesterfirstname'] = $sender->firstname;
            $data['requesterlastname'] = $sender->lastname;
        }

        $params = new stdClass();
        $params->a = $sender->firstname . " " . $sender->lastname;
        if (!$categorywish) {
            $data['recipientid'] = $recipient->id;
            $course = $this->create_lsf_course();
            $params->c = mb_convert_encoding($course->titel, 'UTF-8', 'ISO-8859-1');
            $data['veranstid'] = $course->veranstid;
            if ($request) {
                $data['requestid'] = '1';
            }
        } else {
            // A category wish always goes to the supportuser. We do not create a specific user for the supportuser ...
            // But the naming is different.
            $data['supportuserid'] = $recipient->id;
            $course = $this->create_course();
            $params->b = $sender->id;
            $params->c = mb_convert_encoding($course->fullname, 'UTF-8', 'ISO-8859-1');
            $params->d = $course->id;
            $params->e = 'I want to change to Category xy';
        }
        $data['recipientemail'] = $recipient->email;
        $data['params'] = $params;
        return $data;
    }
}
