(function ($) {
    "use strict";
    $(function () {

        // Stats config
        $("#wpp-stats-config-btn, #wpp_stats_options .button-secondary").on("click", function(e){
            e.preventDefault();

            // Hide custom range modal box
            if ( $("#wpp-stats-range").is(":visible") ) {
                $("#wpp-stats-range").hide();
            }

            if ( $("#wpp-stats-config").is(":visible") ) {
                $("#wpp-stats-config").hide();
            }
            else{
                $("#wpp-stats-config").show();
            }

        });

        // Stats range
        $("#wpp-stats-range form").on('submit', function(e){
            e.preventDefault();

            if ( $("#wpp-stats-range").is(":visible") ) {
                $("#wpp-stats-range").hide();
            }

            get_chart_data( $("#wpp-time-ranges li a[data-range='custom']") );

            $(".wpp-lightbox-tabs li:eq(0) a").trigger("click");

        });

        $("#wpp-stats-range form .button-secondary").on('click', function(e){
            e.preventDefault();

            if ( $("#wpp-stats-range").is(":visible") ) {
                $("#wpp-stats-range").hide();
            }

            $("#stats_range_date").val('');

            $(".wpp-lightbox-tabs li:eq(0) a").trigger("click");

        });

        function get_chart_data( me ) {

            var args = {
                action: 'wpp_update_chart',
                nonce: wpp_admin_params.nonce,
                range: me.data("range"),
                time_quantity: $("#stats_range_time_quantity").val(),
                time_unit: $("#stats_range_time_unit").val()
            };

            if ( '' != $("#stats_range_date").val() ){
                args.dates = $("#stats_range_date").val();
            }

            $.get(
                ajaxurl,
                args,
                function( response ){

                    if ( 'ok' == response.status ) {

                        me.parent().addClass("current").siblings().removeClass("current");

                        var labels = [],
                            dataset_views = [],
                            dataset_comments = [];

                        for ( var date in response.data.dates ) {

                            labels.push( response.data.dates[date].nicename );

                            if ( !$.isEmptyObject( response.data.dates[date] ) ) {

                                if ( 'undefined' != typeof response.data.dates[date].views ) {
                                    dataset_views.push( response.data.dates[date].views );
                                }
                                else {
                                    dataset_views.push( 0 );
                                }

                                if ( 'undefined' != typeof response.data.dates[date].comments ) {
                                    dataset_comments.push( response.data.dates[date].comments );
                                }
                                else {
                                    dataset_comments.push( 0 );
                                }

                            }
                            else {
                                dataset_views.push( 0 );
                                dataset_comments.push( 0 );
                            }

                        }

                        // Update titles
                        $("#wpp-chart-wrapper h4").html( response.data.totals.label_summary );
                        $("#wpp-chart-wrapper h5").html( response.data.totals.label_date_range );

                        // Update chart
                        WPPChart.populate({
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Comments',
                                    data: dataset_comments,
                                },
                                {
                                    label: 'Views',
                                    data: dataset_views,
                                },
                            ]
                        });

                        $("#wpp-listing .wpp-tabbed-nav li:eq(0) a").trigger("click");

                        // Update lists
                        args = {
                            action: 'wpp_get_most_viewed',
                            nonce: wpp_admin_params.nonce
                        };

                        if ( '' != $("#stats_range_date").val() ){
                            args.dates = $("#stats_range_date").val();
                        }

                        $.get(
                            ajaxurl,
                            args,
                            function( response ){
                                $("#wpp-listing .wpp-tab-content:eq(0)").html(response);
                            }
                        );

                        args = {
                            action: 'wpp_get_most_commented',
                            nonce: wpp_admin_params.nonce
                        };

                        if ( '' != $("#stats_range_date").val() ){
                            args.dates = $("#stats_range_date").val();
                        }

                        $.get(
                            ajaxurl,
                            args,
                            function( response ){
                                $("#wpp-listing .wpp-tab-content:eq(1)").html(response);
                            }
                        );

                        // Unset date range
                        $("#stats_range_date").val('');

                    }

                }
            );

        }

        $("#wpp-time-ranges li a").on("click", function(e){
            e.preventDefault();

            var me = $(this);

            // Update chart
            if ( WPPChart.canRender() ) {

                if ( 'custom' != me.data("range") ) {
                    get_chart_data( me );
                }
                else {
                    // Hide Config modal box
                    if ( $("#wpp-stats-config").is(":visible") ) {
                        $("#wpp-stats-config").hide();
                    }

                    if ( !$("#wpp-stats-range").is(":visible") ) {
                        $("#wpp-stats-range").show();
                    }
                }

            }

        });

        $(".wpp-lightbox-tabs li a").on("click", function(e){
            e.preventDefault();

            var me = $(this);

            me.parent().addClass("active").siblings().removeClass("active");

            me.closest(".wpp-lightbox").find(".wpp-lightbox-tab-content").removeClass("wpp-lightbox-tab-content-active").filter(function( index ) {
                return me.parent().index() == index;
            }).addClass("wpp-lightbox-tab-content-active");

        });

        // Datepicker
        $.datepicker._defaults.onAfterUpdate = null;
        var datepicker__updateDatepicker = $.datepicker._updateDatepicker;

        $.datepicker._updateDatepicker = function( instance ){

            datepicker__updateDatepicker.call( this, instance );

            var onAfterUpdate = this._get( instance, 'onAfterUpdate' );

            if ( onAfterUpdate ) {
                onAfterUpdate.apply( ( instance.input ? instance.input[0] : null ), [( instance.input ? instance.input.val() : '' ), instance] );
            }
        };

        var curr = -1,
            prev = -1;

        var dp_field = $("#stats_range_date");

        var wpp_datepicker = dp_field.datepicker({
            maxDate: 0,
            dateFormat: 'yy-mm-dd',
            showButtonPanel: true,
            beforeShowDay: function(date){
                return [true, ( (date.getTime() >= Math.min(prev, curr) && date.getTime() <= Math.max(prev, curr) ) ? 'date-range-selected' : '' )]
            },
            onSelect: function(dateText, instance){

                var d1, d2;

                prev = curr;

                curr = ( new Date(instance.selectedYear, instance.selectedMonth, instance.selectedDay) ).getTime();

                if (
                    -1 == prev
                    || prev == curr
                ) {
                    prev = curr;
                    dp_field.val( dateText );
                }
                else {

                    d1 = $.datepicker.formatDate('yy-mm-dd', new Date( Math.min(prev, curr) ), {});
                    d2 = $.datepicker.formatDate('yy-mm-dd', new Date( Math.max(prev, curr) ), {});

                    dp_field.val( d1 + ' ~ ' + d2 );

                }

                $(this).data('datepicker').inline = true;

            },
            onClose: function(){
                $(this).data('datepicker').inline = false;
            },
            onAfterUpdate: function( instance ){

                var calendar = $(this);

                if (
                    prev > -1
                    && curr > -1
                ){

                    $('<button type="button" class="ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all" data-handler="hide" data-event="click">OK</button>').appendTo( $(".ui-datepicker-buttonpane") ).on('click', function(){
                        dp_field.datepicker('hide');
                    });

                }
            }
        });

        // STATISTICS TABS
        $("#wpp-listing .wpp-tabbed-nav li a").on("click", function(e){
            e.preventDefault();

            var me = $(this),
                target = me.parent().index();

            me.parent().addClass("active").siblings().removeClass("active");

            me.closest("#wpp-listing").children(".wpp-tab-content:eq(" + target + ")").addClass("wpp-tab-content-active").siblings().removeClass("wpp-tab-content-active");

        });

        // TOOLS
        // thumb source selection
        $("#thumb_source").change(function() {
            if ($(this).val() == "custom_field") {
                $("#lbl_field, #thumb_field, #row_custom_field, #row_custom_field_resize").show();
            } else {
                $("#lbl_field, #thumb_field, #row_custom_field, #row_custom_field_resize").hide();
            }
        });
        // file upload
        $('#upload_thumb_button').click(function(e) {
            e.preventDefault();

            var custom_uploader = wp.media({
                title: 'WordPress Popular Posts',
                library: { type : 'image' },
                button: { text: wpp_admin_params.label_media_upload_button },
                id: 'library-' + (Math.random() * 10),
                multiple: false
            }).on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                $('#upload_thumb_src').val( attachment.url );

                var img = new Image();
                img.onload = function() {
                    $("#thumb-review").html( this ).parent().fadeIn();
                }
                img.src = attachment.url;

            })
            .open();

        });
        // log limit
        $("#log_limit").change(function(){
            var me = $(this);

            if (me.val() == 1) {
                me.parent().children("label, .description").show();
                me.parent().children("br").hide();
            } else {
                me.parent().children("label, .description").hide();
                me.parent().children("br").show();
            }
        });
        // cache interval
        $("#cache").change(function() {
            if ($(this).val() == 1) {
                $("#cache_refresh_interval").show();
            } else {
                $("#cache_refresh_interval, #cache_too_long").hide();
            }
        });
        // interval
        $("#cache_interval_time").change(function() {
            var value = parseInt( $("#cache_interval_value").val() );
            var time = $(this).val();

            if ( time == "hour" && value > 72 ) {
                $("#cache_too_long").show();
            } else if ( time == "day" && value > 3 ) {
                $("#cache_too_long").show();
            } else if ( time == "week" && value > 1 ) {
                $("#cache_too_long").show();
            } else if ( time == "month" && value >= 1 ) {
                $("#cache_too_long").show();
            } else if ( time == "year" && value >= 1 ) {
                $("#cache_too_long").show();
            } else {
                $("#cache_too_long").hide();
            }
        });

        $("#cache_interval_value").change(function() {
            var value = parseInt( $(this).val() );
            var time = $("#cache_interval_time").val();

            if ( time == "hour" && value > 72 ) {
                $("#cache_too_long").show();
            } else if ( time == "day" && value > 3 ) {
                $("#cache_too_long").show();
            } else if ( time == "week" && value > 1 ) {
                $("#cache_too_long").show();
            } else if ( time == "month" && value >= 1 ) {
                $("#cache_too_long").show();
            } else if ( time == "year" && value >= 1 ) {
                $("#cache_too_long").show();
            } else {
                $("#cache_too_long").hide();
            }
        });
    });
}(jQuery));