<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Question engine upgrade helper language strings.
 *
 * @package    local
 * @subpackage qeupgradehelper
 * @copyright  2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



$string['pluginname'] = 'LSF Unification';
$string['plugintitle'] = 'LSF Unification';

$string['delete'] = 'Löschen';
$string['warnings'] = 'Hinweise:';
$string['select'] = 'Auswählen';
$string['back'] = 'Zurück';
$string['navigate'] = 'navigieren';
$string['map'] = 'zuordnen';
$string['map_done'] = 'Zuordnung(en) wurde(n) festgehalten';
$string['mapped'] = 'zugeordnet';
$string['not_mapped'] = 'nicht zugeordnet';
$string['choose_course'] = 'Bitte wählen Sie den Kurs aus, den Sie im Learnweb zur Verfügung stellen wollen.';
$string['create_mappings'] = 'Zuordnungen hinzufügen';
$string['main_category'] = 'Hauptkategorie';
$string['overwrite'] = 'überschreiben';
$string['sub_category'] = 'Unterkategorie';
$string['update_helptable'] = 'Hilfstabelle automatisch aktualisieren';

$string['dbhost'] = 'Host';
$string['dbhost_description'] = 'PostgreDB-Host';
$string['dbname'] = 'Name';
$string['dbname_description'] = 'PostgreDB-Name';
$string['dbpass'] = 'Pass';
$string['dbpass_description'] = 'PostgreDB-Password';
$string['dbuser'] = 'User';
$string['dbuser_description'] = 'PostgreDB-User';
$string['db_not_available'] = "Die Import-Funktion ist zurzeit leider nicht verfügbar. Bitte nutzen sie das <a href='../../course/request.php'>reguläre Beantragungsformular</a> (<a href='../../course/request.php'>&rarr; Link</a>).";
$string['defaultcategory'] = 'Standard Kategorie';
$string['defaultcategory_description'] = 'Falls keine Kategorie eingetragen werden kann wird diese Kategorie angegeben';
$string['max_import_age'] = 'Maximales Kurs-Alter';
$string['max_import_age_description'] = 'Maximales Alter, dass ein Kurs haben darf, danach wird er nicht mehr zur Auswahl angezeigt.';
$string['roleid_creator'] = 'RoleID Ersteller';
$string['roleid_creator_description'] = 'Rolle für Kursersteller';
$string['roleid_student'] = 'RoleID Student';
$string['roleid_student_description'] = 'Rolle für automatisch angemeldete Studenten';
$string['roleid_teacher'] = 'RoleID Lehrer';
$string['roleid_teacher_description'] = 'Rolle für automatisch angemeldete Lehrer';
$string['subcategories'] = 'Unterkategorien freischalten';
$string['subcategories_description'] = 'Erlaubt das einordnen nicht zugeordneter Unterkategorien zu Oberkategorien';

$string['notice'] = 'Im Normalfall übernimmt Learnweb die Kursinformationen aus der HIS-Datenbank und schlägt diese unten als Auswahl vor. Voraussetzung ist, dass Sie als Lehrende(r) bzw. Begleitperson der Veranstaltung zugeordnet sind und Ihre Kennung im HIS-LSF hinterlegt ist. Sollte kein Kurs von Ihnen erscheinen, wählen Sie bitte eine der anderen zutreffenden Alternativen.';
$string['question'] = "Bitte wählen Sie die erste zutreffende Aussage:";
$string['answer_course_found'] = "Der zu beantragende Kurs befindet sich in der nachfolgenden Liste:";
$string['answer_course_in_lsf_and_visible'] = "Der zu beantragende Kurs befindet sich im HIS-LSF und Sie sind dort als Lehrende(r) eingetragen.";
$string['answer_proxy_creation'] = "Der Kurs existiert im HIS-LSF und Sie möchten im Auftrag eines dort eingetragenen Lehrenden diesen Kurs erstellen.";
$string['answer_goto_old_requestform'] = "Sie möchten eine Kursbeantragung ohne Übernahme der Daten aus dem HIS-LSF vornehmen.";




$string['info_course_in_lsf_and_visible'] = '<p>Dass der gewünschte Kurs nicht aufgelistet wird, kann folgende Ursachen haben:</p><ol><li>Es ist weniger als 24 Stunden her, dass die Veranstaltung in das HIS-LSF eingetragen wurde. Die Daten werden nur einmal täglich aus dem System übernommen. Wenn kein weiterer Fehler vorliegt, warten Sie bitte bis morgen, dann sollte eine Übernahme in das Learnweb funktionieren.</li><li>Die Kennung, mit der Sie im Learnweb angemeldet sind (Kennung: {$a}), ist keiner Person im HIS-LSF zugeordnet.<br /> Wenden Sie sich bitte an eine Person Ihres Fachbereichs, die Bearbeitungsrechte im HIS-LSF besitzt. Im Normalfall haben Sekretariate bzw. das Dekanat Bearbeitungsrechte im HIS-LSF. Ihr Profil im HIS-LSF muss bearbeitet werden (<strong>Person bearbeiten</strong>). In Registerkarte <strong>2</strong> muss der Bereich <strong>Login</strong> gewählt werden, und im Feld <strong>Login</strong> Ihre ZIV-Nutzerkennung eingetragen werden (vgl. Abb.). Die Änderung wird erst am nächsten Tag wirksam.<p style="text-align: center;"><img alt="Datenbearbeiten" src="http://www.uni-muenster.de/LearnWeb/diverse/HIS-Person_bearbeiten.png" /></p></li></ol>';
$string['info_goto_old_requestform'] = "Bitte nutzen Sie die <a href='../../course/request.php'>manuelle Kursbeantragung</a>, um Ihren Kurs zu beantragen (<a href='../../course/request.php'>&rarr; Link</a>). Geben Sie bitte unbedingt einen Verweis auf den Kurs im HIS-LSF an (einen Link bzw. genaue Kursbezeichnung).<br/> Ihr Antrag wird vom Learnweb-Support schnellst möglichst bearbeitet.";

$string['config_auto_update'] = "Automatische Aktualisierung";
$string['config_auto_update_duration'] = "Neuanmeldungen&Abmeldungen zum Kurs (im HIS-LSF) ins Learnweb übernehmen für";
$string['config_auto_update_duration182'] = "sechs Monate ab Startdatum";
$string['config_auto_update_duration31'] = "einen Monat ab Startdatum";
$string['config_auto_update_duration7'] = "eine Woche ab Startdatum";
$string['config_auto_update_duration-1'] = "Nie";
$string['config_category'] = "Kategorie";
$string['config_category_wish'] = "Kategorie-Umzugs-Wunsch";
$string['config_category_wish_help'] = "Falls Sie den Kurs gerne in einer anderen stärker spezifizierten Kategorie eingeordnet haben möchten, hinterlassen Sie bitte hier einen Kommentar  mit der entsprechenden Wunschkategorie und -pfad.";
$string['config_enrol'] = "Einschreibemethoden";
$string['config_dbenrolment'] = "HISLSF Einschreibung";
$string['config_dbenrolment_help'] = "Eine Möglichkeit den Kurs nur für bestimmte Studierende freizuschalten, ist die automatische Synchronisierung mit der HISLSF-Datenbank. Die Einschreibungen der Studenten in Ihren Kurs werden automatisch mit dem HISLSF synchronisiert sobald sich diese im Learnweb anmelden. Wir empfehlen zusätzlich die Selbsteinschreibung mit Passwort, da der Abgleich mit den HISLSF-Daten systembedingt ca. 24 Std. versetzt stattfinden.";
$string['config_selfenrolment'] = "Selbsteinschreibung";
$string['config_selfenrolment_help'] = "Eine Möglichkeit den Kurs nur für bestimmte Studierende freizuschalten, ist die Selbsteinschreibung mit oder ohne Passwort. Wir empfehlen ein Passwort zu setzen und dieses in der Präsenzveranstaltung bekanntzugeben.";
$string['config_enrolment_key'] = "Selbsteinschreibungs-Schlüssel";
$string['config_enrolment_key_help'] = "Das nachfolgend eingegebene Passwort müssen Studierende einmalig beim Betreten des Kursraums durch Selbsteinschreibung eingeben. Wenn der Learnweb-Kurs nicht Passwortgeschützt werden soll, lassen Sie das Feld leer. Wir empfehlen dringend ein Passwort zu vergeben, insb. wenn se digitale Dokumente im Rahmen von e-Semesterapparaten oder ähnlichem bereitstellen möchten.";
$string['config_misc'] = "Sonstiges";
$string['config_shortname'] = "Kurztitel";
$string['config_summary'] = "Kurzbeschreibung";
$string['config_summary_desc'] = "(Wird in der Kurssuche dargestellt)";

$string['categoryinvalid'] = 'Bitte eine Kategorie wählen';
$string['email_error'] = 'Der Kategorie-Umzugs-Wunsch konnte leider nicht automatisch versendet werden. Kontaktieren Sie den Support manuell! ('.$CFG->supportemail.')';
$string['email_success'] = 'Eine Email bzgl. des Kategorie-Wechsels wird an den Support gesendet.';
$string['new_request'] = 'Einen weiteren Kurs beantragen.';
$string['noConnection'] = "Es konnte keine Verbindung zur LSF-Datenbank hergestellt werden. Bitte nutzen Sie das manuelle <a href='../../course/request.php'>Kursbeantragungs-Formular</a>.";
$string['shortnamehint'] = 'Der Kurzname muss {$a} am Ende enthalten.';
$string['shortnameinvalid'] = 'Kurzbezeichnung fehlerhaft (es muss {$a} am Ende stehen)';
$string['warning_cannot_enrol_nologin'] = "Person wurde nicht hinzugefügt (kein Benutzername gefunden)";
$string['warning_cannot_enrol_nouser'] = "Person wurde nicht hinzugefügt (kein Benutzer gefunden)";
$string['warning_cannot_enrol_other'] = "Person wurde nicht hinzugefügt";

$string['next_steps'] = "Nächste Schritte";
$string['linktext_users'] = "Bearbeiten Sie die eingeschriebenen Lehrenden und Studenten ...";
$string['linktext_course'] = "... oder gehen Sie direkt zum neu erstellten Kurs.";

$string['course_duplication_question'] = 'Wollen Sie die Daten aus einem alten Learnweb-Kurs in den soeben erstellten Kurs kopieren? (Dies ist die einzige Gelegenheit dazu)';
$string['yes'] = 'Ja';
$string['no'] = 'Nein';
$string['skip'] = 'Überspringen';
$string['course_duplication_selection'] = 'Bitte wählen Sie ein Kursbackup aus:';

$string['email'] = 'ABSENDER:
{$a->a} ('.$CFG->wwwroot.'/user/view.php?id={$a->b})

    KURS:
    {$a->c} ('.$CFG->wwwroot.'/course/view.php?id={$a->d})

        NACHRICHT:
        {$a->e}';

$string['choose_teacher'] = 'Bitte geben Sie die Nutzerkennung des autorisierenden Lehrenden an:';
$string['his_info'] = 'Bitte weisen Sie den Lehrenden an, <a href="request.php?answer=3">diesen Hinweisen zu folgen</a>, sodass seine HIS-LSF-Kurse mit seiner Nutzerkennung verknüpft werden können.';
$string['answer_course_in_lsf_but_invisible'] = 'Der zu beantragende Kurs befindet sich im HIS-LSF und der Kurs wird oben nicht angezeigt, obwohl der Nutzer mit der Kennung {$a} als Lehrender für den Kurs eingetragen ist.';
$string['already_requested'] = 'Diesr Kurs wurde bereits angefragt. Der Lehrende muss erst die bestehende Anfrage beantworten, bevor Neue Anfragen getätigt werden können.';
$string['request_sent'] = 'Die Anfrage wird per Mail an den Lehrenden gesendet. Sie werden per Mail eine Rückmeldung erhalten, sobald der Lehrende die Anfrage bearbeitet hat.';
$string['answer_sent'] = 'Vielen Dank für das Verarbeiten dieser Anfrage. Ihre Entscheidung wird dem Anfragenden automatisch per Email mitgeteilt.';

$string['email_from'] = "HIS LSF Import";
$string['email2_title'] = "Kurs Erstellungs-Anfrage";
$string['email2'] = 'Der Benutzer "{$a->a}" ({$a->b}) versucht den Kurs "{$a->c}" in Ihrem Namen zu erstellen. Bitte akzeptieren oder verweigern sie die Anfrage auf dieser Webseite: {$a->d}';
$string['email3_title'] = "Kurs Erstellungs-Anfrage akzeptiert";
$string['email3'] = 'Der Benutzer "{$a->a}" ({$a->b}) akzeptierte Ihre Kursanfrage "{$a->c}". Bitte fahren Sie mit der Erstellung hier fort: {$a->d}';
$string['email4_title'] = "Kurs Erstellungs-Anfrage verweigert";
$string['email4'] = 'Der Benutzer "{$a->a}" ({$a->b}) verweigerte Ihre Kursanfrage "{$a->c}".';
$string['remote_request_select_alternative'] = 'Bitte wählen Sie eine Aktion aus:';
$string['remote_request_accept'] = 'Akzeptiere die Anfrage von "{$a->a}" den Kurs "{$a->b}" zu erstellen';
$string['remote_request_decline'] = 'Verweigere die Anfrage von "{$a->a}"';

$string['no_template'] = 'Alternative {$a}: Kurs ohne Inhalte vorbereiten...';
$string['pre_template'] = 'Alternative {$a}: Kurs mit Inhalten aus einer Vorlage vorbereiten...';
$string['template_from_course'] = 'Alternative {$a}: Fortfahren mit den Inhalten eines existierenden Kurses...';
$string['continue'] = 'Fortfahren';
$string['continue_with_empty_course'] = 'Mit einem leeren Kurs fortfahren';

$string['duplication_timeframe_error'] = 'Aus Sicherheitsgründen ist es nicht erlaubt Kursdaten aus Musterkursen oder Kursbackups wiederherzustellen, falls die Kurserstellung mehr als {$a} Stunden zurückliegt.';

$string['importcalendar'] = 'HISLSF Stundenplan';
$string['importical'] = 'Stundenplan importieren';
$string['icalurl'] = 'ICal URL';
$string['icalurl_description'] = 'URL des HisLSF ICal Exports (eine Liste der relevanten Termine wird dynamisch angehängt)';

$string['eventcourse_imported'] = 'Kurs importiert';
$string['eventmatchingtable_updated'] = 'Matchingtable geändert';
$string['eventcourse_duplicated'] = 'Kursinhalte dupliziert';

