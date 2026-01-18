<?php
/**
 * @var $args
 */

wp_enqueue_style( 'sgg-rate-banner' );
wp_enqueue_style( 'sgg-icons' );

?>

<div class="grim-container grim-rate-container notice <?php echo esc_attr( $args['wrapper_classes'] ?? '' ); ?>">
	<div class="grim-section grim-rate grim-notice-data <?php echo esc_attr( $args['notice_class'] ?? '' ); ?>" data-notice="<?php echo esc_attr( $args['data_notice'] ); ?>">
		<?php if ( ! empty( $args['label'] ) ) : ?>
			<h3 class="grim-rate-title"><?php echo wp_kses_post( $args['label'] ); ?></h3>
		<?php endif; ?>
		<p class="grim-rate-description">
			<?php echo wp_kses_post( $args['description'] ) ?? ''; ?>
		</p>
		<div class="grim-rate-actions">

			<?php if( ! empty( $args['extra_btn_url'] ) ) : ?>
				<a class="grim-button white <?php echo esc_attr( $args['extra_btn_class'] ); ?>" target="_blank" href="<?php echo esc_url( $args['extra_btn_url'] ); ?>">
					<span><?php echo wp_kses_post( $args['extra_btn_text'] ); ?></span>
				</a>
			<?php else: ?>
				<a class="grim-button white sgg-notice">
					<span><?php esc_html_e( 'Dismiss', 'xml-sitemap-generator-for-google' ); ?></span>
				</a>
			<?php endif; ?>
			<a href="<?php echo esc_url( $args['button_url'] ); ?>" target="_blank" class="grim-button secondary sgg-notice">
				<span><?php echo esc_html( $args['button_text'] ) ?><i class="grim-icon-arrow-right"></i></span>
			</a>
		</div>
	</div>
</div>
