<?php

// module configuration

$path = dirname( __FILE__ );

return [
	'classmap' => [
		'Advanced_Ads_Ad_Type_Adsense'     => $path . '/includes/class-ad-type-adsense.php',
		'Advanced_Ads_AdSense_Data'        => $path . '/includes/class-gadsense-data.php',
		'Advanced_Ads_AdSense_MAPI'        => $path . '/includes/class-mapi.php',
		'Advanced_Ads_AdSense_Admin'       => $path . '/admin/admin.php',
		'Advanced_Ads_AdSense_Public'      => $path . '/public/public.php',
		'Advanced_Ads_AdSense_Report'      => $path . '/includes/class-adsense-report.php',
		'Advanced_Ads_AdSense_Report_Data' => $path . '/includes/adsense-report-data.php',
		'Advanced_Ads_AdSense_Report_Api'  => $path . '/includes/adsense-report-api.php',

		'Advanced_Ads_Network_Adsense' => $path . '/includes/class-network-adsense.php',
	],
	'textdomain' => null,
];
