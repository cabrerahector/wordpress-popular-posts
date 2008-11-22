<?php
/*
Plugin Name: Wordpress Popular Posts
Plugin URI: http://rauru.com/wordpress-popular-posts
Description: Retrieves the most active entries of your blog and displays them on a list. Use it as a widget or place it in your templates using  <strong>&lt;?php get_mostpopular('Title', number of posts); ?&gt;</strong>
Version: 1.1
Author: H&eacute;ctor Cabrera
Author URI: http://rauru.com/
*/

// widget core
function widget_mostpopular($args) {
	extract($args);
	
	$options = get_option("widget_mostpopular");
	if (!is_array( $options )) {
		$options = array('title' => 'Popular Posts', 'limit' => 10, 'comments' => true, 'excerpt' => true, 'characters' => 25);
	}
	
	echo $before_widget;
	echo $before_title;
	echo $options['title'];
	echo $after_title;
	
	global $wpdb, $post;		
	
    $mostpopular = $wpdb->get_results("SELECT  $wpdb->posts.ID, post_title, post_name, post_date, COUNT($wpdb->comments.comment_post_ID) AS 'comment_count' FROM $wpdb->posts LEFT JOIN $wpdb->comments ON $wpdb->posts.ID = $wpdb->comments.comment_post_ID WHERE comment_approved = '1' AND post_date_gmt < '".gmdate("Y-m-d H:i:s")."' AND post_status = 'publish' AND post_password = '' GROUP BY $wpdb->comments.comment_post_ID ORDER BY comment_count DESC LIMIT ". $options['limit'] ."");
	echo "<ul>";
	foreach ($mostpopular as $post) {
		if ($options['excerpt']) { 
			$post_title = substr(htmlspecialchars(stripslashes($post->post_title)),0,$options['characters']) . "...";
		} else {
			$post_title = htmlspecialchars(stripslashes($post->post_title));
		}		
		
		if ($options['comments']) {
			$comment_count = (int) $post->comment_count;
			echo "<li><a href=\"".get_permalink()."\">$post_title&nbsp;<strong>($comment_count)</strong></a></li>";
		} else {
			echo "<li><a href=\"".get_permalink()."\">$post_title</a></li>";
		}
	}
	echo "</ul>";
	echo $after_widget;
}

function mostpopular_control() {
  $options = get_option("widget_mostpopular");

  if (!is_array( $options )) {
		$options = array('title' => 'Popular Posts', 'limit' => 10, 'comments' => true, 'excerpt' => true, 'characters' => 25);
  }      

  if ($_POST['mostpopular-Submit']) {
    $options['title'] = htmlspecialchars($_POST['mostpopular-WidgetTitle']);
	$options['limit'] = htmlspecialchars($_POST['mostpopular-Limit']);
	$options['characters'] = htmlspecialchars($_POST['mostpopular-ExcerptLimit']);
	
	if (isset($_POST['mostpopular-ShowCount'])) { $options['comments'] = true; } else { $options['comments'] = false; }
	
	if (isset($_POST['mostpopular-ShowExcerpt'])) { $options['excerpt'] = true; } else { $options['excerpt'] = false; }
	
	if ($options['title'] == '') $options['title'] = "Popular Posts";
	
	if ( (!is_numeric($options['limit'])) || ($options['limit'] <= 0) ) $options['limit'] = 10;
	
	if ( (!is_numeric($options['characters'])) || ($options['characters'] <= 0) ) $options['characters'] = 25;
	

    update_option("widget_mostpopular", $options);
  }

?>
  <p>
    <label for="mostpopular-WidgetTitle">Widget Title: </label>
    <input type="text" id="mostpopular-WidgetTitle" name="mostpopular-WidgetTitle" value="<?php echo $options['title'];?>" class="widefat" />
    <label for="mostpopular-Limit">Show only </label>
    <input type="text" id="mostpopular-Limit" name="mostpopular-Limit" value="<?php echo $options['limit'];?>" class="widefat" style="width:18px" /> posts<br />    
    <input type="checkbox" id="mostpopular-ShowCount" name="mostpopular-ShowCount" <?php if ($options['comments']) echo "checked=\"checked\""; ?> class="checkbox" /> <label for="mostpopular-ShowCount">Show comment count</label><br />    
    <input type="checkbox" id="mostpopular-ShowExcerpt" name="mostpopular-ShowExcerpt" <?php if ($options['excerpt']) echo "checked=\"checked\""; ?> class="checkbox" /> <label for="mostpopular-ShowExcerpt">Show excerpt</label>
    <?php if ($options['excerpt']) { ?>
    <br /><label for="mostpopular-ExcerptLimit">Limit excerpt to </label>
    <input type="text" id="mostpopular-ExcerptLimit" name="mostpopular-ExcerptLimit" value="<?php echo $options['characters'];?>" class="widefat" style="width:18px" /> characters.
    <?php } ?>
    <input type="hidden" id="mostpopular-Submit" name="mostpopular-Submit" value="1" /> 
  </p>
<?php
}

function init_get_mostpopular(){
    register_sidebar_widget("Popular Posts", "widget_mostpopular");
	register_widget_control("Popular Posts", 'mostpopular_control', 200, 200 );
}

add_action("plugins_loaded", "init_get_mostpopular");


// plugin core
function get_mostpopular($title = "Popular Posts", $limit = 25) {
	global $wpdb, $post;		
	
    $mostcommenteds = $wpdb->get_results("SELECT  $wpdb->posts.ID, post_title, post_name, post_date, COUNT($wpdb->comments.comment_post_ID) AS 'comment_count' FROM $wpdb->posts LEFT JOIN $wpdb->comments ON $wpdb->posts.ID = $wpdb->comments.comment_post_ID WHERE comment_approved = '1' AND post_date_gmt < '".gmdate("Y-m-d H:i:s")."' AND post_status = 'publish' AND post_password = '' GROUP BY $wpdb->comments.comment_post_ID ORDER  BY comment_count DESC LIMIT $limit");
	
	echo "<h2 class=\"widgettitle\">$title</h2>";
	echo "<ul>";
	foreach ($mostcommenteds as $post) {
		$post_title = substr(htmlspecialchars(stripslashes($post->post_title)),0,25) . "...";
		$comment_count = (int) $post->comment_count;
		echo "<li><a href=\"".get_permalink()."\">$post_title&nbsp;<strong>($comment_count)</strong></a></li>";
	}
	echo "</ul>";
}

?>