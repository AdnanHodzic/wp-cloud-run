<?php
/**
 * This class represents the "Plain" ad type.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Ads\Types;

use AdvancedAds\Ads\Ad_Plain;
use AdvancedAds\Interfaces\Ad_Type;
use AdvancedAds\Utilities\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * Type Plain.
 */
class Plain implements Ad_Type {

	/**
	 * Get the unique identifier (ID) of the ad type.
	 *
	 * @return string The unique ID of the ad type.
	 */
	public function get_id(): string {
		return 'plain';
	}

	/**
	 * Get the class name of the object as a string.
	 *
	 * @return string
	 */
	public function get_classname(): string {
		return Ad_Plain::class;
	}

	/**
	 * Get the title or name of the ad type.
	 *
	 * @return string The title of the ad type.
	 */
	public function get_title(): string {
		return __( 'Plain Text and Code', 'advanced-ads' );
	}

	/**
	 * Get a description of the ad type.
	 *
	 * @return string The description of the ad type.
	 */
	public function get_description(): string {
		return __( 'Any ad network, Amazon, customized AdSense codes, shortcodes, and code like JavaScript, HTML or PHP.', 'advanced-ads' );
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
		return ADVADS_BASE_URL . 'assets/img/ad-types/plain.svg';
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
	 * @param Ad_Plain $ad Ad instance.
	 *
	 * @return void
	 */
	public function render_parameters( $ad ): void {
		$content = $ad->get_content() ?? '';
		?>
		<p class="description">
			<?php esc_html_e( 'Insert plain text or code into this field.', 'advanced-ads' ); ?>
		</p>
		<?php $this->error_unfiltered_html( $ad ); ?>
		<textarea
			id="advads-ad-content-plain"
			cols="40"
			rows="10"
			name="advanced_ad[content]"
		><?php echo esc_textarea( $content ); ?></textarea>
		<?php
		include ADVADS_ABSPATH . 'views/admin/metaboxes/ads/ad-info-after-textarea.php';

		$this->render_php_allow( $ad );
		$this->render_shortcodes_allow( $ad );
		?>
		<?php
	}

	/**
	 * Render php output field
	 *
	 * @param Ad_Plain $ad Ad instance.
	 *
	 * @return void
	 */
	private function render_php_allow( $ad ) {
		?>
		<label class="label" for="advads-parameters-php">
			<?php esc_html_e( 'Allow PHP', 'advanced-ads' ); ?>
		</label>
		<div>
			<input type="hidden" name="advanced_ad[allow_php]" value="off">
			<input id="advads-parameters-php" type="checkbox" name="advanced_ad[allow_php]" value="on"<?php checked( $ad->is_php_allowed() ); ?><?php disabled( ! Conditional::is_php_allowed() ); ?> />
			<span class="advads-help">
				<span class="advads-tooltip">
					<?php
					echo wp_kses(
						__( 'Execute PHP code (wrapped in <code>&lt;?php ?&gt;</code>)', 'advanced-ads' ),
						[
							'code' => [],
						]
					);
					?>
				</span>
			</span>
			<?php if ( ! Conditional::is_php_allowed() ) : ?>
				<p class="advads-notice-inline advads-error">
					<?php
					printf(
					/* translators: The name of the constant preventing PHP execution */
						esc_html__( 'Executing PHP code has been disallowed by %s', 'advanced-ads' ),
						sprintf( '<code>%s</code>', defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ? 'DISALLOW_FILE_EDIT' : 'ADVANCED_ADS_DISALLOW_PHP' )
					);
					?>
				</p>
			<?php else : ?>
				<p class="advads-notice-inline advads-error" id="advads-allow-php-warning" style="display:none;">
					<?php esc_html_e( 'Using PHP code can be dangerous. Please make sure you know what you are doing.', 'advanced-ads' ); ?>
				</p>
			<?php endif; ?>
			<p class="advads-notice-inline advads-error" id="advads-parameters-php-warning" style="display:none;">
				<?php esc_html_e( 'No PHP tag detected in your code.', 'advanced-ads' ); ?> <?php esc_html_e( 'Uncheck this checkbox for improved performance.', 'advanced-ads' ); ?>
			</p>
		</div>
		<hr/>
		<?php
	}

	/**
	 * Render allow shortcodes field.
	 *
	 * @param Ad_Plain $ad Ad instance.
	 *
	 * @return void
	 */
	private function render_shortcodes_allow( $ad ): void {
		$allow_shortcodes = absint( $ad->is_shortcode_allowed() );
		?>
		<label class="label"
				for="advads-parameters-shortcodes"><?php esc_html_e( 'Execute shortcodes', 'advanced-ads' ); ?></label>
		<div>
			<input type="hidden" name="advanced_ad[output][allow_shortcodes]" value="off"/>
			<input id="advads-parameters-shortcodes" type="checkbox" name="advanced_ad[output][allow_shortcodes]" value="on" <?php checked( $allow_shortcodes ); ?>/>
			<p class="advads-notice-inline advads-error" id="advads-parameters-shortcodes-warning"
					style="display:none;"><?php esc_html_e( 'No shortcode detected in your code.', 'advanced-ads' ); ?> <?php esc_html_e( 'Uncheck this checkbox for improved performance.', 'advanced-ads' ); ?></p>
		</div>
		<hr/>
		<?php
	}

	/**
	 * Check if we're on an ad edit screen, if yes and the user does not have `unfiltered_html` permissions,
	 * show an admin notice.
	 *
	 * @param Ad_Plain $ad Ad instance.
	 *
	 * @return void
	 */
	private function error_unfiltered_html( $ad ): void {
		$author_id       = absint( get_post_field( 'post_author', $ad->get_id() ) );
		$user            = wp_get_current_user();
		$current_user_id = $user->ID;

		if ( Conditional::can_author_unfiltered_html( $author_id ) ) {
			return;
		}

		?>
		<p class="advads-notice-inline advads-error">
			<?php
			if ( $author_id === $current_user_id ) {
				esc_html_e( 'You do not have sufficient permissions to include all HTML tags.', 'advanced-ads' );
			} else {
				esc_html_e( 'The creator of the ad does not have sufficient permissions to include all HTML tags.', 'advanced-ads' );
				if (
					current_user_can( 'unfiltered_html' )
					&& Conditional::has_user_role_on_site()
					&& ! empty( $user->caps['administrator'] ) // A superadmin won't be listed in the author dropdown if he's registered as something other than admin on a blog of the network.
				) {
					printf( '<button type="button" onclick="(()=>Advanced_Ads_Admin.reassign_ad(%d))();" class="button button-primary">%s</button>', esc_attr( $current_user_id ), esc_html__( 'Assign ad to me', 'advanced-ads' ) );
				}
			}
			?>
			<a href="https://wpadvancedads.com/manual/ad-types/#Plain_Text_and_Code" class="advads-manual-link" target="_blank" rel="noopener">
				<?php esc_html_e( 'Manual', 'advanced-ads' ); ?>
			</a>
		</p>
		<?php
	}
}
