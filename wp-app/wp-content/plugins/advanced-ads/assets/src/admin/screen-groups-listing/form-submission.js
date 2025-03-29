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

	if (!form.useableInputs) {
		form.useableInputs = jQuery(form)
			.closest('dialog')
			.find('select,input,textarea,button,a.button')
			.not(':disabled');
	}

	form.useableInputs.prop('disabled', disabled);
}

/**
 * Edit group form
 *
 * @param {Node} form the form node.
 */
function submitUpdateGroup(form) {
	const $form = jQuery(form),
		formData = $form.serialize();

	disable(form);
	apiFetch({
		path: '/advanced-ads/v1/group',
		method: 'PUT',
		data: {
			fields: formData,
		},
	}).then(function (response) {
		if (response.error) {
			// Show an error message if there is an "error" field in the response
			disable(form, false);
			form.closest('dialog').close();
			window.advancedAds.notifications.addError(response.error);
			return;
		}

		const dialog = form.closest('dialog');
		dialog.advadsTermination.resetInitialValues();

		if (response.reload) {
			// Reload the page if needed.
			localStorage.setItem(
				'advadsUpdateMessage',
				JSON.stringify({
					type: 'success',
					message: window.advadstxt.group_forms.updated,
				})
			);
			window.location.reload();
			return;
		}

		window.advancedAds.notifications.addSuccess(
			window.advadstxt.group_forms.updated
		);

		dialog.close();
	});
}

/**
 * Create new group
 *
 * @param {Node} form the form.
 */
function submitNewGroup(form) {
	const $form = jQuery(form),
		formData = $form.serialize();
	disable(form);
	apiFetch({
		path: '/advanced-ads/v1/group',
		method: 'POST',
		data: {
			fields: formData,
		},
	}).then(function (response) {
		if (response.error) {
			// Show an error message if there is an "error" field in the response
			disable(form, false);
			form.closest('dialog').close();
			window.advancedAds.notifications.addError(response.error);
			return;
		}

		const dialog = form.closest('dialog');
		dialog.advadsTermination.resetInitialValues();
		document.location.href = `#modal-group-edit-${response.group_data.id}`;
		localStorage.setItem(
			'advadsUpdateMessage',
			JSON.stringify({
				type: 'success',
				message: window.advadstxt.group_forms.save_new,
			})
		);
		document.location.reload();
	});
}

export default function () {
	// Stop create group form submission.
	wp.hooks.addFilter(
		'advanced-ads-submit-modal-form',
		'advancedAds',
		function (send, form) {
			if ('advads-group-new-form' === form.id) {
				submitNewGroup(form);
				return false;
			}
			return send;
		}
	);

	// Stop edit group form submission.
	wp.hooks.addFilter(
		'advanced-ads-submit-modal-form',
		'advancedAds',
		function (send, form) {
			if ('update-group' === form.name) {
				submitUpdateGroup(form);
				return false;
			}
			return send;
		}
	);

	// Add custom submit button for each edit group form.
	jQuery('[id^="modal-group-edit-"]').each(function () {
		jQuery(this)
			.find('.tablenav.bottom')
			.html(
				`<button class="button button-primary submit-edit-group">${window.advadstxt.group_forms.save}</button>`
			);
	});

	// Add custom submit button for the create group form.
	jQuery('#modal-group-new')
		.find('.tablenav.bottom')
		.html(
			`<button class="button button-primary" id="submit-new-group">${window.advadstxt.group_forms.save_new}</button>`
		);

	// Click on custom submit button of an edit group form.
	jQuery(document).on('click', '.submit-edit-group', function () {
		submitUpdateGroup(jQuery(this).closest('dialog').find('form')[0]);
	});

	// Click on the submit button for the create group form.
	jQuery(document).on('click', '#submit-new-group', function () {
		const $form = jQuery('#advads-group-new-form'),
			validation = $form.closest('dialog')[0].closeValidation;
		if (!window[validation.function](validation.modal_id)) {
			return;
		}
		submitNewGroup($form[0]);
	});

	// Click on the delete group link.
	jQuery(document).on(
		'click',
		'#advads-ad-group-list .delete-tag',
		function (event) {
			event.preventDefault();

			// Confirm deletion dialog.
			if (
				// eslint-disable-next-line no-alert,no-undef
				!confirm(
					window.advadstxt.group_forms.confirmation.replace(
						'%s',
						jQuery(this)
							.closest('div')
							.siblings('.advads-table-name')
							.find('a')
							.text()
					)
				)
			) {
				return;
			}

			const queryVars = new URLSearchParams(jQuery(this).attr('href')),
				tr = jQuery(this).closest('tr');

			apiFetch({
				path: '/advanced-ads/v1/group',
				method: 'DELETE',
				data: {
					id: queryVars.get('group_id'),
					nonce: queryVars.get('_wpnonce'),
				},
			}).then(function (response) {
				if (response.done) {
					tr.remove();
					window.advancedAds.notifications.addSuccess(
						window.advadstxt.group_forms.deleted
					);
				}
			});
		}
	);
}
