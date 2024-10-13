<?php
/**
 * @var WP_Taxonomy $taxonomy
 */
?>
<p class="advads-conditions-not-selected advads-notice-inline advads-idea">
	<?php
	printf(
	// translators: %s is a name of a taxonomy.
		esc_html_x( 'No %s found on your site.', 'Error message shown when no terms exists for display condition; placeholder is taxonomy label.', 'advanced-ads' ),
		esc_html( $taxonomy->label )
	);
	?>
</p>
