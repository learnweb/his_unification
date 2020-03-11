<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/local/lsf_unification/lib_features.php');

class lsf_course_request_form extends moodleform {
    protected $veranstid;
    protected $lsf_course;

    function definition() {
        global $USER, $CFG, $DB;

        $mform    = $this->_form;

        $veranstid = $this->_customdata['veranstid']; // this contains the data of this form
        $this->veranstid = $veranstid;
        $lsf_course = get_course_by_veranstid($veranstid);
        $this->lsf_course = $lsf_course;

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
        $mform->setDefault('fullname', get_default_fullname($lsf_course));

        $mform->addElement('text', 'shortname', get_string('shortnamecourse'), 'maxlength="100" size="30"');
        $mform->addHelpButton('shortname', 'shortnamecourse');
        $mform->addRule('shortname', get_string('missingshortname'), 'required', null, 'client');
        $mform->setType('shortname', PARAM_MULTILANG);
        $mform->setDefault('shortname', get_default_shortname($lsf_course));
        $mform->addElement('html', '<i>'.get_string('shortnamehint', 'local_lsf_unification', shortname_hint($lsf_course)).'</i>');

        $mform->addElement('text','idnumber', get_string('idnumbercourse'),'maxlength="100"  size="10"');
        $mform->addHelpButton('idnumber', 'idnumbercourse');
        $mform->setType('idnumber', PARAM_RAW);
        $mform->hardFreeze('idnumber');
        $mform->setConstant('idnumber', $veranstid);

        $mform->addElement('hidden', 'summary', null);
        $mform->setType('summary', PARAM_RAW);
        $mform->setConstant('summary', get_default_summary($lsf_course));

        $mform->addElement('date_selector', 'startdate', get_string('startdate'));
        $mform->addHelpButton('startdate', 'startdate');
        $mform->setDefault('startdate', get_default_startdate($lsf_course));

        $mform->addElement('header','enrol', get_string('config_enrol', 'local_lsf_unification'));
        $mform->setExpanded('enrol');
        if (get_config('local_lsf_unification', 'enable_enrol_ext_db')) {
            $mform->addElement('advcheckbox', 'dbenrolment', get_string('config_dbenrolment', 'local_lsf_unification'), '', array('group' => 1), array(0, 1));
            $mform->addHelpButton('dbenrolment', 'config_dbenrolment', 'local_lsf_unification');
            $mform->setDefault('dbenrolment', 0);
            
            $mform->addElement('advcheckbox', 'selfenrolment', get_string('config_selfenrolment', 'local_lsf_unification'), '', array('group' => 2), array(0,1));
            $mform->setDefault('selfenrolment', 1);
            $mform->addHelpButton('selfenrolment', 'config_selfenrolment', 'local_lsf_unification');
        }
        
        $mform->addElement('passwordunmask','enrolment_key', get_string('config_enrolment_key','local_lsf_unification'),'maxlength="100"  size="10"');
        $mform->setType('enrolment_key', PARAM_RAW);
        $mform->addHelpButton('enrolment_key', 'config_enrolment_key','local_lsf_unification');
        $mform->disabledIf('enrolment_key', 'selfenrolment', 'neq', 1);

        /* Enrolment Settings (to be implemented)
         $mform->addElement('header','', get_string('config_auto_update', 'local_lsf_unification'));
        $choices = array(
                        -1 => get_string('config_auto_update_duration-1', 'local_lsf_unification'),
                        7 => get_string('config_auto_update_duration7', 'local_lsf_unification'),
                        31 => get_string('config_auto_update_duration31', 'local_lsf_unification'),
                        182 => get_string('config_auto_update_duration182', 'local_lsf_unification')
        );
        $mform->addElement('select', 'update_duration', get_string('config_auto_update_duration','local_lsf_unification'), $choices);
        $mform->addRule('update_duration', '', 'required');
        $mform->setDefault('update_duration', -1);
        */

        $mform->addElement('header','categoryheader', get_string('config_category', 'local_lsf_unification'));

        $choices = get_courses_categories($veranstid);
        $choices = add_path_description($choices);
        $choices[-1] = "";
        $select = $mform->addElement('select', 'category', get_string('config_category','local_lsf_unification'), $choices);
        $mform->addRule('category', '', 'required');
        $mform->setDefault('category', -1);

        $mform->addElement('textarea','category_wish', get_string('config_category_wish','local_lsf_unification'),'');
        $mform->addHelpButton('category_wish', 'config_category_wish','local_lsf_unification');
        $mform->setType('enrolment_key', PARAM_RAW);

        $semesterstring = 'Semester';
        $customfield = $DB->get_record('customfield_field', array('name' =>  $semesterstring, 'type' => 'select'));
        if ($DB->get_record('customfield_field', array('name' => 'Semester', 'type' => 'select'))) {
            $customfieldcontroller = \customfield_date\field_controller::create($customfield->id);
            $configdata = $customfieldcontroller->get('configdata');
            $semesterinarray = explode("\n", $configdata['options']);
            $mform->addElement('header', $semesterstring, $semesterstring);
            $mform->addElement('select', 'current_semester', $semesterstring, $semesterinarray);
            $mform->addRule('current_semester', get_string('config_course_semester_missing', 'local_lsf_unification'),
                'required', null, 'client');
            $mform->setDefault('current_semester', 27);
            $mform->setExpanded($semesterstring);
        }

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

        if (!is_shortname_valid($this->lsf_course, $data['shortname'])) {
            $errors['shortname']= get_string('shortnameinvalid', 'local_lsf_unification', shortname_hint($this->lsf_course));
        }

        $categories = get_courses_categories($this->veranstid, false);
        if (empty($data['category']) || !isset($categories[$data['category']])) {
            $errors['category']= get_string('categoryinvalid', 'local_lsf_unification');
        }

        return $errors;
    }
}

