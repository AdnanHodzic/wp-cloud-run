<?php
/**
 * Core functions
 *
 * @since 2.0.0
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 */

use AdvancedAds\Framework\JSON;

/**
 * Add to JSON object.
 *
 * @since  1.0.0
 *
 * @param mixed ...$args Arguments.
 *
 * Parameters can be
 * 1. array|string Unique identifier or array<key, value>.
 * 2. array|string The data itself can be either a scalar or an array.
 *                 In Case of first param an array this can be object_name.
 * 3. string Name for the JavaScript object.
 *           Passed directly, so it should be qualified JS variable.
 *
 * @return JSON
 */
function wp_advads_json_add( ...$args ): JSON {
	return wp_advads()->json->add( ...$args );
}

/**
 * Remove from JSON object.
 *
 * @since  1.0.0
 *
 * @param string $key         Unique identifier.
 * @param string $object_name Name for the JavaScript object.
 *                            Passed directly, so it should be qualified JS variable.
 * @return JSON
 */
function wp_advads_json_remove( $key, $object_name = false ): JSON {
	return wp_advads()->json->remove( $key, $object_name );
}
