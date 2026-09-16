<?php
/**
 * Plugin Name: Disable JPEG Downscaling and Compression
 * Description: Prevents WordPress from automatically downscaling images larger than 2560px and preserves 100% JPEG quality.
 * Author: Adnan Hodzic
 * Version: 1.0.0
 */

// Disable automatic downscaling of images exceeding 2560px (Big Image Threshold)
add_filter( 'big_image_size_threshold', '__return_false', 999 );

// Set JPEG compression quality to 100%
add_filter( 'jpeg_quality', function( $quality ) {
    return 100;
}, 999 );

add_filter( 'wp_editor_set_quality', function( $quality, $mime_type ) {
    if ( 'image/jpeg' === $mime_type ) {
        return 100;
    }
    return $quality;
}, 999, 2 );
