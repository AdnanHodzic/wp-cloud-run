<?php
/**
 * The interface to provide a contract for Module.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Interfaces Module.
 */
interface Module_Interface {
	/**
	 * Get the unique identifier (ID) of the module.
	 *
	 * @return string The unique ID of the module.
	 */
	public function get_name(): string;

	/**
	 * Get the title or name of the module.
	 *
	 * @return string The title of the module.
	 */
	public function get_title(): string;

	/**
	 * Get a description of the module.
	 *
	 * @return string The description of the module.
	 */
	public function get_description(): string;

	/**
	 * Get the URL for icon to this module.
	 *
	 * @return string The icon URL for the module.
	 */
	public function get_image(): string;

	/**
	 * Load the module.
	 *
	 * @return void
	 */
	public function load(): void;
}
