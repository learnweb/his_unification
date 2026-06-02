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

namespace local_lsf_unification;

use PDO;
use stdClass;

/**
 * Connection to the LSF database.
 *
 * @package   local_lsf_unification
 * @copyright 2026 Daniel Meißner <daniel.meissner@uni-muenster.de>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class his_lsf implements campus_management {
    /** @var PDO The database connection to LSF */
    private PDO $db;
    public function __construct(
        /** @var stdClass The plugin configuration */
        private readonly stdClass $config
    ) {
         $this->db = new PDO($this->connection_string_from($config));
    }

    private function connection_string_from(stdClass $config): string {
        $options = [
            'host' => $config->dbhost,
            'port' => $config->dbport,
            'user' => $config->dbuser,
            'dbname' => $config->dbname,
        ];

        if ($config->dbusessl) {
            $options['sslmode'] = 'verify-full';
            $options['sslrootcert'] = $config->dbsslrootcert;
            $options['sslcert'] = $config->dbsslcert;
            $options['sslkey'] = $config->dbsslkey;
        } else {
            $options['password'] = $config->dbpass;
        }

        $parts = [];
        foreach ($options as $key => $val) {
            $escaped = addcslashes((string) $val, "\\'");
            $parts[] = "$key='$escaped'";
        }
        return implode(' ', $parts);
    }

    #[\Override]
    public function courses_of_teacher(stdClass $teacher): array {
        $courses = $this->db->query("
SELECT
    lv.veranstid,
    lv.veranstnr,
    lv.titel,
    lv.semestertxt,
    lv.urlveranst
FROM
    learnweb_veranstaltung lv
    JOIN learnweb_personal_veranst lpv ON lpv.veranstid = lv.veranstid
    JOIN learnweb_personal lp ON lp.pid = lpv.pid
WHERE
    lp.login = '{$teacher->username}'
",
            PDO::FETCH_OBJ);

        return array_map(
            function($course) {
                $course->info = $course->titel . "&nbsp;&nbsp;(" .
                                $course->semestertxt .
                                '<a href="' . $course->urlveranst . '"> KVV-Nr. ' . $course->veranstnr . '</a>)';
                return $course;
            },
            $courses->fetchAll()
        );
    }
}
