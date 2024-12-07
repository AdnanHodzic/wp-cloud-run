<?php
/**
 * The class is responsible for the one-click module workflow.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Modules\OneClick;

use AdvancedAds\Constants;
use AdvancedAds\Importers\Api_Ads;
use AdvancedAds\Modules\OneClick\Helpers;
use AdvancedAds\Modules\OneClick\Options;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Modules\OneClick\Traffic_Cop;
use AdvancedAds\Modules\OneClick\AdsTxt\AdsTxt;
use AdvancedAds\Modules\OneClick\AdsTxt\Detector;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Workflow.
 */
class Workflow implements Integration_Interface {

	/**
	 * Flush rules option key.
	 *
	 * @var string
	 */
	const FLUSH_KEY = 'pubguru_flush_rewrite_rules';

	/**
	 * Hook into WordPress
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'init', [ $this, 'flush_rewrite_rules' ], 999 );
		add_filter( 'pubguru_module_status_changed', [ $this, 'module_status_changed' ], 10, 3 );
		add_action( Constants::CRON_API_ADS_CREATION, [ $this, 'auto_ad_creation' ], 10, 0 );

		if ( false !== Options::pubguru_config() && ! is_admin() ) {
			add_action( 'wp', [ $this, 'init' ] );

			if ( Options::module( 'ads_txt' ) ) {
				( new AdsTxt() )->hooks();
			}

			if ( ! function_exists( 'wp_advads_pro' ) ) {
				add_filter( 'advanced-ads-placement-content-offsets', [ $this, 'placement_content_offsets' ], 10, 3 );
			}
		}

		if ( Options::module( 'ads_txt' ) ) {
			if ( is_admin() ) {
				( new Detector() )->hooks();
			}
			remove_action( 'advanced-ads-plugin-loaded', 'advanced_ads_ads_txt_init' );
		}
	}

	/**
	 * Init workflow
	 *
	 * @return void
	 */
	public function init(): void {
		// Early bail!!
		$is_debugging = Params::get( 'aa-debug', false, FILTER_VALIDATE_BOOLEAN );

		if ( ! $is_debugging && Helpers::is_ad_disabled() ) {
			return;
		}
		Page_Parser::get_instance();

		if ( $is_debugging || Options::module( 'header_bidding' ) ) {
			( new Header_Bidding() )->hooks();
		}

		if ( $is_debugging || Options::module( 'tag_conversion' ) ) {
			( new Tags_Conversion() )->hooks();
		}

		if ( Options::module( 'traffic_cop' ) && Helpers::has_traffic_cop() ) {
			if ( ! Options::module( 'header_bidding' ) ) {
				( new Header_Bidding() )->hooks();
			}

			( new Traffic_Cop() )->hooks();
		}
	}

	/**
	 * Handle module status change
	 *
	 * @param array  $data   Data to send back to ajax request.
	 * @param string $module Module name.
	 * @param bool   $status Module status.
	 *
	 * @return array
	 */
	public function module_status_changed( $data, $module, $status ): array {
		if ( 'ads_txt' === $module ) {
			$detector = new Detector();
			if ( $status && $detector->detect_files() ) {
				ob_start();
				$detector->show_notice();
				$notice = ob_get_clean();
				if ( $notice ) {
					$data['notice'] = $notice;
				}
			}

			if ( ! $status && $detector->detect_files( 'ads.txt.bak' ) ) {
				$detector->revert_file();
			}

			update_option( self::FLUSH_KEY, 1 );
		}

		return $data;
	}

	/**
	 * Flush the rewrite rules once if the pubguru_flush_rewrite_rules option is set
	 *
	 * @return void
	 */
	public function flush_rewrite_rules(): void {
		if ( get_option( self::FLUSH_KEY ) ) {
			flush_rewrite_rules();
			delete_option( self::FLUSH_KEY );
		}
	}

	/**
	 * Start auto ad creation process
	 *
	 * @return void
	 */
	public function auto_ad_creation(): void {
		( new Api_Ads() )->import();
	}

	/**
	 * Get offsets for Content placement.
	 *
	 * @param array $offsets        Existing Offsets.
	 * @param array $options        Injection options.
	 * @param array $placement_opts Placement options.
	 *
	 * @return array $offsets New offsets.
	 */
	public function placement_content_offsets( $offsets, $options, $placement_opts ) {
		if ( ! isset( $options['paragraph_count'] ) ) {
			return $offsets;
		}

		// "Content" placement, repeat position.
		if (
			( ! empty( $placement_opts['repeat'] ) || ! empty( $options['repeat'] ) ) &&
			isset( $options['paragraph_id'] ) &&
			isset( $options['paragraph_select_from_bottom'] )
		) {

			$offsets = [];
			for ( $i = $options['paragraph_id'] - 1; $i < $options['paragraph_count']; $i++ ) {
				// Select every X number.
				if ( 0 === ( $i + 1 ) % $options['paragraph_id'] ) {
					$offsets[] = $options['paragraph_select_from_bottom'] ? $options['paragraph_count'] - 1 - $i : $i;
				}
			}
		}

		return $offsets;
	}
}
