<?php
/**
 * @var $args
 */

use GRIM_SG\PTSettings;

$options = [
	PTSettings::$ALWAYS  => __( 'Always', 'xml-sitemap-generator-for-google' ),
	PTSettings::$HOURLY  => __( 'Hourly', 'xml-sitemap-generator-for-google' ),
	PTSettings::$DAILY   => __( 'Daily', 'xml-sitemap-generator-for-google' ),
	PTSettings::$WEEKLY  => __( 'Weekly', 'xml-sitemap-generator-for-google' ),
	PTSettings::$MONTHLY => __( 'Monthly', 'xml-sitemap-generator-for-google' ),
	PTSettings::$YEARLY  => __( 'Yearly', 'xml-sitemap-generator-for-google' ),
	PTSettings::$NEVER   => __( 'Never', 'xml-sitemap-generator-for-google' ),
];

$current = $args['value'] ?? PTSettings::$NEVER;
?>
<div class="grim-select grim-select-default" data-name="<?php echo esc_attr( $args['name'] ); ?>">
	<div class="grim-select__trigger">
		<span>
			<?php echo esc_html( $options[ $current ] ?? __( 'Never', 'xml-sitemap-generator-for-google' ) ); ?>
		</span>
		<i class="grim-icon-chevron-down"></i>
	</div>
	<div class="grim-options">
		<?php foreach ( $options as $value => $label ) : ?>
			<div class="grim-option <?php echo (string) $current === (string) $value ? 'selected' : ''; ?>" data-value="<?php echo esc_attr( $value ); ?>">
				<?php echo esc_html( $label ); ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>

<select id="<?php echo esc_attr( $args['name'] ); ?>"
		name="<?php echo esc_attr( $args['name'] ); ?>"
		class="grim-hidden-select"
		hidden>
	<?php foreach ( $options as $value => $label ) : ?>
		<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>>
			<?php echo esc_html( $label ); ?>
		</option>
	<?php endforeach; ?>
</select>
