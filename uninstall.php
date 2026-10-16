<?php
/**
 * Uninstall BitHub Reviews Widget for Google.
 *
 * Remote BitHub service data is intentionally not deleted automatically.
 * Only this WordPress site's local plugin option is removed.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'bithub_reviews_widget_settings' );
