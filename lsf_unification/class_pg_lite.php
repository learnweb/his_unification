<?php
defined('MOODLE_INTERNAL') || die;

class pg_lite {

    public $connection = null;

    public function connect() {
        $config = "host='".get_config('local_lsf_unification', 'dbhost')."' port ='".get_config('local_lsf_unification', 'dbport')."' user='".get_config('local_lsf_unification', 'dbuser')."' password='".get_config('local_lsf_unification', 'dbpass')."' dbname='".get_config('local_lsf_unification', 'dbname')."'";
        ob_start();
        $this->connection = pg_connect($config, PGSQL_CONNECT_FORCE_NEW);
        $dberr = ob_get_contents();
        ob_end_clean();
        echo $dberr;
        return ((pg_connection_status($this->connection) === false) || (pg_connection_status($this->connection) === PGSQL_CONNECTION_BAD))?$dberr:true;
    }

    public function dispose() {
        if ($this->connection) {
            pg_close($this->connection);
            $this->connection = null;
        }
    }

}