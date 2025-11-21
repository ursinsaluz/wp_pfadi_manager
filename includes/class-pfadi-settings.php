<?php

class Pfadi_Settings {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
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
