<?php
/**
 * Render the ad type parameters meta box on the ad edit screen
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 *
 * @var Ad $ad Ad instance.
 */

$ad_type = $ad->get_type_object();

do_action( "advanced-ads-ad-params-before-{$ad->get_type()}", $ad );
do_action( 'advanced-ads-ad-params-before', $ad );
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
	if ( $ad_type->has_size() ) {
		include ADVADS_ABSPATH . 'views/admin/metaboxes/ads/ad-parameters-size.php';
	}
	?>
	</div>
<?php

do_action( "advanced-ads-ad-params-after-{$ad->get_type()}", $ad );
do_action( 'advanced-ads-ad-params-after', $ad );
