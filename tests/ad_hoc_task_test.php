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
 * Test the ad_hoc tasks.
 *
 * @package    his_unification
 * @copyright  2018 Nina Herrmann
 * @group      lsf_unification_ad_hoc_task
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class ad_hoc_task_test extends advanced_testcase {

    /**
     * @var saves all emails send.
     */
    private $sink;
    /**
     * @var local generator.
     */
    private $generator;


    /**
     * Set up the params to redirect mails.
     */
    protected function setUp() {
        $this->resetAfterTest(true);

        unset_config('nomailever');
        $this->sink = $this->redirectEmails();
        $this->generator = $this->getDataGenerator()->get_plugin_generator('local_lsf_unification');

    }

    /**
     * Test the ad hoc task for sending mails to the support for changing the category.
     * @throws coding_exception
     */
    public function test_send_mail_category_wish() {
        $adhoctask = new \local_lsf_unification\task\send_mail_category_wish();

        $setupdata = $this->generator->set_up_params(false);
        $adhoctask->set_custom_data($setupdata['data']);
        $adhoctask->execute();
        $messages = $this->sink->get_messages();
        $this->assertEquals(1, count($messages));

        $message = $messages[0];
        $messagebody = $this->trim_string($message->body);

        // Expected content.
        $content = "\n" . get_string('email', 'local_lsf_unification', $setupdata['params']) . "\n";
        $content = $this->trim_string($content);

        // Assertions.
        $this->assertEquals($content, $messagebody);
        $this->assertEquals($setupdata['recipientemail'], $message->to);
        // The phpunit build in function overwrithes where the email does come from.
        $this->assertEquals('noreply@www.example.com', $message->from);
        $this->assertEquals('Category Relocation Wish', $message->subject);
    }

    /**
     * Test the ad hoc task for sending mails that course was created.
     * @throws coding_exception
     */
    public function test_send_mail_course_creation_accepted() {
        global $CFG;
        $adhoctask = new \local_lsf_unification\task\send_mail_course_creation_accepted();

        $setupdata = $this->generator->set_up_params(true, false, true);
        $adhoctask->set_custom_data($setupdata['data']);

        $adhoctask->execute();
        $messages = $this->sink->get_messages();
        $this->assertEquals(1, count($messages));

        $message = $messages[0];
        $messagebody = $this->trim_string($message->body);

        // Expected content.
        $setupdata['params']->requesturl = $CFG->wwwroot.'/local/lsf_unification/request.php?answer=1&veranstid=' . $setupdata['data']['veranstid'];
        $setupdata['params']->userurl = $CFG->wwwroot.'/user/view.php?id=' . $setupdata['data']['globaluserid'];
        $content = get_string('email3', 'local_lsf_unification', $setupdata['params']);

        // Assertions.
        $this->assertEquals($content, $messagebody);
        $this->assertEquals($setupdata['recipientemail'], $message->to);
        // The phpunit build in function overwrithes where the email does come from.
        $this->assertEquals('noreply@www.example.com', $message->from);
        $this->assertEquals('Course Creation Request accepted', $message->subject);
    }

    /**
     * Test the ad hoc task for sending mails that the creation of a course was declined.
     * @throws coding_exception
     */
    public function test_send_mail_course_creation_declined() {
        global $CFG;
        $adhoctask = new \local_lsf_unification\task\send_mail_course_creation_declined();

        $setupdata = $this->generator->set_up_params();
        $adhoctask->set_custom_data($setupdata['data']);

        $adhoctask->execute();
        $messages = $this->sink->get_messages();
        $this->assertEquals(1, count($messages));

        $message = $messages[0];
        $messagebody = $this->trim_string($message->body);

        // Expected content.
        $setupdata['params']->userurl = $CFG->wwwroot.'/user/view.php?id='. $setupdata['data']['globaluserid'];
        $content = get_string('email4', 'local_lsf_unification', $setupdata['params']);

        // Assertions.
        $this->assertEquals($content, $messagebody);
        $this->assertEquals($setupdata['recipientemail'], $message->to);
        // The phpunit build in function overwrithes where the email does come from.
        $this->assertEquals('noreply@www.example.com', $message->from);
        $this->assertEquals('Course Creation Request declined', $message->subject);
    }

    /**
     * Test the ad hoc task for sending mails to request a teacher whether a course should be created.
     * @throws coding_exception
     */
    public function test_send_mail_request_teacher_to_create_course() {
        global $CFG;
        $adhoctask = new \local_lsf_unification\task\send_mail_request_teacher_to_create_course();

        $setupdata = $this->generator->set_up_params(true, true);
        $adhoctask->set_custom_data($setupdata['data']);

        $adhoctask->execute();
        $messages = $this->sink->get_messages();
        $this->assertEquals(1, count($messages));

        $message = $messages[0];
        $messagebody = $this->trim_string($message->body);

        // Expected content.
        $setupdata['params']->requesturl = $CFG->wwwroot.'/local/lsf_unification/request.php?answer=12&requestid=' . $setupdata['data']['requestid'];
        $setupdata['params']->userurl = $CFG->wwwroot.'/user/view.php?id=' . $setupdata['data']['globaluserid'];
        $content = get_string('email2', 'local_lsf_unification', $setupdata['params']);

        // Assertions.
        $this->assertEquals($content, $messagebody);
        $this->assertEquals($setupdata['recipientemail'], $message->to);
        // The phpunit build in function overwrithes where the email does come from.
        $this->assertEquals('noreply@www.example.com', $message->from);
        $this->assertEquals('Course Creation Request', $message->subject);
    }

    /**
     * Test whether no message is send when the userid is not existing.
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function test_no_user_ad_hoc_task() {
        $adhoctask = new \local_lsf_unification\task\send_mail_category_wish();

        $setupdata = $this->generator->set_up_params(false);
        // Setting the userid to a number which is quiet likely not available.
        $setupdata['data']['userid'] = 23456;
        $adhoctask->set_custom_data($setupdata['data']);
        $adhoctask->execute();
        $messages = $this->sink->get_messages();
        // Task is aborted therefore no messages is expected.
        $this->assertEquals(0, count($messages));
    }
    /**
     * Trims all \n and \r characters from a string.
     * @param $string
     * @return string
     */
    private function trim_string($string) {
        $returnstring = str_replace("\n", " ", $string);
        $returnstring = str_replace("\r", "", $returnstring);
        // Remove leading whitespaces at start and end of string.
        return trim($returnstring);
    }
}