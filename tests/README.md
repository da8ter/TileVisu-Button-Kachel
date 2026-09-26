# Regressionstests

Ohne Zugriff auf einen Symcon-Server aus dem Repository-Verzeichnis ausführen:

```sh
find . -name '*.php' -not -path './.git/*' -exec php -l {} \;
php tests/module_test.php
git diff --check
```

`tests/bootstrap.php` ist eine isolierte SDK-Attrappe: `IPSModuleStrict` mit den typisierten Signaturen (eine abweichende Signatur scheitert beim Laden, ein falscher Argumenttyp unter `strict_types` beim Aufruf) und die Symcon-Funktionen, die das Modul benutzt. Warnungen und Hinweise brechen den Lauf ab.

`tests/module_test.php` prüft:

- **Module Strict:** `strict_types` als erste Anweisung, `IPSModuleStrict`, alle Methoden voll typisiert, dieselben 86 Eigenschaften mit denselben Typen und Standardwerten, Kernelstart (vor `KR_READY` nichts senden, danach vollständig), keine Referenzen und Abos auf ID 0.
- **Payload wie bisher:** Schlüssel und Reihenfolge des Voll-Updates und des Kacheldokuments, Werte, Transparenz-Marker `#FFFFFFFFFFFFFFFF`, VM_UPDATE-Nachrichten (auch dieselbe Variable in zwei Schaltern), Maskierung des Start-Skripts, ungültiges UTF-8, `RequestAction` samt Abweisung fremder Idents.

Vor einer Veröffentlichung in einer Symcon-Testinstanz prüfen:

- Schalter bedienen und Variablen von außen ändern; Farbe, Icon, Name und Wert müssen folgen.
- Symcon neu starten und die Kachel nach dem Kernelstart prüfen.
