<?php
/**
 * Repeat the position upgrade notice.
 *
 * @package Advanced_Ads
 */

use AdvancedAds\Admin\Upgrades;

?>
<p><label><input type="checkbox" disabled="disabled"/><?php esc_html_e( 'repeat the position', 'advanced-ads' ); ?></label>
	<?php Upgrades::upgrade_link( null, 'https://wpadvancedads.com/add-ons/advanced-ads-pro/', 'upgrade-content-repeat' ); ?>
</p>
