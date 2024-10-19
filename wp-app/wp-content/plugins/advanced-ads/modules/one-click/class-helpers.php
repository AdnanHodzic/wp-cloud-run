<?php
/**
 * Helpers.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Modules\OneClick;

use AdvancedAds\Constants;
use AdvancedAds\Utilities\WordPress;
use AdvancedAds\Framework\Utilities\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Helpers.
 */
class Helpers {

	/**
	 * Check if config has traffic cop subscription
	 *
	 * @param array $config Config instance.
	 *
	 * @return bool
	 */
	public static function has_traffic_cop( $config = null ): bool {
		if ( null === $config ) {
			$config = Options::pubguru_config();
		}

		if (
			isset( $config['params'] ) &&
			( isset( $config['params']['trafficCopIvtAction'] ) && 'block' === $config['params']['trafficCopIvtAction'] ) &&
			( isset( $config['params']['trafficCopTestPercent'] ) && $config['params']['trafficCopTestPercent'] > 0.01 )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Get config file
	 *
	 * Cases
	 *  1. For auto_created
	 *  2. For mobile prefix
	 *  3. For desktop prefix
	 *  4. If none of the prefix is found than go with the first one
	 *  5. No configuration found => pg.{domain}.js
	 *
	 * @return bool|string
	 */
	public static function get_config_file() {
		static $pubguru_config_name;

		if ( null !== $pubguru_config_name ) {
			return $pubguru_config_name;
		}

		$pubguru_config_name = false;
		$configs             = Options::pubguru_config();

		// 1. For auto_created  => pg.{domain}_auto_created.js
		foreach ( $configs['configs'] as $config ) {
			if ( isset( $config['auto_created'] ) && $config['auto_created'] ) {
				$pubguru_config_name = $config['name'];
				return $pubguru_config_name;
			}
		}

		// 5. No configuration found => pg.{domain}.js
		if ( ! isset( $configs['configs'] ) || empty( $configs['configs'] ) ) {
			$domain = WordPress::get_site_domain( 'name' );
			return "pg.{$domain}.js";
		}

		$pubguru_config_name = wp_is_mobile()
			// 2. For mobile prefix
			? self::config_contains( 'mobile' )
			// 3. For desktop prefix
			: self::config_contains( 'desktop' );
		$pubguru_config_name = false !== $pubguru_config_name
			? $pubguru_config_name
			// 4. If none of the prefix is found than go with the first one
			: $configs['configs'][0]['name'];

		return $pubguru_config_name;
	}

	/**
	 * Find config name by needle
	 *
	 * @param string $needle Needle to look into config name.
	 *
	 * @return bool|string
	 */
	private static function config_contains( $needle ) {
		$configs = Options::pubguru_config();

		foreach ( $configs['configs'] as $config ) {
			if ( Str::contains( $needle, $config['name'] ) ) {
				return $config['name'];
			}
		}

		return false;
	}

	/**
	 * Start auto ad creation
	 *
	 * @return void
	 */
	public static function start_auto_ad_creation(): void {
		$ads = self::get_ads_from_config();

		if ( $ads && ! wp_next_scheduled( Constants::CRON_API_ADS_CREATION ) ) {
			wp_schedule_single_event( current_datetime()->getTimestamp() + 5, Constants::CRON_API_ADS_CREATION );
		}
	}

	/**
	 * Get ads from saved config.
	 *
	 * In this order
	 *   1. Get ads from auto_created = true
	 *   2. Get ads from first config
	 *   3. Get ads from default config
	 *
	 * @return bool|array
	 */
	public static function get_ads_from_config() {
		static $pubguru_config_ads;
		$config = Options::pubguru_config();

		if ( null !== $pubguru_config_ads ) {
			return $pubguru_config_ads;
		}

		$pubguru_config_ads = false;

		// 1. Get ads from auto_created = true
		foreach ( $config['configs'] as $config ) {
			if ( isset( $config['auto_created'] ) && $config['auto_created'] ) {
				$pubguru_config_ads = $config['ad_units'];
				return $pubguru_config_ads;
			}
		}

		// 2. Get ads from first config
		if ( isset( $config['configs'][0]['ad_units'] ) && ! empty( $config['configs'][0]['ad_units'] ) ) {
			$pubguru_config_ads = $config['configs'][0]['ad_units'];
		} else {
			// 3. Get ads from default config
			$domain             = WordPress::get_site_domain();
			$pubguru_config_ads = [
				$domain . '_leaderboard'  => [
					'ad_unit'              => $domain . '_leaderboard',
					'slot'                 => $domain . '_leaderboard',
					'device'               => 'All',
					'placement'            => 'Before Content',
					'placement_conditions' => 'All',
					'in_content_position'  => 'Before',
					'in_content_count'     => 1,
					'in_content_element'   => 'p',
					'in_content_repeat'    => false,
				],
				$domain . '_in_content_1' => [
					'ad_unit'              => $domain . '_in_content_1',
					'slot'                 => $domain . '_in_content_1',
					'device'               => 'All',
					'placement'            => 'Before Content',
					'placement_conditions' => 'All',
					'in_content_position'  => 'Before',
					'in_content_count'     => 1,
					'in_content_element'   => 'p',
					'in_content_repeat'    => false,
				],
				$domain . '_in_content_2' => [
					'ad_unit'              => $domain . '_in_content_2',
					'slot'                 => $domain . '_in_content_2',
					'device'               => 'All',
					'placement'            => 'After Content',
					'placement_conditions' => 'All',
					'in_content_position'  => 'Aefore',
					'in_content_count'     => 3,
					'in_content_element'   => 'p',
					'in_content_repeat'    => true,
				],
			];
		}

		return $pubguru_config_ads;
	}

	/**
	 * Is ad disabled on page
	 *
	 * @param int $post_id Post id to check for.
	 *
	 * @return bool
	 */
	public static function is_ad_disabled( $post_id = 0 ): bool {
		global $post;

		if ( ! $post ) {
			return false;
		}

		if ( ! $post_id ) {
			$post_id = $post->ID;
		}

		$settings = get_post_meta( $post_id, '_advads_ad_settings', true );

		return is_singular() ? ! empty( $settings['disable_ads'] ) : false;
	}
}
