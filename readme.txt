=== Pfadi-Aktivitäten Manager ===
Contributors: schlingel
Tags: pfadi, activities, manager, scout
Requires at least: 6.0
Tested up to: 6.4
Stable tag: 1.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Digitalisiert und automatisiert den Informationsfluss einer Pfadi-Abteilung.

== Description ==

Der Pfadi-Aktivitäten Manager hilft Abteilungen, ihre Aktivitäten zu verwalten, auf der Webseite darzustellen und Eltern/Teilnehmer per E-Mail zu informieren.

Features:
*   **Aktivitäten:** Verwaltung von Aktivitäten (CPT) mit Stufen-Zuweisung, Zeit, Ort und Mitnehmen-Infos.
*   **Mitteilungen:** Eigener Bereich für allgemeine Infos (Announcements) mit Gültigkeitsdauer.
*   **Frontend-Darstellung:**
    *   `[pfadi_board]`: Zeigt Aktivitäten als Kacheln, Liste oder Tabelle. Filterbar nach Stufen.
    *   `[pfadi_news]`: Zeigt Mitteilungen als Banner oder Karussell.
    *   `[pfadi_subscribe]`: Abo-Formular für den Newsletter.
*   **E-Mail Newsletter:**
    *   Automatischer Versand bei Veröffentlichung (Sofort oder Geplant).
    *   Double Opt-In Verfahren für Abonnenten.
    *   **NEU:** Anpassbare HTML-Templates für E-Mails.
    *   **NEU:** "E-Mail erneut senden" Funktion für Admins.
*   **Stufen-System:** Flexible Verwaltung der Stufen (Biber, Wölfe, Pfadis, etc.) mit Standard-Gruss und Leitung.
*   **iCal / RSS:** Automatische Feeds für Kalender-Integration.
*   **Logging:** Debug-Logs im Admin-Bereich einsehbar.

== Installation ==

1. Lade den Ordner `wp-pfadi-manager` in das Verzeichnis `/wp-content/plugins/` hoch.
2. Aktiviere das Plugin im Menü 'Plugins' in WordPress.
3. Konfiguriere die Einstellungen unter 'Pfadi Aktivitäten' -> 'Konfiguration'.

== Shortcodes ==

*   `[pfadi_board view="cards|list|table" unit="slug"]`
*   `[pfadi_news view="carousel|banner" limit="5"]`
*   `[pfadi_subscribe]`

== Changelog ==

= 1.3.0 =
*   NEU: Docker-Entwicklungsumgebung für einfacheres Testen.
*   NEU: GitHub Actions CI/CD Pipeline repariert und optimiert.
*   FIX: Umfassende Code-Bereinigung (PHP Linting, CSS Linting).
*   FIX: Deployment-Skripte aktualisiert.

= 1.2.2 =
*   FIX: Build-Prozess und Composer-Abhängigkeiten korrigiert (PHP 8.0 Kompatibilität).

= 1.2.1 =
*   NEU: Modernisierung des Workflows (Composer, NPM, Linting).
*   NEU: Logging-Tab in den Einstellungen (Anzeigen, Download, Löschen).
*   FIX: Diverse Code-Style Verbesserungen (PHPCS, ESLint).
*   FIX: Sicherheitsverbesserungen (Escaping, Nonces).

= 1.2.0 =
*   NEU: Anpassbare HTML-Templates für E-Mails (Aktivitäten & Mitteilungen).
*   NEU: "E-Mail erneut senden" als Mehrfachaktion (Bulk Action).
*   NEU: Stufen-Auswahl für Mitteilungen.
*   NEU: Shortcode `[pfadi_news]` für Mitteilungen (Karussell/Banner).
*   NEU: Verbesserte Admin-Oberfläche mit Tabs und Hilfetexten.
*   FIX: Unterstützung für deutsche Platzhalter ({Stufe}, {Titel}) in E-Mail Betreffs.
*   FIX: Korrekte Positionierung der Gültigkeitsdauer bei Mitteilungen.
*   FIX: E-Mail Versand-Timing (leere Felder behoben).

= 1.1.1 =
*   NEU: "Mitteilungen" als eigener Menüpunkt.
*   NEU: Konfigurierbarer URL-Slug für Mitteilungen.
*   NEU: Logging für E-Mail Versand (Debug).
*   FIX: Barrierefreiheit im Abo-Formular.
*   FIX: Filter-Logik für "Abteilung" (zeigt alle).

= 1.1.0 =
*   NEU: AJAX-basierte Filter-Tabs für Aktivitäten.
*   NEU: Content-Typ "Mitteilungen" hinzugefügt.
*   NEU: E-Mail Versandplanung (Geplant vs. Sofort).
*   NEU: "Sofort versenden" Checkbox im Editor.
*   FIX: Diverse Verbesserungen und Bugfixes.

= 1.0.1 =
*   Initiale Version.
