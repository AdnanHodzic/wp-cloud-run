import jQuery from 'jquery';

/**
 * Retrieves ad data for a given ID using AJAX.
 *
 * @param {number} id - The ID of the ad.
 *
 * @return {void}
 */
function getAdData(id) {
	const adVar = `ad_json_${id}`;
	const adData = window[adVar];
	fillInputs(id, adData);
}

/**
 * Fills the input fields in the specified row with the provided data.
 *
 * @param {number} id   - The ID of the row.
 * @param {Object} data - The data to fill the input fields with.
 *
 * @return {void}
 */
function fillInputs(id, data) {
	const theRow = jQuery(`#edit-${id}`);
	theRow.find('.advads-quick-edit').prop('disabled', false);
	theRow.find('[name="debugmode"]').prop('checked', data.debug_mode);

	if (data.expiry.expires) {
		theRow.find('[name="enable_expiry"]').prop('checked', true);
		const inputs = theRow.find('.expiry-inputs').show();
		for (const key in data.expiry.expiry_date) {
			inputs.find(`[name="${key}"]`).val(data.expiry.expiry_date[key]);
		}
	}

	// Privacy module enabled.
	const privacyCheckbox = theRow.find('[name="ignore_privacy"]');
	if (privacyCheckbox.length) {
		privacyCheckbox.prop('checked', data.ignore_privacy);
	}

	// Ad label.
	const adLabel = theRow.find('[name="ad_label"]');
	if (adLabel.length) {
		adLabel.val(data.ad_label);
	}

	/**
	 * Allow add-ons to do field initialization
	 */
	wp.hooks.doAction('advanced-ads-quick-edit-fields-init', id, data);
}

export default function QuickBulkEdit() {
	/* eslint-disable no-undef */
	const editCopy = window.inlineEditPost.edit;

	// Replace the default WP function
	window.inlineEditPost.edit = function (id) {
		/* eslint-enable no-undef */
		// Call the original WP edit function.
		editCopy.apply(this, arguments);

		// Now we do our stuff.
		if ('object' === typeof id) {
			getAdData(parseInt(this.getId(id), 10));
		}
	};

	// Show/hide expiry date inputs on bulk edit.
	jQuery(document).on(
		'change',
		'.advads-bulk-edit [name="expiry_date"]',
		function () {
			const select = jQuery(this);
			select
				.closest('fieldset')
				.find('.expiry-inputs')
				.css('display', 'on' === select.val() ? 'block' : 'none');
		}
	);

	// Show/hide expiry date inputs on quick edit.
	jQuery(document).on('click', '[name="enable_expiry"]', function () {
		const checkbox = jQuery(this);
		checkbox
			.closest('fieldset')
			.find('.expiry-inputs')
			.css('display', checkbox.prop('checked') ? 'block' : 'none');
	});

	jQuery(function () {
		jQuery('.inline-edit-group select option[value="private"]').remove();
	});
}
