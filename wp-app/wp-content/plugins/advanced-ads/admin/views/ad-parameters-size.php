<?php

// don’t show sizes for Google Ad Manager ads.
if ( 'gam' === $type->ID ) {
	return;
}

?>
<span class="label"><?php esc_html_e( 'size', 'advanced-ads' ); ?></span>
<div id="advanced-ads-ad-parameters-size">
	<label><?php esc_html_e( 'width', 'advanced-ads' ); ?><input type="number" value="<?php echo isset( $ad->width ) ? esc_attr( $ad->width ) : 0; ?>" name="advanced_ad[width]">px</label>
	<label><?php esc_html_e( 'height', 'advanced-ads' ); ?><input type="number" value="<?php echo isset( $ad->height ) ? esc_attr( $ad->height ) : 0; ?>" name="advanced_ad[height]">px</label>
	<?php
	$show_reserve_space   = in_array( $type->ID, [ 'plain', 'content', 'group', 'adsense' ], true );
	$enable_reserve_space = $show_reserve_space && ! empty( $ad->output['add_wrapper_sizes'] );
	?>
	<label
	<?php
	if ( ! $show_reserve_space ) {
		echo 'style="display:none;"'; }
	?>
	><input type="checkbox" id="advads-wrapper-add-sizes" name="advanced_ad[output][add_wrapper_sizes]" value="true" <?php checked( $enable_reserve_space ); ?>><?php esc_html_e( 'reserve this space', 'advanced-ads' ); ?></label>
	<?php
	if ( 'image' === $type->ID ) :
		Advanced_Ads_Ad_Type_Image::show_original_image_size( $ad );
	endif;
	?>
</div>
<hr/>
