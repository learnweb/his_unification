<?php
/**
 * Functions that are used by request.php
**/
defined('MOODLE_INTERNAL') || die;

	require_once($CFG->dirroot . '/local/lsf_unification/lib.php');
	require_once($CFG->dirroot . '/local/lsf_unification/lib_his.php');

	/**
	 * creates a course
	 * source code very close to course/lib.php: create_course()
	 *
	 * @param $veranstid idnumber
	 * @param $fullname
	 * @param $shortname
	 * @param $summary
	 * @param $startdate
	 * @param $password
	 * @param $category (id)
	 * @return array consisting of the course-object and warnings
	 */
	function create_lsf_course($veranstid, $fullname, $shortname, $summary, $startdate, $password, $category) {
		global $DB, $USER, $CFG;
		$transaction = $DB->start_delegated_transaction();
		$warnings = "";
		if (course_exists($veranstid)) {
			die("course already exists");
		}
		// create course 
		$course = get_default_course($fullname, $veranstid, $summary, $shortname);
		$course->category = empty($category)?(find_or_create_category("HISLSF",null)->id):($category);
		$course->startdate = $startdate;
		$course->id = $DB->insert_record('course', $course);		
		if ($course->id == false)
			throw new moodle_exception('course not created: '.$DB->get_last_error());
		$course = $DB->get_record("course", array("id" => $course->id));
		// create context
		$context = get_context_instance(CONTEXT_COURSE, $course->id);
		// setup default blocks
		blocks_add_default_course_blocks($course);
		$section = new stdClass();
		$section->course        = $course->id;   // Create a default section.
		$section->section       = 0;
		$section->summaryformat = FORMAT_HTML;
		$DB->insert_record('course_sections', $section);
		
		// enable enrollment
		enable_manual_enrolment($course->id);
		
		// enrole creator
		enrol_try_internal_enrol($course->id, $USER->id, get_config('local_lsf_unification', 'roleid_creator'));
		
		// enrole teachers
		$warnings .= enrole_teachers($veranstid, $course->id);
		
		// enable self-enrolemen
		update_self_enrolment($course, $password);
		
		// TODO enrole students (defaultuserroleid)
		// TODO create hislsf-update-job table entry
		
		// create course in helptable
		set_course_created($veranstid, $course->id);
		
		$transaction->allow_commit();
		return array("course"=>$course,"warnings"=>$warnings);
	}
	
	/**
	 * sends mail to support regarding category moving wishes
	 *
	 * @param $course
	 * @param $text
	 */
	 function send_support_mail($course, $text) {
		global $USER;
		$supportuser = get_or_create_support_user();
		$params = new stdClass();
		$params->a = $USER->firstname." ".$USER->lastname;
		$params->b = $USER->id;
		$params->c = $course->fullname;
		$params->d = $course->id;
		$params->e = $text;
		$content = get_string('email','local_lsf_unification',$params);
		return email_to_user($supportuser, "HIS LSF Import", get_string('config_category_wish','local_lsf_unification'),$content);
	 }
	 
	 function get_my_courses_as_teacher() {
	 	global $DB, $USER, $CFG;
	 	$helpfuntion1 = function($array_el) {
	 		return $array_el->instanceid;
	 	};
	 	$sql = "SELECT ".$CFG->prefix."role_assignments.id, instanceid, roleid FROM ".$CFG->prefix."role_assignments JOIN ".$CFG->prefix."context ON ".$CFG->prefix."role_assignments.contextid = ".$CFG->prefix."context.id WHERE ".$CFG->prefix."role_assignments.roleid=".$CFG->creatornewroleid." AND ".$CFG->prefix."role_assignments.userid=$USER->id AND ".$CFG->prefix."context.contextlevel=50";
	 	return array_map($helpfuntion1, $DB->get_records_sql($sql));
	 }
	 
	 function get_backup_files() {
	 	global $DB, $USER;
		//disable restore feature temporarily
	 	$backuppath = get_config('backup','backup_auto_destination').'/test';
	 	$result = array();
		$copies = implode("|", get_my_courses_as_teacher());
	 	if (!($handle = opendir($backuppath))) return $result;
	 	while (false !== ($entry = readdir($handle))) {
	 		$matches = array();
	 		if (preg_match('/^sicherung-moodle2-course-('.$copies.')-(\d{4})(\d{2})(\d{2})-(\d{2})(\d{2})\.mbz$/mi',$entry,$matches)) {
	 			$file = new stdClass();
	 			$file->name = $entry;
	 			$file->path = $backuppath;
	 			$file->datetime = "$matches[5]:$matches[6] $matches[4].$matches[3].$matches[2]";
	 			$file->course = $DB->get_record("course", array("id"=>$matches[1]), "id, fullname, shortname");
	 			$result[md5($entry."_".$USER->id)] = $file;
	 		}
	 	}
	 	closedir($handle);
	 	return $result;
	 }
	 
	 function duplicate_course($courseid, $foldername) {
	 	global $DB, $USER;	 	
	 	$transaction = $DB->start_delegated_transaction();
	 	
	 	// Restore backup into course
	 	$controller = new restore_controller($foldername, $courseid,
	 			backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $USER->id,
	 			backup::TARGET_EXISTING_ADDING);
	 	
	 	$restoresettings = array (
	 			'users' => 0,               // Include enrolled users (default = 1)
	 			'role_assignments' => 0,    // Include user role assignments (default = 1)
	 			'activities' => 1,          // Include activities (default = 1)
	 			'blocks' => 1,              // Include blocks (default = 1)
	 			'filters' => 1,             // Include filters (default = 1)
	 			'comments' => 0,            // Include comments (default = 1)
	 			'userscompletion' => 0,     // Include user completion details (default = 1)
	 			'logs' => 0,                // Include course logs (default = 0)
	 			'grade_histories' => 0      // Include grade history (default = 0)
	 	);
	 	foreach ($controller->get_plan()->get_tasks() as $taskindex => $task) { 
	 		if ($taskindex == 0) {
           		foreach ($restoresettings as $key => $value) {
            		$settings = $task->get_settings();
            		foreach ($settings as $settingindex => $setting) {
                 		if ($setting->get_name() == $key) {
                    	  	$setting->set_value($value);
                 		}
            		}
            	}
	 		}
	 	}
		if ($controller->get_status() == backup::STATUS_REQUIRE_CONV) {
            $controller->convert();
        }
	 	$controller->execute_precheck();
	 	$controller->execute_plan();
	 	
	 	// Commit
	 	$transaction->allow_commit();
	 }
	 
	 
	 function unzip($zipfile, $destination = '', $showstatus_ignored = true) {
	 	global $CFG;
	 	//Extract everything from zipfile
	 	$path_parts = pathinfo(cleardoubleslashes($zipfile));
	 	$zippath = $path_parts["dirname"];       //The path of the zip file
	 	$zipfilename = $path_parts["basename"];  //The name of the zip file
	 	$extension = $path_parts["extension"];    //The extension of the file
	 	//If no file, error
	 	if (empty($zipfilename))
	 		return false;
	 	//If no extension, error
	 	if (empty($extension))
	 		return false;
	 	//Clear $zipfile
	 	$zipfile = cleardoubleslashes($zipfile);
	 	//Check zipfile exists
	 	if (!file_exists($zipfile))
	 		return false;
	 	//If no destination, passed let's go with the same directory
	 	if (empty($destination))
	 		$destination = $zippath;
	 	//Clear $destination
	 	$destpath = rtrim(cleardoubleslashes($destination), "/");
	 	//Check destination path exists
	 	if (!is_dir($destpath))
	 		return false;
	 	$packer = get_file_packer('application/zip');
	 	$result = $packer->extract_to_pathname($zipfile, $destpath);
	 	if ($result === false)
	 		return false;
	 	foreach ($result as $status) {
	 		if ($status !== true)
	 			return false;
	 	}
	 	return true;
	 }