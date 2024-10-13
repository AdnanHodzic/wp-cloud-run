<?php
function epcl_render_ads(){
    global $epcl_module;
    if( empty($epcl_module) ) return;
    if( $epcl_module['advertising_type'] == 'image'){
        $advertising_image_url = $epcl_module['advertising_image']['url'];
        $advertising_url = $epcl_module['advertising_url'];
    }else{
        $advertising_code = $epcl_module['advertising_code'];
    }
?>
    <!-- start: .epcl-banner -->
    <div class="epcl-banner textcenter section grid-container">
        <?php if( $epcl_module['advertising_type']  == 'image' && $advertising_image_url ): ?>
            <a href="<?php echo esc_url($advertising_url); ?>" target="_blank" rel="nofollow noopener">
                <img src="<?php echo esc_attr( $advertising_image_url ); ?>" class="custom-image" alt="<?php esc_attr_e('Banner', 'rein'); ?>">
            </a>
        <?php else: ?>
            <?php echo do_shortcode( $advertising_code ); ?>
        <?php endif; ?>
    </div>
    <!-- end: .epcl-banner -->
<?php
}

function epcl_render_header_ads(){
    global $epcl_theme;
?>
    <!-- start: .epcl-banner -->
    <div class="epcl-banner textcenter hide-on-tablet hide-on-mobile">
        <?php if( !empty($epcl_theme['header_advertising_image']) && $epcl_theme['header_advertising_type'] == 'image' ): ?>
            <a href="<?php echo esc_url( $epcl_theme['header_advertising_url'] ) ; ?>" target="_blank" rel="nofollow noopener">
                <img src="<?php echo esc_attr( $epcl_theme['header_advertising_image']['url'] ); ?>" class="custom-image" alt="<?php esc_attr_e('Banner', 'rein'); ?>">
            </a>
        <?php else: ?>
            <?php echo do_shortcode( $epcl_theme['header_advertising_code'] ); ?>
        <?php endif; ?>
    </div>
    <!-- end: .epcl-banner -->
    <div class="clear ad"></div>
<?php
}
function epcl_render_global_ads( $section = '' ){
	global $epcl_theme;
	if( !$section || empty($epcl_theme) ) return;

    if( $epcl_theme['ads_enabled_'.$section] !== '1' && !( isset($_GET['ads']) && $section == 'grid_loop' ) ) return;
    
    if( isset($epcl_theme['ads_mobile_'.$section]) && $epcl_theme['ads_mobile_'.$section] == '0' && wp_is_mobile() ) return;

	$margin_top = '0';
    $margin_bottom = '0';
    $class = '';
	if( $epcl_theme['ads_mt_'.$section] ){
	    $margin_top = $epcl_theme['ads_mt_'.$section];
    }
	if( $epcl_theme['ads_mb_'.$section] ){
		$margin_bottom = $epcl_theme['ads_mb_'.$section];
    }
    if( $section == 'below_header'){
        $class .= 'grid-container';
    }
    if( isset($epcl_theme['ads_mobile_'.$section]) && $epcl_theme['ads_mobile_'.$section] == '0'){
        $class .= ' hide-on-mobile hide-on-tablet';
    }
	?>
    <!-- start: .epcl-banner -->
    <div class="epcl-banner textcenter mobile-grid-100 epcl-banner-<?php echo esc_attr($section); ?> <?php echo esc_attr($class); ?>" style="margin-top: <?php echo esc_attr($margin_top); ?>px; margin-bottom: <?php echo esc_attr($margin_bottom); ?>px;">
		<?php if( !empty($epcl_theme['ads_image_'.$section]) && $epcl_theme['ads_type_'.$section] == 'image' ): ?>
            <a href="<?php echo esc_url( $epcl_theme['ads_url_'.$section] ) ; ?>" target="_blank" rel="nofollow noopener">
                <img src="<?php echo esc_attr( $epcl_theme['ads_image_'.$section]['url'] ); ?>" class="custom-image" alt="<?php esc_attr_e('Banner', 'rein'); ?>">
            </a>
		<?php else: ?>
			<?php echo do_shortcode( $epcl_theme['ads_code_'.$section] ); ?>
        <?php endif; ?>
    </div>
    <!-- end: .epcl-banner -->
    <div class="clear"></div>
	<?php
}