<?php
/**
 * This class is serving as the base for placement types.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Abstracts;

defined( 'ABSPATH' ) || exit;

/**
 * Placement Type.
 */
class Placement_Type {

	/**
	 * Hold allowed ads for cache purpose.
	 *
	 * @var array
	 */
	private $allowed_ads = null;

	/**
	 * Hold allowed groups for cache purpose.
	 *
	 * @var array
	 */
	private $allowed_groups = null;

	/**
	 * Apply filters on options by type id.
	 *
	 * @param array $options Options array.
	 *
	 * @return array
	 */
	protected function apply_filter_on_options( $options ): array {
		$defaults = [
			'show_position'    => false,
			'show_lazy_load'   => false,
			'uses_the_content' => false,
			'amp'              => false,
		];
		$options  = wp_parse_args( $options, $defaults );

		return apply_filters(
			'advanced-ads-placement-' . $this->get_id() . '-options',
			$options
		);
	}

	/**
	 * Get allowed item array for dropdown
	 *
	 * @return array
	 */
	public function get_allowed_items(): array {
		return [
			'groups' => [
				'label' => __( 'Ad Groups', 'advanced-ads' ),
				'items' => $this->get_allowed_groups(),
			],
			'ads'    => [
				'label' => __( 'Ads', 'advanced-ads' ),
				'items' => $this->get_allowed_ads(),
			],
		];
	}

	/**
	 * Get all allowed ads for this placement type.
	 *
	 * @return array
	 */
	public function get_allowed_ads(): array {
		if ( null !== $this->allowed_ads ) {
			return $this->allowed_ads;
		}

		$this->allowed_ads = [];
		foreach ( wp_advads_get_all_ads() as $ad ) {
			if ( $this->is_ad_type_allowed( $ad->get_type() ) ) {
				$this->allowed_ads[ 'ad_' . $ad->get_id() ] = $ad->get_title() . ( 'draft' === $ad->get_status() ? ' (' . __( 'draft', 'advanced-ads' ) . ')' : '' );
			}
		}

		return $this->allowed_ads;
	}

	/**
	 * Get all allowed groups for this placement type.
	 *
	 * @return array
	 */
	public function get_allowed_groups(): array {
		if ( null !== $this->allowed_groups ) {
			return $this->allowed_groups;
		}

		$this->allowed_groups = [];

		$ads    = wp_advads_get_all_ads();
		$groups = wp_advads_get_all_groups();

		foreach ( $groups as $group ) {
			if ( ! $this->is_group_type_allowed( $group->get_type() ) ) {
				continue;
			}

			// Check if group has allowed ads.
			foreach ( $group->get_ads_ids() as $ad_id ) {
				if ( array_key_exists( $ad_id, $ads ) && $this->is_ad_type_allowed( $ads[ $ad_id ]->get_type() ) ) {
					$this->allowed_groups[ 'group_' . $group->get_id() ] = $group->get_name();
					break;
				}
			}
		}

		return $this->allowed_groups;
	}

	/**
	 * Check if the provided ad type is allowed
	 *
	 * @param string $type Ad type.
	 *
	 * @return bool
	 */
	public function is_ad_type_allowed( $type ): bool {
		return $this->is_entity_allowed( $type, 'ad' );
	}

	/**
	 * Check if the provided group type is allowed.
	 *
	 * @param string $type Group type.
	 *
	 * @return bool
	 */
	public function is_group_type_allowed( $type ): bool {
		return $this->is_entity_allowed( $type, 'group' );
	}

	/**
	 * Abstraction of whether entity is allowed.
	 *
	 * @param string $type   Type to check.
	 * @param string $entity Entity type i.e. `ad` or `group`.
	 *
	 * @return bool
	 */
	public function is_entity_allowed( $type, $entity ) {
		$options = $this->get_options();
		$allowed = $options[ 'allowed_' . $entity . '_types' ] ?? true;

		return true === $allowed ? true : in_array( $type, $allowed, true );
	}
}
