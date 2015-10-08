(function ($) {
	"use strict";
	$(function () {
		// ADS
		if ( $('#wpp_donate').is(':visible') ) {
			
			$.ajax({
				type: "GET",
				url: "http://cabrerahector.com/ads/get.php",
				timeout: 5000,
				dataType: "jsonp",
				success: function(results){
					if ( !$.isEmptyObject(results) ) {
						$("#wpp_advertisement").html( results.ad );
												
						setTimeout(function(){
							// Ad blocker detected :(
							if ( "none" == $("#wpp_advertisement img").css('display') ) {
								$("#wpp_advertisement").html('<h3 style="margin-top:0; font-size: 1.7em; text-align:center; line-height: 1em;">An <em>awesome</em> ad would be here...</h3><p style="font-size:1.1em; text-align:center;">... <em>if you weren\'t using an <strong>ad blocker</strong></em> :(</p><p style="font-size:0.8em; line-height: 1.4em;">Showing ads help us developers offer our services for free to everyone, so please consider disabling your ad blocker for this page.</p><p style="font-size:0.8em; line-height: 1.4em;">It won\'t be an annoying one, I promise :)</p>').show();
							}
							
							$("#wpp_advertisement").show();
						}, 250);
					}
				},
				error: function(XMLHttpRequest, textStatus, errorThrown){
					if ( window.console && window.console.log )
						window.console.log( 'Could not retrieve the ad: ' + textStatus );
				}
			});
			
		}
		
		// STATISTICS TABS		
		$("#wpp-stats-tabs a").click(function(e){
			var activeTab = $(this).attr("rel");
			$(this).removeClass("button-secondary").addClass("button-primary").siblings().removeClass("button-primary").addClass("button-secondary");
			$(".wpp-stats:visible").hide("fast", function(){
				$("#"+activeTab).slideDown("fast");
			});
			
			e.preventDefault();
		});
			
		$(".wpp-stats").each(function(){
			if ($("li", this).length == 1) {
				$("li", this).addClass("wpp-stats-last-item");
			} else {
				$("li:last", this).addClass("wpp-stats-last-item");
			}
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
			tb_show('Upload a thumbnail', 'media-upload.php?referer=wpp_admin&type=image&TB_iframe=true&post_id=0', false);
			e.preventDefault();			
		});		
		window.send_to_editor = function(html) {
			var regex = /<img[^>]+src="(http:\/\/[^">]+)"/g;
			var result = regex.exec(html);			

			if ( null != result ) {
				$('#upload_thumb_src').val(result[1]);

				var img = new Image();
				img.onload = function() {
					$("#thumb-review").html( this ).parent().fadeIn();
				}
				img.src = result[1];
			}
			
			tb_remove();			
		};
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