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
 * Page that lists the helptable.
 *
 * @package   local_lsf_unification
 * @copyright 2025 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;

define('NO_OUTPUT_BUFFERING', true);

require_once("../../config.php");
global $CFG, $OUTPUT, $PAGE;
require_once("$CFG->libdir/adminlib.php");
require_once("./lib_his.php");
require_once($CFG->dirroot . '/course/lib.php');
require_login();
admin_externalpage_setup('local_lsf_unification_helptable');

$originid         = optional_param('originid', -1, PARAM_INT); // HIS category origin id.
$mainid           = optional_param('mainid', -1, PARAM_INT);   // HIS catecory id.
$mdlid            = optional_param('mdlid', -1, PARAM_INT);    // Moodle category id.
$maxorigin        = optional_param('maxorigin', 0, PARAM_INT); // Max (his origin ids).
$delete           = optional_param('delete', 0, PARAM_INT);    // Category id where to remove a matching.

$basepath = '/local/lsf_unification/helptablemanager.php';
$output = "";
if ($originid == -1) {
    require_capability('moodle/site:config', context_system::instance());
    $isadmin = has_capability('moodle/site:config', context_system::instance());
    $mustachedata = [
        'isadmin' => $isadmin,
        'createmappings' => new moodle_url($basepath, ['originid' => 0]),
    ];
    $PAGE->requires->js_call_amd('local_lsf_unification/update_helptable', 'init');
    $output = $OUTPUT->render_from_template('local_lsf_unification/helptable_manager/overview', $mustachedata);
} else if ($mainid == -1) {
    if (!empty($delete)) {
        set_cat_mapping($delete, 0);
    }
    if (empty($originid)) {
        $origins = implode(", ", get_his_toplevel_originids());
        $parents = ["" => "Lehrveranstaltungen"];
    } else {
        $origins = $originid;
        $id = $originid;
        $parents = [$id => " " . get_newest_element($id)->txt];
        while ($parent = get_newest_parent($id)) {
            foreach ($parents as $key => $txt) {
                $parents[$key] = $parents[$key];
            }
            if (($id == $parent->ueid) || ($id == $parent->origin)) {
                break;
            }
            $id = $parent->origin;
            $parents[$id] = " " . $parent->txt;
        }
    }
    $parentstxt = [];
    foreach (array_reverse($parents, true) as $id => $txt) {
        $navigate = get_string('navigate', 'local_lsf_unification');
        $parentstxt[] = [
            'parentlink' => new moodle_url($basepath, ['originid' => $id]),
            'parenttext' => $txt,
        ];
    }
    $sublevels = get_newest_sublevels($origins);
    $childs = [];
    foreach ($sublevels as $child) {
        $child->mdlid = get_mdlid($child->origin);
        $child->name = empty($child->mdlid) ? "" : get_mdlname($child->origin);
        $maxorigin = ($child->origin > $maxorigin) ? $child->origin : $maxorigin;
        $childs[] = [
            'sublevels' => has_sublevels($child->ueid),
            'childoriginlink' => new moodle_url($basepath, ['originid' => $child->origin]),
            'idchildorigin' => "idch_" . ($child->origin),
            'prefixchildtxt' => $child->txt,
            'emptychildmdlid' => empty($child->mdlid),
            'categorylink' => new moodle_url('/course/index.php', ['categoryid' => $child->mdlid]),
            'childname' => $child->name,
            'namechildorigin' => 'ch_' . ($child->origin),
            'deletelink' => new moodle_url($basepath, ['originid' => $originid, 'delete' => $child->origin]),
        ];
    }
    $maincategories = get_mdl_toplevels();
    $optionsdata = [];
    foreach ($maincategories as $id => $txt) {
        $optionsdata[] = ['value' => $id, 'text' => $txt->name];
    }
    $mustachedata = [
        'parents' => $parentstxt,
        'childs' => $childs,
        'options' => $optionsdata,
        'originid' => $originid,
        'maxorigin' => $maxorigin,
    ];
    $output = $OUTPUT->render_from_template('local_lsf_unification/helptable_manager/childlist', $mustachedata);
} else if ($mdlid == -1) {
    $hiddenfields = "";
    $childs = [];
    $hiddenfields = [];
    for ($i = 0; $i <= $maxorigin; $i++) {
        if (isset($_GET["ch_" . $i])) {
            $hiddenfields[] = ['hiddenname' =>  'ch_' . $i];
            $childs[] = ['childtext' => get_newest_element($i)->txt];
        }
    }
    $subcats = get_mdl_sublevels($mainid);
    $displaylist = [];
    $displaylist = core_course_category::make_categories_list();
    $options = [];
    foreach ($displaylist as $id => $txt) {
        if (isset($subcats[$id])) {
            $options[] =  ['optionid' => $id, 'optiontext' => $txt];
        }
    }
    $mustachedata = [
        'childs' => $childs,
        'options' => $options,
        'originid' => $originid,
        'mainid' => $mainid,
        'maxorigin' => $maxorigin,
        'hiddenfields' => $hiddenfields,
    ];
    $output = $OUTPUT->render_from_template('local_lsf_unification/helptable_manager/mapping_submit', $mustachedata);
} else {
    $count = 0;
    for ($i = 0; $i <= $maxorigin; $i++) {
        if (isset($_GET["ch_" . $i])) {
            set_cat_mapping($i, $mdlid);
            $count++;
        }
    }
    $string = $count . " " . get_string('map_done', 'local_lsf_unification');
    redirect(new moodle_url($basepath, ['originid' => 0]), $string);
}

echo $OUTPUT->header();
echo $OUTPUT->heading('HISLSF Helptable Management');
echo $output;
echo $OUTPUT->footer();
