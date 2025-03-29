<?php
/**
 * AAWP Compatibility.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Compatibility;

use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * AAWP.
 */
class AAWP implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_filter( 'advanced-ads-ad-types', [ $this, 'ad_type' ], 30 );
	}

	/**
	 * Add AAWP ad type to Advanced Ads.
	 *
	 * @param array $types ad types.
	 *
	 * @return array
	 */
	public function ad_type( $types ): array {
		if ( isset( $types['aawp'] ) && 'Advanced_Ads_Ad_Type_Abstract' === get_parent_class( $types['aawp'] ) ) {
			$advanced_ads_aawp = $types['aawp'];
			unset( $types['aawp'] );

			ob_start();
			$advanced_ads_aawp->render_icon( null );
			$icon = ob_get_clean();

			$types['aawp'] = [
				'id'                => 'aawp',
				'title'             => $advanced_ads_aawp->title,
				'description'       => $advanced_ads_aawp->description,
				'is_upgrade'        => false,
				'icon'              => $icon,
				'classname'         => AAWP_Ad::class,
				'render_parameters' => [ $advanced_ads_aawp, 'render_parameters' ],
			];
		}

		return $types;
	}
}
