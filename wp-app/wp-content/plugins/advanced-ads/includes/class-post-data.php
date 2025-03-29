<?php
/**
 * Post Data.
 *
 * Standardize certain post data on save.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds;

use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Post Data.
 */
class Post_Data implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_filter( 'wp_untrash_post_status', [ $this, 'untrash_post_status' ], 10, 3 );
	}

	/**
	 * Ensure statuses are correctly reassigned when restoring ads that are previously expired.
	 *
	 * @param string $new_status      The new status of the post being restored.
	 * @param int    $post_id         The ID of the post being restored.
	 * @param string $previous_status The status of the post at the point where it was trashed.
	 *
	 * @return string
	 */
	public function untrash_post_status( $new_status, $post_id, $previous_status ): string {
		if ( Constants::AD_STATUS_EXPIRED === $previous_status ) {
			return $previous_status;
		}

		$is_ours = in_array( get_post_type( $post_id ), [ Constants::POST_TYPE_AD, Constants::POST_TYPE_PLACEMENT ], true );
		if ( $is_ours && 'draft' === $new_status ) {
			return $previous_status;
		}

		return $new_status;
	}
}
