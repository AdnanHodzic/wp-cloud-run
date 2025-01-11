<?php

if ( ! defined( 'WPBRIGADE_SDK_DIR' ) ) {
	define( 'WPBRIGADE_SDK_DIR', __DIR__ );
}

if ( ! defined( 'WPBRIGADE_PLUGIN_DIR' ) ) {
    define( 'WPBRIGADE_PLUGIN_DIR', dirname(WPBRIGADE_SDK_DIR) );
}

// if (!defined('WPBRIGADE_SDK_MOD_DIR')) {
//     define('WPBRIGADE_SDK_MOD_DIR', dirname(__FILE__) . '/wpb-sdk/start.php');
// }
// require_once dirname(__FILE__) . '/wpb-sdk/start.php';



if (!defined('WPBRIGADE_SDK_DIR_INCLUDES')) {
    define('WPBRIGADE_SDK_DIR_INCLUDES', WPBRIGADE_SDK_DIR . '/includes');
}
if ( ! defined( 'WPBRIGADE_SDK_API_ENDPOINT' ) ) {
	define( 'WPBRIGADE_SDK_API_ENDPOINT', 'https://app.telemetry.wpbrigade.com/api/v2' );
}


// if (!defined('WPBRIGADE_SDK_REMOTE_ADDR')) {
//     define('WPBRIGADE_SDK_REMOTE_ADDR', wpb_get_ip());
// }
// if (!defined('WPBRIGADE_SDK_DIR_TEMPLATES')) {
//     define('WPBRIGADE_SDK_DIR_TEMPLATES', WPBRIGADE_SDK_DIR . '/templates');
// }
// if (!defined('WPBRIGADE_SDK_DIR_ASSETS')) {
//     define('WPBRIGADE_SDK_DIR_ASSETS', WPBRIGADE_SDK_DIR . '/assets');
// }
// if (!defined('WPBRIGADE_SDK_DIR_CSS')) {
//     define('WPBRIGADE_SDK_DIR_CSS', WPBRIGADE_SDK_DIR_ASSETS . '/css');
// }
// if (!defined('WPBRIGADE_SDK_DIR_JS')) {
//     define('WPBRIGADE_SDK_DIR_JS', WPBRIGADE_SDK_DIR_ASSETS . '/js');
// }
// if (!defined('WPBRIGADE_SDK_DIR_IMG')) {
//     define('WPBRIGADE_SDK_DIR_IMG', WPBRIGADE_SDK_DIR_ASSETS . '/img');
// }
if (!defined('WPBRIGADE_SDK_DIR_SDK')) {
    define('WPBRIGADE_SDK_DIR_SDK', WPBRIGADE_SDK_DIR_INCLUDES);
}
