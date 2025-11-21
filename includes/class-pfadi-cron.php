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
		$args = array(
			'post_type'      => 'activity',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'     => '_pfadi_end_time',
					'value'   => current_time( 'Y-m-d\TH:i' ),
					'compare' => '<',
					'type'    => 'DATETIME',
				),
			),
		);

		$query = new WP_Query( $args );

		while ( $query->have_posts() ) {
			$query->the_post();
			$update_args = array(
				'ID'          => get_the_ID(),
				'post_status' => 'draft',
			);
			wp_update_post( $update_args );
		}
		wp_reset_postdata();
	}
}
