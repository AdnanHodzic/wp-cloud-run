<?php

use function SPC\Views\Functions\render_cache_disable_description;
use function SPC\Views\Functions\render_checkbox;

?>

<div class="main_section_header">
	<h3><?php _e( 'Swift Performance (Lite/Pro) settings', 'wp-cloudflare-page-cache' ); ?></h3>
</div>

<?php render_cache_disable_description(); ?>

<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Automatically purge the cache when', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php
		render_checkbox( 'cf_spl_purge_on_flush_all', __( 'Swift Performance (Lite/Pro) flushs all caches', 'wp-cloudflare-page-cache' ), true );
		render_checkbox( 'cf_spl_purge_on_flush_single_post', __( 'Swift Performance (Lite/Pro) flushs single post cache', 'wp-cloudflare-page-cache' ), true );
		?>
	</div>
	<div class="clear"></div>
</div>
