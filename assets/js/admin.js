'use strict';

/**
 * Stats screen
 */
const wppChartContainer = document.getElementById('wpp-chart');

if ( wppChartContainer && WPPChart.canRender() ) {
    wppChartContainer.querySelector('p').remove();
    WPPChart.init('wpp-chart');

    let updatingStats = false;

    const wppBtnStatsConfig = document.getElementById('wpp-stats-config-btn');
    const wppModals = document.querySelectorAll('.wpp-lightbox');
    const wppStatsModal = [...wppModals].filter((modal) => modal.id === 'wpp-stats-config')[0];
    const wppTimeRangeModal = [...wppModals].filter((modal) => modal.id === 'wpp-stats-range')[0];
    const wppTimeRangeOptions = document.getElementById('wpp-time-ranges');
    const wppTimeRangeUnit = document.getElementById('stats_range_time_unit');
    const wppTimeRangeQty = document.getElementById('stats_range_time_quantity');
    const wppListsContainer = document.getElementById('wpp-listing');
    const wppTabComponents = document.querySelectorAll('.wpp-tabs');

    /** Event listeners */
    if ( wppBtnStatsConfig && wppStatsModal ) {
        wppBtnStatsConfig.addEventListener('click', () => {
            closeAllModals();
            wppStatsModal.style.display = 'block';
            wppStatsModal.querySelector('input[id="stats_type"]').focus();
        });

        wppStatsModal.querySelector('.button-secondary').addEventListener('click', () => {
            closeAllModals();
            wppBtnStatsConfig.focus();
        });
    }

    wppTimeRangeOptions.querySelectorAll('li button').forEach((timerRangeBtn) => {
        timerRangeBtn.addEventListener('click', (e) => {
            const me = e.target,
                range = me.dataset.range;

            if ( ! updatingStats && 'custom' !== range ) {
                updatingStats = true;

                closeAllModals();
                wppTimeRangeModal.querySelector('#stats_range_date').value = '';

                getChartData(range)
                .then((json) => {
                    updatingStats = false;

                    if ( json ) {
                        const data = json.data;

                        resetTimeRangeBtns();
                        me.parentNode.classList.add('current');
                        me.classList.add('button-primary');

                        updateChart(data);
                        updateLists();
                    }
                });
            } else {
                closeAllModals();
                wppTimeRangeModal.style.display = 'block';
                wppTimeRangeModal.querySelector('.wpp-tabs-container button[aria-selected="true"]').focus();
            }
        });
    });

    wppTimeRangeModal.querySelectorAll('.wpp-lightbox-tabs button').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            const me = e.target;

            wppTimeRangeModal.querySelector('#stats_range_date').value = '';
            wppTimeRangeModal.querySelector('#stats_range_date').readonly = ( 'custom-time-range' === me.getAttribute('aria-controls') );
        });
    });

    if ( wppTabComponents.length ) {
        wppTabComponents.forEach((tabComponent) => {
            tabComponent.querySelectorAll('.wpp-tabs-container button').forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    const me = e.target,
                        panelID = me.getAttribute('aria-controls');

                    me.setAttribute('aria-selected', 'true');
                    me.setAttribute('tabindex', '0');
                    me.closest('.wpp-tabs').querySelector(`#${panelID}`).classList.add('active');

                    [...me.parentNode.children]
                    .filter((child) => child !== me)
                    .forEach((sibling) => {
                        sibling.setAttribute('aria-selected', 'false');
                        sibling.setAttribute('tabindex', '-1');
                        me.closest('.wpp-tabs').querySelector(`#${sibling.getAttribute('aria-controls')}`).classList.remove('active');
                    });
                });
            });
        });
    }

    // Datepicker
    const jdp = jQuery.datepicker;
    jdp._defaults.onAfterUpdate = null;
    const datepicker__updateDatepicker = jdp._updateDatepicker;

    jdp._updateDatepicker = function(instance) {
        datepicker__updateDatepicker.call(this, instance);

        const onAfterUpdate = this._get(instance, 'onAfterUpdate');

        if ( onAfterUpdate ) {
            onAfterUpdate.apply( ( instance.input ? instance.input[0] : null ), [( instance.input ? instance.input.val() : '' ), instance] );
        }
    };

    let curr = -1,
        prev = -1;

    const dp_field = jQuery(wppTimeRangeModal.querySelector('#stats_range_date'));

    const wppDatepicker = dp_field.datepicker(
        {
            maxDate: 0,
            dateFormat: 'yy-mm-dd',
            showButtonPanel: true,
            beforeShowDay: (date) => {
                return [true, ( (date.getTime() >= Math.min(prev, curr) && date.getTime() <= Math.max(prev, curr) ) ? 'date-range-selected' : '' )]
            },
            onSelect: (dateText, instance) => {
                let d1, d2;

                prev = curr;
                curr = ( new Date(instance.selectedYear, instance.selectedMonth, instance.selectedDay) ).getTime();

                if ( -1 == prev || prev == curr ) {
                    prev = curr;
                    dp_field.val( dateText );
                } else {
                    d1 = jdp.formatDate('yy-mm-dd', new Date( Math.min(prev, curr) ), {});
                    d2 = jdp.formatDate('yy-mm-dd', new Date( Math.max(prev, curr) ), {});
                    dp_field.val(d1 + ' ~ ' + d2)
                }

                dp_field.data('datepicker').inline = true;
            },
            onClose: () => {
                dp_field.data('datepicker').inline = false;
            },
            onAfterUpdate: () => {
                if ( prev > -1 && curr > -1 ) {
                    jQuery('<button type="button" class="ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all" data-handler="hide" data-event="click">OK</button>')
                    .appendTo( jQuery(".ui-datepicker-buttonpane") )
                    .on('click', () =>{
                        dp_field.datepicker('hide');
                    });
                }
            }
        }
    );

    wppTimeRangeModal.querySelector('form').addEventListener('submit', (e) => {
        e.preventDefault();

        const submitBtn = wppTimeRangeModal.querySelector('form .button-primary');

        submitBtn.disabled = true;

        getChartData('custom')
        .then((json) => {
            submitBtn.disabled = false;

            if ( json ) {
                const data = json.data;

                closeAllModals();

                resetTimeRangeBtns();

                const customTimeRangeBtn = wppTimeRangeOptions.querySelector('li button[data-range="custom"]');
                customTimeRangeBtn.parentNode.classList.add('current');
                customTimeRangeBtn.classList.remove('button-secondary');
                customTimeRangeBtn.classList.add('button-primary');
                customTimeRangeBtn.focus();

                updateChart(data);
                updateLists();
            }
        });
    });

    wppTimeRangeModal.querySelector('form .button-secondary').addEventListener('click', () => {
        closeAllModals();
        wppTimeRangeOptions.querySelector('li.current button').focus();
    });

    /** Functions */
    const resetTimeRangeBtns = () => {
        wppTimeRangeOptions.querySelectorAll('li button').forEach((timerRangeBtn) => {
            timerRangeBtn.parentNode.classList.remove('current');
            timerRangeBtn.classList.remove('button-primary');
            timerRangeBtn.classList.add('button-secondary');
        });
    };

    const closeAllModals = () => {
        wppModals.forEach((modal) => { modal.style.display = 'none'; });
    };

    const getChartData = async (range) => {
        const args = {
            action: 'wpp_update_chart',
            nonce: wpp_admin_params.nonce,
            range: range,
            time_quantity: wppTimeRangeQty.value,
            time_unit: wppTimeRangeUnit.value
        };

        if ( 'custom' === range && wppTimeRangeModal.querySelector('#stats_range_date').value ) {
            args.dates = wppTimeRangeModal.querySelector('#stats_range_date').value;
        }

        const url = ajaxurl + '?' + new URLSearchParams(args).toString();

        try {
            const response = await fetch(url);

            if ( ! response.ok ) {
                throw new Error(`Response status: ${response.status}`);
            }

            const json = await response.json();

            return json;
        } catch (error) {
            console.error(error.message);
        }
    };

    const updateChart = (data) => {
        // Update titles
        wppChartContainer.parentNode.querySelector('h4').innerHTML = data.totals.label_summary;
        wppChartContainer.parentNode.querySelector('h5').innerHTML = data.totals.label_date_range;
        // Update chart
        WPPChart.populate(data);
    };

    const updateList = (listIndex, action, items) => {
        const args = {
            action: action,
            nonce: wpp_admin_params.nonce,
            items: items
        };

        if ( wppTimeRangeModal.querySelector('#stats_range_date').value ) {
            args.dates = wppTimeRangeModal.querySelector('#stats_range_date').value;
        }

        const url = ajaxurl + '?' + new URLSearchParams(args).toString();

        wppListsContainer.querySelector(`.wpp-tabs-panel:nth-of-type(${listIndex + 1})`).innerHTML = '<span class="spinner"></span>';

        fetch(url)
        .then((response) => {
            return response.text();
        })
        .then((response) => {
            wppListsContainer.querySelector(`.wpp-tabs-panel:nth-of-type(${listIndex + 1})`).innerHTML = response;
        })
        .catch(() => {
            // handle the error
            wppListsContainer.querySelector(`.wpp-tabs-panel:nth-of-type(${listIndex + 1})`).innerHTML = '<p>Error: Could not fetch data.</p>';
        });
    };

    const updateLists = () => {
        updateList(1, 'wpp_get_most_viewed', 'most-viewed');
        updateList(2, 'wpp_get_most_commented', 'most-commented');
        updateList(3, 'wpp_get_trending', 'trending');
    };

    /** Calls / Triggers */
    // Load chart and lists for the current time range
    wppTimeRangeOptions.querySelector('li.current button').click();
}

/**
 * Tools screen
 */
const wppThumbnailSrc = document.getElementById('thumb_source');

if ( wppThumbnailSrc ) {
    const wppThumbnailCustomFieldNameRow = document.getElementById('row_custom_field');
    const wppThumbnailCustomFieldImgResizeRow = document.getElementById('row_custom_field_resize');
    const wppThumbnailUploadBtn = document.getElementById('upload_thumb_button');
    const wppThumbnailUploadSrc = document.getElementById('upload_thumb_src');
    const wppThumbnailReview = document.getElementById('thumb-review');
    const wppThumbnailResetBtn = document.getElementById('reset_thumb_button');
    const wppThumbnailCacheDeleteBtn = document.getElementById('wpp-reset-image-cache');
    const wppLogLimitDropdown = document.getElementById('log_limit');
    const wppEnableDataCacheDropdown = document.getElementById('cache');
    const wppCacheOptionsRow = document.getElementById('cache_refresh_interval');
    const wppDataSampling = document.getElementById('sampling');
    const wppSamplingRateRow = document.getElementById('sampling_rate');

    /** Event listeners */
    wppThumbnailSrc.addEventListener('change', (e) => {
        wppThumbnailCustomFieldNameRow.style.display = ( 'custom_field' === e.target.value ) ? 'table-row' : 'none';
        wppThumbnailCustomFieldImgResizeRow.style.display = ( 'custom_field' === e.target.value ) ? 'table-row' : 'none';
    });

    wppThumbnailUploadBtn.addEventListener('click', () => {
        const custom_uploader = wp.media({
            title: 'WP Popular Posts',
            library: { type: 'image' },
            button: { text: wpp_admin_params.label_media_upload_button },
            id: 'library-' + (Math.floor(Math.random() * 10) + 1),
            multiple: false
        }).on('select', () => {
            const attachment = custom_uploader.state().get('selection').first().toJSON();
            wppThumbnailUploadSrc.value = attachment.url;

            const img = new Image();
            img.onload = function() {
                wppThumbnailReview.innerHTML = '';
                wppThumbnailReview.append(this);
            }
            img.src = attachment.url;
        })
        .open();
    });

    wppThumbnailResetBtn.addEventListener('click', () => resetDefaultThumbnail());

    wppThumbnailCacheDeleteBtn.addEventListener('click', () => clearThumbnailsCache());

    wppLogLimitDropdown.addEventListener('change', (e) => {
        const me = e.target,
            siblings = me.parentNode.querySelectorAll('label, .description'),
            brTags = me.parentNode.querySelectorAll('br');

        siblings.forEach((elem) => {
            elem.style.display = ( 1 == me.value ) ? ( elem.classList.contains('description') ? 'block' : 'inline-block') : 'none';
        });
        brTags.forEach((elem) => {
            elem.style.display = ( 1 == me.value ) ? 'none' : 'block';
        });
    });

    wppEnableDataCacheDropdown.addEventListener('change', (e) => {
        wppCacheOptionsRow.querySelector('#cache_too_long').style.display = 'none';
        wppCacheOptionsRow.style.display = ( 1 == e.target.value ) ? 'table-row' : 'none';
    });

    wppCacheOptionsRow.querySelector('#cache_interval_value').addEventListener('input', (e) => {
        if ( isNaN(e.target.value) ) {
            e.target.value = 1;
        }

        if ( e.target.reportValidity() ) {
            isCachingDataForTooLong();
        }
    });

    wppCacheOptionsRow.querySelector('#cache_interval_time').addEventListener('change', () => isCachingDataForTooLong());

    wppDataSampling.addEventListener('change', (e) => {
        wppSamplingRateRow.style.display = ( '1' === e.target.value ) ? 'table-row' : 'none';
    });

    /** Functions */
    const resetDefaultThumbnail = () => {
        fetch(ajaxurl + '?action=wpp_reset_thumbnail')
        .then((response) => {
            return response.text();
        })
        .then((thumb_url) => {
            const img = new Image();
            img.onload = function() {
                wppThumbnailReview.innerHTML = '';
                wppThumbnailReview.append(this);
            }
            img.src = thumb_url;
        })
        .catch(() => {
            // handle the error
        });
    };

    const clearThumbnailsCache = () => {
        if ( confirm(wpp_admin_params.text_confirm_image_cache_reset + " \n\n"  + wpp_admin_params.text_continue) ) {
            fetch(ajaxurl, {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'wpp_clear_thumbnail',
                    token: wpp_admin_params.nonce_reset_thumbnails
                })
            })
            .then((response) => response.text())
            .then((response) => {
                let msg = '';

                switch(response) {
                    case '1':
                        msg = wpp_admin_params.text_image_cache_cleared;
                        break;
                    case '2':
                        msg = wpp_admin_params.text_image_cache_already_empty;
                        break;
                    case '4':
                        msg = wpp_admin_params.text_insufficient_permissions;
                        break;
                    default:
                        msg = wpp_admin_params.text_invalid_action;
                        break;
                }

                alert(msg);
            });
        }
    };

    const isCachingDataForTooLong = () => {
        const cacheInternalValue = parseInt(wppCacheOptionsRow.querySelector('#cache_interval_value').value, 10);
        const cacheIntervalTimeUnit = wppCacheOptionsRow.querySelector('#cache_interval_time').value;

        if (
            ('hour' === cacheIntervalTimeUnit && cacheInternalValue > 72 )
            || ('day' === cacheIntervalTimeUnit && cacheInternalValue > 3 )
            || ('week' === cacheIntervalTimeUnit && cacheInternalValue > 1 )
            || ('month' === cacheIntervalTimeUnit && cacheInternalValue >= 1 )
            || ('year' === cacheIntervalTimeUnit && cacheInternalValue >= 1 )
        ) {
            wppCacheOptionsRow.querySelector('#cache_too_long').style.display = 'block';
        } else {
            wppCacheOptionsRow.querySelector('#cache_too_long').style.display = 'none';
        }
    };
}
