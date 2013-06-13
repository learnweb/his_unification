<?php
// This file replaces:
//   * STATEMENTS section in db/install.xml
//   * lib.php/modulename_install() post installation hook
//   * partially defaults.php
defined('MOODLE_INTERNAL') || die();

/**
 * @package local
 * @subpackage lsf_unification
 * @author Olaf Markus Köhler (WWU)
 */

 function xmldb_local_lsf_unification_install() {
    xmldb_local_lsf_unification_install_course_creator_role();
 }
 
function xmldb_local_lsf_unification_install_course_creator_role() {
    global $DB;
    $result = true;
    $sysctx  = get_context_instance(CONTEXT_SYSTEM);
    $levels = array(CONTEXT_COURSECAT, CONTEXT_COURSE, CONTEXT_MODULE);

    /// Fully setup the restore role.
    if (!$mrole = $DB->get_record('role', array('shortname' => 'lsfunificationcourseimporter'))) {
        if ($rid = create_role('LSF Unification Course Importer', 'lsfunificationcourseimporter', '','coursecreator')) {
            $result = $result & assign_capability('moodle/restore:restorecourse', CAP_ALLOW, $rid, $sysctx->id);
            $result = $result & assign_capability('moodle/restore:restoreactivity', CAP_ALLOW, $rid, $sysctx->id);
            $result = $result & assign_capability('moodle/restore:restoresection', CAP_ALLOW, $rid, $sysctx->id);
            $result = $result & assign_capability('moodle/restore:configure', CAP_ALLOW, $rid, $sysctx->id);
            set_role_contextlevels($rid, $levels);
        } else {
            $result = false;
        }
    }
    return $result;
}