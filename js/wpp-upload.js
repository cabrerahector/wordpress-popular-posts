jQuery(document).ready(function($) {
	
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
	
});