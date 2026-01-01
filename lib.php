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
 * Functions that aid core functionality
 * @package local_lsf_unification
 * @copyright 2025 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_lsf_unification\pg_lite;

defined('MOODLE_INTERNAL') || die();


require_once($CFG->libdir . '/enrollib.php');
require_once($CFG->dirroot . '/user/lib.php');

/**
 * get_course_by_idnumber returns the course's id, where idnumber fits $courseid
 *
 * @param int $courseid
 * @param bool $silent
 * @return int
 */
function get_course_by_idnumber(int $courseid, bool $silent = false): int {
    global $DB;
    $result = $DB->get_record('course', ['idnumber' => $courseid,
    ], 'id');
    $externid = isset($result->id) ? $result->id : -1;
    if (!$silent && (empty($externid) || $externid <= 0)) {
        throw new moodle_exception('course not found');
    }
    return $externid;
}

/**
 * creates a category by title
 *
 * @param string $title
 * @param string $parenttitle
 * @return stdClass
 */
function find_or_create_category(string $title, string $parenttitle): stdClass {
    global $DB;
    if (
        $category = $DB->get_record("course_categories", ["name" => $title,
        ])
    ) {
        return $category;
    }
    $parent = empty($parenttitle) ? 0 : (find_or_create_category($parenttitle, null)->id);
    $parent = empty($parent) ? 0 : $parent;
    $newcategory = new stdClass();
    $newcategory->name = $title;
    $newcategory->idnumber = null;
    $newcategory->parent = $parent;
    $newcategory->description = "";
    $newcategory->sortorder = 999;
    $newcategory->id = $DB->insert_record('course_categories', $newcategory);
    $newcategory->context = context_coursecat::instance($newcategory->id);
    $categorycontext = $newcategory->context;
    mark_context_dirty($newcategory->context->path);
    $DB->update_record('course_categories', $newcategory);
    fix_course_sortorder();
    return $newcategory;
}

/**
 * Check if user has the right to import a course.
 * @param int $veranstid
 * @param object $user
 * @return bool
 * @throws dml_exception
 */
function has_course_import_rights(int $veranstid, object $user): bool {
    global $DB;
    if (!is_course_of_teacher($veranstid, $user->username)) {
        if (
            $courseentry = $DB->get_record(
                "local_lsf_course",
                ["veranstid" => $veranstid, "requesterid" => $user->id,
                ]
            )
        ) {
            if ($courseentry->requeststate == 1) {
                // The user shouldn't be on this website because this link isn't known to him.
                echo ("Course cannot be requested.");
                return false;
            } else if ($courseentry->requeststate != 2) {
                // The course already exists, so the user shouldn't get here.
                echo ("Course already created.");
                return false;
            }
        } else {
            // The course isn't in the user's list and isn't requested by him remotely, so he shouldn't be here.
            echo ("Course cannot be requested.");
            return false;
        }
    }
    return true;
}

/**
 * Checks if a course was imported by the user.
 * @param int $mdlid
 * @param object $user
 * @return bool
 * @throws dml_exception
 */
function is_course_imported_by(int $mdlid, object $user): bool {
    global $DB;
    if (
        $courseentry = $DB->get_record(
            "local_lsf_course",
            ["mdlid" => $mdlid, "requesterid" => $user->id, "requeststate" => 2,
            ]
        )
    ) {
        return true;
    }
    return false;
}

/**
 * Get the acceptorid from a moodle course.
 * @param int $mdlid
 * @return int|null
 * @throws dml_exception
 */
function get_course_acceptor(int $mdlid): int|null {
    global $DB;

    if (
        $courseentry = $DB->get_record(
            "local_lsf_course",
            ["mdlid" => $mdlid, "requeststate" => 2,
            ]
        )
    ) {
        return $courseentry->acceptorid;
    }
    return null;
}

/**
 * enable_manual_enrolment does just what it sounds like
 *
 * @param object $course
 * @return void
 */
function enable_manual_enrolment(object $course): void {
    global $DB;

    $plugin = enrol_get_plugin('manual');
    $instanceid = $plugin->add_default_instance($course);
    $instance = $DB->get_record(
        'enrol',
        ['courseid' => $course->id, 'enrol' => 'manual', 'id' => $instanceid,
        ],
        '*',
        MUST_EXIST
    );
    $instance->roleid = get_config('local_lsf_unification', 'roleid_student');
    $DB->update_record('enrol', $instance);
}

/**
 * enable_lsf_enrolment does just what it sounds like
 *
 * @param int $id
 * @param int $enrolmentstart
 * @param int $enrolmentend
 * @return void
 * @throws coding_exception
 * @throws dml_exception
 */
function enable_lsf_enrolment(int $id, int $enrolmentstart, int $enrolmentend): void {
    global $DB;

    $course = $DB->get_record('course', ['id' => $id,
    ], '*', MUST_EXIST);
    $plugin = enrol_get_plugin('lsf');
    $fields = [
        'status' => ENROL_INSTANCE_ENABLED,
        'enrolperiod' => null,
        'roleid' => get_config('local_lsf_webservices', 'role_student'),
        'customint1' => $enrolmentstart,
        'customint2' => $enrolmentend,
    ];
    $plugin->add_instance($course, $fields);
}

/**
 * enable_self_enrolment deletes old and creates a new self enrolment instance
 *
 * @param object $course
 * @param string $password
 * @return void
 */
function enable_self_enrolment(object $course, string $password): void {
    global $DB;

    $plugin = enrol_get_plugin('self');
    $instanceid = $plugin->add_default_instance($course);
    $instance = $DB->get_record(
        'enrol',
        ['courseid' => $course->id, 'enrol' => 'self', 'id' => $instanceid,
        ],
        '*',
        MUST_EXIST
    );
    $instance->password = $password;
    $instance->roleid = get_config('local_lsf_unification', 'roleid_student');
    $instance->expirythreshold = 0;
    $DB->update_record('enrol', $instance);
}

/**
 * enable_database_enrolment deletes old and creates a new ext.
 * database enrolment instance
 *
 * @param object $course
 * @return void
 */
function enable_database_enrolment(object $course): void {
    global $DB;

    $plugin = enrol_get_plugin('database');
    $instanceid = $plugin->add_default_instance($course);
}

/**
 * Creates a guest enrolment.
 * @param object $course
 * @param string $password
 * @param bool $enable
 * @return void
 * @throws dml_exception
 */
function create_guest_enrolment(object $course, string $password = "", bool $enable = false): void {
    global $DB;

    $plugin = enrol_get_plugin("guest");
    $instanceid = $plugin->add_default_instance($course);
    $instance = $DB->get_record(
        'enrol',
        ['courseid' => $course->id, 'enrol' => 'guest', 'id' => $instanceid,
        ],
        '*',
        MUST_EXIST
    );
    $instance->status = ($enable ? ENROL_INSTANCE_ENABLED : ENROL_INSTANCE_DISABLED);
    if (!empty($password)) {
        $instance->password = $password;
    }
    $DB->update_record('enrol', $instance);
}

/**
 * self_enrolment_status returns the password for a course if possible, otherwise ""
 *
 * @param int $courseid
 * @return string $password | ""
 */
function self_enrolment_status(int $courseid): string {
    global $DB;
    return ($a = $DB->get_record('enrol', ["courseid" => $courseid, "enrol" => 'self',
    ])) ? ($a->password) : "";
}

/**
 * get_default_course returns a default course object
 *
 * @param string $fullname
 * @param int $idnumber
 * @param string $summary
 * @param string $shortname
 * @return stdClass $course
 */
function get_default_course(string $fullname, int $idnumber, string $summary, string $shortname): stdClass {
    // Check and format content.
    if (empty($shortname)) {
        $shortname = (strlen($fullname) < 20) ? $fullname : substr(
            $fullname,
            0,
            strpos($fullname . ' ', ' ', 20)
        );
    }
    // Create default object.
    $course = new stdClass();
    $course->fullname = substr($fullname, 0, 254);
    $course->idnumber = $idnumber;
    $course->summary = $summary;
    $course->summaryformat = 1;
    $course->shortname = $shortname;
    $course->startdate = time();
    $course->category = get_config('local_lsf_unification', 'default_category');
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
    $course->groupmode = get_config('moodlecourse', 'enter_groupmode');
    $course->groupmodeforce = get_config('moodlecourse', 'enter_groupmodeforce');
    $course->visible = get_config('moodlecourse', 'visible');
    $course->lang = get_config('moodlecourse', 'lang');
    $course->enablecompletion = get_config('moodlecourse', 'enablecompletion');
    $course->completionstartonenrol = get_config('moodlecourse', 'completionstartonenrol');
    return $course;
}

/**
 * get_or_create_support_user (creates if necessary and) returns a user with the correct
 * supportemail
 *
 * @return false|stdClass
 */
function get_or_create_support_user(): false|stdClass {
    global $DB, $CFG;
    $support = get_or_create_user("support." . md5($CFG->supportemail), $CFG->supportemail);
    return $support;
}

/**
 * Creates a new user or return existing one.
 * @param string $username
 * @param string $email
 * @return false|stdClass
 * @throws dml_exception
 * @throws moodle_exception
 */
function get_or_create_user(string $username, string $email): false|stdClass {
    global $DB, $CFG;
    if (!empty($username) && ($usr = $DB->get_record('user', ['username' => $username]))) {
        if (empty($usr->email)) {
            $usr->email = $email;
        }
        return $usr;
    } else if ($usr = $DB->get_record('user', ['email' => $email])) {
        return $usr;
    } else {
        $user['firstname'] = "";
        $user['lastname'] = "";
        $user['username'] = $username;
        $user['email'] = $email;
        $user['confirmed'] = true; // If confirmation is neccessary, confirm-key is needed.
        $user['mnethostid'] = $CFG->mnet_localhost_id;
        $user['auth'] = 'ldap'; // LEARNWEB-TODO default auth method should be configurable.
        $user['lang'] = $CFG->lang;
        $user['id'] = user_create_user($user);
        return $DB->get_record('user', ['id' => $user['id']]);
    }
}

/**
 * add_path_description adds path-descriptions to an array of categories
 *
 * @param array $choices that maps id to name
 * @return array that maps id to path
 */
function add_path_description(array $choices): array {
    global $DB;
    $result = [];
    foreach ($choices as $id => $name) {
        $cat = $DB->get_record("course_categories", ["id" => $id,
        ]);
        $path = explode("/", $cat->path);
        $result[$id] = "";
        foreach ($path as $pathid) {
            if (empty($pathid)) {
                $name = "";
            } else {
                $name = $DB->get_record(
                    "course_categories",
                    ["id" => $pathid,
                    ]
                )->name;
            }
            if (str_contains($name, 'Archiv')) {
                unset($result[$id]);
                break;
            }
            $result[$id] .= (empty($result[$id]) ? "" : " / ") . $name;
        }
    }
    return $result;
}

/**
 * Function to be run periodically according to the scheduled task.
 *
 * LEARNWEB-TODO: Since 2.7.2 this function is run by scheduled task rather
 * than standard cron.
 * @return void
 */
function local_lsf_unification_cron(): void {
    global $CFG, $pgdb;
    include_once(dirname(__FILE__) . '/class_pg_lite.php');
    include_once(dirname(__FILE__) . '/lib_features.php');

    $pgdb = new pg_lite();
    $connected = $pgdb->connect();
    $recourceid = $pgdb->connection;

    mtrace(
        '! = unknown category found, ? = unknown linkage found;' . 'Verbindung: ' .
        ($connected ? 'ja' : 'nein') . ' (' . $recourceid . ')'
    );

    insert_missing_helptable_entries(true, false);

    $pgdb->dispose();
}
