# Regressionstests

Ohne Zugriff auf einen Symcon-Server aus dem Repository-Verzeichnis ausführen:

```sh
find . -name '*.php' -not -path './.git/*' -exec php -l {} \;
php tests/module_test.php
git diff --check
```

`tests/bootstrap.php` ist eine isolierte SDK-Attrappe: `IPSModuleStrict` mit den typisierten Signaturen (eine abweichende Signatur scheitert beim Laden, ein falscher Argumenttyp unter `strict_types` beim Aufruf), die Symcon-Funktionen, die das Modul benutzt, und der Prüfling `ProbeTile`. Er ruft `ProcessHookData` wie Symcons HookInstance auf, fängt Kopfzeilen und Status ab und zählt, wie oft der Standard-Hintergrund kodiert wird. Warnungen und Hinweise brechen den Lauf ab.

`tests/module_test.php` prüft:

- **Module Strict:** `strict_types` als erste Anweisung, `IPSModuleStrict`, alle Methoden voll typisiert, dieselben 86 Eigenschaften mit denselben Typen und Standardwerten, Kernelstart (vor `KR_READY` nichts senden, danach vollständig), keine Referenzen und Abos auf ID 0.
- **Payload wie bisher:** Schlüssel und Reihenfolge des Voll-Updates und des Kacheldokuments, Werte, Transparenz-Marker `#FFFFFFFFFFFFFFFF`, VM_UPDATE-Nachrichten (auch dieselbe Variable in zwei Schaltern), Maskierung des Start-Skripts, ungültiges UTF-8, `RequestAction` samt Abweisung fremder Idents.
- **Bild-Hook** `/hook/widgetsimages/<ID>?k=bgimage&v=<Version>&t=<Token>`: Adresse statt Base64 im Voll-Update und im Kacheldokument, Auslieferung (Bytes, Bildtyp, Länge, `Cache-Control`, ETag, 304, `nosniff`), 403 bei falschem oder fehlendem Token, 404 bei unbekanntem Schlüssel oder ohne angezeigten Hintergrund, neue Adresse bei neuem Inhalt.
- **Nur bei echter Wertänderung:** `VM_UPDATE` mit `$Data[1] === false` schickt nichts, ein neuer Wert Wert und Details als Paar, dasselbe Paar kein zweites Mal, auch nicht nach den Nachrichten eines anderen Schalters (Prüfwert je Schalter im Puffer `UpdateHashes`); nach `ApplyChanges` und nach dem Erstaufbau geht es wieder hinaus, ohne `$Data[1]` wird gesendet. Gegenprobe: gegen den Stand `5d49264` fallen genau die Prüfungen, die keine Nachricht erwarten (`WIDGETS_MODULE` lädt dafür eine andere Fassung der Kachel).
- **Rückfall:** ohne registrierten Hook und für Bilder über der Ausgabegrenze (`ScriptOutputBufferLimit` − 1 KiB) die Data-URI wie bisher, Byte für Byte.
- **Nur kodieren, was angezeigt wird:** ohne Hintergrund wird nichts gelesen oder kodiert (Voll-Update, Kacheldokument, Variablen-Updates, abgewiesene Hook-Anfragen).

`Widgets/module.html` bleibt unverändert: `handleMessage` setzt `bgimage` als `url(...)` in eine CSS-Variable, eine Hook-Adresse wirkt dort wie eine Data-URI. Die Kachel läuft als `srcdoc`-iframe und löst die Adresse gegen den Visu-Server auf.

Vor einer Veröffentlichung in einer Symcon-Testinstanz prüfen:

- Nach dem Update das Modul neu laden. Erst dann läuft `Create()` erneut, der Hook ist registriert und die Kachel liefert Adressen statt Data-URIs.
- Kachel mit Standard-Hintergrund, eigenem Bild und ohne Hintergrund öffnen, lokal und über Symcon Connect; der Hintergrund muss erscheinen, `/hook/widgetsimages/<ID>` ohne Token muss 403 liefern.
- Schalter bedienen und Variablen von außen ändern; Farbe, Icon, Name und Wert müssen folgen.
- Symcon neu starten und die Kachel nach dem Kernelstart prüfen.
