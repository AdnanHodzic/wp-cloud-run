<?php
/**
 * Groups screen.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin\Pages;

use WP_Error;
use WP_Taxonomy;
use Advanced_Ads_Group;
use Advanced_Ads\Ad_Repository;
use AdvancedAds\Admin\Groups_List_Table;
use AdvancedAds\Entities;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Interfaces\Screen_Interface;
use AdvancedAds\Utilities\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Groups.
 */
class Groups implements Screen_Interface {

	/**
	 * Hold table object.
	 *
	 * @var Groups_List_Table
	 */
	private $list_table = null;

	/**
	 * Register screen into WordPress admin area.
	 *
	 * @return void
	 */
	public function register_screen(): void {
		add_submenu_page(
			ADVADS_SLUG,
			__( 'Ad Groups & Rotations', 'advanced-ads' ),
			__( 'Groups & Rotation', 'advanced-ads' ),
			WordPress::user_cap( 'advanced_ads_edit_ads' ),
			ADVADS_SLUG . '-groups',
			[ $this, 'display' ]
		);

		add_action( 'in_admin_header', [ $this, 'get_list_table' ] );
		if ( 'advanced-ads-groups' === Params::get( 'page' ) ) {
			add_action( 'admin_init', [ $this, 'admin_init' ] );
		}
	}

	/**
	 * Intercept group form submission on dashboard init.
	 *
	 * @return void
	 */
	public function admin_init() {
		if (
			'advanced-ads-groups' !== Params::get( 'page' )
			&& ! Params::post( 'advads-group-update-nonce' )
			&& ! Params::post( 'advads-group-add-nonce' )
			&& 'delete' === Params::get( 'action' )
		) {
			// Just skip if no group created, deleted, or edited.
			return;
		}

		// Update groups.
		$result = $this->handle_action();
		$url    = admin_url( 'admin.php?page=advanced-ads-groups' );

		switch ( $result['code'] ) {
			case 1:
				wp_redirect( "$url&message={$result['code']}&group={$result['group']}" );
				exit;
			case 2:
				wp_redirect( "$url&message={$result['code']}" );
				exit;
			case 3:
				wp_redirect( "$url&message=" . rawurlencode( $result['message'] ) );
				exit;
			default:
		}
	}

	/**
	 * Display notices if any
	 *
	 * @return void
	 */
	public function handle_messages() {
		$message = Params::get( 'message' );

		if ( false === $message ) {
			return;
		}

		$message = sanitize_text_field( wp_unslash( $message ) );

		echo '<div class="wrap">';

		switch ( $message ) {
			case '1':
				echo '<div class="notice inline"><p>' . esc_html__( 'Ad Group successfully created', 'advanced-ads' ) . '</p></div>';
				?>
				<script>
					window.addEventListener( 'DOMContentLoaded', () => {
						window.location.hash = '#modal-group-edit-<?php echo esc_html( sanitize_text_field( wp_unslash( Params::get( 'group' ) ) ) ); ?>';
					} );
				</script>
				<?php
				break;
			case '2':
				echo '<div id="message" class="updated inline"><p>' . esc_html__( 'Ad Groups successfully updated', 'advanced-ads' ) . '</p></div>';
				break;
			default:
				echo '<div id="message" class="notice error inline"><p>' . esc_html( rawurldecode( $message ) ) . '</p></div>';
		}

		echo '</div>';
	}

	/**
	 * Display screen content.
	 *
	 * @return void
	 */
	public function display(): void {
		$this->handle_messages();
		$taxonomy      = get_taxonomy( Entities::TAXONOMY_AD_GROUP );
		$wp_list_table = $this->get_list_table();
		$is_search     = Params::get( 's' );

		include ADVADS_ABSPATH . 'views/admin/screens/groups.php';
	}

	/**
	 * Get list table object
	 *
	 * @return null|Groups_List_Table
	 */
	public function get_list_table() {
		$screen = get_current_screen();
		if ( 'advanced-ads_page_advanced-ads-groups' === $screen->id && null === $this->list_table ) {
			$screen->taxonomy  = Entities::TAXONOMY_AD_GROUP;
			$screen->post_type = Entities::POST_TYPE_AD;
			$this->list_table  = new Groups_List_Table();
		}

		return $this->list_table;
	}

	/**
	 * Handle actions
	 *
	 * @return array
	 */
	private function handle_action(): array {
		$result   = false;
		$taxonomy = get_taxonomy( Entities::TAXONOMY_AD_GROUP );
		$action   = WordPress::current_action();

		if ( Params::request( 'advads-group-add-nonce' ) ) {
			$action = 'create';
		}

		if ( Params::request( 'advads-group-update-nonce' ) ) {
			$action = 'update';
		}

		if ( $action ) {
			$handle   = "handle_{$action}";
			$group_id = Params::request( 'group_id', 0, FILTER_VALIDATE_INT );

			if ( method_exists( $this, $handle ) ) {
				$result = $this->$handle( $group_id, $taxonomy );
			}
		}

		if ( is_wp_error( $result ) ) {
			return [
				'code'    => 3,
				'message' => $result->get_error_message(),
			];
		} elseif ( 'create' === $action ) {
			return [
				'code'  => 1,
				'group' => $result->id,
			];
		} elseif ( 'update' === $action ) {
			return [ 'code' => 2 ];
		} else {
			return [ 'code' => 0 ];
		}
	}

	/**
	 * Handle add new or update group.
	 *
	 * @param int         $group_id Group id.
	 * @param WP_Taxonomy $taxonomy Taxonomy instance.
	 *
	 * @return array|WP_Error
	 */
	private function handle_editedgroup( $group_id, $taxonomy ) {
		check_admin_referer( 'update-group_' . $group_id );

		if ( ! current_user_can( $taxonomy->cap->edit_terms ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to access this feature.', 'advanced-ads' ) );
		}

		// Handle new.
		if ( 0 === $group_id ) {
			return wp_insert_term( Params::post( 'name' ), Entities::TAXONOMY_AD_GROUP, $_POST );
		}

		// Handle updates.
		$tag = get_term( $group_id, Entities::TAXONOMY_AD_GROUP );
		if ( ! $tag ) {
			wp_die( esc_html__( 'You attempted to edit an ad group that doesn&#8217;t exist. Perhaps it was deleted?', 'advanced-ads' ) );
		}

		return wp_update_term( $group_id, Entities::TAXONOMY_AD_GROUP, $_POST );
	}

	/**
	 * Handle delete group.
	 *
	 * @param int         $group_id Group id.
	 * @param WP_Taxonomy $taxonomy Taxonomy instance.
	 *
	 * @return void
	 */
	private function handle_delete( $group_id, $taxonomy ): void {
		check_admin_referer( 'delete-tag_' . $group_id );

		if ( ! current_user_can( $taxonomy->cap->delete_terms ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to access this feature.', 'advanced-ads' ) );
		}

		wp_delete_term( $group_id, Entities::TAXONOMY_AD_GROUP );
		Advanced_Ads_Group::delete_ad_weights( $group_id );
	}

	/**
	 * Bulk update groups
	 */
	public function handle_update() {
		if ( ! wp_verify_nonce( Params::post( 'advads-group-update-nonce' ), 'update-advads-groups' ) ) {
			return new WP_Error( 'invalid_ad_group', __( 'Invalid Ad Group', 'advanced-ads' ) );
		}

		if ( ! WordPress::user_can( 'advanced_ads_edit_ads' ) ) {
			return new WP_Error( 'invalid_ad_group_rights', __( 'You don’t have permission to change the ad groups', 'advanced-ads' ) );
		}

		/**
		 * Empty group settings
		 * edit: emptying disabled, because when only a few groups are saved (e.g. when filtered by search), options are reset
		 * todo: needs a solution that also removes options when the group is removed
		 */
		$all_weights     = get_option( 'advads-ad-weights', [] );
		$ad_groups_assoc = $this->update_remove_groups( $all_weights );
		$this->loop_ad_groups( $all_weights, $ad_groups_assoc );

		update_option( 'advads-ad-weights', $all_weights );

		return true;
	}

	/**
	 * Create a new group.
	 *
	 * @return Advanced_Ads_Group|WP_Error
	 */
	public function handle_create() {
		if ( ! wp_verify_nonce( Params::post( 'advads-group-add-nonce' ), 'add-advads-groups' ) ) {
			return new WP_Error( 'invalid_ad_group', __( 'Invalid Ad Group', 'advanced-ads' ) );
		}

		if ( ! WordPress::user_can( 'advanced_ads_edit_ads' ) ) {
			return new WP_Error( 'invalid_ad_group_rights', __( 'You don’t have permission to change the ad groups', 'advanced-ads' ) );
		}

		$group_name = Params::post( 'advads-group-name' );
		if ( empty( $group_name ) ) {
			return new WP_Error( 'no_ad_group_created', __( 'No ad group created', 'advanced-ads' ) );
		}

		$title     = sanitize_text_field( wp_unslash( $group_name ) );
		$new_group = wp_create_term( $title, Entities::TAXONOMY_AD_GROUP );

		if ( is_wp_error( $new_group ) ) {
			return $new_group;
		}

		$type        = 'default';
		$posted_type = Params::post( 'advads-group-type' );
		if ( ! empty( $posted_type ) && wp_advads()->group_manager->has_type( $posted_type ) ) {
			$type = $posted_type;
		}

		$group      = new Advanced_Ads_Group( $new_group['term_id'] );
		$attributes = apply_filters(
			'advanced-ads-group-save-atts',
			[
				'type'     => $type,
				'ad_count' => 1,
				'options'  => [],
			],
			$group
		);

		$group->save( $attributes );

		return $group;
	}

	/**
	 * Remove group in update routine
	 *
	 * @param array $all_weights Array of weights.
	 *
	 * @return array
	 */
	private function update_remove_groups( &$all_weights ): array {
		$ad_groups      = [];
		$remove_ads     = Params::post( 'advads-groups-removed-ads', false, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$remove_ads_gid = Params::post( 'advads-groups-removed-ads-gid', false, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		if ( $remove_ads && $remove_ads_gid && is_array( $remove_ads ) ) {
			$len = count( $remove_ads );
			for ( $i = 0; $i < $len; $i++ ) {
				$ad_id    = absint( wp_unslash( $remove_ads[ $i ] ) );
				$group_id = absint( wp_unslash( $remove_ads_gid[ $i ] ) );

				$ad_groups[ $ad_id ] = [];

				if ( isset( $all_weights[ $group_id ] ) && isset( $all_weights[ $group_id ][ $ad_id ] ) ) {
					unset( $all_weights[ $group_id ][ $ad_id ] );
				}

				// We need to load all the group ids, that are allocated to this ad and then remove the right one only.
				$group_ids = $this->get_groups_by_ad_id( $ad_id );
				foreach ( $group_ids as $gid ) {
					if ( $gid !== $group_id ) {
						$ad_groups[ $ad_id ][] = $gid;
					}
				}
			}
		}

		return $ad_groups;
	}

	/**
	 * Loop through ad groups
	 *
	 * @param array $all_weights     Array of weights.
	 * @param array $ad_groups_assoc Array of groups.
	 *
	 * @return void
	 */
	private function loop_ad_groups( &$all_weights, $ad_groups_assoc ): void {
		$post_ad_groups = wp_unslash( Params::post( 'advads-groups', [], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) );

		if ( empty( $post_ad_groups ) ) {
			return;
		}

		foreach ( $post_ad_groups as $_group_id => $_group ) {
			wp_update_term( $_group_id, Entities::TAXONOMY_AD_GROUP, $_group );

			$group = new Advanced_Ads_Group( $_group['id'] );

			if ( isset( $_group['ads'] ) && is_array( $_group['ads'] ) ) {
				foreach ( $_group['ads'] as $_ad_id => $_ad_weight ) {
					/**
					 * Check if this ad is representing the current group and remove it in this case
					 * could cause an infinite loop otherwise
					 * see also /classes/ad_type_group.php::remove_from_ad_group()
					 */
					$ad = Ad_Repository::get( $_ad_id );

					if ( ! isset( $ad_groups_assoc[ $_ad_id ] ) ) {
						$ad_groups_assoc[ $_ad_id ] = $this->get_groups_by_ad_id( $_ad_id );
					}

					if ( isset( $ad->type )
							&& 'group' === $ad->type
							&& isset( $ad->output['group_id'] )
							&& absint( $ad->output['group_id'] ) === $_group_id
					) {
						unset( $_group['ads'][ $_ad_id ] );
					} else {
						$ad_groups_assoc[ $_ad_id ][] = (int) $_group_id;
					}
				}

				$all_weights[ $group->id ] = $this->sanitize_ad_weights( $_group['ads'] );
			}

			// Save other attributes.
			$type     = isset( $_group['type'] ) ? $_group['type'] : 'default';
			$ad_count = isset( $_group['ad_count'] ) ? $_group['ad_count'] : 1;
			$options  = isset( $_group['options'] ) ? $_group['options'] : [];

			// allow other add-ons to save their own group attributes.
			$atts = apply_filters(
				'advanced-ads-group-save-atts',
				[
					'type'     => $type,
					'ad_count' => $ad_count,
					'options'  => $options,
				],
				$_group
			);

			$group->save( $atts );
		}

		foreach ( $ad_groups_assoc as $_ad_id => $group_ids ) {
			wp_set_object_terms( $_ad_id, $group_ids, Entities::TAXONOMY_AD_GROUP );
		}
	}

	/**
	 * Load groups with a given ad in them.
	 *
	 * @param integer $ad_id ad ID.
	 *
	 * @return array
	 */
	private function get_groups_by_ad_id( $ad_id ) {
		$terms = wp_get_object_terms( $ad_id, Entities::TAXONOMY_AD_GROUP );

		return empty( $terms ) || is_wp_error( $terms )
			? [] : wp_list_pluck( $terms, 'term_id' );
	}

	/**
	 * Sanitize ad weights
	 * Make sure keys (ad_ids) can be converted to positive integers and weights are integers as well.
	 *
	 * @param array $weights ad weights array with (key: ad id; value: weight).
	 *
	 * @return array
	 */
	private function sanitize_ad_weights( array $weights ): array {
		$sanitized_weights = [];

		foreach ( $weights as $ad_id => $weight ) {
			$ad_id_int = absint( $ad_id );
			if ( 0 === $ad_id_int || array_key_exists( $ad_id_int, $sanitized_weights ) ) {
				continue;
			}

			$sanitized_weights[ $ad_id_int ] = absint( $weight );
		}

		return $sanitized_weights;
	}
}
