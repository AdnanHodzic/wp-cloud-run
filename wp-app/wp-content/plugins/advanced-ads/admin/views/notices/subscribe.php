<?php
/**
 * Subscribe notice template.
 *
 * @package AdvancedAds
 *
 * @var string $_notice     notice ID.
 * @var string $text        notice text.
 * @var array  $notice      notice data (optional).
 */

?>
<div class="notice notice-info advads-admin-notice is-dismissible" data-notice="<?php echo esc_attr( $_notice ); ?>">
	<div class="advads-notice-box_wrapper">
		<p><?php echo $text; // phpcs:ignore ?></p>
		<button type="button" class="button-primary advads-notices-button-subscribe" data-notice="<?php echo esc_attr( $_notice ); ?>">
			<?php echo esc_html( $notice['confirm_text'] ?? __( 'Subscribe me now', 'advanced-ads' ) ); ?>
			</button>
	</div>
</div>
