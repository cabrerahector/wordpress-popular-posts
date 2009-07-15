<?php
/*
Plugin Name: Wordpress Popular Posts
Plugin URI: http://rauru.com/wordpress-popular-posts
Description: Retrieves the most active entries of your blog and displays them with your own formatting (<em>optional</em>). Use it as a widget or place it in your templates using  <strong>&lt;?php get_mostpopular(); ?&gt;</strong>
Version: 1.5.0
Author: H&eacute;ctor Cabrera
Author URI: http://rauru.com/
*/

if ( !class_exists('WordpressPopularPosts') ) {
	class WordpressPopularPosts {
	
		var $version = "1.5.0";
		var $options = array();
		var $options_snippet = array();
		var $options_holder = array();
		var $table_name = "pageviews";
		var $qTrans = false;
		var $GD = false;
		var $postRating = false;
		
		function WordpressPopularPosts() {
			$this->options = get_option("wpp_options");
			if ( empty($this->options) ) {
				$this->options = get_option("widget_mostpopular");
				if ( empty($this->options) ) {
					$this->options = array(
						'title' => __('Popular Posts', 'wordpress-popular-posts'),
						'limit' => 10,
						'pages' => true,
						'thumbnail' => array('show' => false, 'width' => 70, 'height' => 70),
						'pattern' => array('active' => false, 'text' => '{title}: {summary} {stats}'),
						'comments' => true,
						'views' => true,
						'excerpt' => false,
						'characters' => 25,
						'post-excerpt' => false,
						'post-characters' => 55,
						'sortby' => 1,
						'range' => 'all-time',
						'author' => false,
						'date' => false,
						'rating' => false,
						'custom-markup' => false,
						'markup' => array('wpp-start'=>'&lt;ul&gt;', 'wpp-end'=>'&lt;/ul&gt;', 'post-start'=>'&lt;li&gt;', 'post-end'=>'&lt;/li&gt;', 'display'=>'block', 'delimiter' => ' [...]', 'title-start' => '&lt;h2&gt;', 'title-end' => '&lt;/h2&gt;', 'default-title' => true)
					);
				}
			}			
			
			$this->options_snippet = get_option("wpp_options_snippet");			
			
			if ( empty($this->options_snippet) ) $this->options_snippet = $this->options;
			
			if ( !get_option("wpp_widget_on") ) {
				add_option("wpp_widget_on", "off");
			}			
			
			if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
				$this->qTrans = true;
			}
			
			if (extension_loaded('gd') && function_exists('gd_info')) {
				$this->GD = true;
			}
			
			update_option("wpp_options", $this->options);
			update_option("wpp_options_snippet", $this->options_snippet);
			
			$this->options_holder = array($this->options, $this->options_snippet);
			
			add_action('the_content', array(&$this,'update_mostpopular') );
			add_action("plugins_loaded", array(&$this,"init_mostpopular"));
			add_action('after_plugin_row', array(&$this,'wpp_check_version') );
			add_action('init', array(&$this,'wordpress_popular_posts_textdomain'));	
			add_action('wp_head', array(&$this,'mostpopular_header'));
			register_activation_hook(__FILE__, $this->wordpress_popular_posts_install());
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
					$range = "$table_wpp.last_viewed > NOW() - INTERVAL 24 HOUR";
					break;
				case 'weekly':
					$range = "$table_wpp.last_viewed >= '".gmdate("Y-m-d")."' - INTERVAL 7 DAY";
					break;
				case 'monthly':
					$range = "$table_wpp.last_viewed >= '".gmdate("Y-m-d")."' - INTERVAL 30 DAY";
					break;
				case 'yearly':
					$range = "$table_wpp.last_viewed >= '".gmdate("Y-m-d")."' - INTERVAL 365 DAY";
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
			if ( $this->options_holder[$summoner]['views'] || ($sortby == 'pageviews') ) $fields .= "$table_wpp.pageviews AS 'pageviews' ";
			if ( $this->options_holder[$summoner]['comments'] ) {
				if ( $fields != ', ' ) {
					$fields .= ", $wpdb->posts.comment_count AS 'comment_count' ";
				} else {
					$fields .= "$wpdb->posts.comment_count AS 'comment_count' ";
				}
			}
			if ( $sortby == 'avg_views' ) {
				if ( $fields != ', ' ) {
					$fields .= ", ($table_wpp.pageviews/(IF ( DATEDIFF(CURDATE(), MIN($table_wpp.day)) > 0, DATEDIFF(CURDATE(), MIN($table_wpp.day)), 1) )) AS 'avg_views' ";
				} else {
					$fields .= "($table_wpp.pageviews/(IF ( DATEDIFF(CURDATE(), MIN($table_wpp.day)) > 0, DATEDIFF(CURDATE(), MIN($table_wpp.day)), 1) )) AS 'avg_views' ";
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
			
			echo "SELECT $wpdb->posts.ID, $wpdb->posts.post_title $fields FROM $wpdb->posts LEFT JOIN $table_wpp ON $wpdb->posts.ID = $table_wpp.postid WHERE post_status = 'publish' AND post_password = '' AND $range AND pageviews > 0 $nopages GROUP BY postid ORDER BY $sortby DESC LIMIT " . $this->options_holder[$summoner]['limit'] . "";
			
			if ( !is_array($mostpopular) || empty($mostpopular) ) {
				echo "".__('<p>Sorry. No data so far.</p>', 'wordpress-popular-posts')."";
			} else {
				if ($this->options_holder[$summoner]['custom-markup']) {
					echo "\n" . html_entity_decode($this->options_holder[$summoner]['markup']['wpp-start'], ENT_QUOTES) . "<!-- Wordpress Popular Posts Plugin v". $this->version ." -->"."\n";
				} else {
					echo "\n"."<ul><!-- Wordpress Popular Posts Plugin v". $this->version ." -->"."\n";
				}
				//$stat_count = 0;
				
				foreach ($mostpopular as $wppost) {					
				
					$post_stats = "";
					$stats = "";
					$data = array();
					
					/* qTranslate integration check */
					($this->qTrans) ? $tit = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($wppost->post_title) : $tit = $wppost->post_title;
					
					if ( $this->options_holder[$summoner]['excerpt'] ) {
						$post_title = "<span class=\"wpp-post-title\">" . substr(htmlspecialchars(stripslashes($tit)),0,$this->options_holder[$summoner]['characters']) . "...</span>";
					} else {
						$post_title = "<span class=\"wpp-post-title\">" . htmlspecialchars(stripslashes($tit)) . "</span>";
					}
					/* End qTranslate integration check */
					
					if ( $this->options_holder[$summoner]['post-excerpt'] ) {
						if ($this->options_holder[$summoner]['pattern']['active']) {
							$post_content = "<span class=\"wpp-excerpt\">" . $this->get_summary($wppost->ID, $summoner) . "</span>";
						} else {
							$post_content = ": <span class=\"wpp-excerpt\">" . $this->get_summary($wppost->ID, $summoner) . "...</span>";
						}
					} else {
						$post_content = "";
					}
					
					if ( $this->options_holder[$summoner]['comments'] ) {
						$comment_count = (int) $wppost->comment_count;
						$post_stats .= "<span class=\"wpp-comments\">" . $comment_count . " " . __(' comment(s)', 'wordpress-popular-posts') . "</span>";
					}
					if ( $this->options_holder[$summoner]['views'] ) {
						$views_text = __(' view(s)', 'wordpress-popular-posts');
						if ($this->options_holder[$summoner]['sortby'] == 2) {
							$pageviews = (int) $wppost->pageviews;
						} else if ($this->options_holder[$summoner]['sortby'] == 3 && $this->options_holder[$summoner]['range'] != 'today') {							
							$pageviews = ceil($wppost->avg_views);
							$views_text = __(' view(s) per day', 'wordpress-popular-posts');
						} else {
							$pageviews = (int) $wppost->pageviews;
						}			
						
						if ($post_stats != " ") {
							$post_stats .= " | <span class=\"wpp-views\">$pageviews $views_text</span>";
						} else {							
							$post_stats .= "<span class=\"wpp-views\">$pageviews $views_text</span>";
						}										
					}
					if ( $this->options_holder[$summoner]['author'] ) {
						if ($post_stats != " ") {
							$post_stats .= " | by <span class=\"author\">".$wppost->display_name."</span>";
						} else {					
							$post_stats .= "by <span class=\"author\">".$wppost->display_name."</span>";
						}
					}
					if ( $this->options_holder[$summoner]['date'] ) {
						if ($post_stats != " ") {
							$post_stats .= " | <span class=\"wpp-date\">posted on ".date("F, j",strtotime($wppost->date_gmt))."</span>";
						} else {					
							$post_stats .= "<span class=\"wpp-date\">posted on ".date("F, j",strtotime($wppost->date_gmt))."</span>";
						}
					}
					
					if (!empty($post_stats)) {
						if ($this->options_holder[$summoner]['markup']['display'] == 'block') {
							$stats = ' <span class="post-stats" style="display:block;">' . $post_stats . '</span> ';
						} else {
							$stats = ' <span class="post-stats">' . $post_stats . '</span> ';
						}
					}
					
					if ($this->options_holder[$summoner]['thumbnail']['show'] ) {
						// let's try to retrieve the first image of the current post
						$img = $this->get_img($wppost->ID);
						if ( (!$img || empty($img)) || !$this->GD ) {
							$thumb = "";
						} else {
							$thumb = "<a href=\"".get_permalink($wppost->ID)."\" title=\"". htmlspecialchars(stripslashes($tit)) ."\"><img src=\"". get_bloginfo('url') . "/" . PLUGINDIR . "/wordpress-popular-posts/scripts/timthumb.php?src=". $img[1] ."&amp;h=".$this->options_holder[$summoner]['thumbnail']['height']."&amp;w=".$this->options_holder[$summoner]['thumbnail']['width']."&amp;zc=1\" alt=\"".$wppost->post_title."\" border=\"0\" class=\"wpp-thumbnail\" "."/></a>";
						}
												
					}
					
					if ($this->options_holder[$summoner]['rating'] && function_exists('the_ratings_results')) {
						$rating = '<span class="wpp-rating">'.the_ratings_results($wppost->ID).'</span>';
					} else {
						$rating = '';
					}
					
					$data = array(
						'title' => '<a href="'.get_permalink($wppost->ID).'" title="'. htmlspecialchars(stripslashes($tit)) .'">'. html_entity_decode($post_title) .'</a>',
						'summary' => $post_content,
						'stats' => $stats,
						'img' => $thumb,
						'id' => $wppost->ID
					);		
					
					
					if ($this->options_holder[$summoner]['custom-markup']) {
						if ($this->options_holder[$summoner]['pattern']['active']) {
							echo html_entity_decode($this->options_holder[$summoner]['markup']['post-start'], ENT_QUOTES) . $this->format_content($this->options_holder[$summoner]['pattern']['text'], $data) . html_entity_decode($this->options_holder[$summoner]['markup']['post-end'], ENT_QUOTES) . "\n";
						} else {
							echo html_entity_decode($this->options_holder[$summoner]['markup']['post-start'], ENT_QUOTES) . '<a href="'.get_permalink($wppost->ID).'" title="'. htmlspecialchars(stripslashes($tit)) .'">'. html_entity_decode($post_title) .'</a>'.$post_content.' '. $stats . $rating . html_entity_decode($this->options_holder[$summoner]['markup']['post-end'], ENT_QUOTES) . "\n";
						}
					} else {
						echo '<li>'. $thumb .'<a href="'. get_permalink($wppost->ID) .'" title="'. htmlspecialchars(stripslashes($tit)) .'">'. html_entity_decode($post_title) .'</a>'. $post_content .' '. $stats . $rating .'</li>' . "\n";
					}
				}			
				
				if ($this->options_holder[$summoner]['custom-markup']) {
					echo html_entity_decode($this->options_holder[$summoner]['markup']['wpp-end'], ENT_QUOTES) . "<!-- End Wordpress Popular Posts Plugin v". $this->version ." -->"."\n";
				} else {
					echo "\n"."</ul><!-- End Wordpress Popular Posts Plugin v". $this->version ." -->"."\n";
				}
				
				
			}
		}
		
		function update_mostpopular($content) {
			if ( (is_single() || is_page()) && !is_user_logged_in() ) {
				global $wpdb;
				global $wp_query;		
			
				$postid = $wp_query->post->ID; // get post ID
				$table_name = $wpdb->prefix . $this->table_name;
				
				$exists = $wpdb->get_results("SELECT postid FROM $table_name WHERE postid = '".$postid."'");
				
				if ($exists) {
					$result = $wpdb->query("UPDATE $table_name SET last_viewed = NOW(), pageviews = pageviews + 1 WHERE postid = '$postid'");
				} else {				
					$result = $wpdb->query("INSERT INTO $table_name (postid, day, last_viewed) VALUES ('".$postid."', NOW(), NOW())");
				}
				
			}
			
			return $content;
		}
		
		/* Widget core */
		function widget_mostpopular($args) {
			extract($args);
			
			echo $before_widget;					
			if ($this->options['title'] != '') {
			 ($this->qTrans) ? $wtit = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($this->options['title']) : $wtit = $this->options['title'];
				if ($this->options['custom-markup'] && !$this->options['markup']['default-title']) {
					echo html_entity_decode($this->options['markup']['title-start'], ENT_QUOTES) . stripslashes($wtit) . html_entity_decode($this->options['markup']['title-end'], ENT_QUOTES);
				} else {
					echo $before_title . stripslashes($wtit) . $after_title;
				}
			}			
			$this->get_popular_posts(0);			
			echo $after_widget;
		}
		/* End Widget core */
		
		function mostpopular_control() {  
			echo "<p>" . __("Please visit <a href=\"options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php\">Wordpress Popular Post Administration Page</a> to adjust its settings.",'wordpress-popular-posts') . "</p>";
		}
		
		function init_mostpopular(){
			$widget_ops = array('classname' => 'widget_popular_posts', 'description' => __( 'The most popular posts on your blog' ) );
			wp_register_sidebar_widget('popular-posts', __('Popular Posts'), array(&$this,'widget_mostpopular'), $widget_ops);
			wp_register_widget_control('popular-posts', __('Popular Posts'), array(&$this,'mostpopular_control'));
		}
		
		function mostpopular_header() {
			echo "\n"."<!-- Wordpress Popular Posts v". $this->version ." -->"."\n".'<link rel="stylesheet" href="'.WP_PLUGIN_URL.'/wordpress-popular-posts/style/wpp.css" type="text/css" media="screen" />'."\n"."<!-- Wordpress Popular Posts v". $this->version ." -->"."\n";	
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
			load_plugin_textdomain('wordpress-popular-posts', 'wp-content/plugins/wordpress-popular-posts');
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
		
		// install Wordpress Popular Posts
		function wordpress_popular_posts_install () {
			global $wpdb;
			$table_name = $wpdb->prefix . "pageviews";
			$tpp = "popularpostsdata";			
			
			if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) {
				if ( $wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix.$tpp."'") != $wpdb->prefix.$tpp ) {
					$sql = "CREATE TABLE " . $wpdb->prefix.$tpp . " ( UNIQUE KEY id (postid), postid int(10) NOT NULL, day datetime NOT NULL default '0000-00-00 00:00:00', last_viewed datetime NOT NULL default '0000-00-00 00:00:00', pageviews int(10) default 1 );";		
					require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
					dbDelta($sql);
				} else { // table already exists, let's check if updated
					$last = false;
					$result = $wpdb->get_results("DESCRIBE " . $wpdb->prefix.$tpp);
					
					if ($result) {
						foreach ($result as $r) {
							if ($r->Field == 'last_viewed') {
								$last = true;
								break;
							}
						}
					
						if ($last == false) {
							$wpdb->query("CREATE TABLE wp_wpptemp ( UNIQUE KEY id (postid), postid int(10) NOT NULL, day datetime NOT NULL default '0000-00-00 00:00:00', last_viewed datetime NOT NULL default '0000-00-00 00:00:00', pageviews int(10) default 1 )");
							
							$old_rows = $wpdb->get_results("SELECT DISTINCT postid FROM ".$wpdb->prefix.$tpp." ORDER BY postid");
							foreach($old_rows as $row) {
								$tmp = $wpdb->get_results("SELECT day, sum(pageviews) AS views FROM ".$wpdb->prefix.$tpp." WHERE postid = '".$row->postid."'");
								foreach($tmp as $t) {
									$wpdb->query("INSERT INTO wp_wpptemp (postid, day, last_viewed, pageviews) VALUES (".$row->postid.", '".$t->day."', NOW(), ".$t->views.")");
								}
							}
							
							$wpdb->query("RENAME TABLE ".$wpdb->prefix.$tpp." TO ".$wpdb->prefix."popularpostsdata_backup, wp_wpptemp TO ".$wpdb->prefix.$tpp.";");	
						}
					}
				}
			} else { // table already exists, let's check if it is up to date		
				$wpdb->query("ALTER TABLE ".$table_name." RENAME ".$wpdb->prefix.$tpp.", CHANGE day day datetime NOT NULL default '0000-00-00 00:00:00';");
			}
			$this->table_name = $tpp;		
		}
		
		// retrieves sidebar data from functions.php
		function get_sidebar_data($data) {
			// eg. $data = "widget=popular-posts&data=before_widget"
			
			global $wp_registered_widgets, $wp_registered_sidebars;
			$params = array();
			$s = "";		
			
			parse_str($data, $params);
			
			if (!array_key_exists('widget', $params) || !array_key_exists('data', $params)) return false;
						
			$sidebars_widgets = get_option('sidebars_widgets', array());			
			
			for ($i=1; $i<count($sidebars_widgets); $i++) {		
				if ($sidebars_widgets['sidebar-'.$i][0] == $params['widget']) {
					$s = 'sidebar-'.$i;
					break;
				}
			}		
			
			if (empty($s)) {
				return false;
			} else {
				// possible values: name, id, before_widget, after_widget, before_title, after_title
				return $wp_registered_sidebars[$s][$params['data']];
			}		
		}
		
		function get_summary($id, $summoner){
			if (!is_numeric($id) || !is_numeric($summoner)) return false;
			global $wpdb;
			$excerpt = $wpdb->get_results("SELECT post_excerpt FROM $wpdb->posts WHERE ID = " . $id, ARRAY_A);
			if (empty($excerpt[0]['post_excerpt'])) {
				$excerpt = $wpdb->get_results("SELECT post_content FROM $wpdb->posts WHERE ID = " . $id, ARRAY_A);
				$excerpt[0]['post_content'] = preg_replace("/\[caption.*\[\/caption\]/", "", $excerpt[0]['post_content']);
				return substr(strip_tags($excerpt[0]['post_content']), 0, $this->options_holder[$summoner]['post-characters']);
			} else {
				$excerpt[0]['post_excerpt'] = preg_replace("/\[caption.*\[\/caption\]/", "", $excerpt[0]['post_excerpt']);;
				return substr(strip_tags($excerpt[0]['post_excerpt']), 0, $this->options_holder[$summoner]['post-characters']);				
			}
		}
		
		function get_img($id = "", $print = false) {
			if ( empty($id) || !is_numeric($id) ) return false;
			
			global $wpdb;
			$source = array();
			
			$raw = $wpdb->get_results("SELECT post_content FROM $wpdb->posts WHERE ID = " . $id, ARRAY_A);
			
			$source = strip_tags($raw[0]["post_content"], "<img>");
		
			$count = substr_count($source, '<img');
			
			if ($count > 0) { // images have been found
				$p = substr( $source, strpos($source, "<img", 0), (strpos($source, '>') - strpos($source, "<img", 0) + 1) );
				
				$img_pattern = '/<\s*img [^\>]*src\s*=\s*[\""\']?([^\""\'\s>]*)/i';			
				preg_match($img_pattern, $p, $imgm);
				
				if ($print)
					echo $imgm[1];
				else
					return $imgm;
			} else { // post has no images
				return false;
			}
		}
		
		function format_content ($string, $data = array()) {
			if (empty($string) || (empty($data) || !is_array($data))) return false;
			
			$params = array();
			$pattern = '/\{(summary|stats|title|image|rating)\}/i';		
			preg_match_all($pattern, $string, $matches);
			
			for ($i=0; $i < count($matches[0]); $i++) {		
				if (strtolower($matches[0][$i]) == "{title}") {
					$params[$matches[0][$i]] = $data['title'];
					continue;
				}
				if (strtolower($matches[0][$i]) == "{stats}") {
					$params[$matches[0][$i]] = $data['stats'];
					continue;
				}
				if (strtolower($matches[0][$i]) == "{summary}") {
					$params[$matches[0][$i]] = $data['summary'];
					continue;
				}
				if (strtolower($matches[0][$i]) == "{image}") {
					$params[$matches[0][$i]] = $data['img'];
					continue;
				}
				// WP-PostRatings check
				if (function_exists('the_ratings_results') && ( $this->options['rating'] || $this->options_snippet['rating'] )) {
					if (strtolower($matches[0][$i]) == "{rating}") {
						$params[$matches[0][$i]] = the_ratings_results($data['id']);
						continue;
					}
				}
			}
			
			for ($i=0; $i < count($params); $i++) {		
				$string = str_replace($matches[0][$i], $params[$matches[0][$i]], $string);
			}
			
			return $string;
		}
	} // End Wordpress Popular Posts class
	
	$wpp = new WordpressPopularPosts();
	add_action('admin_menu', 'add_mostpopular_admin');
	
	/* Plugin core */
	function get_mostpopular() {
		global $wpp;
		if ( !empty($wpp->options_snippet['title']) ) {
			($wpp->qTrans) ? $ptit = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($wpp->options_snippet['title']) : $ptit = $wpp->options_snippet['title'];
			if ($wpp->options_snippet['custom-markup']) {
				echo html_entity_decode($wpp->options_snippet['markup']['title-start'], ENT_QUOTES) . stripslashes($ptit) . html_entity_decode($wpp->options_snippet['markup']['title-end'], ENT_QUOTES);
			} else {
				echo "<h2 class=\"widgettitle\">".stripslashes($ptit)."</h2>";
			}
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