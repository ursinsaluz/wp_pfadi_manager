<?php
/**
 * Settings page functionality.
 *
 * @package PfadiManager
 */

/**
 * Handles the registration and rendering of settings.
 */
class Pfadi_Settings {

	/**
	 * Initialize the class.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'update_option_pfadi_cpt_slug', array( $this, 'flush_rewrite_rules_flag' ), 10, 2 );
		add_action( 'admin_post_pfadi_download_log', array( $this, 'handle_download_log' ) );
		add_action( 'admin_post_pfadi_clear_log', array( $this, 'handle_clear_log' ) );
	}

	/**
	 * Flag rewrite rules for flushing when CPT slug changes.
	 *
	 * @param mixed $old_value The old option value.
	 * @param mixed $new_value The new option value.
	 */
	public function flush_rewrite_rules_flag( $old_value, $new_value ) {
		if ( $old_value !== $new_value ) {
			update_option( 'pfadi_flush_rewrite_rules', true );
		}
	}

	/**
	 * Add the settings menu page.
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=activity',
			'Pfadi Konfiguration',
			'Konfiguration',
			'manage_options',
			'pfadi_settings',
			array( $this, 'settings_page_html' )
		);
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings() {
		// General Settings.
		register_setting( 'pfadi_settings_group', 'pfadi_cpt_slug' );

		add_settings_section(
			'pfadi_section_general',
			'Allgemeine Einstellungen',
			null,
			'pfadi_settings_general'
		);

		add_settings_field(
			'pfadi_cpt_slug',
			'Aktivitäten Slug (URL)',
			array( $this, 'cpt_slug_field_html' ),
			'pfadi_settings_general',
			'pfadi_section_general'
		);

		add_settings_field(
			'pfadi_announcement_slug',
			'Mitteilungen Slug (URL)',
			array( $this, 'announcement_slug_field_html' ),
			'pfadi_settings_general',
			'pfadi_section_general'
		);

		// Email Settings.
		register_setting( 'pfadi_settings_group', 'pfadi_mail_subject' );
		register_setting( 'pfadi_settings_group', 'pfadi_confirm_subject' );
		register_setting( 'pfadi_settings_group', 'pfadi_confirm_message' );
		register_setting( 'pfadi_settings_group', 'pfadi_mail_mode' );
		register_setting( 'pfadi_settings_group', 'pfadi_mail_mode' );
		register_setting( 'pfadi_settings_group', 'pfadi_mail_time' );
		register_setting( 'pfadi_settings_group', 'pfadi_mail_template_activity' );
		register_setting( 'pfadi_settings_group', 'pfadi_mail_template_announcement' );

		add_settings_section(
			'pfadi_section_email',
			'E-Mail Texte',
			null,
			'pfadi_settings_email'
		);

		add_settings_field(
			'pfadi_mail_subject',
			'Betreff für neue Aktivitäten',
			array( $this, 'text_field_html' ),
			'pfadi_settings_email',
			'pfadi_section_email',
			array(
				'name'        => 'pfadi_mail_subject',
				'default'     => '[{site_title}] Neue Pfadi-Aktivität: {title}',
				'description' => 'Verfügbare Platzhalter: {title} (Titel der Aktivität), {unit} (Stufe), {site_title} (Titel der Website), {date} (Datum dd.mm.yy).',
			)
		);

		add_settings_field(
			'pfadi_confirm_subject',
			'Betreff für Bestätigungsmail',
			array( $this, 'text_field_html' ),
			'pfadi_settings_email',
			'pfadi_section_email',
			array(
				'name'        => 'pfadi_confirm_subject',
				'default'     => '[{site_title}] Pfadi Abo Bestätigen',
				'description' => 'Verfügbare Platzhalter: {site_title} (Titel der Website).',
			)
		);

		add_settings_field(
			'pfadi_confirm_message',
			'Nachricht für Bestätigungsmail',
			array( $this, 'textarea_field_html' ),
			'pfadi_settings_email',
			'pfadi_section_email',
			array(
				'name'    => 'pfadi_confirm_message',
				'default' => 'Bitte bestätigen Sie Ihr Abo: {link}',
			)
		);

		$activity_template_default = '<!DOCTYPE html>
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
		<h1>{title}</h1>
		
		<div class="meta">
			<p><span class="label">Wann:</span> {date_str}</p>
			<p><span class="label">Wo:</span> {location}</p>
		</div>

		<p><span class="label">Mitnehmen:</span><br>
		{bring}</p>

		<p><span class="label">Besonderes:</span><br>
		{special}</p>

		<hr>

		<p>{greeting}</p>
		<p><em>{leaders}</em></p>
	</div>
</body>
</html>';

		$announcement_template_default = '<!DOCTYPE html>
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
		<h1>{title}</h1>
		
		<div class="meta">
			<p><span class="label">Wann:</span> {date_str}</p>
		</div>

		<div class="content">
			{content}
		</div>
	</div>
</body>
</html>';

		add_settings_field(
			'pfadi_mail_template_activity',
			'E-Mail Template für Aktivitäten',
			array( $this, 'textarea_field_html' ),
			'pfadi_settings_email',
			'pfadi_section_email',
			array(
				'name'        => 'pfadi_mail_template_activity',
				'default'     => $activity_template_default,
				'description' => 'HTML Template für Aktivitäten-Mails. Lassen Sie es leer, um das Standard-Template zu verwenden. Platzhalter: {content} (Inhalt), {title}, {unit}, {site_title}, {date_str}, {location}, {bring}, {special}, {greeting}, {leaders}.',
			)
		);

		add_settings_field(
			'pfadi_mail_template_announcement',
			'E-Mail Template für Mitteilungen',
			array( $this, 'textarea_field_html' ),
			'pfadi_settings_email',
			'pfadi_section_email',
			array(
				'name'        => 'pfadi_mail_template_announcement',
				'default'     => $announcement_template_default,
				'description' => 'HTML Template für Mitteilungs-Mails. Lassen Sie es leer, um das Standard-Template zu verwenden. Platzhalter: {content} (Inhalt), {title}, {unit}, {site_title}, {date_str}.',
			)
		);

		add_settings_field(
			'pfadi_mail_mode',
			'E-Mail Versandmodus',
			array( $this, 'mail_mode_field_html' ),
			'pfadi_settings_email',
			'pfadi_section_email'
		);

		add_settings_field(
			'pfadi_mail_time',
			'Versandzeit (wenn geplant)',
			array( $this, 'mail_time_field_html' ),
			'pfadi_settings_email',
			'pfadi_section_email'
		);

		$units             = array( 'Biber', 'Wölfe', 'Pfadis', 'Pios', 'Rover', 'Abteilung' );
		$greeting_defaults = array(
			'biber'     => 'Mit freud debi',
			'wolfe'     => 'Üsers Bescht',
			'woelfe'    => 'Üsers Bescht',
			'pfadis'    => 'Allzeit bereit',
			'pios'      => 'Zäma Wiiter',
			'rover'     => 'Bewusst handlä',
			'abteilung' => 'Allzeit bereit',
		);

		foreach ( $units as $unit ) {
			$unit_slug = sanitize_title( $unit );

			register_setting( 'pfadi_settings_group', "pfadi_greeting_$unit_slug" );
			register_setting( 'pfadi_settings_group', "pfadi_leaders_$unit_slug" );

			add_settings_section(
				"pfadi_section_$unit_slug",
				"Einstellungen für $unit",
				null,
				'pfadi_settings_units'
			);

			$default_greeting = isset( $greeting_defaults[ $unit_slug ] ) ? $greeting_defaults[ $unit_slug ] : 'Allzeit bereit';

			add_settings_field(
				"pfadi_greeting_$unit_slug",
				'Standard-Gruss',
				array( $this, 'text_field_html' ),
				'pfadi_settings_units',
				"pfadi_section_$unit_slug",
				array(
					'name'    => "pfadi_greeting_$unit_slug",
					'default' => $default_greeting,
				)
			);

			add_settings_field(
				"pfadi_leaders_$unit_slug",
				'Standard-Leitung',
				array( $this, 'text_field_html' ),
				'pfadi_settings_units',
				"pfadi_section_$unit_slug",
				array(
					'name'    => "pfadi_leaders_$unit_slug",
					'default' => 'Die Leiter',
				)
			);
		}
	}

	/**
	 * Render the greeting field.
	 *
	 * @param array $args Field arguments.
	 */
	public function greeting_field_html( $args ) {
		$unit_slug = $args['unit_slug'];
		$value     = get_option( "pfadi_greeting_$unit_slug" );
		?>
		<input type="text" name="pfadi_greeting_<?php echo esc_attr( $unit_slug ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text">
		<?php
	}

	/**
	 * Render the leaders field.
	 *
	 * @param array $args Field arguments.
	 */
	public function leaders_field_html( $args ) {
		$unit_slug = $args['unit_slug'];
		$value     = get_option( "pfadi_leaders_$unit_slug" );
		?>
		<input type="text" name="pfadi_leaders_<?php echo esc_attr( $unit_slug ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text">
		<?php
	}

	/**
	 * Render the CPT slug field.
	 */
	public function cpt_slug_field_html() {
		$slug = get_option( 'pfadi_cpt_slug', 'activity' );
		?>
		<input type="text" name="pfadi_cpt_slug" value="<?php echo esc_attr( $slug ); ?>">
		<p class="description">Standard: activity. Ändern Sie dies nur, wenn Sie wissen, was Sie tun. Permalinks werden automatisch aktualisiert.</p>
		<?php
	}

	/**
	 * Render the announcement slug field.
	 */
	public function announcement_slug_field_html() {
		$slug = get_option( 'pfadi_announcement_slug', 'mitteilung' );
		?>
		<input type="text" name="pfadi_announcement_slug" value="<?php echo esc_attr( $slug ); ?>">
		<p class="description">Standard: mitteilung. Ändern Sie dies nur, wenn Sie wissen, was Sie tun. Permalinks werden automatisch aktualisiert.</p>
		<?php
	}

	/**
	 * Render the mail mode field.
	 */
	public function mail_mode_field_html() {
		$mode = get_option( 'pfadi_mail_mode', 'scheduled' );
		?>
		<select name="pfadi_mail_mode">
			<option value="scheduled" <?php selected( $mode, 'scheduled' ); ?>>Geplant (Täglich um X Uhr)</option>
			<option value="immediate" <?php selected( $mode, 'immediate' ); ?>>Sofort nach Veröffentlichung</option>
		</select>
		<?php
	}

	/**
	 * Render the mail time field.
	 */
	public function mail_time_field_html() {
		$time = get_option( 'pfadi_mail_time' );
		if ( empty( $time ) ) {
			$time = '21:00';
		}
		?>
		<input type="time" name="pfadi_mail_time" value="<?php echo esc_attr( $time ); ?>">
		<?php
	}

	/**
	 * Render a text field.
	 *
	 * @param array $args Field arguments.
	 */
	public function text_field_html( $args ) {
		$name        = $args['name'];
		$default     = isset( $args['default'] ) ? $args['default'] : '';
		$description = isset( $args['description'] ) ? $args['description'] : '';

		$value = get_option( $name );
		if ( empty( $value ) ) {
			$value = $default;
		}
		?>
		<input type="text" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" class="large-text">
		<?php if ( $default ) : ?>
			<?php /* translators: %s: Default value */ ?>
			<p class="description"><?php printf( esc_html__( 'Standard: %s', 'wp-pfadi-manager' ), esc_html( $default ) ); ?></p>
		<?php endif; ?>
		<?php if ( $description ) : ?>
			<p class="description"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render a textarea field.
	 *
	 * @param array $args Field arguments.
	 */
	public function textarea_field_html( $args ) {
		$name        = $args['name'];
		$default     = isset( $args['default'] ) ? $args['default'] : '';
		$description = isset( $args['description'] ) ? $args['description'] : '';

		$value = get_option( $name );
		if ( empty( $value ) ) {
			$value = $default;
		}
		?>
		<textarea name="<?php echo esc_attr( $name ); ?>" rows="10" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
		<?php if ( $description ) : ?>
			<p class="description"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render the settings page.
	 */
	public function settings_page_html() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'info';
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
						<nav class="nav-tab-wrapper">
				<a href="?post_type=activity&page=pfadi_settings&tab=info" class="nav-tab <?php echo 'info' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Hilfe & Info', 'wp-pfadi-manager' ); ?></a>
				<a href="?post_type=activity&page=pfadi_settings&tab=general" class="nav-tab <?php echo 'general' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Allgemein', 'wp-pfadi-manager' ); ?></a>
				<a href="?post_type=activity&page=pfadi_settings&tab=email" class="nav-tab <?php echo 'email' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'E-Mail', 'wp-pfadi-manager' ); ?></a>
				<a href="?post_type=activity&page=pfadi_settings&tab=units" class="nav-tab <?php echo 'units' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Stufen-Einstellungen', 'wp-pfadi-manager' ); ?></a>
				<a href="?post_type=activity&page=pfadi_settings&tab=logs" class="nav-tab <?php echo 'logs' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Logs', 'wp-pfadi-manager' ); ?></a>
			</nav>

			<div class="tab-content">
				<?php if ( 'info' === $active_tab ) : ?>
					<div class="card" style="max-width: 800px; margin-top: 20px;">
						<h2><?php esc_html_e( 'Shortcodes', 'wp-pfadi-manager' ); ?></h2>
						<p><?php esc_html_e( 'Folgende Shortcodes stehen zur Verfügung:', 'wp-pfadi-manager' ); ?></p>
						
						<h3>1. <?php esc_html_e( 'Aktivitäten-Board', 'wp-pfadi-manager' ); ?></h3>
						<code>[pfadi_board]</code>
						<p><?php esc_html_e( 'Zeigt die aktuellen Aktivitäten an.', 'wp-pfadi-manager' ); ?></p>
						<p><strong><?php esc_html_e( 'Parameter:', 'wp-pfadi-manager' ); ?></strong></p>
						<ul>
							<li><code>view="cards"</code> (<?php esc_html_e( 'Standard', 'wp-pfadi-manager' ); ?>) - <?php esc_html_e( 'Zeigt Kacheln an.', 'wp-pfadi-manager' ); ?></li>
							<li><code>view="table"</code> - <?php esc_html_e( 'Zeigt eine Tabelle an.', 'wp-pfadi-manager' ); ?></li>
							<li><code>view="list"</code> - <?php esc_html_e( 'Zeigt eine Liste mit seitlichen Tabs an.', 'wp-pfadi-manager' ); ?></li>
							<li><code>unit="slug"</code> (<?php esc_html_e( 'Optional', 'wp-pfadi-manager' ); ?>) - <?php esc_html_e( 'Filtert nach einer bestimmten Stufe (z.B. biber, wolfe, pfadis).', 'wp-pfadi-manager' ); ?></li>
						</ul>
						<p><em><?php esc_html_e( 'Beispiel:', 'wp-pfadi-manager' ); ?></em> <code>[pfadi_board view="list"]</code></p>

						<h3>2. <?php esc_html_e( 'Abo-Formular', 'wp-pfadi-manager' ); ?></h3>
						<code>[pfadi_subscribe]</code>
						<p><?php esc_html_e( 'Zeigt das Formular zum Abonnieren des Newsletters an.', 'wp-pfadi-manager' ); ?></p>

						<h3>3. <?php esc_html_e( 'Mitteilungen', 'wp-pfadi-manager' ); ?></h3>
						<code>[pfadi_news]</code>
						<p><?php esc_html_e( 'Zeigt aktuelle Mitteilungen an.', 'wp-pfadi-manager' ); ?></p>
						<p><strong><?php esc_html_e( 'Parameter:', 'wp-pfadi-manager' ); ?></strong></p>
						<ul>
							<li><code>view="carousel"</code> (<?php esc_html_e( 'Standard', 'wp-pfadi-manager' ); ?>) - <?php esc_html_e( 'Zeigt ein Karussell aller aktuellen Mitteilungen.', 'wp-pfadi-manager' ); ?></li>
							<li><code>view="banner"</code> - <?php esc_html_e( 'Zeigt die neuste Mitteilung als Banner an.', 'wp-pfadi-manager' ); ?></li>
							<li><code>limit="-1"</code> (<?php esc_html_e( 'Optional', 'wp-pfadi-manager' ); ?>) - <?php esc_html_e( 'Anzahl der anzuzeigenden Mitteilungen (Standard: alle).', 'wp-pfadi-manager' ); ?></li>
						</ul>
						<p><em><?php esc_html_e( 'Beispiel:', 'wp-pfadi-manager' ); ?></em> <code>[pfadi_news view="banner"]</code></p>
					</div>

					<div class="card" style="max-width: 800px; margin-top: 20px;">
						<h2><?php esc_html_e( 'Technische Informationen', 'wp-pfadi-manager' ); ?></h2>
						<p><strong><?php esc_html_e( 'Plugin Version:', 'wp-pfadi-manager' ); ?></strong> <?php echo esc_html( PFADI_MANAGER_VERSION ); ?></p>
						<p><strong><?php esc_html_e( 'Datenbank-Tabelle:', 'wp-pfadi-manager' ); ?></strong> 
						<?php
						global $wpdb;
						echo esc_html( $wpdb->prefix . 'pfadi_subscribers' );
						?>
						</p>
						<p><strong><?php esc_html_e( 'Cronjobs:', 'wp-pfadi-manager' ); ?></strong></p>
						<ul>
							<li><?php esc_html_e( 'Täglicher Cleanup (alte Aktivitäten archivieren)', 'wp-pfadi-manager' ); ?></li>
						</ul>
					</div>
					</div>
				<?php elseif ( 'logs' === $active_tab ) : ?>
					<div class="card" style="max-width: 800px; margin-top: 20px;">
						<h2><?php esc_html_e( 'System Logs', 'wp-pfadi-manager' ); ?></h2>
						<p><?php esc_html_e( 'Hier sehen Sie die letzten 100 Log-Einträge des Plugins.', 'wp-pfadi-manager' ); ?></p>
						
						<div class="log-viewer" style="background: #f0f0f1; padding: 10px; border: 1px solid #ccc; height: 400px; overflow-y: scroll; font-family: monospace; white-space: pre-wrap; margin-bottom: 20px;">
							<?php
							$logs = Pfadi_Logger::get_logs( 100 );
							if ( empty( $logs ) ) {
								echo '<em>' . esc_html__( 'Keine Logs vorhanden.', 'wp-pfadi-manager' ) . '</em>';
							} else {
								foreach ( $logs as $log ) {
									echo esc_html( $log ) . "\n";
								}
							}
							?>
						</div>

						<div class="log-actions">
							<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=pfadi_download_log' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Download Log', 'wp-pfadi-manager' ); ?></a>
							<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=pfadi_clear_log' ) ); ?>" class="button button-link-delete" onclick="return confirm('<?php esc_attr_e( 'Sind Sie sicher?', 'wp-pfadi-manager' ); ?>');"><?php esc_html_e( 'Logs löschen', 'wp-pfadi-manager' ); ?></a>
						</div>
					</div>
				<?php else : ?>
					<form action="options.php" method="post">
						<?php
						settings_fields( 'pfadi_settings_group' );

						if ( 'general' === $active_tab ) {
							do_settings_sections( 'pfadi_settings_general' );
						} elseif ( 'email' === $active_tab ) {
							do_settings_sections( 'pfadi_settings_email' );
						} elseif ( 'units' === $active_tab ) {
							do_settings_sections( 'pfadi_settings_units' );
						}

						submit_button( 'Einstellungen speichern' );
						?>
					</form>
				<?php endif; ?>
			</div>
		</div>
		<style>
			.card {
				background: #fff;
				border: 1px solid #ccd0d4;
				padding: 20px;
				margin-bottom: 20px;
				box-shadow: 0 1px 1px rgba(0,0,0,.04);
			}
			.card h2 { margin-top: 0; }
			.card code { background: #f0f0f1; padding: 3px 5px; }
		</style>
		<?php
	}

	/**
	 * Handle log download.
	 */
	public function handle_download_log() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		$file = Pfadi_Logger::get_log_file_path();
		if ( ! file_exists( $file ) ) {
			wp_die( 'Log file not found' );
		}

		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="pfadi_debug.log"' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . filesize( $file ) );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		readfile( $file );
		exit;
	}

	/**
	 * Handle log clearing.
	 */
	public function handle_clear_log() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		Pfadi_Logger::clear_logs();

		// phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		wp_redirect( admin_url( 'edit.php?post_type=activity&page=pfadi_settings&tab=logs&msg=cleared' ) );
		exit;
	}
}
