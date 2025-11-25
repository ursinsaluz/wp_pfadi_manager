<?php
/**
 * Database handling functionality.
 *
 * @package PfadiManager
 */

/**
 * Handles database table creation and updates.
 */
class Pfadi_DB {

	/**
	 * Create the subscribers table.
	 */
	public function create_table() {
		global $wpdb;
		$table_name      = $wpdb->prefix . 'pfadi_subscribers';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			email varchar(100) NOT NULL,
			subscribed_units longtext NOT NULL,
			token varchar(100) NOT NULL,
			status varchar(20) DEFAULT 'pending' NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY email (email)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
