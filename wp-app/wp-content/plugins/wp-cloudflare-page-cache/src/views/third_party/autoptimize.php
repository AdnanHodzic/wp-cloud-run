<?php

use function SPC\Views\Functions\render_switch;

?>

<div class="main_section_header">
	<h3><?php _e( 'Autoptimize settings', 'wp-cloudflare-page-cache' ); ?></h3>
</div>

<!-- Automatically purge the cache when Autoptimize flushs its cache -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Automatically purge the cache when Autoptimize flushs its cache', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_autoptimize_purge_on_cache_flush' ); ?>
	</div>
	<div class="clear"></div>
</div>
