<?php
/**
 * Advanced Ads - Backend modal
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 *
 * @var string $modal_slug       Unique slug that can be addressed by a link or button.
 * @var string $modal_content    The modal content. May contain HTML.
 * @var string $modal_title      The modal title.
 * @var string $cancel_action    Show/Hide cancel button.
 * @var string $close_action     Adds another close button that can trigger an action.
 * @var string $close_form       Add a form ID. This form will be submitted after clicking the close and action button.
 * @var string $close_validation A JavaScript validation function. The function has to return true or the form won't be submitted.
 */

$close_validation_object = [
	'function' => $close_validation,
	'modal_id' => "#modal-$modal_slug",
];

?>
<script>
	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelector( '#modal-<?php echo esc_attr( $modal_slug ); ?>' ).closeValidation = <?php echo wp_json_encode( $close_validation_object ); ?>;
		<?php if ( $close_action && $close_form ) : ?>
		document.querySelector( '#modal-<?php echo esc_attr( $modal_slug ); ?> .advads-modal-close-action' ).addEventListener( 'click', function ( event ) {
			modal_submit_form( event, '<?php echo esc_attr( $close_form ); ?>', '#modal-<?php echo esc_attr( $modal_slug ); ?>', '<?php echo esc_attr( $close_validation ); ?>' );
		} );
		<?php endif; ?>
	} );
</script>
<dialog id="modal-<?php echo esc_attr( $modal_slug ); ?>" class="advads-modal" data-modal-id="<?php echo esc_attr( $modal_slug ); ?>" autofocus>
	<a href="#close" class="advads-modal-close-background">Close</a>
	<div class="advads-modal-content">
		<div class="advads-modal-header">
			<a href="#close" class="advads-modal-close" title="<?php esc_html_e( 'Cancel', 'advanced-ads' ); ?>">&times;</a>
			<h2>
				<?php echo esc_html( $modal_title ); ?>
			</h2>
		</div>
		<div class="advads-modal-body">
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- modal content may contain any kind of custom html
			echo $modal_content;
			?>
		</div>
		<div class="advads-modal-footer">
			<div class="tablenav bottom">
				<?php if ( false === $cancel_action ) : ?>
				<a href="#close" class="button button-secondary advads-modal-close">
					<?php esc_html_e( 'Cancel', 'advanced-ads' ); ?>
				</a>
				<?php endif; ?>

				<?php if ( $close_action ) : ?>
					<?php if ( $close_form ) : ?>
						<button type="submit" form="<?php echo esc_attr( $close_form ); ?>" class="button button-primary advads-modal-close-action">
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- close action may contain custom html like button bar, image or span tag e.g.
							echo $close_action;
							?>
						</button>
					<?php else : ?>
						<a href="#close" class="button button-primary advads-modal-close-action">
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- close action may contain custom html like button bar, image or span tag e.g.
							echo $close_action;
							?>
						</a>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</dialog>
