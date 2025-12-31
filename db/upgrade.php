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
 * This file keeps track of upgrades to the community block
 *
 * Sometimes, changes between versions involve alterations to database structures
 * and other major things that may break installations.
 *
 * The upgrade function in this file will attempt to perform all the necessary
 * actions to upgrade your older installation to the current version.
 *
 * If there's something it cannot do itself, it will tell you what you need to do.
 *
 * The commands in here will all be database-neutral, using the methods of
 * database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @since 2.0
 * @package local_lsf_unification
 * @copyright 2010 Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade script for lsf_unification
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_lsf_unification_upgrade(int $oldversion): bool {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013050700) {
        // Lsf_unification needs a new role from now on.
        require_once($CFG->dirroot . '/local/lsf_unification/db/install.php');
        xmldb_local_lsf_unification_install_course_creator_role();

        // Define key uni2 (unique) to be dropped form local_lsf_course.
        $table = new xmldb_table('local_lsf_course');
        $key = new xmldb_key('uni2', XMLDB_KEY_UNIQUE, ['mdlid']);

        // Launch drop key uni2.
        $dbman->drop_key($table, $key);

        // Lsf_unification savepoint reached.
        upgrade_plugin_savepoint(true, 2013050700, 'local', 'lsf_unification');
    }

    if ($oldversion < 2013060400) {
        // Define field requeststate to be added to local_lsf_course.
        $table = new xmldb_table('local_lsf_course');
        $field = new xmldb_field('requeststate', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '0', 'ueid');

        // Conditionally launch add field requeststate.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Lsf_unification savepoint reached.
        upgrade_plugin_savepoint(true, 2013060400, 'local', 'lsf_unification');
    }

    if ($oldversion < 2013061100) {
        // Define field requeststate to be added to local_lsf_course.
        $table = new xmldb_table('local_lsf_course');
        $field = new xmldb_field('ueid');

        // Conditionally launch drop field ueid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('requesterid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'requeststate');

        // Conditionally launch add field requesterid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('acceptorid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'requesterid');

        // Conditionally launch add field acceptorid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Lsf_unification savepoint reached.
        upgrade_plugin_savepoint(true, 2013061100, 'local', 'lsf_unification');
    }
    if ($oldversion < 2013090300) {
        if (get_config('enrol_self', 'version') > 2012120600) {
            // Lsf courses did not set customerint6 for self enrolments. This is the fix for already created self enrolments.
            $DB->execute("UPDATE {enrol} SET customint6 = 1 WHERE enrol = 'self' and customint6 is null");
        }
        // Lsf_unification savepoint reached.
        upgrade_plugin_savepoint(true, 2013090300, 'local', 'lsf_unification');
    }

    return true;
}
