<?php
/**
 * Render the ad type column content in the ad list.
 *
 * @var Advanced_Ads_Ad_Type_Abstract[] $ad_types class instances for each ad type.
 * @var Advanced_Ads_Ad                 $ad       ad object.
 */
?>
<span class="advads-ad-list-tooltip">
	<span class="advads-ad-list-tooltip-content">
		<strong><?php echo esc_html( $ad_types[ $ad->type ]->title ); ?></strong><br/>
		<?php if ( ! empty( $size ) ) : ?>
			<span class="advads-ad-size"><?php echo esc_html( $size ); ?></span>
			<?php
		endif;
		$ad_types[ $ad->type ]->render_ad_type_tooltip( $ad );
		?>
	</span>
	<a href="<?php echo esc_url( get_edit_post_link( $ad->id ) ); ?>">
		<?php $ad_types[ $ad->type ]->render_icon( $ad ); ?>
	</a>
</span>
