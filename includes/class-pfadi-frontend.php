<?php

class Pfadi_Frontend {

	public function __construct() {
		add_shortcode( 'pfadi_board', array( $this, 'render_board' ) );
		add_shortcode( 'pfadi_subscribe', array( $this, 'render_subscribe' ) );
		add_shortcode( 'pfadi_news', array( $this, 'render_news' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'init', array( $this, 'handle_subscription_actions' ) );
		add_action( 'wp_ajax_pfadi_subscribe', array( $this, 'handle_ajax_subscription' ) );
		add_action( 'wp_ajax_nopriv_pfadi_subscribe', array( $this, 'handle_ajax_subscription' ) );
		add_action( 'wp_ajax_pfadi_load_activities', array( $this, 'handle_ajax_load_activities' ) );
		add_action( 'wp_ajax_nopriv_pfadi_load_activities', array( $this, 'handle_ajax_load_activities' ) );
	}

	public function enqueue_scripts() {
		global $post;

		if ( is_a( $post, 'WP_Post' ) && (
			has_shortcode( $post->post_content, 'pfadi_board' ) ||
			has_shortcode( $post->post_content, 'pfadi_subscribe' ) ||
			has_shortcode( $post->post_content, 'pfadi_news' )
		) ) {
			wp_enqueue_style( 'pfadi-style', PFADI_MANAGER_URL . 'assets/css/style.css', array(), '1.0.0' );
			wp_enqueue_style( 'pfadi-news-style', PFADI_MANAGER_URL . 'assets/css/pfadi-news.css', array(), '1.0.0' );
			wp_enqueue_script( 'pfadi-frontend-js', PFADI_MANAGER_URL . 'assets/js/pfadi-frontend.js', array(), '1.0.0', true );
			wp_enqueue_script( 'pfadi-news-js', PFADI_MANAGER_URL . 'assets/js/pfadi-news.js', array( 'jquery' ), '1.0.0', true );
			wp_localize_script(
				'pfadi-frontend-js',
				'pfadi_ajax',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'pfadi_subscribe_nonce' ),
				)
			);
		}
	}

	public function render_board( $atts ) {
		$atts = shortcode_atts(
			array(
				'view' => 'cards', // cards or table
			),
			$atts
		);

		// Filter dropdown
		$units = $this->get_sorted_units();

		$selected_unit = isset( $_GET['pfadi_unit'] ) ? sanitize_text_field( $_GET['pfadi_unit'] ) : '';

		ob_start();
		?>
		<div class="pfadi-board">
			<ul class="pfadi-tabs">
				<li><a href="#" data-unit="" class="<?php echo ( empty( $selected_unit ) || 'abteilung' === $selected_unit ) ? 'active' : ''; ?>">Abteilung</a></li>
				<?php foreach ( $units as $unit ) : ?>
					<?php
					if ( 'abteilung' === $unit->slug ) {
						continue; }
					?>
					<li>
						<a href="#" data-unit="<?php echo esc_attr( $unit->slug ); ?>" class="<?php echo $selected_unit === $unit->slug ? 'active' : ''; ?>">
							<?php echo esc_html( $unit->name ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>

			<div class="pfadi-activities-content" data-view="<?php echo esc_attr( $atts['view'] ); ?>">
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
							'value'   => current_time( 'Y-m-d H:i' ),
							'compare' => '>',
							'type'    => 'DATETIME',
						),
					),
				);

				if ( ! empty( $selected_unit ) && 'abteilung' !== $selected_unit ) {
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
					} elseif ( 'list' === $atts['view'] ) {
						$this->render_list_view( $query );
					} else {
						$this->render_card_view( $query );
					}
					wp_reset_postdata();
				else :
					echo '<p>' . __( 'Keine aktuellen Aktivitäten.', 'wp-pfadi-manager' ) . '</p>';
				endif;
				?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	private function render_card_view( $query ) {
		echo '<div class="pfadi-cards">';
		while ( $query->have_posts() ) :
			$query->the_post();
			$start    = get_post_meta( get_the_ID(), '_pfadi_start_time', true );
			$end      = get_post_meta( get_the_ID(), '_pfadi_end_time', true );
			$location = get_post_meta( get_the_ID(), '_pfadi_location', true );
			$bring    = get_post_meta( get_the_ID(), '_pfadi_bring', true );

			$start_date = date_i18n( 'l, d.m.Y H:i', strtotime( $start ) );
			$end_date   = date_i18n( 'H:i', strtotime( $end ) );

			$units     = get_the_terms( get_the_ID(), 'activity_unit' );
			$unit_tags = '';
			if ( $units && ! is_wp_error( $units ) ) {
				$unit_tags = '<div class="pfadi-tags">';
				foreach ( $units as $unit ) {
					$unit_tags .= '<span class="pfadi-tag">' . esc_html( $unit->name ) . '</span>';
				}
				$unit_tags .= '</div>';
			}
			?>
			<div class="pfadi-card">
				<h3><?php the_title(); ?></h3>
				<p><strong><?php _e( 'Wann:', 'wp-pfadi-manager' ); ?></strong> <?php echo esc_html( $start_date . ' - ' . $end_date ); ?></p>
				<p><strong><?php _e( 'Wo:', 'wp-pfadi-manager' ); ?></strong> <?php echo esc_html( $location ); ?></p>
				<p><strong><?php _e( 'Mitnehmen:', 'wp-pfadi-manager' ); ?></strong><br><?php echo nl2br( esc_html( $bring ) ); ?></p>
				<?php
				$greeting = get_post_meta( get_the_ID(), '_pfadi_greeting', true );
				$leaders  = get_post_meta( get_the_ID(), '_pfadi_leaders', true );

				if ( ! empty( $greeting ) || ! empty( $leaders ) ) :
					?>
					<p>
						<?php if ( ! empty( $greeting ) ) : ?>
							<strong><?php echo esc_html( $greeting ); ?></strong><br>
						<?php endif; ?>
						<?php echo esc_html( $leaders ); ?>
					</p>
				<?php endif; ?>
				<?php echo $unit_tags; ?>
			</div>
			<?php
		endwhile;
		echo '</div>';
	}

	private function render_table_view( $query ) {
		echo '<table class="pfadi-table">';
		echo '<thead><tr><th>' . __( 'Aktivität', 'wp-pfadi-manager' ) . '</th><th>' . __( 'Wann', 'wp-pfadi-manager' ) . '</th><th>' . __( 'Wo', 'wp-pfadi-manager' ) . '</th><th>' . __( 'Mitnehmen', 'wp-pfadi-manager' ) . '</th><th>' . __( 'Stufen', 'wp-pfadi-manager' ) . '</th></tr></thead>';
		echo '<tbody>';
		while ( $query->have_posts() ) :
			$query->the_post();
			$start    = get_post_meta( get_the_ID(), '_pfadi_start_time', true );
			$end      = get_post_meta( get_the_ID(), '_pfadi_end_time', true );
			$location = get_post_meta( get_the_ID(), '_pfadi_location', true );
			$bring    = get_post_meta( get_the_ID(), '_pfadi_bring', true );

			$start_date = date_i18n( 'l, d.m.Y H:i', strtotime( $start ) );
			$end_date   = date_i18n( 'H:i', strtotime( $end ) );

			$units     = get_the_terms( get_the_ID(), 'activity_unit' );
			$unit_tags = '';
			if ( $units && ! is_wp_error( $units ) ) {
				$unit_tags = '<div class="pfadi-tags">';
				foreach ( $units as $unit ) {
					$unit_tags .= '<span class="pfadi-tag">' . esc_html( $unit->name ) . '</span>';
				}
				$unit_tags .= '</div>';
			}
			?>
			<tr>
				<td><?php the_title(); ?></td>
				<td><?php echo esc_html( $start_date . ' - ' . $end_date ); ?></td>
				<td><?php echo esc_html( $location ); ?></td>
				<td><?php echo nl2br( esc_html( $bring ) ); ?></td>
				<td><?php echo $unit_tags; ?></td>
			</tr>
			<?php
		endwhile;
		echo '</tbody></table>';
	}

	private function render_list_view( $query ) {
		$posts = $query->posts;
		if ( empty( $posts ) ) {
			return;
		}

		echo '<div class="pfadi-list-view">';

		// Left Column: List
		echo '<div class="pfadi-list-sidebar">';
		foreach ( $posts as $index => $post ) {
			$start      = get_post_meta( $post->ID, '_pfadi_start_time', true );
			$start_date = date_i18n( 'd.m.Y', strtotime( $start ) );
			$title      = get_the_title( $post );

			// Get unit name
			$units     = get_the_terms( $post->ID, 'activity_unit' );
			$unit_name = '';
			if ( $units && ! is_wp_error( $units ) ) {
				$unit_name = $units[0]->name;
			}

			$active_class = ( 0 === $index ) ? 'active' : '';

			echo '<div class="pfadi-list-item ' . esc_attr( $active_class ) . '" data-id="' . esc_attr( $post->ID ) . '">';
			echo '<div class="pfadi-list-date">' . esc_html( $start_date . ' - ' . $title ) . '</div>';
			if ( $unit_name ) {
				echo '<div class="pfadi-list-unit">' . __( 'Stufe:', 'wp-pfadi-manager' ) . ' ' . esc_html( $unit_name ) . '</div>';
			}
			echo '</div>';
		}
		echo '</div>'; // .pfadi-list-sidebar

		// Right Column: Content
		echo '<div class="pfadi-list-content-area">';
		foreach ( $posts as $index => $post ) {
			$start    = get_post_meta( $post->ID, '_pfadi_start_time', true );
			$end      = get_post_meta( $post->ID, '_pfadi_end_time', true );
			$location = get_post_meta( $post->ID, '_pfadi_location', true );
			$bring    = get_post_meta( $post->ID, '_pfadi_bring', true );
			$greeting = get_post_meta( $post->ID, '_pfadi_greeting', true );
			$leaders  = get_post_meta( $post->ID, '_pfadi_leaders', true );

			$start_time = date_i18n( 'H:i', strtotime( $start ) );
			$end_time   = date_i18n( 'H:i', strtotime( $end ) );

			// Get unit name again for display
			$units     = get_the_terms( $post->ID, 'activity_unit' );
			$unit_name = '';
			if ( $units && ! is_wp_error( $units ) ) {
				$unit_name = $units[0]->name;
			}

			$active_class = ( 0 === $index ) ? 'active' : '';

			echo '<div class="pfadi-list-content ' . esc_attr( $active_class ) . '" data-id="' . esc_attr( $post->ID ) . '">';
			echo '<h3>' . get_the_title( $post ) . '</h3>';

			if ( $unit_name ) {
				echo '<p class="pfadi-detail-row"><strong>' . __( 'Stufe:', 'wp-pfadi-manager' ) . '</strong> ' . esc_html( $unit_name ) . '</p>';
			}

			echo '<p class="pfadi-detail-row"><strong>' . __( 'Besammlung:', 'wp-pfadi-manager' ) . '</strong> ' . esc_html( $start_time . ' ' . $location ) . '</p>';
			echo '<p class="pfadi-detail-row"><strong>' . __( 'Verabschiedung:', 'wp-pfadi-manager' ) . '</strong> ' . esc_html( $end_time . ' ' . $location ) . '</p>';

			echo '<p class="pfadi-detail-row"><strong>' . __( 'Mitnehmen:', 'wp-pfadi-manager' ) . '</strong></p>';
			echo '<div class="pfadi-bring-list">' . nl2br( esc_html( $bring ) ) . '</div>';

			if ( ! empty( $greeting ) || ! empty( $leaders ) ) {
				echo '<div class="pfadi-signature">';
				if ( ! empty( $greeting ) ) {
					echo '<p><strong>' . esc_html( $greeting ) . '</strong></p>';
				}
				if ( ! empty( $leaders ) ) {
					echo '<p>' . esc_html( $leaders ) . '</p>';
				}
				echo '</div>';
			}

			echo '</div>';
		}
		echo '</div>'; // .pfadi-list-content-area

		echo '</div>'; // .pfadi-list-view
	}

	public function render_news( $atts ) {
		$atts = shortcode_atts(
			array(
				'view'  => 'carousel', // carousel or banner
				'limit' => -1,
			),
			$atts
		);

		$args = array(
			'post_type'      => 'announcement',
			'posts_per_page' => $atts['limit'],
			'meta_key'       => '_pfadi_end_time',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => '_pfadi_start_time',
					'value'   => current_time( 'Y-m-d H:i' ),
					'compare' => '<=',
					'type'    => 'DATETIME',
				),
				array(
					'key'     => '_pfadi_end_time',
					'value'   => current_time( 'Y-m-d H:i' ),
					'compare' => '>',
					'type'    => 'DATETIME',
				),
			),
		);

		$query = new WP_Query( $args );

		ob_start();
		if ( $query->have_posts() ) {
			if ( 'banner' === $atts['view'] ) {
				$this->render_news_banner( $query );
			} else {
				$this->render_news_carousel( $query );
			}
		}
		wp_reset_postdata();
		return ob_get_clean();
	}

	private function render_news_banner( $query ) {
		// Only show the latest one
		$query->the_post();
		?>
		<div class="pfadi-news-banner">
			<div class="pfadi-news-content">
				<strong><?php the_title(); ?>:</strong> <?php echo get_the_excerpt(); ?>
				<a href="<?php the_permalink(); ?>" class="pfadi-news-link"><?php _e( 'Mehr lesen', 'wp-pfadi-manager' ); ?></a>
			</div>
		</div>
		<?php
	}

	private function render_news_carousel( $query ) {
		?>
		<div class="pfadi-news-carousel-container">
			<div class="pfadi-news-carousel">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					?>
					<div class="pfadi-news-item">
						<div class="pfadi-news-card">
							<h4><?php the_title(); ?></h4>
							<div class="pfadi-news-excerpt">
								<?php the_excerpt(); ?>
							</div>
							<a href="<?php the_permalink(); ?>" class="pfadi-news-read-more"><?php _e( 'Weiterlesen', 'wp-pfadi-manager' ); ?></a>
						</div>
					</div>
				<?php endwhile; ?>
			</div>
			<button class="pfadi-carousel-prev" aria-label="Previous">&lt;</button>
			<button class="pfadi-carousel-next" aria-label="Next">&gt;</button>
		</div>
		<?php
	}

	public function render_subscribe( $atts ) {
		$units = $this->get_sorted_units( true );

		ob_start();
		?>
		<div class="pfadi-subscribe">
			<div id="pfadi-subscribe-message"></div>
			<form method="post" id="pfadi-subscribe-form">
				<p>
				<p>
					<label>
						<?php _e( 'E-Mail Adresse:', 'wp-pfadi-manager' ); ?>
						<input type="email" name="pfadi_email" required>
					</label>
				</p>
				<p>
					<strong><?php _e( 'Stufen wählen:', 'wp-pfadi-manager' ); ?></strong><br>
					<?php foreach ( $units as $unit ) : ?>
						<label>
							<input type="checkbox" name="pfadi_units[]" value="<?php echo esc_attr( $unit->term_id ); ?>">
							<?php echo esc_html( $unit->name ); ?>
						</label><br>
					<?php endforeach; ?>
				</p>
				<p><em><?php _e( 'Informationen der Abteilung sind automatisch inbegriffen.', 'wp-pfadi-manager' ); ?></em></p>
				<input type="hidden" name="pfadi_action" value="subscribe">
				<input type="submit" value="<?php esc_attr_e( 'Abonnieren', 'wp-pfadi-manager' ); ?>">
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	private function get_sorted_units( $exclude_abteilung = false ) {
		$units = get_terms(
			array(
				'taxonomy'   => 'activity_unit',
				'hide_empty' => false,
			)
		);

		$order = array( 'abteilung', 'biber', 'woelfe', 'wolfe', 'pfadis', 'pios', 'rover' );

		usort(
			$units,
			function ( $a, $b ) use ( $order ) {
				$pos_a = array_search( $a->slug, $order );
				$pos_b = array_search( $b->slug, $order );

				if ( $pos_a === false ) {
					return 1;
				}
				if ( $pos_b === false ) {
					return -1;
				}

				return $pos_a - $pos_b;
			}
		);

		if ( $exclude_abteilung ) {
			$units = array_filter(
				$units,
				function ( $unit ) {
					return 'abteilung' !== $unit->slug;
				}
			);
		}

		return $units;
	}

	public function handle_subscription_actions() {
		if ( wp_doing_ajax() ) {
			return;
		}

		if ( isset( $_POST['pfadi_action'] ) && 'subscribe' === $_POST['pfadi_action'] ) {
			$email = sanitize_email( $_POST['pfadi_email'] );
			$units = isset( $_POST['pfadi_units'] ) ? array_map( 'intval', $_POST['pfadi_units'] ) : array();

			if ( is_email( $email ) ) {
				global $wpdb;
				$table_name = $wpdb->prefix . 'pfadi_subscribers';
				$token      = wp_generate_password( 32, false );

				// Check if email exists
				$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE email = %s", $email ) );

				if ( $exists ) {
					// Update existing
					$wpdb->update(
						$table_name,
						array(
							'subscribed_units' => json_encode( $units ),
							'token'            => $token,
							'status'           => 'pending', // Re-verify
						),
						array( 'email' => $email )
					);
				} else {
					// Insert new
					$wpdb->insert(
						$table_name,
						array(
							'email'            => $email,
							'subscribed_units' => json_encode( $units ),
							'token'            => $token,
							'status'           => 'pending',
						)
					);
				}

				// Send confirmation email
				$confirm_link = add_query_arg(
					array(
						'pfadi_action' => 'confirm',
						'token'        => $token,
						'email'        => urlencode( $email ),
					),
					home_url()
				);

				$subject = get_option( 'pfadi_confirm_subject', __( 'Pfadi Abo Bestätigen', 'wp-pfadi-manager' ) );
				$message = get_option( 'pfadi_confirm_message', __( 'Bitte bestätigen Sie Ihr Abo: {link}', 'wp-pfadi-manager' ) );
				$message = str_replace( '{link}', $confirm_link, $message );

				wp_mail( $email, $subject, $message );

				echo '<div class="pfadi-message">' . __( 'Bitte prüfen Sie Ihre E-Mails zur Bestätigung.', 'wp-pfadi-manager' ) . '</div>';
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
				echo '<div class="pfadi-message">' . __( 'Abo erfolgreich aktiviert!', 'wp-pfadi-manager' ) . '</div>';
			} else {
				echo '<div class="pfadi-message error">' . __( 'Ungültiger Link.', 'wp-pfadi-manager' ) . '</div>';
			}
		}
	}

	public function handle_ajax_subscription() {
		check_ajax_referer( 'pfadi_subscribe_nonce', 'nonce' );

		$email = sanitize_email( $_POST['pfadi_email'] );
		$units = isset( $_POST['pfadi_units'] ) ? array_map( 'intval', $_POST['pfadi_units'] ) : array();

		Pfadi_Logger::log( "Subscription attempt for email: $email with units: " . implode( ',', $units ) );

		if ( ! is_email( $email ) ) {
			Pfadi_Logger::log( "Invalid email: $email", 'error' );
			wp_send_json_error( array( 'message' => __( 'Ungültige E-Mail Adresse.', 'wp-pfadi-manager' ) ) );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'pfadi_subscribers';
		$token      = wp_generate_password( 32, false );

		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE email = %s", $email ) );

		if ( $exists ) {
			$wpdb->update(
				$table_name,
				array(
					'subscribed_units' => json_encode( $units ),
					'token'            => $token,
					'status'           => 'pending',
				),
				array( 'email' => $email )
			);
		} else {
			$wpdb->insert(
				$table_name,
				array(
					'email'            => $email,
					'subscribed_units' => json_encode( $units ),
					'token'            => $token,
					'status'           => 'pending',
				)
			);
		}

		$confirm_link = add_query_arg(
			array(
				'pfadi_action' => 'confirm',
				'token'        => $token,
				'email'        => urlencode( $email ),
			),
			home_url()
		);

		$subject = get_option( 'pfadi_confirm_subject', __( '[{site_title}] Pfadi Abo Bestätigen', 'wp-pfadi-manager' ) );
		$message = get_option( 'pfadi_confirm_message', __( 'Bitte bestätigen Sie Ihr Abo: {link}', 'wp-pfadi-manager' ) );

		$site_title   = get_bloginfo( 'name' );
		$placeholders = array(
			'{link}'       => $confirm_link,
			'{site_title}' => $site_title,
		);

		$subject = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $subject );
		$message = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $message );

		Pfadi_Logger::log( "Sending confirmation email to $email" );

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		// Removing custom From header to avoid spoofing rejection
		// $admin_email = get_option( 'admin_email' );
		// $headers[] = 'From: Pfadi Manager <' . $admin_email . '>';

		$sent = wp_mail( $email, $subject, $message, $headers );

		if ( $sent ) {
			Pfadi_Logger::log( "Confirmation email sent successfully to $email" );
			wp_send_json_success( array( 'message' => __( 'Du hast eine Email zur Bestätigung deiner Emailadresse erhalten.', 'wp-pfadi-manager' ) ) );
		} else {
			Pfadi_Logger::log( "Failed to send confirmation email to $email", 'error' );
			wp_send_json_error( array( 'message' => __( 'Das hat leider nicht geklappt. Bitte versuche es später noch einmal oder wende dich an admin@alvier.ch.', 'wp-pfadi-manager' ) ) );
		}
	}

	public function handle_ajax_load_activities() {
		check_ajax_referer( 'pfadi_subscribe_nonce', 'nonce' );

		$unit_slug = isset( $_POST['unit'] ) ? sanitize_text_field( $_POST['unit'] ) : '';
		$view      = isset( $_POST['view'] ) ? sanitize_text_field( $_POST['view'] ) : 'cards';

		$args = array(
			'post_type'      => 'activity',
			'posts_per_page' => -1,
			'meta_key'       => '_pfadi_end_time',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => '_pfadi_end_time',
					'value'   => current_time( 'Y-m-d H:i' ),
					'compare' => '>',
					'type'    => 'DATETIME',
				),
			),
		);

		if ( ! empty( $unit_slug ) && 'abteilung' !== $unit_slug ) {
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
			} elseif ( 'list' === $view ) {
				$this->render_list_view( $query );
			} else {
				$this->render_card_view( $query );
			}
			wp_reset_postdata();
		else :
			echo '<p>' . __( 'Keine aktuellen Aktivitäten.', 'wp-pfadi-manager' ) . '</p>';
		endif;
		$content = ob_get_clean();

		wp_send_json_success( array( 'content' => $content ) );
	}
}
