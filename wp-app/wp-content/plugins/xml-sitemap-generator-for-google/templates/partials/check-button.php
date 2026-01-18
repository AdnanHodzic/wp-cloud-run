<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$languages      = sgg_get_languages();
$wpml_languages = apply_filters( 'wpml_active_languages', array() );
$input_class    = $args['input_class'] ?? '';
$input_label    = $args['input_label'] ?? '';
$input_value    = $args['input_value'] ?? '';
$input_name     = $args['input_name'] ?? '';
$description    = $args['description'] ?? '';
$button_id      = $args['button_id'] ?? '';
$button_text    = $args['button_text'] ?? '';
$button_name    = $args['button_name'] ?? '';
$placeholder    = $args['placeholder'] ?? '';

?>
<div class="grim-previews-url">
	<p>
		<?php
		Dashboard::render(
			'fields/input.php',
			array(
				'placeholder' => $placeholder,
				'name'        => $input_name,
				'value'       => $input_value,
				'label'       => $input_label,
				'class'       => $input_class,
			)
		);
		?>
	</p>

	<div class="video-sitemap-depended">
		<input type="hidden" name="<?php echo esc_attr( $button_name ); ?>" value="">
		<button type="submit" id="<?php echo esc_attr( $button_id ); ?>" class="grim-button grim-submit white video-sitemap-depended">
			<span>
				<?php echo esc_html( $button_text ); ?>
			</span>
		</button>
	</div>
</div>
<div class="<?php echo esc_attr( $args['class'] ?? '' ); ?>">
	<?php echo esc_html( $args['label'] ?? '' ); ?>
	<span class="grim-previews-url-desc <?php echo esc_attr( $args['input_class'] ?? '' ); ?>">
		<?php
		if ( ! empty( $description ) ) {
			echo wp_kses_post( $description );
		}
		?>
	</span>
</div>
