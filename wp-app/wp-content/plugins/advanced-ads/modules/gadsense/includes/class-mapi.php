<?php // phpcs:ignoreFile

use AdvancedAds\Utilities\WordPress;

/**
 * AdSense Management API class.
 */
class Advanced_Ads_AdSense_MAPI {

	const OPTNAME = 'advanced-ads-adsense-mapi';

	const ALERTS_URL = 'https://adsense.googleapis.com/v2/accounts/PUBID/alerts';

	const CID = '400595147946-3ot506jh20qld7bqmg1l87ms4vn2uok5.apps.googleusercontent.com';

	const CS = 'WKX8ghwUbxdrBcVmZ9WXOKph';

	const REDIRECT_URI = 'https://c.wpadvancedads.com/oauth.php';

	const CALL_PER_24H = 20;

	private static $instance = null;

	private static $default_options = [];

	private static $empty_account_data = [
		'default_app' => [
			'access_token'  => '',
			'refresh_token' => '',
			'expires'       => 0,
			'token_type'    => '',
		],
		'user_app'    => [
			'access_token'  => '',
			'refresh_token' => '',
			'expires'       => 0,
			'token_type'    => '',
		],
		'ad_units'    => [],
		'details'     => [],
		'alerts'      => [],
	];

	/**
	 * Instance constructor
	 */
	private function __construct() {

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );

		add_action( 'wp_ajax_advads_gadsense_mapi_confirm_code', [ $this, 'ajax_confirm_code' ] );
		//add_action( 'wp_ajax_advads_gadsense_mapi_get_adUnits', array( $this, 'ajax_get_adUnits' ) );
		add_action( 'wp_ajax_advads_gadsense_mapi_get_details', [ $this, 'ajax_get_account_details' ] );
		add_action( 'wp_ajax_advads_gadsense_mapi_select_account', [ $this, 'ajax_account_selected' ] );
		add_action( 'wp_ajax_advads_mapi_get_adCode', [ $this, 'ajax_get_ad_code' ] );
		add_action( 'wp_ajax_advads-mapi-reconstructed-code', [ $this, 'ajax_save_reconstructed_code' ] );
		add_action( 'wp_ajax_advads-mapi-save-manual-code', [ $this, 'ajax_save_manual_code' ] );
		add_action( 'wp_ajax_advads-mapi-revoke-token', [ $this, 'ajax_revoke_tokken' ] );
		add_action( 'wp_ajax_advads-mapi-get-alerts', [ $this, 'ajax_get_account_alerts' ] );
		add_action( 'wp_ajax_advads-mapi-dismiss-alert', [ $this, 'ajax_dismiss_alert' ] );
		add_action( 'wp_ajax_advads_adsense_report_refresh', [ 'Advanced_Ads_Overview_Widgets_Callbacks', 'ajax_gadsense_dashboard' ] );

		add_action( 'admin_footer', [ $this, 'admin_footer' ] );

		self::$default_options = [
			'accounts'          => [],
			'ad_codes'          => [],
			'unsupported_units' => [],
			'quota'             => [
				'count' => self::CALL_PER_24H,
				'ts'    => 0,
			],
			'connect_error' => [],
		];

		add_filter( 'advanced-ads-support-messages', [ 'Advanced_Ads_AdSense_MAPI', 'adsense_warnings_check' ] );

		add_action( 'wp_loaded', [ $this, 'update_ad_health_notices' ] );

	}

	/**
	 * Update all MAPI related notices.
	 */
	public function update_ad_health_notices() {
		$mapi_options = self::get_option();

		$connection_error_messages = self::get_connect_error_messages();

		$health_class = Advanced_Ads_Ad_Health_Notices::get_instance();

		// Last connection failed.
		if ( isset ( $mapi_options['connect_error'] ) && ! empty( $mapi_options['connect_error'] ) ) {

			if ( isset( $connection_error_messages[ $mapi_options['connect_error']['reason'] ] ) ) {
				$health_class->add( 'adsense_connect_' . $mapi_options['connect_error']['reason'] );
			} else {
				$health_class->add( 'adsense_connect_' . $mapi_options['connect_error']['reason'], [
		  'text' => esc_html__( 'Last AdSense account connection attempt failed.', 'advanced-ads' ) . ' ' . $mapi_options['connect_error']['message'],
					'type' => 'problem',
				] );
			}

			foreach ( $health_class->notices as $key => $value ) {
				// There was already a connection error but the user tried again and obtained another error.
				if ( false !== stripos( $key, 'adsense_connect_' ) && 'adsense_connect_' . $mapi_options['connect_error']['reason'] !== $key ) {
					$health_class->remove( $key );
				}
			}

		} else {

			// Once a connection has been established (or a the warning has been discarded on the AA settings page), remove connection related notices.
			foreach ( $health_class->notices as $key => $value ) {
				if ( false !== stripos( $key, 'adsense_connect_' ) ) {
					$health_class->remove( $key );
				}
			}

		}

		$gadsense_data = Advanced_Ads_AdSense_Data::get_instance();
		$adsense_id    = $gadsense_data->get_adsense_id();
		$alerts        = Advanced_Ads_AdSense_MAPI::get_stored_account_alerts( $adsense_id );

		// AdSense account alerts (can not happens simultaneously with the connection error).
		if ( is_array( $alerts ) && isset( $alerts['items'] ) && is_array( $alerts['items'] ) && $alerts['items'] ) {
			$item_ids               = [];
			$alerts_advads_messages = Advanced_Ads_Adsense_MAPI::get_adsense_alert_messages();

			foreach ( $alerts['items'] as $internal_id => $item ) {
				$item_id    = isset( $item['id'] ) ? $item['id'] : str_replace( '-', '_', strtoupper( $item['type'] ) );
				$item_ids[] = $item_id;
				if ( isset( $alerts_advads_messages[ $item_id ] ) ) {
					$health_class->add( 'adsense_alert_' . $item_id );
				} else {
					$health_class->add(
						'adsense_alert_' . $item_id,
						[
							'text' => $item['message'] . ' ' . self::get_adsense_error_link( $item_id ),
							'type' => 'problem',
						]
					);
				}
			}

			// Remove notices that no more exist in the AdSense account (or have been dismissed).
			$_remove_ids = [];
			foreach ( $health_class->notices as $key => $value ) {
				if ( false !== stripos( $key, 'adsense_alert_' ) ) {
					$alert_id = substr( $key, strlen( 'adsense_alert_' ) );
					if ( ! in_array( $alert_id, $item_ids, true ) ) {
						$_remove_ids[] = $key;
					}
				}
			}
			foreach ( $_remove_ids as $id ) {
				$health_class->remove( $id );
			}
		} else {
			// No more alerts.
			foreach ( $health_class->notices as $key => $value ) {
				if ( false !== stripos( $key, 'adsense_alert_' ) ) {
					$health_class->remove( $key );
				}
			}
		}
	}

	/**
	 * Get available quota and eventual message about remaining call
	 */
	public function get_quota() {
		$options = $this->get_option();
		$now     = time();
		if ( self::use_user_app() ) {
			return [ 'count' => PHP_INT_MAX ];
		} else {
			if ( $now > $options['quota']['ts'] + ( 24 * 3600 ) ) {
				return [
					'count' => self::CALL_PER_24H,
				];
			} else {
				$msg = $this->get_quota_msg();
				return [
					'count' => $options['quota']['count'],
					'msg'   => $msg,
				];
			}
		}
	}

	/**
	 *  Get the readable quota
	 */
	public function get_quota_msg() {

		$options = $this->get_option();
		$now     = time();
		$secs    = $options['quota']['ts'] + ( 24 * 3600 ) - $now;
		$hours   = floor( $secs / 3600 );
		$mins    = ceil( ( $secs - ( $hours * 3600 ) ) / 60 );

		if ( 60 === $mins ) {
			++$hours;
			$mins = 0;
		}

		if ( 0 === (int) $options['quota']['count'] ) {
			$msg = sprintf(
				'No API call left before %1$s %2$s.',
				sprintf( '%s hours', $hours ),
				sprintf( '%s minutes', $mins )
			);

			if ( 0 === $hours ) {
				$msg = 'No API call left before.';
			}

			if ( 0 === $mins ) {
				$msg = 'No API call left.';
			}
		} else {
			$msg = sprintf(
				'%1$s for the next %2$s %3$s',
				sprintf( '%s API calls remaining', $options['quota']['count'] ),
				sprintf( '%s hours', $hours ),
				sprintf( '%s minutes', $mins )
			);

			if ( 0 === $hours ) {
				$msg = sprintf( '%s API calls remaining.', $options['quota']['count'] );
			}

			if ( 0 === $mins ) {
				$msg = sprintf(
					'%1$s for the next %2$s',
					sprintf( '%s API calls remaining', $options['quota']['count'] ),
					sprintf( '%s hours', $hours )
				);
			}
		}
		return $msg;
	}

	/**
	 *  Decrement quota by 1, and return message about remaining call
	 */
	public function decrement_quota() {
		$options = $this->get_option();
		if ( 0 < $options['quota']['count'] ) {
			$options['quota']['count']--;
			$now = time();
			if ( $now > $options['quota']['ts'] + ( 24 * 3600 ) ) {
				$options['quota']['ts'] = $now;
			}
			update_option( self::OPTNAME, $options );
			return $this->get_quota_msg();
		}
	}

	/**
	 * Get ad code from Google fpr a given ad unit
	 *
	 * @param string $ad_unit the ad unit to get the ad code for.
	 *
	 * @return array response to send back to the browser.
	 */
	public function get_ad_code( $ad_unit ) {
		$options       = self::get_option();
		$gadsense_data = Advanced_Ads_AdSense_Data::get_instance();
		$adsense_id    = $gadsense_data->get_adsense_id();
		$unit_id       = explode( ':', $ad_unit )[1];
		$url           = 'https://adsense.googleapis.com/v2/accounts/' . $adsense_id . '/adclients/ca-' . $adsense_id . '/adunits/' . $unit_id . '/adcode';
		$access_token  = self::get_access_token( $adsense_id );

		foreach ( Advanced_Ads_Network_Adsense::get_instance()->get_external_ad_units() as $unit ) {
			if (
				isset( $unit->raw )
				&& in_array( $unit->raw['contentAdsSettings']['type'], [ 'ARTICLE', 'FEED', 'MATCHED_CONTENT' ], true )
				&& $ad_unit === $unit->id
				&& ! array_key_exists( $ad_unit, $options['ad_codes'] )
			) {
				$options['unsupported_units'][ $ad_unit ] = 1;
				update_option( self::OPTNAME, $options );

				return [
					'status' => false,
					'msg'    => 'doesNotSupportAdUnitType',
				];
			}
		}

		if ( ! isset( $access_token['msg'] ) ) {
			$headers  = [ 'Authorization' => 'Bearer ' . $access_token ];
			$response = wp_remote_get( $url, [ 'headers' => $headers ] );
			self::log( 'Get ad code for ad Unit [' . $ad_unit . ']' );

			if ( is_wp_error( $response ) ) {
				return [
					'status' => false,
					// translators: %s: ad unit ID.
					'msg'    => sprintf( esc_html__( 'Error while retrieving ad code for "%s".', 'advanced-ads' ), $ad_unit ),
					'raw'    => $response->get_error_message(),
				];
			}
			$ad_code = json_decode( $response['body'], true );
			if ( null === $ad_code || ! isset( $ad_code['adCode'] ) ) {
				if ( $ad_code['error'] &&
					$ad_code['error']['errors'] &&
					isset( $ad_code['error']['errors'][0]['reason'] ) &&
					'doesNotSupportAdUnitType' === $ad_code['error']['errors'][0]['reason']
				) {
					if ( array_key_exists( $ad_unit, $options['ad_codes'] ) && array_key_exists( $ad_unit, $options['unsupported_units'] ) ) {
						unset( $options['unsupported_units'][ $ad_unit ] );
					} else {
						$options['unsupported_units'][ $ad_unit ] = 1;
					}
					update_option( self::OPTNAME, $options );

					return [
						'status' => false,
						'msg'    => 'doesNotSupportAdUnitType',
					];
				}

				return [
					'status' => false,
					// translators: %s: ad unit ID.
					'msg'    => sprintf( esc_html__( 'Invalid response while retrieving ad code for "%s".', 'advanced-ads' ), $ad_unit ),
					'raw'    => $response['body'],
				];
			}
			$options['ad_codes'][ $ad_unit ] = $ad_code['adCode'];
			if ( isset( $options['unsupported_units'][ $ad_unit ] ) ) {
				unset( $options['unsupported_units'][ $ad_unit ] );
			}
			update_option( self::OPTNAME, $options );

			return $ad_code['adCode'];
		}

		// return the original error info.
		return $access_token;
	}

	/**
	 * Convert ad unit data to v1.4 format to match the current UI and logics
	 *
	 * @param array $ad_unit ad unit in MAPI v2 format.
	 *
	 * @return array the ad unit in MAPI v1.4 format.
	 */
	public static function convert_ad_unit_format( $ad_unit ) {
		$chunks = explode( '/', $ad_unit['name'] );

		return [
			'name'                 => $ad_unit['displayName'],
			'nameV2'               => $ad_unit['name'],
			'id'                   => $ad_unit['reportingDimensionId'],
			'code'                 => $chunks[ count( $chunks ) - 1 ],
			'status'               => $ad_unit['state'],
			'contentAdsSettings'   => $ad_unit['contentAdsSettings'],
			'reportingDimensionId' => $ad_unit['reportingDimensionId'],
		];
	}

	/**
	 * Get/Update ad unit list for a given client
	 *
	 * @param string $account publisher ID.
	 *
	 * @return array
	 */
	public static function get_ad_units( $account ) {
		$url          = 'https://adsense.googleapis.com/v2/accounts/' . $account . '/adclients/ca-' . $account . '/adunits?pageSize=350';
		$access_token = self::get_access_token( $account );

		if ( isset( $access_token['msg'] ) ) {
			// Return the token related message.
			return $access_token;
		}

		$response = wp_remote_get( $url, [ 'headers' => [ 'Authorization' => 'Bearer ' . $access_token ] ] );
		self::log( 'Get ad units list for ca-' . $account );

		if ( is_wp_error( $response ) ) {
			return [
				'status' => false,
				// translators: %s is the publisher ID.
				'msg'    => sprintf( esc_html__( 'Error while retrieving ad unit list for "%s".', 'advanced-ads' ), $account ),
				'raw'    => $response->get_error_message(),
			];
		}

		if ( trim( $response['body'] ) === '{}' ) {
			// Empty account.
			return [
				'status' => false,
				'msg'    => sprintf(
				// translators: %1$s is the AdSense publisher ID; %2$s a starting a tag to the AdSense ad unit list and %3$s the closing link.
					esc_html__( 'The account "%1$s" does not seem to have any ad units. Please create some %2$shere%3$s.', 'advanced-ads' ),
					$account,
					'<a href="https://www.google.com/adsense/new/u/0/' . $account . '/myads/units" target="_blank">',
					'</a>'
				),
				'raw'    => $response['body'],
			];
		}

		$response_body = json_decode( trim( $response['body'] ), true );

		if ( null === $response_body || ! isset( $response_body['adUnits'] ) ) {
			// translators: %s is the publisher ID.
			$error_message = sprintf( esc_html__( 'Invalid response while retrieving ad unit list for "%s".', 'advanced-ads' ), $account );
			// check the response for errors and display them for better problem-solving.
			if ( ! empty( $response_body['error']['errors'] ) ) {
				foreach ( $response_body['error']['errors'] as $err ) {
					$hint = self::get_adsense_error_hint( $err['reason'] );
					if ( $hint ) {
						$error_message .= '<p class="description">' . $hint . '</p>';
					}
					$error_message .= sprintf(
						'<p class="description">%1$s %2$s<br>%3$s %4$s</p>',
						_x( 'Reason:', 'Reason of the API call error', 'advanced-ads' ),
						$err['reason'],
						_x( 'Message:', 'Error message from Google', 'advanced-ads' ),
						$err['message']
					);
				}
			}

			return [
				'status' => false,
				'msg'    => $error_message,
				'raw'    => trim( $response['body'] ),
			];
		}

		$options = self::get_option();

		if ( ! isset( $response_body['nextPageToken'] ) ) {
			// Results fit into a single page (of 350 items).
			$new_ad_units = [];

			foreach ( $response_body['adUnits'] as $item ) {
				$item                        = self::convert_ad_unit_format( $item );
				$new_ad_units[ $item['id'] ] = $item;
			}

			$options['accounts'][ $account ]['ad_units'] = $new_ad_units;
			update_option( self::OPTNAME, $options );

			return [ 'done' => true ];
		}

		// There are more than 350 items in the account.
		$page_token   = $response_body['nextPageToken'];
		$new_ad_units = [];

		foreach ( $response_body['adUnits'] as $item ) {
			$item                        = self::convert_ad_unit_format( $item );
			$new_ad_units[ $item['id'] ] = $item;
		}

		$page = 1;
		// While there is a next page of results do . . .
		while ( $page_token ) {
			$access_token = self::get_access_token( $account );

			if ( isset( $access_token['msg'] ) ) {
				// return the original error info.
				return $access_token;
			}

			$next_url = $url . '&pageToken=' . urlencode( $page_token );
			$headers  = [
				'Authorization' => 'Bearer ' . $access_token,
			];
			$response = wp_remote_get( $next_url, [ 'headers' => $headers ] );
			self::log( 'Get ad unit list for ca-' . $account . ' page ' . $page );
			++$page;

			if ( is_wp_error( $response ) ) {
				return [
					'status' => false,
					// translators: %s is the publisher ID.
					'msg'    => sprintf( esc_html__( 'Error while retrieving ad unit list for "%s".', 'advanced-ads' ), $account ),
					'raw'    => $response->get_error_message(),
				];
			}

			$response_body = json_decode( $response['body'], true );
			// Update page token if there are ad units left.
			$page_token = isset( $response_body['nextPageToken'] ) ? $response_body['nextPageToken'] : false;
			// Add items from this page into the final result.
			foreach ( $response_body['adUnits'] as $item ) {
				$item                        = self::convert_ad_unit_format( $item );
				$new_ad_units[ $item['id'] ] = $item;
			}
		}

		$options['accounts'][ $account ]['ad_units'] = $new_ad_units;
		update_option( self::OPTNAME, $options );

		return [ 'done' => true ];
	}

	/**
	 * Get the appropriate access token (default one or from user's Google app). Update it if needed.
	 *
	 * @param string $account publisher ID.
	 *
	 * @return array|string the access token on success, error info (as array) if an error occurred.
	 */
	public static function get_access_token( $account ) {
		$options = self::get_option();
		if ( isset( $options['accounts'][ $account ] ) ) {
			if ( self::use_user_app() ) {
				if ( time() > $options['accounts'][ $account ]['user_app']['expires'] ) {
					$new_tokens = self::renew_access_token( $account );
					if ( $new_tokens['status'] ) {
						return $new_tokens['access_token'];
					} else {
						// return all error info [arr]
						return $new_tokens;
					}
				} else {
					return $options['accounts'][ $account ]['user_app']['access_token'];
				}
			} else {
				if ( time() > $options['accounts'][ $account ]['default_app']['expires'] ) {
					$new_tokens = self::renew_access_token( $account );
					if ( $new_tokens['status'] ) {
						return $new_tokens['access_token'];
					} else {
						// return all error info [arr]
						return $new_tokens;
					}
				} else {
					return $options['accounts'][ $account ]['default_app']['access_token'];
				}
			}
		} else {
			// Account does not exist.
			if ( ! empty( $options['accounts'] ) ) {
				// There is another account connected.
				return [
					'status' => false,
					'msg' => esc_html__( 'It seems that some changes have been made in the Advanced Ads settings. Please refresh this page.', 'advanced-ads' ),
					'reload' => true,
				];
			} else {
				// No account at all.
				return [
					'status' => false,
					/* translators: %s account id */
					'msg' => wp_kses( sprintf( __( 'Advanced Ads does not have access to your account (<code>%s</code>) anymore.', 'advanced-ads' ), $account ), [ 'code' => true ] ),
					'reload' => true,
				];
			}
		}
	}

	/**
	 *  Renew the current access token.
	 *
	 * @param array $account AdSense account ID.
	 */
	public static function renew_access_token( $account ) {
		$cid           = self::CID;
		$cs            = self::CS;
		$options       = self::get_option();
		$access_token  = $options['accounts'][ $account ]['default_app']['access_token'];
		$refresh_token = $options['accounts'][ $account ]['default_app']['refresh_token'];

		if ( self::use_user_app() ) {
			$gadsense_data = Advanced_Ads_AdSense_Data::get_instance();
			$_options      = $gadsense_data->get_options();
			$cid           = ADVANCED_ADS_MAPI_CID;
			$cs            = ADVANCED_ADS_MAPI_CIS;
			$access_token  = $options['accounts'][ $account ]['user_app']['access_token'];
			$refresh_token = $options['accounts'][ $account ]['user_app']['refresh_token'];
		}

		$url  = 'https://www.googleapis.com/oauth2/v4/token';
		$args = [
			'body' => [
				'refresh_token' => $refresh_token,
				'client_id'     => $cid,
				'client_secret' => $cs,
				'grant_type'    => 'refresh_token',
			],
		];

		$response = wp_remote_post( $url, $args );
		self::log( 'Refresh access token' );

		if ( is_wp_error( $response ) ) {
			return [
				'status' => false,
				/* translators: %s account id */
				'msg'    => sprintf( esc_html__( 'error while renewing access token for "%s"', 'advanced-ads' ), $account ),
				'raw'    => $response->get_error_message(),
			];
		} else {
			$tokens = json_decode( $response['body'], true );
			// checking for the $tokens is not enough. it can be empty.
			// monitored this, when the access token is revoked from the outside
			// this can happen, when the user connects from another domain.
			if ( null !== $tokens && isset( $tokens['expires_in'] ) ) {
				$expires = time() + absint( $tokens['expires_in'] );
				if ( self::use_user_app() ) {
					$options['accounts'][ $account ]['user_app']['access_token'] = $tokens['access_token'];
					$options['accounts'][ $account ]['user_app']['expires']      = $expires;
				} else {
					$options['accounts'][ $account ]['default_app']['access_token'] = $tokens['access_token'];
					$options['accounts'][ $account ]['default_app']['expires']      = $expires;
				}
				update_option( self::OPTNAME, $options );
				return [
					'status'       => true,
					'access_token' => $tokens['access_token'],
				];
			} else {
				return [
					'status' => false,
					'msg'    => sprintf(
						/* translators: %s AdSense account ID */
						esc_html__( 'invalid response received while renewing access token for "%s"', 'advanced-ads' ),
						$account
					) . ' ' . __( 'You could try to connect again under Advanced Ads > Settings > AdSense.', 'advanced-ads' ),
					'raw'    => $response['body'],
				];
			}
		}
	}

	/**
	 *  Recoke a refresh token
	 */
	public function ajax_revoke_tokken() {

		$nonce = $_POST['nonce'] ?? '';
		if ( ! WordPress::user_can( 'advanced_ads_manage_options' ) ) {
			die;
		}
		if ( false !== wp_verify_nonce( $nonce, 'advads-mapi' ) ) {
			$adsense_id = stripslashes( $_POST['adsenseId'] );
			$options    = self::get_option();

			if ( self::use_user_app() ) {
				$token = $options['accounts'][ $adsense_id ]['user_app']['refresh_token'];
			} else {
				$token = $options['accounts'][ $adsense_id ]['default_app']['refresh_token'];
			}
			$url  = 'https://accounts.google.com/o/oauth2/revoke?token=' . $token;
			$args = [
				'timeout' => 5,
				'header'  => [ 'Content-type' => 'application/x-www-form-urlencoded' ],
			];

			$response = wp_remote_post( $url, $args );

			self::log( 'Revoke API access for ca-' . $adsense_id );

			if ( is_wp_error( $response ) ) {
				echo json_encode( [ 'status' => false ] );
			} else {
				//  remove all the adsense stats
				global $wpdb;
				$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'advanced_ads_adsense_report_%';" );
				delete_option("advanced-ads-adsense-dashboard-filter");

				header( 'Content-Type: application/json' );
				unset( $options['accounts'][ $adsense_id ] );
				update_option( self::OPTNAME, $options );
				echo json_encode( [ 'status' => true ] );
			}
		}
		die;
	}

	/**
	 * When a user manually adds an ad code, save it
	 */
	public function ajax_save_manual_code() {
		if ( ! WordPress::user_can( 'advanced_ads_edit_ads' ) ) {
			die();
		}

		if ( ! wp_verify_nonce( isset( $_POST['nonce'] ) ? $_REQUEST['nonce'] : '', 'advads-mapi' ) ) {
			die();
		}

		$publisher_id = sanitize_text_field( wp_unslash( isset( $_POST['parsed_code']['pubId'] ) ? $_POST['parsed_code']['pubId'] : '' ) );
		if ( ! $this->check_valid_publisher( $publisher_id ) ) {
			wp_send_json_error(
				[
					'message' => __( 'This ad code is from a different AdSense Account', 'advanced-ads' ),
				],
				400
			);
		}

		if ( empty( $_POST['parsed_code']['slotId'] ) || empty( $_POST['raw_code'] ) ) {
			die();
		}

		static $options;
		if ( is_null( $options ) ) {
			$options = self::get_option();
		}

		$slot_id = 'ca-' . $publisher_id . ':' . sanitize_text_field( wp_unslash( $_POST['parsed_code']['slotId'] ) );

		// phpcs:disable WordPress.Security
		$options['ad_codes'][ $slot_id ] = urldecode( $_POST['raw_code'] );
		// phpcs:enable

		if ( array_key_exists( $slot_id, $options['unsupported_units'] ) ) {
			unset( $options['unsupported_units'][ $slot_id ] );
		}

		wp_send_json_success( [ 'updated' => update_option( self::OPTNAME, $options ) ] );
	}

	/**
	 * Check if the provided AdSense Publisher ID matches the saved ID
	 *
	 * @param string $pub AdSense Publisher ID.
	 *
	 * @return bool
	 */
	protected function check_valid_publisher( $pub ) {
		return Advanced_Ads_AdSense_Data::get_instance()->get_adsense_id() === $pub;
	}

	/**
	 * Save ad code reconstructed from ad parameters
	 */
	public function ajax_save_reconstructed_code() {
		$nonce = $_POST['nonce'] ?? '';
		if ( ! WordPress::user_can( 'advanced_ads_edit_ads' ) ) {
			die;
		}

		if ( false !== wp_verify_nonce( $nonce, 'advads-mapi' ) ) {
			$code          = stripslashes( $_POST['code'] );
			$slot          = stripslashes( $_POST['slot'] );
			$gadsense_data = Advanced_Ads_AdSense_Data::get_instance();
			$adsense_id    = $gadsense_data->get_adsense_id();
			$options       = self::get_option();
			$options['ad_codes'][ 'ca-' . $adsense_id . ':' . $slot ] = $code;
			update_option( self::OPTNAME, $options );
			header( 'Content-Type: application/json' );
			echo json_encode( [ 'status' => true ] );
		}
		die;
	}

	/**
	 * Get ad code for a given unit
	 */
	public function ajax_get_ad_code() {
		if ( ! WordPress::user_can( 'advanced_ads_edit_ads' ) ) {
			wp_send_json_error( 'Unauthorized', 401 );
		}

		$post_data = wp_unslash( $_POST );
		$nonce     = isset( $post_data['nonce'] ) ? $post_data['nonce'] : '';
		if ( false !== wp_verify_nonce( $nonce, 'advads-mapi' ) ) {
			$unit = stripslashes( $post_data['unit'] );

			if ( ! self::use_user_app() ) {
				$quota = $this->get_quota();

				// No more quota left.
				if ( $quota['count'] < 1 ) {
					header( 'Content-Type: application/json' );
					$quota_msg = $this->get_quota_msg();
					echo wp_json_encode(
						[
							'quota'    => 0,
							'quotaMsg' => $quota_msg,
						]
					);
					die;
				}
			}

			$code = $this->get_ad_code( $unit );

			/**
			 * Ad code is returned as string. Otherwise it's an error
			 */
			if ( is_string( $code ) ) {
				$ad_units = array_filter(
					Advanced_Ads_Network_Adsense::get_instance()->get_external_ad_units(),
					function ( Advanced_Ads_Ad_Network_Ad_Unit $ad_unit ) use ( $unit ) {
						return $ad_unit->id === $unit;
					}
				);
				$ad_unit  = reset( $ad_units );
				$response = [
					'code' => $code,
					'type' => self::format_ad_data( $ad_unit, 'type' ),
				];

				/**
				 *  Add quota info for default API creds
				 */
				if ( ! self::use_user_app() ) {
					$quota                = $this->get_quota();
					$quota_msg            = $this->get_quota_msg();
					$response['quota']    = $quota['count'];
					$response['quotaMsg'] = $quota_msg;
				}

				header( 'Content-Type: application/json' );
				echo wp_json_encode( $response );

			} else {

				// return info about the error.
				header( 'Content-Type: application/json' );
				echo wp_json_encode( $code );
			}
		}
		die;
	}

	/**
	 *  Dismiss an account alert
	 */
	public function ajax_dismiss_alert() {
		if ( ! WordPress::user_can( 'advanced_ads_manage_options' ) ) {
			die;
		}
		$nonce = $_POST['nonce'] ?? '';
		if ( false !== wp_verify_nonce( $nonce, 'advads-mapi' ) ) {
			$account = stripslashes( $_POST['account'] );
			$id      = stripslashes( $_POST['id'] );
			$options = self::get_option();

			$items = [];

			// the account exists.
			if ( isset( $options['accounts'][ $account ] ) ) {
				// the alert exists.
				if ( isset( $options['accounts'][ $account ]['alerts']['items'][ $id ] ) ) {
					unset( $options['accounts'][ $account ]['alerts']['items'][ $id ] );

					update_option( self::OPTNAME, $options );
					$items = $options['accounts'][ $account ]['alerts']['items'];
				}
			}
			wp_send_json(
				[
					'status' => true,
					'alerts' => $items,
					'length' => count( $items ),
				]
			);
		}
		die;
	}

	/**
	 *  Get / Update the list of alerts on an AdSense account.
	 */
	public function ajax_get_account_alerts() {
		if ( ! WordPress::user_can( 'advanced_ads_manage_options' ) ) {
			die;
		}

		$nonce = $_POST['nonce'] ?? '';
		if ( false !== wp_verify_nonce( $nonce, 'mapi-alerts' ) ) {
			$account = stripslashes( $_POST['account'] );
			$options = self::get_option();

			// the account exists.
			if ( isset( $options['accounts'][ $account ] ) && self::has_token( $account ) ) {
				$access_token = self::get_access_token( $account );
				$url          = str_replace( 'PUBID', $account, self::ALERTS_URL );

				if ( isset( $_POST['inlineAttempt'] ) ) {
					if ( ! is_array( $options['accounts'][ $account ]['alerts'] ) ) {
						$options['accounts'][ $account ]['alerts'] = [];
					}
					$options['accounts'][ $account ]['alerts']['inlineAttempt'] = time();
					update_option( self::OPTNAME, $options );
				}

				// the token is valid.
				if ( ! isset( $access_token['msg'] ) ) {
					$headers  = [
						'Authorization' => 'Bearer ' . $access_token,
					];
					$response = wp_remote_get( $url, [ 'headers' => $headers ] );

					$this->log( 'Get AdSense alerts for ' . $account );

					// the HTTP response is not an error.
					if ( ! is_wp_error( $response ) ) {
						$alerts = json_decode( $response['body'], true );

						// the response body is valid.
						if ( is_array( $alerts ) && isset( $alerts['alerts'] ) ) {
							$items = [];
							foreach ( $alerts['alerts'] as $item ) {
								// Do not store alerts of type "INFO".
								if ( strcasecmp( $item['severity'], 'INFO' ) !== 0 ) {
									$items[ wp_generate_password( 6, false ) ] = $item;
								}
							}

							// filter alerts that are not relevant to the user.
							$items = self::filter_account_alerts( $items );

							$alerts_array = [
								'items' => $items,
								'lastCheck' => time(),
							];

							$options['accounts'][ $account ]['alerts'] = $alerts_array;
							update_option( self::OPTNAME, $options );
							$results = [
								'status' => true,
								'alerts' => $items,
								'length' => count( $items ),
							];
							header( 'Content-Type:application/json' );
							echo wp_json_encode( $results );
						} else {
							$results = [
								'status' => false,
								'msg'    => esc_html__( 'Invalid response body while retrieving account alerts', 'advanced-ads' ),
							];
							header( 'Content-Type:application/json' );
							echo wp_json_encode( $results );
						}
					} else {
						$results = [
							'status' => false,
							'msg'    => esc_html__( 'error while retrieving account alerts', 'advanced-ads' ),
							'raw'    => $response->get_error_message(),
						];
						header( 'Content-Type:application/json' );
						echo wp_json_encode( $results );
					}
				} else {
					// return the original error info.
					return $access_token;
				}
			} else {
				header( 'Content-Type:application/json' );
				echo wp_json_encode( [ 'status' => false ] );
			}
		}
		die;
	}

	/**
	 * Get / Update the ad unit list for a given ad client. The corresponding <select /> input used in the ad selector is passed as a fied of an array
	 */
	public function ajax_get_adUnits() {
		if ( ! WordPress::user_can( 'advanced_ads_edit_ads' ) ) {
			die;
		}
		$post_vars = wp_unslash( $_POST );
		$nonce     = isset( $post_vars['nonce'] ) ? wp_strip_all_tags( $post_vars['nonce'] ) : '';

		if ( wp_verify_nonce( $nonce, 'advads-mapi' ) && isset( $post_vars['account'] ) ) {
			$account = wp_strip_all_tags( stripslashes( $post_vars['account'] ) );
			$units   = self::get_ad_units( $account );

			if ( isset( $units['done'] ) && true === $units['done'] ) {
				ob_start();
				Advanced_Ads_AdSense_Admin::get_mapi_ad_selector();
				$ad_selector = ob_get_clean();

				$response = [
					'status' => true,
					'html'   => $ad_selector,
				];

				/**
				 *  Add quota info for default API creds
				 */
				if ( ! self::use_user_app() ) {
					$quota                = $this->get_quota();
					$quota_msg            = $this->get_quota_msg();
					$response['quota']    = $quota['count'];
					$response['quotaMsg'] = $quota_msg;
				}
			} else {
				// Return the error info array.
				$response = $units;
			}
			header( 'Content-Type: application/json' );
			echo wp_json_encode( $response );
		}
		die;
	}

	/**
	 * Save account and token after account selection MCN.
	 */
	public function ajax_account_selected() {
		if ( ! WordPress::user_can( 'advanced_ads_manage_options' ) ) {
			die;
		}
		$post_data = wp_unslash( $_POST );
		$nonce     = isset( $post_data['nonce'] ) ? $post_data['nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'advads-mapi' ) ) {
			die( 'Unauthorized request' );
		}

		$token_data = wp_unslash( $post_data['token_data'] );
		$account    = wp_unslash( $post_data['account'] );

		if ( $token_data && $account ) {
			self::save_token_from_data( $token_data, $account );
			wp_send_json( [ 'status' => true ] );
		}
		$error = 'Token data missing';
		if ( $token_data ) {
			$error = 'No account provided';
		}

		wp_send_json(
			[
				'status'    => false,
				'error_msg' => $error,
			]
		);
	}

	/**
	 * Get AdSense account details from a new access token.
	 */
	public function ajax_get_account_details() {
		if ( ! WordPress::user_can( 'advanced_ads_manage_options' ) ) {
			wp_send_json_error( 'Unauthorized', 401 );
		}
		$post_data = wp_unslash( $_POST );
		$nonce     = isset( $post_data['nonce'] ) ? $post_data['nonce'] : '';

		if ( ! wp_verify_nonce( $nonce, 'advads-mapi' ) ) {
			wp_send_json_error( 'invalid authorization', 401 );
		}

		$url            = 'https://adsense.googleapis.com/v2/accounts';
		$list_child_url = 'https://adsense.googleapis.com/v2/accounts/%pubid%:listChildAccounts';
		$token_data     = wp_unslash( $post_data['token_data'] );

		if ( ! is_array( $token_data ) ) {
			wp_send_json(
				[
					'status'    => false,
					'error_msg' => esc_html__( 'No token provided. Token data needed to get account details.', 'advanced-ads' ),
				]
			);
		}

		$headers  = [ 'Authorization' => 'Bearer ' . $token_data['access_token'] ];
		$response = wp_remote_get( $url, [ 'headers' => $headers ] );

		self::log( 'Get account details from new access token' );

		if ( is_wp_error( $response ) ) {
			wp_send_json(
				[
					'status'    => false,
					'error_msg' => $response->get_error_message(),
				]
			);
		}
		if ( trim( $response['body'] ) === '{}' ) {
			// Empty, disapproved or other reason.
			$options                  = self::get_option();
			$options['connect_error'] = [
				'message' => esc_html__( 'No AdSense account data found.', 'advanced-ads' ),
				'reason'  => 'noAdsenseData',
			];
			update_option( self::OPTNAME, $options );
			wp_send_json_error( [ 'error' => esc_html__( 'No AdSense account data found.', 'advanced-ads' ) ], 404 );
		}

		$accounts = json_decode( trim( $response['body'] ), true );

		if ( isset( $accounts['accounts'] ) ) {
			$pub_id         = explode( '/', $accounts['accounts'][0]['name'] )[1];
			$child_accounts = wp_remote_get( str_replace( '%pubid%', $pub_id, $list_child_url ), [ 'headers' => $headers ] );

			if ( is_wp_error( $child_accounts ) ) {
				wp_send_json_error( $child_accounts, 500 );
			}
			$accounts_list = json_decode( trim( $child_accounts['body'] ), true );
			if ( trim( $child_accounts['body'] ) === '{}' ) {
				// Standard AdSense account.
				self::save_token_from_data( $token_data, $accounts['accounts'][0] );
				wp_send_json_success( [ 'reload' => true ] );
			}

			if ( null !== $accounts_list ) {
				// Network account.
				$details            = [];
				$html               = '';
				$details[ $pub_id ] = [
					'id'   => $pub_id,
					'name' => $accounts['accounts'][0]['displayName'],
				];
				$html               .= sprintf( '<option value="%1$s">%2$s [%3$s]</option>', esc_attr( $pub_id ), esc_html( $accounts['accounts'][0]['displayName'] ), esc_html( $pub_id ) );

				foreach ( $accounts_list['accounts'] as $item ) {
					$account_id             = explode( '/', $item['name'] )[1];
					$details[ $account_id ] = [
						'id'   => $account_id,
						'name' => $item['displayName'],
					];
					$html                   .= sprintf( '<option value="%1$s">%2$s [%3$s]</option>', esc_attr( $account_id ), esc_html( $item['displayName'] ), esc_html( $account_id ) );
				}
				wp_send_json_success(
					[
						'details'    => $details,
						'html'       => $html,
						'token_data' => $token_data,
					]
				);
			}
			wp_send_json_error( [ 'message' => 'unexpected response - get child accounts' ], 400 );
		}
	}

	/**
	 * Submit Google API confirmation code. Save the token and update ad client list.
	 */
	public function ajax_confirm_code() {
		if ( ! WordPress::user_can( 'advanced_ads_manage_options' ) ) {
			die;
		}
		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
		if ( false !== wp_verify_nonce( $nonce, 'advads-mapi' ) ) {
			$code = urldecode( $_POST['code'] );
			$cid  = self::CID;
			$cs   = self::CS;

			$use_user_app = self::use_user_app();

			if ( $use_user_app ) {
				$cid = ADVANCED_ADS_MAPI_CID;
				$cs  = ADVANCED_ADS_MAPI_CIS;
			}

			$code_url     = 'https://www.googleapis.com/oauth2/v4/token';
			$redirect_uri = self::REDIRECT_URI;
			$grant_type   = 'authorization_code';

			$args = [
				'timeout' => 10,
				'body'    => [
					'code'          => $code,
					'client_id'     => $cid,
					'client_secret' => $cs,
					'redirect_uri'  => $redirect_uri,
					'grant_type'    => $grant_type,
				],
			];

			$response = wp_remote_post( $code_url, $args );

			self::log( 'Confirm authorization code' );

			if ( is_wp_error( $response ) ) {
				return json_encode(
					[
						'status' => false,
						'msg'    => 'error while submitting code',
						'raw'    => $response->get_error_message(),
					]
				);
			} else {
				$token = json_decode( $response['body'], true );

				if ( null !== $token && isset( $token['refresh_token'] ) ) {
					$expires          = time() + absint( $token['expires_in'] );
					$token['expires'] = $expires;
					header( 'Content-Type: application/json' );
					echo json_encode(
						[
							'status'     => true,
							'token_data' => $token,
						]
					);
				} else {
					header( 'Content-Type: application/json' );
					echo json_encode(
						[
							'status'        => false,
							'response_body' => $response['body'],
						]
					);
				}
			}
		}
		die;
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @param string $hook current page hook.
	 */
	public function admin_scripts( $hook ) {
		if ( 'advanced-ads_page_advanced-ads-settings' == $hook ) {
			wp_enqueue_script( 'gasense/mapi/settings', GADSENSE_BASE_URL . 'admin/assets/js/mapi-settings.js', [ 'jquery', 'wp-util' ], ADVADS_VERSION );
		}
	}

	/**
	 * Print alert data in admin footer
	 */
	public function admin_footer() {
		$data           = Advanced_Ads_AdSense_Data::get_instance();
		$adsense_id     = $data->get_adsense_id();
		$has_token      = Advanced_Ads_AdSense_MAPI::has_token( $adsense_id );
		$alerts         = self::get_stored_account_alerts( $adsense_id );
		$refresh_alerts = false;

		// default value, never checked for alerts.
		if ( [] === $alerts && $has_token ) {
			$refresh_alerts = true;
		}
		if ( $has_token && is_array( $alerts ) ) {
			// Check weekly for alerts.
			if ( isset( $alerts['lastCheck'] ) && time() > absint( $alerts['lastCheck'] ) + DAY_IN_SECONDS * 7 ) {
				$refresh_alerts = true;
			}
			// Only try to get the alerts in the background once a day.
			if ( isset( $alerts['inlineAttempt'] ) && time() < $alerts['inlineAttempt'] + DAY_IN_SECONDS ) {
				$refresh_alerts = false;
			}
		}
		if ( $refresh_alerts ) {
			$nonce = wp_create_nonce( 'mapi-alerts' );
			?>
			<input type="hidden" id="advads-mapi-refresh-alerts" />
			<script type="text/javascript">
			;(function($) {

				$( '#mapi-alerts-overlay' ).css( 'display', 'block' );

				var pubId = $( '#adsense-id' ).val();
				$.ajax({
					url: ajaxurl,
					type: 'post',
					data: {
						action: 'advads-mapi-get-alerts',
						account: '<?php echo wp_strip_all_tags( $adsense_id ); ?>',
						nonce: '<?php echo wp_strip_all_tags( $nonce ); ?>',
						inlineAttempt: 1,
					},
					success:function(response, status, XHR) {
						if ( 'undefined' != typeof response.alerts ) {
							$( '#advads-mapi-refresh-alerts' ).trigger( 'advadsMapiRefreshAlerts', [response] );
						}
						$( '#mapi-alerts-overlay' ).css( 'display', 'none' );
					},
					error:function(request, status, error) {
						$( '#mapi-alerts-overlay' ).css( 'display', 'none' );
					},
				});

			})(window.jQuery);
			</script>
			<?php
		}
	}

	public static function log( $task = 'No task provided' ) {
		if ( ! defined( 'ADVANCED_ADS_LOG_ADSENSE_API' ) || ! ADVANCED_ADS_LOG_ADSENSE_API ) {
			return;
		}

		$message = date_i18n( '[Y-m-d H:i:s]' ) . ' ' . $task . "\n";
		error_log( $message, 3, WP_CONTENT_DIR . '/advanced-ads-google-api-requests.log' );
	}

	/**
	 *  Sort ad units list alphabetically
	 */
	public static function get_sorted_adunits( $adunits ) {
		$units_sorted_by_name = [];
		$units_by_id          = [];
		foreach ( $adunits as $unit ) {
			$units_sorted_by_name[ $unit['name'] ] = $unit['id'];
			$units_by_id[ $unit['id'] ]            = $unit;
		}
		ksort( $units_sorted_by_name );
		$units_sorted_by_name = array_flip( $units_sorted_by_name );
		$results              = [];
		foreach ( $units_sorted_by_name as $id => $name ) {
			$results[ $name ] = $units_by_id[ $id ];
		}
		return $results;
	}

	/**
	 * Format ad type and size strings from Google for display
	 *
	 * @param Advanced_Ads_Ad_Network_Ad_Unit $ad_unit the ad unit for which to format the details.
	 * @param string                          $format takes either type or size.
	 *
	 * @return string
	 */
	public static function format_ad_data( Advanced_Ads_Ad_Network_Ad_Unit $ad_unit, $format = 'type' ) {
		if ( 'type' === $format ) {
			$str     = $ad_unit->display_type;
			$options = self::get_option();
			if ( array_key_exists( $ad_unit->id, $options['ad_codes'] ) ) {
				preg_match_all( '/data-ad-format="(?<format>.+?)"|data-ad-layout="(?<layout>.+?)"/', $options['ad_codes'][ $ad_unit->id ], $matches );
				$format = array_filter( $matches['format'] );
				$layout = array_filter( $matches['layout'] );
				$format = reset( $format );
				$layout = reset( $layout );
				if ( empty( $format ) ) {
					$format = '';
				}
				if ( empty( $layout ) ) {
					$layout = '';
				}

				if ( 'autorelaxed' === $format ) {
					$str = _x( 'Multiplex', 'AdSense ad type', 'advanced-ads' );
				} elseif ( 'fluid' === $format ) {
					if ( 'in-article' === $layout ) {
						$str = _x( 'In-article', 'AdSense ad type', 'advanced-ads' );
					} else {
						$str = _x( 'In-feed', 'AdSense ad type', 'advanced-ads' );
					}
				}
			}

			switch ( $str ) {
				case 'DISPLAY':
					$str = _x( 'Display', 'AdSense ad type', 'advanced-ads' );
					break;
				case 'LINK':
					$str = _x( 'Link', 'AdSense ad type', 'advanced-ads' );
					break;
				case 'MATCHED_CONTENT':
					$str = _x( 'Multiplex', 'AdSense ad type', 'advanced-ads' );
					break;
				case 'ARTICLE':
					$str = _x( 'In-article', 'AdSense ad type', 'advanced-ads' );
					break;
				case 'FEED':
					$str = _x( 'In-feed', 'AdSense ad type', 'advanced-ads' );
					break;
				case 'TYPE_UNSPECIFIED':
				default:
			}
		} elseif ( 'size' === $format ) {
			// size.
			$str = $ad_unit->display_size === '1x3' ? 'Responsive' : $ad_unit->display_size;
			if ( strpos( $str, 'SIZE_' ) === 0 ) {
				$str = str_replace( '_', 'x', substr( $str, 5 ) );
			}
			$str = ucfirst( strtolower( $str ) );
		} else {
			$str = '';
		}

		return $str;
	}

	/**
	 * Check if the credential are the default ones or from user's app
	 */
	public static function use_user_app() {
		if ( ( defined( 'ADVANCED_ADS_MAPI_CID' ) && '' != ADVANCED_ADS_MAPI_CID ) && ( defined( 'ADVANCED_ADS_MAPI_CIS' ) && '' != ADVANCED_ADS_MAPI_CIS ) ) {
			return true;
		} else {
			return false;
		}
	}

	public static function has_token( $adsense_id = '' ) {
		if ( empty( $adsense_id ) ) {
			return false;
		}

		$has_token = false;
		$options   = self::get_option();
		if ( self::use_user_app() ) {
			if ( isset( $options['accounts'][ $adsense_id ] ) && ! empty( $options['accounts'][ $adsense_id ]['user_app']['refresh_token'] ) ) {
				$has_token = true;
			}
		} elseif ( isset( $options['accounts'][ $adsense_id ] ) && ! empty( $options['accounts'][ $adsense_id ]['default_app']['refresh_token'] ) ) {
			$has_token = true;
		}

		return $has_token;
	}

	/**
	 * Save token obtained from confirmation code
	 *
	 * @param string $token   access token data.
	 * @param array  $details selected account details.
	 */
	public static function save_token_from_data( $token, $details ) {
		$options    = self::get_option();
		$adsense_id = isset( $details['id'] ) ? $details['id'] : explode( '/', $details['name'] )[1];

		if ( ! isset( $options['accounts'][ $adsense_id ] ) ) {
			$options['accounts'][ $adsense_id ] = self::$empty_account_data;
		}
		if ( self::use_user_app() ) {
			$options['accounts'][ $adsense_id ]['user_app'] = [
				'access_token'  => $token['access_token'],
				'refresh_token' => $token['refresh_token'],
				'expires'       => $token['expires'],
				'token_type'    => $token['token_type'],
			];
		} else {
			$options['accounts'][ $adsense_id ]['default_app'] = [
				'access_token'  => $token['access_token'],
				'refresh_token' => $token['refresh_token'],
				'expires'       => $token['expires'],
				'token_type'    => $token['token_type'],
			];
		}
		$options['accounts'][ $adsense_id ]['details'] = [
			'id'   => $adsense_id,
			'name' => isset( $details['displayName'] ) ? $details['displayName'] : $details['name'],
		];
		$options['connect_error']                      = [];
		update_option( self::OPTNAME, $options );

		$gadsense_data                  = Advanced_Ads_AdSense_Data::get_instance();
		$gadsense_options               = $gadsense_data->get_options();
		$gadsense_options['adsense-id'] = $adsense_id;
		update_option( GADSENSE_OPT_NAME, $gadsense_options );
	}

	/**
	 *  Get a list of stored alerts for a given AdSense account.
	 *
	 *  @param string $pub_id the publisher account.
	 *  @return array $alerts
	 */
	public static function get_stored_account_alerts( $pub_id = '' ) {
		if ( empty( $pub_id ) ) {
			return false;
		}
		$options = self::get_option();
		if ( isset( $options['accounts'][ $pub_id ] ) ) {
			if ( isset( $options['accounts'][ $pub_id ]['alerts'] ) && is_array( $options['accounts'][ $pub_id ]['alerts'] ) ) {
				$alerts = $options['accounts'][ $pub_id ]['alerts'];
				return self::filter_stored_account_alerts( $alerts );
			} else {
				return [];
			}
		}
		return false;
	}

	/**
	 * We filter out specific alerts from the AdSense account when they are
	 * - duplicates
	 * - irrelevant when placing ads in the frontend
	 *
	 * @param array      $alert_items alerts.
	 * @param null|array $disabled_alerts additional disabled alerts.
	 * @return array filtered alert items.
	 */
	public static function filter_account_alerts( array $alert_items, $disabled_alerts = null ) {
		if ( empty( $alert_items ) || ! is_array( $alert_items ) ) {
			return $alert_items;
		}

		// the message IDs we don’t even import from AdSense.
		$disabled_adsense_alerts = [
			'SELLERS_JSON_CONSENT', // AdSense message: We encourage you to publish your seller information in the Google sellers.json file. Visit the account settings page to review your current visibility status.
			'REPORTING_HORIZON_LEGACY_DATA_NOTICE', // AdSense message: Data older than three years is no longer available in Reporting. This data can be downloaded for a limited time.
		];

		// additional messages to disable. Useful if the function is used in different situations.
		if ( ! empty( $disabled_alerts ) && is_array( $disabled_alerts ) ) {
			$disabled_adsense_alerts = array_merge( $disabled_adsense_alerts, $disabled_alerts );
		}

		// remove alerts based on specific IDs.
		foreach ( $alert_items as $internal_id => $item ) {
			if (
				( isset( $item['id'] ) && in_array( $item['id'], $disabled_adsense_alerts, true ) )
				|| ( isset( $item['type'] ) && in_array( str_replace( '-', '_', strtoupper( $item['type'] ) ), $disabled_adsense_alerts, true ) )
			) {
				unset( $alert_items[ $internal_id ] );
			}
		}

		return $alert_items;
	}

	/**
	 * Remove alerts dynamically when showing them.
	 * only applies to stored alerts and not when they are loaded directly from AdSense
	 *
	 * @param array $alerts Alert options.
	 * @return array $alerts Modified alert options.
	 */
	public static function filter_stored_account_alerts( array $alerts ) {
		if ( empty( $alerts['items'] ) || ! is_array( $alerts['items'] ) ) {
			return $alerts;
		}

		$disabled_alerts = [];

		/**
		 * Asside from the basic filter, we also filter out some messages only from showing up while we still import them
		 * This allows us to show them only under related conditions
		 */
		// Remove `ads.txt` related alerts if the file is displayed to visitors.
		if ( Advanced_Ads_Ads_Txt_Admin::is_displayed() ) {
			$disabled_alerts = [ 'ALERT_TYPE_ADS_TXT_UNAUTHORIZED', 'ADS_TXT_MISSING', 'ADS_TXT_ISSUES' ];
		}

		$alerts['items'] = self::filter_account_alerts( $alerts['items'], $disabled_alerts );

		return $alerts;
	}

	/**
	 * Checks if there is any AdSense warning for the currently connected AdSense account.
	 *
	 * @param array $messages The array of messages.
	 *
	 * @return array The modified array.
	 */
	public static function adsense_warnings_check( $messages ) {
		$data       = Advanced_Ads_AdSense_Data::get_instance();
		$adsense_id = $data->get_adsense_id();
		$alerts     = self::get_stored_account_alerts( $adsense_id );

		if ( ! is_array( $messages ) ) {
			$messages = [];
		}

		if ( ! empty( $alerts ) && ! empty( $alerts['items'] ) ) {
			$messages[] = sprintf(
				wp_kses(
					/* translators: %s admin setting page link */
					__( 'There are one or more warnings about the currently linked AdSense account. You can view them <a href="%s">here</a>', 'advanced-ads' ),
					[ 'a' => [ 'href' => true ] ]
				),
				esc_url( admin_url( 'admin.php?page=advanced-ads-settings#top#adsense' ) )
			);
		}
		return $messages;
	}

	/**
	 * Get the class's option
	 */
	public static function get_option() {
		$options = get_option( self::OPTNAME, [] );
		if ( ! is_array( $options ) ) {
			$options = [];
		}
		return $options + self::$default_options;
	}

	/**
	 * Get the URL to the AdSense error page
	 *
	 * @param string $code Add the error code to the URL.
	 *
	 * @return string The entire text with the url.
	 */
	public static function get_adsense_error_link( $code = '' ) {
		if ( ! empty( $code ) ) {
			$code = '-' . $code;
		}

		return sprintf(
			// translators: %1$s is an anchor (link) opening tag, %2$s is the closing tag.
			esc_attr__( 'Learn more about AdSense account issues %1$shere%2$s.', 'advanced-ads' ),
			'<a href="https://wpadvancedads.com/adsense-errors/?utm_source=advanced-ads&utm_medium=link&utm_campaign=adsense-error' . $code . '" target="_blank">',
			'</a>'
		);
	}

	/**
	 * Get custom account connection error message list.
	 */
	public static function get_connect_error_messages() {
		$messages     = [];
		$health_class = Advanced_Ads_Ad_Health_Notices::get_instance();
		foreach ( $health_class->default_notices as $key => $value ) {
			if ( 0 === strpos( $key, 'adsense_connect_' ) ) {
				$messages[ substr( $key, strlen( 'adsense_connect_' ) ) ] = $value['text'];
			}
		}
		return $messages;
	}

	/**
	 * Get custom messages for AdSense alerts.
	 */
	public static function get_adsense_alert_messages() {
		$messages     = [];
		$health_class = Advanced_Ads_Ad_Health_Notices::get_instance();

		foreach ( $health_class->default_notices as $key => $value ) {
			if ( 0 === strpos( $key, 'adsense_alert_' ) ) {
				$messages[ substr( $key, strlen( 'adsense_alert_' ) ) ] = $value['text'];
			}
		}
		return $messages;
	}

	/**
	 * Get class instance
	 *
	 * @return Advanced_Ads_AdSense_MAPI
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get a hint for an error object that was received from AdSense
	 *
	 * @param string $reason The reason from the response's error.
	 *
	 * @return string|bool if there is a hint for this reason, a string containing the hint will be returned.
	 */
	final public static function get_adsense_error_hint( $reason ) {

		if ( 'authError' === $reason ) {
			return sprintf(
				/* translators: 1: A link to the settings page 2: The name of an ad network */
				__( 'Please try to <a href="%1$s" target="_blank">reconnect to your %2$s account</a>.', 'advanced-ads' ),
				admin_url( 'admin.php?page=advanced-ads-settings#top#adsense' ),
				'AdSense'
			);
		}

		return false;
	}
}
