<?php

use function SPC\Views\Functions\render_checkbox;
use function SPC\Views\Functions\render_cache_disable_description;

?>

<div class="main_section_header">
	<h3><?php _e( 'W3 Total Cache settings', 'wp-cloudflare-page-cache' ); ?></h3>
</div>

<?php render_cache_disable_description(); ?>

<!-- Automatically purge the cache when W3TC flushs all caches -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Automatically purge the cache when', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php
		render_checkbox( 'cf_w3tc_purge_on_flush_all', __( 'W3TC flushs all caches', 'wp-cloudflare-page-cache' ), true );
		render_checkbox( 'cf_w3tc_purge_on_flush_dbcache', __( 'W3TC flushs database cache', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_w3tc_purge_on_flush_fragmentcache', __( 'W3TC flushs fragment cache', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_w3tc_purge_on_flush_objectcache', __( 'W3TC flushs object cache', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_w3tc_purge_on_flush_posts', __( 'W3TC flushs posts cache', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_w3tc_purge_on_flush_minfy', __( 'W3TC flushs minify cache', 'wp-cloudflare-page-cache' ) );
		?>
	</div>
	<div class="clear"></div>
</div>
