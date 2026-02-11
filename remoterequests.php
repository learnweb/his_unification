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

use core\output\single_button;
use core_table\flexible_table;
use core\url;

define('NO_OUTPUT_BUFFERING', true);
require_once("../../config.php");
require_once("$CFG->libdir/adminlib.php");
require_once("./lib_his.php");
require_once("./lib_features.php");
require_once($CFG->dirroot . '/course/lib.php');
require_login();
admin_externalpage_setup('local_lsf_unification_helptable');

echo $OUTPUT->header();
echo $OUTPUT->heading('HISLSF Remote Requests');

$rid              = optional_param('requestid', -1, PARAM_INT);
$action           = optional_param('action', -1, PARAM_INT);
if (establish_secondary_DB_connection() === true) {
    echo "<p>&nbsp;</p>";

    if ($rid != -1 && $action != -1) {
        if ($action == 1) {
            $DB->delete_records("local_lsf_unification_course", ["id" => $rid, "mdlid" => 0]);
            echo $OUTPUT->box("<b>Anfrage (" . $rid . ") geloescht</b>");
        } else if ($action == 2 || $action == 3) {
            $request = get_course_request($rid);
            $veranstid = $request->veranstid;
            $requester = $DB->get_record("user", ["id" => $request->requesterid]);
            set_course_accepted($veranstid);
            $emailsent = send_course_creation_mail($requester, get_course_by_veranstid($veranstid));
            echo $OUTPUT->box("<b>Anfrage (" . $rid . ") wurde zugelassen und es wurde versucht eine Email an (" . $requester->firstname . " " . $requester->lastname . ") zu senden.<b>");
            if ($emailsent) {
                echo $OUTPUT->box("<b>Email erfolgreich versendet</b>");
            } else if (empty($user->email)) {
                echo $OUTPUT->box("<b>Email versenden <u>fehlgeschlagen</u><br>(Der Benutzer (" . $requester->firstname . " " . $requester->lastname . ") hat keine Emailadresse)</b>");
            } else {
                echo $OUTPUT->box("<b>Email versenden <u>fehlgeschlagen</u></b>");
            }
        } else if ($action == 4) {
            $request = get_course_request($rid);
            $veranstid = $request->veranstid;
            echo $OUTPUT->box("<b>Die folgende URL kann <u>nur vom Antragsteller</u> verwendet werden: <br>" . get_remote_creation_continue_link($veranstid) . "</b>");
        }
    }

    $table = new flexible_table("remoterequests");
    $table->define_baseurl(new url('remoterequests.php'));
    $table->define_columns(['requestid', 'course', 'requester', 'actions']);
    $table->define_headers(['Antrag Nr.', 'Veranstaltung', 'Personen', 'Aktionen']);
    $requests = $DB->get_records('local_lsf_course', ['mdlid' => 0], 'id');
    $table->setup();

    $helpfuntion = function ($arrayel) {
        return $arrayel->veranstid;
    };
    $courses = get_courses_by_veranstids(array_map($helpfuntion, $requests));

    foreach ($requests as $requestid => $request) {
        $course = $courses[$request->veranstid];
        $courseinfo = '<a href="' . $course->urlveranst . '">' . delete_bad_chars($course->titel) . "</a><br>" . $course->semestertxt;

        $requester = $DB->get_record("user", ["id" => $request->requesterid]);
        $person = '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $requester->id . '">' . $requester->firstname . " " . $requester->lastname . "</a>";
        if ($request->requeststate == 2) {
            $acceptor = $DB->get_record("user", ["id" => $request->acceptorid]);
            $person .= '<br>zugelassen durch <a href="' . $CFG->wwwroot . '/user/view.php?id=' . $acceptor->id . '">' . $acceptor->firstname . " " . $acceptor->lastname . "</a>";
        }

        $actions = [];
        $baseurl = new url('remoterequests.php', ['requestid' => $requestid]);
        if ($request->requeststate == 1) {
            $actions[] = new single_button(new url($baseurl, ['action' => 2]), 'Erlaubnis geben', 'get', single_button::BUTTON_PRIMARY);
        } else if ($request->requeststate == 2) {
            $actions[] = new single_button(new url($baseurl, ['action' => 3]), 'Email erneut senden', 'get');
            $actions[] = new single_button(new url($baseurl, ['action' => 4]), 'Erstellungs-URL anzeigen', 'get');
        }
        $actions[] = new single_button(new url($baseurl, ['action' => 1]), 'Löschen', 'get', single_button::BUTTON_DANGER);
        $actioncol = '';
        foreach ($actions as $action) {
            $actioncol .= '<p>' . $OUTPUT->render($action) . '</p>';
        }
        $table->add_data([$requestid, $courseinfo, $person, $actioncol]);
    }
    close_secondary_DB_connection();
    $table->finish_output();
}
echo $OUTPUT->footer();
