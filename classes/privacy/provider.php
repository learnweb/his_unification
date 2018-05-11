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
 * Privacy Subsystem implementation for lsf_unification.
 *
 * @package    lsf_unification
 * @copyright  2018  Nina Herrmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lsf_unification\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem for lsf_unification implementing metadata\provider.
 *
 * @copyright  2018 Nina Herrmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use core_privacy\local\metadata\collection;
class provider implements \core_privacy\local\metadata\provider {
    use \core_privacy\local\legacy_polyfill;
    public static function _get_metadata(collection $collection) {
        // The mod uses files and grades.
        $collection->add_database_table(
            'local_lsf_course',
            [
                'veranstid' => 'privacy:metadata:local_lsf_unification:veranstid',
                'mdlid' => 'privacy:metadata:local_lsf_unification:mdlid',
                'timestamp' => 'privacy:metadata:local_lsf_unification:timestamp',
                'requeststate' => 'privacy:metadata:local_lsf_unification:requeststate',
                'requesterid' => 'privacy:metadata:local_lsf_unification:requesterid',
                'acceptorid' => 'privacy:metadata:local_lsf_unification:acceptorid'

            ],
            'privacy:metadata:local_lsf_unification'
        );
        $collection->add_database_table(
            'local_lsf_category',
            [
                'ueid' => 'privacy:metadata:local_lsf_unification:ueid',
                'mdlid' => 'privacy:metadata:local_lsf_unification:mdlid',
                'origin' => 'privacy:metadata:local_lsf_unification:origin',
                'parent' => 'privacy:metadata:local_lsf_unification:parent',
                'txt' => 'privacy:metadata:local_lsf_unification:txt',
                'txt2' => 'privacy:metadata:local_lsf_unification:txt2',
                'timestamp' => 'privacy:metadata:local_lsf_unification:timestamp'
           ],
            'privacy:metadata:local_lsf_unification'
        );

        return $collection;
    }
}