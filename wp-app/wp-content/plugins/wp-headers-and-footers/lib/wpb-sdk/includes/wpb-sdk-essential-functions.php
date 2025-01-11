<?php

function wpb_get_plugin_details($slug)
{
	$plugin_path = WP_PLUGIN_DIR . '/' . $slug . '/' . $slug . '.php';
	if (file_exists($plugin_path)) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		$plugin_data = get_plugin_data($plugin_path);

		return $plugin_data;
	}
}

function wpb_get_plugin_path($slug)
{
	// Get the absolute path to the WordPress installation
	$wp_path = untrailingslashit(ABSPATH);

	// Generate the plugin path based on the provided slug and include the main plugin file
	$plugin_path = $wp_path . DIRECTORY_SEPARATOR . 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $slug . DIRECTORY_SEPARATOR . $slug . '.php';

	return $plugin_path;
}

// Add admin menu item
function custom_admin_menu()
{
	// Check if WP_FS__DEV_MODE constant is defined and true
	if (defined('WPBRIGADE_SDK__DEV_MODE') && WPBRIGADE_SDK__DEV_MODE === true) {
		add_menu_page(
			'Custom Page Title', // Page title
			'WPB-SDK Debug [v1.5.0]',       // Menu title
			'manage_options',    // Capability required
			'wpb-debug-mode',  // Menu slug
			'custom_page_content' // Callback function to render page content
		);
	}
}

add_action('admin_menu', 'custom_admin_menu', 999); // Use a high priority to ensure it runs after other menu items are added



// Render the custom page content
function custom_page_content()
{
	// Check if WP_FS__DEV_MODE constant is defined and true
	if (defined('WPBRIGADE_SDK__DEV_MODE') && WPBRIGADE_SDK__DEV_MODE === true) {
		// Include the template file from the views folder
		include_once plugin_dir_path(__FILE__) . '../views/wpb-debug.php';
	} else {
		// Render the default content
?>
		<div class="wrap">
			<h1>WPB Debug Page</h1>
		</div>
	<?php
	}
}

// Add admin menu item for 'account' page
function custom_account_menu()
{
	// Check if WP_FS__DEV_MODE constant is defined and true
	if (defined('WPBRIGADE_SDK__DEV_MODE') && WPBRIGADE_SDK__DEV_MODE === true) {
		add_menu_page(
			'Custom Page Title', // Page title
			'account',       // Menu title
			'manage_options',    // Capability required
			'account',  // Menu slug
			'account_page_content' // Callback function to render page content
		);
	}

	// Enqueue a script to delay the removal of the menu page after a short delay
	add_action('admin_enqueue_scripts', 'delayed_remove_menu_page');
}

add_action('admin_menu', 'custom_account_menu');

// Callback function for 'account' page content
function account_page_content()
{
	// Check if WP_FS__DEV_MODE constant is defined and true
	if (defined('WPBRIGADE_SDK__DEV_MODE') && WPBRIGADE_SDK__DEV_MODE === true) {
		// Include the template file from the views folder
		include_once plugin_dir_path(__FILE__) . '../views/account.php';
	} else {
		// Render the default content
	?>
		<div class="wrap">
			<h1>WPB Debug Page</h1>
		</div>
<?php
	}
}

// JavaScript function to remove menu page after a delay
function delayed_remove_menu_page()
{
	remove_menu_page('account');
}
