<?php

namespace Advanced_Ads;

/**
 * Class wrapper for placement types array.
 *
 * @property-read string                 $title
 * @property-read string                 $description
 * @property-read string                 $image
 * @property-read float                  $order
 * @property-read Placement_Type_Options $options
 */
class Placement_Type extends \ArrayObject {

	/**
	 * Placement type title.
	 *
	 * @var string
	 */
	private $title;

	/**
	 * Placement type description.
	 *
	 * @var string
	 */
	private $description = '';

	/**
	 * Admin UI image src.
	 *
	 * @var string
	 */
	private $image = '';

	/**
	 * Admin UI order for new placements.
	 *
	 * @var float
	 */
	private $order;

	/**
	 * A class to resolve the placement type options.
	 *
	 * @var Placement_Type_Options
	 */
	private $options;

	/**
	 * Compute all allowed ads once and assign them to this variable.
	 *
	 * @var array
	 */
	private $allowed_ads;

	/**
	 * Compute all allowed groups once and assign them to this variable.
	 *
	 * @var array
	 */
	private $allowed_groups;

	/**
	 * The placement type.
	 *
	 * @var string
	 */
	private $type;

	/**
	 * Assign simple placement definitions to properties.
	 * Instantiate Placement_Type_Options class.
	 *
	 * @param string $type                 The type of placement.
	 * @param array  $placement_definition The definition options for the placement.
	 */
	public function __construct( $type, array $placement_definition ) {
		$this->type = $type;

		if ( array_key_exists( 'title', $placement_definition ) ) {
			$this->title = $placement_definition['title'];
		}

		if ( array_key_exists( 'description', $placement_definition ) ) {
			$this->description = $placement_definition['description'];
		}

		if ( array_key_exists( 'image', $placement_definition ) ) {
			$this->image = $placement_definition['image'];
		}

		if ( array_key_exists( 'order', $placement_definition ) ) {
			$this->order = (float) $placement_definition['order'];
		}

		if ( ! array_key_exists( 'options', $placement_definition ) || ! is_array( $placement_definition['options'] ) ) {
			$placement_definition['options'] = [];
		}

		$this->options = new Placement_Type_Options( $placement_definition['options'] );

		parent::__construct( $placement_definition );
	}

	/**
	 * Magic catch to have readonly properties.
	 *
	 * @param string $name The name of the requested property.
	 *
	 * @return mixed
	 * @noinspection MagicMethodsValidityInspection -- no setter as we only want readonly properties
	 */
	public function __get( $name ) {
		if ( property_exists( $this, $name ) ) {
			return $this->{$name};
		}

		return null;
	}

	/**
	 * Check if the provided ad type is allowed (or at least not excluded).
	 * If an ad type is both allowed and forbidden, the allow-list takes precedence.
	 *
	 * @param string $type Ad type.
	 *
	 * @return bool
	 */
	public function is_ad_type_allowed( $type ) {
		return $this->is_abstract_allowed( $type, 'ad' );
	}

	/**
	 * Check if the provided ad group type is allowed.
	 *
	 * @param string $type Ad group type.
	 *
	 * @return bool
	 */
	public function is_group_type_allowed( $type ) {
		return $this->is_abstract_allowed( $type, 'group' );
	}

	/**
	 * Abstraction of comparing whether type is allowed or excluded.
	 *
	 * @param string $type  Specific Advanced_Ads_Ad::$type or Advanced_Ads_Ad_Group::$type.
	 * @param string $class Overall classification, one of `ad` or `group`.
	 *
	 * @return bool
	 */
	private function is_abstract_allowed( $type, $class ) {
		$allowed = $this->options->offsetGet( 'allowed_' . $class . '_types' );

		if ( $allowed === null ) {
			return ! in_array( $type, $this->options->offsetGet( 'excluded_' . $class . '_types' ), true );
		}

		return in_array( $type, $allowed, true );
	}

	/**
	 * Get all allowed groups for this placement type.
	 * Save them in instance, so they only have to be calculated once per type.
	 *
	 * @return array
	 */
	public function get_allowed_groups() {
		if ( isset( $this->allowed_groups ) ) {
			return $this->allowed_groups;
		}

		$this->allowed_groups = [];

		foreach ( \Advanced_Ads::get_instance()->get_model()->get_ad_groups() as $group ) {
			if ( ! $this->is_group_type_allowed( $group->type ) ) {
				continue;
			}
			// check if the group has allowed ads.
			$group_ads = array_filter(
				$group->get_all_ads(),
				function( \WP_Post $ad_post ) {
					return $this->is_ad_type_allowed( ( \Advanced_Ads\Ad_Repository::get( $ad_post->ID ) )->type );
				}
			);
			if ( empty( $group_ads ) ) {
				continue;
			}

			$this->allowed_groups[ 'group_' . $group->id ] = $group->name;
		}

		return $this->allowed_groups;
	}

	/**
	 * Get all allowed ads for this placement type.
	 * Save them in instance, so they only have to be calculated once per type.
	 *
	 * @return array
	 */
	public function get_allowed_ads() {
		if ( isset( $this->allowed_ads ) ) {
			return $this->allowed_ads;
		}

		$this->allowed_ads = [];

		foreach ( $this->get_all_ads() as $ad ) {
			if ( ! $this->is_ad_type_allowed( $ad->type ) ) {
				continue;
			}
			$this->allowed_ads[ 'ad_' . apply_filters( 'wpml_object_id', $ad->id, 'advanced_ads', true ) ] = $ad->title;
		}

		return $this->allowed_ads;
	}

	/**
	 * Get all available ads once.
	 *
	 * @return \Advanced_Ads_Ad[]
	 */
	private function get_all_ads() {
		static $all_ads;
		if ( $all_ads === null ) {
			$all_ads = array_map( function( $ad_id ) {
				return new \Advanced_Ads_Ad( $ad_id );
			}, \Advanced_Ads::get_instance()->get_model()->get_ads( [
				'orderby' => 'title',
				'order'   => 'ASC',
				'fields'  => 'ids',
			] ) );
		}

		return $all_ads;
	}
}
