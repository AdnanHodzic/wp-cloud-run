<?php

/* Add shortcodes scripts files to Front-End */

function epcl_shortcodes_add_scripts() {
	global $pagenow;
	$prefix = EPCL_THEMEPREFIX.'-';
	if( !is_admin() ){
        $ver = '2.8.8';
		include( EPCL_PLUGIN_PATH.'/shortcodes/shortcodes.php');
		// wp_enqueue_style( $prefix.'shortcodes',  EPCL_PLUGIN_URL.'shortcodes/dist/shortcodes.min.css', NULL, $ver );
		// wp_register_script( $prefix.'shortcodes_js',  EPCL_PLUGIN_URL.'shortcodes/dist/shortcodes.min.js', array('jquery'), NULL, true );
        // wp_enqueue_script( $prefix.'shortcodes_js' );
        
        add_filter('style_loader_tag', 'epcl_style_loader_tag');
	}
}
add_filter('init', 'epcl_shortcodes_add_scripts');

add_action('admin_head', 'epcl_add_my_tc_button');

function epcl_add_my_tc_button() {
    global $typenow;

    // check user permissions
    if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
        return;
    }
    // verify the post type
    if( ! in_array( $typenow, array( 'post', 'page' ) ) ){
        return;
    }
    
}

add_filter('mce_external_plugins', 'epcl_add_tinymce_plugin');
add_filter('mce_buttons', 'epcl_register_my_tc_button');

// Add custom button to the wp editor
function epcl_add_tinymce_plugin( $plugin_array ) {
    
    $plugin_array['epcl_shortcodes'] = plugins_url( '/tinymce.js?v=2.5', __FILE__ ); // CHANGE THE BUTTON SCRIPT HERE

    return $plugin_array;
}

function epcl_register_my_tc_button( $buttons ) {
    
    array_push($buttons, 'epcl_shortcodes');

    return $buttons;
 }

 // Custom css to the new tinymce button
function custom_css_mce_button() {
    wp_enqueue_style('epcl-custom-tinymce', plugins_url( '/dist/custom-tinymce.css', __FILE__ ) );
}
add_action('admin_enqueue_scripts', 'custom_css_mce_button');

?>