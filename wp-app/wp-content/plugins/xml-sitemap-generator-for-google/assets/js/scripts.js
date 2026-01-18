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
            '<td><input type="text" name="custom_sitemap_urls[]" class="grim-input"></td>' +
            '<td><input type="datetime-local" name="custom_sitemap_lastmods[]" class="grim-input"></td>' +
            '<td><a href="#" class="remove_url"><i class="grim-icon-trash"></i></a></td>' +
            '</tr>');
    });

    /** Add Field */
    $('#add_new_url').on('click', function(e) {
        e.preventDefault();
        $('.no_urls').remove();
        $('#additional_urls').append('<tr>' +
            '<td><input type="text" name="additional_urls[]" class="grim-input"></td>' +
            '<td>' + $('#additional_priorities_selector').html() + '</td>' +
            '<td>' + $('#additional_frequencies_selector').html() + '</td>' +
            '<td><input type="datetime-local" name="additional_lastmods[]" class="grim-input"></td>' +
            '<td><a href="#" class="remove_url"><i class="grim-icon-trash"></i></a></td>' +
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
                $('.no_urls').remove();
                $('#additional_urls').append('<tr>' +
                    '<td><input type="text" name="additional_urls[]" class="grim-input" value="' + url.trim() + '"></td>' +
                    '<td>' + $('#additional_priorities_selector').html() + '</td>' +
                    '<td>' + $('#additional_frequencies_selector').html() + '</td>' +
                    '<td><input type="datetime-local" class="grim-input" name="additional_lastmods[]"></td>' +
                    '<td><a href="#" class="remove_url"><i class="grim-icon-trash"></i></a></td>' +
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
    $('.grim-expand-toggle').click(function (e) {
        e.preventDefault();
        const $btn = $(this);
        const $rows = $btn.closest('.expand').find('tbody tr');

        $btn.toggleClass('active');

        if ($btn.hasClass('active')) {
            $rows.removeClass('grim-term-hidden');
        } else {
            $rows.each(function(index){
                if(index >= 5){
                    $(this).addClass('grim-term-hidden');
                }
            });
        }

        $btn.find('span').text(
          $btn.hasClass('active') ? 'Show Less' : 'Show More'
        );
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
            open: function(event, ui) {
                let menu = $(this).autocomplete('widget');
                let currentTop = parseFloat(menu.css('top'));
                menu.css('top', currentTop + 1 + 'px');
            },
            select: function (event, ui) {
                terms = sgg_get_terms(target);

                if (terms.findIndex(el => el.value == ui.item.value) === -1) {
                    terms.unshift(ui.item);

                    let $target = $(`#${target}`).siblings('.expand');
                    $target.children('.grim-expand-toggle').removeClass('active').find('span').text('Show More');
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

    $('#sgg-indexnow').on('mouseup', function () {
        $('input[name="sgg-indexnow"]').val('check');
    });

    $('#sgg-flush-rewrite-rules').on('mouseup', function () {
        $('input[name="sgg-flush-rewrite-rules"]').val('check');
    });

    $('#sgg-clear-cache').on('mouseup', function () {
        $('input[name="sgg-clear-cache"]').val('check');
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
        let $expand = $target.siblings('.expand');
        let $tbody = $expand.find('.sgg-autocomplete-terms');

        if (terms.length < 1) {
            $expand.find('.grim-table').attr('data-count', 0)
        } else {
            $expand.find('.grim-table').attr('data-count', terms.length);
        }

        $tbody.html('');

        terms.forEach((term, index) => {
            const hiddenClass = index >= 5 ? 'grim-term-hidden' : '';

            $tbody.append(`
                <tr class="${hiddenClass}">
                    <td>${term.label}</td>
                    <td>
                        <a href="#" class="remove-term" data-value="${term.value}" data-target="${target}">
                            <i class="grim-icon-trash"></i>
                        </a>
                    </td>
                </tr>
            `);
        });

        $expand.find('.grim-expand-toggle').toggle(terms.length > 5);

        updateAllTabCounts()
    }

    function updateAllTabCounts() {
        $('.grim-section').each(function() {
            let $section = $(this);

            $section.find('.grim-exclude-tab').each(function() {
                let tabId = $(this).data('tab');
                let $panel = $section.find('#' + tabId);
                let count = $panel.find('.grim-table').attr('data-count') || 0;
                $(this).find('.grim-term-count').text(count);
            });

            let $includePanel = $section.find('#others, #google-others');
            if ($includePanel.length) {
                let count = $includePanel.find('.grim-table').attr('data-count') || 0;
                $section.find('.grim-term-count').text(count);
            }
        });
    }

    function sgg_dependency(elements, checked) {
        $(elements).attr('disabled', checked).toggleClass('dependency-disabled', checked);
    }

    $(document).on('click', '.grim-select__trigger', function () {
        let $select = $(this).closest('.grim-select');
        $('.grim-select').not($select).removeClass('open');
        $select.toggleClass('open');
    });

    $(document).on('click', '.grim-select .grim-option', function () {
        let $option = $(this);
        let $select = $option.closest('.grim-select');
        let value = $option.data('value');
        let text = $option.text();
        let $hiddenSelect = $select.next('select');

        $select.find('.grim-option').removeClass('selected');
        $option.addClass('selected');
        $select.find('.grim-select__trigger span').text(text);

        if ($hiddenSelect.length) {
            $hiddenSelect.val(value).trigger('change');
        }

        $select.removeClass('open');
    });

    $('.grim-exclude-tab-nav .grim-exclude-tab').on('click', function() {
        let tabId = $(this).data('tab');
        let $container = $(this).closest('.grim-exclude-tab-nav');

        $container.find('.grim-exclude-tab').removeClass('grim-exclude-tab--active');
        $(this).addClass('grim-exclude-tab--active');

        $container.find('.grim-exclude-tab-panel').removeClass('grim-exclude-tab-panel--active');
        $container.find('#' + tabId).addClass('grim-exclude-tab-panel--active');
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('.grim-select').length) {
            $('.grim-select').removeClass('open');
        }
    });

    $('#grim-import-file').on('change', function() {
        let fileName = this.files.length ? this.files[0].name : 'No file chosen';
        $('#grim-file-name').text(fileName);
    });

    $(".grim-btn-copied").on("click", function() {
        const targetId = $(this).data("target");
        const text = $("#" + targetId).text()
          .replace(/\t+/g, " ")
          .replace(/\s*\n\s*/g, "\n")
          .trim();
        const $btn = $(this);

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                showTooltip($btn);
            });
        } else {
            let $temp = $("<textarea>");
            $("body").append($temp);
            $temp.val(text).select();
            document.execCommand("copy");
            $temp.remove();
            showTooltip($btn);
        }
    });

    function showTooltip($btn) {
        $btn.addClass("grim-show-tooltip");
        setTimeout(() => {
            $btn.removeClass("grim-show-tooltip");
        }, 1500);
    }

    /** Settings Search Functionality */
    const $searchInput = $('#grim-settings-search-input');
    const $searchResults = $('#grim-search-results');
    const $searchResultsList = $searchResults.find('.grim-search-results-list');

    function searchSettings(query) {
        if (!query || query.length < 2) {
            $searchResults.hide();
            return;
        }
    
        const results = grimData.settingsArray.filter(setting => {
            const searchText = query.toLowerCase();
            const labelMatch = setting.label.toLowerCase().includes(searchText);
            const idMatch = setting.id.toLowerCase().includes(searchText);
            const tagsMatch = setting.tags && setting.tags.toLowerCase().includes(searchText);
            
            return labelMatch || idMatch || tagsMatch;
        });
    
        displaySearchResults(results);
    }

    function displaySearchResults(results) {
        $searchResultsList.empty();

        if (results.length === 0) {
            $searchResultsList.append(`<div class="grim-search-no-results">${grimData.noSettingsFound}</div>`);
        } else {
            results.forEach(setting => {
                const $resultItem = $(`
                    <div class="grim-search-result-item" data-setting-id="${setting.id}" data-tab="${setting.tab}">
                        <div class="grim-search-result-label">${setting.label}</div>
                        <div class="grim-search-result-tab">${getTabName(setting.tab)}</div>
                    </div>
                `);
                $searchResultsList.append($resultItem);
            });
        }

        $searchResults.show();
    }

    function getTabName(tabId) {
        const tabNames = {
            'general': 'General',
            'google-news': 'Google News',
            'image-sitemap': 'Image Sitemap',
            'video-sitemap': 'Video Sitemap',
            'advanced': 'Advanced'
        };
        return tabNames[tabId] || tabId;
    }

    $searchInput.on('input', function() {
        const query = $(this).val().trim();
        searchSettings(query);
    });

    $searchResultsList.on('click', '.grim-search-result-item', function() {
        const settingId = $(this).data('setting-id');
        const tabId = $(this).data('tab');

        switchToTab(tabId);

        setTimeout(() => {
            scrollToSetting(settingId);
        }, 300);

        $searchResults.hide();
    });


    function switchToTab(tabId) {
        const $targetTab = $(`.nav-tab-wrapper a[data-id="${tabId}"]`);
        if ($targetTab.length) {
            $tabsList.removeClass('nav-tab-active');
            $targetTab.addClass('nav-tab-active');

            $contentList.hide();

            const targetIndex = $targetTab.index();
            $contentList.eq(targetIndex).show();

            sessionStorage.setItem('sggActiveTab', tabId);
        }
    }

    function scrollToSetting(settingId) {
        const setting = grimData.settingsArray.find(s => s.id === settingId);
        if (setting) {
            let $element = $(`[data-search-id="${settingId}"]`)
            if ($element.length) {
                const $container = $element;
                if ($container.length) {
                    const elementTop = $container.offset().top;

                    let scrollTop = elementTop - 280;

                    $('html, body').animate({
                        scrollTop: scrollTop
                    }, 500);

                    $element.addClass('grim-search-highlight');
                    setTimeout(() => {
                        $element.removeClass('grim-search-highlight');
                    }, 1600);
                }
            }
        }
    }

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.grim-settings-search').length) {
            $searchResults.hide();
        }
    });

    $searchInput.on('keydown', function(e) {
        if (e.key === 'Escape') {
            $searchResults.hide();
            $(this).val('');
        }
    });

    let isSearchOpen = false;

    $('.grim-search-action-btn').on('click', function () {
        const $btn = $(this);
        $btn.css({
            transition: 'opacity 0.2s ease',
            opacity: 0
        });
        setTimeout(() => {
            $btn.hide();
        }, 200);

        isSearchOpen = true;

        $('.grim-settings-search').fadeIn(200);
    });

    $('.grim-settings-search-cancel').on('click', function () {
        const $btn = $('.grim-search-action-btn');
        $btn.css({
            transition: 'opacity 0.2s ease',
            opacity: 1
        });
        setTimeout(() => {
            $btn.show();
        }, 200);

        isSearchOpen = false;

        $('.grim-settings-search').fadeOut(200);

        $searchInput.val('');
    });

    const $nav = $('.grim-nav');
    let grimDynamicNotice = $('.grim-dynamic-notice').length || 0;
    let grimNotice = $('.grim-container .notice.is-dismissible');
    let grimNoticeLength = $(grimNotice).length || 0;
    let grimNoticeHeight = 0;

    grimNotice.each(function() {
       grimNoticeHeight = $(this).outerHeight();
    });

    const getScrollThreshold = () => {
        return 20 + (grimDynamicNotice * 160) + (grimNoticeLength * grimNoticeHeight);
    };

    let scrollThreshold = getScrollThreshold();

    $('.sgg-notice').on('click', () => {
        grimDynamicNotice--;
        scrollThreshold = getScrollThreshold();
    });

    $('.notice-dismiss').on('click', () => {
        grimNoticeLength--;
        scrollThreshold = getScrollThreshold();
    });

    let isSticky = false;

    function handleScroll() {
        const scrollTop = $(window).scrollTop();

        if (scrollTop > scrollThreshold && !isSticky) {
            $nav.removeClass('sticky-removing');
            $nav.addClass('sticky');
            isSticky = true;
            updateViewForStickyState();
        } else if (scrollTop <= scrollThreshold && isSticky) {
            $nav.addClass('sticky-removing');
            setTimeout(() => {
                $nav.removeClass('sticky sticky-removing');
                isSticky = false;
                updateViewForStickyState();
            }, );
        }
    }

    function updateViewForStickyState() {
        const $search = $('.grim-settings-search');
        const $searchBtn = $('.grim-search-action-btn');

        if ( isSearchOpen && !isSticky ) {
            $search.show();
            $searchBtn.hide();
        } else if (isSearchOpen && isSticky) {
            $searchBtn.hide();
            $search.show();
        }
    }

    $(window).on('scroll', handleScroll);
    handleScroll();
});
