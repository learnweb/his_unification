<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) { // needs this condition or there is error on login page

    $settings = new admin_settingpage('local_lsf_unification', 'LSF Unification Config');
    $ADMIN->add('localplugins', $settings);

    $settings2 = new admin_externalpage('local_lsf_unification_helptable', 'LSF Unification Matchings', $CFG->wwwroot . '/local/lsf_unification/helptablemanager.php');
    $ADMIN->add('localplugins', $settings2);

    $settings3 = new admin_externalpage('local_lsf_unification_deeplink_remove', 'LSF Deeplink Removal', $CFG->wwwroot . '/local/lsf_unification/deeplink_remove.php');
    $ADMIN->add('localplugins', $settings3);

    $settings4 = new admin_externalpage('local_lsf_unification_remoterequests', 'LSF Remote Request Handling', $CFG->wwwroot . '/local/lsf_unification/remoterequests.php');
    $ADMIN->add('localplugins', $settings4);

    $settings->add(new admin_setting_configtext('local_lsf_unification/dbhost',
        get_string('dbhost', 'local_lsf_unification'), get_string('dbhost_description', 'local_lsf_unification'),
        '', PARAM_RAW));
    $settings->add(new admin_setting_configtext('local_lsf_unification/dbport',
        get_string('dbport', 'local_lsf_unification'), get_string('dbport_description', 'local_lsf_unification'),
        '', PARAM_RAW));
    $settings->add(new admin_setting_configtext('local_lsf_unification/dbuser',
        get_string('dbuser', 'local_lsf_unification'), get_string('dbuser_description', 'local_lsf_unification'),
        '', PARAM_RAW));
    $settings->add(new admin_setting_configtext('local_lsf_unification/dbpass',
        get_string('dbpass', 'local_lsf_unification'), get_string('dbpass_description', 'local_lsf_unification'),
        '', PARAM_RAW));
    $settings->add(new admin_setting_configtext('local_lsf_unification/dbname',
        get_string('dbname', 'local_lsf_unification'), get_string('dbname_description', 'local_lsf_unification'),
        '', PARAM_RAW));
    $settings->add(new admin_setting_configtext('local_lsf_unification/max_import_age',
        get_string('max_import_age', 'local_lsf_unification'), get_string('max_import_age_description', 'local_lsf_unification'),
        365, PARAM_INT));

    if (!empty($CFG->creatornewroleid)) {
        $settings->add(new admin_setting_configtext('local_lsf_unification/roleid_teacher',
            get_string('roleid_teacher', 'local_lsf_unification'), get_string('roleid_teacher_description', 'local_lsf_unification'),
            $CFG->creatornewroleid, PARAM_INT));
    }

    if (!empty($CFG->defaultuserroleid)) {
        $settings->add(new admin_setting_configtext('local_lsf_unification/roleid_student',
            get_string('roleid_student', 'local_lsf_unification'), get_string('roleid_student_description', 'local_lsf_unification'),
            $CFG->defaultuserroleid, PARAM_INT));
    }

    $settings->add(new admin_setting_configcheckbox('local_lsf_unification/subcategories',
        get_string('subcategories', 'local_lsf_unification'), get_string('subcategories_description', 'local_lsf_unification'),
        0));
    $displaylist = core_course_category::make_categories_list();
    $settings->add(new admin_setting_configselect('local_lsf_unification/defaultcategory',
        get_string('defaultcategory', 'local_lsf_unification'), get_string('defaultcategory_description', 'local_lsf_unification'),
        1, $displaylist));

    $settings->add(new admin_setting_heading('heading_add_features', get_string('add_features', 'local_lsf_unification'), get_string('add_features_information', 'local_lsf_unification')));
    $settings->add(new admin_setting_configcheckbox('local_lsf_unification/remote_creation',
        get_string('remote_creation', 'local_lsf_unification'), get_string('remote_creation_description', 'local_lsf_unification'), 1));
    $settings->add(new admin_setting_configcheckbox('local_lsf_unification/restore_old_courses',
        get_string('restore_old_courses', 'local_lsf_unification'), get_string('restore_old_courses_description', 'local_lsf_unification'), 1));
    $settings->add(new admin_setting_configcheckbox('local_lsf_unification/restore_templates',
        get_string('restore_templates', 'local_lsf_unification'), get_string('restore_templates_description', 'local_lsf_unification'), 1));
    $settings->add(new admin_setting_configcheckbox('local_lsf_unification/enable_enrol_ext_db',
        get_string('enable_enrol_ext_db', 'local_lsf_unification'), get_string('enable_enrol_ext_db_description', 'local_lsf_unification'), 0));

    $settings->add(new admin_setting_configtext('local_lsf_unification/duplication_timeframe',
        get_string('duplication_timeframe', 'local_lsf_unification'), get_string('duplication_timeframe_description', 'local_lsf_unification'),
        5, PARAM_INT));

    $settings->add(new admin_setting_heading('heading_his_deeplink_via_soap', get_string('his_deeplink_heading', 'local_lsf_unification'), get_string('his_deeplink_information', 'local_lsf_unification')));
    $settings->add(new admin_setting_configcheckbox('local_lsf_unification/his_deeplink_via_soap',
        get_string('his_deeplink_via_soap', 'local_lsf_unification'), get_string('his_deeplink_via_soap_description', 'local_lsf_unification'), false));
    $settings->add(new admin_setting_configtext('local_lsf_unification/soapwsdl',
        get_string('soapwsdl', 'local_lsf_unification'), get_string('soapwsdl_description', 'local_lsf_unification'),
        '', PARAM_RAW));
    $settings->add(new admin_setting_configtext('local_lsf_unification/soapuser',
        get_string('soapuser', 'local_lsf_unification'), get_string('soapuser_description', 'local_lsf_unification'),
        '', PARAM_RAW));
    $settings->add(new admin_setting_configtext('local_lsf_unification/soappass',
        get_string('soappass', 'local_lsf_unification'), get_string('soappass_description', 'local_lsf_unification'),
        '', PARAM_RAW));
    $settings->add(new admin_setting_configtext('local_lsf_unification/moodle_url',
        get_string('moodle_url', 'local_lsf_unification'), get_string('moodle_url_description', 'local_lsf_unification'),
        $CFG->wwwroot, PARAM_RAW));

    $settings->add(new admin_setting_heading('heading_cal_import', get_string('importcalendar', 'local_lsf_unification'), get_string('his_deeplink_information', 'local_lsf_unification')));
    $settings->add(new admin_setting_configtext('local_lsf_unification/icalurl',
        get_string('icalurl', 'local_lsf_unification'), get_string('icalurl_description', 'local_lsf_unification'),
        'https://studium.uni-muenster.de/qisserver/rds?state=verpublish&status=transform&vmfile=no&moduleCall=iCalendarPlan&publishConfFile=reports&publishSubDir=veranstaltung&termine=', PARAM_RAW));

}
