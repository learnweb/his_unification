<?php
/**
 * Functions that aid core functionality
**/
defined('MOODLE_INTERNAL') || die;

	require_once("$CFG->dirroot/group/lib.php");
    require_once($CFG->libdir . '/enrollib.php');
    require_once($CFG->dirroot . '/user/lib.php');

	/**
	 * get_course_by_idnumber returns the course's id, where idnumber fits $courseid
	 * 
	 * @param $courseid
	 * @return $externid | -1
	 */
	function get_course_by_idnumber($courseid, $silent = false)
	{
	    global $DB;
		$result = $DB->get_record('course', array('idnumber' => $courseid), 'id');
		$externid = isset($result->id)?$result->id:-1;
		if (!$silent && (empty($externid) || $externid <= 0))
		    throw new moodle_exception('course not found');
		return $externid;
	}	

	/**
	 * creates a category by title
	 * 
	 * @param $title
	 * @return $parent_title | null 
	 */
	function find_or_create_category($title,$parent_title) {
		global $DB;
		if ($category = $DB->get_record("course_categories",array("name"=>$title))) {
			return $category;
		}
		$parent = empty($parent_title)?0:(find_or_create_category($parent_title,null)->id);
		$parent = empty($parent)?0:$parent;
		$newcategory = new stdClass();
		$newcategory->name = $title;
		$newcategory->idnumber = null;
		$newcategory->parent = $parent;
        $newcategory->description = "";
        $newcategory->sortorder = 999;
        $newcategory->id = $DB->insert_record('course_categories', $newcategory);
        $newcategory->context = get_context_instance(CONTEXT_COURSECAT, $newcategory->id);
        $categorycontext = $newcategory->context;
        mark_context_dirty($newcategory->context->path);
		$DB->update_record('course_categories', $newcategory);
		fix_course_sortorder();
		return $newcategory;
	}
	
	
	/**
	 * enable_manual_enrolment does just what it sounds like
	 * 
	 * @param $id
     * @return null
	 */
	function enable_manual_enrolment($id)
	{		
        global $DB;
		$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
		$plugin = enrol_get_plugin('manual');
        $fields = array('status'=>ENROL_INSTANCE_ENABLED, 'enrolperiod'=>null, 'roleid'=>get_config('local_lsf_webservices', 'role_student'));
        $plugin->add_instance($course, $fields);
	}
	
	/**
	 * enable_self_enrolement does just what it sounds like
	 * 
	 * @param $course
	 * @param $password
     * @return null
	 */
	function update_self_enrolment($course,$password,$delete_old_self_enrolment_plugins=false) {
		global $DB;
		if ($delete_old_self_enrolment_plugins) $DB->delete_records('enrol', array("courseid"=>$course->id,"enrol"=>'self'));
		//if ($password == "") return;
		$plugin = enrol_get_plugin('self');
	    $fields = array('status'=>ENROL_INSTANCE_ENABLED, 'name'=>"", 'password'=>$password, 'customint1'=>$plugin->get_config('groupkey'), 'customint2'=>$plugin->get_config('longtimenosee'),
                        'customint3'=>$plugin->get_config('maxenrolled'), 'customint4'=>$plugin->get_config('sendcoursewelcomemessage'), 'customtext1'=>"",
                        'roleid'=>$plugin->get_config('roleid'), 'enrolperiod'=>$plugin->get_config('enrolperiod'), 'enrolstartdate'=>0, 'enrolenddate'=>0);
        $plugin->add_instance($course, $fields);
	}

	/**
	 * self_enrolment_status returns the password for a course if possible, otherwise ""
	 * 
	 * @param $courseid
     * @return $password | ""
	 */
	function self_enrolment_status($courseid) {
		global $DB;
		return ($a = $DB->get_record('enrol',array("courseid"=>$courseid,"enrol"=>'self')))?($a->password):"";
	}
	
	/**
	 * get_default_course returns a default course object
	 * 
	 * @param $fullname
	 * @param $idnumber
	 * @param $summary
	 * @param $shortname
	 * @param $startdate
	 * @return $course
	 */
	function get_default_course($fullname, $idnumber, $summary, $shortname)
	{
		// check&format content
		if(empty($shortname))
			$shortname = (strlen($fullname) < 20) ? $fullname : substr($fullname,0,strpos($fullname.' ',' ',20));
		// create default object
		$course = new stdClass;
		$course->fullname = substr($fullname,0,254);
		$course->idnumber = $idnumber;
		$course->summary = $summary;
		$course->shortname = $shortname;
		$course->startdate = time();
		$course->category = get_config('local_lsf_webservices', 'default_category');
		$course->expirythreshold = '864000';
		$course->timecreated = time();
		$course->format = get_config('moodlecourse', 'format');
		$course->maxsections = get_config('moodlecourse', 'maxsections');
		$course->numsections = get_config('moodlecourse', 'numsections');
		$course->hiddensections = get_config('moodlecourse', 'hiddensections');
		$course->newsitems = get_config('moodlecourse', 'newsitems');
		$course->showgrades = get_config('moodlecourse', 'showgrades');
		$course->showreports = get_config('moodlecourse', 'showreports');
		$course->maxbytes = get_config('moodlecourse', 'maxbytes');
		$course->coursedisplay = get_config('moodlecourse', 'coursedisplay');
		$course->groupmode = get_config('local_lsf_webservices', 'enter_groupmode');
		$course->groupmodeforce = get_config('local_lsf_webservices', 'enter_groupmodeforce');
		$course->visible = get_config('moodlecourse', 'visible');
		$course->lang = get_config('moodlecourse', 'lang');
		$course->enablecompletion = get_config('moodlecourse', 'enablecompletion');
		$course->completionstartonenrol = get_config('moodlecourse', 'completionstartonenrol');
		return $course;
	}
	
	/**
	 * get_or_create_support_user (creates if necessary and) returns a user with the correct supportemail
	 * 
	 * @param $fullname
	 * @param $idnumber
	 * @param $summary
	 * @param $shortname
	 * @param $startdate
	 * @return $course
	 */
	function get_or_create_support_user() {
		global $DB, $CFG;
		if (!($support = $DB->get_record('user', array('email'=>$CFG->supportemail)))) {
			$user['firstname'] = "General";
			$user['lastname'] = "Support";
			$user['username'] = "support.".md5($CFG->supportemail);
			$user['email'] = $CFG->supportemail;
			$user['confirmed'] = true;
			$user['mnethostid'] = $CFG->mnet_localhost_id;
			$user['id'] = user_create_user($user);
			return $DB->get_record('user', array('id'=>$user['id']));
		} else {
			return $support;
		}
	}
	
	/**
	 * add_path_description adds path-descriptions to an array of categories
	 * 
	 * @param array that maps id to name
	 * @return array that maps id to path
	 */
	function add_path_description($choices) {
		global $DB;
		$result = array();
		foreach ($choices as $id => $name) {
			$cat = $DB->get_record("course_categories", array("id"=>$id));
			$path = explode("/", $cat->path);
			$choices[$id] = "";
			foreach ($path as $pathid) {
				$choices[$id] .= (empty($choices[$id])?"":" / ").(empty($pathid)?"":($DB->get_record("course_categories", array("id"=>$pathid))->name));
			}
		}
		return $choices;
	}