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
 * This page adds a hislsf calendar instance to the users calendars. It is accessible from the manage_calendars site.
 * To limit the changes to original moodle-files, the call of his_add_cal() is done here.
 *
 * @package local_lsf_unification
 * @copyright 2025 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('cal_lib.php');
require_login();
$back = required_param('back', PARAM_RAW);
$url = new moodle_url('/local/lsf_unification/cal_import.php');
$url->param('back', $back);
$PAGE->set_url($url);
his_add_cal();
redirect($back);
