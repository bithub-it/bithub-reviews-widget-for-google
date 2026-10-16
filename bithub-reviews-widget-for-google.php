<?php
/**
 * Plugin Name: BitHub Reviews Widget for Google
 * Plugin URI: https://bithub.it/plugin-wordpress/
 * Description: Connect a Google Maps business to the BitHub Reviews service and display its review widget with a shortcode or block.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Author: BitHub
 * Author URI: https://bithub.it/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: bithub-reviews-widget-for-google
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BITHUB_REVIEWS_VERSION', '1.0.0' );
define( 'BITHUB_REVIEWS_FILE', __FILE__ );
define( 'BITHUB_REVIEWS_DIR', plugin_dir_path( __FILE__ ) );
define( 'BITHUB_REVIEWS_URL', plugin_dir_url( __FILE__ ) );
define( 'BITHUB_REVIEWS_OPTION', 'bithub_reviews_widget_settings' );
define( 'BITHUB_REVIEWS_SERVICE_BASE', 'https://extractor.able2code.com' );
define( 'BITHUB_REVIEWS_SERVICE_PAGE', 'https://bithub.it/strumenti/google-reviews-widget/' );
define( 'BITHUB_REVIEWS_DOCS_URL', 'https://bithub.it/plugin-wordpress/' );
define( 'BITHUB_REVIEWS_PRIVACY_URL', 'https://bithub.it/privacy-cookie/' );

require_once BITHUB_REVIEWS_DIR . 'includes/class-bithub-reviews-api-client.php';
require_once BITHUB_REVIEWS_DIR . 'includes/class-bithub-reviews-renderer.php';
require_once BITHUB_REVIEWS_DIR . 'includes/class-bithub-reviews-admin.php';
require_once BITHUB_REVIEWS_DIR . 'includes/class-bithub-reviews-privacy.php';

register_activation_hook( __FILE__, array( 'BitHub_Reviews_Renderer', 'activate' ) );

add_action(
	'plugins_loaded',
	static function() {
		BitHub_Reviews_Renderer::instance();
		BitHub_Reviews_Admin::instance();
		BitHub_Reviews_Privacy::instance();
	}
);
