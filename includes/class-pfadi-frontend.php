<?php

class Pfadi_Frontend {

	public function __construct() {
		add_shortcode( 'pfadi_board', array( $this, 'render_board' ) );
		add_shortcode( 'pfadi_subscribe', array( $this, 'render_subscribe' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'init', array( $this, 'handle_subscription_actions' ) );
		add_action( 'wp_ajax_pfadi_subscribe', array( $this, 'handle_ajax_subscription' ) );
		add_action( 'wp_ajax_nopriv_pfadi_subscribe', array( $this, 'handle_ajax_subscription' ) );
		add_action( 'wp_ajax_pfadi_load_activities', array( $this, 'handle_ajax_load_activities' ) );
		add_action( 'wp_ajax_nopriv_pfadi_load_activities', array( $this, 'handle_ajax_load_activities' ) );
	}

	public function enqueue_scripts() {
		wp_enqueue_style( 'pfadi-style', PFADI_MANAGER_URL . 'assets/css/style.css', array(), '1.0.0' );
		wp_enqueue_script( 'pfadi-frontend-js', PFADI_MANAGER_URL . 'assets/js/pfadi-frontend.js', array(), '1.0.0', true );
		wp_localize_script( 'pfadi-frontend-js', 'pfadi_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'pfadi_subscribe_nonce' ),
		) );
	}

	public function render_board( $atts ) {
		$atts = shortcode_atts( array(
			'view' => 'cards', // cards or table
		), $atts );

		// Filter dropdown
		$units = get_terms( array(
			'taxonomy' => 'activity_unit',
			'hide_empty' => false,
		) );

		$selected_unit = isset( $_GET['pfadi_unit'] ) ? sanitize_text_field( $_GET['pfadi_unit'] ) : '';

		ob_start();
		?>
		<div class="pfadi-board">
			<ul class="pfadi-tabs">
				<li><a href="#" data-unit="" class="<?php echo empty( $selected_unit ) ? 'active' : ''; ?>">Alle Stufen</a></li>
				<?php foreach ( $units as $unit ) : ?>
					<li>
						<a href="#" data-unit="<?php echo esc_attr( $unit->slug ); ?>" class="<?php echo $selected_unit === $unit->slug ? 'active' : ''; ?>">
							<?php echo esc_html( $unit->name ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>

			<div id="pfadi-activities-content" data-view="<?php echo esc_attr( $atts['view'] ); ?>">
				<?php
				$args = array(
					'post_type'      => 'activity',
					'posts_per_page' => -1,
					'meta_key'       => '_pfadi_end_time',
					'orderby'        => 'meta_value',
					'order'          => 'ASC',
					'meta_query'     => array(
						array(
							'key'     => '_pfadi_end_time',
							'value'   => current_time( 'Y-m-d\TH:i' ),
							'compare' => '>',
							'type'    => 'DATETIME',
						),
					),
				);

				if ( ! empty( $selected_unit ) ) {
					$args['tax_query'] = array(
						array(
							'taxonomy' => 'activity_unit',
							'field'    => 'slug',
							'terms'    => $selected_unit,
						),
					);
				}

				$query = new WP_Query( $args );

				if ( $query->have_posts() ) :
					if ( 'table' === $atts['view'] ) {
						$this->render_table_view( $query );
					} else {
						$this->render_card_view( $query );
					}
					wp_reset_postdata();
				else :
					echo '<p>Keine aktuellen Aktivitäten.</p>';
				endif;
				?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	private function render_card_view( $query ) {
		echo '<div class="pfadi-cards">';
		while ( $query->have_posts() ) : $query->the_post();
			$start = get_post_meta( get_the_ID(), '_pfadi_start_time', true );
			$end = get_post_meta( get_the_ID(), '_pfadi_end_time', true );
			$location = get_post_meta( get_the_ID(), '_pfadi_location', true );
			$bring = get_post_meta( get_the_ID(), '_pfadi_bring', true );
			
			$start_date = date_i18n( 'd.m.Y H:i', strtotime( $start ) );
			$end_date = date_i18n( 'H:i', strtotime( $end ) );
			?>
			<div class="pfadi-card">
				<h3><?php the_title(); ?></h3>
				<p><strong>Wann:</strong> <?php echo esc_html( $start_date . ' - ' . $end_date ); ?></p>
				<p><strong>Wo:</strong> <?php echo esc_html( $location ); ?></p>
				<p><strong>Mitnehmen:</strong><br><?php echo nl2br( esc_html( $bring ) ); ?></p>
			</div>
			<?php
		endwhile;
		echo '</div>';
	}

	private function render_table_view( $query ) {
		echo '<table class="pfadi-table">';
		echo '<thead><tr><th>Aktivität</th><th>Wann</th><th>Wo</th><th>Mitnehmen</th></tr></thead>';
		echo '<tbody>';
		while ( $query->have_posts() ) : $query->the_post();
			$start = get_post_meta( get_the_ID(), '_pfadi_start_time', true );
			$end = get_post_meta( get_the_ID(), '_pfadi_end_time', true );
			$location = get_post_meta( get_the_ID(), '_pfadi_location', true );
			$bring = get_post_meta( get_the_ID(), '_pfadi_bring', true );
			
			$start_date = date_i18n( 'd.m.Y H:i', strtotime( $start ) );
			$end_date = date_i18n( 'H:i', strtotime( $end ) );
			?>
			<tr>
				<td><?php the_title(); ?></td>
				<td><?php echo esc_html( $start_date . ' - ' . $end_date ); ?></td>
				<td><?php echo esc_html( $location ); ?></td>
				<td><?php echo nl2br( esc_html( $bring ) ); ?></td>
			</tr>
			<?php
		endwhile;
		echo '</tbody></table>';
	}

	public function render_subscribe( $atts ) {
		$units = get_terms( array(
			'taxonomy' => 'activity_unit',
			'hide_empty' => false,
			'exclude' => get_term_by( 'slug', 'abteilung', 'activity_unit' )->term_id, // Exclude Abteilung as it's auto-included
		) );

		ob_start();
		?>
		<div class="pfadi-subscribe">
			<div id="pfadi-subscribe-message"></div>
			<form method="post" id="pfadi-subscribe-form">
				<p>
					<label>E-Mail Adresse:</label>
					<input type="email" name="pfadi_email" required>
				</p>
				<p>
					<label>Stufen wählen:</label><br>
					<?php foreach ( $units as $unit ) : ?>
						<label>
							<input type="checkbox" name="pfadi_units[]" value="<?php echo esc_attr( $unit->term_id ); ?>">
							<?php echo esc_html( $unit->name ); ?>
						</label><br>
					<?php endforeach; ?>
				</p>
				<p><em>Informationen der Abteilung sind automatisch inbegriffen.</em></p>
				<input type="hidden" name="pfadi_action" value="subscribe">
				<input type="submit" value="Abonnieren">
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	public function handle_subscription_actions() {
		if ( isset( $_POST['pfadi_action'] ) && 'subscribe' === $_POST['pfadi_action'] ) {
			$email = sanitize_email( $_POST['pfadi_email'] );
			$units = isset( $_POST['pfadi_units'] ) ? array_map( 'intval', $_POST['pfadi_units'] ) : array();
			
			if ( is_email( $email ) ) {
				global $wpdb;
				$table_name = $wpdb->prefix . 'pfadi_subscribers';
				$token = wp_generate_password( 32, false );
				
				// Check if email exists
				$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE email = %s", $email ) );
				
				if ( $exists ) {
					// Update existing
					$wpdb->update(
						$table_name,
						array(
							'subscribed_units' => json_encode( $units ),
							'token' => $token,
							'status' => 'pending', // Re-verify
						),
						array( 'email' => $email )
					);
				} else {
					// Insert new
					$wpdb->insert(
						$table_name,
						array(
							'email' => $email,
							'subscribed_units' => json_encode( $units ),
							'token' => $token,
							'status' => 'pending',
						)
					);
				}

				// Send confirmation email
				$confirm_link = add_query_arg( array(
					'pfadi_action' => 'confirm',
					'token' => $token,
					'email' => urlencode( $email ),
				), home_url() );

				$subject = get_option( 'pfadi_confirm_subject', 'Pfadi Abo Bestätigen' );
				$message = get_option( 'pfadi_confirm_message', 'Bitte bestätigen Sie Ihr Abo: {link}' );
				$message = str_replace( '{link}', $confirm_link, $message );

				wp_mail( $email, $subject, $message );
				
				echo '<div class="pfadi-message">Bitte prüfen Sie Ihre E-Mails zur Bestätigung.</div>';
			}
		}

		if ( isset( $_GET['pfadi_action'] ) && 'confirm' === $_GET['pfadi_action'] ) {
			$token = sanitize_text_field( $_GET['token'] );
			$email = sanitize_email( urldecode( $_GET['email'] ) );
			
			global $wpdb;
			$table_name = $wpdb->prefix . 'pfadi_subscribers';
			
			$subscriber = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE email = %s AND token = %s", $email, $token ) );
			
			if ( $subscriber ) {
				$wpdb->update(
					$table_name,
					array( 'status' => 'active' ),
					array( 'id' => $subscriber->id )
				);
				echo '<div class="pfadi-message">Abo erfolgreich aktiviert!</div>';
			} else {
				echo '<div class="pfadi-message error">Ungültiger Link.</div>';
			}
		}
	}

	public function handle_ajax_subscription() {
		check_ajax_referer( 'pfadi_subscribe_nonce', 'nonce' );

		$email = sanitize_email( $_POST['pfadi_email'] );
		$units = isset( $_POST['pfadi_units'] ) ? array_map( 'intval', $_POST['pfadi_units'] ) : array();

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => 'Ungültige E-Mail Adresse.' ) );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'pfadi_subscribers';
		$token = wp_generate_password( 32, false );
		
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE email = %s", $email ) );
		
		if ( $exists ) {
			$wpdb->update(
				$table_name,
				array(
					'subscribed_units' => json_encode( $units ),
					'token' => $token,
					'status' => 'pending',
				),
				array( 'email' => $email )
			);
		} else {
			$wpdb->insert(
				$table_name,
				array(
					'email' => $email,
					'subscribed_units' => json_encode( $units ),
					'token' => $token,
					'status' => 'pending',
				)
			);
		}

		$confirm_link = add_query_arg( array(
			'pfadi_action' => 'confirm',
			'token' => $token,
			'email' => urlencode( $email ),
		), home_url() );

		$subject = get_option( 'pfadi_confirm_subject', 'Pfadi Abo Bestätigen' );
		$message = get_option( 'pfadi_confirm_message', 'Bitte bestätigen Sie Ihr Abo: {link}' );
		$message = str_replace( '{link}', $confirm_link, $message );

		if ( wp_mail( $email, $subject, $message ) ) {
			wp_send_json_success( array( 'message' => 'Du hast eine Email zur Bestätigung deiner Emailadresse erhalten.' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Aktuell funktioniert es nicht, probiere es später nocheinmal oder informiere den Administrator: admin@alvier.ch.' ) );
		}
	}

	public function handle_ajax_load_activities() {
		// Nonce check optional for public read-only data, but good practice if we had one.
		// Since we didn't localize a specific nonce for this action (reusing pfadi_subscribe_nonce might be confusing), 
		// and it's public data, we'll skip strict nonce check or use the existing one if applicable.
		// For now, let's keep it open as it's just reading public posts.
		
		$unit_slug = isset( $_POST['unit'] ) ? sanitize_text_field( $_POST['unit'] ) : '';
		$view = isset( $_POST['view'] ) ? sanitize_text_field( $_POST['view'] ) : 'cards';

		$args = array(
			'post_type'      => 'activity',
			'posts_per_page' => -1,
			'meta_key'       => '_pfadi_end_time',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => '_pfadi_end_time',
					'value'   => current_time( 'Y-m-d\TH:i' ),
					'compare' => '>',
					'type'    => 'DATETIME',
				),
			),
		);

		if ( ! empty( $unit_slug ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'activity_unit',
					'field'    => 'slug',
					'terms'    => $unit_slug,
				),
			);
		}

		$query = new WP_Query( $args );

		ob_start();
		if ( $query->have_posts() ) :
			if ( 'table' === $view ) {
				$this->render_table_view( $query );
			} else {
				$this->render_card_view( $query );
			}
			wp_reset_postdata();
		else :
			echo '<p>Keine aktuellen Aktivitäten.</p>';
		endif;
		$content = ob_get_clean();

		wp_send_json_success( array( 'content' => $content ) );
	}
}
