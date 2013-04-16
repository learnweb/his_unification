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
$string['answer_course_already_created1'] = "The course exists on the HIS-LSF platform, but is not listed above.";

$string['answer_course_found'] = "The following list contains the course I'm looking for:";
$string['answer_goto_old_requestform'] = "The course exists on the HIS-LSF platform, but you are not registered for that course. Nevertheless you are authorized to create this course in the Learnweb.";

$string['info_course_already_created1'] = 'There are two potential reasons, why your course is not listed:<ol><li>Your course is published in HIS-LSF less than 24 hours ago. Data is only transferred once a day. If no further problem occurs, course data will be transferred tomorrow and then Learnweb course creation will be possible.</li><li>The username you use right now ({$a}) is not registered on the HIS-LSF. To access the courses you have registered on the HIS-LSF platform you have to include your username in your HIS-LSF-profile. After doing so, please wait at least one day for our database to update.<p style="text-align: center;"><img alt="Datenbearbeiten" src="/LearnWeb/diverse/HIS-Person_bearbeiten.png" /></p></li></ol>';

$string['info_goto_old_requestform'] = "Please use the standard form to apply for course creation. (<a href='../../course/request.php'>Link</a>)";

$string['config_auto_update'] = "Auto-Update from His-Lsf";
$string['config_auto_update_duration'] = "Keep assigned user up to date for (from now on)";
$string['config_auto_update_duration182'] = "1 semester";
$string['config_auto_update_duration31'] = "1 month";
$string['config_auto_update_duration365'] = "1 year";
$string['config_auto_update_duration7'] = "1 week";
$string['config_category'] = "Category";
$string['config_category_wish'] = "Category Relocation Wish";
$string['config_category_wish_help'] = "If you have a wish to get your course moved into a more specific category, please leave a comment here containing your wish-category and path.";
$string['config_enrolment_key'] = "Enrolment Key";
$string['config_enrolment_key_help'] = "One method for students to enrol for your course is to do this by typing in a password. If you like to activate this enrolementmethod, then specify your password wish. If you don't want this, just leave the textbox empty.";
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

$string['course_duplication_question'] = 'Do you want to copy the course contents of an old Learnweb course to your just created course? (This is the only chance to do this)';
$string['yes'] = 'Yes';
$string['no'] = 'No';
$string['skip'] = 'Skip';
$string['course_duplication_selection'] = 'Please select a backup-file to restore from:';