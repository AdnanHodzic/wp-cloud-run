<?php

$prefix = EPCL_THEMEPREFIX.'_';
$prefix_key = 'epcl_user_';

// Custom user fields
acf_add_local_field_group( array(
	'key' => 'epcl_user',
	'title' => esc_html__('Aditional Information', 'epcl_framework'),
	'fields' => array (
		array (
			'key' => $prefix_key.'facebook',
			'name' => 'facebook',
			'label' => esc_html__('Facebook URL', 'epcl_framework'),
			'instructions' => esc_html__('e.g. http://www.facebook.com/estudiopatagon', 'epcl_framework'),
			'type' => 'url',
		),
		array (
			'key' => $prefix_key.'twitter',
			'name' => 'twitter',
			'label' => esc_html__('Twitter URL', 'epcl_framework'),
			'instructions' => esc_html__('e.g: https://twitter.com/wordpress', 'epcl_framework'),
			'type' => 'url',
        ),
        array (
			'key' => $prefix_key.'position',
			'name' => 'position',
			'label' => esc_html__('Position (tagline)', 'epcl_framework'),
			'instructions' => esc_html__('e.g: Founder & Editor', 'epcl_framework'),
			'type' => 'text',
        ),
        array (
            'key' => $prefix_key.'avatar',
            'name' => 'avatar',
            'label' => esc_html__('Optimized Avatar', 'epcl_framework'),
            'type' => 'image',
            'instructions' => esc_html__('Recommended size: 150x150. This step is totally optional, it\'s just boost a little the web speed rendering the image directly from your hosting, instead of gravatar.', 'epcl_framework'),
            'return_format' => 'array',
            'preview_size' => 'medium',
            'library' => 'all',
        ),
        array (
            'key' => $prefix_key.'background_image',
            'name' => 'background_image',
            'label' => esc_html__('Author Background Image', 'epcl_framework'),
            'type' => 'image',
            'instructions' => esc_html__('Background image for your author information (optional).', 'epcl_framework'),
            'return_format' => 'array',
            'preview_size' => 'medium',
            'library' => 'all',
        ),
	),
	'label_placement' => 'left',
	'instruction_placement' => 'field',
	'location' => array (
		array (
			array (
				'param' => 'user_form',
				'operator' => '==',
				'value' => 'edit',
			),
		),
	)
));

?>
