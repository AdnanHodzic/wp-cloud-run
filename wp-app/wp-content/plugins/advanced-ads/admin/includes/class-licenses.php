<?php // phpcs:ignoreFile
defined( 'ABSPATH' ) || exit;

/**
 * Handle add-on licenses
 */
class Advanced_Ads_Admin_Licenses {
	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * License API endpoint URL
	 *
	 * @const string
	 */
	const API_ENDPOINT = 'https://wpadvancedads.com/license-api/';

	/**
	 * Advanced_Ads_Admin_Licenses constructor.
	 */
	private function __construct() {
		if ( ! defined( 'DOING_AJAX' ) ) {
			add_action( 'load-plugins.php', [ $this, 'check_plugin_licenses' ] );
		}
		add_action( 'plugins_loaded', [ $this, 'wp_plugins_loaded' ] );

		// todo: check if this is loaded late enough and all add-ons are registered already.
		add_filter( 'upgrader_pre_download', [ $this, 'addon_upgrade_filter' ], 10, 3 );
	}

	/**
	 * Actions and filter available after all plugins are initialized
	 */
	public function wp_plugins_loaded() {

		// check for add-on updates.
		add_action( 'admin_init', [ $this, 'add_on_updater' ], 1 );
		// react on API update checks
		add_action( 'http_api_debug', [ $this, 'update_license_after_version_info' ], 10, 5 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return self   object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initiate plugin checks
	 *
	 * @since 1.7.12
	 */
	public function check_plugin_licenses() {
		if ( is_multisite() ) {
			return;
		}

		// gather all add-on plugin files.
		$add_ons = apply_filters( 'advanced-ads-add-ons', [] );
		foreach ( $add_ons as $_add_on ) {

			// check license status.
			if ( $this->get_license_status( $_add_on['options_slug'] ) !== 'valid' ) {
				// register warning.
				$plugin_file = plugin_basename( $_add_on['path'] );
				add_action( 'after_plugin_row_' . $plugin_file, [ $this, 'add_plugin_list_license_notice' ], 10, 2 );
			}
		}
	}

	/**
	 * Add a row below add-ons with an invalid license on the plugin list
	 *
	 * @param string $plugin_file Path to the plugin file, relative to the plugins directory.
	 * @param array  $plugin_data An array of plugin data.
	 *
	 * @since 1.7.12
	 * @todo  make this work on multisite as well
	 */
	public function add_plugin_list_license_notice( $plugin_file, $plugin_data ) {
		static $cols;
		if ( is_null( $cols ) ) {
			$cols = count( _get_list_table( 'WP_Plugins_List_Table' )->get_columns() );
		}
		printf(
			'<tr class="advads-plugin-update-tr plugin-update-tr active"><td class="plugin-update colspanchange" colspan="%d"><div class="update-message notice inline notice-warning notice-alt"><p>%s</p></div></td></tr>',
			esc_attr( $cols ),
			wp_kses_post(
				sprintf(
					/* Translators: 1: add-on name 2: admin URL to license page */
					__( 'There might be a new version of %1$s. Please <strong>provide a valid license key</strong> in order to receive updates and support <a href="%2$s">on this page</a>.', 'advanced-ads' ),
					$plugin_data['Title'],
					admin_url( 'admin.php?page=advanced-ads-settings#top#licenses' )
				)
			)
		);
	}


	/**
	 * Save license key
	 *
	 * @param string $addon string with add-on identifier.
	 * @param string $plugin_name name of the add-on.
	 * @param string $options_slug slug of the option in the database.
	 * @param string $license_key license key.
	 *
	 * @return string
	 * @since 1.2.0
	 */
	public function activate_license( $addon = '', $plugin_name = '', $options_slug = '', $license_key = '' ) {
		if ( '' === $addon || '' === $plugin_name || '' === $options_slug ) {
			return __( 'Error while trying to register the license. Please contact support.', 'advanced-ads' );
		}

		$license_key = esc_attr( trim( $license_key ) );
		if ( '' === $license_key ) {
			return __( 'Please enter a valid license key', 'advanced-ads' );
		}

		if ( has_filter( 'advanced_ads_license_' . $options_slug ) ) {
			return apply_filters( 'advanced_ads_license_' . $options_slug, false, __METHOD__, $plugin_name, $options_slug, $license_key );
		}

		/**
		 * We need to remove the mltlngg_get_url_translated filter added by Multilanguage by BestWebSoft, https://wordpress.org/plugins/multilanguage/
		 * it causes the URL to look much different than it originally is
		 * we are adding it again later
		 */
		remove_filter( 'home_url', 'mltlngg_get_url_translated' );

		$api_params = [
			'edd_action' => 'activate_license',
			'license'    => $license_key,
			'item_name'  => urlencode( $plugin_name ),
			'url'        => home_url(),
		];

		/**
		 * Re-add the filter removed from above
		 */
		if ( function_exists( 'mltlngg_get_url_translated' ) ) {
			add_filter( 'home_url', 'mltlngg_get_url_translated' );
		}

		// Call the custom API.
		$response = wp_remote_post(
			self::API_ENDPOINT,
			[
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			]
		);

		// show license debug output if constant is set.
		if ( defined( 'ADVANCED_ADS_SHOW_LICENSE_RESPONSE' ) ) {
			return '<p><strong>' . esc_html__( 'The license status does not change as long as ADVANCED_ADS_SHOW_LICENSE_RESPONSE is enabled in wp-config.php.', 'advanced-ads' ) . '</strong></p>' .
				'<pre>' . print_r( $response, true ) . '</pre>';
		}

		/**
		 * Send the user to our support when his request is blocked by our firewall
		 */
		if ( $error = $this->blocked_by_firewall( $response ) ) {
			return $error;
		}

		if ( is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			if ( $body ) {
				return $body;
			} else {
				$curl = curl_version();

				return __( 'License couldn’t be activated. Please try again later.', 'advanced-ads' ) . " (cURL {$curl['version']})";
			}
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		// save license status.
		if ( ! empty( $license_data->license ) ) {
			update_option( $options_slug . '-license-status', $license_data->license, false );
		}
		if ( ! empty( $license_data->expires ) ) {
			update_option( $options_slug . '-license-expires', $license_data->expires, false );
		}

		// display activation problem.
		if ( ! empty( $license_data->error ) ) {
			// user friendly texts for errors.
			$errors = [
				'license_not_activable' => __( 'This is the bundle license key.', 'advanced-ads' ),
				'item_name_mismatch'    => __( 'This is not the correct key for this add-on.', 'advanced-ads' ),
				'no_activations_left'   => __( 'There are no activations left.', 'advanced-ads' )
										   . '&nbsp;'
										. sprintf(
											/* translators: %1$s is a starting link tag, %2$s is the closing one. */
											__( 'You can manage activations in %1$syour account%2$s.', 'advanced-ads' ),
											'<a href="https://wpadvancedads.com/account/?utm_source=advanced-ads&utm_medium=link&utm_campaign=settings-licenses-activations-left" target="_blank">',
											'</a>'
										) . '&nbsp;'
										. sprintf(
											/* translators: %1$s is a starting link tag, %2$s is the closing one. */
											__( '%1$sUpgrade%2$s for more activations.', 'advanced-ads' ),
											'<a href="https://wpadvancedads.com/account/upgrades/?utm_source=advanced-ads&utm_medium=link&utm_campaign=settings-licenses-activations-left" target="_blank">',
											'</a>'
										),
			];
			$error  = isset( $errors[ $license_data->error ] ) ? $errors[ $license_data->error ] : $license_data->error;
			if ( 'expired' === $license_data->error ) {
				return 'ex';
			} else {
				if ( isset( $errors[ $license_data->error ] ) ) {
					return $error;
				} else {
					return sprintf(
					// translators: %s is a string containing information about the issue.
						__( 'License is invalid. Reason: %s', 'advanced-ads' ),
						$error
					);
				}
			}
		} else {
			// reset license_expires admin notification.
			Advanced_Ads_Admin_Notices::get_instance()->remove_from_queue( 'license_expires' ); // this one is no longer added, but we keep the check here in case it is still in the queue for some users.
			Advanced_Ads_Admin_Notices::get_instance()->remove_from_queue( 'license_expired' ); // this one is no longer added, but we keep the check here in case it is still in the queue for some users.
			Advanced_Ads_Admin_Notices::get_instance()->remove_from_queue( 'license_invalid' );
			// save license key.
			$licenses           = $this->get_licenses();
			$licenses[ $addon ] = $license_key;
			$this->save_licenses( $licenses );
		}

		return 1;
	}

	/**
	 * Check if a request was blocked by our firewall
	 *
	 * @param array $response response from license call.
	 *
	 * @return mixed message or false
	 */
	public function blocked_by_firewall( $response ) {
		$response_code = wp_remote_retrieve_response_code( $response );
		if ( '403' == $response_code ) {
			$blocked_information = '–';
			if ( isset( $response['body'] ) ) {
				// look for the IP address in this line: `<td><span>95.90.238.103</span></td>`.
				$pattern = '/<span>([.0-9]*)<\/span>/';
				$matches = [];
				preg_match( $pattern, $response['body'], $matches );
				$ip                  = isset( $matches[1] ) ? $matches[1] : '–';
				$blocked_information = 'IP: ' . $ip;
			}

			// translators: %s is a list of server information like IP address. Just keep it as is.
			return sprintf( __( 'Your request was blocked by our firewall. Please send us the following information to unblock you: %s.', 'advanced-ads' ), $blocked_information );
		}

		return false;
	}

	/**
	 * Check if a specific license key was already activated for the current page
	 *
	 * @param string $license_key license key.
	 * @param string $plugin_name name of the add-on.
	 * @param string $options_slug slug of the option in the database.
	 *
	 * @return bool true if already activated
	 * @since 1.6.17
	 * @deprecated since version 1.7.2 because it only checks if a key is valid, not if the url registered with that key
	 */
	public function check_license( $license_key = '', $plugin_name = '', $options_slug = '' ) {
		if ( has_filter( 'advanced_ads_license_' . $options_slug ) ) {
			return apply_filters( 'advanced_ads_license_' . $options_slug, false, __METHOD__, $plugin_name, $options_slug, $license_key );
		}

		$api_params = [
			'edd_action' => 'check_license',
			'license'    => $license_key,
			'item_name'  => urlencode( $plugin_name ),
		];
		$response   = wp_remote_get(
			add_query_arg( $api_params, 'https://wpadvancedads.com/' ),
			[
				'timeout'   => 15,
				'sslverify' => false,
			]
		);
		if ( is_wp_error( $response ) ) {
			return false;
		}
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// if this license is still valid.
		if ( 'valid' === $license_data->license ) {
			update_option( $options_slug . '-license-expires', $license_data->expires, false );
			update_option( $options_slug . '-license-status', $license_data->license, false );

			return true;
		}

		return false;
	}

	/**
	 * Deactivate license key
	 *
	 * @param string $addon string with add-on identifier.
	 * @param string $plugin_name name of the add-on.
	 * @param string $options_slug slug of the option in the database.
	 *
	 * @return string
	 * @since 1.6.11
	 */
	public function deactivate_license( $addon = '', $plugin_name = '', $options_slug = '' ) {
		if ( '' === $addon || '' === $plugin_name || '' === $options_slug ) {
			return __( 'Error while trying to disable the license. Please contact support.', 'advanced-ads' );
		}

		$licenses    = $this->get_licenses();
		$license_key = isset( $licenses[ $addon ] ) ? $licenses[ $addon ] : '';

		if ( has_filter( 'advanced_ads_license_' . $options_slug ) ) {
			return apply_filters( 'advanced_ads_license_' . $options_slug, false, __METHOD__, $plugin_name, $options_slug, $license_key );
		}

		$api_params = [
			'edd_action' => 'deactivate_license',
			'license'    => $license_key,
			'item_name'  => urlencode( $plugin_name ),
		];
		// send the remote request.
		$response = wp_remote_post(
			self::API_ENDPOINT,
			[
				'body'      => $api_params,
				'timeout'   => 15,
				'sslverify' => false,
			]
		);

		// show license debug output if constant is set.
		if ( defined( 'ADVANCED_ADS_SHOW_LICENSE_RESPONSE' ) ) {
			return '<p><strong>' . esc_html__( 'The license status does not change as long as ADVANCED_ADS_SHOW_LICENSE_RESPONSE is enabled in wp-config.php.', 'advanced-ads' ) . '</strong></p>' .
				   '<pre>' . print_r( $response, true ) . '</pre>';
		}

		if ( is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			if ( $body ) {
				return $body;
			} else {
				return __( 'License couldn’t be deactivated. Please try again later.', 'advanced-ads' );
			}
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		/**
		 * Send the user to our support when his request is blocked by our firewall
		 */
		if ( $error = $this->blocked_by_firewall( $response ) ) {
			return $error;
		}

		// remove data.
		if ( 'deactivated' === $license_data->license ) {
			delete_option( $options_slug . '-license-status' );
			delete_option( $options_slug . '-license-expires' );
		} elseif ( 'failed' === $license_data->license ) {
			update_option( $options_slug . '-license-expires', $license_data->expires, false );
			update_option( $options_slug . '-license-status', $license_data->license, false );

			return 'ex';
		} else {
			return __( 'License couldn’t be deactivated. Please try again later.', 'advanced-ads' );
		}

		return 1;
	}

	/**
	 * Get license keys for all add-ons
	 *
	 * @return string[]
	 */
	public function get_licenses() {
		return get_option( ADVADS_SLUG . '-licenses', [] );
	}

	/**
	 * Save license keys for all add-ons
	 *
	 * @param array $licenses licenses.
	 */
	public function save_licenses( $licenses = [] ) {
		update_option( ADVADS_SLUG . '-licenses', $licenses );
	}

	/**
	 * Get license status of an add-on
	 *
	 * @param string $slug slug of the add-on.
	 *
	 * @return string|false license status, "valid", "invalid" or false if option doesn't exist.
	 */
	public function get_license_status( $slug = '' ) {
		return get_option( $slug . '-license-status', false );
	}

	/**
	 * If two or more add-ons use the same valid license this is probably an all-access customer
	 *
	 * @return bool
	 */
	public function get_probably_all_access() {
		$valid = array_filter(
			$this->get_licenses(),
			function ( $key ) {
				return $this->get_license_status( ADVADS_SLUG . '-' . $key );
			},
			ARRAY_FILTER_USE_KEY
		);

		return [] !== $valid && max( array_count_values( $valid ) ) > 1;
	}

	/**
	 * Return the licence expiry time if it is equal for more than one add-on. That indicates it is likely an All Access license
	 *
	 * @return string|null
	 */
	public function get_probably_all_access_expiry() {
		/**
		 * Get expiry dates of all add-ons.
		 *
		 * @param string $key Add-on key.
		 *
		 * @return string|false the expiration date or false.
		 */
		$expiry_counts = array_count_values( array_map( function( $key ) {
			return $this->get_license_expires( ADVADS_SLUG . '-' . $key );
		}, array_keys( array_filter( $this->get_licenses() ) ) ) );
		/**
		 * Remove all licenses that are used only once.
		 *
		 * @param int $count the count from array_count_values_above
		 *
		 * @return bool whether the count is greater 1
		 */
		$all_access = array_filter( $expiry_counts, function( $count ) {
			return $count > 1;
		} );

		// if there is an item in $all_access we can assume this is from All Access and return the expiry date.
		return empty( $all_access ) ? null : key( $all_access );
	}

	/**
	 * Get license expired value of an add-on
	 *
	 * @param string $slug slug of the add-on.
	 *
	 * @return string $date expiry date of an add-on, empty string if no option exists
	 */
	public function get_license_expires( $slug = '' ) {
		return get_option( $slug . '-license-expires', '' );
	}


	/**
	 * Register the Updater class for every add-on, which includes getting version information
	 */
	public function add_on_updater() {

		// ignore, if not main blog or if updater was disabled
		if ( ( is_multisite() && ! is_main_site() ) || ! apply_filters( 'advanced-ads-add-ons-updater', true ) ) {
			return;
		}

		/**
		 * List of registered add ons
		 * contains:
		 *        name
		 *        version
		 *        path
		 *        options_slug
		 *        short option slug (=key)
		 */
		$add_ons = apply_filters( 'advanced-ads-add-ons', [] );

		if ( [] === $add_ons ) {
			return;
		}

		$licenses = get_option( ADVADS_SLUG . '-licenses', [] );

		foreach ( $add_ons as $_add_on_key => $_add_on ) {

			// check if a license expired over time.
			$expiry_date = $this->get_license_expires( $_add_on['options_slug'] );
			$now         = time();
			if ( $expiry_date && 'lifetime' !== $expiry_date && strtotime( $expiry_date ) < $now ) {
				// remove license status.
				delete_option( $_add_on['options_slug'] . '-license-status' );
			}

			// retrieve our license key.
			$license_key = isset( $licenses[ $_add_on_key ] ) ? $licenses[ $_add_on_key ] : '';

			// by default, EDD looks every 3 hours for updates. The following code block changes that to 24 hours. set_expiration_of_update_option delivers that value.
			$option_key = 'pre_update_option_edd_sl_' . md5( serialize( basename( $_add_on['path'], '.php' ) . $license_key ) );
			add_filter( $option_key, [ $this, 'set_expiration_of_update_option' ] );

			new ADVADS_SL_Plugin_Updater(
				self::API_ENDPOINT,
				$_add_on['path'],
				[
					'version'   => $_add_on['version'],
					'license'   => $license_key,
					'item_name' => $_add_on['name'],
					'author'    => 'Advanced Ads',
				]
			);
		}
	}

	/**
	 * Set the expiration of the updater transient key to 1 day instead of 1 hour to prevent too many update checks
	 *
	 * @param array $value value array.
	 *
	 * @return array
	 * @since   1.7.14
	 */
	public function set_expiration_of_update_option( $value ) {
		$value['timeout'] = time() + 86400;

		return $value;
	}

	/**
	 * Add custom messages to plugin updater
	 *
	 * @param bool   $reply Whether to bail without returning the package. Default false.
	 * @param string $package The package file name.
	 * @param string $updater The WP_Upgrader instance.
	 *
	 * @return string
	 *
	 * @todo check if this is still working.
	 */
	public function addon_upgrade_filter( $reply, $package, $updater ) {
		if ( isset( $updater->skin->plugin ) ) {
			$plugin_file = $updater->skin->plugin;
		} elseif ( isset( $updater->skin->plugin_info['Name'] ) ) {
			$add_on = $this->get_installed_add_on_by_name( $updater->skin->plugin_info['Name'] );
			// $add_on['path'] should always be set with out official plugins but might be missing for some local and custom made.
			if ( isset( $add_on['path'] ) ) {
				$plugin_file = plugin_basename( $add_on['path'] );
			}
		}

		if ( isset( $plugin_file ) && $plugin_file ) {
			// hides the download url, but makes debugging harder.
			// $updater->strings['downloading_package'] = __( 'Downloading updated version...', 'advanced-ads' );
			// $updater->skin->feedback( 'downloading_package' );

			// if AJAX; show direct update link as first possible solution.
			if ( defined( 'DOING_AJAX' ) ) {
				$update_link                         = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $plugin_file, 'upgrade-plugin_' . $plugin_file );
				/* translators: %s plugin update link */
				$updater->strings['download_failed'] = sprintf( __( 'Download failed. <a href="%s">Click here to try another method</a>.', 'advanced-ads' ), $update_link );
			} else {
				/* translators: %s download failed knowledgebase link */
				$updater->strings['download_failed'] = sprintf( __( 'Download failed. <a href="%s" target="_blank">Click here to learn why</a>.', 'advanced-ads' ), 'https://wpadvancedads.com/manual/download-failed-updating-add-ons/#utm_source=advanced-ads&utm_medium=link&utm_campaign=download-failed' );
			}
		}

		return $reply;
	}

	/**
	 * Search if a name is in the add-on array and return the add-on data of it
	 *
	 * @param string $name name of an add-on.
	 *
	 * @return  array    array with the add-on data
	 */
	private function get_installed_add_on_by_name( $name = '' ) {
		$add_ons = apply_filters( 'advanced-ads-add-ons', [] );

		if ( is_array( $add_ons ) ) {
			foreach ( $add_ons as $key => $_add_on ) {
				if ( $_add_on['name'] === $name ) {
					return $_add_on;
				}
			}
		}

		return null;
	}

	/**
	 * Check if any license is valid
	 * can be used to display information for any Pro user only, like link to direct support
	 */
	public static function any_license_valid() {
		$add_ons = apply_filters( 'advanced-ads-add-ons', [] );

		if ( [] === $add_ons ) {
			return false;
		}

		foreach ( $add_ons as $_add_on ) {
			$status = self::get_instance()->get_license_status( $_add_on['options_slug'] );

			// check expiry date.
			$expiry_date = self::get_instance()->get_license_expires( $_add_on['options_slug'] );

			if (
				(
					$expiry_date
					&& strtotime( $expiry_date ) > time()
				)
				 || 'valid' === $status
				 || 'lifetime' === $expiry_date
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Update the license status based on information retrieved from the version info check
	 *
	 * @param array|WP_Error $response    HTTP response or WP_Error object.
	 * @param string         $context     Context under which the hook is fired.
	 * @param string         $class       HTTP transport used.
	 * @param array          $parsed_args HTTP request arguments.
	 * @param string         $url         The request URL.
	 * @return array|WP_Error
	 */
	public function update_license_after_version_info( $response, $context, $class, $parsed_args, $url ) {

		// bail if this call is not from our version check or returns an issue
		if ( $url !== self::API_ENDPOINT
			 || (
				empty( $parsed_args['body']['edd_action'] )
				|| 'get_version' !== $parsed_args['body']['edd_action']
			 )
			 || is_wp_error( $response )
		) {
			return $response;
		}

		$params = json_decode( wp_remote_retrieve_body( $response ) );
		// return if no name is given to identify the plugin that needs update
		if ( empty( $params->name ) ) {
			return $response;
		}

		$new_license_status = null;
		$new_expiry_date    = null;

		// Some of the conditions could happen at the same time, though due to different conditions in EDD we are safer to have multiple checks
		if ( isset( $params->valid_until ) ) {
			if ( 'invalid' === $params->valid_until ) {
				$new_license_status = 'invalid';
			}
			if ( 'lifetime' === $params->valid_until ) {
				$new_license_status = 'valid';
				$new_expiry_date    = 'lifetime';
			}
			// license is timestamp
			if ( is_int( $params->valid_until ) ) {
				$new_expiry_date = (int) $params->valid_until;
				if ( time() < $params->valid_until ) {
					$new_license_status = 'valid';
				}
			}
		} elseif ( empty( $params->download_link ) || empty( $params->package ) || isset( $params->msg ) ) {
			// if either of these two parameters is missing then the user does not have a valid license according to our store
			// if there is a "msg" parameter then the license did also not work for another reason
			$new_license_status = 'invalid';
		}

		if ( ! $new_license_status && ! $new_expiry_date ) {
			return $response;
		}

		$add_ons = apply_filters( 'advanced-ads-add-ons', [] );

		// look for the add-on with the appropriate license key
		foreach ( $add_ons as $_add_on_key => $_add_on ) {
			// identify the add-on based on the name
			if ( ! isset( $add_on['name'] ) || $params->name !== $add_on['name'] ) {
				continue;
			}

			$options_slug = $_add_on['options_slug'];

			if ( $new_license_status ) {
				update_option( $options_slug . '-license-status', $new_license_status, false );
			}
			if ( $new_expiry_date ) {
				if ( 'lifetime' !== $new_expiry_date ) {
					$new_expiry_date = gmdate( 'Y-m-d 23:59:49', $new_expiry_date );
				}
				update_option( $options_slug . '-license-expires', $new_expiry_date, false );
			}

			// return with the first match since there should only be one plugin per name
			return $response;
		}

		return $response;
	}
}
