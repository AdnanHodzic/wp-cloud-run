import jQuery from 'jquery';

function setOptions(placementType, options) {
	if ('post_content' === placementType) {
		const paragraph = prompt(advadstxt.after_paragraph_promt, 1);
		if (paragraph !== null) {
			return { ...options, index: parseInt(paragraph, 10) };
		}
	}
	return options;
}

function sendAjaxRequest(placementType, placementId, postId, options) {
	const advadsBox = jQuery('#advads-ad-injection-box');

	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
			action: 'advads-ad-injection-content',
			placement_type: placementType,
			placement_id: placementId,
			ad_id: postId,
			options,
			nonce: advadsglobal.ajax_nonce,
		},
		success(r) {
			if (!r) {
				advadsBox.html('an error occured');
				return;
			}

			jQuery('#advads-ad-injection-box *').hide();
			jQuery(
				'#advads-ad-injection-message-placement-created, #advads-ad-injection-message-placement-created *'
			).show();
		},
		error(MLHttpRequest, textStatus, errorThrown) {
			advadsBox.html(errorThrown);
		},
	});
}

export default function () {
	const advadsBox = jQuery('#advads-ad-injection-box');
	const postId = jQuery('#post_ID').val();

	jQuery(document).on('click', '.advads-ad-injection-button', function () {
		const placementType = jQuery(this).data('placement-type');
		const placementId = jQuery(this).data('placement-id');
		let options = {};

		if (!placementType && !placementId) {
			return;
		}

		options = setOptions(placementType, options);
		sendAjaxRequest(placementType, placementId, postId, options);

		advadsBox.find('.advads-loader').show();
		advadsBox.find('.advads-ad-injection-box-placements').hide();
		jQuery('body').animate(
			{ scrollTop: advadsBox.offset().top - 40 },
			1,
			'linear'
		);
	});
}
