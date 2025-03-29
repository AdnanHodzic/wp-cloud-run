<?php
/**
 * Render 'debug' column.
 *
 * @package AdvancedAds
 *
 * @var bool $privacyignore Debug mode checked or not.
 */

echo $privacyignore ? esc_html__( 'Enabled', 'advanced-ads' ) : '&mdash;';
