<?php
/*
Plugin Name: Wordpress Popular Posts
Plugin URI: http://rauru.com/wordpress-popular-posts
Description: Retrieves the most active entries of your blog and displays them on a list. Use it as a widget or place it in your templates using  <strong>&lt;?php get_mostpopular(); ?&gt;</strong>
Version: 1.4.0
Author: H&eacute;ctor Cabrera
Author URI: http://rauru.com/
*/

if ( !class_exists('WordpressPopularPosts') ) {
	class WordpressPopularPosts {
	
		var $version = "1.4.0";
		var $options = array();
		var $options_snippet = array();
		var $options_holder = array();
		var $table_name = "pageviews";
		
		function WordpressPopularPosts() {
			$this->options = get_option("wpp_options");			
			if ( empty($this->options) ) {
				$this->options = get_option("widget_mostpopular");
				if ( empty($this->options) ) {
					$this->options = array(
						'title' => __('Popular Posts', 'wordpress-popular-posts'),
						'limit' => 10,
						'pages' => true,
						'comments' => true,
						'views' => true,
						'excerpt' => false,
						'characters' => 25,
						'sortby' => 1,
						'range' => 'all-time',
						'author' => false,
						'date' => false
					);
				}
			}			
			
			$this->options_snippet = get_option("wpp_options_snippet");			
			
			if ( empty($this->options_snippet) ) $this->options_snippet = $this->options;
			
			if ( !get_option("wpp_widget_on") ) {
				add_option("wpp_widget_on", "off");
			}
			
			update_option("wpp_options", $this->options);
			update_option("wpp_options_snippet", $this->options_snippet);
			
			$this->options_holder = array($this->options, $this->options_snippet);
		}	
		
		function get_popular_posts($summoner) {
			
			global $wpdb, $post;
			$table_wpp = $wpdb->prefix . $this->table_name;
						
			if ( $this->options_holder[$summoner]['pages'] ) {
				$nopages = '';
			} else {
				$nopages = "AND $wpdb->posts.post_type = 'post'";
			}
			
			// time range
			switch( $this->options_holder[$summoner]['range'] ) {
				case 'all-time':
					$range = "post_date_gmt < '".gmdate("Y-m-d H:i:s")."'";
					break;
				case 'today':					
					$range = "$table_wpp.day = '".gmdate("Y-m-d")."'";					
					break;
				case 'weekly':
					$range = "$table_wpp.day >= '".gmdate("Y-m-d")."' - INTERVAL 7 DAY";
					break;
				case 'monthly':
					$range = "$table_wpp.day >= '".gmdate("Y-m-d")."' - INTERVAL 30 DAY";
					break;
				case 'yearly':
					$range = "$table_wpp.day >= '".gmdate("Y-m-d")."' - INTERVAL 365 DAY";
					break;
				default:
					$range = "post_date_gmt < '".gmdate("Y-m-d H:i:s")."'";
					break;
			}
			
			// sorting options
			switch( $this->options_holder[$summoner]['sortby'] ) {
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
			
			
			// dynamic query fields
			$fields = ', ';			
			if ( $this->options_holder[$summoner]['views'] ) $fields .= "SUM($table_wpp.pageviews) AS 'pageviews' ";
			if ( $this->options_holder[$summoner]['comments'] ) {
				if ( $fields != ', ' ) {
					$fields .= ", $wpdb->posts.comment_count AS 'comment_count' ";
				} else {
					$fields .= "$wpdb->posts.comment_count AS 'comment_count' ";
				}
			}
			if ( $sortby == 'avg_views' ) {
				if ( $fields != ', ' ) {
					$fields .= ", (SUM($table_wpp.pageviews)/(IF ( DATEDIFF(CURDATE(), MIN($table_wpp.day)) > 0, DATEDIFF(CURDATE(), MIN($table_wpp.day)), 1) )) AS 'avg_views' ";
				} else {
					$fields .= "(SUM($table_wpp.pageviews)/(IF ( DATEDIFF(CURDATE(), MIN($table_wpp.day)) > 0, DATEDIFF(CURDATE(), MIN($table_wpp.day)), 1) )) AS 'avg_views' ";
				}
			}
			if ( $this->options_holder[$summoner]['author'] ) {
				if ( $fields != ', ' ) {
					$fields .= ", (SELECT $wpdb->users.display_name FROM $wpdb->users WHERE $wpdb->users.ID = $wpdb->posts.post_author ) AS 'display_name'";
				} else {
					$fields .= "(SELECT $wpdb->users.display_name FROM $wpdb->users WHERE $wpdb->users.ID = $wpdb->posts.post_author ) AS 'display_name'";
				}
			}
			if ( $this->options_holder[$summoner]['date'] ) {
				if ( $fields != ', ' ) {
					$fields .= ", $wpdb->posts.post_date_gmt AS 'date_gmt'";
				} else {
					$fields .= "$wpdb->posts.post_date_gmt AS 'date_gmt'";
				}
			}
			
			if (strlen($fields) == 2) $fields = '';
			
			$mostpopular = $wpdb->get_results("SELECT $wpdb->posts.ID, $wpdb->posts.post_title $fields FROM $wpdb->posts LEFT JOIN $table_wpp ON $wpdb->posts.ID = $table_wpp.postid WHERE post_status = 'publish' AND post_password = '' AND $range AND pageviews > 0 $nopages GROUP BY postid ORDER BY $sortby DESC LIMIT " . $this->options_holder[$summoner]['limit'] . "");
			
			if ( !is_array($mostpopular) || empty($mostpopular) ) {
				echo "".__('<p>Sorry. No data so far.</p>', 'wordpress-popular-posts')."";
			} else {	
				echo "\n"."<ul><!-- Wordpress Popular Posts Plugin ". $this->version ." -->"."\n";
				$stat_count = 0;
				
				foreach ($mostpopular as $post) {
				
					$post_stats = " ";
					
					if ( $this->options_holder[$summoner]['excerpt'] ) { 
						$post_title = substr(htmlspecialchars(stripslashes($post->post_title)),0,$this->options_holder[$summoner]['characters']) . " [...]";
					} else {
						$post_title = htmlspecialchars(stripslashes($post->post_title));
					}			
					
					if ( $this->options_holder[$summoner]['comments'] ) {
						$comment_count = (int) $post->comment_count;
						$post_stats .= $comment_count . " " . __(' comment(s)', 'wordpress-popular-posts');
					}
					if ( $this->options_holder[$summoner]['views'] ) {
						$views_text = __(' view(s)', 'wordpress-popular-posts');
						if ($this->options_holder[$summoner]['sortby'] == 2) {
							$pageviews = (int) $post->pageviews;
						} else if ($this->options_holder[$summoner]['sortby'] == 3 && $this->options_holder[$summoner]['range'] != 'today') {							
							$pageviews = ceil($post->avg_views);
							$views_text = __(' view(s) per day', 'wordpress-popular-posts');
						} else {
							$pageviews = (int) $post->pageviews;
						}			
						
						if ($post_stats != " ") {
							$post_stats .= " | $pageviews $views_text";
						} else {							
							$post_stats .= "$pageviews $views_text";
						}										
					}
					if ( $this->options_holder[$summoner]['author'] ) {
						if ($post_stats != " ") {
							$post_stats .= " | by <span class=\"author\">".$post->display_name."</span>";
						} else {					
							$post_stats .= "by <span class=\"author\">".$post->display_name."</span>";
						}
					}
					if ( $this->options_holder[$summoner]['date'] ) {
						if ($post_stats != " ") {
							$post_stats .= " | posted on ".date("F, j",strtotime($post->date_gmt));
						} else {					
							$post_stats .= "posted on ".date("F, j",strtotime($post->date_gmt));
						}
					}
					if ( !empty($post_stats) ) {
						echo '<li><a href="'.get_permalink($post->ID).'" title="'. htmlspecialchars(stripslashes($post->post_title)) .'">'. html_entity_decode($post_title) .'</a> <span class="post-stats">' . $post_stats . '</span></li>'."\n";
					} else {
						echo '<li><a href="'.get_permalink($post->ID).'" title="'. htmlspecialchars(stripslashes($post->post_title)) .'">'. html_entity_decode($post_title) .'</a></li>'."\n";
					}
				}
				echo "</ul><!-- End Wordpress Popular Posts Plugin ". $this->version ." -->"."\n";		
			}
		}
		
		function update_mostpopular($content) {
			if ( (is_single() || is_page()) && !is_user_logged_in() ) {
				global $wpdb;
				global $wp_query;		
			
				$postid = $wp_query->post->ID; // get post ID
				$table_name = $wpdb->prefix . "pageviews";		
				
				$result = $wpdb->query("INSERT INTO $table_name (postid, day) VALUES ('".$wpdb->escape($postid)."', curdate()) ON DUPLICATE KEY UPDATE pageviews=pageviews+1");
			}
			return $content;
		}
		
		/* Widget core */
		function widget_mostpopular($args) {
			extract($args);			
			
			echo $before_widget;
			if ($this->options['title'] != '') {
				echo $before_title;
				echo $this->options['title'];
				echo $after_title;
			}			
			$this->get_popular_posts(0);			
			echo $after_widget;
		}
		/* End Widget core */
		
		function mostpopular_control() {  
			echo "<p>" . __("Please visit <a href=\"options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php\">Wordpress Popular Post Administration Page</a> to adjust its settings.",'wordpress-popular-posts') . "</p>";
		}
		
		function init_mostpopular(){
			register_sidebar_widget("Popular Posts", array(&$this,"widget_mostpopular"));
			register_widget_control("Popular Posts", array(&$this,'mostpopular_control'), 200, 200 );
		}
		
		function mostpopular_header() {
			echo "\n"."<!-- Wordpress Popular Posts v". $this->version ." -->"."\n".'<link rel="stylesheet" href="'.WP_PLUGIN_URL.'/wordpress-popular-posts/wpp.css" type="text/css" media="screen" />'."\n"."<!-- Wordpress Popular Posts v". $this->version ." -->"."\n";	
		}
		
		function widgetized() {
			if ( function_exists('is_active_widget') ) {
				if ( is_active_widget(array(&$this,"widget_mostpopular")) ) {					
					return true;
				} else {
					return false;
				}
			} else {				
				return false;
			}
		}
		
		// Plugin localization (Credits: Aleksey Timkov at@uadeveloper.com)
		function wordpress_popular_posts_textdomain() {
			load_plugin_textdomain('wordpress-popular-posts', 'wp-content/plugins/wordpress-popular-posts/lang/' . WPLANG);
		}
		
		// Version validator - inspired on cforms' version checker function
		function wpp_check_version($plugin) {
			if( strpos(basename(dirname(__FILE__)) . '/wordpress-popular-posts.php',$plugin)!==false ) {
				$version_file = wp_remote_fopen("http://rauru.com/wppp.chk");
				if ($version_file) { // version file was successfully retrieved from Rauru.com
					$data = explode('@', $version_file);
					if ( version_compare($data[0], $this->version, '>') ) {					
						echo '<td colspan="5" class="plugin-update" style="line-height:1.2em;">'.$data[1].'</td>';
					}
				}
			}
		}
	} // End Wordpress Popular Posts class
	
	$wpp = new WordpressPopularPosts();
	
	add_action('the_content', array(&$wpp,'update_mostpopular') );
	add_action("plugins_loaded", array(&$wpp,"init_mostpopular"));
	add_action('after_plugin_row', array(&$wpp,'wpp_check_version') );
	add_action('init', array(&$wpp,'wordpress_popular_posts_textdomain'));
	add_action('admin_menu', 'add_mostpopular_admin');	
	add_action('wp_head', array(&$wpp,'mostpopular_header'));
	
	/* Plugin core */
	function get_mostpopular() { 
		global $wpp;
		if ( !empty($wpp->options_snippet['title']) ) {
			echo "<h2 class=\"widgettitle\">".$wpp->options_snippet['title']."</h2>";
		}	
		$wpp->get_popular_posts(1);
	}
	/* End Plugin core */
	
	/* Admin page */
	function mostpopular_adminpage() {
		require dirname(__FILE__) . '/admin.php';
	}

	function add_mostpopular_admin() {
		add_submenu_page('options-general.php', 'Wordpress Popular Posts', 'Wordpress Popular Posts', 10, __FILE__, 'mostpopular_adminpage');
	}
	/* End Admin page */	
}
?>