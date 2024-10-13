<?php

/* Unique options for every EP theme */

$admin_url = EPCL_PLUGIN_URL.'/functions/admin';

/* Social Profiles */

Redux::setSection( $opt_name, array(
	'title' => esc_html__('Social Profiles', 'epcl_framework'),
	'desc' => 'Social profiles are used in different places inside the theme.',
	'icon' => 'el-icon-user',
	'customizer' => false,
	'fields' => array(
		// array(
		// 	'id' => 'twitter_id',
		// 	'type' => 'text',
		// 	'title' => '<i class="fa-stack fa-lg"><i class="fa fa-square-o fa-stack-2x"></i><i class="fa fa-twitter fa-stack-1x"></i></i> '.esc_html__('Twitter ID', 'epcl_framework'),
		// 	'desc' => esc_html__('e.g. wordpress', 'epcl_framework')
        // ),
        array(
			'id' => 'twitter_url',
            'type' => 'text',
            'validate' => 'url',
			'title' => '<i class="fa-stack fa-lg"><i class="fa fa-square-o fa-stack-2x"></i><i class="fa fa-twitter fa-stack-1x"></i></i> '.esc_html__('Twitter URL', 'epcl_framework'),
			'desc' => esc_html__('e.g. https://twitter.com/estudiopatagon', 'epcl_framework')
		),
		array(
			'id' => 'facebook_url',
			'type' => 'text',
			'validate' => 'url',
			'title' => '<i class="fa-stack fa-lg"><i class="fa fa-square-o fa-stack-2x"></i><i class="fa fa-facebook fa-stack-1x"></i></i> '.esc_html__('Facebook URL', 'epcl_framework'),
			'desc' => esc_html__('e.g. http://www.facebook.com/estudiopatagon', 'epcl_framework')
		),
		array(
			'id' => 'instagram_url',
			'type' => 'text',
			'validate' => 'url',
			'title' => '<i class="fa-stack fa-lg"><i class="fa fa-square-o fa-stack-2x"></i><i class="fa fa-instagram fa-stack-1x"></i></i> '.esc_html__('Instagram url', 'epcl_framework'),
			'desc' => esc_html__('e.g. https://instagram.com/wordpress', 'epcl_framework')
        ),
        array(
			'id' => 'linkedin_url',
			'type' => 'text',
			'validate' => 'url',
			'title' => '<i class="fa-stack fa-lg"><i class="fa fa-square-o fa-stack-2x"></i><i class="fa fa-linkedin fa-stack-1x"></i></i> '.esc_html__('Linkedin url', 'epcl_framework'),
			'desc' => esc_html__('e.g. https://www.linkedin.com/in/my-name/', 'epcl_framework')
		),
		array(
			'id' => 'pinterest_url',
			'type' => 'text',
			'validate' => 'url',
			'title' => '<i class="fa-stack fa-lg"><i class="fa fa-square-o fa-stack-2x"></i><i class="fa fa-pinterest fa-stack-1x"></i></i> '.esc_html__('Pinterest url', 'epcl_framework'),
			'desc' => esc_html__('e.g. https://www.pinterest.com/envato', 'epcl_framework')
		),
		array(
			'id' => 'dribbble_url',
			'type' => 'text',
			'validate' => 'url',
			'title' => '<i class="fa-stack fa-lg"><i class="fa fa-square-o fa-stack-2x"></i><i class="fa fa-dribbble fa-stack-1x"></i></i> '.esc_html__('Dribbble url', 'epcl_framework'),
			'desc' => esc_html__('e.g. http://dribbble.com/wordpress', 'epcl_framework')
		),
		array(
			'id' => 'tumblr_url',
			'type' => 'text',
			'validate' => 'url',
			'title' => '<i class="fa-stack fa-lg"><i class="fa fa-square-o fa-stack-2x"></i><i class="fa fa-tumblr fa-stack-1x"></i></i> '.esc_html__('Tumblr URL', 'epcl_framework'),
			'desc' => esc_html__('e.g. http://iamcollis.tumblr.com', 'epcl_framework')
		),
		array(
			'id' => 'youtube_url',
			'type' => 'text',
			'validate' => 'url',
			'title' => '<i class="fa-stack fa-lg"><i class="fa fa-square-o fa-stack-2x"></i><i class="fa fa-youtube fa-stack-1x"></i></i> '.esc_html__('Youtube url', 'epcl_framework'),
			'desc' => esc_html__('e.g. https://www.youtube.com/user/wordpress', 'epcl_framework')
		),
		array(
			'id' => 'flickr_url',
			'type' => 'text',
			'validate' => 'url',
			'title' => '<i class="fa-stack fa-lg"><i class="fa fa-square-o fa-stack-2x"></i><i class="fa fa-flickr fa-stack-1x"></i></i> '.esc_html__('Flickr url', 'epcl_framework'),
			'desc' => esc_html__('e.g. http://www.flickr.com/photos/wordpress', 'epcl_framework')
        ),
        array(
			'id' => 'twitch_url',
			'type' => 'text',
			'validate' => 'url',
			'title' => '<i class="fa-stack fa-lg"><i class="fa fa-square-o fa-stack-2x"></i><i class="fa fa-twitch fa-stack-1x"></i></i> '.esc_html__('Twitch url', 'epcl_framework'),
			'desc' => esc_html__('e.g. https://www.twitch.tv/name', 'epcl_framework')
        ),
        array(
			'id' => 'vk_url',
			'type' => 'text',
			'validate' => 'url',
			'title' => '<i class="fa-stack fa-lg"><i class="fa fa-square-o fa-stack-2x"></i><i class="fa fa-vk fa-stack-1x"></i></i> '.esc_html__('Vkontakte url', 'epcl_framework'),
			'desc' => esc_html__('e.g. https://www.vk.com/name', 'epcl_framework')
        ),
        array(
			'id' => 'telegram_url',
			'type' => 'text',
			'validate' => 'url',
			'title' => '<i class="fa-stack fa-lg"><i class="fa fa-square-o fa-stack-2x"></i><i class="fa fa-telegram fa-stack-1x"></i></i> '.esc_html__('Telegram url', 'epcl_framework'),
			'desc' => esc_html__('e.g. https://t.me/username', 'epcl_framework')
		),
        array(
			'id' => 'discord_url',
			'type' => 'text',
			'validate' => 'url',
			'title' => '<i class="fa-stack fa-lg"><i class="fa fa-square-o fa-stack-2x"></i><i class="fa fa-microphone fa-stack-1x"></i></i> '.esc_html__('Discord url', 'epcl_framework'),
			'desc' => esc_html__('e.g. https://discord.com/servers/id', 'epcl_framework')
		),
        array(
			'id' => 'tiktok_url',
			'type' => 'text',
			'validate' => 'url',
			'title' => '<i class="fa-stack fa-lg"><i class="fa fa-square-o fa-stack-2x"></i><i class="fa fa-music fa-stack-1x"></i></i> '.esc_html__('Tiktok url', 'epcl_framework'),
			'desc' => esc_html__('e.g. https://www.tiktok.com/@username', 'epcl_framework')
		),
		array(
			'id' => 'rss_url',
			'type' => 'text',
			'validate' => 'url',
			'title' => '<i class="fa-stack fa-lg"><i class="fa fa-square-o fa-stack-2x"></i><i class="fa fa-rss fa-stack-1x"></i></i> '.esc_html__('RSS URL', 'epcl_framework'),
			'desc' => esc_html__('e.g. http://estudiopatagon.com/feed', 'epcl_framework')
		),
	)
) );

?>