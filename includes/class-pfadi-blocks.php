<?php

/**
 * Block editor integration.
 *
 * @package PfadiManager
 */

/**
 * Registers and renders Gutenberg blocks.
 */
class Pfadi_Blocks {

	/**
	 * Initialize the class.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_blocks' ) );
	}

	/**
	 * Register blocks and assets.
	 */
	public function register_blocks() {
		// Register the block editor script.
		wp_register_script(
			'pfadi-blocks-js',
			PFADI_MANAGER_URL . 'assets/js/blocks.js',
			array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n' ),
			'1.0.0',
			true
		);

		// Localize script with dynamic data.
		$units = get_terms(
			array(
				'taxonomy'   => 'activity_unit',
				'hide_empty' => false,
			)
		);

		$unit_options = array();
		if ( ! is_wp_error( $units ) ) {
			foreach ( $units as $unit ) {
				$unit_options[] = array(
					'label' => $unit->name,
					'value' => $unit->slug,
				);
			}
		}

		wp_localize_script(
			'pfadi-blocks-js',
			'pfadiBlockData',
			array(
				'units' => $unit_options,
			)
		);

		// Register Blocks.
		register_block_type(
			'pfadi/board',
			array(
				'editor_script'   => 'pfadi-blocks-js',
				'render_callback' => array( $this, 'render_board_block' ),
				'attributes'      => array(
					'view' => array(
						'type'    => 'string',
						'default' => 'cards',
					),
					'unit' => array(
						'type'    => 'string',
						'default' => '',
					),
				),
			)
		);

		register_block_type(
			'pfadi/subscribe',
			array(
				'editor_script'   => 'pfadi-blocks-js',
				'render_callback' => array( $this, 'render_subscribe_block' ),
			)
		);

		register_block_type(
			'pfadi/news',
			array(
				'editor_script'   => 'pfadi-blocks-js',
				'render_callback' => array( $this, 'render_news_block' ),
				'attributes'      => array(
					'view'  => array(
						'type'    => 'string',
						'default' => 'carousel',
					),
					'limit' => array(
						'type'    => 'number',
						'default' => -1,
					),
				),
			)
		);
	}

	/**
	 * Render the board block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public function render_board_block( $attributes ) {
		// Reuse the shortcode logic.
		$frontend = new Pfadi_Frontend();
		$atts     = array(
			'view' => isset( $attributes['view'] ) ? $attributes['view'] : 'cards',
		);

		// If unit is set in block, we might want to pre-filter or set active tab.
		// The shortcode logic currently relies on $_GET['pfadi_unit'] or clicks.
		// For the block, we could force a specific unit if desired, but the shortcode
		// is designed to show tabs.
		// If the user wants to show ONLY one unit, we might need to adjust the shortcode logic.
		// For now, we just render the board as is.

		return $frontend->render_board( $atts );
	}

	/**
	 * Render the subscribe block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public function render_subscribe_block( $attributes ) {
		$frontend = new Pfadi_Frontend();
		// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		return $frontend->render_subscribe( array() );
	}

	/**
	 * Render the news block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public function render_news_block( $attributes ) {
		$frontend = new Pfadi_Frontend();
		return $frontend->render_news( $attributes );
	}
}
