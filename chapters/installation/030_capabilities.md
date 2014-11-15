---
layout: recipe
title: Rechte und Rollen anpassen
---

##Vorhandene Rollen abändern um Konflikte zu vermeiden
Moodle unterscheidet bei Rechten zwischen erlaubt, entzogen und verboten.
Ist ein Nutzer in einer Rolle die ein bestimmtes Recht verbietet, ist ihm dieses Recht grundsätzlich verboten, auch wenn es ihm eine andere Rolle erlaubt.
Daher müssen je nach Rechte-Konfiguration vorhandene Rollen angepasst werden, also an den entsprechenden Stellen von "verboten" auf "entzogen" (was so viel bedeutet wie: nicht erlaubt außer durch eine andere Rolle) umgestellt werden.

Zum Beispiel sollte es für Tutoren und Lehrende nicht verboten sein Kurse wiederherzustellen, da sie dann nicht fehlerfrei die Templates bei der Kurserstellung verwenden können - hier bietet sich die Einstellung "entzogen" an, da ihnen die notwendigen Rechte temporär zugeteilt werden.
Die dafür notwendigen Rechte sind insbesondere "moodle/restore:restorecourse", "moodle/restore:restoreactivity", "moodle/restore:restoresection", "moodle/restore:configure"
