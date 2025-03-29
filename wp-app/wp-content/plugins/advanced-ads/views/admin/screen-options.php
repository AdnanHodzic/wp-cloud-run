<?php
/**
 * Screen Options for ads & placements list
 * #list-view-mode needs to be here to fix an issue were the list view mode cannot be reset automatically. Saving the form again does that.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 *
 * @var array $optional_filters    All available filters.
 * @var bool  $is_filter_permanent Show filters permanently.
 */

?>
<input id="list-view-mode" type="hidden" name="mode" value="list">
<input type="hidden" name="advanced-ads-screen-options[screen-id]" value="<?php echo esc_attr( get_current_screen()->id ); ?>">

<fieldset class="metabox-prefs advads-show-filter">
	<legend><?php esc_html_e( 'Filters', 'advanced-ads' ); ?></legend>
	<?php if ( ! empty( $optional_filters ) ) : ?>
		<?php foreach ( $optional_filters as $filter_key => $filter ) : ?>
			<input id="advads-so-filters-<?php echo esc_attr( $filter_key ); ?>" type="checkbox" name="advanced-ads-screen-options[filters_to_show][]" value="<?php echo esc_attr( $filter_key ); ?>" <?php checked( in_array( $filter_key, $selected_filters, true ) ); ?> />
			<label for="advads-so-filters-<?php echo esc_attr( $filter_key ); ?>"><?php echo esc_html( $filter ); ?></label>
		<?php endforeach; ?>
		<br><br>
	<?php endif; ?>
	<input id="advads-so-show-filters" type="checkbox" name="advanced-ads-screen-options[show-filters]" value="true" <?php checked( $is_filter_permanent ); ?> />
	<label for="advads-so-show-filters"><?php esc_html_e( 'Show filters permanently', 'advanced-ads' ); ?></label>
</fieldset>
