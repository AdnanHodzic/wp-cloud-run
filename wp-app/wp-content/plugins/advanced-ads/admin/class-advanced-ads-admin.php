<?php
// phpcs:ignoreFile

use AdvancedAds\Importers\Api_Ads;
use AdvancedAds\Utilities\WordPress;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Utilities\Params;

/**
 * Advanced Ads main admin class
 *
 * @package   Advanced_Ads_Admin
 * @author    Thomas Maier <support@wpadvancedads.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright since 2013 Thomas Maier, Advanced Ads GmbH
 *
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 */
class Advanced_Ads_Admin {

	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Instance of admin notice class.
	 *
	 * @var      object $notices
	 */
	protected $notices = null;

	/**
	 * Slug of the settings page
	 *
	 * @var      string $plugin_screen_hook_suffix
	 */
	public $plugin_screen_hook_suffix = null;

	/**
	 * General plugin slug
	 *
	 * @var     string
	 */
	protected $plugin_slug = '';

	/**
	 * Admin settings.
	 *
	 * @var      array
	 */
	protected static $admin_settings = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 */
	private function __construct() {
		if ( wp_doing_ajax() ) {
			new Advanced_Ads_Ad_Ajax_Callbacks();
			add_action( 'plugins_loaded', [ $this, 'wp_plugins_loaded_ajax' ] );
		} else {
			add_action( 'plugins_loaded', [ $this, 'wp_plugins_loaded' ] );
			Advanced_Ads_Ad_List_Filters::get_instance();
		}
		// add shortcode creator to TinyMCE.
		Advanced_Ads_Shortcode_Creator::get_instance();
		Advanced_Ads_Admin_Licenses::get_instance();
	}

	/**
	 * License handling legacy code after moving license handling code to Advanced_Ads_Admin_Licenses
	 *
	 * @param string $addon slug of the add-on.
	 * @param string $plugin_name name of the add-on.
	 * @param string $options_slug slug of the options the plugin is saving in the options table.
	 *
	 * @return mixed 1 on success or string with error message.
	 * @since version 1.7.16 (early January 2017)
	 */
	public function deactivate_license( $addon = '', $plugin_name = '', $options_slug = '' ) {
		return Advanced_Ads_Admin_Licenses::get_instance()->deactivate_license( $addon, $plugin_name, $options_slug );
	}

	/**
	 * Get license status.
	 *
	 * @param string $slug slug of the add-on.
	 *
	 * @return string   license status
	 */
	public function get_license_status( $slug = '' ) {
		return Advanced_Ads_Admin_Licenses::get_instance()->get_license_status( $slug );
	}

	/**
	 * Actions and filter available after all plugins are initialized.
	 */
	public function wp_plugins_loaded() {
		// call $plugin_slug from public plugin class.
		$plugin            = Advanced_Ads::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// update placements.
		add_action( 'admin_init', [ 'Advanced_Ads_Placements', 'update_placements' ] );
		add_action( 'admin_init', [ $this, 'api_import_rollback' ] );

		// add Advanced Ads admin notices
		// removes admin notices from other plugins
		// `in_admin_header` is the last hook to run before àdmin_notices` according to https://codex.wordpress.org/Plugin_API/Action_Reference.
		add_action( 'in_admin_header', [ $this, 'register_admin_notices' ] );

		add_action( 'plugins_api_result', [ $this, 'recommend_suitable_add_ons' ], 11, 3 );

		// register dynamic action to load a starter setup.
		add_action( 'admin_action_advanced_ads_starter_setup', [ $this, 'import_starter_setup' ] );

		Advanced_Ads_Admin_Meta_Boxes::get_instance();
		Advanced_Ads_Admin_Ad_Type::get_instance();
		Advanced_Ads_Admin_Settings::get_instance();
		Advanced_Ads_Ad_Authors::get_instance();
		new Advanced_Ads_Admin_Upgrades();
		new Advanced_Ads\Admin\Post_List();
	}

	/**
	 * Actions and filters that should also be available for ajax
	 */
	public function wp_plugins_loaded_ajax() {
		// needed here in order to work with Quick Edit option on ad list page.
		Advanced_Ads_Admin_Ad_Type::get_instance();
		new Advanced_Ads\Admin\Post_List();

		add_action( 'wp_ajax_advads_load_rss_widget_content', [ 'Advanced_Ads_Admin_Meta_Boxes', 'dashboard_widget_function_output' ] );
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
	 * Check if the current screen belongs to Advanced Ads
	 *
	 * @deprecated 1.47.0
	 *
	 * @return bool
	 */
	public static function screen_belongs_to_advanced_ads() {
		_deprecated_function( __METHOD__, '1.47.0', '\AdvancedAds\Utilities\Conditional::is_screen_advanced_ads()' );
		return Conditional::is_screen_advanced_ads();
	}

	/**
	 * Get action from the params
	 *
	 * @deprecated 1.47.0
	 */
	public function current_action() {
		_deprecated_function( __METHOD__, '1.47.0', 'AdvancedAds\Utilities\WordPress::current_action()' );
		return WordPress::current_action();
	}

	/**
	 * Get DateTimeZone object for the WP installation
	 *
	 * @return DateTimeZone object set in WP settings.
	 * @see        Advanced_Ads_Utils::get_wp_timezone()
	 *
	 * @deprecated This is also used outside of admin as well as other plugins.
	 */
	public static function get_wp_timezone() {
		return Advanced_Ads_Utils::get_wp_timezone();
	}

	/**
	 * Get literal expression of timezone.
	 *
	 * @param DateTimeZone $date_time_zone the DateTimeZone object to get literal value from.
	 *
	 * @return string time zone.
	 * @see        Advanced_Ads_Utils::get_timezone_name()
	 *
	 * @deprecated This is also used outside of admin as well as other plugins.
	 */
	public static function timezone_get_name( DateTimeZone $date_time_zone ) {
		return Advanced_Ads_Utils::get_timezone_name();
	}

	/**
	 * Registers Advanced Ads admin notices
	 * prevents other notices from showing up on our own pages
	 */
	public function register_admin_notices() {

		/**
		 * Remove all registered admin_notices from AA screens
		 * we need to use this or some users have half or more of their viewports cluttered with unrelated notices
		 */
		if ( Conditional::is_screen_advanced_ads() ) {
			remove_all_actions( 'admin_notices' );
		}

		// register our own notices.
		add_action( 'admin_notices', [ $this, 'admin_notices' ] );
	}

	/**
	 * Initiate the admin notices class
	 */
	public function admin_notices() {
		// display ad block warning to everyone who can edit ads.
		if ( WordPress::user_can( 'advanced_ads_edit_ads' ) ) {
			if ( Conditional::is_screen_advanced_ads() ) {
				$ad_blocker_notice_id = Advanced_Ads_Plugin::get_instance()->get_frontend_prefix() . 'abcheck-' . md5( microtime() );
				wp_register_script( $ad_blocker_notice_id . '-adblocker-notice', false, [], ADVADS_VERSION, true );
				wp_enqueue_script( $ad_blocker_notice_id . '-adblocker-notice' );
				wp_add_inline_script( $ad_blocker_notice_id . '-adblocker-notice', "
				jQuery( document ).ready( function () {
							if ( typeof advanced_ads_adblocker_test === 'undefined' ) {
								jQuery( '#" . esc_attr( $ad_blocker_notice_id ) . ".message' ).show();
							}
						} );" );
				include ADVADS_ABSPATH . 'admin/views/notices/adblock.php';
			}
		}

		// Show success notice after starter setup was imported. Registered here because it will be visible only once.
		if ( isset( $_GET['message'] ) && 'advanced-ads-starter-setup-success' === $_GET['message'] ) {
			add_action( 'advanced-ads-admin-notices', [ $this, 'starter_setup_success_message' ] );
		}

		// register our own notices on Advanced Ads pages, except from the overview page where they should appear in the notices section.
		$screen = get_current_screen();
		if ( class_exists( 'Advanced_Ads_Admin_Notices' )
			 && WordPress::user_can( 'advanced_ads_edit_ads' )
			 && ( ! isset( $screen->id ) || 'toplevel_page_advanced-ads' !== $screen->id ) ) {
			$this->notices = Advanced_Ads_Admin_Notices::get_instance()->notices;

			echo '<div class="wrap">';
			Advanced_Ads_Admin_Notices::get_instance()->display_notices();

			// allow other Advanced Ads plugins to show admin notices at this late stage.
			do_action( 'advanced-ads-admin-notices' );
			echo '</div>';
		}
	}

	/**
	 * Sort visitor and display condition arrays alphabetically by their label.
	 *
	 * @param array $a array to be compared.
	 * @param array $b array to be compared.
	 *
	 * @return mixed
	 */
	public static function sort_condition_array_by_label( $a, $b ) {
		if ( ! isset( $a['label'] ) || ! isset( $b['label'] ) ) {
			return;
		}

		return strcmp( strtolower( $a['label'] ), strtolower( $b['label'] ) );
	}

	/**
	 * Recommend additional add-ons
	 *
	 * @param object|WP_Error $result Response object or WP_Error.
	 * @param string          $action The type of information being requested from the Plugin Installation API.
	 * @param object          $args Plugin API arguments.
	 *
	 * @return object|WP_Error Response object or WP_Error.
	 */
	public function recommend_suitable_add_ons( $result, $action, $args ) {
		if ( empty( $args->browse ) ) {
			return $result;
		}

		if ( 'featured' !== $args->browse && 'recommended' !== $args->browse && 'popular' !== $args->browse ) {
			return $result;
		}

		if ( ! isset( $result->info['page'] ) || 1 < $result->info['page'] ) {
			return $result;
		}

		// Recommend AdSense In-Feed add-on.
		if ( ! is_plugin_active( 'advanced-ads-adsense-in-feed/advanced-ads-in-feed.php' )
			 && ! is_plugin_active_for_network( 'advanced-ads-adsense-in-feed/advanced-ads-in-feed.php' ) ) {

			// Grab all slugs from the api results.
			$result_slugs = wp_list_pluck( $result->plugins, 'slug' );

			if ( in_array( 'advanced-ads-adsense-in-feed', $result_slugs, true ) ) {
				return $result;
			}

			$query_args  = [
				'slug'   => 'advanced-ads-adsense-in-feed',
				'fields' => [
					'icons'             => true,
					'active_installs'   => true,
					'short_description' => true,
					'group'             => true,
				],
			];
			$plugin_data = plugins_api( 'plugin_information', $query_args );

			if ( ! is_wp_error( $plugin_data ) ) {
				if ( 'featured' === $args->browse ) {
					array_push( $result->plugins, $plugin_data );
				} else {
					array_unshift( $result->plugins, $plugin_data );
				}
			}
		}

		// Recommend Genesis Ads add-on.
		if ( defined( 'PARENT_THEME_NAME' ) && 'Genesis' === PARENT_THEME_NAME
			 && ! is_plugin_active( 'advanced-ads-genesis/genesis-ads.php' )
			 && ! is_plugin_active_for_network( 'advanced-ads-genesis/genesis-ads.php' ) ) {

			// Grab all slugs from the api results.
			$result_slugs = wp_list_pluck( $result->plugins, 'slug' );

			if ( in_array( 'advanced-ads-genesis', $result_slugs, true ) ) {
				return $result;
			}

			$query_args  = [
				'slug'   => 'advanced-ads-genesis',
				'fields' => [
					'icons'             => true,
					'active_installs'   => true,
					'short_description' => true,
					'group'             => true,
				],
			];
			$plugin_data = plugins_api( 'plugin_information', $query_args );

			if ( ! is_wp_error( $plugin_data ) ) {
				if ( 'featured' === $args->browse ) {
					array_push( $result->plugins, $plugin_data );
				} else {
					array_unshift( $result->plugins, $plugin_data );
				}
			}
		}

		// Recommend WP Bakery (former Visual Composer) add-on.
		if ( defined( 'WPB_VC_VERSION' )
			 && ! is_plugin_active( 'ads-for-visual-composer/advanced-ads-vc.php' )
			 && ! is_plugin_active_for_network( 'ads-for-visual-composer/advanced-ads-vc.php' ) ) {

			// Grab all slugs from the api results.
			$result_slugs = wp_list_pluck( $result->plugins, 'slug' );

			if ( in_array( 'ads-for-visual-composer', $result_slugs, true ) ) {
				return $result;
			}

			$query_args  = [
				'slug'   => 'ads-for-visual-composer',
				'fields' => [
					'icons'             => true,
					'active_installs'   => true,
					'short_description' => true,
					'group'             => true,
				],
			];
			$plugin_data = plugins_api( 'plugin_information', $query_args );

			if ( ! is_wp_error( $plugin_data ) ) {
				if ( 'featured' === $args->browse ) {
					array_push( $result->plugins, $plugin_data );
				} else {
					array_unshift( $result->plugins, $plugin_data );
				}
			}
		}

		return $result;
	}

	/**
	 * Import a starter setup for new users
	 */
	public function import_starter_setup() {
		if (
			! isset( $_GET['action'] )
			|| 'advanced_ads_starter_setup' !== $_GET['action']
			|| ! WordPress::user_can( 'advanced_ads_edit_ads' )
		) {
			return;
		}

		check_admin_referer( 'advanced-ads-starter-setup' );

		// start importing the ads.
		$xml = file_get_contents( ADVADS_ABSPATH . 'admin/assets/xml/starter-setup.xml' );

		Advanced_Ads_Import::get_instance()->import( $xml );

		// redirect to ad overview page.
		wp_safe_redirect( admin_url( 'edit.php?post_type=advanced_ads&message=advanced-ads-starter-setup-success' ) );
	}

	/**
	 * Show success message after starter setup was created.
	 */
	public function starter_setup_success_message() {

		// load link to latest post.

		$args           = [
			'numberposts' => 1,
		];
		$last_post      = get_posts( $args );
		$last_post_link = isset( $last_post[0]->ID ) ? get_permalink( $last_post[0]->ID ) : false;

		include ADVADS_ABSPATH . 'admin/views/notices/starter-setup-success.php';
	}

	/**
	 * Get admin settings of the current user.
	 *
	 * @return array
	 */
	public static function get_admin_settings() {
		if ( null === self::$admin_settings ) {
			self::$admin_settings = get_user_meta( get_current_user_id(), 'advanced-ads-admin-settings', true );

			if ( ! is_array( self::$admin_settings ) ) {
				self::$admin_settings = [];
			}
		}
		return self::$admin_settings;
	}

	/**
	 * Update admin settings of the current user.
	 *
	 * @param array $new_settings New admin settings.
	 */
	public static function update_admin_setttings( $new_settings ) {
		$current = self::get_admin_settings();

		if ( $current !== $new_settings ) {
			update_user_meta( get_current_user_id(), 'advanced-ads-admin-settings', $new_settings );
			self::$admin_settings = $new_settings;
		}
	}

	/**
	 * Show a note about a deprecated feature and link to the appropriate page in our manual
	 *
	 * @param string $feature simple string to indicate the deprecated feature. Will be added to the UTM campaign attribute.
	 */
	public static function show_deprecated_notice( $feature = '' ) {
		$url = 'https://wpadvancedads.com/manual/deprecated-features/';

		if ( '' !== $feature ) {
			$url .= '#utm_source=advanced-ads&utm_medium=link&utm_campaign=deprecated-' . sanitize_title_for_query( $feature );
		}

		echo '<br/><br/><span class="advads-notice-inline advads-error">';
		printf(
			// Translators: %1$s is the opening link tag, %2$s is closing link tag.
			esc_html__( 'This feature is deprecated. Please find the removal schedule %1$shere%2$s', 'advanced-ads-pro' ),
			'<a href="' . esc_url( $url ) . '" target="_blank">',
			'</a>'
		);
		echo '</span>';
	}

	public function api_import_rollback() {
		if ( 'advads_import_delete' !== Params::get( 'action' ) ) {
			return;
		}

		check_admin_referer( 'advads_import_delete' );

		$session_key = Params::get( 'session_key' );
		$db_session  = get_option( 'advanced-ads-importer-history' );

		if ( $session_key !== $db_session ) {
			add_action( 'all_admin_notices', [ $this, 'import_error' ] );
			return;
		}

		( new Api_Ads() )->rollback( $session_key);
		delete_option( 'advanced-ads-importer-history' );
		add_action( 'all_admin_notices', [ $this, 'import_success' ] );
	}

	public function import_error() {
		?>
		<div class="notice notice-error">
			<p><strong>Error:</strong> Session key not matched.</p>
		</div>
		<?php
	}

	public function import_success() {
		?>
		<div class="notice notice-success">
			<p><strong>Success:</strong> the rollback is completed.</p>
		</div>
		<?php
	}
}
