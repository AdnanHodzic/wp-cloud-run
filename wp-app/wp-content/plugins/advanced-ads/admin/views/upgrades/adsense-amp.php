<?php
/**
 * AdSense AMP upgrade notice.
 *
 * @package Advanced_Ads
 */

use AdvancedAds\Admin\Upgrades;

?>
<label class="label">AMP</label>
<div id="advads-adsense-responsive-amp-inputs">
	<?php esc_html_e( 'Automatically convert AdSense ads into their AMP format', 'advanced-ads' ); ?>
	<p><?php Upgrades::upgrade_link( null, 'https://wpadvancedads.com/add-ons/responsive-ads/', 'upgrade-ad-edit-adsense-amp' ); ?></p>
</div>
<hr />
