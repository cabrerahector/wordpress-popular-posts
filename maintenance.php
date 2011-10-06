<?php
	if (basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__)) exit('Please do not load this page directly');
	
	$rand = md5(uniqid(rand(), true));
	
	$wpp_rand = get_option("wpp_rand");
	
	if (empty($wpp_rand)) {
		add_option("wpp_rand", $rand);
	} else {
		update_option("wpp_rand", $rand);
	}
	
?>
<style>
	#wpp-wrapper {width:100%}
	
		h2#wmpp-title {color:#666; font-weight:100; font-family:Georgia, "Times New Roman", Times, serif; font-size:24px; font-style:italic}
		
		h3 {color:#666; font-weight:100; font-family:Georgia, "Times New Roman", Times, serif; font-size:16px}
		
		h4 {margin:0 0 4px 0; color:#666; font-weight:100; font-family:Georgia, "Times New Roman", Times, serif; font-size:13px}
		h4 a {text-decoration:none}
		h4 a:hover {text-decoration:underline}
		
		.wpp-ans {display:none; width:100%;}
		
		.attr_table {
			width:99%;
			border-top:#ccc 1px solid;
			border-right:#ccc 1px solid;
			border-bottom:#ccc 1px solid;
		}
		
			.attr_table td {
				padding:3px;
				font-size:11px;
				border-left:#ccc 1px solid;
				border-bottom:#ccc 1px solid;
			}
		
		.attr_heading { padding:2px 4px!important; font-size:10px; font-weight:bold; color:#fff; background:#202020; }
</style>
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery("#maintenance_table h4 a").click(function(){
			jQuery(".wpp-ans:visible").slideUp();
			
			if (jQuery("#" + jQuery(this).attr("rel")).is(":hidden")) {
				jQuery("#" + jQuery(this).attr("rel")).slideDown();
			}
			
			return false;
		});
	});
		
	function confirm_reset_cache() {
		if (confirm("<?php _e("This operation will delete all entries from Wordpress Popular Posts' cache table and cannot be undone.", "wordpress-popular-posts"); ?> \n" + "<?php _e("Do you want to continue?", "wordpress-popular-posts"); ?>")) {
			jQuery.post(ajaxurl, {action: 'wpp_clear_cache', token: '<?php echo get_option("wpp_rand"); ?>', clear: 'cache'}, function(data){
				alert(data);
			});
		}
	}
	
	function confirm_reset_all() {
		if (confirm("<?php _e("This operation will delete all stored info from Wordpress Popular Posts' data tables and cannot be undone.", "wordpress"); ?> \n" + "<?php _e("Do you want to continue?", "wordpress-popular-posts"); ?>")) {
			jQuery.post(ajaxurl, {action: 'wpp_clear_all', token: '<?php echo get_option("wpp_rand"); ?>', clear: 'all'}, function(data){
				alert(data);
			});
		}
	}
	
</script>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
    <h2 id="wmpp-title">Wordpress Popular Posts</h2>
    
    <h3><?php _e('Whoa! What just happened in here?!', 'wordpress-popular-posts'); ?></h3>
    <p><?php _e('Previous users of Wordpress Popular Posts will remember that earlier versions of my plugin used to display a Settings page over here. However, from version 2.0 and on things will be slightly different.', 'wordpress-popular-posts'); ?></p>
    <p><?php _e('Wordpress Popular Posts has gone multi-widget so now you\'ll be able to install multiple instances of my plugin on your sidebars, each with its own unique settings! Because of that, having a General Settings page to handle all instances is simply not a good idea. Fear not, my friend, since you still can set each instance\'s configuration via', 'wordpress-popular-posts'); ?> <a href="<?php echo bloginfo('wpurl')."/wp-admin/widgets.php"; ?>"><?php _e('Widgets page', 'wordpress-popular-posts'); ?></a>.</p><br />
    <table width="100%" cellpadding="0" cellspacing="0" style="width:100%!important; border-top:#ccc 1px solid;" id="maintenance_table">
        <tr>
            <td valign="top" width="670"><!-- help area -->
                <h3><?php _e('Help', 'wordpress-popular-posts'); ?></h3>
                <h4><a href="#" rel="q-1"><?php _e('What does "Include pages" do?', 'wordpress-popular-posts'); ?></a></h4>
                <div class="wpp-ans" id="q-1">
                    <p><?php _e('If checked, Wordpress Popular Posts will also list the most viewed pages on your blog. Enabled by default.', 'wordpress-popular-posts'); ?></p>
                </div>
                <h4><a href="#" rel="q-2"><?php _e('What does "Display post rating" do?', 'wordpress-popular-posts'); ?></a></h4>
                <div class="wpp-ans" id="q-2">
                    <p><?php _e('If checked, Wordpress Popular Posts will show how your readers are rating your most popular posts. This feature requires having WP-PostRatings plugin installed and enabled on your blog for it to work. Disabled by default.', 'wordpress-popular-posts'); ?></p>
                </div>
                <h4><a href="#" rel="q-3"><?php _e('What does "Shorten title output" do?', 'wordpress-popular-posts'); ?></a></h4>
                <div class="wpp-ans" id="q-3">
                    <p><?php _e('If checked, all posts titles will be shortened to "n" characters. A new "Shorten title to" option will appear so you can set it to whatever you like. Disabled by default.', 'wordpress-popular-posts'); ?></p>
                </div>
                <h4><a href="#" rel="q-4"><?php _e('What does "Display post excerpt" do?', 'wordpress-popular-posts'); ?></a></h4>
                <div class="wpp-ans" id="q-4">
                    <p><?php _e('If checked, Wordpress Popular Posts will also include a small extract of your posts in the list. Similarly to the previous option, you will be able to decide how long the post excerpt should be. Disabled by default.', 'wordpress-popular-posts'); ?></p>
                </div>
                <h4><a href="#" rel="q-17"><?php _e('What does "Keep text format and links" do?', 'wordpress-popular-posts'); ?></a></h4>
                <div class="wpp-ans" id="q-17">
                    <p><?php _e('If checked, and if the Post Excerpt feature is enabled, Wordpress Popular Posts will keep the styling tags (eg. bold, italic, etc) that were found in the excerpt. Hyperlinks will remain intact, too.', 'wordpress-popular-posts'); ?></p>
                </div>
                <h4><a href="#" rel="q-14"><?php _e('What does "Exclude Categories" do?', 'wordpress-popular-posts'); ?></a></h4>
                <div class="wpp-ans" id="q-14">
                    <p><?php _e('If checked, Wordpress Popular Posts will exclude from the listing all those entries that belong to specific categories. When entering more than one Category ID, you need to use commas to separate them (eg. 1,5,12 - no spaces!). Disabled by default.', 'wordpress-popular-posts'); ?></p>
                </div>
                <h4><a href="#" rel="q-5"><?php _e('What does "Display post thumbnail" do?', 'wordpress-popular-posts'); ?></a></h4>
                <div class="wpp-ans" id="q-5">
                    <p><?php _e('If checked, Wordpress Popular Posts will attempt to use the thumbnail you have selected for each post on the Post Edit Screen under Featured Image (this also requires including add_theme_support("post-thumbnails") to your theme\'s functions.php file). Disabled by default.', 'wordpress-popular-posts'); ?></p>
                </div>
                <h4><a href="#" rel="q-6"><?php _e('What does "Display comment count" do?', 'wordpress-popular-posts'); ?></a></h4>
                <div class="wpp-ans" id="q-6">
                    <p><?php _e('If checked, Wordpress Popular Posts will display how many comments each popular post has got until now. Enabled by default.', 'wordpress-popular-posts'); ?></p>
                </div>
                <h4><a href="#" rel="q-7"><?php _e('What does "Display views" do?', 'wordpress-popular-posts'); ?></a></h4>
                <div class="wpp-ans" id="q-7">
                    <p><?php _e('If checked, Wordpress Popular Posts will show how many pageviews a single post has gotten so far since this plugin was installed. Disabled by default.', 'wordpress-popular-posts'); ?></p>
                </div>
                <h4><a href="#" rel="q-8"><?php _e('What does "Display author" do?', 'wordpress-popular-posts'); ?></a></h4>
                <div class="wpp-ans" id="q-8">
                    <p><?php _e('If checked, Wordpress Popular Posts will display the name of the author of each entry listed. Disabled by default.', 'wordpress-popular-posts'); ?></p>
                </div>
                <h4><a href="#" rel="q-9"><?php _e('What does "Display date" do?', 'wordpress-popular-posts'); ?></a></h4>
                <div class="wpp-ans" id="q-9">
                    <p><?php _e('If checked, Wordpress Popular Posts will display the date when each popular posts was published. Disabled by default.', 'wordpress-popular-posts'); ?></p>
                </div>
                <h4><a href="#" rel="q-10"><?php _e('What does "Use custom HTML Markup" do?', 'wordpress-popular-posts'); ?></a></h4>
                <div class="wpp-ans" id="q-10">
                    <p><?php _e('If checked, you will be able to customize the HTML markup of your popular posts listing. For example, you can decide whether to wrap your posts in an unordered list, an ordered list, a div, etc. If you know xHTML/CSS, this is for you! Disabled by default.', 'wordpress-popular-posts'); ?></p>
                </div>
                <h4><a href="#" rel="q-11"><?php _e('What does "Use content formatting tags" do?', 'wordpress-popular-posts'); ?></a></h4>
                <div class="wpp-ans" id="q-11">
                    <p><?php _e('If checked, you can decide the order of the items displayed on each entry. For example, setting it to "{title}: {summary}" (without the quotes) would display "Post title: excerpt of the post here". Available tags: {image}, {title}, {summary}, {stats} and {rating}. Disabled by default.', 'wordpress-popular-posts'); ?></p>
                </div>
                <h4><a href="#" rel="q-15"><?php _e('What are "Template Tags"?', 'wordpress-popular-posts'); ?></a></h4>
                <div class="wpp-ans" id="q-15">
                    <p><?php _e('Template Tags are simply php functions that allow you to perform certain actions. For example, Wordpress Popular Posts currently supports two different template tags: get_mostpopular() and wpp_get_views().', 'wordpress-popular-posts'); ?></p>
                </div>
                <h4><a href="#" rel="q-16"><?php _e('What are the template tags that Wordpress Popular Posts supports?', 'wordpress-popular-posts'); ?></a></h4>
                <div class="wpp-ans" id="q-16">
                    <p><?php _e('The following are the template tags supported by Wordpress Popular Posts:', 'wordpress-popular-posts'); ?></p>
                    <table cellpadding="0" cellspacing="0" class="attr_table">
                    	<tr>
                        	<td class="attr_heading"><?php _e('Template tag', 'wordpress-popular-posts'); ?></td>
                            <td class="attr_heading"><?php _e('What it does ', 'wordpress-popular-posts'); ?></td>
                            <td class="attr_heading"><?php _e('Parameters', 'wordpress-popular-posts'); ?></td>
                            <td class="attr_heading"><?php _e('Example', 'wordpress-popular-posts'); ?></td>
                        </tr>
                        <tr>
                        	<td><strong>get_mostpopular()</strong></td>
                            <td><?php _e('Similar to the widget functionality, this tag retrieves the most popular posts on your blog. While it can be customized via parameters, these are not needed for it to work.', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Please refer to "What attributes does Wordpress Popular Posts shortcode [wpp] have?"', 'wordpress-popular-posts'); ?></td>
                            <td>&lt;?php get_mostpopular(); ?&gt;<br />&lt;?php get_mostpopular("range=weekly&amp;limit=7"); ?&gt;</td>
                        </tr>
                        <tr>
                        	<td><strong>wpp_get_views()</strong></td>
                            <td><?php _e('Displays the number of views of a single post. Post ID required, or it will return false.', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Post ID', 'wordpress-popular-posts'); ?></td>
                            <td>&lt;?php wpp_get_views($post->ID); ?&gt;<br />&lt;?php wpp_get_views(15); ?&gt;</td>
                        </tr>
                    </table>
                </div>
                <h4><a href="#" rel="q-12"><?php _e('What are "shortcodes"?', 'wordpress-popular-posts'); ?></a></h4>
                <div class="wpp-ans" id="q-12">
                    <p><?php _e('Shortcodes are hooks that allow us to call a php function by simply typing something like [shortcode]. With Wordpress Popular Posts, the shortcode [wpp] will let you insert a list of the most popular posts in posts content and pages too! For more information about shortcodes, please visit', 'wordpress-popular-posts', 'wordpress-popular-posts'); ?> <a href="http://codex.wordpress.org/Shortcode_API" target="_blank">Wordpress Shortcode API</a>.</p>
                </div>
                <h4><a href="#" rel="q-13"><?php _e('What attributes does Wordpress Popular Posts shortcode [wpp] have?', 'wordpress-popular-posts'); ?></a></h4>
                <div class="wpp-ans" id="q-13">
                    <p><?php _e('There are a number of attributes Wordpress Popular Posts currently supports:', 'wordpress-popular-posts'); ?>:</p>
                    <table cellpadding="0" cellspacing="0" class="attr_table">
                    	<tr>
                        	<td class="attr_heading"><?php _e('Attributes', 'wordpress-popular-posts'); ?></td>
                            <td class="attr_heading"><?php _e('What it does ', 'wordpress-popular-posts'); ?></td>
                            <td class="attr_heading"><?php _e('Possible values', 'wordpress-popular-posts'); ?></td>
                            <td class="attr_heading"><?php _e('Defaults to', 'wordpress-popular-posts'); ?></td>
                            <td class="attr_heading"><?php _e('Example', 'wordpress-popular-posts'); ?></td>
                        </tr>
                        <tr>
                        	<td><strong>header</strong></td>
                            <td><?php _e('Sets a heading for the list', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                            <td align="center"><?php _e('Popular Posts', 'wordpress-popular-posts'); ?></td>
                            <td>header="Popular Posts"</td>
                        </tr>
                        <tr>
                        	<td><strong>header_start</strong></td>
                            <td><?php _e('Set the opening tag for the heading of the list', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                            <td align="center">&lt;h2&gt;</td>
                            <td>header_start="&lt;h2&gt;"</td>
                        </tr>
                        <tr>
                        	<td><strong>header_end</strong></td>
                            <td><?php _e('Set the closing tag for the heading of the list', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                            <td align="center">&lt;/h2&gt;</td>
                            <td>header_end="&lt;/h2&gt;"</td>
                        </tr>
                        <tr>
                        	<td><strong>limit</strong></td>
                            <td><?php _e('Sets the maximum number of popular posts to be shown on the listing', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Positive integer', 'wordpress-popular-posts'); ?></td>
                            <td align="center">10</td>
                            <td>limit=10</td>
                        </tr>
                        <tr>
                        	<td><strong>range</strong></td>
                            <td><?php _e('Tells Wordpress Popular Posts to retrieve the most popular entries within the time range specified by you', 'wordpress-popular-posts'); ?></td>
                            <td>"daily", "weekly", "monthly", "all"</td>
                            <td align="center">daily</td>
                            <td>range="daily"</td>
                        </tr>
                        <tr>
                        	<td><strong>order_by</strong></td>
                            <td><?php _e('Sets the sorting option of the popular posts', 'wordpress-popular-posts'); ?></td>
                            <td>"comments", "views", "avg" <?php _e('(for average views per day)', 'wordpress-popular-posts'); ?></td>
                            <td align="center">comments</td>
                            <td>order_by="comments"</td>
                        </tr>
                        <tr>
                        	<td><strong>pages</strong></td>
                            <td><?php _e('Tells Wordpress Popular Posts whether to consider or not pages while building the popular list', 'wordpress-popular-posts'); ?></td>
                            <td>1 (true), (0) false</td>
                            <td align="center">1</td>
                            <td>pages=1</td>
                        </tr>
                        <tr>
                        	<td><strong>title_length</strong></td>
                            <td><?php _e('If set, Wordpress Popular Posts will shorten each post title to "n" characters whenever possible', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Positive integer', 'wordpress-popular-posts'); ?></td>
                            <td align="center">25</td>
                            <td>title_length=25</td>
                        </tr>
                        <tr>
                        	<td><strong>excerpt_length</strong></td>
                            <td><?php _e('If set, Wordpress Popular Posts will build and include an excerpt of "n" characters long from the content of each post listed as popular', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Positive integer', 'wordpress-popular-posts'); ?></td>
                            <td align="center">55</td>
                            <td>excerpt_length=55</td>
                        </tr>
                        <tr>
                        	<td><strong>excerpt_format</strong></td>
                            <td><?php _e('If set, Wordpress Popular Posts will maintaing all styling tags (strong, italic, etc) and hyperlinks found in the excerpt', 'wordpress-popular-posts'); ?></td>
                            <td>1 (true), (0) false</td>
                            <td align="center">0</td>
                            <td>excerpt_format=1</td>
                        </tr>
                        <tr>
                        	<td><strong>cats_to_exclude</strong></td>
                            <td><?php _e('If set, Wordpress Popular Posts will exclude all entries that belong to the specified category(ies).', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                            <td align="center"><?php _e('None', 'wordpress-popular-posts'); ?></td>
                            <td>cats_to_exclude="1,55,74"</td>
                        </tr>
                        <tr>
                        	<td><strong>thumbnail_width</strong></td>
                            <td><?php _e('If set, and if your current server configuration allows it, you will be able to display thumbnails of your posts. This attribute sets the width for thumbnails', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Positive integer', 'wordpress-popular-posts'); ?></td>
                            <td align="center">15</td>
                            <td>thumbnail_width=30</td>
                        </tr>
                        <tr>
                        	<td><strong>thumbnail_height</strong></td>
                            <td><?php _e('If set, and if your current server configuration allows it, you will be able to display thumbnails of your posts. This attribute sets the height for thumbnails', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Positive integer', 'wordpress-popular-posts'); ?></td>
                            <td align="center">15</td>
                            <td>thumbnail_height=30</td>
                        </tr>                        
                        <tr>
                        	<td><strong>thumbnail_selection</strong></td>
                            <td><?php _e('Wordpress Popular Posts will use the thumbnails selected by you. *Requires enabling The Post Thumbnail feature on your theme*', 'wordpress-popular-posts'); ?></td>
                            <td>"usergenerated"</td>
                            <td align="center">usergenerated</td>
                            <td>thumbnail_selection="usergenerated"</td>
                        </tr>
                        <tr>
                        	<td><strong>rating</strong></td>
                            <td><?php _e('If set, and if the WP-PostRatings plugin is installed and enabled on your blog, Wordpress Popular Posts will show how your visitors are rating your entries', 'wordpress-popular-posts'); ?></td>
                            <td>1 (true), (0) false</td>
                            <td align="center">0</td>
                            <td>rating=1</td>
                        </tr>
                        <tr>
                        	<td><strong>stats_comments</strong></td>
                            <td><?php _e('If set, Wordpress Popular Posts will show how many comments each popular post has got until now', 'wordpress-popular-posts'); ?></td>
                            <td>1 (true), 0 (false)</td>
                            <td align="center">1</td>
                            <td>stats_comments=1</td>
                        </tr>
                        <tr>
                        	<td><strong>stats_views</strong></td>
                            <td><?php _e('If set, Wordpress Popular Posts will show how many views each popular post has got since it was installed', 'wordpress-popular-posts'); ?></td>
                            <td>1 (true), (0) false</td>
                            <td align="center">0</td>
                            <td>stats_views=1</td>
                        </tr>
                        <tr>
                        	<td><strong>stats_author</strong></td>
                            <td><?php _e('If set, Wordpress Popular Posts will show who published each popular post on the list', 'wordpress-popular-posts'); ?></td>
                            <td>1 (true), (0) false</td>
                            <td align="center">0</td>
                            <td>stats_author=1</td>
                        </tr>
                        <tr>
                        	<td><strong>stats_date</strong></td>
                            <td><?php _e('If set, Wordpress Popular Posts will when each popular post on the list was published', 'wordpress-popular-posts'); ?></td>
                            <td>1 (true), (0) false</td>
                            <td align="center">0</td>
                            <td>stats_date=1</td>
                        </tr>
                        <tr>
                        	<td><strong>stats_date_format</strong></td>
                            <td><?php _e('Sets the date format', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                            <td align="center">0</td>
                            <td>stats_date_format='F j, Y'</td>
                        </tr>
                        <tr>
                        	<td><strong>wpp_start</strong></td>
                            <td><?php _e('Sets the opening tag for the listing', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                            <td align="center">&lt;ul&gt;</td>
                            <td>wpp_start="&lt;ul&gt;"</td>
                        </tr>
                        <tr>
                        	<td><strong>wpp_end</strong></td>
                            <td><?php _e('Sets the closing tag for the listing', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                            <td align="center">&lt;/ul&gt;</td>
                            <td>wpp_end="&lt;/ul&gt;"</td>
                        </tr>
                        <tr>
                        	<td><strong>post_start</strong></td>
                            <td><?php _e('Sets the opening tag for each item on the list', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                            <td align="center">&lt;li&gt;</td>
                            <td>post_start="&lt;li&gt;"</td>
                        </tr>
                        <tr>
                        	<td><strong>post_end</strong></td>
                            <td><?php _e('Sets the closing tag for each item on the list', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                            <td align="center">&lt;/li&gt;</td>
                            <td>post_end="&lt;/li&gt;"</td>
                        </tr>                        
                        <tr>
                        	<td><strong>do_pattern</strong></td>
                            <td><?php _e('If set, this option will allow you to decide the order of the contents within each item on the list.', 'wordpress-popular-posts'); ?></td>
                            <td>1 (true), (0) false</td>
                            <td align="center">0</td>
                            <td>do_pattern=1</td>
                        </tr>
                        <tr>
                        	<td><strong>pattern_form</strong></td>
                            <td><?php _e('If set, you can decide the order of each content inside a single item on the list. For example, setting it to "{title}: {summary}" would output something like "Your Post Title: summary here". This attribute requires do_pattern to be true.', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Available tags', 'wordpress-popular-posts'); ?>: {image}, {title}, {summary}, {stats}, {rating}</td>
                            <td align="center">{image} {title}: {summary} {stats}</td>
                            <td>pattern_form="{image} {title}: {summary} {stats}"</td>
                        </tr>
                    </table>
                </div>
            </td><!-- end help area -->
            <td width="15">&nbsp;</td><!-- end spacer -->
            <td valign="top"><!-- maintenance -->                
                <h3><?php _e('Maintenance Settings', 'wordpress-popular-posts'); ?></h3>
                <p><?php _e('Wordpress Popular Posts keeps historical data of your most popular entries for up to 30 days. If for some reason you need to clear the cache table, or even both historical and cache tables, please use the buttons below to do so.', 'wordpress-popular-posts') ?></p><br />
                <table cellpadding="5" cellspacing="1">
                	<tr>
                    	<td valign="top" width="140"><input type="button" name="wpp-reset-cache" id="wpp-reset-cache" class="button-secondary" value="<?php _e("Empty cache", "wordpress-popular-posts"); ?>" onclick="confirm_reset_cache()" /></td>
                    	<td><label for="wpp-reset-cache"><small><?php _e('Use this button to manually clear entries from WPP cache only', 'wordpress-popular-posts'); ?></small></label></td>
                    </tr>
                    <tr>
                    	<td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                    	<td valign="top"><input type="button" name="wpp-reset-all" id="wpp-reset-all" class="button-secondary" value="<?php _e("Clear all data", "wordpress-popular-posts"); ?>" onclick="confirm_reset_all()" /></td>
                    	<td><label for="wpp-reset-all"><small><?php _e('Use this button to manually clear entries from all WPP data tables', 'wordpress-popular-posts'); ?></small></label></td>
                    </tr>
                </table>
            </td><!-- end maintenance -->
        </tr>
    </table>
    <br />
    <hr />
    <p><?php _e('Do you like this plugin?', 'wordpress-popular-posts'); ?> <a title="<?php _e('Rate Wordpress Popular Posts!', 'wordpress-popular-posts'); ?>" href="http://wordpress.org/extend/plugins/wordpress-popular-posts/#rate-response" target="_blank"><strong><?php _e('Rate it 5', 'wordpress-popular-posts'); ?></strong></a> <?php _e('on the official Plugin Directory!', 'wordpress-popular-posts'); ?></p>
    <p><?php _e('Do you love this plugin?', 'wordpress-popular-posts'); ?> <a title="<?php _e('Buy me a beer!', 'wordpress-popular-posts'); ?>" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=dadslayer%40gmail%2ecom&lc=GB&item_name=Wordpress%20Popular%20Posts%20Plugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG_global%2egif%3aNonHosted" target="_blank"><strong><?php _e('Buy me a beer!', 'wordpress-popular-posts'); ?></strong></a>. <?php _e('Each donation motivates me to keep releasing free stuff for the Wordpress community!', 'wordpress-popular-posts'); ?></p>
    <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=dadslayer%40gmail%2ecom&lc=GB&item_name=Wordpress%20Popular%20Posts%20Plugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG_global%2egif%3aNonHosted" target="_blank" rel="external nofollow"><img src="<?php echo get_bloginfo('url') . "/" . PLUGINDIR; ?>/wordpress-popular-posts/btn_donateCC_LG_global.gif" width="122" height="47" alt="<?php _e('Buy me a beer!', 'wordpress-popular-posts'); ?>" border="0" /></a>
    <?php
		
	?>
</div>