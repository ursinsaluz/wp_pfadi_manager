<?php
/**
 * Plugin Name: Pfadi-Aktivitäten Manager
 * Description: Digitalisiert und automatisiert den Informationsfluss einer Pfadi-Abteilung.
 * Version: 1.0.1
 * Author: Ursin Saluz v/o Schlingel mit Antigravity
 * Text Domain: wp-pfadi-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PFADI_MANAGER_VERSION', '1.0.1' );
define( 'PFADI_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
define( 'PFADI_MANAGER_URL', plugin_dir_url( __FILE__ ) );

require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-loader.php';

function run_pfadi_manager() {
	$plugin = new Pfadi_Loader();
	$plugin->run();
}
run_pfadi_manager();
