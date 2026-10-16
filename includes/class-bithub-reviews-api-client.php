<?php
/**
 * BitHub Reviews service API client.
 *
 * @package BitHubReviewsWidget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class BitHub_Reviews_API_Client {
	/**
	 * Make a request to the BitHub Reviews service.
	 *
	 * @param string      $method HTTP method.
	 * @param string      $path   Service path beginning with /.
	 * @param array|null  $body   Optional JSON body.
	 * @param string|null $token  Optional management bearer token.
	 * @return array|WP_Error
	 */
	public static function request( $method, $path, $body = null, $token = null ) {
		$method = strtoupper( (string) $method );
		$path   = '/' . ltrim( (string) $path, '/' );
		$url    = BITHUB_REVIEWS_SERVICE_BASE . $path;

		$headers = array(
			'Accept' => 'application/json',
		);

		$args = array(
			'method'      => $method,
			'timeout'     => 25,
			'redirection' => 0,
			'headers'     => $headers,
			'user-agent'  => 'BitHub Reviews Widget for Google/' . BITHUB_REVIEWS_VERSION,
		);

		if ( null !== $body ) {
			$args['headers']['Content-Type'] = 'application/json; charset=utf-8';
			$args['body']                    = wp_json_encode( $body );
		}

		if ( is_string( $token ) && '' !== trim( $token ) ) {
			$args['headers']['Authorization'] = 'Bearer ' . trim( $token );
		}

		$response = wp_remote_request( $url, $args );
		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'bithub_reviews_service_unreachable',
				__( 'The BitHub Reviews service could not be reached. Please try again.', 'bithub-reviews-widget-for-google' )
			);
		}

		$status = (int) wp_remote_retrieve_response_code( $response );
		$raw    = wp_remote_retrieve_body( $response );
		$data   = json_decode( $raw, true );

		if ( ! is_array( $data ) ) {
			return new WP_Error(
				'bithub_reviews_invalid_response',
				__( 'The BitHub Reviews service returned an invalid response.', 'bithub-reviews-widget-for-google' ),
				array( 'status' => $status )
			);
		}

		if ( $status < 200 || $status >= 300 || ( isset( $data['ok'] ) && false === $data['ok'] ) ) {
			$message = isset( $data['message'] ) && is_string( $data['message'] )
				? sanitize_text_field( $data['message'] )
				: __( 'The request could not be completed.', 'bithub-reviews-widget-for-google' );

			$code = isset( $data['error'] ) && is_string( $data['error'] )
				? sanitize_key( $data['error'] )
				: 'bithub_reviews_service_error';

			return new WP_Error(
				$code,
				$message,
				array(
					'status'  => $status,
					'payload' => self::safe_error_payload( $data ),
				)
			);
		}

		return $data;
	}

	/**
	 * Remove fields that could contain credentials before attaching data to an error.
	 *
	 * @param array $payload Original payload.
	 * @return array
	 */
	private static function safe_error_payload( $payload ) {
		$allowed = array( 'ok', 'error', 'message', 'business_id', 'business_name', 'exists' );
		$safe    = array();
		foreach ( $allowed as $key ) {
			if ( array_key_exists( $key, $payload ) ) {
				$safe[ $key ] = is_scalar( $payload[ $key ] ) ? $payload[ $key ] : null;
			}
		}
		return $safe;
	}
}
