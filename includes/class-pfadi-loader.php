<?php

/**
 * @package PfadiManager
 */

/**
 * Register all actions and filters for the plugin.
 *
 * @package PfadiManager
 */
class Pfadi_Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 */
	public function __construct() {

		$this->actions = array();
		$this->filters = array();
		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 */
	private function load_dependencies() {
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-cpt.php';
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-db.php';
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-logger.php';
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-settings.php';
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-metaboxes.php';
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-frontend.php';
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-mailer.php';
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-feeds.php';
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-cron.php';
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-admin-pages.php';
		require_once PFADI_MANAGER_PATH . 'includes/class-pfadi-blocks.php';
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
		new Pfadi_Blocks();
	}

	public function run() {
		// This is where we would execute the loader if we had a more complex hook registry system
		// For now, the hooks are added directly in define_*_hooks
	}
}
