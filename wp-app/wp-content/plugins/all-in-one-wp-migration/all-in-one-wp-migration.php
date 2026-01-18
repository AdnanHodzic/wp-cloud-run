<?php
/**
 * Plugin Name: All-in-One WP Migration and Backup
 * Plugin URI: https://servmask.com/
 * Description: All-in-One WP Migration makes moving your entire WordPress site simple. Export or import your database, media, plugins, and themes with just a few clicks.
 * Author: ServMask
 * Author URI: https://servmask.com/
 * Version: 7.101
 * Text Domain: all-in-one-wp-migration
 * Domain Path: /languages
 * Network: True
 * License: GPLv3
 *
 * Copyright (C) 2014-2025 ServMask Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Attribution: This code is part of the All-in-One WP Migration plugin, developed by
 *
 * ███████╗███████╗██████╗ ██╗   ██╗███╗   ███╗ █████╗ ███████╗██╗  ██╗
 * ██╔════╝██╔════╝██╔══██╗██║   ██║████╗ ████║██╔══██╗██╔════╝██║ ██╔╝
 * ███████╗█████╗  ██████╔╝██║   ██║██╔████╔██║███████║███████╗█████╔╝
 * ╚════██║██╔══╝  ██╔══██╗╚██╗ ██╔╝██║╚██╔╝██║██╔══██║╚════██║██╔═██╗
 * ███████║███████╗██║  ██║ ╚████╔╝ ██║ ╚═╝ ██║██║  ██║███████║██║  ██╗
 * ╚══════╝╚══════╝╚═╝  ╚═╝  ╚═══╝  ╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Kangaroos cannot jump here' );
}

// Check SSL mode
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && ( $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) ) {
	$_SERVER['HTTPS'] = 'on';
}

// Plugin basename
define( 'AI1WM_PLUGIN_BASENAME', basename( __DIR__ ) . '/' . basename( __FILE__ ) );

// Plugin path
define( 'AI1WM_PATH', __DIR__ );

// Plugin URL
define( 'AI1WM_URL', plugins_url( '', AI1WM_PLUGIN_BASENAME ) );

// Plugin storage URL
define( 'AI1WM_STORAGE_URL', plugins_url( 'storage', AI1WM_PLUGIN_BASENAME ) );

// Include constants
require_once __DIR__ . DIRECTORY_SEPARATOR . 'constants.php';

// Include deprecated
require_once __DIR__ . DIRECTORY_SEPARATOR . 'deprecated.php';

// Include functions
require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';

// Include exceptions
require_once __DIR__ . DIRECTORY_SEPARATOR . 'exceptions.php';

// Include loader
require_once __DIR__ . DIRECTORY_SEPARATOR . 'loader.php';

// Plugin initialization
$main_controller = new Ai1wm_Main_Controller();
