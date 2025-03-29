<?php
/**
 * Admin Shortcode Creator.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Admin;

use _WP_Editors;
use AdvancedAds\Utilities\Data;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Shortcode Creator.
 */
class Shortcode_Creator implements Integration_Interface {

	/**
	 * Contains ids of the editors that contains the Advanced Ads button.
	 *
	 * @var array
	 */
	private $editors = [];

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		if (
			true !== boolval( get_user_option( 'rich_editing' ) ) ||
			! Conditional::user_can( 'advanced_ads_place_ads' ) ||
			defined( 'ADVANCED_ADS_DISABLE_SHORTCODE_BUTTON' ) ||
			apply_filters( 'advanced-ads-disable-shortcode-button', false )
		) {
			return;
		}

		add_filter( 'mce_buttons', [ $this, 'register_buttons' ], 10, 2 );
		add_filter( 'tiny_mce_plugins', [ $this, 'register_plugin' ] );
		add_filter( 'tiny_mce_before_init', [ $this, 'tiny_mce_before_init' ], 10, 2 );

		add_action( 'wp_tiny_mce_init', [ $this, 'print_shortcode_plugin' ] );
		add_action( 'print_default_editor_scripts', [ $this, 'print_shortcode_plugin' ] );

		add_action( 'wp_ajax_advads_content_for_shortcode_creator', [ $this, 'get_content_for_shortcode_creator' ] );
	}

	/**
	 * Add button to tinyMCE window
	 *
	 * @param array  $buttons   Array with existing buttons.
	 * @param string $editor_id Unique editor identifier.
	 *
	 * @return array
	 */
	public function register_buttons( $buttons, $editor_id ): array {
		if ( ! $this->hooks_exist() ) {
			return $buttons;
		}

		if ( ! is_array( $buttons ) ) {
			$buttons = [];
		}

		$this->editors[] = $editor_id;
		$buttons[]       = 'advads_shortcode_button';

		return $buttons;
	}

	/**
	 * Add the plugin to the array of default TinyMCE plugins.
	 *
	 * @param array $plugins An array of default TinyMCE plugins.
	 *
	 * @return array
	 */
	public function register_plugin( $plugins ): array {
		if ( ! $this->hooks_exist() ) {
			return $plugins;
		}

		$plugins[] = 'advads_shortcode';

		return $plugins;
	}

	/**
	 * Delete the plugin added by the {@see `tiny_mce_plugins`} method when necessary hooks do not exist.
	 *
	 * @param array  $mce_init   An array with TinyMCE config.
	 * @param string $editor_id Unique editor identifier.
	 *
	 * @return array
	 */
	public function tiny_mce_before_init( $mce_init, $editor_id = '' ): array {
		// Early bail!!
		if ( ! isset( $mce_init['plugins'] ) || ! is_string( $mce_init['plugins'] ) ) {
			return $mce_init;
		}

		$plugins = explode( ',', $mce_init['plugins'] );
		$found   = array_search( 'advads_shortcode', $plugins, true );

		if ( ! $found || ( '' !== $editor_id && in_array( $editor_id, $this->editors, true ) ) ) {
			return $mce_init;
		}

		unset( $plugins[ $found ] );
		$mce_init['plugins'] = implode( ',', $plugins );

		return $mce_init;
	}

	/**
	 * Print shortcode plugin inline.
	 *
	 * @param array|null $mce_settings TinyMCE settings array.
	 *
	 * @return void
	 */
	public function print_shortcode_plugin( $mce_settings = [] ): void {
		static $printed = null;

		if ( null !== $printed ) {
			return;
		}

		$printed = true;

		// The `tinymce` argument of the `wp_editor()` function is set  to `false`.
		if ( empty( $mce_settings ) && ! ( doing_action( 'print_default_editor_scripts' ) && user_can_richedit() ) ) {
			return;
		}

		if ( empty( $this->editors ) ) {
			return;
		}

		?>
		<script type="text/javascript">
			<?php echo $this->get_l10n(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php echo file_get_contents( ADVADS_ABSPATH . 'assets/js/admin/shortcode.js' ); // phpcs:ignore ?>
		</script>
		<?php
	}

	/**
	 * Prints html select field for shortcode creator
	 *
	 * @return void
	 */
	public function get_content_for_shortcode_creator(): void {
		if ( ! ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) ) ) {
			return;
		}

		Data::items_dropdown( [ 'id' => 'advads-select-for-shortcode' ] );
		exit();
	}

	/**
	 * Check if needed actions have not been removed by a plugin.
	 *
	 * @return array
	 */
	private function hooks_exist() {
		if (
			has_action( 'wp_tiny_mce_init', [ $this, 'print_shortcode_plugin' ] ) ||
			has_action( 'print_default_editor_scripts', [ $this, 'print_shortcode_plugin' ] )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Get localization strings.
	 *
	 * @return string
	 */
	private function get_l10n() {
		$strings = [
			'title'  => esc_html_x( 'Add an ad', 'shortcode creator', 'advanced-ads' ),
			'ok'     => esc_html_x( 'Add shortcode', 'shortcode creator', 'advanced-ads' ),
			'cancel' => esc_html_x( 'Cancel', 'shortcode creator', 'advanced-ads' ),
			'image'  => ADVADS_BASE_URL . 'assets/img/icons/tinymce-icon.png',
		];
		$locale  = _WP_Editors::get_mce_locale();

		return 'tinyMCE.addI18n("' . $locale . '.advads_shortcode", ' . wp_json_encode( $strings ) . ");\n";
	}
}
