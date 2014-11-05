<?php
define('NO_OUTPUT_BUFFERING', true);
require_once("../../config.php");
require_once("$CFG->libdir/adminlib.php");
require_once("./lib_his.php");
require_once("./lib_features.php");
require_once($CFG->dirroot.'/course/lib.php');
require_login();
admin_externalpage_setup('local_lsf_unification_helptable');

echo $OUTPUT->header();
echo $OUTPUT->heading('HISLSF Remote Requests');

$rid              = optional_param('requestid', -1, PARAM_INT);
$action           = optional_param('action', -1, PARAM_INT);
if (establish_secondary_DB_connection()===true) {

    echo "<p>&nbsp;</p>";

    if ($rid != -1 && $action != -1) {
        if ($action == 1) {
            $DB->delete_records("local_lsf_course", array("id" => $rid, "mdlid" => 0));
            echo $OUTPUT->box("<b>Anfrage (".$rid.") geloescht</b>");
        } elseif ($action == 2 || $action == 3) {
            $request = get_course_request($rid);
            $veranstid = $request->veranstid;
            $requester = $DB->get_record("user", array("id" => $request->requesterid));
            set_course_accepted($veranstid);
            $emailsent = send_course_creation_mail($requester,get_course_by_veranstid($veranstid));
            echo $OUTPUT->box("<b>Anfrage (".$rid.") wurde zugelassen und es wurde versucht eine Email an (".$requester->firstname." ".$requester->lastname.") zu senden.<b>");
            if ($emailsent) {
                echo $OUTPUT->box("<b>Email erfolgreich versendet</b>");
            } elseif (empty($user->email)) {
                echo $OUTPUT->box("<b>Email versenden <u>fehlgeschlagen</u><br>(Der Benutzer (".$requester->firstname." ".$requester->lastname.") hat keine Emailadresse)</b>");
            } else {
                echo $OUTPUT->box("<b>Email versenden <u>fehlgeschlagen</u></b>");
            }
        } elseif ($action == 4) {
            $request = get_course_request($rid);
            $veranstid = $request->veranstid;
            echo $OUTPUT->box("<b>Die folgende URL kann <u>nur vom Antragsteller</u> verwendet werden: <br>".get_remote_creation_continue_link($veranstid)."</b>");
        }
    }
    $helpfuntion = function($array_el) {
        return $array_el->veranstid;
    };
    $requests = get_course_requests();
    $courses = get_courses_by_veranstids(array_map($helpfuntion, $requests));
    echo "<p>&nbsp;<br><table>";
    $i = 0;
    foreach ($requests as $requestid => $request) {
        echo '<tr bgcolor="'.(($i++ % 2 == 0)?"#FFFFFF":"#CCCCCC").'">';
        echo "<td>".$requestid."</td>";
        $requester = $DB->get_record("user", array("id" => $request->requesterid));
        $course = $courses[$request->veranstid];
        echo '<td><a href="'.$course->urlveranst.'">'.delete_bad_chars($course->titel)."</a></td>";
        echo '<td nowrap>'.$course->semestertxt."</td>";
        echo '<td nowrap><a href="'.$CFG->wwwroot.'/user/view.php?id='.$requester->id.'">'.$requester->firstname." ".$requester->lastname."</a>";
        if ($request->requeststate == 2) {
            $acceptor = $DB->get_record("user", array("id" => $request->acceptorid));
            echo '<br>zugelassen durch <a href="'.$CFG->wwwroot.'/user/view.php?id='.$acceptor->id.'">'.$acceptor->firstname." ".$acceptor->lastname."</a>";
        }
        echo "</td>";
        echo '<td nowrap><a href="remoterequests.php?action=1&requestid='.$requestid.'">[loeschen]</a>';
        if ($request->requeststate == 1) {
            echo '<br><a href="remoterequests.php?action=2&requestid='.$requestid.'">[erlaubnis geben]</a>';
        } elseif ($request->requeststate == 2) {
            echo '<br><a href="remoterequests.php?action=3&requestid='.$requestid.'">[email erneut senden]</a>';
            echo '<br><a href="remoterequests.php?action=4&requestid='.$requestid.'">[erstellungs-url anzeigen]</a>';
        }
        echo "</tr>";
    }
    echo "</table></p>";
    close_secondary_DB_connection();
}
echo $OUTPUT->footer();