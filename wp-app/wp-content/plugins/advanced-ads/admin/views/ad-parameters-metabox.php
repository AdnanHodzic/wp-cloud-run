<?php $types = Advanced_Ads::get_instance()->ad_types; ?>
<?php
/**
 * When changing ad type ad parameter content is loaded via ajax
 *
 * @filesource admin/assets/js/admin.js
 * @filesource includes/class-ajax-callbacks.php ::load_ad_parameters_metabox
 * @filesource classes/ad-type-content.php :: renter_parameters()
 */
do_action( 'advanced-ads-ad-params-before', $ad, $types );
?>
<div id="advanced-ads-tinymce-wrapper" style="display:none;">
	<?php
		$args = [
			// used here instead of textarea_rows, because of display:none.
			'editor_height'    => 300,
			'drag_drop_upload' => true,
		];
		wp_editor( '', 'advanced-ads-tinymce', $args );
		?>
</div>
<div id="advanced-ads-ad-parameters" class="advads-option-list">
	<?php
	$type = ( isset( $types[ $ad->type ] ) ) ? $types[ $ad->type ] : current( $types );
	$type->render_parameters( $ad );

	$types_without_size = [ 'dummy' ];
	$types_without_size = apply_filters( 'advanced-ads-types-without-size', $types_without_size );
	// todo: manage which ad types have a size in the ad type definition.
	if ( ! in_array( $ad->type, $types_without_size ) ) {
		include ADVADS_ABSPATH . 'admin/views/ad-parameters-size.php';
	};
	?>
	</div>
<?php

// extend the parameters form by ad type
if ( isset( $types[ $ad->type ] ) ) {
	do_action( "advanced-ads-ad-params-after-{$ad->type}", $ad, $types );
}

// extend the parameter form
do_action( 'advanced-ads-ad-params-after', $ad, $types );
