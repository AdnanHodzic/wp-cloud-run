<?php
if ( ! class_exists( 'epcl_instagram' ) ) {
	class epcl_instagram extends WP_Widget{

		function __construct(){
			$widget_ops = array('description' => esc_html__('Display recent photos from your Instagram account.', 'epcl_framework'));
			parent::__construct( false, esc_html__('(EP) Instagram Gallery', 'epcl_framework'), $widget_ops);
		}

		function widget($args, $instance){
            echo '<p><b>Instagram API</b> was fully deprecated on March 2020, check this guide: <a href="https://estudiopatagon.ticksy.com/article/15668/" target="_blank">https://estudiopatagon.ticksy.com/article/15668/</a></p>';
            return;
			extract($args);
			$title = apply_filters('widget_title', $instance['title']);
            echo $before_widget;
                $class = '';
				if($title) echo $before_title.$title.$after_title;
				if(!$instance['number']) $instance['number'] = 6;
                // if(!$instance['instagram_id']) esc_html_e('You must enter a valid Instagram User ID', 'epcl_framework');
                if(!$instance['instagram_token']) esc_html_e('You must enter a valid Instagram Access Token', 'epcl_framework');
                if( $instance['open_in'] == 'lightbox'){
                    $class = 'epcl-gallery';
                }
				if($instance['instagram_token']):
	?>
					<div class="epcl-instagram-gallery <?php echo esc_attr($class); ?>" id="epcl-instagram-<?php echo $this->id; ?>" data-limit="<?php echo $instance['number']; ?>">
                        <?php
                        $access_token = $instance['instagram_token'];
                        $ig_user_id = 'self';
                        $limit = $instance['number'];
                        
                        $remote_wp = wp_remote_get( "https://api.instagram.com/v1/users/self/media/recent/?access_token=" . $access_token );
                            
                        $instagram_response = json_decode( $remote_wp['body'] );
                        

                        echo '<ul class="grid-parent np-mobile">';
                            
                        if( $remote_wp['response']['code'] == 200 ) {
                            $i = 0;
                            // echo '<a href="https://www.instagram.com/'.epcl_get_option('instagram_id').'" target="_blank" class="ig-user button title"><i class="fa fa-instagram"></i> '.esc_html__('Follow me!', 'rein').'</a>';
                            foreach( $instagram_response->data as $m ) {
                                if( $i == $limit ) break;
                                $caption = '';
                                $url = $m->link; // Story URL
                                $thumb = esc_url( $m->images->low_resolution->url ); // Thumb 300x300
                                $image_hd = esc_url( $m->images->standard_resolution->url ); // High Quality image
                                if( isset(  $m->caption->text ) ){
                                    $caption =  $m->caption->text ; // Caption (optional)
                                }
                                $class = '';

                                if( $instance['open_in'] == 'lightbox'){
                                    $url = $image_hd;                                    
                                }

                                echo '<li class="grid-50 tablet-grid-33 mobile-grid-33"><div class="wrapper"><a href="' . $url . '" class="item thumb hover-effect '.$class.'" target="_blank" rel="nofollow" title="'.$caption.'"><span class="fullimage cover lazy" data-src="'.$thumb.'"></span></a></div></li>';
                    
                                $i++;
                            
                            }
                            
                        } elseif ( $remote_wp['response']['code'] == 400 ) {
                            echo '<b>Error: ' . $remote_wp['response']['message'] . ': </b>' . $instagram_response->meta->error_message;
                         
                        }

                        echo '</ul>';

                        ?>
					</div>
	<?php
				endif;
			echo $after_widget;
		}

		function update($new_instance, $old_instance){
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
            $instance['number'] = (int) $new_instance['number'];
            $instance['open_in'] = $new_instance['open_in'];
            // $instance['instagram_id'] = $new_instance['instagram_id'];
            $instance['instagram_token'] = $new_instance['instagram_token'];
			return $instance;
		}

		function form($instance){
			$defaults = array(
				'title' => 'Instagram',
                'number' => 6,
                'open_in' => 'external_link',
                // 'instagram_id' => '',
                'instagram_token' => ''
			);
			$instance = wp_parse_args((array)$instance, $defaults);
            $number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 6;
            echo '<p><b>Instagram API</b> was fully deprecated on March 2020, check this guide: <a href="https://estudiopatagon.ticksy.com/article/15668/" target="_blank">https://estudiopatagon.ticksy.com/article/15668/</a></p>';
            return;
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
					<?php esc_html_e('Title:', 'epcl_framework'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('number'); ?>"><?php esc_html_e( 'Number of photos to show:', 'epcl_framework'); ?></label>
				<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="2" />
			</p>
			<!-- <p>
				<label for="<?php echo $this->get_field_id('instagram_id'); ?>">
					<?php esc_html_e('Instagram User ID:', 'epcl_framework'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('instagram_id'); ?>" name="<?php echo $this->get_field_name('instagram_id'); ?>" type="text" value="<?php echo $instance['instagram_id']; ?>" />
					<br /><span><?php esc_html_e('e.g. estudiopatagon', 'epcl_framework'); ?></span>
				</label>
            </p> -->
            <p>
				<label for="<?php echo $this->get_field_id('open_in'); ?>"><?php esc_html_e('Open in:', 'epcl_framework'); ?></label>
                <select id="<?php echo $this->get_field_id('open_in'); ?>" name="<?php echo $this->get_field_name('open_in'); ?>">
					<option <?php if ($instance['open_in'] == 'external_link') echo 'selected="selected"'; ?> value="external_link"><?php esc_html_e('External link (Instagram page).', 'epcl_framework'); ?></option>
                    <option <?php if ($instance['open_in'] == 'lightbox') echo 'selected="selected"'; ?> value="lightbox"><?php esc_html_e('Lightbox', 'epcl_framework'); ?></option>
                </select>
                <br>
                <small><?php esc_html_e('When images are clicked should redirect to your Instagram page or just open on a lightbox.', 'epcl_framework'); ?></small>
			</p>
            <p>
				<label for="<?php echo $this->get_field_id('instagram_token'); ?>">
					<?php esc_html_e('Instagram Access Token:', 'epcl_framework'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('instagram_token'); ?>" name="<?php echo $this->get_field_name('instagram_token'); ?>" type="text" value="<?php echo $instance['instagram_token']; ?>" />
					<br /><span><?php _e('You can follow <a href="https://instagram.pixelunion.net/" target="_blank" rel="nofollow">this guide</a> to create your own access token.<br><br>e.g: <b>18432875969.f88701c.66a2ebcac14b41b78e81a5b99e48b651</b>', 'epcl_framework'); ?></span>
				</label>
			</p>
			<?php
		}

	}

	function epcl_register_instagram() {
		register_widget('epcl_instagram');
	}

	add_action('widgets_init', 'epcl_register_instagram');
}
