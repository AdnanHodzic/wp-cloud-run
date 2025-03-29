<?php
/**
 * Render the "Date" column date in the ad list.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 *
 * @var string $published_date ad published date.
 * @var string $modified_date ad modified date.
 */

?>
<div class="advads-ad-list-date">
	<?php if ( $modified_date === $published_date ) : ?>
		<?php esc_html_e( 'Published', 'advanced-ads' ); ?>
	<?php else : ?>
		<?php esc_html_e( 'Last Modified', 'advanced-ads' ); ?>
	<?php endif; ?>
	<br/>
	<?php echo esc_html( $modified_date ); ?>
</div>
