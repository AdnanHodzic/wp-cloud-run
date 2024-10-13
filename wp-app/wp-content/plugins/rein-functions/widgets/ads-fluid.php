<?php
if ( ! class_exists( 'epcl_ads_fluid' ) ) {

	class epcl_ads_fluid extends WP_Widget{

		function __construct(){
			$widget_ops = array('description' => esc_html__('Display a fluid ads. Note: the max ads width is 300px and height is unlimited.', 'epcl_framework'));
			parent::__construct( false, esc_html__('(EP) Fluid Ads', 'epcl_framework'), $widget_ops);
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
				if($instance['ads']){
					echo '<div class="epcl-banner-wrapper">';
						echo '<div class="epcl-banner align'.$instance['align'].'" style="max-width: '.$instance['width'].'px;">'.$instance['ads'].'</div>';
					echo '</div>';
				}
			echo $after_widget;
		}

		function update($new_instance, $old_instance){
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['width'] = (int) $new_instance['width'];
			$instance['align'] = $new_instance['align'];
			$instance['ads'] = $new_instance['ads'];
			return $instance;
		}

		function form($instance){
			$defaults = array(
				'title' => 'Advertising',
				'width' => 250,
				'align' => 'center',
				'ads' => ''
			);
			$instance = wp_parse_args((array)$instance, $defaults);
			$width = isset($instance['width']) ? absint($instance['width']) : 250;
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
					<?php esc_html_e('Title:', 'epcl_framework'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('width'); ?>"><?php esc_html_e( 'Width of advertise to show:', 'epcl_framework'); ?></label>
				<input id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width; ?>" size="3" />
				<br />
				<small><?php esc_html_e( 'Only integer values are allowed:', 'epcl_framework'); ?></small>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('align'); ?>"><?php esc_html_e('Align:', 'epcl_framework') ?> </label>
				<select id="<?php echo $this->get_field_id('align'); ?>" name="<?php echo $this->get_field_name('align'); ?>">
					<option <?php if ($instance['align'] == 'left') echo 'selected="selected"'; ?> value="left">Left</option>
					<option <?php if ($instance['align'] == 'center') echo 'selected="selected"'; ?> value="center">Center</option>
					<option <?php if ($instance['align'] == 'right') echo 'selected="selected"'; ?> value="right">Right</option>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('ads'); ?>"><?php esc_html_e('Ads Code:', 'epcl_framework') ?> </label>
				 <textarea class="widefat" rows="10" id="<?php echo $this->get_field_id('ads'); ?>" name="<?php echo $this->get_field_name('ads'); ?>"><?php echo $instance['ads']; ?></textarea>
			</p>
			<?php
		}

	}

	function epcl_register_ads_fluid() {
		register_widget('epcl_ads_fluid');
	}

	add_action('widgets_init', 'epcl_register_ads_fluid');

}
