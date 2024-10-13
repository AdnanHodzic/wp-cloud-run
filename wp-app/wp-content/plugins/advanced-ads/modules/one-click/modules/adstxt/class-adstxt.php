<?php
/**
 * The class is responsible to redirect ads.txt to centralized location.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Modules\OneClick\AdsTxt;

use AdvancedAds\Utilities\WordPress;
use AdvancedAds\Framework\Interfaces\Integration_Interface;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Framework\Utilities\Str;

defined( 'ABSPATH' ) || exit;

/**
 * AdsTxt.
 */
class AdsTxt implements Integration_Interface {

	/**
	 * Hook into WordPress
	 *
	 * @return void
	 */
	public function hooks(): void {
		remove_action( 'advanced-ads-plugin-loaded', 'advanced_ads_ads_txt_init' );

		add_action( 'template_redirect', [ $this, 'handle_redirect' ] );
		add_filter( 'allowed_redirect_hosts', [ $this, 'allowed_redirect_hosts' ] );
	}

	/**
	 * Handle redirect
	 *
	 * @return void
	 */
	public function handle_redirect(): void {
		if (
			'ads-txt' !== get_query_var( 'name' ) ||
			Str::contains( Params::server( 'REQUEST_URI' ), 'ads.txt' )
		) {
			return;
		}

		$redirect = sprintf( 'https://adstxt.pubguru.net/pg/%s/ads.txt', WordPress::get_site_domain() );
		wp_safe_redirect( $redirect, 301 );
		exit;
	}

	/**
	 * Allowed redirect hosts
	 *
	 * @param array $hosts Array to hold allowed hosts.
	 *
	 * @return array
	 */
	public function allowed_redirect_hosts( $hosts ): array {
		$hosts[] = 'adstxt.pubguru.net';

		return $hosts;
	}
}
