<?php

/* Unique options for every EP theme */

$admin_url = EPCL_PLUGIN_URL.'/functions/admin';

$background_color = '#2C2048';

$red = $primary_color = '#FF6071';
$yellow = $secondary_color = '#FF7E8E';
$third_color = '#41e3e4';
$text_color = '#DADEED';
$border_color = 'rgba(255,255,255,0.15)';
$boxes_bg_color = '#362953';
$submenu_bg_color = '#47356d';
$input_bg_color = '#37285a';

$black = '#222222';
$white = '#ffffff';

/* Blog */

Redux::setSection( $opt_name, array(
	'title' => esc_html__('Styling', 'epcl_framework'),
	'icon' => 'el-icon-pencil',
) );

/* Background */

Redux::setSection( $opt_name, array(
	'title' => esc_html__('Body Background', 'epcl_framework'),
    // 'icon' => 'el-icon-pencil',
    'subsection' => true,
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
			'default' => $background_color,
			//'validate' => 'color',
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
			// 'default' => '#485DA6',
			//'validate' => 'color',
            'transparent' => false,
            'default'  => array(
                'from' => '#485DA6',
                'to'   => '#32b37b', 
            ),
        ),	
        array(
			'id' => 'body_overlay_color',
            'type' => 'color_rgba',
            'required' => array('background_type', '=', '3'),
			'title' => esc_html__('Body overlay color', 'epcl_framework'),
            // 'subtitle' =>  esc_html__("Default: $border_color", 'epcl_framework'),
            'desc' => esc_html__('This overlay will help you to generate enough contrast to read the website texts.', 'epcl_framework'),
			// //'validate' => 'color',
            'transparent' => false,
            'default'   => array(
                'color' => $background_color,
                'alpha' => 0.45
            ),
            'options' => array(
                'clickout_fires_change' => true,
            )
        ),   
	)
) );

/* Basic Styling */

Redux::setSection( $opt_name, array(
	'title' => esc_html__('Basic Styling', 'epcl_framework'),
    // 'icon' => 'el-icon-pencil',
    'subsection' => true,
	'fields' => array(
        array(
			'id' => 'primary_color',
			'type' => 'color',
			'title' => esc_html__('Primary color ', 'epcl_framework'),
			'default' => $primary_color,
			'subtitle' =>  esc_html__("Default: ".$primary_color, 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
		),
		array(
			'id' => 'secondary_color',
			'type' => 'color',
			'title' => esc_html__('Secondary color ', 'epcl_framework'),
			'default' => $secondary_color,
			'subtitle' =>  esc_html__("Default: ".$secondary_color, 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
		),
		array(
			'id' => 'text_color',
			'type' => 'color',
			'title' => esc_html__('Text Color ', 'epcl_framework'),
			'default' => $text_color,
			'subtitle' =>  esc_html__("Default: ".$text_color, 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
        ),
        array(
			'id' => 'boxes_bg_color',
			'type' => 'color',
			'title' => esc_html__('Main boxes background color', 'epcl_framework'),
			'default' => $boxes_bg_color,
            'subtitle' =>  esc_html__("Default: ".$boxes_bg_color, 'epcl_framework'),
            'desc' =>  esc_html__("This background apply on main content list, pages and also as background on footer menu and social section.", 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
		),
        array(
			'id' => 'main_link_gradient',
			'type' => 'color_gradient',
			'title' => esc_html__('Main gradient effect color', 'epcl_framework'),
			'desc' => esc_html__('Hover color for post titles, header active menu item, etc.', 'epcl_framework'),
			//'validate' => 'color',
            'transparent' => false,
            'default'  => array(
                'from' => $primary_color,
                'to'   => $secondary_color, 
            ),
        ),
	)
) );

/* Header */

Redux::setSection( $opt_name, array(
	'title' => esc_html__('Header', 'epcl_framework'),
    // 'icon' => 'el-icon-edit',
    'subsection' => true,
	'fields' => array(
        array(
			'id' => 'header_bg_color',
			'type' => 'color',
			'title' => esc_html__('Header Background color', 'epcl_framework'),
			'default' => 'transparent',
			'subtitle' =>  esc_html__('Default: transparent', 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => true
        ),
        array(
			'id' => 'header_menu_link_color',
			'type' => 'link_color',
			'title' => esc_html__('Menu links color', 'epcl_framework'),
			'desc' => '',
			//'validate' => 'color',
			'active' => true,
			'default' => array(
				'regular' => $white,
                'hover' => $white,
                'active' => $white,
			),
        ),
        array(
			'id' => 'header_submenu_link_color',
			'type' => 'link_color',
			'title' => esc_html__('Submenu links color', 'epcl_framework'),
			'desc' => '',
			//'validate' => 'color',
			'active' => true,
			'default' => array(
				'regular' => $white,
                'hover' => $white,
                'active' => $white,
			),
        ),
        array(
			'id' => 'header_submenu_bg_color',
			'type' => 'color',
			'title' => esc_html__('Header Submenu Background color', 'epcl_framework'),
			'default' => $submenu_bg_color,
			'subtitle' =>  esc_html__('Default: '.$submenu_bg_color, 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
        ),
        array(
			'id' => 'header_sticky_bg_color',
			'type' => 'color',
			'title' => esc_html__('Header Sticky Background color', 'epcl_framework'),
			'default' => $boxes_bg_color,
			'subtitle' =>  esc_html__("Default: ".$boxes_bg_color, 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
		),
		array(
			'id' => 'header_mobile_icon_color',
			'type' => 'color',
			'title' => esc_html__('Header Mobile Icon color', 'epcl_framework'),
			'default' => $white,
			'subtitle' =>  esc_html__('Default: '.$white, 'epcl_framework'),
			'desc' =>  esc_html__('This is the menu bar icon, only appears on mobile to show/hide the main menu.', 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
        ),
        array(
			'id' => 'header_search_bg_color',
			'type' => 'color',
			'title' => esc_html__('Lightbox Background color', 'epcl_framework'),
			'default' => $boxes_bg_color,
            'subtitle' =>  esc_html__("Default: ".$boxes_bg_color, 'epcl_framework'),
            'desc' =>  esc_html__('This option will change all lightboxes background colors (Search and Galleries).', 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
        ),
        array(
			'id' => 'header_search_text_color',
			'type' => 'color',
			'title' => esc_html__('Search Lightbox Title color', 'epcl_framework'),
			'default' => $text_color,
			'subtitle' =>  esc_html__("Default: ".$text_color, 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
		),
	)
) );

/* Content */

Redux::setSection( $opt_name, array(
	'title' => esc_html__('Content', 'epcl_framework'),
    // 'icon' => 'el-icon-edit',
    'subsection' => true,
	'fields' => array(
        array(
			'id' => 'selection_bg_color',
			'type' => 'color',
			'title' => esc_html__('Selection background color', 'epcl_framework'),
			'default' => $text_color,
            'subtitle' =>  esc_html__('Default: '.$text_color, 'epcl_framework'),
            'desc' => esc_html__('You will see this event whenever a user make a text selection.', 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
        ),
        array(
			'id' => 'selection_text_color',
			'type' => 'color',
			'title' => esc_html__('Selection text color', 'epcl_framework'),
			'default' => $white,
            'subtitle' =>  esc_html__('Default: '.$white, 'epcl_framework'),
            'desc' => esc_html__('You will see this event whenever a user make a text selection.', 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
        ),
        array(
			'id' => 'main_content_bg_color',
			'type' => 'color',
			'title' => esc_html__('Main container background color', 'epcl_framework'),
			'default' => $boxes_bg_color,
            'subtitle' =>  esc_html__("Default: ".$boxes_bg_color, 'epcl_framework'),
            'desc' => esc_html__('This is the main container is used for post list and single post content. By default is the same color as "Boxes background color"', 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
        ),
        array(
			'id' => 'content_border_color',
			'type' => 'color_rgba',
			'title' => esc_html__('Content border color', 'epcl_framework'),
			'default' => $border_color,
            'subtitle' =>  esc_html__("Default: $border_color", 'epcl_framework'),
            'desc' => esc_html__('Border between some article elements and used on the main sidebar.', 'epcl_framework'),
			// //'validate' => 'color',
            'transparent' => false,
            'default'   => array(
                'color'     => $white,
                'alpha'     => 0.15
            ),
            'options' => array(
                'clickout_fires_change' => true,
            )
        ),        
        array(
			'id' => 'main_title_color',
			'type' => 'color',
			'title' => esc_html__('Main titles color', 'epcl_framework'),
			'default' => $white,
			'subtitle' =>  esc_html__("Default: ".$white, 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
        ),
	)
) );

/* Buttons */

Redux::setSection( $opt_name, array(
	'title' => esc_html__('Buttons/Links', 'epcl_framework'),
    // 'icon' => 'el-icon-edit',
    'subsection' => true,
	'fields' => array(
        array(
			'id' => 'content_link_color',
			'type' => 'link_color',
			'title' => esc_html__('General link color', 'epcl_framework'),
			'subtitle' => esc_html__("Default: #DADEED, Hover: #ffffff", 'epcl_framework'),
			//'validate' => 'color',
			'active' => false,
			'default' => array(
				'regular' => $text_color,
                'hover' => $white,
			),
        ),
        array(
			'id' => 'button_bg_color',
			'type' => 'color_gradient',
			'title' => esc_html__('Main button background color', 'epcl_framework'),
			// 'default' => $red,
            'subtitle' =>  esc_html__("Default: same as main gradient", 'epcl_framework'),
            // 'desc' => '',
			//'validate' => 'color',
            'transparent' => false,
            'default'  => array(
                'from' => $primary_color,
                'to'   => $secondary_color, 
            ),
        ),
        array(
			'id' => 'button_text_color',
			'type' => 'color',
			'title' => esc_html__('Main button text color', 'epcl_framework'),
			'default' => $white,
			'subtitle' =>  esc_html__("Default: $white", 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
        ),
        array(
			'id' => 'tag_text_color',
			'type' => 'link_color',
			'title' => esc_html__('Default Tag button text color', 'epcl_framework'),
			'subtitle' => esc_html__("Default: #ffffff, Hover: #ffffff", 'epcl_framework'),
			//'validate' => 'color',
			'active' => false,
			'default' => array(
				'regular' => $white,
                'hover' => $white,
			),
        ),
        array(
			'id' => 'tag_bg_color',
			'type' => 'link_color',
			'title' => esc_html__('Default Tag decoration color', 'epcl_framework'),
            'subtitle' => esc_html__("Default: #7169FE, hover: #8338EC", 'epcl_framework'),
            'desc' => esc_html__('This is the small circle on the left.', 'epcl_framework'),
			//'validate' => 'color',
			'active' => false,
			'default' => array(
				'regular' => $primary_color,
                'hover' => $secondary_color,
			),
        ),
        array(
			'id' => 'reply_button_border_color',
			'type' => 'color',
			'title' => esc_html__('Reply comments button border color', 'epcl_framework'),
			// 'default' => $red,
            'subtitle' =>  esc_html__("Default: $primary_color", 'epcl_framework'),
            // 'desc' => '',
			//'validate' => 'color',
            'transparent' => false,
            'default'  => $primary_color
        ),
        array(
			'id' => 'reply_button_text_color',
			'type' => 'link_color',
			'title' => esc_html__('Reply comments button text color', 'epcl_framework'),
			'default' => $white,
			'subtitle' =>  esc_html__("Default: $white", 'epcl_framework'),
			//'validate' => 'color',
            'transparent' => false,
            'active' => false,
            'default' => array(
				'regular' => $white,
                'hover' => $white,
			),
        ),
	)
) );

/* Sidebar */

Redux::setSection( $opt_name, array(
	'title' => esc_html__('Sidebar', 'epcl_framework'),
    // 'icon' => 'el-icon-edit',
    'subsection' => true,
	'fields' => array(
        // array(
		// 	'id' => 'sidebar_bg_color',
		// 	'type' => 'color',
		// 	'title' => esc_html__('Sidebar widgets background color', 'epcl_framework'),
		// 	'default' => $boxes_bg_color,
        //     'subtitle' =>  esc_html__('Default: '.$boxes_bg_color, 'epcl_framework'),
        //     // 'desc' => '',
		// 	//'validate' => 'color',
		// 	'transparent' => false
        // ),
        array(
			'id' => 'sidebar_text_color',
			'type' => 'color',
			'title' => esc_html__('Sidebar text color', 'epcl_framework'),
			'default' => $text_color,
			'subtitle' =>  esc_html__("Default: $text_color", 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
		),
        array(
			'id' => 'sidebar_link_color',
			'type' => 'link_color',
			'title' => esc_html__('Sidebar links color', 'epcl_framework'),
			'subtitle' => esc_html__("Default: ".$text_color, 'epcl_framework'),
			//'validate' => 'color',
			'active' => false,
			'default' => array(
				'regular' => $text_color,
                'hover' => $white,
			),
        ),
        array(
			'id' => 'sidebar_title_color',
			'type' => 'color',
			'title' => esc_html__('Sidebar titles color', 'epcl_framework'),
			'default' => $white,
			'subtitle' =>  esc_html__('Default: '.$white, 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
        ),
        array(
			'id' => 'sidebar_title_decoration_color',
			'type' => 'color',
			'title' => esc_html__('Sidebar title border color', 'epcl_framework'),
			'default' => $primary_color,
			'subtitle' =>  esc_html__("Default: ".$primary_color, 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
		),
	)
) );

/* Forms */

Redux::setSection( $opt_name, array(
	'title' => esc_html__('Forms', 'epcl_framework'),
    // 'icon' => 'el-icon-edit',
    'subsection' => true,
	'fields' => array(
        array(
            'id'   => 'info_forms',
            'type' => 'info',
            'style' => 'info',
            'icon' => 'el el-info-circle',
            'title' => esc_html__('Important:', 'epcl_framework'),
            'desc' => esc_html__('All these options affects contact and comments form.', 'epcl_framework')
        ),
        array(
			'id' => 'input_bg_color',
			'type' => 'color',
			'title' => esc_html__('Input box background color', 'epcl_framework'),
			'default' => 'transparent',
            'subtitle' =>  esc_html__('Default: '.$input_bg_color, 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => true
        ),
        array(
			'id' => 'input_border_color',
			'type' => 'color_rgba',
			'title' => esc_html__('Input box border color', 'epcl_framework'),
			'default' => $border_color,
            'subtitle' =>  esc_html__('Default: '.$border_color, 'epcl_framework'),
			// //'validate' => 'color',
            'transparent' => false,
            'default'   => array(
                'color'     => $white,
                'alpha'     => 0.15
            ),
            'options' => array(
                'clickout_fires_change' => true,
            )
        ),
        array(
			'id' => 'input_text_color',
			'type' => 'color',
			'title' => esc_html__('Input box text color', 'epcl_framework'),
			'default' => $text_color,
            'subtitle' =>  esc_html__('Default: '.$text_color, 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
        ),
        array(
			'id' => 'label_text_color',
			'type' => 'color',
			'title' => esc_html__('Label text color', 'epcl_framework'),
			'default' => $text_color,
            'subtitle' =>  esc_html__('Default: '.$text_color, 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
        ),
        // array(
		// 	'id' => 'custom_select_bg_color',
		// 	'type' => 'color',
		// 	'title' => esc_html__('Custom select box background color', 'epcl_framework'),
		// 	'default' => $secondary_color,
        //     'subtitle' =>  esc_html__("Default: ".$secondary_color, 'epcl_framework'),
		// 	//'validate' => 'color',
		// 	'transparent' => false
        // ),
        // array(
		// 	'id' => 'custom_select_text_color',
		// 	'type' => 'color',
		// 	'title' => esc_html__('Custom select box text color', 'epcl_framework'),
		// 	'default' => $white,
        //     'subtitle' =>  esc_html__("Default: ".$white, 'epcl_framework'),
		// 	//'validate' => 'color',
		// 	'transparent' => false
        // ),
        array(
			'id' => 'submit_bg_color',
			'type' => 'color_gradient',
			'title' => esc_html__('Submit button background color', 'epcl_framework'),
			// 'default' => $red,
            'subtitle' =>  esc_html__("Default: same as main gradient", 'epcl_framework'),
			//'validate' => 'color',
            'transparent' => false,
            'default'  => array(
                'from' => $primary_color,
                'to'   => $secondary_color, 
            ),
        ),
        array(
			'id' => 'submit_text_color',
			'type' => 'color',
			'title' => esc_html__('Submit button text color', 'epcl_framework'),
			'default' => $white,
            'subtitle' =>  esc_html__('Default: '.$white, 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
        ),
	)
) );

/* Footer */

Redux::setSection( $opt_name, array(
	'title' => esc_html__('Footer', 'epcl_framework'),
    // 'icon' => 'el-icon-edit',
    'subsection' => true,
	'fields' => array(
        array(
			'id' => 'footer_bg_color',
			'type' => 'color',
			'title' => esc_html__('Footer background color', 'epcl_framework'),
			'default' => $background_color,
            'subtitle' =>  esc_html__('Default: '.$background_color, 'epcl_framework'),
            // 'desc' => '',
			//'validate' => 'color',
			'transparent' => false
        ),
        array(
			'id' => 'footer_text_color',
			'type' => 'color',
			'title' => esc_html__('Footer text color', 'epcl_framework'),
			'default' => $white,
			'subtitle' =>  esc_html__('Default: '.$white, 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
		),
        array(
			'id' => 'footer_link_color',
			'type' => 'link_color',
			'title' => esc_html__('Footer links color', 'epcl_framework'),
			'subtitle' => esc_html__('Default: '.$white, 'epcl_framework'),
			//'validate' => 'color',
			'active' => false,
			'default' => array(
				'regular' => $white,
                'hover' => $white,
			),
        ),
        array(
			'id' => 'footer_title_color',
			'type' => 'color',
			'title' => esc_html__('Footer titles color', 'epcl_framework'),
			'default' => '#ffffff',
			'subtitle' =>  esc_html__('Default: #ffffff', 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
        ),
        array(
			'id' => 'footer_widget_border_color',
			'type' => 'color',
			'title' => esc_html__('Footer title border color', 'epcl_framework'),
			'subtitle' =>  esc_html__("Default: ".$primary_color, 'epcl_framework'),
            'default' => $primary_color,
            'transparent' => false
        ),
        array(
			'id' => 'footer_copyright_color',
			'type' => 'color',
			'title' => esc_html__('Footer copyright text color', 'epcl_framework'),
			'default' => $text_color,
			'subtitle' =>  esc_html__('Default: '.$text_color, 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false
        ),
        array(
			'id' => 'footer_copyright_link_color',
			'type' => 'color',
			'title' => esc_html__('Footer copyright links color', 'epcl_framework'),
			'subtitle' => esc_html__('Default: '.$white, 'epcl_framework'),
			//'validate' => 'color',
			'transparent' => false,
			'default' => $white
        ),
	)
) );

?>