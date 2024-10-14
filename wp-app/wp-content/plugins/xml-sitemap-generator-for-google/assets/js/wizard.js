"use strict";
jQuery(document).ready(function($) {
    const stepMenus = $('[class^="wizard-step-menu"]');
    const formSteps = $('[class^="wizard-form-step-"]');
    const formSubmitBtn = $('.wizard-btn');
    const formBackBtn = $('.wizard-back-btn');

    let currentStep = 0;

    updateStep(currentStep);

    $(document).on('change', '.sitemap-cache-toggle input', function() {
        $('.sitemap-cache').attr('disabled', !$(this).is(':checked'));
    });

    formSubmitBtn.on("click", function(event) {
        event.preventDefault();

        if (currentStep < formSteps.length - 1) {
            currentStep++;
            updateStep(currentStep);
        } else {
            $('.wizard-form-btn-wrapper').addClass('loading');

            let data = new FormData();
            data.append('action', 'save_wizard_settings');
            data.append('nonce', sggWizard.nonce);

            $('#wizard-form').serializeArray().forEach(function(item) {
                data.append(item.name, item.value);
            });

            $.ajax({
                url: sggWizard.ajax_url,
                type: 'POST',
                data: data,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.redirect;
                    } else {
                        console.log(response);
                    }
                    $('.wizard-form-btn-wrapper').removeClass('loading');
                }
            });
        }
    });

    formBackBtn.on("click", function(event) {
        event.preventDefault();

        if (currentStep > 0) {
            currentStep--;
            updateStep(currentStep);
        }
    });

    function updateStep(stepIndex) {
        formSteps.removeClass('active');
        formSteps.eq(stepIndex).addClass('active');

        stepMenus.removeClass('active');
        stepMenus.eq(stepIndex).addClass('active');

        stepMenus.slice(0, stepIndex).addClass('completed');
        stepMenus.slice(stepIndex).removeClass('completed');

        if (stepIndex === 0) {
            formBackBtn.removeClass('active');
        } else {
            formBackBtn.addClass('active');
        }

        if (stepIndex === formSteps.length - 1) {
            formSubmitBtn.text(sggWizard.finish);
        } else {
            formSubmitBtn.text(sggWizard.continue);
        }
    }
});
