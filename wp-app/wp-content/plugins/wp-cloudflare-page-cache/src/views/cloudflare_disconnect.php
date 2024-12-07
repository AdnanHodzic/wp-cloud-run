<?php

use SPC\Modules\Admin;
use function SPC\Views\Functions\render_description;
use function SPC\Views\Functions\render_switch;
use function SPC\Views\Functions\render_text_field;

/**
 * @var $sw_cloudflare_pagecache SW_CLOUDFLARE_PAGECACHE
 */
global $sw_cloudflare_pagecache;

$is_token_auth = $sw_cloudflare_pagecache->get_single_config( 'cf_auth_mode', SWCFPC_AUTH_MODE_API_KEY ) == SWCFPC_AUTH_MODE_API_TOKEN;
$has_zone_id   = $sw_cloudflare_pagecache->has_cloudflare_api_zone_id();
$email         = $sw_cloudflare_pagecache->get_single_config( 'cf_email' );
/* translators: %s: user email address */
$label        = $is_token_auth || empty( $email ) ? __( 'Connected to Cloudflare with API Token', 'wp-cloudflare-page-cache' ) : sprintf( __( 'Connected to Cloudflare as %s', 'wp-cloudflare-page-cache' ), $email );
$zone_id_list = Admin::get_zone_id_list_for_display();

// Display the zone selector if the user connects with an API key.
if ( ( ! empty( $zone_id_list ) && ! $has_zone_id ) || $has_zone_id ) {
	if ( ! $is_token_auth && ! $has_zone_id ) { ?>
		<!-- Cloudflare Domain Name -->
		<div class="main_section">
			<div class="left_column">
				<label>
					<?php _e( 'Cloudflare Domain Name', 'wp-cloudflare-page-cache' ); ?>
					<span class="swcfpc-required">*</span>
				</label>
				<?php render_description( __( 'Select the domain for which you want to enable the cache and click on Update settings.', 'wp-cloudflare-page-cache' ), false, false, true ); ?>
			</div>
			<div class="right_column">

				<select name="swcfpc_cf_zoneid">
					<option value=""><?php _e( 'Select a Domain Name', 'wp-cloudflare-page-cache' ); ?></option>
					<?php

					$selected_zone_id = $sw_cloudflare_pagecache->get_cloudflare_api_zone_id();
					if ( empty( $selected_zone_id ) ) {
						$host_domain_name = $sw_cloudflare_pagecache->get_second_level_domain();

						foreach ( $zone_id_list as $zone_id_name => $zone_id ) {
							if ( strpos( $zone_id_name, $host_domain_name ) !== false ) {
								$selected_zone_id = $zone_id;
								break;
							}
						}
					}

					foreach ( $zone_id_list as $zone_id_name => $zone_id ) { 
						?>
						<option value="<?php echo $zone_id; ?>" <?php selected( $zone_id, $selected_zone_id ); ?>>
							<?php echo $zone_id_name; ?>
						</option>
					<?php } ?>
				</select>

			</div>
			<div style="display: flex; justify-content: flex-end;">
				<button type="submit" name="swcfpc_submit_general" class="button button-primary green_button">
					<?php _e( 'Continue', 'wp-cloudflare-page-cache' ); ?>
				</button>
			</div>
			<div class="clear"></div>
		</div>
		<?php
	}

	if ( $is_token_auth ) {
		render_text_field( 'cf_zoneid', '', [ 'type' => 'hidden' ] );
	}
}

if ( ! $has_zone_id ) {
	return;
} 
?>

<div class="main_section is_first">
	<div class="left_column">
		<label><?php echo wp_kses_post( $label ); ?></label>
	</div>
	<div class="right_column">
		<button type="button" id="swcfpc_disconnect_cloudflare" class="button red_button">
			<?php _e( 'Disconnect Cloudflare', 'wp-cloudflare-page-cache' ); ?>
		</button>
	</div>
	<div class="clear"></div>
</div>


<!-- Enable Cloudflare cache -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Enable Cloudflare CDN & Caching', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'Serve cached files from Cloudflare using Cache Rule.', 'wp-cloudflare-page-cache' ), false, false, true ); ?>
	</div>
	<div class="right_column">
		<?php
		// Old page rule ID.
		$has_page_rule = ! empty( $sw_cloudflare_pagecache->get_single_config( 'cf_page_rule_id', '' ) );
		// Has cache rule ID.
		$has_rule_id                 = ! empty( $sw_cloudflare_pagecache->get_single_config( 'cf_cache_settings_ruleset_rule_id', '' ) );
		$is_cloudflare_cache_enabled = $has_rule_id ? 1 : (int) ( $has_page_rule );

		render_switch( 'enable_cache_rule', 0, '', false, true, $is_cloudflare_cache_enabled );
		?>
	</div>
	<div class="clear"></div>
</div>
