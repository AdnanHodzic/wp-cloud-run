<?php return array(
    'root' => array(
        'name' => 'advanced-ads/advanced-ads',
        'pretty_version' => '1.48.2',
        'version' => '1.48.2.0',
        'reference' => NULL,
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => false,
    ),
    'versions' => array(
        'advanced-ads/advanced-ads' => array(
            'pretty_version' => '1.48.2',
            'version' => '1.48.2.0',
            'reference' => NULL,
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'advanced-ads/framework' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => 'bafe2c32b1530cfb18d8b738adb9524f8406b9aa',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../advanced-ads/framework',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
        'mobiledetect/mobiledetectlib' => array(
            'pretty_version' => '3.74.3',
            'version' => '3.74.3.0',
            'reference' => '39582ab62f86b40e4edb698159f895929a29c346',
            'type' => 'library',
            'install_path' => __DIR__ . '/../mobiledetect/mobiledetectlib',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
