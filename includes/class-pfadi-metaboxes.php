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
					);
				}
				wp_localize_script( 'pfadi-admin-js', 'pfadiSettings', $settings );
			}
		}
	}

	public function add_meta_boxes() {
		add_meta_box(
			'pfadi_activity_details',
			'Aktivitäts-Details',
			array( $this, 'render_meta_box' ),
			'activity',
			'normal',
			'high'
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
		$leaders = get_post_meta( $post->ID, '_pfadi_leaders', true );

		// Default values for new posts
		if ( empty( $start_time ) && empty( $end_time ) ) {
			// Next Saturday 14:00
			$next_saturday = new DateTime( 'next saturday 14:00' );
			$start_time = $next_saturday->format( 'Y-m-d\TH:i' );
			
			// Next Saturday 17:00
			$next_saturday_end = new DateTime( 'next saturday 17:00' );
			$end_time = $next_saturday_end->format( 'Y-m-d\TH:i' );

			$location = 'Pfadiheim';
			$bring = 'Gueti Luuna';
		}

		?>
		<p>
			<label for="pfadi_start_time">Startzeit:</label>
			<input type="datetime-local" id="pfadi_start_time" name="pfadi_start_time" value="<?php echo esc_attr( $start_time ); ?>" style="width:100%">
		</p>
		<p>
			<label for="pfadi_end_time">Endzeit:</label>
			<input type="datetime-local" id="pfadi_end_time" name="pfadi_end_time" value="<?php echo esc_attr( $end_time ); ?>" style="width:100%">
		</p>
		<p>
			<label for="pfadi_location">Ort:</label>
			<input type="text" id="pfadi_location" name="pfadi_location" value="<?php echo esc_attr( $location ); ?>" style="width:100%">
		</p>
		<p>
			<label for="pfadi_bring">Mitnehmen:</label>
			<textarea id="pfadi_bring" name="pfadi_bring" style="width:100%" rows="4"><?php echo esc_textarea( $bring ); ?></textarea>
		</p>
		<p>
			<label for="pfadi_special">Besonderes:</label>
			<textarea id="pfadi_special" name="pfadi_special" style="width:100%" rows="2"><?php echo esc_textarea( $special ); ?></textarea>
		</p>
		<p>
			<label for="pfadi_greeting">Gruss:</label>
			<select id="pfadi_greeting" name="pfadi_greeting" style="width:100%">
				<option value="Allzeit bereit" <?php selected( $greeting, 'Allzeit bereit' ); ?>>Allzeit bereit</option>
				<option value="Bewusst handlä" <?php selected( $greeting, 'Bewusst handlä' ); ?>>Bewusst handlä</option>
				<option value="Zäma Wiiter" <?php selected( $greeting, 'Zäma Wiiter' ); ?>>Zäma Wiiter</option>
				<option value="Üsers Bescht" <?php selected( $greeting, 'Üsers Bescht' ); ?>>Üsers Bescht</option>
				<option value="Mit freud debi" <?php selected( $greeting, 'Mit freud debi' ); ?>>Mit freud debi</option>
			</select>
		</p>
		<p>
			<label for="pfadi_leaders">Leitung:</label>
			<input type="text" id="pfadi_leaders" name="pfadi_leaders" value="<?php echo esc_attr( $leaders ); ?>" style="width:100%">
		</p>
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
				update_post_meta( $post_id, '_' . $field, sanitize_text_field( $_POST[ $field ] ) );
			}
		}
	}
}
