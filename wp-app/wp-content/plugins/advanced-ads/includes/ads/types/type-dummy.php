<?php
/**
 * This class represents the "Dummy" ad type.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Ads\Types;

use AdvancedAds\Ads\Ad_Dummy;
use AdvancedAds\Interfaces\Ad_Type;

defined( 'ABSPATH' ) || exit;

/**
 * Type Dummy.
 */
class Dummy implements Ad_Type {

	/**
	 * Get the unique identifier (ID) of the ad type.
	 *
	 * @return string The unique ID of the ad type.
	 */
	public function get_id(): string {
		return 'dummy';
	}

	/**
	 * Get the class name of the object as a string.
	 *
	 * @return string
	 */
	public function get_classname(): string {
		return Ad_Dummy::class;
	}

	/**
	 * Get the title or name of the ad type.
	 *
	 * @return string The title of the ad type.
	 */
	public function get_title(): string {
		return __( 'Dummy', 'advanced-ads' );
	}

	/**
	 * Get a description of the ad type.
	 *
	 * @return string The description of the ad type.
	 */
	public function get_description(): string {
		return __( 'Uses a simple placeholder ad for quick testing.', 'advanced-ads' );
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
		return ADVADS_BASE_URL . 'assets/img/ad-types/dummy.svg';
	}

	/**
	 * Check if this ad type has size parameters.
	 *
	 * @return bool True if has size parameters; otherwise, false.
	 */
	public function has_size(): bool {
		return false;
	}

	/**
	 * Output for the ad parameters metabox
	 *
	 * @param Ad_Dummy $ad Ad instance.
	 *
	 * @return void
	 */
	public function render_parameters( $ad ): void {
		if ( ! defined( 'AAT_VERSION' ) ) :
			$url = $ad->get_url() ?? home_url();
			?>
			<span class="label"><?php esc_html_e( 'URL', 'advanced-ads' ); ?></span>
			<div>
				<input type="text" name="advanced_ad[url]" id="advads-url" class="advads-ad-url" value="<?php echo esc_url( $url ); ?>" />
			</div>
			<hr/>
			<?php
		endif;
		?>

		<img src="<?php echo esc_url( ADVADS_BASE_URL ) . 'public/assets/img/dummy.jpg'; ?>" alt="" width="300" height="250" />
		<input type="hidden" name="advanced_ad[width]" value="300" />
		<input type="hidden" name="advanced_ad[height]" value="250" />
		<?php
	}
}
