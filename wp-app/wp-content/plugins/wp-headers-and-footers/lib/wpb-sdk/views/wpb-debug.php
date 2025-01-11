<?php
// Enqueue CSS file for admin debugging
function enqueue_custom_styles()
{
    // Enqueue the debug.css file from your plugin's directory
    wp_enqueue_style('custom-debug-style', plugins_url('admin/css/debug.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'enqueue_custom_styles');


$slug = get_option('wpb_sdk_module_slug');
$id = get_option('wpb_sdk_module_id');

// Initialize an array to store all plugins
$all_plugins = [];

// Instantiate the Logger class
$wpb = WPBRIGADE_Logger::instance($id, $slug, true);
$Data = $wpb->get_logs_data($slug);

$plugin_path = $Data['product_info']['path'];
$plugins = array_keys(get_plugins());
$active_plugins = get_option('active_plugins', array());
$sdk_path = WPBRIGADE_SDK_DIR;

// Check if SDK path contains the slug
$this_sdk_path = strstr($sdk_path, $slug);
if ($this_sdk_path !== false) {
    $this_sdk_path = '\\' . ltrim($this_sdk_path, '\\');
}

// Clear API cache if requested
if (isset($_POST['wpb_clear_api_cache']) && $_POST['wpb_clear_api_cache'] === 'true') {
    update_option('wpb_api_cache', null);
}

// Clear updates data if requested
if (isset($_POST['wpb_action']) && $_POST['wpb_action'] === 'clear_updates_data') {
    set_site_transient('update_plugins', null);
    set_site_transient('update_themes', null);
}

// Send logs data to external API if background sync is requested
if (isset($_POST['background_sync']) && $_POST['background_sync'] === 'true') {
    $response = wp_remote_post(
        WPBRIGADE_SDK_API_ENDPOINT,
        array(
            'method'  => 'POST',
            'body'    => $Data,
            'timeout' => 5,
            'headers' => array(),
        )
    );

    if (is_wp_error($response)) {
        error_log('Error sending data: ' . $response->get_error_message());
    } else {
        error_log('Log sent successfully' . wp_json_encode($Data));
    }
}

// Function to set an option value in the database
function custom_plugin_set_option($option_name, $option_value)
{
    update_option($option_name, $option_value);
}

// Handle form submission to set option value
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_option_name']) && isset($_POST['option_value'])) {
    $option_name = $_POST['set_option_name'];
    $option_value = $_POST['option_value'];

    custom_plugin_set_option($option_name, $option_value);

    echo '<div id="success_message">Successfully set the option</div>';
}

// Function to get an option value from the database
function custom_plugin_get_option_value($option_name)
{
    return get_option($option_name);
}

// Handle form submission to load option value
$option_value = '';
$result_visible = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['load_option_name'])) {
    $option_name = $_POST['load_option_name'];
    $option_value = custom_plugin_get_option_value($option_name);
    $result_visible = true;
}
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<h1>WPB Debug - SDK v.<?php echo WP_WPBRIGADE_SDK_VERSION; ?></h1>

<h2>Actions</h2>
<table>
    <tbody>
        <tr>
            <td>
                <!-- Clear API Cache -->
                <form action="" method="POST">
                    <input type="hidden" name="wpb_clear_api_cache" value="true">
                    <button class="button button-primary">Clear API Cache</button>
                </form>
            </td>
            <td>
                <!-- Clear Updates Transients -->
                <form action="" method="POST">
                    <input type="hidden" name="wpb_action" value="clear_updates_data">
                    <button class="button">Clear Updates Transients</button>
                </form>
            </td>
            <td>
                <!-- Sync Data with Server -->
                <form action="" method="POST">
                    <input type="hidden" name="background_sync" value="true">
                    <button class="button button-primary">Sync Data From Server</button>
                </form>
            </td>
            <td>
                <!-- Load DB Option -->
                <form method="post">
                    <button type="button" class="button" id="show_input_button">Load DB Option</button>
                    <div id="input_field" style="display: none;">
                        <input type="text" name="load_option_name" id="option_name_input">
                        <button type="submit" id="submit_option_button">Submit</button>
                    </div>
                </form>
                <div id="result" <?php if (!$result_visible) echo 'style="display: none;"'; ?>>
                    <?php
                    if (is_array($option_value)) {
                        echo 'Option Value: ' . implode(', ', $option_value);
                    } else {
                        echo 'Option Value: ' . $option_value;
                    }
                    ?>
                    <button id="clear_result_button">✖</button>
                </div>
            </td>
            <td>
                <!-- Set DB Option -->
                <button type="button" class="button" id="set_option_button">Set DB Option</button>
                <form id="set_option_form" method="post" style="display: none; margin-right: 10px;">
                    <div class="option-input-wrapper" style="display: inline-block;">
                        <label for="option_name">Option Name:</label>
                        <input type="text" name="set_option_name" id="option_name">
                    </div>
                    <div class="option-input-wrapper">
                        <label for="option_value">Option Value:</label>
                        <input type="text" name="option_value" id="option_value">
                    </div>
                    <button type="submit" id="submit_set_option_button">Set Option</button>
                </form>
            </td>
        </tr>
    </tbody>
</table>

<br>

<table class="widefat">
    <thead>
        <tr>
            <th>Key</th>
            <th>Value</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>WP_WPB__REMOTE_ADDR</td>
            <td><?php echo $_SERVER['SERVER_ADDR']; ?></td>
        </tr>
        <tr class="alternate">
            <td>WP_WPB__DIR</td>
            <td><?php echo WPBRIGADE_SDK_DIR; ?></td>
        </tr>
        <tr class="alternate">
            <td>wp_using_ext_object_cache()</td>
            <td>false</td>
        </tr>
    </tbody>
</table>

<h2>SDK Versions</h2>
<table id="wpb_sdks" class="widefat">
    <thead>
        <tr>
            <th>Version</th>
            <th>SDK Path</th>
            <th>Module Path</th>
            <th>Is Active</th>
        </tr>
    </thead>
    <tbody>
        <tr style="background: #E6FFE6; font-weight: bold">
            <td><?php echo WP_WPBRIGADE_SDK_VERSION; ?></td>
            <td><?php echo WPBRIGADE_SDK_DIR; ?></td>
            <td><?php echo WPBRIGADE_PLUGIN_DIR; ?></td>
            <td>Active</td>
        </tr>
    </tbody>
</table>

<h2>Plugins</h2>
<table id="wpb_sdks" class="widefat">
    <thead>
        <tr>
            <th>ID</th>
            <th>Slug</th>
            <th>Version</th>
            <th>Title</th>
            <th>API</th>
            <th>Telemetry State</th>
            <th>Module Path</th>
            <th>Public Key</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?php echo $id; ?></td>
            <td><?php echo $Data['product_info']['slug']; ?></td>
            <td><?php echo $Data['product_info']['version']; ?></td>
            <td><?php echo $Data['product_info']['name']; ?></td>
            <td></td>
            <td></td>
            <td><?php echo WPBRIGADE_PLUGIN_DIR ?></td>
            <td><?php echo $Data['authentication']['public_key']; ?></td>
            <td>
                <button class="button" id="show-account-button" onclick="window.location.href = '<?php echo admin_url('admin.php?page=account'); ?>'">Account</button>
            </td>
        </tr>
    </tbody>
</table>

<h2>Plugins/Sites</h2>
<table id="wpb_sdks" class="widefat">
    <thead>
        <tr>
            <th>ID</th>
            <th>Slug</th>
            <th>User ID</th>
            <th>License ID</th>
            <th>Plan</th>
            <th>Public Key</th>
            <th>Secret Key</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>3538</td>
            <td><?php echo $Data['product_info']['slug']; ?></td>
            <td></td>
            <td></td>
            <td></td>
            <td><?php echo $Data['authentication']['public_key']; ?></td>
            <td><?php echo $Data['authentication']['public_key']; ?></td>
        </tr>
    </tbody>
</table>

<h2>Users</h2>
<table id="wpb_users" class="widefat">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Verified</th>
            <th>Public Key</th>
            <th>Secret Key</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>3538</td>
            <td><?php echo $Data['user_info']['user_nickname']; ?></td>
            <td><?php echo $Data['user_info']['user_email']; ?></td>
            <td></td>
            <td><?php echo $Data['authentication']['public_key']; ?></td>
            <td><?php echo $Data['authentication']['public_key']; ?></td>
        </tr>
    </tbody>
</table>


<!-- JavaScript code to show/hide input field -->
<script>
    // Load DB Option
    document.getElementById('show_input_button').addEventListener('click', function() {
        document.getElementById('input_field').style.display = 'block';
    });

    document.getElementById('submit_option_button').addEventListener('click', function() {
        // Hide the input field
        document.getElementById('input_field').style.display = 'none';
        // Set the result container to be visible
        document.getElementById('result').style.display = 'block';
    });

    document.getElementById('clear_result_button').addEventListener('click', function() {
        // Hide the result container
        document.getElementById('result').style.display = 'none';
    });

    // Set DB Option
    document.getElementById('set_option_button').addEventListener('click', function() {
        // Show the form
        document.getElementById('set_option_form').style.display = 'block';
    });

    document.getElementById('set_option_form').addEventListener('submit', function(event) {
        // Hide the form
        document.getElementById('set_option_form').style.display = 'none';
        // Get the option name and value from the form
        var optionName = document.getElementById('option_name').value;
        var optionValue = document.getElementById('option_value').value;
    });

    document.getElementById('submit_set_option_button').addEventListener('click', function() {
        // Hide the input fields
        document.getElementById('option_name').style.display = 'none';
        document.getElementById('option_value').style.display = 'none';
    });

    setTimeout(function() {
        document.getElementById('success_message').style.display = 'none';
    }, 3000);
</script>