<?php

add_action('wp', 'epcl_disable_cf7_home');

function epcl_disable_cf7_home() {
    $epcl_theme = epcl_get_theme_options();
    if( empty($epcl_theme) ) return;
    if( epcl_get_option('enable_optimization') || defined('W3TC') ){
        if( is_front_page() || is_home() ){
            add_filter( 'wpcf7_load_css', '__return_false' );
            add_filter( 'wpcf7_load_js', '__return_false' );
        }
    }        
}

add_action('wp_enqueue_scripts', 'epcl_enqueue_scripts_plugin', 11);

function epcl_enqueue_scripts_plugin() {
    $epcl_theme = epcl_get_theme_options();

    $assets_folder = EPCL_THEMEPATH.'/assets';
    $prefix = EPCL_THEMEPREFIX.'-';

    $theme = wp_get_theme( EPCL_THEMESLUG );
    $ver = $theme->version;   

    /* CSS */

    $fonts = array(
        $epcl_theme['primary_titles_font'], $epcl_theme['body_font'],
        $epcl_theme['sidebar_titles_font'], $epcl_theme['sidebar_font'],
        $epcl_theme['footer_titles_font'], $epcl_theme['footer_font'],
    );

    wp_register_style( $prefix . 'redux-google-fonts' , epcl_googlefonts_redux( $fonts ), NULL, NULL );
    wp_enqueue_style( $prefix . 'redux-google-fonts' );

    /* Scripts */
    
    // W3 Total Cache optimization

    if( !empty($epcl_theme) && $epcl_theme['move_jquery_footer'] ){ // Only enabled by panel
        wp_scripts()->add_data( 'jquery', 'group', 1 );
        wp_scripts()->add_data( 'jquery-core', 'group', 1 );
        wp_scripts()->add_data( 'jquery-migrate', 'group', 1 );
    }		

    // Disqus inline JS

    if( !empty($epcl_theme) && $epcl_theme['hosted_comments'] == 2 && $epcl_theme['disqus_id'] ){
        $custom_js = epcl_add_disqus_scripts();
        if( epcl_get_option('enable_optimization') == '1'){
            wp_add_inline_script($prefix.'scripts', $custom_js);
        }else{
            wp_add_inline_script($prefix.'functions', $custom_js);
        }
    }

    // Facebook Comments
    if( !empty($epcl_theme) && $epcl_theme['hosted_comments'] == 3 ){
        $fb_lang_code = 'en_US';
        $app_id = '';
        if( epcl_get_option('facebook_lang_code') !== '' ){
            $fb_lang_code = epcl_get_option('facebook_lang_code');
        }
        
        if( epcl_get_option('facebook_app_id') !== '' ){
            $app_id = '&appId='.epcl_get_option('facebook_app_id');
        }
        wp_enqueue_script( $prefix.'facebook-comments', 'https://connect.facebook.net/'.esc_attr($fb_lang_code).'/sdk.js#xfbml=1&version=v3.3'.$app_id, array(), false, true ); 
    }

}

function epcl_googlefonts_redux( $google_fonts ) {
    $link = $fonts_url = "";
    $subsets = array();
    $fonts = array();

    foreach ( $google_fonts as $font ) {        
        $link = '';
        if(  isset($font['google']) && ($font['google'] == 'true' || $font['google'] == "1" ) ){
            
            $link .= $font['font-family'];
            if( !empty($font['font-family']) && !empty($font['font-weight']) ){
                $link .= ':'.$font['font-weight'] ;
            }

            if( $link ){
                $fonts[] = $link;   
            }            
    
            if ( ! empty( $font['subsets'] ) ) {
                if ( ! in_array( $font['subsets'], $subsets ) ) {
                    array_push( $subsets, $font['subsets'] );
                }
            }
        }           
        
    }

    if ( !empty($fonts) ) {
        $fonts_url = add_query_arg( array(
            'family' => urlencode( implode( '|', $fonts ) ),
            'subset' => urlencode( implode( ',', $subsets ) ),
            'display' => 'swap',
        ), '//fonts.googleapis.com/css' );
    }

    return $fonts_url;
}

function epcl_add_disqus_scripts(){
    $epcl_theme = epcl_get_theme_options();

    $js = 
    '
    var disqus_shortname = "'.esc_attr( $epcl_theme['disqus_id']).'";
    
    !function(){var e=document.createElement("script");e.async=!0,e.type="text/javascript",e.src="//"+disqus_shortname+".disqus.com/count.js",document.getElementsByTagName("BODY")[0].appendChild(e)}();
    ';
    if( is_single() || ( is_page() && !is_page_template() ) ){
        if( !comments_open() ){
            return;
        }
        $js .= '
        var disqus_config = function () {
            this.page.url = "'.get_the_permalink().'"; 
            this.page.identifier = "'.get_the_ID().'";
        };
        (function() { 
            var d = document, s = d.createElement("script");
            s.src = "//" + disqus_shortname + ".disqus.com/embed.js";
            s.setAttribute("data-timestamp", +new Date());
            (d.head || d.body).appendChild(s);
        })();';
    }

    return $js;
        
}

if( function_exists('autoptimize') ){
    add_filter('autoptimize_filter_css_exclude','epcl_autoptimize_css_exclude');
    function epcl_autoptimize_css_exclude($in) {
        return $in.',fontawesome.min.css';
    } 
    // add_filter('autoptimize_html_after_minify','preload_to_aodeferload');
    // function preload_to_aodeferload($htmlIn) {
    //     return str_replace('<link rel="preload"','<link rel="prefetch"',$htmlIn);
    // }
}

function epcl_styles_footer() {
    $epcl_theme = epcl_get_theme_options();

    $assets_folder = EPCL_THEMEPATH.'/assets';
    $prefix = EPCL_THEMEPREFIX.'-';
    
    $theme = wp_get_theme( EPCL_THEMESLUG );
    $ver = $theme->version;

    if( epcl_get_option('fonts_icons_method', 'footer') == 'footer' ){
        wp_enqueue_style($prefix.'fontawesome', $assets_folder.'/dist/fontawesome.min.css', NULL, $ver);
    }

    if( epcl_get_option('fonts_icons_method', 'footer') == 'javascript' ){
        $custom_js = epcl_font_icons_scripts();
        if( epcl_get_option('enable_optimization') == '1'){
            wp_add_inline_script($prefix.'scripts', $custom_js);
        }else{
            wp_add_inline_script($prefix.'functions', $custom_js);
        }
    }

    $ajax_scripts = epcl_get_option('custom_ajax_scripts');

    if ( !empty($ajax_scripts) ): ?>
        <div id="epcl-ajax-scripts" style="display: none;">
            <?php foreach( $ajax_scripts as $item ): ?>
                <?php if( $item !== ''): ?>
                    <div
                        data-src="<?php echo esc_attr( trim($item) ); ?>"
                        data-cache="true"
                        data-timeout="<?php echo absint( epcl_get_option('script_timeout', '1500') ); ?>">
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif;

}

add_action( 'wp_footer', 'epcl_styles_footer', 11 );

function epcl_font_icons_scripts(){
    $delay = absint( epcl_get_option('font_icons_delay', '750') );
    $assets_folder = EPCL_THEMEPATH.'/assets';
    $js = "
    setTimeout(function(){
        epcl_load_css_file('$assets_folder/dist/fontawesome.min.css');
    }, $delay);
    function epcl_load_css_file(filename){
        var head = document.getElementsByTagName('head')[0];
        var style = document.createElement('link');
        style.href = filename;
        style.type = 'text/css';
        style.rel = 'stylesheet';
        head.appendChild(style);
    }
    ";
    return $js;
}