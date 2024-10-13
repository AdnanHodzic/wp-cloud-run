<?php $number_of_ads = 0;
// needed error handling due to a weird bug in the piklist plugin.
try {
	$number_of_ads = Advanced_Ads::get_number_of_ads();
} catch ( Exception $e ) {
	// no need to catch anything since we just use TRY/CATCH to prevent an issue caused by another plugin.
}
?>
<div id="aa-welcome-panel">
	<h2><?php esc_attr_e( 'Welcome to Advanced Ads!', 'advanced-ads' ); ?></h2>
	<div class="aa-welcome-panel-column-container">
		<div class="aa-welcome-panel-column">
			<h3><?php esc_attr_e( 'Get Started', 'advanced-ads' ); ?></h3>
			<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=advanced_ads' ) ); ?>"
					class="button button-primary"><?php esc_attr_e( 'Create your first ad', 'advanced-ads' ); ?></a>
			<ul>
				<li>
					<a href="https://wpadvancedads.com/manual/first-ad/?utm_source=advanced-ads&utm_medium=link&utm_campaign=welcome-first-ad"
							target="_blank"><?php esc_attr_e( 'First ad tutorial', 'advanced-ads' ); ?></a></li>
			</ul>
		</div>
		<div class="aa-welcome-panel-column aa-welcome-panel-starter-setup aa-welcome-panel-last">
			<h3><?php esc_attr_e( 'One-Click Setup', 'advanced-ads' ); ?></h3>
			<?php
			// generate link to basic setup.
			$basic_setup_action = '?action=advanced_ads_starter_setup';
			$basic_setup_url    = wp_nonce_url( admin_url( $basic_setup_action ), 'advanced-ads-starter-setup' );
			?>
			<a href="<?php echo esc_url( $basic_setup_url ); ?>"
					class="button button-primary"><?php esc_attr_e( 'Create 2 test ads', 'advanced-ads' ); ?></a>
			<p class="description"><?php esc_attr_e( 'Click to place two ads in the content of your site which are visible to you only.', 'advanced-ads' ); ?></p>
		</div>
		<div class="aa-welcome-panel-column">
			<h3><?php esc_attr_e( 'AdSense Options', 'advanced-ads' ); ?></h3>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=advanced-ads-settings#top#adsense' ) ); ?>"
					class="button button-primary"><?php esc_attr_e( 'Import ads from AdSense', 'advanced-ads' ); ?></a>
			<ul>
				<li>
					<a href="https://wpadvancedads.com/adsense-auto-ads-wordpress/?utm_source=advanced-ads&utm_medium=link&utm_campaign=welcome-auto-ads"
							target="_blank"><?php esc_attr_e( 'Setting up Auto ads', 'advanced-ads' ); ?></a></li>
				<li>
					<a href="https://wpadvancedads.com/place-adsense-ad-unit-manually/?utm_source=advanced-ads&utm_medium=link&utm_campaign=welcome-adsense"
							target="_blank"><?php esc_attr_e( 'Setting up AdSense ads manually', 'advanced-ads' ); ?></a>
				</li>
			</ul>
		</div>
	</div>
</div>
