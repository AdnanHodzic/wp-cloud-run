<?php
/**
 * Render item option for placements.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 *
 * @var Placement $placement Placement instance.
 */

$placement_item = $placement->get_item_object();
$allowed_ads    = $placement->get_type_object()->get_allowed_ads();
$allowed_groups = $placement->get_type_object()->get_allowed_groups();
$has_items      = ! empty( $allowed_ads ) || ! empty( $allowed_groups );

// Show a button when no ads exist, yet.
if ( ! $has_items ) : ?>
	<a class="button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=advanced_ads' ) ); ?>">
		<?php esc_html_e( 'Create your first ad', 'advanced-ads' ); ?>
	</a>
<?php else : ?>
	<label for="advads-placement-item-<?php echo esc_attr( $placement->get_slug() ); ?>" class="screen-reader-text">
		<?php esc_html_e( 'Choose the Ad or Group', 'advanced-ads' ); ?>
	</label>

	<div class="advads-placement-item-select-wrap">
		<?php include 'item-select.php'; ?>
		<span class="advads-loader hidden"></span>

		<a class="advads-placement-item-edit" href="<?php echo esc_url( $placement_item ? $placement_item->get_edit_link() : '#' ); ?>" style="display: <?php echo esc_attr( $placement_item && $placement_item->get_id() > 0 ? 'inline' : 'none' ); ?>" title="<?php esc_attr_e( 'Edit item', 'advanced-ads' ); ?>">
			<span class="dashicons dashicons-external"></span>
		</a>
	</div>
	<?php
endif;
/**
 * Do action after the item select.
 */
do_action( 'advads-placement-item-select-after' );
