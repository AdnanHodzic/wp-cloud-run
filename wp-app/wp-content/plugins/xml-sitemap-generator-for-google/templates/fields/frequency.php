<?php
/**
 * @var $args
 */

use GRIM_SG\PTSettings;
?>
<select name="<?php esc_attr_e( $args['name'] ); ?>">
	<option value="<?php esc_attr_e( PTSettings::$ALWAYS ); ?>" <?php selected( $args['value'] ?? '', PTSettings::$ALWAYS ); ?>>
		<?php esc_html_e( 'Always', 'xml-sitemap-generator-for-google' ); ?>
	</option>
	<option value="<?php esc_attr_e( PTSettings::$HOURLY ); ?>" <?php selected( $args['value'] ?? '', PTSettings::$HOURLY ); ?>>
		<?php esc_html_e( 'Hourly', 'xml-sitemap-generator-for-google' ); ?>
	</option>
	<option value="<?php esc_attr_e( PTSettings::$DAILY ); ?>" <?php selected( $args['value'] ?? '', PTSettings::$DAILY ); ?>>
		<?php esc_html_e( 'Daily', 'xml-sitemap-generator-for-google' ); ?>
	</option>
	<option value="<?php esc_attr_e( PTSettings::$WEEKLY ); ?>" <?php selected( $args['value'] ?? '', PTSettings::$WEEKLY ); ?>>
		<?php esc_html_e( 'Weekly', 'xml-sitemap-generator-for-google' ); ?>
	</option>
	<option value="<?php esc_attr_e( PTSettings::$MONTHLY ); ?>" <?php selected( $args['value'] ?? '', PTSettings::$MONTHLY ); ?>>
		<?php esc_html_e( 'Monthly', 'xml-sitemap-generator-for-google' ); ?>
	</option>
	<option value="<?php esc_attr_e( PTSettings::$YEARLY ); ?>" <?php selected( $args['value'] ?? '', PTSettings::$YEARLY ); ?>>
		<?php esc_html_e( 'Yearly', 'xml-sitemap-generator-for-google' ); ?>
	</option>
	<option value="<?php esc_attr_e( PTSettings::$NEVER ); ?>" <?php selected( $args['value'] ?? '', PTSettings::$NEVER ); ?>>
		<?php esc_html_e( 'Never', 'xml-sitemap-generator-for-google' ); ?>
	</option>
</select>