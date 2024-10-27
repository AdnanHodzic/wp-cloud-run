<?php
/**
 * View: LightPress Pro landing page.
 *
 * @package LightPress Pro
 * @author FirelightWP
 * @copyright Copyright (c) 2023, FirelightWP
 * @license GPL-2.0+
 *
 * This file generates the landing page for the Pro version of the plugin.
 *
 * @version 1.0.1
 */

?>

<div class="sale-banner">
	<p><?php esc_html_e( 'Upgrade to Pro! Take 30% off this week with coupon code PRO.', 'lightpress-pro' ); ?></p>
</div>
<img class="lightpress-logo" src="<?php echo esc_url( LIGHTPRESS_PLUGIN_URL ); ?>admin/lightpress-logo.png">
<div class="hero-section short">
	<div class="hero-section-copy">
		<div>
			<p class="hero-section-copy-tag"><?php esc_html_e( 'LightPress Pro', 'lightpress-pro' ); ?></p>
		</div>
		<h1 class="hero-section-copy-title"><?php esc_html_e( 'The lightbox for serious websites.', 'lightpress-pro' ); ?></h1>
		<p class="hero-section-copy-text"><span class="dashicons dashicons-arrow-right-alt"></span><?php esc_html_e( 'New mobile-first Pro Lightbox with 100+ features!', 'lightpress-pro' ); ?></p>
		<p class="hero-section-copy-text"><span class="dashicons dashicons-arrow-right-alt"></span><?php esc_html_e( 'Social sharing, video galleries, EXIF display', 'lightpress-pro' ); ?></p>
		<p class="hero-section-copy-text"><span class="dashicons dashicons-arrow-right-alt"></span><?php esc_html_e( '50+ more features for free lightboxes', 'lightpress-pro' ); ?></p>
		<p class="hero-section-copy-text"><span class="dashicons dashicons-arrow-right-alt"></span><?php esc_html_e( 'Rockstar Pro support direct from devs', 'lightpress-pro' ); ?></p>
		<p class="hero-section-copy-text"><span class="dashicons dashicons-arrow-right-alt"></span><?php esc_html_e( 'Better user experience = more engagement', 'lightpress-pro' ); ?></p>
		<div class="hero-section-actions">
			<a class="pro-action-button" href="https://lightpress.io/pro-lightbox" target="_blank"><?php esc_html_e( 'Learn More', 'lightpress-pro' ); ?></a>
			<a class="pro-action-button" href="https://lightpress.io/pro-lightbox" target="_blank"><?php esc_html_e( 'See Demos', 'lightpress-pro' ); ?></a>
		</div>
		<p class="hero-section-copy-under-button"><?php esc_html_e( 'Or buy directly from the WordPress dashboard below!', 'lightpress-pro' ); ?></p>
	</div>
	<div class="hero-section-image">
		<figure>
			<picture><img src="<?php echo esc_url( LIGHTPRESS_PLUGIN_URL ); ?>admin/device-mock.jpg" alt="Lightbox display across devices."></picture>
		</figure>
	</div>
</div>

<div class="pricing-section short">
	<div class="pricing-header">
		<h2 class="pricing-headline"><?php esc_html_e( 'Simple Pricing – $59', 'lightpress-pro' ); ?></h2>
		<h4 class="pricing-guarantee-1"><?php esc_html_e( '30% off with coupon code PRO.', 'lightpress-pro' ); ?></h4>
		<h4 class="pricing-guarantee-1"><?php esc_html_e( 'Risk free with 30-day no-questions-asked guarantee.', 'lightpress-pro' ); ?></h4>
	</div>
	<div class="plan-select">
		<div class="plan-select-dropdown">
			<select id="pro-licenses">
			<option value="1" selected="selected"><?php esc_html_e( '1 Site License', 'lightpress-pro' ); ?> ($59)</option>
			<option value="5"><?php esc_html_e( '5 Site License', 'lightpress-pro' ); ?> ($79)</option>
			<option value="25"><?php esc_html_e( '25 Site License', 'lightpress-pro' ); ?> ($149)</option>
			<option value="unlimited"><?php esc_html_e( 'Unlimited Sites', 'lightpress-pro' ); ?> ($299)</option>
			</select>
		</div>
		<button id="pro-purchase" class="pro-action-button"><?php esc_html_e( 'Buy Now', 'lightpress-pro' ); ?></button>
	</div>
	<div style="clear:both;"></div>`
</div>
			