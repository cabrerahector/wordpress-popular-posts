<?php
	if (basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__)) exit('Please do not load this page directly');

	/**
	 * Merges two associative arrays recursively
	 * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
	 * => array('key' => array('new value'));
	 * Source: http://www.php.net/manual/en/function.array-merge-recursive.php#92195
	 * Since 2.3.4
	 */
	function array_merge_recursive_distinct( array &$array1, array &$array2 ) {
		$merged = $array1;

		foreach ( $array2 as $key => &$value ) {
			if ( is_array( $value ) && isset ( $merged[$key] ) && is_array( $merged[$key] ) ) {
				$merged[$key] = array_merge_recursive_distinct ( $merged[$key], $value );
			} else {
				$merged[$key] = $value;
			}
		}

		return $merged;
	}

	$wpp_settings_def = array(
		'stats' => array(
			'order_by' => 'views',
			'limit' => 10,
			'post_type' => 'post,page'
		),
		'tools' => array(
			'ajax' => false,
			'css' => true,
			'stylesheet' => true,
			'link' => array(
				'target' => '_self'
			),
			'thumbnail' => array(
				'source' => 'featured',
				'field' => '_wpp_thumbnail',
				'resize' => false,
				'default' => ''
			),
			'log_loggedin' => false,
			'cache' => array(
				'active' => false,
				'interval' => array(
					'time' => 'hour',
					'value' => 1
				)
			)
		)
	);

	$ops = get_option('wpp_settings_config');
	if ( !$ops ) {
		add_option('wpp_settings_config', $wpp_settings_def);
		$ops = $wpp_settings_def;
	} else {
		$ops = array_merge_recursive_distinct( $wpp_settings_def, $ops );
	}

	if ( isset($_POST['section']) ) {
		if ($_POST['section'] == "stats") {
			$ops['stats']['order_by'] = $_POST['stats_order'];
			$ops['stats']['limit'] = (is_numeric($_POST['stats_limit']) && $_POST['stats_limit'] > 0) ? $_POST['stats_limit'] : 10;
			$ops['stats']['post_type'] = empty($_POST['stats_type']) ? "post,page" : $_POST['stats_type'];

			update_option('wpp_settings_config', $ops);
			echo "<div class=\"updated\"><p><strong>" . __('Settings saved.', 'wordpress-popular-posts' ) . "</strong></p></div>";

		} else if ($_POST['section'] == "linking") {
			
			$ops['tools']['link']['target'] = $_POST['link_target'];
			update_option('wpp_settings_config', $ops);
			echo "<div class=\"updated\"><p><strong>" . __('Settings saved.', 'wordpress-popular-posts' ) . "</strong></p></div>";
			
		}else if ($_POST['section'] == "logging") {

			$ops['tools']['log_loggedin'] = $_POST['log_option'];
			update_option('wpp_settings_config', $ops);
			echo "<div class=\"updated\"><p><strong>" . __('Settings saved.', 'wordpress-popular-posts' ) . "</strong></p></div>";

		} else if ($_POST['section'] == "tools") {

			if ($_POST['thumb_source'] == "custom_field" && (!isset($_POST['thumb_field']) || empty($_POST['thumb_field']))) {
				echo '<div id="wpp-message" class="error fade"><p>'.__('Please provide the name of your custom field.', 'wordpress-popular-posts').'</p></div>';
			} else {
				$ops['tools']['thumbnail']['source'] = $_POST['thumb_source'];
				$ops['tools']['thumbnail']['field'] = ( !empty( $_POST['thumb_field']) ) ? $_POST['thumb_field'] : "_wpp_thumbnail";
				$ops['tools']['thumbnail']['default'] = ( !empty( $_POST['upload_thumb_src']) ) ? $_POST['upload_thumb_src'] : "";
				$ops['tools']['thumbnail']['resize'] = $_POST['thumb_field_resize'];

				update_option('wpp_settings_config', $ops);
				echo "<div class=\"updated\"><p><strong>" . __('Settings saved.', 'wordpress-popular-posts' ) . "</strong></p></div>";
			}
		} else if  ($_POST['section'] == "ajax") {

			$ops['tools']['ajax'] = $_POST['ajax'];

			$ops['tools']['cache']['active'] = $_POST['cache'];
			$ops['tools']['cache']['interval']['time'] = $_POST['cache_interval_time'];
			$ops['tools']['cache']['interval']['value'] = $_POST['cache_interval_value'];

			update_option('wpp_settings_config', $ops);
			echo "<div class=\"updated\"><p><strong>" . __('Settings saved.', 'wordpress-popular-posts' ) . "</strong></p></div>";

		} else if  ($_POST['section'] == "css") {
			$ops['tools']['css'] = $_POST['css'];

			//print_r($ops);

			update_option('wpp_settings_config', $ops);
			echo "<div class=\"updated\"><p><strong>" . __('Settings saved.', 'wordpress-popular-posts' ) . "</strong></p></div>";
		}
	}

	$rand = md5(uniqid(rand(), true));
	$wpp_rand = get_option("wpp_rand");
	if (empty($wpp_rand)) {
		add_option("wpp_rand", $rand);
	} else {
		update_option("wpp_rand", $rand);
	}

?>


<style>
	#wmpp-title {
		color:#666;
		font-family:Georgia, "Times New Roman", Times, serif;
		font-weight:100;
		font-size:24px;
		font-style:italic;
	}

	.wmpp-subtitle {
		margin:8px 0 15px 0;
		color:#666;
		font-family:Georgia, "Times New Roman", Times, serif;
		font-size:16px;
		font-weight:100;
	}

	.wpp_boxes {
		display:none;
		overflow:hidden;
		width:100%;
	}

	#wpp-options {
		width:100%;
	}

		#wpp-options fieldset {
			margin:0 0 15px 0;
			width:99%;
		}

			#wpp-options fieldset legend { font-weight:bold; }

			#wpp-options fieldset .lbl_wpp_stats {
				display:block;
				margin:0 0 8px 0;
			}

	#wpp-stats-tabs {
		padding:2px 0;
	}

	#wpp-stats-canvas {
		overflow:hidden;
		padding:2px 0;
		width:100%;
	}

		.wpp-stats {
			display:none;
			width:96%px;
			padding:1% 0;
			font-size:8px;
			background:#fff;
			border:#999 3px solid;
		}

		.wpp-stats-active {
			display:block;
		}

			.wpp-stats ol {
				margin:0;
				padding:0;
			}

				.wpp-stats ol li {
					overflow:hidden;
					margin:0 8px 10px 8px!important;
					padding:0 0 2px 0!important;
					font-size:12px;
					line-height:12px;
					color:#999;
					border-bottom:#eee 1px solid;
				}

					.wpp-post-title {
						/*display:block;*/
						display:inline;
						float:left;
						font-weight:bold;
					}

					.post-stats {
						display:inline;
						float:right;
						font-size:0.9em!important;
						text-align:right;
						color:#999;
					}

			.wpp-stats-unique-item, .wpp-stats-last-item {
				margin:0!important;
				padding:0!important;
				border:none!important;
			}
			/**/
			.wpp-stats p {
				margin:0;
				padding:0 8px;
				font-size:12px;
			}

	.wp-list-table h4 {
		margin:0 0 0 0;
	}

	.wpp-ans {
		display:none;
		width:100%;
	}

		.wpp-ans p {
			margin:0 0 0 0;
			padding:0;
		}

</style>

<script type="text/javascript">
	jQuery(document).ready(function(){

		// TABS
		jQuery(".subsubsub li a").click(function(e){
			var tab = jQuery(this);
			tab.addClass("current").parent().siblings().children("a").removeClass("current");

			jQuery(".wpp_boxes:visible").hide();
			jQuery("#" + tab.attr("rel")).fadeIn();

			e.preventDefault();
		});

		// STATISTICS TABS
		jQuery("#wpp-stats-tabs a").click(function(e){
			var activeTab = jQuery(this).attr("rel");
			jQuery(this).removeClass("button-secondary").addClass("button-primary").siblings().removeClass("button-primary").addClass("button-secondary");
			jQuery(".wpp-stats:visible").fadeOut("fast", function(){
				jQuery("#"+activeTab).slideDown("fast");
			});

			e.preventDefault();
		});

		jQuery(".wpp-stats").each(function(){
			if (jQuery("li", this).length == 1) {
				jQuery("li", this).addClass("wpp-stats-last-item");
			} else {
				jQuery("li:last", this).addClass("wpp-stats-last-item");
			}
		});

		// FAQ
		jQuery(".wp-list-table a").click(function(e){
			var ans = jQuery(this).attr("rel");

			jQuery(".wpp-ans:visible").hide();
			//jQuery("#"+ans).slideToggle();
			jQuery("#"+ans).show();

			e.preventDefault();
		});

		// TOOLS
		// thumb source selection
		jQuery("#thumb_source").change(function() {
			if (jQuery(this).val() == "custom_field") {
				jQuery("#lbl_field, #thumb_field, #row_custom_field, #row_custom_field_resize").show();
			} else {
				jQuery("#lbl_field, #thumb_field, #row_custom_field, #row_custom_field_resize").hide();
			}
		});
		// cache interval
		jQuery("#cache").change(function() {
			if (jQuery(this).val() == 1) {
				jQuery("#cache_refresh_interval").show();
			} else {
				jQuery("#cache_refresh_interval, #cache_too_long").hide();
			}
		});
		// interval
		jQuery("#cache_interval_time").change(function() {
			var value = parseInt( jQuery("#cache_interval_value").val() );
			var time = jQuery(this).val();

			console.log(time + " " + value);

			if ( time == "hour" && value > 72 ) {
				jQuery("#cache_too_long").show();
			} else if ( time == "day" && value > 3 ) {
				jQuery("#cache_too_long").show();
			} else if ( time == "week" && value > 1 ) {
				jQuery("#cache_too_long").show();
			} else if ( time == "month" && value >= 1 ) {
				jQuery("#cache_too_long").show();
			} else if ( time == "year" && value >= 1 ) {
				jQuery("#cache_too_long").show();
			} else {
				jQuery("#cache_too_long").hide();
			}
		});

		jQuery("#cache_interval_value").change(function() {
			var value = parseInt( jQuery(this).val() );
			var time = jQuery("#cache_interval_time").val();

			if ( time == "hour" && value > 72 ) {
				jQuery("#cache_too_long").show();
			} else if ( time == "day" && value > 3 ) {
				jQuery("#cache_too_long").show();
			} else if ( time == "week" && value > 1 ) {
				jQuery("#cache_too_long").show();
			} else if ( time == "month" && value >= 1 ) {
				jQuery("#cache_too_long").show();
			} else if ( time == "year" && value >= 1 ) {
				jQuery("#cache_too_long").show();
			} else {
				jQuery("#cache_too_long").hide();
			}
		});

	});

	// TOOLS
	function confirm_reset_cache() {
		if (confirm("<?php _e("This operation will delete all entries from Wordpress Popular Posts' cache table and cannot be undone.", "wordpress-popular-posts"); ?> \n" + "<?php _e("Do you want to continue?", "wordpress-popular-posts"); ?>")) {
			jQuery.post(ajaxurl, {action: 'wpp_clear_cache', token: '<?php echo get_option("wpp_rand"); ?>', clear: 'cache'}, function(data){
				alert(data);
			});
		}
	}

	function confirm_reset_all() {
		if (confirm("<?php _e("This operation will delete all stored info from Wordpress Popular Posts' data tables and cannot be undone.", "wordpress-popular-posts"); ?> \n" + "<?php _e("Do you want to continue?", "wordpress-popular-posts"); ?>")) {
			jQuery.post(ajaxurl, {action: 'wpp_clear_all', token: '<?php echo get_option("wpp_rand"); ?>', clear: 'all'}, function(data){
				alert(data);
			});
		}
	}

</script>

<div class="wrap">
    <div id="icon-options-general" class="icon32"><br /></div>
    <h2 id="wmpp-title">Wordpress Popular Posts</h2>

    <ul class="subsubsub">
    	<li id="btn_stats"><a href="#" <?php if (!isset($_POST['section']) || (isset($_POST['section']) && $_POST['section'] == "stats") ) {?>class="current"<?php } ?> rel="wpp_stats"><?php _e("Stats", "wordpress-popular-posts"); ?></a> |</li>
        <li id="btn_faq"><a href="#" rel="wpp_faq"><?php _e("FAQ", "wordpress-popular-posts"); ?></a> |</li>
        <li id="btn_tools"><a href="#" rel="wpp_tools"<?php if (isset($_POST['section']) && ($_POST['section'] == "logging" || $_POST['section'] == "tools" || $_POST['section'] == "ajax" || $_POST['section'] == "css") ) {?> class="current"<?php } ?>><?php _e("Tools", "wordpress-popular-posts"); ?></a></li>
    </ul>
    <!-- Start stats -->
    <div id="wpp_stats" class="wpp_boxes"<?php if (!isset($_POST['section']) || (isset($_POST['section']) && $_POST['section'] == "stats") ) {?> style="display:block;"<?php } ?>>
    	<p><?php _e("Click on each tab to see what are the most popular entries on your blog in the last 24 hours, this week, last 30 days or all time since Wordpress Popular Posts was installed.", "wordpress-popular-posts"); ?></p>

        <div class="tablenav top">
            <div class="alignleft actions">
                <form action="" method="post" id="wpp_stats_options" name="wpp_stats_options">
                    <select name="stats_order">
                        <option <?php if ($ops['stats']['order_by'] == "comments") {?>selected="selected"<?php } ?> value="comments"><?php _e("Order by comments", "wordpress-popular-posts"); ?></option>
                        <option <?php if ($ops['stats']['order_by'] == "views") {?>selected="selected"<?php } ?> value="views"><?php _e("Order by views", "wordpress-popular-posts"); ?></option>
                        <option <?php if ($ops['stats']['order_by'] == "avg") {?>selected="selected"<?php } ?> value="avg"><?php _e("Order by avg. daily views", "wordpress-popular-posts"); ?></option>
                    </select>
                    <label for="stats_type"><?php _e("Post type", "wordpress-popular-posts"); ?>:</label> <input type="text" name="stats_type" value="<?php echo $ops['stats']['post_type']; ?>" size="15" />
                    <label for="stats_limits"><?php _e("Limit", "wordpress-popular-posts"); ?>:</label> <input type="text" name="stats_limit" value="<?php echo $ops['stats']['limit']; ?>" size="5" />
                    <input type="hidden" name="section" value="stats" />
                    <input type="submit" class="button-secondary action" value="<?php _e("Apply", "wordpress-popular-posts"); ?>" name="" />
                </form>
            </div>
        </div>
        <br />
        <div id="wpp-stats-tabs">
            <a href="#" class="button-primary" rel="wpp-daily"><?php _e("Last 24 hours", "wordpress-popular-posts"); ?></a>
            <a href="#" class="button-secondary" rel="wpp-weekly"><?php _e("Last 7 days", "wordpress-popular-posts"); ?></a>
            <a href="#" class="button-secondary" rel="wpp-monthly"><?php _e("Last 30 days", "wordpress-popular-posts"); ?></a>
            <a href="#" class="button-secondary" rel="wpp-all"><?php _e("All-time", "wordpress-popular-posts"); ?></a>
        </div>
        <div id="wpp-stats-canvas">
            <div class="wpp-stats wpp-stats-active" id="wpp-daily">
                <?php echo do_shortcode("[wpp range='daily' post_type='".$ops['stats']['post_type']."' stats_comments=1 stats_views=1 order_by='".$ops['stats']['order_by']."' wpp_start='<ol>' wpp_end='</ol>' post_html='<li>{title} <span class=\"post-stats\">{stats}</span></li>' limit=".$ops['stats']['limit']."]"); ?>
            </div>
            <div class="wpp-stats" id="wpp-weekly">
                <?php echo do_shortcode("[wpp range='weekly' post_type='".$ops['stats']['post_type']."' stats_comments=1 stats_views=1 order_by='".$ops['stats']['order_by']."' wpp_start='<ol>' wpp_end='</ol>' post_html='<li>{title} <span class=\"post-stats\">{stats}</span></li>' limit=".$ops['stats']['limit']."]"); ?>
            </div>
            <div class="wpp-stats" id="wpp-monthly">
                <?php echo do_shortcode("[wpp range='monthly' post_type='".$ops['stats']['post_type']."' stats_comments=1 stats_views=1 order_by='".$ops['stats']['order_by']."' wpp_start='<ol>' wpp_end='</ol>' post_html='<li>{title} <span class=\"post-stats\">{stats}</span></li>' limit=".$ops['stats']['limit']."]"); ?>
            </div>
            <div class="wpp-stats" id="wpp-all">
                <?php echo do_shortcode("[wpp range='all' post_type='".$ops['stats']['post_type']."' stats_views=1 order_by='".$ops['stats']['order_by']."' wpp_start='<ol>' wpp_end='</ol>' post_html='<li>{title} <span class=\"post-stats\">{stats}</span></li>' limit=".$ops['stats']['limit']."]"); ?>
            </div>
        </div>
    </div>
    <!-- End stats -->

    <!-- Start faq -->
    <div id="wpp_faq" class="wpp_boxes">
    	<h3 class="wmpp-subtitle"><?php _e("Frequently Asked Questions", "wordpress-popular-posts"); ?></h3>
    	<table cellspacing="0" class="wp-list-table widefat fixed posts">
            <tr>
                <td valign="top"><!-- help area -->
                	<h4>&raquo; <a href="#" rel="q-1"><?php _e('What does "Title" do?', 'wordpress-popular-posts'); ?></a></h4>
                    <div class="wpp-ans" id="q-1">
                        <p><?php _e('It allows you to show a heading for your most popular posts listing. If left empty, no heading will be displayed at all.', 'wordpress-popular-posts'); ?></p>
                    </div>

                	<h4>&raquo; <a href="#" rel="q-2"><?php _e('What is Time Range for?', 'wordpress-popular-posts'); ?></a></h4>
                    <div class="wpp-ans" id="q-2">
                        <p><?php _e('It will tell Wordpress Popular Posts to retrieve all posts with most views / comments within the selected time range.', 'wordpress-popular-posts'); ?></p>
                    </div>

                    <h4>&raquo; <a href="#" rel="q-3"><?php _e('What is "Sort post by" for?', 'wordpress-popular-posts'); ?></a></h4>
                    <div class="wpp-ans" id="q-3">
                        <p><?php _e('It allows you to decide whether to order your popular posts listing by total views, comments, or average views per day.', 'wordpress-popular-posts'); ?></p>
                    </div>

                    <h4>&raquo; <a href="#" rel="q-4"><?php _e('What does "Display post rating" do?', 'wordpress-popular-posts'); ?></a></h4>
                    <div class="wpp-ans" id="q-4">
                        <p><?php _e('If checked, Wordpress Popular Posts will show how your readers are rating your most popular posts. This feature requires having WP-PostRatings plugin installed and enabled on your blog for it to work.', 'wordpress-popular-posts'); ?></p>
                    </div>

                    <h4>&raquo; <a href="#" rel="q-5"><?php _e('What does "Shorten title" do?', 'wordpress-popular-posts'); ?></a></h4>
                    <div class="wpp-ans" id="q-5">
                        <p><?php _e('If checked, all posts titles will be shortened to "n" characters/words. A new "Shorten title to" option will appear so you can set it to whatever you like.', 'wordpress-popular-posts'); ?></p>
                    </div>

                    <h4>&raquo; <a href="#" rel="q-6"><?php _e('What does "Display post excerpt" do?', 'wordpress-popular-posts'); ?></a></h4>
                    <div class="wpp-ans" id="q-6">
                        <p><?php _e('If checked, Wordpress Popular Posts will also include a small extract of your posts in the list. Similarly to the previous option, you will be able to decide how long the post excerpt should be.', 'wordpress-popular-posts'); ?></p>
                    </div>

                    <h4>&raquo; <a href="#" rel="q-7"><?php _e('What does "Keep text format and links" do?', 'wordpress-popular-posts'); ?></a></h4>
                    <div class="wpp-ans" id="q-7">
                        <p><?php _e('If checked, and if the Post Excerpt feature is enabled, Wordpress Popular Posts will keep the styling tags (eg. bold, italic, etc) that were found in the excerpt. Hyperlinks will remain intact, too.', 'wordpress-popular-posts'); ?></p>
                    </div>

                    <h4>&raquo; <a href="#" rel="q-8"><?php _e('What is "Post type" for?', 'wordpress-popular-posts'); ?></a></h4>
                    <div class="wpp-ans" id="q-8">
                        <p><?php _e('This filter allows you to decide which post types to show on the listing. By default, it will retrieve only posts and pages (which should be fine for most cases).', 'wordpress-popular-posts'); ?></p>
                    </div>

                    <h4>&raquo; <a href="#" rel="q-9"><?php _e('What is "Category(ies) ID(s)" for?', 'wordpress-popular-posts'); ?></a></h4>
                    <div class="wpp-ans" id="q-9">
                        <p><?php _e('This filter allows you to select which categories should be included or excluded from the listing. A negative sign in front of the category ID number will exclude posts belonging to it from the list, for example. You can specify more than one ID with a comma separated list.', 'wordpress-popular-posts'); ?></p>
                    </div>

                    <h4>&raquo; <a href="#" rel="q-10"><?php _e('What is "Author(s) ID(s)" for?', 'wordpress-popular-posts'); ?></a></h4>
                    <div class="wpp-ans" id="q-10">
                        <p><?php _e('Just like the Category filter, this one lets you filter posts by author ID. You can specify more than one ID with a comma separated list.', 'wordpress-popular-posts'); ?></p>
                    </div>

                    <h4>&raquo; <a href="#" rel="q-11"><?php _e('What does "Display post thumbnail" do?', 'wordpress-popular-posts'); ?></a></h4>
                    <div class="wpp-ans" id="q-11">
                        <p><?php _e('If checked, Wordpress Popular Posts will attempt to retrieve the thumbnail of each post. You can set up the source of the thumbnail via Settings - Wordpress Popular Posts - Tools.', 'wordpress-popular-posts'); ?></p>
                    </div>

                    <h4>&raquo; <a href="#" rel="q-12"><?php _e('What does "Display comment count" do?', 'wordpress-popular-posts'); ?></a></h4>
                    <div class="wpp-ans" id="q-12">
                        <p><?php _e('If checked, Wordpress Popular Posts will display how many comments each popular post has got in the selected Time Range.', 'wordpress-popular-posts'); ?></p>
                    </div>

                    <h4>&raquo; <a href="#" rel="q-13"><?php _e('What does "Display views" do?', 'wordpress-popular-posts'); ?></a></h4>
                    <div class="wpp-ans" id="q-13">
                        <p><?php _e('If checked, Wordpress Popular Posts will show how many pageviews a single post has gotten in the selected Time Range.', 'wordpress-popular-posts'); ?></p>
                    </div>

                    <h4>&raquo; <a href="#" rel="q-14"><?php _e('What does "Display author" do?', 'wordpress-popular-posts'); ?></a></h4>
                    <div class="wpp-ans" id="q-14">
                        <p><?php _e('If checked, Wordpress Popular Posts will display the name of the author of each entry listed.', 'wordpress-popular-posts'); ?></p>
                    </div>

                    <h4>&raquo; <a href="#" rel="q-15"><?php _e('What does "Display date" do?', 'wordpress-popular-posts'); ?></a></h4>
                    <div class="wpp-ans" id="q-15">
                        <p><?php _e('If checked, Wordpress Popular Posts will display the date when each popular posts was published.', 'wordpress-popular-posts'); ?></p>
                    </div>

                    <h4>&raquo; <a href="#" rel="q-16"><?php _e('What does "Use custom HTML Markup" do?', 'wordpress-popular-posts'); ?></a></h4>
                    <div class="wpp-ans" id="q-16">
                        <p><?php _e('If checked, you will be able to customize the HTML markup of your popular posts listing. For example, you can decide whether to wrap your posts in an unordered list, an ordered list, a div, etc. If you know xHTML/CSS, this is for you!', 'wordpress-popular-posts'); ?></p>
                    </div>

                    <h4>&raquo; <a href="#" rel="q-17"><?php _e('What are "Content Tags"?', 'wordpress-popular-posts'); ?></a></h4>
                    <div class="wpp-ans" id="q-17">
                        <p><?php _e('Content Tags are codes to display a variety of items on your popular posts custom HTML structure. For example, setting it to "{title}: {summary}" (without the quotes) would display "Post title: excerpt of the post here". For more Content Tags, see "List of parameters accepted by wpp_get_mostpopular() and the [wpp] shortcode".', 'wordpress-popular-posts'); ?></p>
                    </div>

                    <h4>&raquo; <a href="#" rel="q-18"><?php _e('What are "Template Tags"?', 'wordpress-popular-posts'); ?></a></h4>
                    <div class="wpp-ans" id="q-18">
                        <p><?php _e('Template Tags are simply php functions that allow you to perform certain actions. For example, Wordpress Popular Posts currently supports two different template tags: wpp_get_mostpopular() and wpp_get_views().', 'wordpress-popular-posts'); ?></p>
                    </div>

                    <h4>&raquo; <a href="#" rel="q-19"><?php _e('What are the template tags that Wordpress Popular Posts supports?', 'wordpress-popular-posts'); ?></a></h4>
                    <div class="wpp-ans" id="q-19">
                        <p><?php _e('The following are the template tags supported by Wordpress Popular Posts', 'wordpress-popular-posts'); ?>:</p>
                        <table cellspacing="0" class="wp-list-table widefat fixed posts">
                        	<thead>
                                <tr>
                                    <th class="manage-column column-title"><?php _e('Template tag', 'wordpress-popular-posts'); ?></th>
                                    <th class="manage-column column-title"><?php _e('What it does ', 'wordpress-popular-posts'); ?></th>
                                    <th class="manage-column column-title"><?php _e('Parameters', 'wordpress-popular-posts'); ?></th>
                                    <th class="manage-column column-title"><?php _e('Example', 'wordpress-popular-posts'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="post type-post status-draft format-standard hentry category-js alternate iedit"><strong>wpp_get_mostpopular()</strong></td>
                                    <td class="post type-post status-draft format-standard hentry category-js iedit"><?php _e('Similar to the widget functionality, this tag retrieves the most popular posts on your blog. This function also accepts parameters so you can customize your popular listing, but these are not required.', 'wordpress-popular-posts'); ?></td>
                                    <td class="post type-post status-draft format-standard hentry category-js alternate iedit"><?php _e('Please refer to "List of parameters accepted by wpp_get_mostpopular() and the [wpp] shortcode".', 'wordpress-popular-posts'); ?></td>
                                    <td class="post type-post status-draft format-standard hentry category-js iedit">&lt;?php wpp_get_mostpopular(); ?&gt;<br />&lt;?php wpp_get_mostpopular("range=weekly&amp;limit=7"); ?&gt;</td>
                                </tr>
                                <tr>
                                    <td><strong>wpp_get_views()</strong></td>
                                    <td><?php _e('Displays the number of views of a single post. Post ID is required or it will return false.', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('Post ID', 'wordpress-popular-posts'); ?>, range ("daily", "weekly", "monthly", "all")</td>
                                    <td>&lt;?php echo wpp_get_views($post->ID); ?&gt;<br />&lt;?php echo wpp_get_views(15, 'weekly'); ?&gt;</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h4>&raquo; <a href="#" rel="q-20"><?php _e('What are "shortcodes"?', 'wordpress-popular-posts'); ?></a></h4>
                    <div class="wpp-ans" id="q-20">
                        <p><?php _e('Shortcodes are similar to BB Codes, these allow us to call a php function by simply typing something like [shortcode]. With Wordpress Popular Posts, the shortcode [wpp] will let you insert a list of the most popular posts in posts content and pages too! For more information about shortcodes, please visit', 'wordpress-popular-posts', 'wordpress-popular-posts'); ?> <a href="http://codex.wordpress.org/Shortcode_API" target="_blank">Wordpress Shortcode API</a>.</p>
                    </div>
                    <h4>&raquo; <a href="#" rel="q-21"><?php _e('List of parameters accepted by wpp_get_mostpopular() and the [wpp] shortcode', 'wordpress-popular-posts'); ?></a></h4>
                    <div class="wpp-ans" id="q-21" style="display:block;">
                        <p><?php _e('These parameters can be used by both the template tag wpp_get_most_popular() and the shortcode [wpp].', 'wordpress-popular-posts'); ?>:</p>
                        <table cellspacing="0" class="wp-list-table widefat fixed posts">
                        	<thead>
                                <tr>
                                    <th class="manage-column column-title"><?php _e('Parameter', 'wordpress-popular-posts'); ?></th>
                                    <th class="manage-column column-title"><?php _e('What it does ', 'wordpress-popular-posts'); ?></th>
                                    <th class="manage-column column-title"><?php _e('Possible values', 'wordpress-popular-posts'); ?></th>
                                    <th class="manage-column column-title"><?php _e('Defaults to', 'wordpress-popular-posts'); ?></th>
                                    <th class="manage-column column-title"><?php _e('Example', 'wordpress-popular-posts'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>header</strong></td>
                                    <td><?php _e('Sets a heading for the list', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('Popular Posts', 'wordpress-popular-posts'); ?></td>
                                    <td>header="Popular Posts"</td>
                                </tr>
                                <tr class="alternate">
                                    <td><strong>header_start</strong></td>
                                    <td><?php _e('Set the opening tag for the heading of the list', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                                    <td>&lt;h2&gt;</td>
                                    <td>header_start="&lt;h2&gt;"</td>
                                </tr>
                                <tr>
                                    <td><strong>header_end</strong></td>
                                    <td><?php _e('Set the closing tag for the heading of the list', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                                    <td>&lt;/h2&gt;</td>
                                    <td>header_end="&lt;/h2&gt;"</td>
                                </tr>
                                <tr class="alternate">
                                    <td><strong>limit</strong></td>
                                    <td><?php _e('Sets the maximum number of popular posts to be shown on the listing', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('Positive integer', 'wordpress-popular-posts'); ?></td>
                                    <td>10</td>
                                    <td>limit=10</td>
                                </tr>
                                <tr>
                                    <td><strong>range</strong></td>
                                    <td><?php _e('Tells Wordpress Popular Posts to retrieve the most popular entries within the time range specified by you', 'wordpress-popular-posts'); ?></td>
                                    <td>"daily", "weekly", "monthly", "all"</td>
                                    <td>daily</td>
                                    <td>range="daily"</td>
                                </tr>
                                <tr class="alternate">
                                    <td><strong>order_by</strong></td>
                                    <td><?php _e('Sets the sorting option of the popular posts', 'wordpress-popular-posts'); ?></td>
                                    <td>"comments", "views", "avg" <?php _e('(for average views per day)', 'wordpress-popular-posts'); ?></td>
                                    <td>views</td>
                                    <td>order_by="comments"</td>
                                </tr>
                                <tr>
                                    <td><strong>post_type</strong></td>
                                    <td><?php _e('Defines the type of posts to show on the listing', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                                    <td>post,page</td>
                                    <td>post_type=post,page,your-custom-post-type</td>
                                </tr>
                                <tr class="alternate">
                                    <td><strong>pid</strong></td>
                                    <td><?php _e('If set, Wordpress Popular Posts will exclude the specified post(s) ID(s) form the listing.', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('None', 'wordpress-popular-posts'); ?></td>
                                    <td>pid="60,25,31"</td>
                                </tr>
                                <tr>
                                    <td><strong>cat</strong></td>
                                    <td><?php _e('If set, Wordpress Popular Posts will retrieve all entries that belong to the specified category(ies) ID(s). If a minus sign is used, the category(ies) will be excluded instead.', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('None', 'wordpress-popular-posts'); ?></td>
                                    <td>cat="1,55,-74"</td>
                                </tr>
                                <tr class="alternate">
                                    <td><strong>author</strong></td>
                                    <td><?php _e('If set, Wordpress Popular Posts will retrieve all entries created by specified author(s) ID(s).', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('None', 'wordpress-popular-posts'); ?></td>
                                    <td>author="75,8,120"</td>
                                </tr>
                                <tr>
                                    <td><strong>title_length</strong></td>
                                    <td><?php _e('If set, Wordpress Popular Posts will shorten each post title to "n" characters whenever possible', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('Positive integer', 'wordpress-popular-posts'); ?></td>
                                    <td>25</td>
                                    <td>title_length=25</td>
                                </tr>
                                <tr>
                                    <td><strong>title_by_words</strong></td>
                                    <td><?php _e('If set to 1, Wordpress Popular Posts will shorten each post title to "n" words instead of characters', 'wordpress-popular-posts'); ?></td>
                                    <td>1 (true), (0) false</td>
                                    <td>0</td>
                                    <td>title_by_words=1</td>
                                </tr>
                                <tr class="alternate">
                                    <td><strong>excerpt_length</strong></td>
                                    <td><?php _e('If set, Wordpress Popular Posts will build and include an excerpt of "n" characters long from the content of each post listed as popular', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('Positive integer', 'wordpress-popular-posts'); ?></td>
                                    <td>0</td>
                                    <td>excerpt_length=55</td>
                                </tr>
                                <tr>
                                    <td><strong>excerpt_format</strong></td>
                                    <td><?php _e('If set, Wordpress Popular Posts will maintaing all styling tags (strong, italic, etc) and hyperlinks found in the excerpt', 'wordpress-popular-posts'); ?></td>
                                    <td>1 (true), (0) false</td>
                                    <td>0</td>
                                    <td>excerpt_format=1</td>
                                </tr>
                                <tr>
                                    <td><strong>excerpt_by_words</strong></td>
                                    <td><?php _e('If set to 1, Wordpress Popular Posts will shorten the excerpt to "n" words instead of characters', 'wordpress-popular-posts'); ?></td>
                                    <td>1 (true), (0) false</td>
                                    <td>0</td>
                                    <td>excerpt_by_words=1</td>
                                </tr>
                                <tr class="alternate">
                                    <td><strong>thumbnail_width</strong></td>
                                    <td><?php _e('If set, and if your current server configuration allows it, you will be able to display thumbnails of your posts. This attribute sets the width for thumbnails', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('Positive integer', 'wordpress-popular-posts'); ?></td>
                                    <td>15</td>
                                    <td>thumbnail_width=30</td>
                                </tr>
                                <tr>
                                    <td><strong>thumbnail_height</strong></td>
                                    <td><?php _e('If set, and if your current server configuration allows it, you will be able to display thumbnails of your posts. This attribute sets the height for thumbnails', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('Positive integer', 'wordpress-popular-posts'); ?></td>
                                    <td>15</td>
                                    <td>thumbnail_height=30</td>
                                </tr>
                                <tr class="alternate">
                                    <td><strong>rating</strong></td>
                                    <td><?php _e('If set, and if the WP-PostRatings plugin is installed and enabled on your blog, Wordpress Popular Posts will show how your visitors are rating your entries', 'wordpress-popular-posts'); ?></td>
                                    <td>1 (true), (0) false</td>
                                    <td>0</td>
                                    <td>rating=1</td>
                                </tr>
                                <tr>
                                    <td><strong>stats_comments</strong></td>
                                    <td><?php _e('If set, Wordpress Popular Posts will show how many comments each popular post has got until now', 'wordpress-popular-posts'); ?></td>
                                    <td>1 (true), 0 (false)</td>
                                    <td>1</td>
                                    <td>stats_comments=1</td>
                                </tr>
                                <tr class="alternate">
                                    <td><strong>stats_views</strong></td>
                                    <td><?php _e('If set, Wordpress Popular Posts will show how many views each popular post has got since it was installed', 'wordpress-popular-posts'); ?></td>
                                    <td>1 (true), (0) false</td>
                                    <td>0</td>
                                    <td>stats_views=1</td>
                                </tr>
                                <tr>
                                    <td><strong>stats_author</strong></td>
                                    <td><?php _e('If set, Wordpress Popular Posts will show who published each popular post on the list', 'wordpress-popular-posts'); ?></td>
                                    <td>1 (true), (0) false</td>
                                    <td>0</td>
                                    <td>stats_author=1</td>
                                </tr>
                                <tr class="alternate">
                                    <td><strong>stats_date</strong></td>
                                    <td><?php _e('If set, Wordpress Popular Posts will display the date when each popular post on the list was published', 'wordpress-popular-posts'); ?></td>
                                    <td>1 (true), (0) false</td>
                                    <td>0</td>
                                    <td>stats_date=1</td>
                                </tr>
                                <tr>
                                    <td><strong>stats_date_format</strong></td>
                                    <td><?php _e('Sets the date format', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                                    <td>0</td>
                                    <td>stats_date_format='F j, Y'</td>
                                </tr>
                                <tr class="alternate">
                                    <td><strong>stats_category</strong></td>
                                    <td><?php _e('If set, Wordpress Popular Posts will display the category', 'wordpress-popular-posts'); ?></td>
                                    <td>1 (true), (0) false</td>
                                    <td>0</td>
                                    <td>stats_category=1</td>
                                </tr>
                                <tr>
                                    <td><strong>wpp_start</strong></td>
                                    <td><?php _e('Sets the opening tag for the listing', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                                    <td>&lt;ul&gt;</td>
                                    <td>wpp_start="&lt;ul&gt;"</td>
                                </tr>
                                <tr class="alternate">
                                    <td><strong>wpp_end</strong></td>
                                    <td><?php _e('Sets the closing tag for the listing', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                                    <td>&lt;/ul&gt;</td>
                                    <td>wpp_end="&lt;/ul&gt;"</td>
                                </tr>
                                <tr>
                                    <td><strong>post_html</strong></td>
                                    <td><?php _e('Sets the HTML structure of each post', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('Text string, custom HTML', 'wordpress-popular-posts'); ?>.<br /><br /><strong><?php _e('Available Content Tags', 'wordpress-popular-posts'); ?>:</strong> <br /><em>{thumb}</em> (<?php _e('displays thumbnail linked to post/page', 'wordpress-popular-posts'); ?>)<br /> <em>{title}</em> (<?php _e('displays linked post/page title', 'wordpress-popular-posts'); ?>)<br /> <em>{summary}</em> (<?php _e('displays post/page excerpt, and requires excerpt_length to be greater than 0', 'wordpress-popular-posts'); ?>)<br /> <em>{stats}</em> (<?php _e('displays the default stats tags', 'wordpress-popular-posts'); ?>)<br /> <em>{rating}</em> (<?php _e('displays post/page current rating, requires WP-PostRatings installed and enabled', 'wordpress-popular-posts'); ?>)<br /> <em>{score}</em> (<?php _e('displays post/page current rating as an integer, requires WP-PostRatings installed and enabled', 'wordpress-popular-posts'); ?>)<br /> <em>{url}</em> (<?php _e('outputs the URL of the post/page', 'wordpress-popular-posts'); ?>)<br /> <em>{text_title}</em> (<?php _e('displays post/page title, no link', 'wordpress-popular-posts'); ?>)<br /> <em>{author}</em> (<?php _e('displays linked author name, requires stats_author=1', 'wordpress-popular-posts'); ?>)<br /> <em>{category}</em> (<?php _e('displays linked category name, requires stats_category=1', 'wordpress-popular-posts'); ?>)<br /> <em>{views}</em> (<?php _e('displays views count only, no text', 'wordpress-popular-posts'); ?>)<br /> <em>{comments}</em> (<?php _e('displays comments count only, no text, requires stats_comments=1', 'wordpress-popular-posts'); ?>)</td>
                                    <td>&lt;li&gt;{thumb} {title} {stats}&lt;/li&gt;</td>
                                    <td>post_html="&lt;li&gt;{thumb} &lt;a href='{url}'&gt;{text_title}&lt;/a&gt; &lt;/li&gt;"</td>
                                </tr>
                                <!--<tr class="alternate">
                                    <td><strong>post_start</strong></td>
                                    <td><?php _e('Sets the opening tag for each item on the list', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                                    <td>&lt;li&gt;</td>
                                    <td>post_start="&lt;li&gt;"</td>
                                </tr>
                                <tr>
                                    <td><strong>post_end</strong></td>
                                    <td><?php _e('Sets the closing tag for each item on the list', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                                    <td>&lt;/li&gt;</td>
                                    <td>post_end="&lt;/li&gt;"</td>
                                </tr>
                                <tr class="alternate">
                                    <td><strong>do_pattern</strong></td>
                                    <td><?php _e('If set, this option will allow you to decide the order of the contents within each item on the list.', 'wordpress-popular-posts'); ?></td>
                                    <td>1 (true), (0) false</td>
                                    <td>0</td>
                                    <td>do_pattern=1</td>
                                </tr>
                                <tr>
                                    <td><strong>pattern_form</strong></td>
                                    <td><?php _e('If set, you can decide the order of each content inside a single item on the list. For example, setting it to "{title}: {summary}" would output something like "Your Post Title: summary here". This attribute requires do_pattern to be true.', 'wordpress-popular-posts'); ?></td>
                                    <td><?php _e('Available tags', 'wordpress-popular-posts'); ?>: {thumb}, {title}, {summary}, {stats}, {rating}, {url}, {text_title}, {author}, {category}, {views}, {comments}</td>
                                    <td>{image} {thumb}: {summary} {stats}</td>
                                    <td>pattern_form="{thumb} {title}: {summary} {stats}"</td>
                                </tr>-->
							</tbody>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <!-- End faq -->

    <!-- Start tools -->
    <div id="wpp_tools" class="wpp_boxes"<?php if (isset($_POST['section']) && ($_POST['section'] == "linking" || $_POST['section'] == "logging" || $_POST['section'] == "tools" || $_POST['section'] == "ajax" || $_POST['section'] == "css") ) {?> style="display:block;"<?php } ?>>
    	<p><?php _e("Here you will find a handy group of options to tweak Wordpress Popular Posts.", "wordpress-popular-posts"); ?></p><br />
        
        <h3 class="wmpp-subtitle"><?php _e("Popular Posts links behavior", "wordpress-popular-posts"); ?></h3>

        <form action="" method="post" id="wpp_link_options" name="wpp_link_options">
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="link_target"><?php _e("Open links in", "wordpress-popular-posts"); ?>:</label></th>
                        <td>
                            <select name="link_target" id="link_target">
                                <option <?php if (!isset($ops['tools']['link']['target']) || $ops['tools']['link']['target'] == '_self') {?>selected="selected"<?php } ?> value="_self"><?php _e("Current window", "wordpress-popular-posts"); ?></option>
                                <option <?php if (isset($ops['tools']['link']['target']) && $ops['tools']['link']['target'] == '_blank') {?>selected="selected"<?php } ?> value="_blank"><?php _e("New tab/window", "wordpress-popular-posts"); ?></option>
                            </select>
                            <br />
                        </td>
                    </tr>
                    <tr valign="top">
                        <td colspan="2">
                            <input type="hidden" name="section" value="linking" />
                            <input type="submit" class="button-secondary action" id="btn_link_ops" value="<?php _e("Apply", "wordpress-popular-posts"); ?>" name="" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
        <br />
        <p style="display:block; float:none; clear:both">&nbsp;</p>

        <h3 class="wmpp-subtitle"><?php _e("Views logging behavior", "wordpress-popular-posts"); ?></h3>

        <form action="" method="post" id="wpp_log_options" name="wpp_log_options">
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="log_option"><?php _e("Log views from", "wordpress-popular-posts"); ?>:</label></th>
                        <td>
                            <select name="log_option" id="log_option">
                                <option <?php if (!isset($ops['tools']['log_loggedin']) || $ops['tools']['log_loggedin'] == 0) {?>selected="selected"<?php } ?> value="0"><?php _e("Visitors only", "wordpress-popular-posts"); ?></option>
                                <option <?php if (isset($ops['tools']['log_loggedin']) && $ops['tools']['log_loggedin'] == 1) {?>selected="selected"<?php } ?> value="1"><?php _e("Everyone", "wordpress-popular-posts"); ?></option>
                            </select>
                            <br />
                        </td>
                    </tr>
                    <tr valign="top">
                        <td colspan="2">
                            <input type="hidden" name="section" value="logging" />
                            <input type="submit" class="button-secondary action" id="btn_log_ops" value="<?php _e("Apply", "wordpress-popular-posts"); ?>" name="" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
        <br />
        <p style="display:block; float:none; clear:both">&nbsp;</p>

        <h3 class="wmpp-subtitle"><?php _e("Thumbnail source", "wordpress-popular-posts"); ?></h3>

        <form action="" method="post" id="wpp_thumbnail_options" name="wpp_thumbnail_options">
            <table class="form-table">
                <tbody>
                	<tr valign="top">
                        <th scope="row"><label for="thumb_default"><?php _e("Default thumbnail", "wordpress-popular-posts"); ?>:</label></th>
                        <td>
                            <input id="upload_thumb_button" type="button" class="button" value="<?php _e( "Upload thumbnail", "wordpress-popular-posts" ); ?>" />
                            <input type="hidden" id="upload_thumb_src" name="upload_thumb_src" value="" />
                            <br />
                            <p class="description"><?php _e("How-to: upload (or select) an image, set Size to Full and click on Upload. After it's done, hit on Apply to save changes", "wordpress-popular-posts"); ?></p>
                            <div style="display:<?php if ( !empty($ops['tools']['thumbnail']['default']) ) : ?>block<?php else: ?>none<?php endif; ?>;">
                            	<label><?php _e("Preview", "wordpress-popular-posts"); ?>:</label>
                                <div id="thumb-review">
                                    <img src="<?php echo $ops['tools']['thumbnail']['default']; ?>" alt="" border="0" />
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="thumb_source"><?php _e("Pick image from", "wordpress-popular-posts"); ?>:</label></th>
                        <td>
                            <select name="thumb_source" id="thumb_source">
                                <option <?php if ($ops['tools']['thumbnail']['source'] == "featured") {?>selected="selected"<?php } ?> value="featured"><?php _e("Featured image", "wordpress-popular-posts"); ?></option>
                                <option <?php if ($ops['tools']['thumbnail']['source'] == "first_image") {?>selected="selected"<?php } ?> value="first_image"><?php _e("First image on post", "wordpress-popular-posts"); ?></option>
                                <option <?php if ($ops['tools']['thumbnail']['source'] == "custom_field") {?>selected="selected"<?php } ?> value="custom_field"><?php _e("Custom field", "wordpress-popular-posts"); ?></option>
                            </select>
                            <br />
                            <p class="description"><?php _e("Tell Wordpress Popular Posts where it should get thumbnails from", "wordpress-popular-posts"); ?></p>
                        </td>
                    </tr>
                    <tr valign="top" <?php if ($ops['tools']['thumbnail']['source'] != "custom_field") {?>style="display:none;"<?php } ?> id="row_custom_field">
                        <th scope="row"><label for="thumb_field"><?php _e("Custom field name", "wordpress-popular-posts"); ?>:</label></th>
                        <td>
                            <input type="text" id="thumb_field" name="thumb_field" value="<?php echo $ops['tools']['thumbnail']['field']; ?>" size="10" <?php if ($ops['tools']['thumbnail']['source'] != "custom_field") {?>style="display:none;"<?php } ?> />
                        </td>
                    </tr>
                    <tr valign="top" <?php if ($ops['tools']['thumbnail']['source'] != "custom_field") {?>style="display:none;"<?php } ?> id="row_custom_field_resize">
                        <th scope="row"><label for="thumb_field_resize"><?php _e("Resize image from Custom field?", "wordpress-popular-posts"); ?>:</label></th>
                        <td>
                            <select name="thumb_field_resize" id="thumb_field_resize">
                                <option <?php if ( !isset($ops['tools']['thumbnail']['resize']) || !$ops['tools']['thumbnail']['resize'] ) {?>selected="selected"<?php } ?> value="0"><?php _e("No, I will upload my own thumbnail", "wordpress-popular-posts"); ?></option>
                                <option <?php if ( isset($ops['tools']['thumbnail']['resize'] ) && $ops['tools']['thumbnail']['resize'] == 1 ) {?>selected="selected"<?php } ?> value="1"><?php _e("Yes", "wordpress-popular-posts"); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <td colspan="2">
                            <input type="hidden" name="section" value="tools" />
                            <input type="submit" class="button-secondary action" id="btn_th_ops" value="<?php _e("Apply", "wordpress-popular-posts"); ?>" name="" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>

        <br />
        <p style="display:block; float:none; clear:both">&nbsp;</p>

        <h3 class="wmpp-subtitle"><?php _e("Wordpress Popular Posts Stylesheet", "wordpress-popular-posts"); ?></h3>
        <p><?php _e("By default, the plugin includes a stylesheet called wpp.css which you can use to style your popular posts listing. If you wish to use your own stylesheet or do not want it to have it included in the header section of your site, use this.", "wordpress-popular-posts"); ?></p>
        <div class="tablenav top">
        	<div class="alignleft actions">
                <form action="" method="post" id="wpp_css_options" name="wpp_css_options">
                    <select name="css" id="css">
                        <option <?php if ($ops['tools']['css']) {?>selected="selected"<?php } ?> value="1"><?php _e("Enabled", "wordpress-popular-posts"); ?></option>
                        <option <?php if (!$ops['tools']['css']) {?>selected="selected"<?php } ?> value="0"><?php _e("Disabled", "wordpress-popular-posts"); ?></option>
                    </select>
                    <input type="hidden" name="section" value="css" />
                    <input type="submit" class="button-secondary action" id="btn_css_ops" value="<?php _e("Apply", "wordpress-popular-posts"); ?>" name="" />
                </form>
            </div>
        </div>
        <br /><br />

        <h3 class="wmpp-subtitle"><?php _e("Data tools", "wordpress-popular-posts"); ?></h3>
        <form action="" method="post" id="wpp_ajax_options" name="wpp_ajax_options">
        	<table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="thumb_source"><?php _e("Ajaxify widget", "wordpress-popular-posts"); ?>:</label></th>
                        <td>
                            <select name="ajax" id="ajax">
                                <option <?php if (!$ops['tools']['ajax']) {?>selected="selected"<?php } ?> value="0"><?php _e("Disabled", "wordpress-popular-posts"); ?></option>
                                <option <?php if ($ops['tools']['ajax']) {?>selected="selected"<?php } ?> value="1"><?php _e("Enabled", "wordpress-popular-posts"); ?></option>
                            </select>

                            <br />
                            <p class="description"><?php _e("If you are using a caching plugin such as WP Super Cache, enabling this feature will keep the popular list from being cached by it", "wordpress-popular-posts"); ?></p>
                        </td>
                    </tr>
                    <tr valign="top" style="display:none;">
                        <th scope="row"><label for="thumb_source"><?php _e("Popular posts listing refresh interval", "wordpress-popular-posts"); ?>:</label></th>
                        <td>
                            <select name="cache" id="cache">
                                <option <?php if ( !isset($ops['tools']['cache']['active']) || !$ops['tools']['cache']['active'] ) { ?>selected="selected"<?php } ?> value="0"><?php _e("Live", "wordpress-popular-posts"); ?></option>
                                <option <?php if ( isset($ops['tools']['cache']['active']) && $ops['tools']['cache']['active'] ) { ?>selected="selected"<?php } ?> value="1"><?php _e("Custom interval", "wordpress-popular-posts"); ?></option>
                            </select>

                            <br />
                            <p class="description"><?php _e("Sets how often the listing should be updated. For most sites the Live option should be fine, however if you are experiencing slowdowns or your blog gets a lot of visitors then you might want to change the refresh rate", "wordpress-popular-posts"); ?></p>
                        </td>
                    </tr>
                    <tr valign="top" <?php if ( !isset($ops['tools']['cache']['active']) || !$ops['tools']['cache']['active'] ) { ?>style="display:none;"<?php } ?> id="cache_refresh_interval">
                        <th scope="row"><label for="thumb_field_resize"><?php _e("Refresh interval", "wordpress-popular-posts"); ?>:</label></th>
                        <td>
                        	<input name="cache_interval_value" type="text" id="cache_interval_value" value="<?php echo ( isset($ops['tools']['cache']['interval']['value']) ) ? (int) $ops['tools']['cache']['interval']['value'] : 1; ?>" class="small-text">
                            <select name="cache_interval_time" id="cache_interval_time">
                                <option <?php if ($ops['tools']['cache']['interval']['time'] == "hour") {?>selected="selected"<?php } ?> value="hour"><?php _e("Hour(s)", "wordpress-popular-posts"); ?></option>
                                <option <?php if ($ops['tools']['cache']['interval']['time'] == "day") {?>selected="selected"<?php } ?> value="day"><?php _e("Day(s)", "wordpress-popular-posts"); ?></option>
                                <option <?php if ($ops['tools']['cache']['interval']['time'] == "week") {?>selected="selected"<?php } ?> value="week"><?php _e("Week(s)", "wordpress-popular-posts"); ?></option>
                                <option <?php if ($ops['tools']['cache']['interval']['time'] == "month") {?>selected="selected"<?php } ?> value="month"><?php _e("Month(s)", "wordpress-popular-posts"); ?></option>
                                <option <?php if ($ops['tools']['cache']['interval']['time'] == "year") {?>selected="selected"<?php } ?> value="month"><?php _e("Year(s)", "wordpress-popular-posts"); ?></option>
                            </select>
                            <br />
                            <p class="description" style="display:none;" id="cache_too_long"><?php _e("Really? That long?", "wordpress-popular-posts"); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <td colspan="2">
                            <input type="hidden" name="section" value="ajax" />
                    		<input type="submit" class="button-secondary action" id="btn_ajax_ops" value="<?php _e("Apply", "wordpress-popular-posts"); ?>" name="" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>

        <br /><br />

        <p><?php _e('Wordpress Popular Posts maintains data in two separate tables: one for storing the most popular entries in the past 30 days (from now on, "cache"), and another one to keep the All-time data (from now on, "historical data" or just "data"). If for some reason you need to clear the cache table, or even both historical and cache tables, please use the buttons below to do so.', 'wordpress-popular-posts') ?></p>
        <p><input type="button" name="wpp-reset-cache" id="wpp-reset-cache" class="button-secondary" value="<?php _e("Empty cache", "wordpress-popular-posts"); ?>" onclick="confirm_reset_cache()" /> <label for="wpp-reset-cache"><small><?php _e('Use this button to manually clear entries from WPP cache only', 'wordpress-popular-posts'); ?></small></label></p>
        <p><input type="button" name="wpp-reset-all" id="wpp-reset-all" class="button-secondary" value="<?php _e("Clear all data", "wordpress-popular-posts"); ?>" onclick="confirm_reset_all()" /> <label for="wpp-reset-all"><small><?php _e('Use this button to manually clear entries from all WPP data tables', 'wordpress-popular-posts'); ?></small></label></p>
    </div>
    <!-- End tools -->

    <br />
    <hr />
    <p><?php _e('Do you like this plugin?', 'wordpress-popular-posts'); ?> <a title="<?php _e('Rate Wordpress Popular Posts!', 'wordpress-popular-posts'); ?>" href="http://wordpress.org/extend/plugins/wordpress-popular-posts/#rate-response" target="_blank"><strong><?php _e('Rate it', 'wordpress-popular-posts'); ?></strong></a> <?php _e('on the official Plugin Directory!', 'wordpress-popular-posts'); ?></p>
    <p><?php _e('Do you love this plugin?', 'wordpress-popular-posts'); ?> <a title="<?php _e('Buy me a beer!', 'wordpress-popular-posts'); ?>" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=dadslayer%40gmail%2ecom&lc=GB&item_name=Wordpress%20Popular%20Posts%20Plugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG_global%2egif%3aNonHosted" target="_blank"><strong><?php _e('Buy me a beer!', 'wordpress-popular-posts'); ?></strong></a>. <?php _e('Each donation motivates me to keep releasing free stuff for the Wordpress community!', 'wordpress-popular-posts'); ?></p>
    <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=dadslayer%40gmail%2ecom&lc=GB&item_name=Wordpress%20Popular%20Posts%20Plugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG_global%2egif%3aNonHosted" target="_blank" rel="external nofollow"><img src="<?php echo get_bloginfo('url') . "/" . PLUGINDIR; ?>/wordpress-popular-posts/btn_donateCC_LG_global.gif" width="122" height="47" alt="<?php _e('Buy me a beer!', 'wordpress-popular-posts'); ?>" border="0" /></a>
</div>