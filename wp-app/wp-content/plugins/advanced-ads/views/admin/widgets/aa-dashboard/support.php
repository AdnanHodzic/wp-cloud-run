<?php
/**
 * Render manual & support widget
 *
 * @package AdvancedAds
 */

use AdvancedAds\Utilities\Data;

?>
<div class="advads-widget-wrapper">
	<div class="manual-wrapper">
		<div>
			<a class="title" href="https://wpadvancedads.com/manual/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-manual" target="_blank">
				<span class="dashicons dashicons-welcome-learn-more"></span>
				<?php esc_html_e( 'Manual', 'advanced-ads' ); ?>
			</a>
		</div>
		<div class="divider"></div>
		<div>
			<a class="title" href="https://wpadvancedads.com/support/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-support" target="_blank">
				<span class="dashicons dashicons-sos"></span>
				<?php esc_html_e( 'Support & FAQ', 'advanced-ads' ); ?>
			</a>
		</div>
	</div>
</div>

<div class="advads-widget-wrapper">
	<div class="section-title">
		<h3><?php esc_html_e( 'Latest Tutorials', 'advanced-ads' ); ?></h3>
	</div>

	<?php Data::display_rss_feed(); ?>
</div>

<footer>
	<?php
	printf(
		/* translators: %1$s is the opening <a> tag, %2$s is the closing </a> tag. */
		esc_html__( '%1$sThank the developer with a &#9733;&#9733;&#9733;&#9733;&#9733; review on wordpress.org%2$s', 'advanced-ads' ),
		'<a href="' . esc_url( 'https://wordpress.org/support/plugin/advanced-ads/reviews/#new-post' ) . '" target="_blank">',
		'<span aria-hidden="true" class="dashicons dashicons-external"></span></a>'
	);
	?>
</footer>
