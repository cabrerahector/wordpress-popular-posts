<?php
/*
Plugin Name: Wordpress Popular Posts
Plugin URI: http://rauru.com/wordpress-popular-posts
Description: Retrieves the most active entries of your blog and displays them on a list. Use it as a widget or place it in your templates using  <strong>&lt;?php get_mostpopular(); ?&gt;</strong>
Version: 1.3
Author: H&eacute;ctor Cabrera
Author URI: http://rauru.com/
*/

$localversion = 1.3;

function widget_mostpopular($args) {
	extract($args);	
	
	$options = get_option("widget_mostpopular");
	if (!is_array( $options )) {
		$options = array(
			'title' => 'Popular Posts',
			'limit' => 10,
			'pages' => true,
			'comments' => true,
			'views' => true,
			'excerpt' => true,
			'characters' => 25,
			'sortby' => 1
		);
		update_option("widget_mostpopular", $options);
	}
	
	echo $before_widget;
	if ($options['title'] != '') {
		echo $before_title;
		echo $options['title'];
		echo $after_title;
	}
		
	global $wpdb, $post;	
	
	$table_wpp = $wpdb->prefix . "pageviews";
	
	if ($options['pages']) {
		$nopages = '';
	} else {
		$nopages = "AND $wpdb->posts.post_type = 'post'";
	}
	
	switch($options['sortby']) {
		case 1:
			$sortby = 'comment_count';
			break;
		case 2:
			$sortby = 'pageviews';
			break;
		case 3:
			$sortby = 'avg_views';
			break;
		default:
			$sortby = 'comment_count';
			break;
	}
	
	$mostpopular = $wpdb->get_results("SELECT $wpdb->posts.ID, $wpdb->posts.post_title, SUM($table_wpp.pageviews) AS 'pageviews', $wpdb->posts.comment_count AS 'comment_count', (SELECT DATEDIFF(CURDATE(), MIN($table_wpp.day))) AS 'days', (SUM($table_wpp.pageviews)/(IF ( DATEDIFF(CURDATE(), MIN($table_wpp.day)) > 0, DATEDIFF(CURDATE(), MIN($table_wpp.day)), 1) )) AS 'avg_views' FROM $wpdb->posts LEFT JOIN $table_wpp ON $wpdb->posts.ID = $table_wpp.postid WHERE post_status = 'publish' AND post_password = '' AND post_date_gmt < '".gmdate("Y-m-d H:i:s")."' AND pageviews > 0 $nopages GROUP BY postid ORDER BY $sortby DESC LIMIT " . $options['limit'] . "");
	
	if (!is_array($mostpopular) || empty($mostpopular)) {
		echo "<p>Sorry. No data so far.</p>";
	} else {	
		echo "<ul><!-- Wordpress Popular Posts Plugin -->";
		$stat_count = 0;
		
		foreach ($mostpopular as $post) {
		
			$post_stats = " ";
			
			if ($options['excerpt']) { 
				$post_title = substr(htmlspecialchars(stripslashes($post->post_title)),0,$options['characters']) . " [...]";
			} else {
				$post_title = htmlspecialchars(stripslashes($post->post_title));
			}			
			
			if ($options['comments']) {
				$comment_count = (int) $post->comment_count;
				$post_stats .= "$comment_count comment(s)";
				$stat_count++;
			}
			if ($options['views']) {
				$views_text = "view(s)";
				if ($options['sortby'] == 2) {
					$pageviews = (int) $post->pageviews;
				} else if ($options['sortby'] == 3) {
					$days = $post->days;
					if ($days == 0) $days = 1;
					//$pageviews = ceil( ($post->pageviews) / ($days) );
					$pageviews = ceil($post->avg_views);
					$views_text = "view(s) per day";
				} else {
					$pageviews = (int) $post->pageviews;
				}			
				
				if ($stat_count > 0) {
					$post_stats .= " | $pageviews $views_text";
				} else {					
					$post_stats .= "$pageviews $views_text";
				}
			}
			if (!empty($post_stats)) {
				echo '<li><a href="'.get_permalink().'" title="'. htmlspecialchars(stripslashes($post->post_title)) .'">'. $post_title .'</a> <span class="post-stats">' . $post_stats . '</span></li>';
			} else {
				echo '<li><a href="'.get_permalink().'" title="'. htmlspecialchars(stripslashes($post->post_title)) .'">'. $post_title .'</a></li>';
			}
		}
		echo "</ul><!-- End Wordpress Popular Posts Plugin -->";		
	}
	echo $after_widget;
}

function mostpopular_control() {  
	echo "<p>Please visit <a href=\"http://rauru.com/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php\">Wordpress Popular Post Administration Page</a> to adjust its settings.</p>";
}

function init_mostpopular(){
    register_sidebar_widget("Popular Posts", "widget_mostpopular");
	register_widget_control("Popular Posts", 'mostpopular_control', 200, 200 );
}

function mostpopular_header() {
	echo "<!-- Wordpress Popular Posts -->"."\n".'<link rel="stylesheet" href="'.WP_PLUGIN_URL.'/wordpress-popular-posts/wpp.css" type="text/css" media="screen" />'."\n"."<!-- Wordpress Popular Posts -->"."\n";	
}

add_action("plugins_loaded", "init_mostpopular");
add_action('wp_head', 'mostpopular_header');

function update_mostpopular($content) {
	if ((is_single()) || (is_page()) && !is_user_logged_in()) {
		global $wpdb;
		global $wp_query;		
	
		$postid = $wp_query->post->ID; // get post ID
		$table_name = $wpdb->prefix . "pageviews";		
		
		$result = $wpdb->query("INSERT INTO $table_name (postid, day) VALUES ('".$wpdb->escape($postid)."', curdate()) ON DUPLICATE KEY UPDATE pageviews=pageviews+1");
	}
	return $content;
}

// install Wordpress Popular Posts
function jal_install () {
	global $wpdb;
	$table_name = $wpdb->prefix . "pageviews";	
	if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) {
		$sql = "CREATE TABLE " . $table_name . " ( UNIQUE KEY id (postid, day), postid int(10) NOT NULL, day date NOT NULL, pageviews int(10) default 1 );";		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}

add_action('the_content', 'update_mostpopular');
register_activation_hook(__FILE__,'jal_install');


/* Plugin's admin page */
function mostpopular_adminpage() {	
	$mpp_widget_on = false;	
	
	if (function_exists('is_active_widget')) {
		if (is_active_widget('widget_mostpopular')) {
			$mpp_widget_on = true;
		} else {
			$mpp_widget_on = false;
		}
	}
	
	$options = get_option("widget_mostpopular");
			
	if (!is_array( $options )) {		
		$options = array(
			'title' => 'Popular Posts',
			'limit' => 10,
			'pages' => true,
			'comments' => true,
			'views' => true,
			'excerpt' => true,
			'characters' => 25,
			'sortby' => 1
		);			
	}
	
	if ($_POST['plugin_mostpopular-Submit']) {
	$options['title'] = htmlspecialchars($_POST['plugin_mostpopular-WidgetTitle']);
	$options['limit'] = htmlspecialchars($_POST['plugin_mostpopular-Limit']);
	$options['characters'] = htmlspecialchars($_POST['plugin_mostpopular-ExcerptLimit']);	
	$options['sortby'] = $_POST['plugin_mostpopular-Sort'];
	
	if (isset($_POST['plugin_mostpopular-IncludePages'])) { $options['pages'] = true; } else { $options['pages'] = false; }
	
	if (isset($_POST['plugin_mostpopular-ShowCount'])) { $options['comments'] = true; } else { $options['comments'] = false; }
	
	if (isset($_POST['plugin_mostpopular-ShowViews'])) { $options['views'] = true; } else { $options['views'] = false; }
	
	if (isset($_POST['plugin_mostpopular-ShowExcerpt'])) { $options['excerpt'] = true; } else { $options['excerpt'] = false; }
	
	if ( (!is_numeric($options['limit'])) || ($options['limit'] <= 0) ) $options['limit'] = 10;
	
	if ( (!is_numeric($options['characters'])) || ($options['characters'] <= 0) ) $options['characters'] = 25;
	
	
	update_option("widget_mostpopular", $options);
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
		
		input.txt, input.nro {padding:3px 5px!important; height:15px; border:#999 1px solid;}
		input.nro {width:20px}
		
		input.checkbox {border:#999 1px solid}
		
		#btn_submit {border:#333 1px solid; background:#006699; color:#fff; cursor:pointer}
	</style>
    <h2 id="wmpp-title">Wordpress Popular Posts</h2>
    <p>With <strong>Wordpress Popular Posts</strong>, you can show your visitors what are the most popular entries on your blog. You can either use it as a <a href="widgets.php"><strong>Sidebar Widget</strong></a>  (<a href="http://codex.wordpress.org/Plugins/WordPress_Widgets" rel="external nofollow"><small>what's a widget?</small></a>), or place it in your templates using this handy code snippet: <em>&lt;?php if (function_exists('get_mostpopular')) get_mostpopular(); ?&gt</em>. </p>
    <p>Use the Settings Manager below to tweak Wordpress Popular Posts to your liking.</p>
    <h3>Settings</h3>
    <form action="<?php $_SERVER['REQUEST_URI']; ?>" method="post" name="mppform">
	<table cellpadding="0" cellspacing="1" id="config_panel">
    	<tr>
        	<td class="odd_row"><label for="plugin_mostpopular-WidgetTitle">Title: </label></td>
            <td class="odd_row"><input type="text" id="plugin_mostpopular-WidgetTitle" name="plugin_mostpopular-WidgetTitle" value="<?php echo $options['title'];?>" class="txt" /></td>
        </tr>
        <tr>
        	<td class="even_row"><label for="plugin_mostpopular-Limit">Show up to: </label></td>
            <td class="even_row"><input type="text" id="plugin_mostpopular-Limit" name="plugin_mostpopular-Limit" value="<?php echo $options['limit'];?>" class="nro" /> posts</td>
        </tr>
        <tr>
        	<td class="odd_row"><label for="plugin_mostpopular-Sort">Sort posts by: </label></td>
            <td class="odd_row">
            	<select name="plugin_mostpopular-Sort">
				<?php if ($options['sortby'] == 1) {?>
                    <option value="1" selected="selected">Comments</option>
                    <option value="2">Total Views</option>
                    <option value="3">Avg. Daily Views</option>
                <?php } else if ($options['sortby'] == 2) {?>
                    <option value="1">Comments</option>
                    <option value="2" selected="selected">Total Views</option>
                    <option value="3">Avg. Daily Views</option>
                <?php } else if ($options['sortby'] == 3) {?>
                    <option value="1">Comments</option>
                    <option value="2">Total Views</option>
                    <option value="3" selected="selected">Avg. Daily Views</option>
                <?php } else { ?>
                    <option value="1">Comments</option>
                    <option value="2">Total Views</option>
                    <option value="3">Avg. Daily Views</option>
                <?php } ?>
				</select>
			</td>
		</tr>
        <tr>
        	<td class="even_row"><label for="plugin_mostpopular-IncludePages">Include pages:</label></td>
        	<td class="even_row"><input type="checkbox" id="plugin_mostpopular-IncludePages" name="plugin_mostpopular-IncludePages" <?php if ($options['pages']) echo "checked=\"checked\""; ?> class="checkbox" /></td>            
        </tr>
        <tr>
        	<td class="odd_row"><label for="plugin_mostpopular-ShowCount">Show comment count:</label></td>
        	<td class="odd_row"><input type="checkbox" id="plugin_mostpopular-ShowCount" name="plugin_mostpopular-ShowCount" <?php if ($options['comments']) echo "checked=\"checked\""; ?> class="checkbox" /></td>            
        </tr>
        <tr>
        	<td class="even_row"><label for="plugin_mostpopular-ShowViews">Show pageviews:</label></td>
        	<td class="even_row"><input type="checkbox" id="plugin_mostpopular-ShowViews" name="plugin_mostpopular-ShowViews" <?php if ($options['views']) echo "checked=\"checked\""; ?> class="checkbox" /></td>            
        </tr>
        <tr>
        	<td class="odd_row"><label for="plugin_mostpopular-ShowExcerpt">Show excerpt:</label></td>
        	<td class="odd_row"><input type="checkbox" id="plugin_mostpopular-ShowExcerpt" name="plugin_mostpopular-ShowExcerpt" <?php if ($options['excerpt']) echo "checked=\"checked\""; ?> class="checkbox" /></td>            
        </tr>
        <?php if ($options['excerpt']) { ?>
        <tr>
        	<td class="even_row"><label for="plugin_mostpopular-ExcerptLimit">Limit excerpt to </label></td>
            <td class="even_row"><input type="text" id="plugin_mostpopular-ExcerptLimit" name="plugin_mostpopular-ExcerptLimit" value="<?php echo $options['characters'];?>" class="nro" /> characters.</td>
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
}

function add_mostpopular_admin() {
	add_submenu_page('options-general.php', 'Wordpress Popular Posts', 'Wordpress Popular Posts', 10, __FILE__, 'mostpopular_adminpage');
}

add_action('admin_menu', 'add_mostpopular_admin');

// plugin's core
function get_mostpopular() {	
	
	$options = get_option("widget_mostpopular");
	if (!is_array( $options )) {
		$options = array(
			'title' => 'Popular Posts',
			'limit' => 10,
			'pages' => true,
			'comments' => true,
			'views' => true,
			'excerpt' => true,
			'characters' => 25,
			'sortby' => 1
		);
		update_option("widget_mostpopular", $options);
	}
	
	if ($options['title'] != '') {
		echo "<h2 class=\"widgettitle\">".$options['title']."</h2>";
	}
		
	global $wpdb, $post;	
	
	$table_wpp = $wpdb->prefix . "pageviews";
	
	if ($options['pages']) {
		$nopages = '';
	} else {
		$nopages = "AND $wpdb->posts.post_type = 'post'";
	}
	
	switch($options['sortby']) {
		case 1:
			$sortby = 'comment_count';
			break;
		case 2:
			$sortby = 'pageviews';
			break;
		case 3:
			$sortby = 'avg_views';
			break;
		default:
			$sortby = 'comment_count';
			break;
	}
	
	$mostpopular = $wpdb->get_results("SELECT $wpdb->posts.ID, $wpdb->posts.post_title, SUM($table_wpp.pageviews) AS 'pageviews', $wpdb->posts.comment_count AS 'comment_count', (SELECT DATEDIFF(CURDATE(), MIN($table_wpp.day))) AS 'days', (SUM($table_wpp.pageviews)/(IF ( DATEDIFF(CURDATE(), MIN($table_wpp.day)) > 0, DATEDIFF(CURDATE(), MIN($table_wpp.day)), 1) )) AS 'avg_views' FROM $table_wpp LEFT JOIN $wpdb->posts ON $table_wpp.postid = $wpdb->posts.ID WHERE post_status = 'publish' AND post_password = '' AND post_date_gmt < '".gmdate("Y-m-d H:i:s")."' AND pageviews > 0 $nopages GROUP BY postid ORDER BY $sortby DESC LIMIT " . $options['limit'] . "");
	
	if (!is_array($mostpopular) || empty($mostpopular)) {
		echo "<p>Sorry. No data so far.</p>";
	} else {	
		echo "<ul>";
		$stat_count = 0;
		
		foreach ($mostpopular as $post) {
		
			$post_stats = " ";
			
			if ($options['excerpt']) { 
				$post_title = substr(htmlspecialchars(stripslashes($post->post_title)),0,$options['characters']) . " [...]";
			} else {
				$post_title = htmlspecialchars(stripslashes($post->post_title));
			}			
			
			if ($options['comments']) {
				$comment_count = (int) $post->comment_count;
				$post_stats .= "$comment_count comment(s)";
				$stat_count++;
			}
			if ($options['views']) {
				$views_text = "view(s)";
				if ($options['sortby'] == 2) {
					$pageviews = (int) $post->pageviews;
				} else if ($options['sortby'] == 3) {
					$days = $post->days;
					if ($days == 0) $days = 1;
					//$pageviews = ceil( ($post->pageviews) / ($days) );
					$pageviews = ceil($post->avg_views);
					$views_text = "view(s) per day";
				} else {
					$pageviews = (int) $post->pageviews;
				}			
				
				if ($stat_count > 0) {
					$post_stats .= " | $pageviews $views_text";
				} else {					
					$post_stats .= "$pageviews $views_text";
				}
			}
			if (!empty($post_stats)) {
				echo '<li><a href="'.get_permalink($post->ID).'" title="'. htmlspecialchars(stripslashes($post->post_title)) .'">'. $post_title .'</a> <span class="post-stats">' . $post_stats . '</span></li>';
			} else {
				echo '<li><a href="'.get_permalink($post->ID).'" title="'. htmlspecialchars(stripslashes($post->post_title)) .'">'. $post_title .'</a></li>';
			}
		}
		echo "</ul>";		
	}
}

// Version validator - inspired on cforms' version checker function
add_action('after_plugin_row', 'wmpp_check_version');
function wmpp_check_version($plugin) {
	global $localversion;
 	if( strpos(basename(dirname(__FILE__)) . '/wordpress-popular-posts.php',$plugin)!==false ) {
		$version_file = wp_remote_fopen("http://rauru.com/wpmpp.chk");
		if ($version_file) { // version file was successfully retrieved from Rauru.com
			$data = explode('@', $version_file);
			if ( version_compare($data[0], $localversion, '>') ) {					
				echo '<td colspan="5" class="plugin-update" style="line-height:1.2em;">'.$data[1].'</td>';
			}
		}
	}
}
?>