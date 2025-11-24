<?php

class Pfadi_Cron {

	public function __construct() {
		add_action( 'init', array( $this, 'schedule_events' ) );
		add_action( 'pfadi_hourly_cleanup', array( $this, 'run_cleanup' ) );
	}

	public function schedule_events() {
		if ( ! wp_next_scheduled( 'pfadi_hourly_cleanup' ) ) {
			wp_schedule_event( time(), 'hourly', 'pfadi_hourly_cleanup' );
		}
	}

	public function run_cleanup() {
		global $wpdb;

		// Direct SQL update for better performance
		// Sets all published activities with end_time < NOW to draft
		$sql = $wpdb->prepare(
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
		);

		$wpdb->query( $sql );
	}
}
