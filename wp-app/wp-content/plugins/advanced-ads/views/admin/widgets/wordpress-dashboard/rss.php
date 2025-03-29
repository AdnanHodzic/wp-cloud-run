<?php
/**
 * Render RSS section
 *
 * @package AdvancedAds
 */

use AdvancedAds\Utilities\Data;

?>
<div class="advads-widget-wrapper">
	<div class="section-title">
		<h3><?php esc_html_e( 'Latest Tutorials from Advanced Ads', 'advanced-ads' ); ?></h3>
	</div>

	<?php Data::display_rss_feed(); ?>
</div>
