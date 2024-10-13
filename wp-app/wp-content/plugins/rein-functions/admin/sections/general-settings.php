<?php

/* Unique options for every EP theme */

$admin_url = EPCL_PLUGIN_URL.'/functions/admin';

/* General Settings */

Redux::setSection( $opt_name, array(
	'title' => esc_html__('General Settings', 'epcl_framework'),
	'icon' => ' el-icon-cog',
	'fields' => array(
		array(
			'id' => 'background_type',
			'type' => 'button_set',
			'title' => esc_html__('Background Type', 'epcl_framework'),
			'subtitle' => '',
			'desc' => '',
            'options' => array(
                '1' => 'Image Pattern',
                '2' => 'Solid Color',
                '3' => 'Fullscreen Image',
                '4' => 'Gradient',
            ),
			'default' => '2'
		),
		array(
			'id' => 'bg_body_color',
			'type' => 'color',
			'required' => array('background_type', '=', '2'),
			'title' => esc_html__('Body Background Color', 'epcl_framework'),
			'desc' => esc_html__('Pick a background color for the theme.', 'epcl_framework'),
			'default' => '#2C45A3',
			// 'validate' => 'color',
			'transparent' => false
		),
		array(
			'id' => 'bg_body_pattern',
			'type' => 'media',
			'required' => array('background_type', '=', '1'),
			'url' => true,
			'preview'=> true,
			'title' => esc_html__('Pattern Uploader', 'epcl_framework'),
			'desc' => esc_html__('You can get a lot of free patterns on http://subtlepatterns.com', 'epcl_framework'),
		),
		array(
			'id' => 'bg_body_full',
			'type' => 'media',
			'required' => array('background_type', '=', '3'),
			'url' => true,
			'preview'=> true,
			'title' => esc_html__('Fullscreen Image', 'epcl_framework'),
			'desc' => esc_html__('Recommended size: 1920x1080 pixels', 'epcl_framework'),
        ),
        array(
			'id' => 'bg_body_gradient',
			'type' => 'color_gradient',
			'required' => array('background_type', '=', '4'),
			'title' => esc_html__('Gradient Color', 'epcl_framework'),
			'desc' => esc_html__('Pick 2 different colors for the gradient.', 'epcl_framework'),
			'default' => '#2C45A3',
			// 'validate' => 'color',
            'transparent' => false,
            'default'  => array(
                'from' => '#2C45A3',
                'to'   => '#355BC5', 
            ),
        ),		
	)
) );

?>