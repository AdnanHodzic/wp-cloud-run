jQuery(function ($) {
	const element = $('.advanced-ads-adsense-dashboard');
	const reportNeedRefresh = element.find('.report-need-refresh');

	if (reportNeedRefresh.length) {
		element.html(
			'<p style="text-align:center;"><span class="report-need-refresh spinner advads-ad-parameters-spinner advads-spinner"></span></p>'
		);

		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				nonce: Advanced_Ads_Adsense_Report_Helper.nonce,
				type: 'domain',
				filter: '',
				action: 'advads_adsense_report_refresh',
			},
			success: function (response) {
				if (response.success && response.data && response.data.html) {
					element.html(response.data.html);
				}
			},
			error: function (request, status, error) {
				console.log('Refreshing report error: ' + error);
			},
		});
	}
});
