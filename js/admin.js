(function ($) {
	"use strict";
	$(function () {
		
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
			var image_url = $('img',html).attr('src');
			$('#upload_thumb_src').val(image_url);
			
			var img = new Image();
			img.src = image_url;
			
			$("#thumb-review").html( img );
			$("#thumb-review").parent().show();
			
			tb_remove();			
		};
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
			
			console.log(time + " " + value);
			
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