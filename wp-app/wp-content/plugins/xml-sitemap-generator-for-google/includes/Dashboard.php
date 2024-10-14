<?php

namespace GRIM_SG;

use GRIM_SG\Vendor\Controller;
use SGG_PRO\Classes\Video_Sitemap;
use SGG_PRO\Classes\Video_Data_Cache;

class Dashboard extends Controller {
	/**
	 * Dashboard constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu_pages' ) );
		add_filter( 'plugin_action_links_' . GRIM_SG_BASENAME, array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_meta_links' ), 10, 2 );
	}

	/**
	 * Menu
	 */
	public function admin_menu_pages() {
		add_options_page(
			esc_html__( 'Google XML Sitemaps Generator Settings', 'xml-sitemap-generator-for-google' ),
			esc_html__( 'XML Sitemaps', 'xml-sitemap-generator-for-google' ),
			'manage_options',
			self::$slug,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Settings page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_enqueue_style( 'sgg-styles', GRIM_SG_URL . 'assets/css/styles.min.css', array(), GRIM_SG_VERSION );
		wp_enqueue_script( 'jquery-ui', GRIM_SG_URL . 'assets/js/jquery-ui.min.js', array( 'jquery' ), GRIM_SG_VERSION, true );
		wp_enqueue_script( 'sgg-scripts', GRIM_SG_URL . 'assets/js/scripts.js', array( 'jquery' ), GRIM_SG_VERSION, true );

		wp_localize_script(
			'sgg-scripts',
			'sgg',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);

		$this->save_settings();

		self::render(
			'settings.php',
			array(
				'settings'   => $this->get_settings(),
				'cpt'        => $this->get_cpt( 'objects' ),
				'taxonomies' => $this->get_taxonomy_types( 'objects' ),
			)
		);
	}

	/**
	 * Register Settings
	 */
	public function register_settings() {
		register_setting( self::$slug, self::$slug );
	}

	/**
	 * Save Settings
	 */
	public function save_settings() {
		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! isset( $_POST['save_settings'] ) ) {
			return;
		}

		if ( ! isset( $_POST['sgg_settings_nonce'] ) || ! wp_verify_nonce( $_POST['sgg_settings_nonce'], GRIM_SG_BASENAME . '-settings' ) ) {
			return;
		}

		// Change IndexNow API Key
		if ( ! empty( $_POST['change_indexnow_key'] ) ) {
			IndexNow::delete_api_key();

			add_settings_error(
				self::$slug,
				'indexnow_api_key',
				esc_html__( 'IndexNow API Key changed successfully.', 'xml-sitemap-generator-for-google' ),
				'success'
			);

			return;
		}

		// Clear Sitemaps Cache
		if ( ! empty( $_POST['clear_cache'] ) ) {
			Cache::clear();

			add_settings_error(
				self::$slug,
				'sitemap_cache',
				esc_html__( 'Sitemaps Cache cleared.', 'xml-sitemap-generator-for-google' ),
				'success'
			);

			return;
		}

		// Import Settings
		if ( ! empty( $_POST['import_settings'] ) ) {
			ImportExport::import_settings();

			return;
		}

		// Clear YouTube Data Cache
		if ( ! empty( $_POST['clear_video_api_cache'] ) && is_callable( 'SGG_PRO\Classes\Video_Data_Cache::delete' ) ) {
			Video_Data_Cache::delete();

			add_settings_error(
				self::$slug,
				'video_api_cache',
				esc_html__( 'Video API Data Cache cleared.', 'xml-sitemap-generator-for-google' ),
				'success'
			);

			return;
		}

		$settings       = new Settings();
		$saved_settings = $this->get_settings();

		// Check YouTube API Key
		if ( ! empty( $_POST['youtube_api_key'] ) && is_callable( 'SGG_PRO\Classes\Video_Sitemap::request_youtube_data' )
			&& ( ! empty( $_POST['youtube_check_api_key'] ) || $saved_settings->youtube_api_key !== $_POST['youtube_api_key'] ) ) {
			$youtube_data = Video_Sitemap::request_youtube_data( 'dQw4w9WgXcQ', sanitize_text_field( $_POST['youtube_api_key'] ) );

			if ( ! empty( $youtube_data ) && is_array( $youtube_data ) ) {
				add_settings_error( self::$slug, 'youtube_api_key_success', esc_html__( 'YouTube API key passed the check successfully.', 'xml-sitemap-generator-for-google' ), 'success' );
			} else {
				add_settings_error( self::$slug, 'youtube_api_key_error', "YouTube API: $youtube_data" );
			}

			if ( ! empty( $_POST['youtube_check_api_key'] ) ) {
				return;
			}
		}

		// Check Vimeo API Key
		if ( ! empty( $_POST['vimeo_api_key'] ) && is_callable( 'SGG_PRO\Classes\Video_Sitemap::request_vimeo_data' )
			&& ( ! empty( $_POST['vimeo_check_api_key'] ) || $saved_settings->vimeo_api_key !== $_POST['vimeo_api_key'] ) ) {
			$vimeo_data = Video_Sitemap::request_vimeo_data( '22439234', sanitize_text_field( $_POST['vimeo_api_key'] ) );

			if ( ! empty( $vimeo_data ) && is_array( $vimeo_data ) ) {
				add_settings_error( self::$slug, 'vimeo_api_key_success', esc_html__( 'Vimeo Access Token passed the check successfully.', 'xml-sitemap-generator-for-google' ), 'success' );
			} else {
				add_settings_error( self::$slug, 'vimeo_api_key_error', "Vimeo API: $vimeo_data" );
			}

			if ( ! empty( $_POST['vimeo_check_api_key'] ) ) {
				return;
			}
		}

		$settings->enable_sitemap      = sanitize_text_field( $_POST['enable_sitemap'] ?? 0 );
		$settings->sitemap_url         = sanitize_text_field( $_POST['sitemap_url'] ?? $settings->sitemap_url );
		$settings->links_per_page      = sanitize_text_field( $_POST['links_per_page'] ?? $settings->links_per_page );
		$settings->html_sitemap_url    = sanitize_text_field( $_POST['html_sitemap_url'] ?? $settings->html_sitemap_url );
		$settings->enable_html_sitemap = sanitize_text_field( $_POST['enable_html_sitemap'] ?? 0 );
		$settings->sitemap_to_robots   = sanitize_text_field( $_POST['sitemap_to_robots'] ?? 0 );
		$settings->enable_indexnow     = sanitize_text_field( $_POST['enable_indexnow'] ?? 0 );
		$settings->sitemap_view        = sanitize_text_field( $_POST['sitemap_view'] ?? '' );
		$settings->exclude_posts       = apply_filters( 'sanitize_post_array', $_POST['exclude_posts'] ?? '' );
		$settings->exclude_terms       = apply_filters( 'sanitize_post_array', $_POST['exclude_terms'] ?? '' );
		$settings->posts_priority      = sanitize_text_field( $_POST['posts_priority'] ?? '' );

		$settings->enable_google_news        = sanitize_text_field( $_POST['enable_google_news'] ?? 0 );
		$settings->google_news_old_posts     = sanitize_text_field( $_POST['google_news_old_posts'] ?? 0 );
		$settings->google_news_name          = sanitize_text_field( $_POST['google_news_name'] ?? '' );
		$settings->google_news_url           = sanitize_text_field( $_POST['google_news_url'] ?? $settings->google_news_url );
		$settings->google_news_keywords      = sanitize_text_field( $_POST['google_news_keywords'] ?? '' );
		$settings->google_news_stocks        = sanitize_text_field( $_POST['google_news_stocks'] ?? 0 );
		$settings->google_news_exclude       = apply_filters( 'sanitize_post_array', $_POST['google_news_exclude'] ?? '' );
		$settings->google_news_exclude_terms = apply_filters( 'sanitize_post_array', $_POST['google_news_exclude_terms'] ?? '' );

		$settings->enable_image_sitemap    = sanitize_text_field( $_POST['enable_image_sitemap'] ?? 0 );
		$settings->enable_video_sitemap    = sanitize_text_field( $_POST['enable_video_sitemap'] ?? 0 );
		$settings->image_sitemap_url       = sanitize_text_field( $_POST['image_sitemap_url'] ?? $settings->image_sitemap_url );
		$settings->video_sitemap_url       = sanitize_text_field( $_POST['video_sitemap_url'] ?? $settings->video_sitemap_url );
		$settings->image_mime_types        = apply_filters( 'sanitize_post_array', $_POST['image_mime_types'] ?? $settings->image_mime_types );
		$settings->youtube_api_key         = sanitize_text_field( $_POST['youtube_api_key'] ?? $settings->youtube_api_key );
		$settings->vimeo_api_key           = sanitize_text_field( $_POST['vimeo_api_key'] ?? $settings->vimeo_api_key );
		$settings->exclude_broken_images   = sanitize_text_field( $_POST['exclude_broken_images'] ?? 0 );
		$settings->include_featured_images = sanitize_text_field( $_POST['include_featured_images'] ?? 0 );
		$settings->include_woo_gallery     = sanitize_text_field( $_POST['include_woo_gallery'] ?? 0 );

		$settings->enable_cache             = sanitize_text_field( $_POST['enable_cache'] ?? 0 );
		$settings->cache_timeout            = sanitize_text_field( $_POST['cache_timeout'] ?? $settings->cache_timeout );
		$settings->cache_timeout_period     = sanitize_text_field( $_POST['cache_timeout_period'] ?? $settings->cache_timeout_period );
		$settings->clear_cache_on_save_post = sanitize_text_field( $_POST['clear_cache_on_save_post'] ?? 0 );
		$settings->enable_video_api_cache   = sanitize_text_field( $_POST['enable_video_api_cache'] ?? 0 );
		$settings->minimize_sitemap         = sanitize_text_field( $_POST['minimize_sitemap'] ?? 0 );

		$settings->home          = $settings->get_row_value( 'home' );
		$settings->page          = $settings->get_row_value( 'page' );
		$settings->post          = $settings->get_row_value( 'post' );
		$settings->archive       = $settings->get_row_value( 'archive' );
		$settings->archive_older = $settings->get_row_value( 'archive_older' );
		$settings->authors       = $settings->get_row_value( 'authors' );

		foreach ( $this->get_cpt() as $cpt ) {
			$settings->cpt[ $cpt ] = $settings->get_row_value( $cpt );
		}

		foreach ( $this->get_taxonomy_types() as $taxonomy_type ) {
			$settings->taxonomies[ $taxonomy_type ] = $settings->get_row_value( $taxonomy_type );
		}

		$custom_sitemap_urls     = apply_filters( 'sanitize_post_array', $_POST['custom_sitemap_urls'] ?? array() );
		$custom_sitemap_lastmods = apply_filters( 'sanitize_post_array', $_POST['custom_sitemap_lastmods'] ?? array() );

		foreach ( $custom_sitemap_urls as $key => $value ) {
			$custom_sitemap = array(
				'url'     => $value,
				'lastmod' => $custom_sitemap_lastmods[ $key ],
			);

			$settings->custom_sitemaps[] = $custom_sitemap;
		}

		$additional_urls        = apply_filters( 'sanitize_post_array', $_POST['additional_urls'] ?? array() );
		$additional_priorities  = apply_filters( 'sanitize_post_array', $_POST['additional_priorities'] ?? array() );
		$additional_frequencies = apply_filters( 'sanitize_post_array', $_POST['additional_frequencies'] ?? array() );
		$additional_lastmods    = apply_filters( 'sanitize_post_array', $_POST['additional_lastmods'] ?? array() );

		foreach ( $additional_urls as $key => $value ) {
			$page = array(
				'url'       => $value,
				'priority'  => $additional_priorities[ $key ],
				'frequency' => $additional_frequencies[ $key ],
				'lastmod'   => $additional_lastmods[ $key ],
			);

			$settings->additional_pages[] = $page;
		}

		update_option( self::$slug, $settings );

		$new_cache_expires = Cache::get_expiration( $settings );
		if ( Cache::get_expiration( $saved_settings ) !== $new_cache_expires ) {
			Cache::maybe_clear( $new_cache_expires );
		}

		if ( ( $settings->sitemap_view !== $saved_settings->sitemap_view ) || ( $settings->links_per_page !== $saved_settings->links_per_page ) ) {
			Cache::clear();
			flush_rewrite_rules();
		}

		add_settings_error(
			self::$slug,
			'sitemap_settings',
			esc_html__( 'Changes saved!', 'xml-sitemap-generator-for-google' ),
			'success'
		);

		flush_rewrite_rules();
	}

	/**
	 * Render Sitemap Post Row
	 * @param $title
	 * @param $option
	 * @param $data
	 */
	public static function render_post_row( $title, $option, $data ) {
		self::render(
			'fields/post-row.php',
			array(
				'title'  => $title,
				'option' => $option,
				'data'   => $data,
			)
		);
	}

	/**
	 * Render Priority Selectbox
	 * @param $name
	 * @param $value
	 */
	public static function render_priority_field( $name, $value ) {
		self::render(
			'fields/priority.php',
			array(
				'name'  => $name,
				'value' => $value,
			)
		);
	}

	/**
	 * Render Frequency Selectbox
	 * @param $name
	 * @param $value
	 */
	public static function render_frequency_field( $name, $value ) {
		self::render(
			'fields/frequency.php',
			array(
				'name'  => $name,
				'value' => $value,
			)
		);
	}

	/**
	 * Render Google News Selectbox
	 * @param $title
	 * @param $name
	 * @param $value
	 */
	public static function render_content_field( $title, $name, $value, $class = '' ) {
		self::render(
			'fields/content.php',
			array(
				'title' => $title,
				'name'  => $name,
				'value' => $value,
				'class' => $class,
			)
		);
	}

	/**
	 * Render Template
	 * @param $template_name
	 * @param $args
	 */
	public static function render( $template_name, $args = array() ) {
		global $wp_version;

		if ( version_compare( $wp_version, '5.5.0', '<' ) ) {
			set_query_var( 'args', $args );
		}

		load_template( GRIM_SG_PATH . "/templates/{$template_name}", false, $args );
	}

	/**
	 * Setting Link
	 *
	 * @param $links
	 * @return mixed
	 */
	public function plugin_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%1$s">%2$s</a>',
			admin_url( 'options-general.php?page=' . self::$slug ),
			esc_html__( 'Settings', 'xml-sitemap-generator-for-google' )
		);

		array_unshift( $links, $settings_link );

		if ( ! sgg_pro_enabled() ) {
			$pro_link = sprintf(
				'<a href="%1$s" style="font-weight: 600; color: #00b000;" target="_blank">%2$s</a>',
				sgg_get_pro_url( 'plugin-action-link' ),
				esc_html__( 'Get Pro Version', 'xml-sitemap-generator-for-google' )
			);

			$links[] = $pro_link;
		}

		return $links;
	}

	/**
	 * Meta Links
	 *
	 * @param $links
	 * @return mixed
	 */
	public function plugin_meta_links( $links, $file ) {
		if ( GRIM_SG_BASENAME === $file ) {
			$links[] = '<a href="' . esc_url( sgg_get_support_url() ) . '" target="_blank">' . __( 'Support', 'xml-sitemap-generator-for-google' ) . '</a>';
			$links[] = '<a href="' . esc_url( sgg_get_review_url() ) . '" target="_blank">' . __( 'Rate ★★★★★', 'xml-sitemap-generator-for-google' ) . '</a>';
		}

		return $links;
	}
}
