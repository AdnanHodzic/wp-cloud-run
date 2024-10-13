<?php
/**
 * Single ad item in the list of ads in a group.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 *
 * @var int    $i                    Ad index.
 * @var int    $ad_id                Ad ID.
 * @var string $ad_schedule_output   Ad schedule output.
 * @var string $ad_weight_percentage Ad weight percentage.
 * @var Advanced_Ads_Group $group    Group object.
 * @var int $weight_sum              Sum of all ad weights.
 * @var WP_Query $ads                WP Query object of all found posts.
 */

?>
<div style="display: <?php echo ( $i > 3 && $ads->found_posts !== 4) ? 'none' : 'flex'; ?>">
	<div>
		<a href="<?php echo esc_url( get_edit_post_link( $ad_id ) ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
	</div>
	<div>
		<?php echo $ad_schedule_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<?php if ( 'default' === $group->type && $weight_sum ) : ?>
	<div class="advads-ad-group-list-ads-weight">
		<span title="<?php esc_attr_e( 'Ad weight', 'advanced-ads' ); ?>"><?php echo esc_html( $ad_weight_percentage ); ?></span>
	</div>
	<?php endif; ?>
</div>
