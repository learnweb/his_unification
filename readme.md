#Installation

1. Entpacken nach $CFG->wwwroot.'\local\lsf_unification' und Moodle Plugin-Installation starten
2. unter 'Administrator' -> 'Plugins' -> 'Lokale Plugins' befinden sich zwei Erweiterungen 'LSF Unification Config' und 'LSF Unification Matching'

##LSF Unification Config
zur Konfiguration von HIS-LSF-Server/DB und Zuordnung von Rollen

##LSF Unification Matching
zur Zuordnung von Überschriften aus dem LSF zu Kategorien im Moodle (Zu berücksichtigen: LSF-Überschriften werden (zumindest an der WWU) jedes Semester im LSF neu angelegt (jeweils Verweis auf letztsemestrige Überschrift) und muss deshalb hin und wieder aktualisiert werden --> hier fehlt noch der entsprechende Cron-Job

##Vorhandene Rollen abändern um Konflikte zu vermeiden
Moodle unterscheidet bei Rechten zwischen erlaubt, entzogen und verboten.
Ist ein Nutzer in einer Rolle die ein bestimmtes Recht verbietet, ist ihm dieses Recht grundsätzlich verboten, auch wenn es ihm eine andere Rolle erlaubt.
Daher müssen je nach Rechte-Konfiguration vorhandene Rollen angepasst werden, also an den entsprechenden Stellen von "verboten" auf "entzogen" (was so viel bedeutet wie: nicht erlaubt außer durch eine andere Rolle) umgestellt werden.

Zum Beispiel sollte es für Tutoren und Lehrende nicht verboten sein Kurse wiederherzustellen, da sie dann nicht fehlerfrei die Templates bei der Kurserstellung verwenden können - hier bietet sich die Einstellung "entzogen" an, da ihnen die notwendigen Rechte temporär zugeteilt werden.
Die dafür notwendigen Rechte sind insbesondere "moodle/restore:restorecourse", "moodle/restore:restoreactivity", "moodle/restore:restoresection", "moodle/restore:configure"

##Kurs Templates
Dozenten können (sofern dies durch die Einstellungen erlaubt wird) bei der Kurserstellung auf Kurs-Templates (spezielle Kurssicherungen) zurückgreifen.
Diese Templates müssen dafür im Ordner <backup_auto_destination>/templates abgelegt werden.
Dabei ist <backup_auto_destination> der in den Moodle-Einstellungen festgelegte entsprechende Pfad.
Die hinterlegten Dateien müssen das Format template[0-9]+.(mbz|txt) haben (z.B. template2.mbz und template2.txt). 
Die eigentliche Kursvorlage, die .mbz Datei, ist ein normales Kursbackup eines Beispielkurses (dieser sollte keine Einschreibemethoden haben). Einen zugehörigen Beschreibungstext, der dem Nutzer angezeigt wird, kann man in der txt-Datei ablegen, wobei die erste Zeile einen Namen angibt und alle weiteren Zeilen eine Beschreibung beinhalten können.
Ein Beispiel für die Kursvorlage-Dateien ist in course_templates.zip zu finden.

##Kursinhalte aus Kurssicherungen übernehmen
Bei entsprechender Einstellung des Plugins können Dozenten die Inhalte aus alten Kursen in den neuen Kurs wiederherstellen.
Hierzu müssen die Kurssicherungen in dem entsprechenden Pfad für Kurssicherungen hinterlegt sein.

##Notwendige Tabellen/Sichten auf Tabellen

###Sicht der Dozenten
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
```sql
SELECT veranstaltung.veranstid, veranstaltung.veranstnr, veranstaltung.semester, k_semester.ktxt AS semestertxt, k_verart.dtxt AS veranstaltungsart, veranstaltung.dtxt AS titel, veranstaltung.zeitstempel, 'http://uvlsf.uni-muenster.de/qisserver/rds?state=verpublish&status=init&vmfile=no&moduleCall=webInfo&publishConfFile=webInfo&publishSubDir=veranstaltung&publishid='::text || veranstaltung.veranstid::text AS urlveranst
   FROM veranstaltung
   LEFT JOIN k_semester ON k_semester.semid = veranstaltung.semester
   LEFT JOIN k_verart ON k_verart.verartid = veranstaltung.verartid
  WHERE (veranstaltung.semester IN ( SELECT k_semester.semid
   FROM k_semester
  WHERE k_semester.semstatus = 1)) AND (veranstaltung.veranstid IN ( SELECT r_vvzzuord.veranstid
   FROM r_vvzzuord)) AND (veranstaltung.aikz = 'A'::bpchar OR veranstaltung.aikz IS NULL);
```
   
### Sicht Zuordnung Veranstaltung zu Personal
```sql
SELECT r_verpers.veranstid, personal.pid, r_verpers.sort, k_verkenn.dtxt AS zustaendigkeit
   FROM personal
   LEFT JOIN r_verpers ON personal.pid = r_verpers.pid
   LEFT JOIN k_verkenn ON k_verkenn.verkennid = r_verpers.verkennid;
```
   
###Sicht zur Pflege der Überschriften
```sql
SELECT r_hierarchie.uebergeord, r_hierarchie.untergeord, r_hierarchie.semester, ueberschrift.zeitstempel, ueberschrift.ueid, ueberschrift.eid, ueberschrift.txt, ueberschrift.quellid, veranstaltung.veranstid
   FROM r_hierarchie
   LEFT JOIN ueberschrift ON ueberschrift.ueid = r_hierarchie.untergeord
   LEFT JOIN r_vvzzuord ON r_vvzzuord.ueid = ueberschrift.ueid
   LEFT JOIN veranstaltung ON veranstaltung.veranstid = r_vvzzuord.veranstid
  WHERE r_hierarchie.tabelle = 'ueberschrift'::bpchar AND r_hierarchie.beziehung = 'U'::bpchar AND ueberschrift.aikz <> 'I'::bpchar
  ORDER BY r_hierarchie.sortierung, ueberschrift.txt;
```
  
###Sicht Belegung der Studierenden zu einer Veranstaltung
*Wir bekommen über unser SSO die Matrikelnr der Studierenden, möglicherweise bekommen wir bald auch eine Kennung mitgeliefert.*
```sql
SELECT r_beleg.veranstid, r_beleg.status, r_beleg.tabpk AS mtknr, r_beleg.zeitstempel
   FROM r_beleg;
```

### Abkürzungen von Status in Eingeschrieben
STATUS, BEDEUTUNG
AN Angemeldet
ZU Zugelassen
AB Abgemeldet
SP Stundenplan (Ist im Stundenplan veröffentlicht)
WL Warteliste
ST storniert
SA Selbstablehnung
TE Teilgenommen - erfolgreich
CA canceled (Veranstaltung fällt aus)
NE Nicht erfolgreich Teilgenommen
YY nachbelegt
WH Wiederholung
zu Dies hier ist wohl ein Fehler. Sollte "ZU" sein.

Weitere mögliche Kennzeichen wären.
NP Niedrige Priorität
MP Niedrige Modulpriorität
HP Hohe Priorität
TU Terminüberschneidung
XX zum Nachbelegen vorgemerkt
BB Inaktives Modul
VF Malus (Verlust der Fachsemesterpriorität)
