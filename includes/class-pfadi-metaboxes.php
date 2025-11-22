<?php

class Pfadi_Metaboxes {

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	public function enqueue_admin_scripts( $hook ) {
		global $post;

		if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
			if ( 'activity' === $post->post_type ) {
				wp_enqueue_script( 'pfadi-admin-js', PFADI_MANAGER_URL . 'assets/js/admin.js', array( 'jquery' ), '1.0.0', true );
				
				// Pass settings to JS
				$units = array( 'Biber', 'Wölfe', 'Pfadis', 'Pios', 'Rover', 'Abteilung' );
				$settings = array();
				foreach ( $units as $unit ) {
					$slug = sanitize_title( $unit );
					$settings[ $slug ] = array(
						'greeting' => get_option( "pfadi_greeting_$slug" ),
						'leaders'  => get_option( "pfadi_leaders_$slug" ),
						'starttime' => get_option( "pfadi_starttime_$slug" ),
						'endtime'   => get_option( "pfadi_endtime_$slug" ),
					);
				}
				wp_localize_script( 'pfadi-admin-js', 'pfadiSettings', $settings );
			}
		}
	}

	public function add_meta_boxes() {
		add_meta_box(
			'pfadi_activity_details',
			__( 'Aktivitäts-Details', 'wp-pfadi-manager' ),
			array( $this, 'render_meta_box' ),
			'activity',
			'normal',
			'high'
		);

		add_meta_box(
			'pfadi_announcement_details',
			__( 'Mitteilungs-Details', 'wp-pfadi-manager' ),
			array( $this, 'render_meta_box' ),
			'announcement',
			'normal',
			'high'
		);

		// Move 'activity_unit' taxonomy box to main column, above details
		remove_meta_box( 'activity_unitdiv', 'activity', 'side' );
		add_meta_box(
			'activity_unitdiv',
			__( 'Stufen', 'wp-pfadi-manager' ),
			'post_categories_meta_box', // Standard WP callback for hierarchical taxonomies
			'activity',
			'normal',
			'high',
			array( 'taxonomy' => 'activity_unit' )
		);
	}

	public function render_meta_box( $post ) {
		wp_nonce_field( 'pfadi_save_meta_box_data', 'pfadi_meta_box_nonce' );

		$start_time = get_post_meta( $post->ID, '_pfadi_start_time', true );
		$end_time = get_post_meta( $post->ID, '_pfadi_end_time', true );
		$location = get_post_meta( $post->ID, '_pfadi_location', true );
		$bring = get_post_meta( $post->ID, '_pfadi_bring', true );
		$special = get_post_meta( $post->ID, '_pfadi_special', true );
		$greeting = get_post_meta( $post->ID, '_pfadi_greeting', true );
		$greeting = get_post_meta( $post->ID, '_pfadi_greeting', true );
		$leaders = get_post_meta( $post->ID, '_pfadi_leaders', true );
		$send_immediately = get_post_meta( $post->ID, '_pfadi_send_immediately', true );

		// Default values for new posts
		if ( empty( $start_time ) && empty( $end_time ) ) {
			if ( 'announcement' === $post->post_type ) {
				// Announcement: Valid from NOW, until 2 weeks later
				$now = current_time( 'timestamp' );
				$start_time = date( 'Y-m-d\TH:i', $now );
				$end_time = date( 'Y-m-d\TH:i', strtotime( '+2 weeks', $now ) );
			} else {
				// Activity: Next Saturday 14:00 - 17:00
				$next_saturday = new DateTime( 'next saturday 14:00' );
				$start_time = $next_saturday->format( 'Y-m-d\TH:i' );
				
				$next_saturday_end = new DateTime( 'next saturday 17:00' );
				$end_time = $next_saturday_end->format( 'Y-m-d\TH:i' );
			}

			$location = 'Pfadiheim';
			$bring = 'Gueti Luuna';
		} else {
			// Ensure format for input (needs T)
			$start_time = str_replace( ' ', 'T', $start_time );
			$end_time = str_replace( ' ', 'T', $end_time );
		}

		$start_label = 'activity' === $post->post_type ? __( 'Startzeit:', 'wp-pfadi-manager' ) : __( 'Gültig von:', 'wp-pfadi-manager' );
		$end_label = 'activity' === $post->post_type ? __( 'Endzeit:', 'wp-pfadi-manager' ) : __( 'Gültig bis:', 'wp-pfadi-manager' );
		?>
		<p>
			<label for="pfadi_start_time"><?php echo esc_html( $start_label ); ?></label>
			<input type="datetime-local" id="pfadi_start_time" name="pfadi_start_time" value="<?php echo esc_attr( $start_time ); ?>" style="width:100%">
		</p>
		<p>
			<label for="pfadi_end_time"><?php echo esc_html( $end_label ); ?></label>
			<input type="datetime-local" id="pfadi_end_time" name="pfadi_end_time" value="<?php echo esc_attr( $end_time ); ?>" style="width:100%">
		</p>
		<?php if ( 'activity' === $post->post_type ) : ?>
		<p>
			<label for="pfadi_location"><?php _e( 'Ort:', 'wp-pfadi-manager' ); ?></label>
			<input type="text" id="pfadi_location" name="pfadi_location" value="<?php echo esc_attr( $location ); ?>" style="width:100%">
		</p>
		<p>
			<label for="pfadi_bring"><?php _e( 'Mitnehmen:', 'wp-pfadi-manager' ); ?></label>
			<textarea id="pfadi_bring" name="pfadi_bring" style="width:100%" rows="4"><?php echo esc_textarea( $bring ); ?></textarea>
		</p>
		<p>
			<label for="pfadi_special"><?php _e( 'Besonderes:', 'wp-pfadi-manager' ); ?></label>
			<textarea id="pfadi_special" name="pfadi_special" style="width:100%" rows="2"><?php echo esc_textarea( $special ); ?></textarea>
		</p>
		<p>
			<label for="pfadi_greeting"><?php _e( 'Gruss:', 'wp-pfadi-manager' ); ?></label>
			<select id="pfadi_greeting" name="pfadi_greeting" style="width:100%">
				<option value="Allzeit bereit" <?php selected( $greeting, 'Allzeit bereit' ); ?>>Allzeit bereit</option>
				<option value="Bewusst handlä" <?php selected( $greeting, 'Bewusst handlä' ); ?>>Bewusst handlä</option>
				<option value="Zäma Wiiter" <?php selected( $greeting, 'Zäma Wiiter' ); ?>>Zäma Wiiter</option>
				<option value="Üsers Bescht" <?php selected( $greeting, 'Üsers Bescht' ); ?>>Üsers Bescht</option>
				<option value="Mit freud debi" <?php selected( $greeting, 'Mit freud debi' ); ?>>Mit freud debi</option>
			</select>
		</p>
		<p>
			<label for="pfadi_leaders"><?php _e( 'Leitung:', 'wp-pfadi-manager' ); ?></label>
			<input type="text" id="pfadi_leaders" name="pfadi_leaders" value="<?php echo esc_attr( $leaders ); ?>" style="width:100%">
		</p>

		<?php endif; ?>

		<?php if ( 'scheduled' === get_option( 'pfadi_mail_mode', 'scheduled' ) ) : ?>
		<p>
			<label>
				<input type="checkbox" name="pfadi_send_immediately" value="1" <?php checked( $send_immediately, '1' ); ?>>
				<?php _e( 'Sofort versenden (ignoriert Zeitplan)', 'wp-pfadi-manager' ); ?>
			</label>
		</p>
		<?php endif; ?>
		<?php
	}

	public function save_meta_boxes( $post_id ) {
		if ( ! isset( $_POST['pfadi_meta_box_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_POST['pfadi_meta_box_nonce'], 'pfadi_save_meta_box_data' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array( 'pfadi_start_time', 'pfadi_end_time', 'pfadi_location', 'pfadi_bring', 'pfadi_special', 'pfadi_greeting', 'pfadi_leaders' );

		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				$value = sanitize_text_field( $_POST[ $field ] );
				// Convert date format from T to space for DB
				if ( 'pfadi_start_time' === $field || 'pfadi_end_time' === $field ) {
					$value = str_replace( 'T', ' ', $value );
				}
				update_post_meta( $post_id, '_' . $field, $value );
			}
		}

		if ( isset( $_POST['pfadi_send_immediately'] ) ) {
			update_post_meta( $post_id, '_pfadi_send_immediately', '1' );
		} else {
			delete_post_meta( $post_id, '_pfadi_send_immediately' );
		}
	}
}
