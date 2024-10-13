<?php
/**
 * Ads loop in a group.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 *
 * @var Advanced_Ads_Group $group      Group object.
 * @var int[]              $weights    All weights.
 * @var int                $weight_sum Sum of all ad weights.
 * @var Groups_List_Table  $this       The group terms list table class.
 * @var WP_Query           $ads        The query object to fetch all ads inside that group.
 */

use AdvancedAds\Admin\Groups_List_Table;

$i = 1;

echo '<div class="advads-ad-group-list-ads advads-table-flex">';

while ( $ads->have_posts() ) {
	$ads->the_post();
	$ad_id                = get_the_ID();
	$ad_weight_percentage = '';

	// Calculate ad weight percentage if group type is 'default'.
	if ( 'default' === $group->type && $weight_sum ) {
		$weight               = $weights[ $ad_id ] ?? Advanced_Ads_Group::MAX_AD_GROUP_DEFAULT_WEIGHT;
		$ad_weight_percentage = $this->calculate_weight_percentage( $weight, $weight_sum );
	}

	$ad_schedule_output = Advanced_Ads_Admin_Ad_Type::get_ad_schedule_output( $ad_id );
	include ADVADS_ABSPATH . 'views/admin/tables/groups/list-row-ads.php';

	++$i;
}
echo '</div>';

if ( $ads->post_count > 4 ) {
	echo '<p><a href="javascript:void(0)" class="advads-group-ads-list-show-more">+ ' .
		/* translators: %d is a number. */
		sprintf( esc_html__( 'show %d more ads', 'advanced-ads' ), $ads->post_count - 3 ) . // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'</a></p>';
}

if ( $ads->post_count > 1 ) {
	echo '<p>' . esc_html( $this->get_ad_count_string( $group, $ads ) ) . '</p>';
}
