<?php

/* Unique options for every EP theme */

$admin_url = EPCL_PLUGIN_URL.'/functions/admin';

/* Shop */

Redux::setSection( $opt_name, array(
	'title' => esc_html__('Shop', 'epcl_framework'),
    'icon' => 'el-icon-shopping-cart',
    // 'subsection' => true,
	'fields' => array(
        array(
			'id' => 'shop_search_box',
			'type' => 'switch',
			'title' => esc_html__('Display Search Box on Filters', 'epcl_framework'),
			'desc' => esc_html__('This option affects the global articles list with filters (red one).', 'epcl_framework'),
			'default' => 1
        ),
        array(
			'id' => 'shop_search_text',
			'type' => 'text',
			'title' => esc_html__('Search box Text', 'epcl_framework'),
			'fullwidth' => true,
            'desc' => __('By default: Search in the shop...', 'epcl_framework'),
            'default' => 'Search in the shop...'
        ),
        array(
			'id' => 'shop_sort_select_box',
			'type' => 'switch',
			'title' => esc_html__('Display Sort Select Box on Filters', 'epcl_framework'),
			'desc' => esc_html__('This option affects the global articles list with filters (red one).', 'epcl_framework'),
			'default' => 1
        ),
	)
) );


?>