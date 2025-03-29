<?php
/**
 * Ads loop in a group.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 *
 * @var Group $group Group instance.
 */

use AdvancedAds\Abstracts\Group;
use AdvancedAds\Utilities\WordPress;

$counter   = 1;
$ad_count  = $group->get_ads_count();
$weights   = $group->get_ad_weights();
$group_ads = $group->get_ads();
usort($group_ads, function ($a, $b) {
	return $b->get_weight() <=> $a->get_weight();
});

foreach ( $group_ads as $group_ad ) {
	if ( ! $group_ad->is_status( 'publish' ) ) {
		unset( $weights[ $group_ad->get_id() ] );
	}
}

$weight_sum = array_sum( $weights );
?>

<div class="advads-ad-group-list-ads advads-table-flex">
	<?php
	foreach ( $group_ads as $ad ) :
		$ad_weight_percentage = '';
		?>
		<div style="display: <?php echo $counter > 3 ? 'none' : 'flex'; ?>">
			<div>
				<a href="<?php echo esc_url( get_edit_post_link( $ad->get_id() ) ); ?>"><?php echo esc_html( $ad->get_title() ); ?></a>
			</div>
			<div>
				<?php echo $ad->get_ad_schedule_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<?php
			if ( 'default' === $group->get_type() && $weight_sum ) :
				$weight               = $ad->get_weight() ?? Group::MAX_AD_GROUP_DEFAULT_WEIGHT;
				$ad_weight_percentage = $ad->is_status( 'publish' ) ? WordPress::calculate_percentage( $weight, $weight_sum ) : '0%';
				?>
			<div class="advads-ad-group-list-ads-weight">
				<span title="<?php esc_attr_e( 'Ad weight', 'advanced-ads' ); ?>"><?php echo esc_html( $ad_weight_percentage ); ?></span>
			</div>
			<?php endif; ?>
		</div>
		<?php
		++$counter;
	endforeach;
	?>
</div>

<?php if ( $ad_count > 4 ) : ?>
<p>
	<a href="javascript:void(0)" class="advads-group-ads-list-show-more">
	<?php
	/* translators: %d is a number. */
	echo esc_html( sprintf( __( '+ show %d more ads', 'advanced-ads' ), $ad_count - 3 ) );
	?>
	</a>
</p>
<?php endif; ?>

<?php
if ( $ad_count > 1 ) :
	$ad_count = 'all' === $group->get_display_ad_count() ? $ad_count : $group->get_display_ad_count();
	/**
	 * Filters the displayed ad count on the ad groups page.
	 *
	 * @param int   $ad_count The amount of displayed ads.
	 * @param Group $group    The current ad group.
	 */
	$ad_count = (int) apply_filters( 'advanced-ads-group-displayed-ad-count', $ad_count, $group );
	$ad_count = (int) apply_filters( 'advanced-ads-group-' . $group->get_type() . '-displayed-ad-count', $ad_count, $group );

	/* translators: amount of ads displayed */
	echo '<p>' . esc_html( sprintf( _n( 'Up to %d ad displayed.', 'Up to %d ads displayed', $ad_count, 'advanced-ads' ), $ad_count ) ) . '</p>';

endif;
