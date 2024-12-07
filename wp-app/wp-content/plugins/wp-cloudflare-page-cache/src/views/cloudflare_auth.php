<?php

use SPC\Modules\Admin;
use function SPC\Views\Functions\load_view;
use function SPC\Views\Functions\render_description;
use function SPC\Views\Functions\render_header;
use function SPC\Views\Functions\render_text_field;

/**
 * @var $sw_cloudflare_pagecache SW_CLOUDFLARE_PAGECACHE
 */
global $sw_cloudflare_pagecache;

$zone_id_list  = Admin::get_zone_id_list_for_display();
$is_token_auth = $sw_cloudflare_pagecache->get_single_config( 'cf_auth_mode', SWCFPC_AUTH_MODE_API_KEY ) == SWCFPC_AUTH_MODE_API_TOKEN;

render_header( __( 'Cloudflare General Settings', 'wp-cloudflare-page-cache' ), true );

if ( empty( $zone_id_list ) ) {
	load_view( 'cloudflare_instructions' );
}

?>

<!-- Authentication Mode -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Authentication mode', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'Authentication mode to use to connect to your Cloudflare account.', 'wp-cloudflare-page-cache' ), false, false, true ); ?>
	</div>
	<div class="right_column">
		<select name="swcfpc_cf_auth_mode">
			<option value="<?php echo SWCFPC_AUTH_MODE_API_TOKEN; ?>"
				<?php
				if ( $sw_cloudflare_pagecache->get_single_config( 'cf_auth_mode', SWCFPC_AUTH_MODE_API_KEY ) == SWCFPC_AUTH_MODE_API_TOKEN ) {
					echo 'selected';
				}
				?>
			><?php _e( 'API Token', 'wp-cloudflare-page-cache' ); ?></option>
			<option value="<?php echo SWCFPC_AUTH_MODE_API_KEY; ?>"
				<?php
				if ( $sw_cloudflare_pagecache->get_single_config( 'cf_auth_mode', SWCFPC_AUTH_MODE_API_KEY ) == SWCFPC_AUTH_MODE_API_KEY ) {
					echo 'selected';
				}
				?>
			><?php _e( 'API Key', 'wp-cloudflare-page-cache' ); ?></option>
		</select>
	</div>
	<div class="clear"></div>
</div>

<!-- Cloudflare e-mail -->
<div class="main_section api_key_method <?php echo $is_token_auth ? 'swcfpc_hide' : ''; ?>">
	<div class="left_column">
		<label><?php _e( 'Cloudflare e-mail', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'The email address you use to log in to Cloudflare.', 'wp-cloudflare-page-cache' ), false, false, true ); ?>
	</div>
	<div class="right_column">
		<?php
		render_text_field(
			'cf_email',
			'',
			[
				'autocomplete' => 'off',
			]
		);
		?>
	</div>
	<div class="clear"></div>
</div>

<!-- Cloudflare API Key -->
<div class="main_section api_key_method <?php echo $is_token_auth ? 'swcfpc_hide' : ''; ?>">
	<div class="left_column">
		<label><?php _e( 'Cloudflare API Key', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'The Global API Key extrapolated from your Cloudflare account.', 'wp-cloudflare-page-cache' ), false, false, true ); ?>
	</div>
	<div class="right_column">
		<?php
		render_text_field(
			'cf_apikey',
			'',
			[
				'autocomplete' => 'off',
				'type'         => 'password',
			]
		);
		?>
	</div>
	<div class="clear"></div>
</div>

<!-- Cloudflare API Token -->
<div class="main_section api_token_method <?php echo $is_token_auth ? '' : 'swcfpc_hide'; ?>">
	<div class="left_column">
		<label><?php _e( 'Cloudflare API Token', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'The API Token extrapolated from your Cloudflare account.', 'wp-cloudflare-page-cache' ), false, false, true ); ?>
	</div>
	<div class="right_column">
		<?php
		render_text_field(
			'cf_apitoken',
			'',
			[
				'autocomplete' => 'off',
				'type'         => 'password',
			]
		);
		?>
	</div>
	<div class="clear"></div>
</div>

<!-- Cloudflare Domain Name (API TOKEN) -->
<div class="main_section api_token_method <?php echo $is_token_auth ? '' : 'swcfpc_hide'; ?>">
	<div class="left_column">
		<label><?php _e( 'Cloudflare Domain Name', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'Select/add the domain name for which you want to enable the cache exactly as reported on Cloudflare, then click on Update settings.', 'wp-cloudflare-page-cache' ), false, false, true ); ?>
	</div>
	<div class="right_column">
		<?php render_text_field( 'cf_apitoken_domain', $sw_cloudflare_pagecache->get_second_level_domain(), [ 'autocomplete' => 'off' ] ); ?>
	</div>
	<div class="clear"></div>
</div>
