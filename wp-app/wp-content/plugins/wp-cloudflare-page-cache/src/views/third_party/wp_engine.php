<?php

use function SPC\Views\Functions\render_switch;

?>

<div class="main_section_header">
	<h3><?php _e( 'WP Engine settings', 'wp-cloudflare-page-cache' ); ?></h3>
</div>

<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Automatically purge the WP Engine cache when Cloudflare cache is purged', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_wpengine_purge_on_flush' ); ?>
	</div>
	<div class="clear"></div>
</div>
