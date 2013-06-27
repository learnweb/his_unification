<?php
define('NO_OUTPUT_BUFFERING', true);
require_once("../../config.php");
require_once("$CFG->libdir/adminlib.php");
require_once("./lib_his.php");
require_once($CFG->dirroot.'/course/lib.php');
require_login();
admin_externalpage_setup('local_lsf_unification_deeplink_remove');

echo $OUTPUT->header();
echo $OUTPUT->heading('HISLSF Deeplink Removal');

$veranstid = optional_param('veranstid', -1, PARAM_INT);         // his course id

$formcontent = "HisLSF-id: <input type='text' name='veranstid' value=''> &nbsp; <input type='submit' value='Remove Deeplink'>";
echo "<form action='' method='get' class='mform'><p>".$formcontent."</p></form>";

if ($veranstid != -1) {
	echo "<p>".$OUTPUT->box(removeHisLink($veranstid)?"Link in HIS-LSF-Entry $veranstid removed.":"Error!")."</p>";
}

echo $OUTPUT->footer();