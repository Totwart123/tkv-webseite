##### ergebnisdienst - Addon #####

---
Login unter https://tkv-kegeln.de/index.php?article_id=65

Anzeige Spielrecht unter https://tkv-kegeln.de/index.php?article_id=464&passnummer=078402

### Changelog ###

___                      
### 06.06.2023 Version 2.0.6 ###

Korrektur Altersklassen   
Anpassung Ansicht Spielrecht

geänderte Dateien:

MODUL: LED: Anzeige Spielrecht

pages/spieler.php
pages/spielrecht.php
pages/klasse.php
lib/led.functions.php

### 02.06.2023 Version 2.0.5 ###

Einführung Altersklassen   
Anpassung Ansicht Spielrecht

geänderte Dateien:

MODUL: LED: Anzeige Spielrecht

pages/spieler.php
pages/spielrecht.php
pages/klasse.php

### 22.05.2023 Version 2.0.4 ###

Korrekturen

### 11.05.2023 Version 2.0.3 ###

Korrektur Datumsauswertung Spielerliste beim Upload
Farbkorrektur Spielupload-Hinweis
Neue Prüfung bei Änderung des Uploads
separater Reiter "Spielrecht" mit ID statt Auswahlliste
Liste Spielrechte mit Mannschaft und sortiert nach Mannschaft
Löschung mannschaftid nach Saison

Neues Modiul: LED: Anzeige Spielrecht

geänderte Dateien:

boot.php
lib/upload.spielerliste.func.php
lib/upload.einzelspiel.step23.php
pages/geprueft.php
pages/spielrecht.php
pages/spieler.php
pages/klasse.php
pages/saison.php

### 02.05.2023 Version 2.0.2 ###

Anpassung Spielerdaten Lizenz
Ausgabe der Lizenz im Backend (Spieler und Klasse)
Löschfunktion Lizenz-Einträge pro Saison

geänderte Dateien:

spieler.php
saison.php
klasse.php


### 28.04.2023 Version 2.0.1 ###

Anpassung Import Spielerdatei
Anpassung Import Spielplan

geänderte Dateien:

install.sql
led.upload.modul.php (Auswahl Geschlecht entfällt, XLSX für Spielplan)
upload.spielerliste.func.php (neue Datei für alle Geschlechter)
upload.spielplan.func.php ( Umstellung XLSX Auslesen mit getOldCalculatedValue() und Zeitberechnung mit Zeitzone )

Änderung Datenbank:

ALTER TABLE rex_950_spieler ADD spielrecht varchar(200) NOT NULL;  
ALTER TABLE rex_950_spieler ADD spielrecht1 int(10) NOT NULL;  
ALTER TABLE rex_950_spieler ADD spielrecht2 int(10) NOT NULL;  

### 25.04.2023 Version 2.0.0 ###

Ergänzung Spiel geprüft
Signalfarben beim Upload

geänderte Dateien:

install.sql
package.yml
boot.php
geprueft.php
led.liste.spiel.php
led.liste.spielplan.php
upload.einzelspiel.step23.php

Änderung Datenbank:

ALTER TABLE rex_950_spielplan ADD geprueft int(10) NOT NULL;  
ALTER TABLE rex_950_spielplan ADD pruefer varchar(200) NOT NULL;      
ALTER TABLE rex_950_spielplan ADD pruefdatum varchar(200) NOT NULL;     
ALTER TABLE rex_950_spielplan ADD pruefimportid varchar(200) NOT NULL;   
ALTER TABLE rex_950_spielplan ADD prueferlogin varchar(200) NOT NULL;   


### 06.06.2019 Version 1.5.0 ###

Neue Version für Redaxo 5
