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
defined('MOODLE_INTERNAL') || die();
/**
 * The matchingtableupdated event class.
 *
 * @since     Moodle 2.7
 * @copyright 2014 Olaf Markus Koehler
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class course_duplicated extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'course';
    }

    public static function get_name() {
        return get_string('eventcourse_duplicated', 'local_lsf_unification');
    }

    public function get_description() {
        return "The user with id '{$this->userid}' duplicated contents from course with id '{$this->other}' into a course with id '{$this->objectid}'.";
    }

    public function get_url() {
        return new \moodle_url('/course/view.php', ['id' => $this->objectid]);
    }
}
