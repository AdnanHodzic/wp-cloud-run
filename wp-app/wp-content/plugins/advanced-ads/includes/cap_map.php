<?php

/** 
 * Advanced Ads capabilities
 * 
 * currently only for informational purposes
 */

$advanced_ads_capabilities = apply_filters( 'advanced-ads-capabilities', [
	'advanced_ads_manage_options',	    // admins only
	'advanced_ads_see_interface',	    // admins, maybe editors
	'advanced_ads_edit_ads',	    // admins, maybe editors
	'advanced_ads_manage_placements',   // admins, maybe editors
	'advanced_ads_place_ads',	    // admins, maybe editors
]);