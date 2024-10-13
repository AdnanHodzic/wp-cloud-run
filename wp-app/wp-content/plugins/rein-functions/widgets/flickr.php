<?php
if ( ! class_exists( 'epcl_flickr' ) ) {
	class epcl_flickr extends WP_Widget{

		function __construct(){
			$widget_ops = array('description' => esc_html__('Display recent photos from Flickr.', 'epcl_framework'));
			parent::__construct( false, esc_html__('(EP) Flickr Gallery', 'epcl_framework'), $widget_ops);
		}

		function widget($args, $instance){
            // WP 5.9 Patch: always disable widget preview in the backend
            if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
                return false;
            }
			extract($args);
			$title = apply_filters('widget_title', $instance['title']);
			echo $before_widget;
				if($title) echo $before_title.$title.$after_title;
				if(!$instance['number']) $instance['number'] = 6;
				if(!$instance['flickr_id']) esc_html_e('You must enter a valid Flickr id', 'epcl_framework');
				if($instance['flickr_id']):
	?>
					<div class="epcl-flickr-gallery" id="epcl-flickr-<?php echo $this->id; ?>" data-limit="<?php echo $instance['number']; ?>" data-flickr-id="<?php echo $instance['flickr_id']; ?>">
						<div class="loading"><?php esc_html_e('Loading...', 'rein'); ?></div>
						<ul class="grid-parent np-mobile"></ul>
					</div>
	<?php
				endif;
			echo $after_widget;
		}

		function update($new_instance, $old_instance){
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['number'] = (int) $new_instance['number'];
			$instance['flickr_id'] = $new_instance['flickr_id'];
			return $instance;
		}

		function form($instance){
			$defaults = array(
				'title' => 'Flickr Gallery',
				'number' => 6,
				'flickr_id' => ''
			);
			$instance = wp_parse_args((array)$instance, $defaults);
			$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 6;
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
					<?php esc_html_e('Title:', 'epcl_framework'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('number'); ?>"><?php esc_html_e( 'Number of photos to show:', 'epcl_framework'); ?></label>
				<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('flickr_id'); ?>">
					<?php esc_html_e('Flickr id:', 'epcl_framework'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('flickr_id'); ?>" name="<?php echo $this->get_field_name('flickr_id'); ?>" type="text" value="<?php echo $instance['flickr_id']; ?>" />
					<br /><span><?php esc_html_e('You can find your Flickr id on:', 'epcl_framework'); ?> <a href="http://idgettr.com/" target="_blank">http://idgettr.com/</a></span>
				</label>
			</p>
			<?php
		}

	}

	function epcl_register_flickr() {
		register_widget('epcl_flickr');
	}

	add_action('widgets_init', 'epcl_register_flickr');
}
