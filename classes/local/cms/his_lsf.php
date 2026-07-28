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

namespace local_lsf_unification\local\cms;

use DateTimeImmutable;
use DateTimeZone;
use Google\Service\Classroom\Teacher;
use local_lsf_unification\local\models\cms;
use local_lsf_unification\local\models\course;
use moodle_url;
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

    /**
     * Constructor.
     * @param stdClass $config The plugin configuration
     */
    public function __construct(stdClass $config) {
         $this->db = new PDO($this->connection_string_from($config));
    }

    /**
     * Returns connection string.
     * @param stdClass $config
     * @return string
     */
    private function connection_string_from(stdClass $config): string {
        $options = ['host' => $config->dbhost, 'port' => $config->dbport, 'user' => $config->dbuser, 'dbname' => $config->dbname];

        if (!empty($config->dbusessl)) {
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
        return 'pgsql:' . implode(' ', $parts);
    }

    #[\Override]
    public function get_courses_of_teacher(object $teacher): array {
        $maxage = get_config('local_lsf_unification', 'max_import_age');
        $rows = $this->db->query(
            "SELECT lv.veranstid, lv.veranstnr, lv.titel, lv.semestertxt, lv.urlveranst, lv.veranstaltungsart, lv.zeitstempel,
                COALESCE(lvk.description, '') AS description
            FROM learnweb_veranstaltung lv
            JOIN learnweb_personal_veranst lpv ON lpv.veranstid = lv.veranstid
            JOIN learnweb_personal lp ON lp.pid = lpv.pid
            LEFT JOIN LATERAL (
                SELECT string_agg(k.kommentar, '<br>' ORDER BY k.sprache) AS description
                FROM learnweb_veranst_kommentar k
                WHERE k.veranstid = lv.veranstid
            ) lvk ON true
            WHERE lp.login = '{$teacher->username}'
            AND (CURRENT_DATE - CAST(lv.zeitstempel AS date)) < '{$maxage}'
            ORDER BY semester, titel;",
            PDO::FETCH_OBJ
        );

        return array_map(
            fn($row) => new course(
                cms: cms::LSF->value,
                instance: (int) $row->veranstid,
                title: $row->titel,
                shorttitle: $row->veranstaltungsart,
                description: $row->description,
                teacher: $teacher->username,
                semester: $row->semestertxt,
                created:  (new DateTimeImmutable($row->zeitstempel, new DateTimeZone('Europe/Berlin')))->getTimestamp(),
                url: $row->urlveranst
            ),
            $rows->fetchAll()
        );
    }

    #[\Override]
    public function get_users_of_course(object $course): array {
        return [];
    }

    #[\Override]
    public function set_moodle_link(object $course, moodle_url $url): void {
        return;
    }

    #[\Override]
    public function get_course_dates(object $course): array {
        return [];
    }

    #[\Override]
    public function get_teachers(): array {
        $maxage = get_config('local_lsf_unification', 'max_import_age');
        $rows = $this->db->query(
            "SELECT DISTINCT lp.zivk, lp.vorname, lp.nachname
            FROM learnweb_personal lp
                JOIN learnweb_personal_veranst lpv ON lpv.pid = lp.pid
                JOIN learnweb_veranstaltung lv     ON lv.veranstid = lpv.veranstid
            WHERE (CURRENT_DATE - CAST(lv.zeitstempel AS date)) < '{$maxage}';",
            PDO::FETCH_OBJ
        );
        return array_map(
            fn($row) => (object) ['username' => $row->zivk, 'firstname' => $row->vorname, 'lastname' => $row->nachname],
            $rows->fetchAll()
        );
    }
}
