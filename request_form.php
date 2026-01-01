<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Form for requests.
 *
 * @package   local_lsf_unification
 * @copyright 2025 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/lsf_unification/lib_features.php');

/**
 * Form for the course request.
 * @package   local_lsf_unification
 * @copyright 2025 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lsf_course_request_form extends moodleform {
    /** @var int The course that want to be created. */
    protected $veranstid;

    /**
     * Define the form.
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function definition() {
        global $USER, $CFG, $DB;

        $mform    = $this->_form;

        // This contains the data of this form.
        $veranstid = $this->_customdata['veranstid'];

        $this->veranstid = $veranstid;
        $lsfcourse = get_course_by_veranstid($veranstid);
        $this->lsf_course = $lsfcourse;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('hidden', 'veranstid', null);
        $mform->setType('veranstid', PARAM_INT);
        $mform->setConstant('veranstid', $veranstid);
        $mform->addElement('hidden', 'answer', null);
        $mform->setType('answer', PARAM_INT);
        $mform->setConstant('answer', 1);

        $mform->addElement('text', 'fullname', get_string('fullnamecourse'), 'maxlength="254" size="80"');
        $mform->addHelpButton('fullname', 'fullnamecourse');
        $mform->addRule('fullname', get_string('missingfullname'), 'required', null, 'client');
        $mform->setType('fullname', PARAM_MULTILANG);
        $mform->setDefault('fullname', get_default_fullname($lsfcourse));

        $mform->addElement(
            'text',
            'shortname',
            get_string('shortnamecourse', 'local_lsf_unification', shortname_hint($lsfcourse)),
            'maxlength="100" size="30"'
        );
        $mform->addHelpButton('shortname', 'shortnamecourse');
        $mform->addRule('shortname', get_string('missingshortname'), 'required', null, 'client');
        $mform->setType('shortname', PARAM_MULTILANG);
        $mform->setDefault('shortname', get_default_shortname($lsfcourse));

        $mform->addElement('text', 'idnumber', get_string('idnumbercourse'), 'maxlength="100"  size="10"');
        $mform->addHelpButton('idnumber', 'idnumbercourse');
        $mform->setType('idnumber', PARAM_RAW);
        $mform->hardFreeze('idnumber');
        $mform->setConstant('idnumber', $veranstid);

        $mform->addElement('hidden', 'summary', null);
        $mform->setType('summary', PARAM_RAW);
        $mform->setConstant('summary', get_default_summary($lsfcourse));

        $mform->addElement('date_selector', 'startdate', get_string('startdate'));
        $mform->addHelpButton('startdate', 'startdate');
        $mform->setDefault('startdate', get_default_startdate($lsfcourse));

        $mform->addElement('header', 'enrol', get_string('config_enrol', 'local_lsf_unification'));
        $mform->setExpanded('enrol');
        if (get_config('local_lsf_unification', 'enable_enrol_ext_db')) {
            $mform->addElement(
                'advcheckbox',
                'dbenrolment',
                get_string('config_dbenrolment', 'local_lsf_unification'),
                '',
                ['group' => 1],
                [0, 1]
            );
            $mform->addHelpButton('dbenrolment', 'config_dbenrolment', 'local_lsf_unification');
            $mform->setDefault('dbenrolment', 0);

            $mform->addElement(
                'advcheckbox',
                'selfenrolment',
                get_string('config_selfenrolment', 'local_lsf_unification'),
                '',
                ['group' => 2],
                [0, 1]
            );
            $mform->setDefault('selfenrolment', 1);
            $mform->addHelpButton('selfenrolment', 'config_selfenrolment', 'local_lsf_unification');
        }

        $mform->addElement(
            'passwordunmask',
            'enrolment_key',
            get_string('config_enrolment_key', 'local_lsf_unification'),
            'maxlength="100"  size="10"'
        );
        $mform->setType('enrolment_key', PARAM_RAW);
        $mform->addHelpButton('enrolment_key', 'config_enrolment_key', 'local_lsf_unification');
        $mform->disabledIf('enrolment_key', 'selfenrolment', 'neq', 1);

        $mform->addElement('header', 'categoryheader', get_string('config_category', 'local_lsf_unification'));

        $choices = get_courses_categories($veranstid);
        $choices = add_path_description($choices);
        asort($choices);
        $choices[-1] = "";
        $select = $mform->addElement('select', 'category', get_string('config_category', 'local_lsf_unification'), $choices);
        $mform->addRule('category', '', 'required');
        $mform->setDefault('category', -1);

        $mform->addElement('textarea', 'category_wish', get_string('config_category_wish', 'local_lsf_unification'), '');
        $mform->addHelpButton('category_wish', 'config_category_wish', 'local_lsf_unification');
        $mform->setType('enrolment_key', PARAM_RAW);

        $mform->addElement('header', 'semesterheader', get_string('config_course_semester', 'local_lsf_unification'));

        $semesterfieldname = 'semester';
        if ($field = $DB->get_record('customfield_field', ['shortname' => $semesterfieldname, 'type' => 'semester'])) {
            $fieldcontroller = \core_customfield\field_controller::create($field->id);
            $datacontroller = \core_customfield\data_controller::create(0, null, $fieldcontroller);
            $datacontroller->instance_form_definition($mform);

            $mform->setDefault('customfield_' . $datacontroller->get_default_value(), 27);
            // LEARNWEB-TODO default <=> lsf data!
        }

        $this->add_action_buttons();
    }

    /**
     * Perform some extra moodle validation.
     * @param array $data
     * @param array $files
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function validation(array $data, array $files): array {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);
        if ($foundcourses = $DB->get_records('course', ['shortname' => $data['shortname']])) {
            if (!empty($data['id'])) {
                unset($foundcourses[$data['id']]);
            }
            if (!empty($foundcourses)) {
                foreach ($foundcourses as $foundcourse) {
                    $foundcoursenames[] = $foundcourse->fullname;
                }
                $foundcoursenamestring = implode(',', $foundcoursenames);
                $errors['shortname'] = get_string('shortnametaken', '', $foundcoursenamestring);
            }
        }

        if (!is_shortname_valid($this->lsf_course, $data['shortname'])) {
            $errors['shortname'] = get_string('shortnameinvalid', 'local_lsf_unification', shortname_hint($this->lsf_course));
        }

        $categories = get_courses_categories($this->veranstid, false);
        if (empty($data['category']) || !isset($categories[$data['category']])) {
            $errors['category'] = get_string('categoryinvalid', 'local_lsf_unification');
        }

        return $errors;
    }
}
