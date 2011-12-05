<?php
/*
Plugin Name: Wordpress Popular Posts
Plugin URI: http://wordpress.org/extend/plugins/wordpress-popular-posts
Description: Showcases your most popular posts to your visitors on your blog's sidebar. Use Wordpress Popular Posts as a widget or place it anywhere on your theme using  <strong>&lt;?php wpp_get_mostpopular(); ?&gt;</strong>
Version: 2.2.1
Author: H&eacute;ctor Cabrera
Author URI: http://wordpress.org/extend/plugins/wordpress-popular-posts
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
		var $version = "2.2.1";
		var $qTrans = false;
		var $postRating = false;
		var $thumb = false;		
		var $pluginDir = "";
		var $charset = "UTF-8";
		var $magicquotes = false;
		var $default_thumbnail = "";
		
		// constructor
		function WordpressPopularPosts() {
			global $wp_version;
				
			// widget settings
			$widget_ops = array( 'classname' => 'popular-posts', 'description' => 'The most Popular Posts on your blog.' );
	
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
			
			// print stylesheet
			add_action('wp_head', array(&$this, 'wpp_print_stylesheet'));
			
			// add ajax update to wp_ajax_ hook
			add_action('wp_ajax_nopriv_wpp_update', array(&$this, 'wpp_ajax_update'));
			add_action('wp_head', array(&$this, 'wpp_print_ajax'));
			
			// add ajax table truncation to wp_ajax_ hook
			add_action('wp_ajax_wpp_clear_cache', array(&$this, 'wpp_clear_data'));
			add_action('wp_ajax_wpp_clear_all', array(&$this, 'wpp_clear_data'));
			
			// activate textdomain for translations
			add_action('init', array(&$this, 'wpp_textdomain'));
			
			// activate maintenance page
			add_action('admin_menu', array(&$this, 'add_wpp_maintenance_page'));
			
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
			
			// set version
			$wpp_ver = get_option('wpp_ver');
			if (!$wpp_ver) {
				add_option('wpp_ver', $this->version);
			} else if (version_compare($wpp_ver, $this->version, '<')) {
				update_option('wpp_ver', $this->version);
			}
			
			// add stats page
			add_action('admin_menu', array(&$this, 'wpp_stats'));
		}

		// builds Wordpress Popular Posts' widgets
		function widget($args, $instance) {
			extract($args);
			echo "<!-- Wordpress Popular Posts Plugin v". $this->version ." [W] [".$instance['range']."]". (($instance['markup']['custom_html']) ? ' [custom]' : ' [regular]') ." -->"."\n";
			echo $before_widget . "\n";
			
			// has user set a title?
			if ($instance['title'] != '') {
				if ($instance['markup']['custom_html'] && $instance['markup']['title-start'] != "" && $instance['markup']['title-end'] != "" ) {
					echo htmlspecialchars_decode($instance['markup']['title-start'], ENT_QUOTES) . htmlspecialchars_decode($instance['title'], ENT_QUOTES) . htmlspecialchars_decode($instance['markup']['title-end'], ENT_QUOTES);
				} else {
					echo $before_title . htmlspecialchars_decode($instance['title'], ENT_QUOTES) . $after_title;
				}
			}
			
			echo $this->get_popular_posts($instance, false);			
			echo $after_widget . "\n";
			echo "<!-- End Wordpress Popular Posts Plugin v". $this->version ." -->"."\n";
		}

		// updates each widget instance when user clicks the "save" button
		function update($new_instance, $old_instance) {
			
			$instance = $old_instance;
			
			//$instance['title'] = htmlspecialchars( stripslashes(strip_tags( $new_instance['title'] )), ENT_QUOTES, 'UTF-8', FALSE );
			$instance['title'] = ($this->magicquotes) ? htmlspecialchars( stripslashes(strip_tags( $new_instance['title'] )), ENT_QUOTES ) : htmlspecialchars( strip_tags( $new_instance['title'] ), ENT_QUOTES );
			$instance['limit'] = is_numeric($new_instance['limit']) ? $new_instance['limit'] : 10;
			$instance['range'] = $new_instance['range'];
			$instance['order_by'] = $new_instance['order_by'];
			$instance['pages'] = $new_instance['pages'];
			$instance['shorten_title']['active'] = $new_instance['shorten_title-active'];
			$instance['shorten_title']['length'] = is_numeric($new_instance['shorten_title-length']) ? $new_instance['shorten_title-length'] : 25;
			$instance['post-excerpt']['active'] = $new_instance['post-excerpt-active'];
			$instance['post-excerpt']['length'] = is_numeric($new_instance['post-excerpt-length']) ? $new_instance['post-excerpt-length'] : 55;
			$instance['post-excerpt']['keep_format'] = $new_instance['post-excerpt-format'];
			$instance['exclude-cats']['active'] = $new_instance['exclude-cats'];
			$instance['exclude-cats']['cats'] = empty($new_instance['excluded']) ? '' : (ctype_digit(str_replace(",", "", $new_instance['excluded']))) ? $new_instance['excluded'] : '';
			$instance['thumbnail']['thumb_selection'] = "usergenerated";
			
			if ($this->thumb) { // can create thumbnails
				$instance['thumbnail']['active'] = $new_instance['thumbnail-active'];				
				$instance['thumbnail']['width'] = is_numeric($new_instance['thumbnail-width']) ? $new_instance['thumbnail-width'] : 15;
				$instance['thumbnail']['height'] = is_numeric($new_instance['thumbnail-height']) ? $new_instance['thumbnail-height'] : 15;
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
			$instance['markup']['pattern']['form'] = empty($new_instance['pattern_form']) ? '{image} {title}: {summary} {stats}' : strip_tags( $new_instance['pattern_form'] );
	
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
				'pages' => true,
				'shorten_title' => array(
					'active' => false,
					'length' => 25,
					'keep_format' => false
				),
				'post-excerpt' => array(
					'active' => false,
					'length' => 55
				),
				'exclude-cats' => array(
					'active' => false,
					'cats' => ''
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
                        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'wordpress-popular-posts'); ?></label>
            <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" /></p>
            <p><label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e('Show up to:', 'wordpress-popular-posts'); ?></label><br />
            <input id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" value="<?php echo $instance['limit']; ?>"  class="widefat" style="width:50px!important" /> <?php _e('posts', 'wordpress-popular-posts'); ?></p>
            <p><label for="<?php echo $this->get_field_id( 'range' ); ?>"><?php _e('Time Range:', 'wordpress-popular-posts'); ?></label>
            <select id="<?php echo $this->get_field_id( 'range' ); ?>" name="<?php echo $this->get_field_name( 'range' ); ?>" class="widefat">
            	<option value="daily" <?php if ( 'daily' == $instance['range'] ) echo 'selected="selected"'; ?>><?php _e('Last 24 hours', 'wordpress-popular-posts'); ?></option>
                <option value="weekly" <?php if ( 'weekly' == $instance['range'] ) echo 'selected="selected"'; ?>><?php _e('Last 7 days', 'wordpress-popular-posts'); ?></option>
                <option value="monthly" <?php if ( 'monthly' == $instance['range'] ) echo 'selected="selected"'; ?>><?php _e('Last 30 days', 'wordpress-popular-posts'); ?></option>
                <option value="all" <?php if ( 'all' == $instance['range'] ) echo 'selected="selected"'; ?>><?php _e('All-time', 'wordpress-popular-posts'); ?></option>
            </select>
            </p>
            <p><label for="<?php echo $this->get_field_id( 'order_by' ); ?>"><?php _e('Sort posts by:', 'wordpress-popular-posts'); ?></label>
            <select id="<?php echo $this->get_field_id( 'order_by' ); ?>" name="<?php echo $this->get_field_name( 'order_by' ); ?>" class="widefat">
            	<option value="comments" <?php if ( 'comments' == $instance['order_by'] ) echo 'selected="selected"'; ?>><?php _e('Comments', 'wordpress-popular-posts'); ?></option>
                <option value="views" <?php if ( 'views' == $instance['order_by'] ) echo 'selected="selected"'; ?>><?php _e('Total views', 'wordpress-popular-posts'); ?></option>
                <option value="avg" <?php if ( 'avg' == $instance['order_by'] ) echo 'selected="selected"'; ?>><?php _e('Avg. daily views', 'wordpress-popular-posts'); ?></option>
            </select>
            </p>
            <input type="checkbox" class="checkbox" <?php echo ($instance['pages']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'pages' ); ?>" name="<?php echo $this->get_field_name( 'pages' ); ?>" /> <label for="<?php echo $this->get_field_id( 'pages' ); ?>"><?php _e('Include pages', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('url'); ?>/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php">?</a>]</small><br />
            <?php if ($this->postRating) : ?>
            <input type="checkbox" class="checkbox" <?php echo ($instance['rating']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'rating' ); ?>" name="<?php echo $this->get_field_name( 'rating' ); ?>" /> <label for="<?php echo $this->get_field_id( 'rating' ); ?>"><?php _e('Display post rating', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('url'); ?>/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php">?</a>]</small><br />
            <?php endif; ?>
            <input type="checkbox" class="checkbox" <?php echo ($instance['shorten_title']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'shorten_title-active' ); ?>" name="<?php echo $this->get_field_name( 'shorten_title-active' ); ?>" /> <label for="<?php echo $this->get_field_id( 'shorten_title-active' ); ?>"><?php _e('Shorten title output', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('url'); ?>/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php">?</a>]</small><br />
            <?php if ($instance['shorten_title']['active']) : ?>
            <label for="<?php echo $this->get_field_id( 'shorten_title-length' ); ?>"><?php _e('Shorten title to', 'wordpress-popular-posts'); ?> <input id="<?php echo $this->get_field_id( 'shorten_title-length' ); ?>" name="<?php echo $this->get_field_name( 'shorten_title-length' ); ?>" value="<?php echo $instance['shorten_title']['length']; ?>" class="widefat" style="width:50px!important" /> <?php _e('characters', 'wordpress-popular-posts'); ?></label><br /><br />
			<?php endif; ?>
            <input type="checkbox" class="checkbox" <?php echo ($instance['post-excerpt']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'post-excerpt-active' ); ?>" name="<?php echo $this->get_field_name( 'post-excerpt-active' ); ?>" /> <label for="<?php echo $this->get_field_id( 'post-excerpt-active' ); ?>"><?php _e('Display post excerpt', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('url'); ?>/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php">?</a>]</small><br />
            <?php if ($instance['post-excerpt']['active']) : ?>
            <fieldset class="widefat">
                <legend><?php _e('Excerpt Properties', 'wordpress-popular-posts'); ?></legend>
            	&nbsp;&nbsp;<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'post-excerpt-format' ); ?>" name="<?php echo $this->get_field_name( 'post-excerpt-format' ); ?>" <?php echo ($instance['post-excerpt']['keep_format']) ? 'checked="checked"' : ''; ?> /> <label for="<?php echo $this->get_field_id( 'post-excerpt-format' ); ?>"><?php _e('Keep text format and links', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('url'); ?>/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php">?</a>]</small><br />
            	&nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'post-excerpt-length' ); ?>"><?php _e('Excerpt length:', 'wordpress-popular-posts'); ?> <input id="<?php echo $this->get_field_id( 'post-excerpt-length' ); ?>" name="<?php echo $this->get_field_name( 'post-excerpt-length' ); ?>" value="<?php echo $instance['post-excerpt']['length']; ?>" class="widefat" style="width:30px!important" /> <?php _e('characters', 'wordpress-popular-posts'); ?></label>
			</fieldset>
			<br />
            <?php endif; ?>
            <input type="checkbox" class="checkbox" <?php echo ($instance['exclude-cats']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'exclude-cats' ); ?>" name="<?php echo $this->get_field_name( 'exclude-cats' ); ?>" /> <label for="<?php echo $this->get_field_id( 'exclude-cats' ); ?>"><?php _e('Exclude categories', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('url'); ?>/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php">?</a>]</small><br />
            <?php if ($instance['exclude-cats']['active']) : ?>
            <fieldset class="widefat">
                <legend><?php _e('Categories to exclude', 'wordpress-popular-posts'); ?></legend>
                &nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'excluded' ); ?>"><?php _e('ID(s) (comma separated, no spaces):', 'wordpress-popular-posts'); ?></label><br />&nbsp;&nbsp;<input id="<?php echo $this->get_field_id( 'excluded' ); ?>" name="<?php echo $this->get_field_name( 'excluded' ); ?>" value="<?php echo $instance['exclude-cats']['cats']; ?>" class="widefat" style="width:150px" /><br /><br />
            </fieldset>            
            <?php endif; ?>
            <br />
			
			<fieldset style="width:214px; padding:5px;"  class="widefat">
                <legend><?php _e('Thumbnail settings', 'wordpress-popular-posts'); ?></legend>
				<input type="checkbox" class="checkbox" <?php echo ($instance['thumbnail']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'thumbnail-active' ); ?>" name="<?php echo $this->get_field_name( 'thumbnail-active' ); ?>" /> <label for="<?php echo $this->get_field_id( 'thumbnail-active' ); ?>"><?php _e('Display post thumbnail', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('url'); ?>/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php">?</a>]</small><br />
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
                <input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['comment_count']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'comment_count' ); ?>" name="<?php echo $this->get_field_name( 'comment_count' ); ?>" /> <label for="<?php echo $this->get_field_id( 'comment_count' ); ?>"><?php _e('Display comment count', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('url'); ?>/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php">?</a>]</small><br />                
                <input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['views']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'views' ); ?>" name="<?php echo $this->get_field_name( 'views' ); ?>" /> <label for="<?php echo $this->get_field_id( 'views' ); ?>"><?php _e('Display views', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('url'); ?>/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php">?</a>]</small><br />            
                <input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['author']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'author' ); ?>" name="<?php echo $this->get_field_name( 'author' ); ?>" /> <label for="<?php echo $this->get_field_id( 'author' ); ?>"><?php _e('Display author', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('url'); ?>/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php">?</a>]</small><br />            
                <input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['date']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'date' ); ?>" name="<?php echo $this->get_field_name( 'date' ); ?>" /> <label for="<?php echo $this->get_field_id( 'date' ); ?>"><?php _e('Display date', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('url'); ?>/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php">?</a>]</small>
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
                <input type="checkbox" class="checkbox" <?php echo ($instance['markup']['custom_html']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'custom_html' ); ?>" name="<?php echo $this->get_field_name( 'custom_html' ); ?>" /> <label for="<?php echo $this->get_field_id( 'custom_html' ); ?>"><?php _e('Use custom HTML Markup', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('url'); ?>/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php">?</a>]</small><br />
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
                <input type="checkbox" class="checkbox" <?php echo ($instance['markup']['pattern']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'pattern_active' ); ?>" name="<?php echo $this->get_field_name( 'pattern_active' ); ?>" /> <label for="<?php echo $this->get_field_id( 'pattern_active' ); ?>"><?php _e('Use content formatting tags', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('url'); ?>/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php">?</a>]</small><br />
                <?php if ($instance['markup']['pattern']['active']) : ?>
                <br />
                <p style="font-size:11px"><label for="<?php echo $this->get_field_id( 'pattern_form' ); ?>"><?php _e('Content format:', 'wordpress-popular-posts'); ?></label>
                <input type="text" id="<?php echo $this->get_field_id( 'pattern_form' ); ?>" name="<?php echo $this->get_field_name( 'pattern_form' ); ?>" value="<?php echo $instance['markup']['pattern']['form']; ?>" style="width:204px" <?php echo ($instance['markup']['pattern']['active']) ? '' : 'disabled="disabled"' ?> /></p>
                <?php endif; ?>
            </fieldset>
            <?php
		}
		
		// RRR Added to get local time as per WP settings		
		function curdate() {
			//return "'".gmdate( 'Y-m-d', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ))."'";
			return gmdate( 'Y-m-d', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ));
		}
		
		function now() {		
			//return "'".current_time('mysql')."'";
			return current_time('mysql');
		}
		
		// updates popular posts data table
		function wpp_ajax_update() {		
			$nonce = $_POST['token'];
			
			// is this a valid request?
			if (! wp_verify_nonce($nonce, 'wpp-token') ) die("Oops!");
			
			if (is_numeric($_POST['id']) && (intval($_POST['id']) == floatval($_POST['id'])) && ($_POST['id'] != '')) {
				$id = $_POST['id'];
			} else {
				die("Invalid ID");
			}
			
			// if we got an ID, let's update the data table
						
			global $wpdb;
			
			$wpdb->show_errors();
			
			$table = $wpdb->prefix . 'popularpostsdata';
			
			// update popularpostsdata table
			$exists = $wpdb->get_results("SELECT postid FROM $table WHERE postid = '$id'");							
			if ($exists) {
				$result = $wpdb->query("UPDATE $table SET last_viewed = '".$this->now()."', pageviews = pageviews + 1 WHERE postid = '$id'");
			} else {
				$result = $wpdb->query("INSERT INTO $table (postid, day, last_viewed) VALUES ('".$id."', '".$this->now()."', '".$this->now()."')" );
			}
			
			// update popularpostsdatacache table
			$isincache = $wpdb->get_results("SELECT id FROM ".$table."cache WHERE id = '" . $id ."' AND day BETWEEN '".$this->curdate()." 00:00:00' AND '".$this->curdate()." 23:59:59';");
			if ($isincache) {
				$result2 = $wpdb->query("UPDATE ".$table."cache SET pageviews = pageviews + 1, day = '".$this->now()."' WHERE id = '". $id . "' AND day BETWEEN '".$this->curdate()." 00:00:00' AND '".$this->curdate()." 23:59:59';");
			} else {
				$result2 = $wpdb->query("INSERT INTO ".$table."cache (id, day) VALUES ('".$id."', '".$this->now()."')");
			}
			
			if (($result == 1) && ($result2 == 1)) {
				die("OK");
			} else {
				die($wpdb->print_error);
			}		
			
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
			$sql = "";
			$charset_collate = "";
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
			if ( ! empty($wpdb->charset) ) $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) ) $charset_collate .= " COLLATE $wpdb->collate";
			
			// set table name
			$table = $wpdb->prefix . "popularpostsdata";
			
			// does popularpostsdata table exists?
			if ( $wpdb->get_var("SHOW TABLES LIKE '$table'") != $table ) { // fresh setup
				// create tables popularpostsdata and popularpostsdatacache
				$sql = "CREATE TABLE " . $table . " ( UNIQUE KEY id (postid), postid int(10) NOT NULL, day datetime NOT NULL default '0000-00-00 00:00:00', last_viewed datetime NOT NULL default '0000-00-00 00:00:00', pageviews int(10) default 1 ) $charset_collate; CREATE TABLE " . $table ."cache ( UNIQUE KEY id (id, day), id int(10) NOT NULL, day datetime NOT NULL default '0000-00-00 00:00:00', pageviews int(10) default 1 ) $charset_collate;";
			} else {
				$cache = $table . "cache";
				if ( $wpdb->get_var("SHOW TABLES LIKE '$cache'") != $cache ) {
					// someone is upgrading from version 1.5.x
					$sql = "CREATE TABLE " . $table ."cache ( UNIQUE KEY id (id, day), id int(10) NOT NULL, day datetime NOT NULL, pageviews int(10) default 1 ) $charset_collate;";
				}
				
				$dateField = $wpdb->get_results("SHOW FIELDS FROM " . $table ."cache", ARRAY_A);
				if ($dateField[1]['Type'] != 'datetime') $wpdb->query("ALTER TABLE ". $table ."cache CHANGE day day datetime NOT NULL default '0000-00-00 00:00:00';");
			}
			
			dbDelta($sql);
		}
		
		// prints ajax script to theme's header
		function wpp_print_ajax() {		
			// let's add jQuery
			wp_print_scripts('jquery');
				
			// create security token
			$nonce = wp_create_nonce('wpp-token');
			
			// get current post's ID
			global $wp_query;
			wp_reset_query();
			
			// if we're on a page or post, load the script
			if ( (is_single() || is_page()) && !is_user_logged_in() ) {
				$id = $wp_query->post->ID;
			?>
<!-- Wordpress Popular Posts v<?php echo $this->version; ?> -->
<script type="text/javascript" charset="utf-8">
    /* <![CDATA[ */				
	jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {action: 'wpp_update', token: '<?php echo $nonce; ?>', id: <?php echo $id; ?>});
    /* ]]> */
</script>
<!-- End Wordpress Popular Posts v<?php echo $this->version; ?> -->
            <?php
			}
		}		
		
		// prints popular posts
		function get_popular_posts($instance, $echo = true) {		
			
			global $wpdb;
			$table = $wpdb->prefix . "popularpostsdata";
						
			if ( $instance['pages'] ) {
				$nopages = '';
			} else {
				$nopages = "AND $wpdb->posts.post_type = 'post'";
			}
			
			switch( $instance['range'] ) {
				case 'all':
					$range = "post_date_gmt < '".gmdate("Y-m-d H:i:s")."'";
					break;
				case 'yesterday':
					$range = $table."cache.day >= '".gmdate("Y-m-d")."' - INTERVAL 1 DAY";
					break;
				case 'daily':
					//$range = $table."cache.day = ".$this->curdate();
					$range = $table."cache.day >= '".$this->now()."' - INTERVAL 1 DAY";
					break;
				case 'weekly':
					$range = $table."cache.day >= '".gmdate("Y-m-d")."' - INTERVAL 7 DAY";
					break;
				case 'monthly':
					$range = $table."cache.day >= '".gmdate("Y-m-d")."' - INTERVAL 30 DAY";
					break;
				default:
					$range = "post_date_gmt < '".gmdate("Y-m-d H:i:s")."'";
					break;
			}
			
			// sorting options
			switch( $instance['order_by'] ) {
				case 'comments':
					$sortby = 'comment_count';
					break;
				case 'views':
					$sortby = 'pageviews';
					break;
				case 'avg':
					$sortby = 'avg_views';
					break;
				default:
					$sortby = 'comment_count';
					break;
			}
			
			// dynamic query fields
			$fields = ', ';			
			if ( $instance['stats_tag']['views'] || ($sortby != 'comment_count') ) {
				
				if ( $sortby == 'avg_views' ) {
					if ( $instance['range'] == 'all') {
						$fields .= "(".$table.".pageviews / (IF ( DATEDIFF('".$this->now()."', $wpdb->posts.post_date_gmt) > 0, DATEDIFF('".$this->now()."', $wpdb->posts.post_date_gmt), 1) )) AS 'avg_views' ";
					} else {
						$fields .= "(SUM(".$table."cache.pageviews)/(IF ( DATEDIFF('".$this->now()."', MIN(".$table."cache.day)) > 0, DATEDIFF('".$this->now()."', MIN(".$table."cache.day)), 1) )) AS 'avg_views' ";
					}
				} else {
					//$fields .= "(SUM(".$table."cache.pageviews)) AS 'pageviews' ";
					if ( $instance['range'] == 'all') {
						$fields .= "$table.pageviews AS 'pageviews' ";
					} else {
						$fields .= "(SUM(".$table."cache.pageviews)) AS 'pageviews' ";
					}
				}
			}
			
			if ( $instance['stats_tag']['comment_count'] ) {
				if ( $fields != ', ' ) {
					$fields .= ", $wpdb->posts.comment_count AS 'comment_count' ";
				} else {
					$fields .= "$wpdb->posts.comment_count AS 'comment_count' ";
				}
			}
			
			if ( $instance['stats_tag']['author'] ) {
				if ( $fields != ', ' ) {
					$fields .= ", (SELECT $wpdb->users.display_name FROM $wpdb->users WHERE $wpdb->users.ID = $wpdb->posts.post_author ) AS 'display_name'";
				} else {
					$fields .= "(SELECT $wpdb->users.display_name FROM $wpdb->users WHERE $wpdb->users.ID = $wpdb->posts.post_author ) AS 'display_name'";
				}
			}
			if ( $instance['stats_tag']['date']['active'] ) {
				if ( $fields != ', ' ) {
					$fields .= ", $wpdb->posts.post_date_gmt AS 'date_gmt'";
				} else {
					$fields .= "$wpdb->posts.post_date_gmt AS 'date_gmt'";
				}
			}			
			
			if (strlen($fields) == 2) $fields = '';
			
			$force_pv = "";

			if ( $instance['range'] == 'all') {
				$join = "LEFT JOIN $table ON $wpdb->posts.ID = $table.postid";
				$force_pv = "AND ".$table.".pageviews > 0 ";
			} else {
				$join = "RIGHT JOIN ".$table."cache ON $wpdb->posts.ID = ".$table."cache.id";				
			}
			
			// Category excluding snippet suggested by user almergabor at http://wordpress.org/support/topic/plugin-wordpress-popular-posts-exclude-and-include-categories?replies=2#post-2464701
			// Thanks, almergabor!
			if ( $instance['exclude-cats']['active'] && !empty($instance['exclude-cats']['cats']) ) {				
				$exclude = " AND $wpdb->posts.ID NOT IN (
							SELECT object_id
							FROM $wpdb->term_relationships AS r
								 JOIN $wpdb->term_taxonomy AS x ON x.term_taxonomy_id = r.term_taxonomy_id
								 JOIN $wpdb->terms AS t ON t.term_id = x.term_id
							WHERE x.taxonomy = 'category' AND t.term_id IN(".$instance['exclude-cats']['cats'].")
							) ";

			} else {
				$exclude = "";
			}
			
			$mostpopular = $wpdb->get_results("SELECT $wpdb->posts.ID, $wpdb->posts.post_title $fields FROM $wpdb->posts $join WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_password = '' AND $range $force_pv $nopages $exclude GROUP BY $wpdb->posts.ID ORDER BY $sortby DESC LIMIT " . $instance['limit'] . "");
			
			$content = '';
			
			//echo "SELECT $wpdb->posts.ID, $wpdb->posts.post_title $fields FROM $wpdb->posts $join WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_password = '' AND $range $force_pv $nopages $exclude GROUP BY $wpdb->posts.ID ORDER BY $sortby DESC LIMIT " . $instance['limit'];
			
			if ( !is_array($mostpopular) || empty($mostpopular) ) {
				$content .= "<p>".__('Sorry. No data so far.', 'wordpress-popular-posts')."</p>"."\n";
			} else {
				
				if ($instance['markup']['custom_html']) {
					$content .= htmlspecialchars_decode($instance['markup']['wpp-start'], ENT_QUOTES) ."\n";
				} else {
					$content .= "<ul>" . "\n";
				}
				
				foreach ($mostpopular as $wppost) {					
				
					$post_stats = "";
					$stats = "";
					$thumb = "";
					$the_ID = $wppost->ID;
					$data = array();
					
					// get post title
					/* qTranslate integration check */
					($this->qTrans) ? $tit = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($wppost->post_title) : $tit = $wppost->post_title;
					
					$tit = ($this->magicquotes) ? stripslashes($tit) : $tit;
					//$title_attr = htmlentities($tit, ENT_QUOTES, $this->charset);
					
					$title_attr = apply_filters('the_title', $tit);
					
					if ( $instance['shorten_title']['active'] && (strlen($tit) > $instance['shorten_title']['length'])) {
						$tit = mb_substr($tit, 0, $instance['shorten_title']['length'], $this->charset) . "...";
					}
					
					//$tit = htmlentities($tit, ENT_QUOTES, $this->charset);					
					$tit = apply_filters('the_title', $tit);
					
					// get post excerpt
					if ( $instance['post-excerpt']['active'] ) {
						if ($instance['markup']['pattern']['active']) {
							$post_content = "<span class=\"wpp-excerpt\">" . $this->get_summary($the_ID, $instance) . "</span>";
						} else {
							$post_content = ": <span class=\"wpp-excerpt\">" . $this->get_summary($the_ID, $instance) . "...</span>";
						}
					} else {
						$post_content = "";
					}
					
					// build stats tag
					if ( $instance['stats_tag']['comment_count'] ) {
						$comment_count = (int) $wppost->comment_count;
						$post_stats .= "<span class=\"wpp-comments\">" . $comment_count . " " . __(' comment(s)', 'wordpress-popular-posts') . "</span>";
					}
					if ( $instance['stats_tag']['views'] ) {
						$views_text = __(' view(s)', 'wordpress-popular-posts');
						if ($instance['order_by'] == 'views') {
							$pageviews = (int) $wppost->pageviews;
						} else if ($instance['order_by'] == 'avg') {
							//$pageviews = ceil($wppost->avg_views);
							$pageviews = round($wppost->avg_views, 2);
							if ($instance['range'] != 'daily') $views_text = __(' view(s) per day', 'wordpress-popular-posts');
						} else {
							$pageviews = (int) $wppost->pageviews;
						}			
						
						if ($post_stats != "") {
							$post_stats .= " | <span class=\"wpp-views\">$pageviews $views_text</span>";
						} else {							
							$post_stats .= "<span class=\"wpp-views\">$pageviews $views_text</span>";
						}										
					}
					if ( $instance['stats_tag']['author'] ) {
						if ($post_stats != "") {
							$post_stats .= " | ".__('by', 'wordpress-popular-posts')." <span class=\"wpp-author\">".$wppost->display_name."</span>";
						} else {					
							$post_stats .= __('by', 'wordpress-popular-posts')." <span class=\"wpp-author\">".$wppost->display_name."</span>";
						}
					}
					if ( $instance['stats_tag']['date']['active'] ) {
						if ($post_stats != "") {
							$post_stats .= " | <span class=\"wpp-date\">".__('posted on', 'wordpress-popular-posts')." ".date_i18n($instance['stats_tag']['date']['format'], strtotime($wppost->date_gmt))."</span>";
						} else {					
							$post_stats .= "<span class=\"wpp-date\">".__('posted on', 'wordpress-popular-posts')." ".date_i18n($instance['stats_tag']['date']['format'], strtotime($wppost->date_gmt))."</span>";
						}
					}
					
					if (!empty($post_stats)) {
						$stats = ' <span class="post-stats">' . $post_stats . '</span> ';
					}
					
					// get thumbnail
					if ($instance['thumbnail']['active'] && $this->thumb) {
						$tbWidth = $instance['thumbnail']['width'];
						$tbHeight = $instance['thumbnail']['height'];
						
						if (!function_exists('get_the_post_thumbnail')) { // if the Featured Image is not active, show default thumbnail
							$thumb = "<a href=\"".get_permalink($the_ID)."\" class=\"wppnothumb\" title=\"". $title_attr ."\"><img src=\"". $this->default_thumbnail . "\" alt=\"".$title_attr."\" border=\"0\" class=\"wpp-thumbnail\" width=\"".$tbWidth."\" height=\"".$tbHeight."\" "."/></a>";
						} else {
							if (has_post_thumbnail( $the_ID )) { // if the post has a thumbnail, get it
								$thumb = "<a href=\"".get_permalink($the_ID)."\" title=\"". $title_attr ."\">" . get_the_post_thumbnail($the_ID, array($tbWidth, $tbHeight), array('class' => 'wpp-thumbnail', 'alt' => $title_attr, 'title' => $title_attr) ) . "</a>";
							} else { // try to generate a post thumbnail from first image attached to post. If it fails, use default thumbnail
								$thumb = "<a href=\"".get_permalink($the_ID)."\" title=\"". $title_attr ."\">" . $this->generate_post_thumbnail($the_ID, array($tbWidth, $tbHeight), array('class' => 'wpp-thumbnail', 'alt' => $title_attr, 'title' => $title_attr) ) ."</a>";
							}
						}
					}
					
					// get rating
					if ($instance['rating'] && $this->postRating) {
						$rating = '<span class="wpp-rating">'.the_ratings_results($the_ID).'</span>';
					} else {
						$rating = '';
					}
					
					$data = array(
						'title' => '<a href="'.get_permalink($the_ID).'" title="'. $title_attr . '"><span class="wpp-post-title">'. $tit .'</span></a>',
						'summary' => $post_content,
						'stats' => $stats,
						'img' => $thumb,
						'id' => $the_ID
					);
					
					// build custom layout
					if ($instance['markup']['custom_html']) {
						if ($instance['markup']['pattern']['active']) {
							$content .= htmlspecialchars_decode($instance['markup']['post-start'], ENT_QUOTES) . $this->format_content($instance['markup']['pattern']['form'], $data, $instance['rating']) . htmlspecialchars_decode($instance['markup']['post-end'], ENT_QUOTES) . "\n";
						} else {
							$content .= htmlspecialchars_decode($instance['markup']['post-start'], ENT_QUOTES) . $thumb . '<a href="'.get_permalink($the_ID).'" title="'. $title_attr .'"><span class="wpp-post-title">'. $tit .'</span></a>'.$post_content.' '. $stats . $rating . htmlspecialchars_decode($instance['markup']['post-end'], ENT_QUOTES) . "\n";
						}
					} else {
						$content .= '<li>'. $thumb .'<a href="'. get_permalink($the_ID) .'" title="'. $title_attr .'"><span class="wpp-post-title">'. $tit .'</span></a>'. $post_content .' '. $stats . $rating .'</li>' . "\n";
					}
				}			
				
				if ($instance['markup']['custom_html']) {
					$content .= htmlspecialchars_decode($instance['markup']['wpp-end'], ENT_QUOTES) ."\n";
				} else {
					$content .= "\n"."</ul>"."\n";
				}
				
			}
			
			if ($echo) { echo "<noscript>" . $content . "</noscript>"; } else { return $content; }
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
		
		// Generates a featured image from the first image attached to a post if found.
		// Otherwise, returns default thumbnail
		// Since 2.2.0
		function generate_post_thumbnail($id, $dimensions, $atts) {
			// get post attachments
			$attachments = get_children(array('post_parent' => $id, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'orderby' => 'menu_order'));
			
			// no images have been attached to the post, return default thumbnail
			if ( !$attachments ) return "<a href=\"".get_permalink($id)."\" class=\"wppnothumb\" title=\"". $atts['title'] ."\"><img src=\"". $this->default_thumbnail . "\" alt=\"". $atts['alt'] ."\" border=\"0\" class=\"". $atts['class'] ."\" width=\"". $dimensions[0] ."\" height=\"". $dimensions[1] ."\" "."/></a>";
			
			$count = count($attachments);
			$first_attachment = array_shift($attachments);			
			$img = wp_get_attachment_image($first_attachment->ID);
						
			if (!empty($img)) { // found an image, use it as Featured Image
				update_post_meta( $id, '_thumbnail_id', $first_attachment->ID );
				return get_the_post_thumbnail($id, $dimensions, $atts);
			} else { // no images have been found, return default thumbnail
				return "<a href=\"".get_permalink($id)."\" class=\"wppnothumb\" title=\"". $atts['title'] ."\"><img src=\"". $this->default_thumbnail . "\" alt=\"". $atts['alt'] ."\" border=\"0\" class=\"". $atts['class'] ."\" width=\"". $dimensions[0] ."\" height=\"". $dimensions[1] ."\" "."/></a>";
			}
			
		}
		
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
				if (strtolower($matches[0][$i]) == "{image}") {
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
						// maximum lenght is reached, so get off the loop
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
			$css_path = (@file_exists(TEMPLATEPATH.'/wpp.css')) ? get_stylesheet_directory_uri().'/wpp.css' : plugin_dir_url( __FILE__ ).'style/wpp.css';
			echo "\n"."<!-- Wordpress Popular Posts v".$this->version." -->"."\n".'<link rel="stylesheet" href="'. $css_path .'" type="text/css" media="screen" />'."\n"."<!-- End Wordpress Popular Posts v".$this->version." -->"."\n";	
		}
		
		// create Wordpress Popular Posts' maintenance page
		function wpp_maintenance_page() {
			require (dirname(__FILE__) . '/maintenance.php');
		}	
		function add_wpp_maintenance_page() {			
			add_submenu_page( 'options-general.php', 'Wordpress Popular Posts', 'Wordpress Popular Posts', 'manage_options', 'wpp_maintenance_page', array(&$this, 'wpp_maintenance_page') );
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
			
			delete_option('wpp_ver');
		}
		
		// shortcode handler
		function wpp_shortcode($atts = NULL, $content = NULL) {
			extract( shortcode_atts( array(
				'header' => '',
				'limit' => 10,
				'range' => 'daily',
				'order_by' => 'comments',
				'pages' => true,
				'title_length' => 0,
				'excerpt_length' => 0,
				'excerpt_format' => 0,
				'cats_to_exclude' => '',
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
				'pages' => empty($pages) || $pages == "false" ? false : true,
				'shorten_title' => array(
					'active' => empty($title_length) ? false : (is_numeric($title_length)) ? (($title_length > 0) ? true : false) : false,
					'length' => empty($title_length) ? 0 : (is_numeric($title_length)) ? $title_length : 0 
				),
				'post-excerpt' => array(
					'active' => empty($excerpt_length) ? false : (is_numeric($excerpt_length)) ? (($excerpt_length > 0) ? true : false) : false,
					'length' => empty($excerpt_length) ? 0 : (is_numeric($excerpt_length)) ? $excerpt_length : 0,
					'keep_format' => empty($excerpt_format) ? false : (is_numeric($excerpt_format)) ? (($excerpt_format > 0) ? true : false) : false,
				),
				'exclude-cats' => array(
					'active' => empty($cats_to_exclude) ? false : (ctype_digit(str_replace(",", "", $cats_to_exclude))) ? true : false,
					'cats' => empty($cats_to_exclude) ? '' : (ctype_digit(str_replace(",", "", $cats_to_exclude))) ? $cats_to_exclude : ''
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
			
			$shortcode_content = "";
			
			$shortcode_content .= "<!-- Wordpress Popular Posts Plugin v". $this->version ." [SC] [".$shortcode_ops['range']."]". (($shortcode_ops['markup']['custom_html']) ? ' [custom]' : ' [regular]') ." -->"."\n";
			
			// is there a title defined by user?
			if (!empty($header) && !empty($header_start) && !empty($header_end)) {
				$shortcode_content .= $header_start . $header . $header_end;
			}
			
			// print popular posts list
			$shortcode_content .= $this->get_popular_posts($shortcode_ops, false);
			
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

	if (is_null($args)) {
		echo do_shortcode('[wpp]');
	} else {
		$atts = trim(str_replace("&", " ", $args));
		echo do_shortcode('[wpp '.$atts.']');
	}
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
 * Wordpress Popular Posts 2.2.1 Changelog.
 */

/*
	= 2.2.1 =
	* Quick update to fix error with All-time combined with views breaking the plugin.
*/