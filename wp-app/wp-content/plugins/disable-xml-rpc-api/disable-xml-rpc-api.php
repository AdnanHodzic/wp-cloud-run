<?php
/*
Plugin Name: Disable XML-RPC-API
Plugin URI: https://neatma.com/dsxmlrpc-plugin/
Description: Lightweight plugin to disable XML-RPC API and Pingbacks,Trackbacks for faster and more secure website.
Version: 2.1.7
Tested up to: 6.7
Requires at least: 5.0
Author: Neatma
Author URI: https://neatma.com/
Text Domain: dsxmlrpc
License: GPLv2
*/

namespace dsxmlrpc;

if (!defined('ABSPATH')) {
    exit;
}


// Constants
define('DSXMLRPC_FILE', __FILE__);
define('DSXMLRPC_PLUGIN_FILE', plugin_dir_path(__FILE__));
define('DSXMLRPC_URL', plugin_dir_url(__FILE__));
define('DSXMLRPC_HOME_PATH', function_exists('get_home_path') ? get_home_path() : ABSPATH);

require_once(DSXMLRPC_PLUGIN_FILE . '/admin/admin.php');
require_once(DSXMLRPC_PLUGIN_FILE . '/lib/skelet/framework.config.php');
if (!class_exists('PAnD')) {
    require_once(DSXMLRPC_PLUGIN_FILE . '/lib/admin-notices/persist-admin-notices-dismissal.php');
}


/**
 * Class xmlrpcSecurity
 * @package dsxmlrpc
 */
class xmlrpcSecurity
{


    public function __construct()
    {

        $disabled_methods = $this->get_option('disabled-methods');

        // Activation hook
        register_activation_hook(DSXMLRPC_FILE, [$this, 'add_htaccess']);

        // Deactivation hook
        register_deactivation_hook(DSXMLRPC_FILE, [$this, 'pluginDeactivated']);


        //add_action('admin_init', ['PAnD', 'init']);
        add_filter('wp_xmlrpc_server_class', [$this, 'disable_wp_xmlrpc']);
        add_action('admin_head', [$this, 'add_htaccess']);
        add_action('init', [$this, 'speedUpWordpress']);

        add_action( 'admin_enqueue_scripts', [$this, 'load_plugin_scripts']);

        if (isset($disabled_methods) && is_array($disabled_methods)) {
            add_action('init', [$this, 'removeSelectedMethods']);
        }

        if ($this->get_option('remove-emojis')) {
            add_action('init', [$this, 'removeEmojies']);
        }
        if (!empty($this->get_option('xmlrpc-slug')) && $this->get_option('dsxmlrpc-switcher')) {
            add_action('wp_loaded', [$this, 'xmlrpc_rename_wp_loaded']);
        }

        // remove rss
        if ($this->get_option('remove-rss')) {
            remove_action('wp_head', 'rsd_link');
            remove_action('wp_head', 'feed_links', 2);
            remove_action('wp_head', 'feed_links_extra', 3);

            add_action('do_feed', [$this, 'disable_feed'], 1);
            add_action('do_feed_rdf', [$this, 'disable_feed'], 1);
            add_action('do_feed_rss', [$this, 'disable_feed'], 1);
            add_action('do_feed_rss2', [$this, 'disable_feed'], 1);
            add_action('do_feed_atom', [$this, 'disable_feed'], 1);
            add_action('do_feed_rss2_comments', [$this, 'disable_feed'], 1);
            add_action('do_feed_atom_comments', [$this, 'disable_feed'], 1);
        }
        // Load language files
        add_action('plugins_loaded', [$this, 'loadTextdomain']);

    }

        /**
     * Load plugin textdomain for translations.
     */
    public function loadTextdomain()
    {
        load_plugin_textdomain('dsxmlrpc', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * @param $option
     * @return mixed
     * Get options
     */
    public function get_option($option)
    {
        $options = get_option('dsxmlrpc-settings');
        if (isset($options[$option])) {
            return $options[$option];
        }
    }

    /**
     * @return xmlrpcSecurity
     */
    static function initialize()
    {
        return new static();
    }

    /**
     * Deactivation method
     */
    public function pluginDeactivated()
    {
        $htaccess_file = DSXMLRPC_HOME_PATH . '.htaccess';
        $this->file_chmod();
        $this->remove_with_markers('DS-XML-RPC-API', $htaccess_file);

        delete_option('pand-' . md5('wpsg-notice'));
        delete_option('pand-' . md5('dsxmlrpc-notice'));

    }

    /**
     * Change htaccess permissions to writeable
     */
    function file_chmod()
    {
        $htaccess_file = DSXMLRPC_HOME_PATH . '.htaccess';
        if (!is_writable($htaccess_file)) {
            chmod($htaccess_file, 0644);
        }

    }

    /**
     * @param $marker
     * @param $filename
     * Remove with markers from files (.htaccess)
     */
    public function remove_with_markers($marker, $filename)
    {
        if (file_exists($filename)) {
            $myfile  = file_get_contents($filename);
            $pattern = "/#.BEGIN $marker(?<=# BEGIN $marker).*(?=# END $marker)#.END $marker/sui";

            $result = preg_replace($pattern, '', $myfile);
            $result = preg_replace('/\s+$/sui', '', $result);
            file_put_contents($filename, $result);
        }
    }

    /**
     * Remove Rss Feed
     */
    public function disable_feed()
    {
        wp_die(__('No feed available,please visit our <a href="' . get_bloginfo('url') . '">homepage</a>!'));
    }

    /**
     * Disable access to xmlrpc.php completely with .htaccess file
     */
    public function add_htaccess()
    {
        global $current_screen;
        if ($current_screen->id == 'toplevel_page_Security Settings' || $current_screen->id == 'plugins') {
			$htaccess_code = '';
			
            if ($this->get_option('hotlink-fix')) {
                $home_url      = get_home_url();
                $htaccess_code = '
RewriteEngine on
RewriteCond %{HTTP_REFERER} !^$
RewriteCond %{HTTP_REFERER} !^' . $home_url . ' [NC]
RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?google.com [NC]
RewriteRule \.(jpg|jpeg|png|gif)$ – [NC,F,L]

';
            }


            if ($this->get_option('jetpack-switcher')) {
                $jp_allowed_ips = '
Allow from 122.248.245.244/32
Allow from 54.217.201.243/32
Allow from 54.232.116.4/32
Allow from 192.0.80.0/20
Allow from 192.0.96.0/20
Allow from 192.0.112.0/20
Allow from 195.234.108.0/22
Allow from 192.0.64.0/18';
            } else {
                $jp_allowed_ips = '';
            }


            if (!$this->get_option('dsxmlrpc-switcher')) {

                $allowed_ips   = $this->fix_ip('White-list-IPs') . $jp_allowed_ips;
                $htaccess_code .= '<Files xmlrpc.php>
order deny,allow
deny from all
' . $allowed_ips . '
</Files>
';
            } else {

                $disallowed_ips = $this->fix_ip('Black-list-IPs');
                $htaccess_code  .= '<Files xmlrpc.php>
order allow,deny
allow from all
' . $disallowed_ips . '
</Files>
';
            }
			
            $this->file_chmod();
            insert_with_markers(DSXMLRPC_HOME_PATH . '.htaccess', 'DS-XML-RPC-API', $htaccess_code);
            $this->get_option('htaccess protection') ? $this->file_protect() : '';

        }
    }

    /**
     * @param $type
     * @return string|void
     * Fix IP list
     */
    public function fix_ip($type)
    {
        if (!$this->get_option($type)) return;
        $ip_list = $this->get_option($type);
        $ips     = explode(",", $ip_list);
        foreach ((array)$ips as $ip) {
            $ip = trim($ip);
            if (!filter_var($ip, FILTER_VALIDATE_IP) === false) {
                if ($type == 'White-list-IPs') {
                    return "Allow from " . $ip . "\n";
                } elseif ($type == 'Black-list-IPs') {
                    return "Deny from " . $ip . "\n";

                }
            }
        }
    }

    /**
     * Change htaccess permissions to readonly
     */
    public function file_protect()
    {
        $htaccess_file = DSXMLRPC_HOME_PATH . '.htaccess';
        if (is_writable($htaccess_file)) {
            chmod($htaccess_file, 0444);
        }
    }



    /**
     * @param $xmlrpc
     * @return array
     * Disable XML-RPC Methods
     */
    public function dis_methods($xmlrpc)
    {
        if (is_array($xmlrpc)) {
            $methods = $this->get_option('disabled-methods');
            foreach ($methods as $method) {

                unset($xmlrpc[$method]);
            }
        }
        return $xmlrpc;

    }

    /**
     * @param $method
     * @return array
     * Get XML-RPC Disabled Methods
     */
    public function get_methods($method)
    {
        $option = $this->get_option('disabled-methods');
        if (in_array($method, $option)) {
            return array($method);
        }

    }

    /**
     * @param $headers
     * @return mixed
     * Remove x-pingback from header
     */
    public function X_pingback_header($headers)
    {
        unset($headers['X-Pingback']);
        return $headers;
    }

    /**
     * Remove selected methods from xml rpc
     */
    public function removeSelectedMethods()
    {
        $disabled_methods = $this->get_option('disabled-methods');
        if (!$this->get_option('dsxmlrpc-switcher') || array_search('x-pingback', $disabled_methods)) {
            add_filter('wp_headers', array($this, 'X_pingback_header'));
            add_filter('pings_open', '__return_false', PHP_INT_MAX);
        }

    }

    /**
     * @return bool
     * Rename the XML-RPC
     */
    public function xmlrpc_rename_wp_loaded()
    {

        $page = $this->get_current_page();

        if ($page === 'xmlrpc.php') {
            $header_one = apply_filters('header_1', 'HTTP/1.0 404 Not Found');
            $header_two = apply_filters('header_2', 'Status: 404 Not Found');

            header($header_one);
            header($header_two);

            exit();
        }

        if ($page !== $this->get_option('xmlrpc-slug')) {
            return false;
        }

        @define('NO_CACHE', true);
        @define('WTC_IN_MINIFY', true);
        @define('WP_CACHE', false);

        // Prevent errors from defining constants again
        error_reporting(E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR);

        include ABSPATH . '/xmlrpc.php';

        exit();

    }

    /**
     * @return mixed
     * Find the page being accessed
     */
    public function get_current_page()
    {

        $blog_url = trailingslashit(get_bloginfo('url'));

        // Build the Current URL
        $url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        if (is_ssl() && preg_match('/^http\:/is', $blog_url)) {
            $blog_url = substr_replace($blog_url, 's', 4, 0);
        }

        // The relative URL to the Blog URL
        $req = str_replace($blog_url, '', $url);
        $req = str_replace('index.php/', '', $req);

        // We dont need the args
        $parts    = explode('?', $req, 2);
        $relative = basename($parts[0]);

        // Remove trailing slash
        $relative = rtrim($relative, '/');
        $tmp      = explode('/', $relative, 2);
        $page     = end($tmp);

        return $page;

    }

    /**
     * Speed Up wordprees
     * remove emoji
     */
    public function removeEmojies()
    {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    }

    /**
     * Speed Up wordprees
     */
    public function speedUpWordpress()
    {
        /* slow down the heartbeat */
        if ($this->get_option('slow-heartbeat')) {
            add_filter('heartbeat_settings', [$this, 'slow_heartbeat']);
        }
		
        /* Disable wp-json rest api */
        if ($this->get_option('json-rest-api')) {
            add_filter('rest_authentication_errors', function ($result) {
                if (!empty($result)) {
                    return $result;
                }
                if (!is_user_logged_in()) {
                    return new \WP_Error('restx_logged_out', 'Sorry, you must be logged in to make a request.', array('status' => 401));
                }
                return $result;
            });
        }

        /* remove wlw from manifest */
        if ($this->get_option('disable-wlw')) {
            remove_action('wp_head', 'wlwmanifest_link');
        }
        /* disable built-in file editor */
        if ($this->get_option('disable-code-editor') && !defined('DISALLOW_FILE_EDIT')) {
            define('DISALLOW_FILE_EDIT', true);
        }
        /* disable oEmbed for youtube */
        if ($this->get_option('disable-oembed')) {
            add_action('wp_footer', [$this, 'disable_oembed'], 11);
        }

        // Remove the WordPress version info url parameter
        if ($this->get_option('remove-wp-ver') && !is_admin()) {
            remove_action('wp_head', 'wp_generator');
            add_filter('script_loader_src', [$this, 'remove_ver_param']);
            add_filter('style_loader_src', [$this, 'remove_ver_param']);
        }

    }

    /**
     * Remove the WordPress version info url parameter.
     */
    public function remove_ver_param($url)
    {
        return remove_query_arg('ver', $url);
    }

    /**
     * @param $settings
     * @return mixed
     * Slow down the wordpress hearbeat
     */
    public function slow_heartbeat($settings)
    {
        $settings['interval'] = 60;
        return $settings;
    }

    /**
     * Dequeue the oEmbed script.
     */
    public function disable_oembed()
    {
        wp_dequeue_script('wp-embed');
    }

    /**
     * Fallback for disabling the xmlrpc if .htaccess not working
     */
    function disable_wp_xmlrpc($data)
    {
        $client_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ip_whitelist = $this->get_option('White-list-IPs');
        $ip_whitelist_array = is_string($ip_whitelist) ? explode(',', $ip_whitelist) : [];
        // Deny access if the client's IP is not in the whitelisted or global swicher is off
        if (!in_array($client_ip, $ip_whitelist_array, true) && !$this->get_option('dsxmlrpc-switcher')) {
            header('HTTP/1.1 403 Forbidden');
            exit('Access to XML-RPC is disabled by site admin!');
        }


        return $data;
    }


    /**
     *  Enqueue plugin scripts.
     */
    function load_plugin_scripts() {
        wp_enqueue_script( 'dsxmlrpc_admin', DSXMLRPC_URL . '/admin/admin.js', array('jquery'), '1.0.1' );
    }

}


// Initialize the main class
xmlrpcSecurity::initialize();