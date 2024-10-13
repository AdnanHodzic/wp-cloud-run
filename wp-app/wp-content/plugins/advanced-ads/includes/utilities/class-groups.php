<?php
/**
 * Groups utility functions.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Utilities;

use Advanced_Ads_Group;

defined( 'ABSPATH' ) || exit;

/**
 * Groups.
 */
class Groups {

	/**
	 * Build html for group hints.
	 *
	 * @param Advanced_Ads_Group $group Group instance.
	 *
	 * @return string
	 */
	public static function build_hints_html( $group ): string {
		$hints_html = '';
		foreach ( Advanced_Ads_Group::get_hints( $group ) as $hint ) {
			$hints_html .= '<p class="advads-notice-inline advads-error">' . $hint . '</p>';
		}

		return $hints_html;
	}
}
