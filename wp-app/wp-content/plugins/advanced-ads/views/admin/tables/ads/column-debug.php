<?php
/**
 * Render 'debug' column.
 *
 * @package AdvancedAds
 *
 * @var bool $debug_mode Debug mode checked or not.
 */

echo $debug_mode ? esc_html__( 'Enabled', 'advanced-ads' ) : '&mdash;';
