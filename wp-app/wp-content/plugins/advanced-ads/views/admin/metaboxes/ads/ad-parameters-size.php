<?php
/**
 * Render the ad type size parameter on the ad edit screen
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 *
 * @var Ad_Type $ad_type Ad type instance.
 */

// don’t show sizes for Google Ad Manager ads.
if ( 'gam' === $ad_type->get_id() ) {
	return;
}

$show_reserve_space   = in_array( $ad_type->get_id(), [ 'plain', 'content', 'group', 'adsense' ], true );
$enable_reserve_space = $show_reserve_space && $ad->is_space_reserved();
?>
<span class="label"><?php esc_html_e( 'size', 'advanced-ads' ); ?></span>
<div id="advanced-ads-ad-parameters-size">
	<label>
		<?php esc_html_e( 'width', 'advanced-ads' ); ?><input type="number" value="<?php echo esc_attr( $ad->get_width() ); ?>" name="advanced_ad[width]">px
	</label>
	<label>
		<?php esc_html_e( 'height', 'advanced-ads' ); ?><input type="number" value="<?php echo esc_attr( $ad->get_height() ); ?>" name="advanced_ad[height]">px
	</label>
	<label<?php echo ! $show_reserve_space ? ' style="display:none;"' : ''; ?>>
		<input type="checkbox" id="advads-wrapper-add-sizes" name="advanced_ad[reserve_space]" value="true" <?php checked( $enable_reserve_space ); ?>><?php esc_html_e( 'reserve this space', 'advanced-ads' ); ?>
	</label>
	<?php
	if ( 'image' === $ad_type->get_id() ) :
		$ad_type->show_original_image_size( $ad );
	endif;
	?>
</div>
<hr/>
