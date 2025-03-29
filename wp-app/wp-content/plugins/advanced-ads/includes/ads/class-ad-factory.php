<?php
/**
 * The ad factory.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Ads;

use Exception;
use AdvancedAds\Constants;
use AdvancedAds\Abstracts\Ad;
use AdvancedAds\Abstracts\Factory;

defined( 'ABSPATH' ) || exit;

/**
 * Ads Factory.
 */
class Ad_Factory extends Factory {

	/**
	 * Create an empty ad object
	 *
	 * @param string $type Type of ad to create.
	 *
	 * @return Ad|bool Ad object or false if the ad type not found.
	 */
	public function create_ad( $type = 'dummy' ) {
		$ad_type = wp_advads_get_ad_type( $type );

		if ( ! $ad_type ) {
			return false;
		}

		$classname = $ad_type->get_classname();

		// Create ad.
		$ad = new $classname( 0 );
		$ad->set_type( $ad_type->get_id() );

		return $ad;
	}

	/**
	 * Get the ad object.
	 *
	 * @param Ad|WP_Post|int|bool $ad_id    Ad instance, post instance, numeric or false to use global $post.
	 * @param string              $new_type Change type of ad.
	 *
	 * @return Ad|bool Ad object or false if the ad cannot be loaded.
	 */
	public function get_ad( $ad_id, $new_type = '' ) {
		$ad_id = $this->get_ad_id( $ad_id );

		if ( ! $ad_id ) {
			return false;
		}

		$ad_type   = '' !== $new_type ? $new_type : $this->get_ad_type( $ad_id );
		$classname = $this->get_classname( wp_advads_get_ad_type_manager(), $ad_type, 'dummy' );

		try {
			return new $classname( $ad_id );
		} catch ( Exception $e ) {
			return false;
		}

		return new Ad_Content();
	}

	/**
	 * Get the type of the ad.
	 *
	 * @param int $ad_id Ad ID.
	 *
	 * @return string The type of the ad.
	 */
	private function get_ad_type( $ad_id ): string {
		// Allow the overriding of the lookup in this function. Return the ad type here.
		$override = apply_filters( 'advanced-ads-ad-type', false, $ad_id );
		if ( $override ) {
			return $override;
		}

		$options = get_post_meta( $ad_id, Ad_Repository::OPTION_METAKEY, true );
		return $options['type'] ?? 'dummy';
	}

	/**
	 * Get the ad ID depending on what was passed.
	 *
	 * @param Ad|WP_Post|int|bool $ad Ad instance, post instance, numeric or false to use global $post.
	 *
	 * @return int|bool false on failure
	 */
	private function get_ad_id( $ad ) {
		global $post;

		if ( false === $ad && isset( $post, $post->ID ) && Constants::POST_TYPE_AD === get_post_type( $post->ID ) ) {
			return absint( $post->ID );
		}

		if ( is_numeric( $ad ) ) {
			return $ad;
		}

		if ( is_an_ad( $ad ) ) {
			return $ad->get_id();
		}

		if ( ! empty( $ad->ID ) ) {
			return $ad->ID;
		}

		return false;
	}
}
