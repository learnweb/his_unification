---
layout: chapter
title: Stundenplan Import
---

#Studenplan Import
Die Erweiterung ermöglicht es Studenten die Termine aus dem HIS-LSF importieren.

## Notwendige Änderung
Um den Link auf der Abonnement-Verwaltungsseite anzuzeigen, und dynamische Links zu erlauben, muss eine geringfügige Änderung an Moodles Kalender Modul vorgenommen werden.
Die notwendigen Änderungen sind in der Datei [calendar.patch](code/calendar.patch) dargestellt.
Eine Historie der Änderung kann [hier]({{ site.github_repo }}/blob/gh-pages/chapters/cal_stundenplan/code) gefunden werden.

## Verwendung
Um den Stundenplan zu den Kalender-Abonnements hinzufügen, muss der Student auf der Seite calendar/managesubscriptions.php auf die Link "Stundenplan importieren" klicken.
