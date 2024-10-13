<?php
/**
 * The view to render the option.
 *
 * @var boolean $allow True, when the option is checked.
 */
?>
<label>
	<input id="advanced-ads-editors-manage-ads" type="checkbox" <?php checked( $allow, true ); ?> name="<?php echo esc_attr( ADVADS_SLUG ); ?>[editors-manage-ads]"/>
	<?php esc_html_e( 'Allow editors to also manage and publish ads.', 'advanced-ads' ); ?>
	<?php
	printf(
		wp_kses(
		// translators: %s is a URL.
			__( 'You can assign different ad-related roles on a user basis with <a href="%s" target="_blank">Advanced Ads Pro</a>.', 'advanced-ads' ),
			[
				'a' => [
					'href'   => [],
					'target' => [],
				],
			]
		),
		'https://wpadvancedads.com/add-ons/advanced-ads-pro/?utm_source=advanced-ads&utm_medium=link&utm_campaign=settings'
	);
	?>
</label>
