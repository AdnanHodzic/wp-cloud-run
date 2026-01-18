import jQuery from 'jquery';
import apiFetch from '@wordpress/api-fetch';

/**
 * Disable inputs on a form
 *
 * @param {Node}    form     the form.
 * @param {boolean} disabled disable inputs if `true`.
 */
function disable(form, disabled) {
	if ('undefined' === typeof disabled) {
		disabled = true;
	}

	jQuery(form)
		.find('select,input,textarea')
		.add(
			`.submit-placement-form[data-id="${form.id.replace(
				'advanced-ads-placement-form-',
				''
			)}"]`
		)
		.prop('disabled', disabled);
}

/**
 * Submit edit placement form
 *
 * @param {Node} form the form to be submitted.
 */
function submitEditPlacement(form) {
	const $form = jQuery(form),
		formData = $form.serialize();
	disable(form);
	apiFetch({
		path: '/advanced-ads/v1/placement',
		method: 'PUT',
		data: {
			fields: formData,
		},
	}).then(function (response) {
		disable(form, false);

		if (response.error) {
			// Show an error message if there is a "error" field in the response
			disable(form, false);
			form.closest('dialog').close();
			window.advancedAds.notifications.addError(response.error);
			return;
		}

		const dialog = form.closest('dialog');
		dialog.advadsTermination.resetInitialValues();
		const rowTitle = jQuery(
			`#post-${response.placement_data.id} .column-name .row-title`
		);
		rowTitle.text(response.title);
		jQuery(
			`#post-${response.placement_data.id} .column-ad_group .advads-placement-item-select`
		).val(response.item);

		if (
			response.payload.post_status &&
			'draft' === response.payload.post_status
		) {
			const rowParent = rowTitle.parent();
			if (!rowParent.text().includes(advancedAds.placements.draft)) {
				rowTitle
					.parent()
					.append(
						jQuery(
							`<strong>— <span class="post-state">${advancedAds.placements.draft}</span></strong>`
						)
					);
			}
		} else {
			rowTitle.siblings().remove();
		}

		/**
		 * Allow add-on to update the table without refreshing the page if needed.
		 */
		wp.hooks.doAction('advanced-ads-placement-updated', response);

		if (response.reload) {
			// Reload the page if needed.
			// eslint-disable-next-line no-undef
			localStorage.setItem(
				'advadsUpdateMessage',
				JSON.stringify({
					type: 'success',
					message: window.advadstxt.placement_forms.updated,
				})
			);
			window.location.reload();
			return;
		}

		window.advancedAds.notifications.addSuccess(
			window.advadstxt.placement_forms.updated
		);

		dialog.close();
	});
}

/**
 * Submit create placement form
 *
 * @param {Node} form the form.
 */
function submitNewPlacement(form) {
	const dialog = form.closest('dialog');

	if ('function' === typeof window[dialog.closeValidation.function]) {
		const validForm = window[dialog.closeValidation.function](
			dialog.closeValidation.modal_id
		);
		if (!validForm) {
			return;
		}
	}

	const formData = jQuery(form).serialize();
	disable(form);
	apiFetch({
		path: '/advanced-ads/v1/placement',
		method: 'POST',
		data: {
			fields: formData,
		},
	}).then(function (response) {
		disable(form, false);
		if (response.redirectUrl) {
			window.location.href = response.redirectUrl;
		} else if (response.reload) {
			// Reload the page if needed.
			// eslint-disable-next-line no-undef
			localStorage.setItem(
				'advadsUpdateMessage',
				JSON.stringify({
					type: 'success',
					message: window.advadstxt.placement_forms.created,
				})
			);
			window.location.reload();
		}
	});
}

// Submit edit placement form
jQuery(document).on('click', '.submit-placement-edit', function () {
	submitEditPlacement(
		jQuery(`#advanced-ads-placement-form-${this.dataset.id}`)[0]
	);
});

// Submit new placement form
jQuery(document).on('click', '#submit-new-placement', function () {
	submitNewPlacement(jQuery('#advads-placements-new-form')[0]);
});

export default function () {
	// Stop normal new placement form submission.
	wp.hooks.addFilter(
		'advanced-ads-submit-modal-form',
		'advancedAds',
		function (send, form) {
			if ('advads-placements-new-form' === form.id) {
				submitNewPlacement(form);
				return false;
			}
			return send;
		}
	);

	// Stop normal edit placement form submission.
	wp.hooks.addFilter(
		'advanced-ads-submit-modal-form',
		'advancedAds',
		function (send, form) {
			if (0 === form.id.indexOf('advanced-ads-placement-form-')) {
				submitEditPlacement(form);
				return false;
			}
			return send;
		}
	);

	// Place our custom "Save and close" button to edit forms.
	jQuery('[id^="advanced-ads-placement-form-"]').each(function () {
		const id = this.id.replace('advanced-ads-placement-form-', '');
		jQuery(`#modal-placement-edit-${id}`).find('.tablenav.bottom').html(
			`<button class="button button-primary submit-placement-edit" data-id="${id}">${advadstxt.close_save}</button>` // eslint-disable-line no-undef
		);
	});

	jQuery('#modal-placement-new').find('.tablenav.bottom').html(
		`<button class="button button-primary" id="submit-new-placement">${advadstxt.save_new_placement}</button>` // eslint-disable-line no-undef
	);
}
