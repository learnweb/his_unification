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
    }
}

/**
 * @param $current_semester_string
 * @return int
 */
function calculate_customfieldnumber_from_string($current_semester_string){
    $numericalrepresentation = 0;
    switch($current_semester_string) {
        case 'WS20/21':
            $numericalrepresentation = 29;
            break;
        case 'SS20':
            $numericalrepresentation = 28;
            break;
        case 'WS19/20':
            $numericalrepresentation = 27;
            break;
        case 'SS19':
            $numericalrepresentation = 26;
            break;
        case 'WS18/19':
            $numericalrepresentation = 25;
            break;
        case 'SS18':
            $numericalrepresentation = 24;
            break;
        case 'WS17/18':
            $numericalrepresentation = 23;
            break;
        case 'SS17':
            $numericalrepresentation = 22;
            break;
        case 'WS16/17':
            $numericalrepresentation = 21;
            break;
        case 'SS16':
            $numericalrepresentation = 20;
            break;
        case 'WS15/16':
            $numericalrepresentation = 19;
            break;
        case 'SS15':
            $numericalrepresentation = 18;
            break;
        case 'WS14/15':
            $numericalrepresentation = 17;
            break;
        case 'SS14':
            $numericalrepresentation = 16;
            break;
        case 'WS13/14':
            $numericalrepresentation = 15;
            break;
        case 'SS13':
            $numericalrepresentation = 14;
            break;
        case 'WS12/13':
            $numericalrepresentation = 13;
            break;
        case 'SS12':
            $numericalrepresentation = 12;
            break;
        case 'WS11/12':
            $numericalrepresentation = 11;
            break;
        case 'SS11':
            $numericalrepresentation = 10;
            break;
        case 'WS10/11':
            $numericalrepresentation = 9;
            break;
        case 'SS10':
            $numericalrepresentation = 8;
            break;
        case 'WS09/10':
            $numericalrepresentation = 7;
            break;
        case 'SS09':
            $numericalrepresentation = 6;
            break;
        case 'WS08/09':
            $numericalrepresentation = 5;
            break;
        case 'SS08':
            $numericalrepresentation = 4;
            break;
        case 'WS07/08':
            $numericalrepresentation = 3;
            break;
        case 'SS07':
            $numericalrepresentation = 2;
            break;
        case 'No Semester':
            $numericalrepresentation = 1;
            break;
    }
    return $numericalrepresentation;
}