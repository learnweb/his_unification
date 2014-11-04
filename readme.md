#Installation

1. Entpacken nach $CFG->wwwroot.'\local\lsf_unification' und Moodle Plugin-Installation starten
2. unter 'Administrator' -> 'Plugins' -> 'Lokale Plugins' befinden sich vier Erweiterungen 'LSF Unification Config', 'LSF Unification Matching', 'LSF Deeplink Removal' und 'LSF Remote Request Handling'.

##Menüpunkte

###'LSF Unification Config' 
dient zur Konfiguration von HIS-LSF-Server/DB, zur Zuordnung von Rollen und zur aktivierung einzelner Funktionen.

###'LSF Unification Matching' 
dient zur Zuordnung von Überschriften aus dem LSF zu Kategorien im Moodle (Zu berücksichtigen: LSF-Überschriften werden (zumindest an der WWU) jedes Semester im LSF neu angelegt (jeweils Verweis auf letztsemestrige Überschrift) und muss deshalb hin und wieder aktualisiert werden.

###'LSF Deeplink Removal' 
dient dazu einzelne Rückverlinkungen (siehe HIS Deeplink Web Service) wieder zu entfernen.

###'LSF Remote Request Handling' 
dient dazu Anfragen die von Studenten im Namen von Lehrenden gestellt wurden zu verwalten.

##Vorhandene Rollen abändern um Konflikte zu vermeiden
Moodle unterscheidet bei Rechten zwischen erlaubt, entzogen und verboten.
Ist ein Nutzer in einer Rolle die ein bestimmtes Recht verbietet, ist ihm dieses Recht grundsätzlich verboten, auch wenn es ihm eine andere Rolle erlaubt.
Daher müssen je nach Rechte-Konfiguration vorhandene Rollen angepasst werden, also an den entsprechenden Stellen von "verboten" auf "entzogen" (was so viel bedeutet wie: nicht erlaubt außer durch eine andere Rolle) umgestellt werden.

Zum Beispiel sollte es für Tutoren und Lehrende nicht verboten sein Kurse wiederherzustellen, da sie dann nicht fehlerfrei die Templates bei der Kurserstellung verwenden können - hier bietet sich die Einstellung "entzogen" an, da ihnen die notwendigen Rechte temporär zugeteilt werden.
Die dafür notwendigen Rechte sind insbesondere "moodle/restore:restorecourse", "moodle/restore:restoreactivity", "moodle/restore:restoresection", "moodle/restore:configure"

##Kurs Templates
Dozenten können (sofern dies durch die Einstellungen erlaubt wird) bei der Kurserstellung auf Kurs-Templates (spezielle Kurssicherungen) zurückgreifen.
Diese Templates müssen dafür im Ordner <backup_auto_destination>/templates abgelegt werden. Die Templates können in Kategorien eingeteilt werden, dazu können Ordner <backup_auto_destination>/<KategorieName> angelegt werden, in die dann die Kurs-Templates abgelegt werden.
Dabei ist <backup_auto_destination> der in den Moodle-Einstellungen festgelegte entsprechende Pfad.
Die hinterlegten Dateien müssen das Format template[0-9]+.(mbz|txt) haben (z.B. template2.mbz und template2.txt). 
Die eigentliche Kursvorlage, die .mbz Datei, ist ein normales Kursbackup eines Beispielkurses (dieser sollte keine Einschreibemethoden haben). Einen zugehörigen Beschreibungstext, der dem Nutzer angezeigt wird, kann man in der txt-Datei ablegen, wobei die erste Zeile einen Namen angibt und alle weiteren Zeilen eine Beschreibung beinhalten können.
Ein Beispiel für die Kursvorlage-Dateien ist in course_templates.zip zu finden.

##Kursinhalte aus Kurssicherungen übernehmen
Bei entsprechender Einstellung des Plugins können Dozenten die Inhalte aus alten Kursen in den neuen Kurs wiederherstellen.
Hierzu müssen die Kurssicherungen in dem entsprechenden Pfad für Kurssicherungen hinterlegt sein.

##HIS Deeplink Web Service
Die Universität Dortmund hat eine Erweiterung für HIS-LSF geschrieben, die es ermöglicht, einer LSF-Veranstaltung einen Link zum Moodle-Kurs hinzuzufügen. Nachdem ein Kurs im Moodle über dieses Moodle-Plugin erstellt wurde, wird der 'HIS Deeplink Web Service' aufgerufen. So wird dem LSF mitgeteilt, dass nun ein Moodle-Kurs zu der LSF-Veranstaltung existiert. Die Veranstaltungsbeschreibung im LSF wird somit um einen Link zu der Moodle-Veranstaltung ergänzt.

##Notwendige Tabellen/Sichten auf Tabellen
Die Namen der Sichten können in den Definitionen zu Beginn der lib_his.php Datei manipuliert werden.

###Sicht der Dozenten
(HIS_PERSONAL)
Anmerkung: zivk ist die Nutzerkennung des Dozenten, der sich bei uns einloggt, hierüber werden die Dozenten gematcht
```sql
SELECT DISTINCT personal.pid, nutzer."login", "replace"(lower("replace"("replace"(peremail.email::text, ' '::text, ''::text), '@uni-muenster.de'::text, ''::text)), 'atuni-muensterdotde'::text, ''::text) AS zivk, personal.akadgrad, personal.vorname, personal.nachname
   FROM personal
   LEFT JOIN r_verpers ON personal.pid = r_verpers.pid
   LEFT JOIN k_verkenn ON k_verkenn.verkennid = r_verpers.verkennid
   LEFT JOIN r_pernutzer ON r_pernutzer.pid = personal.pid
   LEFT JOIN kontakt ON kontakt.tabpk = personal.pid
   LEFT JOIN nutzer ON nutzer.nid = r_pernutzer.nid
   LEFT JOIN peremail ON peremail.pid = personal.pid
  ORDER BY personal.pid, nutzer."login", "replace"(lower("replace"("replace"(peremail.email::text, ' '::text, ''::text), '@uni-muenster.de'::text, ''::text)), 'atuni-muensterdotde'::text, ''::text), personal.akadgrad, personal.vorname, personal.nachname;
```

### Sicht der Veranstaltungen
(HIS_VERANSTALTUNG)
```sql
SELECT veranstaltung.veranstid, veranstaltung.veranstnr, veranstaltung.semester, veranstaltung.kommentar, veranstaltung.zeitstempel, k_semester.ktxt AS semestertxt, k_verart.dtxt AS veranstaltungsart, veranstaltung.dtxt AS titel, 'http://uvlsf.uni-muenster.de/qisserver/rds?state=verpublish&status=init&vmfile=no&moduleCall=webInfo&publishConfFile=webInfo&publishSubDir=veranstaltung&publishid='::text || veranstaltung.veranstid::text AS urlveranst
   FROM veranstaltung
   LEFT JOIN k_semester ON k_semester.semid = veranstaltung.semester
   LEFT JOIN k_verart ON k_verart.verartid = veranstaltung.verartid
  WHERE (veranstaltung.semester IN ( SELECT k_semester.semid
   FROM k_semester
  WHERE k_semester.semstatus = 1)) AND (veranstaltung.veranstid IN ( SELECT r_vvzzuord.veranstid
   FROM r_vvzzuord)) AND (veranstaltung.aikz = 'A'::bpchar OR veranstaltung.aikz IS NULL);
```
   
### Sicht Zuordnung Veranstaltung zu Personal
(HIS_PERSONAL_VERANST)
```sql
 SELECT r_verpers.veranstid, personal.pid, r_verpers.sort, k_verkenn.dtxt AS zustaendigkeit
   FROM personal
   LEFT JOIN r_verpers ON personal.pid = r_verpers.pid
   LEFT JOIN k_verkenn ON k_verkenn.verkennid = r_verpers.verkennid;
```
   
###Sicht zur Pflege der Überschriften
(HIS_UEBERSCHRIFT)
```sql
 SELECT r_hierarchie.uebergeord, r_hierarchie.untergeord, r_hierarchie.semester, ueberschrift.zeitstempel, ueberschrift.ueid, ueberschrift.eid, ueberschrift.txt, ueberschrift.quellid, veranstaltung.veranstid
   FROM r_hierarchie
   LEFT JOIN ueberschrift ON ueberschrift.ueid = r_hierarchie.untergeord
   LEFT JOIN r_vvzzuord ON r_vvzzuord.ueid = ueberschrift.ueid
   LEFT JOIN veranstaltung ON veranstaltung.veranstid = r_vvzzuord.veranstid
  WHERE r_hierarchie.tabelle = 'ueberschrift'::bpchar AND r_hierarchie.beziehung = 'U'::bpchar AND ueberschrift.aikz <> 'I'::bpchar
  ORDER BY r_hierarchie.sortierung, ueberschrift.txt;
```
   
###Sicht zum Auslesen der Veranstaltungsbeschreibung
(HIS_VERANST_KOMMENTAR)
```sql
 SELECT veranstaltung.veranstid, blobs.txt AS kommentar, r_blob.sprache
   FROM veranstaltung
   LEFT JOIN r_blob ON veranstaltung.veranstid = r_blob.tabpk
   LEFT JOIN blobs ON r_blob.blobid = blobs.blobid
   LEFT JOIN k_semester ON k_semester.semid = veranstaltung.semester
  WHERE r_blob.tabelle::text = 'veranstaltung'::text AND r_blob.spalte::text = 'kommentar'::text AND (veranstaltung.semester IN ( SELECT k_semester.semid
   FROM k_semester
  WHERE k_semester.semstatus = 1));
```
   
###Sicht zum Import des Stundenplans
(HIS_STDP)
```sql
 SELECT DISTINCT veransttermin.vtid AS terminid, veranstaltung.veranstid, r_beleg.tabpk AS mtknr, r_beleg.status
   FROM veransttermin, veranstaltung, r_beleg
  WHERE veransttermin.tabpk = veranstaltung.veranstid AND veransttermin.tabpk = r_beleg.veranstid AND veransttermin.tabelle::text = 'veranstaltung'::text AND (veransttermin.parallelid = r_beleg.parallelid OR (veransttermin.parallelid = 0 OR veransttermin.parallelid IS NULL) AND (r_beleg.parallelid = 0 OR r_beleg.parallelid IS NULL)) AND veranstaltung.semester >= (( SELECT s_lsfsys.aktsem
           FROM s_lsfsys)) AND r_beleg.tabelle = 'sospos'::bpchar AND (r_beleg.status = 'AN'::bpchar OR r_beleg.status = 'ZU'::bpchar OR r_beleg.status = 'SP'::bpchar OR r_beleg.status = 'WL'::bpchar) AND veranstaltung.aikz <> 'I'::bpchar
  ORDER BY veransttermin.vtid, veranstaltung.veranstid, r_beleg.tabpk, r_beleg.status;
```

#Beispieldaten

