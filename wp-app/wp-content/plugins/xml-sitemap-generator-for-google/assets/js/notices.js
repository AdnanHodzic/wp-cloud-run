"use strict";

jQuery(document).ready(function ($) {
    $( document ).on( 'click', '.notice.is-dismissible .notice-dismiss, .sgg-notice', function(e) {
        if ($(this).attr('href') === '#') {
            e.preventDefault();
        }

        let $notice = $(this).closest('.notice');
        let noticeId = $notice.attr('data-notice');

        if (!['sgg_rate', 'sgg_buy_pro'].includes(noticeId)) {
            return;
        }

        $.ajax({
            url: sggNotice.ajax_url,
            method: 'post',
            dataType: 'json',
            data: {
                action: 'sgg_disable_notice',
                nonce: sggNotice.nonce,
                notice: noticeId
            }
        });

        $notice.fadeOut();
    });
});