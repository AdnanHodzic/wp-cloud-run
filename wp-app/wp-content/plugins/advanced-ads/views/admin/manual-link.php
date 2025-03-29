<?php
/**
 * Render Manual Link
 *
 * @package AdvancedAds
 * @since   2.0.0
 *
 * @param string $title link text.
 * @param string $url target URL.
 */

?>

<a class="advads-link advads-manual-link" href="<?php echo esc_url( $url ); ?>" target="_blank">
	<?php echo esc_html( $title ); ?>
</a>
