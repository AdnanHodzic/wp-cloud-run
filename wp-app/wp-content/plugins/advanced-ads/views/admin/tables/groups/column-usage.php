<?php
/**
 * Render the group usage column content in the group table.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 *
 * @var Group $group Group instance.
 */

?>
<div class="advads-usage">
	<strong><?php esc_html_e( 'shortcode', 'advanced-ads' ); ?></strong>
	<code><input type="text" onclick="this.select();" value='[the_ad_group id="<?php echo esc_attr( $group->get_id() ); ?>"]' readonly /></code>
	<br/><br/>
	<strong><?php esc_html_e( 'template (PHP)', 'advanced-ads' ); ?></strong>
	<code><input type="text" onclick="this.select();" value="the_ad_group(<?php echo esc_attr( $group->get_id() ); ?>);" readonly /></code>
</div>
