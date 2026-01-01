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
 * The matchingtableupdated event.
 *
 * @package    local_lsf_unification
 * @copyright  2025 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_lsf_unification\event;
use core\exception\moodle_exception;
use moodle_url;

/**
 * The matchingtableupdated event class.
 *
 * @since     Moodle 2.7
 * @copyright 2014 Olaf Markus Koehler
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class matchingtable_updated extends \core\event\base {
    /**
     * Init function.
     * @return void
     */
    protected function init(): void {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_lsf_category';
    }

    /**
     * Returns the event name.
     * @return string
     * @throws \coding_exception
     */
    public static function get_name(): string {
        return get_string('eventmatchingtable_updated', 'local_lsf_unification');
    }

    /**
     * Returns the event description.
     * @return string
     */
    public function get_description(): string {
        $params = (object) [
            'userid' => $this->userid,
            'objectid' => $this->objectid,
            'mappingold' => $this->other["mappingold"],
            'mappingnew' => $this->other["mappingnew"],
        ];
        return get_string('eventmatchingtable_updated', 'local_lsf_unification', $params);
    }

    /**
     * Return the event url
     * @return moodle_url
     * @throws moodle_exception
     */
    public function get_url(): moodle_url {
        return new moodle_url('/local/lsf_unification/helptablemanager.php', ['originid' => $this->other["originid"]]);
    }
}
