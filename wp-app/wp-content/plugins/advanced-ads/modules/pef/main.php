<?php
/**
 * PEF module main file
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 */

namespace AdvancedAds\Modules\ProductExperimentationFramework;

const DIR  = __DIR__;
const FILE = __FILE__;

if ( ! is_admin() ) {
	return;
}

Module::get_instance();
