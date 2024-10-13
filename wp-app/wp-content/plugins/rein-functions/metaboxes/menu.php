<?php

$prefix = EPCL_THEMEPREFIX.'_';
$prefix_key = 'epcl_menu_item_';

$fa_icons = epcl_get_font_icons();
$array_icons = array();
foreach( $fa_icons as $fa ){
	$array_icons[$fa] = '<i class="'.$fa.'" style="font-size: 120%;"></i>&nbsp;&nbsp;'.str_replace('fa ', '', $fa);
}
//var_dump($array_icons);
// Custom user fields
acf_add_local_field_group( array(
	'key' => 'epcl_menu_item',
	'title' => esc_html__('Aditional Information', 'epcl_framework'),
	'fields' => array (
		array(
			'key' => $prefix_key.'icon',
			'label' => esc_html__('Menu Fontawesome Icon (Optional)', 'epcl_framework'),
			'name' => 'menu_item_icon',
			'type' => 'select',
			'choices' => $array_icons,
			'allow_null' => true,
			'multiple' => false,
			'ui' => 1,
			'ajax' => 0,
			'return_format' => 'value',
		),
	),
	'label_placement' => 'left',
	'instruction_placement' => 'field',
	'location' => array (
		array (
			array(
				'param' => 'nav_menu_item',
				'operator' => '==',
				'value' => 'all',
			),
		),
	)
));

function epcl_load_fa_on_menus( $hook ) {
	if( $hook !== 'nav-menus.php' ){
		return;
	}
	wp_enqueue_style('fontawesome', 'https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css?ver=4.7.1', NULL, NULL);
}
add_action( 'admin_enqueue_scripts', 'epcl_load_fa_on_menus' );

add_filter('wp_nav_menu_objects', 'epcl_wp_nav_menu_objects', 10, 2);
function epcl_wp_nav_menu_objects( $items, $args ) {
	foreach( $items as &$item ) {
		$icon = get_field('menu_item_icon', $item);
		if( $icon ) {
			$item->title = '<i class="'.$icon.'"></i> '.$item->title;
		}

	}
	return $items;
}

?>
