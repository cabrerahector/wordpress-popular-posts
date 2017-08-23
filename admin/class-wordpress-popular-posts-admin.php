<?php

/**
 * The admin-facing functionality of the plugin.
 *
 * @link       http://cabrerahector.com/
 * @since      4.0.0
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/public
 * @author     Hector Cabrera <me@cabrerahector.com>
 */
class WPP_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    4.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    4.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;
    
    /**
     * Administrative settings.
     *
     * @since	2.3.3
     * @var		array
     */
    private $options = array();
    
    /**
     * Slug of the plugin screen.
     *
     * @since	3.0.0
     * @var		string
     */
    protected $plugin_screen_hook_suffix = NULL;

    /**
     * Initialize the class and set its properties.
     *
     * @since    4.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->options = WPP_Settings::get( 'admin_options' );
        
        // Delete old data on demand
        if ( 1 == $this->options['tools']['log']['limit'] ) {
            
            if ( !wp_next_scheduled( 'wpp_cache_event' ) ) {
                $tomorrow = time() + 86400;
                $midnight  = mktime(
                    0,
                    0,
                    0,
                    date( "m", $tomorrow ),
                    date( "d", $tomorrow ),
                    date( "Y", $tomorrow )
                );
                wp_schedule_event( $midnight, 'daily', 'wpp_cache_event' );
            }
            
        } else {
            // Remove the scheduled event if exists
            if ( $timestamp = wp_next_scheduled( 'wpp_cache_event' ) ) {
                wp_unschedule_event( $timestamp, 'wpp_cache_event' );
            }
            
        }
        
        // Allow WP themers / coders to override data sampling status (active/inactive)
        $this->options['tools']['sampling']['active'] = apply_filters( 'wpp_data_sampling', $this->options['tools']['sampling']['active'] );

    }
    
    /**
     * Fired when a new blog is activated on WP Multisite.
     *
     * @since    3.0.0
     * @param    int      blog_id    New blog ID
     */
    public function activate_new_site( $blog_id ){

        if ( 1 !== did_action( 'wpmu_new_blog' ) )
            return;

        // run activation for the new blog
        switch_to_blog( $blog_id );
        WPP_Activator::track_new_site();

        // switch back to current blog
        restore_current_blog();

    } // end activate_new_site
    
    /**
     * Fired when a blog is deleted on WP Multisite.
     *
     * @since    4.0.0
     * @param    array     $tables
     * @param    int       $blog_id
     * @return   array
     */
    public function delete_site_data( $tables, $blog_id ){

        global $wpdb;
        
        $tables[] = $wpdb->prefix . 'popularpostsdata';
        $tables[] = $wpdb->prefix . 'popularpostssummary';
        
        return $tables;

    } // end delete_site_data

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    4.0.0
     */
    public function enqueue_styles() {
        
        if ( !isset( $this->plugin_screen_hook_suffix ) ) {
            return;
        }

        $screen = get_current_screen();
        
        if ( isset( $screen->id ) && $screen->id == $this->plugin_screen_hook_suffix ) {
            wp_enqueue_style( 'font-awesome', plugin_dir_url( __FILE__ ) . 'css/vendor/font-awesome.min.css', array(), '4.7.0', 'all' );
            wp_enqueue_style( 'wordpress-popular-posts-admin-styles', plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), $this->version, 'all' );
        }
        
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    4.0.0
     */
    public function enqueue_scripts() {
        
        if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
            return;
        }

        $screen = get_current_screen();
        
        if ( $screen->id == $this->plugin_screen_hook_suffix ) {
            
            wp_enqueue_script( 'thickbox' );
            wp_enqueue_style( 'thickbox' );
            wp_enqueue_script( 'media-upload' );
            wp_enqueue_script( 'chartjs', plugin_dir_url( __FILE__ ) . 'js/vendor/Chart.min.js', array(), $this->version );
            wp_enqueue_script( 'wpp-chart', plugin_dir_url( __FILE__ ) . 'js/chart.js', array('chartjs'), $this->version );
            wp_register_script( 'wordpress-popular-posts-admin-script', plugin_dir_url( __FILE__ ) . 'js/admin.js', array('jquery'), $this->version, true );
            wp_localize_script( 'wordpress-popular-posts-admin-script', 'wpp_admin_params', array(
                'nonce' => wp_create_nonce( "wpp_admin_nonce" )
            ));
            wp_enqueue_script( 'wordpress-popular-posts-admin-script' );
            
        }
        
    }
    
    /**
     * Hooks into getttext to change upload button text when uploader is called by WPP.
     *
     * @since	2.3.4
     */
    public function thickbox_setup() {

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
    public function replace_thickbox_text( $translated_text, $text, $domain ) {

        if ( 'Insert into Post' == $text ) {
            $referer = strpos( wp_get_referer(), 'wpp_admin' );
            if ( $referer != '' ) {
                return __( 'Upload', 'wordpress-popular-posts' );
            }
        }

        return $translated_text;

    } // end replace_thickbox_text
    
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
            'wordpress-popular-posts',
            array( $this, 'display_plugin_admin_page' )
        );

    }

    public function chart_query_fields( $fields, $options ){

        if ( 'comments' == $this->options['stats']['order_by'] ) {
            return "DATE(c.comment_date_gmt) AS 'date', COUNT(c.comment_post_ID) AS 'comment_count'";
        }

        return "v.view_date AS 'date', SUM(v.pageviews) AS 'pageviews'";

    }

    public function chart_query_table( $table, $options ){

        if ( 'comments' == $this->options['stats']['order_by'] ) {
            return "`wp_comments` c";
        }

        return $table;

    }

    public function chart_query_join( $join, $options ){

        if ( 'comments' == $this->options['stats']['order_by'] ) {
            return "INNER JOIN `wp_posts` p ON c.comment_post_ID = p.ID";
        }

        return $table;

    }

    public function chart_query_where( $where, $options ){

        global $wpdb;

        $now = WPP_Helper::now();

        $where = "WHERE 1 = 1";

        // Determine time range
        switch( $this->options['stats']['range'] ){
            case "last24hours":
            case "daily":
                $interval = "24 HOUR";
                break;

            case "last7days":
            case "weekly":
                $interval = "6 DAY";
                break;

            case "last30days":
            case "monthly":
                $interval = "29 DAY";
                break;

            case "custom":
                $time_units = array( "MINUTE", "HOUR", "DAY" );
                $interval = "24 HOUR";

                // Valid time unit
                if (
                    isset( $this->options['stats']['time_unit'] )
                    && in_array( strtoupper( $this->options['stats']['time_unit'] ), $time_units )
                    && isset( $this->options['stats']['time_quantity'] )
                    && filter_var( $this->options['stats']['time_quantity'], FILTER_VALIDATE_INT )
                    && $this->options['stats']['time_quantity'] > 0
                ) {
                    $interval = "{$this->options['stats']['time_quantity']} " . strtoupper( $this->options['stats']['time_unit'] );
                }

                break;

            default:
                $interval = "1 DAY";
                break;
        }

        $post_types = array_map( 'trim', explode( ',', $this->options['stats']['post_type'] ) );
        $placeholders = array();

        if ( empty($post_types) ) {
            $post_types = array('post', 'page');
        }

        foreach ( $post_types as $post_type ){
            $placeholders[] = '%s';
        }

        $where .= $wpdb->prepare(
            " AND p.post_type IN(" . implode( ', ', $placeholders ) . ") ",
            $post_types
        );

        // Get entries published within the specified time range
        if ( isset( $this->options['stats']['freshness'] ) && $this->options['stats']['freshness'] ) {
            $where .= " AND p.post_date > DATE_SUB('{$now}', INTERVAL {$interval})";
        }

        if ( 'comments' == $this->options['stats']['order_by'] ) {
            return $where . " AND c.comment_date_gmt > DATE_SUB('{$now}', INTERVAL {$interval}) AND c.comment_approved = 1 AND p.post_password = '' AND p.post_status = 'publish'";
        }

        return $where . " AND v.last_viewed  > DATE_SUB('{$now}', INTERVAL {$interval}) AND p.post_password = '' AND p.post_status = 'publish' ";

    }

    public function chart_query_group_by( $groupby, $options ){
        return "GROUP BY date";
    }

    public function chart_query_order_by( $orderby, $options ){
        return "ORDER BY date ASC";
    }

    public function chart_query_limit( $fields, $options ){
        return "";
    }

    public function get_chart_data( $range ){

        $now = new DateTime( WPP_Helper::now() );
        $data = array(
            'dates' => null,
            'totals' => array(
                'views' => 0,
                'comments' => 0,
                'label_summary' => '',
                'label_date_range' => ''
            )
        );

        // Determine time range
        switch( $range ){
            case "last24hours":
            case "daily":
                /*$start_date = $now->format('Y-m-d');
                $end_date = $start_date;*/
                $end_date = $now->format('Y-m-d');
                $start_date = $now->modify('-1 day')->format('Y-m-d');
                break;

            case "today":
                $start_date = $now->format('Y-m-d');
                $end_date = $start_date;
                break;

            case "last7days":
            case "weekly":
                $end_date = $now->format('Y-m-d');
                $start_date = $now->modify('-6 day')->format('Y-m-d');
                break;

            case "last30days":
            case "monthly":
                $end_date = $now->format('Y-m-d');
                $start_date = $now->modify('-29 day')->format('Y-m-d');
                break;

            case "custom":
                $time_units = array(
                    "MINUTE" => array("minute", "minutes"),
                    "HOUR" => array("hour", "hours"),
                    "DAY" => array("day", "days")
                );
                $interval = "-24 hours";

                // Valid time unit
                if (
                    isset( $this->options['stats']['time_unit'] )
                    && isset( $time_units[ strtoupper( $this->options['stats']['time_unit'] ) ] )
                    && isset( $this->options['stats']['time_quantity'] )
                    && filter_var( $this->options['stats']['time_quantity'], FILTER_VALIDATE_INT )
                    && $this->options['stats']['time_quantity'] > 0
                ) {
                    $interval = "-{$this->options['stats']['time_quantity']} " . ( $this->options['stats']['time_quantity'] > 1 ? $time_units[ strtoupper( $this->options['stats']['time_unit'] ) ][1] : $time_units[ strtoupper( $this->options['stats']['time_unit'] ) ][0] );
                }

                $end_date = date( 'Y-m-d', strtotime( $now->format('Y-m-d H:i:s') ) );
                $start_date = date( 'Y-m-d', strtotime( $now->modify($interval)->format('Y-m-d H:i:s') ) );

                break;

            default:
                $end_date = $now->format('Y-m-d');
                $start_date = $now->modify('-6 days')->format('Y-m-d');
                break;
        }

        $dates = WPP_Helper::get_date_range( $start_date, $end_date );

        if ( $dates ) {

            for( $d = 0; $d < count($dates); $d++ ) {
                $data['dates'][ $dates[$d] ] = array(
                    'nicename' => date_i18n( 'D d', strtotime( $dates[$d] ) ),
                    'views' => 0,
                    'comments' => 0
                );
            }

        }

        add_filter( 'wpp_query_fields', array( $this, 'chart_query_fields' ), 1, 2 );
        add_filter( 'wpp_query_where', array( $this, 'chart_query_where' ), 1, 2 );
        add_filter( 'wpp_query_group_by', array( $this, 'chart_query_group_by' ), 1, 2 );
        add_filter( 'wpp_query_order_by', array( $this, 'chart_query_order_by' ), 1, 2 );
        add_filter( 'wpp_query_limit', array( $this, 'chart_query_limit' ), 1, 2 );

        $original_order_by = $this->options['stats']['order_by'];
        $this->options['stats']['order_by'] = 'views';

        $most_viewed = new WPP_query();
        $views_data = $most_viewed->get_posts();

        $this->options['stats']['order_by'] = $original_order_by;

        remove_filter( 'wpp_query_fields', array( $this, 'chart_query_fields' ), 1 );
        remove_filter( 'wpp_query_where', array( $this, 'chart_query_where' ), 1 );
        remove_filter( 'wpp_query_group_by', array( $this, 'chart_query_group_by' ), 1 );
        remove_filter( 'wpp_query_order_by', array( $this, 'chart_query_order_by' ), 1 );
        remove_filter( 'wpp_query_limit', array( $this, 'chart_query_limit' ), 1 );

        $original_order_by = $this->options['stats']['order_by'];
        $this->options['stats']['order_by'] = 'comments';

        add_filter( 'wpp_query_fields', array( $this, 'chart_query_fields' ), 1, 2 );
        add_filter( 'wpp_query_table', array( $this, 'chart_query_table' ), 1, 2 );
        add_filter( 'wpp_query_join', array( $this, 'chart_query_join' ), 1, 2 );
        add_filter( 'wpp_query_where', array( $this, 'chart_query_where' ), 1, 2 );
        add_filter( 'wpp_query_group_by', array( $this, 'chart_query_group_by' ), 1, 2 );
        add_filter( 'wpp_query_order_by', array( $this, 'chart_query_order_by' ), 1, 2 );
        add_filter( 'wpp_query_limit', array( $this, 'chart_query_limit' ), 1, 2 );

        $most_commented = new WPP_query();
        $comments_data = $most_commented->get_posts();

        $this->options['stats']['order_by'] = $original_order_by;

        remove_filter( 'wpp_query_fields', array( $this, 'chart_query_fields' ), 1 );
        remove_filter( 'wpp_query_table', array( $this, 'chart_query_table' ), 1 );
        remove_filter( 'wpp_query_join', array( $this, 'chart_query_join' ), 1 );
        remove_filter( 'wpp_query_where', array( $this, 'chart_query_where' ), 1 );
        remove_filter( 'wpp_query_group_by', array( $this, 'chart_query_group_by' ), 1 );
        remove_filter( 'wpp_query_order_by', array( $this, 'chart_query_order_by' ), 1 );
        remove_filter( 'wpp_query_limit', array( $this, 'chart_query_limit' ), 1 );

        if (
            ( is_array($views_data) && !empty($views_data) ) 
            || ( is_array($comments_data) && !empty($comments_data) )
        ) {

            if ( ( is_array($views_data) && !empty($views_data) ) ) {
                foreach( $views_data as $views ) {
                    if ( isset( $data['dates'][$views->date] ) ) {
                        $data['dates'][$views->date]['views'] = $views->pageviews;
                        $data['totals']['views'] += $views->pageviews;
                    }
                }
            }

            if ( ( is_array($comments_data) && !empty($comments_data) ) ) {
                foreach( $comments_data as $comments ) {
                    if ( isset( $data['dates'][$comments->date] ) ) {
                        $data['dates'][$comments->date]['comments'] = $comments->comment_count;
                        $data['totals']['comments'] += $comments->comment_count;
                    }
                }
            }

            $data['totals']['label_summary'] = sprintf( _n( '1 view', '%s views', $data['totals']['views'], 'wordpress-popular-posts' ), '<strong>' . number_format_i18n( $data['totals']['views'] ) . '</strong>' ) . '<br style="display: none;" /> / ' .  sprintf( _n( '1 comment', '%s comments', $data['totals']['comments'], 'wordpress-popular-posts' ), '<strong>' . number_format_i18n( $data['totals']['comments'] ) . '</strong>' );

            $data['totals']['label_date_range'] = date_i18n( 'M, D d', strtotime( $start_date ) ) . ' &mdash; ' . date_i18n( 'M, D d', strtotime( $end_date ) );
        }

        return $data;

    }

    public function print_chart_script( $data, $containter_id ){

        reset( $data['dates'] );
        $start_date = key( $data['dates'] );
        $end_date = key( end($data['dates']) );
        reset( $data['dates'] );

        $color_scheme = $this->get_admin_color_scheme();

        ?>
        <script>

            if ( WPPChart.canRender() ) {

                jQuery("#<?php echo $containter_id; ?> p").remove();

                var wpp_chart_views_color = '<?php echo $color_scheme->colors[2]; ?>';
                var wpp_chart_comments_color = '<?php echo $color_scheme->colors[3]; ?>';

                var wpp_chart_data = {
                    labels: [ <?php foreach( $data['dates'] as $date => $date_data ) : echo "'" . date_i18n( 'D d', strtotime( $date ) ) . "', "; endforeach; ?>],
                    datasets: [
                        {
                            label: "<?php _e( "Comments", "wordpress-popular-posts" ); ?>",
                            data: [<?php foreach( $data['dates'] as $date => $date_data ) : echo ( isset($date_data['comments']) ? $date_data['comments'] : '0' ) . ", "; endforeach; ?>],
                        },
                        {
                            label: "<?php _e( "Views", "wordpress-popular-posts" ); ?>",
                            data: [<?php foreach( $data['dates'] as $date => $date_data ) : echo ( isset($date_data['views']) ? $date_data['views'] : '0' ) . ", "; endforeach; ?>],
                        }
                    ]
                };

                WPPChart.init( '<?php echo $containter_id; ?>' );
                WPPChart.populate( wpp_chart_data );

            }

        </script>
        <?php
    }

    public function update_chart(){

        $response = array(
            'status' => 'error'
        );
        $nonce = isset( $_GET['nonce'] ) ? $_GET['nonce'] : null;

        if ( wp_verify_nonce( $nonce, 'wpp_admin_nonce' ) ) {

            $valid_ranges = array( 'daily', 'last24hours', 'weekly', 'last7days', 'monthly', 'last30days', 'all', 'custom' );
            $time_units = array( "MINUTE", "HOUR", "DAY" );

            $range = ( isset( $_GET['range'] ) && in_array( $_GET['range'], $valid_ranges ) ) ? $_GET['range'] : 'last7days';
            $time_quantity = ( isset( $_GET['time_quantity'] ) && filter_var( $_GET['time_quantity'], FILTER_VALIDATE_INT ) ) ? $_GET['time_quantity'] : 24;
            $time_unit = ( isset( $_GET['time_unit'] ) && in_array( strtoupper( $_GET['time_unit'] ), $time_units ) ) ? $_GET['time_unit'] : 'hour';

            $admin_options = WPP_Settings::get( 'admin_options' );
            $admin_options['stats']['range'] = $range;
            $admin_options['stats']['time_quantity'] = $time_quantity;
            $admin_options['stats']['time_unit'] = $time_unit;
            $this->options = $admin_options;

            update_site_option( 'wpp_settings_config', $this->options );

            $response = array(
                'status' => 'ok',
                'data' => $this->get_chart_data( $range )
            );

        }

       wp_send_json( $response );

    }

    public function get_most_viewed(){

        $args = array(
            'range' => $this->options['stats']['range'],
            'time_quantity' => $this->options['stats']['time_quantity'],
            'time_unit' => $this->options['stats']['time_unit'],
            'post_type' => $this->options['stats']['post_type'],
            'freshness' => $this->options['stats']['freshness'],
            'limit' => $this->options['stats']['limit'],
            'stats_tag' => array(
                'comment_count' => 0,
                'date' => array(
                    'active' => 1
                )
            )
        );
        $most_viewed = new WPP_query( $args );
        $posts = $most_viewed->get_posts();

        if (
            is_array( $posts )
            && !empty( $posts )
        ) {
        ?>
        <ol class="popular-posts-list">
        <?php
            foreach ( $posts as $post ) { ?>
            <li>
                <p>
                    <a href="<?php echo get_permalink( $post->id ); ?>"><?php echo $post->title; ?></a>
                    <br />
                    <span><?php printf( _n( '1 view', '%s views', $post->pageviews, 'wordpress-popular-posts' ), number_format_i18n( $post->pageviews ) ); ?></span>
                    <small> &mdash; <a href="<?php echo get_permalink( $post->id ); ?>"><?php _e("View"); ?></a> | <a href="<?php echo get_edit_post_link( $post->id ); ?>"><?php _e("Edit"); ?></a></small>
                </p>
            </li>
            <?php
            }
        ?>
        </ol>
        <?php
        }
        else {
        ?>
        <p><?php _e("Err... nothing. Nada. Come back later, alright?"); ?></p>
        <?php
        }

        if ( defined('DOING_AJAX') && DOING_AJAX ) wp_die();

    }

    public function get_most_commented(){

        $args = array(
            'range' => $this->options['stats']['range'],
            'time_quantity' => $this->options['stats']['time_quantity'],
            'time_unit' => $this->options['stats']['time_unit'],
            'post_type' => $this->options['stats']['post_type'],
            'freshness' => $this->options['stats']['freshness'],
            'order_by' => 'comments',
            'limit' => $this->options['stats']['limit'],
            'stats_tag' => array(
                'comment_count' => 1,
                'views' => 0,
                'date' => array(
                    'active' => 1
                )
            )
        );
        $most_commented = new WPP_query( $args );
        $posts = $most_commented->get_posts();

        if (
            is_array( $posts )
            && !empty( $posts )
        ) {
        ?>
        <ol class="popular-posts-list">
        <?php
            foreach ( $posts as $post ) { ?>
            <li>
                <p>
                    <a href="<?php echo get_permalink( $post->id ); ?>"><?php echo $post->title; ?></a>
                    <br />
                    <span><?php printf( _n( '1 comment', '%s comments', $post->comment_count, 'wordpress-popular-posts' ), number_format_i18n( $post->comment_count ) ); ?></span>
                    <small> &mdash; <a href="<?php echo get_permalink( $post->id ); ?>"><?php _e("View"); ?></a> | <a href="<?php echo get_edit_post_link( $post->id ); ?>"><?php _e("Edit"); ?></a></small>
                </p>
            </li>
            <?php
            }
        ?>
        </ol>
        <?php
        }
        else {
        ?>
        <p><?php _e("Err... nothing. Nada. Come back later, alright?"); ?></p>
        <?php
        }

        if ( defined('DOING_AJAX') && DOING_AJAX ) wp_die();

    }

    /*
     * Gets current admin color scheme.
     *
     * @return stdClass
     */
    private function get_admin_color_scheme(){

        global $_wp_admin_css_colors;

        if (
            is_array( $_wp_admin_css_colors )
            && count( $_wp_admin_css_colors )
        ) {

            $current_user = wp_get_current_user();
            $color_scheme = get_user_option( 'admin_color', $current_user->ID );

            if (
                empty( $color_scheme ) 
                || !isset( $_wp_admin_css_colors[ $color_scheme ] )
            ) {
                $color_scheme = 'fresh';
            }

            if ( isset($_wp_admin_css_colors[ $color_scheme ]) ) {
                return $_wp_admin_css_colors[ $color_scheme ];
            }

        }

        // Fallback, just in case

        $theme = new stdClass;
        $theme->colors = ['#333', '#999', '#881111', '#a80000'];

        return $theme;

    }
    
    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {
        include_once( plugin_dir_path(__FILE__) . 'partials/admin.php' );
    }
    
    /**
     * Registers Settings link on plugin description.
     *
     * @since	2.3.3
     * @param	array	$links
     * @param	string	$file
     * @return	array
     */
    public function add_plugin_settings_link( $links, $file ){

        $plugin_file = 'wordpress-popular-posts/wordpress-popular-posts.php';

        if (
            is_plugin_active( $plugin_file ) 
            && $plugin_file == $file
        ) {
            $links[] = '<a href="' . admin_url( 'options-general.php?page=wordpress-popular-posts' ) . '">' . __( 'Settings' ) . '</a>';
        }

        return $links;

    }
    
    /**
     * Register the WPP widget.
     *
     * @since    4.0.0
     */
    public function register_widget() {
        register_widget( 'WPP_Widget' );
    }
    
    /**
     * Flushes post's cached thumbnail(s) when the image is changed.
     *
     * @since    3.3.4
     *
     * @param    integer    $meta_id       ID of the meta data field
     * @param    integer    $object_id     Object ID
     * @param    string     $meta_key      Name of meta field
     * @param    string     $meta_value    Value of meta field
     */
    public function flush_post_thumbnail( $meta_id, $object_id, $meta_key, $meta_value ) {

        // User changed the featured image
        if ( '_thumbnail_id' == $meta_key ) {

            $wpp_image = WPP_Image::get_instance();

            if ( $wpp_image->can_create_thumbnails() ) {
            
                $wpp_uploads_dir = $wpp_image->get_plugin_uploads_dir();
                
                if ( is_array($wpp_uploads_dir) && !empty($wpp_uploads_dir) ) {
            
                    $files = glob( "{$wpp_uploads_dir['basedir']}/{$object_id}-featured-*.*" ); // get all related images
                    
                    if ( is_array($files) && !empty($files) ) {

                        foreach( $files as $file ){ // iterate files
                            if ( is_file( $file ) ) {
                                @unlink( $file ); // delete file
                            }
                        }

                    }

                }

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
        
        $wpp_image = WPP_Image::get_instance();

        if ( $wpp_image->can_create_thumbnails() ) {
        
            $wpp_uploads_dir = $wpp_image->get_plugin_uploads_dir();
            
            if ( is_array($wpp_uploads_dir) && !empty($wpp_uploads_dir) ) {
                
                $token = isset( $_POST['token'] ) ? $_POST['token'] : null;
                $key = get_site_option( "wpp_rand" );				
                
                if (
                    current_user_can( 'manage_options' ) 
                    && ( $token === $key )
                ) {
                    
                    if ( is_dir( $wpp_uploads_dir['basedir'] ) ) {
                        
                        $files = glob( "{$wpp_uploads_dir['basedir']}/*" ); // get all related images
                    
                        if ( is_array($files) && !empty($files) ) {
    
                            foreach( $files as $file ){ // iterate files
                                if ( is_file( $file ) ) {
                                    @unlink( $file ); // delete file
                                }
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
                
            }
            
        } else {
            echo 3;
        }

        wp_die();

    }
    
    /**
     * Truncates data and cache on demand.
     *
     * @since	2.0.0
     * @global	object	wpdb
     */
    public function clear_data() {

        $token = $_POST['token'];
        $clear = isset( $_POST['clear'] ) ? $_POST['clear'] : null;
        $key = get_site_option( "wpp_rand" );

        if (
            current_user_can( 'manage_options' ) 
            && ( $token === $key ) 
            && $clear
        ) {
            
            global $wpdb;

            // set table name
            $prefix = $wpdb->prefix . "popularposts";

            if ( $clear == 'cache' ) {
                
                if ( $wpdb->get_var("SHOW TABLES LIKE '{$prefix}summary'") ) {
                    
                    $wpdb->query("TRUNCATE TABLE {$prefix}summary;");
                    $this->flush_transients();
                    
                    echo 1;
                    
                } else {
                    echo 2;
                }
                
            } elseif ( $clear == 'all' ) {
                
                if ( $wpdb->get_var("SHOW TABLES LIKE '{$prefix}data'") && $wpdb->get_var("SHOW TABLES LIKE '{$prefix}summary'") ) {
                    
                    $wpdb->query("TRUNCATE TABLE {$prefix}data;");
                    $wpdb->query("TRUNCATE TABLE {$prefix}summary;");
                    $this->flush_transients();
                    
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

        wp_die();

    }
    
    /**
     * Deletes cached (transient) data.
     *
     * @since   3.0.0
     * @access  private
     */
    private function flush_transients() {

        $wpp_transients = get_site_option( 'wpp_transients' );

        if ( $wpp_transients && is_array( $wpp_transients ) && !empty( $wpp_transients ) ) {
            
            for ( $t=0; $t < count( $wpp_transients ); $t++ )
                delete_transient( $wpp_transients[$t] );

            update_site_option( 'wpp_transients', array() );
            
        }

    }
    
    /**
     * Purges post from data/summary tables.
     *
     * @since    3.3.0
     */
    public function purge_post_data() {

        if ( current_user_can( 'delete_posts' ) )
            add_action( 'delete_post', array( $this, 'purge_post' ) );

    }

    /**
     * Purges post from data/summary tables.
     *
     * @since    3.3.0
     * @global   object   $wpdb
     * @return   bool
     */
    public function purge_post( $post_ID ) {

        global $wpdb;

        if ( $wpdb->get_var( $wpdb->prepare( "SELECT postid FROM {$wpdb->prefix}popularpostsdata WHERE postid = %d", $post_ID ) ) ) {
            // Delete from data table
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}popularpostsdata WHERE postid = %d;", $post_ID ) );
            // Delete from summary table
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}popularpostssummary WHERE postid = %d;", $post_ID ) );				
        }

    }
    
    /**
     * Purges old post data from summary table.
     *
     * @since	2.0.0
     * @global	object	$wpdb
     */
    public function purge_data() {

        global $wpdb;

        $wpdb->query( "DELETE FROM {$wpdb->prefix}popularpostssummary WHERE view_date < DATE_SUB('" . WPP_Helper::curdate() . "', INTERVAL {$this->options['tools']['log']['expires_after']} DAY);" );

    } // end purge_data
    
    /**
     * Checks if an upgrade procedure is required.
     *
     * @since	2.4.0
     */
    public function upgrade_check(){

        // Get WPP version
        $wpp_ver = get_site_option( 'wpp_ver' );

        if ( !$wpp_ver ) {
            add_site_option( 'wpp_ver', $this->version );
        } elseif ( version_compare( $wpp_ver, $this->version, '<' ) ) {
            $this->upgrade();
        }

    } // end upgrade_check

    /**
     * On plugin upgrade, performs a number of actions: update WPP database tables structures (if needed),
     * run the setup wizard (if needed), and some other checks.
     *
     * @since	2.4.0
     * @access  private
     * @global	object	wpdb
     */
    private function upgrade() {

        // Keep the upgrade process from running too many times
        if ( get_site_option('wpp_update') )
            return;
        
        add_site_option( 'wpp_update', '1' );

        global $wpdb;

        // Set table name
        $prefix = $wpdb->prefix . "popularposts";

        // Validate the structure of the tables, create missing tables / fields if necessary
        WPP_Activator::track_new_site();

        // If summary is empty, import data from popularpostsdatacache
        if ( !$wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}summary" ) ) {

            // popularpostsdatacache table is still there
            if ( $wpdb->get_var( "SHOW TABLES LIKE '{$prefix}datacache'" ) ) {

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
        $storage_engine_data = $wpdb->get_var( "SELECT `ENGINE` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA`='{$wpdb->dbname}' AND `TABLE_NAME`='{$prefix}data';" );
        
        if ( 'InnoDB' != $storage_engine_data ) {
            $wpdb->query( "ALTER TABLE {$prefix}data ENGINE=InnoDB;" );
        }
        
        $storage_engine_summary = $wpdb->get_var( "SELECT `ENGINE` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA`='{$wpdb->dbname}' AND `TABLE_NAME`='{$prefix}summary';" );
        
        if ( 'InnoDB' != $storage_engine_summary ) {
            $wpdb->query( "ALTER TABLE {$prefix}summary ENGINE=InnoDB;" );
        }

        // Update WPP version
        update_site_option( 'wpp_ver', $this->version );
        
        // Remove upgrade flag
        delete_site_option( 'wpp_update' );

    } // end __upgrade
    
    /**
     * Checks if the technical requirements are met.
     *
     * @since	2.4.0
     * @access  private
     * @link	http://wordpress.stackexchange.com/questions/25910/uninstall-activate-deactivate-a-plugin-typical-features-how-to/25979#25979
     * @global	string $wp_version
     * @return	array
     */
    private function check_requirements() {

        global $wp_version;

        $php_min_version = '5.2';
        $wp_min_version = '4.1';
        $php_current_version = phpversion();
        $errors = array();

        if ( version_compare( $php_min_version, $php_current_version, '>' ) ) {
            $errors[] = sprintf(
                __( 'Your PHP installation is too old. WordPress Popular Posts requires at least PHP version %1$s to function correctly. Please contact your hosting provider and ask them to upgrade PHP to %1$s or higher.', 'wordpress-popular-posts' ),
                $php_min_version
            );
        }

        if ( version_compare( $wp_min_version, $wp_version, '>' ) ) {
            $errors[] = sprintf(
                __( 'Your WordPress version is too old. WordPress Popular Posts requires at least WordPress version %1$s to function correctly. Please update your blog via Dashboard &gt; Update.', 'wordpress-popular-posts' ),
                $wp_min_version
            );
        }

        return $errors;

    } // end check_requirements
    
    /**
     * Outputs error messages to wp-admin.
     *
     * @since	2.4.0
     */
    public function check_admin_notices() {

        $errors = $this->check_requirements();

        if ( empty($errors) )
            return;

        if ( isset($_GET['activate']) )
            unset($_GET['activate']);

        printf(
            __('<div class="error"><p>%1$s</p><p><i>%2$s</i> has been <strong>deactivated</strong>.</p></div>', 'wordpress-popular-posts'),
            join( '</p><p>', $errors ),
            'WordPress Popular Posts'
        );

        $plugin_file = 'wordpress-popular-posts/wordpress-popular-posts.php';
        deactivate_plugins( $plugin_file );

    } // end check_admin_notices

} // End WPP_Admin class
