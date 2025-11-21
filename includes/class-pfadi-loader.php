<?php

class Pfadi_Loader {

	protected $actions;
	protected $filters;

	public function __construct() {
		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-cpt.php';
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-db.php';
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-settings.php';
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-metaboxes.php';
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-frontend.php';
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-mailer.php';
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-feeds.php';
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-cron.php';
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-admin-pages.php';
	}

	private function define_admin_hooks() {
		$cpt = new Pfadi_CPT();
		add_action( 'init', array( $cpt, 'register_cpt' ) );
		add_action( 'init', array( $cpt, 'register_taxonomy' ) );

		$db = new Pfadi_DB();
		register_activation_hook( PFADI_MANAGER_PATH . 'wp-pfadi-manager.php', array( $db, 'create_table' ) );

		new Pfadi_Settings();
		new Pfadi_Metaboxes();
		new Pfadi_Mailer();
		new Pfadi_Cron();
		new Pfadi_Admin_Pages();
	}

	private function define_public_hooks() {
		new Pfadi_Frontend();
		new Pfadi_Feeds();
	}

	public function run() {
		// This is where we would execute the loader if we had a more complex hook registry system
		// For now, the hooks are added directly in define_*_hooks
	}
}
