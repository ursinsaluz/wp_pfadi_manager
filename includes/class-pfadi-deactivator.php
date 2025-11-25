<?php
/**
 * Fired during plugin deactivation.
 *
 * @package PfadiManager
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 */
class Pfadi_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// Flush rewrite rules so that custom post types work.
		flush_rewrite_rules();
	}
}
