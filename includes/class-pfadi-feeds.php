<?php
/**
 * Feed functionality.
 *
 * @package PfadiManager
 */

/**
 * Handles RSS and iCal feeds.
 */
class Pfadi_Feeds {

	/**
	 * Initialize the class.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'add_feed_endpoints' ) );
		add_action( 'template_redirect', array( $this, 'render_ical_feed' ) );
		add_filter( 'the_content_feed', array( $this, 'inject_rss_content' ) );
	}

	/**
	 * Add custom feed endpoints.
	 */
	public function add_feed_endpoints() {
		add_feed( 'pfadi-rss', array( $this, 'do_pfadi_rss' ) );
	}

	/**
	 * Render the custom RSS feed.
	 */
	public function do_pfadi_rss() {
		// Just load the standard RSS2 template, but our filter will run.
		do_feed_rss2( false );
	}

	/**
	 * Inject activity details into RSS content.
	 *
	 * @param string $content The post content.
	 * @return string Modified content.
	 */
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

	/**
	 * Render the iCal feed.
	 */
	public function render_ical_feed() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['pfadi-events'] ) && 'ics' === $_GET['pfadi-events'] ) {
			header( 'Content-Type: text/calendar; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="pfadi-events.ics"' );

			echo "BEGIN:VCALENDAR\r\n";
			echo "VERSION:2.0\r\n";
			echo "PRODID:-//Pfadi Manager//NONSGML v1.0//EN\r\n";
			echo "CALSCALE:GREGORIAN\r\n";

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$stufen_param = isset( $_GET['stufen'] ) ? sanitize_text_field( wp_unslash( $_GET['stufen'] ) ) : '';
			$stufen       = explode( ',', $stufen_param );

			// Always include Abteilung.
			// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			if ( ! in_array( 'abteilung', $stufen ) ) {
				$stufen[] = 'abteilung';
			}

			$args = array(
				'post_type'      => array( 'activity', 'announcement' ),
				'posts_per_page' => -1,
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'activity_unit',
						'field'    => 'slug',
						'terms'    => $stufen,
					),
				),
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => '_pfadi_end_time',
						'value'   => current_time( 'Y-m-d\TH:i' ),
						'compare' => '>', // Only future events? Spec implies all or future? Usually feeds are future.
						// Spec: "Sichtbarkeits-Logik: Zeigt nur Aktivitäten, deren Endzeit > JETZT ist." (for Board).
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

				// Convert to UTC for iCal.
				// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				$dtstart = get_gmt_from_date( date( 'Y-m-d H:i:s', strtotime( $start ) ), 'Ymd\THis\Z' );
				// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				$dtend = get_gmt_from_date( date( 'Y-m-d H:i:s', strtotime( $end ) ), 'Ymd\THis\Z' );
				$now   = gmdate( 'Ymd\THis\Z' );

				$description = '';
				if ( 'activity' === get_post_type( $id ) ) {
					$description = "Mitnehmen: $bring\n\nBesonderes: $special\n\nLeitung: $leaders";
				} else {
					$description = get_the_content();
				}
				$description = str_replace( "\n", "\\n", $description );

				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
				$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : 'localhost';

				echo "BEGIN:VEVENT\r\n";
				echo 'UID:' . $id . '@' . $host . "\r\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo 'DTSTAMP:' . $now . "\r\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo 'DTSTART:' . $dtstart . "\r\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo 'DTEND:' . $dtend . "\r\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo 'SUMMARY:' . get_the_title() . "\r\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo 'LOCATION:' . $location . "\r\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo 'DESCRIPTION:' . $description . "\r\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo "END:VEVENT\r\n";
			}
			wp_reset_postdata();

			echo "END:VCALENDAR\r\n";
			exit;
		}
	}
}
