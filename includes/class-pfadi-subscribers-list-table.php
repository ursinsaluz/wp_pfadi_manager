<?php
/**
 * Subscribers list table.
 *
 * @package PfadiManager
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table for subscribers.
 */
class Pfadi_Subscribers_List_Table extends WP_List_Table {

	/**
	 * Initialize the class.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'subscriber',
				'plural'   => 'subscribers',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Get columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'               => '<input type="checkbox" />',
			'email'            => __( 'E-Mail', 'wp-pfadi-manager' ),
			'subscribed_units' => __( 'Abonnierte Stufen', 'wp-pfadi-manager' ),
			'status'           => __( 'Status', 'wp-pfadi-manager' ),
			'created_at'       => __( 'Erstellt am', 'wp-pfadi-manager' ),
		);
	}

	/**
	 * Get sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'email'      => array( 'email', false ),
			'status'     => array( 'status', false ),
			'created_at' => array( 'created_at', false ),
		);
	}

	/**
	 * Default column rendering.
	 *
	 * @param object $item        The current item.
	 * @param string $column_name The column name.
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'email':
			case 'status':
			case 'created_at':
				return esc_html( $item->$column_name );
			case 'subscribed_units':
				return $this->column_subscribed_units( $item );
			default:
				return '';
		}
	}

	/**
	 * Checkbox column rendering.
	 *
	 * @param object $item The current item.
	 * @return string
	 */
	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="subscriber[]" value="%s" />',
			$item->id
		);
	}

	/**
	 * Subscribed units column rendering.
	 *
	 * @param object $item The current item.
	 * @return string
	 */
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

	/**
	 * Email column rendering.
	 *
	 * @param object $item The current item.
	 * @return string
	 */
	protected function column_email( $item ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';

		$actions = array(
			'edit'   => sprintf( '<a href="?post_type=activity&page=%s&action=%s&subscriber=%s">' . __( 'Bearbeiten', 'wp-pfadi-manager' ) . '</a>', $page, 'edit', $item->id ),
			'delete' => sprintf( '<a href="?post_type=activity&page=%s&action=%s&subscriber=%s&_wpnonce=%s">' . __( 'Löschen', 'wp-pfadi-manager' ) . '</a>', $page, 'delete', $item->id, wp_create_nonce( 'delete_subscriber_' . $item->id ) ),
		);

		return sprintf( '%1$s %2$s', $item->email, $this->row_actions( $actions ) );
	}

	/**
	 * Get bulk actions.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'bulk-delete' => __( 'Löschen', 'wp-pfadi-manager' ),
		);
	}

	/**
	 * Prepare items for the table.
	 */
	public function prepare_items() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'pfadi_subscribers';

		$per_page     = 20;
		$current_page = $this->get_pagenum();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total_items = $wpdb->get_var( "SELECT COUNT(id) FROM $table_name" );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_sql_orderby( wp_unslash( $_REQUEST['orderby'] ) ) : 'created_at';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'DESC';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$this->items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, ( $current_page - 1 ) * $per_page ) );
	}

	/**
	 * Process bulk actions.
	 */
	public function process_bulk_action() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'pfadi_subscribers';

		if ( 'delete' === $this->current_action() ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$subscriber_id = isset( $_REQUEST['subscriber'] ) ? absint( wp_unslash( $_REQUEST['subscriber'] ) ) : 0;
			if ( ! wp_verify_nonce( $nonce, 'delete_subscriber_' . $subscriber_id ) ) {
				wp_die( 'Security check failed' );
			}
			$wpdb->delete( $table_name, array( 'id' => $subscriber_id ) );
		}

		if ( 'bulk-delete' === $this->current_action() ) {
			check_admin_referer( 'bulk-subscribers' );

			if ( isset( $_POST['subscriber'] ) && is_array( $_POST['subscriber'] ) ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$subscribers = array_map( 'absint', wp_unslash( $_POST['subscriber'] ) );
				foreach ( $subscribers as $subscriber_id ) {
					$wpdb->delete( $table_name, array( 'id' => absint( $subscriber_id ) ) );
				}
			}
		}
	}
}
