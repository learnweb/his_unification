---
layout: chapter
title: Installation
---

1. Plugin im ```wwwroot```-Verzeichnis bereitstellen
  * Download 
     1. Download des Plugins von [github]({{site.github_repo}})
     2. Entpacken nach ```$CFG->wwwroot.'\local\lsf_unification'``` 
  * **oder**
  * git submodule-Befehl verwenden	
    1. ```git submodule add {{site.github_repo}} local/lsf_unification``` 
1.  Moodle Plugin-Installation starten
2. Datenbank einrichten: (eines der folgenden)
  * Testdatenbank (PostgreSQL) mit Beispieldaten (siehe [beispieltabellen.sql](code/beispieltabellen.sql)) einrichten
  * Views mit Live-Daten entsprechend der unter [Notwendige Tabellen/Sichten auf Tabellen](010_views.html) beschriebenen Sichten auf die LSF-DB bereitstellen
2. unter 'Administrator' -> 'Plugins' -> 'Lokale Plugins' befinden sich vier Erweiterungen (Siehe [Admin-Setting-Seiten](020_adminsettings.html)): 
  * 'LSF Unification Config'
  * 'LSF Unification Matching'
  * 'LSF Deeplink Removal' **und**
  * 'LSF Remote Request Handling'.

3. Zuordnung zwischen Moodle-Kategorien und LSF-Überschriften generieren ```$CFG->wwwroot.'/local/lsf_unification/helptablemanager.php'```
  1. Hilfstabelle aktualisieren -> **Update Helptable With HIS-LSF data** auswählen
  2. LSF-Überschriften zu Moodle-Kategorien zuordnen.'-> **Create New Mappings**
4. Kursbeantragung auf z.B. der Startseite verlinken ```$CFG->wwwroot.'/local/lsf_unification/request.php'```

{% highlight html %}
<p>
  Hier können Lehrende der Universität Münster neue Kurse für ihre Veranstaltungen beantragen.<br />
  Nach Abgabe des Antrags werden Sie per Mail informiert, sobald Ihr Kurs angelegt wurde.
</p>
<div class="buttons">
  <div class="singlebutton">
    <form method="get" action="local/lsf_unification/request.php">
      <div><input value="Kurs beantragen" type="submit" /></div>
    </form>
  </div>
</div>
{% endhighlight %}