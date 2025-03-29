<?php
/**
 * Render the Adsense ID column content in the ad list.
 *
 * @package AdvancedAds
 * @var string $slotid Adsense Slot ID.
 */

?>
<?php if ( null !== $slotid ) : ?>
<div><?php echo esc_html( $slotid ); ?></div>
<?php endif; ?>
