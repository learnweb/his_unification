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
 * Page for Deeplink Removal process.
 * @package local_lsf_unification
 * @copyright 2025 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);
require_once("../../config.php");
require_once("$CFG->libdir/adminlib.php");
require_once("./lib_his.php");
require_once($CFG->dirroot . '/course/lib.php');
require_login();
admin_externalpage_setup('local_lsf_unification_deeplink_remove');

echo $OUTPUT->header();
echo $OUTPUT->heading('HISLSF Deeplink Removal');

$veranstid = optional_param('veranstid', -1, PARAM_INT);         // his course id

$formcontent = "HisLSF-id: <input type='text' name='veranstid' value=''> &nbsp; <input type='submit' value='Remove Deeplink'>";
echo "<form action='' method='get' class='mform'><p>" . $formcontent . "</p></form>";

if ($veranstid != -1) {
    echo "<p>" . $OUTPUT->box(removeHisLink($veranstid) ? "Link in HIS-LSF-Entry $veranstid removed." : "Error!") . "</p>";
}

echo $OUTPUT->footer();
