<?php
/**
 * This class is serving as the base for various placement types and providing a foundation for defining common placement attributes and methods.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Abstracts;

use RuntimeException;
use AdvancedAds\Traits;
use AdvancedAds\Constants;
use AdvancedAds\Frontend\Stats;
use AdvancedAds\Framework\Utilities\Formatting;

defined( 'ABSPATH' ) || exit;

/**
 * Placement.
 */
class Placement extends Data {

	use Traits\Entity;
	use Traits\Wrapper;

	/**
	 * This is the object type.
	 *
	 * @var string
	 */
	protected $object_type = 'placement';

	/**
	 * This is the item object.
	 *
	 * @var Ad|Group|null
	 */
	protected $item_object = null;

	/**
	 * This is the item type.
	 *
	 * @var string
	 */
	protected $item_type = '';

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @var array
	 */
	protected $data = [
		'title'    => '',
		'content'  => '',
		'type'     => 'default',
		'slug'     => '',
		'status'   => '',
		'item'     => '',
		'display'  => [],
		'visitors' => [],
	];

	/**
	 * Get the placement if ID is passed, otherwise the placement is new and empty.
	 *
	 * @param Placement|WP_Post|int $placement Placement to init.
	 */
	public function __construct( $placement = 0 ) {
		parent::__construct();

		$this->set_placement_id( $placement );
		$this->data_store = wp_advads_get_placement_repository();

		if ( $this->get_id() > 0 ) {
			$this->data_store->read( $this );
		}
	}

	/**
	 * Set the placement ID depending on what was passed.
	 *
	 * @param Placement|WP_Post|int $placement Placement instance, post instance or numeric.
	 *
	 * @return void
	 */
	private function set_placement_id( $placement ): void {
		if ( is_numeric( $placement ) && $placement > 0 ) {
			$this->set_id( $placement );
		} elseif ( $placement instanceof self ) {
			$this->set_id( absint( $placement->get_id() ) );
		} elseif ( ! empty( $placement->ID ) ) {
			$this->set_id( absint( $placement->ID ) );
		} else {
			$this->set_object_read( true );
		}
	}

	/* Getter ------------------- */

	/**
	 * Get item.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_item( $context = 'view' ): string {
		return $this->get_prop( 'item', $context );
	}

	/**
	 * Get the display conditions for the placement.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return array
	 */
	public function get_display_conditions( $context = 'view' ): array {
		return $this->get_prop( 'display', $context );
	}

	/**
	 * Get the visitor conditions for the placement.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return array
	 */
	public function get_visitor_conditions( $context = 'view' ): array {
		return $this->get_prop( 'visitors', $context );
	}

	/* Setter ------------------- */

	/**
	 * Set item.
	 *
	 * @param string $item Placement item.
	 *
	 * @return void
	 */
	public function set_item( $item ): void {
		$this->set_prop( 'item', $item );
	}

	/**
	 * Set display conditions.
	 *
	 * @param string $conditions Placement conditions.
	 *
	 * @return void
	 */
	public function set_display_conditions( $conditions ): void {
		$this->set_prop( 'display', $conditions );
	}

	/**
	 * Set conditions.
	 *
	 * @param string $conditions Placement conditions.
	 *
	 * @return void
	 */
	public function set_visitor_conditions( $conditions ): void {
		$this->set_prop( 'visitors', $conditions );
	}

	/* Conditional ------------------- */

	/**
	 * Check if placement item is allowed
	 *
	 * @return bool
	 */
	public function is_item_allowed(): bool {
		$item_type   = $this->get_item_type();
		$item_object = $this->get_item_object();

		return $this->get_type_object()
			->is_entity_allowed( $item_object->get_type(), $item_type );
	}

	/**
	 * Determines whether the placement can be displayed.
	 *
	 * @return bool True if the placement can be displayed, false otherwise.
	 */
	public function can_display(): bool {
		return apply_filters( 'advanced-ads-can-display-placement', true, $this->get_id(), $this );
	}

	/**
	 * Determines whether the placement has cache busting enabled.
	 *
	 * @return bool
	 */
	public function has_cache_busting(): bool {
		$value = $this->get_prop( 'cache-busting' );
		return 'auto' === $value || Formatting::string_to_bool( $value );
	}

	/* Additional Methods ------------------- */

	/**
	 * Prepares the output for the placement.
	 *
	 * @return mixed The prepared output or the overridden return value.
	 */
	public function prepare_output(): string {
		// Early bail!!
		if ( empty( $this->get_item_object() ) || Constants::ENTITY_PLACEMENT === $this->get_item_type() || 'publish' !== $this->get_status() ) {
			return '';
		}

		$prefix    = wp_advads()->get_frontend_prefix();
		$ad_args   = $this->get_prop( 'ad_args' );
		$item_type = $this->get_item_type();

		// Inject options.
		$this->item_object->set_prop_temp( 'ad_args', $ad_args );

		if ( ! $this->is_type( 'header' ) ) {
			$class = $ad_args['output']['class'] ?? [];

			if ( ! in_array( $prefix . $this->get_slug(), $class, true ) ) {
				$class[] = $prefix . $this->get_slug();
			}

			$ad_args['output']          = $ad_args['output'] ?? [];
			$ad_args['output']['class'] = $class;
		}

		// Create placement id for various features like ajax ads.
		$ad_args['output']['placement_id'] = $this->get_id();

		// Inject options.
		$this->item_object->set_prop_temp( 'ad_args', $ad_args );
		$this->item_object->set_parent( $this );

		$method            = Constants::ENTITY_AD === $item_type ? 'ad' : 'group';
		$args              = wp_advads_default_entity_arguments( $method, $this->item_object->get_id(), $ad_args );
		$overridden_entity = Constants::ENTITY_AD === $item_type
			? apply_filters( 'advanced-ads-ad-select-override-by-ad', false, $this->item_object, $args )
			: apply_filters( 'advanced-ads-ad-select-override-by-group', false, $this->item_object, $this->item_object->get_ordered_ad_ids(), $args );

		if ( false !== $overridden_entity ) {
			return $overridden_entity;
		}

		$output = $this->item_object->can_display() ? $this->item_object->output() : '';

		// Maintain Stats.
		if ( $output ) {
			Stats::get()->add_entity( 'placement', $this->get_id(), $this->get_title() );
			Stats::get()->add_entity( $this->get_item_type(), $this->item_object->get_id(), $this->item_object->get_title(), $this->get_id() );
		}

		return $output;
	}

	/**
	 * Update placement item.
	 *
	 * @param string $new_item Placement item id.
	 *
	 * @throws \RuntimeException If the new item is equal to the old item or if the item type is not allowed for the placement type.
	 *
	 * @return mixed
	 */
	public function update_item( $new_item ) {
		list( 'type' => $item_type, 'id' => $item_id )         = $this->get_item_parts( $this->get_item() );
		list( 'type' => $new_item_type, 'id' => $new_item_id ) = $this->get_item_parts( $new_item );

		if ( $new_item_id === $item_id ) {
			throw new RuntimeException( 'New item is equal to old item.' );
		}

		$new_item_object = 'ad' === $new_item_type
			? wp_advads_get_ad( $new_item_id )
			: wp_advads_get_group( $new_item_id );

		$is_allowed = $this->get_type_object()
			->is_entity_allowed( $new_item_object->get_type(), $new_item_type );

		if ( ! $is_allowed ) {
			throw new RuntimeException(
				sprintf(
					/* translators: 1: Entity type 2: Item type 3: Placement title */
					esc_html__( '%1$s type "%2$s" not allowed for placement type "%3$s"', 'advanced-ads' ),
					strtoupper( $new_item_type ), // phpcs:ignore
					$new_item_object->get_type(), // phpcs:ignore
					$this->get_type_object()->get_title() // phpcs:ignore
				)
			);
		}

		if ( ! update_post_meta( $this->get_id(), 'item', $new_item ) ) {
			throw new RuntimeException( 'Can\'t update item.' );
		}

		return $new_item_object;
	}

	/**
	 * Remove placement item.
	 *
	 * @return bool
	 */
	public function remove_item(): bool {
		return delete_post_meta( $this->get_id(), 'item' );
	}

	/**
	 * Get placement item type
	 *
	 * @return string
	 */
	public function get_item_type(): string {
		$this->get_item_object();

		return $this->item_type;
	}

	/**
	 * Get placement item object
	 *
	 * @return Ad|Group|bool|null
	 */
	public function get_item_object() {
		global $sitepress;

		// Early bail!!
		if ( empty( $this->get_item() ) ) {
			return $this->item_object;
		}

		list( 'type' => $item_type, 'id' => $item_id ) = $this->get_item_parts( $this->get_item() );

		if ( Constants::ENTITY_AD === $item_type && defined( 'ICL_SITEPRESS_VERSION' ) ) {
			/**
			 * Deliver the translated version of an ad if set up with WPML.
			 * If an ad is not translated, show the ad in the original language when this is the selected option in the WPML settings.
			 *
			 * @source https://wpml.org/wpml-hook/wpml_object_id/
			 * @source https://wpml.org/forums/topic/backend-custom-post-types-page-overview-with-translation-options/
			 */
			$item_id = apply_filters( 'wpml_object_id', $item_id, 'advanced_ads', $sitepress->is_display_as_translated_post_type( 'advanced_ads' ) );
		}

		$this->item_type   = $item_type;
		$this->item_object = 'ad' === $item_type
			? wp_advads_get_ad( $item_id )
			: wp_advads_get_group( $item_id );

		if ( $this->item_object ) {
			$this->item_object->set_parent( $this );
		}

		return $this->item_object;
	}

	/**
	 * Get placement type object
	 *
	 * @return Placement_Type|bool
	 */
	public function get_type_object() {
		if ( ! wp_advads_has_placement_type( $this->get_type() ) ) {
			wp_advads_create_placement_type( $this->get_type() );
		}

		return wp_advads_get_placement_type( $this->get_type() );
	}

	/**
	 * Get the item parts
	 *
	 * @param string $item Placement item.
	 *
	 * @return array
	 */
	private function get_item_parts( $item ): array {
		$item_parts = explode( '_', $item );

		return [
			'type' => trim( $item_parts[0] ),
			'id'   => absint( $item_parts[1] ?? 0 ),
		];
	}

	/**
	 * Get the placement edit link
	 *
	 * @return string|null
	 */
	public function get_edit_link() {
		return add_query_arg(
			[
				'post_type' => Constants::POST_TYPE_PLACEMENT,
			],
			admin_url( 'edit.php#modal-placement-edit-' . $this->get_id() )
		);
	}
}
