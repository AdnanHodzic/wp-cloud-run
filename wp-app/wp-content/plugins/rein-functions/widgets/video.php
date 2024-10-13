<?php
if ( ! class_exists( 'EPCL_video' ) ) {
	class EPCL_video extends WP_Widget{

		function __construct(){
			$widget_ops = array('description' => esc_html__('Display a Youtube or Vimeo video.', 'epcl_framework'));
			parent::__construct( false, esc_html__('(EP) Video', 'epcl_framework'), $widget_ops);
		}

		function widget($args, $instance){
            // WP 5.9 Patch: always disable widget preview in the backend
            if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
                return false;
            }
			extract($args);
			$title = apply_filters('widget_title', $instance['title']);
			$width = '100%';
			$height = 250;
			if($instance['height']) $height = $instance['height'];
			echo $before_widget;
				if($title) echo $before_title.$title.$after_title;
				if($instance['type'] == 'youtube'){
				    $url = $instance['url'];
					preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+(?=\?)|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $url, $matches);
					$video_url ='https://www.youtube.com/embed/'.$matches[0].'?rel=0&showinfo=0';
						$media_code = '<div class="ep-shortcode ep-video"><iframe width="'.$width.'" height="'.$height.'" src="'.$video_url.'" frameborder="0" allowfullscreen></iframe></div>';

				}elseif($instance['type'] == 'vimeo'){
					$result = preg_match('/(\d+)/', $instance['url'], $matches);
					if($result) $vimeo_id = $matches[0]; else $vimeo_id = $instance['url'];
					$media_code = '<div class="ep-shortcode ep-video"><iframe src="https://player.vimeo.com/video/'.$vimeo_id.'" width="'.$width.'" height="'.$height.'" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe></div>';
				}
				echo $media_code;
			echo $after_widget;
		}

		function update($new_instance, $old_instance){
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['url'] = $new_instance['url'];
			$instance['type'] = $new_instance['type'];
			$instance['height'] = $new_instance['height'];
			return $instance;
		}

		function form($instance){
			$defaults = array(
				'title' => '',
				'type' => 'youtube',
				'url' => '',
				'height' => 250
			);
			$instance = wp_parse_args((array)$instance, $defaults);
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
					<?php esc_html_e('Title:', 'epcl_framework'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('type'); ?>"><?php esc_html_e('Type:', 'epcl_framework') ?> </label>
				<select id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>">
					<option <?php if($instance['type'] == 'youtube') echo 'selected="selected"'; ?> value="youtube">Youtube</option>
					<option <?php if($instance['type'] == 'vimeo') echo 'selected="selected"'; ?> value="vimeo">Vimeo</option>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('url'); ?>"><?php esc_html_e( 'URL:', 'epcl_framework'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('url'); ?>" name="<?php echo $this->get_field_name('url'); ?>" type="text" value="<?php echo $instance['url']; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('height'); ?>"><?php esc_html_e( 'Height:', 'epcl_framework'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo $instance['height']; ?>" />
			</p>
			<?php
		}

	}
}

function epcl_register_video() {
	register_widget('epcl_video');
}

add_action('widgets_init', 'epcl_register_video');
