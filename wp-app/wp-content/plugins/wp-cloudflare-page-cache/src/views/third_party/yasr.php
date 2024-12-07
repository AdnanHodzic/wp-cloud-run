<?php

use function SPC\Views\Functions\render_switch;

?>

<div class="main_section_header">
	<h3><?php _e( 'Yet Another Stars Rating settings', 'wp-cloudflare-page-cache' ); ?></h3>
</div>

<!-- YASR (Yet Another Stars Rating) purge on rating -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Automatically purge the page cache when a visitor votes', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_yasr_purge_on_rating' ); ?>
	</div>
	<div class="clear"></div>
</div>
