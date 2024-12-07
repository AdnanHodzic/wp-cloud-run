<?php

use SPC\Constants;

use function SPC\Views\Functions\render_description;
use function SPC\Views\Functions\render_dummy_switch;
use function SPC\Views\Functions\render_pro_tag;

?>

<div class="main_section">
	<div class="left_column">
		<label>
			<?php
			_e( 'Ignore marketing parameters', 'wp-cloudflare-page-cache' );
			render_pro_tag( 'ignore-marketing-params' );
			?>
		</label>

		<?php render_description( __( 'This feature would significantly increase the cache hit rate and improve site speed, whether or not the sites are behind Cloudflare', 'wp-cloudflare-page-cache' ), false, false, true ); ?>
	</div>
	<div class="right_column">
		<?php render_dummy_switch( 'marketing_params', 0 ); ?>
	</div>
	<div class="clear"></div>
</div>
