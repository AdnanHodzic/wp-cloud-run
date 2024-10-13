import jQuery from 'jquery';

export default function () {
	const form = jQuery('#pubguru-modules');
	const wrapNotice = jQuery('#pubguru-notices');

	form.on('input', 'input:checkbox', function () {
		const checkbox = jQuery(this);
		const module = checkbox.attr('name');
		const status = checkbox.is(':checked');

		jQuery
			.ajax({
				url: form.attr('action'),
				method: 'POST',
				data: {
					action: 'pubguru_module_change',
					security: form.data('security'),
					module,
					status,
				},
			})
			.done((result) => {
				const {
					data: { notice = '' },
				} = result;

				wrapNotice.html('');

				if ('' !== notice) {
					wrapNotice.html(notice);
				}
			});
	});

	wrapNotice.on('click', '.js-btn-backup-adstxt', function () {
		const button = jQuery(this);
		button.prop('disabled', true);
		button.html(button.data('loading'));

		jQuery
			.ajax({
				url: form.attr('action'),
				method: 'POST',
				data: {
					action: 'pubguru_backup_ads_txt',
					security: button.data('security'),
				},
			})
			.done((result) => {
				if (result.success) {
					button.html(button.data('done'));
					setTimeout(() => {
						wrapNotice.fadeOut('slow', () => {
							wrapNotice.html('');
						});
					}, 4000);
				} else {
					button.html(button.data('text'));
				}
			})
			.fail(() => {
				button.html(button.data('text'));
			});
	});
}
