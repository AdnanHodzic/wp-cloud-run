<?php

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

class SWCFPC_Cache_Controller {


	/**
	 * The main plugin class.
	 *
	 * @var \SW_CLOUDFLARE_PAGECACHE
	 */
	private $main_instance = null;

	private $modules = false;

	private $skip_cache             = false;
	private $purge_all_already_done = false;
	private $cache_buster           = 'swcfpc';
	private $htaccess_path          = '';

	function __construct( $cache_buster, $main_instance ) {

		$this->cache_buster  = $cache_buster;
		$this->main_instance = $main_instance;

		if ( ! function_exists( 'get_home_path' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$this->htaccess_path = get_home_path() . '.htaccess';

		$this->actions();
	}


	function actions() {

		// Purge cache cronjob
		add_action( 'swcfpc_cache_purge_cron', [ $this, 'purge_cache_queue_job' ] );
		add_filter( 'cron_schedules', [ $this, 'purge_cache_queue_custom_interval' ] );
		add_action( 'shutdown', [ $this, 'purge_cache_queue_start_cronjob' ], PHP_INT_MAX );

		// SEO redirect for all URLs that for any reason have been indexed together with the cache buster
		if ( $this->main_instance->get_single_config( 'cf_seo_redirect', 1 ) > 0 ) {
			add_action( 'init', [ $this, 'redirect_301_real_url' ], 0 );
		}

		add_action( 'wp_footer', [ $this, 'inject_cache_buster_js_code' ], PHP_INT_MAX );
		add_action( 'admin_footer', [ $this, 'inject_cache_buster_js_code' ], PHP_INT_MAX );

		// Auto prefetch URLs
		add_action( 'wp_footer', [ $this, 'prefetch_urls' ], PHP_INT_MAX );

		// Ajax preloader start
		add_action( 'wp_ajax_swcfpc_preloader_start', [ $this, 'ajax_preloader_start' ] );

		// Ajax unlock preloader
		add_action( 'wp_ajax_swcfpc_preloader_unlock', [ $this, 'ajax_preloader_unlock' ] );

		// Ajax clear whole cache
		add_action( 'wp_ajax_swcfpc_purge_whole_cache', [ $this, 'ajax_purge_whole_cache' ] );

		// Force purge everything
		add_action( 'wp_ajax_swcfpc_purge_everything', [ $this, 'ajax_purge_everything' ] );

		// Ajax clear single post cache
		add_action( 'wp_ajax_swcfpc_purge_single_post_cache', [ $this, 'ajax_purge_single_post_cache' ] );

		// Ajax reset all
		add_action( 'wp_ajax_swcfpc_reset_all', [ $this, 'ajax_reset_all' ] );

		// add_action( 'init', array( $this, 'force_bypass_for_logged_in_users' ), PHP_INT_MAX );

		// This sets response headers for backend
		add_action( 'init', [ $this, 'setup_response_headers_backend' ], 0 );

		// These set response headers for frontend
		add_action( 'send_headers', [ $this, 'bypass_cache_on_init' ], PHP_INT_MAX );
		add_action( 'template_redirect', [ $this, 'apply_cache' ], PHP_INT_MAX );

		// add_filter( 'wp_headers', array($this, 'setup_response_headers_filter'), PHP_INT_MAX );

		// Purge cache via cronjob
		add_action( 'init', [ $this, 'cronjob_purge_cache' ] );

		// Start preloader via cronjob
		add_action( 'init', [ $this, 'cronjob_preloader' ] );

		// W3TC actions
		if ( $this->main_instance->get_single_config( 'cf_w3tc_purge_on_flush_dbcache', 0 ) > 0 ) {
			add_action( 'w3tc_flush_dbcache', [ $this, 'w3tc_hooks' ], PHP_INT_MAX );
		}

		if ( $this->main_instance->get_single_config( 'cf_w3tc_purge_on_flush_all', 0 ) > 0 ) {
			add_action( 'w3tc_flush_all', [ $this, 'w3tc_hooks' ], PHP_INT_MAX );
		}

		if ( $this->main_instance->get_single_config( 'cf_w3tc_purge_on_flush_fragmentcache', 0 ) > 0 ) {
			add_action( 'w3tc_flush_fragmentcache', [ $this, 'w3tc_hooks' ], PHP_INT_MAX );
		}

		if ( $this->main_instance->get_single_config( 'cf_w3tc_purge_on_flush_objectcache', 0 ) > 0 ) {
			add_action( 'w3tc_flush_objectcache', [ $this, 'w3tc_hooks' ], PHP_INT_MAX );
		}

		if ( $this->main_instance->get_single_config( 'cf_w3tc_purge_on_flush_posts', 0 ) > 0 ) {
			add_action( 'w3tc_flush_posts', [ $this, 'w3tc_hooks' ], PHP_INT_MAX );
		}

		if ( $this->main_instance->get_single_config( 'cf_w3tc_purge_on_flush_posts', 0 ) > 0 ) {
			add_action( 'w3tc_flush_post', [ $this, 'w3tc_hooks' ], PHP_INT_MAX );
		}

		if ( $this->main_instance->get_single_config( 'cf_w3tc_purge_on_flush_minfy', 0 ) > 0 ) {
			add_action( 'w3tc_flush_minify', [ $this, 'w3tc_hooks' ], PHP_INT_MAX );
		}

		// WP-Optimize actions
		if ( $this->main_instance->get_single_config( 'cf_wp_optimize_purge_on_cache_flush', 0 ) > 0 ) {
			add_action( 'wpo_cache_flush', [ $this, 'wpo_hooks' ], PHP_INT_MAX );
		}

		// WP Performance actions
		if ( $this->main_instance->get_single_config( 'cf_wp_performance_purge_on_cache_flush', 0 ) > 0 ) {
			add_action( 'wpp-after-cache-delete', [ $this, 'wp_performance_hooks' ], PHP_INT_MAX );
		}

		// WP Rocket actions
		if ( $this->main_instance->get_single_config( 'cf_wp_rocket_purge_on_post_flush', 0 ) > 0 ) {
			add_action( 'after_rocket_clean_post', [ $this, 'wp_rocket_after_rocket_clean_post_hook' ], PHP_INT_MAX );
		}

		if ( $this->main_instance->get_single_config( 'cf_wp_rocket_purge_on_domain_flush', 0 ) > 0 ) {
			add_action( 'after_rocket_clean_domain', [ $this, 'wp_rocket_hooks' ], PHP_INT_MAX );
		}

		if ( $this->main_instance->get_single_config( 'cf_wp_rocket_purge_on_cache_dir_flush', 0 ) > 0 ) {
			add_action( 'rocket_after_automatic_cache_purge_dir', [ $this, 'wp_rocket_hooks' ], PHP_INT_MAX );
		}

		if ( $this->main_instance->get_single_config( 'cf_wp_rocket_purge_on_clean_files', 0 ) > 0 ) {
			add_action( 'after_rocket_clean_files', [ $this, 'wp_rocket_selective_url_purge_hooks' ], PHP_INT_MAX, 1 );
		}

		if ( $this->main_instance->get_single_config( 'cf_wp_rocket_purge_on_clean_cache_busting', 0 ) > 0 ) {
			add_action( 'after_rocket_clean_cache_busting', [ $this, 'wp_rocket_hooks' ], PHP_INT_MAX );
		}

		if ( $this->main_instance->get_single_config( 'cf_wp_rocket_purge_on_clean_minify', 0 ) > 0 ) {
			add_action( 'after_rocket_clean_minify', [ $this, 'wp_rocket_hooks' ], PHP_INT_MAX );
		}

		if ( $this->main_instance->get_single_config( 'cf_wp_rocket_purge_on_ccss_generation_complete', 0 ) > 0 ) {
			add_action( 'rocket_critical_css_generation_process_complete', [ $this, 'wp_rocket_hooks' ], PHP_INT_MAX );
		}

		if ( $this->main_instance->get_single_config( 'cf_wp_rocket_purge_on_rucss_job_complete', 0 ) > 0 ) {
			add_action( 'rocket_rucss_complete_job_status', [ $this, 'wp_rocket_selective_url_purge_hooks' ], PHP_INT_MAX, 1 );
		}

		if ( $this->main_instance->get_single_config( 'cf_wp_rocket_disable_cache', 0 ) > 0 ) {
			add_action( 'admin_init', [ $this, 'wp_rocket_disable_page_cache' ], PHP_INT_MAX );
		}

		// LiteSpeed actions
		if ( $this->main_instance->get_single_config( 'cf_litespeed_purge_on_cache_flush', 0 ) > 0 ) {
			add_action( 'litespeed_purged_all', [ $this, 'litespeed_hooks' ], PHP_INT_MAX );
		}

		if ( $this->main_instance->get_single_config( 'cf_litespeed_purge_on_ccss_flush', 0 ) > 0 ) {
			add_action( 'litespeed_purged_all_ccss', [ $this, 'litespeed_hooks' ], PHP_INT_MAX );
		}

		if ( $this->main_instance->get_single_config( 'cf_litespeed_purge_on_cssjs_flush', 0 ) > 0 ) {
			add_action( 'litespeed_purged_all_cssjs', [ $this, 'litespeed_hooks' ], PHP_INT_MAX );
		}

		if ( $this->main_instance->get_single_config( 'cf_litespeed_purge_on_object_cache_flush', 0 ) > 0 ) {
			add_action( 'litespeed_purged_all_object', [ $this, 'litespeed_hooks' ], PHP_INT_MAX );
		}

		if ( $this->main_instance->get_single_config( 'cf_litespeed_purge_on_single_post_flush', 0 ) > 0 ) {
			add_action( 'litespeed_api_purge_post', [ $this, 'litespeed_single_post_hooks' ], PHP_INT_MAX, 1 );
		}

		// Hummingbird actions
		if ( $this->main_instance->get_single_config( 'cf_hummingbird_purge_on_cache_flush', 0 ) > 0 ) {
			add_action( 'wphb_clear_cache_url', [ $this, 'hummingbird_hooks' ], PHP_INT_MAX );
		}

		// Woocommerce actions
		if ( $this->main_instance->get_single_config( 'cf_auto_purge_woo_product_page', 0 ) > 0 ) {
			add_action( 'woocommerce_updated_product_stock', [ $this, 'woocommerce_purge_product_page_on_stock_change' ], PHP_INT_MAX, 1 );
		}

		// Woocommerce scheduled sales
		if ( $this->main_instance->get_single_config( 'cf_auto_purge_woo_scheduled_sales', 0 ) > 0 ) {
			add_action( 'wc_after_products_starting_sales', [ $this, 'woocommerce_purge_scheduled_sales' ], PHP_INT_MAX );
			add_action( 'wc_after_products_ending_sales', [ $this, 'woocommerce_purge_scheduled_sales' ], PHP_INT_MAX );
		}

		// Swift Performance (Lite/Pro) actions
		if ( $this->main_instance->get_single_config( 'cf_spl_purge_on_flush_all', 0 ) > 0 ) {
			add_action( 'swift_performance_after_clear_all_cache', [ $this, 'spl_purge_all' ], PHP_INT_MAX );
			add_action( 'swift_performance_after_clear_expired_cache', [ $this, 'spl_purge_all' ], PHP_INT_MAX );
			add_action( 'swift_performance_after_clear_post_cache', [ $this, 'spl_purge_single_post' ], PHP_INT_MAX );
		}

		// Edd actions
		if ( $this->main_instance->get_single_config( 'cf_auto_purge_edd_payment_add', 0 ) > 0 ) {
			add_action( 'edd_built_order', [ $this, 'edd_purge_cache_on_payment_add' ], PHP_INT_MAX );
		}

		// Nginx Helper actions
		if ( $this->main_instance->get_single_config( 'cf_nginx_helper_purge_on_cache_flush', 0 ) > 0 ) {
			add_action( 'rt_nginx_helper_after_fastcgi_purge_all', [ $this, 'nginx_helper_purge_all_hooks' ], PHP_INT_MAX );
			add_action( 'rt_nginx_helper_fastcgi_purge_url_base', [ $this, 'nginx_helper_purge_single_url_hooks' ], PHP_INT_MAX, 1 );
		}

		// YASR actions
		if ( $this->main_instance->get_single_config( 'cf_yasr_purge_on_rating', 0 ) > 0 ) {
			add_action( 'yasr_action_on_overall_rating', [ $this, 'yasr_hooks' ], PHP_INT_MAX, 1 );
			add_action( 'yasr_action_on_visitor_vote', [ $this, 'yasr_hooks' ], PHP_INT_MAX, 1 );
			add_action( 'yasr_action_on_visitor_multiset_vote', [ $this, 'yasr_hooks' ], PHP_INT_MAX, 1 );
		}

		// WP Asset Clean Up actions
		if ( $this->main_instance->get_single_config( 'cf_wpacu_purge_on_cache_flush', 0 ) > 0 ) {
			add_action( 'wpacu_clear_cache_after', [ $this, 'wpacu_hooks' ], PHP_INT_MAX );
		}

		// Flying Press actions
		if ( $this->main_instance->get_single_config( 'cf_flypress_purge_on_cache_flush', 0 ) > 0 ) {
			add_action( 'flying_press_purge_pages:after', [ $this, 'flying_press_hook' ], PHP_INT_MAX );
			add_action( 'flying_press_purge_everything:after', [ $this, 'flying_press_hook' ], PHP_INT_MAX );
		}

		// Autoptimize actions
		if ( $this->main_instance->get_single_config( 'cf_autoptimize_purge_on_cache_flush', 0 ) > 0 ) {
			add_action( 'autoptimize_action_cachepurged', [ $this, 'autoptimize_hooks' ], PHP_INT_MAX );
		}

		// Purge when upgrader process is complete
		if ( $this->main_instance->get_single_config( 'cf_auto_purge_on_upgrader_process_complete', 0 ) > 0 ) {
			add_action( 'upgrader_process_complete', [ $this, 'purge_on_plugin_update' ], PHP_INT_MAX );
		}

		// Bypass WP JSON REST
		if ( $this->main_instance->get_single_config( 'cf_bypass_wp_json_rest', 0 ) > 0 ) {
			add_filter( 'rest_send_nocache_headers', '__return_true' );
		}

		// Purge cache on comments
		add_action( 'transition_comment_status', [ $this, 'purge_cache_when_comment_is_approved' ], PHP_INT_MAX, 3 );
		add_action( 'comment_post', [ $this, 'purge_cache_when_new_comment_is_added' ], PHP_INT_MAX, 3 );
		add_action( 'delete_comment', [ $this, 'purge_cache_when_comment_is_deleted' ], PHP_INT_MAX );

		// Programmatically purge the cache via action
		add_action( 'swcfpc_purge_cache', [ $this, 'purge_cache_programmatically' ], PHP_INT_MAX, 1 );

		// Elementor AJAX update
		// add_action('elementor/ajax/register_actions', array($this, 'purge_cache_on_elementor_ajax_update'));

		$purge_actions = [
			'wp_update_nav_menu',                                     // When a custom menu is updated
			'update_option_theme_mods_' . get_option( 'stylesheet' ), // When any theme modifications are updated
			'avada_clear_dynamic_css_cache',                          // When Avada theme purge its own cache
			'switch_theme',                                           // When user changes the theme
			'customize_save_after',                                   // Edit theme
			'permalink_structure_changed',                            // When permalink structure is update
		];

		foreach ( $purge_actions as $action ) {
			add_action( $action, [ $this, 'purge_cache_on_theme_edit' ], PHP_INT_MAX );
		}

		$purge_actions = [
			'deleted_post',                     // Delete a post
			'wp_trash_post',                    // Before a post is sent to the Trash
			'clean_post_cache',                 // After a post’s cache is cleaned
			'edit_post',                        // Edit a post - includes leaving comments
			'delete_attachment',                // Delete an attachment - includes re-uploading
			'elementor/editor/after_save',      // Elementor edit
			'elementor/core/files/clear_cache', // Elementor clear cache
		];

		foreach ( $purge_actions as $action ) {
			add_action( $action, [ $this, 'purge_cache_on_post_edit' ], PHP_INT_MAX, 2 );
		}

		add_action( 'transition_post_status', [ $this, 'purge_cache_when_post_is_published' ], PHP_INT_MAX, 3 );

		// Metabox
		if ( $this->main_instance->get_single_config( 'cf_disable_single_metabox', 0 ) == 0 ) {
			add_action( 'add_meta_boxes', [ $this, 'add_metaboxes' ], PHP_INT_MAX );
			add_action( 'save_post', [ $this, 'swcfpc_cache_mbox_save_values' ], PHP_INT_MAX );
		}

		// Ajax enable page cache
		add_action( 'wp_ajax_swcfpc_enable_page_cache', [ $this, 'ajax_enable_page_cache' ] );

		// Ajax disable page cache
		add_action( 'wp_ajax_swcfpc_disable_page_cache', [ $this, 'ajax_disable_page_cache' ] );

		// Add wp_redirect filter to adding cache buster for logged in users
		add_filter( 'wp_redirect', [ $this, 'wp_redirect_filter' ], PHP_INT_MAX, 2 );
	}


	function wp_rocket_disable_page_cache() {

		// Disable page caching in WP Rocket
		if ( $this->is_cache_enabled() ) {
			// Prevent WP Rocket from writing to the advanced-cache.php file
			add_filter( 'rocket_generate_advanced_cache_file', '__return_false', PHP_INT_MAX );

			// Disable WP Rocket mandatory cookies
			add_filter( 'rocket_cache_mandatory_cookies', '__return_empty_array', PHP_INT_MAX );

			// Prevent WP Rocket from changing the WP_CACHE constant
			add_filter( 'rocket_set_wp_cache_constant', '__return_false', PHP_INT_MAX );

			// Prevent WP Rocket from writing to the htaccess file
			add_filter( 'rocket_disable_htaccess', '__return_false', PHP_INT_MAX );

			// Disable other WP Rocket stuffs that are not needed and handelled by this plugin
			add_filter( 'rocket_display_input_varnish_auto_purge', '__return_false', PHP_INT_MAX );
			add_filter( 'do_rocket_generate_caching_files', '__return_false', PHP_INT_MAX );
		}
	}


	function get_cache_buster() {

		return $this->cache_buster;
	}


	function add_metaboxes() {

		$allowed_post_types = apply_filters( 'swcfpc_bypass_cache_metabox_post_types', [ 'post', 'page' ] );

		add_meta_box(
			'swcfpc_cache_mbox',
			__( 'Cloudflare Page Cache Settings', 'wp-cloudflare-page-cache' ),
			[ $this, 'swcfpc_cache_mbox_callback' ],
			$allowed_post_types,
			'side'
		);
	}


	function swcfpc_cache_mbox_callback( $post ) {

		$bypass_cache = (int) get_post_meta( $post->ID, 'swcfpc_bypass_cache', true );

		?>

	<label for="swcfpc_bypass_cache"><?php _e( 'Bypass the cache for this page', 'wp-cloudflare-page-cache' ); ?></label>
	<select name="swcfpc_bypass_cache">
	  <option value="0" 
		<?php 
		if ( $bypass_cache == 0 ) {
			echo 'selected';} 
		?>
		><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></option>
	  <option value="1" 
		<?php 
		if ( $bypass_cache == 1 ) {
			echo 'selected';} 
		?>
		><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></option>
	</select>

		<?php

	}


	function swcfpc_cache_mbox_save_values( $post_id ) {

		if ( array_key_exists( 'swcfpc_bypass_cache', $_POST ) ) {
			update_post_meta( $post_id, 'swcfpc_bypass_cache', $_POST['swcfpc_bypass_cache'] );
		}
	}


	/*
	function force_bypass_for_logged_in_users() {

		if( !function_exists('is_user_logged_in') ) {
			include_once( ABSPATH . 'wp-includes/pluggable.php' );
		}

		if ( is_user_logged_in() && $this->is_cache_enabled() ) {
			add_action( 'wp_footer', array( $this, 'inject_cache_buster_js_code' ), 100 );
			add_action( 'admin_footer', array( $this, 'inject_cache_buster_js_code' ), 100 );
		}

	}
	*/


	function redirect_301_real_url() {

		// For non logged-in users, only redirect when the request URL is not from a CRON job
		if ( ! is_user_logged_in() && ( isset( $_GET['swcfpc-preloader'] ) || isset( $_GET['swcfpc-purge-all'] ) ) ) {
			return;
		}

		// For non CRON job URLs, we will redirect
		if ( ! is_user_logged_in() && ! empty( $_SERVER['QUERY_STRING'] ) ) {
			if ( strlen( $_SERVER['QUERY_STRING'] ) > 0 && strpos( $_SERVER['QUERY_STRING'], $this->get_cache_buster() ) !== false ) {

				// Build the full URL
				$parts       = parse_url( home_url() );
				$current_uri = "{$parts['scheme']}://{$parts['host']}" . add_query_arg( null, null );

				// Strip out the cache buster
				$parsed       = parse_url( $current_uri );
				$query_string = $parsed['query'];

				parse_str( $query_string, $params );

				unset( $params[ $this->get_cache_buster() ] );
				$query_string = http_build_query( $params );

				// Rebuild the full URL without the cache buster
				$current_uri = "{$parts['scheme']}://{$parts['host']}";

				if ( isset( $parsed['path'] ) ) {
					$current_uri .= $parsed['path'];
				}

				if ( strlen( $query_string ) > 0 ) {
					$current_uri .= "?{$query_string}";
				}

				// SEO redirect
				wp_redirect( $current_uri, 301 );
				die();
			}
		}
	}


	function setup_response_headers_filter( $headers ) {

		if ( ! isset( $headers['X-WP-CF-Super-Cache'] ) ) {

			$this->modules = $this->main_instance->get_modules();

			if ( ! $this->is_cache_enabled() ) {
				$this->modules['fallback_cache']->fallback_cache_disable();
				$this->modules['html_cache']->do_not_cache_current_page();
				$headers['X-WP-CF-Super-Cache'] = 'disabled';
			} elseif ( $this->is_url_to_bypass() || $this->can_i_bypass_cache() ) {

				$this->modules['fallback_cache']->fallback_cache_disable();
				$this->modules['html_cache']->do_not_cache_current_page();

				$headers['Cache-Control']                     = 'no-store, no-cache, must-revalidate, max-age=0';
				$headers['X-WP-CF-Super-Cache-Cache-Control'] = 'no-store, no-cache, must-revalidate, max-age=0';
				$headers['X-WP-CF-Super-Cache']               = 'no-cache';
				$headers['Pragma']                            = 'no-cache';
				$headers['Expires']                           = gmdate( 'D, d M Y H:i:s \G\M\T', time() );
			} else {

				$this->modules['fallback_cache']->fallback_cache_enable();
				$this->modules['html_cache']->cache_current_page();

				$headers['Cache-Control']                      = $this->get_cache_control_value(); // Used by Cloudflare
				$headers['X-WP-CF-Super-Cache-Cache-Control']  = $this->get_cache_control_value(); // Used by all
				$headers['X-WP-CF-Super-Cache-Cookies-Bypass'] = $this->get_cookies_to_bypass_in_worker_mode(); // Used by CF worker
				$headers['X-WP-CF-Super-Cache-Active']         = '1'; // Used by CF worker
				$headers['X-WP-CF-Super-Cache']                = 'cache';
			}
		}

		return $headers;
	}


	function setup_response_headers_backend() {

		$this->modules = $this->main_instance->get_modules();

		if ( is_admin() ) {

			$this->modules['fallback_cache']->fallback_cache_disable();
			$this->modules['html_cache']->do_not_cache_current_page();

			if ( ! $this->is_cache_enabled() ) {

				add_filter(
					'nocache_headers',
					function () {

						return [
							'X-WP-CF-Super-Cache' => 'disabled',
						];
					},
					PHP_INT_MAX
				);
			} else {

				add_filter(
					'nocache_headers',
					function () {

						return [
							'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
							'X-WP-CF-Super-Cache-Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
							'X-WP-CF-Super-Cache' => 'no-cache',
							'Pragma'              => 'no-cache',
							'Expires'             => gmdate( 'D, d M Y H:i:s \G\M\T', time() ),
						];
					},
					PHP_INT_MAX
				);
			}

			return;
		}

		if ( ! $this->is_cache_enabled() ) {

			$this->modules['fallback_cache']->fallback_cache_disable();
			$this->modules['html_cache']->do_not_cache_current_page();

			add_filter(
				'nocache_headers',
				function () {

					return [
						'X-WP-CF-Super-Cache' => 'disabled',
					];
				},
				PHP_INT_MAX
			);
		} elseif ( $this->is_url_to_bypass() || $this->can_i_bypass_cache() ) {

			$this->modules['fallback_cache']->fallback_cache_disable();
			$this->modules['html_cache']->do_not_cache_current_page();

			add_filter(
				'nocache_headers',
				function () {

					return [
						'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
						'X-WP-CF-Super-Cache-Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
						'X-WP-CF-Super-Cache' => 'no-cache',
						'Pragma'              => 'no-cache',
						'Expires'             => gmdate( 'D, d M Y H:i:s \G\M\T', time() ),
					];
				},
				PHP_INT_MAX
			);
		} else {

			$this->modules['fallback_cache']->fallback_cache_enable();
			$this->modules['html_cache']->cache_current_page();

			add_filter(
				'nocache_headers',
				function () {

					return [
						'Cache-Control'              => $this->get_cache_control_value(), // Used by Cloudflare
						'X-WP-CF-Super-Cache-Cache-Control' => $this->get_cache_control_value(), // Used by all
						'X-WP-CF-Super-Cache-Cookies-Bypass' => $this->get_cookies_to_bypass_in_worker_mode(), // Used by CF worker
						'X-WP-CF-Super-Cache-Active' => '1', // Used by CF Worker
						'X-WP-CF-Super-Cache'        => 'cache',
					];
				},
				PHP_INT_MAX
			);
		}
	}


	function bypass_cache_on_init() {

		if ( is_admin() ) {
			return;
		}

		$this->modules = $this->main_instance->get_modules();

		if ( ! $this->is_cache_enabled() ) {
			header( 'X-WP-CF-Super-Cache: disabled' );
			$this->modules['fallback_cache']->fallback_cache_disable();
			$this->modules['html_cache']->do_not_cache_current_page();
			return;
		}

		if ( $this->skip_cache ) {
			return;
		}

		header_remove( 'Pragma' );
		header_remove( 'Expires' );
		header_remove( 'Cache-Control' );

		if ( $this->is_url_to_bypass() ) {
			header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
			header( 'Pragma: no-cache' );
			header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() ) );
			header( 'X-WP-CF-Super-Cache: no-cache' );
			header( 'X-WP-CF-Super-Cache-Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
			$this->skip_cache = true;
			$this->modules['fallback_cache']->fallback_cache_disable();
			$this->modules['html_cache']->do_not_cache_current_page();
			return;
		}

		if ( $this->is_cache_enabled() ) {
			$this->modules['fallback_cache']->fallback_cache_enable();
			$this->modules['html_cache']->cache_current_page();
		}
	}


	function apply_cache() {

		if ( is_admin() ) {
			return;
		}

		$this->modules = $this->main_instance->get_modules();

		if ( ! $this->is_cache_enabled() ) {
			header( 'X-WP-CF-Super-Cache: disabled' );
			header( 'X-WP-CF-Super-Cache-Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
			$this->modules['fallback_cache']->fallback_cache_disable();
			$this->modules['html_cache']->do_not_cache_current_page();
			return;
		}

		if ( $this->skip_cache ) {
			return;
		}

		if ( $this->can_i_bypass_cache() ) {
			header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
			header( 'Pragma: no-cache' );
			header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() ) );
			header( 'X-WP-CF-Super-Cache: no-cache' );
			header( 'X-WP-CF-Super-Cache-Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
			$this->modules['fallback_cache']->fallback_cache_disable();
			$this->modules['html_cache']->do_not_cache_current_page();
			return;
		}

		if ( $this->main_instance->get_single_config( 'cf_strip_cookies', 0 ) > 0 ) {
			header_remove( 'Set-Cookie' );
		}

		header_remove( 'Pragma' );
		header_remove( 'Expires' );
		header_remove( 'Cache-Control' );
		header( 'Cache-Control: ' . $this->get_cache_control_value() );
		header( 'X-WP-CF-Super-Cache: cache' );
		header( 'X-WP-CF-Super-Cache-Active: 1' );
		header( 'X-WP-CF-Super-Cache-Cache-Control: ' . $this->get_cache_control_value() );
		header( 'X-WP-CF-Super-Cache-Cookies-Bypass: ' . $this->get_cookies_to_bypass_in_worker_mode() );

		if ( $this->is_cache_enabled() ) {
			$this->modules['fallback_cache']->fallback_cache_enable();
			$this->modules['html_cache']->cache_current_page();
		}
	}


	function purge_all( $disable_preloader = false, $queue_mode = true, $force_purge_everything = false ) {

		$this->modules = $this->main_instance->get_modules();
		$error         = '';

		if ( $queue_mode && $this->main_instance->get_single_config( 'cf_disable_cache_purging_queue', 0 ) == 0 ) {

			$this->purge_cache_queue_write( [], true );
		} else {

			// Avoid to send multiple purge requests for the same session
			if ( $this->purge_all_already_done ) {
				return true;
			}

			if ( $force_purge_everything == false && $this->main_instance->get_single_config( 'cf_purge_only_html', 0 ) > 0 ) {

				$timestamp         = time();
				$cached_html_pages = $this->modules['html_cache']->get_cached_urls_by_timestamp( $timestamp );

				if ( is_array( $cached_html_pages ) ) {

					$cached_html_pages_count = count( $cached_html_pages );

					if ( $cached_html_pages_count > 0 ) {

						$this->modules['html_cache']->delete_cached_urls_by_timestamp( $timestamp );

						if ( ! $this->modules['cloudflare']->purge_cache_urls( $cached_html_pages, $error ) ) {
							$this->modules['logs']->add_log( 'cache_controller::purge_all', "Unable to purge some URLs from Cloudflare due to error: {$error}" );
							return false;
						}
					} else {
						$this->modules['logs']->add_log( 'cache_controller::purge_all', 'There are no HTML pages to purge' );
					}
				}
			} else {

				if ( $this->modules['cloudflare']->is_enabled() && ! $this->modules['cloudflare']->purge_cache( $error ) ) {
					$this->modules['logs']->add_log( 'cache_controller::purge_all', "Unable to purge the whole Cloudflare cache due to error: {$error}" );
					return false;
				}

				if ( $this->main_instance->get_single_config( 'cf_purge_only_html', 0 ) > 0 ) {
					$this->modules['html_cache']->delete_all_cached_urls();
				}
			}

			if ( $this->main_instance->get_single_config( 'cf_varnish_support', 0 ) > 0 && $this->main_instance->get_single_config( 'cf_varnish_auto_purge', 0 ) > 0 ) {
				$this->modules['varnish']->purge_whole_cache( $error );
			}

			if ( $this->main_instance->get_single_config( 'cf_fallback_cache', 0 ) > 0 && $this->main_instance->get_single_config( 'cf_fallback_cache_auto_purge', 0 ) > 0 ) {
				$this->modules['fallback_cache']->fallback_cache_purge_all();
			}

			if ( $this->main_instance->get_single_config( 'cf_opcache_purge_on_flush', 0 ) > 0 ) {
				$this->purge_opcache();
			}

			if ( $this->main_instance->get_single_config( 'cf_object_cache_purge_on_flush', 0 ) > 0 ) {
				$this->purge_object_cache();
			}

			if ( $this->main_instance->get_single_config( 'cf_wpengine_purge_on_flush', 0 ) > 0 ) {
				$this->purge_wpengine_cache();
			}

			if ( $this->main_instance->get_single_config( 'cf_spinupwp_purge_on_flush', 0 ) > 0 ) {
				$this->purge_spinupwp_cache();
			}

			if ( $this->main_instance->get_single_config( 'cf_kinsta_purge_on_flush', 0 ) > 0 ) {
				$this->purge_kinsta_cache();
			}

			if ( $this->main_instance->get_single_config( 'cf_siteground_purge_on_flush', 0 ) > 0 ) {
				$this->purge_siteground_cache();
			}

			if ( $this->main_instance->get_single_config( 'cf_purge_only_html', 0 ) == 0 || $force_purge_everything == true ) {
				$this->modules['logs']->add_log( 'cache_controller::purge_all', 'Purged whole Cloudflare cache' );
			} else {

				if ( ! is_array( $cached_html_pages ) || ! $cached_html_pages_count ) {
					$this->modules['logs']->add_log( 'cache_controller::purge_all', 'There are no HTML pages to purge' );
				} else {

					if ( $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
						$this->modules['logs']->add_log( 'cache_controller::purge_all', "Purged only {$cached_html_pages_count} HTML pages from Cloudflare" );
						$this->modules['logs']->add_log( 'cache_controller::purge_all', 'Pages purged ' . print_r( $cached_html_pages, true ) );
					} else {
						$this->modules['logs']->add_log( 'cache_controller::purge_all', "Purged only {$cached_html_pages_count} HTML pages from Cloudflare" );
					}
				}
			}

			if ( $disable_preloader === false && $this->main_instance->get_single_config( 'cf_preloader', 1 ) > 0 && $this->main_instance->get_single_config( 'cf_preloader_start_on_purge', 0 ) > 0 ) {
				$this->start_preloader_for_all_urls();
			}

			do_action( 'swcfpc_purge_all' );

			// Reset timestamp for Auto prefetch URLs in viewport option
			if ( $this->main_instance->get_single_config( 'cf_prefetch_urls_viewport', 0 ) > 0 ) {
				$this->generate_new_prefetch_urls_timestamp();
			}

			$this->purge_all_already_done = true;
		}

		return true;
	}


	function purge_urls( $urls, $queue_mode = true ) {

		if ( ! is_array( $urls ) ) {
			return false;
		}

		$this->modules = $this->main_instance->get_modules();
		$error         = '';

		// Strip out external links or invalid URLs
		foreach ( $urls as $array_index => $single_url ) {

			if ( $this->is_external_link( $single_url ) || substr( strtolower( $single_url ), 0, 4 ) != 'http' ) {
				unset( $urls[ $array_index ] );
			}
		}

		if ( $queue_mode && $this->main_instance->get_single_config( 'cf_disable_cache_purging_queue', 0 ) == 0 ) {

			$this->purge_cache_queue_write( $urls );
		} else {

			$count_urls = count( $urls );

			if ( $this->modules['cloudflare']->is_enabled() && ! $this->modules['cloudflare']->purge_cache_urls( $urls, $error ) ) {
				$this->modules['logs']->add_log( 'cache_controller::purge_urls', "Unable to purge some URLs from Cloudflare due to error: {$error}" );
				return false;
			}

			if ( $this->main_instance->get_single_config( 'cf_varnish_support', 0 ) > 0 && $this->main_instance->get_single_config( 'cf_varnish_auto_purge', 0 ) > 0 ) {
				$this->modules['varnish']->purge_urls( $urls );
			}

			if ( $this->main_instance->get_single_config( 'cf_fallback_cache', 0 ) > 0 && $this->main_instance->get_single_config( 'cf_fallback_cache_auto_purge', 0 ) > 0 ) {
				$this->modules['fallback_cache']->fallback_cache_purge_urls( $urls );
			}

			if ( $this->main_instance->get_single_config( 'cf_purge_only_html', 0 ) > 0 ) {
				$this->modules['html_cache']->delete_cached_urls_by_urls_list( $urls );
			}

			if ( $this->main_instance->get_single_config( 'cf_opcache_purge_on_flush', 0 ) > 0 ) {
				$this->purge_opcache();
			}

			if ( $this->main_instance->get_single_config( 'cf_object_cache_purge_on_flush', 0 ) > 0 ) {
				$this->purge_object_cache();
			}

			if ( $this->main_instance->get_single_config( 'cf_wpengine_purge_on_flush', 0 ) > 0 ) {
				$this->purge_wpengine_cache();
			}

			if ( $this->main_instance->get_single_config( 'cf_spinupwp_purge_on_flush', 0 ) > 0 ) {

				if ( $count_urls > 1 ) {
					$this->purge_spinupwp_cache();
				} else {
					$this->purge_spinupwp_cache_single_url( $urls[0] );
				}
			}

			if ( $this->main_instance->get_single_config( 'cf_kinsta_purge_on_flush', 0 ) > 0 ) {

				if ( $count_urls > 1 ) {
					$this->purge_kinsta_cache();
				} else {
					$this->purge_kinsta_cache_single_url( $urls[0] );
				}
			}

			if ( $this->main_instance->get_single_config( 'cf_siteground_purge_on_flush', 0 ) > 0 ) {
				$this->purge_siteground_cache();
			}

			if ( $this->main_instance->get_single_config( 'cf_preloader', 1 ) > 0 && $this->main_instance->get_single_config( 'cf_preloader_start_on_purge', 0 ) > 0 ) {
				$this->start_cache_preloader_for_specific_urls( $urls );
			}

			// $this->unlock_cache_purge();

			$this->modules['logs']->add_log( 'cache_controller::purge_urls', 'Purged specific URLs from Cloudflare cache' );

			do_action( 'swcfpc_purge_urls', $urls );

			// Reset timestamp for Auto prefetch URLs in viewport option
			if ( $this->main_instance->get_single_config( 'cf_prefetch_urls_viewport', 0 ) > 0 ) {
				$this->generate_new_prefetch_urls_timestamp();
			}
		}

		return true;
	}


	function cronjob_purge_cache() {

		if ( $this->is_cache_enabled() && isset( $_GET[ $this->cache_buster ] ) && isset( $_GET['swcfpc-purge-all'] ) && $_GET['swcfpc-sec-key'] == $this->main_instance->get_single_config( 'cf_purge_url_secret_key', wp_generate_password( 20, false, false ) ) ) {

			$this->modules = $this->main_instance->get_modules();
			$this->purge_all( false, false );
			$this->modules['logs']->add_log( 'cache_controller::cronjob_purge_cache', 'Cache purging complete' );

			if ( ! headers_sent() ) {
				nocache_headers();
			}

			die( 'Cache purged' );
		}
	}


	function cronjob_preloader() {

		if ( isset( $_GET[ $this->cache_buster ] ) && isset( $_GET['swcfpc-preloader'] ) && $_GET['swcfpc-sec-key'] == $this->main_instance->get_single_config( 'cf_preloader_url_secret_key', wp_generate_password( 20, false, false ) ) && $this->main_instance->get_single_config( 'cf_preloader', 1 ) > 0 ) {

			$this->start_preloader_for_all_urls();
			$this->modules = $this->main_instance->get_modules();
			$this->modules['logs']->add_log( 'cache_controller::cronjob_preloader', 'Preloader started' );

			if ( ! headers_sent() ) {
				nocache_headers();
			}

			die( 'Preloader started' );
		}
	}


	function purge_cache_when_comment_is_approved( $new_status, $old_status, $comment ) {

		if ( $this->main_instance->get_single_config( 'cf_auto_purge_on_comments', 0 ) > 0 && $this->is_cache_enabled() ) {

			if ( $old_status != $new_status && $new_status == 'approved' ) {

				$current_action = function_exists( 'current_action' ) ? current_action() : '';

				$this->modules = $this->main_instance->get_modules();
				$urls          = [];

				$urls[] = get_permalink( $comment->comment_post_ID );

				$this->purge_urls( $urls );

				$this->modules['logs']->add_log( 'cache_controller::purge_cache_when_comment_is_approved', "Purge Cloudflare cache for only post {$comment->comment_post_ID} - Fired action: {$current_action}" );
			}
		}
	}


	function purge_cache_when_new_comment_is_added( $comment_ID, $comment_approved, $commentdata ) {

		if ( $this->main_instance->get_single_config( 'cf_auto_purge_on_comments', 0 ) > 0 && $this->is_cache_enabled() ) {

			if ( isset( $commentdata['comment_post_ID'] ) ) {

				$current_action = function_exists( 'current_action' ) ? current_action() : '';

				$this->modules = $this->main_instance->get_modules();

				$error = '';
				$urls  = [];

				$urls[] = get_permalink( $commentdata['comment_post_ID'] );

				$this->purge_urls( $urls );

				$this->modules['logs']->add_log( 'cache_controller::purge_cache_when_new_comment_is_added', "Purge Cloudflare cache for only post {$commentdata['comment_post_ID']} - Fired action: {$current_action}" );
			}
		}
	}


	function purge_cache_when_comment_is_deleted( $comment_ID ) {

		if ( $this->main_instance->get_single_config( 'cf_auto_purge_on_comments', 0 ) > 0 && $this->is_cache_enabled() ) {

			$current_action = function_exists( 'current_action' ) ? current_action() : '';

			$this->modules = $this->main_instance->get_modules();
			$urls          = [];

			$comment = get_comment( $comment_ID );
			$urls[]  = get_permalink( $comment->comment_post_ID );

			$this->purge_urls( $urls );

			$this->modules['logs']->add_log( 'cache_controller::purge_cache_when_comment_is_deleted', "Purge Cloudflare cache for only post {$comment->comment_post_ID} - Fired action: {$current_action}" );
		}
	}


	function purge_cache_when_post_is_published( $new_status, $old_status, $post ) {

		if ( ( $this->main_instance->get_single_config( 'cf_auto_purge', 0 ) > 0 || $this->main_instance->get_single_config( 'cf_auto_purge_all', 0 ) > 0 ) && $this->is_cache_enabled() ) {

			if ( in_array( $old_status, [ 'future', 'draft', 'pending' ] ) && in_array( $new_status, [ 'publish', 'private' ] ) ) {

				$current_action = function_exists( 'current_action' ) ? current_action() : '';

				$this->modules = $this->main_instance->get_modules();

				if ( $this->main_instance->get_single_config( 'cf_auto_purge_all', 0 ) > 0 ) {

					$this->purge_all();
					$this->modules['logs']->add_log( 'cache_controller::purge_cache_when_post_is_published', "Purge whole Cloudflare cache (fired action: {$current_action}" );
				} else {

					$urls = $this->get_post_related_links( $post->ID );

					$this->purge_urls( $urls );
					$this->modules['logs']->add_log( 'cache_controller::purge_cache_when_post_is_published', "Purge Cloudflare cache for only post id {$post->ID} and related contents - Fired action: {$current_action}" );
				}
			}
		}
	}


	function purge_cache_on_post_edit( $postId ) {

		static $done = [];

		if ( isset( $done[ $postId ] ) ) {
			return;
		}

		// Do not run this on the WordPress Nav Menu Pages
		global $pagenow;
		if ( $pagenow === 'nav-menus.php' ) {
			return;
		}

		if ( ( $this->main_instance->get_single_config( 'cf_auto_purge', 0 ) > 0 || $this->main_instance->get_single_config( 'cf_auto_purge_all', 0 ) > 0 ) && $this->is_cache_enabled() ) {

			$current_action = function_exists( 'current_action' ) ? current_action() : '';

			$this->modules = $this->main_instance->get_modules();

			$error = '';

			$validPostStatus = [ 'publish', 'trash', 'private' ];
			$thisPostStatus  = get_post_status( $postId );

			if ( get_permalink( $postId ) != true || ! in_array( $thisPostStatus, $validPostStatus ) ) {
				return;
			}

			if ( is_int( wp_is_post_autosave( $postId ) ) || is_int( wp_is_post_revision( $postId ) ) ) {
				return;
			}

			if ( $this->main_instance->get_single_config( 'cf_auto_purge_all', 0 ) > 0 ) {
				$this->purge_all();
				return;
			}

			$savedPost = get_post( $postId );

			if ( is_a( $savedPost, 'WP_Post' ) == false ) {
				return;
			}

			$urls = $this->get_post_related_links( $postId );

			$this->purge_urls( $urls );
			$this->modules['logs']->add_log( 'cache_controller::purge_cache_on_post_edit', "Purge Cloudflare cache for only post id {$postId} and related contents - Fired action: {$current_action}" );

			$done[ $postId ] = true;
		}
	}


	function purge_cache_on_theme_edit() {

		if ( ( $this->main_instance->get_single_config( 'cf_auto_purge', 0 ) > 0 || $this->main_instance->get_single_config( 'cf_auto_purge_all', 0 ) > 0 ) && $this->is_cache_enabled() ) {

			$current_action = function_exists( 'current_action' ) ? current_action() : '';

			$this->modules = $this->main_instance->get_modules();

			$this->purge_all();
			$this->modules['logs']->add_log( 'cache_controller::purge_cache_on_theme_edit', "Purge whole Cloudflare cache - Fired action: {$current_action}" );
		}
	}


	function get_post_related_links( $postId ) {

		$this->modules = $this->main_instance->get_modules();

		$listofurls = apply_filters( 'swcfpc_post_related_url_init', __return_empty_array(), $postId );
		$postType   = get_post_type( $postId );

		// Post URL
		array_push( $listofurls, get_permalink( $postId ) );

		// Purge taxonomies terms URLs
		$postTypeTaxonomies = get_object_taxonomies( $postType );

		foreach ( $postTypeTaxonomies as $taxonomy ) {

			if ( is_object( $taxonomy ) && ( $taxonomy->public == false || $taxonomy->rewrite == false ) ) {
				continue;
			}

			$terms = get_the_terms( $postId, $taxonomy );

			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				continue;
			}

			foreach ( $terms as $term ) {

				$termLink = get_term_link( $term );

				if ( ! is_wp_error( $termLink ) ) {

					array_push( $listofurls, $termLink );

					if ( $this->main_instance->get_single_config( 'cf_post_per_page', 0 ) > 0 ) {

						// Thanks to Davide Prevosto for the suggest
						$term_count   = $term->count;
						$pages_number = ceil( $term_count / $this->main_instance->get_single_config( 'cf_post_per_page', 0 ) );
						$max_pages    = $pages_number > 10 ? 10 : $pages_number; // Purge max 10 pages

						for ( $i = 2; $i <= $max_pages; $i++ ) {
							$paginated_url = "{$termLink}page/" . user_trailingslashit( $i );
							array_push( $listofurls, $paginated_url );
						}
					}
				}
			}
		}

		// Author URL
		array_push(
			$listofurls,
			get_author_posts_url( get_post_field( 'post_author', $postId ) ),
			get_author_feed_link( get_post_field( 'post_author', $postId ) )
		);

		// Archives and their feeds
		if ( get_post_type_archive_link( $postType ) == true ) {
			array_push(
				$listofurls,
				get_post_type_archive_link( $postType ),
				get_post_type_archive_feed_link( $postType )
			);
		}

		// Also clean URL for trashed post.
		if ( get_post_status( $postId ) == 'trash' ) {
			$trashPost = get_permalink( $postId );
			$trashPost = str_replace( '__trashed', '', $trashPost );
			array_push( $listofurls, $trashPost, "{$trashPost}feed/" );
		}

		// Feeds
		/*
		array_push(
			$listofurls,
			get_bloginfo_rss('rdf_url'),
			get_bloginfo_rss('rss_url'),
			get_bloginfo_rss('rss2_url'),
			get_bloginfo_rss('atom_url'),
			get_bloginfo_rss('comments_rss2_url'),
			get_post_comments_feed_link($postId)
		);
		*/

		// Purge the home page as well if SWCFPC_HOME_PAGE_SHOWS_POSTS set to true
		if ( defined( 'SWCFPC_HOME_PAGE_SHOWS_POSTS' ) && SWCFPC_HOME_PAGE_SHOWS_POSTS === true ) {
			array_push( $listofurls, home_url( '/' ) );
		}

		$pageLink = get_permalink( get_option( 'page_for_posts' ) );
		if ( is_string( $pageLink ) && ! empty( $pageLink ) && get_option( 'show_on_front' ) == 'page' ) {
			array_push( $listofurls, $pageLink );
		}

		// Purge https and http URLs
		/*
		if (function_exists('force_ssl_admin') && force_ssl_admin()) {
			$listofurls = array_merge($listofurls, str_replace('https://', 'http://', $listofurls));
		} elseif (!is_ssl() && function_exists('force_ssl_content') && force_ssl_content()) {
			$listofurls = array_merge($listofurls, str_replace('http://', 'https://', $listofurls));
		}
		*/

		return $listofurls;
	}


	function reset_all( $keep_settings = false ) {

		$this->modules = $this->main_instance->get_modules();
		$error         = '';

		// Purge all caches and prevent preloader to start
		$this->purge_all( true, false, true );

		// Reset old browser cache TTL
		$this->modules['cloudflare']->change_browser_cache_ttl( $this->main_instance->get_single_config( 'cf_old_bc_ttl', 0 ), $error );

		// Delete worker and route
		if ( $this->main_instance->get_single_config( 'cf_woker_enabled', 0 ) > 0 ) {

			$this->modules['cloudflare']->worker_delete( $error );

			if ( $this->main_instance->get_single_config( 'cf_woker_route_id', '' ) != '' ) {
				$this->modules['cloudflare']->worker_route_delete( $error );
			}
		}

		// Delete the page rule
		if ( $this->main_instance->get_single_config( 'cf_page_rule_id', '' ) != '' ) {
			$this->modules['cloudflare']->delete_page_rule( $this->main_instance->get_single_config( 'cf_page_rule_id', '' ), $error );
		}

		// Delete additional page rule if exists
		if ( $this->main_instance->get_single_config( 'cf_bypass_backend_page_rule_id', '' ) != '' ) {
			$this->modules['cloudflare']->delete_page_rule( $this->main_instance->get_single_config( 'cf_bypass_backend_page_rule_id', '' ), $error );
		}

		// Delete Cache Rule
		$this->modules['cloudflare']->delete_cache_rule();
		$this->main_instance->set_single_config( 'cf_cache_settings_ruleset_id', '' );
		$this->main_instance->set_single_config( 'cf_cache_settings_ruleset_rule_id', '' );

		// Disable fallback cache
		if ( defined( 'SWCFPC_ADVANCED_CACHE' ) ) {
			$this->modules['fallback_cache']->fallback_cache_advanced_cache_disable();
		}

		// Delete all cached HTML pages temp files
		$this->modules['html_cache']->delete_all_cached_urls();

		// Restore default plugin config
		if ( $keep_settings == false ) {
			$this->main_instance->set_config( $this->main_instance->get_default_config() );
			$this->main_instance->update_config();
		} else {
			$this->main_instance->set_single_config( 'cf_cache_enabled', 0 );
			$this->main_instance->update_config();
		}

		// Delete all htaccess rules
		$this->reset_htaccess();

		// Unschedule purge cache cron
		$timestamp = wp_next_scheduled( 'swcfpc_cache_purge_cron' );

		if ( $timestamp !== false ) {
			wp_unschedule_event( $timestamp, 'swcfpc_cache_purge_cron' );
			wp_clear_scheduled_hook( 'swcfpc_cache_purge_cron' );
		}

		// Reset log
		$this->modules['logs']->reset_log();
		$this->modules['logs']->add_log( 'cache_controller::reset_all', 'Reset complete' );
	}


	function wp_redirect_filter( $location, $status ) {

		if ( $this->remove_cache_buster() ) {
			return $location;
		}

		if ( apply_filters( 'swcfpc_bypass_redirect_cache_buster', false, $location ) === true ) {
			return $location;
		}

		if ( ! $this->is_cache_enabled() ) {
			return $location;
		}

		if ( ! is_user_logged_in() ) {
			return $location;
		}

		$this->modules = $this->main_instance->get_modules();

		if ( $this->main_instance->get_single_config( 'cf_woker_enabled', 0 ) > 0 ) {
			return $location;
		}

		if ( version_compare( phpversion(), '8', '>=' ) ) {
			$cache_buster_exists = str_contains( $location, $this->cache_buster );
		} else {
			$cache_buster_exists = strpos( $location, $this->cache_buster );
		}

		if ( $cache_buster_exists == false ) {
			$location = add_query_arg( [ $this->cache_buster => '1' ], $location );
		}

		return $location;
	}


	function inject_cache_buster_js_code() {

		if ( ! $this->is_cache_enabled() ) {
			return;
		}

		if ( $this->remove_cache_buster() ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			return;
		}

		// Make sure we don't add the following script for AMP endpoints as they are gonna be striped out by the AMP system anyway
		if ( ! is_admin() && ( function_exists( 'amp_is_request' ) && amp_is_request() ) || ( function_exists( 'ampforwp_is_amp_endpoint' ) && ampforwp_is_amp_endpoint() ) ) {
			return;
		}

		$this->modules = $this->main_instance->get_modules();

		// Cache buster is disabled in worker mode
		if ( $this->main_instance->get_single_config( 'cf_woker_enabled', 0 ) > 0 ) {
			return;
		}

		$selectors = 'a';

		if ( is_admin() ) {
			$selectors = '#wp-admin-bar-my-sites-list a, #wp-admin-bar-site-name a, #wp-admin-bar-view-site a, #wp-admin-bar-view a, .row-actions a, .preview, #sample-permalink a, #message a, #editor .is-link, #editor .editor-post-preview, #editor .editor-post-permalink__link, .edit-post-post-link__preview-link-container .edit-post-post-link__link';
		}

		?>

	<script id="swcfpc">
	  var swcfpc_adjust_internal_links = function(selectors_txt) {

		const comp = new RegExp(location.host);
		const current_url = window.location.href.split("#")[0];

		[].forEach.call(document.querySelectorAll(selectors_txt), function(el) {

		  if (comp.test(el.href) && !el.href.includes("<?php echo $this->cache_buster; ?>=1") && !el.href.startsWith("#") && !el.href.startsWith(current_url + "#")) {

			if (el.href.indexOf('#') != -1) {

			  const link_split = el.href.split("#");
			  el.href = link_split[0];
			  el.href += (el.href.indexOf('?') != -1 ? "&<?php echo $this->cache_buster; ?>=1" : "?<?php echo $this->cache_buster; ?>=1");
			  el.href += "#" + link_split[1];

			} else {
			  el.href += (el.href.indexOf('?') != -1 ? "&<?php echo $this->cache_buster; ?>=1" : "?<?php echo $this->cache_buster; ?>=1");
			}

		  }

		});

	  }

	  document.addEventListener("DOMContentLoaded", function() {

		swcfpc_adjust_internal_links("<?php echo $selectors; ?>");

	  });

	  window.addEventListener("load", function() {

		swcfpc_adjust_internal_links("<?php echo $selectors; ?>");

	  });

	  setInterval(function() {
		swcfpc_adjust_internal_links("<?php echo $selectors; ?>");
	  }, 3000);


	  // Looking for dynamic link added after clicking on Pusblish/Update button
	  var swcfpc_wordpress_btn_publish = document.querySelector(".editor-post-publish-button__button");

	  if (swcfpc_wordpress_btn_publish !== undefined && swcfpc_wordpress_btn_publish !== null) {

		swcfpc_wordpress_btn_publish.addEventListener('click', function() {

		  var swcfpc_wordpress_edited_post_interval = setInterval(function() {

			var swcfpc_wordpress_edited_post_link = document.querySelector(".components-snackbar__action");

			if (swcfpc_wordpress_edited_post_link !== undefined) {
			  swcfpc_adjust_internal_links(".components-snackbar__action");
			  clearInterval(swcfpc_wordpress_edited_post_link);
			}

		  }, 100);

		}, false);

	  }
	</script>

		<?php

	}


	function generate_new_prefetch_urls_timestamp() {

		$current_timestamp = $this->main_instance->get_single_config( 'cf_prefetch_urls_viewport_timestamp', time() );

		if ( $current_timestamp < time() ) {

			$current_timestamp = time() + 120; // Cache the timestamp for 2 minutes
			$this->main_instance->set_single_config( 'cf_prefetch_urls_viewport_timestamp', $current_timestamp );
			$this->main_instance->update_config();

			$this->modules = $this->main_instance->get_modules();

			if ( $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
				$this->modules['logs']->add_log( 'cache_controller::generate_new_prefetch_urls_timestamp', "New timestamp generated: {$current_timestamp}" );
			}
		}

		return $current_timestamp;
	}


	function prefetch_urls() {

		if ( ! $this->is_cache_enabled() || is_user_logged_in() ) {
			return;
		}

		if ( $this->main_instance->get_single_config( 'cf_prefetch_urls_viewport', 0 ) > 0 ) : 
			?>

	  <script id="swcfpc">
		const swcfpc_prefetch_urls_timestamp_server = '<?php echo $this->main_instance->get_single_config( 'cf_prefetch_urls_viewport_timestamp', time() ); ?>';

		let swcfpc_prefetched_urls = localStorage.getItem("swcfpc_prefetched_urls");
		swcfpc_prefetched_urls = (swcfpc_prefetched_urls) ? JSON.parse(swcfpc_prefetched_urls) : [];

		let swcfpc_prefetch_urls_timestamp_client = localStorage.getItem("swcfpc_prefetch_urls_timestamp_client");

		if (swcfpc_prefetch_urls_timestamp_client == undefined || swcfpc_prefetch_urls_timestamp_client != swcfpc_prefetch_urls_timestamp_server) {
		  swcfpc_prefetch_urls_timestamp_client = swcfpc_prefetch_urls_timestamp_server;
		  swcfpc_prefetched_urls = new Array();
		  localStorage.setItem("swcfpc_prefetched_urls", JSON.stringify(swcfpc_prefetched_urls));
		  localStorage.setItem("swcfpc_prefetch_urls_timestamp_client", swcfpc_prefetch_urls_timestamp_client);
		}

		function swcfpc_element_is_in_viewport(element) {

		  let bounding = element.getBoundingClientRect();

		  if (bounding.top >= 0 && bounding.left >= 0 && bounding.right <= (window.innerWidth || document.documentElement.clientWidth) && bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight))
			return true;

		  return false;

		}

		function swcfpc_prefetch_urls() {

		  let comp = new RegExp(location.host);

		  document.querySelectorAll("a").forEach((item) => {

			if (item.href) {

			  let href = item.href.split("#")[0];

			  if (swcfpc_can_url_be_prefetched(href) && swcfpc_prefetched_urls.includes(href) == false && comp.test(item.href) && swcfpc_element_is_in_viewport(item)) {

				swcfpc_prefetched_urls.push(href);

				//console.log( href );

				let prefetch_element = document.createElement('link');
				prefetch_element.rel = "prefetch";
				prefetch_element.href = href;

				document.getElementsByTagName('body')[0].appendChild(prefetch_element);

			  }

			}

		  })

		  localStorage.setItem("swcfpc_prefetched_urls", JSON.stringify(swcfpc_prefetched_urls));

		}

		window.addEventListener("load", function(event) {
		  swcfpc_prefetch_urls();
		});

		window.addEventListener("scroll", function(event) {
		  swcfpc_prefetch_urls();
		});
	  </script>

	  <?php endif; ?>

		<?php

	}


	function is_url_to_bypass() {

		$this->modules = $this->main_instance->get_modules();

		// Bypass API requests
		if ( $this->main_instance->is_api_request() ) {
			return true;
		}

		// Bypass AMP
		if ( $this->main_instance->get_single_config( 'cf_bypass_amp', 0 ) > 0 && preg_match( '/(\/)((\?amp)|(amp\/))/', $_SERVER['REQUEST_URI'] ) ) {
			return true;
		}

		// Bypass sitemap
		if ( $this->main_instance->get_single_config( 'cf_bypass_sitemap', 0 ) > 0 && strcasecmp( $_SERVER['REQUEST_URI'], '/sitemap_index.xml' ) == 0 || preg_match( '/[a-zA-Z0-9]-sitemap.xml$/', $_SERVER['REQUEST_URI'] ) ) {
			return true;
		}

		// Bypass robots.txt
		if ( $this->main_instance->get_single_config( 'cf_bypass_file_robots', 0 ) > 0 && preg_match( '/^\/robots.txt/', $_SERVER['REQUEST_URI'] ) ) {
			return true;
		}

		// Bypass the cache on excluded URLs
		$excluded_urls = $this->main_instance->get_single_config( 'cf_excluded_urls', [] );

		if ( is_array( $excluded_urls ) && count( $excluded_urls ) > 0 ) {

			$current_url = $_SERVER['REQUEST_URI'];

			if ( isset( $_SERVER['QUERY_STRING'] ) && strlen( $_SERVER['QUERY_STRING'] ) > 0 ) {
				$current_url .= "?{$_SERVER['QUERY_STRING']}";
			}

			foreach ( $excluded_urls as $url_to_exclude ) {

				if ( $this->main_instance->wildcard_match( $url_to_exclude, $current_url ) ) {
					return true;
				}

				/*
				if( fnmatch($url_to_exclude, $current_url, FNM_CASEFOLD) ) {
					return true;
				}
				*/
			}
		}

		if ( isset( $_GET[ $this->cache_buster ] ) || ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			return true;
		}

		if ( in_array( $GLOBALS['pagenow'], [ 'wp-login.php', 'wp-register.php' ] ) ) {
			return true;
		}

		return false;
	}


	function can_i_bypass_cache() {

		global $post;

		$this->modules = $this->main_instance->get_modules();

		// Bypass the cache using filter
		if ( has_filter( 'swcfpc_cache_bypass' ) ) {

			$cache_bypass = apply_filters( 'swcfpc_cache_bypass', false );

			if ( $cache_bypass === true ) {
				return true;
			}
		}

		// Bypass post protected by password
		if ( is_object( $post ) && post_password_required( $post->ID ) !== false ) {
			return true;
		}

		// Bypass single post by metabox
		if ( $this->main_instance->get_single_config( 'cf_disable_single_metabox', 0 ) == 0 && is_object( $post ) && (int) get_post_meta( $post->ID, 'swcfpc_bypass_cache', true ) > 0 ) {
			return true;
		}

		// Bypass requests with query var
		if ( $this->main_instance->get_single_config( 'cf_bypass_query_var', 0 ) > 0 && isset( $_SERVER['QUERY_STRING'] ) && strlen( $_SERVER['QUERY_STRING'] ) > 0 ) {
			return true;
		}

		// Bypass POST requests
		if ( $this->main_instance->get_single_config( 'cf_bypass_post', 0 ) > 0 && isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] == 'POST' ) {
			return true;
		}

		// Bypass AJAX requests
		if ( $this->main_instance->get_single_config( 'cf_bypass_ajax', 0 ) > 0 ) {

			if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
				return true;
			}

			if ( function_exists( 'is_ajax' ) && is_ajax() ) {
				return true;
			}

			if ( ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return true;
			}
		}

		// Bypass EDD pages
		if ( is_object( $post ) && $this->main_instance->get_single_config( 'cf_bypass_edd_checkout_page', 0 ) > 0 && function_exists( 'edd_get_option' ) && edd_get_option( 'purchase_page', 0 ) == $post->ID ) {
			return true;
		}

		if ( is_object( $post ) && $this->main_instance->get_single_config( 'cf_bypass_edd_success_page', 0 ) > 0 && function_exists( 'edd_get_option' ) && edd_get_option( 'success_page', 0 ) == $post->ID ) {
			return true;
		}

		if ( is_object( $post ) && $this->main_instance->get_single_config( 'cf_bypass_edd_failure_page', 0 ) > 0 && function_exists( 'edd_get_option' ) && edd_get_option( 'failure_page', 0 ) == $post->ID ) {
			return true;
		}

		if ( is_object( $post ) && $this->main_instance->get_single_config( 'cf_bypass_edd_purchase_history_page', 0 ) > 0 && function_exists( 'edd_get_option' ) && edd_get_option( 'purchase_history_page', 0 ) == $post->ID ) {
			return true;
		}

		if ( is_object( $post ) && $this->main_instance->get_single_config( 'cf_bypass_edd_login_redirect_page', 0 ) > 0 && function_exists( 'edd_get_option' ) && edd_get_option( 'login_redirect_page', 0 ) == $post->ID ) {
			return true;
		}

		// Bypass WooCommerce pages
		if ( $this->main_instance->get_single_config( 'cf_bypass_woo_cart_page', 0 ) > 0 && function_exists( 'is_cart' ) && is_cart() ) {
			return true;
		}

		if ( $this->main_instance->get_single_config( 'cf_bypass_woo_account_page', 0 ) > 0 && function_exists( 'is_account' ) && is_account() ) {
			return true;
		}


		if ( $this->main_instance->get_single_config( 'cf_bypass_woo_checkout_page', 0 ) > 0 && function_exists( 'is_checkout' ) && is_checkout() ) {
			return true;
		}


		if ( $this->main_instance->get_single_config( 'cf_bypass_woo_checkout_pay_page', 0 ) > 0 && function_exists( 'is_checkout_pay_page' ) && is_checkout_pay_page() ) {
			return true;
		}


		if ( $this->main_instance->get_single_config( 'cf_bypass_woo_shop_page', 0 ) > 0 && function_exists( 'is_shop' ) && is_shop() ) {
			return true;
		}


		if ( $this->main_instance->get_single_config( 'cf_bypass_woo_product_page', 0 ) > 0 && function_exists( 'is_product' ) && is_product() ) {
			return true;
		}


		if ( $this->main_instance->get_single_config( 'cf_bypass_woo_product_cat_page', 0 ) > 0 && function_exists( 'is_product_category' ) && is_product_category() ) {
			return true;
		}


		if ( $this->main_instance->get_single_config( 'cf_bypass_woo_product_tag_page', 0 ) > 0 && function_exists( 'is_product_tag' ) && is_product_tag() ) {
			return true;
		}


		if ( $this->main_instance->get_single_config( 'cf_bypass_woo_product_tax_page', 0 ) > 0 && function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) {
			return true;
		}


		if ( $this->main_instance->get_single_config( 'cf_bypass_woo_pages', 0 ) > 0 && function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
			return true;
		}


		// Bypass WordPress pages
		if ( $this->main_instance->get_single_config( 'cf_bypass_front_page', 0 ) > 0 && is_front_page() ) {
			return true;
		}


		if ( $this->main_instance->get_single_config( 'cf_bypass_pages', 0 ) > 0 && is_page() ) {
			return true;
		}


		if ( $this->main_instance->get_single_config( 'cf_bypass_home', 0 ) > 0 && is_home() ) {
			return true;
		}


		if ( $this->main_instance->get_single_config( 'cf_bypass_archives', 0 ) > 0 && is_archive() ) {
			return true;
		}


		if ( $this->main_instance->get_single_config( 'cf_bypass_tags', 0 ) > 0 && is_tag() ) {
			return true;
		}


		if ( $this->main_instance->get_single_config( 'cf_bypass_category', 0 ) > 0 && is_category() ) {
			return true;
		}


		if ( $this->main_instance->get_single_config( 'cf_bypass_feeds', 0 ) > 0 && is_feed() ) {
			return true;
		}


		if ( $this->main_instance->get_single_config( 'cf_bypass_search_pages', 0 ) > 0 && is_search() ) {
			return true;
		}


		if ( $this->main_instance->get_single_config( 'cf_bypass_author_pages', 0 ) > 0 && is_author() ) {
			return true;
		}


		if ( $this->main_instance->get_single_config( 'cf_bypass_single_post', 0 ) > 0 && is_single() ) {
			return true;
		}


		if ( $this->main_instance->get_single_config( 'cf_bypass_404', 0 ) > 0 && is_404() ) {
			return true;
		}


		/*
		if( $this->main_instance->get_single_config('cf_bypass_logged_in', 0) > 0 && is_user_logged_in() ) {
			return true;
		}
		*/

		if ( is_user_logged_in() ) {
			return true;
		}


		// Bypass cache if the parameter swcfpc is setted or we are on backend
		if ( isset( $_GET[ $this->cache_buster ] ) || is_admin() ) {
			return true;
		}

		return false;
	}


	function get_cookies_to_bypass_in_worker_mode() {

		$this->modules = $this->main_instance->get_modules();

		$cookies_list = $this->main_instance->get_single_config( 'cf_worker_bypass_cookies', [] );

		if ( ! count( $cookies_list ) ) {
			return 'swfpc-feature-not-enabled';
		}

		return trim( implode( '|', $cookies_list ) );
	}


	function get_cache_control_value() {

		$this->modules = $this->main_instance->get_modules();

		$value = 's-maxage=' . $this->main_instance->get_single_config( 'cf_maxage', 604800 ) . ', max-age=' . $this->main_instance->get_single_config( 'cf_browser_maxage', 60 );

		return $value;
	}


	function is_cache_enabled() {

		$this->modules = $this->main_instance->get_modules();

		if ( $this->main_instance->get_single_config( 'cf_cache_enabled', 0 ) > 0 ) {
			return true;
		}

		return false;
	}

	function remove_cache_buster() {
		$this->modules = $this->main_instance->get_modules();

		if ( $this->main_instance->get_single_config( 'cf_remove_cache_buster', 0 ) > 0 ) {
			return true;
		} else {
			return false;
		}
	}


	function w3tc_hooks() {

		$current_action = function_exists( 'current_action' ) ? current_action() : '';

		$this->modules = $this->main_instance->get_modules();

		if ( $current_action == 'w3tc_flush_minify' ) {
			$this->purge_all( false, true, true );
		} else {
			$this->purge_all();
		}

		$this->modules['logs']->add_log( 'cache_controller::w3tc_hooks', "Purge whole Cloudflare cache (fired action: {$current_action})" );
	}


	function wpo_hooks() {

		if ( did_action( 'wpo_cache_flush' ) === 1 ) {

			$current_action = function_exists( 'current_action' ) ? current_action() : '';

			$this->modules = $this->main_instance->get_modules();

			$this->purge_all();
			$this->modules['logs']->add_log( 'cache_controller::wpo_hooks', "Purge whole Cloudflare cache (fired action: {$current_action})" );
		}
	}


	function wp_performance_hooks() {

		$current_action = function_exists( 'current_action' ) ? current_action() : '';

		$this->modules = $this->main_instance->get_modules();

		$this->purge_all();
		$this->modules['logs']->add_log( 'cache_controller::wp_performance_hooks', "Purge whole Cloudflare cache (fired action: {$current_action})" );
	}


	function wp_rocket_hooks() {
		// Do not run this on the WordPress Nav Menu Pages
		global $pagenow;
		if ( $pagenow === 'nav-menus.php' ) {
			return;
		}

		$current_action = function_exists( 'current_action' ) ? current_action() : '';

		$this->modules = $this->main_instance->get_modules();

		if ( $current_action == 'after_rocket_clean_minify' ) {
			$this->purge_all( false, true, true );
		} else {
			$this->purge_all();
		}

		$this->modules['logs']->add_log( 'cache_controller::wp_rocket_hooks', "Purge whole Cloudflare cache (fired action: {$current_action})" );
	}

	function wp_rocket_after_rocket_clean_post_hook( $post ) {

		static $done = [];

		if ( isset( $done[ $post->ID ] ) ) {
			return;
		}

		$current_action = function_exists( 'current_action' ) ? current_action() : '';
		$this->modules  = $this->main_instance->get_modules();

		if ( is_object( $post ) ) {
			$purged_post_url = get_permalink( $post->ID );

			$this->purge_urls( [ $purged_post_url ] );

			$this->modules['logs']->add_log( 'cache_controller::wp_rocket_after_rocket_clean_post_hook', "Purge Cloudflare cache for only URL {$purged_post_url} - Fired action: {$current_action}" );
		} else {
			$this->modules['logs']->add_log( 'cache_controller::wp_rocket_after_rocket_clean_post_hook', "Unable to Purge Cloudflare cache. Valid post object not received  - Fired action: {$current_action}" );
		}

		$done[ $post->ID ] = true;
	}

	function wp_rocket_selective_url_purge_hooks( $url_to_purge ) {

		$current_action = function_exists( 'current_action' ) ? current_action() : '';

		$this->modules = $this->main_instance->get_modules();

		// If we are receiving only 1 URL then wrap it inside an array else if we are receiving an array of URLs then pass that
		$url_to_purge = is_array( $url_to_purge ) ? $url_to_purge : [ $url_to_purge ];

		$this->purge_urls( $url_to_purge );

		$urls_purged = json_encode( $url_to_purge );

		$this->modules['logs']->add_log( 'cache_controller::wp_rocket_selective_url_purge_hooks', "Purge Cloudflare cache for only URL {$urls_purged} - Fired action: {$current_action}" );
	}


	function litespeed_hooks() {

		$current_action = function_exists( 'current_action' ) ? current_action() : '';

		$this->modules = $this->main_instance->get_modules();

		if ( $current_action == 'litespeed_purged_all_cssjs' || $current_action == 'litespeed_purged_all' ) {
			$this->purge_all( false, true, true );
		} else {
			$this->purge_all();
		}

		$this->modules['logs']->add_log( 'cache_controller::litespeed_hooks', "Purge whole Cloudflare cache (fired action: {$current_action})" );
	}


	function litespeed_single_post_hooks( $post_id ) {

		static $done = [];

		if ( isset( $done[ $post_id ] ) ) {
			return;
		}

		$current_action = function_exists( 'current_action' ) ? current_action() : '';

		$this->modules = $this->main_instance->get_modules();

		$urls   = [];
		$urls[] = get_permalink( $post_id );

		$this->purge_urls( $urls );

		$this->modules['logs']->add_log( 'cache_controller::litespeed_single_post_hooks', "Purge Cloudflare cache for only post {$post_id} - Fired action: {$current_action}" );

		$done[ $post_id ] = true;
	}


	function hummingbird_hooks() {

		$current_action = function_exists( 'current_action' ) ? current_action() : '';

		$this->modules = $this->main_instance->get_modules();

		$this->purge_all();
		$this->modules['logs']->add_log( 'cache_controller::hummingbird_hooks', "Purge whole Cloudflare cache (fired action: {$current_action})" );
	}


	function nginx_helper_purge_all_hooks() {

		$current_action = function_exists( 'current_action' ) ? current_action() : '';

		$this->modules = $this->main_instance->get_modules();

		$this->purge_all();
		$this->modules['logs']->add_log( 'cache_controller::nginx_helper_purge_all_hooks', "Purge whole Cloudflare cache (fired action: {$current_action})" );
	}


	function nginx_helper_purge_single_url_hooks( $url_to_purge ) {

		if ( $this->main_instance->get_single_config( 'cf_nginx_helper_purge_on_cache_flush', 0 ) > 0 ) {

			$current_action = function_exists( 'current_action' ) ? current_action() : '';

			$this->modules = $this->main_instance->get_modules();
			$this->purge_urls( [ $url_to_purge ] );

			$this->modules['logs']->add_log( 'cache_controller::nginx_helper_purge_single_url_hooks', "Purge Cloudflare cache for only URL {$url_to_purge} - Fired action: {$current_action}" );
		}
	}


	function yasr_hooks( $post_id ) {

		static $done = [];

		if ( isset( $done[ $post_id ] ) ) {
			return;
		}

		$current_action = function_exists( 'current_action' ) ? current_action() : '';

		$this->modules = $this->main_instance->get_modules();

		$urls = [];

		$post_id = is_array( $post_id ) ? $post_id['post_id'] : $post_id;

		$urls[] = get_permalink( $post_id );

		$this->purge_urls( $urls );

		$this->modules['logs']->add_log( 'cache_controller::yasr_hooks', "Purge Cloudflare cache for only post {$post_id} - Fired action: {$current_action}" );

		$done[ $post_id ] = true;
	}


	function spl_purge_all() {

		$current_action = function_exists( 'current_action' ) ? current_action() : '';

		$this->modules = $this->main_instance->get_modules();

		if ( $current_action == 'swift_performance_after_clear_all_cache' ) {
			$this->purge_all( false, true, true );
		} else {
			$this->purge_all();
		}

		$this->purge_all();
		$this->modules['logs']->add_log( 'cache_controller::spl_purge_all', "Purge whole Cloudflare cache (fired action: {$current_action})" );
	}


	function spl_purge_single_post( $post_id ) {

		static $done = [];

		if ( isset( $done[ $post_id ] ) ) {
			return;
		}

		if ( $this->main_instance->get_single_config( 'cf_spl_purge_on_flush_single_post', 0 ) > 0 ) {

			$current_action = function_exists( 'current_action' ) ? current_action() : '';

			$this->modules = $this->main_instance->get_modules();

			$urls   = [];
			$urls[] = get_permalink( $post_id );

			$this->purge_urls( $urls );

			$this->modules['logs']->add_log( 'cache_controller::spl_purge_single_post', "Purge Cloudflare cache for only post {$post_id} - Fired action: {$current_action}" );

			$done[ $post_id ] = true;
		}
	}


	function wpacu_hooks() {

		$current_action = function_exists( 'current_action' ) ? current_action() : '';

		$this->modules = $this->main_instance->get_modules();

		$this->purge_all();
		$this->modules['logs']->add_log( 'cache_controller::wpacu_hooks', "Purge whole Cloudflare cache (fired action: {$current_action})" );
	}


	function flying_press_hook() {

		$current_action = function_exists( 'current_action' ) ? current_action() : '';

		$this->modules = $this->main_instance->get_modules();

		$this->purge_all( false, true, true );
		$this->modules['logs']->add_log( 'cache_controller::flying_press_hook', "Purge whole Cloudflare cache (fired action: {$current_action})" );
	}


	function autoptimize_hooks() {

		$current_action = function_exists( 'current_action' ) ? current_action() : '';

		$this->modules = $this->main_instance->get_modules();

		$this->purge_all();
		$this->modules['logs']->add_log( 'cache_controller::autoptimize_hooks', "Purge whole Cloudflare cache (fired action: {$current_action})" );
	}


	function purge_on_plugin_update() {

		$current_action = function_exists( 'current_action' ) ? current_action() : '';

		$this->modules = $this->main_instance->get_modules();

		$this->purge_all( false, true, true );
		$this->modules['logs']->add_log( 'cache_controller::purge_on_plugin_update', "Purge whole Cloudflare cache (fired action: {$current_action})" );
	}


	function edd_purge_cache_on_payment_add() {

		$current_action = function_exists( 'current_action' ) ? current_action() : '';

		$this->modules = $this->main_instance->get_modules();

		$this->purge_all();
		$this->modules['logs']->add_log( 'cache_controller::edd_purge_cache_on_payment_add', "Purge whole Cloudflare cache (fired action: {$current_action})" );
	}


	/*
	function woocommerce_purge_product_page_on_stock_change( $order ) {

		if( $this->main_instance->get_single_config('cf_auto_purge_woo_product_page', 0) > 0 && function_exists('wc_get_order') ) {

			$items = $order->get_items();
			$product_cats_ids = array();

			$this->objects = $this->main_instance->get_modules();
			$urls = array();
			$error = '';

			if( function_exists('wc_get_page_id') ) {
				$urls[] = get_permalink( wc_get_page_id('shop') );
			}

			foreach ( $items as $item ) {

				$product_id = $item->get_product_id();
				//$product_variation_id = $item->get_variation_id();

				$product_cats_ids[] = wc_get_product_cat_ids( $product_id );

				$urls = array_merge( $urls, $this->get_post_related_links( $product_id) );

			}

			$urls = array_unique( $urls );

			// Reduce the multidimensional array to a flat one and get rid of ducplicate product_cat IDS
			$product_cats_ids = call_user_func_array('array_merge', $product_cats_ids);
			$product_cats_ids = array_unique($product_cats_ids);

			foreach ( $product_cats_ids as $category_id ) {
				$urls[] = get_category_link( $category_id );
			}


			$this->objects['cloudflare']->purge_cache_urls( $urls, $error );

			$this->objects['logs']->add_log('cache_controller::woocommerce_purge_product_page_on_stock_change', 'Purge product pages and categories for WooCommerce order' );

		}

	}*/


	function woocommerce_purge_product_page_on_stock_change( $product_id ) {

		if ( function_exists( 'wc_get_order' ) ) {

			$this->modules = $this->main_instance->get_modules();
			$urls          = [];

			// Get shop page URL
			if ( function_exists( 'wc_get_page_id' ) ) {
				$urls[] = get_permalink( wc_get_page_id( 'shop' ) );
			}

			// Get product categories URLs
			$product_cats_ids = wc_get_product_cat_ids( $product_id );

			foreach ( $product_cats_ids as $category_id ) {
				$urls[] = get_category_link( $category_id );
			}

			// GET other related URLs
			$urls = array_merge( $urls, $this->get_post_related_links( $product_id ) );
			$urls = array_unique( $urls );

			$this->purge_urls( $urls );
			$this->modules['logs']->add_log( 'cache_controller::woocommerce_purge_product_page_on_stock_change', 'Purge product pages and categories for WooCommerce order' );
		}
	}


	function woocommerce_purge_scheduled_sales( $product_id_list ) {

		$this->modules = $this->main_instance->get_modules();

		$urls = [];

		if ( is_array( $product_id_list ) && count( $product_id_list ) > 0 ) {

			foreach ( $product_id_list as $product_id ) {

				$single_url = get_permalink( $product_id );

				if ( $single_url !== false ) {
					$urls[] = $single_url;
				}
			}

			if ( count( $urls ) > 0 ) {
				$this->purge_urls( $urls );
			}
		}
	}


	function reset_htaccess() {

		if ( function_exists( 'insert_with_markers' ) ) {
			insert_with_markers( $this->htaccess_path, 'WP Cloudflare Super Page Cache', [] );
		}
	}


	function write_htaccess( &$error_msg ) {

		$this->modules = $this->main_instance->get_modules();

		$htaccess_lines = [];

		if ( $this->main_instance->get_single_config( 'cf_cache_control_htaccess', 0 ) > 0 && $this->is_cache_enabled() && $this->main_instance->get_single_config( 'cf_woker_enabled', 0 ) == 0 ) {

			$htaccess_lines[] = '<IfModule mod_headers.c>';
			// $htaccess_lines[] = 'Header unset Pragma "expr=resp(\'x-wp-cf-super-cache-active\') == \'1\'"';
			// $htaccess_lines[] = 'Header always unset Pragma "expr=resp(\'x-wp-cf-super-cache-active\') == \'1\'"';
			// $htaccess_lines[] = 'Header unset Expires "expr=resp(\'x-wp-cf-super-cache-active\') == \'1\'"';
			// $htaccess_lines[] = 'Header always unset Expires "expr=resp(\'x-wp-cf-super-cache-active\') == \'1\'"';
			// $htaccess_lines[] = 'Header unset Cache-Control "expr=resp(\'x-wp-cf-super-cache-active\') == \'1\'"';
			// $htaccess_lines[] = 'Header always unset Cache-Control "expr=resp(\'x-wp-cf-super-cache-active\') == \'1\'"';
			// $htaccess_lines[] = 'Header always set Cache-Control "' . $this->get_cache_control_value() . '" "expr=resp(\'x-wp-cf-super-cache-active\') == \'1\'"';

			$htaccess_lines[] = 'Header unset Pragma "expr=resp(\'x-wp-cf-super-cache-cache-control\') != \'\'"';
			$htaccess_lines[] = 'Header always unset Pragma "expr=resp(\'x-wp-cf-super-cache-cache-control\') != \'\'"';
			$htaccess_lines[] = 'Header unset Expires "expr=resp(\'x-wp-cf-super-cache-cache-control\') != \'\'"';
			$htaccess_lines[] = 'Header always unset Expires "expr=resp(\'x-wp-cf-super-cache-cache-control\') != \'\'"';
			$htaccess_lines[] = 'Header unset Cache-Control "expr=resp(\'x-wp-cf-super-cache-cache-control\') != \'\'"';
			$htaccess_lines[] = 'Header always unset Cache-Control "expr=resp(\'x-wp-cf-super-cache-cache-control\') != \'\'"';

			// Add a cache-control header with the value of x-wp-cf-super-cache-cache-control response header
			$htaccess_lines[] = 'Header always set Cache-Control "expr=%{resp:x-wp-cf-super-cache-cache-control}" "expr=resp(\'x-wp-cf-super-cache-cache-control\') != \'\'"';

			$htaccess_lines[] = '</IfModule>';
		}

		if ( $this->main_instance->get_single_config( 'cf_strip_cookies', 0 ) > 0 && $this->is_cache_enabled() ) {

			$htaccess_lines[] = '<IfModule mod_headers.c>';
			$htaccess_lines[] = 'Header unset Set-Cookie "expr=resp(\'x-wp-cf-super-cache-active\') == \'1\'"';
			$htaccess_lines[] = 'Header always unset Set-Cookie "expr=resp(\'x-wp-cf-super-cache-active\') == \'1\'"';
			$htaccess_lines[] = '</IfModule>';
		}

		if ( $this->main_instance->get_single_config( 'cf_bypass_sitemap', 0 ) > 0 && $this->is_cache_enabled() ) {

			$htaccess_lines[] = '<IfModule mod_expires.c>';
			$htaccess_lines[] = 'ExpiresActive on';
			$htaccess_lines[] = 'ExpiresByType application/xml "access plus 0 seconds"';
			$htaccess_lines[] = 'ExpiresByType text/xsl "access plus 0 seconds"';
			$htaccess_lines[] = '</IfModule>';

			$htaccess_lines[] = '<FilesMatch "\.(xml|xsl)$">';
			$htaccess_lines[] = '<IfModule mod_headers.c>';
			$htaccess_lines[] = 'Header set Cache-Control "no-cache, no-store, must-revalidate, max-age=0"';
			$htaccess_lines[] = '</IfModule>';
			$htaccess_lines[] = '</FilesMatch>';
		}

		if ( $this->main_instance->get_single_config( 'cf_bypass_file_robots', 0 ) > 0 && $this->is_cache_enabled() ) {

			$htaccess_lines[] = '<FilesMatch "robots\.txt">';
			$htaccess_lines[] = '<IfModule mod_headers.c>';
			$htaccess_lines[] = 'Header set Cache-Control "no-cache, no-store, must-revalidate, max-age=0"';
			$htaccess_lines[] = '</IfModule>';
			$htaccess_lines[] = '</FilesMatch>';
		}

		if ( $this->main_instance->get_single_config( 'cf_browser_caching_htaccess', 0 ) > 0 && $this->is_cache_enabled() ) {

			// Cache CSS/JS/PDF for 1 month
			$htaccess_lines[] = '<FilesMatch "\.(css|js|pdf)$">';
			$htaccess_lines[] = '<IfModule mod_headers.c>';
			$htaccess_lines[] = 'Header set Cache-Control "public, must-revalidate, proxy-revalidate, immutable, max-age=2592000, stale-while-revalidate=86400, stale-if-error=604800"';
			$htaccess_lines[] = '</IfModule>';
			$htaccess_lines[] = '</FilesMatch>';

			// Cache other static files for 1 year
			$htaccess_lines[] = '<FilesMatch "\.(jpg|jpeg|png|gif|ico|eot|swf|svg|webp|avif|ttf|otf|woff|woff2|ogg|mp4|mpeg|avi|mkv|webm|mp3)$">';
			$htaccess_lines[] = '<IfModule mod_headers.c>';
			$htaccess_lines[] = 'Header set Cache-Control "public, must-revalidate, proxy-revalidate, immutable, max-age=31536000, stale-while-revalidate=86400, stale-if-error=604800"';
			$htaccess_lines[] = '</IfModule>';
			$htaccess_lines[] = '</FilesMatch>';
		}

		// Disable direct access to log file
		$log_file_uri = $this->main_instance->get_plugin_wp_content_directory_uri() . '/debug.log';

		$htaccess_lines[] = '<IfModule mod_rewrite.c>';
		$htaccess_lines[] = "RewriteCond %{REQUEST_URI} ^(.*)?{$log_file_uri}(.*)$";
		$htaccess_lines[] = 'RewriteRule ^(.*)$ - [F]';
		$htaccess_lines[] = '</IfModule>';

		// Force cache bypass for wp-cron.php
		$htaccess_lines[] = '<FilesMatch "wp-cron.php">';
		$htaccess_lines[] = '<IfModule mod_headers.c>';
		$htaccess_lines[] = 'Header set Cache-Control "no-cache, no-store, must-revalidate, max-age=0"';
		$htaccess_lines[] = '</IfModule>';
		$htaccess_lines[] = '</FilesMatch>';

		$nginx_rules = $this->get_nginx_rules();

		if ( is_array( $nginx_rules ) ) {
			file_put_contents( $this->main_instance->get_plugin_wp_content_directory() . '/nginx.conf', implode( "\n", $nginx_rules ) );
		} else {
			file_put_contents( $this->main_instance->get_plugin_wp_content_directory() . '/nginx.conf', '' );
		}

		if ( function_exists( 'insert_with_markers' ) && ! insert_with_markers( $this->htaccess_path, 'WP Cloudflare Super Page Cache', $htaccess_lines ) ) {
			$error_msg = sprintf( __( 'The .htaccess file (%s) could not be edited. Check if the file has write permissions.', 'wp-cloudflare-page-cache' ), $this->htaccess_path );
			return false;
		}

		return true;
	}


	function get_nginx_rules() {

		$this->modules = $this->main_instance->get_modules();
		$log_file_uri  = $this->main_instance->get_plugin_wp_content_directory_uri() . '/debug.log';

		$nginx_lines = [];

		if ( $this->main_instance->get_single_config( 'cf_bypass_sitemap', 0 ) > 0 ) {
			$nginx_lines[] = 'location ~* \.(xml|xsl)$ { add_header Cache-Control "no-cache, no-store, must-revalidate, max-age=0"; expires -1; }';
		}

		if ( $this->main_instance->get_single_config( 'cf_bypass_file_robots', 0 ) > 0 ) {
			$nginx_lines[] = 'location /robots.txt { add_header Cache-Control "no-cache, no-store, must-revalidate, max-age=0"; expires -1; }';
		}

		if ( $this->main_instance->get_single_config( 'cf_browser_caching_htaccess', 0 ) > 0 ) {

			// Cache CSS/JS/PDF for 1 month
			$nginx_lines[] = 'location ~* \.(css|js|pdf)$ { add_header Cache-Control "public, must-revalidate, proxy-revalidate, immutable, max-age=2592000, stale-while-revalidate=86400, stale-if-error=604800"; expires 30d; }';

			// Cache other static files for 1 year
			$nginx_lines[] = 'location ~* \.(jpg|jpeg|png|gif|ico|eot|swf|svg|webp|avif|ttf|otf|woff|woff2|ogg|mp4|mpeg|avi|mkv|webm|mp3)$ { add_header Cache-Control "public, must-revalidate, proxy-revalidate, immutable, max-age=31536000, stale-while-revalidate=86400, stale-if-error=604800"; expires 365d; }';

			if ( $this->main_instance->get_single_config( 'cf_bypass_sitemap', 0 ) == 0 ) {
				$nginx_lines[] = 'location ~* \.(xml|xsl)$ { add_header Cache-Control "no-cache, no-store, must-revalidate, max-age=0"; expires -1; }';
			}
		}

		$nginx_lines[] = 'location /wp-cron.php { add_header Cache-Control "no-cache, no-store, must-revalidate, max-age=0"; expires -1; }';

		// Disable direct access to log file
		$nginx_lines[] = "location = {$log_file_uri} { access_log off; deny all; }";

		return $nginx_lines;
	}


	function ajax_purge_everything() {

		check_ajax_referer( 'ajax-nonce-string', 'security' );

		$return_array = [ 'status' => 'ok' ];

		$this->modules = $this->main_instance->get_modules();

		if ( ! $this->main_instance->can_current_user_purge_cache() ) {
			$return_array['status'] = 'error';
			$return_array['error']  = __( 'Permission denied', 'wp-cloudflare-page-cache' );
			die( json_encode( $return_array ) );
		}

		$this->modules['logs']->add_log( 'cache_controller::ajax_purge_whole_cache', 'Purge whole Cloudflare cache' );
		$this->purge_all( false, false, true );

		$return_array['success_msg'] = __( 'Cache purged successfully! It may take up to 30 seconds for the cache to be permanently cleaned.', 'wp-cloudflare-page-cache' );

		die( json_encode( $return_array ) );
	}


	function ajax_purge_whole_cache() {

		check_ajax_referer( 'ajax-nonce-string', 'security' );

		$return_array = [ 'status' => 'ok' ];

		$this->modules = $this->main_instance->get_modules();

		if ( ! $this->main_instance->can_current_user_purge_cache() ) {
			$return_array['status'] = 'error';
			$return_array['error']  = __( 'Permission denied', 'wp-cloudflare-page-cache' );
			die( json_encode( $return_array ) );
		}

		$this->modules['logs']->add_log( 'cache_controller::ajax_purge_whole_cache', 'Purge whole Cloudflare cache' );
		$this->purge_all( false, false );

		$return_array['success_msg'] = __( 'Cache purged successfully! It may take up to 30 seconds for the cache to be permanently cleaned.', 'wp-cloudflare-page-cache' );

		die( json_encode( $return_array ) );
	}


	function ajax_purge_single_post_cache() {

		check_ajax_referer( 'ajax-nonce-string', 'security' );

		$return_array = [ 'status' => 'ok' ];

		$data = stripslashes( $_POST['data'] );
		$data = json_decode( $data, true );

		$this->modules = $this->main_instance->get_modules();

		if ( ! $this->main_instance->can_current_user_purge_cache() ) {
			$return_array['status'] = 'error';
			$return_array['error']  = __( 'Permission denied', 'wp-cloudflare-page-cache' );
			die( json_encode( $return_array ) );
		}

		$post_id = (int) $data['post_id'];

		$urls = $this->get_post_related_links( $post_id );

		if ( ! $this->purge_urls( $urls, false ) ) {
			$return_array['status'] = 'error';
			$return_array['error']  = __( 'An error occurred while cleaning the cache. Please check log file for further details.', 'wp-cloudflare-page-cache' );
			die( json_encode( $return_array ) );
		}

		$this->modules['logs']->add_log( 'cache_controller::ajax_purge_single_post_cache', "Purge Cloudflare cache for only post id {$post_id} and related contents" );

		$return_array['success_msg'] = __( 'Cache purged successfully! It may take up to 30 seconds for the cache to be permanently cleaned.', 'wp-cloudflare-page-cache' );

		die( json_encode( $return_array ) );
	}


	function ajax_reset_all() {

		check_ajax_referer( 'ajax-nonce-string', 'security' );

		$return_array = [ 'status' => 'ok' ];

		if ( ! current_user_can( 'manage_options' ) ) {
			$return_array['status'] = 'error';
			$return_array['error']  = __( 'Permission denied', 'wp-cloudflare-page-cache' );
			die( json_encode( $return_array ) );
		}

		$this->reset_all();

		$return_array['success_msg'] = __( 'Cloudflare and all configurations have been reset to the initial settings.', 'wp-cloudflare-page-cache' );

		die( json_encode( $return_array ) );
	}


	function can_i_start_preloader() {

		$preloader_lock = get_option( 'swcfpc_preloader_lock', 0 );

		if ( $preloader_lock == 0 || ( ( time() - $preloader_lock ) / 60 ) > 15 ) {
			return true;
		}

		return false;
	}


	function lock_preloader() {

		update_option( 'swcfpc_preloader_lock', time() );
	}


	function unlock_preloader() {

		update_option( 'swcfpc_preloader_lock', 0 );
	}


	function is_purge_cache_queue_writable() {

		$purge_cache_lock = get_option( 'swcfpc_purge_cache_lock', 0 );

		if ( $purge_cache_lock == 0 || ( time() - $purge_cache_lock ) > 60 ) {
			return true;
		}

		return false;
	}


	function lock_cache_purge_queue() {

		update_option( 'swcfpc_purge_cache_lock', time() );
	}


	function unlock_cache_purge_queue() {

		update_option( 'swcfpc_purge_cache_lock', 0 );
	}


	function purge_cache_queue_init_directory() {

		$cache_path = $this->main_instance->get_plugin_wp_content_directory() . '/purge_cache_queue/';

		if ( ! file_exists( $cache_path ) && wp_mkdir_p( $cache_path ) ) {
			file_put_contents( $cache_path . 'index.php', '<?php // Silence is golden' );
		}

		return $cache_path;
	}


	function purge_cache_queue_write( $urls = [], $purge_all = false ) {

		$this->modules = $this->main_instance->get_modules();

		while ( ! $this->is_purge_cache_queue_writable() ) {
			$this->modules['logs']->add_log( 'cache_controller::purge_cache_queue_write', 'Queue file not writable. Sleep 1 second' );
			sleep( 1 );
		}

		$this->lock_cache_purge_queue();

		$cache_queue_path   = $this->purge_cache_queue_init_directory() . 'cache_queue.json';
		$swcfpc_cache_queue = [];

		if ( file_exists( $cache_queue_path ) ) {

			$swcfpc_cache_queue = json_decode( file_get_contents( $cache_queue_path ), true );

			if ( ! is_array( $swcfpc_cache_queue ) || ( is_array( $swcfpc_cache_queue ) && ( ! isset( $swcfpc_cache_queue['purge_all'] ) || ! isset( $swcfpc_cache_queue['urls'] ) ) ) ) {
				$this->unlock_cache_purge_queue();
				return true;
			}

			if ( $swcfpc_cache_queue['purge_all'] ) {
				$this->unlock_cache_purge_queue();
				return true;
			}

			if ( $swcfpc_cache_queue['purge_all'] === false && $purge_all === true ) {
				$swcfpc_cache_queue['purge_all'] = true;
			} else {
				$swcfpc_cache_queue['urls'] = array_unique( array_merge( $swcfpc_cache_queue['urls'], $urls ) );
			}
		} else {

			$this->modules['logs']->add_log( 'cache_controller::purge_cache_queue_write', 'queue file not exist' );

			if ( ! is_array( $urls ) ) {
				$urls = [];
			}

			$swcfpc_cache_queue = [
				'purge_all' => $purge_all,
				'urls'      => $urls,
			];
		}

		if ( $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cache_controller::purge_cache_queue_write', 'URLs in purge queue ' . print_r( $swcfpc_cache_queue, true ) );
		}

		file_put_contents( $cache_queue_path, json_encode( $swcfpc_cache_queue ) );

		$this->unlock_cache_purge_queue();
	}


	function purge_cache_queue_custom_interval( $schedules ) {

		$schedules['swcfpc_purge_cache_cron_interval'] = [
			'interval' => ( defined( 'SWCFPC_PURGE_CACHE_CRON_INTERVAL' ) && SWCFPC_PURGE_CACHE_CRON_INTERVAL > 0 ) ? SWCFPC_PURGE_CACHE_CRON_INTERVAL : 10,
			'display'  => esc_html__( 'Super Page Cache - Purge Cache Cron Interval', 'wp-cloudflare-page-cache' ),
		];

		return $schedules;
	}


	function purge_cache_queue_start_cronjob() {

		if ( $this->main_instance->get_single_config( 'cf_disable_cache_purging_queue', 0 ) > 0 ) {
			return false;
		}

		$cache_queue_path = $this->purge_cache_queue_init_directory() . 'cache_queue.json';

		$this->modules = $this->main_instance->get_modules();

		// Purge queue file does not exist, so don't start purge events and unschedule running purge events
		if ( ! file_exists( $cache_queue_path ) ) {

			$timestamp = wp_next_scheduled( 'swcfpc_cache_purge_cron' );

			if ( $timestamp !== false ) {

				if ( wp_unschedule_event( $timestamp, 'swcfpc_cache_purge_cron' ) ) {

					wp_clear_scheduled_hook( 'swcfpc_cache_purge_cron' );

					if ( $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
						$this->modules['logs']->add_log( 'cache_controller::purge_cache_queue_start_cronjob', "Purge queue scheduled event stopped successfully - Timestamp {$timestamp}" );
					}
				} else {

					if ( $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
						$this->modules['logs']->add_log( 'cache_controller::purge_cache_queue_start_cronjob', "Unable to stop the purge queue scheduled event - Timestamp {$timestamp}" );
					}
				}
			}

			return false;
		}

		// If the purge queue file exists and there are not aready running scheduled events, start a new one
		if ( ! wp_next_scheduled( 'swcfpc_purge_cache_cron' ) && ! wp_get_schedule( 'swcfpc_cache_purge_cron' ) ) {

			$timestamp = time();

			if ( wp_schedule_event( $timestamp, 'swcfpc_purge_cache_cron_interval', 'swcfpc_cache_purge_cron' ) ) {

				if ( $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
					$this->modules['logs']->add_log( 'cache_controller::purge_cache_queue_start_cronjob', "Purge queue cronjob started successfully - Timestamp {$timestamp}" );
				}

				return true;
			}

			if ( $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
				$this->modules['logs']->add_log( 'cache_controller::purge_cache_queue_start_cronjob', "Unable to start the purge queue scheduled event - Timestamp {$timestamp}" );
			}
		}

		return false;
	}


	function purge_cache_queue_job() {

		$this->modules = $this->main_instance->get_modules();

		$this->modules['logs']->add_log( 'cache_controller::purge_cache_queue_job', 'I\'m the purge cache cronjob' );

		$cache_queue_path = $this->purge_cache_queue_init_directory() . 'cache_queue.json';

		if ( ! file_exists( $cache_queue_path ) ) {
			$this->modules['logs']->add_log( 'cache_controller::purge_cache_queue_job', 'Queue file does not exists. Exit.' );
			return false;
		}

		while ( ! $this->is_purge_cache_queue_writable() ) {
			$this->modules['logs']->add_log( 'cache_controller::purge_cache_queue_job', 'Queue file not writable. Sleep 1 second' );
			sleep( 1 );
		}

		$this->lock_cache_purge_queue();

		$swcfpc_cache_queue = json_decode( file_get_contents( $cache_queue_path ), true );

		if ( isset( $swcfpc_cache_queue['purge_all'] ) && $swcfpc_cache_queue['purge_all'] ) {
			$this->purge_all( false, false );
		} elseif ( isset( $swcfpc_cache_queue['urls'] ) && is_array( $swcfpc_cache_queue['urls'] ) && count( $swcfpc_cache_queue['urls'] ) > 0 ) {
			$this->purge_urls( $swcfpc_cache_queue['urls'], false );
		}

		@unlink( $cache_queue_path );

		$this->unlock_cache_purge_queue();

		$this->modules['logs']->add_log( 'cache_controller::purge_cache_queue_job', 'Cache purging complete' );

		return true;
	}


	function start_cache_preloader_for_specific_urls( $urls ) {

		if ( class_exists( 'SWCFPC_Preloader_Process' ) ) {

			// Remove empty and duplicated URLs
			$urls = array_filter( $urls );
			$urls = array_unique( $urls );

			$this->modules = $this->main_instance->get_modules();

			if ( $this->can_i_start_preloader() ) {

				$this->lock_preloader();

				$num_url = count( $urls );

				if ( $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
					$this->modules['logs']->add_log( 'cache_controller::start_cache_preloader_for_specific_urls', 'Adding these URLs to preloader queue: ' . print_r( $urls, true ) );
				} else {
					$this->modules['logs']->add_log( 'cache_controller::start_cache_preloader_for_specific_urls', "Adding {$num_url} URLs to preloader queue" );
				}

				$preloader = new SWCFPC_Preloader_Process( $this->main_instance );

				$max_post_to_preload = $num_url >= SWCFPC_PRELOADER_MAX_POST_NUMBER ? SWCFPC_PRELOADER_MAX_POST_NUMBER : $num_url;

				// Add URLs to preloader
				for ( $i = 0; $i < $num_url && $i < $max_post_to_preload; $i++ ) {

					if ( $this->is_external_link( $urls[ $i ] ) === false ) {
						$preloader->push_to_queue( [ 'url' => $urls[ $i ] ] );
					}
				}

				// Start background preloader
				$preloader->save()->dispatch();
			} else {

				$this->modules['logs']->add_log( 'cache_controller::start_cache_preloader_for_specific_urls', 'Unable to start the preloader. Another preloading process is currently running.' );
			}
		}
	}


	function start_preloader_for_all_urls() {

		$this->modules = $this->main_instance->get_modules();
		$home_url      = home_url( '/' );
		$urls          = [];

		// Preload all registered navigation menu locations URLs
		if ( count( $this->main_instance->get_single_config( 'cf_preloader_nav_menus', [] ) ) > 0 ) {

			// Get urls from WordPress menus
			// $wordpress_menus = get_nav_menu_locations();
			$wordpress_menus = $this->main_instance->get_single_config( 'cf_preloader_nav_menus', [] );

			foreach ( $wordpress_menus as $nav_menu_id ) {

				$single_menu_items = wp_get_nav_menu_items( $nav_menu_id );

				if ( $single_menu_items ) {

					foreach ( $single_menu_items as $menu_item ) {

						if ( in_array( $menu_item->url, $urls ) ) {
							continue;
						}

						if ( $menu_item->url && $this->is_external_link( $menu_item->url ) ) {
							continue;
						}

						if ( $menu_item->type == 'post_type' && $menu_item->url && strlen( $menu_item->url ) > 0 && ( substr( strtolower( $menu_item->url ), 0, 6 ) == 'https:' || substr( strtolower( $menu_item->url ), 0, 5 ) == 'http:' ) ) {
							  $urls[] = $menu_item->url;
							  continue;
						}

						if ( $menu_item->url && strcasecmp( substr( $menu_item->url, 0, strlen( $home_url ) - 1 ), $home_url ) == 0 ) {
							  $urls[] = $menu_item->url;
							  continue;
						}
					}
				}
			}
		}

		// Preload URLs in sitemaps
		if ( count( $this->main_instance->get_single_config( 'cf_preload_sitemap_urls', [] ) ) > 0 ) {

			$sitemap_urls = $this->main_instance->get_single_config( 'cf_preload_sitemap_urls', [] );

			if ( is_array( $sitemap_urls ) && count( $sitemap_urls ) > 0 ) {

				foreach ( $sitemap_urls as $single_sitemap_url ) {

					$single_sitemap_url = home_url( $single_sitemap_url );

					$this->modules['logs']->add_log( 'cloudflare::start_preloader_for_all_urls', "Preload sitemap {$single_sitemap_url}" );

					$response = wp_remote_post(
						esc_url_raw( $single_sitemap_url ),
						[
							'timeout'    => defined( 'SWCFPC_CURL_TIMEOUT' ) ? SWCFPC_CURL_TIMEOUT : 10,
							'sslverify'  => false,
							'blocking'   => true,
							'user-agent' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:59.0) Gecko/20100101 Firefox/59.0',
						]
					);

					if ( is_wp_error( $response ) ) {
						$error = sprintf( __( 'Connection error while retriving the sitemap %1$s: %2$s', 'wp-cloudflare-page-cache' ), $single_sitemap_url, $response->get_error_message() );
						$this->modules['logs']->add_log( 'cloudflare::start_preloader_for_all_urls', "Error wp_remote_post: {$error}" );
						continue;
					}

					if ( wp_remote_retrieve_response_code( $response ) != 200 ) {
						$this->modules['logs']->add_log( 'cloudflare::start_preloader_for_all_urls', "Response code for {$single_sitemap_url} is not 200. Response code: " . wp_remote_retrieve_response_code( $response ) );
						continue;
					}

					  $response_body = wp_remote_retrieve_body( $response );

					if ( strlen( $response_body ) == 0 ) {
						$this->modules['logs']->add_log( 'cloudflare::start_preloader_for_all_urls', "Empty response body for sitemap {$single_sitemap_url}" );
						continue;
					}

					libxml_use_internal_errors( true );

					$xml = simplexml_load_string( $response_body );

					if ( $xml === false ) {

							$xml_errors = libxml_get_errors();

						foreach ( $xml_errors as $single_xml_error ) {
							$this->modules['logs']->add_log( 'cloudflare::start_preloader_for_all_urls', "Invalid XML for sitemap {$single_sitemap_url}: {$single_xml_error->message}" );
						}

						  libxml_clear_errors();
					}

					/*
					try {
						$xml = new SimpleXMLElement($response_body);
					} catch (Exception $e){
						$this->objects['logs']->add_log('cloudflare::start_preloader_for_all_urls', 'Invalid XML for sitemap $single_sitemap_url: ' . $e->getMessage());
						continue;
					}
					*/

					if ( isset( $xml->url ) && ! empty( $xml->url ) ) {

						foreach ( $xml->url as $url_list ) {

							if ( ! isset( $url_list->loc ) || empty( $url_list->loc ) || in_array( $url_list->loc, $urls ) || $this->is_external_link( $url_list->loc ) ) {
								continue;
							}

							$urls[] = $url_list->loc->__toString();
						}
					}
				}
			}
		}

		// Preload last published posts
		if ( $this->main_instance->get_single_config( 'cf_preload_last_urls', 0 ) > 0 ) {

			// Get public post types.
			$post_types       = [ 'post', 'page' ];
			$other_post_types = get_post_types(
				[
					'public'             => true,
					'_builtin'           => false,
					'publicly_queryable' => true,
				] 
			);

			foreach ( $other_post_types as $key => $single_post_type ) {
				$post_types[] = $single_post_type;
			}

			$post_types = array_diff( $post_types, $this->main_instance->get_single_config( 'cf_preload_excluded_post_types', [] ) );

			$this->modules['logs']->add_log( 'cloudflare::start_preloader_for_all_urls', 'Getting last published posts for post types: ' . print_r( $post_types, true ) );

			$args = [
				'fields'      => 'ids',
				'numberposts' => 20,
				// 'posts_per_page' => -1,
				'post_type'   => $post_types,
				'orderby'     => 'date',
				'order'       => 'DESC',
			];

			$all_posts = get_posts( $args );

			foreach ( $all_posts as $post ) {

				$permalink = get_permalink( $post );

				if ( $permalink !== false && ! in_array( $permalink, $urls ) && strlen( $permalink ) > 0 ) {
					$urls[] = $permalink;
				}
			}
		}

		// Start preloader
		if ( count( $urls ) > 0 ) {

			if ( ! in_array( $home_url, $urls ) ) {
				$urls[] = $home_url;
			}

			$this->start_cache_preloader_for_specific_urls( $urls );
		} else {
			$this->modules['logs']->add_log( 'cloudflare::start_preloader_for_all_urls', 'Nothing to preload' );
		}
	}


	function is_external_link( $url ) {

		$source = parse_url( home_url() );
		$target = parse_url( $url );

		if ( ! $source || empty( $source['host'] ) || ! $target || empty( $target['host'] ) ) {
			return false;
		}

		if ( strcasecmp( $target['host'], $source['host'] ) === 0 ) {
			return false;
		}

		return true;
	}


	function purge_wpengine_cache() {

		if ( class_exists( 'WpeCommon' ) ) {

			$this->modules = $this->main_instance->get_modules();

			if ( method_exists( 'WpeCommon', 'purge_memcached' ) ) {
				WpeCommon::purge_memcached();
				$this->modules['logs']->add_log( 'cache_controller::purge_wpengine_cache', 'Purge WP Engine memcached cache' );
			}

			if ( method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) {
				WpeCommon::purge_varnish_cache();
				$this->modules['logs']->add_log( 'cache_controller::purge_wpengine_cache', 'Purge WP Engine varnish cache' );
			}
		}
	}


	function can_wpengine_cache_be_purged() {

		if ( ! class_exists( 'WpeCommon' ) ) {
			return false;
		}

		if ( ! method_exists( 'WpeCommon', 'purge_memcached' ) && ! method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) {
			return false;
		}

		return true;
	}


	function purge_spinupwp_cache() {

		if ( ! function_exists( 'spinupwp_purge_site' ) ) {
			return;
		}

		spinupwp_purge_site();

		$this->modules = $this->main_instance->get_modules();

		$this->modules['logs']->add_log( 'cache_controller::purge_spinupwp_cache', 'Purge whole SpinupWP' );
	}


	function purge_spinupwp_cache_single_url( $url ) {

		if ( ! function_exists( 'spinupwp_purge_url' ) ) {
			return;
		}

		spinupwp_purge_url( $url );

		$this->modules = $this->main_instance->get_modules();

		$this->modules['logs']->add_log( 'cache_controller::purge_spinupwp_cache_single_url', "Purge SpinupWP cache for the URL {$url}" );
	}


	function can_spinupwp_cache_be_purged() {
		return ( function_exists( 'spinupwp_purge_site' ) && function_exists( 'spinupwp_purge_url' ) );
	}


	function can_kinsta_cache_be_purged() {

		global $kinsta_cache;

		if ( isset( $kinsta_cache ) && class_exists( '\\Kinsta\\CDN_Enabler' ) ) {
			return true;
		}

		return false;
	}


	function purge_kinsta_cache() {

		global $kinsta_cache;

		if ( $this->can_kinsta_cache_be_purged() && ! empty( $kinsta_cache->kinsta_cache_purge ) ) {

			$kinsta_cache->kinsta_cache_purge->purge_complete_caches();

			$this->modules = $this->main_instance->get_modules();

			$this->modules['logs']->add_log( 'cache_controller::purge_kinsta_cache', 'Purge whole Kinsta cache' );

			return true;
		}

		return false;
	}


	function purge_kinsta_cache_single_url( $url ) {

		if ( ! $this->can_kinsta_cache_be_purged() ) {
			return false;
		}

		$url = trailingslashit( $url ) . 'kinsta-clear-cache/';

		wp_remote_get(
			$url,
			[
				'blocking' => false,
				'timeout'  => 0.01,
			]
		);

		return true;
	}

	function get_siteground_supercacher_version() {

		static $version;

		if ( isset( $version ) ) {
			return $version;
		}

		if ( file_exists( WP_PLUGIN_DIR . '/sg-cachepress/sg-cachepress.php' ) ) {

			$sg_optimizer = get_file_data( WP_PLUGIN_DIR . '/sg-cachepress/sg-cachepress.php', [ 'Version' => 'Version' ] );
			$version      = $sg_optimizer['Version'];

			return $version;
		}

		return false;
	}


	function is_siteground_supercacher_enabled() {

		$sg_version = $this->get_siteground_supercacher_version();

		if ( $sg_version === false ) {
			return false;
		}

		if ( ! version_compare( $sg_version, '5.0' ) < 0 ) {

			global $sg_cachepress_environment;

			return isset( $sg_cachepress_environment ) && $sg_cachepress_environment instanceof SG_CachePress_Environment && $sg_cachepress_environment->cache_is_enabled();
		}

		return (bool) get_option( 'siteground_optimizer_enable_cache', 0 );
	}


	function purge_siteground_cache() {

		if ( ! $this->is_siteground_supercacher_enabled() ) {
			return;
		}

		if ( ! version_compare( $this->get_siteground_supercacher_version(), '5.0' ) < 0 ) {
			SiteGround_Optimizer\Supercacher\Supercacher::purge_cache();
		} elseif ( isset( $sg_cachepress_supercacher ) && $sg_cachepress_supercacher instanceof SG_CachePress_Supercacher ) {
			$sg_cachepress_supercacher->purge_cache();
		}

		$this->modules = $this->main_instance->get_modules();

		$this->modules['logs']->add_log( 'cache_controller::purge_siteground_cache', 'Purge whole Siteground cache' );
	}


	function purge_object_cache() {

		if ( ! function_exists( 'wp_cache_flush' ) ) {
			return false;
		}

		wp_cache_flush();

		$this->modules = $this->main_instance->get_modules();

		$this->modules['logs']->add_log( 'cache_controller::purge_object_cache', 'Purge object cache' );

		return true;
	}


	function purge_opcache() {

		if ( ! extension_loaded( 'Zend OPcache' ) ) {
			return false;
		}

		$opcache_status = opcache_get_status();

		if ( ! $opcache_status || ! isset( $opcache_status['opcache_enabled'] ) || $opcache_status['opcache_enabled'] === false ) {
			return false;
		}

		if ( ! opcache_reset() ) {
			return false;
		}

		/**
		 * opcache_reset() is performed, now try to clear the
		 * file cache.
		 * Please note: http://stackoverflow.com/a/23587079/1297898
		 *   "Opcache does not evict invalid items from memory - they
		 *   stay there until the pool is full at which point the
		 *   memory is completely cleared"
		 */
		foreach ( $opcache_status['scripts'] as $key => $data ) {
			$dirs[ dirname( $key ) ][ basename( $key ) ] = $data;
			opcache_invalidate( $data['full_path'], $force = true );
		}

		$this->modules = $this->main_instance->get_modules();

		$this->modules['logs']->add_log( 'cache_controller::purge_opcache', 'Purge OPcache cache' );

		return true;
	}


	function purge_cache_programmatically( $urls ) {

		if ( ! is_array( $urls ) || count( $urls ) == 0 ) {
			$this->purge_all( true, false );
		} else {
			$this->purge_urls( $urls, false );
		}
	}


	/*
	function purge_cache_on_elementor_ajax_update() {

		if( isset($_REQUEST['editor_post_id']) ) {

			$current_action = function_exists('current_action') ? current_action() : "";

			$post_id = $_REQUEST['editor_post_id'];
			$url = get_permalink( $post_id );

			if( $url !== false ) {
				$this->purge_urls(array($url));

				$this->objects = $this->main_instance->get_modules();

				$this->objects['logs']->add_log('cache_controller::purge_cache_on_elementor_ajax_update', "Purge Cloudflare cache for only post {$post_id} - Fired action: {$current_action}" );
			}

		}

	}*/


	function ajax_preloader_unlock() {

		check_ajax_referer( 'ajax-nonce-string', 'security' );

		$this->modules = $this->main_instance->get_modules();

		$return_array = [ 'status' => 'ok' ];

		if ( ! current_user_can( 'manage_options' ) ) {
			$return_array['status'] = 'error';
			$return_array['error']  = __( 'Permission denied', 'wp-cloudflare-page-cache' );
			die( json_encode( $return_array ) );
		}

		if ( $this->main_instance->get_single_config( 'cf_preloader', 1 ) == 0 ) {
			$return_array['status'] = 'error';
			$return_array['error']  = __( 'Preloader is not enabled', 'wp-cloudflare-page-cache' );
			die( json_encode( $return_array ) );
		}

		if ( $this->can_i_start_preloader() ) {
			$return_array['status'] = 'error';
			$return_array['error']  = __( 'Preloader is already unlocked', 'wp-cloudflare-page-cache' );
			die( json_encode( $return_array ) );
		}

		$this->unlock_preloader();

		$return_array['success_msg'] = __( 'Preloader unlocked successfully', 'wp-cloudflare-page-cache' );

		die( json_encode( $return_array ) );
	}


	function ajax_preloader_start() {

		check_ajax_referer( 'ajax-nonce-string', 'security' );

		$this->modules = $this->main_instance->get_modules();

		$return_array = [ 'status' => 'ok' ];

		if ( ! current_user_can( 'manage_options' ) ) {
			$return_array['status'] = 'error';
			$return_array['error']  = __( 'Permission denied', 'wp-cloudflare-page-cache' );
			die( json_encode( $return_array ) );
		}

		if ( $this->main_instance->get_single_config( 'cf_preloader', 1 ) == 0 ) {
			$return_array['status'] = 'error';
			$return_array['error']  = __( 'Preloader is not enabled', 'wp-cloudflare-page-cache' );
			die( json_encode( $return_array ) );
		}

		if ( ! $this->can_i_start_preloader() ) {
			$return_array['status'] = 'error';
			$return_array['error']  = __( 'Unable to start the preloader. Another preloading process is currently running.', 'wp-cloudflare-page-cache' );
			die( json_encode( $return_array ) );
		}

		if ( ! class_exists( 'WP_Background_Process' ) ) {
			$return_array['status'] = 'error';
			$return_array['error']  = __( 'Unable to start background processes: WP_Background_Process does not exists.', 'wp-cloudflare-page-cache' );
			die( json_encode( $return_array ) );
		}

		if ( ! class_exists( 'SWCFPC_Preloader_Process' ) ) {
			$return_array['status'] = 'error';
			$return_array['error']  = __( 'Unable to start background processes: SWCFPC_Preloader_Process does not exists.', 'wp-cloudflare-page-cache' );
			die( json_encode( $return_array ) );
		}

		if ( ! $this->is_cache_enabled() ) {
			$return_array['status'] = 'error';
			$return_array['error']  = __( 'You cannot start the preloader while the page cache is disabled.', 'wp-cloudflare-page-cache' );
			die( json_encode( $return_array ) );
		}

		$this->start_preloader_for_all_urls();

		$return_array['success_msg'] = __( 'Preloader started successfully', 'wp-cloudflare-page-cache' );

		die( json_encode( $return_array ) );
	}


	function ajax_enable_page_cache() {

		check_ajax_referer( 'ajax-nonce-string', 'security' );

		$this->modules = $this->main_instance->get_modules();

		$return_array = [ 'status' => 'ok' ];
		$error        = '';

		if ( ! current_user_can( 'manage_options' ) ) {
			$return_array['status'] = 'error';
			$return_array['error']  = __( 'Permission denied', 'wp-cloudflare-page-cache' );
			die( json_encode( $return_array ) );
		}

		$this->main_instance->set_single_config( 'cf_cache_enabled', 1 );
		$this->main_instance->set_single_config( 'cf_fallback_cache', 1 );
		$this->main_instance->update_config();

		if (
		$this->main_instance->get_single_config( 'cf_fallback_cache', 0 ) > 0 &&
		$this->main_instance->get_single_config( 'cf_fallback_cache_curl', 0 ) === 0 &&
		! defined( 'SWCFPC_ADVANCED_CACHE' )
		) {
			$this->modules['fallback_cache']->fallback_cache_advanced_cache_enable();
		}

		$return_array['success_msg'] = __( 'Page cache enabled successfully', 'wp-cloudflare-page-cache' );

		die( json_encode( $return_array ) );
	}


	function ajax_disable_page_cache() {

		check_ajax_referer( 'ajax-nonce-string', 'security' );

		$this->modules = $this->main_instance->get_modules();

		$return_array = [ 'status' => 'ok' ];
		$error        = '';

		if ( ! current_user_can( 'manage_options' ) ) {
			$return_array['status'] = 'error';
			$return_array['error']  = __( 'Permission denied', 'wp-cloudflare-page-cache' );
			die( json_encode( $return_array ) );
		}

		if ( ! $this->modules['cloudflare']->disable_page_cache( $error ) ) {
			$return_array['status'] = 'error';
			$return_array['error']  = $error;
			die( json_encode( $return_array ) );
		}

		if ( $this->main_instance->get_single_config( 'cf_fallback_cache', 0 ) > 0 && $this->main_instance->get_single_config( 'cf_fallback_cache_curl', 0 ) == 0 && defined( 'SWCFPC_ADVANCED_CACHE' ) ) {
			$this->modules['fallback_cache']->fallback_cache_advanced_cache_disable();
		}

		$return_array['success_msg'] = __( 'Page cache disabled successfully', 'wp-cloudflare-page-cache' );

		die( json_encode( $return_array ) );
	}
}
