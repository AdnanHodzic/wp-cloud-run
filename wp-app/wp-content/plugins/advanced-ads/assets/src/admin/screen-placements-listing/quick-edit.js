import jQuery from 'jquery';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

/**
 * Fetch the placement data
 *
 * @param {Number} id the placement ID.
 */
const getPlacementData = (id) => {
	apiFetch({
		path: addQueryArgs('/advanced-ads/v1/placement', { id: id }),
		method: 'GET',
	}).then((data) => {
		if (data.error) {
			return;
		}
		updateInputs(id, data);
	});
};

/**
 * Update quick edit fields with the fetched data
 *
 * @param {Number} id placement ID.
 * @param {Object} data placement data.
 */
const updateInputs = (id, data) => {
	const row = jQuery(`#edit-${id}`);
	row.find('fieldset:disabled').prop('disabled', false);
	row.find('select[name="status"]').val(data.status);

	// Add values to field required by the default quick edit functions.
	row.find('[name="post_title"]').val(data.title);
	row.find('[name="mm"]').val('01');

	/**
	 * Allow add-ons to do field initialization
	 */
	wp.hooks.doAction(
		'advanced-ads-quick-edit-plaacement-fields-init',
		id,
		data
	);
};

export default () => {
	/* eslint-disable no-undef */
	const editCopy = window.inlineEditPost.edit;

	// Replace the default WP function
	window.inlineEditPost.edit = function (id) {
		/* eslint-enable no-undef */
		// Call the original WP edit function.
		editCopy.apply(this, arguments);

		// Now we do our stuff.
		if ('object' === typeof id) {
			getPlacementData(parseInt(this.getId(id), 10));
		}
	};
};
