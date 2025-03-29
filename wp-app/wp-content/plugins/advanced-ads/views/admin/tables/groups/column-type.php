<?php
/**
 * Render the group type column content in the group table.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 *
 * @var Group $group Group instance.
 */

$group_type = $group->get_type_object();
?>
<div class="advads-form-type">
	<img src="<?php echo esc_url( $group_type->get_image() ); ?>" alt="<?php echo esc_attr( $group_type->get_title() ); ?>">
</div>
