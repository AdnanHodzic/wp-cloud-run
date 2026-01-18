<?php

namespace SPC\Utils;

use SPC\Constants;
use SPC\Services\Settings_Store;

class Cache_Tester {

	/**
	 * Test cache functionality and return structured response
	 *
	 * @return array API-friendly response array
	 */
	public function test(): array {

		/**
		 * @var \SW_CLOUDFLARE_PAGECACHE
		 */
		global $sw_cloudflare_pagecache;

		/**
		 * @var string
		 */
		$test_file_url = SWCFPC_PLUGIN_URL . 'assets/testcache.html';
		$tester        = new \SWCFPC_Test_Cache( $test_file_url );

		$settings = Settings_Store::get_instance();

		$is_disk_cache_enabled = $settings->get( Constants::SETTING_ENABLE_FALLBACK_CACHE );

		$is_cloudflare_enabled = (
			! empty( $settings->get( Constants::RULE_ID_PAGE ) ) ||
			! empty( $settings->get( Constants::RULESET_ID_CACHE ) ) ||
			! empty( $settings->get( Constants::RULE_ID_CACHE ) )
		);

		$results = [
			'overall_status' => 'success',
			'cloudflare'     => $this->test_cloudflare_cache( $tester, $is_cloudflare_enabled ),
			'disk_cache'     => $this->test_disk_cache( $sw_cloudflare_pagecache, $is_disk_cache_enabled, $test_file_url ),
			'test_url'       => $test_file_url,
			'configuration'  => [
				'cloudflare_enabled' => $is_cloudflare_enabled,
				'disk_cache_enabled' => $is_disk_cache_enabled,
			],
		];

		// Determine overall status
		$has_errors = false;
		if ( $results['cloudflare']['status'] === 'error' || $results['disk_cache']['status'] === 'error' ) {
			$has_errors = true;
		}

		if ( ! $is_cloudflare_enabled && ! $is_disk_cache_enabled ) {
			$has_errors                = true;
			$results['overall_status'] = 'error';
			$results['message']        = __( 'No caching method is enabled.', 'wp-cloudflare-page-cache' );
		} elseif ( $has_errors ) {
			$results['overall_status'] = 'error';
		}

		return $results;
	}

	/**
	 * Test Cloudflare cache functionality
	 *
	 * @param \SWCFPC_Test_Cache $tester
	 * @param bool $is_enabled
	 * @return array
	 */
	private function test_cloudflare_cache( $tester, bool $is_enabled ): array {
		if ( ! $is_enabled ) {
			return [
				'status'  => 'disabled',
				'message' => __( 'Cloudflare Cache Rule is not enabled.', 'wp-cloudflare-page-cache' ),
				'errors'  => [],
			];
		}

		if ( ! $tester->check_cloudflare_cache() ) {
			return [
				'status'               => 'error',
				'message'              => __( 'Cloudflare integration has an issue.', 'wp-cloudflare-page-cache' ),
				'errors'               => $tester->get_errors(),
				'troubleshooting_note' => __( 'Please check if the page caching is working by yourself by surfing the website in incognito mode \'cause sometimes Cloudflare bypass the cache for cURL requests. Reload a page two or three times. If you see the response header cf-cache-status: HIT, the page caching is working well.', 'wp-cloudflare-page-cache' ),
			];
		}

		return [
			'status'  => 'success',
			'message' => __( 'Cloudflare Page Caching is working properly.', 'wp-cloudflare-page-cache' ),
			'errors'  => [],
		];
	}

	/**
	 * Test disk cache functionality
	 *
	 * @param \SW_CLOUDFLARE_PAGECACHE $sw_cloudflare_pagecache
	 * @param bool $is_enabled
	 * @param string $test_file_url
	 * @return array
	 */
	private function test_disk_cache( $sw_cloudflare_pagecache, bool $is_enabled, string $test_file_url ): array {
		if ( ! $is_enabled ) {
			return [
				'status'  => 'disabled',
				'message' => __( 'Disk Page Cache is not enabled.', 'wp-cloudflare-page-cache' ),
				'errors'  => [],
			];
		}

		/**
		 * @var \SWCFPC_Fallback_Cache $fallback_cache
		 */
		$fallback_cache = $sw_cloudflare_pagecache->get_modules()['fallback_cache'];

		$fallback_cache->fallback_cache_add_current_url_to_cache( $test_file_url, true );
		$cache_success = $fallback_cache->fallback_cache_check_cached_page( $test_file_url );

		if ( ! $cache_success ) {
			return [
				'status'  => 'error',
				'message' => __( 'Disk Page Caching has an issue.', 'wp-cloudflare-page-cache' ),
				'errors'  => [ __( 'Could not cache the page on the disk. [Page Disk Cache]', 'wp-cloudflare-page-cache' ) ],
			];
		}

		return [
			'status'  => 'success',
			'message' => __( 'Disk Page Caching is functional.', 'wp-cloudflare-page-cache' ),
			'errors'  => [],
		];
	}

	/**
	 * Generate HTML representation of test results (for backwards compatibility)
	 *
	 * @param array $results
	 * @return string
	 */
	public function generate_html_response( array $results ): string {
		$html_response = '<div class="swcfpc-test-response">';

		// Status section
		$html_response .= '<div class="test-container">';
		$html_response .= '<h3>' . __( 'Status', 'wp-cloudflare-page-cache' ) . '</h3>';
		$html_response .= '<ul>';

		// Add status messages for each component
		foreach ( [ 'cloudflare', 'disk_cache' ] as $component ) {
			$component_result = $results[ $component ];
			$status_class     = $component_result['status'] === 'success' ? 'success' : ( $component_result['status'] === 'error' ? 'error' : 'info' );

			$html_response .= '<li class="is-' . $status_class . '">' . $component_result['message'] . '</li>';
		}

		$html_response .= '</ul>';
		$html_response .= '</div>';

		// Issues section
		$all_errors = array_merge( $results['cloudflare']['errors'], $results['disk_cache']['errors'] );

		if ( ! empty( $all_errors ) ) {
			$html_response .= '<div class="test-container">';
			$html_response .= '<h3>' . __( 'Issues', 'wp-cloudflare-page-cache' ) . '</h3>';
			$html_response .= '<ul>';

			foreach ( $all_errors as $error ) {
				$html_response .= '<li class="is-error">' . $error . '</li>';
			}

			$html_response .= '</ul>';

			// Add troubleshooting notes
			if ( $results['cloudflare']['status'] === 'error' && isset( $results['cloudflare']['troubleshooting_note'] ) ) {
				$html_response .= '<p>' . $results['cloudflare']['troubleshooting_note'] . '</p>';
			}

			if ( $results['configuration']['cloudflare_enabled'] ) {
				$html_response .= '<p><a href="' . esc_url( $results['test_url'] ) . '" target="_blank">' . __( 'Cloudflare Test Page', 'wp-cloudflare-page-cache' ) . '</a></p>';
			}

			$html_response .= '</div>';
		}

		$html_response .= '</div>';

		return $html_response;
	}

	/**
	 * Legacy method that returns the old format for backwards compatibility
	 *
	 * @return array
	 */
	public function test_legacy(): array {
		$results = $this->test();

		return [
			'status' => $results['overall_status'] === 'success' ? 'ok' : 'error',
			'html'   => $this->generate_html_response( $results ),
		];
	}
}
