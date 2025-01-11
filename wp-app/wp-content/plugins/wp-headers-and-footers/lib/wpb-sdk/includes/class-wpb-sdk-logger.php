<?php

class WPBRIGADE_Logger
{
    private static $_instances = array();
    private static $product_data = array();
    private static $_module_id;

    private static $current_uninstall_slug = null;

    // Constructor for the Logger class
    private function __construct($module_id, $slug = false, $is_init = false)
    {
        if (!$is_init && !is_numeric($module_id) && !is_string($slug)) {
            return false;
        }
        self::$_module_id = $module_id;
        self::$current_uninstall_slug = $slug; // Set current uninstall slug
    }

    // Method to create or retrieve a Logger instance
    public static function instance($module_id, $slug = false, $is_init = false)
    {
        if (empty($module_id)) {
            return false;
        }
        if (!$is_init && true === $slug) {
            $is_init = true;
        }

        if (!isset(self::$_instances[$slug])) {
            self::$_instances[$slug] = new WPBRIGADE_Logger($module_id, $slug, $is_init);
        }

        return self::$_instances[$slug];
    }

    // Method to initialize the Logger with module data
    public function wpb_init(array $module)
    {
        $key = $module['slug'];
        self::$product_data[$key] = [];
        self::$product_data[$key]['module'] = $module;
        $this->hooks($module['slug']);
    }

    // Method to attach hooks for scheduled events and AJAX
    public function hooks($slug)
    {
        // Initialize custom schedules
        add_action('init', function () use ($slug) {
            $this->set_logs_schedule($slug);
        });

        // Daily log plugin execution
        add_action('wpb_data_sync_' . $slug, function () use ($slug) {
            $this->daily_log_plugin($slug);
        });

        // Admin footer hook
        add_action('admin_footer', function () use ($slug) {
            $this->deactivation_model($slug);
        });

        // AJAX deactivation action
        add_action('wp_ajax_wpb_sdk_' . $slug . '_deactivation', function () use ($slug) {
            $this->ajax_deactivation($slug);
        });

        // Plugin activation hook
        register_activation_hook(wpb_get_plugin_path($slug), function () use ($slug) {
            $this->log_activation($slug);
        });

        // Plugin deactivation hook
        register_deactivation_hook(wpb_get_plugin_path($slug), function () use ($slug) {
            $this->product_deactivation($slug);
        });

        // Plugin uninstallation hook
        register_uninstall_hook(wpb_get_plugin_path($slug), array(__CLASS__, 'log_uninstallation'));
    }

    // Method to set scheduled events for logging
    public function set_logs_schedule($slug)
    {
         //Clean Old Cron jobs
         wp_clear_scheduled_hook('wpb_logger_cron_' . $slug);
         wp_clear_scheduled_hook('wpb_daily_sync_cron_' . $slug);

        // Calculate future timestamps for scheduling
        $daily_start_time = strtotime('+1 day');
        // Schedule daily cron event if not already scheduled
        if (!wp_next_scheduled('wpb_data_sync_' . $slug)) {
            wp_schedule_event($daily_start_time, 'daily', 'wpb_data_sync_' . $slug);
        }

    }

    public static function reset_logs_schedule($slug)
    {
        // Calculate future timestamps for scheduling
        $daily_start_time = strtotime('+1 day');

        // Schedule daily cron event if not already scheduled
        if (!wp_next_scheduled('wpb_data_sync_' . $slug)) {
            wp_schedule_event($daily_start_time, 'daily', 'wpb_data_sync_' . $slug);
        }
    }

    public static function remove_logs_schedule($slug)
    {
        wp_clear_scheduled_hook('wpb_data_sync_' . $slug);
    }

    // Method to log plugin activity on daily scheduled events
    public function daily_log_plugin($slug)
    {
        $sdk_data = json_decode(get_option('wpb_sdk_' . $slug), true);
        $user_skip = isset($sdk_data['user_skip']) ? $sdk_data['user_skip'] : false;
        $user_skip = $user_skip === "1" ? true : false;
        if ($user_skip) {
            $logs_data = self::get_logs_data($slug, 'user_skip');
            $sdk_data['user_skip'] = "0";
            $sdk_data_json = json_encode($sdk_data);
            update_option('wpb_sdk_' . $slug, $sdk_data_json);
        } else {
            $logs_data = self::get_logs_data($slug, 'daily');
        }

        if (!empty($logs_data)) {
            $logs_to_send = array_merge(
                $logs_data,
                array(
                    'explicit_logs' => array(
                        'action' => 'daily',
                    ),
                )
            );
            self::send($slug, $logs_to_send);
        }


    }

    // Method to log plugin activation
    public function log_activation($slug)
    {
        $logs_data = self::get_logs_data($slug, 'activate');
        if (!empty($logs_data)) {
            $logs_to_send = array_merge(
                $logs_data,
                array(
                    'explicit_logs' => array(
                        'action' => 'activate',
                    ),
                )
            );
            self::send($slug, $logs_to_send);
        }
    }

    // Method to add deactivation model HTML to admin footer
    public function deactivation_model($slug)
    {
        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();
            if ('plugins.php' === $screen->parent_file) {
                $plugin_data = wpb_get_plugin_details($slug);
                $product_name = $plugin_data['Name'];
                $product_slug = $slug;
                $has_pro_version = self::$product_data[$slug]['module']['is_premium'] === true;
                include dirname(__DIR__) . '/views/wpb-sdk-deactivate-form.php';
            }
        }
    }

    // Method to handle AJAX request for plugin deactivation
    public function ajax_deactivation($slug)
    {
        $path = wpb_get_plugin_path($slug);

        if (isset($_POST['nonce']) && empty($_POST['nonce'])) {
            return;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST['nonce']));
        $verify_nonce = wp_verify_nonce($nonce, 'deactivate-plugin_' . plugin_basename($path));

        if (!$verify_nonce) {
            return;
        }

        $this->log_deactivation($slug);

        wp_die();
    }

    // Method to handle plugin deactivation
    public function product_deactivation($slug)
    {
        wp_clear_scheduled_hook('wpb_data_sync_' . $slug);
    }

    // Method to log plugin deactivation
    public function log_deactivation($slug)
    {
        $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
        $reason_detail = isset($_POST['reason_detail']) ? $_POST['reason_detail'] : '';
        $logs_data = self::get_logs_data($slug, 'deactivate');
        if (!empty($logs_data)) {
            $logs_to_send = array_merge(
                $logs_data,
                array(
                    'explicit_logs' => array(
                        'action' => 'deactivate',
                        'reason' => sanitize_text_field(wp_unslash($reason)),
                        'reason_detail' => sanitize_text_field(wp_unslash($reason_detail)),
                    ),
                )
            );
            self::send($slug, $logs_to_send);
        }
    }

    // Method to log plugin uninstallation
    public static function log_uninstallation()
    {
        $slug = self::$current_uninstall_slug;
        $logs_data = self::get_logs_data($slug, 'uninstall');
        if (!empty($logs_data)) {
            $logs_to_send = array_merge(
                $logs_data,
                array(
                    'explicit_logs' => array(
                        'action' => 'uninstall',
                    ),
                )
            );
            self::send($slug, $logs_to_send);
        }
        // Call Plugin uninstall hook
        do_action('wp_wpb_sdk_after_uninstall');
    }

    /**
     * Collect all data for logging.
     *
     * @return array
     */
    public static function get_logs_data($slug, $action = '')
    {
        global $wpdb;

        // Get product data
        $module = self::$product_data[$slug]['module'];
        // Initialize variables
        $data = array();
        $theme_data = wp_get_theme();
        $curl_version = '';
        $external_http_blocked = '';
        $users_count = '';

        $sdk_data = json_decode(get_option('wpb_sdk_' . $slug), true);

        $sdk_communication = isset($sdk_data['communication']) ? $sdk_data['communication'] : '0';
        $sdk_diagnostic_info = isset($sdk_data['diagnostic_info']) ? $sdk_data['diagnostic_info'] : '0';
        $sdk_extensions = isset($sdk_data['extensions']) ? $sdk_data['extensions'] : '0';


        $send_wpb_sdk_communication = $sdk_communication === "1" ? true : false;
        $send_wpb_sdk_diagnostic_info = $sdk_diagnostic_info === "1" ? true : false;
        $send_wpb_sdk_extensions = $sdk_extensions === "1" ? true : false;


        if ($action != "user_skip") {
            if (!$send_wpb_sdk_communication && !$send_wpb_sdk_diagnostic_info && !$send_wpb_sdk_extensions) {
                WPBRIGADE_Logger::remove_logs_schedule($slug);
                return [];
            } else {
                WPBRIGADE_Logger::reset_logs_schedule($slug);
            }
        }
        // Get admin user data
        $admin_users = get_users(array('role' => 'Administrator'));
        $admin = isset($admin_users[0]) ? $admin_users[0]->data : '';
        $admin_meta = !empty($admin) ? get_user_meta($admin->ID) : '';


        // Collect data
        $data['authentication']['public_key'] = $module['public_key'];

        if ($action == "user_skip"||$send_wpb_sdk_communication) {
            // USER INFO
            $data['user_info'] = array(
                'user_email' => !empty($admin) ? sanitize_email($admin->user_email) : '',
                'user_nickname' => !empty($admin) ? sanitize_text_field($admin->user_nicename) : '',
                'user_firstname' => isset($admin_meta['first_name'][0]) ? sanitize_text_field($admin_meta['first_name'][0]) : '',
                'user_lastname' => isset($admin_meta['last_name'][0]) ? sanitize_text_field($admin_meta['last_name'][0]) : '',
            );
        }

        // PRODUCT INFO MUST HAVE
        $data['product_info'] = self::get_product_data($slug);
        $data['product_info']['sdk_version'] = WP_WPBRIGADE_SDK_VERSION;

        if ($action == "user_skip"||$send_wpb_sdk_diagnostic_info) {
            $data['product_info']['product_settings'] = self::get_product_settings($slug);
        }

        // SITE INFO MUST HAVE
        $data['site_info'] = array(
            'site_url' => site_url(),
            'home_url' => home_url(),
        );

        if ($action == "user_skip"||$send_wpb_sdk_diagnostic_info) {
            $ip = self::get_ip();
            $location = self::get_location_details($ip);

            // Check if get_plugins function exists
            if (!function_exists('get_plugins')) {
                include ABSPATH . '/wp-admin/includes/plugin.php';
            }

            // Get users count if function exists
            if (function_exists('count_users')) {
                $users_count = count_users();
                $users_count = isset($users_count['total_users']) ? intval($users_count['total_users']) : '';
            }

            // Check external http request blocking
            if (!defined('WP_HTTP_BLOCK_EXTERNAL') || !WP_HTTP_BLOCK_EXTERNAL) {
                $external_http_blocked = 'none';
            } else {
                $external_http_blocked = defined('WP_ACCESSIBLE_HOSTS') ? 'partially (accessible hosts: ' . esc_html(WP_ACCESSIBLE_HOSTS) . ')' : 'all';
            }

            // Get curl version if function exists
            if (function_exists('curl_init')) {
                $curl = curl_version();
                $curl_version = '(' . $curl['version'] . ' ' . $curl['ssl_version'] . ')';
            }


            $data['site_info']['site_meta_info'] = array(
                'is_multisite' => is_multisite(),
                'multisites' => self::get_multisites(),
                'php_version' => phpversion(),
                'wp_version' => get_bloginfo('version'),
                'server' => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '',
                'timezoneoffset' => date('P'),
                'ext/mysqli' => isset($wpdb->use_mysqli) && !empty($wpdb->use_mysqli) ? true : false,
                'mysql_version' => function_exists('mysqli_get_server_info') ? mysqli_get_server_info($wpdb->dbh) : '',
                'memory_limit' => (defined(WP_MEMORY_LIMIT) ? WP_MEMORY_LIMIT : ini_get('memory_limit')) ? ini_get('memory_limit') : '',
                'external_http_blocked' => $external_http_blocked,
                'wp_locale' => get_locale(),
                'db_charset' => defined('DB_CHARSET') ? DB_CHARSET : '',
                'debug_mode' => defined('WP_DEBUG') && WP_DEBUG ? true : false,
                'wp_max_upload' => size_format(wp_max_upload_size()),
                'php_time_limit' => function_exists('ini_get') ? ini_get('max_execution_time') : '',
                'php_error_log' => function_exists('ini_get') ? ini_get('error_log') : '',
                'fsockopen' => function_exists('fsockopen') ? true : false,
                'open_ssl' => defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : '',
                'curl' => $curl_version,
                'ip' => $ip,
                'user_count' => $users_count,
                'admin_email' => sanitize_email(get_bloginfo('admin_email')),
                'theme_name' => sanitize_text_field($theme_data->Name),
                'theme_version' => sanitize_text_field($theme_data->Version),
            );
            $data['site_info']['location_details'] = $location !== null ? $location : '';
        }

        // SITE PLUGINS
        if ($action == "user_skip"||$send_wpb_sdk_extensions) {
            $data['site_plugins'] = self::get_all_plugins();
        }

        return $data;
    }


    /**
     * Retrieve plugin settings related to the product.
     *
     * @return array
     */
    private static function get_product_settings($slug)
    {
        $product_data = self::$product_data[$slug]['module'];
        $plugin_options = array();

        // Pull settings data from db.
        foreach ($product_data['settings'] as $option_name => $default_value) {
            $get_option = get_option($option_name);
            $plugin_options[] = array(
                'option' => $option_name,
                'value' => !empty($get_option) ? wp_json_encode($get_option) : $default_value
            );
        }

        return $plugin_options;
    }

    /**
     * Collect multisite data.
     *
     * @return array|false
     */
    private static function get_multisites()
    {
        if (!is_multisite()) {
            return false;
        }

        $sites_info = array();
        $sites = get_sites();

        foreach ($sites as $site) {
            $sites_info[$site->blog_id] = array(
                'name' => get_blog_details($site->blog_id)->blogname,
                'domain' => $site->domain,
                'path' => $site->path,
            );
        }

        return $sites_info;
    }

    /**
     * Get user IP information.
     *
     * @return string|null
     */
    private static function get_ip()
    {
        $fields = array(
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        );

        foreach ($fields as $ip_field) {
            if (!empty($_SERVER[$ip_field])) {
                return $_SERVER[$ip_field];
            }
        }

        return null;
    }

    /**
     * Collect plugins information: Active/Inactive plugins.
     *
     * @return array
     */
    private static function get_all_plugins()
    {
        $all_plugins = array_keys(get_plugins());
        $active_plugins = get_option('active_plugins', array());
        $in_active_plugins = [];

        foreach ($all_plugins as $plugin) {
            if (!in_array($plugin, $active_plugins)) {
                // add in-active plugins in list.
                $in_active_plugins[] = $plugin;
            }
        }

        return array(
            'active' => $active_plugins,
            'inactive' => $in_active_plugins,
        );
    }

    /**
     * Get location details based on IP.
     *
     * @param string|null $ip
     * @return array
     */
    private static function get_location_details($ip)
    {
        $location_details = array();
        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "https://api.iplocation.net/?ip={$ip}");

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $execute = curl_exec($ch);

            curl_close($ch);

            $result = json_decode($execute);

            if ($result && $result->response_code === '200') {
                if ($result->country_name !== '-' && $result->country_code2 !== '-') {
                    $location_details['response_code'] = $result->response_code;
                    $location_details['message'] = 'Success';
                    $location_details['data']['country_name'] = $result->country_name;
                    $location_details['data']['country_code'] = $result->country_code2;
                } else {
                    $missing_info = array();
                    if ($result->country_name === '-') {
                        $missing_info[] = 'country_name';
                    }
                    if ($result->country_code2 === '-') {
                        $missing_info[] = 'country_code';
                    }
                    $location_details['response_code'] = '400';
                    $location_details['message'] = 'Error: Missing information for ' . implode(', ', $missing_info) . ' for the IP Address: ' . $ip;
                }
            } else {
                $location_details['response_code'] = '400';
                $location_details['message'] = 'Error: Invalid response code or data for the IP Address: ' . $ip;
            }

            return $location_details;
        } catch (\Exception $e) {
            $location_details['response_code'] = '400';
            $location_details['message'] = 'Error: ' . $e->getMessage();
            return $location_details;
        }
    }

    /**
     * Get product data.
     *
     * @param string $slug
     * @return array
     */
    private static function get_product_data($slug)
    {
        $plugin_data = wpb_get_plugin_details($slug);
        $data = array();
        $data['name'] = isset($plugin_data['Name']) ? $plugin_data['Name'] : $plugin_data['Title'];
        $data['slug'] = $slug;
        $data['id'] = self::$_module_id;
        $data['type'] = 'Plugin';
        $data['path'] = wpb_get_plugin_path($slug);
        $data['version'] = $plugin_data['Version'];
        return $data;
    }


    /**
     * Send log data to the API.
     *
     * @param array $payload The log data payload.
     */
    private static function send($slug, $payload)
    {
        // Add timestamp to the payload
        $payload['sent_at'] = current_time('mysql', 1);

        // Determine the log status
        $logStatus = 'new';
        $payload['log_status'] = $logStatus;

        self::sendDataToAPI($slug, $payload);

    }


    /**
     * Send data to the API endpoint.
     *
     * @param array $data The data to be sent.
     */
    private static function sendDataToAPI($slug, $data)
    {
        $token = self::$product_data[$slug]['module']['public_key'];

        $response = wp_remote_post(
            WPBRIGADE_SDK_API_ENDPOINT . '/logger',
            array(
                'method' => 'POST',
                'body' => $data,
                'headers' => array(
                    'Authorization' => 'Bearer ' . $token, // Add the token in the request headers
                ),
            )
        );
    }
}