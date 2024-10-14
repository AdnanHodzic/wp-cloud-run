<?php
/**
 * @var $args
 */

use GRIM_SG\PTSettings;
?>
<select name="<?php echo esc_attr( $args['name'] ); ?>">
	<option value="<?php echo esc_attr( PTSettings::$ALWAYS ); ?>" <?php selected( $args['value'] ?? '', PTSettings::$ALWAYS ); ?>>
		<?php esc_html_e( 'Always', 'xml-sitemap-generator-for-google' ); ?>
	</option>
	<option value="<?php echo esc_attr( PTSettings::$HOURLY ); ?>" <?php selected( $args['value'] ?? '', PTSettings::$HOURLY ); ?>>
		<?php esc_html_e( 'Hourly', 'xml-sitemap-generator-for-google' ); ?>
	</option>
	<option value="<?php echo esc_attr( PTSettings::$DAILY ); ?>" <?php selected( $args['value'] ?? '', PTSettings::$DAILY ); ?>>
		<?php esc_html_e( 'Daily', 'xml-sitemap-generator-for-google' ); ?>
	</option>
	<option value="<?php echo esc_attr( PTSettings::$WEEKLY ); ?>" <?php selected( $args['value'] ?? '', PTSettings::$WEEKLY ); ?>>
		<?php esc_html_e( 'Weekly', 'xml-sitemap-generator-for-google' ); ?>
	</option>
	<option value="<?php echo esc_attr( PTSettings::$MONTHLY ); ?>" <?php selected( $args['value'] ?? '', PTSettings::$MONTHLY ); ?>>
		<?php esc_html_e( 'Monthly', 'xml-sitemap-generator-for-google' ); ?>
	</option>
	<option value="<?php echo esc_attr( PTSettings::$YEARLY ); ?>" <?php selected( $args['value'] ?? '', PTSettings::$YEARLY ); ?>>
		<?php esc_html_e( 'Yearly', 'xml-sitemap-generator-for-google' ); ?>
	</option>
	<option value="<?php echo esc_attr( PTSettings::$NEVER ); ?>" <?php selected( $args['value'] ?? '', PTSettings::$NEVER ); ?>>
		<?php esc_html_e( 'Never', 'xml-sitemap-generator-for-google' ); ?>
	</option>
</select>
