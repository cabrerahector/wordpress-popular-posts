<?php
/**
 * The admin-facing functionality of the plugin.
 *
 * Defines hooks to enqueue the admin-specific stylesheet and JavaScript,
 * plugin settings and other admin stuff.
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/Admin
 * @author     Hector Cabrera <me@cabrerahector.com>
 */

namespace WordPressPopularPosts\Admin;

use WordPressPopularPosts\{Helper, Image, Output, Query};

class Admin {

    /**
     * Slug of the plugin screen.
     *
     * @since   3.0.0
     * @var     string
     */
    protected $screen_hook_suffix = null;

    /**
     * Plugin options.
     *
     * @var     array      $config
     * @access  private
     */
    private $config;

    /**
     * Image object
     *
     * @since   4.0.2
     * @var     WordPressPopularPosts\Image
     */
    private $thumbnail;

    /**
     * Construct.
     *
     * @since   5.0.0
     * @param   array                               $config     Admin settings.
     * @param   \WordPressPopularPosts\Image        $thumbnail  Image class.
     */
    public function __construct(array $config, Image $thumbnail)
    {
        $this->config = $config;
        $this->thumbnail = $thumbnail;

        // Delete old data on demand
        if ( 1 == $this->config['tools']['log']['limit'] ) {
            if ( ! wp_next_scheduled('wpp_cache_event') ) {
                $midnight = strtotime('midnight') - ( get_option('gmt_offset') * HOUR_IN_SECONDS ) + DAY_IN_SECONDS;
                wp_schedule_event($midnight, 'daily', 'wpp_cache_event');
            }
        } else {
            // Remove the scheduled event if exists
            $timestamp = wp_next_scheduled('wpp_cache_event');

            if ( $timestamp ) {
                wp_unschedule_event($timestamp, 'wpp_cache_event');
            }
        }

        // Allow WP themers / coders to override data sampling status (active/inactive)
        $this->config['tools']['sampling']['active'] = apply_filters('wpp_data_sampling', $this->config['tools']['sampling']['active']);

        if (
            ! ( wp_using_ext_object_cache() && defined('WPP_CACHE_VIEWS') && WPP_CACHE_VIEWS ) // Not using a persistent object cache
            && ! $this->config['tools']['sampling']['active'] // Not using Data Sampling
        ) {
            // Schedule performance nag
            if ( ! wp_next_scheduled('wpp_maybe_performance_nag') ) {
                wp_schedule_event(time(), 'hourly', 'wpp_maybe_performance_nag');
            }
        } else {
            // Remove the scheduled performance nag if found
            $timestamp = wp_next_scheduled('wpp_maybe_performance_nag');

            if ( $timestamp ) {
                wp_unschedule_event($timestamp, 'wpp_maybe_performance_nag');
            }
        }
    }

    /**
     * WordPress public-facing hooks.
     *
     * @since   5.0.0
     */
    public function hooks()
    {
        // Upgrade check
        add_action('init', [$this, 'upgrade_check']);
        // Hook fired when a new blog is activated on WP Multisite
        add_action('wpmu_new_blog', [$this, 'activate_new_site']);
        // Hook fired when a blog is deleted on WP Multisite
        add_filter('wpmu_drop_tables', [$this, 'delete_site_data'], 10, 2);
        // At-A-Glance
        add_filter('dashboard_glance_items', [$this, 'at_a_glance_stats']);
        add_action('admin_head', [$this, 'at_a_glance_stats_css']);
        // Dashboard Trending Now widget
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widgets']);
        // Load WPP's admin styles and scripts
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        // Add admin screen
        add_action('admin_menu', [$this, 'add_plugin_admin_menu']);
        // Contextual help
        add_action('admin_head', [$this, 'add_contextual_help']);
        // Add plugin settings link
        add_filter('plugin_action_links', [$this, 'add_plugin_settings_link'], 10, 2);
        // Update chart
        add_action('wp_ajax_wpp_update_chart', [$this, 'update_chart']);
        // Get lists
        add_action('wp_ajax_wpp_get_most_viewed', [$this, 'get_popular_items']);
        add_action('wp_ajax_wpp_get_most_commented', [$this, 'get_popular_items']);
        add_action('wp_ajax_wpp_get_trending', [$this, 'get_popular_items']);
        // Reset plugin's default thumbnail
        add_action('wp_ajax_wpp_reset_thumbnail', [$this, 'get_default_thumbnail']);
        // Empty plugin's images cache
        add_action('wp_ajax_wpp_clear_thumbnail', [$this, 'clear_thumbnails']);
        // Flush cached thumbnail on featured image change/deletion
        add_action('updated_post_meta', [$this, 'updated_post_meta'], 10, 4);
        add_action('deleted_post_meta', [$this, 'deleted_post_meta'], 10, 4);
        // Purge transients when sending post/page to trash
        add_action('wp_trash_post', [$this, 'purge_data_cache']);
        // Purge post data on post/page deletion
        add_action('admin_init', [$this, 'purge_post_data']);
        // Purge old data on demand
        add_action('wpp_cache_event', [$this, 'purge_data']);
        // Maybe performance nag
        add_action('wpp_maybe_performance_nag', [$this, 'performance_check']);
        add_action('wp_ajax_wpp_handle_performance_notice', [$this, 'handle_performance_notice']);
        // Show notices
        add_action('admin_notices', [$this, 'notices']);
    }

    /**
     * Checks if an upgrade procedure is required.
     *
     * @since   2.4.0
     */
    public function upgrade_check()
    {
        $this->upgrade_site();
    }

    /**
     * Checks whether a performance tweak may be necessary.
     *
     * @since   5.0.2
     */
    public function performance_check()
    {
        $performance_nag = get_option('wpp_performance_nag');

        if ( ! $performance_nag ) {
            $performance_nag = [
                'status' => 0,
                'last_checked' => null
            ];
            add_option('wpp_performance_nag', $performance_nag);
        }

        if ( 3 != $performance_nag['status'] ) { // 0 = inactive, 1 = active, 2 = remind me later, 3 = dismissed
            global $wpdb;

            //phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $views_count = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT IFNULL(SUM(pageviews), 0) AS views FROM {$wpdb->prefix}popularpostssummary WHERE view_datetime > DATE_SUB(%s, INTERVAL 1 HOUR);",
                    Helper::now()
                )
            );
            //phpcs:enable

            // This site is probably a mid/high traffic one,
            // display performance nag
            if ( $views_count >= 420 ) {
                if ( 0 == $performance_nag['status'] ) {
                    $performance_nag['status'] = 1;
                    $performance_nag['last_checked'] = Helper::timestamp();
                    update_option('wpp_performance_nag', $performance_nag);
                }
            }
        }
    }

    /**
     * Upgrades single site.
     *
     * @since   4.0.7
     */
    private function upgrade_site()
    {
        // Get WPP version
        $wpp_ver = get_option('wpp_ver');

        if ( ! $wpp_ver ) {
            add_option('wpp_ver', WPP_VERSION);
        } elseif ( version_compare($wpp_ver, WPP_VERSION, '<') ) {
            $this->upgrade();
        }
    }

    /**
     * On plugin upgrade, performs a number of actions: update WPP database tables structures (if needed),
     * run the setup wizard (if needed), and some other checks.
     *
     * @since   2.4.0
     * @access  private
     * @global  object  $wpdb
     */
    private function upgrade()
    {
        $now = Helper::now();

        // Keep the upgrade process from running too many times
        $wpp_update = get_option('wpp_update');

        if ( $wpp_update ) {
            $from_time = strtotime($wpp_update);
            $to_time = strtotime($now);
            $difference_in_minutes = round(abs($to_time - $from_time)/60, 2);

            // Upgrade flag is still valid, abort
            if ( $difference_in_minutes <= 15 ) {
                return;
            }

            // Upgrade flag expired, delete it and continue
            delete_option('wpp_update');
        }

        global $wpdb;

        // Upgrade flag
        add_option('wpp_update', $now);

        // Set table name
        $prefix = $wpdb->prefix . 'popularposts';

        //phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange

        // Update data table structure and indexes
        $dataFields = $wpdb->get_results("SHOW FIELDS FROM {$prefix}data;");

        foreach ( $dataFields as $column ) {
            if ( 'day' == $column->Field ) {
                $wpdb->query("ALTER TABLE {$prefix}data ALTER COLUMN day DROP DEFAULT;");
            }

            if ( 'last_viewed' == $column->Field ) {
                $wpdb->query("ALTER TABLE {$prefix}data ALTER COLUMN last_viewed DROP DEFAULT;");
            }
        }

        // Update summary table structure and indexes
        $summaryFields = $wpdb->get_results("SHOW FIELDS FROM {$prefix}summary;");

        foreach ( $summaryFields as $column ) {
            if ( 'last_viewed' == $column->Field ) {
                $wpdb->query("ALTER TABLE {$prefix}summary CHANGE last_viewed view_datetime datetime NOT NULL, ADD KEY view_datetime (view_datetime);");
            }

            if ( 'view_date' == $column->Field ) {
                $wpdb->query("ALTER TABLE {$prefix}summary ALTER COLUMN view_date DROP DEFAULT;");
            }

            if ( 'view_datetime' == $column->Field ) {
                $wpdb->query("ALTER TABLE {$prefix}summary ALTER COLUMN view_datetime DROP DEFAULT;");
            }
        }

        $summaryIndexes = $wpdb->get_results("SHOW INDEX FROM {$prefix}summary;");

        foreach( $summaryIndexes as $index ) {
            if ( 'ID_date' == $index->Key_name ) {
                $wpdb->query("ALTER TABLE {$prefix}summary DROP INDEX ID_date;");
            }

            if ( 'last_viewed' == $index->Key_name ) {
                $wpdb->query("ALTER TABLE {$prefix}summary DROP INDEX last_viewed;");
            }
        }

        $transientsIndexes = $wpdb->get_results("SHOW INDEX FROM {$prefix}transients;");
        $transientsHasTKeyIndex = false;

        foreach( $transientsIndexes as $index ) {
            if ( 'tkey' == $index->Key_name ) {
                $transientsHasTKeyIndex = true;
                break;
            }
        }

        if ( ! $transientsHasTKeyIndex ) {
            $wpdb->query("TRUNCATE TABLE {$prefix}transients;");
            $wpdb->query("ALTER TABLE {$prefix}transients ADD UNIQUE KEY tkey (tkey);");
        }

        // Validate the structure of the tables, create missing tables / fields if necessary
        \WordPressPopularPosts\Activation\Activator::track_new_site();

        // Check storage engine
        $storage_engine_data = $wpdb->get_var("SELECT `ENGINE` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA`='{$wpdb->dbname}' AND `TABLE_NAME`='{$prefix}data';");

        if ( 'InnoDB' != $storage_engine_data ) {
            $wpdb->query("ALTER TABLE {$prefix}data ENGINE=InnoDB;");
        }

        $storage_engine_summary = $wpdb->get_var("SELECT `ENGINE` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA`='{$wpdb->dbname}' AND `TABLE_NAME`='{$prefix}summary';");

        if ( 'InnoDB' != $storage_engine_summary ) {
            $wpdb->query("ALTER TABLE {$prefix}summary ENGINE=InnoDB;");
        }

        //phpcs:enable

        // Update WPP version
        update_option('wpp_ver', WPP_VERSION);
        // Remove upgrade flag
        delete_option('wpp_update');
    }

    /**
     * Fired when a new blog is activated on WP Multisite.
     *
     * @since    3.0.0
     * @param    int      $blog_id    New blog ID
     */
    public function activate_new_site(int $blog_id)
    {
        if ( 1 !== did_action('wpmu_new_blog') ) {
            return;
        }

        // run activation for the new blog
        switch_to_blog($blog_id);
        \WordPressPopularPosts\Activation\Activator::track_new_site();
        // switch back to current blog
        restore_current_blog();
    }

    /**
     * Fired when a blog is deleted on WP Multisite.
     *
     * @since    4.0.0
     * @param    array     $tables
     * @param    int       $blog_id
     * @return   array
     */
    public function delete_site_data(array $tables, int $blog_id)
    {
        global $wpdb;

        $tables[] = $wpdb->prefix . 'popularpostsdata';
        $tables[] = $wpdb->prefix . 'popularpostssummary';

        return $tables;
    }

    /**
     * Display some statistics at the "At a Glance" box from the Dashboard.
     *
     * @since    4.1.0
     */
    public function at_a_glance_stats()
    {
        global $wpdb;

        $glances = [];
        $args = ['post', 'page'];
        $post_type_placeholders = '%s, %s';

        if (
            isset($this->config['stats']['post_type']) 
            && ! empty($this->config['stats']['post_type'])
        ) {
            $args = array_map('trim', explode(',', $this->config['stats']['post_type']));
            $post_type_placeholders = implode(', ', array_fill(0, count($args), '%s'));
        }

        $args[] = Helper::now();

        //phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $post_type_placeholder is safe to use
        $query = $wpdb->prepare(
            "SELECT SUM(pageviews) AS total 
            FROM `{$wpdb->prefix}popularpostssummary` v LEFT JOIN `{$wpdb->prefix}posts` p ON v.postid = p.ID 
            WHERE p.post_type IN({$post_type_placeholders}) AND p.post_status = 'publish' AND p.post_password = '' AND v.view_datetime > DATE_SUB(%s, INTERVAL 1 HOUR);",
            $args
        );
        //phpcs:enable

        $total_views = $wpdb->get_var($query); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- $query is built and prepared above
        $total_views = (float) $total_views;

        $pageviews = sprintf(
            _n('%s view in the last hour', '%s views in the last hour', $total_views, 'wordpress-popular-posts'),
            number_format_i18n($total_views)
        );

        if ( current_user_can('edit_published_posts') ) {
            $glances[] = '<a class="wpp-views-count" href="' . admin_url('options-general.php?page=wordpress-popular-posts') . '">' . $pageviews . '</a>';
        }
        else {
            $glances[] = '<span class="wpp-views-count">' . $pageviews . '</a>';
        }

        return $glances;
    }

    /**
     * Add custom inline CSS styles for At a Glance stats.
     *
     * @since    4.1.0
     */
    public function at_a_glance_stats_css()
    {
        echo '<style>#dashboard_right_now a.wpp-views-count:before, #dashboard_right_now span.wpp-views-count:before {content: "\f177";}</style>';
    }

    /**
     * Adds a widget to the dashboard.
     *
     * @since   5.0.0
     */
    public function add_dashboard_widgets()
    {
        if ( current_user_can('edit_published_posts') ) {
            wp_add_dashboard_widget(
                'wpp_trending_dashboard_widget',
                __('Trending now', 'wordpress-popular-posts'),
                [$this, 'trending_dashboard_widget']
            );
        }
    }

    /**
     * Outputs the contents of our Trending Dashboard Widget.
     *
     * @since   5.0.0
     */
    public function trending_dashboard_widget()
    {
        ?>
        <style>
            #wpp_trending_dashboard_widget .inside {
                overflow: hidden;
                position: relative;
                min-height: 150px;
                padding-bottom: 22px;
            }

            #wpp_trending_dashboard_widget .inside::after {
                position: absolute;
                top: 0;
                left: 0;
                opacity: 0.2;
                display: block;
                content: '';
                width: 100%;
                height: 100%;
                z-index: 1;
                background-image: url('<?php echo esc_url(plugin_dir_url(dirname(dirname(__FILE__)))) . 'assets/images/flame.png'; ?>');
                background-position: right bottom;
                background-repeat: no-repeat;
                background-size: 34% auto;
            }

                #wpp_trending_dashboard_widget .inside .no-data {
                    position: absolute;
                    top: calc(50% - 11px);
                    left: 50%;
                    z-index: 2;
                    margin: 0;
                    padding: 0;
                    width: 96%;
                    transform: translate(-50.0001%, -50.0001%);
                }

                #wpp_trending_dashboard_widget .inside .popular-posts-list,
                #wpp_trending_dashboard_widget .inside p#wpp_read_more {
                    position: relative;
                    z-index: 2;
                }

                #wpp_trending_dashboard_widget .inside .popular-posts-list {
                    margin: 1em 0;
                }

                #wpp_trending_dashboard_widget .inside p#wpp_read_more {
                    position: absolute;
                    left: 0;
                    bottom: 0;
                    width: 100%;
                    text-align: center;
                }
        </style>
        <?php
        $args = [
            'range' => 'custom',
            'time_quantity' => 1,
            'time_unit' => 'HOUR',
            'post_type' => $this->config['stats']['post_type'],
            'limit' => 5,
            'stats_tag' => [
                'views' => 1,
                'comment_count' => 1
            ]
        ];
        $options = apply_filters('wpp_trending_dashboard_widget_args', []);

        if ( is_array($options) && ! empty($options) ) {
            $args = Helper::merge_array_r($args, $options);
        }

        $query = new Query($args);
        $posts = $query->get_posts();

        $this->render_list($posts, 'trending');
        echo '<p id="wpp_read_more"><a href="' . esc_url(admin_url('options-general.php?page=wordpress-popular-posts')) . '">' . esc_html(__('View more', 'wordpress-popular-posts')) . '</a><p>';

    }

    /**
     * Enqueues admin facing assets.
     *
     * @since   5.0.0
     */
    public function enqueue_assets()
    {
        $screen = get_current_screen();

        if ( isset($screen->id) ) {
            if ( $screen->id == $this->screen_hook_suffix ) {
                wp_enqueue_style('wpp-datepicker-theme', plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/css/datepicker.css', [], WPP_VERSION, 'all');

                wp_enqueue_media();
                wp_enqueue_script('jquery-ui-datepicker');
                wp_enqueue_script('chartjs', plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/vendor/chart.3.8.0.min.js', [], WPP_VERSION);

                wp_register_script('wpp-chart', plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/chart.js', ['chartjs'], WPP_VERSION);
                wp_localize_script('wpp-chart', 'wpp_chart_params', [
                    'colors' => $this->get_admin_color_scheme()
                ]);
                wp_enqueue_script('wpp-chart');

                wp_register_script('wordpress-popular-posts-admin-script', plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/admin.js', ['jquery'], WPP_VERSION, true); /** @TODO Drop jQuery datepicker dep */
                wp_localize_script('wordpress-popular-posts-admin-script', 'wpp_admin_params', [
                    'label_media_upload_button' => __('Use this image', 'wordpress-popular-posts'),
                    'nonce' => wp_create_nonce('wpp_admin_nonce'),
                    'nonce_reset_thumbnails' => wp_create_nonce('wpp_nonce_reset_thumbnails'),
                    'text_confirm_image_cache_reset' => __('This operation will delete all cached thumbnails and cannot be undone.', 'wordpress-popular-posts'),
                    'text_image_cache_cleared' => __('Success! All files have been deleted!', 'wordpress-popular-posts'),
                    'text_image_cache_already_empty' => __('The thumbnail cache is already empty!', 'wordpress-popular-posts'),
                    'text_continue' => __('Do you want to continue?', 'wordpress-popular-posts'),
                    'text_insufficient_permissions' => __('Sorry, you do not have enough permissions to do this. Please contact the site administrator for support.', 'wordpress-popular-posts'),
                    'text_invalid_action' => __('Invalid action.', 'wordpress-popular-posts')
                ]);
                wp_enqueue_script('wordpress-popular-posts-admin-script');
            }

            if ( $screen->id == $this->screen_hook_suffix || 'dashboard' == $screen->id ) {
                // Fontello icons
                wp_enqueue_style('wpp-fontello', plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/css/fontello.css', [], WPP_VERSION, 'all');
                wp_enqueue_style('wordpress-popular-posts-admin-styles', plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/css/admin.css', [], WPP_VERSION, 'all');
            }
        }

        $performance_nag = get_option('wpp_performance_nag');

        if (
            isset($performance_nag['status'])
            && 3 != $performance_nag['status'] // 0 = inactive, 1 = active, 2 = remind me later, 3 = dismissed
        ) {
            $now = Helper::timestamp();

            // How much time has passed since the notice was last displayed?
            $last_checked = isset($performance_nag['last_checked']) ? $performance_nag['last_checked'] : 0;

            if ( $last_checked ) {
                $last_checked = ($now - $last_checked) / (60 * 60);
            }

            if (
                1 == $performance_nag['status']
                || ( 2 == $performance_nag['status'] && $last_checked && $last_checked >= 24 )
            ) {
                wp_register_script('wpp-admin-notices', plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/admin-notices.js', [], WPP_VERSION);
                wp_localize_script('wpp-admin-notices', 'wpp_admin_notices_params', [
                    'nonce_performance_nag' => wp_create_nonce('wpp_nonce_performance_nag')
                ]);
                wp_enqueue_script('wpp-admin-notices');
            }
        }
    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu()
    {
        $this->screen_hook_suffix = add_options_page(
            'WordPress Popular Posts',
            'WordPress Popular Posts',
            'edit_published_posts',
            'wordpress-popular-posts',
            [$this, 'display_plugin_admin_page']
        );
    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page()
    {
        include_once plugin_dir_path(__FILE__) . 'admin-page.php';
    }

    /**
     * Adds contextual help menu.
     *
     * @since   4.0.0
     */
    public function add_contextual_help()
    {
        $screen = get_current_screen();

        if ( isset($screen->id) && $screen->id == $this->screen_hook_suffix ){
            $screen->add_help_tab(
                [
                    'id'        => 'wpp_help_overview',
                    'title'     => __('Overview', 'wordpress-popular-posts'),
                    'content'   => '<p>' . __("Welcome to WordPress Popular Posts' Dashboard! In this screen you will find statistics on what's popular on your site, tools to further tweak WPP to your needs, and more!", 'wordpress-popular-posts') . '</p>'
                ]
            );
            $screen->add_help_tab(
                [
                    'id'        => 'wpp_help_donate',
                    'title'     => __('Like this plugin?', 'wordpress-popular-posts'),
                    'content'   => '
                        <p style="text-align: center;">' . __('Each donation motivates me to keep releasing free stuff for the WordPress community!', 'wordpress-popular-posts') . '</p>
                        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" style="margin: 0; padding: 0; text-align: center;">
                            <input type="hidden" name="cmd" value="_s-xclick">
                            <input type="hidden" name="hosted_button_id" value="RP9SK8KVQHRKS">
                            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" style="display: inline; margin: 0;">
                            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
                        </form>
                        <p style="text-align: center;">' . sprintf(__('You can <a href="%s" target="_blank">leave a review</a>, too!', 'wordpress-popular-posts'), 'https://wordpress.org/support/view/plugin-reviews/wordpress-popular-posts?rate=5#postform') . '</p>'
                ]
            );

            // Help sidebar
            $screen->set_help_sidebar(
                sprintf(
                    __('<p><strong>For more information:</strong></p><ul><li><a href="%1$s">Documentation</a></li><li><a href="%2$s">Support</a></li></ul>', 'wordpress-popular-posts'),
                    'https://github.com/cabrerahector/wordpress-popular-posts/',
                    'https://wordpress.org/support/plugin/wordpress-popular-posts/'
                )
            );
        }
    }

    /**
     * Registers Settings link on plugin description.
     *
     * @since   2.3.3
     * @param   array   $links
     * @param   string  $file
     * @return  array
     */
    public function add_plugin_settings_link(array $links, string $file)
    {
        $plugin_file = 'wordpress-popular-posts/wordpress-popular-posts.php';

        if (
            is_plugin_active($plugin_file)
            && $plugin_file == $file
        ) {
            array_unshift(
                $links,
                '<a href="' . admin_url('options-general.php?page=wordpress-popular-posts') . '">' . __('Settings') . '</a>', // phpcs:ignore WordPress.WP.I18n.MissingArgDomain -- We're using WordPress' translation here
                '<a href="https://wordpress.org/support/plugin/wordpress-popular-posts/">' . __('Support', 'wordpress-popular-posts') . '</a>'
            );
        }

        return $links;
    }

    /**
     * Gets current admin color scheme.
     *
     * @since   4.0.0
     * @return  array
     */
    private function get_admin_color_scheme()
    {
        global $_wp_admin_css_colors;

        if (
            is_array($_wp_admin_css_colors)
            && count($_wp_admin_css_colors)
        ) {
            $current_user = wp_get_current_user();
            $color_scheme = get_user_option('admin_color', $current_user->ID);

            if (
                empty($color_scheme)
                || ! isset($_wp_admin_css_colors[ $color_scheme])
            ) {
                $color_scheme = 'fresh';
            }

            if ( isset($_wp_admin_css_colors[$color_scheme]) && isset($_wp_admin_css_colors[$color_scheme]->colors) ) {
                return $_wp_admin_css_colors[$color_scheme]->colors;
            }

        }

        // Fallback, just in case
        return ['#333', '#999', '#881111', '#a80000'];
    }

    /**
     * Fetches chart data.
     *
     * @since   4.0.0
     * @return  string
     */
    public function get_chart_data(string $range = 'last7days', string $time_unit = 'HOUR', int $time_quantity = 24)
    {
        $dates = $this->get_dates($range, $time_unit, $time_quantity);
        $start_date = $dates[0];
        $end_date = $dates[count($dates) - 1];
        $date_range = Helper::get_date_range($start_date, $end_date, 'Y-m-d H:i:s');
        $views_data = $this->get_range_item_count($start_date, $end_date, 'views');
        $views = [];
        $comments_data = $this->get_range_item_count($start_date, $end_date, 'comments');
        $comments = [];

        if ( 'today' != $range ) {
            foreach($date_range as $date) {
                $key = date('Y-m-d', strtotime($date));
                $views[] = ( ! isset($views_data[$key]) ) ? 0 : $views_data[$key]->pageviews;
                $comments[] = ( ! isset($comments_data[$key]) ) ? 0 : $comments_data[$key]->comments;
            }
        } else {
            $key = date('Y-m-d', strtotime($dates[0]));
            $views[] = ( ! isset($views_data[$key]) ) ? 0 : $views_data[$key]->pageviews;
            $comments[] = ( ! isset($comments_data[$key]) ) ? 0 : $comments_data[$key]->comments;
        }

        if ( $start_date != $end_date ) {
            $label_date_range = date_i18n('M, D d', strtotime($start_date)) . ' &mdash; ' . date_i18n('M, D d', strtotime($end_date));
        } else {
            $label_date_range = date_i18n('M, D d', strtotime($start_date));
        }

        $total_views = array_sum($views);
        $total_comments = array_sum($comments);

        $label_summary = sprintf(_n('%s view', '%s views', $total_views, 'wordpress-popular-posts'), '<strong>' . number_format_i18n($total_views) . '</strong>') . ' / ' . sprintf(_n('%s comment', '%s comments', $total_comments, 'wordpress-popular-posts'), '<strong>' . number_format_i18n($total_comments) . '</strong>');

        // Format labels
        if ( 'today' != $range ) {
            $date_range = array_map(function($d) {
                return date_i18n('D d', strtotime($d));
            }, $date_range);
        } else {
            $date_range = [date_i18n('D d', strtotime($date_range[0]))];
            $comments = [array_sum($comments)];
            $views = [array_sum($views)];
        }

        $response = [
            'totals' => [
                'label_summary' => $label_summary,
                'label_date_range' => $label_date_range,
            ],
            'labels' => $date_range,
            'datasets' => [
                [
                    'label' => __('Comments', 'wordpress-popular-posts'),
                    'data' => $comments
                ],
                [
                    'label' => __('Views', 'wordpress-popular-posts'),
                    'data' => $views
                ]
            ]
        ];

        return json_encode($response);
    }

    /**
     * Returns an array of dates.
     *
     * @since   5.0.0
     * @return  array|bool
     */
    private function get_dates(string $range = 'last7days', string $time_unit = 'HOUR', int $time_quantity = 24)
    {
        $valid_ranges = ['today', 'daily', 'last24hours', 'weekly', 'last7days', 'monthly', 'last30days', 'all', 'custom'];
        $range = in_array($range, $valid_ranges) ? $range : 'last7days';
        $now = new \DateTime(Helper::now(), wp_timezone());

        // Determine time range
        switch( $range ){
            case 'last24hours':
            case 'daily':
                $end_date = $now->format('Y-m-d H:i:s');
                $start_date = $now->modify('-1 day')->format('Y-m-d H:i:s');
                break;

            case 'today':
                $start_date = $now->format('Y-m-d') . ' 00:00:00';
                $end_date = $now->format('Y-m-d') . ' 23:59:59';
                break;

            case 'last7days':
            case 'weekly':
                $end_date = $now->format('Y-m-d') . ' 23:59:59';
                $start_date = $now->modify('-6 day')->format('Y-m-d') . ' 00:00:00';
                break;

            case 'last30days':
            case 'monthly':
                $end_date = $now->format('Y-m-d') . ' 23:59:59';
                $start_date = $now->modify('-29 day')->format('Y-m-d') . ' 00:00:00';
                break;

            case 'custom':
                $end_date = $now->format('Y-m-d H:i:s');

                if (
                    Helper::is_number($time_quantity)
                    && $time_quantity >= 1
                ) {
                    $end_date = $now->format('Y-m-d H:i:s');
                    $time_unit = strtoupper($time_unit);

                    if ( 'MINUTE' == $time_unit ) {
                        $start_date = $now->sub(new \DateInterval('PT' . (60 * $time_quantity) . 'S'))->format('Y-m-d H:i:s');
                    } elseif ( 'HOUR' == $time_unit ) {
                        $start_date = $now->sub(new \DateInterval('PT' . ((60 * $time_quantity) - 1) . 'M59S'))->format('Y-m-d H:i:s');
                    } else {
                        $end_date = $now->format('Y-m-d') . ' 23:59:59';
                        $start_date = $now->sub(new \DateInterval('P' . ($time_quantity - 1) . 'D'))->format('Y-m-d') . ' 00:00:00';
                    }
                } // fallback to last 24 hours
                else {
                    $start_date = $now->modify('-1 day')->format('Y-m-d H:i:s');
                }

                // Check if custom date range has been requested
                $dates = null;

                // phpcs:disable WordPress.Security.NonceVerification.Recommended -- 'dates' are date strings, and we're validating those below
                if ( isset($_GET['dates']) ) {
                    $dates = explode(' ~ ', esc_html($_GET['dates']));

                    if (
                        ! is_array($dates)
                        || empty($dates)
                        || ! Helper::is_valid_date($dates[0])
                    ) {
                        $dates = null;
                    } else {
                        if (
                            ! isset($dates[1])
                            || ! Helper::is_valid_date($dates[1])
                        ) {
                            $dates[1] = $dates[0];
                        }

                        $start_date = $dates[0] . ' 00:00:00';
                        $end_date = $dates[1] . ' 23:59:59';
                    }
                }
                // phpcs:enable

                break;

            default:
                $end_date = $now->format('Y-m-d') . ' 23:59:59';
                $start_date = $now->modify('-6 day')->format('Y-m-d') . ' 00:00:00';
                break;
        }

        return [$start_date, $end_date];
    }

    /**
     * Returns an array of dates with views/comments count.
     *
     * @since   5.0.0
     * @param   string  $start_date
     * @param   string  $end_date
     * @param   string  $item
     * @return  array
     */
    public function get_range_item_count(string $start_date, string $end_date, string $item = 'views')
    {
        global $wpdb;

        $args = array_map('trim', explode(',', $this->config['stats']['post_type']));

        $types = get_post_types([
            'public' => true
        ], 'names' );
        $types = array_values($types);

        // Let's make sure we're getting valid post types
        $args = array_intersect($types, $args);

        if ( empty($args) ) {
            $args = ['post', 'page'];
        }

        $post_type_placeholders = array_fill(0, count($args), '%s');

        if ( $this->config['stats']['freshness'] ) {
            $args[] = $start_date;
        }

        // Append dates to arguments list
        array_unshift($args, $start_date, $end_date);

        if ( $item == 'comments' ) {
            //phpcs:disable WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- $post_type_placeholders is already prepared above
            $query = $wpdb->prepare(
                "SELECT DATE(`c`.`comment_date_gmt`) AS `c_date`, COUNT(*) AS `comments` 
                FROM `{$wpdb->comments}` c INNER JOIN `{$wpdb->posts}` p ON `c`.`comment_post_ID` = `p`.`ID`
                WHERE (`c`.`comment_date_gmt` BETWEEN %s AND %s) AND `c`.`comment_approved` = '1' AND `p`.`post_type` IN (" . implode(', ', $post_type_placeholders) . ") AND `p`.`post_status` = 'publish' AND `p`.`post_password` = '' 
                " . ( $this->config['stats']['freshness'] ? ' AND `p`.`post_date` >= %s' : '' ) . '
                GROUP BY `c_date` ORDER BY `c_date` DESC;',
                $args
            );
            //phpcs:enable
        } else {
            //phpcs:disable WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- $post_type_placeholders is already prepared above
            $query = $wpdb->prepare(
                "SELECT `v`.`view_date`, SUM(`v`.`pageviews`) AS `pageviews` 
                FROM `{$wpdb->prefix}popularpostssummary` v INNER JOIN `{$wpdb->posts}` p ON `v`.`postid` = `p`.`ID`
                WHERE (`v`.`view_datetime` BETWEEN %s AND %s) AND `p`.`post_type` IN (" . implode(', ', $post_type_placeholders) . ") AND `p`.`post_status` = 'publish' AND `p`.`post_password` = '' 
                " . ( $this->config['stats']['freshness'] ? ' AND `p`.`post_date` >= %s' : '' ) . '
                GROUP BY `v`.`view_date` ORDER BY `v`.`view_date` DESC;',
                $args
            );
            //phpcs:enable

            //error_log($query);
        }

        return $wpdb->get_results($query, OBJECT_K); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- at this point $query has been prepared already
    }

    /**
     * Updates chart via AJAX.
     *
     * @since   4.0.0
     */
    public function update_chart()
    {
        $response = [
            'status' => 'error'
        ];
        $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : null; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This is a nonce

        if ( wp_verify_nonce($nonce, 'wpp_admin_nonce') ) {

            $valid_ranges = ['today', 'daily', 'last24hours', 'weekly', 'last7days', 'monthly', 'last30days', 'all', 'custom'];
            $time_units = ['MINUTE', 'HOUR', 'DAY'];

            $range = ( isset($_GET['range']) && in_array($_GET['range'], $valid_ranges) ) ? $_GET['range'] : 'last7days';
            $time_quantity = ( isset($_GET['time_quantity']) && filter_var($_GET['time_quantity'], FILTER_VALIDATE_INT) ) ? $_GET['time_quantity'] : 24;
            $time_unit = ( isset($_GET['time_unit']) && in_array(strtoupper($_GET['time_unit']), $time_units) ) ? $_GET['time_unit'] : 'hour';

            $this->config['stats']['range'] = $range;
            $this->config['stats']['time_quantity'] = $time_quantity;
            $this->config['stats']['time_unit'] = $time_unit;

            update_option('wpp_settings_config', $this->config);

            $response = [
                'status' => 'ok',
                'data' => json_decode(
                    $this->get_chart_data($this->config['stats']['range'], $this->config['stats']['time_unit'], $this->config['stats']['time_quantity']),
                    true
                )
            ];
        }

        wp_send_json($response);
    }

    /**
     * Fetches most viewed/commented/trending posts via AJAX.
     *
     * @since   5.0.0
     */
    public function get_popular_items()
    {
        $items = isset($_GET['items']) ? $_GET['items'] : null; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification happens below
        $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : null; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This is a nonce

        if ( wp_verify_nonce($nonce, 'wpp_admin_nonce') ) {
            $args = [
                'range' => $this->config['stats']['range'],
                'time_quantity' => $this->config['stats']['time_quantity'],
                'time_unit' => $this->config['stats']['time_unit'],
                'post_type' => $this->config['stats']['post_type'],
                'freshness' => $this->config['stats']['freshness'],
                'limit' => $this->config['stats']['limit'],
                'stats_tag' => [
                    'date' => [
                        'active' => 1
                    ]
                ]
            ];

            if ( 'most-commented' == $items ) {
                $args['order_by'] = 'comments';
                $args['stats_tag']['comment_count'] = 1;
                $args['stats_tag']['views'] = 0;
            } elseif ( 'trending' == $items ) {
                $args['range'] = 'custom';
                $args['time_quantity'] = 1;
                $args['time_unit'] = 'HOUR';
                $args['stats_tag']['comment_count'] = 1;
                $args['stats_tag']['views'] = 1;
            } else {
                $args['stats_tag']['comment_count'] = 0;
                $args['stats_tag']['views'] = 1;
            }

            if ( 'trending' != $items ) {

                add_filter('wpp_query_join', function($join, $options) use ($items) {
                    global $wpdb;
                    $dates = null;

                    if ( isset($_GET['dates']) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is checked above, 'dates' is verified below
                        $dates = explode(' ~ ', esc_html($_GET['dates'])); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

                        if (
                            ! is_array($dates)
                            || empty($dates)
                            || ! Helper::is_valid_date($dates[0])
                        ) {
                            $dates = null;
                        } else {
                            if (
                                ! isset($dates[1])
                                || ! Helper::is_valid_date($dates[1])
                            ) {
                                $dates[1] = $dates[0];
                            }

                            $start_date = $dates[0];
                            $end_date = $dates[1];
                        }

                    }

                    if ( $dates ) {
                        if ( 'most-commented' == $items ) {
                            return "INNER JOIN (SELECT comment_post_ID, COUNT(comment_post_ID) AS comment_count, comment_date_gmt FROM `{$wpdb->comments}` WHERE comment_date_gmt BETWEEN '{$dates[0]} 00:00:00' AND '{$dates[1]} 23:59:59' AND comment_approved = '1' GROUP BY comment_post_ID) c ON p.ID = c.comment_post_ID";
                        }

                        return "INNER JOIN (SELECT SUM(pageviews) AS pageviews, view_date, postid FROM `{$wpdb->prefix}popularpostssummary` WHERE view_datetime BETWEEN '{$dates[0]} 00:00:00' AND '{$dates[1]} 23:59:59' GROUP BY postid) v ON p.ID = v.postid";
                    }

                    $now = Helper::now();

                    // Determine time range
                    switch( $options['range'] ){
                        case 'last24hours':
                        case 'daily':
                            $interval = '24 HOUR';
                            break;

                        case 'today':
                            $hours = date('H', strtotime($now));
                            $minutes = $hours * 60 + (int) date( 'i', strtotime($now) );
                            $interval = "{$minutes} MINUTE";
                            break;

                        case 'last7days':
                        case 'weekly':
                            $interval = '6 DAY';
                            break;

                        case 'last30days':
                        case 'monthly':
                            $interval = '29 DAY';
                            break;

                        case 'custom':
                            $time_units = ['MINUTE', 'HOUR', 'DAY'];
                            $interval = '24 HOUR';

                            // Valid time unit
                            if (
                                isset($options['time_unit'])
                                && in_array(strtoupper($options['time_unit']), $time_units)
                                && isset($options['time_quantity'])
                                && filter_var($options['time_quantity'], FILTER_VALIDATE_INT)
                                && $options['time_quantity'] > 0
                            ) {
                                $interval = "{$options['time_quantity']} " . strtoupper($options['time_unit']);
                            }

                            break;

                        default:
                            $interval = '1 DAY';
                            break;
                    }

                    if ( 'most-commented' == $items ) {
                        return "INNER JOIN (SELECT comment_post_ID, COUNT(comment_post_ID) AS comment_count, comment_date_gmt FROM `{$wpdb->comments}` WHERE comment_date_gmt > DATE_SUB('{$now}', INTERVAL {$interval}) AND comment_approved = '1' GROUP BY comment_post_ID) c ON p.ID = c.comment_post_ID";
                    }

                    return "INNER JOIN (SELECT SUM(pageviews) AS pageviews, view_date, postid FROM `{$wpdb->prefix}popularpostssummary` WHERE view_datetime > DATE_SUB('{$now}', INTERVAL {$interval}) GROUP BY postid) v ON p.ID = v.postid";
                }, 1, 2);

            }

            $query = new Query($args);
            $posts = $query->get_posts();

            if ( 'trending' != $items ) {
                remove_all_filters('wpp_query_join', 1);
            }

            $this->render_list($posts, $items);
        }

        wp_die();
    }

    /**
     * Renders popular posts lists.
     *
     * @since   5.0.0
     * @param   array
     */
    public function render_list(array $posts, $list = 'most-viewed')
    {
        if ( ! empty($posts) ) {
            ?>
            <ol class="popular-posts-list">
                <?php
                foreach( $posts as $post ) {
                    $pageviews = isset($post->pageviews) ? (int) $post->pageviews : 0;
                    $comments_count = isset($post->comment_count) ? (int) $post->comment_count : 0;
                    ?>
                    <li>
                        <a href="<?php echo esc_url(get_permalink($post->id)); ?>" class="wpp-title"><?php echo esc_html(sanitize_text_field(apply_filters('the_title', $post->title, $post->id))); ?></a>
                        <div>
                            <?php if ( 'most-viewed' == $list ) : ?>
                            <span><?php printf(esc_html(_n('%s view', '%s views', $pageviews, 'wordpress-popular-posts')), esc_html(number_format_i18n($pageviews))); ?></span>
                            <?php elseif ( 'most-commented' == $list ) : ?>
                            <span><?php printf(esc_html(_n('%s comment', '%s comments', $comments_count, 'wordpress-popular-posts')), esc_html(number_format_i18n($comments_count))); ?></span>
                            <?php else : ?>
                            <span><?php printf(esc_html(_n('%s view', '%s views', $pageviews, 'wordpress-popular-posts')), esc_html(number_format_i18n($pageviews))); ?></span>, <span><?php printf(esc_html(_n('%s comment', '%s comments', $comments_count, 'wordpress-popular-posts')), esc_html(number_format_i18n($comments_count))); ?></span>
                            <?php endif; ?>
                            <small> &mdash; <a href="<?php echo esc_url(get_permalink($post->id)); ?>"><?php esc_html_e('View'); ?></a><?php if ( current_user_can('edit_others_posts') ): ?> | <a href="<?php echo esc_url(get_edit_post_link($post->id)); ?>"><?php esc_html_e('Edit'); ?></a><?php endif; ?></small>
                        </div>
                    </li>
                    <?php
                }
                ?>
            </ol>
            <?php
        }
        else {
            ?>
            <p class="no-data" style="text-align: center;"><?php _e("Looks like your site's activity is a little low right now. <br />Spread the word and come back later!", 'wordpress-popular-posts'); //phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction ?></p>
            <?php
        }
    }

    /**
     * Deletes cached (transient) data.
     *
     * @since   3.0.0
     * @access  private
     */
    private function flush_transients()
    {
        global $wpdb;

        $wpp_transients = $wpdb->get_results("SELECT tkey FROM {$wpdb->prefix}popularpoststransients;"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

        if ( $wpp_transients && is_array($wpp_transients) && ! empty($wpp_transients) ) {
            foreach( $wpp_transients as $wpp_transient ) {
                try {
                    delete_transient($wpp_transient->tkey);
                } catch (\Throwable $e) {
                    if ( defined('WP_DEBUG') && WP_DEBUG ) {
                        error_log( "Error: " . $e->getMessage() );
                    }
                    continue;
                }
            }

            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}popularpoststransients;");
        }
    }

    /**
     * Returns WPP's default thumbnail.
     *
     * @since 6.3.4
     */
    public function get_default_thumbnail()
    {
        echo esc_url(plugins_url('assets/images/no_thumb.jpg', dirname(__FILE__, 2)));
        wp_die();
    }

    /**
     * Truncates thumbnails cache on demand.
     *
     * @since   2.0.0
     * @global  object  $wpdb
     */
    public function clear_thumbnails()
    {
        $wpp_uploads_dir = $this->thumbnail->get_plugin_uploads_dir();
        $token = isset($_POST['token']) ? $_POST['token'] : null; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- This is a nonce

        if (
            current_user_can('edit_published_posts')
            && wp_verify_nonce($token, 'wpp_nonce_reset_thumbnails')
        ) {
            echo $this->delete_thumbnails();
        } else {
            echo 4;
        }

        wp_die();
    }

    /**
     * Deletes WPP thumbnails from the uploads/wordpress-popular-posts folder.
     *
     * @since  7.0.0
     * @return int   1 on success, 2 if no thumbnails were found, 3 if WPP's folder can't be reached
     */
    private function delete_thumbnails()
    {
        $wpp_uploads_dir = $this->thumbnail->get_plugin_uploads_dir();

        if (
            is_array($wpp_uploads_dir)
            && ! empty($wpp_uploads_dir)
            && is_dir($wpp_uploads_dir['basedir'])
        ) {
            $files = glob("{$wpp_uploads_dir['basedir']}/*");

            if ( is_array($files) && ! empty($files) ) {
                foreach( $files as $file ) {
                    if ( is_file($file) ) {
                        @unlink($file); // delete file
                    }
                }

                return 1;
            }

            return 2;
        }

        return 3;
    }

    /**
     * Fires immediately after deleting metadata of a post.
     *
     * @since 5.0.0
     *
     * @param int    $meta_id    Metadata ID.
     * @param int    $post_id    Post ID.
     * @param string $meta_key   Meta key.
     * @param mixed  $meta_value Meta value.
     */
    public function updated_post_meta(int $meta_id, int $post_id, string $meta_key, $meta_value) /** @TODO: starting PHP 8.0 $meta_valued can be declared as mixed $meta_value, see https://www.php.net/manual/en/language.types.declarations.php */
    {
        if ( '_thumbnail_id' == $meta_key ) {
            $this->flush_post_thumbnail($post_id);
        }
    }

    /**
     * Fires immediately after deleting metadata of a post.
     *
     * @since 5.0.0
     *
     * @param array  $meta_ids   An array of deleted metadata entry IDs.
     * @param int    $post_id    Post ID.
     * @param string $meta_key   Meta key.
     * @param mixed  $meta_value Meta value.
     */
    public function deleted_post_meta(array $meta_ids, int $post_id, string $meta_key, $meta_value) /** @TODO: starting PHP 8.0 $meta_valued can be declared as mixed $meta_value */
    {
        if ( '_thumbnail_id' == $meta_key ) {
            $this->flush_post_thumbnail($post_id);
        }
    }

    /**
     * Flushes post's cached thumbnail(s).
     *
     * @since    3.3.4
     *
     * @param    integer    $post_id     Post ID
     */
    public function flush_post_thumbnail(int $post_id)
    {
        $wpp_uploads_dir = $this->thumbnail->get_plugin_uploads_dir();

        if ( is_array($wpp_uploads_dir) && ! empty($wpp_uploads_dir) ) {
            $files = glob("{$wpp_uploads_dir['basedir']}/{$post_id}-*.*"); // get all related images

            if ( is_array($files) && ! empty($files) ) {
                foreach( $files as $file ){ // iterate files
                    if ( is_file($file) ) {
                        @unlink($file); // delete file
                    }
                }
            }
        }
    }

    /**
     * Purges data cache when a post/page is trashed.
     *
     * @since 5.5.0
     */
    public function purge_data_cache()
    {
        $this->flush_transients();
    }

    /**
     * Purges post from data/summary tables.
     *
     * @since    3.3.0
     */
    public function purge_post_data()
    {
        if ( current_user_can('delete_posts') ) {
            add_action('delete_post', [$this, 'purge_post']);
        }
    }

    /**
     * Purges post from data/summary tables.
     *
     * @since    3.3.0
     * @param    int      $post_ID
     * @global   object   $wpdb
     */
    public function purge_post(int $post_ID)
    {
        global $wpdb;

        $post_ID_exists = $wpdb->get_var($wpdb->prepare("SELECT postid FROM {$wpdb->prefix}popularpostsdata WHERE postid = %d", $post_ID)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

        if ( $post_ID_exists ) {
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            // Delete from data table
            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}popularpostsdata WHERE postid = %d;", $post_ID));
            // Delete from summary table
            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}popularpostssummary WHERE postid = %d;", $post_ID));
            // phpcs:enable
        }

        // Delete cached thumbnail(s) as well
        $this->flush_post_thumbnail($post_ID);
    }

    /**
     * Purges old post data from summary table.
     *
     * @since   2.0.0
     * @global  object  $wpdb
     */
    public function purge_data()
    {
        global $wpdb;
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}popularpostssummary WHERE view_date < DATE_SUB(%s, INTERVAL %d DAY);",
                Helper::curdate(),
                $this->config['tools']['log']['expires_after']
            )
        );
        //phpcs:enable
    }

    /**
     * Displays admin notices.
     *
     * @since   5.0.2
     */
    public function notices()
    {
        /** Performance nag */
        $performance_nag = get_option('wpp_performance_nag');

        if (
            isset($performance_nag['status'])
            && 3 != $performance_nag['status'] // 0 = inactive, 1 = active, 2 = remind me later, 3 = dismissed
        ) {
            $now = Helper::timestamp();

            // How much time has passed since the notice was last displayed?
            $last_checked = isset($performance_nag['last_checked']) ? $performance_nag['last_checked'] : 0;

            if ( $last_checked ) {
                $last_checked = ($now - $last_checked) / (60 * 60);
            }

            if (
                1 == $performance_nag['status']
                || ( 2 == $performance_nag['status'] && $last_checked && $last_checked >= 24 )
            ) {
                ?>
                <div class="notice notice-warning">
                    <p>
                        <strong>WordPress Popular Posts:</strong> 
                        <?php
                        printf(
                            wp_kses(
                                __('It seems that your site is popular (great!) You may want to check <a href="%s">these recommendations</a> to make sure that its performance stays up to par.', 'wordpress-popular-posts'),
                                [
                                    'a' => [
                                        'href' => []
                                    ]
                                ]
                            ),
                            'https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance'
                        );
                        ?>
                    </p>
                    <?php if ( current_user_can('manage_options') ) : ?>
                        <p><a class="button button-primary wpp-dismiss-performance-notice" href="<?php echo esc_url(add_query_arg('wpp_dismiss_performance_notice', '1')); ?>"><?php esc_html_e('Dismiss', 'wordpress-popular-posts'); ?></a> <a class="button wpp-remind-performance-notice" href="<?php echo esc_url(add_query_arg('wpp_remind_performance_notice', '1')); ?>"><?php esc_html_e('Remind me later', 'wordpress-popular-posts'); ?></a> <span class="spinner" style="float: none;"></span></p>
                    <?php endif; ?>
                </div>
                <?php
            }
        }

        $pretty_permalinks_enabled = get_option('permalink_structure');

        if ( ! $pretty_permalinks_enabled ) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong>WordPress Popular Posts:</strong> 
                    <?php
                    printf(
                        wp_kses(
                            /* translators: third placeholder corresponds to the I18N version of the "Plain" permalink structure option */
                            __('It looks like your site is not using <a href="%s">Pretty Permalinks</a>. Please <a href="%s">select a permalink structure</a> other than <em>%s</em> so WordPress Popular Posts can do its job.', 'wordpress-popular-posts'),
                            [
                                'a' => [
                                    'href' => []
                                ],
                                'em' => []
                            ]
                        ),
                        'https://wordpress.org/documentation/article/customize-permalinks/#pretty-permalinks',
                        esc_url(admin_url('options-permalink.php')),
                        __('Plain')
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Handles performance notice click event.
     *
     * @since
     */
    public function handle_performance_notice()
    {
        $response = [
            'status' => 'error'
        ];
        $token = isset($_POST['token']) ? $_POST['token'] : null; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- This is a nonce
        $dismiss = isset($_POST['dismiss']) ? (int) $_POST['dismiss'] : 0;

        if (
            current_user_can('manage_options')
            && wp_verify_nonce($token, 'wpp_nonce_performance_nag')
        ) {
            $now = Helper::timestamp();

            // User dismissed the notice
            if ( 1 == $dismiss ) {
                $performance_nag['status'] = 3;
            } // User asked us to remind them later
            else {
                $performance_nag['status'] = 2;
            }

            $performance_nag['last_checked'] = $now;

            update_option('wpp_performance_nag', $performance_nag);

            $response = [
                'status' => 'success'
            ];
        }

        wp_send_json($response);
    }
}
