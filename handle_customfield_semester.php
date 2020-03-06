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
 * Wrapper for commonly used functions for a customfield named semester defining 27 entries from ss07 to ws20/21
 *
 * @copyright 2020 NinaHerrmann, WWU Muenster
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package ?
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Updates or insert the customfield in the 'customfield_data' table.
 * @param $currentsemester int current_semester
 * @param $courseid int ID of the current course
 */
function update_customfield_semester($currentsemester, $courseid){
    global $DB;
    $customfield = $DB->get_record('customfield_field', array('name' =>  'Semester', 'type' => 'select'));
    $customfieldcontroller = \customfield_date\field_controller::create($customfield->id);
    $configdata = $customfieldcontroller->get('configdata');
    $semesterinarray = explode("\n", $configdata['options']);
    if (array_key_exists($currentsemester, $semesterinarray)){
        $previouscustomfield = $DB->get_record('customfield_data', array('instanceid' => $courseid));
        $numericalrepresentation = $currentsemester + 1;
        // In case we have data for a previous field update in case it changed.
        if ($DB->get_record('customfield_data', array('instanceid' => $courseid))) {
            if (!$previouscustomfield->value == $numericalrepresentation) {
                $previouscustomfield->value = $numericalrepresentation;
                $DB->update_record('customfield_data', $previouscustomfield);
            }
        } else {
            // Otherwise create an object and insert it into table.
            $context = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSE, 'instanceid' => $courseid));
            $currenttimestamp = time();
            $dataobject = (object) [
                'fieldid' => $customfield->id,
                'instanceid' => $courseid,
                'intvalue' => $numericalrepresentation,
                'value' => $numericalrepresentation,
                'valueformat' => 0,
                'timecreated' => $currenttimestamp,
                'timemodified' => $currenttimestamp,
                'contextid' => $context->id
            ];
            $DB->insert_record('customfield_data', $dataobject);
        }
    } else {
        // The key does not exist in the available choices so we will make it a no semester course.
        $context = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSE, 'instanceid' => $courseid));
        $currenttimestamp = time();
        $dataobject = (object) [
            'fieldid' => $customfield->id,
            'instanceid' => $courseid,
            'intvalue' => 1,
            'value' => 1,
            'valueformat' => 0,
            'timecreated' => $currenttimestamp,
            'timemodified' => $currenttimestamp,
            'contextid' => $context->id
        ];
        $DB->insert_record('customfield_data', $dataobject);
    }
}