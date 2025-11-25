<?php
/**
 * Meta boxes functionality.
 *
 * @package PfadiManager
 */

/**
 * Handles the registration and saving of meta boxes.
 */
class Pfadi_Metaboxes {

	/**
	 * Initialize the class.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'edit_form_after_title', array( $this, 'render_validity_fields' ) );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_admin_scripts( $hook ) {
		global $post;

		if ( 'post-new.php' === $hook || 'post.php' === $hook ) {
			if ( 'activity' === $post->post_type ) {
				wp_enqueue_script( 'pfadi-admin-js', PFADI_MANAGER_URL . 'assets/js/admin.js', array( 'jquery' ), '1.0.0', true );

				// Pass settings to JS.
				$units    = array( 'Biber', 'Wölfe', 'Pfadis', 'Pios', 'Rover', 'Abteilung' );
				$settings = array();
				foreach ( $units as $unit ) {
					$slug              = sanitize_title( $unit );
					$settings[ $slug ] = array(
						'greeting'  => get_option( "pfadi_greeting_$slug" ),
						'leaders'   => get_option( "pfadi_leaders_$slug", 'Die Leiter' ),
						'starttime' => get_option( "pfadi_starttime_$slug" ),
						'endtime'   => get_option( "pfadi_endtime_$slug" ),
					);
				}
				wp_localize_script( 'pfadi-admin-js', 'pfadiSettings', $settings );
			}
		}
	}

	/**
	 * Add meta boxes.
	 */
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

		// Move 'activity_unit' taxonomy box to main column, above details.
		remove_meta_box( 'activity_unitdiv', 'activity', 'side' );
		remove_meta_box( 'activity_unitdiv', 'announcement', 'side' );

		add_meta_box(
			'activity_unitdiv',
			__( 'Stufen', 'wp-pfadi-manager' ),
			'post_categories_meta_box', // Standard WP callback for hierarchical taxonomies.
			'activity',
			'normal',
			'high',
			array( 'taxonomy' => 'activity_unit' )
		);

		add_meta_box(
			'activity_unitdiv',
			__( 'Stufen', 'wp-pfadi-manager' ),
			'post_categories_meta_box',
			'announcement',
			'normal',
			'high',
			array( 'taxonomy' => 'activity_unit' )
		);
	}

	/**
	 * Render validity fields for announcements.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_validity_fields( $post ) {
		if ( 'announcement' !== $post->post_type ) {
			return;
		}

		$start_time = get_post_meta( $post->ID, '_pfadi_start_time', true );
		$end_time   = get_post_meta( $post->ID, '_pfadi_end_time', true );

		if ( empty( $start_time ) && empty( $end_time ) ) {
			$now        = time();
			$start_time = gmdate( 'Y-m-d\TH:i', $now );
			$end_time   = gmdate( 'Y-m-d\TH:i', strtotime( '+2 weeks', $now ) );
		} else {
			$start_time = str_replace( ' ', 'T', $start_time );
			$end_time   = str_replace( ' ', 'T', $end_time );
		}
		?>
		<div class="postbox" style="margin-top: 20px; margin-bottom: 20px;">
			<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( 'Gültigkeitsbereich', 'wp-pfadi-manager' ); ?></h2></div>
			<div class="inside">
				<p>
					<label for="pfadi_start_time"><?php esc_html_e( 'Gültig von:', 'wp-pfadi-manager' ); ?></label>
					<input type="datetime-local" id="pfadi_start_time" name="pfadi_start_time" value="<?php echo esc_attr( $start_time ); ?>">
					
					<label for="pfadi_end_time" style="margin-left: 20px;"><?php esc_html_e( 'Gültig bis:', 'wp-pfadi-manager' ); ?></label>
					<input type="datetime-local" id="pfadi_end_time" name="pfadi_end_time" value="<?php echo esc_attr( $end_time ); ?>">
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render meta box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'pfadi_save_meta_box_data', 'pfadi_meta_box_nonce' );

		$start_time       = get_post_meta( $post->ID, '_pfadi_start_time', true );
		$end_time         = get_post_meta( $post->ID, '_pfadi_end_time', true );
		$location         = get_post_meta( $post->ID, '_pfadi_location', true );
		$bring            = get_post_meta( $post->ID, '_pfadi_bring', true );
		$special          = get_post_meta( $post->ID, '_pfadi_special', true );
		$greeting         = get_post_meta( $post->ID, '_pfadi_greeting', true );
		$leaders          = get_post_meta( $post->ID, '_pfadi_leaders', true );
		$send_immediately = get_post_meta( $post->ID, '_pfadi_send_immediately', true );

		// Default values for new posts (Only for Activity now, Announcement handled in render_validity_fields).
		if ( 'activity' === $post->post_type && empty( $start_time ) && empty( $end_time ) ) {
			$next_saturday = new DateTime( 'next saturday 14:00' );
			$start_time    = $next_saturday->format( 'Y-m-d\TH:i' );

			$next_saturday_end = new DateTime( 'next saturday 17:00' );
			$end_time          = $next_saturday_end->format( 'Y-m-d\TH:i' );

			$location = 'Pfadiheim';
			$bring    = 'Gueti Luuna';
		} else {
			$start_time = str_replace( ' ', 'T', $start_time );
			$end_time   = str_replace( ' ', 'T', $end_time );
		}

		if ( 'activity' === $post->post_type ) :
			$start_label = __( 'Startzeit:', 'wp-pfadi-manager' );
			$end_label   = __( 'Endzeit:', 'wp-pfadi-manager' );
			?>
		<p>
			<label for="pfadi_start_time"><?php echo esc_html( $start_label ); ?></label>
			<input type="datetime-local" id="pfadi_start_time" name="pfadi_start_time" value="<?php echo esc_attr( $start_time ); ?>" style="width:100%">
		</p>
		<p>
			<label for="pfadi_end_time"><?php echo esc_html( $end_label ); ?></label>
			<input type="datetime-local" id="pfadi_end_time" name="pfadi_end_time" value="<?php echo esc_attr( $end_time ); ?>" style="width:100%">
		</p>
		<p>
			<label for="pfadi_location"><?php esc_html_e( 'Ort:', 'wp-pfadi-manager' ); ?></label>
			<input type="text" id="pfadi_location" name="pfadi_location" value="<?php echo esc_attr( $location ); ?>" style="width:100%">
		</p>
		<p>
			<label for="pfadi_bring"><?php esc_html_e( 'Mitnehmen:', 'wp-pfadi-manager' ); ?></label>
			<textarea id="pfadi_bring" name="pfadi_bring" style="width:100%" rows="4"><?php echo esc_textarea( $bring ); ?></textarea>
		</p>
		<p>
			<label for="pfadi_special"><?php esc_html_e( 'Besonderes:', 'wp-pfadi-manager' ); ?></label>
			<textarea id="pfadi_special" name="pfadi_special" style="width:100%" rows="2"><?php echo esc_textarea( $special ); ?></textarea>
		</p>
		<p>
			<label for="pfadi_greeting"><?php esc_html_e( 'Gruss:', 'wp-pfadi-manager' ); ?></label>
			<input type="text" id="pfadi_greeting" name="pfadi_greeting" value="<?php echo esc_attr( $greeting ); ?>" style="width:100%">
		</p>
		<p>
			<label for="pfadi_leaders"><?php esc_html_e( 'Leitung:', 'wp-pfadi-manager' ); ?></label>
			<input type="text" id="pfadi_leaders" name="pfadi_leaders" value="<?php echo esc_attr( $leaders ); ?>" style="width:100%">
		</p>

		<?php endif; ?>

		<?php if ( 'scheduled' === get_option( 'pfadi_mail_mode', 'scheduled' ) ) : ?>
		<p>
			<label>
				<input type="checkbox" name="pfadi_send_immediately" value="1" <?php checked( $send_immediately, '1' ); ?>>
				<?php esc_html_e( 'Sofort versenden (ignoriert Zeitplan)', 'wp-pfadi-manager' ); ?>
			</label>
		</p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id The post ID.
	 */
	public function save_meta_boxes( $post_id ) {
		if ( ! isset( $_POST['pfadi_meta_box_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pfadi_meta_box_nonce'] ) ), 'pfadi_save_meta_box_data' ) ) {
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
				$value = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
				// Convert date format from T to space for DB.
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
