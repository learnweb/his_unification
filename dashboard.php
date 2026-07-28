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
 * Dashboard of lsf_unification.
 *
 * @package   local_lsf_unification
 * @copyright 2026 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
global $CFG, $DB, $PAGE, $USER, $SESSION, $OUTPUT;
require_once($CFG->dirroot . '/local/lsf_unification/locallib.php');
require_login();

lsf_unification_cache_strings();
$PAGE->set_url(new moodle_url('/local/lsf_unification/dashboard.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_lsf_unification'));

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_lsf_unification/dashboard/dashboard', []);
echo $OUTPUT->footer();
