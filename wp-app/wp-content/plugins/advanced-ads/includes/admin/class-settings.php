<?php
/**
 * Setting class
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Admin;

defined( 'ABSPATH' ) || exit;

use Advanced_Ads;
use Advanced_Ads_Utils;
use AdvancedAds\Constants;
use AdvancedAds\Utilities\Data;
use AdvancedAds\Utilities\Sanitize;
use AdvancedAds\Utilities\WordPress;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

/**
 * Class Settings
 */
class Settings implements Integration_Interface {

	/**
	 * Settings page slug
	 *
	 * @var string
	 */
	const ADVADS_SETTINGS_LICENSES = ADVADS_SLUG . '-licenses';

	/**
	 * Setting options
	 *
	 * @var array with plugin options
	 */
	private $options;

	/**
	 * Hook name
	 *
	 * @var string
	 */
	private $hook;

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'admin_init', [ $this, 'settings_init' ] );
		add_action( 'admin_init', [ $this, 'settings_capabilities' ], 20 );
		add_filter( 'advanced-ads-setting-tabs', [ $this, 'add_tabs' ], 50 );
		add_filter( 'advanced-ads-ad-admin-options', [ $this, 'allow_save_settings' ] );

		$this->options = Advanced_Ads::get_instance()->options();
	}

	/**
	 * Add tabs to the settings page.
	 *
	 * @param array $tabs setting tabs.
	 *
	 * @return array
	 */
	public function add_tabs( array $tabs ): array {
		if ( ! defined( 'AAP_VERSION' ) ) {
			$tabs['pro_pitch'] = [
				'page'  => 'advanced-ads-settings-pro-pitch-page',
				'tabid' => 'pro-pitch',
				'title' => __( 'Pro', 'advanced-ads' ),
			];
		}

		if ( ! defined( 'AAT_VERSION' ) ) {
			$tabs['tracking_pitch'] = [
				'page'  => 'advanced-ads-settings-tracking-pitch-page',
				'tabid' => 'tracking-pitch',
				'title' => __( 'Tracking', 'advanced-ads' ),
			];
		}

		$tabs['licenses'] = [
			'page'  => 'advanced-ads-settings-license-page',
			'group' => self::ADVADS_SETTINGS_LICENSES,
			'tabid' => 'licenses',
			'title' => __( 'Licenses', 'advanced-ads' ),
		];

		return $tabs;
	}

	/**
	 * Initialize settings
	 *
	 * @since 1.0.1
	 *
	 * @return void
	 */
	public function settings_init(): void {
		$this->hook = wp_advads()->screens->get_hook( 'settings' );
		register_setting( ADVADS_SLUG, ADVADS_SLUG, [ $this, 'sanitize_settings' ] );
		register_setting( self::ADVADS_SETTINGS_LICENSES, self::ADVADS_SETTINGS_LICENSES );

		$this->section_management();
		$this->section_disable_ads();
		$this->section_layout();
		$this->section_content_injection();
		$this->section_pro_pitches();
		$this->section_licenses();

		// hook for additional settings from add-ons.
		do_action( 'advanced-ads-settings-init', $this->hook );
	}

	/**
	 * Make sure ad admin can save options.
	 * Add a filter on `admin_init` priority 20 to allow other modules/add-ons to add their options.
	 * Filter option_page_capability_ with the appropriate slug in return to allow the Ad Admin user role to save these settings/options.
	 */
	public function settings_capabilities() {
		$ad_admin_options = [ ADVADS_SLUG ];
		/**
		 * Filters all options that the Ad Admin Role should have access to.
		 *
		 * @param array $ad_admin_options Array with option names.
		 */
		$ad_admin_options = apply_filters( 'advanced-ads-ad-admin-options', $ad_admin_options );
		foreach ( $ad_admin_options as $ad_admin_option ) {
			add_filter(
				'option_page_capability_' . $ad_admin_option,
				function () {
					return Conditional::user_cap( 'advanced_ads_manage_options' );
				}
			);
		}
	}

	/**
	 * Allow Ad Admin to save settings.
	 *
	 * @param string[] $options Array with allowed options.
	 *
	 * @return string[]
	 */
	public function allow_save_settings( $options ) {
		$options[] = ADVADS_SETTINGS_ADBLOCKER;
		$options[] = self::ADVADS_SETTINGS_LICENSES;
		return $options;
	}

	/**
	 * Options to disable ads
	 */
	public function render_settings_disable_ads() {
		$options = Advanced_Ads::get_instance()->options();

		// set the variables.
		$disable_all       = isset( $options['disabled-ads']['all'] ) ? 1 : 0;
		$disable_404       = isset( $options['disabled-ads']['404'] ) ? 1 : 0;
		$disable_archives  = isset( $options['disabled-ads']['archives'] ) ? 1 : 0;
		$disable_secondary = isset( $options['disabled-ads']['secondary'] ) ? 1 : 0;
		$disable_feed      = ( ! isset( $options['disabled-ads']['feed'] ) || $options['disabled-ads']['feed'] ) ? 1 : 0;
		$disable_rest_api  = isset( $options['disabled-ads']['rest-api'] ) ? 1 : 0;

		// load the template.
		include_once ADVADS_ABSPATH . 'views/admin/settings/general/disable-ads.php';
	}

	/**
	 * Render setting to hide ads from logged in users
	 */
	public function render_settings_hide_for_users() {
		global $wp_roles;

		$hide_for_roles = [];
		$options        = Advanced_Ads::get_instance()->options();
		$roles          = $wp_roles->get_names();

		if ( isset( $options['hide-for-user-role'] ) ) {
			$hide_for_roles = Advanced_Ads_Utils::maybe_translate_cap_to_role( $options['hide-for-user-role'] );
		}

		include_once ADVADS_ABSPATH . 'views/admin/settings/general/hide-for-user-role.php';
	}

	/**
	 * Render setting to hide ads from logged in users
	 */
	public function render_settings_hide_for_ip_address() {
		$disable_ip_addr = $this->options['hide-for-ip-address']['enabled'] ?? 0;
		$ip_address      = $this->options['hide-for-ip-address']['ips'] ?? '';

		include_once ADVADS_ABSPATH . 'views/admin/settings/general/hide-for-ip-address.php';
	}

	/**
	 * Render setting to display advanced js file
	 */
	public function render_settings_advanced_js() {
		$options = Advanced_Ads::get_instance()->options();
		$checked = ( ! empty( $options['advanced-js'] ) ) ? 1 : 0;

		include_once ADVADS_ABSPATH . 'views/admin/settings/general/advanced-js.php';
	}

	/**
	 * Render setting for content injection protection
	 */
	public function render_settings_content_injection_everywhere() {
		$options = Advanced_Ads::get_instance()->options();
		$enabled = $options['content-injection-enabled'] ?? '';
		if ( ! isset( $options['content-injection-everywhere'] ) ) {
			$everywhere = 0;
		} elseif ( 'true' === $options['content-injection-everywhere'] ) {
			$everywhere = - 1;
		} else {
			$everywhere = absint( $options['content-injection-everywhere'] );
		}
		include_once ADVADS_ABSPATH . 'views/admin/settings/general/content-injection-everywhere.php';
	}

	/**
	 * Render setting for content injection priority
	 */
	public function render_settings_content_injection_priority() {
		$options  = Advanced_Ads::get_instance()->options();
		$priority = ( isset( $options['content-injection-priority'] ) ) ? (int) $options['content-injection-priority'] : 100;

		include_once ADVADS_ABSPATH . 'views/admin/settings/general/content-injection-priority.php';
	}

	/**
	 * Render setting to disable content injection level limitation
	 */
	public function render_settings_content_injection_level_limitation() {
		$options = Advanced_Ads::get_instance()->options();
		$checked = ( ! empty( $options['content-injection-level-disabled'] ) ) ? 1 : 0;

		include_once ADVADS_ABSPATH . 'views/admin/settings/general/content-injection-level-limitation.php';
	}

	/**
	 * Render setting for blocking bots
	 */
	public function render_settings_block_bots() {
		$options = Advanced_Ads::get_instance()->options();
		$checked = ( ! empty( $options['block-bots'] ) ) ? 1 : 0;

		include_once ADVADS_ABSPATH . 'views/admin/settings/general/block-bots.php';
	}

	/**
	 * Render setting to disable ads by post types
	 */
	public function render_settings_disable_post_types() {
		$post_types        = get_post_types(
			[
				'public'             => true,
				'publicly_queryable' => true,
			],
			'objects',
			'or'
		);
		$type_label_counts = array_count_values( wp_list_pluck( $post_types, 'label' ) );

		include_once ADVADS_ABSPATH . '/views/admin/settings/general/disable-post-types.php';
	}

	/**
	 * Render setting to disable notices and Ad Health
	 */
	public function render_settings_disabled_notices() {
		$options = Advanced_Ads::get_instance()->options();
		$checked = ( ! empty( $options['disable-notices'] ) ) ? 1 : 0;

		include_once ADVADS_ABSPATH . '/views/admin/settings/general/disable-notices.php';
	}

	/**
	 * Render setting for frontend prefix
	 */
	public function render_settings_front_prefix() {
		$options = Advanced_Ads::get_instance()->options();

		$prefix     = wp_advads()->get_frontend_prefix();
		$old_prefix = ( isset( $options['id-prefix'] ) ) ? esc_attr( $options['id-prefix'] ) : '';

		include_once ADVADS_ABSPATH . '/views/admin/settings/general/frontend-prefix.php';
	}

	/**
	 * Render setting to allow editors to manage ads
	 */
	public function render_settings_editors_manage_ads() {
		$allow   = false;
		$options = Advanced_Ads::get_instance()->options();

		// is false by default if no options where previously set.
		if ( isset( $options['editors-manage-ads'] ) && $options['editors-manage-ads'] ) {
			$allow = true;
		}

		include_once ADVADS_ABSPATH . '/views/admin/settings/general/editors-manage-ads.php';
	}

	/**
	 * Prepare the template for multisite allow unfiltered_html settings.
	 *
	 * @return void
	 */
	public function renders_settings_allow_unfiltered_html() {
		$options               = Advanced_Ads::get_instance()->options();
		$user_roles_to_display = Data::get_filtered_roles_by_cap();

		if ( empty( $user_roles_to_display ) ) {
			return;
		}

		if ( ! isset( $options['allow-unfiltered-html'] ) ) {
			$options['allow-unfiltered-html'] = [];
		}

		$allowed_roles = $options['allow-unfiltered-html'];

		include_once ADVADS_ABSPATH . '/views/admin/settings/general/allow-unfiltered-html.php';
	}

	/**
	 * Render setting to add an "Advertisement" label before ads
	 */
	public function render_settings_add_custom_label() {
		$options = Advanced_Ads::get_instance()->options();

		$enabled      = isset( $options['custom-label']['enabled'] );
		$label        = ! empty( $options['custom-label']['text'] ) ? esc_html( $options['custom-label']['text'] ) : _x( 'Advertisements', 'label before ads', 'advanced-ads' );
		$html_enabled = $options['custom-label']['html_enabled'] ?? false;

		include_once ADVADS_ABSPATH . '/views/admin/settings/general/custom-label.php';
	}

	/**
	 * Render link target="_blank" setting
	 *
	 * @since 1.8.4 – moved here from Tracking add-on
	 */
	public function render_settings_link_target_callback() {

		// get option if saved for tracking.
		$options = Advanced_Ads::get_instance()->options();
		if ( ! isset( $options['target-blank'] ) && function_exists( 'wp_advads_tracking' ) ) {
			$tracking_target = wp_advads_tracking()->options->get( 'target' );
			if ( $tracking_target ) {
				$options['target-blank'] = $tracking_target;
			}
		}

		$target = isset( $options['target-blank'] ) ? $options['target-blank'] : 0;
		include_once ADVADS_ABSPATH . 'views/admin/settings/general/link-target.php';
	}

	/**
	 * Render setting 'Delete data on uninstall"
	 */
	public function render_settings_uninstall_delete_data() {
		$options = Advanced_Ads::get_instance()->options();
		$enabled = ! empty( $options['uninstall-delete-data'] );

		include_once ADVADS_ABSPATH . 'views/admin/settings/general/uninstall-delete-data.php';
	}

	/**
	 * Sanitize plugin settings
	 *
	 * @param array $options all the options.
	 *
	 * @return array sanitized options.
	 */
	public function sanitize_settings( $options ) {
		if ( isset( $options['front-prefix'] ) ) {
			$options['front-prefix'] = Sanitize::frontend_prefix(
				$options['front-prefix'],
				Constants::DEFAULT_FRONTEND_PREFIX
			);
		}

		$options = apply_filters( 'advanced-ads-sanitize-settings', $options );

		// check if editors can edit ads now and set the rights
		// else, remove that right.
		$editor_role = get_role( 'editor' );
		if ( null === $editor_role ) {
			return $options;
		}

		$action = isset( $options['editors-manage-ads'] ) && $options['editors-manage-ads']
			? 'add_cap' : 'remove_cap';

		$editor_role->$action( 'advanced_ads_see_interface' );
		$editor_role->$action( 'advanced_ads_edit_ads' );
		$editor_role->$action( 'advanced_ads_manage_placements' );
		$editor_role->$action( 'advanced_ads_place_ads' );

		// we need 3 states: ! empty, 1, 0.
		$options['disabled-ads']['feed'] = ! empty( $options['disabled-ads']['feed'] ) ? 1 : 0;

		if ( isset( $options['content-injection-everywhere'] ) ) {
			if ( '0' === $options['content-injection-everywhere'] ) {
				unset( $options['content-injection-everywhere'] );
			} elseif ( 'true' === $options['content-injection-everywhere'] || $options['content-injection-everywhere'] <= - 1 ) {
				// Note: the option may be already set 'true' during import.
				$options['content-injection-everywhere'] = 'true';
			} else {
				$options['content-injection-everywhere'] = absint( $options['content-injection-everywhere'] );
			}
		}

		return $options;
	}

	/**
	 * Add management section
	 *
	 * @return void
	 */
	private function section_management(): void {
		$section_id = 'advanced_ads_setting_section';
		add_settings_section(
			$section_id,
			__( 'Admin', 'advanced-ads' ),
			'__return_empty_string',
			$this->hook
		);

		add_settings_field(
			'disable-notices',
			__( 'Disable Ad Health and other notices', 'advanced-ads' ),
			[ $this, 'render_settings_disabled_notices' ],
			$this->hook,
			$section_id
		);

		add_settings_field(
			'editors-manage-ads',
			__( 'Allow editors to manage ads', 'advanced-ads' ),
			[ $this, 'render_settings_editors_manage_ads' ],
			$this->hook,
			$section_id
		);

		if (
			is_multisite()
			// Allow superadmins to edit the setting when DISALLOW_UNFILTERED_HTML is defined.
			&& ( current_user_can( 'unfiltered_html' ) || is_super_admin() )
		) {
			add_settings_field(
				'allow-unfiltered-html',
				/* translators: unfiltered_html */
				sprintf( __( 'Add the %s capability to user roles on multisite', 'advanced-ads' ), '<code>unfiltered_html</code>' ),
				[ $this, 'renders_settings_allow_unfiltered_html' ],
				$this->hook,
				$section_id
			);
		}

		if ( is_main_site() ) {
			add_settings_field(
				'uninstall-delete-data',
				__( 'Delete data on uninstall', 'advanced-ads' ),
				[ $this, 'render_settings_uninstall_delete_data' ],
				$this->hook,
				$section_id
			);
		}
	}

	/**
	 * Disable ads section
	 *
	 * @return void
	 */
	private function section_disable_ads(): void {
		$section_id = 'advanced_ads_setting_section_disable_ads';
		add_settings_section(
			$section_id,
			__( 'Disable ads', 'advanced-ads' ),
			'__return_empty_string',
			$this->hook
		);

		add_settings_field(
			'disable-ads',
			__( 'Disable ads', 'advanced-ads' ),
			[ $this, 'render_settings_disable_ads' ],
			$this->hook,
			$section_id
		);

		add_settings_field(
			'hide-for-user-role',
			__( 'Hide ads for user roles', 'advanced-ads' ),
			[ $this, 'render_settings_hide_for_users' ],
			$this->hook,
			$section_id
		);

		add_settings_field(
			'hide-for-ip-address',
			__( 'Hide ads for IP addresses', 'advanced-ads' ),
			[ $this, 'render_settings_hide_for_ip_address' ],
			$this->hook,
			$section_id
		);

		add_settings_field(
			'block-bots',
			__( 'Hide ads from bots', 'advanced-ads' ),
			[ $this, 'render_settings_block_bots' ],
			$this->hook,
			$section_id
		);

		if ( ! defined( 'AAP_VERSION' ) ) {
			add_settings_field(
				'disable-by-post-types-pro',
				__( 'Disable ads for post types', 'advanced-ads' ),
				[ $this, 'render_settings_disable_post_types' ],
				$this->hook,
				$section_id
			);
		}
	}

	/**
	 * Layout section
	 *
	 * @return void
	 */
	private function section_layout(): void {
		$section_id = 'advanced_ads_setting_section_output';
		add_settings_section(
			$section_id,
			__( 'Layout / Output', 'advanced-ads' ),
			'__return_empty_string',
			$this->hook
		);

		add_settings_field(
			'front-prefix',
			__( 'ID prefix', 'advanced-ads' ),
			[ $this, 'render_settings_front_prefix' ],
			$this->hook,
			$section_id
		);

		add_settings_field(
			'add-custom-label',
			__( 'Ad label', 'advanced-ads' ),
			[ $this, 'render_settings_add_custom_label' ],
			$this->hook,
			$section_id
		);

		add_settings_field(
			'link-target',
			__( 'Open links in a new window', 'advanced-ads' ),
			[ $this, 'render_settings_link_target_callback' ],
			$this->hook,
			$section_id
		);

		add_settings_field(
			'activate-advanced-js',
			__( 'Use advanced JavaScript', 'advanced-ads' ),
			[ $this, 'render_settings_advanced_js' ],
			$this->hook,
			$section_id
		);
	}

	/**
	 * Content injection section
	 *
	 * @return void
	 */
	private function section_content_injection(): void {
		$section_id = 'advanced_ads_setting_section_injection';
		add_settings_section(
			$section_id,
			__( 'Content injection', 'advanced-ads' ),
			'__return_empty_string',
			$this->hook
		);

		add_settings_field(
			'content-injection-everywhere',
			__( 'Content placement in post lists', 'advanced-ads' ),
			[ $this, 'render_settings_content_injection_everywhere' ],
			$this->hook,
			$section_id
		);

		add_settings_field(
			'content-injection-priority',
			__( 'Priority of content injection filter', 'advanced-ads' ),
			[ $this, 'render_settings_content_injection_priority' ],
			$this->hook,
			$section_id
		);

		add_settings_field(
			'content-injection-level-limitation',
			__( 'Disable level limitation', 'advanced-ads' ),
			[ $this, 'render_settings_content_injection_level_limitation' ],
			$this->hook,
			$section_id
		);
	}

	/**
	 * Pro pitches section
	 *
	 * @return void
	 */
	private function section_pro_pitches(): void {
		// Pro pitch section.
		if ( ! defined( 'AAP_VERSION' ) ) {
			add_settings_section(
				'advanced_ads_settings_pro_pitch_section',
				'',
				[ $this, 'render_settings_pro_pitch_section_callback' ],
				'advanced-ads-settings-pro-pitch-page'
			);
		}

		// Tracking pitch section.
		if ( ! defined( 'AAT_VERSION' ) ) {
			add_settings_section(
				'advanced_ads_settings_tracking_pitch_section',
				'',
				[ $this, 'render_settings_tracking_pitch_section_callback' ],
				'advanced-ads-settings-tracking-pitch-page'
			);
		}
	}

	/**
	 * Render pitch for Pro
	 *
	 * @return void
	 */
	public function render_settings_pro_pitch_section_callback() {
		echo '<br/>';
		include ADVADS_ABSPATH . 'views/admin/upgrades/pro-tab.php';
	}

	/**
	 * Render tracking pitch settings section
	 */
	public function render_settings_tracking_pitch_section_callback() {
		echo '<br/>';
		include ADVADS_ABSPATH . 'views/marketing/ad-metabox-tracking.php';
	}

	/**
	 * Licenses section
	 *
	 * @return void
	 */
	private function section_licenses(): void {
		add_settings_section(
			'advanced_ads_settings_license_section',
			'',
			[ $this, 'render_settings_licenses_section_callback' ],
			'advanced-ads-settings-license-page'
		);

		add_settings_section(
			'advanced_ads_settings_license_pitch_section',
			'',
			[ $this, 'render_settings_licenses_pitch_section_callback' ],
			'advanced-ads-settings-license-page'
		);
	}
	/**
	 * Render licenses settings section
	 */
	public function render_settings_licenses_section_callback() {
		include ADVADS_ABSPATH . 'views/admin/settings/license/section-help.php';
	}

	/**
	 * Render licenses pithces settings section
	 */
	public function render_settings_licenses_pitch_section_callback() {
		echo '<h3>' . esc_attr__( 'Are you missing something?', 'advanced-ads' ) . '</h3>';
		\Advanced_Ads_Overview_Widgets_Callbacks::render_addons( true, false );
	}
}
