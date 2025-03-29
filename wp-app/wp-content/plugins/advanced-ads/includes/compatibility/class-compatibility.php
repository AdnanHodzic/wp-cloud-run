<?php
/**
 * Compatibility Compatibility.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Compatibility;

use Exception;
use ReflectionClass;
use AdvancedAds\Utilities\Data;
use AdvancedAds\Framework\Utilities\Str;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Compatibility Compatibility.
 */
class Compatibility implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		if ( is_admin() ) {
			( new Admin_Compatibility() )->hooks();
		}

		add_filter( 'wpseo_sitemap_entry', [ $this, 'wpseo_noindex_ad_attachments' ], 10, 3 );
		add_filter( 'mailpoet_newsletter_shortcode', [ $this, 'mailpoet_ad_shortcode' ] );
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			add_filter( 'advanced-ads-placement-content-injection-xpath', [ $this, 'elementor_content_injection' ] );
		}
		if ( defined( 'BORLABS_COOKIE_VERSION' ) ) {
			add_filter( 'advanced-ads-can-display-ads-in-header', [ $this, 'borlabs_cookie_can_add_auto_ads' ], 10 );
		}
	}

	/**
	 * WordPress SEO: remove attachments attached to ads from `/attachment-sitemap.xml`.
	 *
	 * @param array  $url  Array of URL parts.
	 * @param string $type URL type.
	 * @param object $post WP_Post object of attachment.
	 *
	 * @return array|bool Unmodified array of URL parts or false to remove URL.
	 */
	public function wpseo_noindex_ad_attachments( $url, $type, $post ) {
		if ( 'post' !== $type ) {
			return $url;
		}

		$ad_ids = Data::get_ads_ids();

		if ( isset( $post->post_parent ) && in_array( $post->post_parent, $ad_ids, true ) ) {
			return false;
		}

		return $url;
	}

	/**
	 * Display an ad or ad group in a newsletter created by MailPoet.
	 *
	 * Usage:
	 *   [custom:ad:123] to display ad with the ID 123
	 *   [custom:ad_group:345] to display ad group with the ID 345
	 *
	 * @param string $shortcode Shortcode that placed the ad.
	 *
	 * @return string
	 */
	public function mailpoet_ad_shortcode( $shortcode ): string {
		preg_match( '/\d+/', $shortcode, $matches );
		$entity_id = empty( $matches[0] ) ? false : $matches[0];

		// Display an ad group.
		if ( Str::starts_with( '[custom:ad_group:', $shortcode ) && $entity_id ) {
			$ad_group = wp_advads_get_group( $entity_id );
			if ( $ad_group->is_type( 'default', 'ordered' ) ) {
				return get_the_group( $ad_group );
			}

			return '';
		}

		// Display individual ad.
		if ( Str::starts_with( '[custom:ad:', $shortcode ) && $entity_id ) {
			$ad = wp_advads_get_ad( $entity_id );
			if ( $ad->is_type( 'plain', 'image' ) ) {
				return get_the_ad( $ad );
			}

			return '';
		}

		return $shortcode;
	}

	/**
	 * Modify xPath expression for Elementor plugin.
	 * The plugin does not wrap newly created text in 'p' tags.
	 *
	 * @param string $tag Xpath tag.
	 *
	 * @return string
	 */
	public function elementor_content_injection( $tag ): string {
		// 'p' or 'div.elementor-widget-text-editor' without nested 'p'
		if ( 'p' === $tag ) {
			$tag = "*[self::p or self::div[@class and contains(concat(' ', normalize-space(@class), ' '), ' elementor-widget-text-editor ') and not(descendant::p)]]";
		}

		return $tag;
	}

	/**
	 * Check if Adsense Auto ads code can be added to the header.
	 *
	 * @param bool $can_display If the ad can be displayed.
	 *
	 * @return bool
	 */
	public function borlabs_cookie_can_add_auto_ads( $can_display ): bool {
		if ( ! $can_display ) {
			return false;
		}

		return ! self::borlabs_cookie_adsense_auto_ads_code_exists();
	}

	/**
	 * Thrive Theme Builder Check if placements of type other than `header` can be injected during `wp_head` action.
	 */
	public static function can_inject_during_wp_head() {
		if ( did_action( 'before_theme_builder_template_render' ) && ! did_action( 'after_theme_builder_template_render' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if Adsense Auto ads code is added by the Borlabs Cookie plugin.
	 *
	 * This allows to prevent the "Only one 'enable_page_level_ads' allowed per page" error
	 * that makes impossible to close the "Privacy Preference" window created by the "Borlabs Cookie" plugin.
	 *
	 * @return bool
	 */
	public static function borlabs_cookie_adsense_auto_ads_code_exists(): bool {
		static $result = null;

		if ( null !== $result ) {
			return $result;
		}

		$all_cookies = self::borlabs_get_cookies();
		if ( empty( $all_cookies ) ) {
			$result = false;
			return $result;
		}

		foreach ( $all_cookies as $cookie_group_data ) {
			if ( self::is_marketing_cookie( $cookie_group_data ) ) {
				foreach ( $cookie_group_data->cookies as $cookie_data ) {
					if ( self::is_adsense_cookie( $cookie_data ) ) {
						$opt_in_js = $cookie_data->opt_in_js;
					}
				}
			}
		}

		if ( empty( $opt_in_js ) ) {
			$result = false;
			return $result;
		}

		$result = preg_match( '/<script[^>]+data-ad-client/', $opt_in_js ) || false !== strpos( $opt_in_js, 'enable_page_level_ads:' );
		return $result;
	}

	/**
	 * Get cookies from borlabs plugin
	 *
	 * @return bool|array
	 */
	private static function borlabs_get_cookies() {
		// Early bail!!
		if ( ! class_exists( '\BorlabsCookie\Cookie\Frontend\Cookies' ) ) {
			return false;
		}

		$all_cookies = [];

		try {
			$refl_cookies = new ReflectionClass( '\BorlabsCookie\Cookie\Frontend\Cookies' );

			if ( $refl_cookies->hasMethod( 'getInstance' ) && $refl_cookies->hasMethod( 'getAllCookieGroups' ) ) {
				$instance      = $refl_cookies->getMethod( 'getInstance' );
				$cookie_groups = $refl_cookies->getMethod( 'getAllCookieGroups' );

				if ( $instance->isPublic() && $instance->isStatic() && $cookie_groups->isPublic() ) {
					$all_cookies = \BorlabsCookie\Cookie\Frontend\Cookies::getInstance()->getAllCookieGroups();
				}
			}
		} catch ( Exception $e ) {
			return false;
		}

		return $all_cookies;
	}

	/**
	 * Is cookie is of marketing and has data
	 *
	 * @param mixed $cookie Cookie to check.
	 *
	 * @return bool
	 */
	private static function is_marketing_cookie( $cookie ): bool {
		if (
			! empty( $cookie->group_id ) &&
			'marketing' === $cookie->group_id &&
			! empty( $cookie->cookies )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Is cookie is of marketing and has data
	 *
	 * @param mixed $cookie Cookie to check.
	 *
	 * @return bool
	 */
	private static function is_adsense_cookie( $cookie ): bool {
		if (
			! empty( $cookie->cookie_id ) &&
			'google-adsense' === $cookie->cookie_id &&
			! empty( $cookie->opt_in_js )
		) {
			return true;
		}

		return false;
	}
}
