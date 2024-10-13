<?php
if ( ! class_exists( 'epcl_posts_thumbs' ) ) {
	class epcl_posts_thumbs extends WP_Widget{

		function __construct(){
			$widget_ops = array('description' => esc_html__('Display Random or Recent posts with a small image.', 'epcl_framework'));
			parent::__construct( false, esc_html__('(EP) Recent Posts with image', 'epcl_framework'), $widget_ops);
		}

		function widget($args, $instance){
            // WP 5.9 Patch: always disable widget preview in the backend
            if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
                return false;
            }
		    global $epcl_theme;
			extract($args);
			$prefix = EPCL_THEMEPREFIX.'_';
			$title = apply_filters('widget_title', $instance['title']);
			$args = array(
				'posts_per_page' => $instance['number'],
				'post_type' => 'post',
				'order' => 'DESC',
                'orderby' => $instance['orderby'],
                'ignore_sticky_posts' => true
            );

            if( $instance['orderby'] == 'views' ){
                $args = array(
                    'posts_per_page' => $instance['number'],
                    'post_type' => 'post',
                    'order' => 'DESC',
                    'orderby' => 'meta_value_num',
                    'meta_key' => 'views_counter',
                    'ignore_sticky_posts' => true
                );
            }

            if( isset($instance['orderdate']) && $instance['orderdate'] != 'alltime' ){
                $year = date('Y');
                $month = absint( date('m') );
                $week = absint( date('W') );

                $args['year'] = $year;

                if( $instance['orderdate'] == 'pastmonth' ){
                    $args['monthnum'] = $month - 1;
                }
                if( $instance['orderdate'] == 'pastweek' ){
                    $args['w'] = $week - 1;
                }                
            }

            if( is_single() ){
                $args['post__not_in'] = array( get_the_ID() );
            }

			$query = new WP_Query($args);
			if( !$query->have_posts() ) return;
			echo $before_widget;
				if($title) echo $before_title.$title.$after_title;
				if(!$instance['number']) $instance['number'] = 4;

				if($query->have_posts()):
					$c = 0;

					while($query->have_posts()): $query->the_post(); ?>
						<?php
						$class = 'item';
						$post_id = get_the_ID();
						$thumb_size = 'epcl_widget_thumb';
						?>
						<?php if( !has_post_thumbnail() ) $class .= ' no-thumb'; ?>
						<article <?php post_class($class); ?>>

							<?php if( has_post_thumbnail() ): ?>
								<?php
								$thumb_id = get_post_thumbnail_id($post_id);
                                $thumb_type = get_post_mime_type($thumb_id);
                                $image_alt = get_post_meta( $thumb_id, '_wp_attachment_image_alt', true);
                                if( !$image_alt ){
                                    $image_alt = get_the_title($post_id);
                                }
								if($thumb_type == 'image/gif'){
									$thumb_size = '';
								}
								?>
								<a href="<?php the_permalink(); ?>" class="thumb hover-effect" aria-label="<?php the_title(); ?>">
									<?php if( !empty($epcl_theme) && $epcl_theme['enable_lazyload'] == '1' ): ?>
                                        <span class="fullimage cover lazy" role="img" aria-label="<?php echo esc_attr($image_alt); ?>" data-src="<?php the_post_thumbnail_url($thumb_size); ?>"></span>
									<?php else: ?>
                                        <span class="fullimage cover" role="img" aria-label="<?php echo esc_attr($image_alt); ?>" style="background: url('<?php the_post_thumbnail_url($thumb_size); ?>');"></span>
									<?php endif; ?>
								</a>
							<?php endif; ?>

							<div class="info gradient-effect">
                                
                                <h4 class="title usmall"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>		
                                <time datetime="<?php the_time('Y-m-d'); ?>"><?php the_time( get_option('date_format') ); ?></time>						
							</div>

							<div class="clear"></div>
						</article>
					<?php
					endwhile;
					wp_reset_postdata();
				endif;
			echo $after_widget;
		}

		function update($new_instance, $old_instance){
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['number'] = (int) $new_instance['number'];
            $instance['orderby'] = $new_instance['orderby'];
            $instance['orderdate'] = $new_instance['orderdate'];
			return $instance;
		}

		function form($instance){
			$defaults = array(
				'title' => 'Recent posts',
				'number' => 4,
                'orderby' => 'date',
                'orderdate' => 'alltime'
			);
			$instance = wp_parse_args((array)$instance, $defaults);
			$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 4;
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
					<?php esc_html_e('Title:', 'epcl_framework'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('number'); ?>"><?php esc_html_e( 'Number of posts to show:', 'epcl_framework'); ?></label>
				<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('orderby'); ?>"><?php esc_html_e('Mode:', 'epcl_framework') ?> </label>
				<select id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name('orderby'); ?>">
					<option <?php if ($instance['orderby'] == 'date') echo 'selected="selected"'; ?> value="date"><?php esc_html_e('Recent Posts', 'epcl_framework'); ?></option>
                    <option <?php if ($instance['orderby'] == 'rand') echo 'selected="selected"'; ?> value="rand"><?php esc_html_e('Random Posts', 'epcl_framework'); ?></option>
                    <?php if( function_exists('get_field') ): // By views ?>
                        <option <?php if ($instance['orderby'] == 'views') echo 'selected="selected"'; ?> value="views"><?php esc_html_e('Post views', 'epcl_framework'); ?></option>
                    <?php endif; ?>
				</select>
            </p>
            <p>
				<label for="<?php echo $this->get_field_id('orderdate'); ?>"><?php esc_html_e('Date:', 'epcl_framework') ?> </label>
				<select id="<?php echo $this->get_field_id('orderdate'); ?>" name="<?php echo $this->get_field_name('orderdate'); ?>">
					<option <?php if ($instance['orderdate'] == 'alltime') echo 'selected="selected"'; ?> value="alltime"><?php esc_html_e('All Time', 'epcl_framework'); ?></option>
                    <option <?php if ($instance['orderdate'] == 'pastmonth') echo 'selected="selected"'; ?> value="pastmonth"><?php esc_html_e('Past Month', 'epcl_framework'); ?></option>
                    <option <?php if ($instance['orderdate'] == 'pastweek') echo 'selected="selected"'; ?> value="pastweek"><?php esc_html_e('Past Week', 'epcl_framework'); ?></option>
				</select>
			</p>
			<?php
		}

	}

	function epcl_register_posts_thumbs() {
		register_widget('epcl_posts_thumbs');
	}

	add_action('widgets_init', 'epcl_register_posts_thumbs');
}
