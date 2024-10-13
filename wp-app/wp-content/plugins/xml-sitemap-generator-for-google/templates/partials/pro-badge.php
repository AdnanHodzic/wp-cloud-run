<span class="pro-title-badge">
	<?php
	printf(
		wp_kses( 'This is <a href="%s" target="_blank">Premium</a> feature', 'xml-sitemap-generator-for-google' ),
		esc_url( sgg_get_pro_url() )
	);
	?>
</span>