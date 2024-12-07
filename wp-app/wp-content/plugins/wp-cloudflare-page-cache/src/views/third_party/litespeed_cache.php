<?php

use function SPC\Views\Functions\render_checkbox;
use function SPC\Views\Functions\render_cache_disable_description;

?>

<div class="main_section_header">
	<h3><?php _e( 'LiteSpeed Cache settings', 'wp-cloudflare-page-cache' ); ?></h3>
</div>

<?php render_cache_disable_description(); ?>

<!-- Automatically purge the cache when LiteSpeed Cache flushs all caches -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Automatically purge the cache when', 'wp-cloudflare-page-cache' ); ?></label>
	</div>

	<div class="right_column">
		<?php
		render_checkbox( 'cf_litespeed_purge_on_cache_flush', __( 'LiteSpeed Cache flushs all caches', 'wp-cloudflare-page-cache' ), true );
		render_checkbox( 'cf_litespeed_purge_on_ccss_flush', __( 'LiteSpeed Cache flushs Critical CSS', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_litespeed_purge_on_cssjs_flush', __( 'LiteSpeed Cache flushs CSS and JS cache', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_litespeed_purge_on_object_cache_flush', __( 'LiteSpeed Cache flushs object cache', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_litespeed_purge_on_single_post_flush', __( 'LiteSpeed Cache flushs single post cache via API', 'wp-cloudflare-page-cache' ) );
		?>
	</div>
	<div class="clear"></div>
</div>
