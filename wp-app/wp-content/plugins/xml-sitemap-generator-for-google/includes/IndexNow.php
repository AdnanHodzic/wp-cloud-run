<?php

namespace GRIM_SG;

use GRIM_SG\Vendor\Controller;

class IndexNow extends Controller {
	private $site_url;
	private $api_key;
	private static $api_name = 'sgg_indexnow_api_key';

	public function __construct() {
		$this->set_site_url();
		$this->set_api_key();
	}

	public function ping_site_url() {
		return $this->ping_url( $this->site_url );
	}

	public function ping_url( $url ) {
		$response = $this->request( $url );

		return $this->handle_response( $response );
	}

	public function request( $index_url ) {
		$data = wp_json_encode(
			array(
				'host'        => $this->remove_url_scheme( $this->site_url ),
				'key'         => $this->api_key,
				'keyLocation' => $this->get_api_key_location(),
				'urlList'     => array( $index_url ),
			)
		);

		return wp_remote_post(
			'https://api.indexnow.org/indexnow/',
			array(
				'body'    => $data,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
			)
		);
	}

	private function handle_response( $response ) {
		$error = array(
			'status'  => 'error',
			'message' => __( 'IndexNow Protocol unknown error occurred', 'google-sitemap-generator' ),
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'status'  => 'error',
				'message' => $response->get_error_message(),
			);
		}

		if ( isset( $response['errors'] ) ) {
			return $error;
		}

		if ( ! empty( $response['response']['code'] ) ) {
			if ( in_array( $response['response']['code'], array( 200, 202 ), true ) ) {
				return array(
					'status'  => 'success',
					'message' => __( 'Changes successfully submitted to IndexNow', 'google-sitemap-generator' ),
				);
			} else {
				if ( 400 === $response['response']['code'] ) {
					$error['message'] = __( 'IndexNow Protocol Invalid Request', 'google-sitemap-generator' );
				} elseif ( 403 === $response['response']['code'] ) {
					$error['message'] = __( 'IndexNow Protocol Invalid Api Key', 'google-sitemap-generator' );
				} elseif ( 422 === $response['response']['code'] ) {
					$error['message'] = __( 'IndexNow Protocol Invalid URL', 'google-sitemap-generator' );
				} elseif ( ! empty( $response['response']['message'] ) ) {
					$error['message'] = sprintf(
						/* translators: %s: error message */
						__( 'IndexNow Protocol Error: %s', 'google-sitemap-generator' ),
						$response['response']['message']
					);
				}
			}
		}

		return $error;
	}

	public function get_api_key() {
		if ( ! empty( $this->api_key ) ) {
			return $this->api_key;
		}

		$api_key = is_multisite() ? get_site_option( self::$api_name ) : get_option( self::$api_name );

		if ( $api_key ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			return base64_decode( $api_key );
		}

		return apply_filters( 'sgg_indexnow_api_key', $api_key );
	}

	public function set_api_key() {
		$api_key = $this->get_api_key();

		if ( empty( $api_key ) ) {
			$api_key = preg_replace( '[-]', '', wp_generate_uuid4() );

			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			$base64_api_key = base64_encode( $api_key );

			if ( is_multisite() ) {
				update_site_option( self::$api_name, $base64_api_key );
			} else {
				update_option( self::$api_name, $base64_api_key );
			}
		}

		$this->api_key = $api_key;
	}

	public static function delete_api_key() {
		if ( is_multisite() ) {
			delete_site_option( self::$api_name );
		} else {
			delete_option( self::$api_name );
		}
	}

	public function set_site_url() {
		$this->site_url = get_home_url();
	}

	public function get_api_key_location() {
		return trailingslashit( $this->site_url ) . $this->api_key . '.txt';
	}

	public function remove_url_scheme( $url ) {
		return preg_replace( '/^https?:\/\//', '', $url );
	}
}
