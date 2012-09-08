<?php
/*
Plugin Name: Wordpress Popular Posts
Plugin URI: http://wordpress.org/extend/plugins/wordpress-popular-posts
Description: Showcases your most popular posts to your visitors on your blog's sidebar. Use Wordpress Popular Posts as a widget or place it anywhere on your theme using <strong>&lt;?php wpp_get_mostpopular(); ?&gt;</strong>
Version: 2.3.2
Author: H&eacute;ctor Cabrera
Author URI: http://cabrerahector.com
License: GPL2
*/

if (basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__)) exit('Please do not load this page directly');

/**
 * Load Wordpress Popular Posts to widgets_init.
 * @since 2.0
 */
add_action('widgets_init', 'load_wpp');

function load_wpp() {
	register_widget('WordpressPopularPosts');
}

/**
 * Wordpress Popular Posts class.
 */

if ( !class_exists('WordpressPopularPosts') ) {
	
	class WordpressPopularPosts extends WP_Widget {
		// plugin global variables
		var $version = "2.3.2";
		var $qTrans = false;
		var $postRating = false;
		var $thumb = false;		
		var $pluginDir = "";
		var $charset = "UTF-8";
		var $magicquotes = false;
		var $default_thumbnail = "";
		var $user_ops = array();		
		
		// constructor
		function WordpressPopularPosts() {
			global $wp_version;
				
			// widget settings
			$widget_ops = array( 'classname' => 'popular-posts', 'description' => __('The most Popular Posts on your blog.', 'wordpress-popular-posts') );
	
			// widget control settings
			$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => 'wpp' );
	
			// create the widget
			$this->WP_Widget( 'wpp', 'Wordpress Popular Posts', $widget_ops, $control_ops );
			
			// set plugin path
			if (empty($this->pluginDir)) $this->pluginDir = WP_PLUGIN_URL . '/wordpress-popular-posts';
			
			// set default thumbnail
			$this->default_thumbnail = $this->pluginDir . "/no_thumb.jpg";
			
			// set charset
			$this->charset = get_bloginfo('charset');
			
			// detect PHP magic quotes
			$this->magicquotes = get_magic_quotes_gpc();
			
			// get user options
			$wpp_settings_def = array(
				'stats' => array(
					'order_by' => 'comments',
					'limit' => 10
				),
				'tools' => array(
					'ajax' => false,
					'css' => true,
					'stylesheet' => true,
					'thumbnail' => array(
						'source' => 'featured',
						'field' => ''
					)
				)
			);
			
			$this->user_ops = get_option('wpp_settings_config');
			
			if (!$this->user_ops) {
				add_option('wpp_settings_config', $wpp_settings_def);
				$this->user_ops = $wpp_settings_def;
			}
			
			// print stylesheet
			if ($this->user_ops['tools']['css']) {
				add_action('get_header', array(&$this, 'wpp_print_stylesheet'));
			}
			
			/*
			if ($this->user_ops['tools']['ajax']) {
				// add ajax update to wp_ajax_ hook
				add_action('wp_ajax_nopriv_wpp_update', array(&$this, 'wpp_ajax_update'));
				add_action('wp_head', array(&$this, 'wpp_print_ajax'));
			} else {
				// add update action, no ajax
				add_action('the_content', array(&$this,'wpp_update') );
			}
			*/
			
			add_action('wp_ajax_nopriv_wpp_update', array(&$this, 'wpp_ajax_update'));
			add_action('wp_head', array(&$this, 'wpp_print_ajax'));
			
			// add ajax table truncation to wp_ajax_ hook
			add_action('wp_ajax_wpp_clear_cache', array(&$this, 'wpp_clear_data'));
			add_action('wp_ajax_wpp_clear_all', array(&$this, 'wpp_clear_data'));
			
			// activate textdomain for translations
			add_action('init', array(&$this, 'wpp_textdomain'));
			
			// activate admin page		
			add_action('admin_menu', array(&$this, 'add_wpp_admin'));
			
			// cache maintenance schedule
			register_deactivation_hook(__FILE__, array(&$this, 'wpp_deactivation'));			
			add_action('wpp_cache_event', array(&$this, 'wpp_cache_maintenance'));
			if (!wp_next_scheduled('wpp_cache_event')) {
				$tomorrow = time() + 86400;
				$midnight  = mktime(0, 0, 0, 
					date("m", $tomorrow), 
					date("d", $tomorrow), 
					date("Y", $tomorrow));
				wp_schedule_event( $midnight, 'daily', 'wpp_cache_event' );
			}
			
			// Wordpress version check
			if (version_compare($wp_version, '2.8.0', '<')) add_action('admin_notices', array(&$this, 'wpp_update_warning'));
			
			// qTrans plugin support
			if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) $this->qTrans = true;
			
			// WP-Post Ratings plugin support
			if (function_exists('the_ratings_results')) $this->postRating = true;
			
			// Can we create thumbnails?
			//if (extension_loaded('gd') && function_exists('gd_info') && version_compare(phpversion(), '4.3.0', '>=') && function_exists('add_theme_support')) $this->thumb = true;
			if (extension_loaded('gd') && function_exists('gd_info') && version_compare(phpversion(), '4.3.0', '>=')) $this->thumb = true;
			
			// shortcode
			if( function_exists('add_shortcode') ){
				add_shortcode('wpp', array(&$this, 'wpp_shortcode'));
				add_shortcode('WPP', array(&$this, 'wpp_shortcode'));
			}
			
			// set version and check for upgrades
			$wpp_ver = get_option('wpp_ver');
			if (!$wpp_ver) {
				add_option('wpp_ver', $this->version);
			} else if (version_compare($wpp_ver, $this->version, '<')) {
				$this->wpp_upgrade();
				update_option('wpp_ver', $this->version);
			}
		}

		// builds Wordpress Popular Posts' widgets
		function widget($args, $instance) {
			//$args['widget_id'];
			extract($args);
			echo "<!-- Wordpress Popular Posts Plugin v". $this->version ." [W] [".$instance['range']."]". (($instance['markup']['custom_html']) ? ' [custom]' : ' [regular]') ." -->"."\n";
			echo $before_widget . "\n";
			
			// has user set a title?
			if ($instance['title'] != '') {
				
				$title = apply_filters( 'widget_title', $instance['title'] );
				
				if ($instance['markup']['custom_html'] && $instance['markup']['title-start'] != "" && $instance['markup']['title-end'] != "" ) {
					echo htmlspecialchars_decode($instance['markup']['title-start'], ENT_QUOTES) . $title . htmlspecialchars_decode($instance['markup']['title-end'], ENT_QUOTES);
				} else {
					echo $before_title . $title . $after_title;
				}
			}
			
			echo $this->get_popular_posts($instance);			
			echo $after_widget . "\n";
			echo "<!-- End Wordpress Popular Posts Plugin v". $this->version ." -->"."\n";
		}

		// updates each widget instance when user clicks the "save" button
		function update($new_instance, $old_instance) {
			
			$instance = $old_instance;
			
			$instance['title'] = ($this->magicquotes) ? htmlspecialchars( stripslashes(strip_tags( $new_instance['title'] )), ENT_QUOTES ) : htmlspecialchars( strip_tags( $new_instance['title'] ), ENT_QUOTES );
			$instance['limit'] = is_numeric($new_instance['limit']) ? $new_instance['limit'] : 10;
			$instance['range'] = $new_instance['range'];
			$instance['order_by'] = $new_instance['order_by'];
			
			// FILTERS
			if ($new_instance['post_type'] == "") { // user did not define the custom post type name, so we fall back to default
				$instance['post_type'] = 'post,page';
			} else {
				$instance['post_type'] = $new_instance['post_type'];
			}
			
			$instance['cat'] = implode(",", array_filter(explode(",", preg_replace( '|[^0-9,-]|', '', $new_instance['cat'] ))));
			$instance['author'] = implode(",", array_filter(explode(",", preg_replace( '|[^0-9,]|', '', $new_instance['uid'] ))));			

			$instance['shorten_title']['active'] = $new_instance['shorten_title-active'];
			$instance['shorten_title']['length'] = is_numeric($new_instance['shorten_title-length']) ? $new_instance['shorten_title-length'] : 25;
			$instance['post-excerpt']['active'] = $new_instance['post-excerpt-active'];
			$instance['post-excerpt']['length'] = is_numeric($new_instance['post-excerpt-length']) ? $new_instance['post-excerpt-length'] : 55;
			$instance['post-excerpt']['keep_format'] = $new_instance['post-excerpt-format'];			
			$instance['thumbnail']['thumb_selection'] = "usergenerated";
			
			if ($this->thumb) { // can create thumbnails
				$instance['thumbnail']['active'] = $new_instance['thumbnail-active'];
				
				if (is_numeric($new_instance['thumbnail-width']) && is_numeric($new_instance['thumbnail-height'])) {
					if (($instance['thumbnail']['width'] != $new_instance['thumbnail-width']) || ($instance['thumbnail']['height'] != $new_instance['thumbnail-height'])) {
						$instance['thumbnail']['width'] = $new_instance['thumbnail-width'];
						$instance['thumbnail']['height'] = $new_instance['thumbnail-height'];						
					}
				} else {
					$instance['thumbnail']['width'] = 15;
					$instance['thumbnail']['height'] = 15;
				}
				
			} else { // cannot create thumbnails
				$instance['thumbnail']['active'] = false;
				$instance['thumbnail']['width'] = 15;
				$instance['thumbnail']['height'] = 15;
			}
			
			$instance['rating'] = $new_instance['rating'];
			$instance['stats_tag']['comment_count'] = $new_instance['comment_count'];
			$instance['stats_tag']['views'] = $new_instance['views'];
			$instance['stats_tag']['author'] = $new_instance['author'];
			$instance['stats_tag']['date']['active'] = $new_instance['date'];
			$instance['stats_tag']['date']['format'] = empty($new_instance['date_format']) ? 'F j, Y' : $new_instance['date_format'];
			$instance['markup']['custom_html'] = $new_instance['custom_html'];
			$instance['markup']['wpp-start'] = empty($new_instance['wpp-start']) ? '&lt;ul&gt;' : htmlspecialchars( $new_instance['wpp-start'], ENT_QUOTES );
			$instance['markup']['wpp-end'] = empty($new_instance['wpp-end']) ? '&lt;/ul&gt;' : htmlspecialchars( $new_instance['wpp-end'], ENT_QUOTES );
			$instance['markup']['post-start'] = empty ($new_instance['post-start']) ? '&lt;li&gt;' : htmlspecialchars( $new_instance['post-start'], ENT_QUOTES );
			$instance['markup']['post-end'] = empty ($new_instance['post-end']) ? '&lt;/li&gt;' : htmlspecialchars( $new_instance['post-end'], ENT_QUOTES );
			$instance['markup']['title-start'] = empty($new_instance['title-start']) ? '' : htmlspecialchars( $new_instance['title-start'], ENT_QUOTES );
			$instance['markup']['title-end'] = empty($new_instance['title-end']) ? '' : htmlspecialchars( $new_instance['title-end'], ENT_QUOTES );
			$instance['markup']['pattern']['active'] = $new_instance['pattern_active'];
			//$instance['markup']['pattern']['form'] = empty($new_instance['pattern_form']) ? '{image} {title}: {summary} {stats}' : strip_tags( $new_instance['pattern_form'] );
			$instance['markup']['pattern']['form'] = empty($new_instance['pattern_form']) ? '{image} {title}: {summary} {stats}' : $new_instance['pattern_form'];
	
			return $instance;
		}

		// widget's form
		function form($instance) {
			
			// set default values			
			$defaults = array(
				'title' => __('Popular Posts', 'wordpress-popular-posts'),
				'limit' => 10,
				'range' => 'daily',
				'order_by' => 'comments',
				'post_type' => 'post,page',
				'author' => '',
				'cat' => '',
				'shorten_title' => array(
					'active' => false,
					'length' => 25,
					'keep_format' => false
				),
				'post-excerpt' => array(
					'active' => false,
					'length' => 55
				),				
				'thumbnail' => array(
					'active' => false,
					'width' => 15,
					'height' => 15
				),
				'rating' => false,
				'stats_tag' => array(
					'comment_count' => true,
					'views' => false,
					'author' => false,
					'date' => array(
						'active' => false,
						'format' => 'F j, Y'
					)
				),
				'markup' => array(
					'custom_html' => false,
					'wpp-start' => '&lt;ul&gt;',
					'wpp-end' => '&lt;/ul&gt;',
					'post-start' => '&lt;li&gt;',
					'post-end' => '&lt;/li&gt;',
					'title-start' => '&lt;h2&gt;',
					'title-end' => '&lt;/h2&gt;',
					'pattern' => array(
						'active' => false,
						'form' => '{image} {title}: {summary} {stats}'
					)
				)
			);
			
			// update instance's default options
			$instance = wp_parse_args( (array) $instance, $defaults );
			
			// form
			?>
            <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'wordpress-popular-posts'); ?></label>  <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small> <br />
            <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" /></p>
            <p><label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e('Show up to:', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
            <input id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" value="<?php echo $instance['limit']; ?>"  class="widefat" style="width:50px!important" /> <?php _e('posts', 'wordpress-popular-posts'); ?></p>
            <p><label for="<?php echo $this->get_field_id( 'range' ); ?>"><?php _e('Time Range:', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
            <select id="<?php echo $this->get_field_id( 'range' ); ?>" name="<?php echo $this->get_field_name( 'range' ); ?>" class="widefat">
            	<option value="daily" <?php if ( 'daily' == $instance['range'] ) echo 'selected="selected"'; ?>><?php _e('Last 24 hours', 'wordpress-popular-posts'); ?></option>
                <option value="weekly" <?php if ( 'weekly' == $instance['range'] ) echo 'selected="selected"'; ?>><?php _e('Last 7 days', 'wordpress-popular-posts'); ?></option>
                <option value="monthly" <?php if ( 'monthly' == $instance['range'] ) echo 'selected="selected"'; ?>><?php _e('Last 30 days', 'wordpress-popular-posts'); ?></option>
                <option value="all" <?php if ( 'all' == $instance['range'] ) echo 'selected="selected"'; ?>><?php _e('All-time', 'wordpress-popular-posts'); ?></option>
            </select>
            </p>
            <p><label for="<?php echo $this->get_field_id( 'order_by' ); ?>"><?php _e('Sort posts by:', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small> <br />
            <select id="<?php echo $this->get_field_id( 'order_by' ); ?>" name="<?php echo $this->get_field_name( 'order_by' ); ?>" class="widefat">
            	<option value="comments" <?php if ( 'comments' == $instance['order_by'] ) echo 'selected="selected"'; ?>><?php _e('Comments', 'wordpress-popular-posts'); ?></option>
                <option value="views" <?php if ( 'views' == $instance['order_by'] ) echo 'selected="selected"'; ?>><?php _e('Total views', 'wordpress-popular-posts'); ?></option>
                <option value="avg" <?php if ( 'avg' == $instance['order_by'] ) echo 'selected="selected"'; ?>><?php _e('Avg. daily views', 'wordpress-popular-posts'); ?></option>
            </select>
            </p>
            
            <fieldset style="width:214px; padding:5px;"  class="widefat">
                <legend><?php _e('Posts settings', 'wordpress-popular-posts'); ?></legend>
				<?php if ($this->postRating) : ?>
                <input type="checkbox" class="checkbox" <?php echo ($instance['rating']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'rating' ); ?>" name="<?php echo $this->get_field_name( 'rating' ); ?>" /> <label for="<?php echo $this->get_field_id( 'rating' ); ?>"><?php _e('Display post rating', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
                <?php endif; ?>
                <input type="checkbox" class="checkbox" <?php echo ($instance['shorten_title']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'shorten_title-active' ); ?>" name="<?php echo $this->get_field_name( 'shorten_title-active' ); ?>" /> <label for="<?php echo $this->get_field_id( 'shorten_title-active' ); ?>"><?php _e('Shorten title', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
                <?php if ($instance['shorten_title']['active']) : ?>
                <label for="<?php echo $this->get_field_id( 'shorten_title-length' ); ?>"><?php _e('Shorten title to', 'wordpress-popular-posts'); ?> <input id="<?php echo $this->get_field_id( 'shorten_title-length' ); ?>" name="<?php echo $this->get_field_name( 'shorten_title-length' ); ?>" value="<?php echo $instance['shorten_title']['length']; ?>" class="widefat" style="width:50px!important" /> <?php _e('characters', 'wordpress-popular-posts'); ?></label><br /><br />
                <?php endif; ?>
                <input type="checkbox" class="checkbox" <?php echo ($instance['post-excerpt']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'post-excerpt-active' ); ?>" name="<?php echo $this->get_field_name( 'post-excerpt-active' ); ?>" /> <label for="<?php echo $this->get_field_id( 'post-excerpt-active' ); ?>"><?php _e('Display post excerpt', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
                <?php if ($instance['post-excerpt']['active']) : ?>
                <fieldset class="widefat">
                    <legend><?php _e('Excerpt Properties', 'wordpress-popular-posts'); ?></legend>
                    &nbsp;&nbsp;<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'post-excerpt-format' ); ?>" name="<?php echo $this->get_field_name( 'post-excerpt-format' ); ?>" <?php echo ($instance['post-excerpt']['keep_format']) ? 'checked="checked"' : ''; ?> /> <label for="<?php echo $this->get_field_id( 'post-excerpt-format' ); ?>"><?php _e('Keep text format and links', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
                    &nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'post-excerpt-length' ); ?>"><?php _e('Excerpt length:', 'wordpress-popular-posts'); ?> <input id="<?php echo $this->get_field_id( 'post-excerpt-length' ); ?>" name="<?php echo $this->get_field_name( 'post-excerpt-length' ); ?>" value="<?php echo $instance['post-excerpt']['length']; ?>" class="widefat" style="width:30px!important" /> <?php _e('characters', 'wordpress-popular-posts'); ?></label>
                </fieldset>
                <br />
                <?php endif; ?>                
            </fieldset>
            <br />
            
            <fieldset class="widefat">
                <legend><?php _e('Filters:', 'wordpress-popular-posts'); ?> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small></legend><br />
                &nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php _e('Post type(s):', 'wordpress-popular-posts'); ?></label><br />
                &nbsp;&nbsp;<input id="<?php echo $this->get_field_id( 'post_type' ); ?>" name="<?php echo $this->get_field_name( 'post_type' ); ?>" value="<?php echo $instance['post_type']; ?>" class="widefat" style="width:150px" /><br /><br />
                &nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'cat' ); ?>"><?php _e('Category(ies) ID(s):', 'wordpress-popular-posts'); ?></label><br />
                &nbsp;&nbsp;<input id="<?php echo $this->get_field_id( 'cat' ); ?>" name="<?php echo $this->get_field_name( 'cat' ); ?>" value="<?php echo $instance['cat']; ?>" class="widefat" style="width:150px" /><br /><br />
                &nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'uid' ); ?>"><?php _e('Author(s) ID(s):', 'wordpress-popular-posts'); ?></label><br />
                &nbsp;&nbsp;<input id="<?php echo $this->get_field_id( 'uid' ); ?>" name="<?php echo $this->get_field_name( 'uid' ); ?>" value="<?php echo $instance['author']; ?>" class="widefat" style="width:150px" /><br /><br />
            </fieldset>
            <br />
            
			<fieldset style="width:214px; padding:5px;"  class="widefat">
                <legend><?php _e('Thumbnail settings', 'wordpress-popular-posts'); ?></legend>
				<input type="checkbox" class="checkbox" <?php echo ($instance['thumbnail']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'thumbnail-active' ); ?>" name="<?php echo $this->get_field_name( 'thumbnail-active' ); ?>" /> <label for="<?php echo $this->get_field_id( 'thumbnail-active' ); ?>"><?php _e('Display post thumbnail', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
				<?php if($instance['thumbnail']['active']) : ?>
				<label for="<?php echo $this->get_field_id( 'thumbnail-width' ); ?>"><?php _e('Width:', 'wordpress-popular-posts'); ?></label> 
				<input id="<?php echo $this->get_field_id( 'thumbnail-width' ); ?>" name="<?php echo $this->get_field_name( 'thumbnail-width' ); ?>" value="<?php echo $instance['thumbnail']['width']; ?>"  class="widefat" style="width:30px!important" <?php echo ($this->thumb) ? '' : 'disabled="disabled"' ?> /> <?php _e('px', 'wordpress-popular-posts'); ?> <br />
				<label for="<?php echo $this->get_field_id( 'thumbnail-height' ); ?>"><?php _e('Height:', 'wordpress-popular-posts'); ?></label> 
				<input id="<?php echo $this->get_field_id( 'thumbnail-height' ); ?>" name="<?php echo $this->get_field_name( 'thumbnail-height' ); ?>" value="<?php echo $instance['thumbnail']['height']; ?>"  class="widefat" style="width:30px!important" <?php echo ($this->thumb) ? '' : 'disabled="disabled"' ?> /> <?php _e('px', 'wordpress-popular-posts'); ?><br />				
				<?php endif; ?>
			</fieldset>
			
            <br />
            <fieldset style="width:214px; padding:5px;"  class="widefat">
                <legend><?php _e('Stats Tag settings', 'wordpress-popular-posts'); ?></legend>
                <input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['comment_count']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'comment_count' ); ?>" name="<?php echo $this->get_field_name( 'comment_count' ); ?>" /> <label for="<?php echo $this->get_field_id( 'comment_count' ); ?>"><?php _e('Display comment count', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />                
                <input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['views']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'views' ); ?>" name="<?php echo $this->get_field_name( 'views' ); ?>" /> <label for="<?php echo $this->get_field_id( 'views' ); ?>"><?php _e('Display views', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />            
                <input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['author']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'author' ); ?>" name="<?php echo $this->get_field_name( 'author' ); ?>" /> <label for="<?php echo $this->get_field_id( 'author' ); ?>"><?php _e('Display author', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />            
                <input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['date']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'date' ); ?>" name="<?php echo $this->get_field_name( 'date' ); ?>" /> <label for="<?php echo $this->get_field_id( 'date' ); ?>"><?php _e('Display date', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small>
				<?php if ($instance['stats_tag']['date']['active']) : ?>                	
                	<fieldset class="widefat">
                    	<legend><?php _e('Date Format', 'wordpress-popular-posts'); ?></legend>
                        <label title='F j, Y'><input type='radio' name='<?php echo $this->get_field_name( 'date_format' ); ?>' value='F j, Y' <?php echo ($instance['stats_tag']['date']['format'] == 'F j, Y') ? 'checked="checked"' : ''; ?> /><?php echo date_i18n('F j, Y', time()); ?></label><br />
                        <label title='Y/m/d'><input type='radio' name='<?php echo $this->get_field_name( 'date_format' ); ?>' value='Y/m/d' <?php echo ($instance['stats_tag']['date']['format'] == 'Y/m/d') ? 'checked="checked"' : ''; ?> /><?php echo date_i18n('Y/m/d', time()); ?></label><br />
                        <label title='m/d/Y'><input type='radio' name='<?php echo $this->get_field_name( 'date_format' ); ?>' value='m/d/Y' <?php echo ($instance['stats_tag']['date']['format'] == 'm/d/Y') ? 'checked="checked"' : ''; ?> /><?php echo date_i18n('m/d/Y', time()); ?></label><br />
                        <label title='d/m/Y'><input type='radio' name='<?php echo $this->get_field_name( 'date_format' ); ?>' value='d/m/Y' <?php echo ($instance['stats_tag']['date']['format'] == 'd/m/Y') ? 'checked="checked"' : ''; ?> /><?php echo date_i18n('d/m/Y', time()); ?></label><br />
                    </fieldset>
                <?php endif; ?>
            </fieldset>
            <br />
			
            <fieldset style="width:214px; padding:5px;"  class="widefat">
                <legend><?php _e('HTML Markup settings', 'wordpress-popular-posts'); ?></legend>
                <input type="checkbox" class="checkbox" <?php echo ($instance['markup']['custom_html']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'custom_html' ); ?>" name="<?php echo $this->get_field_name( 'custom_html' ); ?>" /> <label for="<?php echo $this->get_field_id( 'custom_html' ); ?>"><?php _e('Use custom HTML Markup', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
                <?php if ($instance['markup']['custom_html']) : ?>
                <br />
                <p style="font-size:11px"><label for="<?php echo $this->get_field_id( 'title-start' ); ?>"><?php _e('Before / after title:', 'wordpress-popular-posts'); ?></label> <br />
                <input type="text" id="<?php echo $this->get_field_id( 'title-start' ); ?>" name="<?php echo $this->get_field_name( 'title-start' ); ?>" value="<?php echo $instance['markup']['title-start']; ?>" class="widefat" style="width:80px!important" <?php echo ($instance['markup']['custom_html']) ? '' : 'disabled="disabled"' ?> /> <input type="text" id="<?php echo $this->get_field_id( 'title-end' ); ?>" name="<?php echo $this->get_field_name( 'title-end' ); ?>" value="<?php echo $instance['markup']['title-end']; ?>" class="widefat" style="width:80px!important" <?php echo ($instance['markup']['custom_html']) ? '' : 'disabled="disabled"' ?> /></p>
                <p style="font-size:11px"><label for="<?php echo $this->get_field_id( 'wpp_start' ); ?>"><?php _e('Before / after Popular Posts:', 'wordpress-popular-posts'); ?></label> <br />
                <input type="text" id="<?php echo $this->get_field_id( 'wpp-start' ); ?>" name="<?php echo $this->get_field_name( 'wpp-start' ); ?>" value="<?php echo $instance['markup']['wpp-start']; ?>" class="widefat" style="width:80px!important" <?php echo ($instance['markup']['custom_html']) ? '' : 'disabled="disabled"' ?> /> <input type="text" id="<?php echo $this->get_field_id( 'wpp-end' ); ?>" name="<?php echo $this->get_field_name( 'wpp-end' ); ?>" value="<?php echo $instance['markup']['wpp-end']; ?>" class="widefat" style="width:80px!important" <?php echo ($instance['markup']['custom_html']) ? '' : 'disabled="disabled"' ?> /></p>
                <p style="font-size:11px"><label for="<?php echo $this->get_field_id( 'post-start' ); ?>"><?php _e('Before / after each post:', 'wordpress-popular-posts'); ?></label> <br />
                <input type="text" id="<?php echo $this->get_field_id( 'post-start' ); ?>" name="<?php echo $this->get_field_name( 'post-start' ); ?>" value="<?php echo $instance['markup']['post-start']; ?>" class="widefat" style="width:80px!important" <?php echo ($instance['markup']['custom_html']) ? '' : 'disabled="disabled"' ?> /> <input type="text" id="<?php echo $this->get_field_id( 'post-end' ); ?>" name="<?php echo $this->get_field_name( 'post-end' ); ?>" value="<?php echo $instance['markup']['post-end']; ?>" class="widefat" style="width:80px!important" <?php echo ($instance['markup']['custom_html']) ? '' : 'disabled="disabled"' ?> /></p>
                <hr />
                <?php endif; ?>
                <input type="checkbox" class="checkbox" <?php echo ($instance['markup']['pattern']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'pattern_active' ); ?>" name="<?php echo $this->get_field_name( 'pattern_active' ); ?>" /> <label for="<?php echo $this->get_field_id( 'pattern_active' ); ?>"><?php _e('Use content formatting tags', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
                <?php if ($instance['markup']['pattern']['active']) : ?>
                <br />
                <p style="font-size:11px"><label for="<?php echo $this->get_field_id( 'pattern_form' ); ?>"><?php _e('Content format:', 'wordpress-popular-posts'); ?></label>
                <input type="text" id="<?php echo $this->get_field_id( 'pattern_form' ); ?>" name="<?php echo $this->get_field_name( 'pattern_form' ); ?>" value="<?php echo htmlspecialchars($instance['markup']['pattern']['form'], ENT_QUOTES); ?>" style="width:204px" <?php echo ($instance['markup']['pattern']['active']) ? '' : 'disabled="disabled"' ?> /></p>
                <?php endif; ?>
            </fieldset>
            <?php
		}
		
		// RRR Added to get local time as per WP settings		
		function curdate() {			
			return gmdate( 'Y-m-d', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ));
		}
		
		function now() {
			return current_time('mysql');
		}
		
		// Function to calculate script execution time.
		function microtime_float() {
			list( $msec, $sec ) = explode( ' ', microtime() );
			$microtime = (float) $msec + (float) $sec;
			return $microtime;
		}
		
		// updates popular posts data table via ajax
		function wpp_ajax_update() {		
			$nonce = $_POST['token'];
			
			// is this a valid request?
			if (! wp_verify_nonce($nonce, 'wpp-token') ) die("Invalid token");
			
			if (is_numeric($_POST['id']) && (intval($_POST['id']) == floatval($_POST['id'])) && ($_POST['id'] != '')) {
				$id = $_POST['id'];
			} else {
				die("Invalid ID");
			}
			
			// if we got an ID, let's update the data table						
			global $wpdb;
			
			$wpdb->show_errors();
			
			$table = $wpdb->prefix . 'popularpostsdata';			
			$exec_time = 0;
			
			
			// update popularpostsdata table
			$start = $this->microtime_float();
			$result = $wpdb->query("INSERT INTO {$table} (postid, day, last_viewed) VALUES ({$id}, '{$this->now()}', '{$this->now()}') ON DUPLICATE KEY UPDATE last_viewed = '{$this->now()}', pageviews = pageviews + 1;");
			$end = $this->microtime_float();
			
			$exec_time += round($end - $start, 6);			
						
			// update popularpostsdatacache table
			$start = $this->microtime_float();
			
			$result2 = $wpdb->query("INSERT INTO {$table}cache (id, day, day_no_time) VALUES ({$id}, '{$this->now()}', '{$this->curdate()}') ON DUPLICATE KEY UPDATE pageviews = pageviews + 1, day = '{$this->now()}', day_no_time = '{$this->curdate()}';");
			
			$exec_time += round($end - $start, 6);
			
			if ($result && $result2) {
				die("OK. Execution time: " . $exec_time . " seconds");
			} else {
				die($wpdb->print_error);
			}
		}
		
		// Updates popular posts data table on post/page view
		// Since 2.3.0
		function wpp_update($content) {
			if ( (is_single() || is_page()) && !is_user_logged_in() && !is_front_page() ) {
				
				global $wpdb, $wp_query;
				
				$wpdb->show_errors();
				
				$id = $wp_query->post->ID; // get post ID
				$table = $wpdb->prefix . 'popularpostsdata';
				
				// update popularpostsdata table
				
				
				$result = $wpdb->query("INSERT INTO {$table} (postid, day, last_viewed) VALUES ({$id}, '{$this->now()}', '{$this->now()}') ON DUPLICATE KEY UPDATE last_viewed = '{$this->now()}', pageviews = pageviews + 1;");
				
				$result2 = $wpdb->query("INSERT INTO {$table}cache (id, day, day_no_time) VALUES ({$id}, '{$this->now()}', '{$this->curdate()}') ON DUPLICATE KEY UPDATE pageviews = pageviews + 1, day = '{$this->now()}', day_no_time = '{$this->curdate()}';");
				
				
				echo "INSERT INTO {$table} (postid, day, last_viewed) VALUES ({$id}, '{$this->now()}', '{$this->now()}') ON DUPLICATE KEY UPDATE last_viewed = '{$this->now()}', pageviews = pageviews + 1;" . "<br />";
				
				echo "INSERT INTO {$table}cache (id, day, day_no_time) VALUES ({$id}, '{$this->now()}', '{$this->curdate()}') ON DUPLICATE KEY UPDATE pageviews = pageviews + 1, day = '{$this->now()}', day_no_time = '{$this->curdate()}';" . "<br />";
				
				if (!$result || !$result2) {
					die($wpdb->print_error);
				}
				
			}
			
			return $content;
		}
		
		// clears Wordpress Popular Posts' data
		function wpp_clear_data() {
			$token = $_POST['token'];
			$clear = isset($_POST['clear']) ? $_POST['clear'] : '';
			$key = get_option("wpp_rand");
			
			if (current_user_can('manage_options') && ($token === $key) && !empty($clear)) {
				global $wpdb;
				// set table name
				$table = $wpdb->prefix . "popularpostsdata";
				$cache = $wpdb->prefix . "popularpostsdatacache";
				
				if ($clear == 'cache') {
					if ( $wpdb->get_var("SHOW TABLES LIKE '$cache'") == $cache ) {
						$wpdb->query("TRUNCATE TABLE $cache;");
						_e('Success! The cache table has been cleared!', 'wordpress-popular-posts');
					} else {
						_e('Error: cache table does not exist.', 'wordpress-popular-posts');
					}
				} else if ($clear == 'all') {
					if ( $wpdb->get_var("SHOW TABLES LIKE '$table'") == $table && $wpdb->get_var("SHOW TABLES LIKE '$cache'") == $cache ) {
						$wpdb->query("TRUNCATE TABLE $table;");
						$wpdb->query("TRUNCATE TABLE $cache;");
						_e('Success! All data have been cleared!', 'wordpress-popular-posts');
					} else {
						_e('Error: one or both data tables are missing.', 'wordpress-popular-posts');
					}
				} else {
					_e('Invalid action.', 'wordpress-popular-posts');
				}
			} else {
				_e('Sorry, you do not have enough permissions to do this. Please contact the site administrator for support.', 'wordpress-popular-posts');
			}
			
			die();
		}
		
		// database install
		function wpp_install() {
			global $wpdb;
			
			$wpdb->show_errors();
			
			$sql = "";
			$charset_collate = "";
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
			if ( ! empty($wpdb->charset) ) $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) ) $charset_collate .= " COLLATE $wpdb->collate";
			
			// set table name
			$table = $wpdb->prefix . "popularpostsdata";
			
			// does popularpostsdata table exists?
			if ( $wpdb->get_var("SHOW TABLES LIKE '$table'") != $table ) { // fresh setup
				$sql = "CREATE TABLE " . $table . " ( UNIQUE KEY id (postid), postid int(10) NOT NULL, day datetime NOT NULL default '0000-00-00 00:00:00', last_viewed datetime NOT NULL default '0000-00-00 00:00:00', pageviews int(10) default 1 ) $charset_collate; CREATE TABLE " . $table ."cache ( UNIQUE KEY compositeID (id, day_no_time), id int(10) NOT NULL, day datetime NOT NULL default '0000-00-00 00:00:00', day_no_time date NOT NULL default '0000-00-00', pageviews int(10) default 1 ) $charset_collate;";
			} else {
				
				// check if cahe table is missing
				$cache = $table . "cache";
				
				if ( $wpdb->get_var("SHOW TABLES LIKE '$cache'") != $cache ) {									
					$sql = "CREATE TABLE $cache ( UNIQUE KEY compositeID (id, day_no_time), id int(10) NOT NULL, day datetime NOT NULL default '0000-00-00 00:00:00', day_no_time date NOT NULL default '0000-00-00', pageviews int(10) default 1 ) $charset_collate;";
				} else { // check if any column is missing					
					
					// get table columns
					$cacheFields = $wpdb->get_results("SHOW FIELDS FROM $cache", ARRAY_A);
					
					$alter_day = true;
					$add_daynotime = true;
					
					foreach ($cacheFields as $column) {
						// check if day column is type datetime
						if ($column['Field'] == 'day') {
							if ($column['Type'] == 'datetime') {
								$alter_day = false;
							}
						}
						
						// check if day_no_time field exists
						if ($column['Field'] == 'day_no_time') {
							$add_daynotime = false;
						}
					}
					
					if ($alter_day) { // day column is not datimetime, so change it
						$wpdb->query("ALTER TABLE $cache CHANGE day day datetime NOT NULL default '0000-00-00 00:00:00';");
					}
					
					if ($add_daynotime) { // day_no_time column is missing, add it
						$wpdb->query("ALTER TABLE $cache ADD day_no_time date NOT NULL default '0000-00-00';");
						$wpdb->query("UPDATE $cache SET day_no_time = day;");
					}
					
					$cacheIndex = $wpdb->get_results("SHOW INDEX FROM $cache", ARRAY_A);					
					if ($cacheIndex[0]['Key_name'] == "id") { // if index is id-day change to id-day_no_time
						$wpdb->query("ALTER TABLE $cache DROP INDEX id, ADD UNIQUE KEY compositeID (id, day_no_time);");
					}
					
					/*
					ALTER TABLE wp_popularpostsdatacache DROP INDEX id, ADD UNIQUE KEY compositeID (id, day_no_time);
					*/

				}
			}
			
			dbDelta($sql);
		}
		
		// Checks for stuff that needs updating on plugin reactivation
		// Since 2.3.1
		function wpp_upgrade() {
			
			global $wpdb;
			
			$wpdb->show_errors();
			
			$sql = "";
			$charset_collate = "";
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
			if ( ! empty($wpdb->charset) ) $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) ) $charset_collate .= " COLLATE $wpdb->collate";
			
			// set table name
			$data = $wpdb->prefix . "popularpostsdata";
			$cache = $data . "cache";
			
			// if the cache table is missing, create it
			if ( $wpdb->get_var("SHOW TABLES LIKE '$cache'") != $cache ) {									
				$sql = "CREATE TABLE $cache ( UNIQUE KEY compositeID (id, day_no_time), id int(10) NOT NULL, day datetime NOT NULL default '0000-00-00 00:00:00', day_no_time date NOT NULL default '0000-00-00', pageviews int(10) default 1 ) $charset_collate;";
			} else { // check if any column is missing					
				
				// get table columns
				$cacheFields = $wpdb->get_results("SHOW FIELDS FROM $cache", ARRAY_A);
				
				$alter_day = true;
				$add_daynotime = true;
				
				foreach ($cacheFields as $column) {
					// check if day column is type datetime
					if ($column['Field'] == 'day') {
						if ($column['Type'] == 'datetime') {
							$alter_day = false;
						}
					}
					
					// check if day_no_time field exists
					if ($column['Field'] == 'day_no_time') {
						$add_daynotime = false;
					}
				}
				
				if ($alter_day) { // day column is not datimetime, so change it
					$wpdb->query("ALTER TABLE $cache CHANGE day day datetime NOT NULL default '0000-00-00 00:00:00';");
				}
				
				if ($add_daynotime) { // day_no_time column is missing, add it
					$wpdb->query("ALTER TABLE $cache ADD day_no_time date NOT NULL default '0000-00-00';");
					$wpdb->query("UPDATE $cache SET day_no_time = day;");
				}
				
				$cacheIndex = $wpdb->get_results("SHOW INDEX FROM $cache", ARRAY_A);					
				if ($cacheIndex[0]['Key_name'] == "id") { // if index is id-day change to id-day_no_time
					$wpdb->query("ALTER TABLE $cache DROP INDEX id, ADD UNIQUE KEY compositeID (id, day_no_time);");
				}
			}
			
			dbDelta($sql);
			
		}
		
		// prints ajax script to theme's header
		function wpp_print_ajax() {			
			// if we're on a page or post, load the script
			if ( (is_single() || is_page()) && !is_user_logged_in() && !is_front_page() ) {			
				// let's add jQuery
				wp_print_scripts('jquery');
					
				// create security token
				$nonce = wp_create_nonce('wpp-token');
				
				// get current post's ID
				global $wp_query;
				wp_reset_query();
			
				$id = $wp_query->post->ID;
			?>
<!-- Wordpress Popular Posts v<?php echo $this->version; ?> -->
<script type="text/javascript">
    /* <![CDATA[ */				
	jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {action: 'wpp_update', token: '<?php echo $nonce; ?>', id: <?php echo $id; ?>}, function(data){/*alert(data);*/});
    /* ]]> */
</script>
<!-- End Wordpress Popular Posts v<?php echo $this->version; ?> -->
            <?php
			}
		}		
		
		// prints popular posts
		function get_popular_posts($instance, $return = false) {
			
			// set default values			
			$defaults = array(
				'title' => __('Popular Posts', 'wordpress-popular-posts'),
				'limit' => 10,
				'range' => 'daily',
				'order_by' => 'comments',
				'post_type' => 'post,page',
				'author' => '',
				'cat' => '',
				'shorten_title' => array(
					'active' => false,
					'length' => 25,
					'keep_format' => false
				),
				'post-excerpt' => array(
					'active' => false,
					'length' => 55
				),				
				'thumbnail' => array(
					'active' => false,
					'width' => 15,
					'height' => 15
				),
				'rating' => false,
				'stats_tag' => array(
					'comment_count' => true,
					'views' => false,
					'author' => false,
					'date' => array(
						'active' => false,
						'format' => 'F j, Y'
					)
				),
				'markup' => array(
					'custom_html' => false,
					'wpp-start' => '&lt;ul&gt;',
					'wpp-end' => '&lt;/ul&gt;',
					'post-start' => '&lt;li&gt;',
					'post-end' => '&lt;/li&gt;',
					'title-start' => '&lt;h2&gt;',
					'title-end' => '&lt;/h2&gt;',
					'pattern' => array(
						'active' => false,
						'form' => '{image} {title}: {summary} {stats}'
					)
				)
			);
			
			// update instance's default options
			$instance = wp_parse_args( (array) $instance, $defaults );
			
			global $wpdb;
			$table = $wpdb->prefix . "popularpostsdata";
			
			$fields = "";
			$join = "";
			$where = "";
			$having = "";
			$orderby = "";
			$cat = (is_category()) ? get_query_var('cat') : '';
			$content = "";
			
			if ($instance['range'] == "all") { // data - all
			
				// views
				if ($instance['order_by'] == "views" || $instance['order_by'] == "avg" || $instance['stats_tag']['views']) {
					$join .= " LEFT JOIN {$table} v ON p.ID = v.postid ";
					
					if ( $instance['order_by'] == "avg" ) {
						$fields .= ", ( IFNULL(v.pageviews, 0)/(IF ( DATEDIFF('".$this->now()."', MIN(v.day)) > 0, DATEDIFF('".$this->now()."', MIN(v.day)), 1) )) AS 'avg_views' ";
					} else {						
						$fields .= ", IFNULL(v.pageviews, 0) AS 'pageviews' ";							
					}
				}
				
				// comments
				if ($instance['order_by'] == "comments" || $instance['stats_tag']['comment_count']) {
					$fields .= ", p.comment_count AS 'comment_count' ";
				}
				
			} else if ($instance['range'] == "yesterday" || $instance['range'] == "daily") { // data - last 24 hours
				
				// views				
				if ($instance['order_by'] == "views" || $instance['order_by'] == "avg" || $instance['stats_tag']['views']) {
					$join .= " LEFT JOIN (SELECT id, SUM(pageviews) AS 'pageviews', day FROM (SELECT id, pageviews, day FROM {$table}cache WHERE day > DATE_SUB('".$this->now()."', INTERVAL 1 DAY) ORDER BY day) sv GROUP BY id) v ON p.ID = v.id ";
					
					$fields .= ", IFNULL(v.pageviews, 0) AS 'pageviews' ";
				}
				
				// comments
				if ($instance['order_by'] == "comments" || $instance['stats_tag']['comment_count']) {
					$fields .= ", IFNULL(c.comment_count, 0) AS 'comment_count' ";
					$join .= " LEFT JOIN (SELECT comment_post_ID, COUNT(comment_post_ID) AS 'comment_count' FROM $wpdb->comments WHERE comment_approved = 1 AND comment_date > DATE_SUB('".$this->now()."', INTERVAL 1 DAY) GROUP BY comment_post_ID ORDER BY comment_date DESC) c ON p.ID = c.comment_post_ID ";
				}
				
			} else if ($instance['range'] == "weekly") { // data - last 7 days
				
				// views				
				if ($instance['order_by'] == "views" || $instance['order_by'] == "avg" || $instance['stats_tag']['views']) {
					$join .= " LEFT JOIN (SELECT id, SUM(pageviews) AS 'pageviews', day FROM (SELECT id, pageviews, day FROM {$table}cache WHERE day > DATE_SUB('".$this->now()."', INTERVAL 1 WEEK) ORDER BY day) sv GROUP BY id) v ON p.ID = v.id ";
										
					if ( $instance['order_by'] == "avg" ) {
						$fields .= ", ( IFNULL(v.pageviews, 0)/(IF ( DATEDIFF('".$this->now()."', MIN(v.day)) > 0, DATEDIFF('".$this->now()."', MIN(v.day)), 1) )) AS 'avg_views' ";
					} else {						
						$fields .= ", IFNULL(v.pageviews, 0) AS 'pageviews' ";							
					}					
				}
				
				// comments
				if ($instance['order_by'] == "comments" || $instance['stats_tag']['comment_count']) {
					$fields .= ", IFNULL(c.comment_count, 0) AS 'comment_count' ";
					$join .= " LEFT JOIN (SELECT comment_post_ID, COUNT(comment_post_ID) AS 'comment_count' FROM $wpdb->comments WHERE comment_approved = 1 AND comment_date > DATE_SUB('".$this->now()."', INTERVAL 1 WEEK) GROUP BY comment_post_ID ORDER BY comment_date DESC) c ON p.ID = c.comment_post_ID ";
				}
				
			} else if ($instance['range'] == "monthly") { // data - last 30 days
				
				// views				
				if ($instance['order_by'] == "views" || $instance['order_by'] == "avg" || $instance['stats_tag']['views']) {
					$join .= " LEFT JOIN (SELECT id, SUM(pageviews) AS 'pageviews', day FROM (SELECT id, pageviews, day FROM {$table}cache WHERE day > DATE_SUB('".$this->now()."', INTERVAL 1 MONTH) ORDER BY day) sv GROUP BY id) v ON p.ID = v.id ";
										
					if ( $instance['order_by'] == "avg" ) {
						$fields .= ", ( IFNULL(v.pageviews, 0)/(IF ( DATEDIFF('".$this->now()."', MIN(v.day)) > 0, DATEDIFF('".$this->now()."', MIN(v.day)), 1) )) AS 'avg_views' ";
					} else {						
						$fields .= ", IFNULL(v.pageviews, 0) AS 'pageviews' ";							
					}					
				}
				
				// comments
				if ($instance['order_by'] == "comments" || $instance['stats_tag']['comment_count']) {
					$fields .= ", IFNULL(c.comment_count, 0) AS 'comment_count' ";
					$join .= " LEFT JOIN (SELECT comment_post_ID, COUNT(comment_post_ID) AS 'comment_count' FROM $wpdb->comments WHERE comment_approved = 1 AND comment_date > DATE_SUB('".$this->now()."', INTERVAL 1 MONTH) GROUP BY comment_post_ID ORDER BY comment_date DESC) c ON p.ID = c.comment_post_ID ";
				}
				
			}
			
			// sorting options
			switch( $instance['order_by'] ) {
				case 'comments':
					if ($instance['range'] == "all") {
						$where .= " AND p.comment_count > 0 ";
						$orderby = 'p.comment_count';
					} else {
						$where .= " AND c.comment_count > 0 ";
						$orderby = 'c.comment_count';
					}
					break;
					
				case 'views':
					$where .= " AND v.pageviews > 0 ";
					$orderby = 'v.pageviews';
					break;
					
				case 'avg':
					if ($instance['range'] == "yesterday" || $instance['range'] == "daily") {
						$where .= " AND v.pageviews > 0 ";
						$orderby = 'v.pageviews';
					} else {
						$having = " HAVING avg_views > 0.0000 ";
						$orderby = 'avg_views';
					}
					
					break;
					
				default:
					$orderby = 'comment_count';					
					break;
			}
			
			// post filters			
			// * post types - based on code seen at https://github.com/williamsba/WordPress-Popular-Posts-with-Custom-Post-Type-Support
			$post_types = explode(",", $instance['post_type']);
			$i = 0;
			$len = count($post_types);
			$sql_post_types = "";
					
			if ($len > 1) { // we are getting posts from more that one ctp				
				foreach ( $post_types as $post_type ) {
					$sql_post_types .= "'" .$post_type. "'";
					
					if ($i != $len - 1) $sql_post_types .= ",";
					
					$i++;
				}

				$where .= " AND p.post_type IN({$sql_post_types}) ";
			} else if ($len == 1) { // post from one ctp only
				$where .= " AND p.post_type = '".$instance['post_type']."' ";
			}
			
			// * categories
			if ( !empty($instance['cat']) ) {
				$cat_ids = explode(",", $instance['cat']);
				$in = array();
				$out = array();
				$not_in = "";
				
				usort($cat_ids, array(&$this, 'sorter'));					
				
				for ($i=0; $i < count($cat_ids); $i++) {
					if ($cat_ids[$i] >= 0) $in[] = $cat_ids[$i];
					if ($cat_ids[$i] < 0) $out[] = $cat_ids[$i];
				}
				
				$in_cats = implode(",", $in);
				$out_cats = implode(",", $out);
				$out_cats = preg_replace( '|[^0-9,]|', '', $out_cats );
				
				if ($in_cats != "" && $out_cats == "") { // get posts from from given cats only
					$where .= " AND p.ID IN (
						SELECT object_id
						FROM $wpdb->term_relationships AS r
							 JOIN $wpdb->term_taxonomy AS x ON x.term_taxonomy_id = r.term_taxonomy_id
							 JOIN $wpdb->terms AS t ON t.term_id = x.term_id
						WHERE x.taxonomy = 'category' AND t.term_id IN($in_cats)
						) ";
				} else if ($in_cats == "" && $out_cats != "") { // exclude posts from given cats only
					$where .= " AND p.ID NOT IN (
						SELECT object_id
						FROM $wpdb->term_relationships AS r
							 JOIN $wpdb->term_taxonomy AS x ON x.term_taxonomy_id = r.term_taxonomy_id
							 JOIN $wpdb->terms AS t ON t.term_id = x.term_id
						WHERE x.taxonomy = 'category' AND t.term_id IN($out_cats)
						) ";
				} else { // mixed, and possibly a heavy load on the DB
					$where .= " AND p.ID IN (
						SELECT object_id
						FROM $wpdb->term_relationships AS r
							 JOIN $wpdb->term_taxonomy AS x ON x.term_taxonomy_id = r.term_taxonomy_id
							 JOIN $wpdb->terms AS t ON t.term_id = x.term_id
						WHERE x.taxonomy = 'category' AND t.term_id IN($in_cats) 
						) AND p.ID NOT IN (
						SELECT object_id
						FROM $wpdb->term_relationships AS r
							 JOIN $wpdb->term_taxonomy AS x ON x.term_taxonomy_id = r.term_taxonomy_id
							 JOIN $wpdb->terms AS t ON t.term_id = x.term_id
						WHERE x.taxonomy = 'category' AND t.term_id IN($out_cats)
						) ";
				}
			}
			
			// * authors
			if ( !empty($instance['author']) ) {			
				$authors = explode(",", $instance['author']);			
				$len = count($authors);
				
				if ($len > 1) { // we are getting posts from more that one author
					$where .= " AND p.post_author IN(".$instance['author'].") ";
				} else if ($len == 1) { // post from one author only
					$where .= " AND p.post_author = '".$instance['author']."' ";
				}
			}
			
			$query = "SELECT p.ID AS 'id', p.post_title AS 'title', p.post_date AS 'date', p.post_author AS 'uid' {$fields} FROM {$wpdb->posts} p {$join} WHERE p.post_status = 'publish' AND p.post_password = '' {$where} GROUP BY p.ID {$having} ORDER BY {$orderby} DESC LIMIT " . $instance['limit'] . ";";
			
			//echo $query;
			//return $content;
						
			$mostpopular = $wpdb->get_results($query);
			
			if ( !is_array($mostpopular) || empty($mostpopular) ) { // no posts to show
				$content .= "<p>".__('Sorry. No data so far.', 'wordpress-popular-posts')."</p>"."\n";
			} else { // list posts
				
				// THUMBNAIL SOURCE
				$user_def_settings = array(
					'stats' => array(
						'order_by' => 'comments',
						'limit' => 10
					),
					'tools' => array(
						'ajax' => false,
						'css' => true,
						'stylesheet' => true,
						'thumbnail' => array(
							'source' => 'featured',
							'field' => ''
						)
					)
				);
				
				$this->user_ops = get_option('wpp_settings_config');
				
				if (!$this->user_ops || empty($this->user_ops)) {
					add_option('wpp_settings_config', $user_def_settings);
					$this->user_ops = $user_def_settings;
				}
				
				// HTML wrapper
				if ($instance['markup']['custom_html']) {
					$content .= htmlspecialchars_decode($instance['markup']['wpp-start'], ENT_QUOTES) ."\n";
				} else {
					$content .= "<!-- Wordpress Popular Posts Plugin v". $this->version ." [Widget] [".$instance['range']."]". (($instance['markup']['custom_html']) ? ' [custom]' : ' [regular]') ." -->"."\n";
					$content .= "<ul>" . "\n";
				}
				
				// posts array
				$posts_data = array();
			
				foreach($mostpopular as $p) {
					$stats = "";
					$thumb = "";
					$title = "";
					$title_sub = "";
					$permalink = get_permalink( $p->id );
					$author = ($instance['stats_tag']['author']) ? get_the_author_meta('display_name', $p->uid) : "";
					$date = date_i18n( $instance['stats_tag']['date']['format'], strtotime($p->date) );					
					$pageviews = ($instance['order_by'] == "views" || $instance['order_by'] == "avg" || $instance['stats_tag']['views']) ? (($instance['order_by'] == "views" || $instance['order_by'] == "comments") ? number_format($p->pageviews) : ( ($instance['range'] == "yesterday" || $instance['range'] == "daily") ? number_format($p->pageviews) : number_format($p->avg_views, 2)) ) : 0;
					$comments = ($instance['order_by'] == "comments" || $instance['stats_tag']['comment_count']) ? $p->comment_count : 0;
					$excerpt = "";
					$rating = "";
					$data = array();
					
					// TITLE
					$title = ($this->qTrans) ? qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($p->title) : $p->title;
					$title = strip_tags($title);
					$title_sub = strip_tags($title);
					
					if ( $instance['shorten_title']['active'] && (strlen($title) > $instance['shorten_title']['length'])) {
						$title_sub = mb_substr($title, 0, $instance['shorten_title']['length'], $this->charset) . "...";
					}
					
					$title = apply_filters('the_title', $title);
					$title_sub = apply_filters('the_title', $title_sub);
					
					// EXCERPT					
					if ( $instance['post-excerpt']['active'] ) {
						if ($instance['markup']['pattern']['active']) {
							$excerpt = $this->get_summary($p->id, $instance);
						} else {
							$excerpt = ": <span class=\"wpp-excerpt\">" . $this->get_summary($p->id, $instance) . "...</span>";
						}
					}
					
					// STATS
					// comments
					if ( $instance['stats_tag']['comment_count'] ) {						
						$stats .= "<span class=\"wpp-comments\">{$comments} " . __('comment(s)', 'wordpress-popular-posts') . "</span>";
					}
					// views
					if ( $instance['stats_tag']['views'] ) {
						$views_text = ' ' . __('view(s)', 'wordpress-popular-posts');							
						
						if ($instance['order_by'] == 'avg') {
							if ($instance['range'] != 'daily') $views_text = ' ' . __('view(s) per day', 'wordpress-popular-posts');
						}
						
						$stats .= ($stats == "") ? "<span class=\"wpp-views\">{$pageviews} {$views_text}</span>" : " | <span class=\"wpp-views\">{$pageviews} {$views_text}</span>";
					}
					//author
					if ( $instance['stats_tag']['author'] ) {
						$display_name = get_the_author_meta('display_name', $p->uid);
						$stats .= ($stats == "") ? "<span class=\"wpp-author\">" . __('by', 'wordpress-popular-posts')." {$display_name}</span>" : " | <span class=\"wpp-author\">" . __('by', 'wordpress-popular-posts') ." {$display_name}</span>";
					}
					// date
					if ( $instance['stats_tag']['date']['active'] ) {						
						$stats .= ($stats == "") ? "<span class=\"wpp-date\">" . __('posted on', 'wordpress-popular-posts')." {$date}</span>" : " | <span class=\"wpp-date\">" . __('posted on', 'wordpress-popular-posts') ." {$date}</span>";
					}
					
					// POST THUMBNAIL
					if ($instance['thumbnail']['active'] && $this->thumb) {
						$tbWidth = $instance['thumbnail']['width'];
						$tbHeight = $instance['thumbnail']['height'];
						
						$thumb = "<a href=\"". $permalink ."\" class=\"wpp-thumbnail\" title=\"{$title}\">";
												
						if ($this->user_ops['tools']['thumbnail']['source'] == "featured") { // get Featured Image
							if (function_exists('has_post_thumbnail') && has_post_thumbnail( $p->id )) {
								$path = $this->get_img($p->id, "featured");
								
								if ($path) {
									$thumb .= "<img src=\"". $this->pluginDir ."/timthumb.php?src={$path}&amp;h={$tbHeight}&amp;w={$tbWidth}\" width=\"{$tbWidth}\" height=\"{$tbHeight}\" alt=\"{$title}\" border=\"0\" class=\"wpp-thumbnail wpp_fi\" />";
								} else {
									$thumb .= "<img src=\"". $this->default_thumbnail ."\" alt=\"{$title}\" border=\"0\" width=\"{$tbWidth}\" height=\"{$tbHeight}\" class=\"wpp-thumbnail wpp_fi_def\" />";
								}								
							} else {
								$thumb .= "<img src=\"". $this->default_thumbnail ."\" alt=\"{$title}\" border=\"0\" width=\"{$tbWidth}\" height=\"{$tbHeight}\" class=\"wpp-thumbnail wpp_fi_def\" />";
							}
						} else if ($this->user_ops['tools']['thumbnail']['source'] == "first_image") { // get first image on post
							$path = $this->get_img($p->id, "first_image");
								
							if ($path) {
								$thumb .= "<img src=\"". $this->pluginDir ."/timthumb.php?src={$path}&amp;h={$tbHeight}&amp;w={$tbWidth}\" width=\"{$tbWidth}\" height=\"{$tbHeight}\" alt=\"{$title}\" border=\"0\" class=\"wpp-thumbnail wpp_fp\" />";
							} else {
								$thumb .= "<img src=\"". $this->default_thumbnail ."\" alt=\"{$title}\" border=\"0\" width=\"{$tbWidth}\" height=\"{$tbHeight}\" class=\"wpp-thumbnail wpp_fp_def\" />";
							}
						} else if ($this->user_ops['tools']['thumbnail']['source'] == "custom_field") { // get image from custom field
							$path = get_post_meta($p->id, $this->user_ops['tools']['thumbnail']['field'], true);
							
							if ($path != "") {
								$thumb .= "<img src=\"{$path}\" width=\"{$tbWidth}\" height=\"{$tbHeight}\" alt=\"{$title}\" border=\"0\" class=\"wpp-thumbnail wpp_cf\" />";
							} else {
								$thumb .= "<img src=\"". $this->default_thumbnail ."\" alt=\"{$title}\" border=\"0\" width=\"{$tbWidth}\" height=\"{$tbHeight}\" class=\"wpp-thumbnail wpp_cf_def\" />";
							}
						}
						
						$thumb .= "</a>";
					}
					
					$data = array(
						'title' => '<a href="'.$permalink.'" title="'.$title.'">'.$title_sub.'</a>',
						'summary' => $excerpt,
						'stats' => $stats,
						'img' => $thumb,
						'id' => $p->id
					);
					
					array_push($posts_data, $data);
					
					// PUTTING IT ALL TOGETHER					
					if ($instance['markup']['custom_html']) { // build custom layout
						if ($instance['markup']['pattern']['active']) {
							$content .= htmlspecialchars_decode($instance['markup']['post-start'], ENT_QUOTES) . htmlspecialchars_decode($this->format_content($instance['markup']['pattern']['form'], $data, $instance['rating'])) . htmlspecialchars_decode($instance['markup']['post-end'], ENT_QUOTES) . "\n";
						} else {
							$content .= htmlspecialchars_decode($instance['markup']['post-start'], ENT_QUOTES) . "{$thumb}<a href=\"{$permalink}\" title=\"{$title}\" class=\"wpp-post-title\">{$title_sub}</a> {$excerpt}{$stats}{$rating}" . htmlspecialchars_decode($instance['markup']['post-end'], ENT_QUOTES) . "\n";
						}
					} else { // build regular layout
						$content .= "<li>{$thumb}<a href=\"{$permalink}\" title=\"{$title}\" class=\"wpp-post-title\">{$title_sub}</a> {$excerpt}<span class=\"post-stats\">{$stats}</span>{$rating}</li>" . "\n";
					}
				}
				
				//print_r($posts_data);
				
				// END HTML wrapper
				if ($instance['markup']['custom_html']) {
					$content .= htmlspecialchars_decode($instance['markup']['wpp-end'], ENT_QUOTES) ."\n";
				} else {
					$content .= "\n"."</ul>"."\n";
				}
			}
			
			//if ($echo) { echo "<noscript>" . $content . "</noscript>"; } else { return $content; }
			if ($return) { return $posts_data; } else { return $content; }
			
		}		
		
		// builds posts' excerpt
		function get_summary($id, $instance){
			if (!is_numeric($id)) return false;
			global $wpdb;			
			$excerpt = "";
			$result = "";
			
			$result = $wpdb->get_results("SELECT post_excerpt FROM $wpdb->posts WHERE ID = " . $id, ARRAY_A);
			
			if (empty($result[0]['post_excerpt'])) {
				// no custom excerpt defined, how lazy of you!				
				$result = $wpdb->get_results("SELECT post_content FROM $wpdb->posts WHERE ID = " . $id, ARRAY_A);
				$excerpt = preg_replace("/\[caption.*\[\/caption\]/", "", $result[0]['post_content']);				
			} else {
				// user has defined a custom excerpt, yay!
				$excerpt = preg_replace("/\[caption.*\[\/caption\]/", "", $result[0]['post_excerpt']);
			}					
			
			// RRR added call to the_content filters, allows qTranslate to hook in.
            if ($this->qTrans) $excerpt = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($excerpt);
			
			// remove flash objects
			$excerpt = preg_replace("/<object[0-9 a-z_?*=\":\-\/\.#\,\\n\\r\\t]+/smi", "", $excerpt);
			
			// remove shortcodes
			$excerpt = strip_shortcodes($excerpt);
			
			if ($instance['post-excerpt']['keep_format']) {
				$excerpt = strip_tags($excerpt, '<a><b><i><strong><em>');
			} else {
				$excerpt = strip_tags($excerpt);
			}
			
			if (strlen($excerpt) > $instance['post-excerpt']['length']) {
				$excerpt = $this->truncate($excerpt, $instance['post-excerpt']['length'], '', true, true);
			}
			
			return $excerpt;
		}
		
		// gets the first image of post / page
		function get_img($id = "", $source = "featured") {
			
			if ( empty($id) || !is_numeric($id)) return false;
			
			if ($source == "featured") {
				$thumbnail_id = get_post_thumbnail_id($id);
				
				if ($thumbnail_id) {
					$thumbnail = wp_get_attachment_image_src($thumbnail_id, 'full');
					
					if ($thumbnail) {
						return $thumbnail[0];
					}
				}			
				
				return false;
			} else if ($source == "first_image") {
				// get post attachments
				$attachments = get_children(array('post_parent' => $id, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'orderby' => 'menu_order'));
				
				// no image has been found
				if ( !$attachments ) return false;
				
				$image = array_shift($attachments);
				
				return $image->guid;				
			}
			
			
		}
		
		/*
		// gets the first image of post / page
		function get_img($id = "", $print = false) {
			if ( empty($id) || !is_numeric($id) ) return false;
			
			// get post attachments
			$attachments = get_children(array('post_parent' => $id, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'orderby' => 'menu_order'));
			
			// no image has been found
			if ( !$attachments ) return false;
			
			$image = array_shift($attachments);			
			return $image->guid;
		}
		*/
		
		// parses content structure defined by user
		function format_content ($string, $data = array(), $rating) {
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
				if (strtolower($matches[0][$i]) == "{image}" || strtolower($matches[0][$i]) == "{thumb}") {
					$params[$matches[0][$i]] = $data['img'];
					continue;
				}
				// WP-PostRatings check
				if ($rating) {
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
		
		// code seen at http://www.gsdesign.ro/blog/cut-html-string-without-breaking-the-tags/
		// Since 2.0.1
		/**
		 * Truncates text.
		 *
		 * Cuts a string to the length of $length and replaces the last characters
		 * with the ending if the text is longer than length.
		 *
		 * @param string  $text String to truncate.
		 * @param integer $length Length of returned string, including ellipsis.
		 * @param string  $ending Ending to be appended to the trimmed string.
		 * @param boolean $exact If false, $text will not be cut mid-word
		 * @param boolean $considerHtml If true, HTML tags would be handled correctly
		 * @return string Trimmed string.
		 */		
		function truncate($text, $length = 100, $ending = '...', $exact = true, $considerHtml = false) {
			if ($considerHtml) {
				// if the plain text is shorter than the maximum length, return the whole text
				if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
					return $text;
				}
				// splits all html-tags to scanable lines
				preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
				$total_length = strlen($ending);
				$open_tags = array();
				$truncate = '';
				foreach ($lines as $line_matchings) {
					// if there is any html-tag in this line, handle it and add it (uncounted) to the output
					if (!empty($line_matchings[1])) {
						// if it's an "empty element" with or without xhtml-conform closing slash (f.e. <br/>)
						if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
							// do nothing
						// if tag is a closing tag (f.e. </b>)
						} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
							// delete tag from $open_tags list
							$pos = array_search($tag_matchings[1], $open_tags);
							if ($pos !== false) {
								unset($open_tags[$pos]);
							}
						// if tag is an opening tag (f.e. <b>)
						} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
							// add tag to the beginning of $open_tags list
							array_unshift($open_tags, strtolower($tag_matchings[1]));
						}
						// add html-tag to $truncate'd text
						$truncate .= $line_matchings[1];
					}
					// calculate the length of the plain text part of the line; handle entities as one character
					$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
					if ($total_length+$content_length> $length) {
						// the number of characters which are left
						$left = $length - $total_length;
						$entities_length = 0;
						// search for html entities
						if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
							// calculate the real length of all entities in the legal range
							foreach ($entities[0] as $entity) {
								if ($entity[1]+1-$entities_length <= $left) {
									$left--;
									$entities_length += strlen($entity[0]);
								} else {
									// no more characters left
									break;
								}
							}
						}
						//$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
						$truncate .= mb_substr($line_matchings[2], 0, $left+$entities_length);
						// maximum length is reached, so get off the loop
						break;
					} else {
						$truncate .= $line_matchings[2];
						$total_length += $content_length;
					}
					// if the maximum length is reached, get off the loop
					if($total_length>= $length) {
						break;
					}
				}
			} else {
				if (strlen($text) <= $length) {
					return $text;
				} else {
					//$truncate = substr($text, 0, $length - strlen($ending));
					$truncate = mb_substr($text, 0, $length - strlen($ending));
				}
			}
			// if the words shouldn't be cut in the middle...
			if (!$exact) {
				// ...search the last occurance of a space...
				$spacepos = strrpos($truncate, ' ');
				if (isset($spacepos)) {
					// ...and cut the text in this position
					//$truncate = substr($truncate, 0, $spacepos);
					$truncate = mb_substr($truncate, 0, $spacepos);
				}
			}
			// add the defined ending to the text
			$truncate .= $ending;
			if($considerHtml) {
				// close all unclosed html-tags
				foreach ($open_tags as $tag) {
					$truncate .= '</' . $tag . '>';
				}				
			}
			return $truncate;
		}
		
		// plugin localization (Credits: Aleksey Timkov at@uadeveloper.com)
		function wpp_textdomain() {
			load_plugin_textdomain('wordpress-popular-posts', false, dirname(plugin_basename( __FILE__ )));
		}
		
		// insert Wordpress Popular Posts' stylesheet in theme's head section, just in case someone needs it		
		function wpp_print_stylesheet() {
			if (!is_admin()) {
				if ( @file_exists(TEMPLATEPATH.'/wpp.css') ) { // user stored a custom wpp.css on theme's directory, so use it
					$css_path = get_template_directory_uri() . "/wpp.css";
				} else { // no custom wpp.css, use plugin's instead
					$css_path = plugins_url('style/wpp.css', __FILE__);
				}
				
				wp_enqueue_style('wordpress-popular-posts', $css_path, false);
			}
		}
		
		// create Wordpress Popular Posts' admin page		
		function wpp_admin() {
			require (dirname(__FILE__) . '/admin.php');
		}	
		function add_wpp_admin() {			
			add_options_page('Wordpress Popular Posts', 'Wordpress Popular Posts', 'manage_options', 'wpp_admin', array(&$this, 'wpp_admin'));
		}
		
		// version update warning
		function wpp_update_warning() {
			$msg = '<div id="wpp-message" class="error fade"><p>'.__('Your Wordpress version is too old. Wordpress Popular Posts Plugin requires at least version 2.8 to function correctly. Please update your blog via Tools &gt; Upgrade.', 'wordpress-popular-posts').'</p></div>';
			echo trim($msg);
		}
		
		// cache maintenance
		function wpp_cache_maintenance() {
			global $wpdb;
			// RRR modified to use curdate & now functions above
			//$wpdb->query("DELETE FROM ".$wpdb->prefix."popularpostsdatacache WHERE day < DATE_SUB(CURDATE(), INTERVAL 30 DAY);");
			$wpdb->query("DELETE FROM ".$wpdb->prefix."popularpostsdatacache WHERE day < DATE_SUB('".$this->curdate()."', INTERVAL 30 DAY);");
		}
		
		// plugin deactivation
		function wpp_deactivation() {
			wp_clear_scheduled_hook('wpp_cache_event');
			remove_shortcode('wpp');
			remove_shortcode('WPP');
			
			//delete_option('wpp_ver');
		}
		
		// shortcode handler
		function wpp_shortcode($atts = NULL, $content = NULL) {
			
			extract( shortcode_atts( array(
				'header' => '',
				'limit' => 10,
				'range' => 'daily',
				'order_by' => 'comments',
				'post_type' => 'post,page',
				'cat' => '',
				'author' => '',
				'title_length' => 0,
				'excerpt_length' => 0,
				'excerpt_format' => 0,				
				'thumbnail_width' => 0,
				'thumbnail_height' => 0,
				'thumbnail_selection' => 'wppgenerated',
				'rating' => false,
				'stats_comments' => true,
				'stats_views' => false,
				'stats_author' => false,
				'stats_date' => false,
				'stats_date_format' => 'F j, Y',
				'wpp_start' => '<ul>',
				'wpp_end' => '</ul>',
				'post_start' => '<li>',
				'post_end' => '</li>',
				'header_start' => '<h2>',
				'header_end' => '</h2>',
				'do_pattern' => false,
				'pattern_form' => '{image} {title}: {summary} {stats}'
			), $atts ) );
			
			// possible values for "Time Range" and "Order by"
			$range_values = array("yesterday", "daily", "weekly", "monthly", "all");
			$order_by_values = array("comments", "views", "avg");
			$thumbnail_selector = array("wppgenerated", "usergenerated");			
			
			$shortcode_ops = array(
				'title' => strip_tags($header),
				'limit' => empty($limit) ? 10 : (is_numeric($limit)) ? (($limit > 0) ? $limit : 10) : 10,
				'range' => (in_array($range, $range_values)) ? $range : 'daily',
				'order_by' => (in_array($order_by, $order_by_values)) ? $order_by : 'comments',
				'post_type' => empty($post_type) ? 'post,page' : $post_type,
				'cat' => preg_replace( '|[^0-9,-]|', '', $cat ),
				'author' => preg_replace( '|[^0-9,]|', '', $author ),
				'shorten_title' => array(
					'active' => empty($title_length) ? false : (is_numeric($title_length)) ? (($title_length > 0) ? true : false) : false,
					'length' => empty($title_length) ? 0 : (is_numeric($title_length)) ? $title_length : 0 
				),
				'post-excerpt' => array(
					'active' => empty($excerpt_length) ? false : (is_numeric($excerpt_length)) ? (($excerpt_length > 0) ? true : false) : false,
					'length' => empty($excerpt_length) ? 0 : (is_numeric($excerpt_length)) ? $excerpt_length : 0,
					'keep_format' => empty($excerpt_format) ? false : (is_numeric($excerpt_format)) ? (($excerpt_format > 0) ? true : false) : false,
				),				
				'thumbnail' => array(
					'active' => empty($thumbnail_width) ? false : (is_numeric($thumbnail_width)) ? (($thumbnail_width > 0) ? true : false) : false,
					'thumb_selection' => 'usergenerated',
					'width' => empty($thumbnail_width) ? 0 : (is_numeric($thumbnail_width)) ? $thumbnail_width : 0,
					'height' => empty($thumbnail_height) ? 0 : (is_numeric($thumbnail_height)) ? $thumbnail_height : 0
				),
				'rating' => empty($rating) || $rating = "false" ? false : true,
				'stats_tag' => array(
					'comment_count' => empty($stats_comments) ? false : $stats_comments,
					'views' => empty($stats_views) ? false : $stats_views,
					'author' => empty($stats_author) ? false : $stats_author,
					'date' => array(
						'active' => empty($stats_date) ? false : $stats_date,
						'format' => empty($stats_date_format) ? 'F j, Y' : $stats_date_format
					)
				),
				'markup' => array(
					'custom_html' => true,
					'wpp-start' => empty($wpp_start) ? '<ul>' : $wpp_start,
					'wpp-end' => empty($wpp_end) ? '</ul>' : $wpp_end,
					'post-start' => empty($post_start) ? '<li>;' : $post_start,
					'post-end' => empty($post_end) ? '</li>' : $post_end,
					'title-start' => empty($header_start) ? '' : $header_start,
					'title-end' => empty($header_end) ? '' : $header_end,
					'pattern' => array(
						'active' => empty($do_pattern) ? false : (bool)$do_pattern,
						'form' => empty($pattern_form) ? '{image} {title}: {summary} {stats}' : $pattern_form
					)
				)
			);
			
			$shortcode_content = "<!-- Wordpress Popular Posts Plugin v". $this->version ." [SC] [".$shortcode_ops['range']."]". (($shortcode_ops['markup']['custom_html']) ? ' [custom]' : ' [regular]') ." -->"."\n";
				
			// is there a title defined by user?
			if (!empty($header) && !empty($header_start) && !empty($header_end)) {
				$shortcode_content .= $header_start . apply_filters('widget_title', $header) . $header_end;
			}
			
			// print popular posts list
			$shortcode_content .= $this->get_popular_posts($shortcode_ops);				
			$shortcode_content .= "<!-- End Wordpress Popular Posts Plugin v". $this->version ." -->"."\n";
			
			return $shortcode_content;
		}
		
		// stats page
		// Since 2.0.3
		function wpp_stats() {
			if ( function_exists('add_submenu_page') ) add_submenu_page('index.php', __('Wordpress Popular Posts Stats'), __('Wordpress Popular Posts Stats'), 'manage_options', 'wpp-stats-display', array(&$this, 'wpp_stats_display'));
		}
		
		function wpp_stats_display() {
			require (dirname(__FILE__) . '/stats.php');
        }
		
		// stats page
		// Since 2.3.0
		function sorter($a, $b) {
			if ($a > 0 && $b > 0) {
				return $a - $b;
			} else {
				return $b - $a;
			}
		}
	}
	
	// create tables
	//register_activation_hook('WordpressPopularPosts', 'wpp_install');
	register_activation_hook(__FILE__ , array('WordPressPopularPosts', 'wpp_install'));
}

/**
 * Wordpress Popular Posts template tags for use in themes.
 */

// gets views count
// Since 2.0.0
function wpp_get_views($id = NULL) {
	// have we got an id?
	if ( empty($id) || is_null($id) || !is_numeric($id) ) {
		return "-1";
	} else {		
		global $wpdb;
		
		$table_name = $wpdb->prefix . "popularpostsdata";		
		$result = $wpdb->get_results("SELECT pageviews FROM $table_name WHERE postid = '$id'", ARRAY_A);
		
		if ( !is_array($result) || empty($result) ) {
			return "0";
		} else {
			return $result[0]['pageviews'];
		}
	}
}

// gets popular posts
// Since 2.0.3
function wpp_get_mostpopular($args = NULL) {
	
	$shortcode = '[wpp';

	if ( is_null( $args ) ) {
		$shortcode .= ']';
	} else {
		if( is_array( $args ) ){
			$atts = '';
			foreach( $args as $key => $arg ){
				$atts .= ' ' . $key . '="' . $arg . '"';
			}
		} else {
			$atts = trim( str_replace( "&", " ", $args  ) );
		}
		
		$shortcode .= ' ' . $atts . ']';
	}
	
	echo do_shortcode( $shortcode );
}

// gets popular posts
/**
 * Deprecated in 2.0.3.
 * Use wpp_get_mostpopular instead.
 */
function get_mostpopular($args = NULL) {
	return wpp_get_mostpopular($args);
}


/**
 * Wordpress Popular Posts 2.3.2 Changelog.
 */

/*
= 2.3.2 =
* The ability is enabling / disabling the Ajax Update has been removed. It introduced a random bug that doubled the views count of some posts / pages. Will be added back when a fix is ready.
* Fixed a bug preventing the cat parameter from excluding categories (widget was not affected by this).
* FAQ section (Settings / Wordpress Popular Posts / FAQ) updated.
* Added french translation. (Thanks, Le Raconteur!)
*/