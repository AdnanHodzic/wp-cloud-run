<div class="wrap">

	<div id="swcfpc_main_content">

		<h1><?php _e( 'Super Page Cache - Cached HTML pages', 'wp-cloudflare-page-cache' ); ?></h1>


		<?php if ( count( $cached_html_pages_list ) > 0 ) : ?>

			<p><strong><?php printf( __( 'There are %d cached pages in list:', 'wp-cloudflare-page-cache' ), count( $cached_html_pages_list ) ); ?></strong></p>

			<ul>

				<?php foreach ( $cached_html_pages_list as $url ) : ?>

					<li><?php echo $url; ?></li>

				<?php endforeach; ?>

			</ul>

		<?php else : ?>

			<p><?php echo __( 'There are no cached HTML pages right now.', 'wp-cloudflare-page-cache' ); ?></p>

		<?php endif; ?>

	</div>

</div>
