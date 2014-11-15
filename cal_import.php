<?php
/**
 * This page adds a hislsf calendar instance to the users calendars. It is accessible from the manage_calendars site.
 * To limit the changes to original moodle-files, the call of his_add_cal() is done here.
 */
require_once('../../config.php');
require_once('cal_lib.php');
require_login();
$back = required_param('back', PARAM_RAW);
$url = new moodle_url('/local/lsf_unification/cal_import.php');
$url->param('back', $back);
$PAGE->set_url($url);
his_add_cal();
redirect($back);