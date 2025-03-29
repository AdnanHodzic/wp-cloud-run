<?php
/**
 * Welcome box.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 */

?>
<div id="welcome" class="postbox position-full" style="background-image:url('<?php echo esc_url( ADVADS_BASE_URL . '/admin/assets/img/welcome/banner-pattern.svg' ); ?>')" xmlns="http://www.w3.org/1999/html">
	<div>
		<div>
			<span id="subhead"><?php esc_html_e( 'How to get started', 'advanced-ads' ); ?></span>
			<span id="head"><?php esc_html_e( 'Welcome to Advanced Ads', 'advanced-ads' ); ?></span>
			<p>
				<?php esc_html_e( 'Thank you for choosing the best ad management plugin for WordPress.', 'advanced-ads' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'Get started quickly with our setup wizard to ensure optimal ad performance. Advanced Ads empowers you to create, target, and analyze your ads like never before.', 'advanced-ads' ); ?>
				<?php esc_html_e( "Let's unlock your advertising potential and gain your income.", 'advanced-ads' ); ?>
			</p>
			<div id="cta">
				<span><a href="<?php echo esc_url( admin_url( 'admin.php?page=advanced-ads-onboarding' ) ); ?>" id="launch-wizard"><?php esc_html_e( 'Launch the Setup Wizard', 'advanced-ads' ); ?></a></span>
				<span><a href="https://wpadvancedads.com/manual/first-ad/" id="first-step" target="_blank"><?php esc_html_e( 'Read the First Steps', 'advanced-ads' ); ?></a></span>
			</div>
		</div>
		<div>
			<a href="https://www.youtube.com/watch?v=nfybYz8ayXQ" id="welcome-thumbnail" target="_blank">
				<img alt="" src="<?php echo esc_url( plugin_dir_url( ADVADS_FILE ) . '/admin/assets/img/welcome/video-thumbnail.jpg' ); ?>"/>
			</a>
		</div>
	</div>
	<span id="dismiss-welcome">
		<i class="dashicons dashicons-dismiss"></i>
	</span>
</div>
