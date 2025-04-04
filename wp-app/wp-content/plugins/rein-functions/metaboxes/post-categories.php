<?php

$prefix = EPCL_THEMEPREFIX.'_';
$prefix_key = 'epcl_post_categories_';

// Categories
acf_add_local_field_group( array(
	'key' => 'epcl_post_categories',
	'title' => esc_html__('General Information', 'epcl_framework'),
	'fields' => array (
        array (
			'key' => $prefix_key.'main_color',
			'name' => 'main_color',
			'label' => esc_html__('Category Main Color', 'epcl_framework'),
			'instructions' => esc_html__('(Optional) add a custom color for this category.', 'epcl_framework'),
			'type' => 'color_picker',
            'default_value' => '',
        ),
        array (
			'key' => $prefix_key.'archives_image',
			'name' => 'archives_image',
			'label' => esc_html__('Background image for Archive\'s pages', 'epcl_framework'),
			'type' => 'image',
			'instructions' => __('Optional: you can set a background image for this particular category on Archive\'s page.', 'epcl_framework'),
			'required' => false,
			'return_format' => 'array',
			'preview_size' => 'thumbnail',
			'library' => 'all',
        ),
	),
	'menu_order' => 3,
	'label_placement' => 'left',
	'instruction_placement' => 'label',
	'location' => array (
		array (
			array (
				'param' => 'taxonomy',
				'operator' => '==',
				'value' => 'category',
			),
		),
	)
));

?>
