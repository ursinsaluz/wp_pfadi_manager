<?php

class Pfadi_Settings {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'update_option_pfadi_cpt_slug', array( $this, 'flush_rewrite_rules_flag' ), 10, 2 );
		add_action( 'admin_post_pfadi_download_log', array( $this, 'handle_download_log' ) );
		add_action( 'admin_post_pfadi_clear_log', array( $this, 'handle_clear_log' ) );
	}

	public function flush_rewrite_rules_flag( $old_value, $new_value ) {
		if ( $old_value !== $new_value ) {
			update_option( 'pfadi_flush_rewrite_rules', true );
		}
	}

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

	public function register_settings() {
		// General Settings
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

		// Email Settings
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

	public function greeting_field_html( $args ) {
		$unit_slug = $args['unit_slug'];
		$value     = get_option( "pfadi_greeting_$unit_slug" );
		?>
		<input type="text" name="pfadi_greeting_<?php echo esc_attr( $unit_slug ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text">
		<?php
	}

	public function leaders_field_html( $args ) {
		$unit_slug = $args['unit_slug'];
		$value     = get_option( "pfadi_leaders_$unit_slug" );
		?>
		<input type="text" name="pfadi_leaders_<?php echo esc_attr( $unit_slug ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text">
		<?php
	}

	public function cpt_slug_field_html() {
		$slug = get_option( 'pfadi_cpt_slug', 'activity' );
		?>
		<input type="text" name="pfadi_cpt_slug" value="<?php echo esc_attr( $slug ); ?>">
		<p class="description">Standard: activity. Ändern Sie dies nur, wenn Sie wissen, was Sie tun. Permalinks werden automatisch aktualisiert.</p>
		<?php
	}

	public function announcement_slug_field_html() {
		$slug = get_option( 'pfadi_announcement_slug', 'mitteilung' );
		?>
		<input type="text" name="pfadi_announcement_slug" value="<?php echo esc_attr( $slug ); ?>">
		<p class="description">Standard: mitteilung. Ändern Sie dies nur, wenn Sie wissen, was Sie tun. Permalinks werden automatisch aktualisiert.</p>
		<?php
	}

	public function mail_mode_field_html() {
		$mode = get_option( 'pfadi_mail_mode', 'scheduled' );
		?>
		<select name="pfadi_mail_mode">
			<option value="scheduled" <?php selected( $mode, 'scheduled' ); ?>>Geplant (Täglich um X Uhr)</option>
			<option value="immediate" <?php selected( $mode, 'immediate' ); ?>>Sofort nach Veröffentlichung</option>
		</select>
		<?php
	}

	public function mail_time_field_html() {
		$time = get_option( 'pfadi_mail_time' );
		if ( empty( $time ) ) {
			$time = '21:00';
		}
		?>
		<input type="time" name="pfadi_mail_time" value="<?php echo esc_attr( $time ); ?>">
		<?php
	}

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
			<p class="description"><?php printf( esc_html__( 'Standard: %s', 'wp-pfadi-manager' ), esc_html( $default ) ); ?></p>
		<?php endif; ?>
		<?php if ( $description ) : ?>
			<p class="description"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
		<?php
	}

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

	public function settings_page_html() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'info';
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<nav class="nav-tab-wrapper">
				<a href="?post_type=activity&page=pfadi_settings&tab=info" class="nav-tab <?php echo $active_tab == 'info' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Hilfe & Info', 'wp-pfadi-manager' ); ?></a>
				<a href="?post_type=activity&page=pfadi_settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Allgemein', 'wp-pfadi-manager' ); ?></a>
				<a href="?post_type=activity&page=pfadi_settings&tab=email" class="nav-tab <?php echo $active_tab == 'email' ? 'nav-tab-active' : ''; ?>"><?php _e( 'E-Mail', 'wp-pfadi-manager' ); ?></a>
				<a href="?post_type=activity&page=pfadi_settings&tab=units" class="nav-tab <?php echo $active_tab == 'units' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Stufen-Einstellungen', 'wp-pfadi-manager' ); ?></a>
				<a href="?post_type=activity&page=pfadi_settings&tab=logs" class="nav-tab <?php echo $active_tab == 'logs' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Logs', 'wp-pfadi-manager' ); ?></a>
			</nav>

			<div class="tab-content">
				<?php if ( $active_tab == 'info' ) : ?>
					<div class="card" style="max-width: 800px; margin-top: 20px;">
						<h2><?php _e( 'Shortcodes', 'wp-pfadi-manager' ); ?></h2>
						<p><?php _e( 'Folgende Shortcodes stehen zur Verfügung:', 'wp-pfadi-manager' ); ?></p>
						
						<h3>1. <?php _e( 'Aktivitäten-Board', 'wp-pfadi-manager' ); ?></h3>
						<code>[pfadi_board]</code>
						<p><?php _e( 'Zeigt die aktuellen Aktivitäten an.', 'wp-pfadi-manager' ); ?></p>
						<p><strong><?php _e( 'Parameter:', 'wp-pfadi-manager' ); ?></strong></p>
						<ul>
							<li><code>view="cards"</code> (<?php _e( 'Standard', 'wp-pfadi-manager' ); ?>) - <?php _e( 'Zeigt Kacheln an.', 'wp-pfadi-manager' ); ?></li>
							<li><code>view="table"</code> - <?php _e( 'Zeigt eine Tabelle an.', 'wp-pfadi-manager' ); ?></li>
							<li><code>view="list"</code> - <?php _e( 'Zeigt eine Liste mit seitlichen Tabs an.', 'wp-pfadi-manager' ); ?></li>
							<li><code>unit="slug"</code> (<?php _e( 'Optional', 'wp-pfadi-manager' ); ?>) - <?php _e( 'Filtert nach einer bestimmten Stufe (z.B. biber, wolfe, pfadis).', 'wp-pfadi-manager' ); ?></li>
						</ul>
						<p><em><?php _e( 'Beispiel:', 'wp-pfadi-manager' ); ?></em> <code>[pfadi_board view="list"]</code></p>

						<h3>2. <?php _e( 'Abo-Formular', 'wp-pfadi-manager' ); ?></h3>
						<code>[pfadi_subscribe]</code>
						<p><?php _e( 'Zeigt das Formular zum Abonnieren des Newsletters an.', 'wp-pfadi-manager' ); ?></p>

						<h3>3. <?php _e( 'Mitteilungen', 'wp-pfadi-manager' ); ?></h3>
						<code>[pfadi_news]</code>
						<p><?php _e( 'Zeigt aktuelle Mitteilungen an.', 'wp-pfadi-manager' ); ?></p>
						<p><strong><?php _e( 'Parameter:', 'wp-pfadi-manager' ); ?></strong></p>
						<ul>
							<li><code>view="carousel"</code> (<?php _e( 'Standard', 'wp-pfadi-manager' ); ?>) - <?php _e( 'Zeigt ein Karussell aller aktuellen Mitteilungen.', 'wp-pfadi-manager' ); ?></li>
							<li><code>view="banner"</code> - <?php _e( 'Zeigt die neuste Mitteilung als Banner an.', 'wp-pfadi-manager' ); ?></li>
							<li><code>limit="-1"</code> (<?php _e( 'Optional', 'wp-pfadi-manager' ); ?>) - <?php _e( 'Anzahl der anzuzeigenden Mitteilungen (Standard: alle).', 'wp-pfadi-manager' ); ?></li>
						</ul>
						<p><em><?php _e( 'Beispiel:', 'wp-pfadi-manager' ); ?></em> <code>[pfadi_news view="banner"]</code></p>
					</div>

					<div class="card" style="max-width: 800px; margin-top: 20px;">
						<h2><?php _e( 'Technische Informationen', 'wp-pfadi-manager' ); ?></h2>
						<p><strong><?php _e( 'Plugin Version:', 'wp-pfadi-manager' ); ?></strong> <?php echo PFADI_MANAGER_VERSION; ?></p>
						<p><strong><?php _e( 'Datenbank-Tabelle:', 'wp-pfadi-manager' ); ?></strong> 
						<?php
						global $wpdb;
						echo $wpdb->prefix . 'pfadi_subscribers';
						?>
						</p>
						<p><strong><?php _e( 'Cronjobs:', 'wp-pfadi-manager' ); ?></strong></p>
						<ul>
							<li><?php _e( 'Täglicher Cleanup (alte Aktivitäten archivieren)', 'wp-pfadi-manager' ); ?></li>
						</ul>
					</div>
					</div>
				<?php elseif ( $active_tab == 'logs' ) : ?>
					<div class="card" style="max-width: 800px; margin-top: 20px;">
						<h2><?php _e( 'System Logs', 'wp-pfadi-manager' ); ?></h2>
						<p><?php _e( 'Hier sehen Sie die letzten 100 Log-Einträge des Plugins.', 'wp-pfadi-manager' ); ?></p>
						
						<div class="log-viewer" style="background: #f0f0f1; padding: 10px; border: 1px solid #ccc; height: 400px; overflow-y: scroll; font-family: monospace; white-space: pre-wrap; margin-bottom: 20px;">
							<?php
							$logs = Pfadi_Logger::get_logs( 100 );
							if ( empty( $logs ) ) {
								echo '<em>' . __( 'Keine Logs vorhanden.', 'wp-pfadi-manager' ) . '</em>';
							} else {
								foreach ( $logs as $log ) {
									echo esc_html( $log ) . "\n";
								}
							}
							?>
						</div>

						<div class="log-actions">
							<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=pfadi_download_log' ) ); ?>" class="button button-secondary"><?php _e( 'Download Log', 'wp-pfadi-manager' ); ?></a>
							<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=pfadi_clear_log' ) ); ?>" class="button button-link-delete" onclick="return confirm('<?php _e( 'Sind Sie sicher?', 'wp-pfadi-manager' ); ?>');"><?php _e( 'Logs löschen', 'wp-pfadi-manager' ); ?></a>
						</div>
					</div>
				<?php else : ?>
					<form action="options.php" method="post">
						<?php
						settings_fields( 'pfadi_settings_group' );

						if ( $active_tab == 'general' ) {
							do_settings_sections( 'pfadi_settings' ); // General settings are in 'pfadi_settings' page but 'pfadi_general_section' section? No, wait.
							// In register_settings:
							// General: page='pfadi_settings', section='pfadi_general_section' (Wait, add_settings_section uses 'pfadi_section_general')
							// Let's fix the section names in register_settings to be consistent or use do_settings_sections with the page slug.
							// The page slug used in add_settings_section is 'pfadi_settings'.
							// But we want to show only specific sections based on tab.
							// WordPress do_settings_sections prints ALL sections for a page.
							// We need to register sections to DIFFERENT pages if we want to split them, OR manually print sections.
							// Actually, we can just use different page slugs for add_settings_section.

							// Let's assume we refactor register_settings to use tab-specific page slugs.
							do_settings_sections( 'pfadi_settings_general' );
						} elseif ( $active_tab == 'email' ) {
							do_settings_sections( 'pfadi_settings_email' );
						} elseif ( $active_tab == 'units' ) {
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
		readfile( $file );
		exit;
	}

	public function handle_clear_log() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		Pfadi_Logger::clear_logs();

		wp_redirect( admin_url( 'edit.php?post_type=activity&page=pfadi_settings&tab=logs&msg=cleared' ) );
		exit;
	}
}
