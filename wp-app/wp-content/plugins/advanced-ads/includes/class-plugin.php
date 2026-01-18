<?php
/**
 * The plugin bootstrap.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds;

use AdvancedAds\Ads\Ads;
use AdvancedAds\Groups\Groups;
use Advanced_Ads_Admin_Licenses;
use AdvancedAds\Installation\Install;
use AdvancedAds\Placements\Placements;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin.
 *
 * Containers:
 *
 * @property Shortcodes           $shortcodes Shortcodes handler.
 * @property Assets_Registry      $registry   Assets registry.
 * @property Framework\JSON       $json       JSON handler.
 * @property Admin\Admin_Menu     $screens    Admin screens.
 * @property Frontend\Ad_Renderer $renderer   Ads renderer.
 * @property Frontend\Manager     $frontend   Frontend manager.
 * @property Importers\Manager    $importers  Importers manager.
 */
class Plugin extends Framework\Loader {

	use Traits\Extras;

	/**
	 * The ads container
	 *
	 * @var Ads
	 */
	public $ads = null;

	/**
	 * The groups container
	 *
	 * @var Groups
	 */
	public $groups = null;

	/**
	 * The placements container
	 *
	 * @var Placements
	 */
	public $placements = null;

	/**
	 * Modules manager
	 *
	 * @var Modules
	 */
	public $modules = null;

	/**
	 * Main instance
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Plugin
	 */
	public static function get(): Plugin {
		static $instance;

		if ( null === $instance ) {
			$instance = new Plugin();
			$instance->setup();
		}

		return $instance;
	}

	/**
	 * Get plugin version
	 *
	 * @return string
	 */
	public function get_version(): string {
		return ADVADS_VERSION;
	}

	/**
	 * Bootstrap plugin.
	 *
	 * @return void
	 */
	private function setup(): void {
		$this->define_constants();
		$this->includes_functions();
		$this->includes();
		$this->includes_rest();
		$this->includes_admin();
		$this->includes_frontend();
		$this->includes_deprecated();

		/**
		 * Old loading strategy
		 *
		 * TODO: need to remove it in future.
		 */
		// Public-Facing and Core Functionality.
		\Advanced_Ads::get_instance();
		\Advanced_Ads_ModuleLoader::loadModules( ADVADS_ABSPATH . 'modules/' ); // enable modules, requires base class.

		if ( is_admin() ) {
			Advanced_Ads_Admin_Licenses::get_instance();
		}

		add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ], -1 );
		add_action( 'widgets_init', [ $this, 'register_widgets' ] );

		// Load it all.
		$this->ads->initialize();
		$this->groups->initialize();
		$this->placements->initialize();
		$this->modules->initialize();
		$this->load();
	}

	/**
	 * Register the Advanced Ads classic Widget
	 *
	 * @return void
	 */
	public function register_widgets(): void {
		register_widget( '\AdvancedAds\Widget' );
	}

	/**
	 * When WordPress has loaded all plugins, trigger the `advanced-ads-loaded` hook.
	 *
	 * @since 1.47.0
	 *
	 * @return void
	 */
	public function on_plugins_loaded(): void {
		/**
		 * Action trigger after loading finished.
		 *
		 * @since 1.47.0
		 */
		do_action( 'advanced-ads-loaded' );
	}

	/**
	 * Define Advanced Ads constant
	 *
	 * @return void
	 */
	private function define_constants(): void {
		$this->define( 'ADVADS_ABSPATH', dirname( ADVADS_FILE ) . '/' );
		$this->define( 'ADVADS_PLUGIN_BASENAME', plugin_basename( ADVADS_FILE ) );
		$this->define( 'ADVADS_BASE_URL', plugin_dir_url( ADVADS_FILE ) );
		$this->define( 'ADVADS_SLUG', 'advanced-ads' );
		// name for group & option in settings.
		$this->define( 'ADVADS_SETTINGS_ADBLOCKER', 'advanced-ads-adblocker' );

		// Deprecated Constants.
		/**
		 * ADVADS_BASE
		 *
		 * @deprecated 1.47.0 use ADVADS_PLUGIN_BASENAME now.
		 */
		define( 'ADVADS_BASE', ADVADS_PLUGIN_BASENAME );

		/**
		 * ADVADS_BASE_PATH
		 *
		 * @deprecated 1.47.0 use ADVADS_ABSPATH now.
		 */
		define( 'ADVADS_BASE_PATH', ADVADS_ABSPATH );

		/**
		 * ADVADS_BASE_DIR
		 *
		 * @deprecated 1.47.0 Avoid global declaration of the constant used exclusively in `load_text_domain` function; use localized declaration instead.
		 */
		define( 'ADVADS_BASE_DIR', dirname( ADVADS_PLUGIN_BASENAME ) );

		/**
		 * ADVADS_URL
		 *
		 * @deprecated 1.47.0 Deprecating the constant in favor of using the direct URL to circumvent costly `esc_url` function; please update code accordingly.
		 */
		define( 'ADVADS_URL', 'https://wpadvancedads.com/' );
	}

	/**
	 * Includes core files used in admin and on the frontend.
	 *
	 * @return void
	 */
	private function includes(): void {
		$this->ads        = new Ads();
		$this->groups     = new Groups();
		$this->placements = new Placements();
		$this->modules    = new Modules();

		// Common.
		$this->register_initializer( Install::class );
		$this->register_integration( Entities::class );
		$this->register_integration( Assets_Registry::class, 'registry' );
		$this->register_integration( Framework\JSON::class, 'json', [ 'advancedAds' ] );
		$this->register_integration( Compatibility\Compatibility::class );
		$this->register_integration( Compatibility\AAWP::class );
		$this->register_integration( Compatibility\Peepso::class );
		$this->register_integration( Post_Data::class );
		$this->register_integration( Crons\Ads::class );
		$this->register_integration( Shortcodes::class, 'shortcodes' );
		$this->register_integration( Frontend\Debug_Ads::class );
	}

	/**
	 * Includes files used on the frontend.
	 *
	 * @return void
	 */
	private function includes_frontend(): void {
		// Early bail!!
		if ( is_admin() ) {
			return;
		}

		$this->register_integration( Frontend\Ad_Renderer::class, 'renderer' );
		$this->register_integration( Frontend\Manager::class, 'frontend' );
		$this->register_integration( Frontend\Scripts::class );
	}

	/**
	 * Includes files used in admin.
	 *
	 * @return void
	 */
	private function includes_admin(): void {
		// Early bail!!
		if ( ! is_admin() ) {
			return;
		}

		$this->register_integration( Compatibility\Capability_Manager::class );
		$this->register_initializer( Upgrades::class );
		$this->register_integration( Admin\Action_Links::class );
		$this->register_integration( Admin\Admin_Menu::class, 'screens' );
		$this->register_integration( Admin\Admin_Notices::class );
		$this->register_integration( Admin\Assets::class );
		$this->register_integration( Admin\Header::class );
		$this->register_integration( Admin\Marketing::class );
		$this->register_integration( Admin\Metabox_Ad::class );
		$this->register_integration( Admin\Metabox_Ad_Settings::class );
		$this->register_integration( Admin\Post_Types::class );
		$this->register_integration( Admin\Screen_Options::class );
		$this->register_integration( Admin\Shortcode_Creator::class );
		$this->register_integration( Admin\TinyMCE::class );
		$this->register_integration( Admin\WordPress_Dashboard::class );
		$this->register_integration( Admin\Quick_Bulk_Edit::class );
		$this->register_integration( Admin\Page_Quick_Edit::class );
		$this->register_integration( Admin\Placement_Quick_Edit::class );
		$this->register_integration( Importers\Manager::class, 'importers' );
		$this->register_integration( Admin\AJAX::class );
		$this->register_integration( Admin\Version_Control::class );
		$this->register_integration( Admin\Upgrades::class );
		$this->register_integration( Admin\Authors::class );
		$this->register_integration( Admin\Settings::class );
		$this->register_integration( Admin\Misc::class );
		$this->register_integration( Admin\Post_List::class );
		$this->register_integration( Admin\Placement\Bulk_Edit::class );
		$this->register_integration( Admin\Addon_Updater::class );

		if ( ! wp_doing_ajax() ) {
			$this->register_integration( Admin\List_Filters::class, 'list_filters' );
		}
	}

	/**
	 * Includes rest api files used in admin and on the frontend.
	 *
	 * @return void
	 */
	private function includes_rest(): void {
		$this->register_route( Rest\Groups::class );
		$this->register_route( Rest\Page_Quick_Edit::class );
		$this->register_route( Rest\Placements::class );
		$this->register_route( Rest\OnBoarding::class );
		$this->register_route( Rest\Utilities::class );
	}

	/**
	 * Includes the necessary functions files.
	 *
	 * @return void
	 */
	private function includes_functions(): void {
		require_once ADVADS_ABSPATH . 'includes/functions.php';
		require_once ADVADS_ABSPATH . 'includes/functions-core.php';
		require_once ADVADS_ABSPATH . 'includes/functions-conditional.php';
		require_once ADVADS_ABSPATH . 'includes/functions-ad.php';
		require_once ADVADS_ABSPATH . 'includes/functions-group.php';
		require_once ADVADS_ABSPATH . 'includes/functions-placement.php';
		require_once ADVADS_ABSPATH . 'includes/cap_map.php';
		require_once ADVADS_ABSPATH . 'includes/default-hooks.php';
	}

	/**
	 * Includes deprecated files.
	 *
	 * @return void
	 */
	private function includes_deprecated(): void {
		require_once ADVADS_ABSPATH . 'deprecated/ad_group.php';
		require_once ADVADS_ABSPATH . 'deprecated/ad_placements.php';
		require_once ADVADS_ABSPATH . 'deprecated/Ad_Repository.php';
		require_once ADVADS_ABSPATH . 'deprecated/ad_type_abstract.php';
		require_once ADVADS_ABSPATH . 'deprecated/ad_type_content.php';
		require_once ADVADS_ABSPATH . 'deprecated/ad_type_dummy.php';
		require_once ADVADS_ABSPATH . 'deprecated/ad_type_group.php';
		require_once ADVADS_ABSPATH . 'deprecated/ad_type_image.php';
		require_once ADVADS_ABSPATH . 'deprecated/ad_type_plain.php';
		require_once ADVADS_ABSPATH . 'deprecated/ad-ajax.php';
		require_once ADVADS_ABSPATH . 'deprecated/ad-debug.php';
		require_once ADVADS_ABSPATH . 'deprecated/ad-expiration.php';
		require_once ADVADS_ABSPATH . 'deprecated/ad-model.php';
		require_once ADVADS_ABSPATH . 'deprecated/ad-select.php';
		require_once ADVADS_ABSPATH . 'deprecated/ad.php';
		require_once ADVADS_ABSPATH . 'deprecated/deprecated-functions.php';
		require_once ADVADS_ABSPATH . 'deprecated/gadsense-dummy.php';
		require_once ADVADS_ABSPATH . 'deprecated/Group_Repository.php';
		require_once ADVADS_ABSPATH . 'deprecated/class-admin.php';
		require_once ADVADS_ABSPATH . 'deprecated/class-advanced-ads-plugin.php';
	}
}
