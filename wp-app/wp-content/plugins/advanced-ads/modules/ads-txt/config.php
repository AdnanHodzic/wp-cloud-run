<?php
/**
 * Module configuration.
 */

$path = dirname( __FILE__ );

return [
	'classmap'   => [
		'Advanced_Ads_Ads_Txt_Public'          => $path . '/public/class-advanced-ads-ads-txt-public.php',
		'Advanced_Ads_Ads_Txt_Strategy' => $path . '/includes/class-advanced-ads-ads-txt-strategy.php',
		'Advanced_Ads_Ads_Txt_Admin'    => $path . '/admin/class-advanced-ads-ads-txt-admin.php',
		'Advanced_Ads_Ads_Txt_Utils'    => $path . '/includes/class-advanced-ads-ads-txt-utils.php',
		'Advanced_Ads_Ads_Txt_Real_File'=> $path . '/includes/class-advanced-ads-ads-txt-real-file.php',
	],
	'textdomain' => null,
];
