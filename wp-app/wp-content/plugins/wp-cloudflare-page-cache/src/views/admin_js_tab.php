<?php

use function SPC\Views\Functions\render_description;
use function SPC\Views\Functions\render_dummy_textarea;
use function SPC\Views\Functions\render_dummy_switch;
use function SPC\Views\Functions\render_pro_tag;

?>

<div class="main_section first_section">
	<div class="left_column">
		<label>
			<?php
			_e( 'Defer Javascript', 'wp-cloudflare-page-cache' );
			render_pro_tag( 'defer-js' );
			?>
		</label>
		<p class="description">
			<?php _e( 'Deferring Javascript eliminates render-blocking JS on your site and can improve load time.', 'wp-cloudflare-page-cache' ); ?>
			<a href="https://docs.themeisle.com/article/2058-defer-js" target="_blank">
				<?php _e( 'More Info', 'wp-cloudflare-page-cache' ); ?>
			</a>
		</p>
	</div>
	<div class="right_column">
		<?php render_dummy_switch( 'defer_js' ); ?>
	</div>
	<div class="clear"></div>
</div>

<div class="main_section">
	<div class="left_column">
		<label>
			<?php
			_e( 'Delay Javascript', 'wp-cloudflare-page-cache' );
			render_pro_tag( 'delay-js' );
			?>
		</label>
		<p class="description">
			<?php _e( 'It makes the website faster by waiting to load JavaScript files until the user interacts with the page, like scrolling or clicking.', 'wp-cloudflare-page-cache' ); ?>
			<a href="https://docs.themeisle.com/article/2057-delay-js" target="_blank">
				<?php _e( 'More Info', 'wp-cloudflare-page-cache' ); ?>
			</a>
		</p>
	</div>

	<div class="right_column">
		<?php
		render_dummy_switch( 'delay_js' );
		?>
	</div>
	<div class="clear"></div>
</div>

<div class="main_section">
	<div class="left_column">
		<label>
			<?php
			_e( 'Exclude JS', 'wp-cloudflare-page-cache' );
			render_pro_tag( 'delay-js-exclusion-files' );
			?>
		</label>
	</div>
	<div class="right_column">
		<?php
		render_dummy_textarea( 'delay_js_excluded', 'example-1.min.js&#10;example-2.min.js' );
		render_description( __( 'Enter keywords (one per line) to be matched against external file sources or inline JavaScript content.', 'wp-cloudflare-page-cache' ) );
		?>
	</div>
	<div class="clear"></div>
</div>

<div class="main_section">
	<div class="left_column">
		<label>
			<?php
			_e( 'Exclude pages', 'wp-cloudflare-page-cache' );
			render_pro_tag( 'delay-js-exclusion-paths' );
			?>
		</label>
	</div>
	<div class="right_column">
		<?php
		render_dummy_textarea( 'delay_js_excluded_paths', '/about-us&#10;/blog/awesome-post' );
		render_description( __( 'Enter keywords (one per line) to be matched against URL paths. Use %s for home page.', 'wp-cloudflare-page-cache' ) );
		?>
	</div>
	<div class="clear"></div>
</div>
