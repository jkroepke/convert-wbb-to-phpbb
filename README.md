Step by Step - Anleitung
====================

Ein frisches phpBB 3.0.12 (http://downloads.phpbb.de/pakete/3.0.12/phpBB-3.0.12-deutsch.zip ) installieren.
Konverter herunterladen und entpacken.
Danach müsst ihr die config.php des Konverters anpassen. Tragt dort den MySQL Daten zum WBB und phpBB ein, sowie die vollen Dateipfade zu beiden Installationen.
Danach führt den Converter via cmd (Windows) oder bash (Linux) aus.

Unter Windows lautet der Zeile:

<Pfad zur php.exe> /php.exe <Pfad zur Datei>/index.php

Unter Linux:

chmod +x <Pfad zur Datei>/index.php
<Pfad zur Datei>/index.php

Je nach Größe des Forums kann dieser Vorgang mehrere Minuten dauern.

Additional Steps:
1. Log in into ACP
2. Run resynchronize statistics and resynchronize post counter
3. Install STK [https://www.phpbb.com/support/stk/]
4. Log in into STK
5. Run follow actions:
  - fix left/right ids
  - reparse bbcodes (set option 'reparse all bbcodes'!)
  - resynchronize attachments
  - resynchronize avatars
  - remove duplicate permissions
  - sanitise anonymous user