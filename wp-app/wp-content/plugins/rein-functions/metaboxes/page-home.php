<?php

$prefix = EPCL_THEMEPREFIX.'_';
$prefix_key = 'epcl_home_';

// Registered Sidebars
$sidebars = $GLOBALS['wp_registered_sidebars'];
$sidebar_list = array();
foreach($sidebars as $sidebar){
	if($sidebar['id'] != 'epcl_sidebar_footer')
		$sidebar_list[$sidebar['id']] = $sidebar['name'];
}

// Posts
acf_add_local_field_group( array(
	'key' => 'epcl_home',
	'title' => esc_html__('General Information', 'epcl_framework'),
	'fields' => array (
		array(
			'key' => $prefix_key.'modules',
			'name' => 'modules',
			'label' => 'Modules',
			'type' => 'flexible_content',
			'instructions' => esc_html__('Add different kinds of layouts.', 'epcl_framework'),
			'required' => true,
			'layouts' => array(
                // Grid Posts
				'module_grid_posts' => array(
					'key' => $prefix_key.'module_grid_posts',
					'name' => 'grid_posts',
					'label' => esc_html__('Grid Posts', 'epcl_framework'),
					'display' => 'row',
					'max' => 1,
					'sub_fields' => array(
                        array (
							'key' => $prefix_key.'grid_posts_orderby',
							'name' => 'grid_posts_orderby',
							'label' => esc_html__('Order by (optional)', 'epcl_framework'),
							// 'instructions' => esc_html__('Combine with order by Post Views to get your popular posts.', 'epcl_framework'),
							'type' => 'select',
							'choices' => array(
                                'date' => esc_html__('By Date (recent posts)', 'epcl_framework'),
                                'views' => esc_html__('By Post Views (popular posts)', 'epcl_framework'),
                                'title' => esc_html__('By Name', 'epcl_framework'),
                            ),
							'ui' => false,
							'default_value' => 'date'
                        ),
                        array (
							'key' => $prefix_key.'grid_posts_order',
							'name' => 'grid_posts_order',
							'label' => esc_html__('Order (optional)', 'epcl_framework'),
							'instructions' => esc_html__('Default descending (highest to lowest value).', 'epcl_framework'),
							'type' => 'select',
							'choices' => array(
                                'ASC' => esc_html__('ASC (ascending)', 'epcl_framework'),
                                'DESC' => esc_html__('DESC (descending)', 'epcl_framework'),
                            ),
							'ui' => false,
							'default_value' => 'DESC'
                        ),
						array (
							'key' => $prefix_key.'grid_enable_masonry',
							'name' => 'grid_enable_masonry',
							'label' => esc_html__('Enable Masonry Style', 'epcl_framework'),
							'instructions' => esc_html__('You can display the posts like a normal grid or in masonry style.', 'epcl_framework'),
							'type' => 'true_false',
							'ui' => true,
							'default_value' => true,
						),
						array(
							'key' => $prefix_key.'grid_posts_column',
							'name' => 'grid_posts_column',
							'label' => esc_html__('Number of Columns', 'epcl_framework'),
							'type' => 'number',
							'instructions' => esc_html__('2 to 3 columns', 'epcl_framework'),
							'min' => '2',
							'max' => '3',
							'step' => '1',
							'default_value' => '3',
						),
						array(
							'key' => $prefix_key.'grid_category',
							'name' => 'grid_category',
							'label' => esc_html__('Featured Categories', 'epcl_framework'),
							'type' => 'taxonomy',
							'instructions' => esc_html__('(Optional) select only post from these categories or leave blank to display all posts.', 'epcl_framework'),
							'taxonomy' => 'category',
							'field_type' => 'multi_select',
							'return_format' => 'id',
							'multiple' => 0,
							'allow_null' => true,
							'add_term' => false
                        ),
                        array(
							'key' => $prefix_key.'grid_excluded_categories',
							'name' => 'grid_excluded_categories',
							'label' => esc_html__('Excluded Categories', 'epcl_framework'),
							'type' => 'taxonomy',
							'instructions' => esc_html__('(Optional) Usefull if you dont want to display posts used on the carousel.', 'epcl_framework'),
							'taxonomy' => 'category',
							'field_type' => 'multi_select',
							'return_format' => 'id',
							'multiple' => 0,
							'allow_null' => true,
							'add_term' => false
                        ),
                        array(
							'key' => $prefix_key.'grid_posts_per_page',
							'name' => 'grid_posts_per_page',
							'label' => esc_html__('Posts per page', 'epcl_framework'),
							'type' => 'number',
							'instructions' => esc_html__('(Optional) by default is used the amount from Settings -> Reading option', 'epcl_framework'),
							'min' => '2',
							'max' => '30',
							'step' => '1',
							'default_value' => '',
						),
					),
                ),
				// Grid with Sidebar
				'module_grid_sidebar' => array(
					'key' => $prefix_key.'module_grid_sidebar',
					'name' => 'grid_sidebar',
					'label' => esc_html__('Grid Posts with Sidebar', 'epcl_framework'),
					'display' => 'row',
					'max' => 1,
					'sub_fields' => array(
                        array (
							'key' => $prefix_key.'grid_sidebar_orderby',
							'name' => 'grid_sidebar_orderby',
							'label' => esc_html__('Order by (optional)', 'epcl_framework'),
							// 'instructions' => esc_html__('Combine with order by Post Views to get your popular posts.', 'epcl_framework'),
							'type' => 'select',
							'choices' => array(
                                'date' => esc_html__('By Date (recent posts)', 'epcl_framework'),
                                'views' => esc_html__('By Post Views (popular posts)', 'epcl_framework'),
                                'title' => esc_html__('By Name', 'epcl_framework'),
                            ),
							'ui' => false,
							'default_value' => 'date'
                        ),
                        array (
							'key' => $prefix_key.'grid_sidebar_order',
							'name' => 'grid_sidebar_order',
							'label' => esc_html__('Order (optional)', 'epcl_framework'),
							'instructions' => esc_html__('Default descending (highest to lowest value).', 'epcl_framework'),
							'type' => 'select',
							'choices' => array(
                                'ASC' => esc_html__('ASC (ascending)', 'epcl_framework'),
                                'DESC' => esc_html__('DESC (descending)', 'epcl_framework'),
                            ),
							'ui' => false,
							'default_value' => 'DESC'
                        ),
						array (
							'key' => $prefix_key.'grid_sidebar_enable_masonry',
							'name' => 'grid_sidebar_enable_masonry',
							'label' => esc_html__('Enable Masonry Style', 'epcl_framework'),
							'instructions' => esc_html__('You can display the posts like a normal grid or in masonry style.', 'epcl_framework'),
							'type' => 'true_false',
							'ui' => true,
							'default_value' => true,
						),
						array(
							'key' => $prefix_key.'grid_sidebar_category',
							'name' => 'grid_sidebar_category',
							'label' => esc_html__('Featured Categories', 'epcl_framework'),
							'type' => 'taxonomy',
							'instructions' => esc_html__('(Optional) select only post from these categories.', 'epcl_framework'),
							'taxonomy' => 'category',
							'field_type' => 'multi_select',
							'return_format' => 'id',
							'multiple' => 0,
							'allow_null' => true,
							'add_term' => false
                        ),
                        array(
							'key' => $prefix_key.'grid_sidebar_excluded_categories',
							'name' => 'grid_sidebar_excluded_categories',
							'label' => esc_html__('Excluded Categories', 'epcl_framework'),
							'type' => 'taxonomy',
							'instructions' => esc_html__('(Optional) Usefull if you dont want to display posts used on the carousel.', 'epcl_framework'),
							'taxonomy' => 'category',
							'field_type' => 'multi_select',
							'return_format' => 'id',
							'multiple' => 0,
							'allow_null' => true,
							'add_term' => false
                        ),
                        array(
							'key' => $prefix_key.'grid_sidebar_posts_per_page',
							'name' => 'grid_sidebar_posts_per_page',
							'label' => esc_html__('Posts per page', 'epcl_framework'),
							'type' => 'number',
							'instructions' => esc_html__('(Optional) by default is used the amount from Settings -> Reading option', 'epcl_framework'),
							'min' => '2',
							'max' => '30',
							'step' => '1',
							'default_value' => '',
						),
						array (
							'key' => $prefix_key.'sidebar',
							'name' => 'sidebar',
							'label' => esc_html__('Sidebar (optional)', 'epcl_framework'),
							'instructions' => esc_html__('Use a different sidebar for this module.', 'epcl_framework'),
							'type' => 'select',
							'choices' => $sidebar_list,
							'ui' => true,
							'default_value' => 'epcl_sidebar_home'
						),
					),
				),
                 // Classic Posts
				'module_classic_posts' => array(
					'key' => $prefix_key.'module_classic_posts',
					'name' => 'classic_posts',
					'label' => esc_html__('Classic Posts', 'epcl_framework'),
					'display' => 'row',
					'max' => 1,
					'sub_fields' => array(
                        array (
							'key' => $prefix_key.'classic_orderby',
							'name' => 'classic_orderby',
							'label' => esc_html__('Order by (optional)', 'epcl_framework'),
							// 'instructions' => esc_html__('Combine with order by Post Views to get your popular posts.', 'epcl_framework'),
							'type' => 'select',
							'choices' => array(
                                'date' => esc_html__('By Date (recent posts)', 'epcl_framework'),
                                'views' => esc_html__('By Post Views (popular posts)', 'epcl_framework'),
                                'title' => esc_html__('By Name', 'epcl_framework'),
                            ),
							'ui' => false,
							'default_value' => 'date'
                        ),
                        array (
							'key' => $prefix_key.'classic_order',
							'name' => 'classic_order',
							'label' => esc_html__('Order (optional)', 'epcl_framework'),
							'instructions' => esc_html__('Default descending (highest to lowest value).', 'epcl_framework'),
							'type' => 'select',
							'choices' => array(
                                'ASC' => esc_html__('ASC (ascending)', 'epcl_framework'),
                                'DESC' => esc_html__('DESC (descending)', 'epcl_framework'),
                            ),
							'ui' => false,
							'default_value' => 'DESC'
                        ),
						array(
							'key' => $prefix_key.'classic_category',
							'name' => 'classic_category',
							'label' => esc_html__('Featured Categories', 'epcl_framework'),
							'type' => 'taxonomy',
							'instructions' => esc_html__('(Optional) select only post from these categories.', 'epcl_framework'),
							'taxonomy' => 'category',
							'field_type' => 'multi_select',
							'return_format' => 'id',
							'multiple' => 0,
							'allow_null' => true,
							'add_term' => false
                        ),
                        array(
							'key' => $prefix_key.'classic_excluded_categories',
							'name' => 'classic_excluded_categories',
							'label' => esc_html__('Excluded Categories', 'epcl_framework'),
							'type' => 'taxonomy',
							'instructions' => esc_html__('(Optional) Usefull if you dont want to display posts used on the carousel.', 'epcl_framework'),
							'taxonomy' => 'category',
							'field_type' => 'multi_select',
							'return_format' => 'id',
							'multiple' => 0,
							'allow_null' => true,
							'add_term' => false
                        ),
                        array(
							'key' => $prefix_key.'classic_posts_per_page',
							'name' => 'classic_posts_per_page',
							'label' => esc_html__('Posts per page', 'epcl_framework'),
							'type' => 'number',
							'instructions' => esc_html__('(Optional) by default is used the amount from Settings -> Reading option', 'epcl_framework'),
							'min' => '2',
							'max' => '30',
							'step' => '1',
							'default_value' => '',
						),
						array (
							'key' => $prefix_key.'classic_sidebar',
							'name' => 'sidebar',
							'label' => esc_html__('Sidebar (optional)', 'epcl_framework'),
							'instructions' => esc_html__('Use a different sidebar for this module.', 'epcl_framework'),
							'type' => 'select',
							'choices' => $sidebar_list,
							'ui' => true,
							'default_value' => 'epcl_sidebar_home',
						),
					),
                ),
                // Slider
				'module_slider' => array(
					'key' => $prefix_key.'module_slider',
					'name' => 'slider',
					'label' => esc_html__('Posts Slider', 'epcl_framework'),
					'display' => 'row',
					'sub_fields' => array(
						array(
							'key' => $prefix_key.'slider_category',
							'name' => 'slider_category',
							'label' => esc_html__('Featured Categories', 'epcl_framework'),
							'type' => 'taxonomy',
							'instructions' => esc_html__('(Optional) select only post from these categories.', 'epcl_framework'),
							'taxonomy' => 'category',
							'field_type' => 'multi_select',
							'return_format' => 'id',
							'multiple' => 0,
							'allow_null' => true
						),
						array(
							'key' => $prefix_key.'slider_limit',
							'name' => 'slider_limit',
							'label' => esc_html__('Posts Limit', 'epcl_framework'),
							'type' => 'number',
							'instructions' => esc_html__('Max number of posts to retrieve.', 'epcl_framework'),
							'min' => '1',
							'max' => '30',
							'step' => '1',
							'default_value' => '3',
						),
                        array (
							'key' => $prefix_key.'slider_enable_author',
							'name' => 'slider_enable_author',
							'label' => esc_html__('Enable Author', 'epcl_framework'),
							// 'instructions' => esc_html__('This option is useful if you want a totally separated module.', 'epcl_framework'),
							'type' => 'true_false',
							'ui' => true,
							'default_value' => true,
						),
					)
                ),
                // Carousel
				'module_carousel' => array(
					'key' => $prefix_key.'module_carousel',
					'name' => 'carousel',
					'label' => esc_html__('Posts Carousel', 'epcl_framework'),
					'display' => 'row',
					'sub_fields' => array(
						array(
							'key' => $prefix_key.'carousel_category',
							'name' => 'carousel_category',
							'label' => esc_html__('Featured Categories', 'epcl_framework'),
							'type' => 'taxonomy',
							'instructions' => esc_html__('(Optional) select only post from these categories.', 'epcl_framework'),
							'taxonomy' => 'category',
							'field_type' => 'multi_select',
							'return_format' => 'id',
							'multiple' => 0,
							'allow_null' => true
                        ),
                        array (
							'key' => $prefix_key.'carousel_orderby',
							'name' => 'carousel_orderby',
							'label' => esc_html__('Order by (optional)', 'epcl_framework'),
							// 'instructions' => esc_html__('Combine with order by Post Views to get your popular posts.', 'epcl_framework'),
							'type' => 'select',
							'choices' => array(
                                'date' => esc_html__('By Date (recent posts)', 'epcl_framework'),
                                'views' => esc_html__('By Post Views', 'epcl_framework'),
                            ),
							'ui' => false,
							'default_value' => 'date'
						),
                        array (
							'key' => $prefix_key.'carousel_date',
							'name' => 'carousel_date',
							'label' => esc_html__('Date (optional)', 'epcl_framework'),
							'instructions' => esc_html__('Combine with order by Post Views to get your popular posts.', 'epcl_framework'),
							'type' => 'select',
							'choices' => array(
                                'alltime' => esc_html__('All Time', 'epcl_framework'),
                                'pastmonth' => esc_html__('Past Month', 'epcl_framework'),
                                'pastweek' => esc_html__('Past Week', 'epcl_framework'),
                            ),
							'ui' => false,
							'default_value' => 'alltime'
						),
						array(
							'key' => $prefix_key.'carousel_limit',
							'name' => 'carousel_limit',
							'label' => esc_html__('Posts Limit', 'epcl_framework'),
							'type' => 'number',
							'instructions' => esc_html__('Max number of posts to retrieve.', 'epcl_framework'),
							'min' => '3',
							'max' => '99',
							'step' => '1',
							'default_value' => '9',
						),
						array(
							'key' => $prefix_key.'carousel_show_limit',
							'name' => 'carousel_show_limit',
							'label' => esc_html__('Visible Items', 'epcl_framework'),
							'type' => 'number',
							'instructions' => esc_html__('Number of visible elements, recommended: 3', 'epcl_framework'),
							'min' => '2',
							'max' => '6',
							'step' => '1',
							'default_value' => '5',
                        ),
                        array (
							'key' => $prefix_key.'carousel_enable_author',
							'name' => 'carousel_enable_author',
							'label' => esc_html__('Enable Author', 'epcl_framework'),
							// 'instructions' => esc_html__('This option is useful if you want a totally separated module.', 'epcl_framework'),
							'type' => 'true_false',
							'ui' => true,
							'default_value' => true,
						),
					)
                ),
                // Advertising
				'module_advertising' => array(
					'key' => $prefix_key.'module_advertising',
					'name' => 'advertising',
					'label' => esc_html__('Advertising', 'epcl_framework'),
					'display' => 'row',
					'sub_fields' => array(
						array (
							'key' => $prefix_key.'advertising_type',
							'name' => 'advertising_type',
							'label' => esc_html__('Advertising Type', 'epcl_framework'),
							// 'instructions' => esc_html__('How the featured image should look like.', 'epcl_framework'),
							'type' => 'button_group',
							'choices' => array(
								'image' => esc_html__('Image', 'epcl_framework'),
								'code' => esc_html__('External Code', 'epcl_framework'),
							),
							'layout' => 'horizontal',
							'default_value' => 'image',
						),
						array (
							'key' => $prefix_key.'advertising_image',
							'name' => 'advertising_image',
							'label' => esc_html__('Image', 'epcl_framework'),
							'type' => 'image',
							'instructions' => esc_html__('Recommended size: 728x90', 'epcl_framework'),
							'return_format' => 'array',
							'preview_size' => 'medium',
							'library' => 'all',
							'conditional_logic' => array (
								array (
									array (
									'field' => $prefix_key.'advertising_type',
									'operator' => '==',
									'value' => 'image',
									),
								),
							)
						),
						array (
							'key' => $prefix_key.'advertising_url',
							'name' => 'advertising_url',
							'label' => esc_html__('URL', 'epcl_framework'),
							'type' => 'url',
							'instructions' => esc_html__('Where the user will be redirected on click.', 'epcl_framework'),
							'conditional_logic' => array (
								array (
									array (
									'field' => $prefix_key.'advertising_type',
									'operator' => '==',
									'value' => 'image',
									),
								),
							)
						),
						array (
							'key' => $prefix_key.'advertising_code',
							'name' => 'advertising_code',
							'label' => esc_html__('Advertising Code', 'epcl_framework'),
							'instructions' => esc_html__('Add custom code for your banner for example Google Ads', 'epcl_framework'),
							'type' => 'textarea',
							'default_value' => '',
							'conditional_logic' => array (
								array (
									array (
									'field' => $prefix_key.'advertising_type',
									'operator' => '==',
									'value' => 'code',
									),
								),
							)
						),
					),
                ),
                // Text editor
				'module_text_editor' => array(
					'key' => $prefix_key.'module_text_editor',
					'name' => 'text_editor',
					'label' => esc_html__('Text Editor', 'epcl_framework'),
					'display' => 'row',
					'sub_fields' => array(
                        array (
							'key' => $prefix_key.'enable_editor_background',
							'name' => 'enable_editor_background',
							'label' => esc_html__('Enable Background', 'epcl_framework'),
							'instructions' => esc_html__('This option is useful if you want a totally separated module.', 'epcl_framework'),
							'type' => 'true_false',
							'ui' => true,
							'default_value' => true,
						),
						array(
							'key' => $prefix_key.'text_editor_content',
							'name' => 'text_editor_content',
							'label' => esc_html__('Add your description', 'epcl_framework'),
							'instructions' => esc_html__('Shortcodes are allowed.', 'epcl_framework'),
                            'type' => 'wysiwyg',
                            'media_upload' => true,
                            'toolbar' => 'full',	
						),
					),
                ),
                // Popular Categories
				'module_popular_categories' => array(
					'key' => $prefix_key.'module_popular_categories',
					'name' => 'popular_categories',
					'label' => esc_html__('Popular Categories', 'epcl_framework'),
					'display' => 'row',
					'sub_fields' => array(
						array(
							'key' => $prefix_key.'popular_cat_category',
							'name' => 'popular_cat_category',
							'label' => esc_html__('Featured Categories', 'epcl_framework'),
							'type' => 'taxonomy',
							'instructions' => esc_html__('(Optional) select only these categories.', 'epcl_framework'),
							'taxonomy' => 'category',
							'field_type' => 'multi_select',
							'return_format' => 'id',
							'multiple' => 0,
							'allow_null' => true
						),
						array(
							'key' => $prefix_key.'popular_cat_limit',
							'name' => 'popular_cat_limit',
							'label' => esc_html__('Categories Limit', 'epcl_framework'),
							'type' => 'number',
							'instructions' => esc_html__('Max number of categories to retrieve.', 'epcl_framework'),
							'min' => '3',
							'max' => '99',
							'step' => '1',
							'default_value' => '9',
						),
						array(
							'key' => $prefix_key.'popular_cat_show_limit',
							'name' => 'popular_cat_show_limit',
							'label' => esc_html__('Visible Items', 'epcl_framework'),
							'type' => 'number',
							'instructions' => esc_html__('Number of visible elements, recommended: 5', 'epcl_framework'),
							'min' => '4',
							'max' => '7',
							'step' => '1',
							'default_value' => '5',
                        ),
					),
                ),
			)
		)
	),
	'menu_order' => 3,
	'label_placement' => 'left',
	'instruction_placement' => 'label',
	'location' => array (
		array (
			array (
				'param' => 'page_template',
				'operator' => '==',
				'value' => 'page-templates/home.php',
			),
		),
	)
));


?>
