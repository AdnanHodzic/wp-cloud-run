<?php

use SPC\Constants;
use SPC\Modules\Assets_Manager;
use SPC\Modules\Dashboard;
use SPC\Services\Settings_Store;

defined('ABSPATH') || die('Cheatin&#8217; uh?');

class SWCFPC_Backend {
	/**
	 * The main plugin class.
	 *
	 * @var \SW_CLOUDFLARE_PAGECACHE
	 */
	private $main_instance = null;

	function __construct($main_instance) {
		$this->main_instance = $main_instance;
		$this->actions();
	}


	function actions() {
		// TODO: This needs to be reviewed when redoing the purge action on frontend.
		add_action('admin_enqueue_scripts', [$this, 'load_custom_wp_admin_styles_and_script']);
		add_filter('script_loader_tag', [$this, 'modify_script_attributes'], 10, 2);

		if (is_admin() && is_user_logged_in() && current_user_can('manage_options')) {
			add_filter('post_row_actions', [$this, 'add_post_row_actions'], PHP_INT_MAX, 2);
			add_filter('page_row_actions', [$this, 'add_post_row_actions'], PHP_INT_MAX, 2);
		}

		if (! Settings_Store::get_instance()->get(Constants::SETTING_REMOVE_PURGE_OPTION_TOOLBAR) && $this->main_instance->can_current_user_purge_cache()) {
			// TODO: This needs to be reviewed when redoing the purge action on frontend.
			add_action('wp_enqueue_scripts', [$this, 'load_custom_wp_admin_styles_and_script']);
			add_action('admin_bar_menu', [$this, 'add_toolbar_items'], PHP_INT_MAX);
		}
	}

	function load_custom_wp_admin_styles_and_script() {
		$screen = (is_admin() && function_exists('get_current_screen')) ? get_current_screen() : false;

		// Don't load the scripts for Oxygen Builder visual editor pages
		$page_action = $_GET['action'] ?? false;

		$on_oxygen_ct_builder_page = isset($_GET['ct_builder']) && $_GET['ct_builder'] === 'true';
		$on_oxygen_builder_page    = (substr($page_action, 0, strlen('oxy_render')) === 'oxy_render') ? true : false;
		$plugin_version = $this->main_instance->get_plugin_version();

		wp_register_style('swcfpc_sweetalert_css', SWCFPC_PLUGIN_URL . 'assets/css/sweetalert2.min.css', [], '11.7.20');
		wp_register_style('swcfpc_admin_css', SWCFPC_PLUGIN_URL . 'assets/css/style.min.css', ['swcfpc_sweetalert_css'], $plugin_version);

		wp_register_script('swcfpc_sweetalert_js', SWCFPC_PLUGIN_URL . 'assets/js/sweetalert2.min.js', [], '11.7.20', true);
		wp_register_script('swcfpc_admin_js', SWCFPC_PLUGIN_URL . 'assets/js/backend.min.js', ['swcfpc_sweetalert_js'], $plugin_version, true);
		wp_localize_script(
			'swcfpc_admin_js',
			'swcfpcOptions',
			[
				'ajaxUrl'      => admin_url('admin-ajax.php'),
				'nonce'        => wp_create_nonce('spc-ajax-nonce'),
				'cacheEnabled' => Settings_Store::get_instance()->get(Constants::SETTING_CF_CACHE_ENABLED),
			]
		);

		// Making sure we are not adding the following scripts for AMP endpoints as they are not gonna work anyway and will be striped out by the AMP system
		if (
			! (
				(function_exists('amp_is_request') && (! is_admin() && amp_is_request())) ||
				(function_exists('ampforwp_is_amp_endpoint') && (! is_admin() && ampforwp_is_amp_endpoint())) ||
				(is_object($screen) && $screen->base === 'woofunnels_page_wfob') ||
				is_customize_preview() ||
				$on_oxygen_ct_builder_page ||
				$on_oxygen_builder_page
			)
		) {
			wp_enqueue_style('swcfpc_admin_css');
			wp_enqueue_script('swcfpc_admin_js');
		}
	}

	public function modify_script_attributes($tag, $handle) {
		// List of scripts added by this plugin
		$plugin_scripts = [
			'swcfpc_sweetalert_js',
			'swcfpc_admin_js',
		];

		// Check if handle is any of the above scripts made sure we load them as defer
		if (! empty($tag) && in_array($handle, $plugin_scripts)) {
			return str_replace(' id', ' defer id', $tag);
		}

		return $tag;
	}

	public function add_toolbar_items($admin_bar) {
		$screen = is_admin() ? get_current_screen() : false;

		// Make sure we don't add the following admin bar menu as it is not gonna work for AMP endpoints anyway
		if (
			(function_exists('amp_is_request') && (! is_admin() && amp_is_request())) ||
			(function_exists('ampforwp_is_amp_endpoint') && (! is_admin() && ampforwp_is_amp_endpoint())) ||
			(is_object($screen) && $screen->base === 'woofunnels_page_wfob') ||
			is_customize_preview()
		) {
			return;
		}

		if (! Settings_Store::get_instance()->get(Constants::SETTING_REMOVE_PURGE_OPTION_TOOLBAR)) {

			$swpfpc_toolbar_container_url_query_arg_admin = [
				'page' => Dashboard::PAGE_SLUG,
				$this->main_instance->get_cache_controller()->get_cache_buster() => 1,
			];

			if (Settings_Store::get_instance()->get(Constants::SETTING_REMOVE_CACHE_BUSTER)) {
				$swpfpc_toolbar_container_url_query_arg_admin = [
					'page' => Dashboard::PAGE_SLUG,
				];
			}

			$admin_bar->add_menu(
				[
					'id'    => 'wp-cloudflare-super-page-cache-toolbar-container',
					'title' => '<span class="ab-icon"></span><span class="ab-label">' . __('Super Page Cache', 'wp-cloudflare-page-cache') . '</span>',
					'href'  => current_user_can('manage_options') ? add_query_arg($swpfpc_toolbar_container_url_query_arg_admin, admin_url('admin.php')) : '#',
				]
			);

			if (Settings_Store::get_instance()->get(Constants::SETTING_CF_CACHE_ENABLED)) {
				global $post;

				$admin_bar->add_menu(
					[
						'id'     => 'wp-cloudflare-super-page-cache-toolbar-purge-all',
						'parent' => 'wp-cloudflare-super-page-cache-toolbar-container',
						'title'  => __('Purge whole cache', 'wp-cloudflare-page-cache'),
						'href'   => '#',
					]
				);

				if (Settings_Store::get_instance()->get(Constants::SETTING_PURGE_ONLY_HTML)) {

					$admin_bar->add_menu(
						[
							'id'     => 'wp-cloudflare-super-page-cache-toolbar-force-purge-everything',
							'parent' => 'wp-cloudflare-super-page-cache-toolbar-container',
							'title'  => __('Force purge everything', 'wp-cloudflare-page-cache'),
							'href'   => '#',
						]
					);
				}

				if (is_object($post)) {

					$admin_bar->add_menu(
						[
							'id'     => 'wp-cloudflare-super-page-cache-toolbar-purge-single',
							'parent' => 'wp-cloudflare-super-page-cache-toolbar-container',
							'title'  => __('Purge cache for this page only', 'wp-cloudflare-page-cache'),
							'href'   => "#{$post->ID}",
						]
					);
				}

				if ( ! is_admin() && Settings_Store::get_instance()->get( Constants::SETTING_ENABLE_ASSETS_MANAGER) ) {
					$admin_bar->add_menu(
						[
							'id'     => 'wp-cloudflare-super-page-cache-toolbar-asset-manager',
							'title' => '<div style="display: flex; align-items: center;"><span class="ab-icon dashicons dashicons-editor-code"></span><span class="ab-label">' . __('Assets Manager', 'wp-cloudflare-page-cache') . '</span></div>',
							'href'   => add_query_arg(Assets_Manager::ASSETS_MANAGER_QUERY_VAR, 'yes', $_SERVER['REQUEST_URI']),
						]
					);
				}
			}
		}
	}


	function add_post_row_actions($actions, $post) {
		if (! in_array($post->post_type, ['shop_order', 'shop_subscription'])) {
			$actions['swcfpc_single_purge'] = '<a class="swcfpc_action_row_single_post_cache_purge" data-post_id="' . $post->ID . '" href="#" target="_blank">' . __('Purge Cache', 'wp-cloudflare-page-cache') . '</a>';
		}

		return $actions;
	}
}
