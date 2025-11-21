=== Pfadi-Aktivitäten Manager ===
Contributors: schlingel
Tags: pfadi, activities, manager, scout
Requires at least: 6.0
Tested up to: 6.4
Stable tag: 1.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Digitalisiert und automatisiert den Informationsfluss einer Pfadi-Abteilung.

== Description ==

Der Pfadi-Aktivitäten Manager hilft Abteilungen, ihre Aktivitäten zu verwalten, auf der Webseite darzustellen und Eltern/Teilnehmer per E-Mail zu informieren.

Features:
*   Verwaltung von Aktivitäten (CPT) mit Stufen-Zuweisung.
*   Frontend-Darstellung via Shortcode `[pfadi_board]`.
*   AJAX-basierte Filterung nach Stufen.
*   Abonnement-System für Eltern (Double Opt-In).
*   Automatischer E-Mail Versand bei neuen Aktivitäten.
*   Versandplanung (Sofort oder Geplant).
*   Mitteilungen (Announcements) für allgemeine Infos.
*   iCal Feed Integration.

== Installation ==

1. Lade den Ordner `wp-pfadi-manager` in das Verzeichnis `/wp-content/plugins/` hoch.
2. Aktiviere das Plugin im Menü 'Plugins' in WordPress.
3. Konfiguriere die Einstellungen unter 'Pfadi Aktivitäten' -> 'Konfiguration'.

== Changelog ==

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
