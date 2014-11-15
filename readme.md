Dieses Moodle-Plugin stellt die Möglichkeit bereit, Kursbeantragungen automatisiert durchzuführen.
==================================================================================================

Features:
---------

-   Übernahme der Kursdaten aus dem CMS
-   Alle im CMS angegebenen Lehrenden und begleitende Lehrende werden als Lehrende in Moodle übernommen
-   Auswahl der Kurs-Kategorie auf Basis der im CMS hinterlegten Zuordnung
-   Bereitstellung von Inhalten aus Template-Kursen
-   Übernahme von Kursinhalten aus alten Veranstaltungen
-   Beantragung durch Tutoren möglich

Installationsanleitung
======================

Eine Installationsanleitung ist auf den [gh-pages](http://learnweb.github.io/his_unification/) zu finden.

Feature-Details:
================

Der Lehrende bekommt eine Liste seiner aktuellen Lehrveranstaltungen im Campus-Management-System (CMS) angezeigt und kann dann einen Kurs auf Basis der im CMS hinterlegten Daten anlegen.

In einem nächsten optionalen Schritt kann der Lehrende den Kurs mit Inhalten vorfüllen. 
Hierzu stehen zwei Möglichkeiten bereit: 
Duplizierung von Inhalten aus schon bestehenden Sicherungen (hierzu muss die automatische Kurssicherung aktiviert sein) 
oder Übernahme von Kursinhalten aus Kurstemplates (für alle Nutzer vom Administrator bereitgestellte Kurssicherungen).

Zurzeit besteht bei der Kursbeantragung die Möglichkeit einen Einschreibeschlüssel 
oder die Einschreibung über die Kursbelegung im CMS bereitzustellen (hierzu müssen geringfügige Änderungen an enrol/database vorgenommen werden).

Beantragung durch Tutoren: 
Soll die Beantragung des Kurses im Auftrag eines Vertreters (z.B. Tutors) durchgeführt werden, so gibt dieser bei der Beantragung die Kennung des Lehrenden an und bekommt dann dessen CMS-Veranstaltungen angezeigt. 
Die Beantragung muss dann durch den Dozenten bestätigt werden (Benachrichtigung jeweils per Mail). 
Nach der Bestätigung kann der Vertreter den Prozess weiterführen und wird ebenfalls als Lehrender in den Kurs eingetragen.

