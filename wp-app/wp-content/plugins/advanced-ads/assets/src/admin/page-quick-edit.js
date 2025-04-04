import jQuery from 'jquery';
import apiFetch from '@wordpress/api-fetch';

/**
 * Get disabled ads status
 *
 * @param {number} id
 */
function getPostData(id) {
	apiFetch({
		path: `/advanced-ads/v1/page_quick_edit?id=${id}&nonce=${window.advancedAds.page_quick_edit.nonce}`,
		method: 'GET',
	}).then(function (data) {
		setQuickEditValues(id, data);
	});
}

/**
 * Update checkboxes states upon click on a quick edit link
 *
 * @param {number} id
 * @param {Object} data
 */
function setQuickEditValues(id, data) {
	const theRow = jQuery(`#edit-${id}`);
	const disableAds = theRow.find('[name="advads-disable-ads"]');
	disableAds.closest('fieldset').prop('disabled', false);
	disableAds.prop('checked', Boolean(data.disable_ads));
	const inContent = theRow.find('[name="advads-disable-the-content"]');
	if (inContent.length) {
		inContent
			.prop('disabled', false)
			.prop('checked', Boolean(data.disable_the_content));
	}
}

jQuery(function () {
	const editCopy = window.inlineEditPost.edit;

	// Replace the default WP function
	window.inlineEditPost.edit = function (id) {
		/* eslint-enable no-undef */
		// Call the original WP edit function.
		editCopy.apply(this, arguments);

		// Now we do our stuff.
		if ('object' === typeof id) {
			getPostData(parseInt(this.getId(id)));
		}
	};
});
