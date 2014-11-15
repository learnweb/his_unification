---
layout: recipe
title: Erweiterungsoptionen
---

##Kurs Templates
Dozenten können (sofern dies durch die Einstellungen erlaubt wird) bei der Kurserstellung auf Kurs-Templates (spezielle Kurssicherungen) zurückgreifen.
Diese Templates müssen dafür im Ordner [backup_auto_destination]/templates abgelegt werden. 
Die Templates können in Kategorien eingeteilt werden, dazu können Ordner [backup_auto_destination]/[KategorieName] angelegt werden, in die dann die Kurs-Templates abgelegt werden.
Dabei ist [backup_auto_destination] der in den Moodle-Einstellungen festgelegte entsprechende Pfad.

Die hinterlegten Dateien müssen das Format template[0-9]+.(mbz|txt) haben (z.B. template2.mbz und template2.txt). 
Die eigentliche Kursvorlage, die .mbz Datei, ist ein normales Kursbackup eines Beispielkurses (dieser sollte keine Einschreibemethoden haben).
Einen zugehörigen Beschreibungstext, der dem Nutzer angezeigt wird, kann man in der txt-Datei ablegen, wobei die erste Zeile einen Namen angibt und alle weiteren Zeilen eine Beschreibung beinhalten können.
Ein Beispiel für die Kursvorlage-Dateien ist in [course_templates.zip](code/course_templates.zip) zu finden.

##Kursinhalte aus Kurssicherungen übernehmen
Bei entsprechender Einstellung des Plugins können Dozenten die Inhalte aus alten Kursen in den neuen Kurs wiederherstellen.
Hierzu müssen die Kurssicherungen in dem entsprechenden Pfad für Kurssicherungen hinterlegt sein.

##Erweiterung: HIS Deeplink Web Service
Die Universität Dortmund hat eine Erweiterung für HIS-LSF geschrieben, die es ermöglicht, einer LSF-Veranstaltung einen Link zum Moodle-Kurs hinzuzufügen.
Nachdem ein Kurs im Moodle über dieses Moodle-Plugin erstellt wurde, wird der 'HIS Deeplink Web Service' aufgerufen. 
So wird dem LSF mitgeteilt, dass nun ein Moodle-Kurs zu der LSF-Veranstaltung existiert. 
Die Veranstaltungsbeschreibung im LSF wird somit um einen Link zu der Moodle-Veranstaltung ergänzt.

##Erweiterung: Automatisierte Studenteneinschreibung
Es ist möglich durch eine Anpassung des Database-Enrolmentplugins Studenten automatisiert in die Kurse einzuschreiben, die ins Learnweb importiert wurden. 
Genauere Informationen finden sich dazu unter [HISLSF Enrolment](/chapters/enrol_database/index.html).

##Stundenplan-Import
Um den Stundenplanimport zu aktivieren, müssen geringfügige Änderungen im Kalender-Modul von Moodle vorgenommen werden. Genauere Informationen finden sich dazu [Stundenplan Import](/chapters/cal_stundenplan/index.html).
