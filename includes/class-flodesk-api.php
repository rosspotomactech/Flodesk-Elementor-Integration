<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flodesk_API {

	private $api_key;
	private $api_base = 'https://api.flodesk.com/v1/';

	public function __construct( $api_key = '' ) {
		$this->api_key = ! empty( $api_key ) ? $api_key : get_option( 'flodesk_api_key', '' );
	}

	private function get_headers() {
		return array(
			'Authorization' => 'Basic ' . base64_encode( $this->api_key . ':' ),
			'Content-Type'  => 'application/json',
			'User-Agent'    => 'WordPress-Elementor-Flodesk-Integration/' . get_bloginfo( 'version' ),
		);
	}

	public function upsert_subscriber( $payload ) {
		if ( empty( $this->api_key ) ) {
			return new WP_Error( 'missing_key', __( 'Flodesk API Key is missing.', 'flodesk-elementor' ) );
		}

		$response = wp_remote_post(
			$this->api_base . 'subscribers',
			array(
				'headers' => $this->get_headers(),
				'body'    => wp_json_encode( $payload ),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $status_code >= 400 ) {
			$message = isset( $body['message'] ) ? $body['message'] : __( 'Error communicating with Flodesk API.', 'flodesk-elementor' );
			return new WP_Error( 'api_error', $message, $body );
		}

		return $body;
	}

	/**
	 * Fetch segments and cache them for Elementor dropdowns.
	 */
	public function get_segments_options() {
		if ( empty( $this->api_key ) ) {
			return array();
		}

		// Check for cached segments to avoid hitting API rate limits
		$cached_segments = get_transient( 'flodesk_segments_options' );
		if ( false !== $cached_segments ) {
			return $cached_segments;
		}

		$response = wp_remote_get(
			$this->api_base . 'segments?per_page=100',
			array(
				'headers' => $this->get_headers(),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return array();
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$segments = isset( $body['data'] ) ? $body['data'] : array();

		$options = array();
		foreach ( $segments as $segment ) {
			// Save as [ id => name ] mapping
			$options[ $segment['id'] ] = $segment['name'];
		}

		// Cache for 1 hour
		set_transient( 'flodesk_segments_options', $options, HOUR_IN_SECONDS );

		return $options;
	}
}
