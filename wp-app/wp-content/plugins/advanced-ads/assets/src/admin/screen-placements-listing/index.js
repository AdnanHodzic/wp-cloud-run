import jQuery from 'jquery';
import filters from '../shared-modules/ads-placements-listing';
import frontendPicker from './frontend-picker';
import itemSelect from './item-select';
import formSubmission from './form-submission';
import QuickEdit from './quick-edit';

jQuery(function () {
	filters();
	frontendPicker();
	itemSelect();
	formSubmission();
	QuickEdit();
	newPlacement();
});

function newPlacement() {
	// open modal if no placements are available.
	if (jQuery('#posts-filter tr.no-items').length) {
		const modal = document.querySelector('#modal-placement-new');
		if (modal && typeof modal.showModal === 'function') {
			modal.showModal();
		}
	}
}

jQuery(document).on(
	'click',
	'.post-type-advanced_ads_plcmnt .wp-list-table [type="checkbox"]',
	() => {
		jQuery(
			'.post-type-advanced_ads_plcmnt .tablenav.bottom .bulkactions'
		).toggleClass(
			'fixed',
			0 <
				jQuery(
					'.post-type-advanced_ads_plcmnt .check-column [type="checkbox"]:checked'
				).length
		);
	}
);
