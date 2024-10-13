<?php

/* Unique options for every EP theme */

$admin_url = EPCL_PLUGIN_URL.'/functions/admin';

/* General Settings */

Redux::setSection( $opt_name, array(
	'title' => esc_html__('Subscribe Settings', 'epcl_framework'),
	'icon' => ' el-icon-envelope',
	'fields' => array(
        array(
			'id' => 'mailchimp_url',
			'type' => 'text',
			'validate' => 'url',
			'title' => esc_html__('Subscribe Url', 'epcl_framework'),
			'subtitle' => esc_html__('You can use a Mailchimp Form or any mailing system that generate a public Url. (Mailchimp is recommended, check the documentation).', 'epcl_framework'),
			'fullwidth' => true,
			'desc' => esc_html__('e.g. http://eepurl.com/dxHIUz', 'epcl_framework')
        ),
        array(
			'id' => 'enable_subscribe',
			'type' => 'switch',
			'title' => esc_html__('Display subscribe button on Header', 'epcl_framework'),
			'desc' => esc_html__('You must enter a valid Mailchimp url to use this section.', 'epcl_framework'),
			'default' => false
        ),
        array(
			'id' => 'title_subscribe_button',
			'type' => 'text',
			'title' => esc_html__('Title of subscribe header button', 'epcl_framework'),
            'desc' => esc_html__('e.g. Subscribe', 'epcl_framework'),
            'required' => array('enable_subscribe', '=', '1'),
        ),
        array(
			'id' => 'enable_subscribe_footer',
			'type' => 'switch',
			'title' => esc_html__('Display subscribe section on Footer', 'epcl_framework'),
			'desc' => esc_html__('You must enter a valid Mailchimp url to use this section.', 'epcl_framework'),
			'default' => false
        ),
        // array(
		// 	'id' => 'bg_subscribe',
		// 	'type' => 'media',
		// 	'url' => true,
		// 	'preview'=> true,
		// 	'title' => esc_html__('Subscribe Background Image', 'epcl_framework'),
        //     'desc' => esc_html__('Recommended size: 1650x600 pixels', 'epcl_framework'),
        //     'required' => array('enable_subscribe_footer', '=', '1'),
        // ),
        array(
			'id' => 'title_subscribe',
			'type' => 'text',
			'title' => esc_html__('Title of subscribe section (Optional)', 'epcl_framework'),
            'desc' => esc_html__('e.g. Subscribe to Rein', 'epcl_framework'),
            'required' => array('enable_subscribe_footer', '=', '1'),
        ),
        array(
			'id' => 'description_subscribe',
			'type' => 'text',
			'title' => esc_html__('Description of subscribe section (Optional)', 'epcl_framework'),
            'desc' => esc_html__('e.g. Get the latest posts delivered right to your email.', 'epcl_framework'),
            'required' => array('enable_subscribe_footer', '=', '1'),
        ),
	)
) );

?>