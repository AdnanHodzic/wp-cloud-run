<?php
add_thickbox();

$install_url = add_query_arg(
	[
		'tab'       => 'plugin-information',
		'plugin'    => 'optimole-wp',
		'TB_iframe' => 'true',
		'width'     => '600',
		'height'    => '500',
	],
	network_admin_url( 'plugin-install.php' )
);

require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

$data = get_transient( 'swpcf_optimole_data' );

if ( empty( $data ) ) {
	$data = plugins_api( 'plugin_information', [ 'slug' => 'optimole-wp' ] );

	if ( ! is_wp_error( $data ) ) {
		set_transient( 'swpcf_optimole_data', $data, 12 * HOUR_IN_SECONDS );
	}
}

if ( ! is_array( $data ) ) {
	$data = [
		'num_ratings'     => 588,
		'rating'          => 94,
		'active_installs' => 200000,
	];
}

$rating          = (int) $data['rating'] * 5 / 100;
$rating          = number_format( $rating, 1 );
$active_installs = number_format( $data['active_installs'] );
?>

<style>
	#om-upsell {
		margin: 20px auto;
		background-color: #fff;
		padding: 30px;
		border-radius: 5px;
		box-shadow: 0 3px 3px 3px #dedede;
		font-size: 16px;
		max-width: 800px;
	}
	#om-upsell img {
		max-width: 60px;
	}
	#om-upsell .head {
		display: flex;
		gap: 20px;
	}
	#om-upsell h2 {
		color: #1d2327;
		font-size: 24px;
		font-weight: 600;
		line-height: 1.3;
	}
	#om-upsell p {
		font-size: 16px;
		margin-bottom: 20px;
	}
	#om-upsell ul {
		list-style: circle;
		font-size: 16px;
		padding-left: 30px;
	}
	#om-upsell li {
		margin-bottom: 10px;
		position: relative;
	}
	#om-upsell .install-section h3 {
		margin-top: 0;
	}
	#om-upsell .install-section {
		background-color: #f6f7f7;
		padding: 20px;
		border-radius: 5px;
		margin-top: 20px;
	}
	#om-upsell .button {
		text-align: center;
	}

</style>

<div id="om-upsell">
<div class="head">
	<img src="<?php echo esc_url( SWCFPC_PLUGIN_URL . 'assets/img/optimole-logo.svg' ); ?>" alt="<?php esc_attr_e( 'Optimole Logo', 'wp-cloudflare-page-cache' ); ?>">
	<h2><?php esc_html_e( 'Super Page Cache Pro team created Optimole to give your website an extra speed boost!', 'wp-cloudflare-page-cache' ); ?></h2>
</div>
	<p><?php esc_html_e( 'Images can account for 50% of your loading time!', 'wp-cloudflare-page-cache' ); ?></p>
	<p><?php esc_html_e( 'Optimole automatically optimizes your images in real-time, helping your website gain precious seconds while saving you time. With just one click, it intelligently optimizes and serves your images for the best user experience.', 'wp-cloudflare-page-cache' ); ?></p>

	<ul class="feature-list">
		<li><?php esc_html_e( 'Real-time image optimization with cloud-based system', 'wp-cloudflare-page-cache' ); ?></li>
		<li><?php esc_html_e( 'Automatic compression using Machine Learning (ML)', 'wp-cloudflare-page-cache' ); ?></li>
		<li><?php esc_html_e( 'Global CDN delivery from 450+ locations', 'wp-cloudflare-page-cache' ); ?></li>
		<li><?php esc_html_e( 'Device-specific optimization for perfect sizing', 'wp-cloudflare-page-cache' ); ?></li>
	</ul>

	<div class="install-section">
		<h3><?php esc_html_e( 'Install Optimole, the Smart WordPress Image Optimizer', 'wp-cloudflare-page-cache' ); ?></h3>
		<p>⭐⭐⭐⭐⭐ <?php echo sprintf( __( '%1$s out of 5 stars (%2$d reviews)', 'wp-cloudflare-page-cache' ), $rating, $data['num_ratings'] ); ?></p>
		<p><?php echo sprintf( __( '%s+ Active installations', 'wp-cloudflare-page-cache' ), $active_installs ); ?></p>
		<a href="<?php echo esc_url( $install_url ); ?>" class="button button-primary thickbox" target="_blank"><?php _e( 'Install Optimole', 'wp-cloudflare-page-cache' ); ?> </a>
	</div>
</div>
