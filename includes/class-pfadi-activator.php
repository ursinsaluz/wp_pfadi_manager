<?php
/**
 * Fired during plugin activation.
 *
 * @package PfadiManager
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class Pfadi_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// Flush rewrite rules so that custom post types work.
		flush_rewrite_rules();
	}

}
