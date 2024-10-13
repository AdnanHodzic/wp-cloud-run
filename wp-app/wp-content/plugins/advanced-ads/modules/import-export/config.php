<?php

// module configuration

$path = dirname( __FILE__ );

return [
	'classmap' => [
		'Advanced_Ads_XmlEncoder' => $path . '/classes/XmlEncoder.php',
		'Advanced_Ads_Export' => $path . '/classes/export.php',
		'Advanced_Ads_Import' => $path . '/classes/import.php',
	],
	'textdomain' => null,
];