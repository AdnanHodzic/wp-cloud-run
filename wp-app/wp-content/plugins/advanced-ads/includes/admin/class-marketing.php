<?php
/**
 * The class is responsible for adding marketing widgets to the plugin.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Admin;

use AdvancedAds\Constants;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Marketing.
 */
class Marketing implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'add_meta_boxes_' . Constants::POST_TYPE_AD, [ $this, 'add_meta_boxes' ] );
	}

	/**
	 * Add meta boxes
	 *
	 * @return void
	 */
	public function add_meta_boxes(): void {
		if ( ! defined( 'AAP_VERSION' ) ) {
			add_meta_box(
				'advads-pro-pitch',
				__( 'Increase your ad revenue', 'advanced-ads' ),
				[ $this, 'display_metabox' ],
				Constants::POST_TYPE_AD,
				'side',
				'low'
			);
		}

		if ( ! defined( 'AAT_VERSION' ) ) {
			add_meta_box(
				'advads-tracking-pitch',
				__( 'Statistics', 'advanced-ads' ),
				[ $this, 'display_metabox' ],
				Constants::POST_TYPE_AD,
				'normal',
				'low'
			);
		}
	}

	/**
	 * Display metaboxes by their id.
	 *
	 * @param WP_Post $post WP_Post object.
	 * @param array   $box  meta box information.
	 *
	 * @return void
	 */
	public function display_metabox( $post, $box ): void {
		$views = [
			'advads-pro-pitch'      => 'marketing/ad-metabox-all-access.php',
			'advads-tracking-pitch' => 'marketing/ad-metabox-tracking.php',
		];

		$view = $views[ $box['id'] ] ?? false;

		if ( $view ) {
			require_once ADVADS_ABSPATH . 'views/' . $view;
		}
	}
}
