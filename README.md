## Deutsch
### Einleitung
Dieser Konverter erlaubt die Daten von einem WBB ab Version 3.1 zu einen phpBB 3.0.12 zu konvertieren.

Folgende Daten werden zum phpBB übertragen:
* Benutzter
    * Gruppen
    * Ränke
    * Avatare
    * Freunde
    * Ignorier-Liste
    * Passwörter **Brauchen nach den Konventierung nicht zurückgesetzt werden!**
    * Themen Abonnenten
* Private Nachrichten
    * Eigene Nachrichtenordner
* Änhänge
* Foren
* Themen
* Beiträge
* Umfragen
    * Ausnahme: phpBB unterstützt nur im ersten Post Umfragen, da im phpBB Umfragen Themenbezogen sind. Umfragen, die nicht im ersten Post sind, werden nicht mit übertragen!
* bbcodes
    * Eigene BBCode werden nicht komplett unterstützt

**Vor dem Konvertieren bitte ein Backup des Forum anlegen!**

### Step by Step

1. Ein frisches [phpBB 3.0.12 Deutsch] installieren.
Konverter herunterladen und entpacken.

  **Bei der Installation sollte die selbe E-Mail Adresse wie in der WBB Installation angegeben werden. Andernfalls extistiert nach der Konvertierung keine Administrator**
2. Die [config.php] des Konverters anpassen. Dort die MySQL Daten zum WBB und phpBB eintragen, sowie die vollen Dateipfade zu beiden Installationen.
3. Den Converter via Console starten. Je nach OS cmd (Windows) oder die Standard Shell (Linux/Unix).
  
  * Windows: 
  ```sh
  $ <Pfad zur php.exe>/php.exe <Pfad zum Konverter>/index.php
  ```
  * Linux/Unix
  ```sh
  $ php <Pfad zum Konverter>/index.php
  ```
  Je nach Größe des Forums kann dieser Vorgang mehrere Minuten dauern.
4. Zusätzliche Schritte:
  * Im ACP einloggen
  * Statistiken und Postzähler synchronisieren
  * [phpBB Support Toolkit] installieren.
  * Im STK einloggen
  * Folgende Aktionen durchführen:
    * fix left/right ids
    * reparse bbcodes (set option 'reparse all bbcodes'!)
    * resynchronize attachments
    * resynchronize avatars
    * remove duplicate permissions
    * sanitise anonymous user

## English
### Introduction
Within the converter support the migration from a Woltlab Burning Board 3.1 to a fresh phpBB 3.0.12.

Supported convertion actions:

* user
    * groups
    * ranks
    * avatars
    * friends
    * ignore list
    * passwords **Passwords must not be reset!**
    * topics subscription
* private messages
    * custom folders
    * attachments
* forums
* topic
* posts
* polls
    * Only poll at the first topic post will be converted due phpbb limitations!
* bbcodes
    * custom bbcode partially supported 

**Before converting, please create a backup of the forum!**

### Step by Step
1. Install a new [phpBB 3.0.12].

  **You should be use the same email adresse as in your wbb installation. Otherwise, there is no administrator after the convertion.**
2. Change the [config.php]. Enter the database connection settings for WBB and phpBB and add the absolute path to the wbb and phpbb installation.
3. Run the converter from the command line. If you use windows you can use the cmd, otherwise you the default commandline on your Linux/Unix system.
  
  * Windows: 
  ```sh
  $ <Path to php.exe>/php.exe <Path to converter>/index.php
  ```
  * Linux/Unix:
  ```sh
  $ php <Path to converter>/index.php
  ```
  Depending on the size of the forum may take several minutes to complete.
4. Additional Steps:
  * Log in into ACP
  * Run resynchronize statistics and resynchronize post counter
  * Install [phpBB Support Toolkit]
  * Log in into STK
  * Run follow actions:
    * fix left/right ids
    * reparse bbcodes (set option 'reparse all bbcodes'!)
    * resynchronize attachments
    * resynchronize avatars
    * remove duplicate permissions
    * sanitise anonymous user

#convert-wbb-to-phpbb License

convert-wbb-to-phpbb is released under [GNU General Public License, version 3].

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; version 3 of the License.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.

[GNU General Public License, version 3]:http://www.gnu.org/licenses/gpl-3.0.html
[config.php]:https://github.com/jkroepke/convert-wbb-to-phpbb/blob/master/config.php
[phpBB Support Toolkit]:https://www.phpbb.com/support/stk/
[phpBB 3.0.12 Deutsch]:http://downloads.phpbb.de/pakete/3.0.12/phpBB-3.0.12-deutsch.zip
[phpBB 3.0.12]:https://www.phpbb.com/files/release/phpBB-3.0.12.zip
