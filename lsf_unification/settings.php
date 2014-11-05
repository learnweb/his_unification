<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) { // needs this condition or there is error on login page

    $settings = new admin_settingpage('local_lsf_unification', 'LSF Unification Config');
    $ADMIN->add('localplugins', $settings);

    $settings2 = new admin_externalpage('local_lsf_unification_helptable', 'LSF Unification Matchings', $CFG->wwwroot.'/local/lsf_unification/helptablemanager.php');
    $ADMIN->add('localplugins', $settings2);
    
    $settings3 = new admin_externalpage('local_lsf_unification_deeplink_remove', 'LSF Deeplink Removal', $CFG->wwwroot.'/local/lsf_unification/deeplink_remove.php');
    $ADMIN->add('localplugins', $settings3);
    
    $settings4 = new admin_externalpage('local_lsf_unification_remoterequests', 'LSF Remote Request Handling', $CFG->wwwroot.'/local/lsf_unification/remoterequests.php');
    $ADMIN->add('localplugins', $settings4);

    /*
     $settings->add(new admin_setting_configcheckbox('local_lsf_unification/send_errors',
         get_string('send_errors', 'local_lsf_unification'), get_string('send_errors_description', 'local_lsf_unification'),
         0));
    $settings->add(new admin_setting_configselect('local_lsf_unification/log_mode',
        get_string('log_mode', 'local_lsf_unification'), get_string('log_mode_description', 'local_lsf_unification'),
        1, array(	0 => get_string('log_mode_0', 'local_lsf_unification'),
                        1 => get_string('log_mode_1', 'local_lsf_unification'),
                        2 => get_string('log_mode_2', 'local_lsf_unification'))));
    */

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
    $settings->add(new admin_setting_configtext('local_lsf_unification/roleid_teacher',
        get_string('roleid_teacher', 'local_lsf_unification'), get_string('roleid_teacher_description', 'local_lsf_unification'),
        $CFG->creatornewroleid, PARAM_INT));
    $settings->add(new admin_setting_configtext('local_lsf_unification/roleid_student',
        get_string('roleid_student', 'local_lsf_unification'), get_string('roleid_student_description', 'local_lsf_unification'),
        $CFG->defaultuserroleid, PARAM_INT));
    $settings->add(new admin_setting_configcheckbox('local_lsf_unification/subcategories',
        get_string('subcategories', 'local_lsf_unification'), get_string('subcategories_description', 'local_lsf_unification'),
        0));
    $displaylist = coursecat::make_categories_list();
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

    /*
     $settings->add(new admin_setting_configselect('local_lsf_unification/enter_groupmode',
         get_string('enter_groupmode', 'local_lsf_unification'), get_string('enter_groupmode_description', 'local_lsf_unification'),
         1, array(	0 => get_string('enter_groupmode_0', 'local_lsf_unification'),
                         1 => get_string('enter_groupmode_1', 'local_lsf_unification'),
                         2 => get_string('enter_groupmode_2', 'local_lsf_unification'))));
    //$settings->add(new admin_setting_configcheckbox('local_lsf_unification/replace_template_patterns',
        //	get_string('replace_template_patterns', 'local_lsf_unification'), get_string('replace_template_patterns_description', 'local_lsf_unification'),
        //	1));
    $settings->add(new admin_setting_configtext('local_lsf_unification/new_user_auth',
        get_string('new_user_auth', 'local_lsf_unification'), get_string('new_user_auth_description', 'local_lsf_unification'),
        'ldap', PARAM_AUTH));
    $settings->add(new admin_setting_configtext('local_lsf_unification/new_user_mnethostid',
        get_string('new_user_mnethostid', 'local_lsf_unification'), get_string('new_user_mnethostid_description', 'local_lsf_unification'),
        $CFG->mnet_localhost_id, PARAM_INT));
    $settings->add(new admin_setting_configtext('local_lsf_unification/new_user_institution',
        get_string('new_user_institution', 'local_lsf_unification'), get_string('new_user_institution_description', 'local_lsf_unification'),
        'WWU', PARAM_RAW));
    $settings->add(new admin_setting_configtext('local_lsf_unification/new_user_department',
        get_string('new_user_department', 'local_lsf_unification'), get_string('new_user_department_description', 'local_lsf_unification'),
        '', PARAM_RAW));
    $settings->add(new admin_setting_configtext('local_lsf_unification/new_user_city',
        get_string('new_user_city', 'local_lsf_unification'), get_string('new_user_city_description', 'local_lsf_unification'),
        'Muenster', PARAM_RAW));
    $settings->add(new admin_setting_configtext('local_lsf_unification/new_user_country',
        get_string('new_user_country', 'local_lsf_unification'), get_string('new_user_country_description', 'local_lsf_unification'),
        'DE', PARAM_RAW));
    $settings->add(new admin_setting_configtext('local_lsf_unification/new_user_lang',
        get_string('new_user_lang', 'local_lsf_unification'), get_string('new_user_lang_description', 'local_lsf_unification'),
        'de', PARAM_ALPHANUMEXT));
    $settings->add(new admin_setting_configtext('local_lsf_unification/role_course_creator',
        get_string('role_course_creator', 'local_lsf_unification'), get_string('role_course_creator_description', 'local_lsf_unification'),
        2, PARAM_INT));
    $settings->add(new admin_setting_configtext('local_lsf_unification/role_teacher',
        get_string('role_teacher', 'local_lsf_unification'), get_string('role_teacher_description', 'local_lsf_unification'),
        3, PARAM_INT));
    $settings->add(new admin_setting_configtext('local_lsf_unification/role_student',
        get_string('role_student', 'local_lsf_unification'), get_string('role_student_description', 'local_lsf_unification'),
        5, PARAM_INT));
    */
}