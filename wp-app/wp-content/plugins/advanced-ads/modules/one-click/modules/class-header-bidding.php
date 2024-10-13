<?php
/**
 * The class is responsible to inject header bidding tags.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Modules\OneClick;

use AdvancedAds\Framework\Utilities\Str;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Header bidding tags.
 */
class Header_Bidding implements Integration_Interface {

	/**
	 * Hook into WordPress
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_filter( 'pubguru_page_script_tag', [ $this, 'remove_tags' ] );
		add_filter( 'pubguru_current_page', [ $this, 'add_tags' ] );
	}

	/**
	 * Add TrafficCop tags
	 *
	 * @param string $page Page html.
	 *
	 * @return string
	 */
	public function add_tags( $page ): string {
		$page = $this->add_script_tag( $page );
		return $page;
	}

	/**
	 * Remove script tag
	 *
	 * @param string $script Scrip tag.
	 *
	 * @return string
	 */
	public function remove_tags( $script ): string {

		if ( Str::contains( '/gpt.js', $script ) ) {
			$script = '';
		}

		if ( Str::contains( '//m2d.m2.ai/', $script ) || Str::contains( '//c.pubguru.net/', $script ) ) {
			$script = '';
		}

		if ( Str::contains( 'window.pg.atq = window.pg.atq || [];', $script ) ) {
			$script = '';
		}

		return $script;
	}

	/**
	 * Guard the page from getting JS errors
	 *
	 * @param string $page Page html.
	 *
	 * @return string
	 */
	private function add_script_tag( $page ): string {
		$name    = Helpers::get_config_file();
		$at_body = Options::module( 'header_bidding_at_body' );

		if ( $name ) {
			$script = [];
			if ( ! $at_body ) {
				$script[] = '<head>';
			}

			$script[] = '<script src="https://securepubads.g.doubleclick.net/tag/js/gpt.js" async></script>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
			$script[] = '<script type="text/javascript">window.googletag=window.googletag||{};window.googletag.cmd=window.googletag.cmd||[];';
			$script[] = 'window.googletag.cmd.push(function(){window.__onpageGptEmbed=(new Date()).getTime()})</script>';
			$script[] = sprintf( '<script src="//c.pubguru.net/%s" async> </script>', $name ); // phpcs:ignore

			if ( Options::module( 'traffic_cop' ) && Helpers::has_traffic_cop() ) {
				$script[] = '<script>window.pg = window.pg || {};window.pg.atq = window.pg.atq || [];</script>';
			}

			if ( $at_body ) {
				$page = $page . join( "\n", $script );
			} else {
				$page = str_replace(
					'<head>',
					join( "\n", $script ),
					$page
				);
			}
		}

		return $page;
	}
}
