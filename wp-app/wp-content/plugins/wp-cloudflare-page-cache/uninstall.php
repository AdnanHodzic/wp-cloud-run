<?php

// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

global $wpdb;

delete_option( 'swcfpc_config' );
delete_option( 'swcfpc_version' );
delete_option( 'swcfpc_preloader_lock' );
delete_option( 'swcfpc_purge_cache_lock' );
delete_option( 'swcfpc_fc_ttl_registry' );

$parts = parse_url( home_url() );

if ( file_exists( WP_CONTENT_DIR . "/wp-cloudflare-super-page-cache/{$parts['host']}/debug.log" ) ) {
	@unlink( WP_CONTENT_DIR . "/wp-cloudflare-super-page-cache/{$parts['host']}/debug.log" );
}

if ( defined( 'SWCFPC_ADVANCED_CACHE' ) ) {
	@unlink( WP_CONTENT_DIR . '/advanced-cache.php' );
}

$config_file_path = ABSPATH . 'wp-config.php';

$parts                    = parse_url( home_url() );
$plugin_storage_main_path = WP_CONTENT_DIR . '/wp-cloudflare-super-page-cache/';
$plugin_storage_path      = $plugin_storage_main_path . $parts['host'];

if ( file_exists( $config_file_path ) && is_writable( $config_file_path ) ) {

	// Get content of the config file.
	$config_file       = explode( "\n", file_get_contents( $config_file_path ) );
	$config_file_count = count( $config_file );
	$last_line         = '';

	for ( $i = 0; $i < $config_file_count; ++$i ) {

		// Remove double empty line
		if ( $i > 0 && trim( $config_file[ $i ] ) == '' && $last_line == '' ) {
			unset( $config_file[ $i ] );
			continue;
		}

		$last_line = trim( $config_file[ $i ] );

		if ( ! preg_match( '/^define\(\s*\'([A-Z_]+)\',(.*)\)/', $config_file[ $i ], $match ) ) {
			continue;
		}

		if ( 'WP_CACHE' === $match[1] && strpos( $config_file[ $i ], 'Added by WP Cloudflare Super Page Cache' ) !== false ) {
			unset( $config_file[ $i ] );
			$last_line = '';
			continue;
		}   
	}

	if ( trim( $config_file[ $config_file_count - 1 ] ) == '' ) {
		unset( $config_file[ $config_file_count - 1 ] );
	}

	// Insert the constant in wp-config.php file.
	$handle = @fopen( $config_file_path, 'w' );

	foreach ( $config_file as $line ) {
		@fwrite( $handle, $line . "\n" );
	}

	@fclose( $handle );

}

$timestamp = wp_next_scheduled( 'swcfpc_cache_purge_cron' );
wp_unschedule_event( $timestamp, 'swcfpc_cache_purge_cron' );

if ( file_exists( $plugin_storage_path ) ) {
	delete_directory_recursive( $plugin_storage_path );
}

if ( file_exists( $plugin_storage_main_path ) && is_directory_empty( $plugin_storage_main_path ) ) {
	rmdir( $plugin_storage_main_path );
}

function delete_directory_recursive( $dir ) { 
	if ( ! class_exists( 'RecursiveDirectoryIterator' ) || ! class_exists( 'RecursiveIteratorIterator' ) ) {
		return false;
	}

	$it    = new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS );
	$files = new RecursiveIteratorIterator( $it, RecursiveIteratorIterator::CHILD_FIRST );

	foreach ( $files as $file ) {

		if ( $file->isDir() ) {
			rmdir( $file->getRealPath() );
		} else {
			unlink( $file->getRealPath() );
		}   
	}

	rmdir( $dir );

	return true;

}


function is_directory_empty( $dir ) { 
	$handle = opendir( $dir );

	while ( false !== ( $entry = readdir( $handle ) ) ) {
		if ( $entry != '.' && $entry != '..' ) {
			closedir( $handle );
			return false;
		}
	}

	closedir( $handle );

	return true;

}
