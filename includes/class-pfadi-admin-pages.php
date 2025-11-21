<?php

class Pfadi_Admin_Pages {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menus' ) );
	}

	public function add_admin_menus() {
		add_submenu_page(
			'edit.php?post_type=activity',
			'Abonnenten',
			'Abonnenten',
			'manage_options',
			'pfadi_subscribers',
			array( $this, 'render_subscribers_page' )
		);

		add_submenu_page(
			'edit.php?post_type=activity',
			'Hilfe & Info',
			'Hilfe & Info',
			'manage_options',
			'pfadi_info',
			array( $this, 'render_info_page' )
		);
	}

	public function render_subscribers_page() {
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-subscribers-list-table.php';

		$this->handle_manual_subscription();

		$list_table = new Pfadi_Subscribers_List_Table();
		$list_table->process_bulk_action();
		$list_table->prepare_items();
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">Abonnenten</h1>
			
			<div class="card" style="max-width: 100%; margin-top: 20px;">
				<h2>Neuen Abonnenten hinzufügen</h2>
				<form method="post">
					<table class="form-table">
						<tr>
							<th scope="row"><label for="new_subscriber_email">E-Mail</label></th>
							<td><input type="email" name="new_subscriber_email" id="new_subscriber_email" class="regular-text" required></td>
						</tr>
						<tr>
							<th scope="row">Stufen</th>
							<td>
								<?php
								$units = get_terms( array(
									'taxonomy' => 'activity_unit',
									'hide_empty' => false,
								) );
								foreach ( $units as $unit ) {
									echo '<label style="margin-right: 10px;"><input type="checkbox" name="new_subscriber_units[]" value="' . esc_attr( $unit->term_id ) . '"> ' . esc_html( $unit->name ) . '</label>';
								}
								?>
							</td>
						</tr>
					</table>
					<?php wp_nonce_field( 'add_subscriber', 'pfadi_add_subscriber_nonce' ); ?>
					<p class="submit"><input type="submit" name="add_subscriber" id="submit" class="button button-primary" value="Abonnent hinzufügen"></p>
				</form>
			</div>

			<form method="post">
				<?php
				$list_table->display();
				?>
			</form>
		</div>
		<?php
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
					echo '<div class="notice notice-success is-dismissible"><p>Abonnent aktualisiert.</p></div>';
				} else {
					$wpdb->insert(
						$table_name,
						array(
							'email' => $email,
							'subscribed_units' => json_encode( $units ),
							'token' => wp_generate_password( 32, false ),
							'status' => 'active',
						)
					);
					echo '<div class="notice notice-success is-dismissible"><p>Abonnent hinzugefügt.</p></div>';
				}
			} else {
				echo '<div class="notice notice-error is-dismissible"><p>Ungültige E-Mail Adresse.</p></div>';
			}
		}
	}

	public function render_info_page() {
		?>
		<div class="wrap">
			<h1>Hilfe & Informationen</h1>
			
			<div class="card">
				<h2>Shortcodes</h2>
				<p>Folgende Shortcodes stehen zur Verfügung:</p>
				
				<h3>1. Aktivitäten-Board</h3>
				<code>[pfadi_board]</code>
				<p>Zeigt die aktuellen Aktivitäten an.</p>
				<p><strong>Parameter:</strong></p>
				<ul>
					<li><code>view="cards"</code> (Standard) - Zeigt Kacheln an.</li>
					<li><code>view="table"</code> - Zeigt eine Tabelle an.</li>
				</ul>
				<p><em>Beispiel:</em> <code>[pfadi_board view="table"]</code></p>

				<h3>2. Abo-Formular</h3>
				<code>[pfadi_subscribe]</code>
				<p>Zeigt das Formular zum Abonnieren des Newsletters an.</p>
			</div>

			<div class="card">
				<h2>Technische Informationen</h2>
				<p><strong>Plugin Version:</strong> <?php echo PFADI_MANAGER_VERSION; ?></p>
				<p><strong>Datenbank-Tabelle:</strong> <?php global $wpdb; echo $wpdb->prefix . 'pfadi_subscribers'; ?></p>
				<p><strong>Cronjobs:</strong></p>
				<ul>
					<li>Täglicher Cleanup (alte Aktivitäten archivieren)</li>
				</ul>
			</div>

			<div class="card">
				<h2>Wartung</h2>
				<p>Die Aktivitäten werden automatisch archiviert, sobald das Enddatum erreicht ist.</p>
			</div>
		</div>
		<style>
			.card {
				background: #fff;
				border: 1px solid #ccd0d4;
				padding: 20px;
				margin-bottom: 20px;
				max-width: 800px;
				box-shadow: 0 1px 1px rgba(0,0,0,.04);
			}
			.card h2 {
				margin-top: 0;
			}
			.card code {
				background: #f0f0f1;
				padding: 3px 5px;
			}
		</style>
		<?php
	}
}
