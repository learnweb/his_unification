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
     * @param bool $lsfcourse Is a lsf course required (otherwise normal course)?
     * @param bool $request Should the params include the link for a request?
     * @param bool $answer Should the params include the link for a answer?
     * @return array Data structure for returning all necessary data for the assertions
     */
    public function set_up_json_params($lsfcourse = true, $request = false, $answer = false) {
        global $CFG;
        $sender = $this->create_user();
        $recipient = $this->create_user();

        $data = array();
        $params = new stdClass();
        $params->a = $sender->firstname." ".$sender->lastname;

        if ($lsfcourse) {
            $course = $this->create_lsf_course();
            $params->b = $CFG->wwwroot.'/user/view.php?id='.$sender->id;
            $params->c = utf8_encode($course->titel);
            if ($request) {
                $params->d = $CFG->wwwroot.'/local/lsf_unification/request.php?answer=12&requestid=1';
            }
            if ($answer) {
                $params->d = $CFG->wwwroot.'/local/lsf_unification/request.php?answer=1&veranstid=' . $course->veranstid;
            }
        } else {
            $course = $this->create_course();
            $params->b = $sender->id;
            $params->c = utf8_encode($course->fullname);
            $params->d = $course->id;
            $params->e = 'I want to change to Category xy';
        }
        $data['params'] = $params;
        $jsondata = json_encode(array('userid' => $recipient->id, 'userfirstname' => $sender->firstname,
            'userlastname' => $sender->lastname, 'params' => $params));
        $data['jsondata'] = $jsondata;
        $data['recipientemail'] = $recipient->email;

        return $data;
    }

}