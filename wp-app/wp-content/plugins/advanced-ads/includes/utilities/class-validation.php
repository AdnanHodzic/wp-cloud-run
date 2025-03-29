<?php
/**
 * The class provides utility functions related to Validation.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Utilities;

use DOMDocument;
use AdvancedAds\Abstracts\Ad;

defined( 'ABSPATH' ) || exit;

/**
 * Validation.
 */
class Validation {

	/**
	 * Check if an ad is valid for 'Post Content' placement
	 *
	 * @param Ad $ad Ad instance.
	 *
	 * @see Regex source: http://stackoverflow.com/questions/17852537/preg-replace-only-specific-part-of-string
	 *
	 * @return string|bool If not valid string with errors, otherwise empty string
	 */
	public static function is_valid_ad_dom( Ad $ad ) {
		$ad_content = $ad->get_content() ?? '';
		if ( ! extension_loaded( 'dom' ) || ! $ad_content ) {
			return false;
		}

		$wp_charset = get_bloginfo( 'charset' );
		$ad_dom     = new DOMDocument( '1.0', $wp_charset );

		$libxml_previous_state = libxml_use_internal_errors( true );
		libxml_clear_errors();
		$ad_content = preg_replace( '#(document.write.+)</(.*)#', '$1<\/$2', $ad_content );
		$ad_dom->loadHtml( '<!DOCTYPE html><html><meta http-equiv="Content-Type" content="text/html; charset=' . $wp_charset . '" /><body>' . $ad_content );

		$errors = '';
		foreach ( libxml_get_errors() as $error ) {
			if ( stripos( $error->message, 'htmlParseEntityRef:' ) || preg_match( '/tag \S+ invalid/i', $error->message ) ) {
				continue;
			}

			$errors .= print_r( $error, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}

		libxml_use_internal_errors( $libxml_previous_state );

		return $errors;
	}

	/**
	 * Check if the current URL is HTTPS, but the ad code contains HTTP.
	 *
	 * @param Ad $ad Ad instance.
	 *
	 * @return bool false/string
	 */
	public static function is_ad_https( Ad $ad ) {
		if (
			$ad &&
			is_ssl() &&
			$ad->is_type( [ 'plain', 'content' ] ) &&
			// Find img, iframe, script. '\\\\' denotes a single backslash.
			preg_match( '#\ssrc=\\\\?[\'"]http:\\\\?/\\\\?/#i', $ad->get_content() )
		) {
			return __( 'Your website is using HTTPS, but the ad code contains HTTP and might not work.', 'advanced-ads' );
		}

		return false;
	}

	/**
	 * Check post and nonce while saving post to reduce complexity.
	 *
	 * @param int    $post_id Post ID.
	 * @param object $post    Post object.
	 *
	 * @return boolean
	 */
	public static function check_save_post( $post_id, $post ): bool {
		if ( empty( $post_id ) || empty( $post ) || ! is_a( $post, 'WP_Post' ) ) {
			return false;
		}

		// Dont' save meta boxes for revisions or autosaves.
		if ( Conditional::doing_autosave() || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return false;
		}

		return true;
	}
}
