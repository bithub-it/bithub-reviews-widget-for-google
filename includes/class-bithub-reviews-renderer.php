<?php
/**
 * Front-end shortcode and block renderer.
 *
 * @package BitHubReviewsWidget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class BitHub_Reviews_Renderer {
	/** @var BitHub_Reviews_Renderer|null */
	private static $instance = null;

	/** @var array<string,bool> */
	private $rendered = array();

	/**
	 * Singleton.
	 *
	 * @return BitHub_Reviews_Renderer
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Activation defaults.
	 *
	 * @return void
	 */
	public static function activate() {
		if ( false === get_option( BITHUB_REVIEWS_OPTION, false ) ) {
			add_option( BITHUB_REVIEWS_OPTION, array(), '', false );
		}
	}

	/** Constructor. */
	private function __construct() {
		add_shortcode( 'bithub_reviews', array( $this, 'shortcode' ) );
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Register the dynamic Gutenberg block.
	 *
	 * @return void
	 */
	public function register_block() {
		$script_path = BITHUB_REVIEWS_DIR . 'blocks/reviews/index.js';
		if ( file_exists( $script_path ) ) {
			wp_register_script(
				'bithub-reviews-block-editor',
				BITHUB_REVIEWS_URL . 'blocks/reviews/index.js',
				array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor' ),
				BITHUB_REVIEWS_VERSION,
				true
			);
			wp_localize_script(
				'bithub-reviews-block-editor',
				'BitHubReviewsBlock',
				array(
					'title'       => __( 'BitHub Reviews Widget', 'bithub-reviews-widget-for-google' ),
					'description' => __( 'Displays the Google review widget configured in BitHub Reviews.', 'bithub-reviews-widget-for-google' ),
					'configured'  => $this->has_widget(),
					'readyText'   => __( 'The configured reviews widget will be displayed here on the front end.', 'bithub-reviews-widget-for-google' ),
					'emptyText'   => __( 'Configure a widget in Tools > BitHub Reviews before publishing this block.', 'bithub-reviews-widget-for-google' ),
				)
			);
		}

		register_block_type(
			BITHUB_REVIEWS_DIR . 'blocks/reviews',
			array(
				'editor_script'   => 'bithub-reviews-block-editor',
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Block render callback.
	 *
	 * @return string
	 */
	public function render_block() {
		return $this->render_widget();
	}

	/**
	 * Shortcode handler.
	 *
	 * @return string
	 */
	public function shortcode() {
		return $this->render_widget();
	}

	/**
	 * Whether the local site has a valid widget id.
	 *
	 * @return bool
	 */
	private function has_widget() {
		$settings = get_option( BITHUB_REVIEWS_OPTION, array() );
		return is_array( $settings ) && ! empty( $settings['widget_id'] ) && self::valid_widget_id( $settings['widget_id'] );
	}

	/**
	 * Render the remote service widget loader.
	 *
	 * @return string
	 */
	private function render_widget() {
		$settings = get_option( BITHUB_REVIEWS_OPTION, array() );
		if ( ! is_array( $settings ) || empty( $settings['widget_id'] ) ) {
			return '';
		}

		$widget_id = (string) $settings['widget_id'];
		if ( ! self::valid_widget_id( $widget_id ) ) {
			return '';
		}

		if ( isset( $this->rendered[ $widget_id ] ) ) {
			return '<!-- BitHub Reviews Widget already rendered on this page. -->';
		}
		$this->rendered[ $widget_id ] = true;

		$target_id = 'bithub-reviews-' . $widget_id;
		$script    = BITHUB_REVIEWS_SERVICE_BASE . '/widgets/' . rawurlencode( $widget_id ) . '.js';
		$handle    = 'bithub-reviews-widget-' . strtolower( $widget_id );

		wp_enqueue_script(
			$handle,
			$script,
			array(),
			BITHUB_REVIEWS_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'async',
			)
		);

		return sprintf(
			'<div id="%1$s" class="bithub-reviews-widget-host"></div>',
			esc_attr( $target_id )
		);
	}

	/**
	 * Validate a service widget id before using it in markup or a remote URL.
	 *
	 * @param mixed $widget_id Widget id.
	 * @return bool
	 */
	public static function valid_widget_id( $widget_id ) {
		return is_string( $widget_id ) && 1 === preg_match( '/^[A-Za-z0-9_-]{3,80}$/', $widget_id );
	}
}
