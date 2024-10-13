<?php
if ( ! class_exists( 'epcl_about' ) ) {
	class epcl_about extends WP_Widget{

		function __construct(){
			$widget_ops = array( 'description' => esc_html__('Display about author section.', 'epcl_framework') );
			parent::__construct( false, esc_html__('(EP) About me', 'epcl_framework'), $widget_ops);
		}

		function widget($args, $instance){
            // WP 5.9 Patch: always disable widget preview in the backend
            if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
                return false;
            }
		    global $epcl_theme;
			extract($args);
            $title = apply_filters('widget_title', $instance['title']);
            $author_id =  $instance['author'];
			echo $before_widget;
				if($title) echo $before_title.$title.$after_title;
                if( $author_id ):
                    
                    $optimized_avatar = get_the_author_meta('avatar', $author_id);
                    if( $optimized_avatar ){
                        $author_avatar = wp_get_attachment_url( $optimized_avatar );
                    }else{
                        $author_avatar = get_avatar_url( get_the_author_meta('email', $author_id), array( 'size' => 192 ));
                    }           
                    $author_name = get_the_author_meta('display_name', $author_id);
                    $author_url = get_author_posts_url($author_id);
                    $website = get_the_author_meta('user_url', $author_id);
                    $facebook = get_the_author_meta('facebook', $author_id);
                    $twitter = get_the_author_meta('twitter', $author_id);                    

                ?>
                        <?php if($author_avatar): ?>
                            <div class="avatar">                            
                                <a href="<?php echo esc_url( $author_url ); ?>" class="translate-effect thumb"><span class="fullimage cover lazy"  data-src="<?php echo esc_url( $author_avatar ); ?>"></span></a>                            
                            </div>
                        <?php endif; ?>
                        <div class="info">
                            <h4 class="title small author-name gradient-effect no-margin"><a href="<?php echo esc_url( $author_url ); ?>"><?php echo esc_html( $author_name ); ?></a></h4>
                            <?php if( isset( $instance['position'] ) ): ?>
                                <p class="founder"><?php echo esc_html( $instance['position'] ); ?></p>
                            <?php endif; ?>                          
                            <div class="social">
                            <?php if($website): ?>
                                <a href="<?php echo esc_url($website); ?>" class="website tooltip" title="<?php esc_attr_e('Website', 'rein'); ?>: <?php echo esc_url($website); ?>" target="_blank" rel="nofollow noopener"><i class="fa fa-globe"></i></a>
                            <?php endif; ?>
                            <?php if($twitter): ?>
                                <a href="<?php echo esc_url($twitter); ?>" class="twitter tooltip" title="<?php esc_attr_e('Follow me on Twitter', 'rein'); ?>" target="_blank" rel="nofollow noopener"><i class="fa fa-twitter"></i></a>
                            <?php endif; ?>
                            <?php if($facebook): ?>
                                <a href="<?php echo esc_url($facebook); ?>" class="facebook tooltip" title="<?php esc_attr_e('Follow me on Facebook', 'rein'); ?>" target="_blank" rel="nofollow noopener"><i class="fa fa-facebook"></i></a>
                            <?php endif; ?>
                            </div>
                        </div>
                        <div class="clear"></div>
                        <p><?php the_author_meta('description', $author_id); ?></p>
                <?php
                endif;
                
			echo $after_widget;
		}

		function update($new_instance, $old_instance){
			$instance = $old_instance;
            $instance['title'] = strip_tags($new_instance['title']);
            $instance['position'] = strip_tags($new_instance['position']);
			$instance['author'] = absint( $new_instance['author'] );
			return $instance;
		}

		function form($instance){
			$defaults = array(
                'title' => 'About me',
                'position' => 'Founder &amp; Editor',
				'author' => ''
			);
            $instance = wp_parse_args((array)$instance, $defaults);
            $users = get_users();
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
					<?php esc_html_e('Title:', 'epcl_framework'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
				</label>
            </p>
            <p>
				<label for="<?php echo $this->get_field_id('position'); ?>">
					<?php esc_html_e('Author Position:', 'epcl_framework'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('position'); ?>" name="<?php echo $this->get_field_name('position'); ?>" type="text" value="<?php echo $instance['position']; ?>" />
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('author'); ?>"><?php esc_html_e('Author:', 'epcl_framework') ?> </label>
                <?php wp_dropdown_users( array('name' => $this->get_field_name( 'author' ), 'selected' => $instance['author'] ) ); ?>
                <br><small><?php esc_html_e('Select an author to display all his information. Remember to fill your profile (website, Twitter and Facebook.)', 'epcl_framework') ?></small>
			</p>
			<?php
		}

	}

	function epcl_register_about() {
		register_widget('epcl_about');
	}

	add_action('widgets_init', 'epcl_register_about');

}
