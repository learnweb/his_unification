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
 * Class for database connection.
 * @package    local_lsf_unification
 * @copyright  2025 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_lsf_unification;

use PgSql\Connection;
use stdClass;

/**
 * Class that wraps a connection to psql.
 * @package    local_lsf_unification
 * @copyright  2025 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pg_lite {
    /** @var null|Connection Represents the connection. */
    public $connection = null;

    /**
     * Connect to psql.
     * @return bool|string
     * @throws dml_exception
     */
    public function connect() {
        $connstring = $this->connection_string_from(get_config('local_lsf_unification'));
        ob_start();
        $this->connection = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);
        $dberr = ob_get_contents();
        ob_end_clean();
        echo $dberr;
        $failedconnection = pg_connection_status($this->connection) === PGSQL_CONNECTION_BAD;
        return ($failedconnection) ? $dberr : true;
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

    /**
     * Dispose connection to psql.
     * @return void
     */
    public function dispose() {
        if ($this->connection) {
            pg_close($this->connection);
            $this->connection = null;
        }
    }
}
