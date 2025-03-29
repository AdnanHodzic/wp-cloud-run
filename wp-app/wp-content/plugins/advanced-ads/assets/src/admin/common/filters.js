import jQuery from 'jquery';

export default function () {
	// change btn icon on click
	jQuery('#advads-show-filters').on('click', function () {
		const dashicons = jQuery(this).find('.dashicons');
		const disabled = dashicons.hasClass('dashicons-arrow-up');

		dashicons.toggleClass('dashicons-filter', disabled);
		dashicons.toggleClass('dashicons-arrow-up', !disabled);
	});
}
