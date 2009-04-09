<?php
	global $wpp;
	
	$wpp->options = get_option("wpp_options");
	
	//$wpp->options = array();
			
	if (!is_array( $wpp->options ) || empty( $wpp->options )) {
		$wpp->options = array( 'title' => 'Popular Posts', 'limit' => 10, 'pages' => true, 'comments' => true, 'views' => true, 'excerpt' => true, 'characters' => 25, 'sortby' => 1, 'range' => 'all-time', 'author' => false, 'date' => false, 'markup' => array('wpp-start'=>'&lt;ul&gt;', 'wpp-end'=>'&lt;/ul&gt;', 'post-start'=>'&lt;li&gt;', 'post-end'=>'&lt;/li&gt;', 'display'=>'block', 'delimiter' => ' [...]', 'title-start' => '&lt;h2&gt;', 'title-end' => '&lt;/h2&gt;')	);			
	}
	
	$wpp->options_snippet = get_option("wpp_options_snippet");
	if (!is_array( $wpp->options_snippet ) || empty($wpp->options_snippet)) $wpp->options_snippet = $wpp->options;	
	
	if ($_POST['plugin_mostpopular-Submit']) {	
		$wpp->options['title'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-WidgetTitle']));
		$wpp->options['limit'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-Limit']));
		$wpp->options['characters'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-ExcerptLimit']));	
		$wpp->options['sortby'] = $_POST['plugin_mostpopular-Sort'];
		$wpp->options['range'] = $_POST['plugin_mostpopular-Range'];
		// Experimental		
		$wpp->options['markup']['wpp-start'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-BeforeWPP']));
		$wpp->options['markup']['wpp-end'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-AfterWPP']));
		$wpp->options['markup']['title-start'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-BeforeTitle']));
		$wpp->options['markup']['title-end'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-AfterTitle']));
		$wpp->options['markup']['post-start'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-BeforePost']));
		$wpp->options['markup']['post-end'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-AfterPost']));
		$wpp->options['markup']['display'] = $_POST['plugin_mostpopular-StatsTagDisplay'];
		$wpp->options['markup']['delimiter'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-Delimiter']));
		// Experimental
		
		if (isset($_POST['plugin_mostpopular-IncludePages'])) { $wpp->options['pages'] = true; } else { $wpp->options['pages'] = false; }		
		if (isset($_POST['plugin_mostpopular-ShowCount'])) { $wpp->options['comments'] = true; } else { $wpp->options['comments'] = false; }		
		if (isset($_POST['plugin_mostpopular-ShowViews'])) { $wpp->options['views'] = true; } else { $wpp->options['views'] = false; }		
		if (isset($_POST['plugin_mostpopular-ShowAuthor'])) { $wpp->options['author'] = true; } else { $wpp->options['author'] = false; }
		if (isset($_POST['plugin_mostpopular-ShowDate'])) { $wpp->options['date'] = true; } else { $wpp->options['date'] = false; }		
		if (isset($_POST['plugin_mostpopular-ShowExcerpt'])) { $wpp->options['excerpt'] = true; } else { $wpp->options['excerpt'] = false; }		
		if ( (!is_numeric($wpp->options['limit'])) || ($wpp->options['limit'] <= 0) ) $wpp->options['limit'] = 10;		
		if ( (!is_numeric($wpp->options['characters'])) || ($wpp->options['characters'] <= 0) ) $wpp->options['characters'] = 25;		
		
		if (isset($_POST['plugin_mostpopular-SeparateSettings'])) {			
			if (get_option("wpp_widget_on") == "on") {				
				$wpp->options_snippet['title'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-SnippetTitle']));
				$wpp->options_snippet['limit'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-Limit_Snippet']));
				$wpp->options_snippet['characters'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-ExcerptLimit_Snippet']));	
				$wpp->options_snippet['sortby'] = $_POST['plugin_mostpopular-Sort_Snippet'];
				$wpp->options_snippet['range'] = $_POST['plugin_mostpopular-Range_Snippet'];
				// Experimental
				$wpp->options_snippet['markup']['wpp-start'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-BeforeWPP_Snippet']));
				$wpp->options_snippet['markup']['wpp-end'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-AfterWPP_Snippet']));
				$wpp->options_snippet['markup']['title-start'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-BeforeTitle_Snippet']));
				$wpp->options_snippet['markup']['title-end'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-AfterTitle_Snippet']));
				$wpp->options_snippet['markup']['post-start'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-BeforePost_Snippet']));
				$wpp->options_snippet['markup']['post-end'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-AfterPost_Snippet']));
				$wpp->options_snippet['markup']['display'] = $_POST['plugin_mostpopular-StatsTagDisplay_Snippet'];
				$wpp->options_snippet['markup']['delimiter'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-Delimiter_Snippet']));
				// Experimental
				
				if (isset($_POST['plugin_mostpopular-IncludePages_Snippet'])) { $wpp->options_snippet['pages'] = true; } else { $wpp->options_snippet['pages'] = false; }		
				if (isset($_POST['plugin_mostpopular-ShowCount_Snippet'])) { $wpp->options_snippet['comments'] = true; } else { $wpp->options_snippet['comments'] = false; }		
				if (isset($_POST['plugin_mostpopular-ShowViews_Snippet'])) { $wpp->options_snippet['views'] = true; } else { $wpp->options_snippet['views'] = false; }	
				if (isset($_POST['plugin_mostpopular-ShowAuthor_Snippet'])) { $wpp->options_snippet['author'] = true; } else { $wpp->options_snippet['author'] = false; }
				if (isset($_POST['plugin_mostpopular-ShowDate_Snippet'])) { $wpp->options_snippet['date'] = true; } else { $wpp->options_snippet['date'] = false; }	
				if (isset($_POST['plugin_mostpopular-ShowExcerpt_Snippet'])) { $wpp->options_snippet['excerpt'] = true; } else { $wpp->options_snippet['excerpt'] = false; }		
				if ( (!is_numeric($wpp->options_snippet['limit'])) || ($wpp->options_snippet['limit'] <= 0) ) $wpp->options_snippet['limit'] = 10;		
				if ( (!is_numeric($wpp->options_snippet['characters'])) || ($wpp->options_snippet['characters'] <= 0) ) $wpp->options_snippet['characters'] = 25;
			} else {
				update_option("wpp_widget_on", "on");
			}
		} else {
			update_option("wpp_widget_on", "off");
			$wpp->options_snippet = $wpp->options; // reset options		
		}
		
		update_option("wpp_options", $wpp->options);
		update_option("wpp_options_snippet", $wpp->options_snippet);
		$wpp->options_holder = array($wpp->options, $wpp->options_snippet);
	}
	
	?>
	<style>
		h2#wmpp-title {color:#666; font-weight:100; font-family:Georgia, "Times New Roman", Times, serif; font-size:24px; font-style:italic}
		h3 {color:#666; font-weight:100; font-family:Georgia, "Times New Roman", Times, serif; font-size:18px}
		
		table#config_panel {}
		td.odd_row, td.even_row {padding:5px!important;}
		td.odd_row {background:#ccc}
		td.even_row {background:#ddd}
		td.odd_row label, td.even_row label {font-weight:bold; font-size:11px}
		
		td.separate_title, td.separate_titles {padding:5px!important; font-weight:bold; color:#fff; background:#333;}
		td.separate_titles {text-align:center;}
		
		input.txt, input.nro {padding:3px 5px!important; border:#999 1px solid;}
		input.nro {width:15%; text-align:center}
		input.txt2 {width:25%;}
		
		input.checkbox {border:#999 1px solid}
		
		#btn_submit {border:#333 1px solid; background:#006699; color:#fff; cursor:pointer}
		
		.preview_box {overflow:auto; width:350px; height:200px;}		
	</style>
	<h2 id="wmpp-title">Wordpress Popular Posts</h2>
	<p><?php echo __('With Wordpress Popular Posts, you can show your visitors what are the most popular entries on your blog. You can either use it as a <a href="widgets.php"><strong>Sidebar Widget</strong></a>  (<a href="http://codex.wordpress.org/Plugins/WordPress_Widgets" rel="external nofollow"><small>what\'s a widget?</small></a>), or place it in your templates using this handy <strong>code snippet</strong>: <em>&lt;?php if (function_exists("get_mostpopular")) get_mostpopular(); ?&gt</em>.', 'wordpress-popular-posts'); ?></p>
	<p><?php echo __('Use the Settings Manager below to tweak Wordpress Popular Posts to your liking.', 'wordpress-popular-posts')?></p>
	<h3><?php echo __('Settings', 'wordpress-popular-posts')?></h3>
	<form action="<?php $_SERVER['REQUEST_URI']; ?>" method="post" name="mppform">
	<table cellpadding="0" cellspacing="1" id="config_panel">
    	<?php if ( $wpp->widgetized() ) : ?>
    	<tr>
        	<td class="even_row" width="250"><label><?php echo __('Separate settings for the widget and the code snippet:','wordpress-popular-posts'); ?> <span style="color:#ff0000; font-size:9px">NEW!</span></label></td>
            <td class="even_row" align="center"<?php if (get_option("wpp_widget_on") == "on") echo " colspan=\"2\""; ?>><input type="checkbox" id="plugin_mostpopular-SeparateSettings" name="plugin_mostpopular-SeparateSettings" <?php if (get_option("wpp_widget_on") == "on") echo "checked=\"checked\""; ?> class="checkbox" /></td>
        </tr>
		<?php endif; ?>
        <?php if (get_option("wpp_widget_on") == "on") : ?>
        <tr>
        	<td class="separate_titles"></td>
            <td class="separate_titles"><small><?php echo __('WIDGET SETTINGS', 'wordpress-popular-posts'); ?></small></td>
            <td class="separate_titles"><small><?php echo __('CODE SNIPPET SETTINGS', 'wordpress-popular-posts'); ?></small></td>
        </tr>
        <?php endif; ?>
		<tr>
			<td class="odd_row"><label for="plugin_mostpopular-WidgetTitle"><?php echo __('Title:', 'wordpress-popular-posts'); ?> </label></td>
			<td class="odd_row"><input type="text" id="plugin_mostpopular-WidgetTitle" name="plugin_mostpopular-WidgetTitle" value="<?php echo $wpp->options['title'];?>" class="txt" /></td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="odd_row"><input type="text" id="plugin_mostpopular-SnippetTitle" name="plugin_mostpopular-SnippetTitle" value="<?php echo $wpp->options_snippet['title'];?>" class="txt" /></td>
            <?php endif; ?>
		</tr>
		<tr>
			<td class="even_row"><label for="plugin_mostpopular-Limit"><?php echo __('Show up to:', 'wordpress-popular-posts'); ?> </label></td>
			<td class="even_row"><input type="text" id="plugin_mostpopular-Limit" name="plugin_mostpopular-Limit" value="<?php echo $wpp->options['limit'];?>" class="nro" /> posts</td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="even_row"><input type="text" id="plugin_mostpopular-Limit_Snippet" name="plugin_mostpopular-Limit_Snippet" value="<?php echo $wpp->options_snippet['limit'];?>" class="nro" /> <?php echo __('posts', 'wordpress-popular-posts'); ?></td>
            <?php endif; ?>
		</tr>
        <tr>
			<td class="odd_row"><label for="plugin_mostpopular-Range"><?php echo __('Time Range:', 'wordpress-popular-posts'); ?> <span style="color:#ff0000; font-size:9px">NEW!</span></label></td>
			<td class="odd_row">
				<select name="plugin_mostpopular-Range">
                     <option value="all-time" <?php if ($wpp->options['range'] == 'all-time') { ?> selected="selected"<?php } ?>><?php echo __('All-Time', 'wordpress-popular-posts'); ?></option>
                     <option value="today" <?php if ($wpp->options['range'] == 'today') { ?> selected="selected"<?php } ?>><?php echo __('Today', 'wordpress-popular-posts'); ?></option>
                     <option value="weekly" <?php if ($wpp->options['range'] == 'weekly') { ?> selected="selected"<?php } ?>><?php echo __('Last 7 days', 'wordpress-popular-posts'); ?></option>
                     <option value="monthly" <?php if ($wpp->options['range'] == 'monthly') { ?> selected="selected"<?php } ?>><?php echo __('Last 30 days', 'wordpress-popular-posts'); ?></option>
                     <option value="yearly" <?php if ($wpp->options['range'] == 'yearly') { ?> selected="selected"<?php } ?>><?php echo __('Last Year', 'wordpress-popular-posts'); ?></option>
				</select>
			</td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="odd_row">
				<select name="plugin_mostpopular-Range_Snippet">
                     <option value="all-time" <?php if ($wpp->options_snippet['range'] == 'all-time') { ?> selected="selected"<?php } ?>><?php echo __('All-Time', 'wordpress-popular-posts'); ?></option>
                     <option value="today" <?php if ($wpp->options_snippet['range'] == 'today') { ?> selected="selected"<?php } ?>><?php echo __('Today', 'wordpress-popular-posts'); ?></option>
                     <option value="weekly" <?php if ($wpp->options_snippet['range'] == 'weekly') { ?> selected="selected"<?php } ?>><?php echo __('Last 7 days', 'wordpress-popular-posts'); ?></option>
                     <option value="monthly" <?php if ($wpp->options_snippet['range'] == 'monthly') { ?> selected="selected"<?php } ?>><?php echo __('Last 30 days', 'wordpress-popular-posts'); ?></option>
                     <option value="yearly" <?php if ($wpp->options_snippet['range'] == 'yearly') { ?> selected="selected"<?php } ?>><?php echo __('Last Year', 'wordpress-popular-posts'); ?></option>
				</select>
			</td>
            <?php endif; ?>
		</tr>
		<tr>
			<td class="even_row"><label for="plugin_mostpopular-Sort"><?php echo __('Sort posts by:', 'wordpress-popular-posts'); ?> </label></td>
			<td class="even_row">
            	<select name="plugin_mostpopular-Sort">				
					<option value="1" <?php if ($wpp->options['sortby'] == 1) {?> selected="selected"<?php } ?>><?php echo __('Comments', 'wordpress-popular-posts'); ?></option>
					<option value="2" <?php if ($wpp->options['sortby'] == 2) {?> selected="selected"<?php } ?>><?php echo __('Total Views', 'wordpress-popular-posts'); ?></option>
					<option value="3" <?php if ($wpp->options['sortby'] == 3) {?> selected="selected"<?php } ?>><?php echo __('Avg. Daily Views', 'wordpress-popular-posts'); ?></option>
				</select>
			</td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="even_row">
            	<select name="plugin_mostpopular-Sort_Snippet">				
					<option value="1" <?php if ($wpp->options_snippet['sortby'] == 1) {?> selected="selected"<?php } ?>><?php echo __('Comments', 'wordpress-popular-posts'); ?></option>
					<option value="2" <?php if ($wpp->options_snippet['sortby'] == 2) {?> selected="selected"<?php } ?>><?php echo __('Total Views', 'wordpress-popular-posts'); ?></option>
					<option value="3" <?php if ($wpp->options_snippet['sortby'] == 3) {?> selected="selected"<?php } ?>><?php echo __('Avg. Daily Views', 'wordpress-popular-posts'); ?></option>
				</select>
			</td>
            <?php endif; ?>
		</tr>
		<tr>
			<td class="odd_row"><label for="plugin_mostpopular-IncludePages"><?php echo __('Include pages:', 'wordpress-popular-posts'); ?></label></td>
			<td class="odd_row" align="center"><input type="checkbox" id="plugin_mostpopular-IncludePages" name="plugin_mostpopular-IncludePages" <?php if ($wpp->options['pages']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="odd_row" align="center"><input type="checkbox" id="plugin_mostpopular-IncludePages_Snippet" name="plugin_mostpopular-IncludePages_Snippet" <?php if ($wpp->options_snippet['pages']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php endif; ?>
		</tr>
        <tr>
        	<td class="separate_title" colspan="<?php if (get_option("wpp_widget_on") == "on") { echo "3"; } else { echo "2"; } ?>"><small><?php echo __('STATS TAG SETTINGS', 'wordpress-popular-posts'); ?></small></td>
        </tr>
		<tr>
			<td class="even_row"><label for="plugin_mostpopular-ShowCount"><?php echo __('Show comment count:', 'wordpress-popular-posts'); ?></label></td>
			<td class="even_row" align="center"><input type="checkbox" id="plugin_mostpopular-ShowCount" name="plugin_mostpopular-ShowCount" <?php if ($wpp->options['comments']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="even_row" align="center"><input type="checkbox" id="plugin_mostpopular-ShowCount_Snippet" name="plugin_mostpopular-ShowCount_Snippet" <?php if ($wpp->options_snippet['comments']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php endif; ?>
		</tr>
		<tr>
			<td class="odd_row"><label for="plugin_mostpopular-ShowViews"><?php echo __('Show pageviews:', 'wordpress-popular-posts'); ?></label></td>
			<td class="odd_row" align="center"><input type="checkbox" id="plugin_mostpopular-ShowViews" name="plugin_mostpopular-ShowViews" <?php if ($wpp->options['views']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="odd_row" align="center"><input type="checkbox" id="plugin_mostpopular-ShowViews_Snippet" name="plugin_mostpopular-ShowViews_Snippet" <?php if ($wpp->options_snippet['views']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php endif; ?>
		</tr>
        <tr>
			<td class="even_row"><label for="plugin_mostpopular-ShowAuthor"><?php echo __('Show author:', 'wordpress-popular-posts'); ?> <span style="color:#ff0000; font-size:9px">NEW!</span></label></td>
			<td class="even_row" align="center"><input type="checkbox" id="plugin_mostpopular-ShowAuthor" name="plugin_mostpopular-ShowAuthor" <?php if ($wpp->options['author']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="even_row" align="center"><input type="checkbox" id="plugin_mostpopular-ShowAuthor_Snippet" name="plugin_mostpopular-ShowAuthor_Snippet" <?php if ($wpp->options_snippet['author']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php endif; ?>
		</tr>
        <tr>
			<td class="odd_row"><label for="plugin_mostpopular-ShowDate"><?php echo __('Show date:', 'wordpress-popular-posts'); ?> <span style="color:#ff0000; font-size:9px">NEW!</span></label></td>
			<td class="odd_row" align="center"><input type="checkbox" id="plugin_mostpopular-ShowDate" name="plugin_mostpopular-ShowDate" <?php if ($wpp->options['date']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="odd_row" align="center"><input type="checkbox" id="plugin_mostpopular-ShowDate_Snippet" name="plugin_mostpopular-ShowDate_Snippet" <?php if ($wpp->options_snippet['date']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php endif; ?>
		</tr>
		<tr>
			<td class="even_row"><label for="plugin_mostpopular-ShowExcerpt"><?php echo __('Shorten title output:', 'wordpress-popular-posts'); ?></label></td>
			<td class="even_row" align="center"><input type="checkbox" id="plugin_mostpopular-ShowExcerpt" name="plugin_mostpopular-ShowExcerpt" <?php if ($wpp->options['excerpt']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="even_row" align="center"><input type="checkbox" id="plugin_mostpopular-ShowExcerpt_Snippet" name="plugin_mostpopular-ShowExcerpt_Snippet" <?php if ($wpp->options_snippet['excerpt']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php endif; ?>            
		</tr>
		<?php if ($wpp->options['excerpt'] || ($wpp->options_snippet['excerpt'] && (get_option("wpp_widget_on") == "on"))) { ?>
		<tr>
			<td class="odd_row"><label for="plugin_mostpopular-ExcerptLimit"><?php echo __('Shorten title to:', 'wordpress-popular-posts'); ?></label></td>
			<td class="odd_row" align="center"><input type="text" id="plugin_mostpopular-ExcerptLimit" name="plugin_mostpopular-ExcerptLimit" value="<?php echo $wpp->options['characters'];?>" class="nro" /> <?php echo __('characters', 'wordpress-popular-posts'); ?></td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="odd_row" align="center"><input type="text" id="plugin_mostpopular-ExcerptLimit_Snippet" name="plugin_mostpopular-ExcerptLimit_Snippet" value="<?php echo $wpp->options_snippet['characters'];?>" class="nro" /> <?php echo __('characters', 'wordpress-popular-posts'); ?></td>
            <?php endif; ?>
		</tr>        
		<?php } ?>
        <tr>
        	<td class="separate_title" colspan="<?php if (get_option("wpp_widget_on") == "on") { echo "3"; } else { echo "2"; } ?>"><small><?php echo __('HTML MARKUP SETTINGS', 'wordpress-popular-posts'); ?></small></td>
        </tr>
        <tr>
        	<td class="even_row"><label for="plugin_mostpopular-BeforeTitle"><?php echo __('Before / after title:', 'wordpress-popular-posts'); ?>  <span style="color:#ff0000; font-size:9px">NEW!</span></label><br /><small><?php echo __('(for example: &lt;h2&gt; | &lt;/h2&gt;)', 'wordpress-popular-posts'); ?></small></td>
            <td class="even_row" align="center"><input type="text" id="plugin_mostpopular-BeforeTitle" name="plugin_mostpopular-BeforeTitle" value="<?php echo $wpp->options['markup']['title-start'];?>" class="txt2" /> <input type="text" id="plugin_mostpopular-AfterTitle" name="plugin_mostpopular-AfterTitle" value="<?php echo $wpp->options['markup']['title-end'];?>" class="txt2" /> </td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="even_row" align="center"><input type="text" id="plugin_mostpopular-BeforeTitle_Snippet" name="plugin_mostpopular-BeforeTitle_Snippet" value="<?php echo $wpp->options_snippet['markup']['title-start'];?>" class="txt2" /> <input type="text" id="plugin_mostpopular-AfterTitle_Snippet" name="plugin_mostpopular-AfterTitle_Snippet" value="<?php echo $wpp->options_snippet['markup']['title-end'];?>" class="txt2" /> </td>
            <?php endif; ?>
        </tr>
        <tr>
        	<td class="odd_row"><label for="plugin_mostpopular-BeforeWPP"><?php echo __('Before / after Popular Posts:', 'wordpress-popular-posts'); ?>  <span style="color:#ff0000; font-size:9px">NEW!</span></label><br /><small><?php echo __('(for example: &lt;ul&gt; | &lt;/ul&gt;)', 'wordpress-popular-posts'); ?></small></td>
            <td class="odd_row" align="center"><input type="text" id="plugin_mostpopular-BeforeWPP" name="plugin_mostpopular-BeforeWPP" value="<?php echo $wpp->options['markup']['wpp-start'];?>" class="txt2" /> <input type="text" id="plugin_mostpopular-AfterWPP" name="plugin_mostpopular-AfterWPP" value="<?php echo $wpp->options['markup']['wpp-end'];?>" class="txt2" /> </td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="odd_row" align="center"><input type="text" id="plugin_mostpopular-BeforeWPP_Snippet" name="plugin_mostpopular-BeforeWPP_Snippet" value="<?php echo $wpp->options_snippet['markup']['wpp-start'];?>" class="txt2" /> <input type="text" id="plugin_mostpopular-AfterWPP_Snippet" name="plugin_mostpopular-AfterWPP_Snippet" value="<?php echo $wpp->options_snippet['markup']['wpp-end'];?>" class="txt2" /> </td>
            <?php endif; ?>
        </tr>        
        <tr>
        	<td class="even_row"><label for="plugin_mostpopular-BeforePost"><?php echo __('Before / after each post:', 'wordpress-popular-posts'); ?>  <span style="color:#ff0000; font-size:9px">NEW!</span></label><br /><small><?php echo __('(for example: &lt;li&gt; | &lt;/li&gt;)', 'wordpress-popular-posts'); ?></small></td>
            <td class="even_row" align="center"><input type="text" id="plugin_mostpopular-BeforePost" name="plugin_mostpopular-BeforePost" value="<?php echo $wpp->options['markup']['post-start'];?>" class="txt2" /> <input type="text" id="plugin_mostpopular-AfterPost" name="plugin_mostpopular-AfterPost" value="<?php echo $wpp->options['markup']['post-end'];?>" class="txt2" /> </td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="even_row" align="center"><input type="text" id="plugin_mostpopular-BeforePost_Snippet" name="plugin_mostpopular-BeforePost_Snippet" value="<?php echo $wpp->options_snippet['markup']['post-start'];?>" class="txt2" /> <input type="text" id="plugin_mostpopular-AfterPost_Snippet" name="plugin_mostpopular-AfterPost_Snippet" value="<?php echo $wpp->options_snippet['markup']['post-end'];?>" class="txt2" /> </td>
            <?php endif; ?>
        </tr>
        <tr>
        	<td class="odd_row"><label for=""><?php echo __('Stats Tag display style:', 'wordpress-popular-posts'); ?>  <span style="color:#ff0000; font-size:9px">NEW!</span></label></td>
            <td class="odd_row" align="center">
            	<select name="plugin_mostpopular-StatsTagDisplay">				
					<option value="inline" <?php if ($wpp->options['markup']['display'] == 'inline') {?> selected="selected"<?php } ?>><?php echo __('Inline', 'wordpress-popular-posts'); ?></option>
                    <option value="block" <?php if ($wpp->options['markup']['display'] == 'block') {?> selected="selected"<?php } ?>><?php echo __('Block', 'wordpress-popular-posts'); ?></option>
				</select>
			</td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="odd_row" align="center">
            	<select name="plugin_mostpopular-StatsTagDisplay_Snippet">				
					<option value="inline" <?php if ($wpp->options_snippet['markup']['display'] == 'inline') {?> selected="selected"<?php } ?>><?php echo __('Inline', 'wordpress-popular-posts'); ?></option>
                    <option value="block" <?php if ($wpp->options_snippet['markup']['display'] == 'block') {?> selected="selected"<?php } ?>><?php echo __('Block', 'wordpress-popular-posts'); ?></option>
				</select>
			</td>
            <?php endif; ?>
        </tr>
        <?php if ($wpp->options['excerpt'] || ($wpp->options_snippet['excerpt'] && (get_option("wpp_widget_on") == "on"))) { ?>
        <tr>
        	<td class="even_row"><label for=""><?php echo __('Delimiter:', 'wordpress-popular-posts'); ?>  <span style="color:#ff0000; font-size:9px">NEW!</span></label></td>
            <td class="even_row" align="center">
            	<input type="text" id="plugin_mostpopular-Delimiter" name="plugin_mostpopular-Delimiter" value="<?php echo $wpp->options['markup']['delimiter'];?>" class="txt2" <?php if (!$wpp->options['excerpt']) echo "disabled=\"disabled\""; ?> />
			</td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="even_row" align="center">
            	<input type="text" id="plugin_mostpopular-Delimiter_Snippet" name="plugin_mostpopular-Delimiter_Snippet" value="<?php echo $wpp->options_snippet['markup']['delimiter'];?>" class="txt2" <?php if (!$wpp->options_snippet['excerpt']) echo "disabled=\"disabled\""; ?> />
			</td>
            <?php endif; ?>
        </tr>
        <?php } ?>
        <tr>
        	<td class="separate_title" colspan="<?php if (get_option("wpp_widget_on") == "on") { echo "3"; } else { echo "2"; } ?>"><small><?php echo __('HTML OUTPUT PREVIEW', 'wordpress-popular-posts'); ?></small></td>
        </tr>
        <tr>
        	<td class="even_row"><label><?php echo __('Preview (Widget):', 'wordpress-popular-posts'); ?>  <span style="color:#ff0000; font-size:9px">NEW!</span></label><br /><small>(<?php echo __('click on Update Settings to see changes', 'wordpress-popular-posts'); ?>)</small></td>
        	<td class="even_row" colspan="<?php if (get_option("wpp_widget_on") == "on") { echo "2"; } else { echo "1"; } ?>" style="font-size:10px">
            	<div class="preview_box">
            	<?php
				echo (!empty($wpp->options['title']) ? $wpp->options['markup']['title-start'].$wpp->options['title'].$wpp->options['markup']['title-end']."<br />" : "");
				echo $wpp->options['markup']['wpp-start'] . "<br />";
				$title = "Sed ut perspiciatis unde omnis iste";
				
				if ($wpp->options['comments'] || $wpp->options['views'] || $wpp->options['author'] || $wpp->options['date']) {
					$stats = "[ stats tag ]";
				} else {
					$stats = "";
				}
				
				for ($i=0; $i < $wpp->options['limit']; $i++) {
					if ( $wpp->options['excerpt'] ) { 
						$post_title = substr(htmlspecialchars(stripslashes($title)),0,$wpp->options['characters']) . $wpp->options['markup']['delimiter'];
					} else {
						$post_title = htmlspecialchars(stripslashes($title));
					}					
					echo $wpp->options['markup']['post-start'] . "&lt;a href=\"post-url-here\" title=\"$title\"&gt;".$post_title." ".$stats."&lt;/a&gt;" . $wpp->options['markup']['post-end'] ."<br />";
				}
				echo $wpp->options['markup']['wpp-end'];
				?>
                </div>
            </td>
        </tr>
        <?php if (get_option("wpp_widget_on") == "on") : ?>
        <tr>
        	<td class="odd_row"><label><?php echo __('Preview (Code Snippet):', 'wordpress-popular-posts'); ?>  <span style="color:#ff0000; font-size:9px">NEW!</span></label><br /><small>(<?php echo __('click on Update Settings to see changes', 'wordpress-popular-posts'); ?>)</small></td>
        	<td class="odd_row" colspan="<?php if (get_option("wpp_widget_on") == "on") { echo "2"; } else { echo "1"; } ?>" style="font-size:10px">
            	<div class="preview_box">
            	<?php
				echo (!empty($wpp->options_snippet['title']) ? $wpp->options_snippet['markup']['title-start'].$wpp->options_snippet['title'].$wpp->options_snippet['markup']['title-end']."<br />" : "");
				echo $wpp->options_snippet['markup']['wpp-start'] . "<br />";
				$title = "Sed ut perspiciatis unde omnis iste";
				
				if ($wpp->options_snippet['comments'] || $wpp->options_snippet['views'] || $wpp->options_snippet['author'] || $wpp->options_snippet['date']) {
					$stats_snippet = "[ stats tag ]";
				} else {
					$stats_snippet = "";
				}
				for ($i=0; $i < $wpp->options_snippet['limit']; $i++) {
					if ( $wpp->options_snippet['excerpt'] ) { 
						$post_title = substr(htmlspecialchars(stripslashes($title)),0,$wpp->options_snippet['characters']) . $wpp->options_snippet['markup']['delimiter'];
					} else {
						$post_title = htmlspecialchars(stripslashes($title));
					}
					echo $wpp->options_snippet['markup']['post-start'] . "&lt;a href=\"post-url-here\" title=\"$title\"&gt;".$post_title." ".$stats_snippet."&lt;/a&gt;" . $wpp->options_snippet['markup']['post-end'] ."<br />";
				}
				echo $wpp->options_snippet['markup']['wpp-end'];
				?>
                </div>
            </td>
        </tr>
        <?php endif; ?>
		<tr>
			<td colspan="<?php if (get_option("wpp_widget_on") == "on") { echo "3"; } else { echo "2"; } ?>" align="center">
				<br />
				<input type="submit" name="Submit" value="<?php echo __('Update settings', 'wordpress-popular-posts'); ?>" id="btn_submit" />	
				<input type="hidden" id="plugin_mostpopular-Submit" name="plugin_mostpopular-Submit" value="1" />
			</td>
		</tr>
	</table>
    <br /><br /><p><?php echo __('Do you <em>like</em> this plugin?', 'wordpress-popular-posts'); ?> <a title="<?php echo __('Rate Wordpress Popular Posts!', 'wordpress-popular-posts'); ?>" href="http://wordpress.org/extend/plugins/wordpress-popular-posts/stats/rate-topic_5377?rate=5&amp;topic_id=5377&amp;_wpnonce=ae391888be"><strong><?php echo __('Rate it 5', 'wordpress-popular-posts'); ?></strong></a> <?php echo __('on the official Plugin Directory!', 'wordpress-popular-posts'); ?></p>
    <p><?php echo __('Do you <em>love</em> this plugin?', 'wordpress-popular-posts'); ?> <a title="<?php echo __('Buy me a beer!', 'wordpress-popular-posts'); ?>" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=dadslayer%40gmail%2ecom&lc=GB&item_name=Wordpress%20Popular%20Posts%20Plugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG_global%2egif%3aNonHosted"><strong><?php echo __('Buy me a beer!', 'wordpress-popular-posts'); ?></strong></a>. <?php echo __('Each donation motivates me to keep releasing free stuff for the Wordpress community!', 'wordpress-popular-posts'); ?></p><br /><br />
	<?php	
	function escapeThis($data) {
	   if (get_magic_quotes_gpc() == 1){
		  if (is_array($data))
			 return array_map('escapeThis', $data);
		  else
			 return stripslashes($data);
	   } else return $data;
	}
?>