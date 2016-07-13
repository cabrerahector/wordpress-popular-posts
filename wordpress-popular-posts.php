<?php
/*
Plugin Name: WordPress Popular Posts
Plugin URI: http://wordpress.org/extend/plugins/wordpress-popular-posts
Description: WordPress Popular Posts is a highly customizable widget that displays the most popular posts on your blog
Version: 3.3.4
Author: Hector Cabrera
Author URI: http://cabrerahector.com
Author Email: hcabrerab@gmail.com
Text Domain: wordpress-popular-posts
Domain Path: /lang/
Network: false
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Copyright 2008-2015 Hector Cabrera (hcabrerab@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !defined('ABSPATH') )
	exit('Please do not load this file directly.');

/**
 * WordPress Popular Posts class.
 */
if ( !class_exists('WordpressPopularPosts') ) {

	/**
	 * Register plugin's activation / deactivation functions
	 * @since 1.3
	 */
	register_activation_hook( __FILE__, array( 'WordpressPopularPosts', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'WordpressPopularPosts', 'deactivate' ) );

	/**
	 * Add function to widgets_init that'll load WPP.
	 * @since 2.0
	 */
	function load_wpp() {
		register_widget( 'WordpressPopularPosts' );
	}
	add_action( 'widgets_init', 'load_wpp' );

	class WordpressPopularPosts extends WP_Widget {

		/**
		 * Plugin version, used for cache-busting of style and script file references.
		 *
		 * @since	1.3.0
		 * @var		string
		 */
		private $version = '3.3.4';

		/**
		 * Plugin identifier.
		 *
		 * @since	3.0.0
		 * @var		string
		 */
		private $plugin_slug = 'wordpress-popular-posts';

		/**
		 * Instance of this class.
		 *
		 * @since    3.0.0
		 * @var      object
		 */
		protected static $instance = NULL;

		/**
		 * Slug of the plugin screen.
		 *
		 * @since	3.0.0
		 * @var		string
		 */
		protected $plugin_screen_hook_suffix = NULL;
		
		/**
		 * Flag for singular pages.
		 *
		 * @since	3.1.2
		 * @var		int
		 */
		private $current_post_id = 0;

		/**
		 * Plugin directory.
		 *
		 * @since	1.4.6
		 * @var		string
		 */
		private $plugin_dir = '';
		
		/**
		 * Plugin uploads directory.
		 *
		 * @since	3.0.4
		 * @var		array
		 */
		private $uploads_dir = array();

		/**
		 * Default thumbnail.
		 *
		 * @since	2.2.0
		 * @var		string
		 */
		private $default_thumbnail = '';
		
		/**
		 * Default thumbnail sizes
		 *
		 * @since	3.2.2
		 * @var		array
		 */
		private $default_thumbnail_sizes = array();

		/**
		 * Flag to verify if thumbnails can be created or not.
		 *
		 * @since	1.4.6
		 * @var		bool
		 */
		private $thumbnailing = false;

		/**
		 * Flag to verify if qTrans is present.
		 *
		 * @since	1.4.6
		 * @var		bool
		 */
		private $qTrans = false;

		/**
		 * Default charset.
		 *
		 * @since	2.1.4
		 * @var		string
		 */
		private $charset = "UTF-8";

		/**
		 * Plugin defaults.
		 *
		 * @since	2.3.3
		 * @var		array
		 */
		protected $defaults = array(
			'title' => '',
			'limit' => 10,
			'range' => 'daily',
			'freshness' => false,
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
				'build' => 'manual',
				'width' => 15,
				'height' => 15,
				'crop' => true
			),
			'rating' => false,
			'stats_tag' => array(
				'comment_count' => false,
				'views' => true,
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

		/**
		 * Admin page user settings defaults.
		 *
		 * @since	2.3.3
		 * @var		array
		 */
		protected $default_user_settings = array(
			'stats' => array(
				'order_by' => 'views',
				'limit' => 10,
				'post_type' => 'post,page',
				'freshness' => false
			),
			'tools' => array(
				'ajax' => false,
				'css' => true,
				'link' => array(
					'target' => '_self'
				),
				'thumbnail' => array(
					'source' => 'featured',
					'field' => '',
					'resize' => false,
					'default' => '',
					'responsive' => false
				),
				'log' => array(
					'level' => 1,
					'limit' => 0,
					'expires_after' => 180
				),
				'cache' => array(
					'active' => false,
					'interval' => array(
						'time' => 'hour',
						'value' => 1
					)
				),
				'sampling' => array(
					'active' => false,
					'rate' => 100
				)
			)
		);

		/**
		 * Admin page user settings.
		 *
		 * @since	2.3.3
		 * @var		array
		 */
		private $user_settings = array();

		/**
		 * Bots list.
		 *
		 * @since	3.0.0
		 * @var		array
		 */
		protected $botlist = array( 'bot', 'crawl', 'curl', 'facebookexternalhit', 'geturl', 'google', 'java', 'msn', 'perl', 'slurp', 'spider', 'sqworm', 'search', 'wget' );

		/*--------------------------------------------------*/
		/* Constructor
		/*--------------------------------------------------*/

		/**
		 * Initialize the widget by setting localization, filters, and administration functions.
		 *
		 * @since	1.0.0
		 */
		public function __construct() {

			// Load plugin text domain
			add_action( 'init', array( $this, 'widget_textdomain' ) );

			// Upgrade check
			add_action( 'init', array( $this, 'upgrade_check' ) );
			
			// Check location on template redirect
			add_action( 'template_redirect',  array( $this, 'is_single' ) );

			// Hook fired when a new blog is activated on WP Multisite
			add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

			// Notices check
			add_action( 'admin_notices', array( $this, 'check_admin_notices' ) );

			// Create the widget
			parent::__construct(
				'wpp',
				'WordPress Popular Posts',
				array(
					'classname'		=>	'popular-posts',
					'description'	=>	__( 'The most Popular Posts on your blog.', $this->plugin_slug )
				)
			);

			// Get user options
			$this->user_settings = get_site_option('wpp_settings_config');
			if ( !$this->user_settings ) {
				add_site_option('wpp_settings_config', $this->default_user_settings);
				$this->user_settings = $this->default_user_settings;
			} else {
				$this->user_settings = $this->__merge_array_r( $this->default_user_settings, $this->user_settings );
			}
			
			// Allow WP themers / coders to override data sampling status (active/inactive)
			$this->user_settings['tools']['sampling']['active'] = apply_filters( 'wpp_data_sampling', $this->user_settings['tools']['sampling']['active'] );

			// Add the options page and menu item.
			add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

			// Register admin styles and scripts
			add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );
			add_action( 'admin_init', array( $this, 'thickbox_setup' ) );

			// Register site styles and scripts
			if ( $this->user_settings['tools']['css'] )
				add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_scripts' ) );

			// Add plugin settings link
			add_filter( 'plugin_action_links', array( $this, 'add_plugin_settings_link' ), 10, 2 );

			// Set plugin directory
			$this->plugin_dir = plugin_dir_url(__FILE__);

			// Get blog charset
			$this->charset = get_bloginfo('charset');

			// Add ajax table truncation to wp_ajax_ hook
			add_action('wp_ajax_wpp_clear_data', array( $this, 'clear_data' ));
			
			// Add thumbnail cache truncation to wp_ajax_ hook
			add_action('wp_ajax_wpp_clear_thumbnail', array( $this, 'clear_thumbnails' ));

			// Add ajax hook for widget
			add_action('wp_ajax_wpp_get_popular', array( $this, 'get_popular') );
			add_action('wp_ajax_nopriv_wpp_get_popular', array( $this, 'get_popular') );

			// Check if images can be created
			if ( extension_loaded('ImageMagick') || (extension_loaded('GD') && function_exists('gd_info')) ) {
				// Enable thumbnail feature
				$this->thumbnailing = true;				
				// Get available thumbnail size(s)
				$this->default_thumbnail_sizes = $this->__get_image_sizes();
				// Add hook to flush cached thumbnail when image is changed
				add_action( 'update_postmeta', array( $this, 'flush_post_thumbnail'), 10, 4 );
			}

			// Set default thumbnail
			$this->default_thumbnail = $this->plugin_dir . "no_thumb.jpg";
			$this->default_user_settings['tools']['thumbnail']['default'] = $this->default_thumbnail;

			if ( !empty($this->user_settings['tools']['thumbnail']['default']) )
				$this->default_thumbnail = $this->user_settings['tools']['thumbnail']['default'];
			else
				$this->user_settings['tools']['thumbnail']['default'] = $this->default_thumbnail;
			
			// Set uploads folder
			$wp_upload_dir = ( function_exists('wp_get_upload_dir') ) ? wp_get_upload_dir() : wp_upload_dir(); // wp_get_upload_dir() was introduced in WP 4.5!
			$this->uploads_dir['basedir'] = $wp_upload_dir['basedir'] . "/" . $this->plugin_slug;
			$this->uploads_dir['baseurl'] = $wp_upload_dir['baseurl'] . "/" . $this->plugin_slug;

			if ( !is_dir($this->uploads_dir['basedir']) ) {
				if ( !wp_mkdir_p($this->uploads_dir['basedir']) ) {
					$this->uploads_dir['basedir'] = $wp_upload_dir['basedir'];
					$this->uploads_dir['baseurl'] = $wp_upload_dir['baseurl'];
				}
			}

			// qTrans plugin support
			if ( function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage') )
				$this->qTrans = true;

			// Remove post/page prefetching!
			remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
			// Add the update hooks only if the logging conditions are met
			if ( (0 == $this->user_settings['tools']['log']['level'] && !is_user_logged_in()) || (1 == $this->user_settings['tools']['log']['level']) || (2 == $this->user_settings['tools']['log']['level'] && is_user_logged_in()) ) {
				
				add_action( 'wp_head', array(&$this, 'print_ajax') );

				// Register views from everyone and/or connected users
				if ( 0 != $this->user_settings['tools']['log']['level'] )
					add_action( 'wp_ajax_update_views_ajax', array($this, 'update_views_ajax') );
				// Register views from everyone and/or visitors only
				if ( 2 != $this->user_settings['tools']['log']['level'] )
					add_action( 'wp_ajax_nopriv_update_views_ajax', array($this, 'update_views_ajax') );

			}

			// Add shortcode
			add_shortcode('wpp', array(&$this, 'shortcode'));
			
			// Purge post data from DB on deletion
			add_action( 'admin_init', array($this, 'purge_post_init') );

			// Enable data purging at midnight
			if ( 1 == $this->user_settings['tools']['log']['limit'] ) {
				
				add_action( 'wpp_cache_event', array($this, 'purge_data') );
				if ( !wp_next_scheduled('wpp_cache_event') ) {
					$tomorrow = time() + 86400;
					$midnight  = mktime(0, 0, 0,
						date("m", $tomorrow),
						date("d", $tomorrow),
						date("Y", $tomorrow));
					wp_schedule_event( $midnight, 'daily', 'wpp_cache_event' );
				}
				
			} else {
				// Remove the scheduled event if exists
				if ( $timestamp = wp_next_scheduled('wpp_cache_event') ) {
					wp_unschedule_event( $timestamp, 'wpp_cache_event' );
				}
				
			}

		} // end constructor

		/*--------------------------------------------------*/
		/* Widget API Functions
		/*--------------------------------------------------*/

		/**
		 * Outputs the content of the widget.
		 *
		 * @since	1.0.0
		 * @param	array	args		The array of form elements
		 * @param	array	instance	The current instance of the widget
		 */
		public function widget( $args, $instance ) {

			$this->__debug($args);

			/**
		     * @var String $name
		     * @var String $id
		     * @var String $description
		     * @var String $class
		     * @var String $before_widget
		     * @var String $after_widget
		     * @var String $before_title
		     * @var String $after_title
		     * @var String $widget_id
		     * @var String $widget_name
		     */
			extract( $args, EXTR_SKIP );

			$markup = ( $instance['markup']['custom_html'] || has_filter('wpp_custom_html') || has_filter('wpp_post') )
			  ? 'custom'
			  : 'regular';

			echo "\n". "<!-- WordPress Popular Posts Plugin v{$this->version} [W] [{$instance['range']}] [{$instance['order_by']}] [{$markup}]" . ( !empty($instance['pid']) ? " [PID]" : "" ) . ( !empty($instance['cat']) ? " [CAT]" : "" ) . ( !empty($instance['author']) ? " [UID]" : "" ) . " -->" . "\n";

			echo $before_widget . "\n";

			// has user set a title?
			if ( '' != $instance['title'] ) {

				$title = apply_filters( 'widget_title', $instance['title'] );

				if ($instance['markup']['custom_html'] && $instance['markup']['title-start'] != "" && $instance['markup']['title-end'] != "" ) {
					echo htmlspecialchars_decode($instance['markup']['title-start'], ENT_QUOTES) . $title . htmlspecialchars_decode($instance['markup']['title-end'], ENT_QUOTES);
				} else {
					echo $before_title . $title . $after_title;
				}
			}

			if ( $this->user_settings['tools']['ajax'] ) {
				if ( empty($before_widget) || !preg_match('/id="[^"]*"/', $before_widget) ) {
				?>
                <p><?php _e('Error: cannot ajaxify WordPress Popular Posts on this theme. It\'s missing the <em>id</em> attribute on before_widget (see <a href="http://codex.wordpress.org/Function_Reference/register_sidebar" target="_blank" rel="nofollow">register_sidebar</a> for more).', $this->plugin_slug ); ?></p>
                <?php
				} else {
				?>
                <script type="text/javascript">
					/* jQuery is available, so proceed */
					if ( window.jQuery ) {
						
						jQuery(document).ready(function($){
							$.get('<?php echo admin_url('admin-ajax.php'); ?>', {
								action: 'wpp_get_popular',
								id: '<?php echo $this->number; ?>'
							}, function(data){
								$('#<?php echo $widget_id; ?>').append(data);
							});
						});
					
					} /* jQuery is not defined */
					else {
						if ( window.console && window.console.log ) {
							window.console.log('WordPress Popular Posts: jQuery is not defined!');
						}
					}
                </script>
                <?php
				}
			} else {
				echo $this->__get_popular_posts( $instance );
			}

			echo $after_widget . "\n";
			echo "<!-- End WordPress Popular Posts Plugin v{$this->version} -->"."\n";

		} // end widget

		/**
		 * Processes the widget's options to be saved.
		 *
		 * @since	1.0.0
		 * @param	array	new_instance	The previous instance of values before the update.
		 * @param	array	old_instance	The new instance of values to be generated via the update.
		 * @return	array	instance		Updated instance.
		 */
		public function update( $new_instance, $old_instance ) {

			$instance = $old_instance;

			$instance['title'] = htmlspecialchars( stripslashes_deep(strip_tags( $new_instance['title'] )), ENT_QUOTES );
			$instance['limit'] = ( $this->__is_numeric($new_instance['limit']) && $new_instance['limit'] > 0 )
			  ? $new_instance['limit']
			  : 10;
			$instance['range'] = $new_instance['range'];
			$instance['order_by'] = $new_instance['order_by'];

			// FILTERS
			// user did not define the custom post type name, so we fall back to default
			$instance['post_type'] = ( '' == $new_instance['post_type'] )
			  ? 'post,page'
			  : $new_instance['post_type'];

			$instance['freshness'] = isset( $new_instance['freshness'] );

			$instance['pid'] = implode(",", array_filter(explode(",", preg_replace( '|[^0-9,]|', '', $new_instance['pid'] ))));
			$instance['cat'] = implode(",", array_filter(explode(",", preg_replace( '|[^0-9,-]|', '', $new_instance['cat'] ))));
			$instance['author'] = implode(",", array_filter(explode(",", preg_replace( '|[^0-9,]|', '', $new_instance['uid'] ))));

			$instance['shorten_title']['words'] = $new_instance['shorten_title-words'];
			$instance['shorten_title']['active'] = isset( $new_instance['shorten_title-active'] );
			$instance['shorten_title']['length'] = ( $this->__is_numeric($new_instance['shorten_title-length']) && $new_instance['shorten_title-length'] > 0 )
			  ? $new_instance['shorten_title-length']
			  : 25;

			$instance['post-excerpt']['keep_format'] = isset( $new_instance['post-excerpt-format'] );
			$instance['post-excerpt']['words'] = $new_instance['post-excerpt-words'];
			$instance['post-excerpt']['active'] = isset( $new_instance['post-excerpt-active'] );
			$instance['post-excerpt']['length'] = ( $this->__is_numeric($new_instance['post-excerpt-length']) && $new_instance['post-excerpt-length'] > 0 )
			  ? $new_instance['post-excerpt-length']
			  : 55;

			$instance['thumbnail']['active'] = false;
			$instance['thumbnail']['width'] = 15;
			$instance['thumbnail']['height'] = 15;

			// can create thumbnails
			if ( $this->thumbnailing ) {

				$instance['thumbnail']['active'] = isset( $new_instance['thumbnail-active'] );
				$instance['thumbnail']['build'] = $new_instance['thumbnail-size-source'];
				
				// Use predefined thumbnail sizes
				if ( 'predefined' == $new_instance['thumbnail-size-source'] ) {
					$size = $this->default_thumbnail_sizes[ $new_instance['thumbnail-size'] ];
					$instance['thumbnail']['width'] = $size['width'];
					$instance['thumbnail']['height'] = $size['height'];
					$instance['thumbnail']['crop'] = $size['crop'];
				} // Set thumbnail size manually
				else {
					if ($this->__is_numeric($new_instance['thumbnail-width']) && $this->__is_numeric($new_instance['thumbnail-height'])) {
						$instance['thumbnail']['width'] = $new_instance['thumbnail-width'];
						$instance['thumbnail']['height'] = $new_instance['thumbnail-height'];
						$instance['thumbnail']['crop'] = true;
					}
				}

			}

			$instance['rating'] = isset( $new_instance['rating'] );
			$instance['stats_tag']['comment_count'] = isset( $new_instance['comment_count'] );
			$instance['stats_tag']['views'] = isset( $new_instance['views'] );
			$instance['stats_tag']['author'] = isset( $new_instance['author'] );
			$instance['stats_tag']['date']['active'] = isset( $new_instance['date'] );
			$instance['stats_tag']['date']['format'] = empty($new_instance['date_format'])
			  ? 'F j, Y'
			  : $new_instance['date_format'];

			$instance['stats_tag']['category'] = isset( $new_instance['category'] );
			$instance['markup']['custom_html'] = isset( $new_instance['custom_html'] );
			$instance['markup']['wpp-start'] = empty($new_instance['wpp-start'])
			  ? htmlspecialchars( '<ul class="wpp-list">', ENT_QUOTES )
			  : htmlspecialchars( $new_instance['wpp-start'], ENT_QUOTES );

			$instance['markup']['wpp-end'] = empty($new_instance['wpp-end'])
			  ? htmlspecialchars( '</ul>', ENT_QUOTES )
			  : htmlspecialchars( $new_instance['wpp-end'], ENT_QUOTES );

			$instance['markup']['post-html'] = empty($new_instance['post-html'])
			  ? htmlspecialchars( '<li>{thumb} {title} {stats}</li>', ENT_QUOTES )
			  : htmlspecialchars( $new_instance['post-html'], ENT_QUOTES );

			$instance['markup']['title-start'] = empty($new_instance['title-start'])
			  ? ''
			  : htmlspecialchars( $new_instance['title-start'], ENT_QUOTES );

			$instance['markup']['title-end'] = empty($new_instance['title-end'])
			  ? '' :
			  htmlspecialchars( $new_instance['title-end'], ENT_QUOTES );

			return $instance;

		} // end widget

		/**
		 * Generates the administration form for the widget.
		 *
		 * @since	1.0.0
		 * @param	array	instance	The array of keys and values for the widget.
		 */
		public function form( $instance ) {

			// parse instance values
			$instance = $this->__merge_array_r(
				$this->defaults,
				$instance
			);

			// Display the admin form
			include( plugin_dir_path(__FILE__) . '/views/form.php' );

		} // end form

		/*--------------------------------------------------*/
		/* Public methods
		/*--------------------------------------------------*/

		/**
		 * Loads the Widget's text domain for localization and translation.
		 *
		 * @since	1.0.0
		 */
		public function widget_textdomain() {

			$domain = $this->plugin_slug;
			$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

			load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
			load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

		} // end widget_textdomain

		/**
		 * Registers and enqueues admin-specific styles.
		 *
		 * @since	1.0.0
		 */
		public function register_admin_styles() {

			if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
				return;
			}

			$screen = get_current_screen();
			if ( $screen->id == $this->plugin_screen_hook_suffix ) {
				wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'style/admin.css', __FILE__ ), array(), $this->version );
			}

		} // end register_admin_styles

		/**
		 * Registers and enqueues admin-specific JavaScript.
		 *
		 * @since	2.3.4
		 */
		public function register_admin_scripts() {

			if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
				return;
			}

			$screen = get_current_screen();
			if ( $screen->id == $this->plugin_screen_hook_suffix ) {
				wp_enqueue_script( 'thickbox' );
				wp_enqueue_style( 'thickbox' );
				wp_enqueue_script( 'media-upload' );
				wp_enqueue_script( $this->plugin_slug .'-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array('jquery'), $this->version, true );
			}

		} // end register_admin_scripts

		/**
		 * Hooks into getttext to change upload button text when uploader is called by WPP.
		 *
		 * @since	2.3.4
		 */
		function thickbox_setup() {

			global $pagenow;
			if ( 'media-upload.php' == $pagenow || 'async-upload.php' == $pagenow ) {
				add_filter( 'gettext', array( $this, 'replace_thickbox_text' ), 1, 3 );
			}

		} // end thickbox_setup

		/**
		 * Replaces upload button text when uploader is called by WPP.
		 *
		 * @since	2.3.4
		 * @param	string	translated_text
		 * @param	string	text
		 * @param	string	domain
		 * @return	string
		 */
		function replace_thickbox_text($translated_text, $text, $domain) {

			if ('Insert into Post' == $text) {
				$referer = strpos( wp_get_referer(), 'wpp_admin' );
				if ( $referer != '' ) {
					return __('Upload', $this->plugin_slug );
				}
			}

			return $translated_text;

		} // end replace_thickbox_text

		/**
		 * Registers and enqueues widget-specific styles.
		 *
		 * @since	1.0.0
		 */
		public function register_widget_styles() {

			$theme_file = get_stylesheet_directory() . '/wpp.css';
			$plugin_file = plugin_dir_path(__FILE__) . 'style/wpp.css';

			if ( @file_exists($theme_file) ) { // user stored a custom wpp.css on theme's directory, so use it
				wp_enqueue_style( $this->plugin_slug, get_stylesheet_directory_uri() . "/wpp.css", array(), $this->version );
			} elseif ( @file_exists($plugin_file) ) { // no custom wpp.css, use plugin's instead
				wp_enqueue_style( $this->plugin_slug, plugins_url( 'style/wpp.css', __FILE__ ), array(), $this->version );
			}

		} // end register_widget_styles
		
		/**
		 * Registers and enqueues widget-specific scripts.
		 */
		public function register_widget_scripts() {
			// We need jQuery in the front-end only when ajaxifying the widget
			if ( $this->user_settings['tools']['ajax'] )
				wp_enqueue_script( 'jquery' );
		} // end register_widget_scripts

		/**
		 * Register the administration menu for this plugin into the WordPress Dashboard menu.
		 *
		 * @since    1.0.0
		 */
		public function add_plugin_admin_menu() {

			$this->plugin_screen_hook_suffix = add_options_page(
				'WordPress Popular Posts',
				'WordPress Popular Posts',
				'manage_options',
				$this->plugin_slug,
				array( $this, 'display_plugin_admin_page' )
			);

		}

		/**
		 * Render the settings page for this plugin.
		 *
		 * @since    1.0.0
		 */
		public function display_plugin_admin_page() {
			include_once( 'views/admin.php' );
		}

		/**
		 * Registers Settings link on plugin description.
		 *
		 * @since	2.3.3
		 * @param	array	links
		 * @param	string	file
		 * @return	array
		 */
		public function add_plugin_settings_link( $links, $file ){

			$this_plugin = plugin_basename(__FILE__);

			if ( is_plugin_active($this_plugin) && $file == $this_plugin ) {
				$links[] = '<a href="' . admin_url( 'options-general.php?page=wordpress-popular-posts' ) . '">Settings</a>';
			}

			return $links;

		} // end add_plugin_settings_link

		/*--------------------------------------------------*/
		/* Install / activation / deactivation methods
		/*--------------------------------------------------*/

		/**
		 * Return an instance of this class.
		 *
		 * @since     3.0.0
		 * @return    object    A single instance of this class.
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( NULL == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;

		} // end get_instance

		/**
		 * Fired when the plugin is activated.
		 *
		 * @since	1.0.0
		 * @global	object	wpdb
		 * @param	bool	network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
		 */
		public static function activate( $network_wide ) {

			global $wpdb;

			if ( function_exists( 'is_multisite' ) && is_multisite() ) {

				// run activation for each blog in the network
				if ( $network_wide ) {

					$original_blog_id = get_current_blog_id();
					$blogs_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

					foreach( $blogs_ids as $blog_id ) {
						switch_to_blog( $blog_id );
						self::__activate();
					}

					// switch back to current blog
					switch_to_blog( $original_blog_id );

					return;

				}

			}

			self::__activate();

		} // end activate

		/**
		 * Fired when a new blog is activated on WP Multisite.
		 *
		 * @since	3.0.0
		 * @param	int	blog_id	New blog ID
		 */
		public function activate_new_site( $blog_id ){

			if ( 1 !== did_action( 'wpmu_new_blog' ) )
				return;

			// run activation for the new blog
			switch_to_blog( $blog_id );
			self::__activate();

			// switch back to current blog
			restore_current_blog();

		} // end activate_new_site

		/**
		 * On plugin activation, checks that the WPP database tables are present.
		 *
		 * @since	2.4.0
		 * @global	object	wpdb
		 */
		private static function __activate() {

			global $wpdb;

			// set table name
			$prefix = $wpdb->prefix . "popularposts";

			// fresh setup
			if ( $prefix != $wpdb->get_var("SHOW TABLES LIKE '{$prefix}data'") ) {
				self::__do_db_tables( $prefix );
			}

		} // end __activate

		/**
		 * Fired when the plugin is deactivated.
		 *
		 * @since	1.0.0
		 * @global	object	wpbd
		 * @param	bool	network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
		 */
		public static function deactivate( $network_wide ) {

			global $wpdb;

			if ( function_exists( 'is_multisite' ) && is_multisite() ) {

				// Run deactivation for each blog in the network
				if ( $network_wide ) {

					$original_blog_id = get_current_blog_id();
					$blogs_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

					foreach( $blogs_ids as $blog_id ) {
						switch_to_blog( $blog_id );
						self::__deactivate();
					}

					// Switch back to current blog
					switch_to_blog( $original_blog_id );

					return;

				}

			}

			self::__deactivate();

		} // end deactivate

		/**
		 * On plugin deactivation, disables the shortcode and removes the scheduled task.
		 *
		 * @since	2.4.0
		 */
		private static function __deactivate() {

			remove_shortcode('wpp');
			wp_clear_scheduled_hook('wpp_cache_event');

		} // end __deactivate

		/**
		 * Checks if an upgrade procedure is required.
		 *
		 * @since	2.4.0
		 */
		public function upgrade_check(){

			// Get WPP version
			$wpp_ver = get_site_option('wpp_ver');

			if ( !$wpp_ver ) {
				add_site_option('wpp_ver', $this->version);
			} elseif ( version_compare($wpp_ver, $this->version, '<') ) {
				$this->__upgrade();
			}

		} // end upgrade_check

		/**
		 * On plugin upgrade, performs a number of actions: update WPP database tables structures (if needed),
		 * run the setup wizard (if needed), and some other checks.
		 *
		 * @since	2.4.0
		 * @global	object	wpdb
		 */
		private function __upgrade() {

			// Keep the upgrade process from running too many times
			if ( get_site_option('wpp_update') )
				return;
			
			add_site_option( 'wpp_update', 1 );

			global $wpdb;

			// set table name
			$prefix = $wpdb->prefix . "popularposts";

			// validate the structure of the tables and create missing tables
			self::__do_db_tables( $prefix );

			// If summary is empty, import data from popularpostsdatacache
			if ( !$wpdb->get_var("SELECT COUNT(*) FROM {$prefix}summary") ) {

				// popularpostsdatacache table is still there
				if ( $wpdb->get_var("SHOW TABLES LIKE '{$prefix}datacache'") ) {

					$sql = "
					INSERT INTO {$prefix}summary (postid, pageviews, view_date, last_viewed)
					SELECT id, pageviews, day_no_time, day
					FROM {$prefix}datacache
					GROUP BY day_no_time, id
					ORDER BY day_no_time DESC";

					$result = $wpdb->query( $sql );

				}

			}
			
			// Deletes old caching tables, if found
			$wpdb->query( "DROP TABLE IF EXISTS {$prefix}datacache, {$prefix}datacache_backup;" );

			// Check storage engine
			$storage_engine_data = $wpdb->get_var("SELECT `ENGINE` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA`='{$wpdb->dbname}' AND `TABLE_NAME`='{$prefix}data';");
			
			if ( 'InnoDB' != $storage_engine_data ) {
				$wpdb->query("ALTER TABLE {$prefix}data ENGINE=INNODB;");
			}
			
			$storage_engine_summary = $wpdb->get_var("SELECT `ENGINE` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA`='{$wpdb->dbname}' AND `TABLE_NAME`='{$prefix}summary';");
			
			if ( 'InnoDB' != $storage_engine_summary ) {
				$wpdb->query("ALTER TABLE {$prefix}summary ENGINE=INNODB;");
			}

			// Update WPP version
			update_site_option('wpp_ver', $this->version);
			
			// Remove upgrade flag
			delete_site_option('wpp_update');

		} // end __upgrade

		/**
		 * Creates/updates the WPP database tables.
		 *
		 * @since	2.4.0
		 * @global	object	wpdb
		 */
		private static function __do_db_tables( $prefix ) {

			global $wpdb;

			$sql = "";
			$charset_collate = "";

			if ( !empty($wpdb->charset) )
				$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset} ";

			if ( !empty($wpdb->collate) )
				$charset_collate .= "COLLATE {$wpdb->collate}";

			$sql = "
				CREATE TABLE {$prefix}data (
					postid bigint(20) NOT NULL,
					day datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					last_viewed datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					pageviews bigint(20) DEFAULT 1,
					PRIMARY KEY  (postid)
				) {$charset_collate} ENGINE=INNODB;
				CREATE TABLE {$prefix}summary (
					ID bigint(20) NOT NULL AUTO_INCREMENT,
					postid bigint(20) NOT NULL,
					pageviews bigint(20) NOT NULL DEFAULT 1,
					view_date date NOT NULL DEFAULT '0000-00-00',
					last_viewed datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					PRIMARY KEY  (ID),
					UNIQUE KEY ID_date (postid,view_date),
					KEY postid (postid),
					KEY view_date (view_date),
					KEY last_viewed (last_viewed)
				) {$charset_collate} ENGINE=INNODB;";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);

		} // end __do_db_tables

		/**
		 * Checks if the technical requirements are met.
		 *
		 * @since	2.4.0
		 * @link	http://wordpress.stackexchange.com/questions/25910/uninstall-activate-deactivate-a-plugin-typical-features-how-to/25979#25979
		 * @global	string $wp_version
		 * @return	array
		 */
		private function __check_requirements() {

			global $wp_version;

			$php_min_version = '5.2';
			$wp_min_version = '3.8';
			$php_current_version = phpversion();
			$errors = array();

			if ( version_compare( $php_min_version, $php_current_version, '>' ) ) {
				$errors[] = sprintf(
					__( 'Your PHP installation is too old. WordPress Popular Posts requires at least PHP version %1$s to function correctly. Please contact your hosting provider and ask them to upgrade PHP to %1$s or higher.', $this->plugin_slug ),
					$php_min_version
				);
			}

			if ( version_compare( $wp_min_version, $wp_version, '>' ) ) {
				$errors[] = sprintf(
					__( 'Your WordPress version is too old. WordPress Popular Posts requires at least WordPress version %1$s to function correctly. Please update your blog via Dashboard &gt; Update.', $this->plugin_slug ),
					$wp_min_version
				);
			}

			return $errors;

		} // end __check_requirements

		/**
		 * Outputs error messages to wp-admin.
		 *
		 * @since	2.4.0
		 */
		public function check_admin_notices() {

			$errors = $this->__check_requirements();

			if ( empty($errors) )
				return;

			if ( isset($_GET['activate']) )
				unset($_GET['activate']);

			printf(
				__('<div class="error"><p>%1$s</p><p><i>%2$s</i> has been <strong>deactivated</strong>.</p></div>', $this->plugin_slug),
				join( '</p><p>', $errors ),
				'WordPress Popular Posts'
			);

			deactivate_plugins( plugin_basename( __FILE__ ) );

		} // end check_admin_notices


		/*--------------------------------------------------*/
		/* Plugin methods / functions
		/*--------------------------------------------------*/

		/**
		 * Purges post from data/summary tables.
		 *
		 * @since	3.3.0
		 */
		public function purge_post_init() {

			if ( current_user_can( 'delete_posts' ) )
				add_action( 'delete_post', array( $this, 'purge_post' ), 10 );

		} // end purge_post_init

		/**
		 * Purges post from data/summary tables.
		 *
		 * @since	3.3.0
		 * @global	object	$wpdb
		 * @return	bool
		 */
		public function purge_post( $pID ) {

			global $wpdb;

			if ( $wpdb->get_var( $wpdb->prepare( "SELECT postid FROM {$wpdb->prefix}popularpostsdata WHERE postid = %d", $pID ) ) ) {
				// Delete from data table
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}popularpostsdata WHERE postid = %d;", $pID ) );
				// Delete from summary table
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}popularpostssummary WHERE postid = %d;", $pID ) );				
			}
			
			return true;

		} // end purge_post

		/**
		 * Purges old post data from summary table.
		 *
		 * @since	2.0.0
		 * @global	object	$wpdb
		 */
		public function purge_data() {

			global $wpdb;

			$wpdb->query( "DELETE FROM {$wpdb->prefix}popularpostssummary WHERE view_date < DATE_SUB('{$this->__curdate()}', INTERVAL {$this->user_settings['tools']['log']['expires_after']} DAY);" );

		} // end purge_data

		/**
		 * Truncates data and cache on demand.
		 *
		 * @since	2.0.0
		 * @global	object	wpdb
		 */
		public function clear_data() {

			$token = $_POST['token'];
			$clear = isset($_POST['clear']) ? $_POST['clear'] : '';
			$key = get_site_option("wpp_rand");

			if (current_user_can('manage_options') && ($token === $key) && !empty($clear)) {
				global $wpdb;

				// set table name
				$prefix = $wpdb->prefix . "popularposts";

				if ($clear == 'cache') {
					if ( $wpdb->get_var("SHOW TABLES LIKE '{$prefix}summary'") ) {
						$wpdb->query("TRUNCATE TABLE {$prefix}summary;");
						$this->__flush_transients();
						
						echo 1;
					} else {
						echo 2;
					}
				} else if ($clear == 'all') {
					if ( $wpdb->get_var("SHOW TABLES LIKE '{$prefix}data'") && $wpdb->get_var("SHOW TABLES LIKE '{$prefix}summary'") ) {
						$wpdb->query("TRUNCATE TABLE {$prefix}data;");
						$wpdb->query("TRUNCATE TABLE {$prefix}summary;");
						$this->__flush_transients();
						
						echo 1;
					} else {
						echo 2;
					}
				} else {
					echo 3;
				}
			} else {
				echo 4;
			}

			die();

		} // end clear_data
		
		/**
		 * Flushes post's cached thumbnail(s) when the image is changed.
		 *
		 * @since	3.3.4
		 *
		 * @param	integer		$meta_id     ID of the meta data field
		 * @param	integer		$object_id   Object ID
		 * @param	string		$meta_key    Name of meta field
		 * @param	string		$meta_value  Value of meta field
		 */
		public function flush_post_thumbnail( $meta_id, $object_id, $meta_key, $meta_value ) {
			
			$files = null;
			
			// User changed the featured image
			if ( '_thumbnail_id' == $meta_key ) {
				$files = glob( "{$this->uploads_dir['basedir']}/{$object_id}-featured-*.*" ); // get all related images
			}
			
			if ( is_array($files) && !empty($files) ) {					
				foreach( $files as $file ){ // iterate files
					if ( is_file($file) )
						@unlink($file); // delete file
				}
			}
			
		}
		
		/**
		 * Truncates thumbnails cache on demand.
		 *
		 * @since	2.0.0
		 * @global	object	wpdb
		 */
		public function clear_thumbnails() {

			$token = $_POST['token'];			
			$key = get_site_option("wpp_rand");			

			if ( current_user_can('manage_options') && ($token === $key) ) {
				$wp_upload_dir = wp_upload_dir();
				
				if ( is_dir( $wp_upload_dir['basedir'] . "/" . $this->plugin_slug ) ) {
					$files = glob( $wp_upload_dir['basedir'] . "/" . $this->plugin_slug . "/*" ); // get all file names
					
					if ( is_array($files) && !empty($files) ) {					
						foreach($files as $file){ // iterate files
							if ( is_file($file) )
								@unlink($file); // delete file
						}
						
						echo 1;
					} else {
						echo 2;
					}
				} else {
					echo 3;
				}
			} else {
				echo 4;
			}

			die();

		} // end clear_data

		/**
		 * Updates views count on page load via AJAX.
		 *
		 * @since	2.0.0
		 */
		public function update_views_ajax(){

			if ( !wp_verify_nonce($_POST['token'], 'wpp-token') || !$this->__is_numeric($_POST['wpp_id']) )
				die("WPP: Oops, invalid request!");

			$nonce = $_POST['token'];
			$post_ID = $_POST['wpp_id'];

			$exec_time = 0;

			$start = $this->__microtime_float();
			$result = $this->__update_views($post_ID);
			$end = $this->__microtime_float();

			$exec_time += round($end - $start, 6);

			if ( $result ) {
				die( "WPP: OK. Execution time: " . $exec_time . " seconds" );
			}

			die( "WPP: Oops, could not update the views count!" );

		} // end update_views_ajax

		/**
		 * Outputs script to update views via AJAX.
		 *
		 * @since	2.0.0
		 * @global	object	post
		 */
		public function print_ajax(){

			if ( 0 != $this->current_post_id ) {
				?>
				<!-- WordPress Popular Posts v<?php echo $this->version; ?> -->
				<script type="text/javascript">

					var sampling_active = <?php echo ( $this->user_settings['tools']['sampling']['active'] ) ? 1 : 0; ?>;
					var sampling_rate   = <?php echo intval( $this->user_settings['tools']['sampling']['rate'] ); ?>;
					var do_request = false;

					if ( !sampling_active ) {
						do_request = true;
					} else {
						var num = Math.floor(Math.random() * sampling_rate) + 1;
						do_request = ( 1 === num );
					}

					if ( do_request ) {

						/* Create XMLHttpRequest object and set variables */
						var xhr = ( window.XMLHttpRequest )
						  ? new XMLHttpRequest()
						  : new ActiveXObject( "Microsoft.XMLHTTP" ),
						url = '<?php echo admin_url('admin-ajax.php', is_ssl() ? 'https' : 'http'); ?>',
						params = 'action=update_views_ajax&token=<?php echo wp_create_nonce('wpp-token') ?>&wpp_id=<?php echo $this->current_post_id; ?>';
						/* Set request method and target URL */
						xhr.open( "POST", url, true );
						/* Set request header */
						xhr.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" );
						/* Hook into onreadystatechange */
						xhr.onreadystatechange = function() {
							if ( 4 === xhr.readyState && 200 === xhr.status ) {
								if ( window.console && window.console.log ) {
									window.console.log( xhr.responseText );
								}
							}
						};
						/* Send request */
						xhr.send( params );

					}

				</script>
				<!-- End WordPress Popular Posts v<?php echo $this->version; ?> -->
				<?php
			}

		} // end print_ajax

		/**
		 * Deletes cached (transient) data.
		 *
		 * @since	3.0.0
		 */
		private function __flush_transients() {

			$wpp_transients = get_site_option('wpp_transients');

			if ( $wpp_transients && is_array($wpp_transients) && !empty($wpp_transients) ) {
				for ($t=0; $t < count($wpp_transients); $t++)
					delete_transient( $wpp_transients[$t] );

				update_site_option('wpp_transients', array());
			}

		} // end __flush_transients

		/**
		 * Updates views count.
		 *
		 * @since	1.4.0
		 * @global	object	$wpdb
		 * @param	int				Post ID
		 * @return	bool|int		FALSE if query failed, TRUE on success
		 */
		private function __update_views($id) {

			/*
			TODO:
			For WordPress Multisite, we must define the DIEONDBERROR constant for database errors to display like so:
			<?php define( 'DIEONDBERROR', true ); ?>
			*/

			global $wpdb;
			$table = $wpdb->prefix . "popularposts";
			$wpdb->show_errors();

			// WPML support, get original post/page ID
			if ( defined('ICL_LANGUAGE_CODE') && function_exists('icl_object_id') ) {
				global $sitepress;
				if ( isset( $sitepress )) { // avoids a fatal error with Polylang
					$id = icl_object_id( $id, get_post_type( $id ), true, $sitepress->get_default_language() );
				}
				else if ( function_exists( 'pll_default_language' ) ) { // adds Polylang support
					$id = icl_object_id( $id, get_post_type( $id ), true, pll_default_language() );
				}
			}

			$now = $this->__now();
			$curdate = $this->__curdate();
			$views = ( $this->user_settings['tools']['sampling']['active'] )
			  ? $this->user_settings['tools']['sampling']['rate']
			  : 1;
			
			// Allow WP themers / coders perform an action
			// before updating views count
			if ( has_action( 'wpp_pre_update_views' ) )
				do_action( 'wpp_pre_update_views', $id, $views );

			// Update all-time table
			$result1 = $wpdb->query( $wpdb->prepare(
				"INSERT INTO {$table}data
				(postid, day, last_viewed, pageviews) VALUES (%d, %s, %s, %d)
				ON DUPLICATE KEY UPDATE pageviews = pageviews + %4\$d, last_viewed = '%3\$s';",
				$id,
				$now,
				$now,
				$views
			));

			// Update range (summary) table
			$result2 = $wpdb->query( $wpdb->prepare(
				"INSERT INTO {$table}summary
				(postid, pageviews, view_date, last_viewed) VALUES (%d, %d, %s, %s)
				ON DUPLICATE KEY UPDATE pageviews = pageviews + %2\$d, last_viewed = '%4\$s';",
				$id,
				$views,
				$curdate,
				$now
			));

			if ( !$result1 || !$result2 )
				return false;

			// Allow WP themers / coders perform an action
			// after updating views count
			if ( has_action( 'wpp_post_update_views' ) )
				do_action( 'wpp_post_update_views', $id );

			return true;

		} // end __update_views

		/**
		 * Queries the database and returns the posts (if any met the criteria set by the user).
		 *
		 * @since	1.4.0
		 * @global	object 		$wpdb
		 * @param	array		Widget instance
		 * @return	null|array	Array of posts, or null if nothing was found
		 */
		protected function _query_posts($instance) {

			global $wpdb;

			// parse instance values
			$instance = $this->__merge_array_r(
				$this->defaults,
				$instance
			);

			$prefix = $wpdb->prefix . "popularposts";
			$fields = "p.ID AS 'id', p.post_title AS 'title', p.post_date AS 'date', p.post_author AS 'uid'";
			$from = "";
			$where = "WHERE 1 = 1";
			$orderby = "";
			$groupby = "";
			$limit = "LIMIT {$instance['limit']}";

			$post_types = "";
			$pids = "";
			$cats = "";
			$authors = "";
			$content = "";

			$now = $this->__now();

			// post filters
			// * freshness - get posts published within the selected time range only
			if ( $instance['freshness'] ) {
				switch( $instance['range'] ){
					case "daily":
						$where .= " AND p.post_date > DATE_SUB('{$now}', INTERVAL 1 DAY) ";
					break;

					case "weekly":
						$where .= " AND p.post_date > DATE_SUB('{$now}', INTERVAL 1 WEEK) ";
					break;

					case "monthly":
						$where .= " AND p.post_date > DATE_SUB('{$now}', INTERVAL 1 MONTH) ";
					break;

					default:
						$where .= "";
					break;
				}
			}

			// * post types - based on code seen at https://github.com/williamsba/WordPress-Popular-Posts-with-Custom-Post-Type-Support
			$types = explode(",", $instance['post_type']);
			$sql_post_types = "";
			$join_cats = true;

			// if we're getting just pages, why join the categories table?
			if ( 'page' == strtolower($instance['post_type']) ) {

				$join_cats = false;
				$where .= " AND p.post_type = '{$instance['post_type']}'";

			}
			// we're listing other custom type(s)
			else {

				if ( count($types) > 1 ) {

					foreach ( $types as $post_type ) {
						$post_type = trim($post_type); // required in case user places whitespace between commas
						$sql_post_types .= "'{$post_type}',";
					}

					$sql_post_types = rtrim( $sql_post_types, ",");
					$where .= " AND p.post_type IN({$sql_post_types})";

				} else {
					$where .= " AND p.post_type = '{$instance['post_type']}'";
				}

			}

			// * posts exclusion
			if ( !empty($instance['pid']) ) {

				$ath = explode(",", $instance['pid']);

				$where .= ( count($ath) > 1 )
				  ? " AND p.ID NOT IN({$instance['pid']})"
				  : " AND p.ID <> '{$instance['pid']}'";

			}

			// * categories
			if ( !empty($instance['cat']) && $join_cats ) {

				$cat_ids = explode(",", $instance['cat']);
				$in = array();
				$out = array();

				for ($i=0; $i < count($cat_ids); $i++) {
					if ($cat_ids[$i] >= 0)
						$in[] = $cat_ids[$i];
					else
						$out[] = $cat_ids[$i];
				}

				$in_cats = implode(",", $in);
				$out_cats = implode(",", $out);
				$out_cats = preg_replace( '|[^0-9,]|', '', $out_cats );

				if ($in_cats != "" && $out_cats == "") { // get posts from from given cats only
					$where .= " AND p.ID IN (
						SELECT object_id
						FROM {$wpdb->term_relationships} AS r
							 JOIN {$wpdb->term_taxonomy} AS x ON x.term_taxonomy_id = r.term_taxonomy_id
						WHERE x.taxonomy = 'category' AND x.term_id IN({$in_cats})
						)";
				} else if ($in_cats == "" && $out_cats != "") { // exclude posts from given cats only
					$where .= " AND p.ID NOT IN (
						SELECT object_id
						FROM {$wpdb->term_relationships} AS r
							 JOIN {$wpdb->term_taxonomy} AS x ON x.term_taxonomy_id = r.term_taxonomy_id
						WHERE x.taxonomy = 'category' AND x.term_id IN({$out_cats})
						)";
				} else { // mixed
					$where .= " AND p.ID IN (
						SELECT object_id
						FROM {$wpdb->term_relationships} AS r
							 JOIN {$wpdb->term_taxonomy} AS x ON x.term_taxonomy_id = r.term_taxonomy_id
						WHERE x.taxonomy = 'category' AND x.term_id IN({$in_cats}) AND x.term_id NOT IN({$out_cats})
						) ";
				}

			}

			// * authors
			if ( !empty($instance['author']) ) {

				$ath = explode(",", $instance['author']);

				$where .= ( count($ath) > 1 )
				  ? " AND p.post_author IN({$instance['author']})"
				  : " AND p.post_author = '{$instance['author']}'";

			}

			// All-time range
			if ( "all" == $instance['range'] ) {

				$fields .= ", p.comment_count AS 'comment_count'";

				// order by comments
				if ( "comments" == $instance['order_by'] ) {

					$from = "{$wpdb->posts} p";
					$where .= " AND p.comment_count > 0 ";
					$orderby = " ORDER BY p.comment_count DESC";

					// get views, too
					if ( $instance['stats_tag']['views'] ) {

						$fields .= ", IFNULL(v.pageviews, 0) AS 'pageviews'";
						$from .= " LEFT JOIN {$prefix}data v ON p.ID = v.postid";

					}

				}
				// order by (avg) views
				else {

					$from = "{$prefix}data v LEFT JOIN {$wpdb->posts} p ON v.postid = p.ID";

					// order by views
					if ( "views" == $instance['order_by'] ) {

						$fields .= ", v.pageviews AS 'pageviews'";
						$orderby = "ORDER BY pageviews DESC";

					}
					// order by avg views
					elseif ( "avg" == $instance['order_by'] ) {

						$fields .= ", ( v.pageviews/(IF ( DATEDIFF('{$now}', MIN(v.day)) > 0, DATEDIFF('{$now}', MIN(v.day)), 1) ) ) AS 'avg_views'";
						$groupby = "GROUP BY v.postid";
						$orderby = "ORDER BY avg_views DESC";

					}

				}

			} else { // CUSTOM RANGE

				$interval = "";

				switch( $instance['range'] ){
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

				// order by comments
				if ( "comments" == $instance['order_by'] ) {

					$fields .= ", COUNT(c.comment_post_ID) AS 'comment_count'";
					$from = "{$wpdb->comments} c LEFT JOIN {$wpdb->posts} p ON c.comment_post_ID = p.ID";
					$where .= " AND c.comment_date_gmt > DATE_SUB('{$now}', INTERVAL {$interval}) AND c.comment_approved = 1 ";
					$groupby = "GROUP BY c.comment_post_ID";
					$orderby = "ORDER BY comment_count DESC";

					if ( $instance['stats_tag']['views'] ) { // get views, too

						$fields .= ", IFNULL(v.pageviews, 0) AS 'pageviews'";
						$from .= " LEFT JOIN (SELECT postid, SUM(pageviews) AS pageviews FROM {$prefix}summary WHERE last_viewed > DATE_SUB('{$now}', INTERVAL {$interval}) GROUP BY postid) v ON p.ID = v.postid";

					}

				}
				// ordered by views / avg
				else {

					$from = "{$prefix}summary v LEFT JOIN {$wpdb->posts} p ON v.postid = p.ID";
					$where .= " AND v.last_viewed > DATE_SUB('{$now}', INTERVAL {$interval}) ";
					$groupby = "GROUP BY v.postid";

					// ordered by views
					if ( "views" == $instance['order_by'] ) {
						
						$fields .= ", SUM(v.pageviews) AS 'pageviews'";
						$orderby = "ORDER BY pageviews DESC";
						
					}
					// ordered by avg views
					elseif ( "avg" == $instance['order_by'] ) {

						$fields .= ", ( SUM(v.pageviews)/(IF ( DATEDIFF('{$now}', DATE_SUB('{$now}', INTERVAL {$interval})) > 0, DATEDIFF('{$now}', DATE_SUB('{$now}', INTERVAL {$interval})), 1) ) ) AS 'avg_views' ";						
						$orderby = "ORDER BY avg_views DESC";

					}

					// get comments, too
					if ( $instance['stats_tag']['comment_count'] ) {

						$fields .= ", IFNULL(c.comment_count, 0) AS 'comment_count'";
						$from .= " LEFT JOIN (SELECT comment_post_ID, COUNT(comment_post_ID) AS 'comment_count' FROM {$wpdb->comments} WHERE comment_date_gmt > DATE_SUB('{$now}', INTERVAL {$interval}) AND comment_approved = 1 GROUP BY comment_post_ID) c ON p.ID = c.comment_post_ID";

					}

				}

			}

			// List only published, non password-protected posts
			$where .= " AND p.post_password = '' AND p.post_status = 'publish'";

			// Build query
			$query = "SELECT {$fields} FROM {$from} {$where} {$groupby} {$orderby} {$limit};";

			$this->__debug( $query );

			$result = $wpdb->get_results($query);

			return apply_filters( 'wpp_query_posts', $result, $instance );

		} // end query_posts

		/**
		 * Returns the formatted list of posts.
		 *
		 * @since	3.0.0
		 * @param	array	instance	The current instance of the widget / shortcode parameters
		 * @return	string	HTML list of popular posts
		 */
		private function __get_popular_posts( $instance ) {

			// Parse instance values
			$instance = $this->__merge_array_r(
				$this->defaults,
				$instance
			);
			
			// Pass the widget ID, might come in handy
			if ( isset($this->id) )
				$instance['widget_id'] = $this->id;

			$content = "";

			// Fetch posts
			if ( !defined('WPP_ADMIN') && $this->user_settings['tools']['cache']['active'] ) {
				$transient_name = md5(json_encode($instance));
				$mostpopular = ( function_exists( 'is_multisite' ) && is_multisite() )
				  ? get_site_transient( $transient_name )
				  : get_transient( $transient_name );

				$content = "\n" . "<!-- cached -->" . "\n";

				// It wasn't there, so regenerate the data and save the transient
				if ( false === $mostpopular ) {
					$mostpopular = $this->_query_posts( $instance );

					switch($this->user_settings['tools']['cache']['interval']['time']){
						case 'minute':
							$time = 60;
						break;

						case 'hour':
							$time = 60 * 60;
						break;

						case 'day':
							$time = 60 * 60 * 24;
						break;

						case 'week':
							$time = 60 * 60 * 24 * 7;
						break;

						case 'month':
							$time = 60 * 60 * 24 * 30;
						break;

						case 'year':
							$time = 60 * 60 * 24 * 365;
						break;
					}

					$expiration = $time * $this->user_settings['tools']['cache']['interval']['value'];

					if ( function_exists( 'is_multisite' ) && is_multisite() )
						set_site_transient( $transient_name, $mostpopular, $expiration );
					else
						set_transient( $transient_name, $mostpopular, $expiration );

					$wpp_transients = get_site_option('wpp_transients');

					if ( !$wpp_transients ) {
						$wpp_transients = array( $transient_name );
						add_site_option('wpp_transients', $wpp_transients);
					} else {
						if ( !in_array($transient_name, $wpp_transients) ) {
							$wpp_transients[] = $transient_name;
							update_site_option('wpp_transients', $wpp_transients);
						}
					}
				}
			} else {
				$mostpopular = $this->_query_posts( $instance );
			}

			// No posts to show
			if ( !is_array($mostpopular) || empty($mostpopular) ) {
				return apply_filters( 'wpp_no_data', "<p class=\"wpp-no-data\">" . __('Sorry. No data so far.', $this->plugin_slug) . "</p>" );
			}

			// Allow WP themers / coders access to raw data
			// so they can build their own output
			if ( has_filter( 'wpp_custom_html' ) && !defined('WPP_ADMIN') ) {
				return apply_filters( 'wpp_custom_html', $mostpopular, $instance );
			}

			// HTML wrapper
			if ($instance['markup']['custom_html']) {
				$content .= "\n" . htmlspecialchars_decode($instance['markup']['wpp-start'], ENT_QUOTES) ."\n";
			} else {
				$content .= "\n" . "<ul class=\"wpp-list\">" . "\n";
			}

			// Loop through posts
			foreach($mostpopular as $p) {
				$content .= $this->__render_popular_post( $p, $instance );
			}

			// END HTML wrapper
			if ($instance['markup']['custom_html']) {
				$content .= "\n". htmlspecialchars_decode($instance['markup']['wpp-end'], ENT_QUOTES) ."\n";
			} else {
				$content .= "\n". "</ul>". "\n";
			}

			return $content;

		} // end __get_popular_posts

		/**
		 * Returns the formatted post.
		 *
		 * @since	3.0.0
		 * @param	object	p
		 * @param	array	instance	The current instance of the widget / shortcode parameters
		 * @return	string
		 */
		private function __render_popular_post($p, $instance) {

			// WPML support, based on Serhat Evren's suggestion - see http://wordpress.org/support/topic/wpml-trick#post-5452607
			if ( defined('ICL_LANGUAGE_CODE') && function_exists('icl_object_id') ) {
				$current_id = icl_object_id( $p->id, get_post_type( $p->id ), true, ICL_LANGUAGE_CODE );
				$permalink = get_permalink( $current_id );
			} // Get original permalink
			else {
				$permalink = get_permalink($p->id);
			}

			$title = $this->_get_title($p, $instance);
			$title_sub = $this->_get_title_sub($p, $instance);

			$author = $this->_get_author($p, $instance);
			$post_cat = $this->_get_post_cat($p, $instance);

			$thumb = $this->_get_thumb($p, $instance);
			$excerpt = $this->_get_excerpt($p, $instance);

			$pageviews = $this->_get_pageviews($p, $instance);
			$comments = $this->_get_comments($p, $instance);
			$rating = $this->_get_rating($p, $instance);
			$date = $this->_get_date($p, $instance);

			$_stats = join(' | ', $this->_get_stats($p, $instance));

			// PUTTING IT ALL TOGETHER
			// build custom layout
			if ($instance['markup']['custom_html']) {

				$data = array(
					'title' => '<a href="'.$permalink.'" title="'. esc_attr($title) .'" class="wpp-post-title" target="' . $this->user_settings['tools']['link']['target'] . '">'.$title_sub.'</a>',
					'summary' => $excerpt,
					'stats' => $_stats,
					'img' => ( !empty($thumb) ) ? '<a href="'.$permalink.'" title="'. esc_attr($title) .'" target="' . $this->user_settings['tools']['link']['target'] . '">' . $thumb . '</a>' : '',
					'img_no_link' => $thumb,
					'id' => $p->id,
					'url' => $permalink,
					'text_title' => esc_attr($title),
					'category' => $post_cat,
					'author' => '<a href="' . get_author_posts_url($p->uid) . '">' . $author . '</a>',
					'views' => ($instance['order_by'] == "views" || $instance['order_by'] == "comments") ? number_format_i18n( $pageviews ) : number_format_i18n( $pageviews, 2 ),
					'comments' => number_format_i18n( $comments ),
					'date' => $date
				);

				$content = $this->__format_content( htmlspecialchars_decode($instance['markup']['post-html'], ENT_QUOTES ), $data, $instance['rating'] ). "\n";

			}
			// build regular layout
			else {
				$thumb = ( !empty($thumb) ) 
				  ? '<a ' . ( ( $this->current_post_id == $p->id ) ? '' : 'href="' . $permalink . '"' ) . ' title="' . esc_attr($title) . '" target="' . $this->user_settings['tools']['link']['target'] . '">' . $thumb . '</a> '
				  : '';
				
				$_stats = ( !empty($_stats) ) 
				  ? ' <span class="post-stats">' . $_stats . '</span> '
				  : '';
				
				$content =
					'<li' . ( ( $this->current_post_id == $p->id ) ? ' class="current"' : '' ) . '>'
					. $thumb
					. '<a ' . ( ( $this->current_post_id == $p->id ) ? '' : 'href="' . $permalink . '"' ) . ' title="' . esc_attr($title) . '" class="wpp-post-title" target="' . $this->user_settings['tools']['link']['target'] . '">' . $title_sub . '</a> '
					. $excerpt . $_stats
					. $rating
					. "</li>\n";
			}

			return apply_filters('wpp_post', $content, $p, $instance);

		} // end __render_popular_post

		/**
		 * Cache.
		 *
		 * @since	3.0.0
		 * @param	string $func function name
		 * @param	mixed $default
		 * @return	mixed
		 */
		private function &__cache($func, $default = null) {

			static $cache;

			if ( !isset($cache) ) {
				$cache = array();
			}

			if ( !isset($cache[$func]) ) {
				$cache[$func] = $default;
			}

			return $cache[$func];

		} // end __cache

		/**
		 * Gets post title.
		 *
		 * @since	3.0.0
		 * @param	object	p
		 * @param	array	instance	The current instance of the widget / shortcode parameters
		 * @return	string
		 */
		protected function _get_title($p, $instance) {

			$cache = &$this->__cache(__FUNCTION__, array());

			if ( isset($cache[$p->id]) ) {
				return $cache[$p->id];
			}

			// WPML support, based on Serhat Evren's suggestion - see http://wordpress.org/support/topic/wpml-trick#post-5452607
			if ( defined('ICL_LANGUAGE_CODE') && function_exists('icl_object_id') ) {
				$current_id = icl_object_id( $p->id, get_post_type( $p->id ), true, ICL_LANGUAGE_CODE );
				$title = get_the_title( $current_id );
			} // Check for qTranslate
			else if ( $this->qTrans && function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage') ) {
				$title = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $p->title );
			} // Use ol' plain title
			else {
				$title = $p->title;
			}

			// Strip HTML tags
			$title = strip_tags($title);

			return $cache[$p->id] = apply_filters('the_title', $title, $p->id);

		} // end _get_title

		/**
		 * Gets substring of post title.
		 *
		 * @since	3.0.0
		 * @param	object	p
		 * @param	array	instance	The current instance of the widget / shortcode parameters
		 * @return	string
		 */
		protected function _get_title_sub($p, $instance) {

			$cache = &$this->__cache(__FUNCTION__, array());

			if ( isset($cache[$p->id]) ) {
				return $cache[$p->id];
			}

			// TITLE
			$title_sub = $this->_get_title($p, $instance);

			// truncate title
			if ($instance['shorten_title']['active']) {
				// by words
				if (isset($instance['shorten_title']['words']) && $instance['shorten_title']['words']) {

					$words = explode(" ", $title_sub, $instance['shorten_title']['length'] + 1);
					if (count($words) > $instance['shorten_title']['length']) {
						array_pop($words);
						$title_sub = rtrim( implode(" ", $words), ",." ) . " ...";
					}

				}
				elseif (strlen($title_sub) > $instance['shorten_title']['length']) {
					$title_sub = rtrim( mb_substr($title_sub, 0, $instance['shorten_title']['length'], $this->charset), " ,." ) . "...";
				}
			}

			return $cache[$p->id] = $title_sub;

		} // end _get_title_sub

		/**
		 * Gets post's excerpt.
		 *
		 * @since	3.0.0
		 * @param	object	p
		 * @param	array	instance	The current instance of the widget / shortcode parameters
		 * @return	string
		 */
		protected function _get_excerpt($p, $instance) {

			$excerpt = '';

			// EXCERPT
			if ($instance['post-excerpt']['active']) {

				$excerpt = trim($this->_get_summary($p->id, $instance));

				if (!empty($excerpt) && !$instance['markup']['custom_html']) {
					$excerpt = '<span class="wpp-excerpt">' . $excerpt . '</span>';
				}

			}

			return $excerpt;

		} // end _get_excerpt

		/**
		 * Gets post's thumbnail.
		 *
		 * @since	3.0.0
		 * @param	object	p
		 * @param	array	instance	The current instance of the widget / shortcode parameters
		 * @return	string
		 */
		protected function _get_thumb($p, $instance) {

			if ( !$instance['thumbnail']['active'] || !$this->thumbnailing ) {
				return '';
			}

			$tbWidth = $instance['thumbnail']['width'];
			$tbHeight = $instance['thumbnail']['height'];
			$crop = $instance['thumbnail']['crop'];
			$title = $this->_get_title($p, $instance);

			$thumb = '';

			// get image from custom field
			if ($this->user_settings['tools']['thumbnail']['source'] == "custom_field") {
				$path = get_post_meta($p->id, $this->user_settings['tools']['thumbnail']['field'], true);

				if ($path != '') {
					// user has requested to resize cf image
					if ( $this->user_settings['tools']['thumbnail']['resize'] ) {
						$thumb .= $this->__get_img($p, null, $path, array($tbWidth, $tbHeight), $crop, $this->user_settings['tools']['thumbnail']['source'], $title);
					}
					// use original size
					else {
						$thumb .= $this->_render_image($path, array($tbWidth, $tbHeight), 'wpp-thumbnail wpp_cf', $title);
					}
				}
				else {
					$thumb .= $this->_render_image($this->default_thumbnail, array($tbWidth, $tbHeight), 'wpp-thumbnail wpp_cf_def', $title);
				}
			}
			// get image from post / Featured Image
			else {

				// User wants to use the Featured Image using the 'stock' sizes, get stock thumbnails
				if ( 'predefined' == $instance['thumbnail']['build'] && 'featured' == $this->user_settings['tools']['thumbnail']['source'] ) {

					// The has_post_thumbnail() functions requires theme's 'post-thumbnails' support, otherwise an error will be thrown
					if ( current_theme_supports( 'post-thumbnails' ) ) {

						// Featured image found, retrieve it
						if ( has_post_thumbnail($p->id) ) {
							$size = null;

							foreach ( $this->default_thumbnail_sizes as $name => $attr ) :
								if ( $attr['width'] == $tbWidth && $attr['height'] == $tbHeight && $attr['crop'] == $crop ) {
									$size = $name;
									break;
								}
							endforeach;

							// Couldn't find a matching size (this should like never happen, but...) let's use width & height instead
							if ( null == $size ) {
								$size = array( $tbWidth, $tbHeight );
							}
							
							if ( $this->user_settings['tools']['thumbnail']['responsive'] )
								$thumb .= preg_replace( '/(width|height)=["\']\d*["\']\s?/', "", get_the_post_thumbnail($p->id, $size, array( 'class' => 'wpp-thumbnail wpp_featured_stock' )) );
							else
								$thumb .= get_the_post_thumbnail( $p->id, $size, array( 'class' => 'wpp-thumbnail wpp_featured_stock' ) );
						}
						// No featured image found
						else {
							$thumb .= $this->_render_image($this->default_thumbnail, array($tbWidth, $tbHeight), 'wpp-thumbnail wpp_featured_def', $title);
						}

					} // Current theme does not support 'post-thumbnails' feature
					else {
						$thumb .= $this->_render_image($this->default_thumbnail, array($tbWidth, $tbHeight), 'wpp-thumbnail wpp_featured_def', $title, 'No post-thumbnail support?');
					}

				}
				// Get/generate custom thumbnail
				else {
					$thumb .= $this->__get_img($p, $p->id, null, array($tbWidth, $tbHeight), $crop, $this->user_settings['tools']['thumbnail']['source'], $title);
				}

			}

			return $thumb;

		} // end _get_thumb

		/**
		 * Gets post's views.
		 *
		 * @since	3.0.0
		 * @param	object	p
		 * @param	array	instance	The current instance of the widget / shortcode parameters
		 * @return	int|float
		 */
		protected function _get_pageviews($p, $instance) {

			$pageviews = 0;

			if (
				$instance['order_by'] == "views"
				|| $instance['order_by'] == "avg"
				|| $instance['stats_tag']['views']
			) {
				$pageviews = ($instance['order_by'] == "views" || $instance['order_by'] == "comments")
				? $p->pageviews
				: $p->avg_views;
			}

			return $pageviews;

		} // end _get_pageviews

		/**
		 * Gets post's comment count.
		 *
		 * @since	3.0.0
		 * @param	object	p
		 * @param	array	instance	The current instance of the widget / shortcode parameters
		 * @return	int
		 */
		protected function _get_comments($p, $instance) {

			$comments = ($instance['order_by'] == "comments" || $instance['stats_tag']['comment_count'])
			  ? $p->comment_count 
			  : 0;

			return $comments;

		} // end _get_comments

		/**
		 * Gets post's rating.
		 *
		 * @since	3.0.0
		 * @param	object	p
		 * @param	array	instance	The current instance of the widget / shortcode parameters
		 * @return	string
		 */
		protected function _get_rating($p, $instance) {

			$cache = &$this->__cache(__FUNCTION__, array());

			if ( isset($cache[$p->id]) ) {
				return $cache[$p->id];
			}

			$rating = '';

			// RATING
			if (function_exists('the_ratings') && $instance['rating']) {
				$rating = '<span class="wpp-rating">' . the_ratings('span', $p->id, false) . '</span>';
			}

			return $cache[$p->id] = $rating;
		} // end _get_rating

		/**
		 * Gets post's author.
		 *
		 * @since	3.0.0
		 * @param	object	p
		 * @param	array	instance	The current instance of the widget / shortcode parameters
		 * @return	string
		 */
		protected function _get_author($p, $instance) {

			$cache = &$this->__cache(__FUNCTION__, array());

			if ( isset($cache[$p->id]) ) {
				return $cache[$p->id];
			}

			$author = ($instance['stats_tag']['author'])
			  ? get_the_author_meta('display_name', $p->uid)
			  : "";

			return $cache[$p->id] = $author;

		} // end _get_author

		/**
		 * Gets post's date.
		 *
		 * @since	3.0.0
		 * @param	object	p
		 * @param	array	instance	The current instance of the widget / shortcode parameters
		 * @return	string
		 */
		protected function _get_date($p, $instance) {

			$cache = &$this->__cache(__FUNCTION__, array());

			if ( isset($cache[$p->id]) ) {
				return $cache[$p->id];
			}

			$date = date_i18n($instance['stats_tag']['date']['format'], strtotime($p->date));
			return $cache[$p->id] = $date;

		} // end _get_date

		/**
		 * Gets post's category.
		 *
		 * @since	3.0.0
		 * @param	object	p
		 * @param	array	instance	The current instance of the widget / shortcode parameters
		 * @return	string
		 */
		protected function _get_post_cat($p, $instance) {

			$post_cat = '';

            if ($instance['stats_tag']['category']) {
				
				$cache = &$this->__cache(__FUNCTION__, array());

				if ( isset($cache[$p->id]) ) {
					return $cache[$p->id];
				}

                // Try and get parent category
                $cats = get_the_category($p->id);

                foreach( $cats as $cat ) {
                    if( $cat->category_parent == 0) {
                        $post_cat = $cat;
                    }
                }

                // Default to first category avaliable
                if ( $post_cat == "" && isset($cats[0]) && isset($cats[0]->slug) ) {
                    $post_cat = $cats[0];
                }
				
				// Build category tag
				if ( "" != $post_cat ) {
					
					$category_id = $post_cat->term_id;
					$category_name = $post_cat->cat_name;
					
					// WPML support, based on Serhat Evren's suggestion - see http://wordpress.org/support/topic/wpml-trick#post-5452607
					if ( defined('ICL_LANGUAGE_CODE') && function_exists('icl_object_id') ) {
						$category_id = icl_object_id( $category_id, 'category', true, ICL_LANGUAGE_CODE );
						$category_name = get_the_category_by_ID( $category_id );
					}
					
					$post_cat = '<a href="' . get_category_link( $category_id ) . '" class="cat-id-' . $category_id . '">' . $category_name . '</a>';
					
				}
				
				return $cache[$p->id] = $post_cat;

			}

			return $post_cat;

		} // end _get_post_cat

		/**
		 * Gets statistics data.
		 *
		 * @since	3.0.0
		 * @param	object	p
		 * @param	array	instance	The current instance of the widget / shortcode parameters
		 * @return	array
		 */
		protected function _get_stats($p, $instance) {

			$cache = &$this->__cache(__FUNCTION__ . md5(json_encode($instance)), array());

			if ( isset($cache[$p->id]) ) {
				return $cache[$p->id];
			}

			$stats = array();

			// STATS
			// comments
			if ($instance['stats_tag']['comment_count']) {
				$comments = $this->_get_comments($p, $instance);

				$comments_text = sprintf(
				_n('1 comment', '%s comments', $comments, $this->plugin_slug),
				number_format_i18n( $comments )
				);
				
			}

			// views
			if ($instance['stats_tag']['views']) {
				$pageviews = $this->_get_pageviews($p, $instance);

				if ($instance['order_by'] == 'avg') {
					$views_text = sprintf(
					_n('1 view per day', '%s views per day', $pageviews, $this->plugin_slug),
					number_format_i18n( $pageviews, 2 )
					);
				}
				else {
					$views_text = sprintf(
					_n('1 view', '%s views', $pageviews, $this->plugin_slug),
					number_format_i18n( $pageviews )
					);
				}
				
			}
			
			if ( "comments" == $instance['order_by'] ) {
				if ($instance['stats_tag']['comment_count'])
					$stats[] = '<span class="wpp-comments">' . $comments_text . '</span>'; // First comments count
				if ($instance['stats_tag']['views'])
					$stats[] = '<span class="wpp-views">' . $views_text . "</span>"; // ... then views
			} else {
				if ($instance['stats_tag']['views'])
					$stats[] = '<span class="wpp-views">' . $views_text . "</span>"; // First views count
				if ($instance['stats_tag']['comment_count'])
					$stats[] = '<span class="wpp-comments">' . $comments_text . '</span>'; // ... then comments
			}

			// author
			if ($instance['stats_tag']['author']) {
				$author = $this->_get_author($p, $instance);
				$display_name = '<a href="' . get_author_posts_url($p->uid) . '">' . $author . '</a>';
				$stats[] = '<span class="wpp-author">' . sprintf(__('by %s', $this->plugin_slug), $display_name).'</span>';
			}

			// date
			if ($instance['stats_tag']['date']['active']) {
				$date = $this->_get_date($p, $instance);
				$stats[] = '<span class="wpp-date">' . sprintf(__('posted on %s', $this->plugin_slug), $date) . '</span>';
			}

			// category
			if ($instance['stats_tag']['category']) {
				$post_cat = $this->_get_post_cat($p, $instance);

				if ($post_cat != '') {
					$stats[] = '<span class="wpp-category">' . sprintf(__('under %s', $this->plugin_slug), $post_cat) . '</span>';
				}
			}

			return $cache[$p->id] = $stats;

		} // end _get_stats

		/**
		 * Retrieves / creates the post thumbnail.
		 *
		 * @since	2.3.3
		 * @param	int	id			Post ID
		 * @param	string	url		Image URL
		 * @param	array	dim		Thumbnail width & height
		 * @param	string	source	Image source
		 * @return	string
		 */
		private function __get_img($p, $id = null, $url = null, $dim = array(80, 80), $crop = true, $source = "featured", $title) {

			if ( (!$id || empty($id) || !$this->__is_numeric($id)) && (!$url || empty($url)) ) {
				return $this->_render_image($this->default_thumbnail, $dim, 'wpp-thumbnail wpp_def_noID', $title);
			}

			// Get image by post ID (parent)
			if ( $id ) {
				$file_path = $this->__get_image_file_paths($id, $source);

				// No images found, return default thumbnail
				if ( !$file_path ) {
					return $this->_render_image($this->default_thumbnail, $dim, 'wpp-thumbnail wpp_def_noPath wpp_' . $source, $title);
				}
			}
			// Get image from URL
			else {
				// sanitize URL, just in case
				$image_url = esc_url( $url );
				// remove querystring
				preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $image_url, $matches);
				$image_url = $matches[0];

				$attachment_id = $this->__get_attachment_id($image_url);

				// Image is hosted locally
				if ( $attachment_id ) {
					$file_path = get_attached_file($attachment_id);
				}
				// Image is hosted outside WordPress
				else {
					$external_image = $this->__fetch_external_image($p->id, $image_url);

					if ( !$external_image ) {
						return $this->_render_image($this->default_thumbnail, $dim, 'wpp-thumbnail wpp_def_noPath wpp_no_external', $title);
					}

					$file_path = $external_image;
				}
			}

			$file_info = pathinfo($file_path);

			// there is a thumbnail already
			if ( file_exists(trailingslashit($this->uploads_dir['basedir']) . $p->id . '-' . $source . '-' . $dim[0] . 'x' . $dim[1] . '.' . $file_info['extension']) ) {
				return $this->_render_image( trailingslashit($this->uploads_dir['baseurl']) . $p->id . '-' . $source . '-' . $dim[0] . 'x' . $dim[1] . '.' . $file_info['extension'], $dim, 'wpp-thumbnail wpp_cached_thumb wpp_' . $source, $title );
			}

			return $this->__image_resize($p, $file_path, $dim, $crop, $source);

		} // end __get_img

		/**
		 * Resizes image.
		 *
		 * @since	3.0.0
		 * @param	object	p			Post object
		 * @param	string	path		Image path		 
		 * @param	array	dimension	Image's width and height
		 * @param	string	source		Image source
		 * @return	string
		 */
		private function __image_resize($p, $path, $dimension, $crop, $source) {

			$image = wp_get_image_editor($path);

			// valid image, create thumbnail
			if ( !is_wp_error($image) ) {
				$file_info = pathinfo($path);

				$image->resize($dimension[0], $dimension[1], $crop);
				$new_img = $image->save( trailingslashit($this->uploads_dir['basedir']) . $p->id . '-' . $source . '-' . $dimension[0] . 'x' . $dimension[1] . '.' . $file_info['extension'] );

				if ( is_wp_error($new_img) ) {
					return $this->_render_image($this->default_thumbnail, $dimension, 'wpp-thumbnail wpp_imgeditor_error wpp_' . $source, '', $new_img->get_error_message());
				}

				return $this->_render_image( trailingslashit($this->uploads_dir['baseurl']) . $new_img['file'], $dimension, 'wpp-thumbnail wpp_imgeditor_thumb wpp_' . $source, '');
			}

			// ELSE
			// image file path is invalid
			return $this->_render_image($this->default_thumbnail, $dimension, 'wpp-thumbnail wpp_imgeditor_error wpp_' . $source, '', $image->get_error_message());

		} // end __image_resize

		/**
		 * Get image absolute path / URL.
		 *
		 * @since	3.0.0
		 * @param	int	id			Post ID
		 * @param	string	source	Image source
		 * @return	array
		 */
		private function __get_image_file_paths($id, $source) {

			$file_path = '';

			// get thumbnail path from the Featured Image
			if ($source == "featured") {

				// thumb attachment ID
				$thumbnail_id = get_post_thumbnail_id($id);

				if ($thumbnail_id) {
					// image path
					return get_attached_file($thumbnail_id);
				}

			}
			// get thumbnail path from first image attachment
			elseif ($source == "first_attachment") {

				$post_attachments = get_children(
							array( 'numberposts' => 1,
								'order' => 'ASC',
								'post_parent' => $id,
								'post_type' => 'attachment',
								'post_mime_type' => 'image'
								));
				if ( !empty($post_attachments) ) {
					$first_img = array_shift( $post_attachments );
					return get_attached_file($first_img->ID);
				}
			}
			// get thumbnail path from post content
			elseif ($source == "first_image") {

				/** @var wpdb $wpdb */
				global $wpdb;
				
				if ( $content = $wpdb->get_var( "SELECT post_content FROM {$wpdb->posts} WHERE ID = {$id};" ) ) {

					// at least one image has been found
					if ( preg_match( '/<img[^>]+>/i', $content, $img ) ) {
						
						// get img src attribute from the first image found
						preg_match( '/(src)="([^"]*)"/i', $img[0], $src_attr );
						
						if ( isset($src_attr[2]) && !empty($src_attr[2]) ) {
						
							// image from Media Library
							if ( $attachment_id = $this->__get_attachment_id( $src_attr[2] ) ) {
								
								$file_path = get_attached_file($attachment_id);
								
								// There's a file path, so return it
								if ( !empty($file_path) ) {
									return $file_path;
								}
								
							} // external image?
							else {
								return $this->__fetch_external_image($id, $src_attr[2]);
							}
						
						}
						
					}
					
				}

			}

			return false;

		} // end __get_image_file_paths

		/**
		 * Render image tag.
		 *
		 * @since	3.0.0
		 * @param	string	src			Image URL
		 * @param	array	dimension	Image's width and height
		 * @param	string	class		CSS class
		 * @param	string	title		Image's title/alt attribute
		 * @param	string	error		Error, if the image could not be created
		 * @return	string
		 */
		protected function _render_image($src, $dimension, $class, $title = "", $error = null) {

			$msg = '';

			if ($error) {
				$msg = '<!-- ' . $error . ' --> ';
			}
			
			if ( is_ssl() ) {
				$src = str_ireplace( "http://", "https://", $src );
			}

			return apply_filters( 'wpp_render_image', $msg .
			'<img src="' . $src . '" ' . ( false == $this->user_settings['tools']['thumbnail']['responsive'] ? 'width="' . $dimension[0] . '" height="' . $dimension[1] . '"' : '' ) . ' title="' . esc_attr($title) . '" alt="' . esc_attr($title) . '" class="' . $class . '" />' );

		} // _render_image

		/**
		* Get the Attachment ID for a given image URL.
		*
		* @since	3.0.0
		* @author	Frankie Jarrett
		* @link		http://frankiejarrett.com/get-an-attachment-id-by-url-in-wordpress/
		* @param	string	url
		* @return	bool|int
		*/
		private function __get_attachment_id($url) {

			// Split the $url into two parts with the wp-content directory as the separator.
			$parse_url  = explode( parse_url( WP_CONTENT_URL, PHP_URL_PATH ), $url );

			// Get the host of the current site and the host of the $url, ignoring www.
			$this_host = str_ireplace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );
			$file_host = str_ireplace( 'www.', '', parse_url( $url, PHP_URL_HOST ) );

			// Return nothing if there aren't any $url parts or if the current host and $url host do not match.
			if ( ! isset( $parse_url[1] ) || empty( $parse_url[1] ) || ( $this_host != $file_host ) ) {
				return false;
			}

			// Now we're going to quickly search the DB for any attachment GUID with a partial path match.
			// Example: /uploads/2013/05/test-image.jpg
			global $wpdb;

			if ( !$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid RLIKE %s;", $parse_url[1] ) ) ) {
				// Maybe it's a resized image, so try to get the full one
				$parse_url[1] = preg_replace( '/-[0-9]{1,4}x[0-9]{1,4}\.(jpg|jpeg|png|gif|bmp)$/i', '.$1', $parse_url[1] );
				$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid RLIKE %s;", $parse_url[1] ) );
			}

			// Returns null if no attachment is found.
			return isset($attachment[0]) ? $attachment[0] : NULL;

		} // __get_attachment_id

		/**
		* Fetchs external images.
		*
		* @since 2.3.3
		* @param	string	url
		* @return	bool|int
		*/
		private function __fetch_external_image($id, $url){

			$full_image_path = trailingslashit( $this->uploads_dir['basedir'] ) . "{$id}_". sanitize_file_name( rawurldecode(wp_basename( $url )) );

			// if the file exists already, return URL and path
			if ( file_exists($full_image_path) )
				return $full_image_path;

			$accepted_status_codes = array( 200, 301, 302 );
			$response = wp_remote_head( $url, array( 'timeout' => 5, 'sslverify' => false ) );

			if ( !is_wp_error($response) && in_array(wp_remote_retrieve_response_code($response), $accepted_status_codes) ) {
				
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				$url = str_replace( 'https://', 'http://', $url );
				$tmp = download_url( $url );
				
				if ( !is_wp_error( $tmp ) ) {
					
					if ( function_exists('exif_imagetype') ) {
						$image_type = exif_imagetype( $tmp );
					} else {
						$image_type = getimagesize( $tmp );
						$image_type = ( isset($image_type[2]) ) ? $image_type[2] : NULL;
					}
	
					if ( in_array($image_type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)) ) {
	
						// move file to Uploads
						if ( @rename($tmp, $full_image_path) ) {
							// borrowed from WP - set correct file permissions
							$stat = stat( dirname( $full_image_path ));
							$perms = $stat['mode'] & 0000644;
							@chmod( $full_image_path, $perms );
	
							return $full_image_path;
						}
						
					}
					
					// remove temp file
					@unlink( $tmp );
					
				}
				
			}

			return false;

		} // end __fetch_external_image

		/**
		 * Builds post's excerpt
		 *
		 * @since	1.4.6
		 * @global	object	wpdb
		 * @param	int	post ID
		 * @param	array	widget instance
		 * @return	string
		 */
		protected function _get_summary($id, $instance){

			if ( !$this->__is_numeric($id) )
				return false;

			global $wpdb;

			$excerpt = "";

			// WPML support, get excerpt for current language
			if ( defined('ICL_LANGUAGE_CODE') && function_exists('icl_object_id') ) {
				$current_id = icl_object_id( $id, get_post_type( $id ), true, ICL_LANGUAGE_CODE );

				$the_post = get_post( $current_id );
				$excerpt = ( empty($the_post->post_excerpt) )
				  ? $the_post->post_content
				  : $the_post->post_excerpt;
			} // Use ol' plain excerpt
			else {
				$the_post = get_post( $id );
				$excerpt = ( empty($the_post->post_excerpt) )
				  ? $the_post->post_content
				  : $the_post->post_excerpt;

				// RRR added call to the_content filters, allows qTranslate to hook in.
				if ( $this->qTrans )
					$excerpt = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $excerpt );
			}

			// remove caption tags
			$excerpt = preg_replace( "/\[caption.*\[\/caption\]/", "", $excerpt );

			// remove Flash objects
			$excerpt = preg_replace( "/<object[0-9 a-z_?*=\":\-\/\.#\,\\n\\r\\t]+/smi", "", $excerpt );

			// remove Iframes
			$excerpt = preg_replace( "/<iframe.*?\/iframe>/i", "", $excerpt);

			// remove WP shortcodes
			$excerpt = strip_shortcodes( $excerpt );
			
			// remove style/script tags
			$excerpt = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $excerpt );

			// remove HTML tags if requested
			if ( $instance['post-excerpt']['keep_format'] ) {
				$excerpt = strip_tags($excerpt, '<a><b><i><em><strong>');
			} else {
				$excerpt = strip_tags($excerpt);
				// remove URLs, too
				$excerpt = preg_replace( '_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iuS', '', $excerpt );
			}

			// Fix RSS CDATA tags
			$excerpt = str_replace( ']]>', ']]&gt;', $excerpt );

			// do we still have something to display?
			if ( !empty($excerpt) ) {

				// truncate excerpt
				if ( isset($instance['post-excerpt']['words']) && $instance['post-excerpt']['words'] ) { // by words

					$words = explode(" ", $excerpt, $instance['post-excerpt']['length'] + 1);

					if ( count($words) > $instance['post-excerpt']['length'] ) {
						array_pop($words);
						$excerpt = rtrim( implode(" ", $words), ".," ) . " ...";
					}

				} else { // by characters

					if ( strlen($excerpt) > $instance['post-excerpt']['length'] ) {
						$excerpt = rtrim( mb_substr( $excerpt, 0, $instance['post-excerpt']['length'], $this->charset ), ".," ) . "...";
					}

				}

			}

			// Balance tags, if needed
			if ( $instance['post-excerpt']['keep_format'] ) {
				$excerpt = force_balance_tags($excerpt);
			}

			return $excerpt;

		} // _get_summary

		/**
		 * WPP shortcode handler
		 * Since 2.0.0
		 */
		public function shortcode($atts = null, $content = null) {
			/**
			* @var String $header
			* @var Int $limit
			* @var String $range
			* @var Bool $freshness
			* @var String $order_by
			* @var String $post_type
			* @var String $pid
			* @var String $cat
			* @var String $author
			* @var Int $title_length
			* @var Int $title_by_words
			* @var Int $excerpt_length
			* @var Int $excerpt_format
			* @var Int $excerpt_by_words
			* @var Int $thumbnail_width
			* @var Int $thumbnail_height
			* @var Bool $rating
			* @var Bool $stats_comments
			* @var Bool $stats_views
			* @var Bool $stats_author
			* @var Bool $stats_date
			* @var String $stats_date_format
			* @var Bool $stats_category
			* @var String $wpp_start
			* @var String $wpp_end
			* @var String $header_start
			* @var String $header_end
			* @var String $post_html
			* @var Bool $php
			*/
			extract( shortcode_atts( array(
				'header' => '',
				'limit' => 10,
				'range' => 'daily',
				'freshness' => false,
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
				'rating' => false,
				'stats_comments' => false,
				'stats_views' => true,
				'stats_author' => false,
				'stats_date' => false,
				'stats_date_format' => 'F j, Y',
				'stats_category' => false,
				'wpp_start' => '<ul class="wpp-list">',
				'wpp_end' => '</ul>',
				'header_start' => '<h2>',
				'header_end' => '</h2>',
				'post_html' => '',
				'php' => false
			), $atts, 'wpp'));

			// possible values for "Time Range" and "Order by"
			$range_values = array("yesterday", "daily", "weekly", "monthly", "all");
			$order_by_values = array("comments", "views", "avg");

			$shortcode_ops = array(
				'title' => strip_tags($header),
				'limit' => (!empty($limit) && $this->__is_numeric($limit) && $limit > 0) ? $limit : 10,
				'range' => (in_array($range, $range_values)) ? $range : 'daily',
				'freshness' => empty($freshness) ? false : $freshness,
				'order_by' => (in_array($order_by, $order_by_values)) ? $order_by : 'views',
				'post_type' => empty($post_type) ? 'post,page' : $post_type,
				'pid' => preg_replace('|[^0-9,]|', '', $pid),
				'cat' => preg_replace('|[^0-9,-]|', '', $cat),
				'author' => preg_replace('|[^0-9,]|', '', $author),
				'shorten_title' => array(
					'active' => (!empty($title_length) && $this->__is_numeric($title_length) && $title_length > 0),
					'length' => (!empty($title_length) && $this->__is_numeric($title_length)) ? $title_length : 0,
					'words' => (!empty($title_by_words) && $this->__is_numeric($title_by_words) && $title_by_words > 0),
				),
				'post-excerpt' => array(
					'active' => (!empty($excerpt_length) && $this->__is_numeric($excerpt_length) && ($excerpt_length > 0)),
					'length' => (!empty($excerpt_length) && $this->__is_numeric($excerpt_length)) ? $excerpt_length : 0,
					'keep_format' => (!empty($excerpt_format) && $this->__is_numeric($excerpt_format) && ($excerpt_format > 0)),
					'words' => (!empty($excerpt_by_words) && $this->__is_numeric($excerpt_by_words) && $excerpt_by_words > 0),
				),
				'thumbnail' => array(
					'active' => (!empty($thumbnail_width) && $this->__is_numeric($thumbnail_width) && $thumbnail_width > 0),
					'width' => (!empty($thumbnail_width) && $this->__is_numeric($thumbnail_width) && $thumbnail_width > 0) ? $thumbnail_width : 0,
					'height' => (!empty($thumbnail_height) && $this->__is_numeric($thumbnail_height) && $thumbnail_height > 0) ? $thumbnail_height : 0,
				),
				'rating' => empty($rating) ? false : $rating,
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
					'wpp-start' => empty($wpp_start) ? '<ul class="wpp-list">' : $wpp_start,
					'wpp-end' => empty($wpp_end) ? '</ul>' : $wpp_end,
					'title-start' => empty($header_start) ? '' : $header_start,
					'title-end' => empty($header_end) ? '' : $header_end,
					'post-html' => empty($post_html) ? '<li>{thumb} {title} {stats}</li>' : $post_html
				)
			);

			$shortcode_content = "\n". "<!-- WordPress Popular Posts Plugin v". $this->version ." [" . ( $php ? "PHP" : "SC" ) . "] [".$shortcode_ops['range']."] [".$shortcode_ops['order_by']."] [custom]" . ( !empty($shortcode_ops['pid']) ? " [PID]" : "" ) . ( !empty($shortcode_ops['cat']) ? " [CAT]" : "" ) . ( !empty($shortcode_ops['author']) ? " [UID]" : "" ) . " -->"."\n";

			// is there a title defined by user?
			if (!empty($header) && !empty($header_start) && !empty($header_end)) {
				$shortcode_content .= htmlspecialchars_decode($header_start, ENT_QUOTES) . apply_filters('widget_title', $header) . htmlspecialchars_decode($header_end, ENT_QUOTES);
			}

			// print popular posts list
			$shortcode_content .= $this->__get_popular_posts($shortcode_ops);
			$shortcode_content .= "\n". "<!-- End WordPress Popular Posts Plugin v". $this->version ." -->"."\n";

			return $shortcode_content;

		} // end shortcode

		/**
		 * Parses content tags
		 *
		 * @since	1.4.6
		 * @param	string	HTML string with content tags
		 * @param	array	Post data
		 * @param	bool	Used to display post rating (if functionality is available)
		 * @return	string
		 */
		private function __format_content($string, $data = array(), $rating) {

			if (empty($string) || (empty($data) || !is_array($data)))
				return false;

			$params = array();
			$pattern = '/\{(pid|excerpt|summary|stats|title|image|thumb|thumb_img|rating|score|url|text_title|author|category|views|comments|date)\}/i';
			preg_match_all($pattern, $string, $matches);

			array_map('strtolower', $matches[0]);

			if ( in_array("{pid}", $matches[0]) ) {
				$string = str_replace( "{pid}", $data['id'], $string );
			}

			if ( in_array("{title}", $matches[0]) ) {
				$string = str_replace( "{title}", $data['title'], $string );
			}

			if ( in_array("{stats}", $matches[0]) ) {
				$string = str_replace( "{stats}", $data['stats'], $string );
			}

			if ( in_array("{excerpt}", $matches[0]) || in_array("{summary}", $matches[0]) ) {
				$string = str_replace( array("{excerpt}", "{summary}"), $data['summary'], $string );
			}

			if ( in_array("{image}", $matches[0]) || in_array("{thumb}", $matches[0]) ) {
				$string = str_replace( array("{image}", "{thumb}"), $data['img'], $string );
			}
			
			if ( in_array("{thumb_img}", $matches[0]) ) {
				$string = str_replace( "{thumb_img}", $data['img_no_link'], $string );
			}

			// WP-PostRatings check
			if ( $rating ) {
				if ( function_exists('the_ratings_results') && in_array("{rating}", $matches[0]) ) {
					$string = str_replace( "{rating}", the_ratings_results($data['id']), $string );
				}

				if ( function_exists('expand_ratings_template') && in_array("{score}", $matches[0]) ) {
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

			if ( in_array("{date}", $matches[0]) ) {
				$string = str_replace( "{date}", $data['date'], $string );
			}

			return $string;

		} // end __format_content

		/**
		 * Returns HTML list via AJAX
		 *
		 * @since	2.3.3
		 * @return	string
		 */
		public function get_popular( ) {

			if ( $this->__is_numeric($_GET['id']) && ($_GET['id'] != '') ) {
				$id = $_GET['id'];
			} else {
				die("Invalid ID");
			}

			$widget_instances = $this->get_settings();

			if ( isset($widget_instances[$id]) ) {

				echo $this->__get_popular_posts( $widget_instances[$id] );

			} else {

				echo "Invalid Widget ID";
			}

			exit();

		} // end get_popular

		/*--------------------------------------------------*/
		/* Helper functions
		/*--------------------------------------------------*/

		/**
		 * Gets list of available thumbnails sizes
		 *
		 * @since	3.2.0
		 * @link	http://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
		 * @param	string	$size
		 * @return	array|bool
		 */
		private function __get_image_sizes( $size = '' ) {

			global $_wp_additional_image_sizes;
	
			$sizes = array();
			$get_intermediate_image_sizes = get_intermediate_image_sizes();
	
			// Create the full array with sizes and crop info
			foreach( $get_intermediate_image_sizes as $_size ) {
	
				if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {

					$sizes[ $_size ]['width'] = get_option( $_size . '_size_w' );
					$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
					$sizes[ $_size ]['crop'] = (bool) get_option( $_size . '_crop' );

				} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

					$sizes[ $_size ] = array( 
						'width' => $_wp_additional_image_sizes[ $_size ]['width'],
						'height' => $_wp_additional_image_sizes[ $_size ]['height'],
						'crop' =>  $_wp_additional_image_sizes[ $_size ]['crop']
					);

				}
	
			}
	
			// Get only 1 size if found
			if ( $size ) {
	
				if( isset( $sizes[ $size ] ) ) {
					return $sizes[ $size ];
				} else {
					return false;
				}
	
			}
	
			return $sizes;
		}
		
		/**
		 * Gets post/page ID if current page is singular
		 *
		 * @since	3.1.2
		 */
		public function is_single() {
			$trackable = array();
			$registered_post_types = get_post_types( array('public' => true), 'names' );
			
			foreach ( $registered_post_types as $post_type ) {
				$trackable[] = $post_type;
			}
			
			$trackable = apply_filters( 'wpp_trackable_post_types', $trackable );
			
			if ( is_singular($trackable) && !is_front_page() && !is_preview() && !is_trackback() && !is_feed() && !is_robots() ) {
				global $post;				
				$this->current_post_id = ( is_object($post) ) ? $post->ID : 0;
			} else {
				$this->current_post_id = 0;
			}
		} // end is_single

		/**
		 * Checks for valid number
		 *
		 * @since	2.1.6
		 * @param	int	number
		 * @return	bool
		 */
		private function __is_numeric($number){
			return !empty($number) && is_numeric($number) && (intval($number) == floatval($number));
		}

		/**
		 * Returns server datetime
		 *
		 * @since	2.1.6
		 * @return	string
		 */
		private function __curdate() {
			return gmdate( 'Y-m-d', ( time() + ( get_site_option( 'gmt_offset' ) * 3600 ) ));
		} // end __curdate

		/**
		 * Returns mysql datetime
		 *
		 * @since	2.1.6
		 * @return	string
		 */
		private function __now() {
			return current_time('mysql');
		} // end __now

		/**
		 * Returns time
		 *
		 * @since	2.3.0
		 * @return	string
		 */
		private function __microtime_float() {

			list( $msec, $sec ) = explode( ' ', microtime() );

			$microtime = (float) $msec + (float) $sec;
			return $microtime;

		} // end __microtime_float

		/**
		 * Merges two associative arrays recursively
		 *
		 * @since	2.3.4
		 * @link	http://www.php.net/manual/en/function.array-merge-recursive.php#92195
		 * @param	array	array1
		 * @param	array	array2
		 * @return	array
		 */
		private function __merge_array_r( array &$array1, array &$array2 ) {

			$merged = $array1;

			foreach ( $array2 as $key => &$value ) {

				if ( is_array( $value ) && isset ( $merged[$key] ) && is_array( $merged[$key] ) ) {
					$merged[$key] = $this->__merge_array_r( $merged[$key], $value );
				} else {
					$merged[$key] = $value;
				}
			}

			return $merged;

		} // end __merge_array_r

		/**
		 * Checks if visitor is human or bot.
		 *
		 * @since	3.0.0
		 * @return	bool	FALSE if human, TRUE if bot
		 */
		private function __is_bot() {

			if ( !isset($_SERVER['HTTP_USER_AGENT']) || empty($_SERVER['HTTP_USER_AGENT']) )
				return true; // No UA? Bot (probably)

			$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);

			foreach ( $this->botlist as $bot ) {
				if ( false !== strpos($user_agent, $bot) ) {
					return true; // Bot
				}
			}

			return false; // Human, I guess...

		} // end __is_bot

		/**
		 * Debug function.
		 *
		 * @since	3.0.0
		 * @param	mixed $v variable to display with var_dump()
		 * @param	mixed $v,... unlimited optional number of variables to display with var_dump()
		 */
		private function __debug($v) {

			if ( !defined('WPP_DEBUG') || !WPP_DEBUG )
				return;

			foreach (func_get_args() as $arg) {

				print "<pre>";
				var_dump($arg);
				print "</pre>";

			}

		} // end __debug

	} // end class

}

/**
 * WordPress Popular Posts template tags for use in themes.
 */

/**
 * Template tag - gets views count.
 *
 * @since	2.0.3
 * @global	object	wpdb
 * @param	int		id
 * @param	string	range
 * @param	bool	number_format
 * @return	string
 */
function wpp_get_views($id = NULL, $range = NULL, $number_format = true) {

	// have we got an id?
	if ( empty($id) || is_null($id) || !is_numeric($id) ) {
		return "-1";
	} else {
		global $wpdb;

		$table_name = $wpdb->prefix . "popularposts";

		if ( !$range || 'all' == $range ) {
			$query = "SELECT pageviews FROM {$table_name}data WHERE postid = '{$id}'";
		} else {
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

			$query = "SELECT SUM(pageviews) FROM {$table_name}summary WHERE postid = '{$id}' AND last_viewed > DATE_SUB('{$now}', INTERVAL {$interval}) LIMIT 1;";
		}

		$result = $wpdb->get_var($query);

		if ( !$result ) {
			return "0";
		}

		return ($number_format) ? number_format_i18n( intval($result) ) : $result;
	}

}

/**
 * Template tag - gets popular posts.
 *
 * @since	2.0.3
 * @param	mixed	args
 */
function wpp_get_mostpopular($args = NULL) {

	$shortcode = '[wpp';

	if ( is_null( $args ) ) {
		$shortcode .= ']';
	} else {
		if( is_array( $args ) ){
			$atts = '';
			foreach( $args as $key => $arg ){
				$atts .= ' ' . $key . '="' . htmlspecialchars($arg, ENT_QUOTES, $encoding = ini_get("default_charset"), false) . '"';
			}
		} else {
			$atts = trim( str_replace( "&", " ", $args  ) );
		}

		$shortcode .= ' ' . $atts . ' php=true]';
	}

	echo do_shortcode( $shortcode );

}

/**
 * Template tag - gets popular posts. Deprecated in 2.0.3, use wpp_get_mostpopular instead.
 *
 * @since	1.0
 * @param	mixed	args
 */
function get_mostpopular($args = NULL) {
	trigger_error( 'The get_mostpopular() template tag has been deprecated since 2.0.3. Please use wpp_get_mostpopular() instead.', E_USER_WARNING );
}
