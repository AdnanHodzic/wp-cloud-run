<?php

use function SPC\Views\Functions\render_description;

?>

<!-- Cloudflare Account Signup -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'You don\'t have a Cloudflare account?', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'Cloudflare significantly speeds up your website by leveraging a global network of servers to deliver content faster to your visitors.', 'wp-cloudflare-page-cache' ), false, false, true ); ?>
		<br>
		<label><?php _e( 'Here’s how it works', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'Cloudflare stores copies of your site’s content (HTML, images, CSS, and JavaScript files) on multiple servers around the world.', 'wp-cloudflare-page-cache' ), false, false, true ); ?>
		<?php render_description( __( 'When a visitor accesses your site, Cloudflare serves this content from the server nearest to their location.', 'wp-cloudflare-page-cache' ) ); ?>
		<?php render_description( __( 'This approach, known as edge caching or content delivery network (CDN) service, reduces the distance data needs to travel.', 'wp-cloudflare-page-cache' ) ); ?>
		<br>
	</div>
	<div class="right_column">
		<a href="https://dash.cloudflare.com/sign-up?pt=f" target="_blank"
		   class="button button-secondary"><?php _e( 'Sign up for free', 'wp-cloudflare-page-cache' ); ?></a>

		<br>
		<br>

		<div>
			<p><?php _e( 'After creating your account, get your API Keys:', 'wp-cloudflare-page-cache' ); ?></p>
			<ol>
				<li><a href="https://dash.cloudflare.com/login"
					   target="_blank"><?php _e( 'Log in to your Cloudflare account', 'wp-cloudflare-page-cache' ); ?></a> <?php _e( 'and click on My Profile', 'wp-cloudflare-page-cache' ); ?>
				</li>
				<li><?php _e( 'Click on API tokens, scroll to API Keys and click on View beside Global API Key', 'wp-cloudflare-page-cache' ); ?></li>
				<li><?php _e( 'Enter your Cloudflare login password and click on View', 'wp-cloudflare-page-cache' ); ?></li>
				<li><?php _e( 'Enter both API key and e-mail address into the form below and click on Update settings', 'wp-cloudflare-page-cache' ); ?></li>
			</ol>
		</div>

		<br>
		<p>
			<?php 
			echo sprintf(
			/* translators: %1$s: 'API Token', %2$s: 'this documentation'. */
				__( 'If you want to connect using %1$s, you can follow the steps outlined in %2$s.', 'wp-cloudflare-page-cache' ),
				'<strong>' . __( 'API Token', 'wp-cloudflare-page-cache' ) . '</strong>',
				'<a href="https://docs.themeisle.com/article/2077-super-page-cache-cloudflare-permissions" target="_blank">' . __( 'this documentation', 'wp-cloudflare-page-cache' ) . '</a>'
			); 
			?>
		</p>
	</div>
	<div class="clear"></div>
</div>
