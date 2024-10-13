<?php
/**
 * Plugin Name: Rein Theme Functions
 * Plugin URI: https://estudiopatagon.com/projects/rein-for-wordpress/
 * Description: This plugin enables core functions for <a href="https://estudiopatagon.com/projects/rein-for-wordpress/">Rein - Lightweight Dark Theme for Wordpress</a> by <a href="https://estudiopatagon.com/">EstudioPatagon</a>.
 * Version: 2.5.0
 * Author: Estudio Patagon
 * Author URI: https://estudiopatagon.com/
 * License: Themeforest Licence
 * License URI: http://themeforest.net/licenses/standard
 * Text Domain: rein
 * Domain Path: /languages
 *
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' ); // No direct access allowed

if( get_template() != 'rein' ){
    return;
}

 if ( !class_exists('epcl_rein_theme_functions') ) {

    define('EPCL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
    define('EPCL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

    /* Base paths */
    
    if( !defined('EPCL_THEMEPREFIX') ) define('EPCL_THEMEPREFIX', 'epcl');
    if( !defined('EPCL_FRAMEWORK_VAR') ) define('EPCL_FRAMEWORK_VAR', 'epcl_theme');
    if( !defined('EPCL_THEMESLUG') ) define('EPCL_THEMESLUG', 'rein'); // Do not change
    if( !defined('EPCL_ITEM_ID') ) define('EPCL_ITEM_ID', '24487827'); // Do not change

    class epcl_rein_theme_functions {

        public function __construct() {

            /* Check if Rein Theme is activated */

            if( get_template() != 'rein' ){
                return;
            }

            /* Languages */

            load_theme_textdomain('epcl_framework', EPCL_PLUGIN_PATH.'/languages');
            
            /* Enqueue Scripts */

            require_once(EPCL_PLUGIN_PATH.'/enqueue-scripts.php');

            /* Dashboard section */

            if( is_admin() && isset($_GET['page']) && $_GET['page'] == 'estudiopatagon-license' && isset($_GET['debug'])){
                define("LB_API_DEBUG", true); 
            } 
            require_once(EPCL_PLUGIN_PATH.'/dashboard/includes/lb_helper.php');
            require_once(EPCL_PLUGIN_PATH.'/dashboard/init.php');
            register_activation_hook( __FILE__, array( $this, 'plugin_activated' ));

            /* Admin: Theme Options */

            add_action( 'plugins_loaded', array( $this, 'init_custom_fields') );

            require_once(EPCL_PLUGIN_PATH.'/render-ads.php');          
            require_once(EPCL_PLUGIN_PATH.'/widgets/init.php');
            require_once(EPCL_PLUGIN_PATH.'/shortcodes/init.php');
            require_once(EPCL_PLUGIN_PATH.'/custom-styles.php');  
            require_once(EPCL_PLUGIN_PATH.'/social-buttons.php');               
                
            /* Front-End: Custom Gallery */

            remove_shortcode('gallery', 'gallery_shortcode');
            add_shortcode('gallery', array( $this, 'gallery_shortcode') );

            add_action( 'init', array( $this, 'enable_lazyload') );
            add_action( 'init', array( $this, 'enable_acf_menu') );
            add_action( 'init', array( $this, 'set_notice_cookie') );   

            /* Admin: Add a thumb on post list */

			add_filter('manage_posts_columns', array( $this, 'posts_columns'), 5);
			add_action('manage_posts_custom_column', array( $this, 'posts_custom_columns'), 5, 2);

            /* Fixes */

            add_filter('the_content', array( $this, 'shortcode_empty_paragraph_fix') );    
            add_filter('acf_the_content', array( $this, 'shortcode_empty_paragraph_fix'), 11 ); 
            
            /* Schema */

            add_action( 'wp_head', array( $this, 'insert_fb_in_head'), 1 );
            add_filter('language_attributes', array( $this, 'add_opengraph_doctype') );

        }

        public function init_custom_fields(){
	        require_once(EPCL_PLUGIN_PATH.'/metaboxes/init.php');
	        require_once(EPCL_PLUGIN_PATH.'/admin/config.php'); // Initializes the framework
	        require_once(EPCL_PLUGIN_PATH.'/admin/custom-functions.php'); // Just some modifications to the panel
        }

        public function plugin_activated( ) {
            $lbapi = new LicenseBoxAPI();
            $license_file = get_option( EPCL_THEMESLUG . '_license_key_file');
            if( !$lbapi->check_local_license_exist() && $license_file !== '' ){
                $lbapi->create_license( false, false, $license_file );  
            }            
        }

        public function add_opengraph_doctype( $output ) {
            global $epcl_theme;
            if( isset($epcl_theme['enable_open_graph']) && $epcl_theme['enable_open_graph'] !== '0' ){
                return $output . ' prefix="og: http://ogp.me/ns#"';
            }
            return $output;
        }    

        public function insert_fb_in_head() {
            global $post, $epcl_theme, $wp;

            if( isset($epcl_theme['facebook_app_id']) && $epcl_theme['facebook_app_id'] != ''){
                echo '<meta property="fb:app_id" content="' . $epcl_theme['facebook_app_id'] . '" />'."\n";
            }      

            if( isset($epcl_theme['enable_open_graph']) && $epcl_theme['enable_open_graph'] !== '0' ){
            
                $obj = get_queried_object();

                $image = '';

                $locale = get_locale();
                $site_name = get_bloginfo('name');
                $title = get_the_title(). ' - '. $site_name;
                $content = get_the_excerpt();
                $permalink = home_url( $wp->request );
                $canonical_url = wp_get_canonical_url();
                $type = 'article';

                // Home
                if( is_home() || is_front_page() ){
                    $title = $site_name;
                    $content = get_bloginfo('description');
                    $type = 'website';
                    $permalink = site_url();
                    $canonical_url = site_url();
                    if( isset($epcl_theme['logo_image']) && $epcl_theme['logo_image']['url'] != '' ) { // No image
                        $image = $epcl_theme['logo_image']['url'];
                    }
                }                
                // Post or pages
                if( is_singular() ){
                    if(!has_post_thumbnail( $post->ID ) && isset($epcl_theme['logo_image']) && $epcl_theme['logo_image']['url'] != '' ) { // No image
                        $image = $epcl_theme['logo_image']['url'];
                    }else{
                        $thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );
                        if( !empty($thumbnail_src) ){
                            $image = $thumbnail_src[0];  
                        }                     
                    }                    
                }
                // Arcvhives and Categories
                if( is_archive() ){
                    $title = get_the_archive_title(). ' - '. $site_name;
                    $type = 'object';
                    $image = '';
                    if( is_category() ){
                        $content = term_description();
                        if( function_exists('get_fields') && !empty($obj) ){
                            $fields = get_fields($obj);
                            if( isset( $fields['archives_image']['sizes'] ) ) { // Background category image
                                $image = $fields['archives_image']['sizes']['large'];
                            }
                        }
                    }
                }
                
                echo '<!-- start: Estudio Patagon Meta Tags -->' ."\n";
                //echo '<link rel="canonical" href="'.$canonical_url.'" />'."\n";
                if( $content ) echo '<meta property="description" content="' . esc_attr($content). '"/>'."\n";
                echo '<meta property="og:locale" content="' . esc_attr($locale) . '" />'."\n";
                echo '<meta property="og:title" content="' . esc_attr( $title) . '"/>'."\n";
                echo '<meta property="og:description" content="' . esc_attr($content). '"/>'."\n";
                if( isset($image) && $image != '' ) echo '<meta property="og:image" content="' . esc_attr( $image ) . '"/>'."\n";
                echo '<meta property="og:type" content="'.esc_attr($type).'"/>'."\n";
                echo '<meta property="og:url" content="' . esc_url( user_trailingslashit( $permalink ) ) . '"/>'."\n";
                echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '"/>'."\n";

                if( is_single() ){
                    echo '<meta property="article:published_time" content="'.get_the_date('Y-m-d\TH:i:sP').'" />'."\n";
                    echo '<meta property="article:modified_time" content="'.get_the_modified_date('Y-m-d\TH:i:sP').'" />'."\n";
                }

                // Twitter
                echo '<meta name="twitter:card" content="summary_large_image" />'."\n";
                echo '<meta name="twitter:description" content="'. esc_attr( $content ).'" />'."\n";
                echo '<meta name="twitter:title" content="'. esc_attr( $title ) .'" />'."\n";
                if( isset($image) && $image != '' ) echo '<meta name="twitter:image" content="'. esc_url($image) .'" />'."\n";

                echo '<!-- end: Estudio Patagon Meta Tags -->'."\n";

            }
        }

        public function set_notice_cookie(){
            global $wp;
            if( isset($_GET['epcl-action']) && $_GET['epcl-action'] == 'remove-notice') {
                setcookie( 'epcl_show_notice', 'false', time() + ( DAY_IN_SECONDS * 5 ), COOKIEPATH, COOKIE_DOMAIN );
                wp_redirect( home_url( $wp->request ) );
                exit();
            }
        }

        /* Improved Wordpress Gallery: new lightbox and new grid layout */

		public function gallery_shortcode($atts){
		    global $post;
            if( !empty($atts['ids']) ){
                if( empty( $atts['orderby']) ){
                    $atts['orderby'] = 'post__in';
                }       

                $atts['include'] = $atts['ids'];
			}
			extract(shortcode_atts(array(
			   'orderby' => 'menu_order ASC, ID ASC',
				'include' => '',
				'id' => $post->ID,
				'columns' => 3,
			), $atts));

			$args = array(
				'post_type' => 'attachment',
				'post_status' => 'inherit',
				'post_mime_type' => 'image',
				'orderby' => $orderby,
				'suppress_filters' => false
			);

			if( !empty($include) ) $args['include'] = $include;
			else{ $args['post_parent'] = $id; $args['numberposts'] = -1; }

			$images = get_posts($args);
			$class = $wrapper_class = $output = '';
			switch($columns){
				case 2: $class = 'grid-50 tablet-grid-33 mobile-grid-50'; break;
				case 3: $class = 'grid-33 tablet-grid-33 mobile-grid-50'; break;
				case 4: $class = 'grid-25 tablet-grid-33 mobile-grid-50'; break;
				case 5: $class = 'grid-20 tablet-grid-33 mobile-grid-50'; break;
				case 6: $class = 'grid-20 tablet-grid-33 mobile-grid-50'; break;
				case 7: $class = 'grid-15 tablet-grid-33 mobile-grid-50'; break;
				case 8: $class = 'grid-10 tablet-grid-33 mobile-grid-50'; break;
				case 9: $class = 'grid-10 tablet-grid-33 mobile-grid-50'; break;
			}
            $thumb_size = 'medium';
            $output = '<div class="epcl-gallery">';
			$output .= '<ul class="columns-'.$columns.' grid-container grid-parent np-tablet np-mobile">';
				$count = 0;
				foreach($images as $image){
					$caption = $image->post_excerpt;
					$description = $image->post_content;
					if($description == '') $description = $image->post_title;
					$url = wp_get_attachment_image_src($image->ID, 'ep-large');
                    $thumb_url = wp_get_attachment_image_src($image->ID, $thumb_size);
                    if( isset($epcl_theme['enable_lazyload_posts']) && $epcl_theme['enable_lazyload_posts'] === '1' ){
                        $output .= '
                        <li class="'.$class.'">
                           <div class="wrapper">
                               <a href="'.$url[0].'" class="full-image mfp-image hover-effect" title="'.$caption.'"><span class="img cover lazy" data-src="'.$thumb_url[0].'"></span></a>
                           </div>
                        </li>';
                    }else{
                        $output .= '
                        <li class="'.$class.'">
                           <div class="wrapper">
                               <a href="'.$url[0].'" class="full-image mfp-image hover-effect" title="'.$caption.'"><span class="img cover" style="background: url('.$thumb_url[0].');"></span></a>
                           </div>
                        </li>';
                    }
					$count++;
				}
            $output .= '</ul><div class="clear"></div>';
            $output .= '</div>';
			return $output;
		}

		public function remove_gallery($content) {
			add_shortcode('gallery', '__return_false');
			return $content;
        }

        /* Add a custom image column to wp-admin-> posts */

		public function posts_columns($defaults){
            $defaults[EPCL_THEMEPREFIX.'_post_image'] = esc_html__('Image', 'rein');
			return $defaults;
		}

		public function posts_custom_columns($column_name, $id){

			if( $column_name === EPCL_THEMEPREFIX.'_post_image' ){
                the_post_thumbnail(EPCL_THEMEPREFIX.'_admin_thumb');
            }
            
		}

        /* Clear empty spaces before shortcodes */

		public function shortcode_empty_paragraph_fix($content){
			$array = array (
				'<p>[' => '[',
				']</p>' => ']',
				']<br />' => ']'
			);
			$content = strtr($content, $array);
			return $content;
		}

        /* Enable ACF menu for developers */

        public function enable_acf_menu() {
            global $epcl_theme;

            if( empty($epcl_theme) ) return;
            
            if( $epcl_theme['enable_acf_menu'] == '1' ){
                add_filter('acf/settings/show_admin', '__return_true');
            }           

        }

        /* Lazy Load for post content */

        public function enable_lazyload() {
            global $epcl_theme;

            if( empty($epcl_theme) ) return;

            if( $epcl_theme['enable_lazyload_posts'] === '1' || $epcl_theme['enable_lazyload_embed'] === '1' ){
                add_filter( 'the_content', array( $this, 'add_image_placeholders' ), 99 );
		        add_filter( 'post_thumbnail_html', array( $this, 'add_image_placeholders' ), 11 );
            }
        }

        public function add_image_placeholders( $content ) {
            global $epcl_theme;
            // Don't lazyload for feeds, previews, mobile
            if( is_feed() || is_preview() || is_admin() )
                return $content;
            // Don't lazyload for amp-wp content
            if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
                return $content;
            }
            // Don't lazy-load if the content has already been run through previously
            if ( false !== strpos( $content, 'data-lazy-src' ) )
                return $content;

            if ( false !== strpos( $content, 'data-lazy="false"' ) )
                return $content;
            
            if( isset($epcl_theme['enable_lazyload_posts']) && $epcl_theme['enable_lazyload_posts'] === '1' ){
                $content = preg_replace_callback( '/<img .*?>/', function($matches) {
                    $replaced = preg_replace( '/\bsrc\s*=\s*[\'"](.*?)[\'"]/', 'src="'.EPCL_THEMEPATH.'/assets/images/transparent.gif" data-lazy="true" data-src="$1"', $matches[0] );
                    $replaced = str_replace( array('srcset=', 'sizes='), array('data-srcset=', 'data-sizes='), $replaced );                    
                    return $replaced;
                }, $content);
            }

            if( isset($epcl_theme['enable_lazyload_embed']) && $epcl_theme['enable_lazyload_embed'] === '1' ){
                $content = preg_replace_callback( '/<iframe .*?>/', function($matches) {
                    return preg_replace( '/\bsrc\s*=\s*[\'"](.*?)[\'"]/', 
                        'data-lazy="true" data-src="$1"', $matches[0] ); } , $content);
            }           

            return $content;
        }
        
     }  

	 new epcl_rein_theme_functions();

 }

 // Render Instagram

function epcl_render_instagram(){
    $nonce = $_POST['nonce'];
	
    if ( ! wp_verify_nonce( $nonce, 'epcl_render_instagram' ) ){
        die ( 'Error');
    }        

    $user_id = epcl_get_option('instagram_id');
    $access_token = epcl_get_option('instagram_access_token');
    $ig_user_id = 'self';
    $limit = 6;
 
    $remote_wp = wp_remote_get( "https://api.instagram.com/v1/users/self/media/recent/?access_token=" . $access_token );
     
    $instagram_response = json_decode( $remote_wp['body'] );
     
    if( $remote_wp['response']['code'] == 200 ) {
        $i = 0;
        echo '<a href="https://www.instagram.com/'.epcl_get_option('instagram_id').'" target="_blank" class="ig-user button title"><i class="fa fa-instagram"></i> '.esc_html__('Follow me!', 'rein').'</a>';
        foreach( $instagram_response->data as $m ) {
            if( $i == $limit ) break;
            $caption = '';
            $url = $m->link; // Story URL
            $thumb = esc_url( $m->images->thumbnail->url ); // Thumb 150x150
            $thumb = esc_url( $m->images->low_resolution->url ); // Thumb 300x300
            $image_hd = esc_url( $m->images->standard_resolution->url ); // High Quality image
            if( isset(  $m->caption->text ) ){
                $caption =  $m->caption->text ; // Caption (optional)
            }

            echo '<a href="' . $m->link . '" class="item thumb hover-effect grid-20 tablet-grid-20 mobile-grid-33" target="_blank" rel="nofollow"><span class="fullimage cover lazy" data-src="'.$thumb.'"></span></a>';
            
            // echo '<a href="' . $m->link . '" class="item thumb hover-effect grid-20 tablet-grid-20 mobile-grid-33" target="_blank" rel="nofollow"><span class="fullimage cover" style="background-image: url('.$thumb.');"></span></a>';

            // more parameters here https://www.instagram.com/developer/endpoints/users/#get_users_media_recent

            $i++;
     
        }
     
    } elseif ( $remote_wp['response']['code'] == 400 ) {
        echo '<b>Error: ' . $remote_wp['response']['message'] . ': </b>' . $instagram_response->meta->error_message;
        die();
    }

    die();
}

add_action('wp_ajax_nopriv_epcl_render_instagram', 'epcl_render_instagram');
add_action('wp_ajax_epcl_render_instagram', 'epcl_render_instagram');

?>