<?php
/**
 * Render ad list filters.
 *
 * @package AdvancedAds
 */

use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Options;

// TODO: refactor whole filter system.

global $wp_query;

$screen             = get_current_screen();
$filters_to_show    = $screen->get_option( 'filters_to_show' ) ?? [];
$is_privacy_enabled = Options::instance()->get( 'privacy.enabled' );

$all_filters = wp_advads()->list_filters->get_all_filters();

$selected_ad_type = Params::request( 'adtype', '' );
$ad_size          = Params::request( 'adsize', '' );
$ad_date          = Params::request( 'addate', '' );
$ad_group         = Params::request( 'adgroup', '' );
$ad_author        = Params::request( 'ad_author', '' );
$ad_debug         = Params::request( 'ad_debugmode', '' );
$ad_displayonce   = Params::request( 'ad_displayonce', '' );
$ad_privacyignore = Params::request( 'ad_privacyignore', '' );

// hide the filter button. Can not filter correctly with "trashed" posts.
if ( 'trash' === Params::request( 'post_status', '' ) ) {
	echo '<style type="text/css">#post-query-submit{display:none;}</style>';
}

$ad_types = wp_advads_get_ad_types();
usort(
	$ad_types,
	function ( $a, $b ) {
		return strcmp( $a->get_title(), $b->get_title() );
	}
);
?>
<div class="advads-ad-filters-container">
	<!-- Types -->
	<select id="advads-filter-type" name="adtype">
		<option value="">- <?php esc_html_e( 'Ad Types', 'advanced-ads' ); ?> -</option>
		<?php foreach ( $ad_types as $ad_type ) : ?>
		<option <?php selected( $selected_ad_type, $ad_type->get_id() ); ?> value="<?php echo esc_attr( $ad_type->get_id() ); ?>"><?php echo esc_html( $ad_type->get_title() ); ?></option>
		<?php endforeach; ?>
	</select>

	<!-- Sizes -->
	<?php if ( ! empty( $all_filters['all_sizes'] ) ) : ?>
	<select id="advads-filter-size" name="adsize">
		<option value="">- <?php esc_html_e( 'Ad Sizes', 'advanced-ads' ); ?> -</option>
		<?php foreach ( $all_filters['all_sizes'] as $key => $value ) : ?>
		<option <?php selected( $ad_size, $key ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
		<?php endforeach; ?>
	</select>
	<?php endif; ?>

	<!-- Dates -->
	<?php if ( ! empty( $all_filters['all_dates'] ) ) : ?>
	<select id="advads-filter-date" name="addate">
		<option value="">- <?php esc_html_e( 'Ad Dates', 'advanced-ads' ); ?> -</option>
		<?php foreach ( $all_filters['all_dates'] as $key => $value ) : ?>
		<option <?php selected( $ad_date, $key ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
		<?php endforeach; ?>
	</select>
	<?php endif; ?>

	<!-- Groups -->
	<?php if ( ! empty( $all_filters['all_groups'] ) ) : ?>
	<select id="advads-filter-group" name="adgroup">
		<option value="">- <?php esc_html_e( 'Ad Groups', 'advanced-ads' ); ?> -</option>
		<?php foreach ( $all_filters['all_groups'] as $key => $value ) : ?>
		<option <?php selected( $ad_group, $key ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
		<?php endforeach; ?>
	</select>
	<?php endif; ?>

	<!-- Debug Mode -->
	<?php if ( in_array( 'all_debug_mode', $filters_to_show, true ) ) : ?>
	<select name="ad_debugmode">
		<option value="">- <?php esc_html_e( 'Debug Mode', 'advanced-ads' ); ?> -</option>
		<option <?php selected( $ad_debug, 'yes' ); ?> value="yes"><?php esc_html_e( 'Enabled', 'advanced-ads' ); ?></option>
		<option <?php selected( $ad_debug, 'no' ); ?> value="no"><?php esc_html_e( 'Disabled', 'advanced-ads' ); ?></option>
	</select>
	<?php endif; ?>

	<!-- Author -->
	<?php if ( in_array( 'all_authors', $filters_to_show, true ) && ! empty( $all_filters['all_authors'] ) ) : ?>
	<select name="ad_author">
		<option value="">- <?php esc_html_e( 'Ad Authors', 'advanced-ads' ); ?> -</option>
		<?php foreach ( $all_filters['all_authors'] as $key => $value ) : ?>
		<option <?php selected( $ad_author, $key ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
		<?php endforeach; ?>
	</select>
	<?php endif; ?>

	<!-- Display once -->
	<?php if ( defined( 'AAP_VERSION' ) && in_array( 'all_displayonce', $filters_to_show, true ) ) : ?>
	<select name="ad_displayonce">
		<option value="">- <?php esc_html_e( 'Display Once', 'advanced-ads' ); ?> -</option>
		<option <?php selected( $ad_displayonce, 'yes' ); ?> value="yes"><?php esc_html_e( 'Enabled', 'advanced-ads' ); ?></option>
		<option <?php selected( $ad_displayonce, 'no' ); ?> value="no"><?php esc_html_e( 'Disabled', 'advanced-ads' ); ?></option>
	</select>
	<?php endif; ?>

	<!-- Privacy ignore -->
	<?php if ( $is_privacy_enabled && in_array( 'all_privacyignore', $filters_to_show, true ) ) : ?>
	<select name="ad_privacyignore">
		<option value="">- <?php esc_html_e( 'Privacy Ignore', 'advanced-ads' ); ?> -</option>
		<option <?php selected( $ad_privacyignore, 'yes' ); ?> value="yes"><?php esc_html_e( 'Enabled', 'advanced-ads' ); ?></option>
		<option <?php selected( $ad_privacyignore, 'no' ); ?> value="no"><?php esc_html_e( 'Disabled', 'advanced-ads' ); ?></option>
	</select>
	<?php endif; ?>

	<?php if ( isset( $wp_query->found_posts ) && $wp_query->found_posts > 0 ) : ?>
		<?php do_action( 'advanced-ads-ad-list-filter-markup', $all_filters ); ?>
	<?php endif; ?>

	<a href="#" id="advads-ad-filter-customize"><?php esc_html_e( 'Customize filters', 'advanced-ads' ); ?></a>
</div>
