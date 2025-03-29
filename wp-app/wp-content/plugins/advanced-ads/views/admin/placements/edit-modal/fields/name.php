<?php
/**
 * Render the input to change the placement name.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 *
 * @var Placement $placement Placement instance.
 */

?>
<input type="text" name="post_title" size="30" value="<?php echo esc_html( $placement->get_title() ); ?>">
<span class="advads-help">
	<span class="advads-tooltip">
		<?php esc_html_e( 'Modifying the placement name will result in a change to the placement slug as well. Remember to update any customized CSS accordingly.', 'advanced-ads' ); ?>
	</span>
</span>
