<?php
/**
 * Ad Layout.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Admin\Metaboxes;

use AdvancedAds\Constants;

defined( 'ABSPATH' ) || exit;

/**
 * Ad Layout.
 */
class Ad_Layout {

	/**
	 * Get metabox id
	 *
	 * @return string
	 */
	public function get_box_id(): string {
		return 'ad-output-box';
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
			__( 'Layout / Output', 'advanced-ads' ),
			[ $manager, 'display' ],
			Constants::POST_TYPE_AD,
			'normal',
			'high'
		);
	}

	/**
	 * Get metaboxe view file
	 *
	 * @return string
	 */
	public function get_view(): string {
		return ADVADS_ABSPATH . 'views/admin/metaboxes/ads/ad-layout.php';
	}

	/**
	 * Return manual link
	 *
	 * @return array|string
	 */
	public function get_handle_link() {
		return '<a href="https://wpadvancedads.com/manual/optimizing-the-ad-layout/?utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-ad-layout" target="_blank" class="advads-manual-link">' . __( 'Manual', 'advanced-ads' ) . '</a>';
	}
}
