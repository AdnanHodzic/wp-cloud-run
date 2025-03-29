import jQuery from 'jquery';

export default function () {
	jQuery('.js-update-placement-item').on('change', function () {
		const select = jQuery(this);
		const wrap = select.parent();
		const spinner = wrap.find('.advads-loader');
		const errorMessage = wrap.find('.advads-error');

		select.prop('disabled', true);
		spinner.removeClass('hidden');

		jQuery
			.ajax({
				type: 'POST',
				url: advancedAds.endpoints.ajaxUrl,
				data: {
					action: 'advads-placement-update-item',
					placement_id: select.data('placement-id'),
					item_id: select.val(),
				},
			})
			.always(function () {
				select.prop('disabled', false);
				spinner.addClass('hidden');
			})
			.fail(function (response) {
				window.advancedAds.notifications.addError(
					response.responseJSON.data.message
				);
			})
			.done(function (response) {
				const { data } = response;
				const success = wrap.find('.advads-success-message');
				const modalForm = jQuery(
					'#advanced-ads-placement-form-' + data.placement_id
				);

				const editLinks = [
					wrap.find('.advads-placement-item-edit'),
					modalForm.find('.advads-placement-item-edit'),
				];

				editLinks.forEach(function (link) {
					link.attr('href', data.edit_href);
					link.css(
						'display',
						data.edit_href === '' ? 'none' : 'inline'
					);
				});

				modalForm
					.find('.advads-placement-item-select')
					.val(data.item_id);

				window.advancedAds.notifications.addSuccess(
					window.advadstxt.placement_forms.updated
				);
			});
	});
}
