<?php
/**
 * Traits Entity.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Traits;

use AdvancedAds\Abstracts\Group;
use AdvancedAds\Abstracts\Placement;

defined( 'ABSPATH' ) || exit;

/**
 * Traits Entity.
 */
trait Entity {

	/**
	 * Entity parent object.
	 *
	 * @var Group|Placement|null
	 */
	private $parent = null;

	/* Getter ------------------- */

	/**
	 * Get the parent entity name.
	 *
	 * @return int
	 */
	public function get_parent_entity_name(): string {
		if ( $this->is_parent_group() ) {
			return _x( 'Ad Group', 'ad group singular name', 'advanced-ads' );
		}

		if ( $this->is_parent_placement() ) {
			return _x( 'Placement', 'ad placement singular name', 'advanced-ads' );
		}

		return __( 'Unknown', 'advanced-ads' );
	}

	/**
	 * Get title.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_title( $context = 'view' ): string {
		return $this->get_prop( 'title', $context ) ?? '';
	}

	/**
	 * Get slug.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_slug( $context = 'view' ): string {
		return $this->get_prop( 'slug', $context ) ?? '';
	}

	/**
	 * Get content.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_content( $context = 'view' ): string {
		return $this->get_prop( 'content', $context );
	}

	/**
	 * Get status.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_status( $context = 'view' ): string {
		return $this->get_prop( 'status', $context );
	}

	/**
	 * Get type.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_type( $context = 'view' ): string {
		return $this->get_prop( 'type', $context );
	}

	/**
	 * Get author ID.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int The author ID.
	 */
	public function get_author_id( $context = 'view' ): int {
		return absint( $this->get_prop( 'author_id', $context ) ) ?? 0;
	}

	/**
	 * Get parent object.
	 *
	 * @return Group|Placement|null
	 */
	public function get_parent() {
		return $this->parent;
	}

	/**
	 * Get placement that is its parent or grandparent if any.
	 *
	 * @return Placement|false
	 */
	public function get_root_placement() {
		if ( $this->is_parent_placement() ) {
			return $this->get_parent();
		}

		if ( $this->has_parent() && $this->get_parent()->is_parent_placement() ) {
			return $this->get_parent()->get_parent();
		}

		return false;
	}

	/* Setter ------------------- */

	/**
	 * Set title.
	 *
	 * @param string $title Entity title.
	 *
	 * @return void
	 */
	public function set_title( $title ): void {
		$this->set_prop( 'title', $title );
	}

	/**
	 * Set slug.
	 *
	 * @param string $slug Entity slug.
	 *
	 * @return void
	 */
	public function set_slug( $slug ): void {
		$this->set_prop( 'slug', sanitize_text_field( wp_unslash( $slug ) ) );
	}

	/**
	 * Set content.
	 *
	 * @param string $content Entity content.
	 *
	 * @return void
	 */
	public function set_content( $content ): void {
		$this->set_prop( 'content', $content );
	}

	/**
	 * Set status.
	 *
	 * @param string $status Entity status.
	 *
	 * @return void
	 */
	public function set_status( $status ): void {
		$this->set_prop( 'status', $status );
	}

	/**
	 * Set author ID.
	 *
	 * @param int $author_id The author ID.
	 *
	 * @return void
	 */
	public function set_author_id( $author_id ): void {
		$this->set_prop( 'author_id', absint( $author_id ) );
	}

	/**
	 * Set type.
	 *
	 * @param string $type Entity type.
	 *
	 * @return void
	 */
	public function set_type( $type ): void {
		$this->set_prop( 'type', $type );
	}

	/**
	 * Set parent object.
	 *
	 * @param Group|Placement|null $item Parent object.
	 *
	 * @return void
	 */
	public function set_parent( $item ): void {
		$this->parent = $item;
	}

	/* Conditional ------------------- */

	/**
	 * Check if the entity has parent.
	 *
	 * @return bool
	 */
	public function has_parent(): bool {
		return null !== $this->parent;
	}

	/**
	 * Check the status.
	 *
	 * @param string|array $status Status to check.
	 *
	 * @return bool
	 */
	public function is_status( $status ): bool {
		return $this->get_status() === $status || ( is_array( $status ) && in_array( $this->get_status(), $status, true ) );
	}

	/**
	 * Check the type.
	 *
	 * @param string|string[] $type Type to check.
	 *
	 * @return bool
	 */
	public function is_type( $type ): bool {
		return $this->get_type() === $type || ( is_array( $type ) && in_array( $this->get_type(), $type, true ) );
	}

	/**
	 * Check if the entity is a group.
	 *
	 * @return bool
	 */
	public function is_parent_group(): bool {
		return $this->parent && is_a_group( $this->parent );
	}

	/**
	 * Check if the entity is a placement.
	 *
	 * @return bool
	 */
	public function is_parent_placement(): bool {
		return $this->parent && is_a_placement( $this->parent );
	}

	/* Additional Methods ----------- */

	/**
	 * Outputs the entity.
	 *
	 * @return string The output of the entity.
	 */
	public function output(): string {
		/**
		 * Allow developers to modify the output and short-circuit the function.
		 *
		 * @param string $pre_output The pre-output value of the entity.
		 * @param object $this       The current instance of the entity.
		 *
		 * @return string The modified pre-output value.
		 */
		$pre_output = apply_filters( "advanced-ads-{$this->object_type}-pre-output", null, $this );
		if ( null !== $pre_output ) {
			return $pre_output;
		}

		do_action( "advanced-ads-{$this->object_type}-before-output", $this );

		if ( ! $this->can_display() ) {
			return '';
		}

		$output = $this->prepare_output();

		do_action( "advanced-ads-{$this->object_type}-output-ready", $this, $output );

		return apply_filters(
			"advanced-ads-{$this->object_type}-output",
			$output,
			$this
		);
	}
}
