<?php

/* Unique options for every EP theme */

$admin_url = EPCL_PLUGIN_URL.'/functions/admin';

/* Advanced Settings */

Redux::setSection( $opt_name, array(
	'title' => esc_html__('Advanced Settings', 'epcl_framework'),
	'icon' => 'el-icon-cogs',
	'fields' => array(
        array(
			'id' => 'enable_acf_menu',
			'type' => 'switch',
			'title' => esc_html__('Display ACF menu on the sidebar.', 'epcl_framework'),
			'desc' => esc_html__('If you are a developer, this will let you access to the ACF (Custom Fields menu) and create custom fields. After activated, you must reload.', 'epcl_framework'),
			'default' => 0
        ),
        array(
			'id' => 'enable_gutenberg_admin',
			'type' => 'switch',
			'title' => esc_html__('Enable Gutenberg Custom Styles', 'epcl_framework'),
			'desc' => esc_html__('By default the Gutenberg editor will display same background and styles from the front-end, you can disable them if you want to show the Gutenberg default styles.', 'epcl_framework'),
			'default' => 1
		),
        array(
			'id' => 'enable_open_graph',
			'type' => 'switch',
			'title' => esc_html__('Enable Open Graph', 'epcl_framework'),
			'desc' => esc_html__('Enable Open Graph basic support, if you are using Yoast SEO, it is recommended to disable this option.', 'epcl_framework'),
			'default' => 1
		),
		array(
			'id' => 'css_code',
			'type' => 'ace_editor',
			'title' => esc_html__('Custom CSS Code', 'epcl_framework'),
			'desc' => esc_html__('e.g. #header{ background: #000; } Dont use &lt;style&gt; tags', 'epcl_framework'),
			'subtitle' => esc_html__('Paste your CSS code here.', 'epcl_framework'),
			'mode' => 'css',
			'theme' => 'monokai'
        ),
        array(
			'id' => 'custom_scripts',
			'type' => 'textarea',
			'title' => esc_html__('Custom Scripts Below <head>', 'epcl_framework'),
			'desc' => esc_html__('Here you can paste your Google Analytics code (not your id) or Adsense code. If you dont have it or you are already using one, just leave blank.', 'epcl_framework')
        ),
        array(
			'id' => 'custom_scripts_body',
			'type' => 'textarea',
			'title' => esc_html__('Custom Scripts just after opening <body>', 'epcl_framework'),
			'desc' => esc_html__('Here you can paste your any custom script that will be included on Body with high priority, like Google Tag code.', 'epcl_framework')
        ),
        array(
			'id' => 'custom_scripts_footer',
			'type' => 'textarea',
			'title' => esc_html__('Custom Scripts on Footer before closing </body>', 'epcl_framework'),
			'desc' => esc_html__('Here you can paste your any custom script that will be included on Footer with less priority.', 'epcl_framework')
        ),
        
		// array(
		// 	'id' => 'js_code',
		// 	'type' => 'ace_editor',
		// 	'title' => esc_html__('Custom JS Code', 'epcl_framework'),
		// 	'desc' => esc_html__('e.g. alert("Hello World!");<br> Dont use <b>&lt;script&gt;</b> tags or <strong>document.ready</strong>', 'epcl_framework'),
		// 	'subtitle' => esc_html__('Paste your JS code here.', 'epcl_framework'),
		// 	'mode' => 'javascript',
		// 	'theme' => 'monokai'
		// )
	)
) );

?>