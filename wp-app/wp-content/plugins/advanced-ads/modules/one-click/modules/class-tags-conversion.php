<?php
/**
 * The class is responsible to convert ad tags to pubguru tag.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Modules\OneClick;

use AdvancedAds\Modules\OneClick\Helpers;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Ad tags conversion.
 */
class Tags_Conversion implements Integration_Interface {

	/**
	 * Hook into WordPress
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_filter( 'pubguru_current_page', [ $this, 'convert_tags' ], 20 );
	}

	/**
	 * Convert tags
	 *
	 * @param string $page Page html.
	 *
	 * @return string
	 */
	public function convert_tags( $page ): string {
		$page = $this->convert_ad_by_config( $page );
		return $page;
	}

	/**
	 * Convert ad divs to pubguru tags
	 *
	 * @param string $page Page html.
	 *
	 * @return string
	 */
	private function convert_ad_by_config( $page ): string {
		$slots = $this->get_ad_slots();

		if ( false === $slots ) {
			return $page;
		}

		$divs = $this->get_ad_divs( $page, $slots );

		foreach ( $divs as $div ) {
			$replace = str_replace(
				[ '<div', '</div>' ],
				[ '<pubguru', '</pubguru>' ],
				$div
			);

			$page = str_replace( $div, $replace, $page );
		}

		return $page;
	}

	/**
	 * Get ad divs only
	 *
	 * @param string $page Page html.
	 * @param array  $slots Ad slots ids.
	 *
	 * @return array
	 */
	private function get_ad_divs( $page, $slots ): array {
		$matches  = [];
		$slot_ids = [];

		foreach ( $slots as $slot ) {
			$slot_ids[] = preg_quote( $slot, '/' );
		}
		$slot_ids = join( '|', $slot_ids );
		preg_match_all( '/<div id="(' . $slot_ids . ')"[^>]*>(.*?)<\/div>/mis', $page, $matches );

		return isset( $matches[0] ) ? $matches[0] : [];
	}

	/**
	 * Get slots from config
	 *
	 * @return bool|array
	 */
	private function get_ad_slots() {
		$ads = Helpers::get_ads_from_config();

		if ( false === $ads ) {
			return false;
		}

		$slots = [];
		foreach ( $ads as $ad ) {
			$slots[] = $ad['slot'];
		}

		return $slots;
	}
}
