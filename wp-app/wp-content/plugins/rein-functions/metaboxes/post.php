<?php

// Registered Sidebars
$sidebars = $GLOBALS['wp_registered_sidebars'];
$sidebar_list = array();
foreach($sidebars as $sidebar){
	if($sidebar['id'] != 'epcl_sidebar_footer')
		$sidebar_list[$sidebar['id']] = $sidebar['name'];
}

$prefix = EPCL_THEMEPREFIX.'_';
$prefix_key = 'epcl_post_';

// $edd_plugin = array (
//     'key' => $prefix_key.'edd_download_id',
//     'name' => 'edd_download_id',
//     'label' => esc_html__('EDD Plugin Enabled', 'epcl_framework'),
//     'value' => esc_html__('False', 'epcl_framework'),
//     'type' => 'text',
//     'readonly' => true
// );

// if( function_exists('edd_get_checkout_uri') ){
//     $edd_plugin = array (
//         'key' => $prefix_key.'edd_download_id',
//         'name' => 'edd_download_id',
//         'label' => esc_html__('EDD Download Product', 'epcl_framework'),
//         'instructions' => esc_html__('If you are using Easy Digital Downloads plugin, you can select your product here.', 'epcl_framework'),
//         'type' => 'post_object',
//         'return_format' => 'id',
//         'post_type' => 'download',
//         'required' => false,
//         'default_value' => '',
//         'conditional_logic' => array (
//             array (
//                 array (
//                     'field' => $prefix_key.'enable_download',
//                     'operator' => '==',
//                     'value' => 1,
//                 ),
//             ),
//         )
//     );
// }

// Posts
acf_add_local_field_group( array(
	'key' => 'epcl_post',
	'title' => esc_html__('General Information', 'epcl_framework'),
	'fields' => array (
        array (
			'key' => $prefix_key.'msg_thumbs',
			'name' => 'msg_thumbs',
			'label' => esc_html__('Important', 'epcl_framework'),
            'message' => __('
            <h3 style="margin-top: 0">Optimized images recommended sizes are:</h3>
            <p><b>For Single content (inside post):</b> 950x500px</p><p><b>Grids images on home:</b> 850x500px</p>
            <p><b>Classic posts on home:</b> 850x450px</p>
            <h3 style="margin-top: 0">Featured image recommended size:</h3>
            <p><b>Standard style:</b> 950x500px</p>
            <p><b>Fullcover style or post without sidebar:</b> 1500x500px</p>
            <h3><b>Recommended Compression Tool:</b> <a href="https://compressimage.toolur.com/" target="_blank">Toolur Website</a></h3>
            ', 'epcl_framework'),
            'type' => 'message',
        ),
        array (
			'key' => $prefix_key.'optimized_image',
			'name' => 'optimized_image',
			'label' => esc_html__('Optimized Image (optional)', 'epcl_framework'),
			'type' => 'image',
			'instructions' => __('This image will be used on homepages only and the uploaded image will not be cropped, resized or any other method that can change the size or the quality of the image.<br><br>(This is useful if you have texts over images or you are already compressed the image manually).', 'epcl_framework'),
			'required' => false,
			'return_format' => 'array',
			'preview_size' => 'thumbnail',
			'library' => 'all',
        ),
        array (
			'key' => $prefix_key.'optimized_image_slider',
			'name' => 'optimized_image_slider',
			'label' => esc_html__('Optimized Image for Slider (optional)', 'epcl_framework'),
			'type' => 'image',
			'instructions' => __('This image will be used on <b>slider module only</b> and the uploaded image will not be cropped, resized or any other method that can change the size or the quality of the image.<br><br>Recommended Size: 1280x600.', 'epcl_framework'),
			'required' => false,
			'return_format' => 'array',
			'preview_size' => 'thumbnail',
			'library' => 'all',
        ),
        array (
			'key' => $prefix_key.'loop_style',
			'name' => 'loop_style',
			'label' => esc_html__('Loop Post Style', 'epcl_framework'),
			'instructions' => esc_html__('How the post should look like on homepages, archives, etc.', 'epcl_framework'),
			'type' => 'button_group',
			'choices' => array(
				'standard' => esc_html__('Standard', 'epcl_framework'),
				'bgstyle' => esc_html__('Background Style', 'epcl_framework'),
			),
			'layout' => 'horizontal',
			'default_value' => 'standard',
		),
		array (
			'key' => $prefix_key.'style',
			'name' => 'style',
			'label' => esc_html__('Single Post Style', 'epcl_framework'),
			'instructions' => esc_html__('How the featured image should look like inside the post.', 'epcl_framework'),
			'type' => 'button_group',
			'choices' => array(
				'standard' => esc_html__('Standard', 'epcl_framework'),
				'fullcover' => esc_html__('Full Cover', 'epcl_framework'),
			),
			'layout' => 'horizontal',
			'default_value' => 'standard',
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
        array (
			'key' => $prefix_key.'views_counter',
			'name' => 'views_counter',
			'label' => esc_html__('Views Counter', 'epcl_framework'),
			'instructions' => esc_html__('How many times this post has been viewed.', 'epcl_framework'),
			'type' => 'number',
			'readonly' => false,
            'default_value' => 0
        ),
	),
	'menu_order' => 3,
	'label_placement' => 'left',
	'instruction_placement' => 'label',
	'location' => array (
		array (
			array (
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'post',
			),
		),
	)
));

// Video Format
acf_add_local_field_group( array(
	'key' => 'epcl_post_video',
	'title' => esc_html__('Video Information', 'epcl_framework'),
	'fields' => array (
        array (
			'key' => $prefix_key.'show_featured_image_video',
			'name' => 'show_featured_image',
			'label' => esc_html__('Show Featured Image', 'epcl_framework'),
			'instructions' => esc_html__('By default it will be displayed the video on home pages, archives, etc. Enabling this option will show the featured image instead.', 'epcl_framework'),
			'type' => 'true_false',
			'default_value' => false,
			'ui' => true,
        ),
		array (
			'key' => $prefix_key.'video_type',
			'name' => 'video_type',
			'label' => esc_html__('Video Type', 'epcl_framework'),
			'instructions' => esc_html__('Select platform.', 'epcl_framework'),
			'type' => 'button_group',
			'choices' => array(
				'youtube' => esc_html__('Youtube', 'epcl_framework'),
                'vimeo' => esc_html__('Vimeo', 'epcl_framework'),
                'custom' => esc_html__('Custom Embed', 'epcl_framework'),
			),
			'layout' => 'horizontal',
			'default_value' => 'youtube',
		),
		array (
			'key' => $prefix_key.'video_url',
			'name' => 'video_url',
			'label' => esc_html__('Video URL', 'epcl_framework'),
			'instructions' => esc_html__('eg. https://www.youtube.com/watch?v=v9nBysHSzhE', 'epcl_framework'),
            'type' => 'url',
            'conditional_logic' => array (
                array (
                    array (
                        'field' => $prefix_key.'video_type',
                        'operator' => '!=',
                        'value' => 'custom',
                    ),
                ),
            )
        ),
        array (
			'key' => $prefix_key.'custom_embed',
			'name' => 'custom_embed',
			'label' => esc_html__('Custom Embed Code', 'epcl_framework'),
			'instructions' => esc_html__('eg. <iframe>....</iframe>', 'epcl_framework'),
            'type' => 'textarea',
            'conditional_logic' => array (
                array (
                    array (
                        'field' => $prefix_key.'video_type',
                        'operator' => '==',
                        'value' => 'custom',
                    ),
                ),
            )
		),
	),
	'menu_order' => 0,
	'label_placement' => 'left',
	'instruction_placement' => 'label',
	'location' => array (
		array (
			array (
				'param' => 'post_format',
				'operator' => '==',
				'value' => 'video',
			),
		),
	)
));

// Gallery format
acf_add_local_field_group( array(
	'key' => 'epcl_post_gallery',
	'title' => esc_html__('Gallery Information', 'epcl_framework'),
	'fields' => array (
		array (
			'key' => $prefix_key.'gallery',
			'name' => 'gallery',
			'label' => esc_html__('Gallery images', 'epcl_framework'),
			'type' => 'gallery',
			'instructions' => '',
			'required' => false,
			'return_format' => 'array',
			'preview_size' => 'thumbnail',
			'library' => 'all',
		),
	),
	'menu_order' => 0,
	'label_placement' => 'left',
	'instruction_placement' => 'label',
	'location' => array (
		array (
			array (
				'param' => 'post_format',
				'operator' => '==',
				'value' => 'gallery',
			),
		),
	)
));

// Audio format
acf_add_local_field_group( array(
	'key' => 'epcl_post_audio',
	'title' => esc_html__('Audio Information', 'epcl_framework'),
	'fields' => array (
        array (
			'key' => $prefix_key.'show_featured_image_audio',
			'name' => 'show_featured_image',
			'label' => esc_html__('Show Featured Image', 'epcl_framework'),
			'instructions' => esc_html__('By default it will be displayed the audio iframe on home pages, archives, etc. Enabling this option will show the featured image instead.', 'epcl_framework'),
			'type' => 'true_false',
			'default_value' => false,
			'ui' => true,
        ),
		array (
			'key' => $prefix_key.'soundcloud_url',
			'name' => 'soundcloud_url',
			'label' => esc_html__('SoundCloud URL', 'epcl_framework'),
			'instructions' => esc_html__('eg. https://soundcloud.com/mahu-semo/led-zepellin-stairway-to-heaven', 'epcl_framework'),
			'type' => 'url',
		),
	),
	'menu_order' => 0,
	'label_placement' => 'left',
	'instruction_placement' => 'label',
	'location' => array (
		array (
			array (
				'param' => 'post_format',
				'operator' => '==',
				'value' => 'audio',
			),
		),
	)
));

// Link format
acf_add_local_field_group( array(
	'key' => 'epcl_post_link',
	'title' => esc_html__('Audio Information', 'epcl_framework'),
	'fields' => array (
		array (
			'key' => $prefix_key.'link_url',
			'name' => 'link_url',
			'label' => esc_html__('External URL', 'epcl_framework'),
			'instructions' => esc_html__('eg. https://www.google.com', 'epcl_framework'),
			'type' => 'url',
		),
	),
	'menu_order' => 0,
	'label_placement' => 'left',
	'instruction_placement' => 'label',
	'location' => array (
		array (
			array (
				'param' => 'post_format',
				'operator' => '==',
				'value' => 'link',
			),
		),
	)
));

?>
