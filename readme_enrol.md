#Auto Enrolment

##Änderungen am Database Enrolment Plugin

##Konfiguration des Plugins

##Anbindung an das HISLSF

###Notwendige Sicht Belegung der Studierenden zu einer Veranstaltung
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
