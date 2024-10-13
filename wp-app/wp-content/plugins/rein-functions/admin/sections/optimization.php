<?php

/* Unique options for every EP theme */

$admin_url = EPCL_PLUGIN_URL.'/functions/admin';

/* Advanced Settings */

Redux::setSection( $opt_name, array(
	'title' => esc_html__('Optimization', 'epcl_framework'),
	'icon' => ' el-icon-dashboard',
) );

Redux::setSection( $opt_name, array(
	'title' => esc_html__('Standard Settings', 'epcl_framework'),
    // 'icon' => 'el-icon-dashboard',
    'subsection' => true,
	'fields' => array(
		array(
			'id' => 'enable_optimization',
			'type' => 'switch',
            'title' => esc_html__('Enable Speed Optimization', 'epcl_framework'),
            // 'subtitle' => esc_html__('W3 Total Cache plugin recommended', 'epcl_framework'),
			'desc' => esc_html__('This will move the main theme styles to the footer and load minified CSS and JS libraries. If you are not comfortable with website load (some jumps when loading), just disable this option.', 'epcl_framework'),
			'default' => true
        ),
        array(
			'id' => 'move_jquery_footer',
			'type' => 'switch',
            'title' => esc_html__('Move jQuery to Footer', 'epcl_framework'),
            // 'subtitle' => esc_html__('W3 Total Cache plugin recommended', 'epcl_framework'),
			'desc' => esc_html__('This will the main javascript framework to the footer and will increase Google Page Score. If you are experiencing any error on the console, just disable this option.', 'epcl_framework'),
			'default' => false
        ),
        array(
			'id' => 'remove_gutenberg_styles',
			'type' => 'switch',
            'title' => esc_html__('Remove Gutenberg Styles', 'epcl_framework'),
            // 'subtitle' => esc_html__('', 'epcl_framework'),
			'desc' => esc_html__('This will remove Gutenberg styles from the <head> only activate it if you and all your website users (writers) are using the Classic Editor, use with caution.', 'epcl_framework'),
			'default' => false
        ),
        array(
			'id' => 'remove_gutenberg_styles_home',
			'type' => 'switch',
            'title' => esc_html__('Remove Gutenberg Styles on Home', 'epcl_framework'),
            // 'subtitle' => esc_html__('', 'epcl_framework'),
			'desc' => esc_html__('You can safely remove Gutenberg Styles if your home is not using any custom text.', 'epcl_framework'),
			'default' => false
        ),
		array(
			'id' => 'enable_lazyload',
			'type' => 'switch',
			'title' => esc_html__('Enable Lazy Load (Lists)', 'epcl_framework'),
			'subtitle' => esc_html__('For loops: home pages, archives and categories.', 'epcl_framework'),
			'desc' => esc_html__('This will load your images when scrolling (this option can improve your website overall speed).', 'epcl_framework'),
			'default' => false
        ),
        array(
			'id' => 'enable_lazyload_posts',
			'type' => 'switch',
			'title' => esc_html__('Enable Lazy Load (Post)', 'epcl_framework'),
			'subtitle' => esc_html__('For single post content.', 'epcl_framework'),
			'desc' => esc_html__('This will load your images when scrolling only for articles content.', 'epcl_framework'),
			'default' => false
        ),
        array(
			'id' => 'enable_lazyload_embed',
			'type' => 'switch',
			'title' => esc_html__('Enable Lazy Load (Embeds)', 'epcl_framework'),
			// 'subtitle' => esc_html__('For single post content.', 'epcl_framework'),
			'desc' => esc_html__('This will load your videos/embeds when scrolling only works for Vimeo/Youtube and iframes in general.', 'epcl_framework'),
			'default' => false
        ),
        array(
			'id' => 'enable_lazyload_adsense',
			'type' => 'switch',
			'title' => esc_html__('Enable Lazy Load (Adsense)', 'epcl_framework'),
			'subtitle' => esc_html__('Beta feature use with caution.', 'epcl_framework'),
			'desc' => __('This will load your adsense blocks when scrolling.<br><b>Important:</b> don\'t include any kind of <br>&lt;script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"> tag to maximize your website speed.<br>This option will load that script dynamically.', 'epcl_framework'),
			'default' => false
		),
	)
) );

Redux::setSection( $opt_name, array(
	'title' => esc_html__('Advanced Settings', 'epcl_framework'),
    // 'icon' => 'el-icon-dashboard',
    'subsection' => true,
	'fields' => array(
        array(
			'id' => 'adv_optimization_title',
            'type' => 'info',
            'style' => 'info',
			'title' => __('Important', 'epcl_framework'),
			'subtitle' => __( 'All these options <b>will affect only files and codes related to the theme</b>, if you have any additional plugin, it is recommend to install Autoptimize and follow the documentation.', 'epcl_framework'),
			'indent' => false
        ),
        array(
			'id' => 'secondary_css_method',
			'type' => 'button_set',
            'title' => esc_html__('Secondary Theme CSS load method (low priority elements)', 'epcl_framework'),
            'subtitle' => esc_html__('This will affect: Carousel, Lightbox and Shortcodes CSS files.', 'epcl_framework'),
            'desc' => __('<b>Standard:</b> is render blocking but the CSS appears inmediatly.<br><b>Preload:</b> elements will be preloaded even before other critical CSS elements, this will assign high priority (not render blocking).<br><b>Important:</b> if you are using PageSpeed on your hosting, standard method is recommended.', 'epcl_framework'),
            'options'  => array(
                'standard' => esc_html__('Standard (head)', 'epcl_framework'),
                // 'prefetch' => esc_html__('Prefetch', 'epcl_framework'),
                'preload' => esc_html__('Preload (recommended)', 'epcl_framework'),              
            ),
			'default' => 'preload'
        ),
        array(
			'id' => 'fonts_icons_method',
			'type' => 'button_set',
			'title' => esc_html__('Theme Fonts Icons load method', 'epcl_framework'),
            'desc' => __('<b>Standard:</b> is render blocking but the icons appears inmediatly.<br><b>Footer:</b> icons will appear when everything is already loaded.<br><b>Javascript</b> will improve the speed, but the icons will be loaded with an important delay.', 'epcl_framework'),
            'options'  => array(
                'standard' => esc_html__('Standard (head)', 'epcl_framework'),
                'footer' => esc_html__('Footer (recommended)', 'epcl_framework'),
                'javascript' => esc_html__('Javascript with delay', 'epcl_framework'),              
            ),
			'default' => 'footer'
        ),
        array(
			'id' => 'font_icons_delay',
            'type' => 'text',
            'validate' => 'numeric',
			'required' => array('fonts_icons_method', '=', 'javascript'),
			'title' => esc_html__('Timeout on miliseconds:', 'epcl_framework'),
			'desc' => esc_html__('(Optional) 1000 = 1 second, this will add a delay before load the font icon.', 'epcl_framework'),
            'default' => '750',
            'unit' => ''
        ),
        array(
			'id' => 'title_ajax_scripts',
            'type' => 'info',
            'notice' => false,
            'title' => __('<strong style="font-size: 16px;">Async Scripts (Javascripts only)</strong>', 'epcl_framework'),
            'subtitle' => __( 'This section will help you to reduce the <b>negative impact of third party scripts</b>, a good example is Adsense, this kind of script greatly reduce the server speed so you can add it into the Custom Scripts area and load that JS dynamically after all your website is fully loaded.<br><br><b>Note:</b> in case of Adsense, remove all <b>adsbygoogle.js</b> script from your <b>source code:</b> <a href="https://prnt.sc/r4w05g" target="_blank">Example</a>', 'epcl_framework'),
        ),
        array(
			'id' => 'remove_custom_ajax_scripts',
			'type' => 'switch',
            'title' => esc_html__('Remove Custom Scripts', 'epcl_framework'),
            // 'subtitle' => esc_html__('W3 Total Cache plugin recommended', 'epcl_framework'),
			'desc' => __('It is recommended to remove your custom scripts manually but you can remove them automatically. <br><b>How it works:</b> If you add adsbygoogle.js into below section and this option is enabled, the script will be automatically removed from your source code (HTML), use with caution.', 'epcl_framework'),
			'default' => false
        ),
        array(
			'id' => 'custom_ajax_scripts',
			'type' => 'multi_text',
			'title' => esc_html__('Add Custom Scripts', 'epcl_framework'),
			// 'subtitle' => esc_html__('Enter an unique name', 'epcl_framework'),
			'desc' => __('e.g. https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js <br>e.g. https://connect.facebook.net/en_US/sdk.js', 'epcl_framework'),
			'show_empty' => false
        ),
        array(
            'id' => 'script_timeout',
            'type'  => 'text',
            'validate' => 'numeric',
            'title' => esc_attr__('Timeout on miliseconds:', 'epcl_framework'),
            'desc' => esc_html__('(Optional) 1000 = 1 second, this will add a delay before load the script.', 'epcl_framework'),
            'default' => 1500,
            // 'required' => array('custom_ajax_scripts', 'contains', 'http'),
        ),
	)
) );