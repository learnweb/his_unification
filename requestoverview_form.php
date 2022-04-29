<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/local/lsf_unification/lib_features.php');

class course_overview_request_form extends moodleform
{
    function definition(){
        $mform = $this->_form;
        $courselist = $this->_customdata['courselist'];
        $courselist_sap = $this->_customdata['courselist_sap'];

        $mform->addElement('html',"<table><tr><td colspan='2'><b>" . get_string('question', 'local_lsf_unification') . "</b></td></tr>");
        if($courselist != "<ul></ul>"){
            $mform->addElement('html', "<tr><td style='vertical-align:top;'><input type='radio' name='answer' id='answer1' value='1'></td><td><label for='answer1'>" . get_string('answer_course_found', 'local_lsf_unification') . $courselist . "</label></td></tr>");
        } else {
            $mform->addElement('html', "");
        }
        $mform->addElement('html', "<tr><td><input type='radio' name='answer' id='answer3' value='3'></td><td><label for='answer3'>" . get_string('answer_course_in_lsf_and_visible', 'local_lsf_unification') . "</label></td></tr>");

        if (get_config('local_lsf_unification', 'remote_creation')) {
            $mform->addElement('html', "<tr><td><input type='radio' name='answer' id='answer11' value='11'></td><td><label for='answer11'>" . get_string('answer_proxy_creation', 'local_lsf_unification') . "</label></td></tr>");
        }
        if($courselist_sap != "<ul></ul>"){
            $mform->addElement('html', "<tr><td style='vertical-align:top;'><input type='radio' name='answer_sap' id='answer1_sap' value='1'></td><td><label for='answer1'>" . get_string('answer_course_found_sap', 'local_lsf_unification') .  $courselist_sap . "</label></td></tr>");
        } else {
            $mform->addElement('html', "");
        }
        //echo "<tr><td><input type='radio' name='answer_sap' id='answer3_sap' value='3'></td><td><label for='answer3_sap'>" . get_string('answer_course_in_lsf_and_visible', 'local_lsf_unification') . "</label></td></tr>";
        if (get_config('local_lsf_unification', 'remote_creation')) {
            $mform->addElement('html', "<tr><td><input type='radio' name='answer_sap' id='answer11_sap' value='11'></td><td><label for='answer11_sap'>" . get_string('answer_proxy_creation_sap', 'local_lsf_unification') . "</label></td></tr>");
        }
        $mform->addElement('html',"<tr><td><input type='radio' name='answer' id='answer6' value='6'></td><td><label for='answer6'>" . get_string('answer_goto_old_requestform', 'local_lsf_unification') . "</label></td></tr>");
        $mform->addElement('html', "<tr><td>&nbsp;</td><td><input type='submit' value='" . get_string('select', 'local_lsf_unification') . "'/></td></tr></table>");

    }
}