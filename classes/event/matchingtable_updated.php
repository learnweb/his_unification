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
 * @package    FULLPLUGINNAME
 * @copyright  2014 YOUR NAME
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
class matchingtable_updated extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'u'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_lsf_category';
    }
 
    public static function get_name() {
        return get_string('eventmatchingtable_updated', 'local_lsf_unification');
    }
 
    public function get_description() {
        return "The user with id '{$this->userid}' updated a his category matching with id '{$this->objectid}'. Original mapping: '{$this->other["mappingold"]}'. New mapping: '{$this->other["mappingnew"]}'.";
    }
 
    public function get_url() {
        return new \moodle_url('/local/lsf_unification/helptablemanager.php', array('originid' => $this->other["originid"]));
    }
}
