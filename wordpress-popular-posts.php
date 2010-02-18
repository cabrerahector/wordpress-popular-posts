<?php
/*
Plugin Name: Wordpress Popular Posts
Plugin URI: http://wordpress.org/extend/plugins/wordpress-popular-posts/
Description: Retrieves the most active entries of your blog and displays them with your own formatting (<em>optional</em>). Use it as a widget or place it in your templates using  <strong>&lt;?php get_mostpopular(); ?&gt;</strong>
Version: 2.0.1
Author: H&eacute;ctor Cabrera
Author URI: http://wordpress.org/extend/plugins/wordpress-popular-posts/
*/

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
		var $version = "2.0.1";
		var $qTrans = false;
		var $postRating = false;
		var $thumb = false;		
		var $pluginDir = "";
		
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
			
			// add ajax update to wp_ajax_ hook
			add_action('wp_ajax_nopriv_wpp_update', array(&$this, 'wpp_ajax_update'));
			add_action('wp_head', array(&$this, 'wpp_print_ajax'));
			
			// add ajax table truncation to wp_ajax_ hook
			add_action('wp_ajax_wpp_clear_cache', array(&$this, 'wpp_clear_data'));
			add_action('wp_ajax_wpp_clear_all', array(&$this, 'wpp_clear_data'));
			
			// print stylesheet
			add_action('wp_head', array(&$this, 'wpp_print_stylesheet'));
			
			// activate textdomain for translations
			add_action('init', array(&$this, 'wpp_textdomain'));
			
			// activate maintenance page
			add_action('admin_menu', array(&$this, 'add_wpp_maintenance_page'));
							
			// database creation
			register_activation_hook(__FILE__, $this->wpp_install());
			
			// cache maintenance schedule
			register_activation_hook(__FILE__, array(&$this, 'wpp_cache_schedule'));
			add_action('wpp_cache_event', array(&$this, 'wpp_cache_maintenance'));
			register_deactivation_hook(__FILE__, array(&$this, 'wpp_deactivation'));
			
			// Wordpress version check
			if (version_compare($wp_version, '2.8.0', '<')) add_action('admin_notices', array(&$this, 'wpp_update_warning'));
			
			// qTrans plugin support
			if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) $this->qTrans = true;
			
			// WP-Post Ratings plugin support
			if (function_exists('the_ratings_results')) $this->postRating = true;
			
			// Can we create thumbnails?
			if (extension_loaded('gd') && function_exists('gd_info') && version_compare(phpversion(), '4.3.0', '>=')) $this->thumb = true;
			
			// shortcode
			if( function_exists('add_shortcode') ){
				add_shortcode('wpp', array(&$this, 'wpp_shortcode'));
				add_shortcode('WPP', array(&$this, 'wpp_shortcode'));
			}
		}		

		// builds Wordpress Popular Posts' widgets
		function widget($args, $instance) {
			extract($args);
			echo $before_widget;
			
			// has user set a title?
			if ($instance['title'] != '') {
				if ($instance['markup']['custom_html'] && $instance['markup']['title-start'] != "" && $instance['markup']['title-end'] != "" ) {
					echo htmlspecialchars_decode($instance['markup']['title-start'], ENT_QUOTES) . htmlspecialchars_decode($instance['title'], ENT_QUOTES) . htmlspecialchars_decode($instance['markup']['title-end'], ENT_QUOTES);
				} else {
					echo $before_title . htmlspecialchars_decode($instance['title'], ENT_QUOTES) . $after_title;
				}
			}
			
			echo $this->get_popular_posts($instance, false);
			echo $after_widget;
		}

		// updates each widget instance when user clicks the "save" button
		function update($new_instance, $old_instance) {
			
			$instance = $old_instance;
			
			$instance['title'] = htmlspecialchars( strip_tags( $new_instance['title'] ), ENT_QUOTES );
			$instance['limit'] = empty($new_instance['limit']) ? 10 : (is_numeric($new_instance['limit'])) ? $new_instance['limit'] : 10;
			$instance['range'] = $new_instance['range'];
			$instance['order_by'] = $new_instance['order_by'];
			$instance['pages'] = $new_instance['pages'];
			$instance['shorten_title']['active'] = $new_instance['shorten_title-active'];
			$instance['shorten_title']['length'] = empty($new_instance['shorten_title-length']) ? 25 : (is_numeric($new_instance['shorten_title-length'])) ? $new_instance['shorten_title-length'] : 25;
			$instance['post-excerpt']['active'] = $new_instance['post-excerpt-active'];
			$instance['post-excerpt']['length'] = empty($new_instance['post-excerpt-length']) ? 55 : (is_numeric($new_instance['post-excerpt-length'])) ? $new_instance['post-excerpt-length'] : 55;
			$instance['exclude-cats']['active'] = $new_instance['exclude-cats'];
			$instance['exclude-cats']['cats'] = empty($new_instance['excluded']) ? '' : (ctype_digit(str_replace(",", "", $new_instance['excluded']))) ? $new_instance['excluded'] : '';
			if ($this->thumb) { // can create thumbnails
				$instance['thumbnail']['active'] = $new_instance['thumbnail-active'];
				$instance['thumbnail']['width'] = empty($new_instance['thumbnail-width']) ? 15 : (is_numeric($new_instance['thumbnail-width'])) ? $new_instance['thumbnail-width'] : 15;
				$instance['thumbnail']['height'] = empty($new_instance['thumbnail-height']) ? 15 : (is_numeric($new_instance['thumbnail-height'])) ? $new_instance['thumbnail-height'] : 15;
			} else { // cannot create thumbnails
				$instance['thumbnail']['active'] = false;
				$instance['thumbnail']['width'] = 15;
				$instance['thumbnail']['height'] = 15;
			}
			
			$instance['rating'] = $new_instance['rating'];
			$instance['stats_tag']['comment_count'] = $new_instance['comment_count'];
			$instance['stats_tag']['views'] = $new_instance['views'];
			$instance['stats_tag']['author'] = $new_instance['author'];
			$instance['stats_tag']['date'] = $new_instance['date'];
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
					'length' => 25
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
					'date' => false
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
            	<option value="daily" <?php if ( 'daily' == $instance['range'] ) echo 'selected="selected"'; ?>><?php _e('Today', 'wordpress-popular-posts'); ?></option>
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
            <label for="<?php echo $this->get_field_id( 'post-excerpt-length' ); ?>"><?php _e('Excerpt length:', 'wordpress-popular-posts'); ?> <input id="<?php echo $this->get_field_id( 'post-excerpt-length' ); ?>" name="<?php echo $this->get_field_name( 'post-excerpt-length' ); ?>" value="<?php echo $instance['post-excerpt']['length']; ?>" class="widefat" style="width:50px!important" /> <?php _e('characters', 'wordpress-popular-posts'); ?></label><br /><br />
            <?php endif; ?>
            
            <input type="checkbox" class="checkbox" <?php echo ($instance['exclude-cats']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'exclude-cats' ); ?>" name="<?php echo $this->get_field_name( 'exclude-cats' ); ?>" /> <label for="<?php echo $this->get_field_id( 'exclude-cats' ); ?>"><?php _e('Exclude categories', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('url'); ?>/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php">?</a>]</small><br />
            <?php if ($instance['exclude-cats']['active']) : ?>
            <label for="<?php echo $this->get_field_id( 'excluded' ); ?>"><?php _e('ID(s) to exclude (comma separated, no spaces):', 'wordpress-popular-posts'); ?></label><br /><input id="<?php echo $this->get_field_id( 'excluded' ); ?>" name="<?php echo $this->get_field_name( 'excluded' ); ?>" value="<?php echo $instance['exclude-cats']['cats']; ?>" class="widefat" /><br /><br />
            <?php endif; ?>
            
            <input type="checkbox" class="checkbox" <?php echo ($instance['thumbnail']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'thumbnail-active' ); ?>" name="<?php echo $this->get_field_name( 'thumbnail-active' ); ?>" /> <label for="<?php echo $this->get_field_id( 'thumbnail-active' ); ?>"><?php _e('Display post thumbnail', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('url'); ?>/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php">?</a>]</small><br />
            <?php if($instance['thumbnail']['active']) : ?>
            <label for="<?php echo $this->get_field_id( 'thumbnail-width' ); ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('Width:', 'wordpress-popular-posts'); ?></label> 
            <input id="<?php echo $this->get_field_id( 'thumbnail-width' ); ?>" name="<?php echo $this->get_field_name( 'thumbnail-width' ); ?>" value="<?php echo $instance['thumbnail']['width']; ?>"  class="widefat" style="width:30px!important" <?php echo ($this->thumb) ? '' : 'disabled="disabled"' ?> /> <?php _e('px', 'wordpress-popular-posts'); ?> <br />
            <label for="<?php echo $this->get_field_id( 'thumbnail-height' ); ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('Height:', 'wordpress-popular-posts'); ?></label> 
            <input id="<?php echo $this->get_field_id( 'thumbnail-height' ); ?>" name="<?php echo $this->get_field_name( 'thumbnail-height' ); ?>" value="<?php echo $instance['thumbnail']['height']; ?>"  class="widefat" style="width:30px!important" <?php echo ($this->thumb) ? '' : 'disabled="disabled"' ?> /> <?php _e('px', 'wordpress-popular-posts'); ?><br />
            <?php endif; ?>
            <br />
            <fieldset style="width:214px; padding:5px;"  class="widefat">
                <legend><?php _e('Stats Tag settings', 'wordpress-popular-posts'); ?></legend>
                <input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['comment_count']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'comment_count' ); ?>" name="<?php echo $this->get_field_name( 'comment_count' ); ?>" /> <label for="<?php echo $this->get_field_id( 'comment_count' ); ?>"><?php _e('Display comment count', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('url'); ?>/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php">?</a>]</small><br />                
                <input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['views']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'views' ); ?>" name="<?php echo $this->get_field_name( 'views' ); ?>" /> <label for="<?php echo $this->get_field_id( 'views' ); ?>"><?php _e('Display views', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('url'); ?>/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php">?</a>]</small><br />            
                <input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['author']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'author' ); ?>" name="<?php echo $this->get_field_name( 'author' ); ?>" /> <label for="<?php echo $this->get_field_id( 'author' ); ?>"><?php _e('Display author', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('url'); ?>/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php">?</a>]</small><br />            
                <input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['date']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'date' ); ?>" name="<?php echo $this->get_field_name( 'date' ); ?>" /> <label for="<?php echo $this->get_field_id( 'date' ); ?>"><?php _e('Display date', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('url'); ?>/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php">?</a>]</small>            
            </fieldset>
            <br /><br /> 
            <fieldset style="width:214px; padding:5px;"  class="widefat">
                <legend><?php _e('HTML Markup settings', 'wordpress-popular-posts'); ?></legend>
                <input type="checkbox" class="checkbox" <?php echo ($instance['markup']['custom_html']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'custom_html' ); ?>" name="<?php echo $this->get_field_name( 'custom_html' ); ?>" /> <label for="<?php echo $this->get_field_id( 'custom_html' ); ?>"><?php _e('Use custom HTML Markup', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('url'); ?>/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php">?</a>]</small><br /><br />
                <p style="font-size:11px"><label for="<?php echo $this->get_field_id( 'title-start' ); ?>"><?php _e('Before / after title:', 'wordpress-popular-posts'); ?></label> <br />
                <input type="text" id="<?php echo $this->get_field_id( 'title-start' ); ?>" name="<?php echo $this->get_field_name( 'title-start' ); ?>" value="<?php echo $instance['markup']['title-start']; ?>" class="widefat" style="width:80px!important" <?php echo ($instance['markup']['custom_html']) ? '' : 'disabled="disabled"' ?> /> <input type="text" id="<?php echo $this->get_field_id( 'title-end' ); ?>" name="<?php echo $this->get_field_name( 'title-end' ); ?>" value="<?php echo $instance['markup']['title-end']; ?>" class="widefat" style="width:80px!important" <?php echo ($instance['markup']['custom_html']) ? '' : 'disabled="disabled"' ?> /></p>
                <p style="font-size:11px"><label for="<?php echo $this->get_field_id( 'wpp_start' ); ?>"><?php _e('Before / after Popular Posts:', 'wordpress-popular-posts'); ?></label> <br />
                <input type="text" id="<?php echo $this->get_field_id( 'wpp-start' ); ?>" name="<?php echo $this->get_field_name( 'wpp-start' ); ?>" value="<?php echo $instance['markup']['wpp-start']; ?>" class="widefat" style="width:80px!important" <?php echo ($instance['markup']['custom_html']) ? '' : 'disabled="disabled"' ?> /> <input type="text" id="<?php echo $this->get_field_id( 'wpp-end' ); ?>" name="<?php echo $this->get_field_name( 'wpp-end' ); ?>" value="<?php echo $instance['markup']['wpp-end']; ?>" class="widefat" style="width:80px!important" <?php echo ($instance['markup']['custom_html']) ? '' : 'disabled="disabled"' ?> /></p>
                <p style="font-size:11px"><label for="<?php echo $this->get_field_id( 'post-start' ); ?>"><?php _e('Before / after each post:', 'wordpress-popular-posts'); ?></label> <br />
                <input type="text" id="<?php echo $this->get_field_id( 'post-start' ); ?>" name="<?php echo $this->get_field_name( 'post-start' ); ?>" value="<?php echo $instance['markup']['post-start']; ?>" class="widefat" style="width:80px!important" <?php echo ($instance['markup']['custom_html']) ? '' : 'disabled="disabled"' ?> /> <input type="text" id="<?php echo $this->get_field_id( 'post-end' ); ?>" name="<?php echo $this->get_field_name( 'post-end' ); ?>" value="<?php echo $instance['markup']['post-end']; ?>" class="widefat" style="width:80px!important" <?php echo ($instance['markup']['custom_html']) ? '' : 'disabled="disabled"' ?> /></p>
                <hr />
                <input type="checkbox" class="checkbox" <?php echo ($instance['markup']['pattern']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'pattern_active' ); ?>" name="<?php echo $this->get_field_name( 'pattern_active' ); ?>" /> <label for="<?php echo $this->get_field_id( 'pattern_active' ); ?>"><?php _e('Use content formatting tags', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('url'); ?>/wp-admin/options-general.php?page=wordpress-popular-posts/wordpress-popular-posts.php">?</a>]</small><br /><br />
                <p style="font-size:11px"><label for="<?php echo $this->get_field_id( 'pattern_form' ); ?>"><?php _e('Content format:', 'wordpress-popular-posts'); ?></label>
                <input type="text" id="<?php echo $this->get_field_id( 'pattern_form' ); ?>" name="<?php echo $this->get_field_name( 'pattern_form' ); ?>" value="<?php echo $instance['markup']['pattern']['form']; ?>" style="width:204px" <?php echo ($instance['markup']['pattern']['active']) ? '' : 'disabled="disabled"' ?> /></p>
            </fieldset>
            <?php // end form	
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
			$table = $wpdb->prefix . 'popularpostsdata';
			
			// update popularpostsdata table
			$exists = $wpdb->get_results("SELECT postid FROM $table WHERE postid = '".$id."'");							
			if ($exists) {
				$result = $wpdb->query("UPDATE $table SET last_viewed = NOW(), pageviews = pageviews + 1 WHERE postid = '$id'");
			} else {				
				$result = $wpdb->query("INSERT INTO $table (postid, day, last_viewed) VALUES ('".$id."', NOW(), NOW())");
			}
			
			// update popularpostsdatacache table
			$isincache = $wpdb->get_results("SELECT id FROM ".$table."cache WHERE id = '".$id."' AND day = CURDATE()");			
			if ($isincache) {
				$result2 = $wpdb->query("UPDATE ".$table."cache SET pageviews = pageviews + 1 WHERE id = '".$id."' AND day = CURDATE()");
			} else {		
				$result2 = $wpdb->query("INSERT INTO ".$table."cache (id, day) VALUES ('".$id."', CURDATE())");
			}
			
			die();
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
			
			// set table name
			$table = $wpdb->prefix . "popularpostsdata";
			
			// does popularpostsdata table exists?
			if ( $wpdb->get_var("SHOW TABLES LIKE '$table'") != $table ) { // fresh setup
				// create tables popularpostsdata and popularpostsdatacache
				$sql = "CREATE TABLE " . $table . " ( UNIQUE KEY id (postid), postid int(10) NOT NULL, day datetime NOT NULL default '0000-00-00 00:00:00', last_viewed datetime NOT NULL default '0000-00-00 00:00:00', pageviews int(10) default 1 ); CREATE TABLE " . $table ."cache ( UNIQUE KEY id (id, day), id int(10) NOT NULL, day date NOT NULL, pageviews int(10) default 1 );";
				
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');				
				dbDelta($sql);				
			} else {
				$cache = $table . "cache";
				if ( $wpdb->get_var("SHOW TABLES LIKE '$cache'") != $cache ) {
					// someone is upgrading from version 1.5.x
					$sql = "CREATE TABLE " . $table ."cache ( UNIQUE KEY id (id, day), id int(10) NOT NULL, day date NOT NULL, pageviews int(10) default 1 );";
					
					require_once(ABSPATH . 'wp-admin/includes/upgrade.php');				
					dbDelta($sql);
				}
			}
		}
		
		// prints ajax script to theme's header
		function wpp_print_ajax() {
			// let's add jQuery
			wp_print_scripts('jquery');
			
			// create security token
			$nonce = wp_create_nonce('wpp-token');
			
			// get current post's ID
			global $wp_query;
			
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
				case 'daily':
					$range = $table."cache.day = CURDATE()";
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
				if ( $instance['range'] == 'all') {
					$fields .= "$table.pageviews AS 'pageviews' ";
				} else {
					if ( $sortby == 'avg_views' ) {
						$fields .= "(SUM(".$table."cache.pageviews)/(IF ( DATEDIFF(CURDATE(), MIN(".$table."cache.day)) > 0, DATEDIFF(CURDATE(), MIN(".$table."cache.day)), 1) )) AS 'avg_views' ";						
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
			if ( $instance['stats_tag']['date'] ) {
				if ( $fields != ', ' ) {
					$fields .= ", $wpdb->posts.post_date_gmt AS 'date_gmt'";
				} else {
					$fields .= "$wpdb->posts.post_date_gmt AS 'date_gmt'";
				}
			}			
			
			if (strlen($fields) == 2) $fields = '';

			if ( $instance['range'] == 'all') {
				$join = "LEFT JOIN $table ON $wpdb->posts.ID = $table.postid";
				$force_pv = "AND ".$table.".pageviews > 0 ";
			} else {
				$join = "RIGHT JOIN ".$table."cache ON $wpdb->posts.ID = ".$table."cache.id";
				$force_pv = "";
			}
			
			// Category excluding snippet suggested by user erik.straud at http://wordpress.org/support/topic/272363?replies=10#post-1331386
			// Modified it a bit to fit my plugin better.
			// Thanks, erik!
			if ( $instance['exclude-cats']['active'] && !empty($instance['exclude-cats']['cats']) ) {
				$exclude = " AND $wpdb->posts.ID NOT IN (SELECT object_id FROM $wpdb->term_relationships WHERE term_taxonomy_id IN (".$instance['exclude-cats']['cats'].")) ";
			} else {
				$exclude = "";
			}
			
			$mostpopular = $wpdb->get_results("SELECT $wpdb->posts.ID, $wpdb->posts.post_title $fields FROM $wpdb->posts $join WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_password = '' AND $range $force_pv $nopages $exclude GROUP BY $wpdb->posts.ID ORDER BY $sortby DESC LIMIT " . $instance['limit'] . "");
			
			$content = '';
			
			if ( !is_array($mostpopular) || empty($mostpopular) ) {
				$content .= "<p>".__('Sorry. No data so far.', 'wordpress-popular-posts')."</p>";
			} else {
				if ($instance['markup']['custom_html']) {
					$content .= "\n" . htmlspecialchars_decode($instance['markup']['wpp-start'], ENT_QUOTES) . "<!-- Wordpress Popular Posts Plugin v". $this->version ." -->"."\n";
				} else {
					$content .= "\n"."<ul><!-- Wordpress Popular Posts Plugin v". $this->version ." -->"."\n";
				}
				//$stat_count = 0;
				
				foreach ($mostpopular as $wppost) {					
				
					$post_stats = "";
					$stats = "";
					$thumb = "";
					$data = array();
					
					// get post title
					/* qTranslate integration check */
					($this->qTrans) ? $tit = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($wppost->post_title) : $tit = $wppost->post_title;
					
					if ( $instance['shorten_title']['active'] && (strlen($tit) > $instance['shorten_title']['length'])) {
						$tit = $this->truncate($tit, $instance['shorten_title']['length'] + 3, '', true, true) . "...";
					}
					$post_title = "<span class=\"wpp-post-title\">" . stripslashes($tit) . "</span>";
					
					// get post excerpt
					if ( $instance['post-excerpt']['active'] ) {
						if ($instance['markup']['pattern']['active']) {
							$post_content = "<span class=\"wpp-excerpt\">" . $this->get_summary($wppost->ID, $instance) . "</span>";
						} else {
							$post_content = ": <span class=\"wpp-excerpt\">" . $this->get_summary($wppost->ID, $instance) . "...</span>";
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
						} else if ($instance['order_by'] == 'avg' && $instance['range'] != 'daily') {							
							$pageviews = ceil($wppost->avg_views);
							$views_text = __(' view(s) per day', 'wordpress-popular-posts');
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
							$post_stats .= " | by <span class=\"wpp-author\">".$wppost->display_name."</span>";
						} else {					
							$post_stats .= "by <span class=\"wpp-author\">".$wppost->display_name."</span>";
						}
					}
					if ( $instance['stats_tag']['date'] ) {
						if ($post_stats != "") {
							$post_stats .= " | <span class=\"wpp-date\">posted on ".date("F, j",strtotime($wppost->date_gmt))."</span>";
						} else {					
							$post_stats .= "<span class=\"wpp-date\">posted on ".date("F, j",strtotime($wppost->date_gmt))."</span>";
						}
					}
					
					if (!empty($post_stats)) {
						$stats = ' <span class="post-stats">' . $post_stats . '</span> ';
					}
					
					// get thumbnail
					if ($instance['thumbnail']['active'] && $this->thumb ) {
						// let's try to retrieve the first image of the current post
						$img = $this->get_img($wppost->ID);
						if ( (!$img || empty($img)) ) {
							$thumb = "";
						} else {
							$thumb = "<a href=\"".get_permalink($wppost->ID)."\" title=\"". htmlspecialchars(stripslashes($tit)) ."\"><img src=\"". $this->pluginDir . "/scripts/timthumb.php?src=". $img[1] ."&amp;h=".$instance['thumbnail']['height']."&amp;w=".$instance['thumbnail']['width']."&amp;zc=1\" alt=\"".$wppost->post_title."\" border=\"0\" class=\"wpp-thumbnail\" width=\"".$instance['thumbnail']['width']."\" height=\"".$instance['thumbnail']['height']."\" "."/></a>";
						}
												
					}
					
					// get rating
					if ($instance['rating'] && $this->postRating) {
						$rating = '<span class="wpp-rating">'.the_ratings_results($wppost->ID).'</span>';
					} else {
						$rating = '';
					}
					
					$data = array(
						'title' => '<a href="'.get_permalink($wppost->ID).'" title="'. htmlspecialchars(stripslashes($tit)) .'">'. htmlspecialchars_decode($post_title) .'</a>',
						'summary' => $post_content,
						'stats' => $stats,
						'img' => $thumb,
						'id' => $wppost->ID
					);		
					
					// build custom layout
					if ($instance['markup']['custom_html']) {
						if ($instance['markup']['pattern']['active']) {
							$content .= htmlspecialchars_decode($instance['markup']['post-start'], ENT_QUOTES) . $this->format_content($instance['markup']['pattern']['form'], $data, $instance['rating']) . htmlspecialchars_decode($instance['markup']['post-end'], ENT_QUOTES) . "\n";
						} else {
							$content .= htmlspecialchars_decode($instance['markup']['post-start'], ENT_QUOTES) . $thumb . '<a href="'.get_permalink($wppost->ID).'" title="'. htmlspecialchars(stripslashes($tit)) .'">'. htmlspecialchars_decode($post_title) .'</a>'.$post_content.' '. $stats . $rating . htmlspecialchars_decode($instance['markup']['post-end'], ENT_QUOTES) . "\n";
						}
					} else {
						$content .= '<li>'. $thumb .'<a href="'. get_permalink($wppost->ID) .'" title="'. htmlspecialchars(stripslashes($tit)) .'">'. htmlspecialchars_decode($post_title) .'</a>'. $post_content .' '. $stats . $rating .'</li>' . "\n";
					}
				}			
				
				if ($instance['markup']['custom_html']) {
					$content .= htmlspecialchars_decode($instance['markup']['wpp-end'], ENT_QUOTES) . "<!-- End Wordpress Popular Posts Plugin v". $this->version ." -->"."\n";
				} else {
					$content .= "\n"."</ul><!-- End Wordpress Popular Posts Plugin v". $this->version ." -->"."\n";
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
			
			if (strlen($excerpt) <= $instance['post-excerpt']['length']) {
				$excerpt = strip_tags($excerpt, '<a><b><i><strong><em>');
			} else {				
				$excerpt = $this->truncate($excerpt, $instance['post-excerpt']['length'], '', true, true);
			}
			
			return $excerpt;
		}
		
		// gets the first image of post / page
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
		// slightly modified by Hector Cabrera
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
						$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
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
					$truncate = substr($text, 0, $length); // modified by Hector Cabrera
				}
			}
			// if the words shouldn't be cut in the middle...
			if (!$exact) {
				// ...search the last occurance of a space...
				$spacepos = strrpos($truncate, ' ');
				if (isset($spacepos)) {
					// ...and cut the text in this position
					$truncate = substr($truncate, 0, $spacepos);
				}
			}
			// add the defined ending to the text
			$truncate .= $ending;
			if($considerHtml) {
				// close all unclosed html-tags
				foreach ($open_tags as $tag) {
					$truncate .= '</' . $tag . '>';
				}				
				$truncate = strip_tags($truncate, '<a><b><i><strong><em>'); // added by Hector Cabrera
			}
			return $truncate;
		}
		
		// plugin localization (Credits: Aleksey Timkov at@uadeveloper.com)
		function wpp_textdomain() {
			load_plugin_textdomain('wordpress-popular-posts', 'wp-content/plugins/wordpress-popular-posts');
		}
		
		// insert Wordpress Popular Posts' stylesheet in theme's head section, just in case someone needs it
		function wpp_print_stylesheet() {
			echo "\n"."<!-- Wordpress Popular Posts v". $this->version ." -->"."\n".'<link rel="stylesheet" href="'.$this->pluginDir.'/style/wpp.css" type="text/css" media="screen" />'."\n"."<!-- Wordpress Popular Posts v". $this->version ." -->"."\n";	
		}
		
		// create Wordpress Popular Posts' maintenance page
		function wpp_maintenance_page() {
			require (dirname(__FILE__) . '/maintenance.php');
		}	
		function add_wpp_maintenance_page() {
			add_submenu_page('options-general.php', 'Wordpress Popular Posts', 'Wordpress Popular Posts', 10, __FILE__, array(&$this, 'wpp_maintenance_page'));
		}
		
		// version update warning
		function wpp_update_warning() {
			$msg = '<div id="wpp-message" class="error fade"><p>'.__('Your Wordpress version is too old. Wordpress Popular Posts Plugin requires at least version 2.8 to function correctly. Please update your blog via Tools &gt; Upgrade.', 'wordpress-popular-posts').'</p></div>';
			echo trim($msg);
		}
		
		// cache maintenance
		function wpp_cache_schedule() {
			$tomorrow = time() + 86400;
			$midnight  = mktime(0, 0, 0, 
				date("m", $tomorrow), 
				date("d", $tomorrow), 
				date("Y", $tomorrow));
			wp_schedule_event($midnight, 'daily', 'wpp_cache_event');
		}
		
		function wpp_cache_maintenance() {
			global $wpdb;
			$wpdb->query("DELETE FROM ".$wpdb->prefix."popularpostscache WHERE day < DATE_SUB(CURDATE(), INTERVAL 30 DAY);");
		}
		
		// plugin deactivation
		function wpp_deactivation() {
			wp_clear_scheduled_hook('wpp_cache_event');
			remove_shortcode('wpp');
			remove_shortcode('WPP');
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
				'cats_to_exclude' => '',
				'thumbnail_width' => 0,
				'thumbnail_height' => 0,
				'rating' => false,
				'stats_comments' => true,
				'stats_views' => false,
				'stats_author' => false,
				'stats_date' => false,				
				'wpp_start' => '<ul>',
				'wpp_end' => '</ul>',
				'post_start' => '<li>',
				'post_end' => '</li>',
				'header_start' => '<h2>',
				'header_end' => '</h2>',
				'do_pattern' => false,
				'pattern_form' => '{image} {title}: {summary} {stats}'
			), $atts ) );			
			
			$shortcode_ops = array(
				'title' => strip_tags($header),
				'limit' => empty($limit) ? 10 : (is_numeric($limit)) ? (($limit > 0) ? $limit : 10) : 10,
				'range' => empty($range) ? 'daily' : $range,
				'order_by' => empty($order_by) ? 'comments' : ($order_by != 'comments' || $order_by =! 'views' || $order_by =! 'avg') ? 'comments' : $range,
				'pages' => empty($pages) ? false : $pages,
				'shorten_title' => array(
					'active' => empty($title_length) ? false : (is_numeric($title_length)) ? (($title_length > 0) ? true : false) : false,
					'length' => empty($title_length) ? 0 : (is_numeric($title_length)) ? $title_length : 0 
				),
				'post-excerpt' => array(
					'active' => empty($excerpt_length) ? false : (is_numeric($excerpt_length)) ? (($excerpt_length > 0) ? true : false) : false,
					'length' => empty($excerpt_length) ? 0 : (is_numeric($excerpt_length)) ? $excerpt_length : 0
				),
				'exclude-cats' => array(
					'active' => empty($cats_to_exclude) ? false : (ctype_digit(str_replace(",", "", $cats_to_exclude))) ? true : false,
					'cats' => empty($cats_to_exclude) ? '' : (ctype_digit(str_replace(",", "", $cats_to_exclude))) ? $cats_to_exclude : ''
				),		
				'thumbnail' => array(
					'active' => empty($thumbnail_width) ? false : (is_numeric($thumbnail_width)) ? (($thumbnail_width > 0) ? true : false) : false,
					'width' => empty($thumbnail_width) ? 0 : (is_numeric($thumbnail_width)) ? $thumbnail_width : 0,
					'height' => empty($thumbnail_height) ? 0 : (is_numeric($thumbnail_height)) ? $thumbnail_height : 0
				),
				'rating' => empty($rating) ? false : is_bool($rating) ? $rating : false,
				'stats_tag' => array(
					'comment_count' => empty($stats_comments) ? false : $stats_comments,
					'views' => empty($stats_views) ? false : $stats_views,
					'author' => empty($stats_author) ? false : $stats_author,
					'date' => empty($stats_date) ? false : $stats_date
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
						'active' => empty($do_pattern) ? false : (is_bool($do_pattern)) ? $do_pattern : false,
						'form' => empty($pattern_form) ? '{image} {title}: {summary} {stats}' : $pattern_form
					)
				)
			);
			
			// is there a title defined by user?
			if (!empty($header) && !empty($header_start) && !empty($header_end)) {
				echo $header_start . $header . $header_end;
			}
			
			// print popular posts list
			echo $this->get_popular_posts($shortcode_ops, false);
			
		}
	}
}

/**
 * Wordpress Popular Posts template tags for use in themes.
 */

// gets views count
function wpp_get_views($id = NULL) {
	// have we got an id?
	if ( empty($id) || is_null($id) || !is_numeric($id) ) {
		return false;
	} else {		
		global $wpdb;
		$table_name = $wpdb->prefix . "popularpostsdata";
		
		$result = $wpdb->get_results("SELECT pageviews AS 'views' FROM $table_name WHERE postid = '$id'", ARRAY_A);
		
		if ( !is_array($result) || empty($result) ) {
			return "0";
		} else {
			return $result[0]['views'];
		}
	}
}

// gets popular posts
function get_mostpopular($args = NULL) {

	if (is_null($args)) {
		return do_shortcode('[wpp]');
	} else {
		$atts = trim(str_replace("&", " ", $args));
		return do_shortcode('[wpp '.$atts.']');
	}
}


/**
 * Wordpress Popular Posts 2.0.1 Changelog.
 */

/*
 = 2.0.1 =
* Post title excerpt now includes html entities. Characters like AÄÖ should display properly now.
* Post excerpt has been improved. Now it supports the following HTML tags: <a><b><i><strong><em>.
* Template tag wpp_get_views() added. Retrieves the views count of a single post.
* Template tag get_mostpopular() re-added. Parameter support included.
* Shortcode bug fixed (range was always "daily" no matter what option was being selected by the user).
*/