<?php
/**
 * Privacy policy helper text.
 *
 * @package BitHubReviewsWidget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class BitHub_Reviews_Privacy {
	/** @var BitHub_Reviews_Privacy|null */
	private static $instance = null;

	/** @return BitHub_Reviews_Privacy */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Constructor. */
	private function __construct() {
		add_action( 'admin_init', array( $this, 'add_policy_text' ) );
	}

	/**
	 * Add suggested privacy policy text in the WordPress privacy guide.
	 *
	 * @return void
	 */
	public function add_policy_text() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		$content  = '<p>';
		$content .= esc_html__( 'When the BitHub Reviews Widget is configured, administrators explicitly send the selected Google Maps business URL or Place ID, an email address, a Google Places API key, the site language and widget preferences to the BitHub Reviews service. The public widget is then loaded from the BitHub service when a page containing the shortcode or block is viewed.', 'bithub-reviews-widget-for-google' );
		$content .= '</p><p>';
		$content .= sprintf(
			/* translators: 1: service URL, 2: privacy URL. */
			wp_kses_post( __( 'Service documentation: <a href="%1$s">%1$s</a>. BitHub privacy information: <a href="%2$s">%2$s</a>.', 'bithub-reviews-widget-for-google' ) ),
			esc_url( BITHUB_REVIEWS_SERVICE_PAGE ),
			esc_url( BITHUB_REVIEWS_PRIVACY_URL )
		);
		$content .= '</p>';

		wp_add_privacy_policy_content(
			__( 'BitHub Reviews Widget for Google', 'bithub-reviews-widget-for-google' ),
			wp_kses_post( $content )
		);
	}
}
