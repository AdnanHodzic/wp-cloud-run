<?php
/**
 * Default hooks
 *
 * @since 2.0.0
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 */

use AdvancedAds\Framework\Utilities\Formatting;

add_filter( 'advanced-ads-ad-get-once_per_page', [ Formatting::class, 'string_to_bool' ] );
