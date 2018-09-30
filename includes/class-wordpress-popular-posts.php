<?php

class WordPressPopularPosts {

    /**
     * The unique identifier of this plugin.
     *
     * @since    4.0.0
     * @access   protected
     * @var      string     $plugin_name
     */
    protected $plugin_name;

    /**
     * The current version of this plugin.
     *
     * @since    4.0.0
     * @access   protected
     * @var      string     $version
     */
    protected $version;

    /**
     * Constructor.
     *
     * @since    4.0.0
     */
    public function __construct(){

        $this->plugin_name = 'wordpress-popular-posts';
        $this->version = WPP_VER;

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();

    }

    /**
     * Loads the required dependencies for this plugin.
     *
     * @since    4.0.0
     * @access   private
     */
    private function load_dependencies(){

        /**
         * Caching class.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordpress-popular-posts-cache.php';

        /**
         * The class responsible for defining internationalization functionality of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordpress-popular-posts-i18n.php';

        /**
         * The class responsible for translating objects.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordpress-popular-posts-translate.php';

        /**
         * Settings class.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordpress-popular-posts-settings.php';

        /**
         * Helper class.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordpress-popular-posts-helper.php';

        /**
         * Template functions.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordpress-popular-posts-template.php';

        /**
         * The class responsible for handling the actions and filters of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordpress-popular-posts-loader.php';

        /**
         * The class responsible for querying the database.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordpress-popular-posts-query.php';

        /**
         * The class responsible for creating images.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordpress-popular-posts-image.php';

        /**
         * The class responsible for building the HTML output.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordpress-popular-posts-output.php';

        /**
         * The widget class.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordpress-popular-posts-widget.php';

        /**
         * The class responsible for defining all actions that occur on the admin-facing side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wordpress-popular-posts-admin.php';

        /**
         * The class responsible for defining all actions that occur on the public-facing side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wordpress-popular-posts-public.php';

        /**
         * The REST API controller class for the popular posts endpoing.
         */
        if ( class_exists('WP_REST_Controller', false) ) {
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordpress-popular-posts-rest-controller.php';
        }

        /**
         * Get loader.
         */
        $this->loader = new WPP_Loader();

    }

    /**
     * Register all of the hooks related to the admin area functionality of the plugin.
     *
     * @since    4.0.0
     * @access   private
     */
    public function define_admin_hooks() {

        $plugin_admin = new WPP_Admin( $this->get_plugin_name(), $this->get_version() );

        // Upgrade check
        $this->loader->add_action( 'init', $plugin_admin, 'upgrade_check' );
        // Hook fired when a new blog is activated on WP Multisite
        $this->loader->add_action( 'wpmu_new_blog', $plugin_admin, 'activate_new_site' );
        // Hook fired when a blog is deleted on WP Multisite
        $this->loader->add_filter( 'wpmu_drop_tables', $plugin_admin, 'delete_site_data', 10, 2 );
        // At-A-Glance
        $this->loader->add_filter( 'dashboard_glance_items', $plugin_admin, 'at_a_glance_stats' );
        $this->loader->add_action( 'admin_head', $plugin_admin, 'at_a_glance_stats_css' );
        // Load WPP's admin styles and scripts
        $this->loader->add_action( 'admin_print_styles', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        // Add admin screen
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
        // Contextual help
        $this->loader->add_action( 'admin_head', $plugin_admin, 'add_contextual_help' );
        // Add plugin settings link
        $this->loader->add_filter( 'plugin_action_links', $plugin_admin, 'add_plugin_settings_link', 10, 2 );
        // Update chart
        $this->loader->add_action( 'wp_ajax_wpp_update_chart', $plugin_admin, 'update_chart' );
        // Get lists
        $this->loader->add_action( 'wp_ajax_wpp_get_most_viewed', $plugin_admin, 'get_most_viewed' );
        $this->loader->add_action( 'wp_ajax_wpp_get_most_commented', $plugin_admin, 'get_most_commented' );
        // Delete plugin data
        $this->loader->add_action( 'wp_ajax_wpp_clear_data', $plugin_admin, 'clear_data' );
        // Empty plugin's images cache
        $this->loader->add_action( 'wp_ajax_wpp_clear_thumbnail', $plugin_admin, 'clear_thumbnails' );
        // Flush cached thumbnail on featured image change
        $this->loader->add_action( 'update_postmeta', $plugin_admin, 'flush_post_thumbnail', 10, 4 );
        // Purge post data on post/page deletion
        $this->loader->add_action( 'admin_init', $plugin_admin, 'purge_post_data' );
        // Purge old data on demand
        $this->loader->add_action( 'wpp_cache_event', $plugin_admin, 'purge_data' );
        // Initialize widget
        $this->loader->add_action( 'widgets_init', $plugin_admin, 'register_widget' );

    }

    /**
     * Register all of the hooks related to the public area functionality of the plugin.
     *
     * @since    4.0.0
     * @access   private
     */
    public function define_public_hooks() {

        $plugin_public = new WPP_Public( $this->get_plugin_name(), $this->get_version() );

        // Register logging AJAX hook
        $this->loader->add_action( 'wp_ajax_update_views_ajax', $plugin_public, 'update_views' );
        $this->loader->add_action( 'wp_ajax_nopriv_update_views_ajax', $plugin_public, 'update_views' );
        // Add WPP's stylesheet and scripts
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        // Add [wpp] shortcode
        $this->loader->add_shortcode( 'wpp', $plugin_public, 'wpp_shortcode' );
		// Register the REST route
        $this->loader->add_action( 'rest_api_init', $plugin_public, 'init_rest_route' );

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    4.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new WPP_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     4.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     4.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    4.0.0
     */
    public function run() {
        $this->loader->run();
    }

} // End WordPressPopularPosts class
