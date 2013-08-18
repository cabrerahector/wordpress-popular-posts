<?php
/*
Plugin Name: Wordpress Popular Posts
Plugin URI: http://wordpress.org/extend/plugins/wordpress-popular-posts
Description: Showcases your most popular posts to your visitors on your blog's sidebar. Use Wordpress Popular Posts as a widget or place it anywhere on your theme using <strong>&lt;?php wpp_get_mostpopular(); ?&gt;</strong>
Version: 2.3.7
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
		var $version = "2.3.7";
		var $qTrans = false;
		var $postRating = false;
		var $thumb = false;
		var $pluginDir = "";
		var $charset = "UTF-8";
		var $magicquotes = false;
		var $default_thumbnail = "";
		var $user_ops = array();
		var $defaults = array(
			'title' => '',
			'limit' => 10,
			'range' => 'daily',
			'order_by' => 'views',
			'post_type' => 'post,page',
			'pid' => '',
			'author' => '',
			'cat' => '',
			'shorten_title' => array(
				'active' => false,
				'length' => 25,
				'words'	=> false
			),
			'post-excerpt' => array(
				'active' => false,
				'length' => 55,
				'keep_format' => false,
				'words' => false
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
				),
				'category' => false
			),
			'markup' => array(
				'custom_html' => false,
				'wpp-start' => '&lt;ul class="wpp-list"&gt;',
				'wpp-end' => '&lt;/ul&gt;',
				'post-html' => '&lt;li&gt;{thumb} {title} {stats}&lt;/li&gt;',
				'post-start' => '&lt;li&gt;',
				'post-end' => '&lt;/li&gt;',
				'title-start' => '&lt;h2&gt;',
				'title-end' => '&lt;/h2&gt;'
			)
		);
		var $wpp_user_settings_def = array(
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
					'field' => '',
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

		/**
		 * WPP's Constructor
		 * Since 1.4.0
		 */
		function WordpressPopularPosts() {
			global $wp_version;

			// Set default title
			$this->defaults['title'] = __('Popular Posts', 'wordpress-popular-posts');

			// widget settings
			$widget_ops = array( 'classname' => 'popular-posts', 'description' => __('The most Popular Posts on your blog.', 'wordpress-popular-posts') );

			// widget control settings
			$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => 'wpp' );

			// create the widget
			$this->WP_Widget( 'wpp', 'Wordpress Popular Posts', $widget_ops, $control_ops );

			// set plugin path
			if (empty($this->pluginDir)) $this->pluginDir = plugin_dir_url(__FILE__);

			// set charset
			$this->charset = get_bloginfo('charset');

			// detect PHP magic quotes
			$this->magicquotes = get_magic_quotes_gpc();

			// get user options
			$this->user_ops = get_option('wpp_settings_config');
			if ( !$this->user_ops ) {
				add_option('wpp_settings_config', $this->wpp_user_settings_def);
				$this->user_ops = $this->wpp_user_settings_def;
			}

			// set default thumbnail
			$this->default_thumbnail = $this->pluginDir . "no_thumb.jpg";
			$this->wpp_user_settings_def['tools']['thumbnail']['default'] = $this->default_thumbnail;

			if ( !empty($this->user_ops['tools']['thumbnail']['default']) ) {
				$this->default_thumbnail = $this->user_ops['tools']['thumbnail']['default'];
			}

			// set version and check for upgrades
			$wpp_ver = get_option('wpp_ver');
			if ( !$wpp_ver ) {
				add_option('wpp_ver', $this->version);
			} else if ( version_compare($wpp_ver, $this->version, '<') ) {
				$this->wpp_upgrade();
			}

			// print stylesheet
			if ($this->user_ops['tools']['css']) {
				add_action('get_header', array(&$this, 'wpp_print_stylesheet'));
			}

			//wpp_ajax_get_popular
			add_action('wp_ajax_wpp_ajax_get_popular', array(&$this, 'ajax_getpopular'));
			add_action('wp_ajax_nopriv_wpp_ajax_get_popular', array(&$this, 'ajax_getpopular'));

			if ($this->user_ops['tools']['ajax']) {
				remove_action('the_content', array(&$this,'wpp_update') );
				// add ajax update to wp_ajax_ hook
				if ( isset($this->user_ops['tools']['log_loggedin']) && $this->user_ops['tools']['log_loggedin'] == 1 ) {
					add_action('wp_ajax_wpp_update', array(&$this, 'wpp_ajax_update'));
				}
				add_action('wp_ajax_nopriv_wpp_update', array(&$this, 'wpp_ajax_update'));
				add_action('wp_head', array(&$this, 'wpp_print_ajax'));

			} else {
				// stop Wordpress from preloading next post and thus calling single.php twice!
				remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');

				if ( isset($this->user_ops['tools']['log_loggedin']) && $this->user_ops['tools']['log_loggedin'] == 1 ) {
					remove_action('wp_ajax_wpp_update', array(&$this, 'wpp_ajax_update'));
				}
				remove_action('wp_ajax_nopriv_wpp_update', array(&$this, 'wpp_ajax_update'));
				remove_action('wp_head', array(&$this, 'wpp_print_ajax'));
				// add update action, no ajax
				add_action('the_content', array(&$this,'wpp_update') );
			}

			// add ajax table truncation to wp_ajax_ hook
			add_action('wp_ajax_wpp_clear_cache', array(&$this, 'wpp_clear_data'));
			add_action('wp_ajax_wpp_clear_all', array(&$this, 'wpp_clear_data'));

			// activate textdomain for translations
			add_action('init', array(&$this, 'wpp_textdomain'));

			// activate admin page
			add_action('admin_menu', array(&$this, 'add_wpp_admin'));
			// enqueue scripts
			add_action('admin_enqueue_scripts', array(&$this, 'wpp_admin_enqueue_scripts'));
			add_action('admin_init', array(&$this, 'wpp_tb_setup') );

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
			if (version_compare($wp_version, '3.3', '<')) add_action('admin_notices', array(&$this, 'wpp_update_warning'));

			// PHP version check
			if ( version_compare(phpversion(), '5.2.0', '<') ) add_action('admin_notices', array(&$this, 'php_update_warning'));

			// qTrans plugin support
			if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) $this->qTrans = true;

			// WP-Post Ratings plugin support
			if (function_exists('the_ratings_results')) $this->postRating = true;

			// Can we create thumbnails?
			if (extension_loaded('gd') && function_exists('gd_info') && version_compare(phpversion(), '5.2.0', '>=')) $this->thumb = true;

			// shortcode
			if( function_exists('add_shortcode') ){
				add_shortcode('wpp', array(&$this, 'wpp_shortcode'));
				add_shortcode('WPP', array(&$this, 'wpp_shortcode'));
			}

			// add plugin action link
			add_filter('plugin_action_links', array(&$this, 'wpp_action_links'), 10, 2);
		}

		/**
		 * Builds WPP's widget
		 * Since 2.0.0
		 */
		function widget($args, $instance) {
			extract($args);
			//echo $widget_id;

			echo "<!-- Wordpress Popular Posts Plugin v". $this->version ." [W] [".$instance['range']."] [".$instance['order_by']."]". (($instance['markup']['custom_html']) ? ' [custom]' : ' [regular]') ." -->"."\n";

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

			if ( $this->user_ops['tools']['ajax'] ) {
				?>
                <script type="text/javascript">
                    /* <![CDATA[ */
                    jQuery(document).ready(function(){
                        jQuery.get('<?php echo admin_url('admin-ajax.php'); ?>', {action: 'wpp_ajax_get_popular', id: '<?php echo $this->number; ?>'}, function(data){
							jQuery('#<?php echo $widget_id; ?>').append(data);
						});
                    });
                    /* ]]> */
                </script>
                <?php
			} else {
				echo $this->get_popular_posts( $instance );
			}

			echo $after_widget . "\n";
			echo "<!-- End Wordpress Popular Posts Plugin v". $this->version ." -->"."\n";
		}

		/**
		 * Updates each widget instance when user clicks the "save" button
		 * Since 2.0.0
		 */
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

			$instance['pid'] = implode(",", array_filter(explode(",", preg_replace( '|[^0-9,]|', '', $new_instance['pid'] ))));

			$instance['cat'] = implode(",", array_filter(explode(",", preg_replace( '|[^0-9,-]|', '', $new_instance['cat'] ))));
			$instance['author'] = implode(",", array_filter(explode(",", preg_replace( '|[^0-9,]|', '', $new_instance['uid'] ))));

			$instance['shorten_title']['active'] = (bool) $new_instance['shorten_title-active'];
			$instance['shorten_title']['length'] = is_numeric($new_instance['shorten_title-length']) ? $new_instance['shorten_title-length'] : 25;
			$instance['shorten_title']['words'] = $new_instance['shorten_title-words'];
			$instance['post-excerpt']['active'] = (bool) $new_instance['post-excerpt-active'];
			$instance['post-excerpt']['length'] = is_numeric($new_instance['post-excerpt-length']) ? $new_instance['post-excerpt-length'] : 55;
			$instance['post-excerpt']['keep_format'] = $new_instance['post-excerpt-format'];
			$instance['post-excerpt']['words'] = $new_instance['post-excerpt-words'];

			if ($this->thumb) { // can create thumbnails
				$instance['thumbnail']['active'] = (bool) $new_instance['thumbnail-active'];

				if (is_numeric($new_instance['thumbnail-width']) && is_numeric($new_instance['thumbnail-height'])) {
					$instance['thumbnail']['width'] = $new_instance['thumbnail-width'];
					$instance['thumbnail']['height'] = $new_instance['thumbnail-height'];
				} else {
					$instance['thumbnail']['width'] = 15;
					$instance['thumbnail']['height'] = 15;
				}

			} else { // cannot create thumbnails
				$instance['thumbnail']['active'] = false;
				$instance['thumbnail']['width'] = 15;
				$instance['thumbnail']['height'] = 15;
			}

			if ( isset($instance['thumbnail']['thumb_selection']) )
				unset( $instance['thumbnail']['thumb_selection'] );

			$instance['rating'] = (bool) $new_instance['rating'];
			$instance['stats_tag']['comment_count'] = (bool) $new_instance['comment_count'];
			$instance['stats_tag']['views'] = (bool) $new_instance['views'];
			$instance['stats_tag']['author'] = (bool) $new_instance['author'];
			$instance['stats_tag']['date']['active'] = (bool) $new_instance['date'];
			$instance['stats_tag']['date']['format'] = empty($new_instance['date_format']) ? 'F j, Y' : $new_instance['date_format'];
			$instance['stats_tag']['category'] = (bool) $new_instance['category'];
			$instance['markup']['custom_html'] = $new_instance['custom_html'];
			$instance['markup']['wpp-start'] = empty($new_instance['wpp-start']) ? '&lt;ul&gt;' : htmlspecialchars( $new_instance['wpp-start'], ENT_QUOTES );
			$instance['markup']['wpp-end'] = empty($new_instance['wpp-end']) ? '&lt;/ul&gt;' : htmlspecialchars( $new_instance['wpp-end'], ENT_QUOTES );
			$instance['markup']['post-html'] = empty ($new_instance['post-html']) ? '&lt;li&gt;{thumb} {title} {stats}&lt;/li&gt;' : htmlspecialchars( $new_instance['post-html'], ENT_QUOTES );
			$instance['markup']['post-start'] = empty ($new_instance['post-start']) ? '&lt;li&gt;' : htmlspecialchars( $new_instance['post-start'], ENT_QUOTES );
			$instance['markup']['post-end'] = empty ($new_instance['post-end']) ? '&lt;/li&gt;' : htmlspecialchars( $new_instance['post-end'], ENT_QUOTES );
			$instance['markup']['title-start'] = empty($new_instance['title-start']) ? '' : htmlspecialchars( $new_instance['title-start'], ENT_QUOTES );
			$instance['markup']['title-end'] = empty($new_instance['title-end']) ? '' : htmlspecialchars( $new_instance['title-end'], ENT_QUOTES );

			return $instance;
		}

		/**
		 * WPP widget's form
		 * Since 2.0.0
		 */
		function form($instance) {

			// update instance's settings
			/*echo "<pre>"; print_r($instance); echo "</pre>";*/
			$instance = $this->array_merge_recursive_distinct($this->defaults, $instance);
			/*echo "<pre>"; print_r($instance); echo "</pre>";*/

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
				<div style="display:<?php if ($this->postRating) : ?>block<?php else: ?>none<?php endif; ?>">
                	<input type="checkbox" class="checkbox" <?php echo ($instance['rating']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'rating' ); ?>" name="<?php echo $this->get_field_name( 'rating' ); ?>" /> <label for="<?php echo $this->get_field_id( 'rating' ); ?>"><?php _e('Display post rating', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
                </div>
                <input type="checkbox" class="checkbox" <?php echo ($instance['shorten_title']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'shorten_title-active' ); ?>" name="<?php echo $this->get_field_name( 'shorten_title-active' ); ?>" /> <label for="<?php echo $this->get_field_id( 'shorten_title-active' ); ?>"><?php _e('Shorten title', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
                <div style="display:<?php if ($instance['shorten_title']['active']) : ?>block<?php else: ?>none<?php endif; ?>">
                    <br />
                    <label for="<?php echo $this->get_field_id( 'shorten_title-length' ); ?>"><?php _e('Shorten title to', 'wordpress-popular-posts'); ?> <input id="<?php echo $this->get_field_id( 'shorten_title-length' ); ?>" name="<?php echo $this->get_field_name( 'shorten_title-length' ); ?>" value="<?php echo $instance['shorten_title']['length']; ?>" class="widefat" style="width:50px!important" /></label><br />
                    <label for="<?php echo $this->get_field_id( 'shorten_title-words' ); ?>"><input type="radio" name="<?php echo $this->get_field_name( 'shorten_title-words' ); ?>" value="0" <?php echo (!isset($instance['shorten_title']['words']) || !$instance['shorten_title']['words']) ? 'checked="checked"' : ''; ?> /> <?php _e('characters', 'wordpress-popular-posts'); ?></label><br />
                    <label for="<?php echo $this->get_field_id( 'shorten_title-words' ); ?>"><input type="radio" name="<?php echo $this->get_field_name( 'shorten_title-words' ); ?>" value="1" <?php echo (isset($instance['shorten_title']['words']) && $instance['shorten_title']['words']) ? 'checked="checked"' : ''; ?> /> <?php _e('words', 'wordpress-popular-posts'); ?></label><br /><br />
                </div>
                <input type="checkbox" class="checkbox" <?php echo ($instance['post-excerpt']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'post-excerpt-active' ); ?>" name="<?php echo $this->get_field_name( 'post-excerpt-active' ); ?>" /> <label for="<?php echo $this->get_field_id( 'post-excerpt-active' ); ?>"><?php _e('Display post excerpt', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
                <div style="display:<?php if ($instance['post-excerpt']['active']) : ?>block<?php else: ?>none<?php endif; ?>">
                    <fieldset class="widefat">
                        <legend><?php _e('Excerpt Properties', 'wordpress-popular-posts'); ?></legend><br />
                        &nbsp;&nbsp;<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'post-excerpt-format' ); ?>" name="<?php echo $this->get_field_name( 'post-excerpt-format' ); ?>" <?php echo ($instance['post-excerpt']['keep_format']) ? 'checked="checked"' : ''; ?> /> <label for="<?php echo $this->get_field_id( 'post-excerpt-format' ); ?>"><?php _e('Keep text format and links', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br /><br />
                        &nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'post-excerpt-length' ); ?>"><?php _e('Excerpt length:', 'wordpress-popular-posts'); ?> <input id="<?php echo $this->get_field_id( 'post-excerpt-length' ); ?>" name="<?php echo $this->get_field_name( 'post-excerpt-length' ); ?>" value="<?php echo $instance['post-excerpt']['length']; ?>" class="widefat" style="width:50px!important" /></label><br  />

                        &nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'post-excerpt-words' ); ?>"><input type="radio" name="<?php echo $this->get_field_name( 'post-excerpt-words' ); ?>" value="0" <?php echo (!isset($instance['post-excerpt']['words']) || !$instance['post-excerpt']['words']) ? 'checked="checked"' : ''; ?> /> <?php _e('characters', 'wordpress-popular-posts'); ?></label><br />
                        &nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'post-excerpt-words' ); ?>"><input type="radio" name="<?php echo $this->get_field_name( 'post-excerpt-words' ); ?>" value="1" <?php echo (isset($instance['post-excerpt']['words']) && $instance['post-excerpt']['words']) ? 'checked="checked"' : ''; ?> /> <?php _e('words', 'wordpress-popular-posts'); ?></label><br /><br />

                    </fieldset>
                    <br />
                </div>
            </fieldset>
            <br />

            <fieldset class="widefat">
                <legend><?php _e('Filters:', 'wordpress-popular-posts'); ?> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small></legend><br />
                &nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php _e('Post type(s):', 'wordpress-popular-posts'); ?></label><br />
                &nbsp;&nbsp;<input id="<?php echo $this->get_field_id( 'post_type' ); ?>" name="<?php echo $this->get_field_name( 'post_type' ); ?>" value="<?php echo $instance['post_type']; ?>" class="widefat" style="width:150px" /><br /><br />
                &nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'pid' ); ?>"><?php _e('Post(s) ID(s) to exclude:', 'wordpress-popular-posts'); ?></label><br />
                &nbsp;&nbsp;<input id="<?php echo $this->get_field_id( 'pid' ); ?>" name="<?php echo $this->get_field_name( 'pid' ); ?>" value="<?php echo $instance['pid']; ?>" class="widefat" style="width:150px" /><br /><br />
                &nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'cat' ); ?>"><?php _e('Category(ies) ID(s):', 'wordpress-popular-posts'); ?></label><br />
                &nbsp;&nbsp;<input id="<?php echo $this->get_field_id( 'cat' ); ?>" name="<?php echo $this->get_field_name( 'cat' ); ?>" value="<?php echo $instance['cat']; ?>" class="widefat" style="width:150px" /><br /><br />
                &nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'uid' ); ?>"><?php _e('Author(s) ID(s):', 'wordpress-popular-posts'); ?></label><br />
                &nbsp;&nbsp;<input id="<?php echo $this->get_field_id( 'uid' ); ?>" name="<?php echo $this->get_field_name( 'uid' ); ?>" value="<?php echo $instance['author']; ?>" class="widefat" style="width:150px" /><br /><br />
            </fieldset>
            <br />

			<fieldset style="width:214px; padding:5px;"  class="widefat">
                <legend><?php _e('Thumbnail settings', 'wordpress-popular-posts'); ?></legend>
				<input type="checkbox" class="checkbox" <?php echo ($instance['thumbnail']['active'] && $this->thumb) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'thumbnail-active' ); ?>" name="<?php echo $this->get_field_name( 'thumbnail-active' ); ?>" /> <label for="<?php echo $this->get_field_id( 'thumbnail-active' ); ?>"><?php _e('Display post thumbnail', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
				<div style="display:<?php if ($instance['thumbnail']['active']) : ?>block<?php else: ?>none<?php endif; ?>">
                    <label for="<?php echo $this->get_field_id( 'thumbnail-width' ); ?>"><?php _e('Width:', 'wordpress-popular-posts'); ?></label>
                    <input id="<?php echo $this->get_field_id( 'thumbnail-width' ); ?>" name="<?php echo $this->get_field_name( 'thumbnail-width' ); ?>" value="<?php echo $instance['thumbnail']['width']; ?>"  class="widefat" style="width:30px!important" <?php echo ($this->thumb) ? '' : 'disabled="disabled"' ?> /> <?php _e('px', 'wordpress-popular-posts'); ?> <br />
                    <label for="<?php echo $this->get_field_id( 'thumbnail-height' ); ?>"><?php _e('Height:', 'wordpress-popular-posts'); ?></label>
                    <input id="<?php echo $this->get_field_id( 'thumbnail-height' ); ?>" name="<?php echo $this->get_field_name( 'thumbnail-height' ); ?>" value="<?php echo $instance['thumbnail']['height']; ?>"  class="widefat" style="width:30px!important" <?php echo ($this->thumb) ? '' : 'disabled="disabled"' ?> /> <?php _e('px', 'wordpress-popular-posts'); ?><br />
				</div>
			</fieldset>

            <br />
            <fieldset style="width:214px; padding:5px;"  class="widefat">
                <legend><?php _e('Stats Tag settings', 'wordpress-popular-posts'); ?></legend>
                <input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['comment_count']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'comment_count' ); ?>" name="<?php echo $this->get_field_name( 'comment_count' ); ?>" /> <label for="<?php echo $this->get_field_id( 'comment_count' ); ?>"><?php _e('Display comment count', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
                <input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['views']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'views' ); ?>" name="<?php echo $this->get_field_name( 'views' ); ?>" /> <label for="<?php echo $this->get_field_id( 'views' ); ?>"><?php _e('Display views', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
                <input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['author']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'author' ); ?>" name="<?php echo $this->get_field_name( 'author' ); ?>" /> <label for="<?php echo $this->get_field_id( 'author' ); ?>"><?php _e('Display author', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
                <input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['date']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'date' ); ?>" name="<?php echo $this->get_field_name( 'date' ); ?>" /> <label for="<?php echo $this->get_field_id( 'date' ); ?>"><?php _e('Display date', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small>
				<div style="display:<?php if ($instance['stats_tag']['date']['active']) : ?>block<?php else: ?>none<?php endif; ?>">
                	<fieldset class="widefat">
                    	<legend><?php _e('Date Format', 'wordpress-popular-posts'); ?></legend>
                        <label title='F j, Y'><input type='radio' name='<?php echo $this->get_field_name( 'date_format' ); ?>' value='F j, Y' <?php echo ($instance['stats_tag']['date']['format'] == 'F j, Y') ? 'checked="checked"' : ''; ?> /><?php echo date_i18n('F j, Y', time()); ?></label><br />
                        <label title='Y/m/d'><input type='radio' name='<?php echo $this->get_field_name( 'date_format' ); ?>' value='Y/m/d' <?php echo ($instance['stats_tag']['date']['format'] == 'Y/m/d') ? 'checked="checked"' : ''; ?> /><?php echo date_i18n('Y/m/d', time()); ?></label><br />
                        <label title='m/d/Y'><input type='radio' name='<?php echo $this->get_field_name( 'date_format' ); ?>' value='m/d/Y' <?php echo ($instance['stats_tag']['date']['format'] == 'm/d/Y') ? 'checked="checked"' : ''; ?> /><?php echo date_i18n('m/d/Y', time()); ?></label><br />
                        <label title='d/m/Y'><input type='radio' name='<?php echo $this->get_field_name( 'date_format' ); ?>' value='d/m/Y' <?php echo ($instance['stats_tag']['date']['format'] == 'd/m/Y') ? 'checked="checked"' : ''; ?> /><?php echo date_i18n('d/m/Y', time()); ?></label><br />
                    </fieldset>
                </div>
                <br /><input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['category']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>" /> <label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e('Display category', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
            </fieldset>
            <br />

            <fieldset style="width:214px; padding:5px;"  class="widefat">
                <legend><?php _e('HTML Markup settings', 'wordpress-popular-posts'); ?></legend>
                <input type="checkbox" class="checkbox" <?php echo ($instance['markup']['custom_html']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'custom_html' ); ?>" name="<?php echo $this->get_field_name( 'custom_html' ); ?>" /> <label for="<?php echo $this->get_field_id( 'custom_html' ); ?>"><?php _e('Use custom HTML Markup', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=wpp_admin" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
                <div style="display:<?php if ($instance['markup']['custom_html']) : ?>block<?php else: ?>none<?php endif; ?>">
                    <br />
                    <p style="font-size:11px"><label for="<?php echo $this->get_field_id( 'title-start' ); ?>"><?php _e('Before / after title:', 'wordpress-popular-posts'); ?></label> <br />
                    <input type="text" id="<?php echo $this->get_field_id( 'title-start' ); ?>" name="<?php echo $this->get_field_name( 'title-start' ); ?>" value="<?php echo $instance['markup']['title-start']; ?>" class="widefat" style="width:80px!important" <?php echo ($instance['markup']['custom_html']) ? '' : 'disabled="disabled"' ?> /> <input type="text" id="<?php echo $this->get_field_id( 'title-end' ); ?>" name="<?php echo $this->get_field_name( 'title-end' ); ?>" value="<?php echo $instance['markup']['title-end']; ?>" class="widefat" style="width:80px!important" <?php echo ($instance['markup']['custom_html']) ? '' : 'disabled="disabled"' ?> /></p>
                    <p style="font-size:11px"><label for="<?php echo $this->get_field_id( 'wpp_start' ); ?>"><?php _e('Before / after Popular Posts:', 'wordpress-popular-posts'); ?></label> <br />
                    <input type="text" id="<?php echo $this->get_field_id( 'wpp-start' ); ?>" name="<?php echo $this->get_field_name( 'wpp-start' ); ?>" value="<?php echo $instance['markup']['wpp-start']; ?>" class="widefat" style="width:80px!important" <?php echo ($instance['markup']['custom_html']) ? '' : 'disabled="disabled"' ?> /> <input type="text" id="<?php echo $this->get_field_id( 'wpp-end' ); ?>" name="<?php echo $this->get_field_name( 'wpp-end' ); ?>" value="<?php echo $instance['markup']['wpp-end']; ?>" class="widefat" style="width:80px!important" <?php echo ($instance['markup']['custom_html']) ? '' : 'disabled="disabled"' ?> /></p>

                    <p style="font-size:11px"><label for="<?php echo $this->get_field_id( 'post-html' ); ?>"><?php _e('Post HTML Markup:', 'wordpress-popular-posts'); ?></label> <br />
                    <textarea class="widefat" rows="10" id="<?php echo $this->get_field_id( 'post-html' ); ?>" name="<?php echo $this->get_field_name( 'post-html' ); ?>"><?php echo $instance['markup']['post-html']; ?></textarea>
                </div>
            </fieldset>
            <?php
		}

		/**
		 * RRR Added to get local time as per WP settings
		 * Since 2.1.6
		 */
		function curdate() {
			return gmdate( 'Y-m-d', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ));
		}

		function now() {
			return current_time('mysql');
		}

		/**
		 * Calculates script execution time
		 * Since 2.3.0
		 */
		function microtime_float() {
			list( $msec, $sec ) = explode( ' ', microtime() );
			$microtime = (float) $msec + (float) $sec;
			return $microtime;
		}

		/**
		 * Updates popular posts data table via AJAX on post/page view
		 * Since 2.0.0
		 */
		function wpp_ajax_update() {
			$nonce = $_POST['token'];

			// is this a valid request?
			if ( !wp_verify_nonce($nonce, 'wpp-token') ) die("Invalid token");

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
				die( "OK. Execution time: " . $exec_time . " seconds" );
			} else {
				die( "Oops: " . $wpdb->print_error() );
			}
		}

		/**
		 * Updates popular posts data table on post/page view
		 * Since 2.3.0
		 */
		function wpp_update($content) {

			//if ( (is_single() || is_page()) && !is_user_logged_in() && !is_front_page() ) {
			if ( (is_single() || is_page()) && !is_front_page() && !is_preview() ) {

				if ( isset($this->user_ops['tools']['log_loggedin']) && $this->user_ops['tools']['log_loggedin'] == 0 && is_user_logged_in() )
					return $content;

				global $wpdb, $post;

				$table = $wpdb->prefix . 'popularpostsdata';
				$result = $wpdb->query("INSERT INTO {$table} (postid, day, last_viewed) VALUES ({$post->ID}, '{$this->now()}', '{$this->now()}') ON DUPLICATE KEY UPDATE last_viewed = '{$this->now()}', pageviews = pageviews + 1;");
				$result2 = $wpdb->query("INSERT INTO {$table}cache (id, day, day_no_time) VALUES ({$post->ID}, '{$this->now()}', '{$this->curdate()}') ON DUPLICATE KEY UPDATE pageviews = pageviews + 1, day = '{$this->now()}', day_no_time = '{$this->curdate()}';");

				if (!$result || !$result2) {
					print_r( $wpdb->print_error() );
				}

			}

			return $content;

		}

		/**
		 * Builds list via AJAX
		 * Since 2.3.3
		 */
		function ajax_getpopular() {

			if ( is_numeric($_GET['id']) && (intval($_GET['id']) == floatval($_GET['id'])) && ($_GET['id'] != '') ) {
				$id = $_GET['id'];
			} else {
				die("Invalid ID");
			}

			$settings = $this->get_settings();

			if ( isset($settings[$id]) ) {

				$instance = $settings[$id];
				echo $this->get_popular_posts( $instance );

			} else {

				echo "Invalid Widget ID";

			}

			die();

		}

		/**
		 * Clears WPP datacache table
		 * Since 2.0.0
		 */
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

		/**
		 * Installs WPP DB tables
		 * Since 2.0.0
		 */
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

				}
			}

			dbDelta($sql);
		}

		/**
		 * Checks for stuff that needs updating on plugin upgrade
		 * Since 2.3.1
		 */
		function wpp_upgrade() {

			update_option('wpp_ver', $this->version); // update wpp version in db

			// update user options
			$this->user_ops = $this->array_merge_recursive_distinct( $this->wpp_user_settings_def, $this->user_ops );
			update_option('wpp_settings_config', $this->user_ops);

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

		/**
		 * Prints AJAX script to wp_head()
		 * Since 2.0.0
		 */
		function wpp_print_ajax() {

			wp_print_scripts('jquery');

			// if we're on a page or post, load the script
			if ( (is_single() || is_page()) && !is_front_page() && !is_preview() ) {

				if ( isset($this->user_ops['tools']['log_loggedin']) && $this->user_ops['tools']['log_loggedin'] == 0 && is_user_logged_in() )
					return;

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

		/**
		 * Builds popular posts list
		 * Since 1.4.0
		 */
		function get_popular_posts($instance, $return = false) {

			// update instance's settings
			/*echo "<pre>"; print_r($instance); echo "</pre>";*/
			$instance = $this->array_merge_recursive_distinct($this->defaults, $instance);
			/*echo "<pre>"; print_r($instance); echo "</pre>";*/

			global $wpdb;
			$table = $wpdb->prefix . "popularpostsdata";
			$fields = "";
			$from = "";
			$where = "";
			$post_types = "";
			$pids = "";
			$cats = "";
			$authors = "";
			$content = "";

			// post filters
			// * post types - based on code seen at https://github.com/williamsba/WordPress-Popular-Posts-with-Custom-Post-Type-Support
			$types = explode(",", $instance['post_type']);
			$i = 0;
			$len = count($types);
			$sql_post_types = "";
			$join_cats = true;

			if ($len > 1) { // we are getting posts from more that one ctp
				foreach ( $types as $post_type ) {
					$sql_post_types .= "'" .$post_type. "'";

					if ($i != $len - 1) $sql_post_types .= ",";

					$i++;
				}

				$post_types = " p.post_type IN({$sql_post_types}) ";
			} else if ($len == 1) { // post from one ctp only

				$post_types = " p.post_type = '".$instance['post_type']."' ";

				// if we're getting just pages, why join the categories table?
				if ( strtolower($instance['post_type']) == 'page' )
					$join_cats = false;

			}

			// * posts exclusion
			if ( !empty($instance['pid']) ) {
				$ath = explode(",", $instance['pid']);
				$len = count($ath);

				if ($len > 1) { // we are excluding more than one post
					$pids = " AND p.ID NOT IN(".$instance['pid'].") ";
				} else if ($len == 1) { // exclude one post only
					$pids = " AND p.ID <> '".$instance['pid']."' ";
				}
			}

			// * categories
			if ( !empty($instance['cat']) && $join_cats ) {
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
					$cats = " AND p.ID IN (
						SELECT object_id
						FROM $wpdb->term_relationships AS r
							 JOIN $wpdb->term_taxonomy AS x ON x.term_taxonomy_id = r.term_taxonomy_id
							 JOIN $wpdb->terms AS t ON t.term_id = x.term_id
						WHERE x.taxonomy = 'category' AND t.term_id IN($in_cats)
						) ";
				} else if ($in_cats == "" && $out_cats != "") { // exclude posts from given cats only
					$cats = " AND p.ID NOT IN (
						SELECT object_id
						FROM $wpdb->term_relationships AS r
							 JOIN $wpdb->term_taxonomy AS x ON x.term_taxonomy_id = r.term_taxonomy_id
							 JOIN $wpdb->terms AS t ON t.term_id = x.term_id
						WHERE x.taxonomy = 'category' AND t.term_id IN($out_cats)
						) ";
				} else { // mixed, and possibly a heavy load on the DB
					$cats = " AND p.ID IN (
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
				$ath = explode(",", $instance['author']);
				$len = count($ath);

				if ($len > 1) { // we are getting posts from more that one author
					$authors = " AND p.post_author IN(".$instance['author'].") ";
				} else if ($len == 1) { // post from one author only
					$authors = " AND p.post_author = '".$instance['author']."' ";
				}
			}

			$fields = "p.ID AS 'id', p.post_title AS 'title', p.post_date AS 'date', p.post_author AS 'uid' ";

			if ($instance['range'] == "all") { // ALL TIME

				$fields .= ", p.comment_count AS 'comment_count' ";

				if ($instance['order_by'] == "comments") { // ordered by comments

					if ($instance['stats_tag']['views']) { // get views, too

						$fields .= ", IFNULL(v.pageviews, 0) AS 'pageviews' ";
						$from = " {$wpdb->posts} p LEFT JOIN {$table} v ON p.ID = v.postid WHERE {$post_types} {$pids} {$authors} {$cats} AND p.comment_count > 0 AND p.post_password = '' AND p.post_status = 'publish' ORDER BY p.comment_count DESC LIMIT {$instance['limit']} ";

					} else { // get data from wp_posts only
						$from = " {$wpdb->posts} p WHERE {$post_types} {$pids} {$authors} {$cats} AND p.comment_count > 0 AND p.post_password = '' AND p.post_status = 'publish' ORDER BY p.comment_count DESC LIMIT {$instance['limit']} ";
					}

				} else { // ordered by views / avg

					if ( $instance['order_by'] == "views" ) {

						$fields .= ", v.pageviews AS 'pageviews' ";
						$from = " {$table} v LEFT JOIN {$wpdb->posts} p ON v.postid = p.ID WHERE {$post_types} {$pids} {$authors} {$cats} AND p.post_password = '' AND p.post_status = 'publish' ORDER BY pageviews DESC LIMIT {$instance['limit']} ";

					} else if ( $instance['order_by'] == "avg" ) {

						$fields .= ", ( v.pageviews/(IF ( DATEDIFF('{$this->now()}', MIN(v.day)) > 0, DATEDIFF('{$this->now()}', MIN(v.day)), 1) ) ) AS 'avg_views' ";
						$from = " {$table} v LEFT JOIN {$wpdb->posts} p ON v.postid = p.ID WHERE {$post_types} {$pids} {$authors} {$cats} AND p.post_password = '' AND p.post_status = 'publish' GROUP BY p.ID ORDER BY avg_views DESC LIMIT {$instance['limit']} ";

					}

				}

			} else { // CUSTOM RANGE

				$interval = "";

				switch( $instance['range'] ){
					case "yesterday":
						$interval = "1 DAY";
					break;

					case "daily":
						$interval = "1 DAY";
					break;

					case "weekly":
						$interval = "1 WEEK";
					break;

					case "monthly":
						$interval = "1 MONTH";
					break;

					default:
						$interval = "1 DAY";
					break;
				}

				if ($instance['order_by'] == "comments") { // ordered by comments

					$fields .= ", c.comment_count AS 'comment_count' ";
					$from = " (SELECT comment_post_ID AS 'id', COUNT(comment_post_ID) AS 'comment_count', MAX(comment_date) AS comment_date FROM {$wpdb->comments} WHERE comment_date > DATE_SUB('{$this->now()}', INTERVAL {$interval}) AND comment_approved = 1 GROUP BY id ORDER BY comment_count DESC, comment_date DESC) c LEFT JOIN {$wpdb->posts} p ON p.ID = c.id ";

					if ($instance['stats_tag']['views']) { // get views, too

						$fields .= ", IFNULL(v.pageviews, 0) AS 'pageviews' ";
						$from .= " LEFT JOIN (SELECT id, SUM(pageviews) AS pageviews, MAX(day) AS day FROM {$table}cache WHERE day > DATE_SUB('{$this->now()}', INTERVAL {$interval}) GROUP BY id ORDER BY pageviews DESC, day DESC) v ON p.ID = v.id ";

					}

					$from .= " WHERE {$post_types} {$pids} {$authors} {$cats} AND p.post_password = '' AND p.post_status = 'publish' LIMIT {$instance['limit']} ";

				} else { // ordered by views / avg

					if ( $instance['order_by'] == "views" ) {

						$fields .= ", v.pageviews AS 'pageviews' ";
						$from = " (SELECT id, SUM(pageviews) AS pageviews, MAX(day) AS day FROM {$table}cache WHERE day > DATE_SUB('{$this->now()}', INTERVAL {$interval}) GROUP BY id ORDER BY pageviews DESC, day DESC) v LEFT JOIN {$wpdb->posts} p ON v.id = p.ID ";

					} else if ( $instance['order_by'] == "avg" ) {

						$fields .= ", ( v.pageviews/(IF ( DATEDIFF('{$this->now()}', DATE_SUB('{$this->now()}', INTERVAL {$interval})) > 0, DATEDIFF('{$this->now()}', DATE_SUB('{$this->now()}', INTERVAL {$interval})), 1) ) ) AS 'avg_views' ";
						$from = " (SELECT id, SUM(pageviews) AS pageviews, MAX(day) AS day FROM {$table}cache WHERE day > DATE_SUB('{$this->now()}', INTERVAL {$interval}) GROUP BY id ORDER BY pageviews DESC, day DESC) v LEFT JOIN {$wpdb->posts} p ON v.id = p.ID ";

					}

					if ( $instance['stats_tag']['comment_count'] ) { // get comments, too

						$fields .= ", IFNULL(c.comment_count, 0) AS 'comment_count' ";
						$from .= " LEFT JOIN (SELECT comment_post_ID AS 'id', COUNT(comment_post_ID) AS 'comment_count', MAX(comment_date) AS comment_date FROM {$wpdb->comments} WHERE comment_date > DATE_SUB('{$this->now()}', INTERVAL {$interval}) AND comment_approved = 1 GROUP BY id ORDER BY comment_count DESC, comment_date DESC) c ON p.ID = c.id ";

					}

					$from .= " WHERE {$post_types} {$pids} {$authors} {$cats} AND p.post_password = '' AND p.post_status = 'publish' ";

					if ( $instance['order_by'] == "avg" ) {
						$from .= " GROUP BY v.id ORDER BY avg_views DESC ";
					}

					$from .= " LIMIT {$instance['limit']} ";

				}

			}

			$query = "SELECT {$fields} FROM {$from}";
			//echo $query;

			$mostpopular = $wpdb->get_results($query);
			/*echo "<pre>"; print_r($mostpopular); echo "</pre>";*/

			// posts array
			$posts_data = array();

			if ( !is_array($mostpopular) || empty($mostpopular) ) { // no posts to show
				$content .= "<p>".__('Sorry. No data so far.', 'wordpress-popular-posts')."</p>"."\n";
			} else { // list posts

				// HTML wrapper
				if ($instance['markup']['custom_html']) {
					$content .= htmlspecialchars_decode($instance['markup']['wpp-start'], ENT_QUOTES) ."\n";
				} else {
					$content .= "<ul class=\"wpp-list\">" . "\n";
				}

				foreach($mostpopular as $p) {

					$stats = "";
					$thumb = "";
					$title = "";
					$title_sub = "";
					$permalink = get_permalink( $p->id );
					$author = ($instance['stats_tag']['author']) ? get_the_author_meta('display_name', $p->uid) : "";
					$date = date_i18n( $instance['stats_tag']['date']['format'], strtotime($p->date) );
					$pageviews = ($instance['order_by'] == "views" || $instance['order_by'] == "avg" || $instance['stats_tag']['views']) ? (($instance['order_by'] == "views" || $instance['order_by'] == "comments") ? $p->pageviews : $p->avg_views ) : 0;
					$comments = ($instance['order_by'] == "comments" || $instance['stats_tag']['comment_count']) ? $p->comment_count : 0;

					$post_cat = "";
					$excerpt = "";
					$rating = "";
					$data = array();

					// TITLE
					$title = ($this->qTrans) ? qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($p->title) : $p->title;
					$title = strip_tags($title);
					$title_sub = strip_tags($title);

					// truncate title
					if ( $instance['shorten_title']['active'] ) {

						if ( isset($instance['shorten_title']['words']) && $instance['shorten_title']['words'] ) { // by words

							$words = explode(" ", $title, $instance['shorten_title']['length'] + 1);

							if ( count($words) > $instance['shorten_title']['length'] ) {

								array_pop($words);
								$title_sub = implode(" ", $words) . "...";

							}

						} else { // by characters

							if ( strlen($title) > $instance['shorten_title']['length'] ) {
								$title_sub = mb_substr($title, 0, $instance['shorten_title']['length'], $this->charset) . "...";
							}

						}

					}

					$title = htmlspecialchars( $title, ENT_QUOTES, $this->charset );
					$title_sub = htmlspecialchars( $title_sub, ENT_QUOTES, $this->charset );

					$title = apply_filters('the_title', $title, $p->id);
					$title_sub = apply_filters('the_title', $title_sub, $p->id);

					// EXCERPT
					if ( $instance['post-excerpt']['active'] ) {

						$excerpt = trim( $this->get_summary($p->id, $instance) );

						if ( !empty($excerpt) ) {

							if ( !$instance['markup']['custom_html'] ) {
								$excerpt = ": <span class=\"wpp-excerpt\">" . $excerpt . "</span>";
							}

						}

					}

					// STATS
					// comments
					if ( $instance['stats_tag']['comment_count'] ) {
						$comments_text =sprintf(
						_n('1 comment', '%s comments', $comments, 'wordpress-popular-posts'),
						number_format_i18n($comments));
						$stats .= "<span class=\"wpp-comments\">" . $comments_text . "</span>";
					} else {
					}
					// views
					if ( $instance['stats_tag']['views'] ) {

						if ($instance['order_by'] == 'avg') {
							$views_text = sprintf(
							_n('1 view per day', '%s views per day', intval($pageviews), 'wordpress-popular-posts'),
							number_format_i18n($pageviews, 2)
							);
						}
						else {
							$views_text = sprintf(
							_n('1 view', '%s views', intval($pageviews), 'wordpress-popular-posts'),
							number_format_i18n($pageviews)
							);
						}

						$stats .= ($stats == "") ? "<span class=\"wpp-views\">" . $views_text . "</span>" : " | <span class=\"wpp-views\">" . $views_text . "</span>";

					}
					//author
					if ( $instance['stats_tag']['author'] ) {
						$display_name = "<a href=\"".get_author_posts_url($p->uid)."\">{$author}</a>";
						$stats .= ($stats == "") ? "<span class=\"wpp-author\">" . __('by', 'wordpress-popular-posts')." {$display_name}</span>" : " | <span class=\"wpp-author\">" . __('by', 'wordpress-popular-posts') . " {$display_name}</span>";
					}
					// date
					if ( $instance['stats_tag']['date']['active'] ) {
						$stats .= ($stats == "") ? "<span class=\"wpp-date\">" . __('posted on', 'wordpress-popular-posts')." {$date}</span>" : " | <span class=\"wpp-date\">" . __('posted on', 'wordpress-popular-posts') . " {$date}</span>";
					}
					// category
					if ( $instance['stats_tag']['category'] ) {
						$post_cat = get_the_category( $p->id );
						$post_cat = ( isset($post_cat[0]) ) ? '<a href="'.get_category_link($post_cat[0]->term_id ).'">'.$post_cat[0]->cat_name.'</a>' : '';

						if ( $post_cat != '' ) {
							$stats .= ($stats == "") ? "<span class=\"wpp-category\">" . __('under', 'wordpress-popular-posts'). " {$post_cat}</span>" : " | <span class=\"wpp-category\">" . __('under', 'wordpress-popular-posts') . " {$post_cat}</span>";
						}
					}

					// RATING
					if ( $instance['rating'] && $this->postRating && function_exists('the_ratings') ) {
						$rating = '<span class="wpp-rating">'.the_ratings( 'span', $p->id, false ).'</span>';
					}

					// POST THUMBNAIL
					if ($instance['thumbnail']['active'] && $this->thumb) {

						$tbWidth = $instance['thumbnail']['width'];
						$tbHeight = $instance['thumbnail']['height'];

						$thumb = "<a href=\"". $permalink ."\" title=\"{$title}\" target=\"".$this->user_ops['tools']['link']['target']."\">";

						if ( $this->user_ops['tools']['thumbnail']['source'] == "custom_field" ) { // get image from custom field

							$path = get_post_meta($p->id, $this->user_ops['tools']['thumbnail']['field'], true);

							if ( $path != "" ) {
								
								if ( $this->user_ops['tools']['thumbnail']['resize'] ) {
									
									$thumb .= $this->get_img( $p->id, array($tbWidth, $tbHeight), $this->user_ops['tools']['thumbnail']['source'] );
																		
								} else {
									$thumb .= "<img src=\"{$path}\" width=\"{$tbWidth}\" height=\"{$tbHeight}\" alt=\"{$title}\" border=\"0\" class=\"wpp-thumbnail wpp_cf\" />";
								}
								
							} else {
								$thumb .= "<img src=\"". $this->default_thumbnail ."\" alt=\"{$title}\" border=\"0\" width=\"{$tbWidth}\" height=\"{$tbHeight}\" class=\"wpp-thumbnail wpp_cf_def\" />";
							}

						} else { // get image from post / Featured Image
							$thumb .= $this->get_img( $p->id, array($tbWidth, $tbHeight), $this->user_ops['tools']['thumbnail']['source'] );
						}

						$thumb .= "</a>";
					}

					$data = array(
						'id' => $p->id,
						'title' => '<a href="'.$permalink.'" title="'.$title.'">'.$title_sub.'</a>',
						'summary' => $excerpt,
						'stats' => $stats,
						'img' => $thumb,
						'url' => $permalink,
						'text_title' => $title,
						'category' => $post_cat,
						'author' => "<a href=\"".get_author_posts_url($p->uid)."\">{$author}</a>",
						'views' => $pageviews,
						'comments' => $comments
					);

					$posts_data[] = (object) $data;

					// PUTTING IT ALL TOGETHER
					if ($instance['markup']['custom_html']) { // build custom layout

						$content .= htmlspecialchars_decode( $this->format_content($instance['markup']['post-html'], $data, $instance['rating']), ENT_QUOTES ) . "\n";

					} else { // build regular layout
						$content .= "<li>{$thumb}<a href=\"{$permalink}\" title=\"{$title}\" class=\"wpp-post-title\" target=\"".$this->user_ops['tools']['link']['target']."\">{$title_sub}</a> {$excerpt}<span class=\"post-stats\">{$stats}</span>{$rating}</li>" . "\n";
					}
				}

				// END HTML wrapper
				if ($instance['markup']['custom_html']) {
					$content .= htmlspecialchars_decode($instance['markup']['wpp-end'], ENT_QUOTES) ."\n";
				} else {
					$content .= "\n"."</ul>"."\n";
				}
			}

			//return $content;
			return apply_filters( 'wpp_html', $content, $posts_data );
			die();

		}

		/**
		 * Builds post's excerpt
		 * Since 1.4.6
		 */
		function get_summary($id, $instance){

			if ( !is_numeric($id) )
				return false;

			global $wpdb;

			$excerpt = "";
			$the_post = get_post( $id );
			$excerpt = ( $the_post->post_excerpt == "" ) ? $the_post->post_content : $the_post->post_excerpt;

			// RRR added call to the_content filters, allows qTranslate to hook in.
            if ( $this->qTrans )
				$excerpt = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $excerpt );

			// remove WP shortcodes (and HTML tags if requested)
			if ( $instance['post-excerpt']['keep_format'] ) {
				$excerpt = strip_shortcodes( strip_tags($excerpt, '<a><b><i><em><strong>') );
			} else {
				$excerpt = strip_shortcodes( strip_tags($excerpt) );
			}

			// remove caption tags
			$excerpt = preg_replace( "/\[caption.*\[\/caption\]/", "", $excerpt );

			// remove Flash objects
			$excerpt = preg_replace( "/<object[0-9 a-z_?*=\":\-\/\.#\,\\n\\r\\t]+/smi", "", $excerpt );

			// remove Iframes
			$excerpt = preg_replace( "/<iframe.*?\/iframe>/i", "", $excerpt);

			// remove URLs
			$excerpt = preg_replace( '_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iuS', '', $excerpt );

			// do we still have something to display?
			if ( !empty($excerpt) ) {

				// truncate excerpt
				if ( isset($instance['post-excerpt']['words']) && $instance['post-excerpt']['words'] ) { // by words

					$words = explode(" ", $excerpt, $instance['post-excerpt']['length'] + 1);

					if ( count($words) > $instance['post-excerpt']['length'] ) {

						array_pop($words);
						$excerpt = implode(" ", $words) . "...";

					}

				} else { // by characters

					if ( strlen($excerpt) > $instance['post-excerpt']['length'] ) {
						$excerpt = mb_substr( $excerpt, 0, $instance['post-excerpt']['length'] ) . "...";
					}

				}

			}

			// remove HTML tags if requested
			if ( $instance['post-excerpt']['keep_format'] ) {
				$excerpt = force_balance_tags(strip_tags($excerpt, '<a><b><i><em><strong>'));
			} else {
				$excerpt = strip_tags($excerpt);
			}

			// remove WP shortcodes
			$excerpt = strip_shortcodes( $excerpt );

			return $excerpt;

		}

		/**
		 * Retrieves post's image
		 * Since 1.4.6
		 * Last modified: 2.3.3
		 * Borrowed some ideas from Victor Teixeira's VT function http://core.trac.wordpress.org/ticket/15311
		 * and added a check for the WP_Image_Editor Class
		 */
		function get_img( $id = NULL, $dim = array(80, 80), $source = "featured" ) {

			if ( !$id || empty($id) || !is_numeric($id) )
				return "<img src=\"". $this->default_thumbnail ."\" alt=\"\" border=\"0\" width=\"{$dim[0]}\" height=\"{$dim[1]}\" class=\"wpp-thumbnail wpp_def_noID\" />";

			$file_path = '';

			if ( $source == "featured" ) { // get thumbnail path from the Featured Image

				$thumbnail_id = get_post_thumbnail_id( $id ); // thumb attachment ID

				if ( $thumbnail_id ) {

					$thumbnail = wp_get_attachment_image_src( $thumbnail_id, 'full' ); // full size image
					$file_path = get_attached_file( $thumbnail_id ); // image path

				}

			} else if ( $source == "first_image" ) { // get thumbnail path from post content

				global $wpdb;

				$content = $wpdb->get_var( "SELECT post_content FROM $wpdb->posts WHERE ID = {$id}" );
				$count = substr_count($content, '<img');

				if ($count > 0) { // images have been found

					$output = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $ContentImages );

					if ( isset($ContentImages[1][0]) ) {

						$image_url = esc_url( $ContentImages[1][0] ); // sanitize URL, just in case
						preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $image_url, $matches); // remove querystring
						$image_url = $matches[0];

						$attachment_id = $this->wpp_get_attachment_id( $image_url );

						if ( $attachment_id ) { // image comes from Media library

							$thumbnail[0] = $image_url;
							$file_path = get_attached_file( $attachment_id );

						} else { // image not found in Media library, maybe external image?
						
							$result = $this->download_image( $image_url, $id );
							
							if ( is_array($result) && !empty($result) ) {
								
								$thumbnail[0] = $result[0];
								$file_path = $result[1];
								
							}

						}

					}

				}

			} else if ( $source == "custom_field" ) { // get thumbnail path from custom field

				$image_url = get_post_meta($id, $this->user_ops['tools']['thumbnail']['field'], true);
				$image_url = esc_url( $image_url ); // sanitize URL, just in case
				preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $image_url, $matches); // remove querystring
				$image_url = $matches[0];
				
				$result = $this->download_image( $image_url, $id );
							
				if ( is_array($result) && !empty($result) ) {
					
					$thumbnail[0] = $result[0];
					$file_path = $result[1];
					
				}

			}

			if ( $file_path == '' ) {
				return "<img src=\"". $this->default_thumbnail ."\" alt=\"\" border=\"0\" width=\"{$dim[0]}\" height=\"{$dim[1]}\" class=\"wpp-thumbnail wpp_def_noPath wpp_{$source}\" />";
			}

			$file_info = pathinfo( $file_path );
			$extension = '.'. $file_info['extension'];

			$cropped_thumb = $file_info['dirname'].'/'.$file_info['filename'].'-'.$dim[0].'x'.$dim[1].$extension;

			if ( file_exists( $cropped_thumb ) ) { // there is a thumbnail already, use it

				$new_img = str_replace( wp_basename( $thumbnail[0] ), wp_basename( $cropped_thumb ), $thumbnail[0] );
				return "<img src=\"". $new_img ."\" alt=\"\" border=\"0\" width=\"{$dim[0]}\" height=\"{$dim[1]}\" class=\"wpp-thumbnail wpp_cached_thumb wpp_{$source}\" />";

			} else { // no thumbnail or image file missing, try to create it

				if ( function_exists('wp_get_image_editor') ) { // if supported, use WP_Image_Editor Class

					$image = wp_get_image_editor( $file_path );

					if ( is_wp_error( $image ) ) { // image file path is invalid
						return "<!-- " . $image->get_error_message() ." --> <img src=\"". $this->default_thumbnail ."\" alt=\"\" border=\"0\" width=\"{$dim[0]}\" height=\"{$dim[1]}\" class=\"wpp-thumbnail wpp_imgeditor_error wpp_{$source}\" />";
					}

					$image->resize( $dim[0], $dim[1], true );
					$new_img = $image->save();

					if ( is_wp_error( $new_img ) ) {
						return "<!-- " . $new_img->get_error_message() ." --> <img src=\"". $this->default_thumbnail ."\" alt=\"\" border=\"0\" width=\"{$dim[0]}\" height=\"{$dim[1]}\" class=\"wpp-thumbnail wpp_imgeditor_error wpp_{$source}\" />";
					}

					$new_img = str_replace( wp_basename( $thumbnail[0] ), $new_img['file'], $thumbnail[0] );
					return "<img src=\"". $new_img ."\" alt=\"\" border=\"0\" width=\"{$dim[0]}\" height=\"{$dim[1]}\" class=\"wpp-thumbnail wpp_imgeditor_thumb wpp_{$source}\" />";

				} else { // create thumb using image_resize()

					$new_img_path = image_resize( $file_path, $dim[0], $dim[1], true );

					if ( is_wp_error( $new_img_path ) ) { // image file path is invalid
						return "<!-- " . $new_img_path->get_error_message() ." --> <img src=\"". $this->default_thumbnail ."\" alt=\"\" border=\"0\" width=\"{$dim[0]}\" height=\"{$dim[1]}\" class=\"wpp-thumbnail wpp_image_resize_error wpp_{$source}\" />";
					}

					$new_img_size = getimagesize( $new_img_path );
					$new_img = str_replace( wp_basename( $thumbnail[0] ), wp_basename( $new_img_path ), $thumbnail[0] );
					return "<img src=\"". $new_img ."\" alt=\"\" border=\"0\" width=\"{$dim[0]}\" height=\"{$dim[1]}\" class=\"wpp-thumbnail wpp_image_resize_thumb wpp_{$source}\" />";

				}

			}

		}
		
		function is_external_image( $url ){
			
			$dir = wp_upload_dir();

			// baseurl never has a trailing slash
			if ( false === strpos( $url, $dir['baseurl'] . '/' ) ) {
				// URL points to a place outside of upload directory
				return true;
			}
			
			return false;
			
		}
		
		function download_image( $url, $id ){
			
			$image = array();
			
			$uploads = wp_upload_dir();
			$image[0] = trailingslashit( $uploads['baseurl'] ) . "{$id}_". sanitize_file_name( rawurldecode(wp_basename( $url )) );
			$image[1] = trailingslashit( $uploads['basedir'] ) . "{$id}_". sanitize_file_name( rawurldecode(wp_basename( $url )) );
			
			// if the file exists already, return URL and path
			if ( file_exists($image[1]) ) 
				return $image;
				
			$accepted_status_codes = array( 200, 301, 302 );
			$response = wp_remote_head( $url, array( 'timeout' => 5, 'sslverify' => false ) );
			
			if ( !is_wp_error($response) && in_array(wp_remote_retrieve_response_code($response), $accepted_status_codes) ) {
				
				$image_data = getimagesize( $url );
				
				if ( is_array($image_data) && !empty($image_data) ) {
					
					require_once( ABSPATH . 'wp-admin/includes/file.php' );

					$url = str_replace( 'https://', 'http://', $url );
					$tmp = download_url( $url );

					// move file to Uploads
					if ( !is_wp_error( $tmp ) && rename($tmp, $image[1]) ) {
						
						// borrowed from WP - set correct file permissions
						$stat = stat( dirname( $image[1] ));
						$perms = $stat['mode'] & 0000666;
						@chmod( $image[1], $perms );
						
						return $image;
						
					}
					
				}
				
			}
			
			return false;
			
		}

		/**
		* Get the Attachment ID for a given image URL.
		* Source: http://wordpress.stackexchange.com/a/7094
		* @param  string $url
		* @return boolean|integer
		*/
		function wpp_get_attachment_id( $url ) {
			
			if ( $this->is_external_image($url) )
				return false;

			$file  = wp_basename( $url );
			$query = array(
				'post_type'  => 'attachment',
				'fields'     => 'ids',
				'meta_query' => array(
					array(
						'value'   => $file,
						'compare' => 'LIKE',
					),
				)
			);

			$query['meta_query'][0]['key'] = '_wp_attached_file';

			// query attachments
			$ids = get_posts( $query );

			if ( ! empty( $ids ) ) {

				foreach ( $ids as $id ) {

					// first entry of returned array is the URL
					if ( $url === array_shift( wp_get_attachment_image_src( $id, 'full' ) ) )
						return $id;
				}
			}

			$query['meta_query'][0]['key'] = '_wp_attachment_metadata';

			// query attachments again
			$ids = get_posts( $query );

			if ( empty( $ids) )
				return false;

			foreach ( $ids as $id ) {

				$meta = wp_get_attachment_metadata( $id );

				foreach ( $meta['sizes'] as $size => $values ) {

					if ( $values['file'] === $file && $url === array_shift( wp_get_attachment_image_src( $id, $size ) ) )
						return $id;
				}
			}

			return false;
		}

		/**
		 * Parses content tags
		 * Since 1.4.6
		 */
		function format_content($string, $data = array(), $rating) {

			if (empty($string) || (empty($data) || !is_array($data)))
				return false;

			$string = htmlentities( $string );

			$params = array();
			$pattern = '/\{(excerpt|summary|stats|title|image|thumb|rating|score|url|text_title|author|category|views|comments)\}/i';
			preg_match_all($pattern, $string, $matches);

			array_map('strtolower', $matches[0]);

			if ( in_array("{title}", $matches[0]) ) {
				$string = str_replace( "{title}", $data['title'], $string );
			}

			if ( in_array("{stats}", $matches[0]) ) {
				$string = str_replace( "{stats}", $data['stats'], $string );
			}

			if ( in_array("{excerpt}", $matches[0]) ) {
				$string = str_replace( "{excerpt}", htmlentities($data['summary'], ENT_QUOTES), $string );
			}

			if ( in_array("{summary}", $matches[0]) ) {
				$string = str_replace( "{summary}", htmlentities($data['summary'], ENT_QUOTES), $string );
			}

			if ( in_array("{image}", $matches[0]) ) {
				$string = str_replace( "{image}", $data['img'], $string );
			}

			if ( in_array("{thumb}", $matches[0]) ) {
				$string = str_replace( "{thumb}", $data['img'], $string );
			}

			// WP-PostRatings check
			if ($rating && $this->postRating) {
				if ( in_array("{rating}", $matches[0]) ) {
					$string = str_replace( "{rating}", the_ratings_results($data['id']), $string );
				}

				if ( in_array("{score}", $matches[0]) ) {
					$string = str_replace( "{score}", expand_ratings_template('%RATINGS_SCORE%', $data['id']), $string);
					// removing the redundant plus sign
					$string = str_replace('+', '', $string);
				}
			}

			if ( in_array("{url}", $matches[0]) ) {
				$string = str_replace( "{url}", $data['url'], $string );
			}

			if ( in_array("{text_title}", $matches[0]) ) {
				$string = str_replace( "{text_title}", $data['text_title'], $string );
			}

			if ( in_array("{author}", $matches[0]) ) {
				$string = str_replace( "{author}", $data['author'], $string );
			}

			if ( in_array("{category}", $matches[0]) ) {
				$string = str_replace( "{category}", $data['category'], $string );
			}

			if ( in_array("{views}", $matches[0]) ) {
				$string = str_replace( "{views}", $data['views'], $string );
			}

			if ( in_array("{comments}", $matches[0]) ) {
				$string = str_replace( "{comments}", $data['comments'], $string );
			}

			return html_entity_decode( $string );
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

		/**
		 * Loads translations
		 * Since 2.0.0
		 */
		function wpp_textdomain() {
			load_plugin_textdomain( 'wordpress-popular-posts', false, dirname(plugin_basename( __FILE__ )) . '/lang/' );
		}

		/**
		 * Loads WPP stylesheet into wp_head()
		 * Since 2.0.0
		 */
		function wpp_print_stylesheet() {

			$css_path = NULL;

			if ( !is_admin() ) {

				$theme_file = get_stylesheet_directory() . '/wpp.css';
				$plugin_file = plugin_dir_path(__FILE__) . 'style/wpp.css';

				if ( @file_exists($theme_file) ) { // user stored a custom wpp.css on theme's directory, so use it
					$css_path = get_stylesheet_directory_uri() . "/wpp.css";
				} elseif ( @file_exists($plugin_file) ) { // no custom wpp.css, use plugin's instead
					$css_path = $this->pluginDir . 'style/wpp.css';
				}

				if ( $css_path )
					wp_enqueue_style('wordpress-popular-posts', $css_path, false, $this->version);

			}
		}

		/**
		 * WPP Admin page
		 * Since 2.3.0
		 */
		function wpp_admin() {
			require (dirname(__FILE__) . '/admin.php');
		}
		function add_wpp_admin() {
			add_options_page('Wordpress Popular Posts', 'Wordpress Popular Posts', 'manage_options', 'wpp_admin', array(&$this, 'wpp_admin'));
		}
		/**
		 * Upload scripts for the admin section
		 * Since 2.3.4
		 */
		function wpp_admin_enqueue_scripts() {

			wp_register_script( 'wpp-upload', $this->pluginDir . 'js/wpp-upload.js', array('jquery','media-upload','thickbox'), $this->version );

			if ( get_current_screen()->id == 'settings_page_wpp_admin' ) {

				wp_enqueue_script('jquery');
				wp_enqueue_script('thickbox');
				wp_enqueue_style('thickbox');
				wp_enqueue_script('media-upload');
				wp_enqueue_script('wpp-upload');

			}

		}
		function wpp_tb_setup() {

			global $pagenow;
			if ( 'media-upload.php' == $pagenow || 'async-upload.php' == $pagenow ) {
				add_filter( 'gettext', array(&$this, 'replace_thickbox_text'), 1, 3 );
			}

		}
		function replace_thickbox_text($translated_text, $text, $domain) {

			if ('Insert into Post' == $text) {
				$referer = strpos( wp_get_referer(), 'wpp_admin' );
				if ( $referer != '' ) {
					return __('Upload', 'wordpress-popular-posts' );
				}
			}

			return $translated_text;
		}

		/**
		 * WPP Update warning - lets the user know that the current WP version is too old
		 * Since 2.0.0
		 */
		function wpp_update_warning() {
			$msg = '<div id="wpp-message" class="error fade"><p>'.__('Your Wordpress version is too old. Wordpress Popular Posts Plugin requires at least version 3.3 to function correctly. Please update your blog via Tools &gt; Upgrade.', 'wordpress-popular-posts').'</p></div>';
			echo trim($msg);
		}

		/**
		 * PHP Update warning - lets the user know that the PHP version is too old
		 * Since 2.3.3
		 */
		function php_update_warning() {
			$msg = '<div id="wpp-php-message" class="error fade"><p>'.__('Your PHP installation is too old. Wordpress Popular Posts Plugin requires at least PHP v5.2.0 to function correctly. Please contact your hosting provider and ask them to upgrade PHP to 5.2.x or higher.', 'wordpress-popular-posts').'</p></div>';
			echo trim($msg);
		}

		/**
		 * WPP cache maintenance
		 * Since 2.0.0
		 */
		function wpp_cache_maintenance() {
			global $wpdb;

			// delete posts that have not been seen in the past 30 days
			$wpdb->query( "DELETE FROM ".$wpdb->prefix."popularpostsdatacache WHERE day < DATE_SUB('".$this->curdate()."', INTERVAL 30 DAY);" );

			// delete posts that have been deleted or trashed - added on ver 2.3.3
			$wpdb->query( "DELETE FROM {$wpdb->prefix}popularpostsdata WHERE postid IN (SELECT c.id FROM (SELECT id FROM {$wpdb->prefix}popularpostsdatacache GROUP BY id) c LEFT JOIN {$wpdb->posts} p ON c.id = p.ID WHERE p.ID IS NULL OR p.post_status = 'trash');" );
			$wpdb->query( "DELETE FROM {$wpdb->prefix}popularpostsdatacache WHERE id IN (SELECT c.id FROM (SELECT id FROM {$wpdb->prefix}popularpostsdatacache GROUP BY id) c LEFT JOIN {$wpdb->posts} p ON c.id = p.ID WHERE p.ID IS NULL OR p.post_status = 'trash');" );

		}

		/**
		 * WPP plugin deactivation
		 * Since 2.0.0
		 */
		function wpp_deactivation() {
			wp_clear_scheduled_hook('wpp_cache_event');
			remove_shortcode('wpp');
			remove_shortcode('WPP');
		}

		/**
		 * WPP shortcode handler
		 * Since 2.0.0
		 */
		function wpp_shortcode($atts = NULL, $content = NULL) {

			extract( shortcode_atts( array(
				'header' => '',
				'limit' => 10,
				'range' => 'daily',
				'order_by' => 'views',
				'post_type' => 'post,page',
				'pid' => '',
				'cat' => '',
				'author' => '',
				'title_length' => 0,
				'title_by_words' => 0,
				'excerpt_length' => 0,
				'excerpt_format' => 0,
				'excerpt_by_words' => 0,
				'thumbnail_width' => 0,
				'thumbnail_height' => 0,
				'thumbnail_selection' => 'wppgenerated',
				'rating' => false,
				'stats_comments' => true,
				'stats_views' => false,
				'stats_author' => false,
				'stats_date' => false,
				'stats_date_format' => 'F j, Y',
				'stats_category' => false,
				'wpp_start' => '<ul class="wpp-list">',
				'wpp_end' => '</ul>',
				'post_html' => '',
				'post_start' => '<li>',
				'post_end' => '</li>',
				'header_start' => '<h2>',
				'header_end' => '</h2>',
				'do_pattern' => false,
				'pattern_form' => '{thumb} {title}: {summary} {stats}'
			), $atts ) );

			// possible values for "Time Range" and "Order by"
			$range_values = array("yesterday", "daily", "weekly", "monthly", "all");
			$order_by_values = array("comments", "views", "avg");
			$thumbnail_selector = array("wppgenerated", "usergenerated");

			$shortcode_ops = array(
				'title' => strip_tags($header),
				'limit' => empty($limit) ? 10 : (is_numeric($limit)) ? (($limit > 0) ? $limit : 10) : 10,
				'range' => (in_array($range, $range_values)) ? $range : 'daily',
				'order_by' => (in_array($order_by, $order_by_values)) ? $order_by : 'views',
				'post_type' => empty($post_type) ? 'post,page' : $post_type,
				'pid' => preg_replace( '|[^0-9,]|', '', $pid ),
				'cat' => preg_replace( '|[^0-9,-]|', '', $cat ),
				'author' => preg_replace( '|[^0-9,]|', '', $author ),
				'shorten_title' => array(
					'active' => empty($title_length) ? false : (is_numeric($title_length)) ? (($title_length > 0) ? true : false) : false,
					'length' => empty($title_length) ? 0 : (is_numeric($title_length)) ? $title_length : 0,
					'words' => empty($title_by_words) ? false : (is_numeric($title_by_words) && $title_by_words > 0) ? true : false
				),
				'post-excerpt' => array(
					'active' => empty($excerpt_length) ? false : (is_numeric($excerpt_length)) ? (($excerpt_length > 0) ? true : false) : false,
					'length' => empty($excerpt_length) ? 0 : (is_numeric($excerpt_length)) ? $excerpt_length : 0,
					'keep_format' => empty($excerpt_format) ? false : (is_numeric($excerpt_format)) ? (($excerpt_format > 0) ? true : false) : false,
					'words' => empty($excerpt_by_words) ? false : (is_numeric($excerpt_by_words) && $excerpt_by_words > 0) ? true : false,
				),
				'thumbnail' => array(
					'active' => empty($thumbnail_width) ? false : (is_numeric($thumbnail_width)) ? (($thumbnail_width > 0) ? true : false) : false,
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
					),
					'category' => empty($stats_category) ? false : $stats_category,
				),
				'markup' => array(
					'custom_html' => true,
					'wpp-start' => empty($wpp_start) ? '&lt;ul class="wpp-list"&gt;' : $wpp_start,
					'wpp-end' => empty($wpp_end) ? '&lt;/ul&gt;' : $wpp_end,
					'post-html' => empty($post_html) ? '&lt;li&gt;{thumb} {title} {stats}&lt;/li&gt;' : $post_html,
					'post-start' => empty($post_start) ? '&lt;li&gt;' : $post_start,
					'post-end' => empty($post_end) ? '&lt;/li&gt;' : $post_end,
					'title-start' => empty($header_start) ? '' : $header_start,
					'title-end' => empty($header_end) ? '' : $header_end
				)
			);

			/*echo "<pre>"; print_r( $shortcode_ops ); echo "</pre>";*/

			$shortcode_content = "<!-- Wordpress Popular Posts Plugin v". $this->version ." [SC] [".$shortcode_ops['range']."] [".$shortcode_ops['order_by']."]". (($shortcode_ops['markup']['custom_html']) ? ' [custom]' : ' [regular]') ." -->"."\n";

			// is there a title defined by user?
			if (!empty($header) && !empty($header_start) && !empty($header_end)) {
				$shortcode_content .= $header_start . apply_filters('widget_title', $header) . $header_end;
			}

			// print popular posts list
			$shortcode_content .= $this->get_popular_posts($shortcode_ops);
			$shortcode_content .= "<!-- End Wordpress Popular Posts Plugin v". $this->version ." -->"."\n";

			return $shortcode_content;
		}

		/**
		 * WPP action links
		 * Since 2.3.3
		 */
		function wpp_action_links( $links, $file ){
			static $this_plugin;

			if (!$this_plugin) {
				$this_plugin = plugin_basename(__FILE__);
			}

			if ($file == $this_plugin) {
				$settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=wpp_admin">Settings</a>';
				array_unshift($links, $settings_link);
			}

			return $links;
		}

		/**
		 * Sorter
		 * Since 2.3.0
		 */
		function sorter($a, $b) {
			if ($a > 0 && $b > 0) {
				return $a - $b;
			} else {
				return $b - $a;
			}
		}

		/**
		 * Merges two associative arrays recursively
		 * Source: http://www.php.net/manual/en/function.array-merge-recursive.php#92195
		 * Since 2.3.4
		 */
		function array_merge_recursive_distinct( array &$array1, array &$array2 ) {
			$merged = $array1;

			foreach ( $array2 as $key => &$value ) {
				if ( is_array( $value ) && isset ( $merged[$key] ) && is_array( $merged[$key] ) ) {
					$merged[$key] = $this->array_merge_recursive_distinct ( $merged[$key], $value );
				} else {
					$merged[$key] = $value;
				}
			}

			return $merged;
		}

	}

	// create tables
	register_activation_hook(__FILE__ , array('WordPressPopularPosts', 'wpp_install'));
}

/**
 * Wordpress Popular Posts template tags for use in themes.
 */

/**
 * Template tag - gets views count
 * $id (int) - post / page ID
 * $range (string) - time frame
 * Since 2.0.3.
 */
function wpp_get_views($id = NULL, $range = NULL) {
	// have we got an id?
	if ( empty($id) || is_null($id) || !is_numeric($id) ) {
		return "-1";
	} else {
		global $wpdb;

		$table_name = $wpdb->prefix . "popularpostsdata";
		$query = "SELECT pageviews, last_viewed AS day FROM {$table_name} WHERE postid = '{$id}'";

		if ( $range ) {

			$interval = "";

			switch( $range ){
				case "yesterday":
					$interval = "1 DAY";
				break;

				case "daily":
					$interval = "1 DAY";
				break;

				case "weekly":
					$interval = "1 WEEK";
				break;

				case "monthly":
					$interval = "1 MONTH";
				break;

				default:
					$interval = "1 DAY";
				break;
			}

			$now = current_time('mysql');

			$query = "SELECT SUM(pageviews) AS pageviews, MAX(day) AS day FROM {$table_name}cache WHERE id = '{$id}' AND day > DATE_SUB('{$now}', INTERVAL {$interval}) LIMIT 1;";
		}

		$result = $wpdb->get_results($query, ARRAY_A);

		if ( !is_array($result) || empty($result) || empty($result[0]['pageviews']) ) {
			return "0";
		} else {
			return number_format( $result[0]['pageviews'] );
		}
	}
}

/**
 * Template tag - gets popular posts
 * Since 2.0.3.
 */
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

/**
 * Template tag - gets popular posts
 * Deprecated in 2.0.3.
 * Use wpp_get_mostpopular instead.
 */
function get_mostpopular($args = NULL) {
	return wpp_get_mostpopular($args);
}


/**
 * Wordpress Popular Posts 2.3.6 Changelog.
 */

/*
= 2.3.6 =
* Added wpp_html filter to allow total control of the HTML output.
* Added sanitization for external thumbnail filenames to avoid weird characters.
* Updated thumbnail feature to handle external images.
* Removed unnecesary wpp-thumbnail class from link tag, the image already has it.
* Added wpp-list class to the UL tag, this should help style the popular list better.
* Updated wpp.css with text floating next to thumbnails - this sets a predefined style for the plugin for the first time.
* Added plugin version to wp_enqueue_* calls.
* Fixed typo in wpp_update_warning. From v2.3.3, minimun Wordpress version required is 3.3.
*/