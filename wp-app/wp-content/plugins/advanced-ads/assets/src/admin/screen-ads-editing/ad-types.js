/* global AdvancedAdsAdmin */
import jQuery from 'jquery';
import Constants from '../../constants';

const adTypesList = jQuery('#advanced-ad-type');
const parametersBox = jQuery('#advanced-ads-ad-parameters');
const tinyMceWrapper = jQuery('#advanced-ads-tinymce-wrapper');

function loadAdTypeParameter(adType) {
	adTypesList.addClass('is-list-disabled');
	parametersBox.html(Constants.spinnerHTML);
	tinyMceWrapper.hide();

	let firstLoad = true;

	jQuery
		.ajax({
			type: 'POST',
			url: advancedAds.endpoints.ajaxUrl,
			data: {
				action: 'load_ad_parameters_metabox',
				ad_type: adType,
				ad_id: jQuery('#post_ID').val(),
				nonce: advadsglobal.ajax_nonce,
			},
		})
		.done((data) => {
			if (data) {
				parametersBox.html(data).trigger('paramloaded');
				// eslint-disable-next-line no-undef
				advads_maybe_textarea_to_tinymce(adType);
				if (firstLoad) {
					firstLoad = false;
					setTimeout(() => {
						advancedAds.termination.resetInitialValues();
					}, 500);
				}
			}
		})
		.fail((MLHttpRequest, textStatus, errorThrown) => {
			parametersBox.html(errorThrown);
		})
		.always(() => {
			adTypesList.removeClass('is-list-disabled');
		});
}

function adsenseSubscribeNotice() {
	jQuery('input[name="advanced_ad[type]"]').on('change', function () {
		const isAdsense = jQuery(this).val() === 'adsense';
		jQuery('.advads-notice-adsense')
			.toggleClass('block-important', isAdsense)
			.toggleClass('!hidden', !isAdsense);
	});
}

export default function () {
	const inputs = jQuery('#advanced-ad-type input');
	const metaboxHeadingElem = jQuery('#ad-types-box h2');
	const metaboxTitle = metaboxHeadingElem.text();

	inputs.on('change', () => {
		AdvancedAdsAdmin.AdImporter.onChangedAdType();
		const selected = inputs.filter(':checked');
		const selectedText = selected.next('label').text();
		metaboxHeadingElem.html(metaboxTitle + ': ' + selectedText);

		loadAdTypeParameter(selected.val());
	});

	inputs.eq(0).trigger('change');

	adsenseSubscribeNotice();
}
