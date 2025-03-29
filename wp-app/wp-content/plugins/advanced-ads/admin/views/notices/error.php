<?php
/**
 * Error notice template.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.x.x
 *
 * @var string $_notice Notice ID.
 * @var string $text    Notice text.
 */

?>
<div class="notice notice-error advads-notice advads-admin-notice is-dismissible" data-notice="<?php echo esc_attr( $_notice ); ?>">
	<p>
		<?php echo wp_kses_post( $text ); ?>
	</p>
</div>
