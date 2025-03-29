<?php
/**
 * Extras functionality needed to be on the root level.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Traits;

use Advanced_Ads;
use AdvancedAds\Constants;
use AdvancedAds\Utilities\Sanitize;

defined( 'ABSPATH' ) || exit;

/**
 * Trait Extras.
 */
trait Extras {
	/**
	 * Frontend prefix for classes and IDs.
	 *
	 * @var string
	 */
	private $frontend_prefix = null;

	/**
	 * Get frontend prefix for classes and IDs.
	 *
	 * @return string
	 */
	public function get_frontend_prefix(): string {
		// Early bail!!
		if ( null !== $this->frontend_prefix ) {
			return $this->frontend_prefix;
		}

		$options = Advanced_Ads::get_instance()->options();

		if ( ! isset( $options['front-prefix'] ) ) {
			if ( isset( $options['id-prefix'] ) ) {
				// deprecated: keeps widgets working that previously received an id based on the front-prefix.
				$frontend_prefix = $options['id-prefix'];
			} else {
				$frontend_prefix = preg_match( '/[A-Za-z][A-Za-z0-9_]{4}/', wp_parse_url( get_home_url(), PHP_URL_HOST ), $result )
					? $result[0] . '-'
					: Constants::DEFAULT_FRONTEND_PREFIX;
			}
		} else {
			$frontend_prefix = $options['front-prefix'];
		}
		/**
		 * Applying the filter here makes sure that it is the same frontend prefix for all
		 * calls on this page impression
		 *
		 * @param string $frontend_prefix
		 */
		$this->frontend_prefix = (string) apply_filters( 'advanced-ads-frontend-prefix', $frontend_prefix );
		$this->frontend_prefix = Sanitize::frontend_prefix( $frontend_prefix );

		return $this->frontend_prefix;
	}
}
