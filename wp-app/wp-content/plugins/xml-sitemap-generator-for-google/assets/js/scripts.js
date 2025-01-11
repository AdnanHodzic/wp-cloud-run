"use strict";

jQuery(document).ready(function ($) {
    /** Tabs */
    let activeIndex = $('.nav-tab-active').index();
    const $contentList = $('.nav-tabs-content .section'),
        $tabsList = $('.nav-tab-wrapper a'),
        $importSettings = $('#import-settings'),
        $importSettingsInput = $('input[name="import_settings"]');

    const savedTab = sessionStorage.getItem('sggActiveTab');

    if (savedTab && $(`.nav-tab-wrapper a[data-id="${savedTab}"]`).length > 0) {
        activeIndex = $(`.nav-tab-wrapper a[data-id="${savedTab}"]`).index();
    }

    $tabsList.removeClass('nav-tab-active');
    $tabsList.eq(activeIndex).addClass('nav-tab-active');
    $contentList.hide().eq(activeIndex).show();

    $('.nav-tab-wrapper').on('click', 'a', function (e) {
        e.preventDefault();

        let $current = $(e.currentTarget),
            index = $current.index(),
            id = $current.data('id');

        $tabsList.removeClass('nav-tab-active');
        $current.addClass('nav-tab-active');
        $contentList.hide().eq(index).show();
        sessionStorage.setItem('sggActiveTab', id);
    });

    /** Dependency */
    $('.has-dependency').click(function () {
        if (this.type === 'radio') {
            $(`input[name="${this.name}"]`).each(function () {
                sgg_dependency(`.${$(this).data('target')}`, !this.checked);
            });
        } else {
            sgg_dependency(`.${$(this).data('target')}`, !this.checked);
        }
    }).each(function () {
        if (this.type === 'radio') {
            $(`input[name="${this.name}"]`).each(function () {
                sgg_dependency(`.${$(this).data('target')}`, !this.checked);
            });
        } else {
            sgg_dependency(`.${$(this).data('target')}`, !this.checked);
        }
    });

     /** Add Custom Sitemap */
     $('#add_sitemap_url').on('click', function(e) {
        e.preventDefault();
        $('.no_urls').remove();
        $('#custom_sitemaps').append('<tr>' +
            '<td><input type="text" name="custom_sitemap_urls[]"></td>' +
            '<td><input type="datetime-local" name="custom_sitemap_lastmods[]"></td>' +
            '<td><a href="#" class="remove_url">x</a></td>' +
            '</tr>');
    });

    /** Add Field */
    $('#add_new_url').on('click', function(e) {
        e.preventDefault();
        $('.no_urls').remove();
        $('#additional_urls').append('<tr>' +
            '<td><input type="text" name="additional_urls[]"></td>' +
            '<td>' + $('#additional_priorities_selector').html() + '</td>' +
            '<td>' + $('#additional_frequencies_selector').html() + '</td>' +
            '<td><input type="datetime-local" name="additional_lastmods[]"></td>' +
            '<td><a href="#" class="remove_url">x</a></td>' +
            '</tr>');
    });

    /** Add Bulk URLs */
    $('#add_bulk_urls').on('click', function(e) {
        e.preventDefault();
        $('.add-bulk-urls-section').removeClass('hidden');
    });

    $('#run_add_bulk_urls').on('click', function(e) {
        e.preventDefault();
        const $bulk_urls = $('#bulk_urls');

        // Add URLs
        $bulk_urls.val().split('\n').forEach(url => {
            if (url.trim() !== '') {
                $('#additional_urls').append('<tr>' +
                    '<td><input type="text" name="additional_urls[]" value="' + url.trim() + '"></td>' +
                    '<td>' + $('#additional_priorities_selector').html() + '</td>' +
                    '<td>' + $('#additional_frequencies_selector').html() + '</td>' +
                    '<td><input type="datetime-local" name="additional_lastmods[]"></td>' +
                    '<td><a href="#" class="remove_url">x</a></td>' +
                    '</tr>');
            }
        });

        $bulk_urls.val('');

        $('.add-bulk-urls-section').addClass('hidden');
    });

    $('#cancel_add_bulk_urls').on('click', function(e) {
        e.preventDefault();
        $('#bulk_urls').val('');
        $('.add-bulk-urls-section').addClass('hidden');
    });

    /** Remove Field */
    $(document).on('click', '.remove_url', function(e) {
        e.preventDefault();
        $(this).closest('tr').remove();
    })

    /** Expand */
    $('.expand-toggle').click(function (e) {
        e.preventDefault();
        $(this).toggleClass('active');
        $(this).siblings('ul').toggleClass('active');
        $(this).html($(this).hasClass('active') ? 'Show Less &#9650;' : 'Show More &#9660;');
    });

    /** Autocomplete */
    $('.sgg-autocomplete').each(function() {
        let $el = $(this);
        let target = $el.data('target');
        let type = $el.data('type');
        let terms = sgg_get_terms(target);

        sgg_render_terms(terms, target);

        $el.autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: sgg.ajax_url,
                    method: 'post',
                    dataType: 'json',
                    data: {
                        action: 'sgg_autocomplete_search',
                        term: request.term,
                        type
                    },
                    success: function (res) {
                        if (res?.success) {
                            response(res?.data);
                        } else {
                            response([{
                                label: res?.message,
                                value: 'false'
                            }])
                        }
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                terms = sgg_get_terms(target);

                if (terms.findIndex(el => el.value == ui.item.value) === -1) {
                    terms.unshift(ui.item);

                    let $target = $(`#${target}`).siblings('.expand');
                    $target.children('.expand-toggle').addClass('active').html('Show Less &#9650;' );
                    $target.children('ul').addClass('active');
                }

                sgg_update_terms(terms, target);

                this.value = '';
                return false;
            }
        }).data('ui-autocomplete')._renderItem = function (ul, item) {
            if (item.value === 'false') {
                return $('<li class="ui-state-disabled">' + item.label + '</li>').appendTo(ul);
            } else {
                return $('<li>').append(item.label).appendTo(ul);
            }
        };
    });

    /** Remove Term */
    $(document).on('click', '.sgg-autocomplete-terms .remove-term', function (e) {
        e.preventDefault();
        let termValue = $(this).data('value');
        let target = $(this).data('target');
        let terms = sgg_get_terms(target);

        if (termValue) {
            terms = terms.filter(el => el.value != termValue)

            sgg_update_terms(terms, target);
        }
    });

    /** Form Actions */
    $('#change-indexnow-key').on('mouseup', function () {
        $('input[name="change_indexnow_key"]').val('change');
    });

    $('#clear-sitemap-cache').on('mouseup', function () {
        $('input[name="clear_cache"]').val('clear');
    });

    $importSettings.on('mouseup', function () {
        $importSettingsInput.val('import');
    });

    $importSettings.on('click', function (e) {
        if ($importSettingsInput.val().trim() !== '' && !confirm('Your current Settings will be replaced with importing values. Would you like to continue?')) {
            e.preventDefault();
            $importSettingsInput.val('');
        }
    });

    $('#youtube-check-api-key').on('mouseup', function () {
        $('input[name="youtube_check_api_key"]').val('check');
    });

    $('#vimeo-check-api-key').on('mouseup', function () {
        $('input[name="vimeo_check_api_key"]').val('check');
    });

    $('#clear-video-api-cache').on('mouseup', function () {
        $('input[name="clear_video_api_cache"]').val('clear');
    });

    function sgg_get_terms(target) {
        let selector = $(`#${target}`)

        return JSON.parse(!selector.val() ? '[]' : selector.val());
    }

    function sgg_update_terms(terms, target) {
        $(`#${target}`).val(JSON.stringify(terms));

        sgg_render_terms(terms, target);
    }

    function sgg_render_terms(terms, target) {
        let $target = $(`#${target}`);
        $target.siblings('.expand').children('.sgg-autocomplete-terms').html('');

        terms.forEach(term => {
            $target.siblings('.expand').children('.sgg-autocomplete-terms')
                .append(`<li>${term.label} <a href="#" class="remove-term" data-value="${term.value}" data-target="${target}">x</a></li>`)
        });

        $target.siblings('.expand').children('.expand-toggle').toggle(terms.length > 3);
    }

    function sgg_dependency(elements, checked) {
        $(elements).attr('disabled', checked).toggleClass('dependency-disabled', checked);
    }
});