<?php

// If acf pro is not active don't load all custom fields
if( !function_exists('acf_add_local_field_group')  )
    return;

add_filter('acf/settings/show_admin', '__return_false');

if( !function_exists('epcl_register_custom_fields')  ){

    function epcl_register_custom_fields(){
        require_once(EPCL_PLUGIN_PATH.'/metaboxes/user.php');
        require_once(EPCL_PLUGIN_PATH.'/metaboxes/post.php');
        require_once(EPCL_PLUGIN_PATH.'/metaboxes/post-categories.php');
        require_once(EPCL_PLUGIN_PATH.'/metaboxes/post-tags.php');
        require_once(EPCL_PLUGIN_PATH.'/metaboxes/page-default.php');
        require_once(EPCL_PLUGIN_PATH.'/metaboxes/page-home.php');
	    require_once(EPCL_PLUGIN_PATH.'/metaboxes/menu.php');
    }
    add_action('init', 'epcl_register_custom_fields' );

}

?>
