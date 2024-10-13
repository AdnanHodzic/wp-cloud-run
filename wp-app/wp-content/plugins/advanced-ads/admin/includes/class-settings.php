<?php

use AdvancedAds\Utilities\WordPress;

/**
 * Class Advanced_Ads_Admin_Settings
 */
class Advanced_Ads_Admin_Settings {
	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Advanced_Ads_Admin_Settings constructor.
	 */
	private function __construct() {
		// settings handling.
		add_action( 'admin_init', [ $this, 'settings_init' ] );

		// add ad admin capabilities for settings.
		add_action( 'admin_init', [ $this, 'settings_capabilities' ], 20 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize settings
	 *
	 * @since 1.0.1
	 */
	public function settings_init() {

		// get settings page hook.
		$hook = Advanced_Ads_Admin::get_instance()->plugin_screen_hook_suffix;

		// register settings.
		register_setting( ADVADS_SLUG, ADVADS_SLUG, [ $this, 'sanitize_settings' ] );

		// "Management" settings section.
		add_settings_section(
			'advanced_ads_setting_section',
			__( 'Admin', 'advanced-ads' ),
			[ $this, 'render_settings_section_callback' ],
			$hook
		);

		// "Disable ads"  settings section.
		add_settings_section(
			'advanced_ads_setting_section_disable_ads',
			__( 'Disable ads', 'advanced-ads' ),
			[ $this, 'render_settings_section_disable_ads_callback' ],
			$hook
		);

		// "Layout / Output"  settings section.
		add_settings_section(
			'advanced_ads_setting_section_output',
			__( 'Layout / Output', 'advanced-ads' ),
			[ $this, 'render_settings_section_output_callback' ],
			$hook
		);

		// "Content injection"  settings section.
		add_settings_section(
			'advanced_ads_setting_section_injection',
			__( 'Content injection', 'advanced-ads' ),
			[ $this, 'render_settings_section_injection_callback' ],
			$hook
		);

		// Pro pitch section.
		if ( ! defined( 'AAP_VERSION' ) ) {
			add_settings_section(
				'advanced_ads_settings_pro_pitch_section',
				'',
				[ $this, 'render_settings_pro_pitch_section_callback' ],
				'advanced-ads-settings-pro-pitch-page'
			);

			add_filter( 'advanced-ads-setting-tabs', [ $this, 'pro_pitch_tab' ] );
		}

		// Tracking pitch section.
		if ( ! defined( 'AAT_VERSION' ) ) {
			add_settings_section(
				'advanced_ads_settings_tracking_pitch_section',
				'',
				[ $this, 'render_settings_tracking_pitch_section_callback' ],
				'advanced-ads-settings-tracking-pitch-page'
			);

			add_filter( 'advanced-ads-setting-tabs', [ $this, 'tracking_pitch_tab' ] );
		}

		// licenses section.
		// register license settings.
		register_setting( ADVADS_SLUG . '-licenses', ADVADS_SLUG . '-licenses' );

		/**
		 * Allow Ad Admin to save license settings..
		 *
		 * @param string[] $options Array with allowed options.
		 *
		 * @return string[]
		 */
		add_filter( 'advanced-ads-ad-admin-options', function( $options ) {
			$options[] = ADVADS_SLUG . '-licenses';

			return $options;
		} );

		add_settings_section(
			'advanced_ads_settings_license_section',
			'',
			[ $this, 'render_settings_licenses_section_callback' ],
			'advanced-ads-settings-license-page'
		);

		add_filter( 'advanced-ads-setting-tabs', [ $this, 'license_tab' ] );

		add_settings_section(
			'advanced_ads_settings_license_pitch_section',
			'',
			[ $this, 'render_settings_licenses_pitch_section_callback' ],
			'advanced-ads-settings-license-page'
		);

		// add setting fields to disable ads.
		add_settings_field(
			'disable-ads',
			__( 'Disable ads', 'advanced-ads' ),
			[ $this, 'render_settings_disable_ads' ],
			$hook,
			'advanced_ads_setting_section_disable_ads'
		);
		// add setting fields for user role.
		add_settings_field(
			'hide-for-user-role',
			__( 'Hide ads for user roles', 'advanced-ads' ),
			[ $this, 'render_settings_hide_for_users' ],
			$hook,
			'advanced_ads_setting_section_disable_ads'
		);
		// add setting fields for content injection protection.
		add_settings_field(
			'content-injection-everywhere',
			__( 'Content placement in post lists', 'advanced-ads' ),
			[ $this, 'render_settings_content_injection_everywhere' ],
			$hook,
			'advanced_ads_setting_section_injection'
		);
		// add setting fields for content injection priority.
		add_settings_field(
			'content-injection-priority',
			__( 'Priority of content injection filter', 'advanced-ads' ),
			[ $this, 'render_settings_content_injection_priority' ],
			$hook,
			'advanced_ads_setting_section_injection'
		);
		// add setting fields to remove injection level limitation.
		add_settings_field(
			'content-injection-level-limitation',
			__( 'Disable level limitation', 'advanced-ads' ),
			[ $this, 'render_settings_content_injection_level_limitation' ],
			$hook,
			'advanced_ads_setting_section_injection'
		);
		// add setting fields for hiding ads from bots.
		add_settings_field(
			'block-bots',
			__( 'Hide ads from bots', 'advanced-ads' ),
			[ $this, 'render_settings_block_bots' ],
			$hook,
			'advanced_ads_setting_section_disable_ads'
		);
		// dummy setting field to hide ads for post types.
		if ( ! defined( 'AAP_VERSION' ) ) {
			add_settings_field(
				'disable-by-post-types-pro',
				__( 'Disable ads for post types', 'advanced-ads' ),
				[ $this, 'render_settings_disable_post_types' ],
				$hook,
				'advanced_ads_setting_section_disable_ads'
			);
		}
		// opt out from internal notices.
		add_settings_field(
			'disable-notices',
			__( 'Disable Ad Health and other notices', 'advanced-ads' ),
			[ $this, 'render_settings_disabled_notices' ],
			$hook,
			'advanced_ads_setting_section'
		);
		// opt out from internal notices.
		add_settings_field(
			'front-prefix',
			__( 'ID prefix', 'advanced-ads' ),
			[ $this, 'render_settings_front_prefix' ],
			$hook,
			'advanced_ads_setting_section_output'
		);
		// allow editors to manage ads.
		add_settings_field(
			'editors-manage-ads',
			__( 'Allow editors to manage ads', 'advanced-ads' ),
			[ $this, 'render_settings_editors_manage_ads' ],
			$hook,
			'advanced_ads_setting_section'
		);

		// add unfiltered_html option to multisites
		if ( is_multisite() && current_user_can( 'unfiltered_html' ) ) {
			add_settings_field(
				'allow-unfiltered-html',
				/* translators: unfiltered_html */
				sprintf( __( 'Add the %s capability to user roles on multisite', 'advanced-ads' ), '<code>unfiltered_html</code>' ),
				[ $this, 'renders_settings_allow_unfiltered_html' ],
				$hook,
				'advanced_ads_setting_section'
			);
		}

		// ad label.
		add_settings_field(
			'add-custom-label',
			__( 'Ad label', 'advanced-ads' ),
			[ $this, 'render_settings_add_custom_label' ],
			$hook,
			'advanced_ads_setting_section_output'
		);

		// add setting fields.
		add_settings_field(
			'link-target',
			__( 'Open links in a new window', 'advanced-ads' ),
			[ $this, 'render_settings_link_target_callback' ],
			$hook,
			'advanced_ads_setting_section_output'
		);
		// add setting fields for advanced js.
		add_settings_field(
			'activate-advanced-js',
			__( 'Use advanced JavaScript', 'advanced-ads' ),
			[ $this, 'render_settings_advanced_js' ],
			$hook,
			'advanced_ads_setting_section_output'
		);

		// only for main blog.
		if ( is_main_site( get_current_blog_id() ) ) {
			add_settings_field(
				'uninstall-delete-data',
				__( 'Delete data on uninstall', 'advanced-ads' ),
				[ $this, 'render_settings_uninstall_delete_data' ],
				$hook,
				'advanced_ads_setting_section'
			);
		}

		// hook for additional settings from add-ons.
		do_action( 'advanced-ads-settings-init', $hook );
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
			add_filter( 'option_page_capability_' . $ad_admin_option, function() {
				return WordPress::user_cap( 'advanced_ads_manage_options' );
			} );
		}
	}

	/**
	 * Add license tab
	 *
	 * @param array $tabs setting tabs.
	 * @return array
	 */
	public function license_tab( array $tabs ) {

		$tabs['licenses'] = [
			'page'  => 'advanced-ads-settings-license-page',
			'group' => ADVADS_SLUG . '-licenses',
			'tabid' => 'licenses',
			'title' => __( 'Licenses', 'advanced-ads' ),
		];

		return $tabs;
	}

	/**
	 * Add pro pitch tab
	 *
	 * @param array $tabs setting tabs.
	 *
	 * @return array $tabs
	 */
	public function pro_pitch_tab( array $tabs ) {

		$tabs['pro_pitch'] = [
			'page'  => 'advanced-ads-settings-pro-pitch-page',
			// 'group' => ADVADS_SLUG . '-pro-pitch',
			'tabid' => 'pro-pitch',
			'title' => __( 'Pro', 'advanced-ads' ),
		];

		return $tabs;
	}

	/**
	 * Add tracking pitch tab
	 *
	 * @param array $tabs setting tabs.
	 *
	 * @return array $tabs
	 */
	public function tracking_pitch_tab( array $tabs ) {

		$tabs['tracking_pitch'] = [
			'page'  => 'advanced-ads-settings-tracking-pitch-page',
			'tabid' => 'tracking-pitch',
			'title' => __( 'Tracking', 'advanced-ads' ),
		];

		return $tabs;
	}

	/**
	 * Render settings section
	 */
	public function render_settings_section_callback() {
		// for whatever purpose there might come.
	}

	/**
	 * Render "Disable Ads" settings section
	 */
	public function render_settings_section_disable_ads_callback() {
		// for whatever purpose there might come.
	}

	/**
	 * Render "Content Injection" settings section
	 */
	public function render_settings_section_output_callback() {
		// for whatever purpose there might come.
	}

	/**
	 * Render "Content Injection" settings section
	 */
	public function render_settings_section_injection_callback() {
		// for whatever purpose there might come.
	}

	/**
	 * Render licenses settings section
	 */
	public function render_settings_licenses_section_callback() {
		include ADVADS_ABSPATH . 'admin/views/settings/license/section.php';
	}

	/**
	 * Render licenses pithces settings section
	 */
	public function render_settings_licenses_pitch_section_callback() {

		echo '<h3>' . esc_attr__( 'Are you missing something?', 'advanced-ads' ) . '</h3>';

		Advanced_Ads_Overview_Widgets_Callbacks::render_addons( true );
	}

	/**
	 * Render pro pitch settings section
	 */
	public function render_settings_pro_pitch_section_callback() {
		echo '<br/>';
		include ADVADS_ABSPATH . 'admin/views/upgrades/pro-tab.php';
	}

	/**
	 * Render tracking pitch settings section
	 */
	public function render_settings_tracking_pitch_section_callback() {
		echo '<br/>';
		include ADVADS_ABSPATH . 'admin/views/upgrades/tracking.php';
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
		include ADVADS_ABSPATH . 'admin/views/settings/general/disable-ads.php';
	}

	/**
	 * Render setting to hide ads from logged in users
	 */
	public function render_settings_hide_for_users() {
		$options = Advanced_Ads::get_instance()->options();
		if ( isset( $options['hide-for-user-role'] ) ) {
			$hide_for_roles = Advanced_Ads_Utils::maybe_translate_cap_to_role( $options['hide-for-user-role'] );
		} else {
			$hide_for_roles = [];
		}

		global $wp_roles;
		$roles = $wp_roles->get_names();

		include ADVADS_ABSPATH . 'admin/views/settings/general/hide-for-user-role.php';
	}

	/**
	 * Render setting to display advanced js file
	 */
	public function render_settings_advanced_js() {
		$options = Advanced_Ads::get_instance()->options();
		$checked = ( ! empty( $options['advanced-js'] ) ) ? 1 : 0;

		include ADVADS_ABSPATH . 'admin/views/settings/general/advanced-js.php';
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
		include ADVADS_ABSPATH . 'admin/views/settings/general/content-injection-everywhere.php';
	}

	/**
	 * Render setting for content injection priority
	 */
	public function render_settings_content_injection_priority() {
		$options  = Advanced_Ads::get_instance()->options();
		$priority = ( isset( $options['content-injection-priority'] ) ) ? (int) $options['content-injection-priority'] : 100;

		include ADVADS_ABSPATH . 'admin/views/settings/general/content-injection-priority.php';
	}

	/**
	 * Render setting to disable content injection level limitation
	 */
	public function render_settings_content_injection_level_limitation() {
		$options = Advanced_Ads::get_instance()->options();
		$checked = ( ! empty( $options['content-injection-level-disabled'] ) ) ? 1 : 0;

		include ADVADS_ABSPATH . 'admin/views/settings/general/content-injection-level-limitation.php';
	}

	/**
	 * Render setting for blocking bots
	 */
	public function render_settings_block_bots() {
		$options = Advanced_Ads::get_instance()->options();
		$checked = ( ! empty( $options['block-bots'] ) ) ? 1 : 0;

		include ADVADS_ABSPATH . 'admin/views/settings/general/block-bots.php';
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

		require ADVADS_ABSPATH . '/admin/views/settings/general/disable-post-types.php';
	}

	/**
	 * Render setting to disable notices and Ad Health
	 */
	public function render_settings_disabled_notices() {
		$options = Advanced_Ads::get_instance()->options();
		$checked = ( ! empty( $options['disable-notices'] ) ) ? 1 : 0;

		require ADVADS_ABSPATH . '/admin/views/settings/general/disable-notices.php';
	}

	/**
	 * Render setting for frontend prefix
	 */
	public function render_settings_front_prefix() {
		$options = Advanced_Ads::get_instance()->options();

		$prefix     = Advanced_Ads_Plugin::get_instance()->get_frontend_prefix();
		$old_prefix = ( isset( $options['id-prefix'] ) ) ? esc_attr( $options['id-prefix'] ) : '';

		require ADVADS_ABSPATH . '/admin/views/settings/general/frontend-prefix.php';
	}

	/**
	 * Render setting to allow editors to manage ads
	 */
	public function render_settings_editors_manage_ads() {
		$options = Advanced_Ads::get_instance()->options();

		// is false by default if no options where previously set.
		if ( isset( $options['editors-manage-ads'] ) && $options['editors-manage-ads'] ) {
			$allow = true;
		} else {
			$allow = false;
		}

		require ADVADS_ABSPATH . '/admin/views/settings/general/editors-manage-ads.php';
	}

	/**
	 * Prepare the template for multisite allow unfiltered_html settings.
	 *
	 * @return void
	 */
	public function renders_settings_allow_unfiltered_html() {
		$options               = Advanced_Ads::get_instance()->options();
		$user_roles_to_display = array_filter( wp_roles()->role_objects, static function( WP_Role $role ) {
			return $role->has_cap( 'advanced_ads_edit_ads' );
		} );
		if ( empty( $user_roles_to_display ) ) {
			return;
		}
		if ( ! isset( $options['allow-unfiltered-html'] ) ) {
			$options['allow-unfiltered-html'] = [];
		}
		$allowed_roles = $options['allow-unfiltered-html'];

		require ADVADS_ABSPATH . '/admin/views/settings/general/allow-unfiltered-html.php';
	}

	/**
	 * Render setting to add an "Advertisement" label before ads
	 */
	public function render_settings_add_custom_label() {
		$options = Advanced_Ads::get_instance()->options();

		$enabled = isset( $options['custom-label']['enabled'] );
		$label   = ! empty( $options['custom-label']['text'] ) ? esc_html( $options['custom-label']['text'] ) : _x( 'Advertisements', 'label before ads', 'advanced-ads' );

		require ADVADS_ABSPATH . '/admin/views/settings/general/custom-label.php';
	}

	/**
	 * Render link target="_blank" setting
	 *
	 * @since 1.8.4 – moved here from Tracking add-on
	 */
	public function render_settings_link_target_callback() {

		// get option if saved for tracking.
		$options = Advanced_Ads::get_instance()->options();
		if ( ! isset( $options['target-blank'] ) && class_exists( 'Advanced_Ads_Tracking_Plugin' ) ) {
			$tracking_options = Advanced_Ads_Tracking_Plugin::get_instance()->options();
			if ( isset( $tracking_options['target'] ) ) {
				$options['target-blank'] = $tracking_options['target'];
			}
		}

		$target = isset( $options['target-blank'] ) ? $options['target-blank'] : 0;
		include ADVADS_ABSPATH . 'admin/views/settings/general/link-target.php';
	}

	/**
	 * Render setting 'Delete data on uninstall"
	 */
	public function render_settings_uninstall_delete_data() {
		$options = Advanced_Ads::get_instance()->options();
		$enabled = ! empty( $options['uninstall-delete-data'] );

		include ADVADS_ABSPATH . 'admin/views/settings/general/uninstall-delete-data.php';
	}

	/**
	 * Sanitize plugin settings
	 *
	 * @param array $options all the options.
	 *
	 * @return array sanitized options.
	 */
	public function sanitize_settings( $options ) {

		// sanitize whatever option one wants to sanitize.
		if ( isset( $options['front-prefix'] ) ) {
			$options['front-prefix'] = Advanced_Ads_Plugin::get_instance()->sanitize_frontend_prefix(
				$options['front-prefix'],
				Advanced_Ads_Plugin::DEFAULT_FRONTEND_PREFIX
			);
		}

		$options = apply_filters( 'advanced-ads-sanitize-settings', $options );

		// check if editors can edit ads now and set the rights
		// else, remove that right.
		$editor_role = get_role( 'editor' );
		if ( null === $editor_role ) {
			return $options;
		}
		if ( isset( $options['editors-manage-ads'] ) && $options['editors-manage-ads'] ) {
			$editor_role->add_cap( 'advanced_ads_see_interface' );
			$editor_role->add_cap( 'advanced_ads_edit_ads' );
			$editor_role->add_cap( 'advanced_ads_manage_placements' );
			$editor_role->add_cap( 'advanced_ads_place_ads' );
		} else {
			$editor_role->remove_cap( 'advanced_ads_see_interface' );
			$editor_role->remove_cap( 'advanced_ads_edit_ads' );
			$editor_role->remove_cap( 'advanced_ads_manage_placements' );
			$editor_role->remove_cap( 'advanced_ads_place_ads' );
		}

		// we need 3 states: ! empty, 1, 0.
		$options['disabled-ads']['feed'] = ! empty( $options['disabled-ads']['feed'] ) ? 1 : 0;

		if ( isset( $options['content-injection-everywhere'] ) ) {
			if ( '0' === $options['content-injection-everywhere'] ) {
				unset( $options['content-injection-everywhere'] );
			} elseif ( $options['content-injection-everywhere'] === 'true' || $options['content-injection-everywhere'] <= - 1 ) {
				// Note: the option may be already set 'true' during import.
				$options['content-injection-everywhere'] = 'true';
			} else {
				$options['content-injection-everywhere'] = absint( $options['content-injection-everywhere'] );
			}
		}

		return $options;
	}

}
