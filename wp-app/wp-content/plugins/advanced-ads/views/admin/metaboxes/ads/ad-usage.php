<?php
/**
 * Render the Usage meta box on the ad edit screen
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 *
 * @var Ad $ad Ad instance.
 */

?>
<div id="advads-ad-usage" class="advads-option-list">
	<?php
	require_once ADVADS_ABSPATH . 'views/admin/metaboxes/ads/ad-usage-notes.php';
	require_once ADVADS_ABSPATH . 'views/admin/metaboxes/ads/ad-usage-shortcodes.php';
	?>
</div>
