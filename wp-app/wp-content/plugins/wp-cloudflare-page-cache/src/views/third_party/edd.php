<?php

use function SPC\Views\Functions\render_checkbox;
use function SPC\Views\Functions\render_switch;

?>

<div class="main_section_header first_section">
	<h3><?php _e( 'Easy Digital Downloads settings', 'wp-cloudflare-page-cache' ); ?></h3>
</div>

<!-- Don't cache the following EDD page types -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Don\'t cache the following EDD page types', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php
		render_checkbox( 'cf_bypass_edd_checkout_page', __( 'Primary checkout page', 'wp-cloudflare-page-cache' ), true );
		render_checkbox( 'cf_bypass_edd_purchase_history_page', __( 'Purchase history page', 'wp-cloudflare-page-cache' ), true );
		render_checkbox( 'cf_bypass_edd_login_redirect_page', __( 'Login redirect page', 'wp-cloudflare-page-cache' ), true );
		render_checkbox( 'cf_bypass_edd_success_page', __( 'Success page', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_bypass_edd_failure_page', __( 'Failure page', 'wp-cloudflare-page-cache' ) );
		?>
	</div>
	<div class="clear"></div>
</div>

<!-- Automatically purge cache when a payment is inserted into the database -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Automatically purge cache when a payment is inserted into the database', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_auto_purge_edd_payment_add' ); ?>
	</div>
	<div class="clear"></div>
</div>
