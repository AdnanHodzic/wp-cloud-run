<?php
/**
 * Inline notice template.
 *
 * @package AdvancedAds
 *
 * @var string $_notice     notice ID.
 * @var string $text        notice text.
 * @var array  $notice      notice data (optional).
 * @var string $box_classes additional classes (optional).
 */

?>
<div class="notice notice-info advads-admin-notice is-dismissible inline advads-notice-box <?php echo esc_attr( $box_classes ?? '' ); ?>" data-notice="<?php echo esc_attr( $_notice ); ?>">
	<div class="advads-notice-box_wrapper">
		<p><?php echo $text; // phpcs:ignore ?></p>
		<button type="button" class="button-primary advads-notices-button-subscribe with-icon" data-notice="<?php echo esc_attr( $_notice ); ?>">
			<span class="dashicons dashicons-email-alt"></span>
			<?php echo esc_html( $notice['confirm_text'] ?? __( 'Subscribe me now', 'advanced-ads' ) ); ?>
		</button>
	</div>
</div>
