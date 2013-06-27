<?php
define('NO_OUTPUT_BUFFERING', true);
require_once("../../config.php");
require_once("$CFG->libdir/adminlib.php");
require_once("./lib_his.php");
require_once($CFG->dirroot.'/course/lib.php');
require_login();
admin_externalpage_setup('local_lsf_unification_helptable');

echo $OUTPUT->header();
echo $OUTPUT->heading('HISLSF Helptable Management');

$originid         = optional_param('originid', -1, PARAM_INT);       // his category origin id
$mainid        	  = optional_param('mainid', -1, PARAM_INT);         // his catecory id
$mdlid         	  = optional_param('mdlid', -1, PARAM_INT);          // moodle category id
$maxorigin        = optional_param('maxorigin', 0, PARAM_INT);       // max (his origin ids)
$delete           = optional_param('delete', 0, PARAM_INT);          // category id where to remove a matching

if ($originid == -1) {
	echo "<p>".$OUTPUT->box('<a href="./update_helptable.php">'.get_string('update_helptable','local_lsf_unification').'</a>')."</p>";
	echo "<p>".$OUTPUT->box('<a href="?originid=0">'.get_string('create_mappings','local_lsf_unification').'</a>')."</p>";
} elseif ($mainid == -1) {
	if (!empty($delete)) set_cat_mapping($delete, 0);
	$prefix = "-";
	if (empty($originid)) {
		$origins = implode(", ", get_his_toplevel_originids());
		$parents = array("" => "- Lehrveranstaltungen");
		$prefix .= "-";
	} else {
		$origins = $originid;
		$id = $originid;
		$parents = array($id => " ".get_newest_element($id)->txt);
		while ($parent = get_newest_parent($id)) {
			foreach ($parents as $key => $txt) $parents[$key] = "-".$parents[$key];
			$prefix .= "-";
			if (($id == $parent->ueid) || ($id == $parent->origin)) break;
			$id = $parent->origin;
			$parents[$id] = " ".$parent->txt;
		}
	}
	$parentstxt = "";
	foreach ($parents as $id => $txt) {
		$parentstxt = " [<a href='?originid=".$id."'>".get_string('navigate','local_lsf_unification')."</a>] ".$txt."<br>".$parentstxt;
	}
	$sublevels = get_newest_sublevels($origins);
	$childlist = "";
	foreach ($sublevels as $child) {
		$child->mdlid = get_mdlid($child->origin);
		$child->name = empty($child->mdlid)?"":get_mdlname($child->origin);
		$maxorigin = ($child->origin > $maxorigin)?$child->origin:$maxorigin;
		$childlist .= "<tr>".((!has_sublevels($child->origin))?"<td>&nbsp;</td>":("<td nowrap='nowrap'>&nbsp;[<a href='?originid=".($child->origin)."'>".get_string('navigate','local_lsf_unification')."</a>]</td>"))."<td><label for='idch_".($child->origin)."'>".$prefix." ".($child->txt)."</label></td><td nowrap='nowrap'>&nbsp;[".(empty($child->mdlid)?get_string('not_mapped','local_lsf_unification'):("<a href='../../course/category.php?id=".($child->mdlid)."'>".($child->name)."</a>"))."]</td><td nowrap='nowrap'>&nbsp;[<input id='idch_".($child->origin)."' type='checkbox' name='ch_".($child->origin)."' value='x'><label for='idch_".($child->origin)."'> ".get_string(empty($child->mdlid)?'map':'overwrite','local_lsf_unification')."</label>]</td>".((empty($child->mdlid))?"<td>&nbsp;</td>":("<td nowrap='nowrap'>&nbsp;[<a href='?originid=".$originid."&delete=".$child->origin."'>".get_string('delete','local_lsf_unification')."</a>]</td>"))."</tr>";
	}
	$maincategories = get_mdl_toplevels();
	$options = "";
	foreach ($maincategories as $id => $txt) {
		$options .= "<option value='".$id."'>".$txt->name."</option>";
	}
	$catchoice = "<b>".get_string('main_category','local_lsf_unification')."</b>: <select name='mainid'>".$options."</select> &nbsp; <input type='submit' value='".get_string('map','local_lsf_unification')."'><input type='hidden' name='originid' value='".$originid."'><input type='hidden' name='maxorigin' value='".$maxorigin."'>";
	echo "<form action='' method='get' class='mform'><p>".$OUTPUT->box($parentstxt)."</p><p>".$OUTPUT->box("<table>".$childlist."</table>")."</p><p>".$OUTPUT->box($catchoice)."</p></form>";
} elseif ($mdlid == -1) {
	$hiddenfields = "";
	$childlist = "";
	for ($i=0;$i<=$maxorigin;$i++) {
		if (isset($_GET["ch_".$i])) {
			$hiddenfields .= "<input type='hidden' name='ch_".$i."' value='x'>";
			$childlist .= (empty($childlist)?"":"<br>")."-".(get_newest_element($i)->txt)."";
		}
	}
	$subcats = get_mdl_sublevels($mainid);
	$displaylist = array();
	$notused = array();
	make_categories_list($displaylist, $notused);
	$options = "";
	foreach ($displaylist as $id => $txt) {
		if (isset($subcats[$id])) $options .= "<option value='".$id."'>".$txt."</option>";
	}
	echo "<form action='' method='get' class='mform'><p>".$OUTPUT->box("<p>".$childlist."</p><b>=&gt;</b><p><b>".get_string('sub_category','local_lsf_unification')."</b>: <select name='mdlid'>".$options."</select></p>")."</p><input type='hidden' name='originid' value='".$originid."'><input type='hidden' name='mainid' value='".$mainid."'><input type='hidden' name='maxorigin' value='".$maxorigin."'>".$hiddenfields."<input type='submit' value='".get_string('map','local_lsf_unification')."'></form>";
} else {
	$mapchilds = array();
	$maptxt = "";
	$count = 0;
	for ($i=0;$i<=$maxorigin;$i++) {
		if (isset($_GET["ch_".$i])) {
			$maptxt .= $i."-".$mdlid."<br>";
			set_cat_mapping($i,$mdlid);
			$count++;
		}
	}
	echo "<p>".$OUTPUT->box($maptxt."<b>".$count." ".get_string('map_done','local_lsf_unification'))."</b></p>";
	echo "<p>".$OUTPUT->box('<a href="?originid=0">'.get_string('create_mappings','local_lsf_unification').'</a>')."</p>";
}
echo $OUTPUT->footer();