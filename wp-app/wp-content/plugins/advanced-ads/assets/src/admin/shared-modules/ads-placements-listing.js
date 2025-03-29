import jQuery from 'jquery';

const toggleFilters = () => {
	jQuery('.search-box').toggle();
	jQuery('.tablenav.top .alignleft.actions:not(.bulkactions)').toggle();
};

const filters = () => {
	jQuery('#advads-show-filters').on('click', toggleFilters);
	if (jQuery('#advads-reset-filters').length) {
		toggleFilters();
	}
};

export default function () {
	filters();
}
