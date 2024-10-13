<?php

namespace GRIM_SG;

use GRIM_SG\Vendor\Controller;

class Frontend extends Controller {
	private static $rules_option  = 'grim_sg_rules';
	private static $rules_version = '1.1';

	/**
	 * Sitemap constructor.
	 */
	public function __construct() {
		self::set_rewrite_hooks();

		add_filter( 'query_vars', array( $this, 'register_query_vars' ), 1 );
		add_action( 'template_redirect', array( $this, 'template_redirect' ), 1 );
		add_action( 'do_robots', array( $this, 'do_robots_link' ), 100 );
		add_action( 'admin_init', array( $this, 'reset_rewrite_rules' ) );
	}

	/**
	 * Register Sitemap Query Variable
	 * @param $query_vars
	 * @return mixed
	 */
	public function register_query_vars( $query_vars ) {
		$query_vars[] = 'sitemap_xsl';
		$query_vars[] = 'sitemap_xml';
		$query_vars[] = 'sitemap_html';
		$query_vars[] = 'google_news';
		$query_vars[] = 'image_sitemap';
		$query_vars[] = 'video_sitemap';
		$query_vars[] = 'inner_sitemap';
		$query_vars[] = 'multilingual_sitemap';
		$query_vars[] = 'page';

		return $query_vars;
	}

	/**
	 * Template Redirect
	 */
	public function template_redirect() {
		global $wp_query;

		$is_xsl_sitemap   = ! empty( $wp_query->query_vars['sitemap_xsl'] );
		$is_xml_sitemap   = ! empty( $wp_query->query_vars['sitemap_xml'] );
		$is_html_sitemap  = ! empty( $wp_query->query_vars['sitemap_html'] );
		$is_google_news   = ! empty( $wp_query->query_vars['google_news'] );
		$is_image_sitemap = ! empty( $wp_query->query_vars['image_sitemap'] );
		$is_video_sitemap = ! empty( $wp_query->query_vars['video_sitemap'] );
		$is_multilingual  = ! empty( $wp_query->query_vars['multilingual_sitemap'] );

		if ( $is_xsl_sitemap || $is_xml_sitemap || $is_html_sitemap || $is_google_news || $is_image_sitemap || $is_video_sitemap || $is_multilingual ) {
			$wp_query->is_404  = false;
			$wp_query->is_feed = true;

			if ( $is_xsl_sitemap ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				Sitemap::generate_sitemap_xsl( sanitize_text_field( $_GET['template'] ?? $wp_query->query_vars['sitemap_xsl'] ) );
			} elseif ( $is_google_news ) {
				( new GoogleNews() )->show_sitemap( GoogleNews::$template );
			} elseif ( $is_image_sitemap ) {
				( new ImageSitemap() )->show_sitemap( ImageSitemap::$template );
			} elseif ( $is_video_sitemap ) {
				( new VideoSitemap() )->show_sitemap( VideoSitemap::$template );
			} elseif ( $is_multilingual ) {
				( new MultilingualSitemap() )->show_sitemap( MultilingualSitemap::$template );
			} else {
				$inner_sitemap = $wp_query->query_vars['inner_sitemap'] ?? null;
				$current_page  = $wp_query->query_vars['page'] ?? null;

				if ( ! empty( $inner_sitemap ) && empty( $current_page ) ) {
					$current_page = 0;
				}

				( new Sitemap() )->show_sitemap( 'sitemap', $is_xml_sitemap, $inner_sitemap, $current_page );
			}

			exit;
		}
	}

	/**
	 * Add Sitemap Links to Robots
	 */
	public function do_robots_link() {
		$settings = $this->get_settings();
		$home_url = get_site_url();

		if ( $settings->sitemap_to_robots ) {
			echo "\nSitemap: {$home_url}/{$settings->sitemap_url}\n"; // phpcs:ignore

			if ( $settings->enable_google_news ) {
				echo "Sitemap: {$home_url}/{$settings->google_news_url}\n"; // phpcs:ignore
			}

			if ( empty( $settings->sitemap_view ) ) {
				if ( $settings->enable_image_sitemap ) {
					echo "Sitemap: {$home_url}/{$settings->image_sitemap_url}\n"; // phpcs:ignore
				}

				if ( $settings->enable_video_sitemap ) {
					echo "Sitemap: {$home_url}/{$settings->video_sitemap_url}\n"; // phpcs:ignore
				}
			}
		}
	}

	/**
	 * Add Custom Rewrite Rules
	 *
	 * @param $wp_rules
	 * @return array
	 */
	public static function add_rewrite_rules( $wp_rules ) {
		$settings       = get_option( self::$slug, new Settings() );
		$stylesheet_url = str_replace( '.', '\.', apply_filters( 'sitemap_xsl_template_path', 'sitemap-stylesheet.xsl' ) ) . '$';
		$sitemap_types  = array( 'page', 'post', 'category', 'author', 'archive', 'additional' );
		$custom_posts   = ( new Controller() )->get_cpt();

		$grim_sg_rules = array(
			$stylesheet_url => 'index.php?sitemap_xsl=true',
		);

		if ( ! empty( $settings->enable_sitemap ) && ! apply_filters( 'sgg_disable_xml_sitemap', false ) ) {
			$sitemap_url                   = str_replace( '.', '\.', $settings->sitemap_url ) . '$';
			$grim_sg_rules[ $sitemap_url ] = 'index.php?sitemap_xml=true';

			if ( ! empty( $settings->sitemap_view ) ) {
				foreach ( $sitemap_types as $type ) {
					$grim_sg_rules[ "{$type}-sitemap([0-9]+)?\.xml$" ] = "index.php?sitemap_xml=true&inner_sitemap={$type}&page=\$matches[1]";
				}

				foreach ( $custom_posts as $cpt ) {
					$grim_sg_rules[ "{$cpt}-sitemap([0-9]+)?\.xml$" ] = "index.php?sitemap_xml=true&inner_sitemap={$cpt}&page=\$matches[1]";
				}
			}
		}

		if ( sgg_pro_enabled() && $settings->enable_html_sitemap ) {
			$html_sitemap_url                   = str_replace( '.', '\.', $settings->html_sitemap_url ) . '$';
			$grim_sg_rules[ $html_sitemap_url ] = 'index.php?sitemap_html=true';

			foreach ( $sitemap_types as $type ) {
				$grim_sg_rules[ "{$type}-sitemap([0-9]+)?\.html$" ] = "index.php?sitemap_html=true&inner_sitemap={$type}&page=\$matches[1]";
			}

			foreach ( $custom_posts as $cpt ) {
				$grim_sg_rules[ "{$cpt}-sitemap([0-9]+)?\.html$" ] = "index.php?sitemap_html=true&inner_sitemap={$cpt}&page=\$matches[1]";
			}
		}

		if ( $settings->enable_google_news ) {
			$google_news_url                   = str_replace( '.', '\.', $settings->google_news_url ) . '$';
			$grim_sg_rules[ $google_news_url ] = 'index.php?google_news=true';
		}

		if ( $settings->enable_image_sitemap ) {
			$image_sitemap_url                   = str_replace( '.', '\.', $settings->image_sitemap_url ) . '$';
			$grim_sg_rules[ $image_sitemap_url ] = 'index.php?image_sitemap=true';
		}

		if ( $settings->enable_video_sitemap ) {
			$video_sitemap_url                   = str_replace( '.', '\.', $settings->video_sitemap_url ) . '$';
			$grim_sg_rules[ $video_sitemap_url ] = 'index.php?video_sitemap=true';
		}

		if ( sgg_is_multilingual() ) {
			$languages = sgg_get_languages();
			if ( ! empty( $languages ) ) {
				global $wp_rewrite;

				$lang_slug  = $wp_rewrite->root . '^(' . implode( '|', $languages ) . ')?/?';
				$lang_rules = array();

				foreach ( $grim_sg_rules as $key => $rule ) {
					$lang_rules[ $lang_slug . $key ] = preg_replace( '/matches\[1\]/', 'matches[2]', $rule );
				}

				$grim_sg_rules = $lang_rules;
			}

			$grim_sg_rules['multilingual-sitemap.xml'] = 'index.php?multilingual_sitemap=true';
		}

		if ( empty( $wp_rules ) ) {
			return $grim_sg_rules;
		}

		return array_merge( $grim_sg_rules, $wp_rules );
	}

	/**
	 * Set Rewrite Hooks
	 */
	public static function set_rewrite_hooks() {
		add_filter( 'option_rewrite_rules', array( self::class, 'add_rewrite_rules' ), 100, 1 );
	}

	/**
	 * Activate Rewrite Rules
	 */
	public static function activate_rewrite_rules() {
		global $wp_rewrite;

		$wp_rewrite->flush_rules( false );

		update_option( self::$rules_option, self::$rules_version );
	}

	/**
	 * Run on Plugin Activate
	 */
	public static function activate_plugin() {
		self::set_rewrite_hooks();
		self::activate_rewrite_rules();
		flush_rewrite_rules();
	}

	/**
	 * Run on Rules Version Updated
	 */
	public function reset_rewrite_rules() {
		$rules_version = get_option( self::$rules_option, false );

		if ( $rules_version !== self::$rules_version ) {
			self::activate_plugin();
		}
	}
}
