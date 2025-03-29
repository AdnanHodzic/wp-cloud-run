<?php
/**
 * Render 'debug' column.
 *
 * @package AdvancedAds
 *
 * @var bool $display_once 'Display once' checked or not.
 */

echo $display_once ? esc_html__( 'Enabled', 'advanced-ads' ) : '&mdash;';
