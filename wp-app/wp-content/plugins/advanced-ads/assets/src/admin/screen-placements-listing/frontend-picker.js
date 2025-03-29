/* eslint-disable no-console */
/* global localStorage */

const resetLocalStorage = () => {
	const keys = [
		'advads_frontend_action',
		'advads_frontend_element',
		'advads_frontend_picker',
		'advads_prev_url',
		'advads_frontend_pathtype',
		'advads_frontend_boundary',
		'advads_frontend_blog_id',
		'advads_frontend_starttime',
	];

	keys.forEach((key) => localStorage.removeItem(key));
	window.Advanced_Ads_Admin.set_cookie('advads_frontend_picker', '', -1);
};

export default function () {
	const storageElement = localStorage.getItem('advads_frontend_element');
	const frontendPicker = localStorage.getItem('advads_frontend_picker');

	// Set element from frontend into placement input field
	if (storageElement) {
		const pickerWrapper = document.querySelector(
			'[id="advads-frontend-element-' + frontendPicker + '"]'
		);

		pickerWrapper.querySelector('.advads-frontend-element').value =
			storageElement;

		if (
			typeof localStorage.getItem('advads_frontend_action') !==
			'undefined'
		) {
			// Auto-save the placement after selecting an element in the frontend.
			const form = pickerWrapper.closest('form');
			const formData = new FormData(form);
			formData.set('nonce', advadsglobal.ajax_nonce);
			formData.set('ID', formData.get('post_ID'));

			wp.ajax
				.post(
					'advads-update-frontend-element',
					Object.fromEntries(formData.entries())
				)
				.then(resetLocalStorage)
				.fail((r) => console.error(r));
		}
	}

	Array.from(
		document.querySelectorAll('.advads-activate-frontend-picker')
	).forEach(function (element) {
		element.addEventListener('click', function () {
			localStorage.setItem(
				'advads_frontend_picker',
				this.dataset.placementid
			);
			localStorage.setItem('advads_frontend_action', this.dataset.action);
			localStorage.setItem('advads_prev_url', window.location);
			localStorage.setItem(
				'advads_frontend_pathtype',
				this.dataset.pathtype
			);
			localStorage.setItem(
				'advads_frontend_boundary',
				this.dataset.boundary
			);
			localStorage.setItem(
				'advads_frontend_blog_id',
				window.advancedAds.siteInfo.blogId
			);
			localStorage.setItem(
				'advads_frontend_starttime',
				new Date().getTime()
			);
			window.Advanced_Ads_Admin.set_cookie(
				'advads_frontend_picker',
				this.dataset.placementid,
				null
			);

			if (this.dataset.boundary) {
				window.location =
					window.advancedAds.content_placement_picker_url;
			} else {
				window.location = window.advancedAds.siteInfo.homeUrl;
			}
		});
	});

	// allow to deactivate frontend picker
	if (frontendPicker) {
		document
			.querySelector(
				'[id="advads-frontend-element-' + frontendPicker + '"]'
			)
			.querySelector('.advads-deactivate-frontend-picker').style.display =
			'block';
	}

	Array.from(
		document.querySelectorAll('.advads-deactivate-frontend-picker')
	).forEach(function (element) {
		element.addEventListener('click', function () {
			resetLocalStorage();
			Array.from(
				document.querySelectorAll('.advads-deactivate-frontend-picker')
			).forEach(function (item) {
				item.style.display = 'none';
			});
		});
	});
}
