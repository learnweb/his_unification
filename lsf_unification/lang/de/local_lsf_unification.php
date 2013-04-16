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
 * Question engine upgrade helper langauge strings.
 *
 * @package    local
 * @subpackage qeupgradehelper
 * @copyright  2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



$string['pluginname'] = 'LSF Unification';
$string['plugintitle'] = 'LSF Unification';

$string['delete'] = 'l&ouml;schen';
$string['warnings'] = 'Hinweise:';
$string['select'] = 'Ausw&auml;hlen';
$string['back'] = 'Zur&uuml;ck';
$string['navigate'] = 'navigieren';
$string['map'] = 'zuordnen';
$string['map_done'] = 'Zuordnung(en) wurde(n) festgehalten';
$string['mapped'] = 'zugeordnet';
$string['not_mapped'] = 'nicht zugeordnet';
$string['choose_course'] = 'Bitte w&auml;hlen Sie den Kurs aus, den Sie im Learnweb zur Verf&uuml;gung stellen wollen.';
$string['create_mappings'] = 'Zuordnungen hinzuf&uuml;gen';
$string['main_category'] = 'Hauptkategorie';
$string['overwrite'] = '&uuml;berschreiben';
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
$string['db_not_available'] = "Die Import-Funktion ist zurzeit leider nicht verf&uuml;gbar. Bitte nutzen sie das regul&auml;re Beantragungsformular. (<a href='../../course/request.php'>Link</a>)";
$string['defaultcategory'] = 'Standard Kategorie';
$string['defaultcategory_description'] = 'Falls keine Kategorie eingetragen werden kann wird diese Kategorie angegeben';
$string['max_import_age'] = 'Maximales Kurs-Alter';
$string['max_import_age_description'] = 'Maximales Alter, dass ein Kurs haben darf, danach wird er nicht mehr zur Auswahl angezeigt.';
$string['roleid_creator'] = 'RoleID Ersteller';
$string['roleid_creator_description'] = 'Rolle f&uuml;r Kursersteller';
$string['roleid_student'] = 'RoleID Student';
$string['roleid_student_description'] = 'Rolle f&uuml;r automatisch angemeldete Studenten';
$string['roleid_teacher'] = 'RoleID Lehrer';
$string['roleid_teacher_description'] = 'Rolle f&uuml;r automatisch angemeldete Lehrer';
$string['subcategories'] = 'Unterkategorien freischalten';
$string['subcategories_description'] = 'Erlaubt das einordnen nicht zugeordneter Unterkategorien zu Oberkategorien';

$string['notice'] = 'Im Normalfall &uuml;bernimmt Learnweb die Kursinformationen aus der HIS-Datenbank und schl&auml;gt diese unten als Auswahl bereit. Voraussetzung ist, dass Sie als Lehrender bzw. Begleitperson der Veranstaltung zugeordnet sind und Ihre Kennung im HIS-LSF hinterlegt ist. Sollte kein Kurs von Ihnen erscheinen, w&auml;hlen Sie bitte eine der anderen zutreffenden Alternativen.';
$string['question'] = "Bitte w&auml;hlen Sie die erste zutreffende Aussage:";
$string['answer_course_already_created1'] = "Der zu beantragende Kurs befindet sich im HIS-LSF, wird jedoch nicht angezeigt.";
$string['answer_course_found'] = "Der zu beantragende Kurs befindet sich in der nachfolgenden Liste:";
$string['answer_goto_old_requestform'] = "Ich m&ouml;chte eine Kursbeantragung ohne &Uuml;bernahme der Daten aus dem HIS-LSF vornehmen.";

$string['info_course_already_created1'] = '<p>Das der gew&uuml;nschte Kurs nicht aufgelistet wird, kann folgende Ursachen haben:</p><ol><li>Es ist weniger als 24 Stunden her, dass die Veranstaltung in das HIS-LSF eingetragen wurde. Die Daten werden nur einmal t&auml;glich aus dem System &uuml;bernommen. Wenn kein weiterer Fehler vorliegt, warten Sie bitte bis morgen, dann sollte eine &Uuml;bernahme in das Learnweb funktionieren.</li><li>Die Kennung, mit der Sie im Learnweb angemeldet sind (Kennung: {$a}), ist keiner Person im HIS-LSF zugeordnet.<br /> Wenden Sie sich bitte an eine Person Ihres Fachbereichs, die Bearbeitungsrechte im HIS-LSF besitzt. Im Normalfall haben Sekretariate bzw. das Dekanat Bearbeitungsrechte im HIS-LSF. Ihr Profil im HIS-LSF muss bearbeitet werden (<strong>Person bearbeiten</strong>). In Registerkarte <strong>2</strong> muss der Bereich <strong>Login</strong> gew&auml;hlt werden, und im Feld <strong>Login</strong> Ihre ZIV-Nutzerkennung eingetragen werden (vgl. Abb.). Die &Auml;nderung wird erst am n&auml;chsten Tag wirksam.<p style="text-align: center;"><img alt="Datenbearbeiten" src="/LearnWeb/diverse/HIS-Person_bearbeiten.png" /></p></li></ol>';
$string['info_goto_old_requestform'] = "Bitte nutzen Sie die manuelle Kursbeantragung um Ihren Kurs zu beantragen. (<a href='../../course/request.php'>Link</a>) Geben Sie bitte unbedingt einen Verweis auf den Kurs im HIS-LSF an (einen Link bzw. genaue Kursbezeichnung).<br/> Ihr Antrag wird vom Learnweb-Support schnellst m&ouml;glichst bearbeitet.";

$string['config_auto_update'] = "Automatische Aktualisierung";
$string['config_auto_update_duration'] = "Neuanmeldungen zum Kurs (im HIS-LSF) ins Learnweb &uuml;bernehmen f&uuml;r (ab jetzt)";
$string['config_auto_update_duration182'] = "Ein Semester";
$string['config_auto_update_duration31'] = "Einen Monat";
$string['config_auto_update_duration365'] = "Ein Jahr";
$string['config_auto_update_duration7'] = "Eine Woche";
$string['config_category'] = "Kategorie";
$string['config_category_wish'] = "Kategorie-Umzugs-Wunsch";
$string['config_category_wish_help'] = "Falls Sie den Kurs gerne in einer anderen st&auml;rker spezifizierten Kategorie eingeordnet haben m&ouml;chten, hinterlassen Sie bitte hier einen Kommentar  mit der entsprechenden Wunschkategorie und -pfad.";
$string['config_enrolment_key'] = "Einschreibeschl&uuml;ssel";
$string['config_enrolment_key_help'] = "Eine M&ouml;glichkeit den Kurs nur f&uuml;r bestimmte Studierende freizuschalten, ist die Passwort-Einschreibung. Das nachfolgend eingegebene Passwort m&uuml;ssen Studierende einmalig beim Betreten des Kursraums eingeben. Wenn der Learnweb-Kurs nicht Passwortgesch&uuml;tzt werden soll, lassen Sie das Feld leer.";
$string['config_misc'] = "Sonstiges";
$string['config_shortname'] = "Kurztitel";
$string['config_summary'] = "Kurzbeschreibung";
$string['config_summary_desc'] = "(Wird in der Kurssuche dargestellt)";

$string['categoryinvalid'] = 'Bitte eine Kategorie w&auml;hlen';
$string['email_error'] = 'Der Kategorie-Umzugs-Wunsch konnte leider nicht automatisch versendet werden. Kontaktieren Sie den Support manuell! ('.$CFG->supportemail.')';
$string['email_success'] = 'Email bzgl. des Kategorie-Wechsels an den Support gesendet.';
$string['new_request'] = 'Einen weiteren Kurs beantragen.';
$string['noConnection'] = "Es konnte keine Verbindung zur LSF-Datenbank hergestellt werden. Bitte nutzen Sie das manuelle <a href='../../course/request.php'>Kursbeantragungs-Formular</a>.";
$string['shortnamehint'] = 'Der Kurzname muss {$a} am Ende enthalten.';
$string['shortnameinvalid'] = 'Kurzbezeichnung fehlerhaft (es muss {$a} am Ende stehen)';
$string['warning_cannot_enrol_nologin'] = "Person wurde nicht hinzugef&uuml;gt (kein Benutzername gefunden)";
$string['warning_cannot_enrol_nouser'] = "Person wurde nicht hinzugef&uuml;gt (kein Benutzer gefunden)";
$string['warning_cannot_enrol_other'] = "Person wurde nicht hinzugef&uuml;gt";

$string['next_steps'] = "N&auml;chste Schritte";
$string['linktext_users'] = "Bearbeiten Sie die eingeschriebenen Lehrenden und Studenten ...";
$string['linktext_course'] = "... oder gehen Sie direkt zum neu erstellten Kurs.";

$string['course_duplication_question'] = 'Wollen Sie die Daten aus einem alten Learnweb-Kurs in den soeben erstellten Kurs kopieren? (Dies ist die einzige Gelegenheit dazu)';
$string['yes'] = 'Ja';
$string['no'] = 'Nein';
$string['skip'] = '&Uuml;berspringen';
$string['course_duplication_selection'] = 'Bitte w&auml;hlen Sie ein Kursbackup aus:';

$string['email'] = 'ABSENDER:
{$a->a} ('.$CFG->wwwroot.'/user/view.php?id={$a->b})

KURS:
{$a->c} ('.$CFG->wwwroot.'/course/view.php?id={$a->d})

NACHRICHT:
{$a->e}';