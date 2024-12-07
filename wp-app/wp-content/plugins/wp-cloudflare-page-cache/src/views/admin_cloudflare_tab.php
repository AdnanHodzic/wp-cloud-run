<?php

use SPC\Constants;
use SPC\Modules\Admin;
use function SPC\Views\Functions\load_view;
use function SPC\Views\Functions\render_checkbox;
use function SPC\Views\Functions\render_description;
use function SPC\Views\Functions\render_description_section;
use function SPC\Views\Functions\render_header;
use function SPC\Views\Functions\render_number_field;
use function SPC\Views\Functions\render_switch;
use function SPC\Views\Functions\render_text_field;
use function SPC\Views\Functions\render_textarea;

/**
 * @var $sw_cloudflare_pagecache SW_CLOUDFLARE_PAGECACHE
 */
global $sw_cloudflare_pagecache;


$zone_id_list                = Admin::get_zone_id_list_for_display();
$nginx_instructions_page_url = add_query_arg( [ 'page' => 'wp-cloudflare-super-page-cache-nginx-settings' ], admin_url( 'options-general.php' ) );
$is_token_auth               = $sw_cloudflare_pagecache->get_single_config( 'cf_auth_mode', SWCFPC_AUTH_MODE_API_KEY ) == SWCFPC_AUTH_MODE_API_TOKEN;

$switch_counter = 10000;

?>

<?php load_view( 'cloudflare_auth' ); ?>
