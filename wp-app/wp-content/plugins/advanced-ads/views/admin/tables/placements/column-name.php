<?php
/**
 * Render the placement name column content in the placement table.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 *
 * @var Placement $placement Placement instance.
 */

$type_object = $placement->get_type_object();
?>
<div class="advads-table-name">
	<a class="row-title" href="#modal-placement-edit-<?php echo esc_attr( $placement->get_id() ); ?>"><?php echo esc_html( $placement->get_title() ); ?></a>
	<?php if ( 'draft' === $placement->get_status() ) : ?>
		<strong>— <span class="post-state"><?php esc_html_e( 'Draft', 'advanced-ads' ); ?></span></strong>
	<?php endif; ?>
</div>
<?php if ( $type_object->is_premium() ) : ?>
<p class="advads-notice-inline advads-error">
	<?php
	echo esc_html(
		sprintf(
			/* translators: %s is the placement type string */
			__( 'The originally selected placement type “%s” is not enabled.', 'advanced-ads' ),
			$type_object->get_title()
		)
	);
	?>
</p>
	<?php
endif;
