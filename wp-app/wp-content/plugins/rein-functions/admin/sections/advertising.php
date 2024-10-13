<?php

/* Unique options for every EP theme */

$admin_url = EPCL_PLUGIN_URL.'/functions/admin';

/* Advertising Settings */

Redux::setSection( $opt_name, array(
	'title' => esc_html__('Advertising', 'epcl_framework'),
	'icon' => ' el-icon-photo',
) );

/* Header */

Redux::setSection( $opt_name, array(
	'title' => esc_html__('Header section', 'epcl_framework'),
	'subsection' => true,
	'fields' => array(
		array(
			'id' => 'ads_top_header',
			'type' => 'section',
			'title' => __('Top Header', 'epcl_framework'),
			'subtitle' => __('This section is only visible when header layout is equal to advertising area. Size: 728x90px', 'epcl_framework'),
			'indent' => true
		),
		array(
			'id' => 'header_advertising_type',
			'type' => 'button_set',
			'title' => esc_html__('Header Advertising Type', 'epcl_framework'),
			'desc' => '',
			'options'   => array(
				'image' => 'Image',
				'code' => 'Code',
			),
			'default' => 'image',
		),
		array(
			'id' => 'header_advertising_image',
			'type' => 'media',
			'required' => array('header_advertising_type', '=', 'image'),
			'url' => true,
			'preview'=> true,
			'title' => esc_html__('Header Advertising Image', 'epcl_framework'),
			'desc' => esc_html__('Recommended sizes - width: 728px, height: 90px.', 'epcl_framework'),
		),
		array(
			'id' => 'header_advertising_url',
			'type' => 'text',
			'required' => array('header_advertising_type', '=', 'image'),
			'validate' => 'url',
			'title' => esc_html__('Header Advertising URL', 'epcl_framework'),
			'desc' => esc_html__('e.g. http://www.ads.com/myurl', 'epcl_framework')
		),
		array(
			'id' => 'header_advertising_code',
			'type' => 'textarea',
			'required' => array('header_advertising_type', '=', 'code'),
			'title' => esc_html__('Header Custom Code', 'epcl_framework'),
			'desc' => esc_html__('Here you can paste your custom advertising code.', 'epcl_framework')
        ),
        // Below header
		array(
			'id' => 'ads_below_header',
			'type' => 'section',
			'title' => __('Below Header', 'epcl_framework'),
			'subtitle' => __('This section will show a global ads (pages, single posts, archives, etc). Size: 970x90 (or ad-format="horizontal" for adsense)', 'epcl_framework'),
			'indent' => true
		),
		array(
			'id' => 'ads_enabled_below_header',
			'type' => 'switch',
			'title' => esc_html__('Display Advertise', 'epcl_framework'),
			'desc' => esc_html__('Enable or disable this particular ads section.', 'epcl_framework'),
			'default' => 0
		),
		array(
			'id' => 'ads_type_below_header',
			'type' => 'button_set',
			'title' => esc_html__('Advertising Type', 'epcl_framework'),
			'desc' => '',
			'options'   => array(
				'image' => 'Image',
				'code' => 'Code',
			),
			'default' => 'image',
			'required' => array('ads_enabled_below_header', '=', '1')
        ),
        array(
			'id' => 'ads_mobile_below_header',
			'type' => 'switch',
			'title' => esc_html__('Display Advertise on Mobile', 'epcl_framework'),
			'desc' => esc_html__('Disabling ads on mobile can improve your website speed on cellphones.', 'epcl_framework'),
            'default' => 1,
            'required' => array('ads_enabled_below_header', '=', '1')
		),
		array(
			'id' => 'ads_image_below_header',
			'type' => 'media',
			'required' => array('ads_type_below_header', '=', 'image'),
			'url' => true,
			'preview'=> true,
			'title' => esc_html__('Advertising Image', 'epcl_framework'),
			'desc' => esc_html__('Recommended sizes - width: 728px, height: 90px.', 'epcl_framework'),
		),

		array(
			'id' => 'ads_url_below_header',
			'type' => 'text',
			'required' => array('ads_type_below_header', '=', 'image'),
			'validate' => 'url',
			'title' => esc_html__('Advertising URL', 'epcl_framework'),
			'desc' => esc_html__('e.g. http://www.ads.com/myurl', 'epcl_framework')
		),
		array(
			'id' => 'ads_code_below_header',
			'type' => 'textarea',
			'required' => array('ads_type_below_header', '=', 'code'),
			'title' => esc_html__('Custom Code', 'epcl_framework'),
			'desc' => esc_html__('Here you can paste your custom advertising code.', 'epcl_framework')
		),
		array(
			'id' => 'ads_mt_below_header',
			'type' => 'slider',
			'title' => esc_html__('Spacing Top', 'epcl_framework'),
			'subtitle' => '',
			'desc' => esc_html__('Default: 50 pixels.', 'epcl_framework'),
			'default' => '50',
			'min' => '0',
			'step' => '1',
			'max' => '100',
			'required' => array('ads_enabled_below_header', '=', '1')
		),
		array(
			'id' => 'ads_mb_below_header',
			'type' => 'slider',
			'title' => esc_html__('Spacing Bottom', 'epcl_framework'),
			'subtitle' => '',
			'desc' => esc_html__('Default: 50 pixels.', 'epcl_framework'),
			'default' => '50',
			'min' => '0',
			'step' => '1',
			'max' => '100',
			'required' => array('ads_enabled_below_header', '=', '1')
		),
	)
) );

/* Single Post Content */

Redux::setSection( $opt_name, array(
	'title' => esc_html__('Single Post section', 'epcl_framework'),
	'subsection' => true,
	'fields' => array(
		array(
			'id' => 'ads_single_top',
			'type' => 'section',
			'title' => __('Before Post Content', 'epcl_framework'),
			'subtitle' => __('This section will show an advertise only on single post content.', 'epcl_framework'),
			'indent' => true
		),
		array(
			'id' => 'ads_enabled_single_top',
			'type' => 'switch',
			'title' => esc_html__('Display Advertise', 'epcl_framework'),
			'desc' => esc_html__('Enable or disable this particular ads section.', 'epcl_framework'),
			'default' => 0
        ),
        array(
			'id' => 'ads_mobile_single_top',
			'type' => 'switch',
			'title' => esc_html__('Display Advertise on Mobile', 'epcl_framework'),
			'desc' => esc_html__('Disabling ads on mobile can improve your website speed on cellphones.', 'epcl_framework'),
            'default' => 1,
            'required' => array('ads_enabled_single_top', '=', '1')
		),
		array(
			'id' => 'ads_type_single_top',
			'type' => 'button_set',
			'title' => esc_html__('Advertising Type', 'epcl_framework'),
			'desc' => '',
			'options'   => array(
				'image' => 'Image',
				'code' => 'Code',
			),
			'default' => 'image',
			'required' => array('ads_enabled_single_top', '=', '1')
		),
		array(
			'id' => 'ads_image_single_top',
			'type' => 'media',
			'required' => array('ads_type_single_top', '=', 'image'),
			'url' => true,
			'preview'=> true,
			'title' => esc_html__('Advertising Image', 'epcl_framework'),
//			'desc' => esc_html__('Recommended sizes - width: 728px, height: 90px.', 'epcl_framework'),
		),
		array(
			'id' => 'ads_url_single_top',
			'type' => 'text',
			'required' => array('ads_type_single_top', '=', 'image'),
			'validate' => 'url',
			'title' => esc_html__('Advertising URL', 'epcl_framework'),
			'desc' => esc_html__('e.g. http://www.ads.com/myurl', 'epcl_framework')
		),
		array(
			'id' => 'ads_code_single_top',
			'type' => 'textarea',
			'required' => array('ads_type_single_top', '=', 'code'),
			'title' => esc_html__('Custom Code', 'epcl_framework'),
			'desc' => esc_html__('Here you can paste your custom advertising code.', 'epcl_framework')
		),
		array(
			'id' => 'ads_mt_single_top',
			'type' => 'slider',
			'title' => esc_html__('Spacing Top', 'epcl_framework'),
			'subtitle' => '',
			'desc' => esc_html__('Default: 25 pixels.', 'epcl_framework'),
			'default' => '25',
			'min' => '0',
			'step' => '1',
			'max' => '100',
			'required' => array('ads_enabled_single_top', '=', '1')
		),
		array(
			'id' => 'ads_mb_single_top',
			'type' => 'slider',
			'title' => esc_html__('Spacing Bottom', 'epcl_framework'),
			'subtitle' => '',
			'desc' => esc_html__('Default: 25 pixels.', 'epcl_framework'),
			'default' => '25',
			'min' => '0',
			'step' => '1',
			'max' => '100',
			'required' => array('ads_enabled_single_top', '=', '1')
        ),
        // Single Bottom
		array(
			'id' => 'ads_single_bottom',
			'type' => 'section',
			'title' => __('After Post content', 'epcl_framework'),
			'subtitle' => __('This section will show an advertise at the of the single post content.', 'epcl_framework'),
			'indent' => true
		),
		array(
			'id' => 'ads_enabled_single_bottom',
			'type' => 'switch',
			'title' => esc_html__('Display Advertise', 'epcl_framework'),
			'desc' => esc_html__('Enable or disable this particular ads section.', 'epcl_framework'),
			'default' => 0
        ),
        array(
			'id' => 'ads_mobile_single_bottom',
			'type' => 'switch',
			'title' => esc_html__('Display Advertise on Mobile', 'epcl_framework'),
			'desc' => esc_html__('Disabling ads on mobile can improve your website speed on cellphones.', 'epcl_framework'),
            'default' => 1,
            'required' => array('ads_enabled_single_bottom', '=', '1')
		),
		array(
			'id' => 'ads_type_single_bottom',
			'type' => 'button_set',
			'title' => esc_html__('Advertising Type', 'epcl_framework'),
			'desc' => '',
			'options'   => array(
				'image' => 'Image',
				'code' => 'Code',
			),
			'default' => 'image',
			'required' => array('ads_enabled_single_bottom', '=', '1')
		),
		array(
			'id' => 'ads_image_single_bottom',
			'type' => 'media',
			'required' => array('ads_type_single_bottom', '=', 'image'),
			'url' => true,
			'preview'=> true,
			'title' => esc_html__('Advertising Image', 'epcl_framework'),
//			'desc' => esc_html__('Recommended sizes - width: 728px, height: 90px.', 'epcl_framework'),
		),
		array(
			'id' => 'ads_url_single_bottom',
			'type' => 'text',
			'required' => array('ads_type_single_bottom', '=', 'image'),
			'validate' => 'url',
			'title' => esc_html__('Advertising URL', 'epcl_framework'),
			'desc' => esc_html__('e.g. http://www.ads.com/myurl', 'epcl_framework')
		),
		array(
			'id' => 'ads_code_single_bottom',
			'type' => 'textarea',
			'required' => array('ads_type_single_bottom', '=', 'code'),
			'title' => esc_html__('Custom Code', 'epcl_framework'),
			'desc' => esc_html__('Here you can paste your custom advertising code.', 'epcl_framework')
		),
		array(
			'id' => 'ads_mt_single_bottom',
			'type' => 'slider',
			'title' => esc_html__('Spacing Top', 'epcl_framework'),
			'subtitle' => '',
			'desc' => esc_html__('Default: 25 pixels.', 'epcl_framework'),
			'default' => '25',
			'min' => '0',
			'step' => '1',
			'max' => '100',
			'required' => array('ads_enabled_single_bottom', '=', '1')
		),
		array(
			'id' => 'ads_mb_single_bottom',
			'type' => 'slider',
			'title' => esc_html__('Spacing Bottom', 'epcl_framework'),
			'subtitle' => '',
			'desc' => esc_html__('Default: 25 pixels.', 'epcl_framework'),
			'default' => '25',
			'min' => '0',
			'step' => '1',
			'max' => '100',
			'required' => array('ads_enabled_single_bottom', '=', '1')
		),
	)
) );

/* Custom Shortcode ADS */

Redux::setSection( $opt_name, array(
	'title' => esc_html__('Custom Ads (shortcode)', 'epcl_framework'),
	'subsection' => true,
	'fields' => array(
		array(
			'id' => 'ads_custom_shortcode_title_1',
			'type' => 'section',
			'title' => __('Shortcode nº 1', 'epcl_framework'),
			'subtitle' => __('This section can be displayed anywhere on the website, just copy and paste the shortcode below.', 'epcl_framework'),
			'indent' => true
		),
		array(
			'id' => 'ads_custom_shortcode_copy',
			'type' => 'text',
			'title' => esc_html__('Shortcode', 'epcl_framework'),
			'default' => '[epcl_custom_ads id="1"]',
			'readonly' => true
		),
		array(
			'id' => 'ads_enabled_custom_shortcode',
			'type' => 'switch',
			'title' => esc_html__('Display Advertise', 'epcl_framework'),
			'desc' => esc_html__('Enable or disable this particular ads section.', 'epcl_framework'),
			'default' => 0
        ),
        array(
			'id' => 'ads_mobile_custom_shortcode',
			'type' => 'switch',
			'title' => esc_html__('Display Advertise on Mobile', 'epcl_framework'),
			'desc' => esc_html__('Disabling ads on mobile can improve your website speed on cellphones.', 'epcl_framework'),
            'default' => 1,
            'required' => array('ads_enabled_custom_shortcode', '=', '1')
		),
		array(
			'id' => 'ads_type_custom_shortcode',
			'type' => 'button_set',
			'title' => esc_html__('Advertising Type', 'epcl_framework'),
			'desc' => '',
			'options'   => array(
				'image' => 'Image',
				'code' => 'Code',
			),
			'default' => 'image',
			'required' => array('ads_enabled_custom_shortcode', '=', '1')
		),
		array(
			'id' => 'ads_image_custom_shortcode',
			'type' => 'media',
			'required' => array('ads_type_custom_shortcode', '=', 'image'),
			'url' => true,
			'preview'=> true,
			'title' => esc_html__('Advertising Image', 'epcl_framework'),
//			'desc' => esc_html__('Recommended sizes - width: 728px, height: 90px.', 'epcl_framework'),
		),
		array(
			'id' => 'ads_url_custom_shortcode',
			'type' => 'text',
			'required' => array('ads_type_custom_shortcode', '=', 'image'),
			'validate' => 'url',
			'title' => esc_html__('Advertising URL', 'epcl_framework'),
			'desc' => esc_html__('e.g. http://www.ads.com/myurl', 'epcl_framework')
		),
		array(
			'id' => 'ads_code_custom_shortcode',
			'type' => 'textarea',
			'required' => array('ads_type_custom_shortcode', '=', 'code'),
			'title' => esc_html__('Custom Code', 'epcl_framework'),
			'desc' => esc_html__('Here you can paste your custom advertising code.', 'epcl_framework')
		),
		array(
			'id' => 'ads_mt_custom_shortcode',
			'type' => 'slider',
			'title' => esc_html__('Spacing Top', 'epcl_framework'),
			'subtitle' => '',
			'desc' => esc_html__('Default: 25 pixels.', 'epcl_framework'),
			'default' => '25',
			'min' => '0',
			'step' => '1',
			'max' => '100',
			'required' => array('ads_enabled_custom_shortcode', '=', '1')
		),
		array(
			'id' => 'ads_mb_custom_shortcode',
			'type' => 'slider',
			'title' => esc_html__('Spacing Bottom', 'epcl_framework'),
			'subtitle' => '',
			'desc' => esc_html__('Default: 25 pixels.', 'epcl_framework'),
			'default' => '25',
			'min' => '0',
			'step' => '1',
			'max' => '100',
			'required' => array('ads_enabled_custom_shortcode', '=', '1')
        ),
        // Shortcode 2
        array(
			'id' => 'ads_custom_shortcode_title_2',
			'type' => 'section',
			'title' => __('Shortcode nº 2', 'epcl_framework'),
			'subtitle' => __('This section can be displayed anywhere on the website, just copy and paste the shortcode below.', 'epcl_framework'),
			'indent' => true
		),
        array(
			'id' => 'ads_custom_shortcode_copy_2',
			'type' => 'text',
			'title' => esc_html__('Shortcode', 'epcl_framework'),
			'default' => '[epcl_custom_ads id="2"]',
			'readonly' => true
		),
		array(
			'id' => 'ads_enabled_custom_shortcode_2',
			'type' => 'switch',
			'title' => esc_html__('Display Advertise', 'epcl_framework'),
			'desc' => esc_html__('Enable or disable this particular ads section.', 'epcl_framework'),
			'default' => 0
        ),
        array(
			'id' => 'ads_mobile_custom_shortcode_2',
			'type' => 'switch',
			'title' => esc_html__('Display Advertise on Mobile', 'epcl_framework'),
			'desc' => esc_html__('Disabling ads on mobile can improve your website speed on cellphones.', 'epcl_framework'),
            'default' => 1,
            'required' => array('ads_enabled_custom_shortcode_2', '=', '1')
		),
		array(
			'id' => 'ads_type_custom_shortcode_2',
			'type' => 'button_set',
			'title' => esc_html__('Advertising Type', 'epcl_framework'),
			'desc' => '',
			'options'   => array(
				'image' => 'Image',
				'code' => 'Code',
			),
			'default' => 'image',
			'required' => array('ads_enabled_custom_shortcode_2', '=', '1')
		),
		array(
			'id' => 'ads_image_custom_shortcode_2',
			'type' => 'media',
			'required' => array('ads_type_custom_shortcode_2', '=', 'image'),
			'url' => true,
			'preview'=> true,
			'title' => esc_html__('Advertising Image', 'epcl_framework'),
//			'desc' => esc_html__('Recommended sizes - width: 728px, height: 90px.', 'epcl_framework'),
		),
		array(
			'id' => 'ads_url_custom_shortcode_2',
			'type' => 'text',
			'required' => array('ads_type_custom_shortcode_2', '=', 'image'),
			'validate' => 'url',
			'title' => esc_html__('Advertising URL', 'epcl_framework'),
			'desc' => esc_html__('e.g. http://www.ads.com/myurl', 'epcl_framework')
		),
		array(
			'id' => 'ads_code_custom_shortcode_2',
			'type' => 'textarea',
			'required' => array('ads_type_custom_shortcode_2', '=', 'code'),
			'title' => esc_html__('Custom Code', 'epcl_framework'),
			'desc' => esc_html__('Here you can paste your custom advertising code.', 'epcl_framework')
		),
		array(
			'id' => 'ads_mt_custom_shortcode_2',
			'type' => 'slider',
			'title' => esc_html__('Spacing Top', 'epcl_framework'),
			'subtitle' => '',
			'desc' => esc_html__('Default: 25 pixels.', 'epcl_framework'),
			'default' => '25',
			'min' => '0',
			'step' => '1',
			'max' => '100',
			'required' => array('ads_enabled_custom_shortcode_2', '=', '1')
		),
		array(
			'id' => 'ads_mb_custom_shortcode_2',
			'type' => 'slider',
			'title' => esc_html__('Spacing Bottom', 'epcl_framework'),
			'subtitle' => '',
			'desc' => esc_html__('Default: 25 pixels.', 'epcl_framework'),
			'default' => '25',
			'min' => '0',
			'step' => '1',
			'max' => '100',
			'required' => array('ads_enabled_custom_shortcode_2', '=', '1')
		),
	)
) );

/* Loops */

Redux::setSection( $opt_name, array(
	'title' => esc_html__('Ads on grid loop', 'epcl_framework'),
	'subsection' => true,
	'fields' => array(
		array(
			'id' => 'ads_custom_shortcode',
			'type' => 'section',
			//'title' => __('', 'epcl_framework'),
			'subtitle' => __('This section will be displayed between posts, specifically on grids articles loop.', 'epcl_framework'),
			'indent' => true
		),
		array(
			'id' => 'ads_enabled_grid_loop',
			'type' => 'switch',
			'title' => esc_html__('Display Advertise', 'epcl_framework'),
			'desc' => esc_html__('Enable or disable this particular ads section.', 'epcl_framework'),
			'default' => 0
        ),
        array(
			'id' => 'ads_mobile_grid_loop',
			'type' => 'switch',
			'title' => esc_html__('Display Advertise on Mobile', 'epcl_framework'),
			'desc' => esc_html__('Disabling ads on mobile can improve your website speed on cellphones.', 'epcl_framework'),
            'default' => 1,
            'required' => array('ads_enabled_grid_loop', '=', '1')
		),
		array(
			'id' => 'ads_position_grid_loop',
			'type' => 'slider',
			'title' => esc_html__('Ads position', 'epcl_framework'),
			'subtitle' => esc_html__('Recommended: 5', 'epcl_framework'),
			'desc' => esc_html__('Set the position that will use this ads on the main loop.', 'epcl_framework'),
			'default' => '5',
			'min' => '1',
			'step' => '1',
			'max' => get_option( 'posts_per_page' ),
			'required' => array('ads_enabled_grid_loop', '=', '1')
		),
		array(
			'id' => 'ads_type_grid_loop',
			'type' => 'button_set',
			'title' => esc_html__('Advertising Type', 'epcl_framework'),
			'desc' => '',
			'options'   => array(
				'image' => 'Image',
				'code' => 'Code',
			),
			'default' => 'image',
			'required' => array('ads_enabled_grid_loop', '=', '1')
		),
		array(
			'id' => 'ads_image_grid_loop',
			'type' => 'media',
			'required' => array('ads_type_grid_loop', '=', 'image'),
			'url' => true,
			'preview'=> true,
			'title' => esc_html__('Advertising Image', 'epcl_framework'),
//			'desc' => esc_html__('Recommended sizes - width: 728px, height: 90px.', 'epcl_framework'),
		),
		array(
			'id' => 'ads_url_grid_loop',
			'type' => 'text',
			'required' => array('ads_type_grid_loop', '=', 'image'),
			'validate' => 'url',
			'title' => esc_html__('Advertising URL', 'epcl_framework'),
			'desc' => esc_html__('e.g. http://www.ads.com/myurl', 'epcl_framework')
		),
		array(
			'id' => 'ads_code_grid_loop',
			'type' => 'textarea',
			'required' => array('ads_type_grid_loop', '=', 'code'),
			'title' => esc_html__('Custom Code', 'epcl_framework'),
			'desc' => esc_html__('Here you can paste your custom advertising code.', 'epcl_framework')
		),
		array(
			'id' => 'ads_mt_grid_loop',
			'type' => 'slider',
			'title' => esc_html__('Spacing Top', 'epcl_framework'),
			'subtitle' => '',
			'desc' => esc_html__('Default: 25 pixels.', 'epcl_framework'),
			'default' => '25',
			'min' => '0',
			'step' => '1',
			'max' => '100',
			'required' => array('ads_enabled_grid_loop', '=', '1')
		),
		array(
			'id' => 'ads_mb_grid_loop',
			'type' => 'slider',
			'title' => esc_html__('Spacing Bottom', 'epcl_framework'),
			'subtitle' => '',
			'desc' => esc_html__('Default: 25 pixels.', 'epcl_framework'),
			'default' => '25',
			'min' => '0',
			'step' => '1',
			'max' => '100',
			'required' => array('ads_enabled_grid_loop', '=', '1')
		),
	)
) );

?>