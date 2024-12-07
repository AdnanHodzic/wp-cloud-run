<?php

use function SPC\Views\Functions\render_cache_disable_description;
use function SPC\Views\Functions\render_switch;

?>

<div class="main_section_header">
	<h3><?php _e( 'Hummingbird settings', 'wp-cloudflare-page-cache' ); ?></h3>
</div>

<?php render_cache_disable_description(); ?>

<!-- Automatically purge the cache when Hummingbird flushs page cache -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Automatically purge the cache when Hummingbird flushs page cache', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<div class="switch-field">
			<?php render_switch( 'cf_hummingbird_purge_on_cache_flush' ); ?>
		</div>
	</div>
	<div class="clear"></div>
</div>
