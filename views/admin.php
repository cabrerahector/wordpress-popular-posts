<?php
if (basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__))
	exit('Please do not load this page directly');

define('WPP_ADMIN', true);

// Set active tab
if ( isset($_GET['tab']) )
	$current = $_GET['tab'];
else
	$current = 'stats';

// Update options on form submission
if ( isset($_POST['section']) ) {
	
	if ( "stats" == $_POST['section'] ) {		
		$current = 'stats';
		
		$this->user_settings['stats']['order_by'] = $_POST['stats_order'];
		$this->user_settings['stats']['limit'] = (is_numeric($_POST['stats_limit']) && $_POST['stats_limit'] > 0) ? $_POST['stats_limit'] : 10;
		$this->user_settings['stats']['post_type'] = empty($_POST['stats_type']) ? "post,page" : $_POST['stats_type'];
		$this->user_settings['stats']['freshness'] = empty($_POST['stats_freshness']) ? false : $_POST['stats_freshness'];
		
		update_site_option('wpp_settings_config', $this->user_settings);			
		echo "<div class=\"updated\"><p><strong>" . __('Settings saved.', $this->plugin_slug ) . "</strong></p></div>";		
	}
	elseif ( "misc" == $_POST['section'] ) {		
		$current = 'tools';
			
		$this->user_settings['tools']['link']['target'] = $_POST['link_target'];		
		$this->user_settings['tools']['css'] = $_POST['css'];
		
		update_site_option('wpp_settings_config', $this->user_settings);
		echo "<div class=\"updated\"><p><strong>" . __('Settings saved.', $this->plugin_slug ) . "</strong></p></div>";		
	}	
	elseif ( "thumb" == $_POST['section'] ) {		
		$current = 'tools';
		
		if ($_POST['thumb_source'] == "custom_field" && (!isset($_POST['thumb_field']) || empty($_POST['thumb_field']))) {
			echo '<div id="wpp-message" class="error fade"><p>'.__('Please provide the name of your custom field.', $this->plugin_slug).'</p></div>';
		} else {				
			$this->user_settings['tools']['thumbnail']['source'] = $_POST['thumb_source'];
			$this->user_settings['tools']['thumbnail']['field'] = ( !empty( $_POST['thumb_field']) ) ? $_POST['thumb_field'] : "wpp_thumbnail";
			$this->user_settings['tools']['thumbnail']['default'] = ( !empty( $_POST['upload_thumb_src']) ) ? $_POST['upload_thumb_src'] : "";
			$this->user_settings['tools']['thumbnail']['resize'] = $_POST['thumb_field_resize'];
			
			update_site_option('wpp_settings_config', $this->user_settings);				
			echo "<div class=\"updated\"><p><strong>" . __('Settings saved.', $this->plugin_slug ) . "</strong></p></div>";
		}
	}
	elseif ( "data" == $_POST['section'] ) {		
		$current = 'tools';
		
		$this->user_settings['tools']['log']['level'] = $_POST['log_option'];
		$this->user_settings['tools']['ajax'] = $_POST['ajax'];
		
		// if any of the caching settings was updated, destroy all transients created by the plugin
		if ( $this->user_settings['tools']['cache']['active'] != $_POST['cache'] || $this->user_settings['tools']['cache']['interval']['time'] != $_POST['cache_interval_time'] || $this->user_settings['tools']['cache']['interval']['value'] != $_POST['cache_interval_value'] ) {
			$this->__flush_transients();
		}
		
		$this->user_settings['tools']['cache']['active'] = $_POST['cache'];			
		$this->user_settings['tools']['cache']['interval']['time'] = $_POST['cache_interval_time'];
		$this->user_settings['tools']['cache']['interval']['value'] = ( isset($_POST['cache_interval_value']) && is_numeric($_POST['cache_interval_value']) && $_POST['cache_interval_value'] > 0 ) 
		  ? $_POST['cache_interval_value']
		  : 1;
		
		$this->user_settings['tools']['sampling']['active'] = $_POST['sampling'];			
		$this->user_settings['tools']['sampling']['rate'] = ( isset($_POST['sample_rate']) && is_numeric($_POST['sample_rate']) && $_POST['sample_rate'] > 0 ) 
		  ? $_POST['sample_rate']
		  : 100;
		
		update_site_option('wpp_settings_config', $this->user_settings);
		echo "<div class=\"updated\"><p><strong>" . __('Settings saved.', $this->plugin_slug ) . "</strong></p></div>";		
	}
		
}

if ( $this->user_settings['tools']['css'] && !file_exists( get_stylesheet_directory() . '/wpp.css' ) ) {
	echo '<div id="wpp-message" class="error fade"><p>'. __('Any changes made to WPP\'s default stylesheet will be lost after every plugin update. In order to prevent this from happening, please copy the wpp.css file (located at wp-content/plugins/wordpress-popular-posts/style) into your theme\'s directory', $this->plugin_slug) .'.</p></div>';
}

$rand = md5(uniqid(rand(), true));	
$wpp_rand = get_site_option("wpp_rand");	
if (empty($wpp_rand)) {
	add_site_option("wpp_rand", $rand);
} else {
	update_site_option("wpp_rand", $rand);
}

?>
<script type="text/javascript">
	// TOOLS
	function confirm_reset_cache() {
		if (confirm("<?php _e("This operation will delete all entries from WordPress Popular Posts' cache table and cannot be undone.", $this->plugin_slug); ?> \n\n" + "<?php _e("Do you want to continue?", $this->plugin_slug); ?>")) {
			jQuery.post(ajaxurl, {action: 'wpp_clear_data', token: '<?php echo get_site_option("wpp_rand"); ?>', clear: 'cache'}, function(data){
				alert(data);
			});
		}
	}
	
	function confirm_reset_all() {
		if (confirm("<?php _e("This operation will delete all stored info from WordPress Popular Posts' data tables and cannot be undone.", $this->plugin_slug); ?> \n\n" + "<?php _e("Do you want to continue?", $this->plugin_slug); ?>")) {
			jQuery.post(ajaxurl, {action: 'wpp_clear_data', token: '<?php echo get_site_option("wpp_rand"); ?>', clear: 'all'}, function(data){
				alert(data);
			});
		}
	}
	
	function confirm_clear_image_cache() {
		if (confirm("<?php _e("This operation will delete all cached thumbnails and cannot be undone.", $this->plugin_slug); ?> \n\n" + "<?php _e("Do you want to continue?", $this->plugin_slug); ?>")) {
			jQuery.post(ajaxurl, {action: 'wpp_clear_thumbnail', token: '<?php echo get_site_option("wpp_rand"); ?>'}, function(data){
				alert(data);
			});
		}
	}
	
	jQuery(document).ready(function($){
		<?php if ( "params" != $current ) : ?>
		$('.wpp_boxes:visible').css({
			display: 'inline',
			float: 'left'
		}).width( $('.wpp_boxes:visible').parent().width() - $('.wpp_box').outerWidth() - 15 );
		
		$(window).on('resize', function(){
			$('.wpp_boxes:visible').css({
				display: 'inline',
				float: 'left'
			}).width( $('.wpp_boxes:visible').parent().width() - $('.wpp_box').outerWidth() - 15 );
		});
		<?php else: ?>
		$('.wpp_box').hide();
		<?php endif; ?>
	});
</script>

<div class="wrap">
    <div id="icon-options-general" class="icon32"><br /></div>
    <h2>WordPress Popular Posts</h2>
    
    <h2 class="nav-tab-wrapper">
    <?php
    // build tabs    
    $tabs = array( 
        'stats' => __('Stats', $this->plugin_slug),
		'tools' => __('Tools', $this->plugin_slug),
		'params' => __('Parameters', $this->plugin_slug),
        'faq' => __('FAQ', $this->plugin_slug),
		'about' => __('About', $this->plugin_slug)
    );
    foreach( $tabs as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$class' href='?page=wordpress-popular-posts&tab=$tab'>$name</a>";
    }    
    ?>
    </h2>
    
    <!-- Start stats -->
    <div id="wpp_stats" class="wpp_boxes"<?php if ( "stats" == $current ) {?> style="display:block;"<?php } ?>>
    	<p><?php _e("Click on each tab to see what are the most popular entries on your blog in the last 24 hours, this week, last 30 days or all time since WordPress Popular Posts was installed.", $this->plugin_slug); ?></p>
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <form action="" method="post" id="wpp_stats_options" name="wpp_stats_options">
                    <select name="stats_order">
                        <option <?php if ($this->user_settings['stats']['order_by'] == "comments") {?>selected="selected"<?php } ?> value="comments"><?php _e("Order by comments", $this->plugin_slug); ?></option>
                        <option <?php if ($this->user_settings['stats']['order_by'] == "views") {?>selected="selected"<?php } ?> value="views"><?php _e("Order by views", $this->plugin_slug); ?></option>
                        <option <?php if ($this->user_settings['stats']['order_by'] == "avg") {?>selected="selected"<?php } ?> value="avg"><?php _e("Order by avg. daily views", $this->plugin_slug); ?></option>
                    </select>
                    <label for="stats_type"><?php _e("Post type", $this->plugin_slug); ?>:</label> <input type="text" name="stats_type" value="<?php echo $this->user_settings['stats']['post_type']; ?>" size="15" />
                    <label for="stats_limits"><?php _e("Limit", $this->plugin_slug); ?>:</label> <input type="text" name="stats_limit" value="<?php echo $this->user_settings['stats']['limit']; ?>" size="5" />
                    <input type="hidden" name="section" value="stats" />
                    <input type="submit" class="button-secondary action" value="<?php _e("Apply", $this->plugin_slug); ?>" name="" />
                    
                    <div class="clear"></div>
                    <label for="stats_freshness"><input type="checkbox" class="checkbox" <?php echo ($this->user_settings['stats']['freshness']) ? 'checked="checked"' : ''; ?> id="stats_freshness" name="stats_freshness" /> <?php _e('Display only posts published within the selected Time Range', 'wordpress-popular-posts'); ?></label>
                </form>
            </div>
        </div>
        <div class="clear"></div>
        <br />
        <div id="wpp-stats-tabs">            
            <a href="#" class="button-primary" rel="wpp-daily"><?php _e("Last 24 hours", $this->plugin_slug); ?></a>
            <a href="#" class="button-secondary" rel="wpp-weekly"><?php _e("Last 7 days", $this->plugin_slug); ?></a>
            <a href="#" class="button-secondary" rel="wpp-monthly"><?php _e("Last 30 days", $this->plugin_slug); ?></a>
            <a href="#" class="button-secondary" rel="wpp-all"><?php _e("All-time", $this->plugin_slug); ?></a>
        </div>
        <div id="wpp-stats-canvas">            
            <div class="wpp-stats wpp-stats-active" id="wpp-daily">            	
                <?php echo do_shortcode("[wpp range='daily' post_type='".$this->user_settings['stats']['post_type']."' stats_comments=1 stats_views=1 order_by='".$this->user_settings['stats']['order_by']."' wpp_start='<ol>' wpp_end='</ol>' post_html='<li><a href=\"{url}\" target=\"_blank\" class=\"wpp-post-title\">{text_title}</a> <span class=\"post-stats\">{stats}</span></li>' limit=".$this->user_settings['stats']['limit']." freshness=" . $this->user_settings['stats']['freshness'] . "]"); ?>
            </div>
            <div class="wpp-stats" id="wpp-weekly">
                <?php echo do_shortcode("[wpp range='weekly' post_type='".$this->user_settings['stats']['post_type']."' stats_comments=1 stats_views=1 order_by='".$this->user_settings['stats']['order_by']."' wpp_start='<ol>' wpp_end='</ol>' post_html='<li><a href=\"{url}\" target=\"_blank\" class=\"wpp-post-title\">{text_title}</a> <span class=\"post-stats\">{stats}</span></li>' limit=".$this->user_settings['stats']['limit']." freshness=" . $this->user_settings['stats']['freshness'] . "]"); ?>
            </div>
            <div class="wpp-stats" id="wpp-monthly">
                <?php echo do_shortcode("[wpp range='monthly' post_type='".$this->user_settings['stats']['post_type']."' stats_comments=1 stats_views=1 order_by='".$this->user_settings['stats']['order_by']."' wpp_start='<ol>' wpp_end='</ol>' post_html='<li><a href=\"{url}\" target=\"_blank\" class=\"wpp-post-title\">{text_title}</a> <span class=\"post-stats\">{stats}</span></li>' limit=".$this->user_settings['stats']['limit']." freshness=" . $this->user_settings['stats']['freshness'] . "]"); ?>
            </div>
            <div class="wpp-stats" id="wpp-all">
                <?php echo do_shortcode("[wpp range='all' post_type='".$this->user_settings['stats']['post_type']."' stats_comments=1 stats_views=1 order_by='".$this->user_settings['stats']['order_by']."' wpp_start='<ol>' wpp_end='</ol>' post_html='<li><a href=\"{url}\" target=\"_blank\" class=\"wpp-post-title\">{text_title}</a> <span class=\"post-stats\">{stats}</span></li>' limit=".$this->user_settings['stats']['limit']." freshness=" . $this->user_settings['stats']['freshness'] . "]"); ?>
            </div>
        </div>
    </div>
    <!-- End stats -->
    
    <!-- Start tools -->
    <div id="wpp_tools" class="wpp_boxes"<?php if ( "tools" == $current ) {?> style="display:block;"<?php } ?>>
        
        <h3 class="wmpp-subtitle"><?php _e("Thumbnails", $this->plugin_slug); ?></h3>        	
        <form action="" method="post" id="wpp_thumbnail_options" name="wpp_thumbnail_options">            
            <table class="form-table">
                <tbody>
                	<tr valign="top">
                        <th scope="row"><label for="thumb_default"><?php _e("Default thumbnail", $this->plugin_slug); ?>:</label></th>
                        <td>                        	
                            <div id="thumb-review">
                                <img src="<?php echo $this->user_settings['tools']['thumbnail']['default']; ?>" alt="" border="0" />
                            </div>
                            <input id="upload_thumb_button" type="button" class="button" value="<?php _e( "Upload thumbnail", $this->plugin_slug ); ?>" />
                            <input type="hidden" id="upload_thumb_src" name="upload_thumb_src" value="" />
                            <p class="description"><?php _e("How-to: upload (or select) an image, set Size to Full and click on Upload. After it's done, hit on Apply to save changes", $this->plugin_slug); ?>.</p>
                        </td>
                    </tr>                    
                    <tr valign="top">
                        <th scope="row"><label for="thumb_source"><?php _e("Pick image from", $this->plugin_slug); ?>:</label></th>
                        <td>
                            <select name="thumb_source" id="thumb_source">
                                <option <?php if ($this->user_settings['tools']['thumbnail']['source'] == "featured") {?>selected="selected"<?php } ?> value="featured"><?php _e("Featured image", $this->plugin_slug); ?></option>
                                <option <?php if ($this->user_settings['tools']['thumbnail']['source'] == "first_image") {?>selected="selected"<?php } ?> value="first_image"><?php _e("First image on post", $this->plugin_slug); ?></option>
                                <option <?php if ($this->user_settings['tools']['thumbnail']['source'] == "custom_field") {?>selected="selected"<?php } ?> value="custom_field"><?php _e("Custom field", $this->plugin_slug); ?></option>
                            </select>
                            <br />
                            <p class="description"><?php _e("Tell WordPress Popular Posts where it should get thumbnails from", $this->plugin_slug); ?>.</p>
                        </td>
                    </tr>
                    <tr valign="top" <?php if ($this->user_settings['tools']['thumbnail']['source'] != "custom_field") {?>style="display:none;"<?php } ?> id="row_custom_field">
                        <th scope="row"><label for="thumb_field"><?php _e("Custom field name", $this->plugin_slug); ?>:</label></th>
                        <td>
                            <input type="text" id="thumb_field" name="thumb_field" value="<?php echo $this->user_settings['tools']['thumbnail']['field']; ?>" size="10" <?php if ($this->user_settings['tools']['thumbnail']['source'] != "custom_field") {?>style="display:none;"<?php } ?> />
                        </td>
                    </tr>
                    <tr valign="top" <?php if ($this->user_settings['tools']['thumbnail']['source'] != "custom_field") {?>style="display:none;"<?php } ?> id="row_custom_field_resize">
                        <th scope="row"><label for="thumb_field_resize"><?php _e("Resize image from Custom field?", $this->plugin_slug); ?>:</label></th>
                        <td>
                            <select name="thumb_field_resize" id="thumb_field_resize">
                                <option <?php if ( !$this->user_settings['tools']['thumbnail']['resize'] ) {?>selected="selected"<?php } ?> value="0"><?php _e("No, I will upload my own thumbnail", $this->plugin_slug); ?></option>
                                <option <?php if ( $this->user_settings['tools']['thumbnail']['resize'] == 1 ) {?>selected="selected"<?php } ?> value="1"><?php _e("Yes", $this->plugin_slug); ?></option>                        
                            </select>
                        </td>
                    </tr>
                    <?php
					$wp_upload_dir = wp_upload_dir();					
					if ( is_dir( $wp_upload_dir['basedir'] . "/" . $this->plugin_slug ) ) :
					?>
                    <tr valign="top">
                        <th scope="row"></th>
                        <td>                        	
                            <input type="button" name="wpp-reset-cache" id="wpp-reset-cache" class="button-secondary" value="<?php _e("Empty image cache", $this->plugin_slug); ?>" onclick="confirm_clear_image_cache()" />                            
                            <p class="description"><?php _e("Use this button to clear WPP's thumbnails cache", $this->plugin_slug); ?>.</p>
                        </td>
                    </tr>
                    <?php
					endif;
					?>
                    <tr valign="top">                            	
                        <td colspan="2">
                            <input type="hidden" name="section" value="thumb" />
                            <input type="submit" class="button-secondary action" id="btn_th_ops" value="<?php _e("Apply", $this->plugin_slug); ?>" name="" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
        <br />
        <p style="display:block; float:none; clear:both">&nbsp;</p>
                
        <h3 class="wmpp-subtitle"><?php _e("Data", $this->plugin_slug); ?></h3>
        <form action="" method="post" id="wpp_ajax_options" name="wpp_ajax_options">
        	<table class="form-table">
                <tbody>
                	<tr valign="top">
                        <th scope="row"><label for="log_option"><?php _e("Log views from", $this->plugin_slug); ?>:</label></th>
                        <td>
                            <select name="log_option" id="log_option">
                                <option <?php if ($this->user_settings['tools']['log']['level'] == 0) {?>selected="selected"<?php } ?> value="0"><?php _e("Visitors only", $this->plugin_slug); ?></option>
                                <option <?php if ($this->user_settings['tools']['log']['level'] == 2) {?>selected="selected"<?php } ?> value="2"><?php _e("Logged-in users only", $this->plugin_slug); ?></option>
                                <option <?php if ($this->user_settings['tools']['log']['level'] == 1) {?>selected="selected"<?php } ?> value="1"><?php _e("Everyone", $this->plugin_slug); ?></option>
                            </select>
                            <br />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="ajax"><?php _e("Ajaxify widget", $this->plugin_slug); ?>:</label></th>
                        <td>
                            <select name="ajax" id="ajax">                                
                                <option <?php if (!$this->user_settings['tools']['ajax']) {?>selected="selected"<?php } ?> value="0"><?php _e("Disabled", $this->plugin_slug); ?></option>
                                <option <?php if ($this->user_settings['tools']['ajax']) {?>selected="selected"<?php } ?> value="1"><?php _e("Enabled", $this->plugin_slug); ?></option>
                            </select>
                    
                            <br />
                            <p class="description"><?php _e("If you are using a caching plugin such as WP Super Cache, enabling this feature will keep the popular list from being cached by it", $this->plugin_slug); ?>.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="cache"><?php _e("WPP Cache Expiry Policy", $this->plugin_slug); ?>:</label></th>
                        <td>
                            <select name="cache" id="cache">
                                <option <?php if ( !$this->user_settings['tools']['cache']['active'] ) { ?>selected="selected"<?php } ?> value="0"><?php _e("Never cache", $this->plugin_slug); ?></option>
                                <option <?php if ( $this->user_settings['tools']['cache']['active'] ) { ?>selected="selected"<?php } ?> value="1"><?php _e("Enable caching", $this->plugin_slug); ?></option>
                            </select>
                    
                            <br />
                            <p class="description"><?php _e("Sets WPP's cache expiration time. WPP can cache the popular list for a specified amount of time. Recommended for large / high traffic sites", $this->plugin_slug); ?>.</p>
                        </td>
                    </tr>
                    <tr valign="top" <?php if ( !$this->user_settings['tools']['cache']['active'] ) { ?>style="display:none;"<?php } ?> id="cache_refresh_interval">
                        <th scope="row"><label for="cache_interval_value"><?php _e("Refresh cache every", $this->plugin_slug); ?>:</label></th>
                        <td>
                        	<input name="cache_interval_value" type="text" id="cache_interval_value" value="<?php echo ( isset($this->user_settings['tools']['cache']['interval']['value']) ) ? (int) $this->user_settings['tools']['cache']['interval']['value'] : 1; ?>" class="small-text">
                            <select name="cache_interval_time" id="cache_interval_time">
                            	<option <?php if ($this->user_settings['tools']['cache']['interval']['time'] == "minute") {?>selected="selected"<?php } ?> value="minute"><?php _e("Minute(s)", $this->plugin_slug); ?></option>
                                <option <?php if ($this->user_settings['tools']['cache']['interval']['time'] == "hour") {?>selected="selected"<?php } ?> value="hour"><?php _e("Hour(s)", $this->plugin_slug); ?></option>
                                <option <?php if ($this->user_settings['tools']['cache']['interval']['time'] == "day") {?>selected="selected"<?php } ?> value="day"><?php _e("Day(s)", $this->plugin_slug); ?></option>                                
                                <option <?php if ($this->user_settings['tools']['cache']['interval']['time'] == "week") {?>selected="selected"<?php } ?> value="week"><?php _e("Week(s)", $this->plugin_slug); ?></option>
                                <option <?php if ($this->user_settings['tools']['cache']['interval']['time'] == "month") {?>selected="selected"<?php } ?> value="month"><?php _e("Month(s)", $this->plugin_slug); ?></option>
                                <option <?php if ($this->user_settings['tools']['cache']['interval']['time'] == "year") {?>selected="selected"<?php } ?> value="month"><?php _e("Year(s)", $this->plugin_slug); ?></option>
                            </select>                            
                            <br />
                            <p class="description" style="display:none;" id="cache_too_long"><?php _e("Really? That long?", $this->plugin_slug); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="sampling"><?php _e("Data Sampling", $this->plugin_slug); ?>:</label></th>
                        <td>
                            <select name="sampling" id="sampling">
                                <option <?php if ( !$this->user_settings['tools']['sampling']['active'] ) { ?>selected="selected"<?php } ?> value="0"><?php _e("Disabled", $this->plugin_slug); ?></option>
                                <option <?php if ( $this->user_settings['tools']['sampling']['active'] ) { ?>selected="selected"<?php } ?> value="1"><?php _e("Enabled", $this->plugin_slug); ?></option>
                            </select>
                    
                            <br />
                            <p class="description"><?php echo sprintf( __('By default, WordPress Popular Posts stores in database every single visit your site receives. For small / medium sites this is generally OK, but on large / high traffic sites the constant writing to the database may have an impact on performance. With data sampling, WordPress Popular Posts will store only a subset of your traffic and report on the tendencies detected in that sample set (for more on <em>data sampling</em>, please <a href="%1$s" target="_blank">read here</a>)', $this->plugin_slug), 'http://en.wikipedia.org/wiki/Sample_%28statistics%29' ); ?>.</p>
                        </td>
                    </tr>
                    <tr valign="top" <?php if ( !$this->user_settings['tools']['sampling']['active'] ) { ?>style="display:none;"<?php } ?>>
                        <th scope="row"><label for="sample_rate"><?php _e("Sample Rate", $this->plugin_slug); ?>:</label></th>
                        <td>
                        	<input name="sample_rate" type="text" id="sample_rate" value="<?php echo ( isset($this->user_settings['tools']['sampling']['rate']) ) ? (int) $this->user_settings['tools']['sampling']['rate'] : 100; ?>" class="small-text">
                            <br />
                            <p class="description"><?php echo sprintf( __("A sampling rate of %d is recommended for large / high traffic sites. For lower traffic sites, you should lower the value", $this->plugin_slug), $this->default_user_settings['tools']['sampling']['rate'] ); ?>.</p>
                        </td>
                    </tr>
                    <tr valign="top">                            	
                        <td colspan="2">
                            <input type="hidden" name="section" value="data" />
                    		<input type="submit" class="button-secondary action" id="btn_ajax_ops" value="<?php _e("Apply", $this->plugin_slug); ?>" name="" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
        <br />
        <p style="display:block; float:none; clear:both">&nbsp;</p>
        
        <h3 class="wmpp-subtitle"><?php _e("Miscellaneous", $this->plugin_slug); ?></h3>
        <form action="" method="post" id="wpp_link_options" name="wpp_link_options">
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="link_target"><?php _e("Open links in", $this->plugin_slug); ?>:</label></th>
                        <td>
                            <select name="link_target" id="link_target">
                                <option <?php if ( $this->user_settings['tools']['link']['target'] == '_self' ) {?>selected="selected"<?php } ?> value="_self"><?php _e("Current window", $this->plugin_slug); ?></option>
                                <option <?php if ( $this->user_settings['tools']['link']['target'] == '_blank' ) {?>selected="selected"<?php } ?> value="_blank"><?php _e("New tab/window", $this->plugin_slug); ?></option>
                            </select>
                            <br />
                        </td>
                    </tr>                    
                    <tr valign="top">
                        <th scope="row"><label for="css"><?php _e("Use plugin's stylesheet", $this->plugin_slug); ?>:</label></th>
                        <td>
                            <select name="css" id="css">
                                <option <?php if ($this->user_settings['tools']['css']) {?>selected="selected"<?php } ?> value="1"><?php _e("Enabled", $this->plugin_slug); ?></option>
                                <option <?php if (!$this->user_settings['tools']['css']) {?>selected="selected"<?php } ?> value="0"><?php _e("Disabled", $this->plugin_slug); ?></option>
                            </select>
                            <br />
                            <p class="description"><?php _e("By default, the plugin includes a stylesheet called wpp.css which you can use to style your popular posts listing. If you wish to use your own stylesheet or do not want it to have it included in the header section of your site, use this.", $this->plugin_slug); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <td colspan="2">
                            <input type="hidden" name="section" value="misc" />
                            <input type="submit" class="button-secondary action" value="<?php _e("Apply", $this->plugin_slug); ?>" name="" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
        <br />
        <p style="display:block; float:none; clear:both">&nbsp;</p>
        
        <br /><br />
        
        <p><?php _e('WordPress Popular Posts maintains data in two separate tables: one for storing the most popular entries on a daily basis (from now on, "cache"), and another one to keep the All-time data (from now on, "historical data" or just "data"). If for some reason you need to clear the cache table, or even both historical and cache tables, please use the buttons below to do so.', $this->plugin_slug) ?></p>
        <p><input type="button" name="wpp-reset-cache" id="wpp-reset-cache" class="button-secondary" value="<?php _e("Empty cache", $this->plugin_slug); ?>" onclick="confirm_reset_cache()" /> <label for="wpp-reset-cache"><small><?php _e('Use this button to manually clear entries from WPP cache only', $this->plugin_slug); ?></small></label></p>
        <p><input type="button" name="wpp-reset-all" id="wpp-reset-all" class="button-secondary" value="<?php _e("Clear all data", $this->plugin_slug); ?>" onclick="confirm_reset_all()" /> <label for="wpp-reset-all"><small><?php _e('Use this button to manually clear entries from all WPP data tables', $this->plugin_slug); ?></small></label></p>
    </div>
    <!-- End tools -->
    
    <!-- Start params -->
    <div id="wpp_params" class="wpp_boxes"<?php if ( "params" == $current ) {?> style="display:block;"<?php } ?>>        
        <div>
            <p><?php printf( __('With the following parameters you can customize the popular posts list when using either the <a href="%1$s">wpp_get_most_popular() template tag</a> or the <a href="%2$s">[wpp] shortcode</a>.', $this->plugin_slug),
				admin_url('options-general.php?page=wordpress-popular-posts&tab=faq#template-tags'),
				admin_url('options-general.php?page=wordpress-popular-posts&tab=faq#shortcode')
			); ?></p>
            <br />
            <table cellspacing="0" class="wp-list-table widefat fixed posts">
                <thead>
                    <tr>
                        <th class="manage-column column-title"><?php _e('Parameter', $this->plugin_slug); ?></th>
                        <th class="manage-column column-title"><?php _e('What it does ', $this->plugin_slug); ?></th>
                        <th class="manage-column column-title"><?php _e('Possible values', $this->plugin_slug); ?></th>
                        <th class="manage-column column-title"><?php _e('Defaults to', $this->plugin_slug); ?></th>
                        <th class="manage-column column-title"><?php _e('Example', $this->plugin_slug); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>header</strong></td>
                        <td><?php _e('Sets a heading for the list', $this->plugin_slug); ?></td>
                        <td><?php _e('Text string', $this->plugin_slug); ?></td>
                        <td><?php _e('Popular Posts', $this->plugin_slug); ?></td>
                        <td>&lt;?php wpp_get_mostpopular( 'header="Popular Posts"' ); ?&gt;</td>
                    </tr>
                    <tr class="alternate">
                        <td><strong>header_start</strong></td>
                        <td><?php _e('Set the opening tag for the heading of the list', $this->plugin_slug); ?></td>
                        <td><?php _e('Text string', $this->plugin_slug); ?></td>
                        <td>&lt;h2&gt;</td>
                        <td>&lt;?php wpp_get_mostpopular( 'header_start="&lt;h2&gt;"&amp;header_end="&lt;/h2&gt;"' ); ?&gt;</td>
                    </tr>
                    <tr>
                        <td><strong>header_end</strong></td>
                        <td><?php _e('Set the closing tag for the heading of the list', $this->plugin_slug); ?></td>
                        <td><?php _e('Text string', $this->plugin_slug); ?></td>
                        <td>&lt;/h2&gt;</td>
                        <td>&lt;?php wpp_get_mostpopular( 'header_start="&lt;h2&gt;"&amp;header_end="&lt;/h2&gt;"' ); ?&gt;</td>
                    </tr>
                    <tr class="alternate">
                        <td><strong>limit</strong></td>
                        <td><?php _e('Sets the maximum number of popular posts to be shown on the listing', $this->plugin_slug); ?></td>
                        <td><?php _e('Positive integer', $this->plugin_slug); ?></td>
                        <td>10</td>
                        <td>&lt;?php wpp_get_mostpopular( 'limit=10' ); ?&gt;</td>
                    </tr>
                    <tr>
                        <td><strong>range</strong></td>
                        <td><?php _e('Tells WordPress Popular Posts to retrieve the most popular entries within the time range specified by you', $this->plugin_slug); ?></td>
                        <td>"daily", "weekly", "monthly", "all"</td>
                        <td>daily</td>
                        <td>&lt;?php wpp_get_mostpopular( 'range="daily"' ); ?&gt;</td>
                    </tr>
                    <tr class="alternate">
                        <td><strong>freshness</strong></td>
                        <td><?php _e('Tells WordPress Popular Posts to retrieve the most popular entries published within the time range specified by you', $this->plugin_slug); ?></td>
                        <td>1 (true), 0 (false)</td>
                        <td>0</td>
                        <td>&lt;?php wpp_get_mostpopular( 'freshness=1' ); ?&gt;</td>
                    </tr>
                    <tr>
                        <td><strong>order_by</strong></td>
                        <td><?php _e('Sets the sorting option of the popular posts', $this->plugin_slug); ?></td>
                        <td>"comments", "views", "avg" <?php _e('(for average views per day)', $this->plugin_slug); ?></td>
                        <td>views</td>
                        <td>&lt;?php wpp_get_mostpopular( 'order_by="comments"' ); ?&gt;</td>
                    </tr>
                    <tr class="alternate">
                        <td><strong>post_type</strong></td>
                        <td><?php _e('Defines the type of posts to show on the listing', $this->plugin_slug); ?></td>
                        <td><?php _e('Text string', $this->plugin_slug); ?></td>
                        <td>post,page</td>
                        <td>&lt;?php wpp_get_mostpopular( 'post_type="post,page,your-custom-post-type"' ); ?&gt;</td>
                    </tr>
                    <tr>
                        <td><strong>pid</strong></td>
                        <td><?php _e('If set, WordPress Popular Posts will exclude the specified post(s) ID(s) form the listing.', $this->plugin_slug); ?></td>
                        <td><?php _e('Text string', $this->plugin_slug); ?></td>
                        <td><?php _e('None', $this->plugin_slug); ?></td>
                        <td>&lt;?php wpp_get_mostpopular( 'pid="60,25,31"' ); ?&gt;</td>
                    </tr>
                    <tr class="alternate">
                        <td><strong>cat</strong></td>
                        <td><?php _e('If set, WordPress Popular Posts will retrieve all entries that belong to the specified category(ies) ID(s). If a minus sign is used, the category(ies) will be excluded instead.', $this->plugin_slug); ?></td>
                        <td><?php _e('Text string', $this->plugin_slug); ?></td>
                        <td><?php _e('None', $this->plugin_slug); ?></td>
                        <td>&lt;?php wpp_get_mostpopular( 'cat="1,55,-74"' ); ?&gt;</td>
                    </tr>
                    <tr>
                        <td><strong>author</strong></td>
                        <td><?php _e('If set, WordPress Popular Posts will retrieve all entries created by specified author(s) ID(s).', $this->plugin_slug); ?></td>
                        <td><?php _e('Text string', $this->plugin_slug); ?></td>
                        <td><?php _e('None', $this->plugin_slug); ?></td>
                        <td>&lt;?php wpp_get_mostpopular( 'author="75,8,120"' ); ?&gt;</td>
                    </tr>
                    <tr class="alternate">
                        <td><strong>title_length</strong></td>
                        <td><?php _e('If set, WordPress Popular Posts will shorten each post title to "n" characters whenever possible', $this->plugin_slug); ?></td>
                        <td><?php _e('Positive integer', $this->plugin_slug); ?></td>
                        <td>25</td>
                        <td>&lt;?php wpp_get_mostpopular( 'title_length=25' ); ?&gt;</td>
                    </tr>
                    <tr>
                        <td><strong>title_by_words</strong></td>
                        <td><?php _e('If set to 1, WordPress Popular Posts will shorten each post title to "n" words instead of characters', $this->plugin_slug); ?></td>
                        <td>1 (true), (0) false</td>
                        <td>0</td>
                        <td>&lt;?php wpp_get_mostpopular( 'title_by_words=1' ); ?&gt;</td>
                    </tr>
                    <tr class="alternate">
                        <td><strong>excerpt_length</strong></td>
                        <td><?php _e('If set, WordPress Popular Posts will build and include an excerpt of "n" characters long from the content of each post listed as popular', $this->plugin_slug); ?></td>
                        <td><?php _e('Positive integer', $this->plugin_slug); ?></td>
                        <td>0</td>
                        <td>&lt;?php wpp_get_mostpopular( 'excerpt_length=55&amp;post_html="&lt;li&gt;{thumb} {title} {summary}&lt;/li&gt;"' ); ?&gt;</td>
                    </tr>
                    <tr>
                        <td><strong>excerpt_format</strong></td>
                        <td><?php _e('If set, WordPress Popular Posts will maintaing all styling tags (strong, italic, etc) and hyperlinks found in the excerpt', $this->plugin_slug); ?></td>
                        <td>1 (true), (0) false</td>
                        <td>0</td>
                        <td>&lt;?php wpp_get_mostpopular( 'excerpt_format=1&amp;excerpt_length=55&amp;post_html="&lt;li&gt;{thumb} {title} {summary}&lt;/li&gt;"' ); ?&gt;</td>
                    </tr>
                    <tr class="alternate">
                        <td><strong>excerpt_by_words</strong></td>
                        <td><?php _e('If set to 1, WordPress Popular Posts will shorten the excerpt to "n" words instead of characters', $this->plugin_slug); ?></td>
                        <td>1 (true), (0) false</td>
                        <td>0</td>
                        <td>&lt;?php wpp_get_mostpopular( 'excerpt_by_words=1&amp;excerpt_length=55&amp;post_html="&lt;li&gt;{thumb} {title} {summary}&lt;/li&gt;"' ); ?&gt;</td>
                    </tr>
                    <tr>
                        <td><strong>thumbnail_width</strong></td>
                        <td><?php _e('If set, and if your current server configuration allows it, you will be able to display thumbnails of your posts. This attribute sets the width for thumbnails', $this->plugin_slug); ?></td>
                        <td><?php _e('Positive integer', $this->plugin_slug); ?></td>
                        <td>15</td>
                        <td>&lt;?php wpp_get_mostpopular( 'thumbnail_width=30&amp;thumbnail_height=30' ); ?&gt;</td>
                    </tr>
                    <tr class="alternate">
                        <td><strong>thumbnail_height</strong></td>
                        <td><?php _e('If set, and if your current server configuration allows it, you will be able to display thumbnails of your posts. This attribute sets the height for thumbnails', $this->plugin_slug); ?></td>
                        <td><?php _e('Positive integer', $this->plugin_slug); ?></td>
                        <td>15</td>
                        <td>&lt;?php wpp_get_mostpopular( 'thumbnail_width=30&amp;thumbnail_height=30' ); ?&gt;</td>
                    </tr>
                    <tr>
                        <td><strong>rating</strong></td>
                        <td><?php _e('If set, and if the WP-PostRatings plugin is installed and enabled on your blog, WordPress Popular Posts will show how your visitors are rating your entries', $this->plugin_slug); ?></td>
                        <td>1 (true), (0) false</td>
                        <td>0</td>
                        <td>&lt;?php wpp_get_mostpopular( 'rating=1&amp;post_html="&lt;li&gt;{thumb} {title} {rating}&lt;/li&gt;"' ); ?&gt;</td>
                    </tr>
                    <tr class="alternate">
                        <td><strong>stats_comments</strong></td>
                        <td><?php _e('If set, WordPress Popular Posts will show how many comments each popular post has got until now', $this->plugin_slug); ?></td>
                        <td>1 (true), 0 (false)</td>
                        <td>1</td>
                        <td>&lt;?php wpp_get_mostpopular( 'stats_comments=1' ); ?&gt;</td>
                    </tr>
                    <tr>
                        <td><strong>stats_views</strong></td>
                        <td><?php _e('If set, WordPress Popular Posts will show how many views each popular post has got since it was installed', $this->plugin_slug); ?></td>
                        <td>1 (true), (0) false</td>
                        <td>0</td>
                        <td>&lt;?php wpp_get_mostpopular( 'stats_views=1' ); ?&gt;</td>
                    </tr>
                    <tr class="alternate">
                        <td><strong>stats_author</strong></td>
                        <td><?php _e('If set, WordPress Popular Posts will show who published each popular post on the list', $this->plugin_slug); ?></td>
                        <td>1 (true), (0) false</td>
                        <td>0</td>
                        <td>&lt;?php wpp_get_mostpopular( 'stats_author=1' ); ?&gt;</td>
                    </tr>
                    <tr>
                        <td><strong>stats_date</strong></td>
                        <td><?php _e('If set, WordPress Popular Posts will display the date when each popular post on the list was published', $this->plugin_slug); ?></td>
                        <td>1 (true), (0) false</td>
                        <td>0</td>
                        <td>&lt;?php wpp_get_mostpopular( 'stats_date=1' ); ?&gt;</td>
                    </tr>
                    <tr class="alternate">
                        <td><strong>stats_date_format</strong></td>
                        <td><?php _e('Sets the date format', $this->plugin_slug); ?></td>
                        <td><?php _e('Text string', $this->plugin_slug); ?></td>
                        <td>0</td>
                        <td>&lt;?php wpp_get_mostpopular( 'stats_date_format="F j, Y"' ); ?&gt;</td>
                    </tr>
                    <tr>
                        <td><strong>stats_category</strong></td>
                        <td><?php _e('If set, WordPress Popular Posts will display the category', $this->plugin_slug); ?></td>
                        <td>1 (true), (0) false</td>
                        <td>0</td>
                        <td>&lt;?php wpp_get_mostpopular( 'stats_category=1' ); ?&gt;</td>
                    </tr>
                    <tr class="alternate">
                        <td><strong>wpp_start</strong></td>
                        <td><?php _e('Sets the opening tag for the listing', $this->plugin_slug); ?></td>
                        <td><?php _e('Text string', $this->plugin_slug); ?></td>
                        <td>&lt;ul&gt;</td>
                        <td>&lt;?php wpp_get_mostpopular( 'wpp_start="&lt;ol&gt;"&amp;wpp_end="&lt;/ol&gt;"' ); ?&gt;</td>
                    </tr>
                    <tr>
                        <td><strong>wpp_end</strong></td>
                        <td><?php _e('Sets the closing tag for the listing', $this->plugin_slug); ?></td>
                        <td><?php _e('Text string', $this->plugin_slug); ?></td>
                        <td>&lt;/ul&gt;</td>
                        <td>&lt;?php wpp_get_mostpopular( 'wpp_start="&lt;ol&gt;"&amp;wpp_end="&lt;/ol&gt;"' ); ?&gt;</td>
                    </tr>
                    <tr class="alternate">
                        <td><strong>post_html</strong></td>
                        <td><?php _e('Sets the HTML structure of each post', $this->plugin_slug); ?></td>
                        <td><?php _e('Text string, custom HTML', $this->plugin_slug); ?>.<br /><br /><strong><?php _e('Available Content Tags', $this->plugin_slug); ?>:</strong> <br /><br /><em>{thumb}</em> (<?php _e('displays thumbnail linked to post/page', $this->plugin_slug); ?>)<br /><br /> <em>{thumb_img}</em> (<?php _e('displays thumbnail image without linking to post/page', $this->plugin_slug); ?>)<br /><br /> <em>{title}</em> (<?php _e('displays linked post/page title', $this->plugin_slug); ?>)<br /><br /> <em>{summary}</em> (<?php _e('displays post/page excerpt, and requires excerpt_length to be greater than 0', $this->plugin_slug); ?>)<br /><br /> <em>{stats}</em> (<?php _e('displays the default stats tags', $this->plugin_slug); ?>)<br /><br /> <em>{rating}</em> (<?php _e('displays post/page current rating, requires WP-PostRatings installed and enabled', $this->plugin_slug); ?>)<br /><br /> <em>{score}</em> (<?php _e('displays post/page current rating as an integer, requires WP-PostRatings installed and enabled', $this->plugin_slug); ?>)<br /><br /> <em>{url}</em> (<?php _e('outputs the URL of the post/page', $this->plugin_slug); ?>)<br /><br /> <em>{text_title}</em> (<?php _e('displays post/page title, no link', $this->plugin_slug); ?>)<br /><br /> <em>{author}</em> (<?php _e('displays linked author name, requires stats_author=1', $this->plugin_slug); ?>)<br /><br /> <em>{category}</em> (<?php _e('displays linked category name, requires stats_category=1', $this->plugin_slug); ?>)<br /><br /> <em>{views}</em> (<?php _e('displays views count only, no text', $this->plugin_slug); ?>)<br /><br /> <em>{comments}</em> (<?php _e('displays comments count only, no text, requires stats_comments=1', $this->plugin_slug); ?>)<br /><br /> <em>{date}</em> (<?php _e('displays post/page date, requires stats_date=1', $this->plugin_slug); ?>)</td>
                        <td>&lt;li&gt;{thumb} {title} {stats}&lt;/li&gt;</td>
                        <td>&lt;?php wpp_get_mostpopular( 'post_html="&lt;li&gt;{thumb} &lt;a href=\'{url}\'&gt;{text_title}&lt;/a&gt;&lt;/li&gt;"' ); ?&gt;</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <!-- End params -->
    
    <!-- Start faq -->
    <div id="wpp_faq" class="wpp_boxes"<?php if ( "faq" == $current ) {?> style="display:block;"<?php } ?>>    	
        <h4 id="widget-title">&raquo; <a href="#" rel="q-1"><?php _e('What does "Title" do?', $this->plugin_slug); ?></a></h4>
        
        <div class="wpp-ans" id="q-1">
            <p><?php _e('It allows you to show a heading for your most popular posts listing. If left empty, no heading will be displayed at all.', $this->plugin_slug); ?></p>
        </div>
        
        <h4 id="time-range">&raquo; <a href="#" rel="q-2"><?php _e('What is Time Range for?', $this->plugin_slug); ?></a></h4>
        <div class="wpp-ans" id="q-2">
            <p><?php _e('It will tell WordPress Popular Posts to retrieve all posts with most views / comments within the selected time range.', $this->plugin_slug); ?></p>
        </div>
        
        <h4 id="sorting">&raquo; <a href="#" rel="q-3"><?php _e('What is "Sort post by" for?', $this->plugin_slug); ?></a></h4>
        <div class="wpp-ans" id="q-3">
            <p><?php _e('It allows you to decide whether to order your popular posts listing by total views, comments, or average views per day.', $this->plugin_slug); ?></p>
        </div>                    
        
        <h4 id="display-rating">&raquo; <a href="#" rel="q-4"><?php _e('What does "Display post rating" do?', $this->plugin_slug); ?></a></h4>
        <div class="wpp-ans" id="q-4">
            <p><?php _e('If checked, WordPress Popular Posts will show how your readers are rating your most popular posts. This feature requires having WP-PostRatings plugin installed and enabled on your blog for it to work.', $this->plugin_slug); ?></p>
        </div>
        
        <h4 id="shorten-title">&raquo; <a href="#" rel="q-5"><?php _e('What does "Shorten title" do?', $this->plugin_slug); ?></a></h4>
        <div class="wpp-ans" id="q-5">
            <p><?php _e('If checked, all posts titles will be shortened to "n" characters/words. A new "Shorten title to" option will appear so you can set it to whatever you like.', $this->plugin_slug); ?></p>
        </div>
        
        <h4 id="display-excerpt">&raquo; <a href="#" rel="q-6"><?php _e('What does "Display post excerpt" do?', $this->plugin_slug); ?></a></h4>
        <div class="wpp-ans" id="q-6">
            <p><?php _e('If checked, WordPress Popular Posts will also include a small extract of your posts in the list. Similarly to the previous option, you will be able to decide how long the post excerpt should be.', $this->plugin_slug); ?></p>
        </div>
        
        <h4 id="keep-format">&raquo; <a href="#" rel="q-7"><?php _e('What does "Keep text format and links" do?', $this->plugin_slug); ?></a></h4>
        <div class="wpp-ans" id="q-7">
            <p><?php _e('If checked, and if the Post Excerpt feature is enabled, WordPress Popular Posts will keep the styling tags (eg. bold, italic, etc) that were found in the excerpt. Hyperlinks will remain intact, too.', $this->plugin_slug); ?></p>
        </div>
        
        <h4 id="filter-post-type">&raquo; <a href="#" rel="q-8"><?php _e('What is "Post type" for?', $this->plugin_slug); ?></a></h4>
        <div class="wpp-ans" id="q-8">
            <p><?php _e('This filter allows you to decide which post types to show on the listing. By default, it will retrieve only posts and pages (which should be fine for most cases).', $this->plugin_slug); ?></p>
        </div>
        
        <h4 id="filter_category">&raquo; <a href="#" rel="q-9"><?php _e('What is "Category(ies) ID(s)" for?', $this->plugin_slug); ?></a></h4>
        <div class="wpp-ans" id="q-9">
            <p><?php _e('This filter allows you to select which categories should be included or excluded from the listing. A negative sign in front of the category ID number will exclude posts belonging to it from the list, for example. You can specify more than one ID with a comma separated list.', $this->plugin_slug); ?></p>
        </div>
        
        <h4 id="filter-author">&raquo; <a href="#" rel="q-10"><?php _e('What is "Author(s) ID(s)" for?', $this->plugin_slug); ?></a></h4>
        <div class="wpp-ans" id="q-10">
            <p><?php _e('Just like the Category filter, this one lets you filter posts by author ID. You can specify more than one ID with a comma separated list.', $this->plugin_slug); ?></p>
        </div>
        
        <h4 id="display-thumb">&raquo; <a href="#" rel="q-11"><?php _e('What does "Display post thumbnail" do?', $this->plugin_slug); ?></a></h4>
        <div class="wpp-ans" id="q-11">
            <p><?php _e('If checked, WordPress Popular Posts will attempt to retrieve the thumbnail of each post. You can set up the source of the thumbnail via Settings - WordPress Popular Posts - Tools.', $this->plugin_slug); ?></p>
        </div>
        
        <h4 id="stats-comments">&raquo; <a href="#" rel="q-12"><?php _e('What does "Display comment count" do?', $this->plugin_slug); ?></a></h4>
        <div class="wpp-ans" id="q-12">
            <p><?php _e('If checked, WordPress Popular Posts will display how many comments each popular post has got in the selected Time Range.', $this->plugin_slug); ?></p>
        </div>
        
        <h4 id="stats-views">&raquo; <a href="#" rel="q-13"><?php _e('What does "Display views" do?', $this->plugin_slug); ?></a></h4>
        <div class="wpp-ans" id="q-13">
            <p><?php _e('If checked, WordPress Popular Posts will show how many pageviews a single post has gotten in the selected Time Range.', $this->plugin_slug); ?></p>
        </div>
        
        <h4 id="stats-author">&raquo; <a href="#" rel="q-14"><?php _e('What does "Display author" do?', $this->plugin_slug); ?></a></h4>
        <div class="wpp-ans" id="q-14">
            <p><?php _e('If checked, WordPress Popular Posts will display the name of the author of each entry listed.', $this->plugin_slug); ?></p>
        </div>
        
        <h4 id="stats-date">&raquo; <a href="#" rel="q-15"><?php _e('What does "Display date" do?', $this->plugin_slug); ?></a></h4>
        <div class="wpp-ans" id="q-15">
            <p><?php _e('If checked, WordPress Popular Posts will display the date when each popular posts was published.', $this->plugin_slug); ?></p>
        </div>
        
        <h4 id="stats-cat">&raquo; <a href="#" rel="q-16"><?php _e('What does "Display category" do?', $this->plugin_slug); ?></a></h4>
        <div class="wpp-ans" id="q-16">
            <p><?php _e('If checked, WordPress Popular Posts will display the category of each post.', $this->plugin_slug); ?></p>
        </div>
        
        <h4 id="custom-html-markup">&raquo; <a href="#" rel="q-17"><?php _e('What does "Use custom HTML Markup" do?', $this->plugin_slug); ?></a></h4>
        <div class="wpp-ans" id="q-17">
            <p><?php _e('If checked, you will be able to customize the HTML markup of your popular posts listing. For example, you can decide whether to wrap your posts in an unordered list, an ordered list, a div, etc. If you know xHTML/CSS, this is for you!', $this->plugin_slug); ?></p>
        </div>
        
        <h4>&raquo; <a href="#" rel="q-18"><?php _e('What are "Content Tags"?', $this->plugin_slug); ?></a></h4>
        <div class="wpp-ans" id="q-18">            
            <p><?php echo sprintf( __('Content Tags are codes to display a variety of items on your popular posts custom HTML structure. For example, setting it to "{title}: {summary}" (without the quotes) would display "Post title: excerpt of the post here". For more Content Tags, see the <a href="%s" target="_blank">Parameters</a> section.', $this->plugin_slug), admin_url('options-general.php?page=wordpress-popular-posts&tab=params') ); ?></p>
        </div>
        
        <h4 id="template-tags">&raquo; <a href="#" rel="q-19"><?php _e('What are "Template Tags"?', $this->plugin_slug); ?></a></h4>
        <div class="wpp-ans" id="q-19">
            <p><?php _e('Template Tags are simply php functions that allow you to perform certain actions. For example, WordPress Popular Posts currently supports two different template tags: wpp_get_mostpopular() and wpp_get_views().', $this->plugin_slug); ?></p>
        </div>
        
        <h4>&raquo; <a href="#" rel="q-20"><?php _e('What are the template tags that WordPress Popular Posts supports?', $this->plugin_slug); ?></a></h4>
        <div class="wpp-ans" id="q-20">
            <p><?php _e('The following are the template tags supported by WordPress Popular Posts', $this->plugin_slug); ?>:</p>
            <table cellspacing="0" class="wp-list-table widefat fixed posts">
                <thead>
                    <tr>
                        <th class="manage-column column-title"><?php _e('Template tag', $this->plugin_slug); ?></th>
                        <th class="manage-column column-title"><?php _e('What it does ', $this->plugin_slug); ?></th>
                        <th class="manage-column column-title"><?php _e('Parameters', $this->plugin_slug); ?></th>
                        <th class="manage-column column-title"><?php _e('Example', $this->plugin_slug); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>wpp_get_mostpopular()</strong></td>
                        <td><?php printf( __('Similar to the widget functionality, this tag retrieves the most popular posts on your blog. This function also accepts <a href="%1$s">parameters</a> so you can customize your popular listing, but these are not required.', $this->plugin_slug), admin_url('options-general.php?page=wordpress-popular-posts&tab=params') ); ?></td>
                        <td><?php printf( __('Please refer to the <a href="%1$s">Parameters section</a> for a complete list of attributes.', $this->plugin_slug), admin_url('options-general.php?page=wordpress-popular-posts&tab=params') ); ?></td>
                        <td>&lt;?php wpp_get_mostpopular(); ?&gt;<br />&lt;?php wpp_get_mostpopular("range=weekly&amp;limit=7"); ?&gt;</td>
                    </tr>
                    <tr class="alternate">
                        <td><strong>wpp_get_views()</strong></td>
                        <td><?php _e('Displays the number of views of a single post. Post ID is required or it will return false.', $this->plugin_slug); ?></td>
                        <td><?php _e('Post ID', $this->plugin_slug); ?>, range ("daily", "weekly", "monthly", "all")</td>
                        <td>&lt;?php echo wpp_get_views($post->ID); ?&gt;<br />&lt;?php echo wpp_get_views(15, 'weekly'); ?&gt;</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <h4 id="shortcode">&raquo; <a href="#" rel="q-21"><?php _e('What are "shortcodes"?', $this->plugin_slug); ?></a></h4>
        <div class="wpp-ans" id="q-21">
            <p><?php echo sprintf( __('Shortcodes are similar to BB Codes, these allow us to call a php function by simply typing something like [shortcode]. With WordPress Popular Posts, the shortcode [wpp] will let you insert a list of the most popular posts in posts content and pages too! For more information about shortcodes, please visit the <a href="%s" target="_blank">WordPress Shortcode API</a> page.', $this->plugin_slug), 'http://codex.wordpress.org/Shortcode_API' ); ?></p>
        </div>        
    </div>
    <!-- End faq -->
    
    <!-- Start about -->
    <div id="wpp_faq" class="wpp_boxes"<?php if ( "about" == $current ) {?> style="display:block;"<?php } ?>>
        
        <h3><?php echo sprintf( __('About WordPress Popular Posts %s', $this->plugin_slug), $this->version); ?></h3>
        <p><?php _e( 'This version includes the following changes', $this->plugin_slug ); ?>:</p>
        
        <ul>
            <li>Fixes missing HTML decoding for custom HTML in widget.</li>
            <li>Puts LIMIT clause back to the outer query.</li>
        </ul>
                
    </div>
    <!-- End about -->
    
    <div id="wpp_donate" class="wpp_box" style="">
        <h3 style="margin-top:0; text-align:center;"><?php _e('Do you like this plugin?', $this->plugin_slug); ?></h3>
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="RP9SK8KVQHRKS">
            <input type="image" src="//www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" style="display:block; margin:0 auto;">
            <img alt="" border="0" src="//www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
        </form>
        <p><?php _e( 'Each donation motivates me to keep releasing free stuff for the WordPress community!', $this->plugin_slug ); ?></p>
        <p><?php echo sprintf( __('You can <a href="%s" target="_blank">leave a review</a>, too!', $this->plugin_slug), 'http://wordpress.org/support/view/plugin-reviews/wordpress-popular-posts' ); ?></p>
    </div>
    
    <div id="wpp_support" class="wpp_box" style="">
        <h3 style="margin-top:0; text-align:center;"><?php _e('Need help?', $this->plugin_slug); ?></h3>
        <p><?php echo sprintf( __('Visit <a href="%s" target="_blank">the forum</a> for support, questions and feedback.', $this->plugin_slug), 'http://wordpress.org/support/plugin/wordpress-popular-posts' ); ?></p>
        <p><?php _e('Let\'s make this plugin even better!', $this->plugin_slug); ?></p>
    </div>
        
</div>