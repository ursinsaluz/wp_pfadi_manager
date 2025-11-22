<?php

class Pfadi_CPT {

	public function register_cpt() {
		$slug = get_option( 'pfadi_cpt_slug', 'activity' );
		$announcement_slug = get_option( 'pfadi_announcement_slug', 'mitteilung' );

		// Register Custom Post Status 'Archived'
		register_post_status( 'archived', array(
			'label'                     => _x( 'Archiviert', 'post status label', 'wp-pfadi-manager' ),
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Archiviert <span class="count">(%s)</span>', 'Archiviert <span class="count">(%s)</span>', 'wp-pfadi-manager' ),
		) );

		$labels = array(
			'name'                  => _x( 'Aktivitäten', 'Post Type General Name', 'wp-pfadi-manager' ),
			'singular_name'         => _x( 'Aktivität', 'Post Type Singular Name', 'wp-pfadi-manager' ),
			'menu_name'             => __( 'Aktivitäten', 'wp-pfadi-manager' ),
			'name_admin_bar'        => __( 'Aktivität', 'wp-pfadi-manager' ),
			'archives'              => __( 'Aktivitäten Archiv', 'wp-pfadi-manager' ),
			'attributes'            => __( 'Aktivitäten Attribute', 'wp-pfadi-manager' ),
			'parent_item_colon'     => __( 'Eltern-Aktivität:', 'wp-pfadi-manager' ),
			'all_items'             => __( 'Alle Aktivitäten', 'wp-pfadi-manager' ),
			'add_new_item'          => __( 'Neue Aktivität erstellen', 'wp-pfadi-manager' ),
			'add_new'               => __( 'Erstellen', 'wp-pfadi-manager' ),
			'new_item'              => __( 'Neue Aktivität', 'wp-pfadi-manager' ),
			'edit_item'             => __( 'Aktivität bearbeiten', 'wp-pfadi-manager' ),
			'update_item'           => __( 'Aktivität aktualisieren', 'wp-pfadi-manager' ),
			'view_item'             => __( 'Aktivität ansehen', 'wp-pfadi-manager' ),
			'view_items'            => __( 'Aktivitäten ansehen', 'wp-pfadi-manager' ),
			'search_items'          => __( 'Aktivität suchen', 'wp-pfadi-manager' ),
			'not_found'             => __( 'Nicht gefunden', 'wp-pfadi-manager' ),
			'not_found_in_trash'    => __( 'Nicht im Papierkorb gefunden', 'wp-pfadi-manager' ),
			'featured_image'        => __( 'Beitragsbild', 'wp-pfadi-manager' ),
			'set_featured_image'    => __( 'Beitragsbild festlegen', 'wp-pfadi-manager' ),
			'remove_featured_image' => __( 'Beitragsbild entfernen', 'wp-pfadi-manager' ),
			'use_featured_image'    => __( 'Als Beitragsbild verwenden', 'wp-pfadi-manager' ),
			'insert_into_item'      => __( 'In Aktivität einfügen', 'wp-pfadi-manager' ),
			'uploaded_to_this_item' => __( 'Zu dieser Aktivität hochgeladen', 'wp-pfadi-manager' ),
			'items_list'            => __( 'Aktivitäten Liste', 'wp-pfadi-manager' ),
			'items_list_navigation' => __( 'Aktivitäten Liste Navigation', 'wp-pfadi-manager' ),
			'filter_items_list'     => __( 'Aktivitäten Liste filtern', 'wp-pfadi-manager' ),
		);
		$args = array(
			'label'                 => __( 'Aktivität', 'wp-pfadi-manager' ),
			'description'           => __( 'Pfadi Aktivitäten', 'wp-pfadi-manager' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'author' ),
			'taxonomies'            => array( 'activity_unit' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'menu_icon'             => 'dashicons-calendar-alt',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'post',
			'show_in_rest'          => false, // Disable Block Editor
			'rewrite'               => array( 'slug' => $slug ),
		);
		register_post_type( 'activity', $args );

		// Register Announcement CPT
		// Register Announcement CPT
		$labels_announcement = array(
			'name'                  => _x( 'Mitteilungen', 'Post Type General Name', 'wp-pfadi-manager' ),
			'singular_name'         => _x( 'Mitteilung', 'Post Type Singular Name', 'wp-pfadi-manager' ),
			'menu_name'             => __( 'Mitteilungen', 'wp-pfadi-manager' ),
			'name_admin_bar'        => __( 'Mitteilung', 'wp-pfadi-manager' ),
			'add_new'               => __( 'Erstellen', 'wp-pfadi-manager' ),
			'add_new_item'          => __( 'Neue Mitteilung erstellen', 'wp-pfadi-manager' ),
			'new_item'              => __( 'Neue Mitteilung', 'wp-pfadi-manager' ),
			'edit_item'             => __( 'Mitteilung bearbeiten', 'wp-pfadi-manager' ),
			'view_item'             => __( 'Mitteilung ansehen', 'wp-pfadi-manager' ),
			'all_items'             => __( 'Alle Mitteilungen', 'wp-pfadi-manager' ),
			'search_items'          => __( 'Mitteilungen suchen', 'wp-pfadi-manager' ),
			'not_found'             => __( 'Keine Mitteilungen gefunden.', 'wp-pfadi-manager' ),
			'not_found_in_trash'    => __( 'Keine Mitteilungen im Papierkorb gefunden.', 'wp-pfadi-manager' ),
		);

		$args_announcement = array(
			'labels'                => $labels_announcement,
			'public'                => true,
			'publicly_queryable'    => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => $announcement_slug ),
			'capability_type'       => 'post',
			'has_archive'           => true,
			'hierarchical'          => false,
			'menu_position'         => 6,
			'menu_icon'             => 'dashicons-megaphone',
			'supports'              => array( 'title', 'editor' ),
			'show_in_rest'          => false,
		);

		register_post_type( 'announcement', $args_announcement );
		
		// Register Taxonomy for Units (shared)
		register_taxonomy_for_object_type( 'activity_unit', 'announcement' );

		if ( get_option( 'pfadi_flush_rewrite_rules' ) ) {
			flush_rewrite_rules();
			delete_option( 'pfadi_flush_rewrite_rules' );
		}

		// Admin Columns
		add_filter( 'manage_activity_posts_columns', array( $this, 'add_activity_columns' ) );
		add_action( 'manage_activity_posts_custom_column', array( $this, 'render_activity_columns' ), 10, 2 );
		add_filter( 'manage_edit_activity_sortable_columns', array( $this, 'sortable_activity_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'sort_activity_by_date' ) );

		// Bulk Actions
		add_filter( 'bulk_actions-edit-activity', array( $this, 'register_bulk_actions' ) );
		add_filter( 'bulk_actions-edit-announcement', array( $this, 'register_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-activity', array( $this, 'handle_bulk_actions' ), 10, 3 );
		add_filter( 'handle_bulk_actions-edit-announcement', array( $this, 'handle_bulk_actions' ), 10, 3 );
		add_action( 'admin_notices', array( $this, 'bulk_action_admin_notice' ) );
	}

	public function register_bulk_actions( $bulk_actions ) {
		if ( current_user_can( 'publish_posts' ) ) {
			$bulk_actions['pfadi_resend_email'] = __( 'E-Mail erneut senden', 'wp-pfadi-manager' );
		}
		return $bulk_actions;
	}

	public function handle_bulk_actions( $redirect_to, $action, $post_ids ) {
		if ( 'pfadi_resend_email' !== $action ) {
			return $redirect_to;
		}

		if ( ! current_user_can( 'publish_posts' ) ) {
			wp_die( __( 'Keine Berechtigung.', 'wp-pfadi-manager' ) );
		}

		$processed = 0;
		$mailer = new Pfadi_Mailer();

		foreach ( $post_ids as $post_id ) {
			// Verify post type just in case
			$post = get_post( $post_id );
			if ( 'activity' === $post->post_type || 'announcement' === $post->post_type ) {
				// We call send_newsletter_by_id which triggers the mailer
				// Note: Pfadi_Mailer needs to be instantiated or method static. 
				// send_newsletter_by_id is public but not static. 
				// However, it's hooked to an action 'pfadi_send_post_email'.
				// We can either call it directly on an instance or trigger the action.
				// Triggering the action via wp_schedule_single_event( time(), ... ) is safer for performance if many are selected,
				// but user might expect immediate feedback.
				// Let's call it directly via instance for "Action" feel, or schedule it.
				// Given "Resend", immediate is probably expected or at least "queued".
				// Let's use the instance we created.
				
				$mailer->send_newsletter_by_id( $post_id );
				$processed++;
			}
		}

		$redirect_to = add_query_arg( 'pfadi_resent_emails', $processed, $redirect_to );
		return $redirect_to;
	}

	public function bulk_action_admin_notice() {
		if ( ! empty( $_REQUEST['pfadi_resent_emails'] ) ) {
			$count = intval( $_REQUEST['pfadi_resent_emails'] );
			printf(
				'<div id="message" class="updated notice is-dismissible"><p>%s</p></div>',
				sprintf( _n( '%s E-Mail wurde erneut versendet.', '%s E-Mails wurden erneut versendet.', $count, 'wp-pfadi-manager' ), $count )
			);
		}
	}

	public function register_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Stufen', 'Taxonomy General Name', 'wp-pfadi-manager' ),
			'singular_name'              => _x( 'Stufe', 'Taxonomy Singular Name', 'wp-pfadi-manager' ),
			'menu_name'                  => __( 'Stufen', 'wp-pfadi-manager' ),
			'all_items'                  => __( 'Alle Stufen', 'wp-pfadi-manager' ),
			'parent_item'                => __( 'Eltern Stufe', 'wp-pfadi-manager' ),
			'parent_item_colon'          => __( 'Eltern Stufe:', 'wp-pfadi-manager' ),
			'new_item_name'              => __( 'Neuer Stufen Name', 'wp-pfadi-manager' ),
			'add_new_item'               => __( 'Neue Stufe hinzufügen', 'wp-pfadi-manager' ),
			'edit_item'                  => __( 'Stufe bearbeiten', 'wp-pfadi-manager' ),
			'update_item'                => __( 'Stufe aktualisieren', 'wp-pfadi-manager' ),
			'view_item'                  => __( 'Stufe ansehen', 'wp-pfadi-manager' ),
			'separate_items_with_commas' => __( 'Stufen mit Kommas trennen', 'wp-pfadi-manager' ),
			'add_or_remove_items'        => __( 'Stufen hinzufügen oder entfernen', 'wp-pfadi-manager' ),
			'choose_from_most_used'      => __( 'Aus den meistgenutzten wählen', 'wp-pfadi-manager' ),
			'popular_items'              => __( 'Beliebte Stufen', 'wp-pfadi-manager' ),
			'search_items'               => __( 'Stufen suchen', 'wp-pfadi-manager' ),
			'not_found'                  => __( 'Nicht gefunden', 'wp-pfadi-manager' ),
			'no_terms'                   => __( 'Keine Stufen', 'wp-pfadi-manager' ),
			'items_list'                 => __( 'Stufen Liste', 'wp-pfadi-manager' ),
			'items_list_navigation'      => __( 'Stufen Liste Navigation', 'wp-pfadi-manager' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
			'show_in_rest'               => false,
		);
		register_taxonomy( 'activity_unit', array( 'activity' ), $args );
		
		// Register default terms if they don't exist
		if ( ! term_exists( 'Biber', 'activity_unit' ) ) wp_insert_term( 'Biber', 'activity_unit' );
		if ( ! term_exists( 'Wölfe', 'activity_unit' ) ) wp_insert_term( 'Wölfe', 'activity_unit' );
		if ( ! term_exists( 'Pfadis', 'activity_unit' ) ) wp_insert_term( 'Pfadis', 'activity_unit' );
		if ( ! term_exists( 'Pios', 'activity_unit' ) ) wp_insert_term( 'Pios', 'activity_unit' );
		if ( ! term_exists( 'Rover', 'activity_unit' ) ) wp_insert_term( 'Rover', 'activity_unit' );
		if ( ! term_exists( 'Abteilung', 'activity_unit' ) ) wp_insert_term( 'Abteilung', 'activity_unit' );
	}

	public function add_activity_columns( $columns ) {
		$columns['activity_date'] = __( 'Datum', 'wp-pfadi-manager' );
		return $columns;
	}

	public function render_activity_columns( $column, $post_id ) {
		if ( 'activity_date' === $column ) {
			$start = get_post_meta( $post_id, '_pfadi_start_time', true );
			if ( $start ) {
				echo date_i18n( 'd.m.Y H:i', strtotime( $start ) ) . ' Uhr';
			} else {
				echo '-';
			}
		}
	}

	public function sortable_activity_columns( $columns ) {
		$columns['activity_date'] = 'activity_date';
		return $columns;
	}

	public function sort_activity_by_date( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( 'activity_date' === $query->get( 'orderby' ) ) {
			$query->set( 'meta_key', '_pfadi_start_time' );
			$query->set( 'orderby', 'meta_value' );
		}
	}
}
