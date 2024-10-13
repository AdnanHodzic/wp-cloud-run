<?php

/* Custom ADS */

function epcl_custom_ads($atts, $content = null) {
	global $epcl_theme;
    if( empty($epcl_theme) ) return;
    
    extract(shortcode_atts(array(
		'id' => '1'
    ), $atts));
    
    $section = 'custom_shortcode';
    
    if( $id > 1 ){
        $section = 'custom_shortcode_'.$id; 
    }

    if( $epcl_theme['ads_enabled_'.$section] !== '1' ) return;
    
    if( isset($epcl_theme['ads_mobile_'.$section]) && $epcl_theme['ads_mobile_'.$section] == '0' && wp_is_mobile() ) return;

	$margin_top = '0';
	$margin_bottom = '0';
	if( $epcl_theme['ads_mt_'.$section] ){
		$margin_top = $epcl_theme['ads_mt_'.$section];
	}
	if( $epcl_theme['ads_mb_'.$section] ){
		$margin_bottom = $epcl_theme['ads_mb_'.$section];
	}
	$html = '<!-- start: .epcl-banner -->
	    <div class="epcl-banner mobile-grid-100 textcenter epcl-banner-'.$section.'" style="margin-top: '.esc_attr($margin_top).'px; margin-bottom: '.esc_attr($margin_bottom). 'px;">';
	if( !empty($epcl_theme['ads_image_'.$section]) && $epcl_theme['ads_type_'.$section] == 'image' ) {
		$html .= '		    
	            <a href="'.esc_url( $epcl_theme['ads_url_'.$section] ).'" target="_blank">
	                <img src="'.esc_attr( $epcl_theme['ads_image_'.$section]['url'] ).'" class="custom-image" alt="'.esc_attr__('Banner', 'rein').'">
	            </a>';
    }else{
		$html .= $epcl_theme['ads_code_'.$section];
    }

    $html .= '
		</div>
	    <!-- end: .epcl-banner -->
	    <div class="clear"></div>';

	return $html;
}
add_shortcode('epcl_custom_ads', 'epcl_custom_ads');

/* Columns */

function epcl_shortcodes_columns($atts, $content = null) {
	extract(shortcode_atts(array(
		'structure' => ''
	), $atts));
		
	return '<div class="epcl-shortcode epcl-columns">'.do_shortcode($content).'<div class="clear"></div></div>';
	 
}
add_shortcode('epcl_columns', 'epcl_shortcodes_columns');

/* Column */

function epcl_shortcodes_column($atts, $content = null) {
	extract(shortcode_atts(array(
		'width' => '50'
    ), $atts));
    
    $width = intval($width);
	
	return '<div class="epcl-shortcode epcl-col grid-'.$width.'">'.wpautop( do_shortcode($content) ).'</div>';

}
add_shortcode('epcl_col', 'epcl_shortcodes_column');

/* Button */

function epcl_button_shortcode($atts, $content = NULL) {
	extract( shortcode_atts( array(
		'label' => '',
		'url' => '',
		'color' => 'green',
		'type' => 'gradient',
		'size' => 'regular',
		'icon' => '',
        'target' => '_self',
        'rel' => ''
	), $atts ) );
	if($icon) $icon = '<i class="epcl-icon fa '.$icon.'"></i>';
    else $icon = '';
    $rel_attr = '';
    if( $rel == 'nofollow' ) $rel_attr = 'rel="nofollow"';
	return '<a href="'.$url.'" class="epcl-shortcode epcl-button '.$size.' '.$type.' '.$color.'" target="'.$target.'" '.$rel_attr.'>'.$icon.$label.'</a>';
	
}
add_shortcode('epcl_button', 'epcl_button_shortcode');

/* Boxes/Alerts */

function epcl_box_shortcode($atts, $content = NULL){
	extract( shortcode_atts( array(
		'type' => 'error',
		'color' => ''
	), $atts ) );
	if($type == 'custom' && $color){
		return '<div class="epcl-shortcode epcl-box custom" style="background: '.$color.';">'.do_shortcode($content).'</div>';
	}else{
		switch($type){
			default: case 'error': $icon = 'fa-warning'; break;
			case 'success': $icon = 'fa-check'; break;
			case 'notice': $icon = 'fa-info'; break;
			case 'information': $icon = 'fa-eye'; break;
		}
		return '<div class="epcl-shortcode epcl-box '.$type.'"><i class="epcl-icon fa '.$icon.'"></i>'.do_shortcode($content).'</div>';	
	}
}

add_shortcode('epcl_box', 'epcl_box_shortcode');

/* Icon */

function epcl_icon_shortcode($atts, $content = NULL){
	extract( shortcode_atts( array(
		'size' => '16px',
		'color' => '#999999',
		'icon' => 'icon-circlepath',
	), $atts ) );
    $size = str_replace('px', '', $size);
	return '<i class="epcl-shortcode epcl-icon fa '.$icon.'" style="font-size: '.$size.'px;color: '.$color.';"></i>';
}
add_shortcode('epcl_icon', 'epcl_icon_shortcode');

/* Elements */

function epcl_clear_shortcode($atts, $content = NULL){
	return '<div class="clear"></div>';	
}
add_shortcode('clear', 'epcl_clear_shortcode');

/* Toggles */

function epcl_toggle_shortcode($atts, $content = NULL){
	extract( shortcode_atts( array(
		'title' => '',
        'show' => 'closed',
        'custom_class' => ''
	), $atts ) );
	$active = '';
	if($show == 'opened') $active = 'active';
	return '<div class="epcl-shortcode epcl-toggle epcl-toggle-elem '.$show.' '.esc_attr($custom_class).'"><h3 class="toggle-title">'.$title.'<i class="epcl-icon fa fa-plus"></i></h3><div class="toggle-content">'.do_shortcode($content).'</div></div>';
}

add_shortcode('epcl_toggle', 'epcl_toggle_shortcode');

/* Accordion */

function epcl_accordions_shortcode($atts, $content = NULL){
	return '<div class="epcl-shortcode epcl-accordions">'.do_shortcode($content).'</div>';
}

add_shortcode('epcl_accordions', 'epcl_accordions_shortcode');

function epcl_accordion_shortcode($atts, $content = NULL){
	extract( shortcode_atts( array(
        'title' => '',
        'custom_class' => ''
	), $atts ) );
	return '<div class="epcl-shortcode epcl-toggle accordion-elem '.esc_attr($custom_class).'"><h3 class="toggle-title">'.$title.'<i class="epcl-icon fa fa-plus"></i></h3><div class="toggle-content">'.do_shortcode($content).'</div></div>';
}

add_shortcode('epcl_accordion', 'epcl_accordion_shortcode');

/* Tabs */

$tabs_divs = '';

function epcl_tabs_shortcode($atts, $content = NULL ) {
    global $tabs_divs;
	extract(shortcode_atts(array(  
        'mode' => 'horizontal'
    ), $atts)); 
    $tabs_divs = '';

    $output = '<div class="epcl-shortcode epcl-tabs">';
		$output.= '<ul class="tab-links">'.do_shortcode($content).'</ul>';
		$output.= '<div class="tab-container">'.do_shortcode($tabs_divs).'</div>';
		$output.= '<div class="clear"></div>';
	$output.= '</div>';
    return $output;  
}
add_shortcode('epcl_tabs', 'epcl_tabs_shortcode');

function epcl_tab_shortcode($atts, $content = NULL) {  
    global $tabs_divs;
    extract(shortcode_atts(array(  
        'title' => ''
    ), $atts));  
	$id = 'tab-'.sanitize_title($title).rand(100, 999);
    $output = '<li><a href="javascript:void(0)" data-id="'.$id.'">'.$title.'</a></li>';
    $tabs_divs .= '<div id="'.$id.'" class="tab-item">'.do_shortcode($content).'</div>';
    return $output;
}
add_shortcode('epcl_tab', 'epcl_tab_shortcode');

/* Testimonials */

function epcl_testimonials_shortcode($atts, $content = NULL){
	extract( shortcode_atts( array( 'limit' => '', 'order' => 'DESC'), $atts ) );
	$args = array(
		'posts_per_page' => $limit,
		'order' => $order,
		'post_type' => 'testimonials',
		'suppress_filters' => false
	);
	$posts = get_posts($args); 
	$testimonials = '<div class="flexslider epcl-shortcode epcl-testimonials">
		<ul class="slides">';
			foreach($posts as $post): setup_postdata($post);
				$class = '';
				if(!has_post_thumbnail($post->ID)) $class = 'class="no-thumb"';
                $testimonials .= '<li '.$class.'>
					<div class="container">
						<div class="left">
							<div class="img">'.epcl_get_thumb('testimonials', $post->ID).'</div>
							<h4>'.get_the_title($post->ID).'</h4>
						</div>
						<div class="right text">
							'.apply_filters('the_content', do_shortcode(get_the_content())).'
						</div>
						<div class="clear"></div>
					</div>
                </li>';
             endforeach; 
	$testimonials .= '</div></ul>';
	wp_reset_postdata();
	return $testimonials;
}
add_shortcode('epcl_testimonials', 'epcl_testimonials_shortcode');

/* Portfolio */

function epcl_portfolio_shortcode($atts, $content = NULL){
	extract( shortcode_atts( array( 'columns' => 3, 'order' => 'DESC', 'categories' => ''), $atts ) );
	
	if($columns < 3 || $columns > 5) $columns = 3;
	
	/* Filter by category slug */
	if($categories){
		$categories_array = explode(',', $categories);
		$categories_array = array_map('trim', $categories_array);
		$args = array(
			'posts_per_page' => -1,
			'order' => $order,
			'post_type' => 'portfolio',
			'suppress_filters' => false,
			'tax_query' => array(
				array('taxonomy' => 'portfolio_taxonomy',
					'field' => 'slug',
					'terms' => $categories_array
				)
			)
		);
	/* No filter */		
	}else{
		$args = array(
			'posts_per_page' => -1,
			'order' => $order,
			'post_type' => 'portfolio',
			'suppress_filters' => false
		);	
	}
	$posts = get_posts($args);
	$grid_class = '';
	switch($columns){
		default: case 3: $grid_class = 'grid-33 tablet-grid-50 mobile-grid-100'; break;
		case 4: $grid_class = 'grid-25 tablet-grid-50 mobile-grid-100'; break;
		case 5: $grid_class = 'grid-20 tablet-grid-50 mobile-grid-100'; break;
	}
	$thumb = 'portfolio';
	$cats = '';
	$html = '<div class="epcl-shortcode epcl-portfolio columns-'.$columns.'" id="portfolio-'.rand().'">';
		$categories = get_categories('taxonomy=portfolio_taxonomy&orderby=id');
		foreach($categories as $c){
			if(!empty($categories_array) && !in_array($c->slug, $categories_array)){
				
			}else{
				$cats .= '<li><a href="#" data-filter=".'.$c->slug.'">'.$c->name.'</a></li>';
			}
		}
		
		$html .= '
				<ul class="categories">
                    <li><a href="#" data-filter="*" class="active">'.esc_html__('Show All', 'epcl_framework').'</a></li>
					'.$cats.'
                </ul>';
		$html .= '<div class="grid-container"><div class="masonry">';
		$zindex = 2000;
		foreach($posts as $post): setup_postdata($post);
			$id = $post->ID;
			$terms_list = $terms = '';
			$terms = get_the_terms($id, 'portfolio_taxonomy');
			if(!empty($terms))
				foreach($terms as $term) $terms_list .= ' '.$term->slug;	
			$html .= '
			<div class="item '.$grid_class.' '.$terms_list.'" style="z-index: '.$zindex--.';">
				<article>
					<div class="img">'.epcl_get_thumb($thumb, $id).'</div>
					<div class="info">
						<time datetime="'.get_the_time('Y-m-j', $id).'"><i class="fa fa-clock-o"></i> '.get_the_time(get_option('date_format'), $id).'</time>
						<h4>'.get_the_title($id).'</h4>
						<a href="'.get_permalink($id).'" class="readmore">'.esc_html__('View more', 'epcl_framework').'</a>
					</div>
				</article>
			</div>';
		 endforeach; 
	$html .= '</div></div></div>';
	wp_reset_postdata();
	return $html;
}
add_shortcode('epcl_portfolio', 'epcl_portfolio_shortcode');