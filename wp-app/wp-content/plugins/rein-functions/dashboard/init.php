<?php
if(!defined('ABSPATH')){ exit; }

require_once(EPCL_PLUGIN_PATH.'/dashboard/welcome.php');
require_once(EPCL_PLUGIN_PATH.'/dashboard/dashboard.php');
include(EPCL_PLUGIN_PATH.'/dashboard/products.php');

function epcl_dashboard_menu() {
    $menu_title = 'EstudioPatagon';
    $user_id = get_current_user_id();
    $visited = get_user_meta( $user_id, EPCL_THEMESLUG . '_themes_visited', true);
    if( !$visited ){
        $menu_title = 'EstudioPatagon&nbsp;<span class="awaiting-mod" style="position:absolute;">1</span>'; 
    }
    $my_page = add_menu_page(
        EPCL_THEMENAME . ' - Welcome Page',
        $menu_title,
        'administrator',
        'estudiopatagon-welcome',
        'epcl_welcome_page',
        EPCL_PLUGIN_URL.'shortcodes/images/lightbox-icon.png',
        3
    );

   $welcome_page = add_submenu_page(
        'estudiopatagon-welcome',
        'Welcome',
        'Welcome',
        'administrator',
        'estudiopatagon-welcome',
        'epcl_welcome_page' 
    );

    $license_page = add_submenu_page(
        'estudiopatagon-welcome',
        'License &amp; Updates',
        'License &amp; Updates',
        'administrator',
        'estudiopatagon-license',
        'epcl_license_page' 
    );
    add_submenu_page(
        'estudiopatagon-welcome',
        'Import Demo Content',
        'Import Demo Content',
        'administrator',
        admin_url( 'themes.php?page=estudiopatagon-wizard&step=content' )
    );
    $products_page = add_submenu_page(
        'estudiopatagon-welcome',
        'EstudioPatagon - Products',
        'Our Themes <span class="awaiting-mod">new</span>',
        'read',
        'estudiopatagon-products',
        'epcl_products_page' 
    );
    add_action( "admin_print_scripts-$welcome_page", 'epcl_enqueue_admin_styles' );
    add_action( "admin_print_scripts-$license_page", 'epcl_enqueue_admin_styles' );
}
add_action( 'admin_menu', 'epcl_dashboard_menu' );

function epcl_enqueue_admin_styles() {
    wp_enqueue_style('epcl-admin-styles', EPCL_PLUGIN_URL.'/dashboard/css/admin.css', NULL, '1.1');
    if( isset($_GET['changelog']) ){ ?>
        <style>
            #adminmenumain, #wpadminbar, .notice, #adminmenuwrap{
                display: none !important;
            }
            #wpcontent, #wpfooter{ margin: 0; }
            .epcl-changelog{
                margin: 0 auto;
            }
        </style>
    <?php
    }
}

function epcl_let_to_num( $size ) {
    $l   = substr( $size, -1 );
    $ret = substr( $size, 0, -1 );
    switch ( strtoupper( $l ) ) {
      case 'P':
        $ret *= 1024;
      case 'T':
        $ret *= 1024;
      case 'G':
        $ret *= 1024;
      case 'M':
        $ret *= 1024;
      case 'K':
        $ret *= 1024;
    }
    return $ret;
}