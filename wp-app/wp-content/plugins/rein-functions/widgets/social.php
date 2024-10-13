<?php
if ( ! class_exists( 'epcl_social' ) ) {
	class epcl_social extends WP_Widget{

		function __construct(){
			$widget_ops = array('description' => esc_html__('Display your social profiles.', 'epcl_framework'));
			parent::__construct( false, esc_html__('(EP) Social', 'epcl_framework'), $widget_ops);
		}

		function widget($args, $instance){
            // WP 5.9 Patch: always disable widget preview in the backend
            if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
                return false;
            }
			global $epcl_theme;
			extract($args);
			$title = apply_filters('widget_title', $instance['title'] );

			$enable_twitter = $instance[ 'enable_twitter' ] ? true : false;
			$enable_facebook = $instance[ 'enable_facebook' ] ? true : false;
			$enable_instagram = $instance[ 'enable_instagram' ] ? true : false;
			$enable_linkedin = $instance[ 'enable_linkedin' ] ? true : false;
			$enable_pinterest = $instance[ 'enable_pinterest' ] ? true : false;
			$enable_dribbble = $instance[ 'enable_dribbble' ] ? true : false;
			$enable_tumblr = $instance[ 'enable_tumblr' ] ? true : false;
			$enable_youtube = $instance[ 'enable_youtube' ] ? true : false;
            $enable_flickr = $instance[ 'enable_flickr' ] ? true : false;
            $enable_twitch = isset( $instance[ 'enable_twitch' ] ) && $instance[ 'enable_twitch' ] ? true : false;
            $enable_vk = isset( $instance[ 'enable_vk' ] ) && $instance[ 'enable_vk' ] ? true : false;
            $enable_telegram = isset( $instance[ 'enable_telegram' ] ) && $instance[ 'enable_telegram' ] ? true : false;
            $enable_discord = isset( $instance[ 'enable_discord' ] ) && $instance[ 'enable_discord' ] ? true : false;
            $enable_tiktok = isset( $instance[ 'enable_tiktok' ] ) && $instance[ 'enable_tiktok' ] ? true : false;
			$enable_rss = $instance[ 'enable_rss' ] ? true : false;

			echo $before_widget;

				if($title) echo $before_title.$title.$after_title;
				echo '<div class="icons title">';

					if( $epcl_theme['twitter_url'] && $enable_twitter != false )
						echo '<a href="'.$epcl_theme['twitter_url'].'" class="twitter" target="_blank" rel="nofollow noopener"><i class="fa fa-twitter"></i><p>'.esc_html__('Twitter', 'rein').' <span>'.esc_html__('Follow me!', 'rein').'</span></p></a>';

					if( $epcl_theme['facebook_url'] && $enable_facebook != false )
						echo '<a href="'.$epcl_theme['facebook_url'].'" class="facebook" target="_blank" rel="nofollow noopener"><i class="fa fa-facebook"></i><p>'.esc_html__('Facebook', 'rein').' <span>'.esc_html__('Follow me!', 'rein').'</span></p></a>';

					if( $epcl_theme['instagram_url'] && $enable_instagram != false )
                        echo '<a href="'.$epcl_theme['instagram_url'].'" class="instagram" target="_blank" rel="nofollow noopener"><i class="fa fa-instagram"></i><p>'.esc_html__('Instagram', 'rein').' <span>'.esc_html__('Our photos!', 'rein').'</span></p></a>';

                    if( $epcl_theme['linkedin_url'] && $enable_linkedin != false )
						echo '<a href="'.esc_url( $epcl_theme['linkedin_url'] ).'" class="linkedin" target="_blank" rel="nofollow noopener"><i class="fa fa-linkedin"></i> <p>'.esc_html__('Linkedin', 'rein').' <span>'.esc_html__('Visit me!', 'rein').'</span></p></a>';

					if( $epcl_theme['pinterest_url'] && $enable_pinterest != false )
						echo '<a href="'.$epcl_theme['pinterest_url'].'" class="pinterest" target="_blank" rel="nofollow noopener"><i class="fa fa-pinterest"></i><p>'.esc_html__('Pinterest', 'rein').' <span>'.esc_html__('Pin it!', 'rein').'</span></p></a>';

					if( $epcl_theme['dribbble_url'] && $enable_dribbble != false )
						echo '<a href="'.$epcl_theme['dribbble_url'].'" class="dribbble" target="_blank" rel="nofollow noopener"><i class="fa fa-dribbble"></i><p>'.esc_html__('Dribbble', 'rein').' <span>'.esc_html__('Our work!', 'rein').'</span></p></a>';

					if( $epcl_theme['tumblr_url'] && $enable_tumblr != false )
						echo '<a href="'.$epcl_theme['tumblr_url'].'" class="tumblr" target="_blank" rel="nofollow noopener"><i class="fa fa-tumblr"></i><p>'.esc_html__('Tumblr', 'rein').' <span>'.esc_html__('Visit me!', 'rein').'</span></p></a>';

					if( $epcl_theme['youtube_url'] && $enable_youtube != false )
						echo '<a href="'.$epcl_theme['youtube_url'].'" class="youtube" target="_blank" rel="nofollow noopener"><i class="fa fa-youtube"></i> <p>'.esc_html__('Youtube', 'rein').' <span>'.esc_html__('Check my videos!', 'rein').'</span></p></a>';

					if( $epcl_theme['flickr_url'] && $enable_flickr != false )
                        echo '<a href="'.$epcl_theme['flickr_url'].'" class="flickr" target="_blank" rel="nofollow noopener"><i class="fa fa-flickr"></i><p>'.esc_html__('Flickr', 'rein').' <span>'.esc_html__('See more photos!', 'rein').'</span></p></a>';

                    if( $epcl_theme['twitch_url'] && $enable_twitch != false )
                        echo '<a href="'.$epcl_theme['twitch_url'].'" class="twitch" target="_blank" rel="nofollow noopener"><i class="fa fa-twitch"></i><p>'.esc_html__('Twitch', 'rein').' <span>'.esc_html__('Check my videos!', 'rein').'</span></p></a>';
                        
                    if( $epcl_theme['vk_url'] && $enable_vk != false )
                        echo '<a href="'.$epcl_theme['vk_url'].'" class="vk" target="_blank" rel="nofollow noopener"><i class="fa fa-vk"></i><p>'.esc_html__('VKontakte', 'rein').' <span>'.esc_html__('Follow me!', 'rein').'</span></p></a>';   
                        
                    if( $epcl_theme['telegram_url'] && $enable_telegram != false )
						echo '<a href="'.$epcl_theme['telegram_url'].'" class="telegram" target="_blank" rel="nofollow noopener"><i class="fa fa-telegram"></i><p>'.esc_html__('Telegram', 'rein').' <span>'.esc_html__('Follow me!', 'rein').'</span></p></a>';  

                    if( $epcl_theme['discord_url'] && $enable_discord != false )
						echo '<a href="'.$epcl_theme['discord_url'].'" class="discord" target="_blank" rel="nofollow noopener"><span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19.3034 5.33716C17.9344 4.71103 16.4805 4.2547 14.9629 4C14.7719 4.32899 14.5596 4.77471 14.411 5.12492C12.7969 4.89144 11.1944 4.89144 9.60255 5.12492C9.45397 4.77471 9.2311 4.32899 9.05068 4C7.52251 4.2547 6.06861 4.71103 4.70915 5.33716C1.96053 9.39111 1.21766 13.3495 1.5891 17.2549C3.41443 18.5815 5.17612 19.388 6.90701 19.9187C7.33151 19.3456 7.71356 18.73 8.04255 18.0827C7.41641 17.8492 6.82211 17.5627 6.24904 17.2231C6.39762 17.117 6.5462 17.0003 6.68416 16.8835C10.1438 18.4648 13.8911 18.4648 17.3082 16.8835C17.4568 17.0003 17.5948 17.117 17.7434 17.2231C17.1703 17.5627 16.576 17.8492 15.9499 18.0827C16.2789 18.73 16.6609 19.3456 17.0854 19.9187C18.8152 19.388 20.5875 18.5815 22.4033 17.2549C22.8596 12.7341 21.6806 8.80747 19.3034 5.33716ZM8.5201 14.8459C7.48007 14.8459 6.63107 13.9014 6.63107 12.7447C6.63107 11.5879 7.45884 10.6434 8.5201 10.6434C9.57071 10.6434 10.4303 11.5879 10.4091 12.7447C10.4091 13.9014 9.57071 14.8459 8.5201 14.8459ZM15.4936 14.8459C14.4535 14.8459 13.6034 13.9014 13.6034 12.7447C13.6034 11.5879 14.4323 10.6434 15.4936 10.6434C16.5442 10.6434 17.4038 11.5879 17.3825 12.7447C17.3825 13.9014 16.5548 14.8459 15.4936 14.8459Z"></path></svg></span><p>'.esc_html__('Discord', 'rein').' <span>'.esc_html__('Follow me!', 'rein').'</span></p></a>'; 

                    if( $epcl_theme['tiktok_url'] && $enable_discord != false )
						echo '<a href="'.$epcl_theme['tiktok_url'].'" class="tiktok" target="_blank" rel="nofollow noopener"><span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8.24537V15.5C16 19.0899 13.0899 22 9.5 22C5.91015 22 3 19.0899 3 15.5C3 11.9101 5.91015 9 9.5 9C10.0163 9 10.5185 9.06019 11 9.17393V12.3368C10.5454 12.1208 10.0368 12 9.5 12C7.567 12 6 13.567 6 15.5C6 17.433 7.567 19 9.5 19C11.433 19 13 17.433 13 15.5V2H16C16 4.76142 18.2386 7 21 7V10C19.1081 10 17.3696 9.34328 16 8.24537Z"></path></svg></span><p>'.esc_html__('TikTok', 'rein').' <span>'.esc_html__('Follow me!', 'rein').'</span></p></a>'; 

					if( $epcl_theme['rss_url'] && $enable_rss != false )
						echo '<a href="'.$epcl_theme['rss_url'].'" class="rss" target="_blank" rel="nofollow noopener"><i class="fa fa-rss"></i><p>'.esc_html__('RSS', 'rein').' <span>'.esc_html__('Get our latest news!', 'rein').'</span></p></a>';

				echo '</div>';
			echo $after_widget;
		}

		function update($new_instance, $old_instance){
			$instance = $old_instance;
			$instance['title'] = strip_tags( $new_instance['title'] );
			$instance[ 'enable_twitter' ] = $new_instance[ 'enable_twitter' ];
			$instance[ 'enable_facebook' ] = $new_instance[ 'enable_facebook' ];
			$instance[ 'enable_instagram' ] = $new_instance[ 'enable_instagram' ];
			$instance[ 'enable_linkedin' ] = $new_instance[ 'enable_linkedin' ];
			$instance[ 'enable_pinterest' ] = $new_instance[ 'enable_pinterest' ];
			$instance[ 'enable_dribbble' ] = $new_instance[ 'enable_dribbble' ];
			$instance[ 'enable_tumblr' ] = $new_instance[ 'enable_tumblr' ];
			$instance[ 'enable_youtube' ] = $new_instance[ 'enable_youtube' ];
            $instance[ 'enable_flickr' ] = $new_instance[ 'enable_flickr' ];
            $instance[ 'enable_twitch' ] = $new_instance[ 'enable_twitch' ];
            $instance[ 'enable_vk' ] = $new_instance[ 'enable_vk' ];
            $instance[ 'enable_telegram' ] = $new_instance[ 'enable_telegram' ];
            $instance[ 'enable_discord' ] = $new_instance[ 'enable_discord' ];
            $instance[ 'enable_tiktok' ] = $new_instance[ 'enable_tiktok' ];
			$instance[ 'enable_rss' ] = $new_instance[ 'enable_rss' ];
			return $instance;
		}

		function form($instance){
			$defaults = array(
				'title' => 'Social',
				'enable_twitter' => 'on',
				'enable_facebook' => 'on',
				'enable_instagram' => 'on',
				'enable_linkedin' => 'on',
				'enable_pinterest' => 'on',
				'enable_dribbble' => 'on',
				'enable_tumblr' => 'on',
				'enable_youtube' => 'on',
                'enable_flickr' => 'on',
                'enable_twitch' => 'on',
                'enable_vk' => 'on',
                'enable_telegram' => 'on',
                'enable_discord' => 'off',
                'enable_tiktok' => 'off',
				'enable_rss' => 'on',
			);
			$instance = wp_parse_args( (array)$instance, $defaults );
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
					<?php esc_html_e('Title:', 'epcl_framework'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
				</label>
			</p>
			<p><small><?php esc_html_e('Don\'t forget to fill your social profiles', 'epcl_framework'); ?> <a href="<?php echo admin_url(); ?>admin.php?page=ThemeOptionsPanel&tab=27"><?php esc_html_e('here', 'epcl_framework'); ?>.</a></small></p>
            <p>
                <?php foreach( $defaults as $key => $value ): if($key != 'title'): ?>
                    <input class="checkbox" type="checkbox" <?php checked( $instance[ $key ], 'on' ); ?> id="<?php echo $this->get_field_id( $key ); ?>" name="<?php echo $this->get_field_name( $key ); ?>" />
                    <label for="<?php echo $this->get_field_id( $key ); ?>"> <?php echo 'Enable '.ucfirst( str_replace('enable_', '', $key) ); ?></label>
                    <br>
                <?php  endif; endforeach; ?>
            </p>
			<?php
		}

	}
}

function epcl_register_social() {
	register_widget('epcl_social');
}

add_action('widgets_init', 'epcl_register_social');
