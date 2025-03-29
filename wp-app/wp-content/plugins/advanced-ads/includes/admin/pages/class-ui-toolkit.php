<?php
/**
 * Admin Pages UI Toolkit.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Admin\Pages;

use AdvancedAds\Abstracts\Screen;

defined( 'ABSPATH' ) || exit;

/**
 * UI Toolkit Screen.
 */
class Ui_Toolkit extends Screen {

	/**
	 * Screen unique id.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return 'ui-toolkit';
	}

	/**
	 * Register screen into WordPress admin area.
	 *
	 * @return void
	 */
	public function register_screen(): void {
		$hook = add_submenu_page(
			ADVADS_SLUG,
			__( 'Advanced Ads Ui Toolkit', 'advanced-ads' ),
			__( 'Ui Toolkit', 'advanced-ads' ),
			'manage_options',
			ADVADS_SLUG . '-ui-toolkit',
			[ $this, 'display' ]
		);

		$this->set_hook( $hook );
		$this->set_tabs(
			[
				'basic'    => [
					'label'    => __( 'Basic', 'advanced-ads' ),
					'filename' => 'views/ui-toolkit/basic.php',
				],
				'forms'    => [
					'label'    => __( 'Forms', 'advanced-ads' ),
					'filename' => 'views/ui-toolkit/forms.php',
				],
				'advanced' => [
					'label'    => __( 'Advanced', 'advanced-ads' ),
					'filename' => 'views/ui-toolkit/advanced.php',
				],
			]
		);
	}

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		wp_advads()->registry->enqueue_style( 'common' );
	}

	/**
	 * Display screen content.
	 *
	 * @return void
	 */
	public function display(): void {
		include_once ADVADS_ABSPATH . 'views/admin/screens/ui-toolkit.php';
	}
}
