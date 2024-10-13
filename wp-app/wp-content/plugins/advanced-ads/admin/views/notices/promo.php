<?php
/**
 * Template for a promotional banner
 *
 * @var string $text    content of the notice.
 * @var string $_notice internal key of the notice.
 */
?>
<div class="notice notice-promo advads-notice advads-admin-notice message is-dismissible"
	data-notice="<?php echo esc_attr( $_notice ); ?>">
	<p>
		<?php
		echo wp_kses(
			$text,
			[
				'a' => [
					'href' => [],
					'class' => [],
					'target' => [],
				],
				'span' => [
					'style' => [],
				],
			]
		);
		?>
	</p>
	<a href="
	<?php
	add_query_arg(
		[
			'action'   => 'advads-close-notice',
			'notice'   => $_notice,
			'nonce'    => wp_create_nonce( 'advanced-ads-admin-ajax-nonce' ),
			'redirect' => $_SERVER['REQUEST_URI'],
		],
		admin_url( 'admin-ajax.php' )
	);
	?>
	" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html__( 'Dismiss this notice.', 'advanced-ads' ); ?></span></a>
</div>
