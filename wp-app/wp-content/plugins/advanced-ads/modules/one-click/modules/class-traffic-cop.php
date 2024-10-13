<?php
/**
 * The class is responsible to wrap the google tags into traffic cop wrappers.
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
 * Traffic Cop.
 */
class Traffic_Cop implements Integration_Interface {

	/**
	 * Hook into WordPress
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_filter( 'pubguru_page_script_tag', [ $this, 'modify_script_tag' ], 30 );
	}

	/**
	 * Modify scrip if google tag found
	 *
	 * @param mixed $content Scrip tag.
	 *
	 * @return bool|string
	 */
	public function modify_script_tag( $content ) {
		// Early bail!!
		if ( ! Str::contains( 'googletag.display', $content ) ) {
			return false;
		}

		$content = str_replace( '<script>', '<script>pg.atq.push(function() {', $content );
		$content = str_replace( '</script>', '});</script>', $content );

		return $content;
	}
}
