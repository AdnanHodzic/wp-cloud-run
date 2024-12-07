<?php
// phpcs:ignoreFile

use AdvancedAds\Entities;
use AdvancedAds\Utilities\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class Advanced_Ads_Admin_Meta_Boxes
 */
class Advanced_Ads_Admin_Meta_Boxes {
	/**
	 * Instance of this class.
	 *
	 * @var      object $instance
	 */
	protected static $instance = null;

	/**
	 * Meta box ids
	 *
	 * @var     array $meta_box_ids
	 */
	protected $meta_box_ids = [];

	/**
	 * Advanced_Ads_Admin_Meta_Boxes constructor.
	 */
	private function __construct() {
		add_action( 'add_meta_boxes_' . Entities::POST_TYPE_AD, [ $this, 'add_meta_boxes' ] );
		// add meta box for post types edit pages.
		add_action( 'add_meta_boxes', [ $this, 'add_post_meta_box' ] );
		add_action( 'save_post', [ $this, 'save_post_meta_box' ] );
		// register dashboard widget.
		add_action( 'wp_dashboard_setup', [ $this, 'add_dashboard_widget' ] );
		add_action( 'wp_dashboard_setup', [ $this, 'add_adsense_widget' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'adsense_widget_js' ] );
		// fixes compatibility issue with WP QUADS PRO.
		add_action( 'quads_meta_box_post_types', [ $this, 'fix_wpquadspro_issue' ], 11 );
	}

	public function add_adsense_widget() {
		if ( Advanced_Ads_AdSense_Data::get_instance()->is_setup()
			&& ! Advanced_Ads_AdSense_Data::get_instance()->is_hide_stats()
			&& isset ( Advanced_Ads::get_instance()->get_adsense_options()['adsense-wp-widget'] ) ) {
			wp_add_dashboard_widget(
				'custom_dashboard_widget',
				__( 'AdSense Earnings', 'advanced-ads' ),
				[ $this, 'adsense_widget_content' ],
				null,
				null,
				'side'
			);
		}
	}

	public function adsense_widget_content() {
       Advanced_Ads_Overview_Widgets_Callbacks::add_meta_box(
				'advads_overview_adsense_stats',
				'',
				'full',
				'render_adsense_stats'
			);
    }

	public function adsense_widget_js() {
		global $pagenow;

		if ( 'index.php' === $pagenow ) {
			wp_advads()->registry->enqueue_script( 'wp-widget-adsense' );
		}
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
	 * Add meta boxes
	 *
	 * @since    1.0.0
	 */
	public function add_meta_boxes() {
		global $post;
		$post_type = Entities::POST_TYPE_AD;

		add_meta_box(
			'ad-main-box',
			__( 'Ad Type', 'advanced-ads' ),
			[ $this, 'markup_meta_boxes' ],
			$post_type,
			'normal',
			'high'
		);
		if (
			$post->ID
			&& Advanced_Ads_AdSense_Data::get_instance()->is_setup()
			&& ! Advanced_Ads_AdSense_Data::get_instance()->is_hide_stats()
		) {
			$ad_unit = Advanced_Ads_Network_Adsense::get_instance()->get_ad_unit( $post->ID );

			if ( $ad_unit ) {
				add_meta_box(
					'advads-gadsense-box',
					sprintf(
						/* translators: 1: Name of ad unit */
						esc_html__( 'Earnings of  %1$s', 'advanced-ads' ),
						esc_html( $ad_unit->name )
					),
					[ $this, 'markup_meta_boxes' ],
					$post_type,
					'normal',
					'high'
				);
			}
		}

		// use dynamic filter from to add close class to ad type meta box after saved first time.
		add_filter( 'postbox_classes_advanced_ads_ad-main-box', [ $this, 'close_ad_type_metabox' ] );

		// show the Usage box for saved ads
		if ( $post->filter === 'edit' ) {
			add_meta_box(
				'ad-usage-box',
				__( 'Usage', 'advanced-ads' ),
				[ $this, 'markup_meta_boxes' ],
				$post_type,
				'normal',
				'high'
			);
		}
		add_meta_box(
			'ad-parameters-box',
			__( 'Ad Parameters', 'advanced-ads' ),
			[ $this, 'markup_meta_boxes' ],
			$post_type,
			'normal',
			'high'
		);
		add_meta_box(
			'ad-output-box',
			__( 'Layout / Output', 'advanced-ads' ),
			[ $this, 'markup_meta_boxes' ],
			$post_type,
			'normal',
			'high'
		);
		add_meta_box(
			'ad-targeting-box',
			__( 'Targeting', 'advanced-ads' ),
			[ $this, 'markup_meta_boxes' ],
			$post_type,
			'normal',
			'default'
		);
		if ( ! defined( 'AAP_VERSION' ) ) {
			add_meta_box(
				'advads-pro-pitch',
				__( 'Increase your ad revenue', 'advanced-ads' ),
				[ $this, 'markup_meta_boxes' ],
				$post_type,
				'side',
				'low'
			);
		}
		if ( ! defined( 'AAT_VERSION' ) ) {
			add_meta_box(
				'advads-tracking-pitch',
				__( 'Statistics', 'advanced-ads' ),
				[ $this, 'markup_meta_boxes' ],
				$post_type,
				'normal',
				'low'
			);
		}

		// register meta box ids.
		$this->meta_box_ids = [
			'ad-main-box',
			'advads-gadsense-box',
			'ad-parameters-box',
			'ad-output-box',
			'ad-targeting-box',
			'advads-pro-pitch',
			'advads-tracking-pitch',
			'revisionsdiv', // revisions – only when activated.
			'advanced_ads_groupsdiv', // automatically added by ad groups taxonomy.
		];

		// force AA meta boxes to never be completely hidden by screen options.
		add_filter( 'hidden_meta_boxes', [ $this, 'unhide_meta_boxes' ], 10, 2 );
		// hide the checkboxes for "unhideable" meta boxes within screen options via CSS.
		add_action( 'admin_head', [ $this, 'unhide_meta_boxes_style' ] );

		$whitelist = apply_filters(
			'advanced-ads-ad-edit-allowed-metaboxes',
			array_merge(
				$this->meta_box_ids,
				[ // meta boxes in this array can be hidden using Screen Option
					'submitdiv',
					'slugdiv',
					'ad-usage-box',
					'authordiv',
					'tracking-ads-box',
					'ad-layer-ads-box', // deprecated.
				]
			)
		);

		global $wp_meta_boxes;
		// remove non-white-listed meta boxes.
		foreach ( [ 'normal', 'advanced', 'side' ] as $context ) {
			if ( isset( $wp_meta_boxes[ $post_type ][ $context ] ) ) {
				foreach ( [ 'high', 'sorted', 'core', 'default', 'low' ] as $priority ) {
					if ( isset( $wp_meta_boxes[ $post_type ][ $context ][ $priority ] ) ) {
						foreach ( (array) $wp_meta_boxes[ $post_type ][ $context ][ $priority ] as $id => $box ) {
							if ( ! in_array( $id, $whitelist ) ) {
								unset( $wp_meta_boxes[ $post_type ][ $context ][ $priority ][ $id ] );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Load templates for all meta boxes
	 *
	 * @param WP_Post $post WP_Post object.
	 * @param array   $box  meta box information.
	 * @todo move ad initialization to main function and just global it
	 */
	public function markup_meta_boxes( $post, $box ) {
		$ad = \Advanced_Ads\Ad_Repository::get( $post->ID );

		switch ( $box['id'] ) {
			case 'ad-main-box':
				$view       = 'ad-main-metabox.php';
				$hndlelinks = '<a href="https://wpadvancedads.com/manual/ad-types?utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-ad-type" target="_blank" class="advads-manual-link">' . __( 'Manual', 'advanced-ads' ) . '</a>';
				break;
			case 'ad-usage-box':
				$view = 'ad-usage-metabox.php';
				break;
			case 'ad-parameters-box':
				$view = 'ad-parameters-metabox.php';
				break;
			case 'ad-output-box':
				$positioning        = ( new Advanced_Ads_Ad_Positioning( $ad ) )->return_admin_view();
				$wrapper_id         = $ad->options( 'output.wrapper-id', '' );
				$wrapper_class      = $ad->options( 'output.wrapper-class', '' );
				$debug_mode_enabled = (bool) $ad->options( 'output.debugmode', false );
				$view               = 'ad-output-metabox.php';
				$hndlelinks         = '<a href="https://wpadvancedads.com/manual/optimizing-the-ad-layout/?utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-ad-layout" target="_blank" class="advads-manual-link">' . __( 'Manual', 'advanced-ads' ) . '</a>';
				break;
			case 'ad-targeting-box':
				$view                         = 'conditions/ad-targeting-metabox.php';
				$hndlelinks                   = '<a href="#" class="advads-video-link">' . __( 'Video', 'advanced-ads' ) . '</a>';
				$hndlelinks                   .= '<a href="https://wpadvancedads.com/manual/display-conditions/?utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-display" target="_blank" class="advads-manual-link">' . __( 'Display Conditions', 'advanced-ads' ) . '</a>';
				$hndlelinks                   .= '<a href="https://wpadvancedads.com/manual/visitor-conditions/?utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-visitor" target="_blank" class="advads-manual-link">' . __( 'Visitor Conditions', 'advanced-ads' ) . '</a>';
				$videomarkup                  = '<iframe width="420" height="315" src="https://www.youtube-nocookie.com/embed/VjfrRl5Qn4I?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>';
				$ad_options                   = $ad->options();
				$display_conditions           = ! empty( $ad_options['conditions'] ) && is_array( $ad_options['conditions'] ) ? $ad_options['conditions'] : []; // default value for older version is `""` (empty string)
				$visitor_conditions           = ! empty( $ad_options['visitors'] ) && is_array( $ad_options['visitors'] ) ? $ad_options['visitors'] : []; // default value for older version is `""` (empty string)
				$display_conditions_available = ( empty( $display_conditions ) );
				$visitor_conditions_available = ( empty( $visitor_conditions ) );
				break;
			case 'advads-pro-pitch':
				$view = 'upgrades/all-access.php';
				break;
			case 'advads-tracking-pitch':
				$view = 'upgrades/tracking.php';
				break;
			case 'advads-gadsense-box':
				$unit_code = null;
				if ( $ad && isset( $ad->type ) && 'adsense' === $ad->type ) {
					if ( isset( $ad->content ) ) {
						$json_content = json_decode( $ad->content );
						// phpcs:ignore
						if ( isset( $json_content->slotId ) ) {
							// phpcs:ignore
							$unit_code = $json_content->slotId;
						}
					}
				}

				$report_type                       = 'unit';
				$report_filter                     = $unit_code;
				$advads_gadsense_options['hidden'] = ! $unit_code;
				$view                              = 'gadsense-dashboard.php';
				$pub_id                            = Advanced_Ads_AdSense_Data::get_instance()->get_adsense_id();
				$hndlelinks                        = '<a href="' . esc_url( admin_url( 'admin.php?page=advanced-ads-settings#top#adsense' ) ) . '" target="_blank">' . __( 'Disable', 'advanced-ads' ) . '</a>';
				break;
		}

		if ( ! isset( $view ) ) {
			return;
		}
		// markup moved to handle headline of the metabox.
		if ( isset( $hndlelinks ) ) {
			?><span class="advads-hndlelinks hidden">
			<?php
			echo wp_kses(
				$hndlelinks,
				[
					'a' => [
						'target' => [],
						'href'   => [],
						'class'  => [],
					],
				]
			);
			?>
														</span>
			<?php
		}
		// show video markup.
		if ( isset( $videomarkup ) ) {
			echo '<div class="advads-video-link-container" data-videolink=\'' . wp_kses(
				$videomarkup,
				[
					'iframe' => [
						'width'           => [],
						'height'          => [],
						'src'             => [],
						'frameborder'     => [],
						'allowfullscreen' => [],
					],
				]
			) . '\'></div>';
		}
		/**
		 *  List general notices
		 *  elements in $warnings contain [text] and [class] attributes.
		 */
		$warnings = [];
		// show warning if ad contains https in parameters box.
		$https_message = Advanced_Ads_Ad_Debug::is_https_and_http( $ad );
		if ( 'ad-parameters-box' === $box['id'] && $https_message ) {
			$warnings[] = [
				'text'  => $https_message,
				'class' => 'advads-ad-notice-https-missing advads-notice-inline advads-error',
			];
		}

		if ( 'ad-parameters-box' === $box['id'] ) {
			$warnings[] = [
				'text'  => Advanced_Ads_AdSense_Admin::get_auto_ads_messages()[ Advanced_Ads_AdSense_Data::get_instance()->is_page_level_enabled() ? 'enabled' : 'disabled' ],
				'class' => 'advads-auto-ad-in-ad-content hidden advads-notice-inline advads-error',
			];
		}

		// Let users know that they could use the Google AdSense ad type when they enter an AdSense code.
		if ( 'ad-parameters-box' === $box['id'] && Advanced_Ads_Ad_Type_Adsense::content_is_adsense( $ad->content ) && in_array( $ad->type, [ 'plain', 'content' ], true ) ) {
			if (
				false === strpos( $ad->content, 'enable_page_level_ads' )
				&& ! preg_match( '/script[^>]+data-ad-client=/', $ad->content )
			) {
				$adsense_auto_ads = Advanced_Ads_AdSense_Data::get_instance()->is_page_level_enabled();
				$warnings[]       = [
					'class' => 'advads-adsense-found-in-content advads-notice-inline advads-error',
					'text'  => sprintf(
						// translators: %1$s opening button tag, %2$s closing button tag.
						esc_html__( 'This looks like an AdSense ad. Switch the ad type to “AdSense ad” to make use of more features. %1$sSwitch to AdSense ad%2$s.', 'advanced' ),
						'<button class="button-secondary" id="switch-to-adsense-type">',
						'</button>'
					),
				];
			}
		}

		$warnings = apply_filters( 'advanced-ads-ad-notices', $warnings, $box, $post );
		echo '<ul id="' . esc_attr( $box['id'] ) . '-notices" class="advads-metabox-notices">';
		foreach ( $warnings as $_warning ) {
			if ( isset( $_warning['text'] ) ) :
				$warning_class = isset( $_warning['class'] ) ? $_warning['class'] : '';
				echo '<li class="' . esc_attr( $warning_class ) . '">';
				// skip CodeSniffer because this could be complex HTML.
				// phpcs:ignore
				echo $_warning['text'];
				echo '</li>';
			endif;
		}
		echo '</ul>';
		include ADVADS_ABSPATH . 'admin/views/' . $view;
	}

	/**
	 * Force all AA related meta boxes to stay visible
	 *
	 * @param array     $hidden       An array of hidden meta boxes.
	 * @param WP_Screen $screen       WP_Screen object of the current screen.
	 *
	 * @return array
	 */
	public function unhide_meta_boxes( $hidden, $screen ) {
		// only check on Advanced Ads edit screen.
		if ( ! isset( $screen->id ) || 'advanced_ads' !== $screen->id || ! is_array( $this->meta_box_ids ) ) {
			return $hidden;
		}

		// return only hidden elements which are not among the Advanced Ads meta box ids.
		return array_diff( $hidden, (array) apply_filters( 'advanced-ads-unhide-meta-boxes', $this->meta_box_ids ) );
	}

	/**
	 * Add dynamic CSS for un-hideable meta boxes.
	 */
	public function unhide_meta_boxes_style() {
		$screen = get_current_screen();
		if ( empty( $screen ) || ! isset( $screen->id ) || 'advanced_ads' !== $screen->id ) {
			return;
		}

		$meta_boxes = (array) apply_filters( 'advanced-ads-unhide-meta-boxes', $this->meta_box_ids );
		if ( empty( $meta_boxes ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- we don't need to escape the string we just concatenated.
		printf( '<style>%s {display: none;}</style>', implode( ', ', array_reduce( $meta_boxes, function( $styles, $box_id ) {
			$styles[] = sprintf( 'label[for="%s-hide"]', $box_id );

			return $styles;
		}, [] ) ) );
	}

	/**
	 * Add a meta box to post type edit screens with ad settings
	 *
	 * @param string $post_type current post type.
	 */
	public function add_post_meta_box( $post_type = '' ) {
		// don’t display for non admins.
		if ( ! WordPress::user_can( 'advanced_ads_edit_ads' ) ) {
			return;
		}

		// get public post types.
		$public_post_types = get_post_types(
			[
				'public'             => true,
				'publicly_queryable' => true,
			],
			'names',
			'or'
		);

		// limit meta box to public post types.
		if ( in_array( $post_type, $public_post_types ) ) {
			$disabled_post_types = Advanced_Ads::get_instance()->options()['pro']['general']['disable-by-post-types'] ?? [];
			add_meta_box(
				'advads-ad-settings',
				__( 'Ad Settings', 'advanced-ads' ),
				[ $this, in_array( $post_type, $disabled_post_types, true ) ? 'render_disable_post_type_notice' : 'render_post_meta_box' ],
				$post_type,
				'side',
				'low'
			);
		}
	}

	/**
	 * Render meta box for ad settings on a per post basis
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_post_meta_box( $post ) {

		// nonce field to check when we save the values.
		wp_nonce_field( 'advads_post_meta_box', 'advads_post_meta_box_nonce' );

		// retrieve an existing value from the database.
		$values = get_post_meta( $post->ID, '_advads_ad_settings', true );

		// load the view.
		include ADVADS_ABSPATH . 'admin/views/post-ad-settings-metabox.php';

		do_action( 'advanced_ads_render_post_meta_box', $post, $values );
	}

	/**
	 * Save the ad meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 *
	 * @return mixed empty or post ID.
	 */
	public function save_post_meta_box( $post_id ) {

		if ( ! WordPress::user_can( 'advanced_ads_edit_ads' ) ) {
			return;
		}

		// check nonce.
		if ( ! isset( $_POST['advads_post_meta_box_nonce'] ) ) {
			return $post_id; }

		$nonce = $_POST['advads_post_meta_box_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'advads_post_meta_box' ) ) {
			return $post_id; }

		// don’t save on autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id; }

		// check the user's permissions.
		if ( 'page' === $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id; }
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id; }
		}

		// sanitize the user input.
		$_data['disable_ads'] = isset( $_POST['advanced_ads']['disable_ads'] ) ? absint( $_POST['advanced_ads']['disable_ads'] ) : 0;

		$_data = apply_filters( 'advanced_ads_save_post_meta_box', $_data );

		// update the meta field.
		update_post_meta( $post_id, '_advads_ad_settings', $_data );
	}

	/**
	 * Add "close" class to collapse the ad-type metabox after ad was saved first
	 *
	 * @param array $classes class attributes.
	 * @return array $classes
	 */
	public function close_ad_type_metabox( $classes = [] ) {
		global $post;
		if ( isset( $post->ID ) && 'publish' === $post->post_status ) {
			if ( ! in_array( 'closed', $classes, true ) ) {
				$classes[] = 'closed';
			}
		} else {
			$classes = [];
		}
		return $classes;
	}

	/**
	 * Add dashboard widget with ad stats and additional information
	 */
	public function add_dashboard_widget() {
		// display dashboard widget only to authors and higher roles.
		if ( ! WordPress::user_can( 'advanced_ads_see_interface' ) ) {
				return;
		}
		add_meta_box( 'advads_dashboard_widget', __( 'Advanced Ads', 'advanced-ads' ), [ $this, 'dashboard_widget_function' ], 'dashboard', 'side', 'high' );
	}

	/**
	 * Display widget functions
	 *
	 * @param WP_Post $post post object.
	 * @param array   $callback_args callback arguments.
	 */
	public static function dashboard_widget_function( $post, $callback_args ) {
		// get number of ads.
		$ads_count = Advanced_Ads::get_number_of_ads();
		if ( WordPress::user_can( 'advanced_ads_edit_ads' ) ) {
			echo '<p>';
			printf(
				// translators: %1$d is the number of ads, %2$s and %3$s are URLs.
				wp_kses( __( '%1$d ads – <a href="%2$s">manage</a> - <a href="%3$s">new</a>', 'advanced-ads' ), [ 'a' => [ 'href' => [] ] ] ),
				absint( $ads_count ),
				'edit.php?post_type=' . esc_attr( Entities::POST_TYPE_AD ),
				'post-new.php?post_type=' . esc_attr( Entities::POST_TYPE_AD )
			);
			echo '</p>';
		}

		$notice_options = Advanced_Ads_Admin_Notices::get_instance()->options();
		$_notice        = 'nl_first_steps';
		if ( ! isset( $notice_options['closed'][ $_notice ] ) ) {
			?>
			<div class="advads-admin-notice">
				<p><button type="button" class="button-primary advads-notices-button-subscribe" data-notice="<?php echo esc_attr( $_notice ); ?>"><?php esc_html_e( 'Get the tutorial via email', 'advanced-ads' ); ?></button></p>
			</div>
			<?php
		}

		$_notice = 'nl_adsense';
		if ( ! isset( $notice_options['closed'][ $_notice ] ) ) {
			?>
			<div class="advads-admin-notice">
				<p><button type="button" class="button-primary advads-notices-button-subscribe" data-notice="<?php echo esc_attr( $_notice ); ?>"><?php esc_html_e( 'Get AdSense tips via email', 'advanced-ads' ); ?></button></p>
			</div>
			<?php
		}

		// RSS feed.
		self::dashboard_cached_rss_widget();

		?>
		<?php

		// add markup for utm variables.
		// todo: move to js file.
		?>
		<script>jQuery('#advads_dashboard_widget .rss-widget a').each(function(){ this.href = this.href + '?utm_source=advanced-ads&utm_medium=rss-link&utm_campaign=dashboard'; })</script>
		<?php
	}

	/**
	 * Checks to see if there are feed urls in transient cache; if not, load them
	 * built using a lot of https://developer.wordpress.org/reference/functions/wp_dashboard_cached_rss_widget/
	 *
	 * @return bool False on failure. True on success.
	 */
	public static function dashboard_cached_rss_widget() {

		$cache_key = 'dash_' . md5( 'advads_dashboard_widget' );

		$output = get_transient( $cache_key );
		if ( false !== ( $output ) ) {
			// phpcs:ignore
			echo $output; // complex HTML widget.
			return true;
		}

		/**
		 * Only display dummy output which then loads the content via AJAX
		 */
		?>
		<div id="advads-dashboard-widget-placeholder">
			<img src="<?php echo esc_url( admin_url( 'images/spinner.gif' ) ); ?>" width="20" height="20" alt="spinner"/>
			<script>window.addEventListener( 'load', function() { advads_load_dashboard_rss_widget_content() } );</script>
		</div>
		<?php

		return true;
	}

	/**
	 * Create the rss output of the widget
	 */
	public static function dashboard_widget_function_output() {
		$adsense_num = 0;
		$currency    = '';
		$adsense_obj = Advanced_Ads_AdSense_Report_Data::get_data_from_options( 'domain' );

		if ( $adsense_obj->get_currency() !== '' ) {
			$adsense_num = $adsense_obj->get_sums()['28days'] ?? 0;
			$currency    = $adsense_obj->get_currency() ?? '';
		}

		$currencies  = [ 'EUR', 'USD', 'GBP', 'CHF' ];
		$feed_url    = 'https://wpadvancedads.com/category/tutorials/feed/';
		$campaign    = 'dashboard';
		if ( $adsense_num > 1000 && in_array( $currency, $currencies ) ) {
			$feed_url = 'https://wpadvancedads.com/category/adsense-motors/feed/';
			$campaign = 'dashboard-adsense-motors';
		} elseif ( $adsense_num > 100 && in_array( $currency, $currencies ) ) {
			$feed_url = 'https://wpadvancedads.com/category/adsense/feed/';
			$campaign = 'dashboard-adsense';
		}
		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );

		$cache_key = 'dash_' . md5( 'advads_dashboard_widget' );

		$feeds = [
			[
				'link'         => 'https://wpadvancedads.com/',
				'url'          => $feed_url,
				'title'        => sprintf(
				// translators: %s is our URL.
					__( 'Latest posts on wpadvancedads.com', 'advanced-ads' ),
					'https://wpadvancedads.com/'
				),
				'items'        => 2,
				'show_summary' => 1,
				'show_author'  => 0,
				'show_date'    => 0,
			],
		];

		// create output and also cache it.

		ob_start();
		foreach ( $feeds as $_feed ) {
			echo '<div class="rss-widget">';
			echo '<h4>' . esc_html( $_feed['title'] ) . '</h4>';
			wp_widget_rss_output( $_feed['url'], $_feed );
			echo '</div>';
			echo '<p><a href="https://wpadvancedads.com/category/tutorials/?utm_source=advanced-ads&utm_medium=link&utm_campaign=' . $campaign . '" target="_blank">' . esc_html( 'Visit our blog for more articles about ad optimization', 'advanced-ads' ) . '</a></p>';
		}

		$feed_content = ob_get_clean();
		$error_string = '<strong>' . __( 'RSS Error:' ) . '</strong> ';

		// empty the widget content, if we find the error string in it.
		if ( strpos( $feed_content, $error_string ) ) {
			$feed_content = '';
		}

		// phpcs:ignore
		echo $feed_content;

		set_transient( $cache_key, $feed_content, 48 * HOUR_IN_SECONDS ); // Default lifetime in cache of 48 hours.
		die();
	}

	/**
	 * Fixes a WP QUADS PRO compatibility issue
	 * they inject their ad optimization meta box into our ad page, even though it is not a public post type
	 * using they filter, we remove AA from the list of post types they inject this box into
	 *
	 * @param array $allowed_post_types array of allowed post types.
	 * @return array
	 */
	public function fix_wpquadspro_issue( $allowed_post_types ) {
		unset( $allowed_post_types['advanced_ads'] );
		return $allowed_post_types;
	}

	/**
	 * Render meta box for ad settings notice when ads disabled for post type
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_disable_post_type_notice( $post ) {
		$labels = get_post_type_object( $post->post_type )->labels;
		// load the view.
		include ADVADS_ABSPATH . 'admin/views/post-ad-settings-hint-metabox.php';
	}

}
