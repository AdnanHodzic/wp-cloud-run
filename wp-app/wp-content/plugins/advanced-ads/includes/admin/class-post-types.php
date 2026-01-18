<?php
/**
 * The class is responsible for handling the edit posts views and some functionality on the edit post screen.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Admin;

use stdClass;
use WP_Query;
use AdvancedAds\Constants;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Post Types.
 */
class Post_Types implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_filter( 'post_updated_messages', [ $this, 'post_updated_messages' ] );
		add_filter( 'bulk_post_updated_messages', [ $this, 'bulk_post_updated_messages' ], 10, 2 );

		add_filter( 'wp_count_posts', [ $this, 'update_count_posts' ], 10, 2 );
		add_filter( 'get_edit_post_link', [ $this, 'get_edit_post_link' ], 10, 2 );
	}

	/**
	 * Update post counts to have expiring ads.
	 *
	 * @param stdClass $counts An object containing the current post_type's post
	 *                         counts by status.
	 * @param string   $type   Post type.
	 *
	 * @return stdClass
	 */
	public function update_count_posts( $counts, $type ): stdClass {
		if ( Constants::POST_TYPE_AD !== $type ) {
			return $counts;
		}

		$query = new WP_Query(
			[
				'post_type'   => Constants::POST_TYPE_AD,
				'post_status' => 'any',
				'fields'      => 'ids',
				'meta_query'  => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					[
						'key'     => Constants::AD_META_EXPIRATION_TIME,
						'value'   => current_time( 'mysql', true ),
						'compare' => '>=',
						'type'    => 'DATETIME',
					],
				],
			]
		);

		$counts->{Constants::AD_STATUS_EXPIRING} = $query->found_posts;

		return $counts;
	}

	/**
	 * Change messages when a post type is updated.
	 *
	 * @since 1.4.7
	 *
	 * @param array $messages Existing post update messages.
	 *
	 * @return array
	 */
	public function post_updated_messages( $messages = [] ): array {
		global $post;

		// Added to fix error message array caused by third party code that uses post_updated_messages filter wrong.
		if ( ! is_array( $messages ) ) {
			$messages = [];
		}

		$revision = Params::get( 'revision', 0, FILTER_VALIDATE_INT );

		$messages[ Constants::POST_TYPE_AD ] = [
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Ad updated.', 'advanced-ads' ),
			4  => __( 'Ad updated.', 'advanced-ads' ),
			5  => $revision
				/* translators: %s: date and time of the revision */
				? sprintf( __( 'Ad restored to revision from %s', 'advanced-ads' ), wp_post_revision_title( $revision, false ) )
				: false,
			6  => __( 'Ad saved.', 'advanced-ads' ),
			7  => __( 'Ad saved.', 'advanced-ads' ),
			8  => __( 'Ad submitted.', 'advanced-ads' ),
			9  => sprintf(
				/* translators: %s: date */
				__( 'Ad scheduled for: <strong>%1$s</strong>.', 'advanced-ads' ),
				'<strong>' . date_i18n( __( 'M j, Y @ G:i', 'advanced-ads' ), strtotime( $post->post_date ) ) . '</strong>'
			),
			10 => __( 'Ad draft updated.', 'advanced-ads' ),
		];

		return $messages;
	}

	/**
	 * Edit ad bulk update messages
	 *
	 * @param array $messages existing bulk update messages.
	 * @param array $counts numbers of updated ads.
	 *
	 * @return array
	 */
	public function bulk_post_updated_messages( array $messages, array $counts ): array {
		$messages[ Constants::POST_TYPE_AD ] = [
			/* translators: %s: ad count */
			'updated'   => _n( '%s ad updated.', '%s ads updated.', $counts['updated'], 'advanced-ads' ),
			/* translators: %s: ad count */
			'locked'    => _n( '%s ad not updated, somebody is editing it.', '%s ads not updated, somebody is editing them.', $counts['locked'], 'advanced-ads' ),
			/* translators: %s: ad count */
			'deleted'   => _n( '%s ad permanently deleted.', '%s ads permanently deleted.', $counts['deleted'], 'advanced-ads' ),
			/* translators: %s: ad count */
			'trashed'   => _n( '%s ad moved to the Trash.', '%s ads moved to the Trash.', $counts['trashed'], 'advanced-ads' ),
			/* translators: %s: ad count */
			'untrashed' => _n( '%s ad restored from the Trash.', '%s ads restored from the Trash.', $counts['untrashed'], 'advanced-ads' ),
		];

		$messages[ Constants::POST_TYPE_PLACEMENT ] = [
			/* translators: %s: placement count */
			'updated'   => _n( '%s placement updated.', '%s placements updated.', $counts['updated'], 'advanced-ads' ),
			/* translators: %s: placement count */
			'locked'    => _n( '%s placement not updated, somebody is editing it.', '%s placements not updated, somebody is editing them.', $counts['locked'], 'advanced-ads' ),
			/* translators: %s: placement count */
			'deleted'   => _n( '%s placement permanently deleted.', '%s placements permanently deleted.', $counts['deleted'], 'advanced-ads' ),
			/* translators: %s: placement count */
			'trashed'   => _n( '%s placement moved to the Trash.', '%s placements moved to the Trash.', $counts['trashed'], 'advanced-ads' ),
			/* translators: %s: placement count */
			'untrashed' => _n( '%s placement restored from the Trash.', '%s placements restored from the Trash.', $counts['untrashed'], 'advanced-ads' ),
		];

		return $messages;
	}

	/**
	 * Replace the edit link with a link to the modal to edit the placement.
	 *
	 * @param string $link    The previous link.
	 * @param int    $post_id The \WP_Post::$ID for the current item.
	 *
	 * @return string
	 */
	public function get_edit_post_link( string $link, int $post_id ): string {
		if ( get_post_type( $post_id ) === Constants::POST_TYPE_PLACEMENT ) {
			$link = admin_url( 'edit.php?post_type=' . Constants::POST_TYPE_PLACEMENT . '#modal-placement-edit-' . $post_id );
		}

		return $link;
	}
}
