import jQuery from 'jquery';
import quickBulkEdit from './quick-bulk-edit';
import commonFilters from '../shared-modules/ads-placements-listing.js';
import adsFilter from './filters.js';

jQuery(function () {
	quickBulkEdit();
	commonFilters();
	adsFilter();
});
