<?php

class Pfadi_Mailer {

	public function __construct() {
		add_action( 'transition_post_status', array( $this, 'maybe_send_newsletter' ), 10, 3 );
		add_action( 'pfadi_send_post_email', array( $this, 'send_newsletter_by_id' ) );
	}

	public function maybe_send_newsletter( $new_status, $old_status, $post ) {
		if ( 'activity' !== $post->post_type && 'announcement' !== $post->post_type ) {
			return;
		}

		if ( 'publish' === $new_status && 'publish' !== $old_status ) {
			$mode = get_option( 'pfadi_mail_mode', 'scheduled' );
			$send_immediately = get_post_meta( $post->ID, '_pfadi_send_immediately', true );

			if ( 'immediate' === $mode || '1' === $send_immediately ) {
				// Schedule for "now" to ensure it runs in a separate process/after save_post
				wp_schedule_single_event( time(), 'pfadi_send_post_email', array( $post->ID ) );
			} else {
				// Scheduled mode
				$time_str = get_option( 'pfadi_mail_time', '20:00' );
				$schedule_time = strtotime( $time_str );
				
				if ( time() > $schedule_time ) {
					// Schedule for "now"
					wp_schedule_single_event( time(), 'pfadi_send_post_email', array( $post->ID ) );
				} else {
					wp_schedule_single_event( $schedule_time, 'pfadi_send_post_email', array( $post->ID ) );
				}
			}
		}
	}

	public function send_newsletter_by_id( $post_id ) {
		$post = get_post( $post_id );
		if ( $post ) {
			Pfadi_Logger::log( "Triggering newsletter for post ID: $post_id" );
			$this->send_newsletter( $post );
		} else {
			Pfadi_Logger::log( "Failed to trigger newsletter: Post ID $post_id not found", 'error' );
		}
	}

	private function send_newsletter( $post ) {
		Pfadi_Logger::log( "Starting newsletter process for post: {$post->post_title} (ID: {$post->ID})" );
		$units = wp_get_post_terms( $post->ID, 'activity_unit', array( 'fields' => 'ids' ) );
		
		if ( empty( $units ) && ! is_wp_error( $units ) ) {
			Pfadi_Logger::log( "No units assigned to post ID: {$post->ID}. Aborting." );
			return;
		}

		// Check if 'Abteilung' is one of the units
		$abteilung_term = get_term_by( 'slug', 'abteilung', 'activity_unit' );
		$is_abteilung = false;
		if ( $abteilung_term && in_array( $abteilung_term->term_id, $units ) ) {
			$is_abteilung = true;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'pfadi_subscribers';
		
		// Get all active subscribers
		$subscribers = $wpdb->get_results( "SELECT email, subscribed_units FROM $table_name WHERE status = 'active'" );

		$recipients = array();

		foreach ( $subscribers as $subscriber ) {
			$subscribed_units = json_decode( $subscriber->subscribed_units, true );
			if ( ! is_array( $subscribed_units ) ) {
				continue;
			}

			// If activity is 'Abteilung', send to everyone
			if ( $is_abteilung ) {
				$recipients[] = $subscriber->email;
				continue;
			}

			// Otherwise check intersection
			if ( array_intersect( $units, $subscribed_units ) ) {
				$recipients[] = $subscriber->email;
			}
		}

		$recipients = array_unique( $recipients );

		if ( empty( $recipients ) ) {
			Pfadi_Logger::log( "No recipients found for post ID: {$post->ID}." );
			return;
		}

		Pfadi_Logger::log( "Found " . count( $recipients ) . " recipients for post ID: {$post->ID}." );

		// Get Site Name
		$site_name = get_bloginfo( 'name' );

		// Get Unit Name(s)
		$unit_names = array();
		if ( $is_abteilung || count( $units ) > 1 ) {
			$unit_names[] = __( 'Abteilungs', 'wp-pfadi-manager' );
		} else {
			$term_objects = wp_get_post_terms( $post->ID, 'activity_unit' );
			foreach ( $term_objects as $term ) {
				$unit_names[] = $term->name;
			}
		}
		$unit_str = implode( ', ', $unit_names );
		if ( empty( $unit_str ) ) {
			$unit_str = 'Pfadi';
		}

		// Construct Subject
		$subject_template = get_option( 'pfadi_mail_subject', '[{site_title}] Neue Pfadi-Aktivität: {title}' );
		
		// Support both English and German placeholders, and case-insensitive variants
		$placeholders = array(
			'{site_title}' => $site_name,
			'{SiteTitle}'  => $site_name,
			'{unit}'       => $unit_str,
			'{Unit}'       => $unit_str,
			'{stufe}'      => $unit_str,
			'{Stufe}'      => $unit_str,
			'{title}'      => $post->post_title,
			'{Title}'      => $post->post_title,
			'{titel}'      => $post->post_title,
			'{Titel}'      => $post->post_title,
			'{date}'       => date( 'd.m.y', $start_ts ),
			'{datum}'      => date( 'd.m.y', $start_ts ),
		);

		$subject = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $subject_template );

		if ( 'announcement' === $post->post_type ) {
			// For announcements, we might want a different default or setting, but for now let's use the same logic or a specific one if requested.
			// The user didn't ask for a separate announcement subject setting, but the default was different.
			// Let's use a sensible default if the setting is empty or just use the setting.
			// Actually, the user sees "Betreff für neue Aktivitäten".
			// Maybe we should prepend "Mitteilung:" if it's an announcement?
			// Or just let the user configure it. For now, let's stick to the configured subject but maybe add a fallback/override for announcements if needed.
			// Given the user request, they want to use placeholders.
			// Let's use the same subject setting for now, as it's flexible enough with placeholders.
		}

		$message = $this->get_email_template( $post );
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		foreach ( $recipients as $email ) {
			$sent = wp_mail( $email, $subject, $message, $headers );
			if ( $sent ) {
				Pfadi_Logger::log( "Sent newsletter to $email" );
			} else {
				Pfadi_Logger::log( "Failed to send newsletter to $email", 'error' );
			}
		}
	}

	private function get_email_template( $post ) {
		$start = get_post_meta( $post->ID, '_pfadi_start_time', true );
		$end = get_post_meta( $post->ID, '_pfadi_end_time', true );
		
		$start_ts = strtotime( $start );
		$end_ts = strtotime( $end );

		// Date Formatting Logic
		// Format: Samstag, 21.11.2025 von 14:00 bis 17:00 (if same day)
		// Format: Samstag, 21.11.2025 14:00 bis Sonntag, 22.11.2025 14:00 (if different day)

		$start_day = date( 'Ymd', $start_ts );
		$end_day = date( 'Ymd', $end_ts );

		if ( $start_day === $end_day ) {
			// Same day
			$date_str = sprintf(
				__( '%s von %s bis %s', 'wp-pfadi-manager' ),
				date_i18n( 'l, d.m.Y', $start_ts ),
				date_i18n( 'H:i', $start_ts ),
				date_i18n( 'H:i', $end_ts )
			);
		} else {
			// Different days
			$date_str = sprintf(
				__( '%s bis %s', 'wp-pfadi-manager' ),
				date_i18n( 'l, d.m.Y H:i', $start_ts ),
				date_i18n( 'l, d.m.Y H:i', $end_ts )
			);
		}

		// Prepare Placeholders
		$placeholders = array(
			'{title}'      => esc_html( $post->post_title ),
			'{site_title}' => get_bloginfo( 'name' ),
			'{date_str}'   => esc_html( $date_str ),
			'{content}'    => wpautop( wp_kses_post( $post->post_content ) ),
		);

		// Unit placeholders
		$units = wp_get_post_terms( $post->ID, 'activity_unit' );
		$unit_names = array();
		if ( $units && ! is_wp_error( $units ) ) {
			foreach ( $units as $unit ) {
				$unit_names[] = $unit->name;
			}
		}
		$placeholders['{unit}'] = esc_html( implode( ', ', $unit_names ) );

		// Activity specific placeholders
		if ( 'activity' === $post->post_type ) {
			$location = get_post_meta( $post->ID, '_pfadi_location', true );
			$bring = get_post_meta( $post->ID, '_pfadi_bring', true );
			$special = get_post_meta( $post->ID, '_pfadi_special', true );
			$greeting = get_post_meta( $post->ID, '_pfadi_greeting', true );
			$leaders = get_post_meta( $post->ID, '_pfadi_leaders', true );

			$placeholders['{location}'] = esc_html( $location );
			$placeholders['{bring}']    = nl2br( esc_html( $bring ) );
			$placeholders['{special}']  = nl2br( esc_html( $special ) );
			$placeholders['{greeting}'] = esc_html( $greeting );
			$placeholders['{leaders}']  = esc_html( $leaders );
		} else {
			// Empty strings for activity placeholders if not activity
			$placeholders['{location}'] = '';
			$placeholders['{bring}']    = '';
			$placeholders['{special}']  = '';
			$placeholders['{greeting}'] = '';
			$placeholders['{leaders}']  = '';
		}

		// Get Template
		if ( 'activity' === $post->post_type ) {
			$template = get_option( 'pfadi_mail_template_activity' );
		} else {
			$template = get_option( 'pfadi_mail_template_announcement' );
		}

		if ( empty( $template ) ) {
			// Default Template
			ob_start();
			?>
			<!DOCTYPE html>
			<html>
			<head>
				<style>
					body { font-family: sans-serif; line-height: 1.6; }
					.container { max-width: 600px; margin: 0 auto; padding: 20px; }
					h1 { color: #333; }
					.meta { background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
					.label { font-weight: bold; }
				</style>
			</head>
			<body>
				<div class="container">
					<h1><?php echo $placeholders['{title}']; ?></h1>
					
					<div class="meta">
						<p><span class="label"><?php _e( 'Wann:', 'wp-pfadi-manager' ); ?></span> <?php echo $placeholders['{date_str}']; ?></p>
						<?php if ( 'activity' === $post->post_type ) : ?>
							<p><span class="label"><?php _e( 'Wo:', 'wp-pfadi-manager' ); ?></span> <?php echo $placeholders['{location}']; ?></p>
						<?php endif; ?>
					</div>

					<?php if ( 'activity' === $post->post_type ) : ?>
						<p><span class="label"><?php _e( 'Mitnehmen:', 'wp-pfadi-manager' ); ?></span><br>
						<?php echo $placeholders['{bring}']; ?></p>

						<?php if ( ! empty( $placeholders['{special}'] ) ) : ?>
							<p><span class="label"><?php _e( 'Besonderes:', 'wp-pfadi-manager' ); ?></span><br>
							<?php echo $placeholders['{special}']; ?></p>
						<?php endif; ?>

						<hr>

						<p><?php echo $placeholders['{greeting}']; ?></p>
						<p><em><?php echo $placeholders['{leaders}']; ?></em></p>
					<?php else : ?>
						<div class="content">
							<?php echo $placeholders['{content}']; ?>
						</div>
					<?php endif; ?>
				</div>
			</body>
			</html>
			<?php
			return ob_get_clean();
		} else {
			// Custom Template
			return str_replace( array_keys( $placeholders ), array_values( $placeholders ), $template );
		}
		return ob_get_clean(); // Should be unreachable but safe
	}
}
