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

defined('MOODLE_INTERNAL') || die();

/**
 * Generator for the local_lsf_unification plugin.
 *
 * @package    his_unification
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
     * @param bool $lsfcourse Is a lsf course required (otherwise normal course)?
     * @param bool $request Should the params include the link for a request?
     * @param bool $answer Should the params include the link for a answer?
     * @return array Data structure for returning all necessary data for the assertions
     */
    public function set_up_params($lsfcourse = true, $request = false, $answer = false) {
        global $CFG, $USER;
        $sender = $this->create_user();
        $recipient = $this->create_user();

        $data = array();
        $params = new stdClass();
        $params->a = $sender->firstname." ".$sender->lastname;
        $data = array('userid' => $recipient->id, 'userfirstname' => $sender->firstname,
            'userlastname' => $sender->lastname, 'params' => $params);
        $data['data'] = $data;
        $data['recipientemail'] = $recipient->email;

        if ($lsfcourse) {
            $course = $this->create_lsf_course();
            $data['data']['globaluserid'] = $sender->id;
            $params->c = utf8_encode($course->titel);
            $data['data']['veranstid'] = $course->veranstid;
            if ($request) {
                $data['data']['requestid'] = '1';
            }
        } else {
            $course = $this->create_course();
            $params->b = $sender->id;
            $params->c = utf8_encode($course->fullname);
            $params->d = $course->id;
            $params->e = 'I want to change to Category xy';
        }
        $data['params'] = $params;

        return $data;
    }

}