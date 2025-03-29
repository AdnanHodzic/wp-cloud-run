import jQuery from 'jquery';

function createNotice(message, type, after, fadeAway = false) {
	const notice = jQuery(
		`<div class="notice notice-${type} is-dismissible" />`
	);
	notice.html(
		'<div class="py-3">' + message + '</div>'
	);
	after.after(notice);

	if (fadeAway) {
		setTimeout(() => {
			notice.fadeOut(500, () => {
				notice.remove();
			});
		}, 3000);
	}
	jQuery(document).trigger( 'wp-notice-added');
}

export default function () {
	jQuery(document).on('click', '.js-btn-backup-adstxt', function () {
		const button = jQuery(this);
		const wrap = button.closest('.notice');
		button.prop('disabled', true);
		button.html(button.data('loading'));

		jQuery
			.ajax({
				url: advancedAds.endpoints.ajaxUrl,
				method: 'POST',
				data: {
					action: 'pubguru_backup_ads_txt',
					security: button.data('security'),
				},
			})
			.always(() => {
				button.prop('disabled', false);
				button.html(button.data('text'));
			})
			.done((result) => {
				if (result.success) {
					createNotice(result.data, 'success', wrap, true);
					wrap.remove();
				} else {
					createNotice(result.data, 'error', wrap);
					wrap.remove();
				}
			});
	});
}
