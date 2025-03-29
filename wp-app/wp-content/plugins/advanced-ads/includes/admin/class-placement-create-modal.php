<?php
/**
 * Placement Create Modal.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Admin;

use AdvancedAds\Modal;
use AdvancedAds\Entities;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Placement Create Modal.
 */
class Placement_Create_Modal implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'admin_footer', [ $this, 'render_modal' ] );
	}

	/**
	 * Load the modal for creating a new Placement.
	 *
	 * @return void
	 */
	public function render_modal(): void {
		global $wp_query;

		ob_start();
		$placements_description = 0 === $wp_query->found_posts ? Entities::get_placement_description() : '';
		include ADVADS_ABSPATH . 'views/admin/placements/create-modal/new-modal-content.php';

		// @TODO Add old JS validation errors to the Modal - advads_validate_new_form in `admin/assets/js/admin.js`.
		Modal::create(
			[
				'modal_slug'       => 'placement-new',
				'modal_content'    => ob_get_clean(),
				'modal_title'      => __( 'New Placement', 'advanced-ads' ),
				'close_action'     => __( 'Save New Placement', 'advanced-ads' ),
				'close_form'       => 'advads-placements-new-form',
				'close_validation' => 'advads_validate_new_form',
			]
		);
	}
}
