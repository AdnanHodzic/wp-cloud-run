<?php // phpcs:ignoreFile

use AdvancedAds\Entities;
use AdvancedAds\Utilities\Conditional;

/**
 * Class Advanced_Ads_AdSense_Admin
 */
class Advanced_Ads_AdSense_Admin {

	/**
	 * AdSense options.
	 *
	 * @var Advanced_Ads_AdSense_Data
	 */
	private $data;

	/**
	 * Noncetodo: check if this is still used
	 * todo: check if this is still used
	 *
	 * @var string $nonce
	 */
	private $nonce;

	/**
	 * Instance of Advanced_Ads_AdSense_Admin
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Notices
	 * todo: still used?
	 *
	 * @var null
	 */
	protected $notice = null;

	/**
	 * Settings page hook
	 *
	 * @var string
	 */
	private $settings_page_hook = 'advanced-ads-adsense-settings-page';

	const   ADSENSE_NEW_ACCOUNT_LINK = 'https://www.google.com/adsense/start/?utm_source=AdvancedAdsPlugIn&utm_medium=partnerships&utm_campaign=AdvancedAdsPartner1';

	/**
	 * Advanced_Ads_AdSense_Admin constructor.
	 */
	private function __construct() {
		$this->data = Advanced_Ads_AdSense_Data::get_instance();

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'admin_print_scripts', [ $this, 'print_scripts' ] );
		add_filter( 'advanced-ads-ad-notices', [ $this, 'ad_notices' ], 10, 3 );
		add_filter( 'advanced-ads-ad-settings-pre-save', [ $this, 'pre_save_post' ] );
	}

	/**
	 * Edit $_POST['advanced_ad'] before saving
	 *
	 * @param array $advanced_ad content of $_POST['advanced_ad'].
	 *
	 * @return array
	 */
	public function pre_save_post( $advanced_ad ) {
		if ( $advanced_ad['type'] !== 'adsense' ) {
			return $advanced_ad;
		}

		// Remove ad size options for responsive AdSense ads.
		$content = json_decode( str_replace( "\n", '', wp_unslash( $advanced_ad['content'] ) ), true );
		if ( in_array( $content['unitType'], [
			'responsive',
			'link',
			'link-responsive',
			'matched-content',
			'in-article',
			'in-feed',
		], true )
		) {
			$advanced_ad['width']  = '';
			$advanced_ad['height'] = '';
		}

		return $advanced_ad;
	}

	/**
	 * Load JavaScript needed on some pages.
	 */
	public function print_scripts() {
		global $pagenow, $post_type;
		if (
				( 'post-new.php' === $pagenow && Entities::POST_TYPE_AD === $post_type ) ||
				( 'post.php' === $pagenow && Entities::POST_TYPE_AD === $post_type && isset( $_GET['action'] ) && 'edit' === $_GET['action'] )
		) {
			$db     = Advanced_Ads_AdSense_Data::get_instance();
			$pub_id = $db->get_adsense_id();
			?>
			<script type="text/javascript">
				if ( 'undefined' == typeof gadsenseData ) {
					window.gadsenseData = {};
				}
				// todo: check why we are using echo here.
				gadsenseData['pagenow'] = '<?php echo esc_attr( $pagenow ); ?>';
			</script>
			<?php
		}
	}

	/**
	 * Add AdSense-related scripts.
	 */
	public function enqueue_scripts() {
		global $gadsense_globals, $pagenow, $post_type;
		$screen = get_current_screen();
		$plugin = Advanced_Ads_Admin::get_instance();

		if ( Conditional::is_screen_advanced_ads() ) {
			self::enqueue_connect_adsense();
		}
		if (
				( 'post-new.php' === $pagenow && Entities::POST_TYPE_AD === $post_type ) ||
				( 'post.php' === $pagenow && Entities::POST_TYPE_AD === $post_type && isset( $_GET['action'] ) && 'edit' === $_GET['action'] )
		) {
			$scripts = [];

			// Allow modifications of script files to enqueue.
			$scripts = apply_filters( 'advanced-ads-gadsense-ad-param-script', $scripts );

			foreach ( $scripts as $handle => $value ) {
				if ( empty( $handle ) ) {
					continue;
				}
				if ( ! empty( $handle ) && empty( $value ) ) {
					// Allow inclusion of WordPress's built-in script like jQuery.
					wp_enqueue_script( $handle );
				} else {
					if ( ! isset( $value['version'] ) ) {
						$value['version'] = null; }
					wp_enqueue_script( $handle, $value['path'], $value['dep'], $value['version'] );
				}
			}

			$styles = [];

			// Allow modifications of default style files to enqueue.
			$styles = apply_filters( 'advanced-ads-gadsense-ad-param-style', $styles );

			foreach ( $styles as $handle => $value ) {
				if ( ! isset( $value['path'] ) ||
						! isset( $value['dep'] ) ||
						empty( $handle )
				) {
					continue;
				}
				if ( ! isset( $value['version'] ) ) {
					$value['version'] = null; }
				wp_enqueue_style( $handle, $value['path'], $value['dep'], $value['version'] );
			}
		}
	}

	/**
	 * Get instance of Advanced_Ads_AdSense_Admin.
	 *
	 * @return Advanced_Ads_AdSense_Admin|null
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Show AdSense ad specific notices in parameters box
	 *
	 * @param array   $notices some notices to show in the parameters box.
	 * @param string  $box ID of the meta box.
	 * @param WP_Post $post post object.
	 */
	public function ad_notices( $notices, $box, $post ) {

		$ad = \Advanced_Ads\Ad_Repository::get( $post->ID );

		// $content = json_decode( stripslashes( $ad->content ) );

		switch ( $box['id'] ) {
			case 'ad-parameters-box':
				// Add warning if this is a responsive ad unit without custom sizes and position is set to left or right.
				// Hidden by default and made visible with JS.
				$notices[] = [
					'text'  => sprintf(
							// translators: %s is a URL.
						__( 'Responsive AdSense ads don’t work reliably with <em>Position</em> set to left or right. Either switch the <em>Type</em> to "normal" or follow <a href="%s" target="_blank">this tutorial</a> if you want the ad to be wrapped in text.', 'advanced-ads' ),
						'https://wpadvancedads.com/adsense-responsive-custom-sizes/?utm_source=advanced-ads&utm_medium=link&utm_campaign=adsense-custom-sizes-tutorial'
					),
					'class' => 'advads-ad-notice-responsive-position advads-notice-inline advads-error hidden',
				];
				// Show hint about AdSense In-feed add-on.
				if ( ! class_exists( 'Advanced_Ads_In_Feed', false ) && ! class_exists( 'Advanced_Ads_Pro_Admin', false ) ) {
					$notices[] = [
						'text'  => sprintf(
								// translators: %s is a URL.
							__( '<a href="%s" target="_blank">Install the free AdSense In-feed add-on</a> in order to place ads between posts.', 'advanced-ads' ),
							wp_nonce_url(
								self_admin_url( 'update.php?action=install-plugin&plugin=advanced-ads-adsense-in-feed' ),
								'install-plugin_advanced-ads-adsense-in-feed'
							)
						),
						'class' => 'advads-ad-notice-in-feed-add-on advads-notice-inline advads-idea hidden',
					];
				}
				break;
		}

		return $notices;
	}

	/**
	 * Enqueue AdSense connection script.
	 */
	public static function enqueue_connect_adsense() {
		if ( ! wp_script_is( 'advads/connect-adsense', 'registered' ) ) {
			wp_enqueue_script( 'advads/connect-adsense', GADSENSE_BASE_URL . 'admin/assets/js/connect-adsense.js', [ 'jquery' ], ADVADS_VERSION );
		}
		if ( ! has_action( 'admin_footer', [ 'Advanced_Ads_AdSense_Admin', 'print_connect_adsense' ] ) ) {
			add_action( 'admin_footer', [ 'Advanced_Ads_AdSense_Admin', 'print_connect_adsense' ] );
		}
	}

	/**
	 * Prints AdSense connection markup.
	 */
	public static function print_connect_adsense() {
		require_once GADSENSE_BASE_PATH . 'admin/views/connect-adsense.php';
	}

	/**
	 * Get Auto Ads messages.
	 */
	public static function get_auto_ads_messages() {
		return [
			'enabled'  => sprintf(
						  // translators: %s is a URL.
				__( 'The AdSense verification and Auto ads code is already activated in the <a href="%s">AdSense settings</a>.', 'advanced-ads' ),
				admin_url( 'admin.php?page=advanced-ads-settings#top#adsense' )
			)
			. ' ' . __( 'No need to add the code manually here, unless you want to include it into certain pages only.', 'advanced-ads' ),
			'disabled' => sprintf(
				'%s <button id="adsense_enable_pla" type="button" class="button">%s</button>',
				sprintf(
						// translators: %s is a URL.
					__( 'The AdSense verification and Auto ads code should be set up in the <a href="%s">AdSense settings</a>. Click on the following button to enable it now.', 'advanced-ads' ),
					admin_url( 'admin.php?page=advanced-ads-settings#top#adsense' )
				),
				esc_attr__( 'Activate', 'advanced-ads' )
			),
		];
	}

	/**
	 * Get the ad selector markup
	 *
	 * @param bool $hide_idle_ads Whether to hide idle ads.
	 */
	public static function get_mapi_ad_selector( $hide_idle_ads = true ) {
		global $closeable, $use_dashicons, $network, $ad_units, $display_slot_id;
		$closeable       = true;
		$use_dashicons   = false;
		$network         = Advanced_Ads_Network_Adsense::get_instance();
		$ad_units        = $network->get_external_ad_units();
		$display_slot_id = true;
		$pub_id          = Advanced_Ads_AdSense_Data::get_instance()->get_adsense_id();

		require_once GADSENSE_BASE_PATH . 'admin/views/external-ads-list.php';
	}
}
