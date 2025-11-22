<?php

class Pfadi_Admin_Pages {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menus' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function register_settings() {
		// Settings are handled in class-pfadi-settings.php
	}

	public function add_admin_menus() {
		add_submenu_page(
			'edit.php?post_type=activity',
			__( 'Abonnenten', 'wp-pfadi-manager' ),
			__( 'Abonnenten', 'wp-pfadi-manager' ),
			'manage_options',
			'pfadi_subscribers',
			array( $this, 'render_subscribers_page' )
		);


	}



	public function render_subscribers_page() {
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-subscribers-list-table.php';
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-mailer.php';

		$this->handle_manual_subscription();
		$this->handle_edit_subscription();

		$action = isset( $_GET['action'] ) ? $_GET['action'] : '';
		$subscriber_id = isset( $_GET['subscriber'] ) ? intval( $_GET['subscriber'] ) : 0;

		if ( 'edit' === $action && $subscriber_id > 0 ) {
			$this->render_edit_form( $subscriber_id );
			return;
		}

		$list_table = new Pfadi_Subscribers_List_Table();
		$list_table->process_bulk_action();
		$list_table->prepare_items();
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php _e( 'Abonnenten', 'wp-pfadi-manager' ); ?></h1>
			
			<div class="card" style="max-width: 100%; margin-top: 20px;">
				<h2><?php _e( 'Neuen Abonnenten hinzufügen', 'wp-pfadi-manager' ); ?></h2>
				<form method="post">
					<table class="form-table">
						<tr>
							<th scope="row"><label for="new_subscriber_email"><?php _e( 'E-Mail', 'wp-pfadi-manager' ); ?></label></th>
							<td><input type="email" name="new_subscriber_email" id="new_subscriber_email" class="regular-text" required></td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Stufen', 'wp-pfadi-manager' ); ?></th>
							<td>
								<?php
								$units = get_terms( array(
									'taxonomy' => 'activity_unit',
									'hide_empty' => false,
								) );
								
								$order = array( 'abteilung', 'biber', 'woelfe', 'wolfe', 'pfadis', 'pios', 'rover' );
								usort( $units, function( $a, $b ) use ( $order ) {
									$pos_a = array_search( $a->slug, $order );
									$pos_b = array_search( $b->slug, $order );
									if ( $pos_a === false ) return 1;
									if ( $pos_b === false ) return -1;
									return $pos_a - $pos_b;
								} );

								foreach ( $units as $unit ) {
									if ( 'abteilung' === $unit->slug ) continue;
									echo '<label style="margin-right: 10px;"><input type="checkbox" name="new_subscriber_units[]" value="' . esc_attr( $unit->term_id ) . '"> ' . esc_html( $unit->name ) . '</label>';
								}
								?>
							</td>
						</tr>
					</table>
					<?php wp_nonce_field( 'add_subscriber', 'pfadi_add_subscriber_nonce' ); ?>
					<p class="submit"><input type="submit" name="add_subscriber" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Abonnent hinzufügen', 'wp-pfadi-manager' ); ?>"></p>
				</form>
			</div>

			<form method="post">
				<?php wp_nonce_field( 'bulk-subscribers' ); ?>
				<?php
				$list_table->display();
				?>
			</form>
		</div>
		<?php
	}

	private function render_edit_form( $subscriber_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'pfadi_subscribers';
		$subscriber = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $subscriber_id ) );

		if ( ! $subscriber ) {
			echo '<div class="notice notice-error"><p>' . __( 'Abonnent nicht gefunden.', 'wp-pfadi-manager' ) . '</p></div>';
			return;
		}

		$subscribed_units = json_decode( $subscriber->subscribed_units, true );
		if ( ! is_array( $subscribed_units ) ) {
			$subscribed_units = array();
		}
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php _e( 'Abonnent bearbeiten', 'wp-pfadi-manager' ); ?></h1>
			<a href="?post_type=activity&page=pfadi_subscribers" class="page-title-action"><?php _e( 'Zurück zur Übersicht', 'wp-pfadi-manager' ); ?></a>
			
			<div class="card" style="max-width: 100%; margin-top: 20px;">
				<form method="post">
					<table class="form-table">
						<tr>
							<th scope="row"><?php _e( 'E-Mail', 'wp-pfadi-manager' ); ?></th>
							<td><input type="email" value="<?php echo esc_attr( $subscriber->email ); ?>" class="regular-text" disabled></td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Stufen', 'wp-pfadi-manager' ); ?></th>
							<td>
								<?php
								$units = get_terms( array(
									'taxonomy' => 'activity_unit',
									'hide_empty' => false,
								) );

								$order = array( 'abteilung', 'biber', 'woelfe', 'wolfe', 'pfadis', 'pios', 'rover' );
								usort( $units, function( $a, $b ) use ( $order ) {
									$pos_a = array_search( $a->slug, $order );
									$pos_b = array_search( $b->slug, $order );
									if ( $pos_a === false ) return 1;
									if ( $pos_b === false ) return -1;
									return $pos_a - $pos_b;
								} );

								foreach ( $units as $unit ) {
									if ( 'abteilung' === $unit->slug ) continue;
									$checked = in_array( $unit->term_id, $subscribed_units ) ? 'checked' : '';
									echo '<label style="margin-right: 10px;"><input type="checkbox" name="edit_subscriber_units[]" value="' . esc_attr( $unit->term_id ) . '" ' . $checked . '> ' . esc_html( $unit->name ) . '</label>';
								}
								?>
							</td>
						</tr>
					</table>
					<input type="hidden" name="subscriber_id" value="<?php echo esc_attr( $subscriber->id ); ?>">
					<?php wp_nonce_field( 'edit_subscriber', 'pfadi_edit_subscriber_nonce' ); ?>
					<p class="submit"><input type="submit" name="edit_subscriber" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Speichern', 'wp-pfadi-manager' ); ?>"></p>
				</form>
			</div>
		</div>
		<?php
	}

	private function handle_edit_subscription() {
		if ( isset( $_POST['edit_subscriber'] ) && check_admin_referer( 'edit_subscriber', 'pfadi_edit_subscriber_nonce' ) ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'pfadi_subscribers';
			
			$subscriber_id = intval( $_POST['subscriber_id'] );
			$units = isset( $_POST['edit_subscriber_units'] ) ? array_map( 'intval', $_POST['edit_subscriber_units'] ) : array();

			$wpdb->update(
				$table_name,
				array( 'subscribed_units' => json_encode( $units ) ),
				array( 'id' => $subscriber_id )
			);

			echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Abonnent aktualisiert.', 'wp-pfadi-manager' ) . '</p></div>';
		}
	}

	private function handle_manual_subscription() {
		if ( isset( $_POST['add_subscriber'] ) && check_admin_referer( 'add_subscriber', 'pfadi_add_subscriber_nonce' ) ) {
			$email = sanitize_email( $_POST['new_subscriber_email'] );
			$units = isset( $_POST['new_subscriber_units'] ) ? array_map( 'intval', $_POST['new_subscriber_units'] ) : array();

			if ( is_email( $email ) ) {
				global $wpdb;
				$table_name = $wpdb->prefix . 'pfadi_subscribers';
				
				$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE email = %s", $email ) );

				if ( $exists ) {
					$wpdb->update(
						$table_name,
						array(
							'subscribed_units' => json_encode( $units ),
							'status' => 'active',
						),
						array( 'email' => $email )
					);
					echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Abonnent aktualisiert.', 'wp-pfadi-manager' ) . '</p></div>';
				} else {
					$token = wp_generate_password( 32, false );
					$wpdb->insert(
						$table_name,
						array(
							'email' => $email,
							'subscribed_units' => json_encode( $units ),
							'token' => $token,
							'status' => 'pending',
						)
					);

					// Send confirmation email
					$confirm_link = add_query_arg( array(
						'pfadi_action' => 'confirm',
						'token' => $token,
						'email' => urlencode( $email ),
					), home_url() );

					$subject = get_option( 'pfadi_confirm_subject', __( '[{site_title}] Pfadi Abo Bestätigen', 'wp-pfadi-manager' ) );
					$message = get_option( 'pfadi_confirm_message', __( 'Bitte bestätigen Sie Ihr Abo: {link}', 'wp-pfadi-manager' ) );
					
					$site_title = get_bloginfo( 'name' );
					$placeholders = array(
						'{link}'       => $confirm_link,
						'{site_title}' => $site_title,
					);
					
					$subject = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $subject );
					$message = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $message );
					
					if ( wp_mail( $email, $subject, $message ) ) {
						echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Abonnent hinzugefügt. Bestätigungs-E-Mail wurde versendet.', 'wp-pfadi-manager' ) . '</p></div>';
					} else {
						echo '<div class="notice notice-error is-dismissible"><p>' . __( 'Abonnent hinzugefügt, aber die Bestätigungs-E-Mail konnte nicht gesendet werden. Bitte prüfen Sie Ihre E-Mail-Einstellungen.', 'wp-pfadi-manager' ) . '</p></div>';
					}
				}
			} else {
				echo '<div class="notice notice-error is-dismissible"><p>' . __( 'Ungültige E-Mail Adresse.', 'wp-pfadi-manager' ) . '</p></div>';
			}
		}
	}
}
