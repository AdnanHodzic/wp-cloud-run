<?php
/**
 * Placement list table description.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

use AdvancedAds\Entities;

?>
<p class="description">
	<?php echo esc_html( Entities::get_placement_description() ); ?>
	<a href="https://wpadvancedads.com/manual/placements/?utm_source=advanced-ads&utm_medium=link&utm_campaign=placements" target="_blank" class="advads-manual-link">
		<?php esc_html_e( 'Manual', 'advanced-ads' ); ?>
	</a>
</p>
