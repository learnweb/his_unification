<?php
defined('MOODLE_INTERNAL') || die;

class pg_lite_sap {

    public $connection = null;

    public function connect() {
        $config = "host='".get_config('local_lsf_unification', 'dbhost_sap')."' port ='".get_config('local_lsf_unification', 'dbport_sap')."' user='".get_config('local_lsf_unification', 'dbuser_sap')."' password='".get_config('local_lsf_unification', 'dbpass_sap')."' dbname='".get_config('local_lsf_unification', 'dbname_sap')."'";
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