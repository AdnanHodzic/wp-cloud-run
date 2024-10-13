<?php

use AdvancedAds\Utilities\WordPress;

/**
 * Shortcode generator for TinyMCE editor
 *
 * Includes the shortcode plugin inline to prevent it from being blocked by ad blockers.
 */
class Advanced_Ads_Shortcode_Creator {
	/**
	 * Contains ids of the editors that contains the Advanced Ads button.
	 *
	 * @var array
	 */
	private $editors_with_buttons = [];

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Advanced_Ads_Shortcode_Creator constructor.
	 */
	private function __construct() {
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Call needed hooks and functions
	 */
	public function init() {
		$options = Advanced_Ads::get_instance()->options();

		if ( 'true' !== get_user_option( 'rich_editing' )
			|| ! WordPress::user_can( 'advanced_ads_place_ads' )
			|| defined( 'ADVANCED_ADS_DISABLE_SHORTCODE_BUTTON' )
			|| apply_filters( 'advanced-ads-disable-shortcode-button', false )
		) {
			return;
		}

		add_action( 'wp_ajax_advads_content_for_shortcode_creator', [ $this, 'get_content_for_shortcode_creator' ] );

		add_filter( 'mce_buttons', [ $this, 'register_buttons' ], 10, 2 );
		add_filter( 'tiny_mce_plugins', [ $this, 'tiny_mce_plugins' ] );
		add_filter( 'tiny_mce_before_init', [ $this, 'tiny_mce_before_init' ], 10, 2 );

		add_action( 'wp_tiny_mce_init', [ $this, 'print_shortcode_plugin' ] );
		add_action( 'print_default_editor_scripts', [ $this, 'print_shortcode_plugin' ] );
	}

	/**
	 * Check if needed actions and filters have not been removed by a plugin.
	 *
	 * @return array
	 */
	private function hooks_exist() {
		if (
			has_action( 'wp_tiny_mce_init', [ $this, 'print_shortcode_plugin' ] )
			|| has_action( 'print_default_editor_scripts', [ $this, 'print_shortcode_plugin' ] )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Print shortcode plugin inline.
	 *
	 * @param array|null $mce_settings TinyMCE settings array.
	 */
	public function print_shortcode_plugin( $mce_settings = [] ) {
		static $printed = null;

		if ( $printed !== null ) {
			return;
		}

		$printed = true;

		// The `tinymce` argument of the `wp_editor()` function is set  to `false`.
		if ( empty( $mce_settings ) && ! ( doing_action( 'print_default_editor_scripts' ) && user_can_richedit() ) ) {
			return;
		}

		if ( empty( $this->editors_with_buttons ) ) {
			return;
		}

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo "<script>\n"
			. $this->get_l10n() . "\n"
			. file_get_contents( ADVADS_ABSPATH . 'admin/assets/js/shortcode.js' ) . "\n"
			. "</script>\n";
		// phpcs:enable
	}

	/**
	 * Get localization strings.
	 *
	 * @return string
	 */
	private function get_l10n() {
		static $script = null;

		if ( null === $script ) {
			include_once ADVADS_ABSPATH . 'admin/includes/shortcode-creator-l10n.php';
			$script = $strings;
		}

		return $script;
	}

	/**
	 * Add the plugin to the array of default TinyMCE plugins.
	 * We do not use the array of external TinyMCE plugins because we print the plugin file inline.
	 *
	 * @param array $plugins An array of default TinyMCE plugins.
	 * @return array $plugins An array of default TinyMCE plugins.
	 */
	public function tiny_mce_plugins( $plugins ) {
		if ( ! $this->hooks_exist() ) {
			return $plugins;
		}

		$plugins[] = 'advads_shortcode';
		return $plugins;
	}

	/**
	 * Delete the plugin added by the {@see `tiny_mce_plugins`} method when necessary hooks do not exist.
	 *
	 * This is needed because a plugin may call `wp_editor` (which will permanently add our `advads_shortcode` plugin,
	 * because the `tiny_mce_plugins` hooks is called only once) and after that another plugin may call
	 * `remove_all_filters( 'mce_buttons') function that will remove our hook.
	 *
	 * @param array  $mce_init   An array with TinyMCE config.
	 * @param string $editor_id Unique editor identifier.
	 * @return array the TinyMCE config.
	 */
	public function tiny_mce_before_init( $mce_init, $editor_id = '' ) {
		if (
			! isset( $mce_init['plugins'] )
			|| ! is_string( $mce_init['plugins'] )
		) {
			return $mce_init;
		}

		$plugins = explode( ',', $mce_init['plugins'] );
		$found   = array_search( 'advads_shortcode', $plugins, true );

		if ( ! $found || ( $editor_id !== '' && in_array( $editor_id, $this->editors_with_buttons, true ) ) ) {
			return $mce_init;
		}

		unset( $plugins[ $found ] );
		$mce_init['plugins'] = implode( ',', $plugins );

		return $mce_init;
	}

	/**
	 * Add button to tinyMCE window
	 *
	 * @param array  $buttons   Array with existing buttons.
	 * @param string $editor_id Unique editor identifier.
	 *
	 * @return array
	 */
	public function register_buttons( $buttons, $editor_id ) {
		if ( ! $this->hooks_exist() ) {
			return $buttons;
		}
		if ( ! is_array( $buttons ) ) {
			$buttons = [];
		}

		$this->editors_with_buttons[] = $editor_id;
		$buttons                   [] = 'advads_shortcode_button';
		return $buttons;
	}

	/**
	 * Prints html select field for shortcode creator
	 */
	public function get_content_for_shortcode_creator() {
		if ( ! ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) ) ) {
			return;
		}

		$items = self::items_for_select(); ?>

		<select id="advads-select-for-shortcode">
			<option value=""><?php esc_html_e( '--empty--', 'advanced-ads' ); ?></option>
			<?php if ( isset( $items['ads'] ) ) : ?>
				<optgroup label="<?php esc_html_e( 'Ads', 'advanced-ads' ); ?>">
					<?php foreach ( $items['ads'] as $_item_id => $_item_title ) : ?>
					<option value="<?php echo esc_attr( $_item_id ); ?>"><?php echo esc_html( $_item_title ); ?></option>
					<?php endforeach; ?>
				</optgroup>
			<?php endif; ?>
			<?php if ( isset( $items['groups'] ) ) : ?>
				<optgroup label="<?php esc_html_e( 'Ad Groups', 'advanced-ads' ); ?>">
					<?php foreach ( $items['groups'] as $_item_id => $_item_title ) : ?>
					<option value="<?php echo esc_attr( $_item_id ); ?>"><?php echo esc_html( $_item_title ); ?></option>
					<?php endforeach; ?>
				</optgroup>
			<?php endif; ?>
			<?php if ( isset( $items['placements'] ) ) : ?>
				<optgroup label="<?php esc_html_e( 'Placements', 'advanced-ads' ); ?>">
					<?php foreach ( $items['placements'] as $_item_id => $_item_title ) : ?>
					<option value="<?php echo esc_attr( $_item_id ); ?>"><?php echo esc_html( $_item_title ); ?></option>
					<?php endforeach; ?>
				</optgroup>
			<?php endif; ?>
		</select>
		<?php
		exit();
	}

	/**
	 * Get items for item select field
	 *
	 * @return array $select items for select field.
	 */
	public static function items_for_select() {
		$select = [];
		$model  = Advanced_Ads::get_instance()->get_model();

		// load all ads.
		$ads = $model->get_ads(
			[
				'orderby' => 'title',
				'order'   => 'ASC',
			]
		);
		foreach ( $ads as $_ad ) {
			$select['ads'][ 'ad_' . $_ad->ID ] = $_ad->post_title;
		}

		// load all ad groups.
		$groups = $model->get_ad_groups();
		foreach ( $groups as $_group ) {
			$select['groups'][ 'group_' . $_group->term_id ] = $_group->name;
		}

		// load all placements.
		$placements = $model->get_ad_placements_array();
		ksort( $placements );
		foreach ( $placements as $key => $_placement ) {
			$select['placements'][ 'placement_' . $key ] = $_placement['name'];
		}

		return $select;
	}
}
