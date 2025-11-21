<?php

class Pfadi_Settings {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'update_option_pfadi_cpt_slug', array( $this, 'flush_rewrite_rules_flag' ), 10, 2 );
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
			'pfadi_settings'
		);

		add_settings_field(
			'pfadi_cpt_slug',
			'Aktivitäten Slug (URL)',
			array( $this, 'cpt_slug_field_html' ),
			'pfadi_settings',
			'pfadi_general_section'
		);

		add_settings_field(
			'pfadi_announcement_slug',
			'Mitteilungen Slug (URL)',
			array( $this, 'announcement_slug_field_html' ),
			'pfadi_settings',
			'pfadi_general_section'
		);

		// Email Settings
		register_setting( 'pfadi_settings_group', 'pfadi_mail_subject' );
		register_setting( 'pfadi_settings_group', 'pfadi_confirm_subject' );
		register_setting( 'pfadi_settings_group', 'pfadi_confirm_message' );
		register_setting( 'pfadi_settings_group', 'pfadi_mail_mode' );
		register_setting( 'pfadi_settings_group', 'pfadi_mail_time' );

		add_settings_section(
			'pfadi_section_email',
			'E-Mail Texte',
			null,
			'pfadi_settings'
		);

		add_settings_field(
			'pfadi_mail_subject',
			'Betreff für neue Aktivitäten',
			array( $this, 'text_field_html' ),
			'pfadi_settings',
			'pfadi_section_email',
			array( 'name' => 'pfadi_mail_subject', 'default' => 'Neue Pfadi-Aktivität: {title}' )
		);

		add_settings_field(
			'pfadi_confirm_subject',
			'Betreff für Bestätigungsmail',
			array( $this, 'text_field_html' ),
			'pfadi_settings',
			'pfadi_section_email',
			array( 'name' => 'pfadi_confirm_subject', 'default' => 'Pfadi Abo Bestätigen' )
		);

		add_settings_field(
			'pfadi_confirm_message',
			'Nachricht für Bestätigungsmail',
			array( $this, 'textarea_field_html' ),
			'pfadi_settings',
			'pfadi_section_email',
			array( 'name' => 'pfadi_confirm_message', 'default' => 'Bitte bestätigen Sie Ihr Abo: {link}' )
		);

		add_settings_field(
			'pfadi_mail_mode',
			'E-Mail Versandmodus',
			array( $this, 'mail_mode_field_html' ),
			'pfadi_settings',
			'pfadi_section_email'
		);

		add_settings_field(
			'pfadi_mail_time',
			'Versandzeit (wenn geplant)',
			array( $this, 'mail_time_field_html' ),
			'pfadi_settings',
			'pfadi_section_email'
		);

		$units = array( 'Biber', 'Wölfe', 'Pfadis', 'Pios', 'Rover', 'Abteilung' );

		foreach ( $units as $unit ) {
			$unit_slug = sanitize_title( $unit );
			
			register_setting( 'pfadi_settings_group', "pfadi_greeting_$unit_slug" );
			register_setting( 'pfadi_settings_group', "pfadi_leaders_$unit_slug" );

			add_settings_section(
				"pfadi_section_$unit_slug",
				"Einstellungen für $unit",
				null,
				'pfadi_settings'
			);

			add_settings_field(
				"pfadi_greeting_$unit_slug",
				'Standard-Gruss',
				array( $this, 'greeting_field_html' ),
				'pfadi_settings',
				"pfadi_section_$unit_slug",
				array( 'unit_slug' => $unit_slug )
			);

			add_settings_field(
				"pfadi_leaders_$unit_slug",
				'Standard-Leitung',
				array( $this, 'leaders_field_html' ),
				'pfadi_settings',
				"pfadi_section_$unit_slug",
				array( 'unit_slug' => $unit_slug )
			);
		}
	}

	public function greeting_field_html( $args ) {
		$unit_slug = $args['unit_slug'];
		$value = get_option( "pfadi_greeting_$unit_slug" );
		?>
		<input type="text" name="pfadi_greeting_<?php echo esc_attr( $unit_slug ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text">
		<?php
	}

	public function leaders_field_html( $args ) {
		$unit_slug = $args['unit_slug'];
		$value = get_option( "pfadi_leaders_$unit_slug" );
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
		$time = get_option( 'pfadi_mail_time', '20:00' );
		?>
		<input type="time" name="pfadi_mail_time" value="<?php echo esc_attr( $time ); ?>">
		<?php
	}

	public function text_field_html( $args ) {
		$name = $args['name'];
		$default = isset( $args['default'] ) ? $args['default'] : '';
		$value = get_option( $name, $default );
		?>
		<input type="text" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" class="large-text">
		<?php
	}

	public function textarea_field_html( $args ) {
		$name = $args['name'];
		$default = isset( $args['default'] ) ? $args['default'] : '';
		$value = get_option( $name, $default );
		?>
		<textarea name="<?php echo esc_attr( $name ); ?>" rows="5" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
		<p class="description">Platzhalter: {link} für den Bestätigungslink.</p>
		<?php
	}

	public function settings_page_html() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'pfadi_settings_group' );
				do_settings_sections( 'pfadi_settings' );
				submit_button( 'Einstellungen speichern' );
				?>
			</form>
		</div>
		<?php
	}
}
