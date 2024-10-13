<?php
if ( ! class_exists( 'epcl_ads_125' ) ) {

	class epcl_ads_125 extends WP_Widget{

		function __construct(){
			$widget_ops = array('description' => esc_html__('Display 125x125 grid ads.', 'epcl_framework'));
			parent::__construct( false, esc_html__('(EP) 125x125 Ads', 'epcl_framework'), $widget_ops);
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
				echo '<div class="epcl-banner-wrapper">';
                    if($instance['ads_1'])
                        echo '<div class="epcl-banner-1 epcl-banner">'.$instance['ads_1'].'</div>';
                    if($instance['ads_2'])
                        echo '<div class="epcl-banner-2 epcl-banner">'.$instance['ads_2'].'</div>';
                    if($instance['ads_3'])
                        echo '<div class="epcl-banner-3 epcl-banner">'.$instance['ads_3'].'</div>';
                    if($instance['ads_4'])
                        echo '<div class="epcl-banner-4 epcl-banner">'.$instance['ads_4'].'</div>';
				echo '</div>';

			echo $after_widget;
		}

		function update($new_instance, $old_instance){
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['ads_1'] = $new_instance['ads_1'];
			$instance['ads_2'] = $new_instance['ads_2'];
			$instance['ads_3'] = $new_instance['ads_3'];
			$instance['ads_4'] = $new_instance['ads_4'];
			return $instance;
		}

		function form($instance){
			$defaults = array(
				'title' => 'Advertise',
				'ads_1' => '',
				'ads_2' => '',
				'ads_3' => '',
				'ads_4' => ''
			);
			$instance = wp_parse_args((array)$instance, $defaults);
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
					<?php esc_html_e('Title:', 'epcl_framework'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
				</label>
			</p>
			<p><small><?php esc_html_e('Copy and paste your ads code:', 'epcl_framework'); ?></small></p>
			<p>
				<label for="<?php echo $this->get_field_id('ads_1'); ?>"><?php esc_html_e('Ads Block n&ordm; 1:', 'epcl_framework') ?> </label>
				 <textarea class="widefat" rows="7" id="<?php echo $this->get_field_id('ads_1'); ?>" name="<?php echo $this->get_field_name('ads_1'); ?>"><?php echo $instance['ads_1']; ?></textarea>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('ads_2'); ?>"><?php esc_html_e('Ads Block n&ordm; 2:', 'epcl_framework') ?> </label>
				 <textarea class="widefat" rows="7" id="<?php echo $this->get_field_id('ads_1'); ?>" name="<?php echo $this->get_field_name('ads_2'); ?>"><?php echo $instance['ads_2']; ?></textarea>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('ads_3'); ?>"><?php esc_html_e('Ads Block n&ordm; 3:', 'epcl_framework') ?> </label>
				 <textarea class="widefat" rows="7" id="<?php echo $this->get_field_id('ads_3'); ?>" name="<?php echo $this->get_field_name('ads_3'); ?>"><?php echo $instance['ads_3']; ?></textarea>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('ads_4'); ?>"><?php esc_html_e('Ads Block n&ordm; 4:', 'epcl_framework') ?> </label>
				 <textarea class="widefat" rows="7" id="<?php echo $this->get_field_id('ads_4'); ?>" name="<?php echo $this->get_field_name('ads_4'); ?>"><?php echo $instance['ads_4']; ?></textarea>
			</p>
			<?php
		}

	}

}

function epcl_register_ads_125() {
	register_widget('epcl_ads_125');
}

add_action('widgets_init', 'epcl_register_ads_125');
