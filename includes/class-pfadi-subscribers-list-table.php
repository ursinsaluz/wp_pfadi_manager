<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Pfadi_Subscribers_List_Table extends WP_List_Table {

	public function __construct() {
		parent::__construct( array(
			'singular' => 'subscriber',
			'plural'   => 'subscribers',
			'ajax'     => false,
		) );
	}

	public function get_columns() {
		return array(
			'cb'               => '<input type="checkbox" />',
			'cb'               => '<input type="checkbox" />',
			'email'            => __( 'E-Mail', 'wp-pfadi-manager' ),
			'subscribed_units' => __( 'Abonnierte Stufen', 'wp-pfadi-manager' ),
			'status'           => __( 'Status', 'wp-pfadi-manager' ),
			'created_at'       => __( 'Erstellt am', 'wp-pfadi-manager' ),
		);
	}

	public function get_sortable_columns() {
		return array(
			'email'      => array( 'email', false ),
			'status'     => array( 'status', false ),
			'created_at' => array( 'created_at', false ),
		);
	}

	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'email':
			case 'status':
			case 'created_at':
				return esc_html( $item->$column_name );
			case 'subscribed_units':
				return $this->column_subscribed_units( $item );
			default:
				return print_r( $item, true );
		}
	}

	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="subscriber[]" value="%s" />',
			$item->id
		);
	}

	protected function column_subscribed_units( $item ) {
		$units = json_decode( $item->subscribed_units, true );
		if ( ! is_array( $units ) || empty( $units ) ) {
			return '-';
		}

		$unit_names = array();
		foreach ( $units as $unit_id ) {
			$term = get_term( $unit_id, 'activity_unit' );
			if ( $term && ! is_wp_error( $term ) ) {
				$unit_names[] = $term->name;
			}
		}

		return esc_html( implode( ', ', $unit_names ) );
	}

	protected function column_email( $item ) {
		$actions = array(
			'edit'   => sprintf( '<a href="?post_type=activity&page=%s&action=%s&subscriber=%s">' . __( 'Bearbeiten', 'wp-pfadi-manager' ) . '</a>', $_REQUEST['page'], 'edit', $item->id ),
			'delete' => sprintf( '<a href="?post_type=activity&page=%s&action=%s&subscriber=%s&_wpnonce=%s">' . __( 'Löschen', 'wp-pfadi-manager' ) . '</a>', $_REQUEST['page'], 'delete', $item->id, wp_create_nonce( 'delete_subscriber_' . $item->id ) ),
		);

		return sprintf( '%1$s %2$s', $item->email, $this->row_actions( $actions ) );
	}

	public function get_bulk_actions() {
		return array(
			'bulk-delete' => __( 'Löschen', 'wp-pfadi-manager' ),
		);
	}

	public function prepare_items() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'pfadi_subscribers';

		$per_page = 20;
		$current_page = $this->get_pagenum();
		$total_items = $wpdb->get_var( "SELECT COUNT(id) FROM $table_name" );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
		) );

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_sql_orderby( $_REQUEST['orderby'] ) : 'created_at';
		$order = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field( $_REQUEST['order'] ) : 'DESC';

		$sql = "SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d";
		$this->items = $wpdb->get_results( $wpdb->prepare( $sql, $per_page, ( $current_page - 1 ) * $per_page ) );
	}

	public function process_bulk_action() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'pfadi_subscribers';

		if ( 'delete' === $this->current_action() ) {
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );
			if ( ! wp_verify_nonce( $nonce, 'delete_subscriber_' . $_REQUEST['subscriber'] ) ) {
				wp_die( 'Security check failed' );
			}
			$wpdb->delete( $table_name, array( 'id' => absint( $_REQUEST['subscriber'] ) ) );
		}

		if ( 'bulk-delete' === $this->current_action() ) {
			check_admin_referer( 'bulk-subscribers' );
			
			if ( isset( $_POST['subscriber'] ) && is_array( $_POST['subscriber'] ) ) {
				foreach ( $_POST['subscriber'] as $subscriber_id ) {
					$wpdb->delete( $table_name, array( 'id' => absint( $subscriber_id ) ) );
				}
			}
		}
	}
}
