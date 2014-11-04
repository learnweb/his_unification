#Auto Enrolment

##Änderungen am Database Enrolment Plugin
Die notwendigen Änderungen an enrol/database/lib.php sind in changes_to_enrol_database.diff aufgelistet. Des weiteren ist die neue Datei enrol/database/addinstance.php hinzuzufügen.

##Konfiguration des Plugins
Einstellungen unter admin/enrol/database: (falls nicht anders spezifiziert)
- enrol_database | dbtype = 'postgres7'
- enrol_database | dbhost= <DatenbankHost>
- enrol_database | dbuser= <DatenbankNutzer>
- enrol_database | dbpass= <DatenbankPasswort>
- enrol_database | dbname= <DatenbankName>
- enrol_database | remoteenroltable= ' (SELECT * FROM ((SELECT "mtknr", "veranstid", max("zeitstempel") as "zeitstempel" FROM <SichtName> GROUP BY "mtknr", "veranstid") AS "neusteEintraege" NATURAL INNER JOIN <SichtName>) WHERE "zeitstempel" > (current_timestamp - interval '1 year') AND "status" IN ('AN', 'ZU', 'YY', 'TE', 'NE', 'SP')) as einschreibungen'
- enrol_database | remotecoursefield= 'veranstid'
- enrol_database | remoteuserfield= 'mtknr'
- enrol_database | unenrolaction= 'Keep user enrolled'

##Anbindung an das HISLSF
Die Anbindung kann, sofern die nötigen Informationen zur Verfügung stehen, durch eine Anpassung der Pluginkonfiguration (enrol_database | remoteenroltable) geschaffen werden. (Anmerkung: In Münster gilt <SichtName> = "public"."learnweb_eingeschrieben")

In der vorliegenden Version geschieht das Mapping der Studenten über deren Matrikelnummer, die beim SSO-Verfahren dem Learnweb übergeben wird. Sofern das HisLsf die Nutzerkennung übergeben würde, wäre auch ein mapping über die Nutzerkennung denkbar. Dementsprechend müsste die Sicht und Konfiguration angepasst werden.

###Notwendige Sicht Belegung der Studierenden zu einer Veranstaltung
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
