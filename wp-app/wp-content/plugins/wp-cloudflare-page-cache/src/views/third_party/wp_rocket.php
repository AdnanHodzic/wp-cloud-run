<?php

use function SPC\Views\Functions\render_cache_disable_description;
use function SPC\Views\Functions\render_checkbox;
use function SPC\Views\Functions\render_switch;

?>

<div class="main_section_header">
	<h3><?php _e( 'WP Rocket settings', 'wp-cloudflare-page-cache' ); ?></h3>
</div>

<?php render_cache_disable_description(); ?>

<!-- Automatically purge the cache when WP Rocket flush its cache -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Automatically purge the cache when', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php
		render_checkbox( 'cf_wp_rocket_purge_on_domain_flush', __( 'WP Rocket flushs all caches', 'wp-cloudflare-page-cache' ), true );
		render_checkbox( 'cf_wp_rocket_purge_on_post_flush', __( 'WP Rocket flushs single post cache', 'wp-cloudflare-page-cache' ), true );
		render_checkbox( 'cf_wp_rocket_purge_on_cache_dir_flush', __( 'WP Rocket flushs cache directories', 'wp-cloudflare-page-cache' ), true );
		render_checkbox( 'cf_wp_rocket_purge_on_clean_files', __( 'WP Rocket flushs files', 'wp-cloudflare-page-cache' ), true );
		render_checkbox( 'cf_wp_rocket_purge_on_clean_cache_busting', __( 'WP Rocket flushs cache busting', 'wp-cloudflare-page-cache' ), true );
		render_checkbox( 'cf_wp_rocket_purge_on_clean_minify', __( 'WP Rocket flushs minified files', 'wp-cloudflare-page-cache' ), true );
		render_checkbox( 'cf_wp_rocket_purge_on_ccss_generation_complete', __( 'CCSS generation process ends', 'wp-cloudflare-page-cache' ), true );
		render_checkbox( 'cf_wp_rocket_purge_on_rucss_job_complete', __( 'RUCSS generation process ends', 'wp-cloudflare-page-cache' ), true );
		?>
	</div>
	<div class="clear"></div>
</div>

<!-- Disable WP Rocket page cache -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Disable WP Rocket page cache', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_wp_rocket_disable_cache' ); ?>
	</div>
	<div class="clear"></div>
</div>
