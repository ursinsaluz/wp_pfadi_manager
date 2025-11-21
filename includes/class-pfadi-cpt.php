<?php

class Pfadi_CPT {

	public function register_cpt() {
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
		);
		register_post_type( 'activity', $args );
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
}
