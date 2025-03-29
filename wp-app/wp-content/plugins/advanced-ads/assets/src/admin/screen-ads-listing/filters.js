import jQuery from 'jquery';

function btnCustomizeFilters() {
	jQuery('#advads-ad-filter-customize').on('click', function () {
		jQuery('#show-settings-link').trigger('click');
	});
}

export default function () {
	btnCustomizeFilters();
}
