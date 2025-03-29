<?php
/**
 * Ad Targeting.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Admin\Metaboxes;

use AdvancedAds\Constants;

defined( 'ABSPATH' ) || exit;

/**
 * Ad Targeting.
 */
class Ad_Targeting {

	/**
	 * Get metabox id
	 *
	 * @return string
	 */
	public function get_box_id(): string {
		return 'ad-targeting-box';
	}

	/**
	 * Hook into WordPress.
	 *
	 * @param Metabox_Ad $manager Manager instance.
	 *
	 * @return void
	 */
	public function register( $manager ): void {
		add_meta_box(
			$this->get_box_id(),
			__( 'Targeting', 'advanced-ads' ),
			[ $manager, 'display' ],
			Constants::POST_TYPE_AD,
			'normal',
			'default'
		);
	}

	/**
	 * Get metaboxe view file
	 *
	 * @return string
	 */
	public function get_view(): string {
		return ADVADS_ABSPATH . 'views/admin/metaboxes/ads/ad-targeting.php';
	}

	/**
	 * Return manual link
	 *
	 * @return array|string
	 */
	public function get_handle_link() {
		return [
			'<a href="#" class="advads-video-link">' . __( 'Video', 'advanced-ads' ) . '</a>',
			'<a href="https://wpadvancedads.com/manual/display-conditions/?utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-display" target="_blank" class="advads-manual-link">' . __( 'Display Conditions', 'advanced-ads' ) . '</a>',
			'<a href="https://wpadvancedads.com/manual/visitor-conditions/?utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-visitor" target="_blank" class="advads-manual-link">' . __( 'Visitor Conditions', 'advanced-ads' ) . '</a>',
		];
	}
}
