<?php

class Pfadi_Feeds {

	public function __construct() {
		add_action( 'init', array( $this, 'add_feed_endpoints' ) );
		add_action( 'template_redirect', array( $this, 'render_ical_feed' ) );
		add_filter( 'the_content_feed', array( $this, 'inject_rss_content' ) );
	}

	public function add_feed_endpoints() {
		add_feed( 'pfadi-rss', array( $this, 'do_pfadi_rss' ) );
	}

	public function do_pfadi_rss() {
		// Just load the standard RSS2 template, but our filter will run
		do_feed_rss2( false );
	}

	public function inject_rss_content( $content ) {
		global $post;
		if ( 'activity' !== $post->post_type && 'announcement' !== $post->post_type ) {
			return $content;
		}

		$start    = get_post_meta( $post->ID, '_pfadi_start_time', true );
		$end      = get_post_meta( $post->ID, '_pfadi_end_time', true );
		$location = get_post_meta( $post->ID, '_pfadi_location', true );
		$bring    = get_post_meta( $post->ID, '_pfadi_bring', true );

		$start_date = date_i18n( 'd.m.Y H:i', strtotime( $start ) );
		$end_date   = date_i18n( 'H:i', strtotime( $end ) );

		$html  = '<table border="1" cellpadding="5" cellspacing="0">';
		$html .= '<tr><td><strong>Wann:</strong></td><td>' . esc_html( $start_date . ' - ' . $end_date ) . '</td></tr>';
		$html .= '<tr><td><strong>Wo:</strong></td><td>' . esc_html( $location ) . '</td></tr>';
		$html .= '<tr><td><strong>Mitnehmen:</strong></td><td>' . nl2br( esc_html( $bring ) ) . '</td></tr>';
		$html .= '</table>';

		return $html . $content;
	}

	public function render_ical_feed() {
		if ( isset( $_GET['pfadi-events'] ) && $_GET['pfadi-events'] == 'ics' ) {
			header( 'Content-Type: text/calendar; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="pfadi-events.ics"' );

			echo "BEGIN:VCALENDAR\r\n";
			echo "VERSION:2.0\r\n";
			echo "PRODID:-//Pfadi Manager//NONSGML v1.0//EN\r\n";
			echo "CALSCALE:GREGORIAN\r\n";

			$stufen_param = isset( $_GET['stufen'] ) ? sanitize_text_field( $_GET['stufen'] ) : '';
			$stufen       = explode( ',', $stufen_param );

			// Always include Abteilung
			if ( ! in_array( 'abteilung', $stufen ) ) {
				$stufen[] = 'abteilung';
			}

			$args = array(
				'post_type'      => array( 'activity', 'announcement' ),
				'posts_per_page' => -1,
				'tax_query'      => array(
					array(
						'taxonomy' => 'activity_unit',
						'field'    => 'slug',
						'terms'    => $stufen,
					),
				),
				'meta_query'     => array(
					array(
						'key'     => '_pfadi_end_time',
						'value'   => current_time( 'Y-m-d\TH:i' ),
						'compare' => '>', // Only future events? Spec implies all or future? Usually feeds are future.
						// Spec: "Sichtbarkeits-Logik: Zeigt nur Aktivitäten, deren Endzeit > JETZT ist." (for Board)
						// For iCal, it's usually good to show recent history too, but let's stick to future for now or maybe last 30 days?
						// Let's stick to future to keep it clean as per board logic, or maybe all?
						// Spec doesn't explicitly say for iCal. Let's assume future + recent past?
						// Let's just do all for now, or maybe limit to last month?
						// Let's follow the board logic for consistency: Future only.
						'type'    => 'DATETIME',
					),
				),
			);

			$query = new WP_Query( $args );

			while ( $query->have_posts() ) {
				$query->the_post();
				$id = get_the_ID();

				$start    = get_post_meta( $id, '_pfadi_start_time', true );
				$end      = get_post_meta( $id, '_pfadi_end_time', true );
				$location = get_post_meta( $id, '_pfadi_location', true );
				$bring    = get_post_meta( $id, '_pfadi_bring', true );
				$special  = get_post_meta( $id, '_pfadi_special', true );
				$leaders  = get_post_meta( $id, '_pfadi_leaders', true );

				// Convert to UTC for iCal
				$dtstart = get_gmt_from_date( date( 'Y-m-d H:i:s', strtotime( $start ) ), 'Ymd\THis\Z' );
				$dtend   = get_gmt_from_date( date( 'Y-m-d H:i:s', strtotime( $end ) ), 'Ymd\THis\Z' );
				$now     = gmdate( 'Ymd\THis\Z' );

				$description = '';
				if ( 'activity' === get_post_type( $id ) ) {
					$description = "Mitnehmen: $bring\n\nBesonderes: $special\n\nLeitung: $leaders";
				} else {
					$description = get_the_content();
				}
				$description = str_replace( "\n", "\\n", $description );

				echo "BEGIN:VEVENT\r\n";
				echo 'UID:' . $id . '@' . $_SERVER['HTTP_HOST'] . "\r\n";
				echo 'DTSTAMP:' . $now . "\r\n";
				echo 'DTSTART:' . $dtstart . "\r\n";
				echo 'DTEND:' . $dtend . "\r\n";
				echo 'SUMMARY:' . get_the_title() . "\r\n";
				echo 'LOCATION:' . $location . "\r\n";
				echo 'DESCRIPTION:' . $description . "\r\n";
				echo "END:VEVENT\r\n";
			}
			wp_reset_postdata();

			echo "END:VCALENDAR\r\n";
			exit;
		}
	}
}
