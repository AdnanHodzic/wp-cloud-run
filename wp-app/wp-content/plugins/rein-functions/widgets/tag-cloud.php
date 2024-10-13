<?php
if ( ! class_exists( 'epcl_tag_cloud' ) ) {
	class epcl_tag_cloud extends WP_Widget{

		function __construct(){
			$widget_ops = array( 'description' => esc_html__('Display tags or categories with limit and special filters.', 'epcl_framework') );
			parent::__construct( false, esc_html__('(EP) Tag Cloud', 'epcl_framework'), $widget_ops);
		}

		function widget($args, $instance){
            // WP 5.9 Patch: always disable widget preview in the backend
            if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
                return false;
            }
		    global $epcl_theme;
			extract($args);
			$title = apply_filters('widget_title', $instance['title']); 


			echo $before_widget;
				if($title) echo $before_title.$title.$after_title;
				if(!$instance['limit']) $instance['limit'] = 15;
                if(!$instance['orderby']) $instance['orderby'] = 'name';
                if(!$instance['order']) $instance['order'] = 'ASC';
                if(!$instance['taxonomy']) $instance['taxonomy'] = 'category';

                $categories = get_terms(array(
                    'taxonomy' => $instance['taxonomy'],
                    'orderby' => $instance['orderby'],
                    'order' => $instance['order'],
                    'number' => $instance['limit'],
                ));

                
                $html = '<div class="tagcloud '.$class.'">';
                $i = 0;
                foreach($categories as $c){
                    $count = '';
                    if( $instance['count'] ){
                        $count = ' ('.$c->count.') ';
                    }
                    $html .= '<a href="'.get_category_link($c).'" class="tag-link-'.$c->term_id.'">'.$c->name.$count.'</a>';
                    $i++;
                }
                $html .= '</div>';

                echo $html;

			echo $after_widget;
		}

		function update($new_instance, $old_instance){
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['limit'] = (int) $new_instance['limit'];
            $instance['orderby'] = $new_instance['orderby'];
            $instance['order'] = $new_instance['order'];
            $instance['count'] = $new_instance['count'];
            $instance['taxonomy'] = $new_instance['taxonomy'];
			return $instance;
		}

		function form($instance){
			$defaults = array(
				'title' => 'Tag Cloud',
				'limit' => 10,
                'orderby' => 'name',
                'order' => 'ASC',
                'count' => '',
                'taxonomy' => 'category'
			);
			$instance = wp_parse_args((array)$instance, $defaults);
			$limit = isset( $instance['limit'] ) ? absint( $instance['limit'] ) : 10;
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
					<?php esc_html_e('Title:', 'epcl_framework'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('limit'); ?>"><?php esc_html_e( 'Max number of elements to display:', 'epcl_framework'); ?></label>
				<input id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="text" value="<?php echo $limit; ?>" size="3" />
            </p>
            <p>
				<label for="<?php echo $this->get_field_id('taxonomy'); ?>"><?php esc_html_e('Taxonomy (mode):', 'epcl_framework') ?> </label>
				<select id="<?php echo $this->get_field_id('taxonomy'); ?>" name="<?php echo $this->get_field_name('taxonomy'); ?>">
					<option <?php if ($instance['taxonomy'] == 'category') echo 'selected="selected"'; ?> value="category"><?php esc_html_e('Post Category', 'epcl_framework'); ?></option>
                    <option <?php if ($instance['taxonomy'] == 'post_tag') echo 'selected="selected"'; ?> value="post_tag"><?php esc_html_e('Post Tags', 'epcl_framework'); ?></option>
				</select>
            </p>
            <p>
				<label for="<?php echo $this->get_field_id('orderby'); ?>"><?php esc_html_e('Order By:', 'epcl_framework') ?> </label>
				<select id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name('orderby'); ?>">
					<option <?php if ($instance['orderby'] == 'name') echo 'selected="selected"'; ?> value="name"><?php esc_html_e('Name', 'epcl_framework'); ?></option>
                    <option <?php if ($instance['orderby'] == 'count') echo 'selected="selected"'; ?> value="count"><?php esc_html_e('Count', 'epcl_framework'); ?></option>
				</select>
            </p>
            <p>
				<label for="<?php echo $this->get_field_id('order'); ?>"><?php esc_html_e('Order:', 'epcl_framework') ?> </label>
				<select id="<?php echo $this->get_field_id('order'); ?>" name="<?php echo $this->get_field_name('order'); ?>">
					<option <?php if ($instance['order'] == 'ASC') echo 'selected="selected"'; ?> value="ASC"><?php esc_html_e('Ascendant', 'epcl_framework'); ?></option>
                    <option <?php if ($instance['order'] == 'DESC') echo 'selected="selected"'; ?> value="DESC"><?php esc_html_e('Descendant', 'epcl_framework'); ?></option>
				</select>
            </p>
            <p>
                <input id="<?php echo $this->get_field_id('count'); ?>" type="checkbox" class="checkbox" name="<?php echo $this->get_field_name('count'); ?>" <?php if ($instance['count'] ) echo 'checked="checked"'; ?>>
                <label for="<?php echo $this->get_field_id('count'); ?>"><?php esc_html_e('Show tag counts', 'epcl_framework'); ?></label>
            </p>
			<?php
		}

	}

	function epcl_register_tag_cloud() {
		register_widget('epcl_tag_cloud');
	}

	add_action('widgets_init', 'epcl_register_tag_cloud');

}
