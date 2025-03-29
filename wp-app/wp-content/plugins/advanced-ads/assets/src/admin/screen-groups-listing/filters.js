import jQuery from 'jquery';

export default function () {
	const groupFilter = jQuery('#advads-group-filter');

	jQuery('#advads-show-filters').on('click', () => groupFilter.toggle());

	// always show filters when reset btn exist.
	if (jQuery('#advads-reset-filters').length) {
		groupFilter.show();
	}
}
