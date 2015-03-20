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
 * Question engine upgrade helper langauge strings.
 *
 * @package    local
 * @subpackage qeupgradehelper
 * @copyright  2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



$string['pluginname'] = 'LSF Unification';
$string['plugintitle'] = 'LSF Unification';

$string['delete'] = 'delete';
$string['warnings'] = 'Warnings:';
$string['select'] = 'Submit';
$string['back'] = 'Back';
$string['navigate'] = 'navigate';
$string['map'] = 'map';
$string['map_done'] = 'mapping(s) saved';
$string['mapped'] = 'mapped';
$string['not_mapped'] = 'not mapped';
$string['choose_course'] = 'Please select the course you want to create.';
$string['create_mappings'] = 'Create New Mappings';
$string['main_category'] = 'Maincateogry';
$string['overwrite'] = 'overwrite';
$string['sub_category'] = 'Subcategory';
$string['update_helptable'] = 'Update Helptable With HIS-LSF data';

$string['dbhost'] = 'Host';
$string['dbhost_description'] = 'PostgreDB-Host';
$string['dbport'] = 'Port';
$string['dbport_description'] = 'PostgreDB-Port';
$string['dbname'] = 'Name';
$string['dbname_description'] = 'PostgreDB-Name';
$string['dbpass'] = 'Pass';
$string['dbpass_description'] = 'PostgreDB-Password';
$string['dbuser'] = 'User';
$string['dbuser_description'] = 'PostgreDB-User';
$string['db_not_available'] = "The Import-Services is currently not available. Please use the standard request-form. (<a href='../../course/request.php'>Link</a>)";
$string['defaultcategory'] = 'Default Category';
$string['defaultcategory_description'] = 'If no category can be matched this category will be offered';
$string['max_import_age'] = 'Max Import Age';
$string['max_import_age_description'] = 'The maximum age, a course may have before being imported.';
$string['roleid_creator'] = 'RoleID Creator';
$string['roleid_creator_description'] = 'role for coursecreators';
$string['roleid_student'] = 'RoleID Student';
$string['roleid_student_description'] = 'role for autoassigned students';
$string['roleid_teacher'] = 'RoleID Teacher';
$string['roleid_teacher_description'] = 'role for autoassigned teachers';
$string['subcategories'] = 'Unlock Subcategories';
$string['subcategories_description'] = 'Enable choosing not mapped subcategories of mapped categories';


$string['notice'] = 'Usually Learnweb copies course information from the HIS database and provides these information below. This requires that you are assigned to the course in HIS-LSF as a teacher or assistant. Furthermore your ZIV account has to be assigned to your HIS-LSF profile. If your course is not listed, please choose from the other provided options.';
$string['question'] = "Please select the first accurate statement:";
$string['answer_course_found'] = "The following list contains the course I'm looking for:";
$string['answer_course_in_lsf_and_visible'] = "The course exists on the HIS-LSF platform and you are registered as a teacher for that course.";
$string['answer_proxy_creation'] = "The course exists on the HIS-LSF platform and you are authorized to create this course on behalf of a registered teacher.";
$string['answer_goto_old_requestform'] = "None of the above apply and you are authorized to create this course in the Learnweb.";

$string['info_course_in_lsf_and_visible'] = 'There are two potential reasons, why your course is not listed:<ol><li>Your course is published in HIS-LSF less than 24 hours ago. Data is only transferred once a day. If no further problem occurs, course data will be transferred tomorrow and then Learnweb course creation will be possible.</li><li>The username you use right now ({$a}) is not registered on the HIS-LSF. To access the courses you have registered on the HIS-LSF platform you have to include your username in your HIS-LSF-profile. After doing so, please wait at least one day for our database to update.<p style="text-align: center;"><img alt="Datenbearbeiten" src="http://www.uni-muenster.de/LearnWeb/diverse/HIS-Person_bearbeiten.png" /></p></li></ol>';

$string['info_goto_old_requestform'] = "Please use the standard form to apply for course creation. (<a href='../../course/request.php'>Link</a>)";

$string['config_auto_update'] = "Auto-Update from His-Lsf";
$string['config_auto_update_duration'] = "Keep newly assigned users up to date for";
$string['config_auto_update_duration182'] = "1 semester since startdate";
$string['config_auto_update_duration31'] = "1 month since startdate";
$string['config_auto_update_duration7'] = "1 week since startdate";
$string['config_auto_update_duration-1'] = "never";
$string['config_category'] = "Category";
$string['config_category_wish'] = "Category Relocation Wish";
$string['config_category_wish_help'] = "If you have a wish to get your course moved into a more specific category, please leave a comment here containing your wish-category and path.";
$string['config_enrol'] = "Enrolment";
$string['config_dbenrolment'] = "HISLSF Enrolment";
$string['config_dbenrolment_help'] = "One method for students to enrol for your course is to do so by enroling themself in the HISLSF. Their enrolments will be automatically synchronized when they log into Learnweb.";
$string['config_selfenrolment'] = "Self Enrolment";
$string['config_selfenrolment_help'] = "One method for students to enrol for your course is to do this by clicking an enrolment button and optionally typing in a password.";
$string['config_enrolment_key'] = "Self Enrolment Key";
$string['config_enrolment_key_help'] = "If you only want students with knowledge of a specific password to enrol, then specify your password wish. If you want every student to be able to enrol, just leave the textbox empty.";
$string['config_misc'] = "Miscellaneous";
$string['config_shortname'] = "Shortname";
$string['config_summary'] = "Summary";
$string['config_summary_desc'] = "(Will be displayed in course-search)";

$string['categoryinvalid'] = 'please choose a category from this selection';
$string['email_error'] = 'The category wish wasn\'t sent. Please contact the support team manually. ('.$CFG->supportemail.')';
$string['email_success'] = 'Email regarding category wish sent.';
$string['new_request'] = 'request another course';
$string['noConnection'] = "A connection to LSF-Database couldn't be established. Please use the regular <a href='../../course/request.php'>formular</a>.";
$string['shortnamehint'] = 'shortname must contain {$a} at the end';
$string['shortnameinvalid'] = 'shortname is invalid (it must contain {$a} at the end)';
$string['warning_cannot_enrol_nologin'] = "person wasn't enrolled (no username found)";
$string['warning_cannot_enrol_nouser'] = "person wasn't enrolled (no user found)";
$string['warning_cannot_enrol_other'] = "person wasn't enrolled";

$string['next_steps'] = "Next Steps";
$string['linktext_users'] = "Edit the assigned teachers and students ...";
$string['linktext_course'] = "... or go directly to your new couse.";


$string['email'] = 'SENDER:
{$a->a} ('.$CFG->wwwroot.'/user/view.php?id={$a->b})

    COURSE:
    {$a->c} ('.$CFG->wwwroot.'/course/view.php?id={$a->d})

        MESSAGE:
        {$a->e}';


$string['choose_teacher'] = 'Please enter the username of the authorizing teacher:';
$string['his_info'] = 'Please inform your teacher to follow <a href="request.php?answer=3">this guide</a>, for the HIS-LSF courses to be matched to the username.';
$string['answer_course_in_lsf_but_invisible'] = 'The course is not listed above, but exists on the HIS-LSF platform and {$a} is registered as a teacher for that course.';
$string['already_requested'] = 'Sorry, this course was already requested and the teacher has to reply to this request before a new request can be issued';
$string['request_sent'] = 'Request sent, please wait for an answer, that you will recieve via email.';
$string['answer_sent'] = 'Thank you for processing this request, your decision is sent to the requester.';

$string['email_from'] = "HIS LSF Import";
$string['email2_title'] = "Course Creation Request";
$string['email2'] = 'The user "{$a->a}" ({$a->b}) requested to create the Learnweb-course "{$a->c}" in your name. Please confirm or refuse the request on this website: {$a->d}';
$string['email3_title'] = "Course Creation Request accepted";
$string['email3'] = 'The user "{$a->a}" ({$a->b}) accepted your request to create the Learnweb-course "{$a->c}". Please continue the course-creation on this website: {$a->d}';
$string['email4_title'] = "Course Creation Request declined";
$string['email4'] = 'The user "{$a->a}" ({$a->b}) declined your request to create the Learnweb-course "{$a->c}".';
$string['remote_request_select_alternative'] = 'Please select the action you want to perform:';
$string['remote_request_accept'] = 'Accept request by "{$a->a}" to create the course "{$a->b}"';
$string['remote_request_decline'] = 'Decline request and send the regarding information to "{$a->a}"';

$string['no_template'] = 'Alternative {$a}: Leave the course blank course and continue';
$string['pre_template'] = 'Alternative {$a}: Continue with content from a course template ...';
$string['template_from_course'] = 'Alternative {$a}: Include contents from an existing course ...';
$string['continue'] = 'Continue';
$string['continue_with_empty_course'] = 'Leave the course blank and continue';

$string['duplication_timeframe_error'] = 'Sorry but for safety reasons it is not allowed import course data from templates or backups more than {$a} hour(s) after course creation';

$string['add_features'] = 'Additional Features';
$string['add_features_information'] = 'configure additional features here';

$string['remote_creation'] = 'Remote Course Creation';
$string['remote_creation_description'] = 'Allow everyone to request courses in the name of a teacher, who than has to confirm';
$string['restore_old_courses'] = 'Duplicating Courses';
$string['restore_old_courses_description'] = 'Allow a course creator to duplicate course contents by restoring from a course backup of his courses';
$string['restore_templates'] = 'Course from Template';
$string['restore_templates_description'] = 'Allow a course creator to add standard course contents by restoring from a course backup functioning as template';
$string['enable_enrol_ext_db'] = 'Enable external database enrolement';
$string['enable_enrol_ext_db_description'] = 'Teacher can choose if he wants to use external database enrolement. External database enrolment must be enabled and it must be changed so that it can be enabled for particular courses only';
$string['duplication_timeframe'] = 'Course Duplication Timeframe';
$string['duplication_timeframe_description'] = 'The number of hours after course creation where restore-actions are allowed';

$string['his_deeplink_heading'] = 'HIS Deeplink Web Service settings';
$string['his_deeplink_information'] = 'HIS Deeplink Web Service calls a HIS web service that adds a link to the his course pointing to the moodle course';
$string['soappass_description'] = 'Password for the HIS Deeplink service';
$string['soappass'] = 'Password';
$string['soapuser_description'] = 'Username for the HIS Deeplink service';
$string['soapuser'] = 'soapuser';
$string['soapwsdl_description'] = 'Wdsl link for the HIS Deeplink service';
$string['soapwsdl'] = 'soapwsdl';
$string['his_deeplink_via_soap_description'] = 'Enable the HIS Deeplink web service';
$string['his_deeplink_via_soap'] = 'Enable HIS Deeplink';
$string['moodle_url'] = 'Moodle www root';
$string['moodle_url_description'] = 'URL to Moodle www root, which is used to create the link for the his course';

$string['importcalendar'] = 'HISLSF timetable';
$string['importical'] = 'import timetable';
$string['icalurl'] = 'ICal URL';
$string['icalurl_description'] = 'URL that points to the HisLSF Ical Export (the list of relevant event ids will be dynamically appended)';

$string['eventcourse_imported'] = 'Course imported';
$string['eventmatchingtable_updated'] = 'Matchingtable updated';  
$string['eventcourse_duplicated'] = 'Course duplicated';  

$string['restore_templates_category'] = 'Template Category';
$string['restore_templates_category_description'] = 'Category ID that contains the template courses (one sublevel of categories allowed)';  

