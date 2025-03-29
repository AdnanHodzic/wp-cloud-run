<?php
/**
 * Render column.
 *
 * @package AdvancedAds
 * @var int $ad_id Ad ID.
 */

$groups     = wp_advads_get_group_repository()->get_groups_by_ad_id( $ad_id );
$placements = wp_advads_get_placement_repository()->find_by_item_id( 'ad_' . $ad_id );

if ( $groups ) :
	?>
	<strong><?php echo esc_html__( 'Groups', 'advanced-ads' ) . ':'; ?></strong>
	<div>
		<?php
		$group_links = [];
		foreach ( $groups as $group ) {
			$group_links[] = '<a href="' . esc_attr( $group->get_edit_link() ) . '" target="_blank">'
				. esc_html( $group->get_name() ) . '</a>';
		}
		echo implode( ', ', $group_links ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $group_links is HTML.
		?>
	</div>
	<?php
endif;

if ( $groups && $placements ) {
	echo '<br>';
}

if ( $placements ) :
	?>
	<strong><?php echo esc_html__( 'Placements', 'advanced-ads' ) . ':'; ?></strong>
	<div>
		<?php
		$ids             = [];
		$placement_links = [];
		foreach ( $placements as $placement ) {
			$ids[]             = $placement->get_id();
			$placement_links[] = '<a href="' . esc_attr( $placement->get_edit_link() ) . '" target="_blank">'
				. esc_html( $placement->get_title() ) . '</a>';
		}
		echo implode( ', ', $placement_links ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $placement_links is HTML.
		?>
	</div>
	<?php
endif;
