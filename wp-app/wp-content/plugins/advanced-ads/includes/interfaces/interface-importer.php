<?php
/**
 * This interface defines a contract for implementing importers.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Interface for Importers.
 */
interface Importer {

	/**
	 * Get the unique identifier (ID) of the importer.
	 *
	 * @return string The unique ID of the importer.
	 */
	public function get_id(): string;

	/**
	 * Get the title or name of the importer.
	 *
	 * @return string The title of the importer.
	 */
	public function get_title(): string;

	/**
	 * Get a description of the importer.
	 *
	 * @return string The description of the importer.
	 */
	public function get_description(): string;

	/**
	 * Get the icon to this importer.
	 *
	 * @return string The icon for the importer.
	 */
	public function get_icon(): string;

	/**
	 * Detect the importer in database.
	 *
	 * @return bool True if detected; otherwise, false.
	 */
	public function detect(): bool;

	/**
	 * Render form.
	 *
	 * @return void
	 */
	public function render_form(): void;

	/**
	 * Import data.
	 *
	 * @return WP_Error|string
	 */
	public function import();
}
