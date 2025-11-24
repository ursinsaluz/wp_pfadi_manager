<?php
/**
 * Plugin Name: Pfadi-Aktivitäten Manager
 * Description: Digitalisiert und automatisiert den Informationsfluss einer Pfadi-Abteilung.
 * Version: 1.2.0
 * Author: Ursin Saluz v/o Schlingel mit Antigravity
 * Text Domain: wp-pfadi-manager
 *
 * Changelog:
 * 1.2.0
 * - NEU: "Side Tabs" Ansicht für Aktivitäten (view="list").
 * - NEU: Einstellungs-Seite für Standard-Werte (Gruss, Leitung, Zeiten).
 * - NEU: Automatische Befüllung von Aktivitäts-Feldern basierend auf Stufe.
 * - NEU: Start- und Endzeit pro Stufe konfigurierbar.
 * - UX: Stufen-Auswahl im Editor nach oben verschoben.
 * - FIX: Anzeige von Mitteilungen korrigiert.
 * - FIX: Synchronisation von mehreren Aktivitäts-Boards auf einer Seite.
 *
 * 1.1.1
 * - NEU: "Mitteilungen" als eigener Menüpunkt.
 * - NEU: Konfigurierbarer URL-Slug für Mitteilungen.
 * - NEU: Logging für E-Mail Versand (Debug).
 * - FIX: Barrierefreiheit im Abo-Formular.
 * - FIX: Filter-Logik für "Abteilung" (zeigt alle).
 *
 * @package PfadiManager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'PFADI_MANAGER_VERSION', '1.2.1' );
define( 'PFADI_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
define( 'PFADI_MANAGER_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pfadi-activator.php
 */
function activate_pfadi_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pfadi-activator.php';
	Pfadi_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pfadi-deactivator.php
 */
function deactivate_pfadi_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pfadi-deactivator.php';
	Pfadi_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_pfadi_manager' );
register_deactivation_hook( __FILE__, 'deactivate_pfadi_manager' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-pfadi-loader.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function pfadi_manager_run() {

	$plugin = new Pfadi_Loader();
	$plugin->run();
}
pfadi_manager_run();

/**
 * Load the plugin text domain for translation.
 */
function pfadi_manager_load_textdomain() {
	load_plugin_textdomain( 'wp-pfadi-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'pfadi_manager_load_textdomain' );
