<?php
/**
 * OneClick Connect module
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

use AdvancedAds\Modules\OneClick\Admin\Admin;
use AdvancedAds\Modules\OneClick\Admin\Ajax;
use AdvancedAds\Modules\OneClick\Workflow;

// Common.
( new Workflow() )->hooks();

// Admin.
if ( is_admin() ) {
	( new Ajax() )->hooks();
	( new Admin() )->hooks();
}
