<?php
/**
 * Info notice template
 *
 * @package AdvancedAds
 *
 * @var string $text notice text
 * @var string $_notice notice id
 */

use AdvancedAds\Framework\Utilities\Params;

?>
<div class="notice notice-info advads-notice advads-admin-notice message is-dismissible" data-notice="<?php echo esc_attr( $_notice ); ?>">
	<p><?php echo $text; // phpcs:ignore ?></p>
	<a href="
	<?php
	add_query_arg(
		[
			'action'   => 'advads-close-notice',
			'notice'   => $_notice,
			'nonce'    => wp_create_nonce( 'advanced-ads-admin-ajax-nonce' ),
			'redirect' => Params::server( 'REQUEST_URI' ),
		],
		admin_url( 'admin-ajax.php' )
	);
	?>
	" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html__( 'Dismiss this notice.', 'advanced-ads' ); ?></span></a>
</div>
