<?php
/**
 * Plugin Name: Pfadi-Aktivitäten Manager
 * Description: Digitalisiert und automatisiert den Informationsfluss einer Pfadi-Abteilung.
 * Version: 1.1.1
 * Author: Ursin Saluz v/o Schlingel mit Antigravity
 * Text Domain: wp-pfadi-manager
 *
 * Changelog:
 * 1.1.1
 * - NEU: "Mitteilungen" als eigener Menüpunkt.
 * - NEU: Konfigurierbarer URL-Slug für Mitteilungen.
 * - NEU: Logging für E-Mail Versand (Debug).
 * - FIX: Barrierefreiheit im Abo-Formular.
 * - FIX: Filter-Logik für "Abteilung" (zeigt alle).
 *
 * 1.1.0
 * - NEU: AJAX-basierte Filter-Tabs für Aktivitäten (kein Neuladen der Seite mehr).
 * - NEU: Content-Typ "Mitteilungen" für allgemeine Infos.
 * - NEU: E-Mail Versandplanung (Geplant vs. Sofort).
 * - NEU: "Sofort versenden" Option bei Erstellung.
 * - FIX: Diverse kleine Verbesserungen.
 *
 * 1.0.1
 * - Initiale Version mit Aktivitäten, Abos und E-Mail Versand.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PFADI_MANAGER_VERSION', '1.1.1' );
define( 'PFADI_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
define( 'PFADI_MANAGER_URL', plugin_dir_url( __FILE__ ) );

require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-loader.php';

function run_pfadi_manager() {
	$plugin = new Pfadi_Loader();
	$plugin->run();
}
run_pfadi_manager();
