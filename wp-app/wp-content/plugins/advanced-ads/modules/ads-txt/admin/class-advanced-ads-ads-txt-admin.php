<?php // phpcs:ignoreFile

use AdvancedAds\Utilities\WordPress;

/**
 * User interface for managing the 'ads.txt' file.
 */
class Advanced_Ads_Ads_Txt_Admin {
	/**
	 * Ads.txt data management class
	 *
	 * @var Advanced_Ads_Ads_Txt_Strategy
	 */
	private $strategy;

	/**
	 * Ads.txt frontend logic class
	 *
	 * @var Advanced_Ads_Ads_Txt_Public
	 */
	private $public;

	/**
	 * AdSense network ID.
	 */
	const adsense = 'adsense';

	const ACTION = 'wp_ajax_advads-ads-txt';

	/**
	 * Whether the notices should be updated via AJAX because no cached data exists.
	 *
	 * @var bool
	 */
	private $notices_are_stale = false;

	/**
	 * Constructor
	 *
	 * @param Advanced_Ads_Ads_Txt_Strategy $strategy Ads.txt data management class.
	 * @param Advanced_Ads_Ads_Txt_Public   $public Ads.txt frontend logic class.
	 */
	public function __construct( Advanced_Ads_Ads_Txt_Strategy $strategy, Advanced_Ads_Ads_Txt_Public $public ) {
		$this->strategy = $strategy;
		$this->public   = $public;

		add_filter( 'advanced-ads-sanitize-settings', [ $this, 'toggle' ], 10, 1 );
		add_action( 'pre_update_option_advanced-ads-adsense', [ $this, 'update_adsense_option' ], 10, 2 );
		add_action( 'advanced-ads-settings-init', [ $this, 'add_settings' ] );
		add_action( self::ACTION, [ $this, 'ajax_refresh_notices' ] );
	}


	/**
	 * Toggle ads.txt and add additional content.
	 *
	 * @param array $options Options.
	 * @return array $options Options.
	 */
	public function toggle( $options ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$create             = ! empty( $_POST['advads-ads-txt-create'] );
		$all_network        = ! empty( $_POST['advads-ads-txt-all-network'] );
		$additional_content = ! empty( $_POST['advads-ads-txt-additional-content'] ) ? trim( wp_unslash( $_POST['advads-ads-txt-additional-content'] ) ) : '';
		// phpcs:enable

		$this->strategy->toggle( $create, $all_network, $additional_content );
		$content = $this->get_adsense_blog_data();
		$this->strategy->add_network_data( self::adsense, $content );
		$r = $this->strategy->save_options();

		if ( is_wp_error( $r ) ) {
			add_settings_error(
				'advanced-ads-adsense',
				'adsense-ads-txt-created',
				$r->get_error_message(),
				'error'
			);
		}

		return $options;
	}

	/**
	 * Update the 'ads.txt' file every time the AdSense settings are saved.
	 * The reason for not using `update_option_*` filter is that the function
	 * should also get called for newly added AdSense options.
	 *
	 * @param array $prev Previous options.
	 * @return array $new New options.
	 */
	public function update_adsense_option( $new, $prev ) {
		if ( $new === $prev ) {
			return $new;
		}
		$content = $this->get_adsense_blog_data( $new );
		$this->strategy->add_network_data( self::adsense, $content );
		$r = $this->strategy->save_options();

		if ( is_wp_error( $r ) ) {
			add_settings_error(
				'advanced-ads-adsense',
				'adsense-ads-txt-created',
				$r->get_error_message(),
				'error'
			);
		}
		return $new;
	}

	/**
	 * Add setting fields.
	 *
	 * @param string $hook The slug-name of the settings page.
	 */
	public function add_settings( $hook ) {

		$adsense_data = Advanced_Ads_AdSense_Data::get_instance();
		$adsense_id   = $adsense_data->get_adsense_id();

		add_settings_section(
			'advanced_ads_ads_txt_setting_section',
			'ads.txt',
			[ $this, 'render_ads_txt_section_callback' ],
			$hook
		);

		add_settings_field(
			'adsense-ads-txt-enable',
			'',
			[ $this, 'render_setting_toggle' ],
			$hook,
			'advanced_ads_ads_txt_setting_section'
		);

		add_settings_field(
			'adsense-ads-txt-content',
			'',
			[ $this, 'render_setting_additional_content' ],
			$hook,
			'advanced_ads_ads_txt_setting_section'
		);
	}

	public function render_ads_txt_section_callback() {}

	/**
	 * Render toggle settings.
	 */
	public function render_setting_toggle() {
		global $current_blog;
		$domain = isset( $current_blog->domain ) ? $current_blog->domain : '';

		$can_process_all_network = $this->can_process_all_network();
		$is_all_network          = $this->strategy->is_all_network();

		$is_enabled = $this->strategy->is_enabled();
		include dirname( __FILE__ ) . '/views/setting-create.php';
	}

	/**
	 * Render additional content settings.
	 */
	public function render_setting_additional_content() {
		$content = $this->strategy->get_additional_content();
		$notices = $this->get_notices();
		$notices = $this->get_notices_markup( $notices );

		$link         = home_url( '/' ) . 'ads.txt';
		$adsense_line = $this->get_adsense_blog_data();
		include dirname( __FILE__ ) . '/views/setting-additional-content.php';
	}

	/**
	 * Check if other sites of the network can be processed by the user.
	 *
	 * @return bool
	 */
	private function can_process_all_network() {
		return ! Advanced_Ads_Ads_Txt_Utils::is_subdir()
			&& is_super_admin()
			&& is_multisite();
	}

	/**
	 * Get notices.
	 *
	 * @return array Array of notices.
	 */
	public function get_notices() {
		$url        = home_url( '/' );
		$parsed_url = wp_parse_url( $url );
		$notices    = [];

		if ( ! isset( $parsed_url['scheme'] ) || ! isset ( $parsed_url['host'] ) ) {
			return $notices;
		}

		$link   = sprintf( '<a href="%1$s" target="_blank">%1$s</a>', esc_url( $url . 'ads.txt' ) );
		$button = ' <button type="button" class="advads-ads-txt-action button" style="vertical-align: middle;" id="%s">%s</button>';

		if ( ! $this->strategy->is_enabled() ) {
			return $notices;
		}

		if ( Advanced_Ads_Ads_Txt_Utils::is_subdir() ) {
			$notices[] = [
				'advads-error-message',
				sprintf(
					/* translators: %s homepage link */
					esc_html__( 'The ads.txt file cannot be placed because the URL contains a subdirectory. You need to make the file available at %s', 'advanced-ads' ),
					sprintf( '<a href="%1$s" target="_blank">%1$s</a>', esc_url( $parsed_url['scheme'] . '://' . $parsed_url['host'] ) )
				),
			];
		} else {
			if ( null === ( $file = $this->get_notice( 'get_file_info', $url ) ) ) {
				$this->notices_are_stale = true;
				return $notices;
			}

			if ( ! is_wp_error( $file ) ) {
				if ( $file['exists'] ) {
					$notices[] = [
						'',
						sprintf(
							/* translators: %s link of ads.txt */
							esc_html__( 'The file is available on %s.', 'advanced-ads' ),
							$link
						),
					];
				} else {
					$notices[] = [ '', esc_html__( 'The file was not created.', 'advanced-ads' ) ];
				}

				if ( $file['is_third_party'] ) {
					/* translators: %s link */
					$message = sprintf( esc_html__( 'A third-party file exists: %s', 'advanced-ads' ), $link );

					if ( $this->can_edit_real_file() ) {
						$message .= sprintf( $button, 'advads-ads-txt-remove-real', __( 'Import & Replace', 'advanced-ads' ) );
						$message .= '<p class="description">'
							. __( 'Move the content of the existing ads.txt file into Advanced Ads and remove it.', 'advanced-ads' )
						. '</p>';
					}
					$notices['is_third_party'] = [ 'advads-error-message', $message ];
				}
			} else {
				$notices[] = [
					'advads-error-message',
					sprintf(
						/* translators: %s is replaced with an error message. */
						esc_html__( 'An error occured: %s.', 'advanced-ads' ),
						esc_html( $file->get_error_message() )
					),
				];
			}

			$need_file_on_root_domain = $this->get_notice( 'need_file_on_root_domain', $url );
			if ( null === $need_file_on_root_domain ) {
				$this->notices_are_stale = true;
				return $notices;
			}

			if ( $need_file_on_root_domain ) {
				$notices[] = [
					'advads-ads-txt-nfor',
					sprintf(
						/* translators: %s the line that may need to be added manually */
						esc_html__( 'If your site is located on a subdomain, you need to add the following line to the ads.txt file of the root domain: %s', 'advanced-ads' ),
						// Without http://.
						'<code>subdomain=' . esc_html( $parsed_url['host'] ) . '</code>'
					),
				];
			}
		}

		return $notices;
	}

	/**
	 * Get HTML markup of the notices.
	 *
	 * @param array $notices Notices.
	 * @return string $r HTML markup.
	 */
	private function get_notices_markup( $notices ) {
		if ( $this->notices_are_stale ) {
			// Do not print `ul` to fetch notices via AJAX.
			return '';
		}

		$r = '<ul id="advads-ads-txt-notices">';
		foreach ( $notices as $notice ) {
			$r .= sprintf( '<li class="%s">%s</li>', $notice[0], $notice[1] );
		}
		$r .= '</ul>';
		return $r;
	}

	/**
	 * Check if the `ads.txt` file is displayed to visitors.
	 *
	 * @return bool True if displayed, False otherwise.
	 */
	public static function is_displayed() {
		$url = home_url( '/' );

		$file = self::get_notice( 'get_file_info', $url );
		return is_array( $file ) && ! empty( $file['exists'] );
	}

	/**
	 * Get a notice.
	 *
	 * @return null/bool Boolean on success or null if no cached data exists.
	 *                   In the latter case, this function should be called using AJAX
	 *                   to get fresh data.
	 */
	public static function get_notice( $func, $url ) {
		if ( ! method_exists( 'Advanced_Ads_Ads_Txt_Utils', $func ) ) {
			return false;
		}

		$url       = $url ? $url : home_url( '/' );
		$key       = self::get_transient_key();
		$transient = get_transient( $key );

		if ( ! wp_doing_ajax() || ! doing_action( self::ACTION ) ) {
			return isset( $transient[ $func ] ) ? $transient[ $func ] : null;
		}

		$r = call_user_func( [ 'Advanced_Ads_Ads_Txt_Utils', $func ], $url );

		$transient          = is_array( $transient ) ? $transient : [];
		$transient[ $func ] = $r;
		set_transient( $key, $transient, WEEK_IN_SECONDS );
		return $r;
	}

	/**
	 * Get Adsense data.
	 *
	 * @param array $new New data.
	 *
	 * @return string
	 */
	public function get_adsense_blog_data( $new = null ) {
		if ( null === $new ) {
			$new = Advanced_Ads_AdSense_Data::get_instance()->get_options();
		}

		$adsense_id = ! empty( $new['adsense-id'] ) ? trim( $new['adsense-id'] ) : '';
		if ( ! $adsense_id ) {
			return '';
		}

		$data   = [
			'domain'                  => 'google.com',
			'account_id'              => $adsense_id,
			'account_type'            => 'DIRECT',
			'certification_authority' => 'f08c47fec0942fa0',
		];
		$result = implode( ', ', $data );

		return $result;
	}

	/**
	 * Check if a third-party ads.txt file exists.
	 */
	public function ajax_refresh_notices() {

		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );

		if ( ! WordPress::user_can( 'advanced_ads_manage_options' ) ) {
			return;
		}

		$response       = [];
		$action_notices = [];

		if ( isset( $_REQUEST['type'] ) ) {
			if ( 'remove_real_file' === $_REQUEST['type'] ) {
				$remove = $this->remove_real_file();
				if ( is_wp_error( $remove ) ) {
					$action_notices[] = [ 'advads-ads-txt-updated advads-notice-inline advads-error', $remove->get_error_message() ];
				} else {
					$action_notices[]               = [
						'advads-ads-txt-updated',
						__( 'The ads.txt is now managed with Advanced Ads.', 'advanced-ads' )
					];
					$options                        = $this->strategy->get_options();
					$response['additional_content'] = esc_textarea( $options['custom'] );
				}
			}

			if ( 'create_real_file' === $_REQUEST['type'] ) {
				$action_notices[] = $this->create_real_file();
			}
		}

		$notices             = $this->get_notices();
		$notices             = array_merge( $notices, $action_notices );
		$response['notices'] = $this->get_notices_markup( $notices );

		wp_send_json( $response );
	}

	/**
	 * Connect to the filesystem.
	 */
	private function fs_connect() {
		global $wp_filesystem;
		$fs_connect = Advanced_Ads_Filesystem::get_instance()->fs_connect( [ ABSPATH ] );

		if ( false === $fs_connect || is_wp_error( $fs_connect ) ) {
			$message = __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'advanced-ads' );

			if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
				$message = esc_html( $wp_filesystem->errors->get_error_message() );
			}
			if ( is_wp_error( $fs_connect ) && $fs_connect->get_error_code() ) {
				$message = esc_html( $fs_connect->get_error_message() );
			}
			return new WP_Error( 'can_not_connect', $message );
		}
		return true;
	}

	/**
	 * Remove existing real ads.txt file.
	 */
	public function remove_real_file() {
		global $wp_filesystem;

		if ( ! $this->can_edit_real_file() ) {
			return new WP_Error( 'not_main_site', __( 'Not the main blog', 'advanced-ads' ) );
		}

		$fs_connect = $this->fs_connect();
		if ( is_wp_error( $fs_connect ) ) {
			return $fs_connect;
		}

		$abspath = trailingslashit( $wp_filesystem->abspath() );
		$file    = $abspath . 'ads.txt';
		if ( $wp_filesystem->exists( $file ) && $wp_filesystem->is_file( $file ) ) {
			$data = $wp_filesystem->get_contents( $file );

			$tp_file = new Advanced_Ads_Ads_Txt_Real_File();
			$tp_file->parse_file( $data );

			$aa_data = $this->public->get_frontend_output();
			$aa_file = new Advanced_Ads_Ads_Txt_Real_File();
			$aa_file->parse_file( $aa_data );

			$tp_file->subtract( $aa_file );
			$output = $tp_file->output();
			$this->strategy->set_additional_content( $output );
			$this->strategy->save_options();

			if ( $wp_filesystem->delete( $file ) ) {
				return true;
			} else {
				return new WP_Error( 'could_not_delete', __( 'Could not delete the existing ads.txt file', 'advanced-ads' ) );
			}
		} else {
			return new WP_Error( 'not_found', __( 'Could not find the existing ads.txt file', 'advanced-ads' ) );
		}
	}

	/**
	 * Check if the user is alowed to edit real file.
	 */
	private function can_edit_real_file() {
		return is_super_admin();
	}


	/**
	 * Get transient key.
	 */
	public static function get_transient_key() {
		return 'advanced_ads_ads_txt_ctp' . home_url( '/') ;
	}
}
