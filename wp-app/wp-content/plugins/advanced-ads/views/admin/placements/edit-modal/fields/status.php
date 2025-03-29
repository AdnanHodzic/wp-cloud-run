<?php
/**
 * Markup for the placement status select box.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 *
 * @var Placement $placement Placement instance.
 */

$statuses = [
	'draft'   => __( 'Draft', 'advanced-ads' ),
	'publish' => __( 'Publish', 'advanced-ads' ),
]
?>
<div class="advads-placement-status-select-wrap">
	<select
		id="advads-placements-modal-status-<?php echo esc_attr( $placement->get_id() ); ?>"
		name="post_status"
		class="advads-placement-status-select"
	>
		<?php foreach ( $statuses as $key => $status ) : // phpcs:ignore ?>
			<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $placement->get_status(), $key ); ?>>
				<?php echo esc_html( $status ); ?>
			</option>
		<?php endforeach; ?>
	</select>
</div>
