<?php
// phpcs:ignoreFile
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Utilities\WordPress;

/**
 * Class Advanced_Ads_Ad_Network
 */
abstract class Advanced_Ads_Ad_Network {
	/**
	 * The identifier will be used for generated ids, names etc.
	 *
	 * @var string
	 */
	protected $identifier;

	/**
	 * The name of the ad network
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The name of the hook for the advanced ads settings page
	 *
	 * @var string
	 */
	protected $settings_page_hook;

	/**
	 * The WordPress nonce (retrieve with the get_nonce method)
	 *
	 * @var string
	 */
	protected $nonce;

	/**
	 * The network’s settings section ID
	 *
	 * @var string
	 */
	protected $settings_section_id;

	/**
	 * The network’s settings init hook.
	 *
	 * @var string
	 */
	private $settings_init_hook;

	/**
	 * Advanced_Ads_Ad_Network constructor.
	 *
	 * @param string $identifier an identifier that will be used for hooks, settings, ids and much more - MAKE SURE IT IS UNIQUE.
	 * @param string $name - the (translateable) display name for this ad network.
	 */
	public function __construct( $identifier, $name ) {
		$this->identifier          = $identifier;
		$this->name                = $name;
		$this->settings_page_hook  = ADVADS_SLUG . '-' . $this->identifier . '-settings-page';
		$this->settings_section_id = ADVADS_SLUG . '-' . $this->identifier . '-settings-section';
		$this->settings_init_hook  = ADVADS_SLUG . '-' . $this->identifier . '-settings-init';
	}

	/**
	 * The identifier for this network
	 *
	 * @return string
	 */
	public function get_identifier() {
		return $this->identifier;
	}

	/**
	 * The display name for this network
	 *
	 * @return string
	 */
	public function get_display_name() {
		return $this->name;
	}

	/**
	 * The display value for the settings tab
	 *
	 * @return string
	 */
	public function get_settings_tab_name() {
		return $this->get_display_name();
	}

	/**
	 * URL for the settings page (admin)
	 *
	 * @return string
	 */
	public function get_settings_href() {
		return admin_url( 'admin.php?page=advanced-ads-settings#top#' . $this->identifier );
	}

	/**
	 * The identifier / name for the javascript file that will be injected.
	 *
	 * @return string
	 */
	public function get_js_library_name() {
		return 'advanced-ads-network' . $this->identifier;
	}

	/**
	 * Registers this ad network
	 */
	public function register() {
		// register the ad type.
		add_filter( 'advanced-ads-ad-types', [ $this, 'register_ad_type_callback' ] );

		if ( is_admin() ) {
			if ( wp_doing_ajax() ) {
				// we need add all the actions for our ajax calls here.
				// our ajax method that will trigger an update of the ad units of this network.
				add_action( 'wp_ajax_advanced_ads_get_ad_units_' . $this->identifier, [ $this, 'update_external_ad_units' ] );
				add_action( 'wp_ajax_advanced_ads_toggle_idle_ads_' . $this->identifier, [ $this, 'toggle_idle_ads' ] );
			} else {
				// find out if we need to register the settings. this is necessary
				// 1) when viewing the settings (admin.php with page="advanced-ads-settings")
				// 2) when posting the settings to options.php
				// in all other cases, there is nothing to do.
				global $pagenow;
				$requires_settings   = false;
				$requires_javascript = false;

				if ( 'admin.php' === $pagenow ) {
					$page = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : null;
					switch ( $page ) {
						case 'advanced-ads-settings':
							$requires_settings   = true;
							$requires_javascript = true;
							break;
						case 'advanced-ads':
							$requires_javascript = true;
						default:
							break;
					}
				} elseif ( 'options.php' === $pagenow ) {
					$requires_settings = true;
				} elseif ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
					$post_type = isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : '';
					add_filter( 'advanced-ads-ad-settings-pre-save', [ $this, 'sanitize_ad_settings' ] );

					if ( isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) {
						$requires_javascript = true;
					} elseif ( 'advanced_ads' === $post_type ) {
						$requires_javascript = true;
					}
				}

				if ( $requires_settings ) {
					// register the settings.
					add_action( 'advanced-ads-settings-init', [ $this, 'register_settings_callback' ] );
					add_filter( 'advanced-ads-setting-tabs', [ $this, 'register_settings_tabs_callback' ] );
				}
				if ( $requires_javascript ) {
					add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts_callback' ] );
				}
			}
		}
	}

	/**
	 * The callback method for the filter "advanced-ads-ad-types"
	 *
	 * @param array $types ad types.
	 *
	 * @return array
	 */
	public function register_ad_type_callback( $types ) {
		$types[ $this->identifier ] = $this->get_ad_type();

		return $types;
	}

	/**
	 * This method will be called for the wp action "advanced-ads-settings-init" and therefore has to be public.
	 */
	public function register_settings_callback() {
		// register new settings.
		register_setting(
			ADVADS_SLUG . '-' . $this->identifier,
			ADVADS_SLUG . '-' . $this->identifier,
			[ $this, 'sanitize_settings_callback' ]
		);

		/**
		 * Allow Ad Admin to save AdSense options.
		 *
		 * @param array $settings Array with allowed options.
		 *
		 * @return array
		 */
		add_filter( 'advanced-ads-ad-admin-options', function( $options ) {
			$options[] = ADVADS_SLUG . '-' . $this->identifier;

			return $options;
		} );

		// add a new section.
		add_settings_section(
			$this->settings_section_id,
			'',
			[ $this, 'render_settings_callback' ],
			$this->settings_page_hook
		);

		// register all the custom settings.
		$this->register_settings( $this->settings_page_hook, $this->settings_section_id );

		do_action( $this->settings_init_hook, $this->settings_page_hook );
	}

	/**
	 * Create name of the object used for localized data
	 *
	 * @return string
	 */
	protected function get_localized_script_object_name() {
		return $this->identifier . 'AdvancedAdsJS';
	}

	/**
	 * Engueue scripts
	 */
	public function enqueue_scripts_callback() {

		if ( ! Conditional::is_screen_advanced_ads() ) {
			return;
		}

		$js_path = $this->get_javascript_base_path();
		if ( $js_path ) {
			$id = $this->get_js_library_name();
			wp_enqueue_script( $id, $js_path, [ 'jquery', wp_advads()->registry->prefix_it( 'admin' ) ] );
			// next we have to pass the data.
			$data = [
				'nonce' => $this->get_nonce(),
			];
			$data = $this->append_javascript_data( $data );
			wp_localize_script( $id, $this->get_localized_script_object_name(), $data );
		}
	}

	/**
	 * Get a nonce
	 *
	 * @return string
	 */
	public function get_nonce() {
		if ( ! $this->nonce ) {
			$this->nonce = wp_create_nonce( $this->get_nonce_action() );
		}

		return $this->nonce;
	}

	/**
	 * Returns the action (name) of the nonce for this network
	 * in some cases you may want to override this method to faciliate
	 * integration with existing code
	 *
	 * @return string
	 */
	public function get_nonce_action() {
		return 'advads-network-' . $this->identifier;
	}

	/**
	 * This method will be called for the wp action "advanced-ads-settings-tabs" and therefore has to be public.
	 * it simply adds a tab for this ad type. if you don't want that just override this method with an empty one.
	 *
	 * @param array $tabs tabs on Advanced Ads settings page.
	 *
	 * @return array
	 */
	public function register_settings_tabs_callback( $tabs ) {
		$tab_id          = $this->identifier;
		$tabs[ $tab_id ] = [
			'page'  => $this->settings_page_hook,
			'group' => ADVADS_SLUG . '-' . $this->identifier,
			'tabid' => $tab_id,
			'title' => $this->get_settings_tab_name(),
		];

		return $tabs;
	}

	/**
	 * Render settings page
	 */
	public function render_settings_callback() {

	}

	/**
	 * Callback to sanitize settings
	 *
	 * @param array $options options to be sanitized.
	 *
	 * @return mixed
	 */
	public function sanitize_settings_callback( $options ) {
		$options = $this->sanitize_settings( $options );

		return $options;
	}

	/**
	 * Callback to sanitize settings for an individual ad
	 *
	 * @param array $ad_settings_post post data from the ad settings form.
	 *
	 * @return mixed
	 */
	public function sanitize_ad_settings_callback( array $ad_settings_post ) {
		return $this->sanitize_ad_settings( $ad_settings_post );
	}

	/**
	 * Performs basic security checks for wp ajax requests (nonce, capabilities)
	 * dies, when a problem was detected
	 */
	protected function ajax_security_checks() {
		if ( ! WordPress::user_can( 'advanced_ads_manage_options' ) ) {
			// TODO: translate.
			$this->send_ajax_error_response_and_die( "You don't have the permission to manage ads." );
		}
		$nonce = isset( $_REQUEST['nonce'] ) ? $_REQUEST['nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, $this->get_nonce_action() ) ) {
			// TODO: translate.
			$this->send_ajax_error_response_and_die( 'You sent an invalid request.' );
		}
	}

	/**
	 * Send data via AJAX but don’t react on it.
	 *
	 * @param bool $json_serializable_response true if data can be serialized.
	 */
	protected function send_ajax_response_and_die( $json_serializable_response = false ) {
		if ( ! $json_serializable_response ) {
			$json_serializable_response = new stdClass();
		}
		header( 'Content-Type: application/json' );
		echo wp_json_encode( $json_serializable_response );
		die();
	}

	/**
	 * Send message via AJAX but don’t react on it.
	 *
	 * @param string $message message string.
	 */
	protected function send_ajax_error_response_and_die( $message ) {
		header( 'Content-Type: application/json' );
		$r        = new stdClass();
		$r->error = $message;
		echo wp_json_encode( $r );
		die();
	}

	/**
	 * Toggle ad IDs
	 */
	public function toggle_idle_ads() {
		$this->ajax_security_checks();
		global $external_ad_unit_id;
		$hide_idle_ads       = isset( $_POST['hide'] ) ? $_POST['hide'] : false;
		$external_ad_unit_id = isset( $_POST['ad_unit_id'] ) ? $_POST['ad_unit_id'] : "";
		if ( ! $external_ad_unit_id ) {
			$external_ad_unit_id = '';
		}
		ob_start();

		$this->print_external_ads_list( $hide_idle_ads );
		$ad_selector = ob_get_clean();

		$response = [
			'status' => true,
			'html'   => $ad_selector,
		];
		$this->send_ajax_response_and_die( $response );
	}

	/**
	 * When you need some kind of manual ad setup (meaning you can edit the custom inputs of this ad type)
	 * you should override this method to return true. this results in an additional link (Setup code manually)
	 *
	 * @return bool
	 */
	public function supports_manual_ad_setup() {
		return false;
	}

	/**
	 * Print a list of ads.
	 *
	 * @param bool $hide_idle_ads true to hide idle ids.
	 *
	 * @return mixed
	 */
	abstract public function print_external_ads_list( $hide_idle_ads = true );

	/**
	 * Retrieves an instance of the ad type for this ad network
	 */
	abstract public function get_ad_type();

	/**
	 * This method will be called via wp AJAX.
	 * it has to retrieve the list of ads from the ad network and store it as an option
	 * does not return ad units - use "get_external_ad_units" if you're looking for an array of ad units
	 */
	abstract public function update_external_ad_units();

	/**
	 * Adds the custom wp settings to the tab for this ad unit
	 *
	 * @param string $hook hook for the settings page.
	 * @param string $section_id settings section ID.
	 */
	abstract protected function register_settings( $hook, $section_id );

	/**
	 * Sanitize the network specific options
	 *
	 * @param array $options the options to sanitize.
	 *
	 * @return mixed the sanitizzed options
	 */
	abstract protected function sanitize_settings( $options );

	/**
	 * Sanitize the settings for this ad network
	 *
	 * @param array $ad_settings_post ad settings in POST format.
	 *
	 * @return mixed the sanitized settings
	 */
	abstract public function sanitize_ad_settings( $ad_settings_post );


	/**
	 * Get external ad units from the given network.
	 *
	 * @return array of ad units (Advanced_Ads_Ad_Network_Ad_Unit)
	 */
	abstract public function get_external_ad_units();

	/**
	 * Checks if the ad_unit is supported by Advanced Ads.
	 * this determines wheter it can be imported or not.
	 *
	 * @param object $ad_unit ad unit.
	 *
	 * @return boolean
	 */
	abstract public function is_supported( $ad_unit );

	/**
	 * There is no common way to connect to an external account. you will have to implement it somehow, just
	 * like the whole setup process (usually done in the settings tab of this network). this method provides
	 * a way to return this account connection
	 *
	 * @return boolean true, when an account was successfully connected
	 */
	abstract public function is_account_connected();

	/**
	 * External ad networks rely on the same javascript base code. however you still have to provide
	 * a javascript class that inherits from the AdvancedAdsAdNetwork js class
	 * this has to point to that file, or return false,
	 * if you don't have to include it in another way (NOT RECOMMENDED!)
	 *
	 * @return string path to the javascript file containing the javascriot class for this ad type
	 */
	abstract public function get_javascript_base_path();

	/**
	 * Our script might need translations or other variables (llike a nonce, which is included automatically)
	 * add anything you need in this method and return the array
	 *
	 * @param array $data holding the data.
	 *
	 * @return array the data, that will be passed to the base javascript file containing the AdvancedAdsAdNetwork class
	 */
	abstract public function append_javascript_data( &$data );
}
