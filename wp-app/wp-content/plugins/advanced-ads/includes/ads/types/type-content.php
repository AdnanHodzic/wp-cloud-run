<?php
/**
 * This class represents the "Content" ad type.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Ads\Types;

use AdvancedAds\Ads\Ad_Content;
use AdvancedAds\Interfaces\Ad_Type;

defined( 'ABSPATH' ) || exit;

/**
 * Type Content.
 */
class Content implements Ad_Type {

	/**
	 * Get the unique identifier (ID) of the ad type.
	 *
	 * @return string The unique ID of the ad type.
	 */
	public function get_id(): string {
		return 'content';
	}

	/**
	 * Get the class name of the object as a string.
	 *
	 * @return string
	 */
	public function get_classname(): string {
		return Ad_Content::class;
	}

	/**
	 * Get the title or name of the ad type.
	 *
	 * @return string The title of the ad type.
	 */
	public function get_title(): string {
		return __( 'Rich Content', 'advanced-ads' );
	}

	/**
	 * Get a description of the ad type.
	 *
	 * @return string The description of the ad type.
	 */
	public function get_description(): string {
		return __( 'The full content editor from WordPress with all features like shortcodes, image upload or styling, but also simple text/html mode for scripts and code.', 'advanced-ads' );
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
		return ADVADS_BASE_URL . 'assets/img/ad-types/content.svg';
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
	 * @param Ad_Content $ad Ad instance.
	 *
	 * @return void
	 */
	public function render_parameters( $ad ): void {
		$content = $ad->get_content() ?? '';

		/**
		 * Build the tinymc editor
		 *
		 * @link http://codex.wordpress.org/Function_Reference/wp_editor
		 *
		 * Don’t build it when ajax is used; display message and buttons instead
		 */
		if ( wp_doing_ajax() ) : ?>
			<textarea id="advads-ad-content-plain" style="display:none;" cols="40" rows="10" name="advanced_ad[content]">
				<?php echo esc_textarea( $content ); ?>
			</textarea>
			<?php
		else :
			if ( ! user_can_richedit() ) {
				$content = esc_textarea( $content );
			}
			add_filter( 'tiny_mce_before_init', [ $this, 'tiny_mce_before_init' ], 10, 2 );

			$args = [
				'textarea_name'    => 'advanced_ad[content]',
				'textarea_rows'    => 10,
				'drag_drop_upload' => true,
			];
			wp_editor( $content, 'advanced-ad-parameters-content', $args );
		endif;
		?>
		<br class="clear"/>
		<input type="hidden" name="advanced_ad[output][allow_shortcodes]" value="1" />
		<?php
		include ADVADS_ABSPATH . 'views/admin/metaboxes/ads/ad-info-after-textarea.php';
	}

	/**
	 * Add JS into tinyMCE
	 *
	 * @param array  $init_array TinyMCE arguments.
	 * @param string $editor_id  Editor id.
	 *
	 * @return array
	 */
	public function tiny_mce_before_init( array $init_array, $editor_id ): array {
		if ( 'advanced-ad-parameters-content' !== $editor_id ) {
			return $init_array;
		}

		// Add a JS listener to trigger an `input` event for the rich text textarea.
				$init_array['setup'] = <<<'JS'
[editor => {
	const textarea = document.getElementById('advanced-ad-parameters-content');
	editor.on('Dirty', event => {
		textarea.value = editor.getContent();
		textarea.dispatchEvent(new Event('input'));
	});
}][0]
JS;
		return $init_array;
	}
}
