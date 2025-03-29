<?php
/**
 * Shortcodes.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds;

use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Shortcodes.
 */
class Shortcodes implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_shortcode( 'the_ad', [ $this, 'render_ad' ] );
		add_shortcode( 'the_ad_group', [ $this, 'render_group' ] );
		add_shortcode( 'the_ad_placement', [ $this, 'render_placement' ] );
	}

	/**
	 * Renders an ad based on the provided attributes.
	 *
	 * @param array $atts The attributes for the ad.
	 *
	 * @return string The rendered ad.
	 */
	public function render_ad( $atts ): string {
		$ad = wp_advads_get_ad( absint( $atts['id'] ?? 0 ) );
		// Early bail!!
		if ( ! $ad ) {
			return '';
		}

		$atts = is_array( $atts ) ? $atts : [];

		// Check if there is an inline attribute with or without value.
		if ( isset( $atts['inline'] ) || in_array( 'inline', $atts, true ) ) {
			$atts['inline_wrapper_element'] = true;
		}

		$atts = $this->prepare_shortcode_atts( $atts );

		$this->set_shortcode_atts( $ad, $atts );

		return get_the_ad( $ad, '', $atts );
	}

	/**
	 * Renders a group based on the provided attributes.
	 *
	 * @param array $atts The attributes for the group.
	 *
	 * @return string The rendered group.
	 */
	public function render_group( $atts ): string {
		$group = wp_advads_get_group( absint( $atts['id'] ?? 0 ) );
		// Early bail!!
		if ( ! $group ) {
			return '';
		}

		$atts = is_array( $atts ) ? $atts : [];
		$atts = $this->prepare_shortcode_atts( $atts );

		$this->set_shortcode_atts( $group, $atts );

		return get_the_group( $group, '', $atts );
	}

	/**
	 * Renders a placement based on the provided attributes.
	 *
	 * @param array $atts The attributes for the placement.
	 *
	 * @return string The rendered placement.
	 */
	public function render_placement( $atts ): string {
		$placement = wp_advads_get_placement( (string) ( $atts['id'] ?? '' ) );
		// Early bail!!
		if ( ! $placement ) {
			return '';
		}

		$atts = is_array( $atts ) ? $atts : [];
		$atts = $this->prepare_shortcode_atts( $atts );

		$this->set_shortcode_atts( $placement, $atts );

		return get_the_placement( $placement, '', $atts );
	}

	/**
	 * Prepare shortcode attributes.
	 *
	 * @param array $atts array with strings.
	 *
	 * @return array
	 */
	private function prepare_shortcode_atts( $atts ): array {
		$result = [];

		/**
		 * Prepare attributes by converting strings to multi-dimensional array
		 * Example: [ 'output__margin__top' => 1 ]  =>  ['output']['margin']['top'] = 1
		 */
		if ( ! defined( 'ADVANCED_ADS_DISABLE_CHANGE' ) || ! ADVANCED_ADS_DISABLE_CHANGE ) {
			foreach ( $atts as $attr => $data ) {
				// Remove the prefix (change-ad__, change-group__, change-placement__).
				$attr   = preg_replace( '/^(change-ad|change-group|change-placement)__/', '', $attr );
				$levels = explode( '__', $attr );
				$last   = array_pop( $levels );

				$cur_lvl = &$result;

				foreach ( $levels as $lvl ) {
					if ( ! isset( $cur_lvl[ $lvl ] ) ) {
						$cur_lvl[ $lvl ] = [];
					}

					$cur_lvl = &$cur_lvl[ $lvl ];
				}

				$cur_lvl[ $last ] = $data;
			}

			$result = array_diff_key(
				$result,
				[
					'id'      => false,
					'blog_id' => false,
					'ad_args' => false,
				]
			);
		}

		// Ad type: 'content' and a shortcode inside.
		if ( isset( $atts['ad_args'] ) ) {
			$result = array_merge( $result, json_decode( urldecode( $atts['ad_args'] ), true ) );
		}

		// Flat output array for Ad.
		if ( isset( $result['output'] ) && is_array( $result['output'] ) ) {
			$result = array_merge( $result, $result['output'] );
			unset( $result['output'] );
		}

		// Special cases.
		if ( isset( $result['tracking']['link'] ) ) {
			$result['url'] = $result['tracking']['link'];
			unset( $result['tracking']['link'] );
		}

		return $result;
	}

	/**
	 * Set shortcode attributes.
	 *
	 * @param object $entity The entity object.
	 * @param array  $atts   The attributes to set for the entity.
	 *
	 * @return void
	 */
	private function set_shortcode_atts( $entity, $atts ): void {
		foreach ( $atts as $key => $value ) {
			$entity->set_prop_temp( $key, $value );
		}
	}
}
