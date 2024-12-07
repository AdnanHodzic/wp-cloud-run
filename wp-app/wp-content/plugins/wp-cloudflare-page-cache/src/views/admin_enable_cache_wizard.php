<div class="step">
	<h2><?php _e( 'Enable Page Caching', 'wp-cloudflare-page-cache' ); ?></h2>
	<p style="text-align: center"><?php _e( 'A WordPress performance plugin that lets you get Edge Caching enabled on a Cloudflare free plan.', 'wp-cloudflare-page-cache' ); ?></p>

	<form action="" method="post" id="swcfpc_form_enable_cache">
		<p class="submit">
			<input
					type="submit"
					name="swcfpc_submit_enable_page_cache"
					class="button button-primary green_button"
					value="<?php _e( 'Enable Page Caching Now', 'wp-cloudflare-page-cache' ); ?>"
			>
		</p>
		<div class="swcfpc-highlight">
			<?php _e( 'We strongly recommend disabling all page caching functions of other plugins.', 'wp-cloudflare-page-cache' ); ?>
		</div>
	</form>
</div>
