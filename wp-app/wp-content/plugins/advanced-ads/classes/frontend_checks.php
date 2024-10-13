<?php
// phpcs:ignoreFile

use AdvancedAds\Utilities\WordPress;

/**
 * Class Advanced_Ads_Frontend_Checks
 *
 * Handle Ad Health and other notifications and checks in the frontend.
 */
class Advanced_Ads_Frontend_Checks {
	/**
	 * True if 'the_content' was invoked, false otherwise.
	 *
	 * @var bool
	 */
	private $did_the_content      = false;
	private $has_many_the_content = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Wait until other plugins (for example Elementor) have disabled admin bar using `show_admin_bar` filter.
		add_action( 'template_redirect', [ $this, 'init' ], 11 );

		if ( wp_doing_ajax() ) {
			add_filter( 'advanced-ads-ad-output', [ $this, 'after_ad_output' ], 10, 2 );
		}
	}

	/**
	 * Ad Health init.
	 */
	public function init() {
		if ( ! is_admin()
		&& is_admin_bar_showing()
		&& WordPress::user_can( 'advanced_ads_edit_ads' )
		&& Advanced_Ads_Ad_Health_Notices::notices_enabled()
		) {
			add_action( 'admin_bar_menu', [ $this, 'add_admin_bar_menu' ], 1000 );
			add_filter( 'the_content', [ $this, 'set_did_the_content' ] );
			add_action( 'wp_footer', [ $this, 'footer_checks' ], -101 );
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
			add_filter( 'advanced-ads-ad-select-args', [ $this, 'ad_select_args_callback' ] );
			add_filter( 'advanced-ads-ad-output', [ $this, 'after_ad_output' ], 10, 2 );
		}

		if ( Advanced_Ads_Ad_Health_Notices::notices_enabled() ) {
			add_action( 'body_class', [ $this, 'body_class' ] );
		}

		if( $this->has_adblocker_placements() ) {
			if ( ! wp_advads()->registry->is_script('find-adblocker', 'enqueued') ) {
				wp_advads()->registry->enqueue_script('find-adblocker');
			}
		}
	}

	/**
	 * Notify ads loaded with AJAX.
	 *
	 * @param array $args ad arguments.
	 * @return array $args
	 */
	public function ad_select_args_callback( $args ) {
		$args['frontend-check'] = true;
		return $args;
	}

	/**
	 * Enqueue scripts
	 * needs to add ajaxurl in case no other plugin is doing that
	 */
	public function enqueue_scripts() {
		if ( advads_is_amp() ) {
			return;
		}

		// we don’t have our own script, so we attach this information to jquery.
		wp_localize_script( 'jquery', 'advads_frontend_checks', [ 'ajax_url' => admin_url( 'admin-ajax.php' ) ] );
	}

	/**
	 * List current ad situation on the page in the admin-bar.
	 *
	 * @param object $wp_admin_bar WP_Admin_Bar.
	 */
	public function add_admin_bar_menu( $wp_admin_bar ) {
		global $wp_the_query, $post, $wp_scripts;

		$options = Advanced_Ads_Plugin::get_instance()->options();

		// load AdSense related options.
		$adsense_options = Advanced_Ads_AdSense_Data::get_instance()->get_options();

		// check if jQuery is loaded in the header
		// Hidden, will be shown using js.
		// message removed after we fixed all issues we know of.

		/*
			$wp_admin_bar->add_node( array(
			'parent' => 'advanced_ads_ad_health',
			'id'    => 'advanced_ads_ad_health_jquery',
			'title' => __( 'jQuery not in header', 'advanced-ads' ),
			'href'  => ADVADS_URL . 'manual/common-issues#frontend-issues-javascript',
			'meta'   => array(
				'class' => 'hidden advanced_ads_ad_health_warning',
				'target' => '_blank'
			)
		) );
		*/

		// check if AdSense loads Auto Ads ads
		// Hidden, will be shown using js.
		if ( ! isset( $adsense_options['violation-warnings-disable'] ) ) {
			$nodes[] = [
				'type' => 2,
				'data' => [
					'parent' => 'advanced_ads_ad_health',
					'id'     => 'advanced_ads_autoads_displayed',
					'title'  => __( 'Random AdSense ads', 'advanced-ads' ),
					'href'   => ADVADS_URL . 'adsense-in-random-positions-auto-ads/?utm_source=advanced-ads&utm_medium=link&utm_campaign=frontend-autoads-ads',
					'meta'   => [
						'class'  => 'hidden',
						'target' => '_blank',
					],
				],
			];
		}

		// check if current user was identified as a bot.
		if ( Advanced_Ads::get_instance()->is_bot() ) {
			$nodes[] = [ 'type' => 1, 'data' => [
				'parent' => 'advanced_ads_ad_health',
				'id'    => 'advanced_ads_user_is_bot',
				'title' => __( 'You look like a bot', 'advanced-ads' ),
				'href'  => ADVADS_URL . 'manual/ad-health/#look-like-bot',
				'meta'   => [
					'class' => 'advanced_ads_ad_health_warning',
					'target' => '_blank'
				]
			] ];
		}

		// check if an ad blocker is enabled
		// Hidden, will be shown using js.
		$nodes[] = [ 'type' => 2, 'data' => [
			'parent' => 'advanced_ads_ad_health',
			'id'     => 'advanced_ads_ad_health_adblocker_enabled',
			'title'  => __( 'Ad blocker enabled', 'advanced-ads' ),
			'meta'   => [
				'class' => 'hidden advanced_ads_ad_health_warning',
				'target' => '_blank'
			]
		] ];

		if ( $wp_the_query->is_singular() ) {
			if ( $this->has_the_content_placements() ) {
				$nodes[] = [ 'type' => 2, 'data' => [
					'parent' => 'advanced_ads_ad_health',
					'id'    => 'advanced_ads_ad_health_the_content_not_invoked',
					'title' => sprintf( __( '<em>%s</em> filter does not exist', 'advanced-ads' ), 'the_content' ),
					'href'  => ADVADS_URL . 'manual/ads-not-showing-up/?utm_source=advanced-ads&utm_medium=link&utm_campaign=adhealth-content-filter-missing#the_content-filter-missing',
					'meta'   => [
						'class' => 'hidden advanced_ads_ad_health_warning',
						'target' => '_blank'
					]
				] ];
			}

			if ( ! empty( $post->ID ) ) {
				$ad_settings = get_post_meta( $post->ID, '_advads_ad_settings', true );

				if ( ! empty( $ad_settings['disable_the_content'] ) ) {
					$nodes[] = [ 'type' => 1, 'data' => [
						'parent' => 'advanced_ads_ad_health',
						'id'    => 'advanced_ads_ad_health_disabled_in_content',
						'title' => __( 'Ads are disabled in the content of this page', 'advanced-ads' ),
						'href'  => get_edit_post_link( $post->ID ) . '#advads-ad-settings',
						'meta'   => [
							'class' => 'advanced_ads_ad_health_warning',
							'target' => '_blank'
						]
					] ];
				}
			} else {
				$nodes[] = [ 'type' => 1, 'data' => [
					'parent' => 'advanced_ads_ad_health',
					'id'    => 'advanced_ads_ad_health_post_zero',
					'title' => __( 'the current post ID is 0 ', 'advanced-ads' ),
					'href'  => ADVADS_URL . 'manual/ad-health/#post-id-0',
					'meta'   => [
						'class' => 'advanced_ads_ad_health_warning',
						'target' => '_blank'
					]
				] ];
			}
		}

		$disabled_reason = Advanced_Ads::get_instance()->disabled_reason;
		$disabled_id = Advanced_Ads::get_instance()->disabled_id;

		if ( 'page' === $disabled_reason && $disabled_id ) {
			$nodes[] = [
				'type' => 1,
				'data' => [
					'parent' => 'advanced_ads_ad_health',
					'id'     => 'advanced_ads_ad_health_disabled_on_page',
					'title'  => __( 'Ads are disabled on this page', 'advanced-ads' ),
					'href'   => get_edit_post_link( $disabled_id ) . '#advads-ad-settings',
					'meta'   => [
						'class'  => 'advanced_ads_ad_health_warning',
						'target' => '_blank',
					],
				],
			];
		}

		if ( 'all' === $disabled_reason ) {
			$nodes[] = [ 'type' => 1, 'data' => [
				'parent' => 'advanced_ads_ad_health',
				'id'    => 'advanced_ads_ad_health_no_all',
				'title' => __( 'Ads are disabled on all pages', 'advanced-ads' ),
				'href'  => admin_url( 'admin.php?page=advanced-ads-settings' ),
				'meta'   => [
					'class' => 'advanced_ads_ad_health_warning',
					'target' => '_blank'
				]
			] ];
		}

		if ( '404' === $disabled_reason ) {
			$nodes[] = [
				'type' => 1,
				'data' => [
					'parent' => 'advanced_ads_ad_health',
					'id'     => 'advanced_ads_ad_health_no_404',
					'title'  => __( 'Ads are disabled on 404 pages', 'advanced-ads' ),
					'href'   => admin_url( 'admin.php?page=advanced-ads-settings' ),
					'meta'   => [
						'class'  => 'advanced_ads_ad_health_warning',
						'target' => '_blank',
					],
				],
			];
		}

		if ( 'archive' === $disabled_reason ) {
			$nodes[] = [ 'type' => 1, 'data' => [
				'parent' => 'advanced_ads_ad_health',
				'id'     => 'advanced_ads_ad_health_no_archive',
				'title'  => __( 'Ads are disabled on non singular pages', 'advanced-ads' ),
				'href'   => admin_url( 'admin.php?page=advanced-ads-settings' ),
				'meta'   => [
					'class'  => 'advanced_ads_ad_health_warning',
					'target' => '_blank'
				]
			] ];
		}

		$nodes[] = [ 'type' => 2, 'data' => [
			'parent' => 'advanced_ads_ad_health',
			'id'     => 'advanced_ads_ad_health_has_http',
			'title'  => sprintf( '%s %s',
				__( 'Your website is using HTTPS, but the ad code contains HTTP and might not work.', 'advanced-ads' ),
				sprintf( __( 'Ad IDs: %s', 'advanced-ads'  ), '<i></i>' )
			),
			'href'   => ADVADS_URL . 'manual/ad-health/?utm_source=advanced-ads&utm_medium=link&utm_campaign=adhealth-https-ads#https-ads',
			'meta'   => [
				'class'  => 'hidden advanced_ads_ad_health_warning advanced_ads_ad_health_has_http',
				'target' => '_blank'
			]
		] ];

		$nodes[] = [ 'type' => 2, 'data' => [
			'parent' => 'advanced_ads_ad_health',
			'id'     => 'advanced_ads_ad_health_incorrect_head',
			'title'  => sprintf( __( 'Visible ads should not use the Header placement: %s', 'advanced-ads' ), '<i></i>' ),
			'href'   => ADVADS_URL . 'manual/ad-health/?utm_source=advanced-ads&utm_medium=link&utm_campaign=adhealth-visible-ad-in-header#header-ads',
			'meta'   => [
				'class'  => 'hidden advanced_ads_ad_health_warning advanced_ads_ad_health_incorrect_head',
				'target' => '_blank'
			]
		] ];

		// warn if an AdSense ad seems to be hidden.
		if ( ! isset( $adsense_options['violation-warnings-disable'] ) ) {
			$nodes[] = [
				'type' => 2,
				'data' => [
					'parent' => 'advanced_ads_ad_health',
					'id'    => 'advanced_ads_ad_health_hidden_adsense',
					'title' => sprintf( '%s: %s. %s',
						__( 'AdSense violation', 'advanced-ads' ),
						__( 'Ad is hidden', 'advanced-ads' ),
						sprintf( __( 'IDs: %s', 'advanced-ads'  ), '<i></i>' )
					),
					'href'  => ADVADS_URL . 'manual/ad-health/?utm_source=advanced-ads&utm_medium=link&utm_campaign=adhealth-frontend-adsense-hidden#adsense-hidden',
					'meta'   => [
						'class' => 'hidden advanced_ads_ad_health_warning advanced_ads_ad_health_hidden_adsense',
						'target' => '_blank',
					],
				],
			];
		}

		$nodes[] = [
			'type' => 2,
			'data' => [
				'parent' => 'advanced_ads_ad_health',
				'id'     => 'advanced_ads_ad_health_floated_responsive_adsense',
				'title'  => sprintf( __( 'The following responsive AdSense ads are not showing up: %s', 'advanced-ads'  ), '<i></i>' ),
				'href'   => ADVADS_URL . 'manual/ad-health/?utm_source=advanced-ads&utm_medium=link&utm_campaign=adhealth-adsense-responsive-not-showing#The_following_responsive_AdSense_ads_arenot_showing_up',
				'meta'   => [
					'class'  => 'hidden advanced_ads_ad_health_warning advanced_ads_ad_health_floated_responsive_adsense',
					'target' => '_blank',
				],
			],
		];

		// warn if consent was not given.
		$privacy = Advanced_Ads_Privacy::get_instance();
		if ( 'not_needed' !== $privacy->get_state() ) {
			$nodes[] = [
				'type' => 2,
				'data' => [
					'parent' => 'advanced_ads_ad_health',
					'id'     => 'advanced_ads_ad_health_consent_missing',
					'title'  => __( 'Consent not given', 'advanced-ads' ),
					'href'   => admin_url( 'admin.php?page=advanced-ads-settings#top#privacy' ),
					'meta'   => [
						'class'  => 'hidden advanced_ads_ad_health_warning advanced_ads_ad_health_consent_missing',
						'target' => '_blank',
					],
				],
			];
		}

		$privacy_options = $privacy->options();
		if ( ( empty( $privacy_options['enabled'] ) || $privacy_options['consent-method'] !== 'iab_tcf_20' ) ) {
			$nodes[] = [
				'type' => 2,
				'data' => [
					'parent' => 'advanced_ads_ad_health',
					'id'     => 'advanced_ads_ad_health_privacy_disabled',
					'title'  => __( 'Enable TCF integration', 'advanced-ads' ),
					'href'   => admin_url( 'admin.php?page=advanced-ads-settings#top#privacy' ),
					'meta'   => [
						'class'  => 'hidden advanced_ads_ad_health_warning advanced_ads_ad_health_privacy_disabled',
						'target' => '_blank',
					],
				],
			];
		}

		$nodes[] = [
			'type' => 3,
			'data' => [
				'parent' => 'advanced_ads_ad_health',
				'id'     => 'advanced_ads_ad_health_gam_debug',
				'title'  => __( 'Debug Google Ad Manager', 'advanced-ads' ),
				'href'   => esc_url( add_query_arg( 'google_force_console', '1' ) ),
				'meta'   => [
					'class' => 'hidden advanced_ads_ad_health_gam_debug_link',
				],
			],
		];

		// link to highlight ads and jump from one ad to the next.
		$nodes[] = [
			'type' => 3,
			'amp'  => false,
			'data' => [
				'parent' => 'advanced_ads_ad_health',
				'id'     => 'advanced_ads_ad_health_highlight_ads',
				'title'  => sprintf(
					'<span class="link">%s</span> %s',
					__( 'highlight ads', 'advanced-ads' ),
					'<span class="arrows">
						<i class = "dashicons dashicons-arrow-up-alt previous"></i>
						<i class = "dashicons dashicons-arrow-down-alt next"></i>
					</span>'
				),
				'meta'   => [
					'class' => 'advanced_ads_ad_health_highlight_ads',
				],
			],
		];

		/**
		 * Add new node.
		 *
		 * @param array $node An array that contains:
		 *      'type' => 1 - warning, 2 - hidden warning that will be shown using JS, 3 - info message
		 *      'data': @see WP_Admin_Bar->add_node
		 * @param object  $wp_admin_bar
		 */
		$nodes = apply_filters( 'advanced-ads-ad-health-nodes', $nodes );

		usort( $nodes, [ $this, 'sort_nodes' ] );

		// load number of already detected notices.
		$notices = Advanced_Ads_Ad_Health_Notices::get_number_of_notices();

		if ( ! advads_is_amp() ) {
			$warnings = 0; // Will be updated using JS.
		} else {
			$warnings = $this->count_visible_warnings( $nodes, [ 1 ] );
		}

		$issues = $warnings;

		$this->add_header_nodes( $wp_admin_bar, $issues, $notices );

		foreach ( $nodes as $node ) {
			if ( isset( $node['data'] ) ) {
				$wp_admin_bar->add_node( $node['data'] );
			}
		}

		$this->add_footer_nodes( $wp_admin_bar, $issues );
	}


	/**
	 * Add classes to the `body` tag.
	 *
	 * @param string[] $classes Array of existing class names.
	 * @return string[] $classes Array of existing and new class names.
	 */
	public function body_class( $classes ) {
		$aa_classes = [
			'aa-prefix-' . Advanced_Ads_Plugin::get_instance()->get_frontend_prefix(),
		];

		$disabled_reason = Advanced_Ads::get_instance()->disabled_reason;
		if ( $disabled_reason ) {
			$aa_classes[] = 'aa-disabled-' . esc_attr( $disabled_reason );
		}

		global $post;
		if ( ! empty( $post->ID ) ) {
			$ad_settings = get_post_meta( $post->ID, '_advads_ad_settings', true );
			if ( ! empty( $ad_settings['disable_the_content'] ) ) {
				$aa_classes[] = 'aa-disabled-content';
			}
		}

		// hide-ads-from-bots option is enabled.
		$options = Advanced_Ads_Plugin::get_instance()->options();
		if ( ! empty( $options['block-bots'] ) ) {
			$aa_classes[] = 'aa-disabled-bots';
		}

		$aa_classes = apply_filters( 'advanced-ads-body-classes', $aa_classes );

		if ( ! is_array( $classes ) ) {
			$classes = [];
		}
		if ( ! is_array( $aa_classes ) ) {
			$aa_classes = [];
		}

		return array_merge( $classes, $aa_classes );
	}




	/**
	 * Count visible notices and warnings.
	 *
	 * @param array $nodes Nodes to add.
	 * @param array $types Warning types.
	 */
	private function count_visible_warnings( $nodes, $types = [] ) {
		$warnings = 0;
		foreach ( $nodes as $node ) {
			if ( ! isset( $node['type'] ) || ! isset( $node['data'] ) ) { continue; }
			if ( in_array( $node['type'], $types ) ) {
				$warnings++;
			}
		}
		return $warnings;
	}

	/**
	 * Add header nodes.
	 *
	 * @param object $wp_admin_bar WP_Admin_Bar object.
	 * @param int    $issues Number of all issues.
	 * @param int    $notices Number of notices.
	 */
	private function add_header_nodes( $wp_admin_bar, $issues, $notices ) {
		$wp_admin_bar->add_node(
			[
				'id'     => 'advanced_ads_ad_health',
				'title'  => __( 'Ad Health', 'advanced-ads' ) . '&nbsp;<span class="advanced-ads-issue-counter">' . $issues . '</span>',
				'parent' => false,
				'href'   => admin_url( 'admin.php?page=advanced-ads' ),
				'meta'   => [
					'class' => $issues ? 'advads-adminbar-is-warnings' : '',
				],
			]
		);

		// show that there are backend notices.
		if ( $notices ) {
			$wp_admin_bar->add_node(
				[
					'parent' => 'advanced_ads_ad_health',
					'id'     => 'advanced_ads_ad_health_more',
					'title'  => sprintf( __( 'Show %d more notifications', 'advanced-ads' ), absint( $notices ) ),
					'href'   => admin_url( 'admin.php?page=advanced-ads' ),
				]
			);
		}
	}

	/**
	 * Add footer nodes.
	 *
	 * @param obj $wp_admin_bar WP_Admin_Bar object.
	 * @param int $issues Number of all issues.
	 */
	private function add_footer_nodes( $wp_admin_bar, $issues ) {
		if ( ! $issues ) {
			$wp_admin_bar->add_node(
				[
					'parent' => 'advanced_ads_ad_health',
					'id'     => 'advanced_ads_ad_health_fine',
					'title'  => __( 'Everything is fine', 'advanced-ads' ),
					'href'   => false,
					'meta'   => [
						'target' => '_blank',
					],
				]
			);
		}

		$wp_admin_bar->add_node(
			[
				'parent' => 'advanced_ads_ad_health',
				'id'     => 'advanced_ads_ad_health_support',
				'title'  => __( 'Get help', 'advanced-ads' ),
				'href'   => Advanced_Ads_Plugin::support_url( '?utm_source=advanced-ads&utm_medium=link&utm_campaign=health-support' ),
				'meta'   => [
					'target' => '_blank',
				],
			]
		);
	}

	/**
	 * Filter out nodes intended to AMP pages only.
	 *
	 * @param array $nodes Nodes to add.
	 * @return array $nodes Nodes to add.
	 */
	private function filter_nodes( $nodes ) {
		return $nodes;
	}

	/**
	 * Sort nodes.
	 */
	function sort_nodes( $a, $b ) {
		if ( ! isset( $a['type'] ) || ! isset( $b['type'] ) ) {
			return 0;
		}
		if ( $a['type'] == $b['type'] ) {
			return 0;
		}
		return ( $a['type'] < $b['type'] ) ? -1 : 1;
	}

	/**
	 * Set variable to 'true' when 'the_content' filter is invoked.
	 *
	 * @param string $content
	 * @return string $content
	 */
	public function set_did_the_content( $content ) {
		if ( ! $this->did_the_content ) {
			$this->did_the_content = true;
		}

		if ( Advanced_Ads::get_instance()->has_many_the_content() ) {
			$this->has_many_the_content = true;
		}
		return $content;
	}

	/**
	 * Check conditions and display warning.
	 * Conditions:
	 *     AdBlocker enabled,
	 *     jQuery is included in header
	 *     AdSense Quick Start ads are running
	 */
	public function footer_checks() {
		ob_start();
		?><!-- Advanced Ads: <?php esc_html_e( 'the following code is used for automatic error detection and only visible to admins', 'advanced-ads' ); ?>-->
		<style>#wp-admin-bar-advanced_ads_ad_health .hidden { display: none; }
		#wp-admin-bar-advanced_ads_ad_health-default a:after { content: "\25BA"; margin-left: .5em; font-size: smaller; }
		#wp-admin-bar-advanced_ads_ad_health-default .advanced_ads_ad_health_highlight_ads div:before { content: "\f177"; margin-right: .2em; line-height: 1em; padding: 0.2em 0 0; color: inherit; }
		#wp-admin-bar-advanced_ads_ad_health-default .advanced_ads_ad_health_highlight_ads div:hover { color: #00b9eb; cursor: pointer; }
		#wpadminbar .advanced-ads-issue-counter { background-color: #d54e21; display: none; padding: 1px 7px 1px 6px!important; border-radius: 50%; color: #fff; }
		#wpadminbar .advads-adminbar-is-warnings .advanced-ads-issue-counter { display: inline; }
		.advanced-ads-highlight-ads { outline:4px solid #0474A2 !important; }
		#wp-admin-bar-advanced_ads_ad_health .advanced_ads_ad_health_highlight_ads .arrows {display: none;}
		#wp-admin-bar-advanced_ads_ad_health .arrows .dashicons {font-family: 'dashicons';}
		#wp-admin-bar-advanced_ads_ad_health.hover .advanced_ads_ad_health_highlight_ads.active .arrows {display: inline-block;}
		</style>
		<?php
			// phpcs:ignore
			echo ob_get_clean();

		if ( advads_is_amp() ) {
			return;
		}

		$adsense_options = Advanced_Ads_AdSense_Data::get_instance()->get_options();
		ob_start();
		?>
		<script type="text/javascript" src="<?php echo ADVADS_BASE_URL . 'admin/assets/js/advertisement.js' ?>"></script>
		<script>
			var advanced_ads_frontend_checks = {
				showCount: function() {
					try {
						// Count only warnings that have the 'advanced_ads_ad_health_warning' class.
						var warning_count = document.querySelectorAll( '.advanced_ads_ad_health_warning:not(.hidden)' ).length;
						var fine_item = document.getElementById( 'wp-admin-bar-advanced_ads_ad_health_fine' );
					} catch ( e ) { return; }

					var header = document.querySelector( '#wp-admin-bar-advanced_ads_ad_health > a' );
					if ( warning_count ) {
						if ( fine_item ) {
							// Hide 'fine' item.
							fine_item.className += ' hidden';
						}

						if ( header ) {
							header.innerHTML = header.innerHTML.replace(/<span class="advanced-ads-issue-counter">\d*<\/span>/, '') + '<span class="advanced-ads-issue-counter">' + warning_count + '</span>';
							// add class
							header.className += ' advads-adminbar-is-warnings';
						}
					} else {
						// Show 'fine' item.
						if ( fine_item ) {
							fine_item.classList.remove('hidden');
						}

						// Remove counter.
						if ( header ) {
							header.innerHTML = header.innerHTML.replace(/<span class="advanced-ads-issue-counter">\d*<\/span>/, '');
							header.classList.remove('advads-adminbar-is-warnings');
						}
					}
				},

				array_unique: function( array ) {
					var r= [];
					for ( var i = 0; i < array.length; i++ ) {
						if ( r.indexOf( array[ i ] ) === -1 ) {
							r.push( array[ i ] );
						}
					}
					return r;
				},

				/**
				 * Add item to Ad Health node.
				 *
				 * @param string selector Selector of the node.
				 * @param string/array item item(s) to add.
				 */
				add_item_to_node: function( selector, item ) {
					if ( typeof item === 'string' ) {
						item = item.split();
					}
					var selector = document.querySelector( selector );
					if ( selector ) {
						selector.className = selector.className.replace( 'hidden', '' );
						selector.innerHTML = selector.innerHTML.replace( /(<i>)(.*?)(<\/i>)/, function( match, p1, p2, p3 ) {
							p2 = ( p2 ) ? p2.split( ', ' ) : [];
							p2 = p2.concat( item );
							p2 = advanced_ads_frontend_checks.array_unique( p2 );
							return p1 + p2.join( ', ' ) + p3;
						} );
						advanced_ads_frontend_checks.showCount();
					}
				},

				/**
				 * Add item to Ad Health notices in the backend
				 *
				 * @param key of the notice
				 * @param attr
				 * @returns {undefined}
				 */
				add_item_to_notices: function( key, attr = '' ) {
					var cookie = advads.get_cookie( 'advanced_ads_ad_health_notices' );
					if ( cookie ){
						advads_cookie_notices = JSON.parse( cookie );
					} else {
						advads_cookie_notices = new Array();
					}
					// stop if notice was added less than 1 hour ago
					if ( 0 <= advads_cookie_notices.indexOf( key ) ){
						return;
					}
					var query = {
						action: 'advads-ad-health-notice-push',
						key: key,
						attr: attr,
						nonce: '<?php echo wp_create_nonce('advanced-ads-ad-health-ajax-nonce'); ?>'
					};
					// send query
					// update notices and cookie
					jQuery.post( advads_frontend_checks.ajax_url, query, function (r) {
						advads_cookie_notices.push( key );
						var notices_str = JSON.stringify( advads_cookie_notices );
						advads.set_cookie_sec( 'advanced_ads_ad_health_notices', notices_str, 3600 ); // 1 hour
					});
				},

				/**
				 * Search for hidden AdSense.
				 *
				 * @param string context Context for search.
				 */
				advads_highlight_hidden_adsense: function( context ) {
					if ( ! context ) {
						context = 'html'
					}
					if ( window.jQuery ) {
						var responsive_zero_width = [];
						jQuery( 'ins.adsbygoogle', context ).each( function() {
							// Zero width, perhaps because a parent container is floated
							if ( jQuery( this ).attr( 'data-ad-format' ) && 0 === jQuery( this ).width() ) {
								responsive_zero_width.push( this.dataset.adSlot );
							}
						});
						if ( responsive_zero_width.length ) {
							advanced_ads_frontend_checks.add_item_to_node( '.advanced_ads_ad_health_floated_responsive_adsense', responsive_zero_width );
						}
					}
				}
			};

			(function(d, w) {
				// highlight link as global
				var highlightLink = d.getElementById( 'wp-admin-bar-advanced_ads_ad_health_highlight_ads' );
				var adWrappers;
				// update ad count in health tool admin bar
				updateAdsCount(d);
				var addEvent = function( obj, type, fn ) {
					if ( obj.addEventListener )
						obj.addEventListener( type, fn, false );
					else if ( obj.attachEvent )
						obj.attachEvent( 'on' + type, function() { return fn.call( obj, window.event ); } );
				};

				function getAdWrappers() {
					return document.querySelectorAll(".<?php echo Advanced_Ads_Plugin::get_instance()->get_frontend_prefix(); ?>highlight-wrapper, .google-auto-placed");
				}

				// highlight ads that use Advanced Ads placements or AdSense Auto ads
				function highlightAds() {
					/**
					 * Selectors:
					 * Placement container: ".<?php echo Advanced_Ads_Plugin::get_instance()->get_frontend_prefix(); ?>highlight-wrapper, .google-auto-placed"
					 * AdSense Auto ads: 'google-auto-placed'
					 */
					try {
						<?php //phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						adWrappers = getAdWrappers();
						<?php //phpcs:enable ?>
					} catch ( e ) { return; }
					for ( i = 0; i < adWrappers.length; i++ ) {
						// Check highlighted ads active
						adWrappers[i].classList.toggle('advanced-ads-highlight-ads');
						// show title only when highlight ads active.
						if ( adWrappers[i].classList.contains('advanced-ads-highlight-ads') ) {
							adWrappers[i].title = adWrappers[i].getAttribute('data-title');
						} else {
							adWrappers[i].title = '';
						}
					}

					// add or remove active class from highlight link
					highlightLink.classList.toggle('active');
				}

				function scrollToHighlightedAd() {
					try {
						// If no ad wrappers are found, exit the function
						if (adWrappers.length === 0) return;

						// Initialize or update the index of the currently highlighted ad
						if (typeof window.current_highlighted_ad === "undefined") {
							window.current_highlighted_ad = 0;
						} else if (this.classList.contains('next') && adWrappers.length - 1 > window.current_highlighted_ad) {
							window.current_highlighted_ad++;
						} else if (this.classList.contains('previous') && window.current_highlighted_ad > 0) {
							window.current_highlighted_ad--;
						}

						// Get the offsetTop of the currently highlighted ad's wrapper
						const scrollDiv = document.getElementById(adWrappers[window.current_highlighted_ad]?.id)?.offsetTop;

						// If scrollDiv is defined, scroll to the ad wrapper's position
						if (scrollDiv !== undefined) {
							window.scrollTo({ top: scrollDiv, behavior: 'smooth' });
						}
					} catch (e) {
						// Handle any errors that might occur
					}
				}

				advanced_ads_ready( function() {
					var adblock_item = d.getElementById( 'wp-admin-bar-advanced_ads_ad_health_adblocker_enabled' );

					// handle click on the highlightAds link
					var link = highlightLink.querySelector('.link');
					addEvent( link, 'click', highlightAds );

					// arrows click handler
					var arrows = highlightLink.querySelector('.arrows').querySelectorAll('.dashicons');
					for ( let i = 0; i < arrows.length; i++ ) {
						arrows[i].addEventListener("click", scrollToHighlightedAd);
					}

					if ( adblock_item && typeof advanced_ads_adblocker_test === 'undefined' ) {
						// show hidden item
						adblock_item.className = adblock_item.className.replace( /hidden/, '' );
					}

					<?php if ( ! $this->did_the_content ) : ?>
						var the_content_item = d.getElementById( 'wp-admin-bar-advanced_ads_ad_health_the_content_not_invoked' );
						if ( the_content_item ) {
							the_content_item.className = the_content_item.className.replace( /hidden/, '' );
						}
					<?php endif; ?>

					advanced_ads_frontend_checks.showCount();
				});

				<?php if ( ! isset( $adsense_options['violation-warnings-disable'] ) ) : ?>
					// show warning if AdSense ad is hidden
					// show hint if AdSense Auto ads are enabled
					setTimeout( function(){
						advanced_ads_ready( advanced_ads_frontend_checks.advads_highlight_hidden_adsense );
					}, 2000 );

					// highlight AdSense Auto Ads ads 3 seconds after site loaded
					setTimeout( function(){
						advanced_ads_ready( advads_highlight_adsense_autoads );
					}, 3000 );
					function advads_highlight_adsense_autoads(){
						if ( ! window.jQuery ) {
							window.console && window.console.log( 'Advanced Ads: jQuery not found. Some Ad Health warnings will not be displayed.' );
							return;
						}
						var autoads_ads = document.querySelectorAll('.google-auto-placed');
						// show Auto Ads warning in Ad Health bar if relevant
						if ( autoads_ads.length ){
							var advads_autoads_link = document.querySelector( '#wp-admin-bar-advanced_ads_autoads_displayed.hidden' );
							if ( advads_autoads_link ) {
								advads_autoads_link.className = advads_autoads_link.className.replace( 'hidden', '' );
							}
							advanced_ads_frontend_checks.showCount();
						}
					}
					<?php
				endif;
				/**
				 * Code to check if current user gave consent to show ads
				 */
				$privacy = Advanced_Ads_Privacy::get_instance();
				if ( 'not_needed' !== $privacy->get_state() ) :
					?>
					document.addEventListener('advanced_ads_privacy', function (event) {
						var advads_consent_link = document.querySelector('#wp-admin-bar-advanced_ads_ad_health_consent_missing');

						if (!advads_consent_link) {
							return;
						}

						if (event.detail.state !== 'accepted' && event.detail.state !== 'not_needed') {
							advads_consent_link.classList.remove('hidden');
						} else {
							advads_consent_link.classList.add('hidden');
						}

						advanced_ads_frontend_checks.showCount();
					});
					<?php
				endif;
				$privacy_options = $privacy->options();
				if (
					( empty( $privacy_options['enabled'] ) || 'iab_tcf_20' !== $privacy_options['consent-method'] )
					&& (bool) apply_filters( 'advanced-ads-ad-health-show-tcf-notice', true )
				) :
					?>
				var count = 0,
					tcfapiInterval = setInterval(function () {
					if (++count === 600) {
						clearInterval(tcfapiInterval);
					}
					if (typeof window.__tcfapi === 'undefined') {
						return;
					}
					clearInterval(tcfapiInterval);

					var advadsPrivacyLink = document.querySelector('#wp-admin-bar-advanced_ads_ad_health_privacy_disabled');

					if (!advadsPrivacyLink) {
						return;
					}

					advadsPrivacyLink.classList.remove('hidden');

					advanced_ads_frontend_checks.showCount();
				}, 100);
				<?php endif; ?>
				/**
				 * show Google Ad Manager debug link in Ad Health
				 *
				 * look for container with ID starting with `div-gpt-ad-`
				 * or `gpt-ad-` as used by our own Google Ad Manager integration
				 * we don’t look for the gpt header script because that is also used by other services that are based on Google Publisher Tags
				 */
				function advadsGamShowDebugLink(){
					var advadsGamDebugLink = document.querySelector( '.advanced_ads_ad_health_gam_debug_link.hidden' );

					if ( ! advadsGamDebugLink ){
						return;
					}

					// Check for the `googletag` variable created in the page header or directly in the body alongside the ad slot definition.
					if ( typeof window.googletag !== 'undefined' ) {
						advadsGamDebugLink.className = advadsGamDebugLink.className.replace( 'hidden', '' );
					}
				}
				// look for Google Ad Manager tags with a delay of 2 seconds
				setTimeout( function(){
					advanced_ads_ready( advadsGamShowDebugLink );
				}, 2000 );

				// Function to count visible ads with unique group IDs
				function getAdsCount(){
					// Get all elements with the specified class name
					const adWrappers = getAdWrappers();
					// Initialize a count for visible ads
					let ads_count = 0;
					// Loop through each ad wrapper element
					for ( let i = 0; i < adWrappers.length; i++ ) {
						// Check if the group ID is either null or not included in the array of seen group IDs.
						if ( adWrappers[i].offsetHeight > 0 ) {
							// Increment the ad count and add the group ID to the list.
							ads_count++;
						}
					}
					// Return the total count of eligible ads
					return ads_count;
				}

				function updateAdsCount(d){
					var highlightLink = d.getElementById( 'wp-admin-bar-advanced_ads_ad_health_highlight_ads' );
					// update ad count in health tool admin bar
					highlightLink.querySelector('.link').innerHTML += ' (<span class="highlighted_ads_count">' + getAdsCount() + '</span>) ';

					// If any ads load by ajax its update count after ajax load
					var origOpen = XMLHttpRequest.prototype.open;
					XMLHttpRequest.prototype.open = function() {
						this.addEventListener('load', function() {
							if ( this.status === 200 ) {
								highlightLink.querySelector('.highlighted_ads_count').innerHTML = getAdsCount();
							}
						});
						origOpen.apply(this, arguments);
					};
				}
			})(document, window);
		</script>
		<?php echo Advanced_Ads_Utils::get_inline_asset( ob_get_clean() );
	}

	/**
	 * Inject JS after ad content.
	 *
	 * @param str $content ad content.
	 * @param obj $ad Advanced_Ads_Ad.
	 * @return str $content ad content
	 */
	public function after_ad_output( $content, Advanced_Ads_Ad $ad ) {
		if ( ! isset( $ad->args['frontend-check'] ) ) { return $content; }

		if ( advads_is_amp() ) {
			return $content;
		}

		if ( Advanced_Ads_Ad_Debug::is_https_and_http( $ad ) ) {
			ob_start(); ?>
			<script>advanced_ads_ready( function() {
				var ad_id = '<?php echo $ad->id; ?>';
				advanced_ads_frontend_checks.add_item_to_node( '.advanced_ads_ad_health_has_http', ad_id );
				advanced_ads_frontend_checks.add_item_to_notices( 'ad_has_http', { append_key: ad_id, ad_id: ad_id } );
			});</script>
			<?php
			$content .= Advanced_Ads_Utils::get_inline_asset( ob_get_clean() );
		}

		if ( ! Advanced_Ads_Frontend_Checks::can_use_head_placement( $content, $ad ) ) {
			ob_start(); ?>
			<script>advanced_ads_ready( function() {
			var ad_id = '<?php echo $ad->id; ?>';
			advanced_ads_frontend_checks.add_item_to_node( '.advanced_ads_ad_health_incorrect_head', ad_id );
			advanced_ads_frontend_checks.add_item_to_notices( 'ad_with_output_in_head', { append_key: ad_id, ad_id: ad_id } );
			});</script>
			<?php
			$content .= Advanced_Ads_Utils::get_inline_asset( ob_get_clean() );
		}

		$adsense_options = Advanced_Ads_AdSense_Data::get_instance()->get_options();
		if ( 'adsense' === $ad->type
			&& ! empty( $ad->args['cache_busting_elementid'] )
			&& ! isset( $adsense_options['violation-warnings-disable'] )
		) {
			ob_start(); ?>
			<script>advanced_ads_ready( function() {
				var ad_id = '<?php echo $ad->id; ?>';
				var wrapper = '#<?php echo $ad->args['cache_busting_elementid']; ?>';
				advanced_ads_frontend_checks.advads_highlight_hidden_adsense( wrapper );
			});</script>
			<?php
			$content .= Advanced_Ads_Utils::get_inline_asset( ob_get_clean() );
		}

		return $content;
	}


	/**
	 * Check if the 'Header Code' placement can be used to delived the ad.
	 *
	 * @param string          $content Ad content.
	 * @param Advanced_Ads_Ad $ad Advanced_Ads_Ad.
	 * @return bool
	 */
	public static function can_use_head_placement( $content, Advanced_Ads_Ad $ad ) {

		if ( ! $ad->is_head_placement ) {
			return true;
		}

		// strip linebreaks, because, a line break after a comment is identified as a text node.
		$content = preg_replace( "/\r|\n/", "", $content );

		if ( ! $dom = self::get_ad_dom( $content ) ) {
			return true;
		}

		$body = $dom->getElementsByTagName( 'body' )->item( 0 );

		$count = $body->childNodes->length;
		for ( $i = 0; $i < $count; $i++ ) {
			$node = $body->childNodes->item( $i );

			if ( XML_TEXT_NODE  === $node->nodeType ) {
				return false;
			}

			if ( XML_ELEMENT_NODE === $node->nodeType
				&& ! in_array( $node->nodeName, [ 'meta', 'link', 'title', 'style', 'script', 'noscript', 'base' ] ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Convert ad content to a DOMDocument.
	 *
	 * @param string $content
	 * @return DOMDocument|false
	 */
	private static function get_ad_dom( $content ) {
		if ( ! extension_loaded( 'dom' ) ) {
			return false;
		}
		$libxml_previous_state = libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		$result = $dom->loadHTML( '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>' . $content . '</body></html>' );

		libxml_clear_errors();
		libxml_use_internal_errors( $libxml_previous_state );

		if ( ! $result ) {
			return false;
		}

		return $dom;
	}

	/**
	 * Check if at least one placement uses `the_content`.
	 *
	 * @return bool True/False.
	 */
	private function has_the_content_placements() {
		$placements = Advanced_Ads::get_ad_placements_array();
		$placement_types = Advanced_Ads_Placements::get_placement_types();
		// Find a placement that depends on 'the_content' filter.
		foreach ( $placements as $placement ) {
			if ( isset ( $placement['type'] )
				&& ! empty( $placement_types[ $placement['type'] ]['options']['uses_the_content'] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if atleast one placement uses `adblocker item`.
	 *
	 * @return bool True/False.
	 */
	private function has_adblocker_placements() {
		$placements = Advanced_Ads::get_instance()->get_model()->get_ad_placements_array();
		foreach ($placements as $placement) {
			if (!empty($placement['options']['item_adblocker'])) {
				return true;
			}
		}

		return false;
	}
}
