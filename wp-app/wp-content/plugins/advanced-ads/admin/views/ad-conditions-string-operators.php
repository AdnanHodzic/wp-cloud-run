<?php
/**
 * Render select field for matching-technique of string-based Display Conditions
 *
 * @package   Advanced_Ads_Admin
 * @author    Thomas Maier <support@wpadvancedads.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright since 2013 Thomas Maier, Advanced Ads GmbH
 *
 * @var string $name option name.
 * @var string $operator value of the $operator option.
 */
?><select name="<?php echo esc_attr( $name ); ?>[operator]">
	<option value="contain" <?php selected( 'contain', $operator ); ?>><?php esc_attr_e( 'contains', 'advanced-ads' ); ?></option>
	<option value="start" <?php selected( 'start', $operator ); ?>><?php esc_attr_e( 'starts with', 'advanced-ads' ); ?></option>
	<option value="end" <?php selected( 'end', $operator ); ?>><?php esc_attr_e( 'ends with', 'advanced-ads' ); ?></option>
	<option value="match" <?php selected( 'match', $operator ); ?>><?php esc_attr_e( 'matches', 'advanced-ads' ); ?></option>
	<option value="regex" <?php selected( 'regex', $operator ); ?>><?php esc_attr_e( 'matches regex', 'advanced-ads' ); ?></option>
	<option value="contain_not" <?php selected( 'contain_not', $operator ); ?>><?php esc_attr_e( 'does not contain', 'advanced-ads' ); ?></option>
	<option value="start_not" <?php selected( 'start_not', $operator ); ?>><?php esc_attr_e( 'does not start with', 'advanced-ads' ); ?></option>
	<option value="end_not" <?php selected( 'end_not', $operator ); ?>><?php esc_attr_e( 'does not end with', 'advanced-ads' ); ?></option>
	<option value="match_not" <?php selected( 'match_not', $operator ); ?>><?php esc_attr_e( 'does not match', 'advanced-ads' ); ?></option>
	<option value="regex_not" <?php selected( 'regex_not', $operator ); ?>><?php esc_attr_e( 'does not match regex', 'advanced-ads' ); ?></option>
</select>
