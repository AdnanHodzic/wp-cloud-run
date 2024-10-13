<?php

// Registered Sidebars
$sidebars = $GLOBALS['wp_registered_sidebars'];
$sidebar_list = array();
foreach($sidebars as $sidebar){
	if($sidebar['id'] != 'epcl_sidebar_footer')
		$sidebar_list[$sidebar['id']] = $sidebar['name'];
}

$prefix = EPCL_THEMEPREFIX.'_';
$prefix_key = 'epcl_page_';

// Posts
acf_add_local_field_group( array(
	'key' => 'epcl_page',
	'title' => esc_html__('General Information', 'epcl_framework'),
	'fields' => array (
        array (
			'key' => $prefix_key.'enable_title',
			'name' => 'enable_title',
			'label' => esc_html__('Enable Title', 'epcl_framework'),
			'instructions' => esc_html__('Enable/Disable title for this page.', 'epcl_framework'),
			'type' => 'true_false',
			'default_value' => true,
			'ui' => true,
		),
		array (
			'key' => $prefix_key.'enable_sidebar',
			'name' => 'enable_sidebar',
			'label' => esc_html__('Enable Sidebar', 'epcl_framework'),
			'instructions' => esc_html__('Enable/Disable sidebar for this post.', 'epcl_framework'),
			'type' => 'true_false',
			'default_value' => true,
			'ui' => true,
		),
		array (
			'key' => $prefix_key.'sidebar',
			'name' => 'sidebar',
			'label' => esc_html__('Sidebar (optional)', 'epcl_framework'),
			'instructions' => esc_html__('Use a different sidebar for this post.', 'epcl_framework'),
			'type' => 'select',
			'choices' => $sidebar_list,
			'ui' => true,
			'default_value' => 'epcl_sidebar_default',
			'conditional_logic' => array (
				array (
					array (
					'field' => $prefix_key.'enable_sidebar',
					'operator' => '==',
					'value' => '1',
					),
				),
			)
		),
	),
	'menu_order' => 3,
	'label_placement' => 'left',
	'instruction_placement' => 'label',
	'location' => array (
		array (
			array (
				'param' => 'page_template',
				'operator' => '==',
				'value' => 'default',
			),
		),
	)
));


?>
