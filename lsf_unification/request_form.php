<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/local/lsf_unification/lib_features.php');

class lsf_course_request_form extends moodleform {
    protected $veranstid;

    function definition() {
        global $USER, $CFG;
		
        $mform    = $this->_form;

        $veranstid = $this->_customdata['veranstid']; // this contains the data of this form
		$this->veranstid = $veranstid;
		
        $mform->addElement('header','general', get_string('general', 'form'));

        $mform->addElement('hidden', 'veranstid', null);
        $mform->setType('veranstid', PARAM_INT);
        $mform->setConstant('veranstid', $veranstid);
        $mform->addElement('hidden', 'answer', null);
        $mform->setType('answer', PARAM_INT);
        $mform->setConstant('answer', 1);

        $mform->addElement('text','fullname', get_string('fullnamecourse'),'maxlength="254" size="80"');
        $mform->addHelpButton('fullname', 'fullnamecourse');
        $mform->addRule('fullname', get_string('missingfullname'), 'required', null, 'client');
        $mform->setType('fullname', PARAM_MULTILANG);
        $mform->setDefault('fullname', get_default_fullname($veranstid));
		
        $mform->addElement('text', 'shortname', get_string('shortnamecourse'), 'maxlength="100" size="30"');
        $mform->addHelpButton('shortname', 'shortnamecourse');
        $mform->addRule('shortname', get_string('missingshortname'), 'required', null, 'client');
        $mform->setType('shortname', PARAM_MULTILANG);
        $mform->setDefault('shortname', get_default_shortname($veranstid));
		$mform->addElement('html', '<i>'.get_string('shortnamehint', 'local_lsf_unification', shortname_hint($this->veranstid)).'</i>');

        $mform->addElement('text','idnumber', get_string('idnumbercourse'),'maxlength="100"  size="10"');
        $mform->addHelpButton('idnumber', 'idnumbercourse');
        $mform->setType('idnumber', PARAM_RAW);
        $mform->hardFreeze('idnumber');
        $mform->setConstant('idnumber', $veranstid);
		
        $mform->addElement('hidden', 'summary', null);
        $mform->setType('summary', PARAM_RAW);
        $mform->setConstant('summary', get_default_summary($veranstid));

        $mform->addElement('date_selector', 'startdate', get_string('startdate'));
        $mform->addHelpButton('startdate', 'startdate');
        $mform->setDefault('startdate', get_default_startdate($veranstid));
		
        $mform->addElement('passwordunmask','enrolment_key', get_string('config_enrolment_key','local_lsf_unification'),'maxlength="100"  size="10"');
        $mform->setType('enrolment_key', PARAM_RAW);
        $mform->addHelpButton('enrolment_key', 'config_enrolment_key','local_lsf_unification');


        $mform->addElement('header','', get_string('config_category', 'local_lsf_unification'));
		

        $choices = get_courses_categories($veranstid);
        $choices = add_path_description($choices);
        $choices[-1] = "";
        $select = $mform->addElement('select', 'category', get_string('config_category','local_lsf_unification'), $choices);
        $mform->addRule('category', '', 'required');
        $mform->setDefault('category', -1);

        $mform->addElement('textarea','category_wish', get_string('config_category_wish','local_lsf_unification'),'');
        $mform->addHelpButton('category_wish', 'config_category_wish','local_lsf_unification');
        $mform->setType('enrolment_key', PARAM_RAW);

		
        $this->add_action_buttons();

    }

    function definition_after_data() {
    }


/// perform some extra moodle validation
    function validation($data, $files) {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);
        if ($foundcourses = $DB->get_records('course', array('shortname'=>$data['shortname']))) {
            if (!empty($data['id'])) {
                unset($foundcourses[$data['id']]);
            }
            if (!empty($foundcourses)) {
                foreach ($foundcourses as $foundcourse) {
                    $foundcoursenames[] = $foundcourse->fullname;
                }
                $foundcoursenamestring = implode(',', $foundcoursenames);
                $errors['shortname']= get_string('shortnametaken', '', $foundcoursenamestring);
            }
        }
		
		if (!is_shortname_valid($this->veranstid, $data['shortname'])) {
            $errors['shortname']= get_string('shortnameinvalid', 'local_lsf_unification', shortname_hint($this->veranstid));
		}
		
		$categories = get_courses_categories($this->veranstid, false);
		if (empty($data['category']) || !isset($categories[$data['category']])) {
            $errors['category']= get_string('categoryinvalid', 'local_lsf_unification');
		}

        return $errors;
    }
}

