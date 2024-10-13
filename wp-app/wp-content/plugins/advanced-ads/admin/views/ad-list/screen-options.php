<?php
/**
 * Screen Options for the ad list
 * #list-view-mode needs to be here to fix an issue were the list view mode cannot be reset automatically. Saving the form again does that.
 *
 * @var bool $show_filters
 */
?>
<input id="list-view-mode" type="hidden" name="mode" value="list">
<fieldset class="metabox-prefs advads-show-filter">
	<legend><?php esc_html_e( 'Filters', 'advanced-ads' ); ?></legend>
		<input id="advads-screen-options-show-filters" type="checkbox" name="advanced-ads-screen-options[show-filters]" value="true" <?php checked( $show_filters ); ?> />
		<label for="advads-screen-options-show-filters"><?php esc_html_e( 'Show filters permanently', 'advanced-ads' ); ?></label>
</fieldset>
<input type="hidden" name="advanced-ads-screen-options[sent]" value="true"/>
