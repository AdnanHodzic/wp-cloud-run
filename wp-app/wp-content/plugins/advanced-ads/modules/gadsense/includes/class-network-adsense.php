<?php
/**
 * Class Advanced_Ads_Network_Adsense
 */
class Advanced_Ads_Network_Adsense extends Advanced_Ads_Ad_Network {
	/**
	 * An array containing all the AdSense status codes that flag an {$link Advanced_Ads_Ad_Network_Ad_Unit} ad unit as active
	 * for downward compatibility with PHP < 5.6 the const had to be changed to static field. you can revert to const when PHP5 support is FINALLY dropped
	 *
	 * @var array
	 */
	private static $status_codes_active = [ 'ACTIVE', 'NEW' ];

	/**
	 * A globally usable instance, that will be created when calling {$link Advanced_Ads_Ad_Network#get_instance) for the first time
	 *
	 * @var Advanced_Ads_Ad_Type_Adsense
	 */
	private static $instance;

	/**
	 * AdSense options handling class.
	 *
	 * @var Advanced_Ads_AdSense_Data
	 */
	private $data;

	/**
	 * The AdSense network’s settings section ID
	 *
	 * @var string
	 */
	protected $settings_section_id;

	/**
	 * Instance of Advanced_Ads_Network_Adsense
	 *
	 * @return Advanced_Ads_Ad_Type_Adsense|Advanced_Ads_Network_Adsense
	 */
	final public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Advanced_Ads_Network_Adsense();
		}
		return self::$instance;
	}

	/**
	 * Advanced_Ads_Network_Adsense constructor.
	 */
	public function __construct() {
		parent::__construct( 'adsense', 'AdSense' );
		$this->data = Advanced_Ads_AdSense_Data::get_instance();

		// Adsense does not use the default generated settings section id. overwrite it with the old value.
		$this->settings_section_id = 'advanced_ads_adsense_setting_section';
	}

	/**
	 * Register settings to Advanced Ads > Settings > AdSense
	 *
	 * @param string $hook settings page hook.
	 * @param string $section_id settings section id.
	 */
	protected function register_settings( $hook, $section_id ) {
		// Add setting field to disable ads.
		add_settings_field(
			'adsense-id',
			__( 'AdSense account', 'advanced-ads' ),
			[ $this, 'render_settings_adsense_id' ],
			$hook,
			$section_id
		);

		// Activate AdSense verification code and Auto ads (previously Page-Level ads).
		add_settings_field(
			'adsense-page-level',
			__( 'Auto ads', 'advanced-ads' ),
			[ $this, 'render_settings_adsense_page_level' ],
			$hook,
			$section_id
		);

		// AdSense anchor ad on top of pages.
		// Only show this field if selected, otherwise use new auto ads code.
		if ( isset( $this->data->get_options()['top-anchor-ad'] ) ) {
			add_settings_field(
				'top_anchor_ad',
				__( 'Auto ads', 'advanced-ads' ) . ':&nbsp;' . __( 'Disable top anchor ad', 'advanced-ads' ),
				[ $this, 'render_settings_adsense_top_anchor_ad' ],
				$hook,
				$section_id
			);
		}

		// Hide AdSense stats in the backend.
		add_settings_field(
			'hide_stats',
			__( 'Disable stats', 'advanced-ads' ),
			[ $this, 'render_settings_adsense_hide_stats' ],
			$hook,
			$section_id
		);

		// Disable AdSense violation warnings.
		add_settings_field(
			'adsense-warnings-disable',
			__( 'Disable violation warnings', 'advanced-ads' ),
			[ $this, 'render_settings_adsense_warnings_disable' ],
			$hook,
			$section_id
		);

		// Show AdSense widget on WP Dashboard.
		add_settings_field(
			'adsense_wp_dashboard',
			__( 'Show AdSense Earnings', 'advanced-ads' ),
			[ $this, 'render_settings_adsense_wp_dashboard' ],
			$hook,
			$section_id
		);

		add_settings_field(
			'adsense-background',
			__( 'Transparent background', 'advanced-ads' ),
			[ $this, 'render_settings_adsense_background' ],
			$hook,
			$section_id
		);

		add_settings_field(
			'adsense-full-width',
			__( 'Full width responsive ads on mobile', 'advanced-ads' ),
			[ $this, 'render_settings_adsense_fullwidth' ],
			$hook,
			'advanced_ads_adsense_setting_section'
		);
	}

	/**
	 * Render AdSense settings section
	 */
	public function render_settings_section_callback() {
		// For whatever purpose there might come.
	}

	/**
	 * Render AdSense management api setting
	 */
	public function render_settings_management_api() {
		require_once GADSENSE_BASE_PATH . 'admin/views/mapi-settings.php';
	}

	/**
	 * Render AdSense id setting
	 */
	public function render_settings_adsense_id() {
		require_once GADSENSE_BASE_PATH . 'admin/views/adsense-account.php';
	}

	/**
	 * Render top anchor ad setting
	 */
	public function render_settings_adsense_top_anchor_ad() {
		$options   = $this->data->get_options();
		$anchor_ad = isset( $options['top-anchor-ad'] ) ? $options['top-anchor-ad'] : '';
		?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( GADSENSE_OPT_NAME ); ?>[top-anchor-ad]" value="1" <?php checked( $anchor_ad ); ?> />
			<?php esc_html_e( 'Enable this box if you don’t want Google Auto ads to place anchor ads at the top of your page.', 'advanced-ads' ); ?>
			<?php Advanced_Ads_Admin::show_deprecated_notice('top Anchor Ad'); ?>
		</label>
		<?php
	}

	/**
	 * Render setting to hide AdSense stats showing in the backend
	 */
	public function render_settings_adsense_hide_stats() {
		$options    = $this->data->get_options();
		$hide_stats = isset( $options['hide-stats'] );
		?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( GADSENSE_OPT_NAME ); ?>[hide-stats]" value="1" <?php checked( $hide_stats ); ?> />
			<?php esc_html_e( 'Enable this option to stop loading stats from AdSense into your WordPress backend.', 'advanced-ads' ); ?>
		</label>
		<?php
	}

	/**
	 * Render setting to hide AdSense stats showing in the backend
	 */
	public function render_settings_adsense_wp_dashboard() {
		$options     = $this->data->get_options();
		$show_widget = isset( $options['adsense-wp-widget'] );
		?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( GADSENSE_OPT_NAME ); ?>[adsense-wp-widget]" value="1" <?php checked( $show_widget ); ?> />
			<?php esc_html_e( 'Show Earnings widget on the WordPress dashboard.', 'advanced-ads' ); ?>
		</label>
		<?php
	}

	/**
	 * Render page-level ads setting
	 *
	 * @since 1.6.9
	 */
	public function render_settings_adsense_page_level() {
		$options    = $this->data->get_options();
		$page_level = $options['page-level-enabled'];

		?>
		<label><input type="checkbox" name="<?php echo esc_attr( GADSENSE_OPT_NAME ); ?>[page-level-enabled]" value="1" <?php checked( $page_level ); ?> />
			<?php esc_attr_e( 'Insert the AdSense header code to enable Auto ads and verify your website.', 'advanced-ads' ); ?>
		</label>
		<ul>
			<li><a href="https://wpadvancedads.com/adsense-auto-ads-wordpress/?utm_source=advanced-ads&utm_medium=link&utm_campaign=settings-adsense-specific-pages#Display_AdSense_Auto_Ads_only_on_specific_pages" target="_blank"><?php esc_attr_e( 'Display Auto ads only on specific pages', 'advanced-ads' ); ?></a></li>
			<li><a href="https://wpadvancedads.com/adsense-in-random-positions-auto-ads/?utm_source=advanced-ads&utm_medium=link&utm_campaign=backend-autoads-ads" target="_blank"><?php esc_attr_e( 'Why are ads appearing in random positions?', 'advanced-ads' ); ?></a></li>
			<?php
			if ( ! empty( $options['adsense-id'] ) ) :
				?>
				<li><a href="https://www.google.com/adsense/new/u/0/<?php echo esc_attr( $options['adsense-id'] ); ?>/myads/auto-ads" target="_blank">
						<?php
						 /* translators: this is the text for a link to a sub-page in an AdSense account */
						esc_attr_e( 'Adjust Auto ads options', 'advanced-ads' );
						?>
					</a></li>
				<?php
			endif;
			?>
		</ul>
		<?php if ( Advanced_Ads_Compatibility::borlabs_cookie_adsense_auto_ads_code_exists() ) : ?>
			<p class="advads-notice-inline advads-error">
				<?php require GADSENSE_BASE_PATH . 'admin/views/borlabs-cookie-auto-ads-warning.php'; ?>
			</p>
		<?php endif; ?>

		<?php
		self::render_settings_adsense_amp();

		do_action( 'advanced-ads-settings-adsense-below-auto-ads-option' );
	}

	/**
	 * Render Adsense AMP setting fields.
	 */
	public function render_settings_adsense_amp() {
		// AMP Auto ads was removed from Responsive add-on version 1.10.0
		if ( defined( 'AAR_VERSION' ) && 1 === version_compare( '1.10.0', AAR_VERSION ) ) {
			return;
		}

		$adsense_options  = Advanced_Ads_AdSense_Data::get_instance()->get_options();
		$auto_ads_enabled = ! empty( $adsense_options['amp']['auto_ads_enabled'] );
		$option_name      = GADSENSE_OPT_NAME . '[amp]';
		include GADSENSE_BASE_PATH . 'admin/views/settings/amp-auto-ads.php';
	}

	/**
	 * Render AdSense violation warnings setting
	 *
	 * @since 1.6.9
	 */
	public function render_settings_adsense_warnings_disable() {
		$options                    = $this->data->get_options();
		$disable_violation_warnings = isset( $options['violation-warnings-disable'] ) ? 1 : 0;

		?>
		<label><input type="checkbox" name="<?php echo esc_attr( GADSENSE_OPT_NAME ); ?>[violation-warnings-disable]" value="1" <?php checked( 1, $disable_violation_warnings ); ?> />
			<?php esc_html_e( 'Disable warnings about potential violations of the AdSense terms.', 'advanced-ads' ); ?></label>
		<p class="description">
			<?php
			printf(
				wp_kses(
				/* translators: %s is a URL. */
					__( 'Our <a href="%s" target="_blank">Ad Health</a> feature monitors if AdSense is implemented correctly on your site. It also considers ads not managed with Advanced Ads. Enable this option to remove these checks', 'advanced-ads' ),
					[
						'a' => [
							'href'   => true,
							'target' => true,
						],
					]
				),
				'https://wpadvancedads.com/manual/ad-health/?utm_source=advanced-ads&utm_medium=link&utm_campaign=backend-autoads-ads'
			);
			?>
		</p>
		<?php
	}

	/**
	 * Render transparent background setting.
	 */
	public function render_settings_adsense_background() {
		$options    = $this->data->get_options();
		$background = $options['background'];

		?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( GADSENSE_OPT_NAME ); ?>[background]" value="1" <?php checked( $background ); ?> />
			<?php esc_html_e( 'Enable this option in case your theme adds an unfortunate background color to AdSense ads.', 'advanced-ads' ); ?>
		</label>
		<?php
	}

	/**
	 * Render full width ads setting.
	 */
	public function render_settings_adsense_fullwidth() {
		$options = $this->data->get_options();
		$fw      = ! empty( $options['fullwidth-ads'] ) ? $options['fullwidth-ads'] : 'default';
		?>
		<select name="<?php echo esc_attr( GADSENSE_OPT_NAME ); ?>[fullwidth-ads]">
			<option value="default" <?php selected( $fw, 'default' ); ?>><?php esc_html_e( 'default', 'advanced-ads' ); ?></option>
			<option value="enable" <?php selected( $fw, 'enable' ); ?>><?php esc_html_e( 'enable', 'advanced-ads' ); ?></option>
			<option value="disable" <?php selected( $fw, 'disable' ); ?>><?php esc_html_e( 'disable', 'advanced-ads' ); ?></option>
		</select>
		<p class="description">
			<?php
			echo wp_kses(
				sprintf(
				/* translators: %s is a URL. */
					__( "Whether your responsive ad unit may expand to <a href='%s' target='blank'>use the full width</a> of your visitor's mobile device screen", 'advanced-ads' ),
					esc_url( 'https://support.google.com/adsense/answer/7445870' )
				),
				[
					'a' => [
						'href'   => true,
						'target' => true,
					],
				]
			);
			?>
		</p>
		<?php
	}

	/**
	 * Sanitize AdSense settings
	 *
	 * @param array $options all the options.
	 */
	protected function sanitize_settings( $options ) {
		// Sanitize whatever option one wants to sanitize.
		if ( isset( $options['adsense-id'] ) && '' !== $options['adsense-id'] ) {
			// Remove "ca-" prefix if it was added by the user.
			if ( 0 === strpos( $options['adsense-id'], 'ca-' ) ) {
				$options['adsense-id'] = str_replace( 'ca-', '', $options['adsense-id'] );
			}

			// Trim publisher id.
			$options['adsense-id'] = trim( $options['adsense-id'] );
		}
		return $options;
	}

	/**
	 * Sanitize ad settingssave publisher id from new ad unit if not given in main options
	 *  save publisher id from new ad unit if not given in main options
	 *
	 * @param array $ad_settings_post ad settings.
	 * @return array sanitized ad settings.
	 */
	public function sanitize_ad_settings( $ad_settings_post ) {
		// Check ad type.
		if ( ! isset( $ad_settings_post['type'] ) || 'adsense' !== $ad_settings_post['type'] ) {
			return $ad_settings_post;
		}

		// Save AdSense publisher ID if there is no one stored yet.
		if ( ! empty( $ad_settings_post['output']['adsense-pub-id'] ) ) {
			// Get options.
			$adsense_options = get_option( 'advanced-ads-adsense', [] );

			if ( empty( $adsense_options['adsense-id'] ) ) {
				$adsense_options['adsense-id'] = $ad_settings_post['output']['adsense-pub-id'];
				update_option( 'advanced-ads-adsense', $adsense_options );
			}
		}
		unset( $ad_settings_post['output']['adsense-pub-id'] );
		return $ad_settings_post;
	}

	/**
	 * Return ad type object.
	 *
	 * @return Advanced_Ads_Ad_Type_Adsense
	 */
	public function get_ad_type() {
		return new Advanced_Ads_Ad_Type_Adsense();
	}

	/**
	 * Return ad units loaded through the API.
	 *
	 * @return array
	 */
	public function get_external_ad_units() {
		$db         = Advanced_Ads_AdSense_Data::get_instance();
		$adsense_id = trim( $db->get_adsense_id() );

		$units        = [];
		$mapi_options = Advanced_Ads_AdSense_MAPI::get_option();

		if (
			isset( $mapi_options['ad_codes'] )
			 && isset( $mapi_options['accounts'] )
			 && isset( $mapi_options['accounts'][ $adsense_id ] )
			 && isset( $mapi_options['accounts'][ $adsense_id ]['ad_units'] )
		) {
			$ad_codes = $mapi_options['ad_codes'];
			foreach ( $mapi_options['accounts'][ $adsense_id ]['ad_units'] as $id => $raw ) {
				$ad_unit          = new Advanced_Ads_Ad_Network_Ad_Unit( $raw );
				$ad_unit->id      = $id;
				$ad_unit->slot_id = isset( $raw['code'] ) ? $raw['code'] : '-';
				$ad_unit->name    = isset( $raw['name'] ) ? $raw['name'] : '-';
				// phpcs:ignore
				$ad_unit->active  = isset( $raw['status'] ) && in_array( $raw['status'], self::$status_codes_active );

				if ( isset( $ad_codes[ $id ] ) ) {
					$ad_unit->code = $ad_codes[ $id ];
				}
				if ( isset( $raw['contentAdsSettings'] ) ) {
					if ( isset( $raw['contentAdsSettings']['type'] ) ) {
						$ad_unit->display_type = $raw['contentAdsSettings']['type'];
						$ad_unit->display_type = Advanced_Ads_AdSense_MAPI::format_ad_data( $ad_unit, 'type' );
					}
					if ( isset( $raw['contentAdsSettings']['size'] ) ) {
						$ad_unit->display_size = $raw['contentAdsSettings']['size'];
						$ad_unit->display_size = Advanced_Ads_AdSense_MAPI::format_ad_data( $ad_unit, 'size' );
					}
				}
				$units[] = $ad_unit;
			}
		}
		return $units;
	}

	/**
	 * Render the list of ads loaded through the API.
	 *
	 * @param bool $hide_idle_ads true to hide inactive ads.
	 * @param null $ad_unit_id ID of the ad unit.
	 *
	 * @return mixed|void
	 */
	public function print_external_ads_list( $hide_idle_ads = true, $ad_unit_id = null ) {
		Advanced_Ads_AdSense_Admin::get_mapi_ad_selector( $hide_idle_ads );
	}

	/**
	 * Whether the loaded AdSense ad is supported through the API.
	 * at the time we wrote this, native ad formats like In-article, In-feed and Matched Content are not supported.
	 *
	 * @param object $ad_unit ad unit object.
	 *
	 * @return bool
	 */
	public function is_supported( $ad_unit ) {
		$mapi_options = Advanced_Ads_AdSense_MAPI::get_option();
		$supported    = ! array_key_exists( $ad_unit->id, $mapi_options['unsupported_units'] );
		if ( ! $supported ) {
			$supported = array_key_exists( $ad_unit->id, $mapi_options['ad_codes'] );
		}

		return $supported;
	}

	/**
	 * Update the list of external ad units.
	 */
	public function update_external_ad_units() {
		Advanced_Ads_AdSense_MAPI::get_instance()->ajax_get_adUnits();
	}

	/**
	 * If the AdSense account is connected.
	 *
	 * @return bool
	 */
	public function is_account_connected() {
		return Advanced_Ads_AdSense_Data::get_instance()->is_setup();
	}

	/**
	 * Return path to module-specific JavaScript.
	 *
	 * @return string
	 */
	public function get_javascript_base_path() {
		return GADSENSE_BASE_URL . 'admin/assets/js/adsense.js';
	}

	/**
	 * JavaScript data to print in the source code.
	 *
	 * @inheritDoc
	 *
	 * @param array $data data to be printed.
	 * @return array
	 */
	public function append_javascript_data( &$data ) {
		$pub_id            = Advanced_Ads_AdSense_Data::get_instance()->get_adsense_id();
		$data['pubId']     = $pub_id;
		$data['connected'] = $this->is_account_connected();
		$data['ad_types']  = [
			'matched_content' => _x( 'Multiplex', 'AdSense ad type', 'advanced-ads' ),
			'in_article'      => _x( 'In-article', 'AdSense ad type', 'advanced-ads' ),
			'in_feed'         => _x( 'In-feed', 'AdSense ad type', 'advanced-ads' ),
			'display'         => _x( 'Display', 'AdSense ad type', 'advanced-ads' ),
			'link'            => _x( 'Link', 'AdSense ad type', 'advanced-ads' ),
		];

		return $data;
	}

	/**
	 * If the ad also has a manual ad setup option.
	 *
	 * @return bool
	 */
	public function supports_manual_ad_setup() {
		return true;
	}

	/**
	 * Get the ad unit associated with a given ad ID.
	 *
	 * @param int $ad_id The ID of the ad.
	 * @return object|null The ad unit object associated with the given ad ID, or null if not found.
	 */
	function get_ad_unit( $ad_id ){
		$ad_repository = new \Advanced_Ads\Ad_Repository();
		$adense_ad = $ad_repository->get( $ad_id );
		// Early bail!!
		if ( ! $adense_ad || 'adsense' !== ( $adense_ad->type ?? '' ) || ! isset( $adense_ad->content ) ) {
			return null;
		}

		$ad_units = $this->get_external_ad_units();
		if ( empty( $ad_units ) ) {
			return null;
		}

		$json_content = json_decode( $adense_ad->content );
		$unit_code = $json_content->slotId ?? null;

		foreach( $ad_units as $ad_unit ) {
			if( $ad_unit->slot_id === $unit_code){
				return $ad_unit;
			}
		}

		return null;
	}
}
