<?php
/**
 * Render the group name column content in the group table.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 *
 * @var Group $group Group instance.
 */

?>
<div class="advads-table-name">
	<a class="row-title" href="#modal-group-edit-<?php echo esc_attr( $group->get_id() ); ?>"><?php echo esc_html( $group->get_name() ); ?></a>
</div>
<?php if ( $this->type_error ) : ?>
<p class="advads-notice-inline advads-error">
	<?php echo esc_html( $this->type_error ); ?>
</p>
	<?php
endif;
