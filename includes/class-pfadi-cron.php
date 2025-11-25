<?php
/**
 * Cron job functionality.
 *
 * @package PfadiManager
 */

/**
 * Handles scheduled events and cleanup tasks.
 */
class Pfadi_Cron {

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'schedule_events' ) );
		add_action( 'pfadi_hourly_cleanup', array( $this, 'run_cleanup' ) );
	}

	/**
	 * Schedule the hourly cleanup event if not already scheduled.
	 */
	public function schedule_events() {
		if ( ! wp_next_scheduled( 'pfadi_hourly_cleanup' ) ) {
			wp_schedule_event( time(), 'hourly', 'pfadi_hourly_cleanup' );
		}
	}

	/**
	 * Run the cleanup task to archive past activities.
	 */
	public function run_cleanup() {
		global $wpdb;

		// Direct SQL update for better performance.
		// Sets all published activities with end_time < NOW to draft.
		$wpdb->query(
			$wpdb->prepare(
				"
				UPDATE $wpdb->posts p
				INNER JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
				SET p.post_status = 'archived'
				WHERE p.post_type = 'activity'
				AND p.post_status = 'publish'
				AND pm.meta_key = '_pfadi_end_time'
				AND pm.meta_value < %s
				",
				current_time( 'Y-m-d\TH:i' )
			)
		);
	}
}
