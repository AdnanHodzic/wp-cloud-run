<?php

/**
 * Ad Expiration functionality.
 */
class Advanced_Ads_Ad_Expiration {
	const POST_STATUS = 'advanced_ads_expired';
	const POST_META   = 'advanced_ads_expiration_time';

	/**
	 * The current ad object.
	 *
	 * @var Advanced_Ads_Ad
	 */
	private $ad;

	/**
	 * Inject ad object, hook to option saving.
	 *
	 * @param Advanced_Ads_Ad $ad the current ad object.
	 */
	public function __construct( Advanced_Ads_Ad $ad ) {
		$this->ad = $ad;

		add_filter( 'advanced-ads-save-options', [ $this, 'save_expiration_date' ], 10, 2 );
	}

	/**
	 * Check whether this ad is expired.
	 *
	 * @return bool
	 */
	public function is_ad_expired() {
		if ( $this->ad->expiry_date <= 0 || $this->ad->expiry_date > time() ) {
			return false;
		}

		// if the ad is not trashed, but has a different status than expired, transition the status.
		if ( ! in_array( $this->ad->status, [ self::POST_STATUS, 'trash' ], true ) ) {
			$this->transition_post_status();
		}

		return true;
	}

	/**
	 * Extract the expiration date from the options array and save it as post_meta directly.
	 *
	 * @param array           $options array with all ad options.
	 * @param Advanced_Ads_Ad $ad      the current ad object.
	 *
	 * @return array
	 */
	public function save_expiration_date( $options, Advanced_Ads_Ad $ad ) {
		if ( empty( $options['expiry_date'] ) ) {
			delete_post_meta( $ad->id, self::POST_META );
			return $options;
		}
		$datetime = ( new DateTimeImmutable() )->setTimestamp( (int) $options['expiry_date'] );
		update_post_meta( $ad->id, self::POST_META, $datetime->format( 'Y-m-d H:i:s' ) );

		return $options;
	}

	/**
	 * Transition the post form previous status to self::POST_STATUS.
	 * Remove kses filters before updating the post so that expiring ads don’t lose HTML or other code.
	 */
	private function transition_post_status() {
		kses_remove_filters();
		wp_update_post(
			[
				'ID'          => $this->ad->id,
				'post_status' => self::POST_STATUS,
			]
		);
		kses_init_filters();
	}

	/**
	 * Register custom post status for expired ads.
	 */
	public static function register_post_status() {
		register_post_status( self::POST_STATUS, [
			'label'   => __( 'Expired', 'advanced-ads' ),
			'private' => true,
		] );
	}

	/**
	 * Hook into wp_untrash_post_status, to revert ads that previously had the expired status to that status instead of draft.
	 *
	 * @param string $new_status      The new status after untrashing a post.
	 * @param int    $post_id         The post id of the post to be untrashed.
	 * @param string $previous_status The post status before trashing.
	 *
	 * @return string
	 */
	public static function wp_untrash_post_status( $new_status, $post_id, $previous_status ) {
		if ( $previous_status === self::POST_STATUS ) {
			return $previous_status;
		}

		return $new_status;
	}
}
