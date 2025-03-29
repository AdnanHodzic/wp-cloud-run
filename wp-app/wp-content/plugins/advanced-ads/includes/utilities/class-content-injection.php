<?php
/**
 * Utilities Content Injection.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Utilities;

defined( 'ABSPATH' ) || exit;

/**
 * Utilities Content Injection.
 */
class Content_Injection {

	/**
	 * Get html tags for content injection
	 *
	 * @since 1.3.5
	 *
	 * @return array Tags that can be used for content injection
	 */
	public static function get_tags(): array {
		$headline_tags = apply_filters( 'advanced-ads-headlines-for-ad-injection', [ 'h2', 'h3', 'h4' ] );
		$headline_tags = '&lt;' . implode( '&gt;, &lt;', $headline_tags ) . '&gt;';

		$tags = apply_filters(
			'advanced-ads-tags-for-injection',
			[
				/* translators: %s is an html tag. */
				'p'           => sprintf( __( 'paragraph (%s)', 'advanced-ads' ), '&lt;p&gt;' ),
				/* translators: %s is an html tag. */
				'pwithoutimg' => sprintf( __( 'paragraph without image (%s)', 'advanced-ads' ), '&lt;p&gt;' ),
				/* translators: %s is an html tag. */
				'h2'          => sprintf( __( 'headline 2 (%s)', 'advanced-ads' ), '&lt;h2&gt;' ),
				/* translators: %s is an html tag. */
				'h3'          => sprintf( __( 'headline 3 (%s)', 'advanced-ads' ), '&lt;h3&gt;' ),
				/* translators: %s is an html tag. */
				'h4'          => sprintf( __( 'headline 4 (%s)', 'advanced-ads' ), '&lt;h4&gt;' ),
				/* translators: %s is an html tag. */
				'headlines'   => sprintf( __( 'any headline (%s)', 'advanced-ads' ), $headline_tags ),
				/* translators: %s is an html tag. */
				'img'         => sprintf( __( 'image (%s)', 'advanced-ads' ), '&lt;img&gt;' ),
				/* translators: %s is an html tag. */
				'table'       => sprintf( __( 'table (%s)', 'advanced-ads' ), '&lt;table&gt;' ),
				/* translators: %s is an html tag. */
				'li'          => sprintf( __( 'list item (%s)', 'advanced-ads' ), '&lt;li&gt;' ),
				/* translators: %s is an html tag. */
				'blockquote'  => sprintf( __( 'quote (%s)', 'advanced-ads' ), '&lt;blockquote&gt;' ),
				/* translators: %s is an html tag. */
				'iframe'      => sprintf( __( 'iframe (%s)', 'advanced-ads' ), '&lt;iframe&gt;' ),
				/* translators: %s is an html tag. */
				'div'         => sprintf( __( 'container (%s)', 'advanced-ads' ), '&lt;div&gt;' ),
				'anyelement'  => __( 'any element', 'advanced-ads' ),
				'custom'      => _x( 'custom', 'for the "custom" content placement option', 'advanced-ads' ),
			]
		);

		return $tags;
	}
}
