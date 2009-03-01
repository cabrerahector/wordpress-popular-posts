<?php
	global $wpp;
	
	$wpp->options = get_option("wpp_options");
			
	if (!is_array( $wpp->options )) {		
		$wpp->options = array( 'title' => 'Popular Posts', 'limit' => 10, 'pages' => true, 'comments' => true, 'views' => true, 'excerpt' => true, 'characters' => 25, 'sortby' => 1, 'range' => 'all-time', 'author' => false, 'date' => false	);			
	}
	
	$wpp->options_snippet = get_option("wpp_options_snippet");
	if (!is_array( $wpp->options_snippet ) || empty($wpp->options_snippet)) $wpp->options_snippet = $wpp->options;	
	
	if ($_POST['plugin_mostpopular-Submit']) {	
		$wpp->options['title'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-WidgetTitle']));
		$wpp->options['limit'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-Limit']));
		$wpp->options['characters'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-ExcerptLimit']));	
		$wpp->options['sortby'] = $_POST['plugin_mostpopular-Sort'];
		$wpp->options['range'] = $_POST['plugin_mostpopular-Range'];		
		
		if (isset($_POST['plugin_mostpopular-IncludePages'])) { $wpp->options['pages'] = true; } else { $wpp->options['pages'] = false; }		
		if (isset($_POST['plugin_mostpopular-ShowCount'])) { $wpp->options['comments'] = true; } else { $wpp->options['comments'] = false; }		
		if (isset($_POST['plugin_mostpopular-ShowViews'])) { $wpp->options['views'] = true; } else { $wpp->options['views'] = false; }		
		if (isset($_POST['plugin_mostpopular-ShowAuthor'])) { $wpp->options['author'] = true; } else { $wpp->options['author'] = false; }
		if (isset($_POST['plugin_mostpopular-ShowDate'])) { $wpp->options['date'] = true; } else { $wpp->options['date'] = false; }		
		if (isset($_POST['plugin_mostpopular-ShowExcerpt'])) { $wpp->options['excerpt'] = true; } else { $wpp->options['excerpt'] = false; }		
		if ( (!is_numeric($wpp->options['limit'])) || ($wpp->options['limit'] <= 0) ) $wpp->options['limit'] = 10;		
		if ( (!is_numeric($wpp->options['characters'])) || ($wpp->options['characters'] <= 0) ) $wpp->options['characters'] = 25;		
		
		if (isset($_POST['plugin_mostpopular-SeparateSettings'])) {
			update_option("wpp_widget_on", "on");
			
			$wpp->options_snippet['title'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-SnippetTitle']));
			$wpp->options_snippet['limit'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-Limit_Snippet']));
			$wpp->options_snippet['characters'] = htmlspecialchars(escapeThis($_POST['plugin_mostpopular-ExcerptLimit_Snippet']));	
			$wpp->options_snippet['sortby'] = $_POST['plugin_mostpopular-Sort_Snippet'];
			$wpp->options_snippet['range'] = $_POST['plugin_mostpopular-Range_Snippet'];
			
			if (isset($_POST['plugin_mostpopular-IncludePages_Snippet'])) { $wpp->options_snippet['pages'] = true; } else { $wpp->options_snippet['pages'] = false; }		
			if (isset($_POST['plugin_mostpopular-ShowCount_Snippet'])) { $wpp->options_snippet['comments'] = true; } else { $wpp->options_snippet['comments'] = false; }		
			if (isset($_POST['plugin_mostpopular-ShowViews_Snippet'])) { $wpp->options_snippet['views'] = true; } else { $wpp->options_snippet['views'] = false; }	
			if (isset($_POST['plugin_mostpopular-ShowAuthor_Snippet'])) { $wpp->options_snippet['author'] = true; } else { $wpp->options_snippet['author'] = false; }
			if (isset($_POST['plugin_mostpopular-ShowDate_Snippet'])) { $wpp->options_snippet['date'] = true; } else { $wpp->options_snippet['date'] = false; }	
			if (isset($_POST['plugin_mostpopular-ShowExcerpt_Snippet'])) { $wpp->options_snippet['excerpt'] = true; } else { $wpp->options_snippet['excerpt'] = false; }		
			if ( (!is_numeric($wpp->options_snippet['limit'])) || ($wpp->options_snippet['limit'] <= 0) ) $wpp->options_snippet['limit'] = 10;		
			if ( (!is_numeric($wpp->options_snippet['characters'])) || ($wpp->options_snippet['characters'] <= 0) ) $wpp->options_snippet['characters'] = 25;
		} else {
			update_option("wpp_widget_on", "off");			
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
		
		input.checkbox {border:#999 1px solid}
		
		#btn_submit {border:#333 1px solid; background:#006699; color:#fff; cursor:pointer}
	</style>
	<h2 id="wmpp-title">Wordpress Popular Posts</h2>
	<p>With Wordpress Popular Posts, you can show your visitors what are the most popular entries on your blog. You can either use it as a <a href="widgets.php"><strong>Sidebar Widget</strong></a>  (<a href="http://codex.wordpress.org/Plugins/WordPress_Widgets" rel="external nofollow"><small>what's a widget?</small></a>), or place it in your templates using this handy <strong>code snippet</strong>: <em>&lt;?php if (function_exists('get_mostpopular')) get_mostpopular(); ?&gt</em>. </p>
	<p>Use the Settings Manager below to tweak Wordpress Popular Posts to your liking.</p>
	<h3>Settings</h3>
	<form action="<?php $_SERVER['REQUEST_URI']; ?>" method="post" name="mppform">
	<table cellpadding="0" cellspacing="1" id="config_panel">
    	<?php if ( $wpp->widgetized() ) : ?>
    	<tr>
        	<td class="even_row" width="250"><label>Separate settings for the widget and the code snippet: <span style="color:#ff0000; font-size:9px">NEW!</span></label></td>
            <td class="even_row" align="center"<?php if (get_option("wpp_widget_on") == "on") echo " colspan=\"2\""; ?>><input type="checkbox" id="plugin_mostpopular-SeparateSettings" name="plugin_mostpopular-SeparateSettings" <?php if (get_option("wpp_widget_on") == "on") echo "checked=\"checked\""; ?> class="checkbox" /></td>
        </tr>
		<?php endif; ?>
        <?php if (get_option("wpp_widget_on") == "on") : ?>
        <tr>
        	<td class="separate_titles"></td>
            <td class="separate_titles"><small>WIDGET SETTINGS</small></td>
            <td class="separate_titles"><small>CODE SNIPPET SETTINGS</small></td>
        </tr>
        <?php endif; ?>
		<tr>
			<td class="odd_row"><label for="plugin_mostpopular-WidgetTitle">Title: </label></td>
			<td class="odd_row"><input type="text" id="plugin_mostpopular-WidgetTitle" name="plugin_mostpopular-WidgetTitle" value="<?php echo $wpp->options['title'];?>" class="txt" /></td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="odd_row"><input type="text" id="plugin_mostpopular-SnippetTitle" name="plugin_mostpopular-SnippetTitle" value="<?php echo $wpp->options_snippet['title'];?>" class="txt" /></td>
            <?php endif; ?>
		</tr>
		<tr>
			<td class="even_row"><label for="plugin_mostpopular-Limit">Show up to: </label></td>
			<td class="even_row"><input type="text" id="plugin_mostpopular-Limit" name="plugin_mostpopular-Limit" value="<?php echo $wpp->options['limit'];?>" class="nro" /> posts</td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="even_row"><input type="text" id="plugin_mostpopular-Limit_Snippet" name="plugin_mostpopular-Limit_Snippet" value="<?php echo $wpp->options_snippet['limit'];?>" class="nro" /> posts</td>
            <?php endif; ?>
		</tr>
        <tr>
			<td class="odd_row"><label for="plugin_mostpopular-Range">Time Range: <span style="color:#ff0000; font-size:9px">NEW!</span></label></td>
			<td class="odd_row">
				<select name="plugin_mostpopular-Range">
                     <option value="all-time" <?php if ($wpp->options['range'] == 'all-time') { ?> selected="selected"<?php } ?>>All-Time</option>
                     <option value="today" <?php if ($wpp->options['range'] == 'today') { ?> selected="selected"<?php } ?>>Today</option>
                     <option value="weekly" <?php if ($wpp->options['range'] == 'weekly') { ?> selected="selected"<?php } ?>>Last Week</option>
                     <option value="monthly" <?php if ($wpp->options['range'] == 'monthly') { ?> selected="selected"<?php } ?>>Last Month</option>
                     <option value="yearly" <?php if ($wpp->options['range'] == 'yearly') { ?> selected="selected"<?php } ?>>Last Year</option>
				</select>
			</td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="odd_row">
				<select name="plugin_mostpopular-Range_Snippet">
                     <option value="all-time" <?php if ($wpp->options_snippet['range'] == 'all-time') { ?> selected="selected"<?php } ?>>All-Time</option>
                     <option value="today" <?php if ($wpp->options_snippet['range'] == 'today') { ?> selected="selected"<?php } ?>>Today</option>
                     <option value="weekly" <?php if ($wpp->options_snippet['range'] == 'weekly') { ?> selected="selected"<?php } ?>>Last Week</option>
                     <option value="monthly" <?php if ($wpp->options_snippet['range'] == 'monthly') { ?> selected="selected"<?php } ?>>Last Month</option>
                     <option value="yearly" <?php if ($wpp->options_snippet['range'] == 'yearly') { ?> selected="selected"<?php } ?>>Last Year</option>
				</select>
			</td>
            <?php endif; ?>
		</tr>
		<tr>
			<td class="even_row"><label for="plugin_mostpopular-Sort">Sort posts by: </label></td>
			<td class="even_row">
            	<select name="plugin_mostpopular-Sort">				
					<option value="1" <?php if ($wpp->options['sortby'] == 1) {?> selected="selected"<?php } ?>>Comments</option>
					<option value="2" <?php if ($wpp->options['sortby'] == 2) {?> selected="selected"<?php } ?>>Total Views</option>
					<option value="3" <?php if ($wpp->options['sortby'] == 3) {?> selected="selected"<?php } ?>>Avg. Daily Views</option>
				</select>
			</td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="even_row">
            	<select name="plugin_mostpopular-Sort_Snippet">				
					<option value="1" <?php if ($wpp->options_snippet['sortby'] == 1) {?> selected="selected"<?php } ?>>Comments</option>
					<option value="2" <?php if ($wpp->options_snippet['sortby'] == 2) {?> selected="selected"<?php } ?>>Total Views</option>
					<option value="3" <?php if ($wpp->options_snippet['sortby'] == 3) {?> selected="selected"<?php } ?>>Avg. Daily Views</option>
				</select>
			</td>
            <?php endif; ?>
		</tr>
		<tr>
			<td class="odd_row"><label for="plugin_mostpopular-IncludePages">Include pages:</label></td>
			<td class="odd_row" align="center"><input type="checkbox" id="plugin_mostpopular-IncludePages" name="plugin_mostpopular-IncludePages" <?php if ($wpp->options['pages']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="odd_row" align="center"><input type="checkbox" id="plugin_mostpopular-IncludePages_Snippet" name="plugin_mostpopular-IncludePages_Snippet" <?php if ($wpp->options_snippet['pages']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php endif; ?>
		</tr>
        <tr>
        	<td class="separate_title" colspan="<?php if (get_option("wpp_widget_on") == "on") { echo "3"; } else { echo "2"; } ?>"><small>STATS TAG SETTINGS</small></td>
        </tr>
		<tr>
			<td class="even_row"><label for="plugin_mostpopular-ShowCount">Show comment count:</label></td>
			<td class="even_row" align="center"><input type="checkbox" id="plugin_mostpopular-ShowCount" name="plugin_mostpopular-ShowCount" <?php if ($wpp->options['comments']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="even_row" align="center"><input type="checkbox" id="plugin_mostpopular-ShowCount_Snippet" name="plugin_mostpopular-ShowCount_Snippet" <?php if ($wpp->options_snippet['comments']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php endif; ?>
		</tr>
		<tr>
			<td class="odd_row"><label for="plugin_mostpopular-ShowViews">Show pageviews:</label></td>
			<td class="odd_row" align="center"><input type="checkbox" id="plugin_mostpopular-ShowViews" name="plugin_mostpopular-ShowViews" <?php if ($wpp->options['views']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="odd_row" align="center"><input type="checkbox" id="plugin_mostpopular-ShowViews_Snippet" name="plugin_mostpopular-ShowViews_Snippet" <?php if ($wpp->options_snippet['views']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php endif; ?>
		</tr>
        <tr>
			<td class="even_row"><label for="plugin_mostpopular-ShowAuthor">Show author: <span style="color:#ff0000; font-size:9px">NEW!</span></label></td>
			<td class="even_row" align="center"><input type="checkbox" id="plugin_mostpopular-ShowAuthor" name="plugin_mostpopular-ShowAuthor" <?php if ($wpp->options['author']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="even_row" align="center"><input type="checkbox" id="plugin_mostpopular-ShowAuthor_Snippet" name="plugin_mostpopular-ShowAuthor_Snippet" <?php if ($wpp->options_snippet['author']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php endif; ?>
		</tr>
        <tr>
			<td class="even_row"><label for="plugin_mostpopular-ShowDate">Show date: <span style="color:#ff0000; font-size:9px">NEW!</span></label></td>
			<td class="even_row" align="center"><input type="checkbox" id="plugin_mostpopular-ShowDate" name="plugin_mostpopular-ShowDate" <?php if ($wpp->options['date']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="even_row" align="center"><input type="checkbox" id="plugin_mostpopular-ShowDate_Snippet" name="plugin_mostpopular-ShowDate_Snippet" <?php if ($wpp->options_snippet['date']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php endif; ?>
		</tr>
		<tr>
			<td class="odd_row"><label for="plugin_mostpopular-ShowExcerpt">Shorten title output:</label></td>
			<td class="odd_row" align="center"><input type="checkbox" id="plugin_mostpopular-ShowExcerpt" name="plugin_mostpopular-ShowExcerpt" <?php if ($wpp->options['excerpt']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="odd_row" align="center"><input type="checkbox" id="plugin_mostpopular-ShowExcerpt_Snippet" name="plugin_mostpopular-ShowExcerpt_Snippet" <?php if ($wpp->options_snippet['excerpt']) echo "checked=\"checked\""; ?> class="checkbox" /></td>
            <?php endif; ?>            
		</tr>
		<?php if ($wpp->options['excerpt'] || ($wpp->options_snippet['excerpt'] && (get_option("wpp_widget_on") == "on"))) { ?>
		<tr>
			<td class="even_row"><label for="plugin_mostpopular-ExcerptLimit">Shorten title to:</label></td>
			<td class="even_row" align="center"><input type="text" id="plugin_mostpopular-ExcerptLimit" name="plugin_mostpopular-ExcerptLimit" value="<?php echo $wpp->options['characters'];?>" class="nro" /> characters</td>
            <?php if (get_option("wpp_widget_on") == "on") : ?>
            <td class="even_row" align="center"><input type="text" id="plugin_mostpopular-ExcerptLimit_Snippet" name="plugin_mostpopular-ExcerptLimit_Snippet" value="<?php echo $wpp->options_snippet['characters'];?>" class="nro" /> characters</td>
            <?php endif; ?>
		</tr>        
		<?php } ?>
		<tr>
			<td colspan="2" align="center">
				<br />
				<input type="submit" name="Submit" value="Update options" id="btn_submit" />	
				<input type="hidden" id="plugin_mostpopular-Submit" name="plugin_mostpopular-Submit" value="1" />
			</td>
		</tr>
	</table>    
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