<?php
/**
 * Traits Wrapper.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Traits;

use AdvancedAds\Framework\Utilities\HTML;

defined( 'ABSPATH' ) || exit;

/**
 * Traits Wrapper.
 */
trait Wrapper {

	/**
	 * Get the wrapper ID for the entity.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_wrapper_id( $context = 'view' ): string {
		return (string) $this->get_prop( 'wrapper-id', $context );
	}

	/**
	 * Get the wrapper classes for the entity.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_wrapper_class( $context = 'view' ): string {
		return $this->get_prop( 'wrapper-class', $context );
	}

	/**
	 * Set wrapper id.
	 *
	 * @param string $wrapper_id Entity wrapper id.
	 *
	 * @return void
	 */
	public function set_wrapper_id( $wrapper_id ): void {
		$this->set_prop( 'wrapper-id', sanitize_key( $wrapper_id ) );
	}

	/**
	 * Set wrapper class.
	 *
	 * @param string $wrapper_class Entity wrapper class.
	 *
	 * @return void
	 */
	public function set_wrapper_class( $wrapper_class ): void {
		$this->set_prop( 'wrapper-class', sanitize_text_field( $wrapper_class ) );
	}

	/**
	 * Creates a wrapper element with the specified tag, attributes, and content.
	 *
	 * @param string $tag     The HTML tag for the wrapper element.
	 * @param array  $attrs   Optional. An array of attributes to add to the wrapper element. Default is an empty array.
	 * @param string $content Optional. The content to be placed inside the wrapper element. Default is an empty string.
	 *
	 * @return string The generated wrapper element.
	 */
	public function create_wrapper( $tag, $attrs = [], $content = '' ) {
		$attrs = HTML::build_attributes( $attrs );

		return "<{$tag} {$attrs}>{$content}</{$tag}>";
	}

	/**
	 * Get the wrapper attributes.
	 *
	 * @return array
	 */
	public function get_wrapper_attributes(): array {
		$attrs = [];

		$attrs['id']    = $this->get_wrapper_id();
		$attrs['class'] = $this->get_wrapper_class();

		return $attrs;
	}
}
