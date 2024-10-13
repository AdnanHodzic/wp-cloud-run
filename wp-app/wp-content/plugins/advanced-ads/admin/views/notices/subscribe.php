<div class="notice notice-info advads-admin-notice is-dismissible" data-notice="<?php echo $_notice; ?>">
	<p><?php echo $text; ?>
	<button type="button" class="button-primary advads-notices-button-subscribe" data-notice="<?php echo $_notice; ?>"><?php echo isset( $notice['confirm_text'] ) ? $notice['confirm_text'] : __( 'Subscribe me now', 'advanced-ads' ); ?></button>
	</p>
</div>
