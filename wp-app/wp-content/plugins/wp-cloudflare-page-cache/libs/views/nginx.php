<div class="wrap">

	<div id="swcfpc_main_content">

		<h1><?php _e( 'Super Page Cache - Nginx Settings', 'wp-cloudflare-page-cache' ); ?></h1>

		<?php if ( $this->main_instance->get_single_config( 'cf_cache_control_htaccess', 0 ) > 0 ) : ?>

			<div class="blocco_dati_header">
				<h3><?php echo __( 'Overwrite the cache-control header', 'wp-cloudflare-page-cache' ); ?></h3>
			</div>

			<p><?php echo __( 'Edit the main Nginx configuration file, usually /etc/nginx.conf, and enter these rules immediately after opening the http block:', 'wp-cloudflare-page-cache' ); ?></p>

			<strong><pre>
	map $upstream_http_x_wp_cf_super_cache_active $wp_cf_super_cache_active {
		default  'no-cache, no-store, must-revalidate, max-age=0';
		'1' '<?php echo $this->modules['cache_controller']->get_cache_control_value(); ?>';
	}
						</pre></strong>

			<p><?php echo __( 'Now open the configuration file of your domain and add the following rules inside the block that deals with the management of PHP pages:', 'wp-cloudflare-page-cache' ); ?></p>

			<strong><pre>
	more_clear_headers 'Pragma';
	more_clear_headers 'Expires';
	more_clear_headers 'Cache-Control';
	add_header Cache-Control $wp_cf_super_cache_active;
							</pre></strong>

			<p><?php echo __( 'Save and restart Nginx.', 'wp-cloudflare-page-cache' ); ?></p>

		<?php endif; ?>

		<?php if ( count( $nginx_lines ) > 0 ) : ?>

			<div class="blocco_dati_header">
				<h3><?php echo __( 'Browser caching rules', 'wp-cloudflare-page-cache' ); ?></h3>
			</div>

			<p><?php echo __( 'Open the configuration file of your domain and add the following rules:', 'wp-cloudflare-page-cache' ); ?></p>

			<strong><pre>
			<?php 
			foreach ( $nginx_lines as $single_nginx_line ) {
				echo "{$single_nginx_line}\n";} 
			?>
						</pre></strong>

			<p><?php echo __( 'Save and restart Nginx.', 'wp-cloudflare-page-cache' ); ?></p>

		<?php endif; ?>

	</div>

</div>
