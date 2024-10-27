<?php
/**
 * WP JQuery Lightbox Class
 *
 * The WP JQuery registers options and enqueues assets for the WP JQuery Lighbox.
 *
 * @package    LightPress Lightbox
 * @author     LightPress
 * @copyright  Copyright (c) 2024, LightPress
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @version    2.0.0
 */

/**
 * WP JQuery Lightbox Class
 *
 * @package    LightPress Lightbox
 * @author     LightPress
 * @since      2.0.0
 */
class WP_JQuery_Lightbox {
	/**
	 * Holds the singleton instance of this class
	 *
	 * @var WP_JQuery_Lightbox|null
	 */
	public static $instance = null;

	/**
	 * Lightbox slug
	 *
	 * @var string
	 */
	public $lightbox_slug = 'wp-jquery-lightbox';

	/**
	 * Is this lightbox active?
	 *
	 * @var bool
	 */
	public $is_active_lightbox = false;

	/**
	 * The group of the lightbox
	 *
	 * @var int
	 */
	public $lightbox_group = -1;

	/**
	 * Holds the options for the JQuery Lightbox
	 *
	 * @var array
	 */
	public $jqlb_options;

	/**
	 * Return instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor for the LightPress class.
	 *
	 * Initializes the LightPress and sets up hooks and filters.
	 */
	public function __construct() {
		$this->is_active_lightbox = LightPress::$active_lightbox === $this->lightbox_slug;

		// Frontend.
		if ( ! is_admin() && $this->is_active_lightbox ) {
			add_action( 'wp_print_styles', array( $this, 'enqueue_css' ) );
			add_action( 'wp_print_scripts', array( $this, 'enqueue_js' ) );
			add_filter( 'the_content', array( $this, 'filter_content' ), 99 );
			add_filter( 'post_gallery', array( $this, 'filter_groups' ), 10, 2 );
			if ( get_option( 'jqlb_comments' ) === 1 ) {
				remove_filter( 'pre_comment_content', 'wp_rel_nofollow' );
				add_filter( 'comment_text', array( $this, 'lightbox_comment' ), 99 );
			}
			if ( $this->should_disable_core_lightbox() ) {
				add_action( 'wp_enqueue_scripts', 'LightPress::disable_core_lightbox_on_frontend', 99 );
			}
		}

		// Admin.
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'add_lightbox_settings' ) );
			if ( $this->is_active_lightbox ) {
				add_filter( 'image_send_to_editor', array( $this, 'remove_rel' ), 10, 2 );
				if ( $this->should_disable_core_lightbox() ) {
					add_filter( 'wp_theme_json_data_user', 'LightPress::hide_core_lightbox_in_editor' );
				}
			}
		}
	}

	/**
	 * Registers and adds settings for plugin and WP JQuery Lightbox.
	 */
	public function add_lightbox_settings() {
		include LIGHTPRESS_PLUGIN_DIR . 'lightboxes/wp-jquery-lightbox/wp-jquery-lightbox-options.php';
		$this->jqlb_options = $wp_jquery_lightbox_options;

		// Register WP JQuery Lightbox settings.
		foreach ( $this->jqlb_options as $key => $setting ) {
			$id                = $setting['id'];
			$default           = isset( $setting['default'] ) ? $setting['default'] : '';
			$sanitize_callback = isset( $setting['sanitize_callback'] ) ? $setting['sanitize_callback'] : null;
			register_setting(
				'lightpress-settings-group',
				$id,
				array(
					'sanitize_callback' => $sanitize_callback,
					'show_in_rest'      => true,
					'default'           => $default,
				)
			);
			add_option( $id, $default );
		}

		// Add WP JQuery Lightbox settings section.
		add_settings_section(
			'wp-jquery-lightbox-settings-section', // Section ID.
			__( 'WP JQuery Lightbox: All Settings', 'wp-jquery-lightbox' ), // Section title.
			null, // Callback for top-of-section content.
			'lightpress-settings', // Page ID.
			array(
				'before_section' => '<div id="wp-jquery-lightbox-settings-section" class="wp-jquery-lightbox wp-jquery-lightbox-settings-section settings-section sub-settings-section">',
				'after_section'  => '</div>',
			)
		);

		// Fields for WP Jquery Lightbox Settings.
		foreach ( $this->jqlb_options as $key => $setting ) {
			$id    = $setting['id'];
			$title = $setting['title'] ?? '';
			add_settings_field(
				$id, // Setting ID.
				$title, // Setting label.
				'LightPress::render_settings_fields', // Setting callback.
				'lightpress-settings', // Page ID.
				'wp-jquery-lightbox-settings-section', // Section ID.
				$setting
			);
		}
	}

	/**
	 * Enqueues JavaScript for the LightPress plugin.
	 */
	public function enqueue_js() {
		$enqueue_in_footer = get_option( 'jqlb_enqueue_in_footer' ) ? true : false;
		$version           = defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : LIGHTPRESS_VERSION;

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script(
			'wp-jquery-lightbox-swipe',
			LIGHTPRESS_PLUGIN_URL . 'lightboxes/wp-jquery-lightbox/vendor/jquery.touchwipe.min.js',
			array( 'jquery' ),
			$version,
			$enqueue_in_footer
		);
		wp_enqueue_script(
			'wp-jquery-lightbox-purify',
			LIGHTPRESS_PLUGIN_URL . 'inc/purify.min.js',
			array(),
			$version,
			$enqueue_in_footer
		);
		if ( get_option( 'jqlb_pinchzoom' ) === '1' ) {
			wp_enqueue_script(
				'wp-jquery-lightbox-panzoom',
				LIGHTPRESS_PLUGIN_URL . 'lightboxes/wp-jquery-lightbox/vendor/panzoom.min.js',
				array( 'jquery' ),
				$version,
				$enqueue_in_footer
			);
			wp_enqueue_script(
				'wp-jquery-lightbox',
				LIGHTPRESS_PLUGIN_URL . 'lightboxes/wp-jquery-lightbox/jquery.lightbox.js',
				array( 'jquery', 'wp-jquery-lightbox-panzoom' ),
				$version,
				$enqueue_in_footer
			);
		} else {
			wp_enqueue_script(
				'wp-jquery-lightbox',
				LIGHTPRESS_PLUGIN_URL . 'lightboxes/wp-jquery-lightbox/jquery.lightbox.js',
				array( 'jquery' ),
				$version,
				$enqueue_in_footer
			);
		}

		wp_localize_script(
			'wp-jquery-lightbox',
			'JQLBSettings',
			array(
				'showTitle'        => get_option( 'jqlb_showTitle' ),
				'useAltForTitle'   => get_option( 'jqlb_useAltForTitle' ),
				'showCaption'      => get_option( 'jqlb_showCaption' ),
				'showNumbers'      => get_option( 'jqlb_showNumbers' ),
				'fitToScreen'      => get_option( 'jqlb_resize_on_demand' ),
				'resizeSpeed'      => get_option( 'jqlb_resize_speed' ),
				'showDownload'     => get_option( 'jqlb_showDownload' ),
				'navbarOnTop'      => get_option( 'jqlb_navbarOnTop' ),
				'marginSize'       => get_option( 'jqlb_margin_size' ),
				'mobileMarginSize' => get_option( 'jqlb_mobile_margin_size' ),
				'slideshowSpeed'   => get_option( 'jqlb_slideshow_speed' ),
				'allowPinchZoom'   => get_option( 'jqlb_pinchzoom' ),
				'borderSize'       => get_option( 'jqlb_borderSize' ),
				'borderColor'      => get_option( 'jqlb_borderColor' ),
				'overlayColor'     => get_option( 'jqlb_overlayColor' ),
				'overlayOpacity'   => get_option( 'jqlb_overlayOpacity' ),
				'newNavStyle'      => get_option( 'jqlb_newNavStyle' ),
				'fixedNav'         => get_option( 'jqlb_fixedNav' ),
				'showInfoBar'      => get_option( 'jqlb_showInfoBar' ),
				'prevLinkTitle'    => __( 'previous image', 'wp-jquery-lightbox' ),
				'nextLinkTitle'    => __( 'next image', 'wp-jquery-lightbox' ),
				'closeTitle'       => __( 'close image gallery', 'wp-jquery-lightbox' ),
				'image'            => __( 'Image ', 'wp-jquery-lightbox' ),
				'of'               => __( ' of ', 'wp-jquery-lightbox' ),
				'download'         => __( 'Download', 'wp-jquery-lightbox' ),
				'pause'            => __( '(Pause Slideshow)', 'wp-jquery-lightbox' ),
				'play'             => __( '(Play Slideshow)', 'wp-jquery-lightbox' ),
			)
		);
	}

	/**
	 * Enqueues CSS for the LightPress plugin.
	 */
	public function enqueue_css() {
		$locale         = $this->get_locale();
		$file_name      = "lightbox.min.{$locale}.css";
		$have_theme_css = false;
		$version        = defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : LIGHTPRESS_VERSION;

		if ( get_option( 'jqlb_use_theme_styles' ) === 1 ) {
			$path_theme     = get_stylesheet_directory() . "/{$file_name}";
			$have_theme_css = is_readable( $path_theme );
			if ( ! $have_theme_css ) {
				$file_name      = 'lightbox.min.css';
				$path_theme     = get_stylesheet_directory() . "/{$file_name}";
				$have_theme_css = is_readable( $path_theme );
			}
		}

		if ( ! $have_theme_css ) {
			$path = plugin_dir_path( __FILE__ ) . "styles/{$file_name}";
			if ( ! is_readable( $path ) ) {
				$file_name = 'lightbox.min.css';
			}
		}
		$uri = ( $have_theme_css )
			? get_stylesheet_directory_uri() . '/' . $file_name
			: LIGHTPRESS_PLUGIN_URL . 'lightboxes/wp-jquery-lightbox/styles/' . $file_name;

		wp_enqueue_style(
			'jquery.lightbox.min.css',
			$uri,
			false,
			$version
		);
		wp_enqueue_style(
			'jqlb-overrides',
			LIGHTPRESS_PLUGIN_URL . 'lightboxes/wp-jquery-lightbox/styles/overrides.css',
			false,
			$version
		);

		// Add inline styles for new nav arrow styling.
		// Needed to apply styles to :before pseudo-selectors.
		$nav_arrow_color      = get_option( 'jqlb_navArrowColor' );
		$nav_background_color = get_option( 'jqlb_navBackgroundColor' );
		$border_width         = get_option( 'jqlb_borderSize' );
		$has_box_shadow       = get_option( 'jqlb_boxShadow' );
		$has_info_bar         = get_option( 'jqlb_showInfoBar' );
		$has_info_bar_top     = get_option( 'jqlb_navbarOnTop' );
		$image_box_shadow     = $has_box_shadow ? '0 0 4px 2px rgba(0,0,0,.2)' : 'none';
		$infobar_box_shadow   = ( $has_box_shadow && $has_info_bar )
			? '0 -4px 0 0 #fff, 0 0 4px 2px rgba(0,0,0,.1)'
			: 'none';
		if ( $has_box_shadow && $has_info_bar && $has_info_bar_top ) {
			$infobar_box_shadow = '0 4px 0 0 #fff, 0 0 4px 2px rgba(0,0,0,.1)';
		}
		$infobar_zindex = ( $has_box_shadow && $has_info_bar && $has_info_bar_top )
			? '99'
			: 'auto';

		$custom_css           = "
			#outerImageContainer {
				box-shadow: {$image_box_shadow};
			}
			#imageContainer{
				padding: {$border_width}px;
			}
			#imageDataContainer {
				box-shadow: {$infobar_box_shadow};
				z-index: {$infobar_zindex};
			}
			#prevArrow,
			#nextArrow{
				background-color: {$nav_background_color};
				color: {$nav_arrow_color};
			}";
		wp_add_inline_style( 'jqlb-overrides', $custom_css );
	}

	/**
	 * Gets the locale for the LightPress plugin.
	 *
	 * @return string The locale.
	 */
	public function get_locale() {
		global $lang_locales;
		if ( defined( 'ICL_LANGUAGE_CODE' ) && isset( $lang_locales[ ICL_LANGUAGE_CODE ] ) ) {
			$locale = $lang_locales[ ICL_LANGUAGE_CODE ];
		} else {
			$locale = get_locale();
		}
		return $locale;
	}

	/**
	 * Filters the content for the LightPress plugin.
	 *
	 * @param string $content The content to filter.
	 * @return string The filtered content.
	 */
	public function filter_content( $content ) {
		if ( get_option( 'jqlb_automate' ) === '1' ) {
			global $post;
			$id      = isset( $post->ID ) ? $post->ID : -1;
			$content = $this->do_regexp( $content, $id );
		}
		return $content;
	}

	/**
	 * Automatically insert rel="lightbox[nameofpost]" to every image with no manual work.
	 * If there are already rel="lightbox[something]" attributes, they are not clobbered.
	 * Michael Tyson, you are a regular expressions god! - http://atastypixel.com
	 *
	 * @param string $content The content to perform the operation on.
	 * @param int    $id      Post id.
	 * @return string The result of the operation.
	 */
	public function do_regexp( $content, $id ) {
		$id          = esc_attr( $id );
		$content     = preg_replace( '/\s+rel="attachment wp-att-[0-9]+"/i', '', $content ); // Remove WP 4.4 garbage.
		$pattern     = "/(<a(?![^>]*?rel=['\"]lightbox.*)[^>]*?href=['\"][^'\"]+?\.(?:bmp|gif|jpg|jpeg|png|webp)(\?\S{0,}){0,1}['\"][^\>]*)>/i";
		$replacement = '$1 rel="lightbox[' . $id . ']">';
		return preg_replace( $pattern, $replacement, $content );
	}

	/**
	 * Determines whether to add filter for grouping images in a gallery.
	 *
	 * Runs on the post_gallery filter.
	 *
	 * @param string $html Content to filter.
	 * @param array  $attr Array of attributes.
	 * @return string
	 */
	public function filter_groups( $html, $attr ) {
		if ( empty( $attr['group'] ) ) {
			$this->lightbox_group = -1;
			remove_filter( 'wp_get_attachment_link', array( $this, 'lightbox_gallery_links' ), 10, 1 );
		} else {
			$this->lightbox_group = $attr['group'];
			add_filter( 'wp_get_attachment_link', array( $this, 'lightbox_gallery_links' ), 10, 1 );
		}
		return '';
	}

	/**
	 * Modifies gallery links to use lightbox, honoring custom group attributes.
	 *
	 * @param  string $html Content to filter.
	 * @return string $html
	 */
	public function lightbox_gallery_links( $html ) {
		// No grouping.
		if ( ! isset( $this->lightbox_group ) || -1 === $this->lightbox_group ) {
			return $html;
		}

		// Grouping.
		return str_replace( '<a', '<a rel="lightbox[' . $this->lightbox_group . ']"', $html );
	}

	/**
	 * Removes rel-attributes to image-links added by WP.
	 *
	 * This filter runs when adding images to a post in the WP 4.4 editor
	 * removing the garbage rel before they're saved to the database.
	 *
	 * @param  string $content Content to filter.
	 * @return string $html    Filtered content.
	 */
	public function remove_rel( $content ) {
		return preg_replace( '/\s+rel="attachment wp-att-[0-9]+"/i', '', $content );
	}

	/**
	 * Filters comments to allow lightbox in comments.
	 *
	 * @param  string $comment Comment to filter.
	 * @return string $html    Filtered comment.
	 */
	public function lightbox_comment( $comment ) {
		$comment = str_replace( 'rel=\'external nofollow\'', '', $comment );
		$comment = str_replace( 'rel=\'nofollow\'', '', $comment );
		$comment = str_replace( 'rel="external nofollow"', '', $comment );
		$comment = str_replace( 'rel="nofollow"', '', $comment );
		return $this->filter_content( $comment );
	}

	/**
	 * Determines if we should disable core lightbox.
	 *
	 * @return bool True if we should disable core lightbox.
	 */
	public function should_disable_core_lightbox() {
		global $wp_version;
		$is_supported_wp_version = isset( $wp_version ) && version_compare( $wp_version, '6.5.0' ) >= 0;
		$is_disabled             = get_option( 'jqlb_disable_core_lightbox', true );

		if ( $is_supported_wp_version && $this->is_active_lightbox && $is_disabled ) {
			return true;
		}

		return false;
	}
}
