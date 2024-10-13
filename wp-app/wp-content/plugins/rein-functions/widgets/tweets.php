<?php
if ( ! class_exists( 'EPCL_tweets' ) ) {
	class epcl_tweets extends WP_Widget{

		function __construct(){
			$widget_ops = array('description' => esc_html__('Display recent tweets.', 'epcl_framework'));
			parent::__construct( false, esc_html__('(EP) Recent Tweets', 'epcl_framework'), $widget_ops);
		}

		function widget($args, $instance){
            // WP 5.9 Patch: always disable widget preview in the backend
            if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
                return false;
            }
			extract($args);
            $title = apply_filters('widget_title', $instance['title']);
            $exclude_replies = (isset($instance[ 'exclude_replies' ]) && $instance[ 'exclude_replies' ] ) ? true : false;
			echo $before_widget;
				if($title) echo $before_title.$title.$after_title;
				if(!$instance['number']) $instance['number'] = 3;
				require_once(EPCL_PLUGIN_PATH.'/twitter_api/Creare_Twitter.php');

				$twitter = new Creare_Twitter();
				$twitter->screen_name = $instance['twitter_id'];
				$twitter->not = $instance['number'];

				$twitter->consumerkey = "9fczReaawqJPjeamYIM58NHmY";
				$twitter->consumersecret = "jBy7zgFwTQ7afUiXNMN38BuDxsVRpGimW2zQ0IBbbBScr5v4rr";
				$twitter->accesstoken = "1318265592433004544-SEkdcDzpze6T9gCSLQs0etS6SQUBq1";
				$twitter->accesstokensecret = "rukxkeTy9n0b7J3QaVpxt3WRVkfUJ2E9KZ2XBFDrE8F2o";
				$tweets = $twitter->getLatestTweets( $exclude_replies );

				if( !empty($tweets) && $tweets != "Tweets can't be loaded" ){
                    $i = 0;
                    foreach($tweets as $t){
                        if( $instance['number'] == $i ){
                            break;
                        }
                        echo '<p class="gradient-effect"><i class="fa fa-twitter"></i>'.$t['tweet'].'<br><small>'.$t['time'].'</small></p>';   
                        $i++;
                    }
                }else{
                    echo "Tweets cant be loaded.";
                }

			echo $after_widget;
		}

		function update($new_instance, $old_instance){
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['number'] = (int) $new_instance['number'];
            $instance['twitter_id'] = $new_instance['twitter_id'];
            $instance[ 'exclude_replies' ] = $new_instance[ 'exclude_replies' ];
			return $instance;
		}

		function form($instance){
			$defaults = array(
				'title' => 'Recent Tweets',
				'number' => 3,
                'twitter_id' => '',
                'exclude_replies' => ''
			);
			$instance = wp_parse_args((array)$instance, $defaults);
            $number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 3;
            // $exclude_replies = $instance[ 'exclude_replies' ] ? true : false;
            // $exclude_replies = $instance[ 'exclude_replies' ]  ? 'on' : '';
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
					<?php esc_html_e('Title:', 'epcl_framework'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('number'); ?>"><?php esc_html_e( 'Number of tweets to show:', 'epcl_framework'); ?></label>
				<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('twitter_id'); ?>">
					<?php esc_html_e('Twitter ID: (without @)', 'epcl_framework'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('twitter_id'); ?>" name="<?php echo $this->get_field_name('twitter_id'); ?>" type="text" value="<?php echo $instance['twitter_id']; ?>" />
				</label>
			</p>
            <p>
                <input class="checkbox" type="checkbox" <?php checked( 'on', $instance['exclude_replies'] ); ?> id="<?php echo $this->get_field_id( 'exclude_replies' ); ?>" name="<?php echo $this->get_field_name( 'exclude_replies' ); ?>" />
                <label for="<?php echo $this->get_field_id( 'exclude_replies' ); ?>"> <?php esc_html_e( 'Exclude Replies', 'epcl_framework'); ?></label>
            </p>
			<?php
		}

	}
}

function epcl_register_tweets() {
	register_widget('epcl_tweets');
}

add_action('widgets_init', 'epcl_register_tweets');
