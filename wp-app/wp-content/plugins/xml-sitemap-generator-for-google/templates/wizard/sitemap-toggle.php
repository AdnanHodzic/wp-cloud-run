<?php
/**
 * @var array $args
 */

$disabled = ! empty( $args['is_pro'] ) && ! sgg_pro_enabled();
?>
<div class="wizard-toggle-section">
	<div class="wizard-toggle-title">
		<h3><?php echo esc_html( $args['title'] ); ?></h3>
		<p class="description"><?php echo esc_html( $args['description'] ); ?></p>
	</div>
	<label class="toggle-switch">
		<input type="checkbox" class="wizard-form-checkbox" name="<?php echo esc_html( $args['name'] ); ?>" value="1"
			<?php checked( $args['checked'] ?? false ); ?> <?php disabled( $disabled ); ?>/>
		<span class="toggle-switch-background">
			<span class="toggle-switch-handle"></span>
		</span>
	</label>
	<?php
	if ( $disabled ) {
		sgg_show_pro_overlay();
	}
	?>
</div>
