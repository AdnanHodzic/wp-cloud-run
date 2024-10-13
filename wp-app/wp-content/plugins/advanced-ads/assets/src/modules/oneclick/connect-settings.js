import jQuery from 'jquery';

export default function () {
	const form = jQuery('#pubguru-modules');
	const wrapNotice = jQuery('#pubguru-notices');
	const headerBiddingBody = jQuery('#pubguru-header-bidding-at-body').closest(
		'div'
	);

	form.on('input', 'input:checkbox', function () {
		const checkbox = jQuery(this);
		const module = checkbox.attr('name');
		const status = checkbox.is(':checked');

		if ('header_bidding' === module) {
			headerBiddingBody.toggle(status);
		}

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

	jQuery(document).on('click', '.js-btn-backup-adstxt', function () {
		const button = jQuery(this);
		const wrap = button.closest('.notice');
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
			.always(() => {
				button.prop('disabled', false);
				button.html(button.data('text'));
			})
			.done((result) => {
				if (result.success) {
					const newNotice = jQuery(result.data);
					wrapNotice.html(newNotice);
					wrap.after(newNotice);
					wrap.remove();
					setTimeout(() => {
						newNotice.fadeOut('slow', () => {
							newNotice.html('');
						});
					}, 4000);
				} else {
					wrapNotice.html(result.data);
					wrap.after(result.data);
				}
			});
	});
}
