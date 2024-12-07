<?php

use function SPC\Views\Functions\render_checkbox;
use function SPC\Views\Functions\render_switch;

?>

<div class="main_section_header first_section">
	<h3><?php _e( 'WooCommerce settings', 'wp-cloudflare-page-cache' ); ?></h3>
</div>

<!-- Don't cache the following WooCommerce page types -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Don\'t cache the following WooCommerce page types', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php
		render_checkbox( 'cf_bypass_woo_cart_page', __( 'Cart (is_cart)', 'wp-cloudflare-page-cache' ), true );
		render_checkbox( 'cf_bypass_woo_checkout_page', __( 'Checkout (is_checkout)', 'wp-cloudflare-page-cache' ), true );
		render_checkbox( 'cf_bypass_woo_checkout_pay_page', __( 'Checkout\'s pay page (is_checkout_pay_page)', 'wp-cloudflare-page-cache' ), true );
		render_checkbox( 'cf_bypass_woo_product_page', __( 'Product (is_product)', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_bypass_woo_shop_page', __( 'Shop (is_shop)', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_bypass_woo_product_tax_page', __( 'Product taxonomy (is_product_taxonomy)', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_bypass_woo_product_tag_page', __( 'Product tag (is_product_tag)', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_bypass_woo_product_cat_page', __( 'Product category (is_product_category)', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_bypass_woo_pages', __( 'WooCommerce page (is_woocommerce)', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_bypass_woo_account_page', __( 'My Account page (is_account)', 'wp-cloudflare-page-cache' ), true );
		?>
	</div>
	<div class="clear"></div>
</div>

<!-- Automatically purge cache for product page and related categories when stock quantity changes -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Automatically purge cache for product page and related categories when stock quantity changes', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_auto_purge_woo_product_page', 1 ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Automatically purge cache for product page and related categories when product is updated -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Automatically purge cache for scheduled sales', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_auto_purge_woo_scheduled_sales', 1 ); ?>
	</div>
	<div class="clear"></div>
</div>
