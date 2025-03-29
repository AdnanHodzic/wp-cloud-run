<?php
/**
 * This class represents the "Group" ad type.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Ads\Types;

use AdvancedAds\Constants;
use AdvancedAds\Ads\Ad_Group;
use AdvancedAds\Interfaces\Ad_Type;

defined( 'ABSPATH' ) || exit;

/**
 * Type Group.
 */
class Group implements Ad_Type {

	/**
	 * Get the unique identifier (ID) of the ad type.
	 *
	 * @return string The unique ID of the ad type.
	 */
	public function get_id(): string {
		return 'group';
	}

	/**
	 * Get the class name of the object as a string.
	 *
	 * @return string
	 */
	public function get_classname(): string {
		return Ad_Group::class;
	}

	/**
	 * Get the title or name of the ad type.
	 *
	 * @return string The title of the ad type.
	 */
	public function get_title(): string {
		return __( 'Ad Group', 'advanced-ads' );
	}

	/**
	 * Get a description of the ad type.
	 *
	 * @return string The description of the ad type.
	 */
	public function get_description(): string {
		return __( 'Choose an existing ad group. Use this type when you want to assign the same display and visitor conditions to all ads in that group.', 'advanced-ads' );
	}

	/**
	 * Check if this ad type requires premium.
	 *
	 * @return bool True if premium is required; otherwise, false.
	 */
	public function is_premium(): bool {
		return false;
	}

	/**
	 * Get the URL for upgrading to this ad type.
	 *
	 * @return string The upgrade URL for the ad type.
	 */
	public function get_upgrade_url(): string {
		return '';
	}

	/**
	 * Get the URL for upgrading to this ad type.
	 *
	 * @return string The upgrade URL for the ad type.
	 */
	public function get_image(): string {
		return ADVADS_BASE_URL . 'assets/img/ad-types/group.svg';
	}

	/**
	 * Check if this ad type has size parameters.
	 *
	 * @return bool True if has size parameters; otherwise, false.
	 */
	public function has_size(): bool {
		return true;
	}

	/**
	 * Output for the ad parameters metabox
	 *
	 * @param Ad_Group $ad Ad instance.
	 *
	 * @return void
	 */
	public function render_parameters( $ad ): void {
		?>
		<label for="advads-group-id" class="label">
			<?php esc_html_e( 'Ad Group', 'advanced-ads' ); ?>
		</label>

		<div>
			<?php
			wp_dropdown_categories(
				[
					'name'             => 'advanced_ad[output][group_id]',
					'id'               => 'advads-group-id',
					'selected'         => $ad->get_group_id() ?? '',
					'taxonomy'         => Constants::TAXONOMY_GROUP,
					'hide_empty'       => false,
					'show_option_none' => esc_html__( 'Select a group', 'advanced-ads' ),
				]
			);
			?>
		</div>
		<hr/>
		<?php
	}
}
