import jQuery from 'jquery';

function createNotice(message, type, fadeAway = false) {
	const notice = jQuery(
		`<div class="notice notice-${type} is-dismissible" />`
	);
	notice.html(
		'<p>' +
			message +
			'</p>' +
			'<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>'
	);

	jQuery('#pubguru-notices').append(notice);

	if (fadeAway) {
		setTimeout(() => {
			notice.fadeOut(500, () => {
				notice.remove();
			});
		}, 3000);
	}
}

export default function () {
	const connectWrap = jQuery('.js-pubguru-connect');
	const disconnectWrap = jQuery('.js-pubguru-disconnect');
	const metabox = jQuery('#advads-m2-connect');
	const connectButton = jQuery('.js-pubguru-connecting');
	const spinner = connectButton.next('.aa-spinner');
	const contentConnected = jQuery('.pubguru-connected');
	const contentNotConnected = jQuery('.pubguru-not-connected');

	// Show consent box.
	connectWrap.on('click', '.button', function (event) {
		event.preventDefault();
		metabox.show();
	});

	jQuery('#m2-connect-consent').on('change', function () {
		const checkbox = jQuery(this);
		connectButton.prop('disabled', !checkbox.is(':checked'));
	});

	connectButton.on('click', function (event) {
		event.preventDefault();

		spinner.addClass('show');
		jQuery
			.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'pubguru_connect',
					nonce: advadsglobal.ajax_nonce,
				},
				dataType: 'json',
			})
			.done(function (response) {
				if (!response.success) {
					return;
				}

				contentNotConnected.addClass('hidden');
				connectWrap.addClass('hidden');
				contentConnected.removeClass('hidden');
				disconnectWrap.removeClass('hidden');

				jQuery('.pg-tc-trail').toggle(!response.data.hasTrafficCop);
				jQuery('.pg-tc-install').toggle(response.data.hasTrafficCop);
				createNotice(response.data.message, 'success');
			})
			.fail(function (jqXHR) {
				const response = jqXHR.responseJSON;
				createNotice(response.data, 'error');
			})
			.complete(() => spinner.removeClass('show'));
	});

	disconnectWrap.on('click', '.button', function (event) {
		event.preventDefault();

		metabox.hide();
		contentNotConnected.removeClass('hidden');
		connectWrap.removeClass('hidden');
		contentConnected.addClass('hidden');
		disconnectWrap.addClass('hidden');

		jQuery
			.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'pubguru_disconnect',
					nonce: advadsglobal.ajax_nonce,
				},
				dataType: 'json',
			})
			.done(function (response) {
				if (!response.success) {
					return;
				}

				createNotice(response.data.message, 'success', true);
			});
	});
}
