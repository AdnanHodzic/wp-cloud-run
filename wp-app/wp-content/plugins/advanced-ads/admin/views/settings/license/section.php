<p>
	<a href="https://wpadvancedads.com/manual/how-to-install-an-add-on/?utm_source=advanced-ads&utm_medium=link&utm_campaign=settings-licenses-install-add-ons" target="_blank">
		<?php esc_html_e( 'How to install and activate an add-on.', 'advanced-ads' ); ?>
	</a>
<?php
printf(
	wp_kses(
	// translators: %s is a URL.
		__( 'See also <a href="%s" target="_blank">Issues and questions about licenses</a>.', 'advanced-ads' ),
		[
			'a' => [
				'href'   => [],
				'target' => [],
			],
		]
	),
	'https://wpadvancedads.com/manual/purchase-licenses/?utm_source=advanced-ads&utm_medium=link&utm_campaign=settings-licenses'
);
?>
</p>
<input type="hidden" id="advads-licenses-ajax-referrer" value="<?php echo esc_attr( wp_create_nonce( 'advads_ajax_license_nonce' ) ); ?>"/>
