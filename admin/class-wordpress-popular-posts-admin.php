<?php

/**
 * The admin-facing functionality of the plugin.
 *
 * @link       https://cabrerahector.com/
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
                $midnight = strtotime( 'midnight' ) - ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) + DAY_IN_SECONDS;
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
     * Display some statistics at the "At a Glance" box from the Dashboard.
     *
     * @since    4.1.0
     */
    public function at_a_glance_stats(){

        global $wpdb;

        $glances = array();
        $args = array( 'post', 'page' );
        $post_type_placeholders = '%s, %s';

        if (
            isset( $this->options['stats']['post_type'] ) 
            && !empty( $this->options['stats']['post_type'] )
        ) {
            $args = array_map( 'trim', explode( ',', $this->options['stats']['post_type'] ) );
            $post_type_placeholders = implode( ', ', array_fill( 0, count( $args ), '%s' ) );
        }

        $args[] = WPP_Helper::now();

        $query = $wpdb->prepare(
            "SELECT SUM(pageviews) AS total 
            FROM `{$wpdb->prefix}popularpostssummary` v LEFT JOIN `{$wpdb->prefix}posts` p ON v.postid = p.ID 
            WHERE p.post_type IN( {$post_type_placeholders} ) AND p.post_status = 'publish' AND p.post_password = '' AND v.view_datetime > DATE_SUB( %s, INTERVAL 1 HOUR );"
            , $args
        );

        $total_views = $wpdb->get_var( $query );

        $pageviews = sprintf(
            _n( '1 view in the last hour', '%s views in the last hour', $total_views, 'wordpress-popular-posts' ),
            number_format_i18n( $total_views )
        );

        if ( current_user_can('manage_options') ) {
            $glances[] = '<a class="wpp-views-count" href="' . admin_url( 'options-general.php?page=wordpress-popular-posts' ) .'">' . $pageviews . '</a>';
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
    public function at_a_glance_stats_css(){
        echo '<style>#dashboard_right_now a.wpp-views-count:before, #dashboard_right_now span.wpp-views-count:before { content: "\f177"; }</style>';
    }

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
            wp_enqueue_style( 'wpp-datepicker-theme', plugin_dir_url( __FILE__ ) . 'css/datepicker.css', array(), $this->version, 'all' );
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

            wp_enqueue_media();
            wp_enqueue_script( 'jquery-ui-datepicker' );
            wp_enqueue_script( 'chartjs', plugin_dir_url( __FILE__ ) . 'js/vendor/Chart.min.js', array(), $this->version );
            wp_enqueue_script( 'wpp-chart', plugin_dir_url( __FILE__ ) . 'js/chart.js', array('chartjs'), $this->version );
            wp_register_script( 'wordpress-popular-posts-admin-script', plugin_dir_url( __FILE__ ) . 'js/admin.js', array('jquery'), $this->version, true );
            wp_localize_script( 'wordpress-popular-posts-admin-script', 'wpp_admin_params', array(
                'label_media_upload_button' => __( "Use this image", "wordpress-popular-posts" ),
                'nonce' => wp_create_nonce( "wpp_admin_nonce" )
            ));
            wp_enqueue_script( 'wordpress-popular-posts-admin-script' );

        }

    }

    public function add_contextual_help(){

        //get the current screen object
        $screen = get_current_screen();

        if ( isset( $screen->id ) && $screen->id == $this->plugin_screen_hook_suffix ){
            $screen->add_help_tab(
                array(
                    'id'        => 'wpp_help_overview',
                    'title'     => __('Overview', 'wordpress-popular-posts'),
                    'content'   => "<p>" . __( "Welcome to WordPress Popular Posts' Dashboard! In this screen you will find statistics on what's popular on your site, tools to further tweak WPP to your needs, and more!", "wordpress-popular-posts" ) . "</p>"
                )
            );
            $screen->add_help_tab(
                array(
                    'id'        => 'wpp_help_donate',
                    'title'     => __('Like this plugin?', 'wordpress-popular-posts'),
                    'content'   => '
                        <p style="text-align: center;">' . __( 'Each donation motivates me to keep releasing free stuff for the WordPress community!', 'wordpress-popular-posts' ) . '</p>
                        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" style="margin: 0; padding: 0; text-align: center;">
                            <input type="hidden" name="cmd" value="_s-xclick">
                            <input type="hidden" name="hosted_button_id" value="RP9SK8KVQHRKS">
                            <input type="image" src="//www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" style="display: inline; margin: 0;">
                            <img alt="" border="0" src="//www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
                        </form>
                        <p style="text-align: center;">' . sprintf( __('You can <a href="%s" target="_blank">leave a review</a>, too!', 'wordpress-popular-posts'), 'https://wordpress.org/support/view/plugin-reviews/wordpress-popular-posts?rate=5#postform' ) . '</p>'
                )
            );

            //$screen->remove_help_tabs();

            // Help sidebar
            $screen->set_help_sidebar(
                sprintf(
                    __( '<p><strong>For more information:</strong></p><ul><li><a href="%1$s">Documentation</a></li><li><a href="%2$s">Support</a></li></ul>', 'wordpress-popular-posts' ),
                    "https://github.com/cabrerahector/wordpress-popular-posts/",
                    "https://wordpress.org/support/plugin/wordpress-popular-posts/"
                )
            );
        }

    }

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

        if ( 'comments' == $options['order_by'] )
            return "DATE(comment_date_gmt) AS 'comments_date', COUNT(comment_post_ID) AS 'comment_count'";

        return "SUM(pageviews) AS 'pageviews', view_date";

    }

    public function chart_query_table( $table, $options ){

        global $wpdb;

        if ( 'comments' == $options['order_by'] )
            return "`{$wpdb->comments}` c";

        return "`{$wpdb->prefix}popularpostssummary` v";

    }

    public function chart_query_join( $join, $options ){

        global $wpdb;

        if ( 'comments' == $options['order_by'] )
            return "INNER JOIN `{$wpdb->posts}` p ON comment_post_ID = p.ID";

        return "INNER JOIN `{$wpdb->posts}` p ON postid = p.ID";

    }

    public function chart_query_where( $where, $options ){

        global $wpdb;

        $now = WPP_Helper::now();

        // Check if custom date range has been requested
        $dates = null;

        if ( isset( $_GET['dates']) ) {

            $dates = explode( " ~ ", $_GET['dates'] );

            if (
                !is_array( $dates )
                || empty( $dates )
                || !WPP_Helper::is_valid_date( $dates[0] )
            )
            {
                $dates = null;
            }
            else {
                if (
                    !isset( $dates[1] )
                    || !WPP_Helper::is_valid_date( $dates[1] )
                ) {
                    $dates[1] = $dates[0];
                }
            }

        }

        $where = "WHERE 1 = 1";

        // Determine time range
        switch( $options['range'] ){
            case "last24hours":
            case "daily":
                $interval = "24 HOUR";
                break;

            case "today":
                $hours = date( 'H', strtotime($now) );
                $minutes = $hours * 60 + (int) date( 'i', strtotime($now) );
                $interval = "{$minutes} MINUTE";
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
                    isset( $options['time_unit'] )
                    && in_array( strtoupper( $options['time_unit'] ), $time_units )
                    && isset( $options['time_quantity'] )
                    && filter_var( $options['time_quantity'], FILTER_VALIDATE_INT )
                    && $options['time_quantity'] > 0
                ) {
                    $interval = "{$options['time_quantity']} " . strtoupper( $options['time_unit'] );
                }

                break;

            default:
                $interval = "1 DAY";
                break;
        }

        $post_types = array_map( 'trim', explode( ',', $options['post_type'] ) );
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

        if ( $dates ) {
            if ( 'comments' == $options['order_by'] ) {
                $where .= " AND comment_date_gmt BETWEEN '{$dates[0]} 00:00:00' AND '{$dates[1]} 23:59:59' AND comment_approved = '1'";
            } else {
                $where .= " AND view_datetime BETWEEN '{$dates[0]} 00:00:00' AND '{$dates[1]} 23:59:59'";
            }
        } else {
            if ( 'comments' == $options['order_by'] ) {
                $where .= " AND comment_date_gmt > DATE_SUB('{$now}', INTERVAL {$interval})";
            } else {
                $where .= " AND view_datetime > DATE_SUB('{$now}', INTERVAL {$interval})";
            }
        }

        // Get entries published within the specified time range
        if ( isset( $options['freshness'] ) && $options['freshness'] ) {

            if ( $dates ) {
                $where .= " AND p.post_date BETWEEN '{$dates[0]} 00:00:00' AND '{$dates[1]} 23:59:59'";
            }
            else {
                $where .= " AND p.post_date > DATE_SUB('{$now}', INTERVAL {$interval})";
            }

        }

        return $where . " AND p.post_password = '' AND p.post_status = 'publish' ";

    }

    public function chart_query_group_by( $groupby, $options ){
        if ( 'comments' == $options['order_by'] )
            return "GROUP BY comments_date";
        return "GROUP BY view_date";
    }

    public function chart_query_order_by( $orderby, $options ){
        if ( 'comments' == $options['order_by'] )
            return "ORDER BY comments_date ASC";
        return "ORDER BY view_date ASC";
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

                // Check if custom date range has been requested
                $dates = null;

                if ( isset( $_GET['dates']) ) {

                    $dates = explode( " ~ ", $_GET['dates'] );

                    if (
                        !is_array( $dates )
                        || empty( $dates )
                        || !WPP_Helper::is_valid_date( $dates[0] )
                    )
                    {
                        $dates = null;
                    }
                    else {
                        if (
                            !isset( $dates[1] )
                            || !WPP_Helper::is_valid_date( $dates[1] )
                        ) {
                            $dates[1] = $dates[0];
                        }

                        $end_date = $dates[1];
                        $start_date = $dates[0];
                    }

                }

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
        add_filter( 'wpp_query_table', array( $this, 'chart_query_table' ), 1, 2 );
        add_filter( 'wpp_query_join', array( $this, 'chart_query_join' ), 1, 2 );
        add_filter( 'wpp_query_where', array( $this, 'chart_query_where' ), 1, 2 );
        add_filter( 'wpp_query_group_by', array( $this, 'chart_query_group_by' ), 1, 2 );
        add_filter( 'wpp_query_order_by', array( $this, 'chart_query_order_by' ), 1, 2 );
        add_filter( 'wpp_query_limit', array( $this, 'chart_query_limit' ), 1, 2 );

        $most_viewed = new WPP_query( array(
            'post_type' => $this->options['stats']['post_type'],
            'range' => $this->options['stats']['range'],
            'time_unit' => $this->options['stats']['time_unit'],
            'time_quantity' => $this->options['stats']['time_quantity'],
            'order_by' => 'views'
        ) );
        $views_data = $most_viewed->get_posts();

        remove_filter( 'wpp_query_fields', array( $this, 'chart_query_fields' ), 1 );
        remove_filter( 'wpp_query_table', array( $this, 'chart_query_table' ), 1 );
        remove_filter( 'wpp_query_join', array( $this, 'chart_query_join' ), 1 );
        remove_filter( 'wpp_query_where', array( $this, 'chart_query_where' ), 1 );
        remove_filter( 'wpp_query_group_by', array( $this, 'chart_query_group_by' ), 1 );
        remove_filter( 'wpp_query_order_by', array( $this, 'chart_query_order_by' ), 1 );
        remove_filter( 'wpp_query_limit', array( $this, 'chart_query_limit' ), 1 );

        add_filter( 'wpp_query_fields', array( $this, 'chart_query_fields' ), 1, 2 );
        add_filter( 'wpp_query_table', array( $this, 'chart_query_table' ), 1, 2 );
        add_filter( 'wpp_query_join', array( $this, 'chart_query_join' ), 1, 2 );
        add_filter( 'wpp_query_where', array( $this, 'chart_query_where' ), 1, 2 );
        add_filter( 'wpp_query_group_by', array( $this, 'chart_query_group_by' ), 1, 2 );
        add_filter( 'wpp_query_order_by', array( $this, 'chart_query_order_by' ), 1, 2 );
        add_filter( 'wpp_query_limit', array( $this, 'chart_query_limit' ), 1, 2 );

        $most_commented = new WPP_query( array(
            'post_type' => $this->options['stats']['post_type'],
            'range' => $this->options['stats']['range'],
            'time_unit' => $this->options['stats']['time_unit'],
            'time_quantity' => $this->options['stats']['time_quantity'],
            'order_by' => 'comments'
        ) );
        $comments_data = $most_commented->get_posts();

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
                    if ( isset( $data['dates'][$views->view_date] ) ) {
                        $data['dates'][$views->view_date]['views'] = $views->pageviews;
                        $data['totals']['views'] += $views->pageviews;
                    }
                }
            }

            if ( ( is_array($comments_data) && !empty($comments_data) ) ) {
                foreach( $comments_data as $comments ) {
                    if ( isset( $data['dates'][$comments->comments_date] ) ) {
                        $data['dates'][$comments->comments_date]['comments'] = $comments->comment_count;
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

                var wpp_chart_views_color = '<?php echo $color_scheme[2]; ?>';
                var wpp_chart_comments_color = '<?php echo $color_scheme[3]; ?>';

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

            $valid_ranges = array( 'today', 'daily', 'last24hours', 'weekly', 'last7days', 'monthly', 'last30days', 'all', 'custom' );
            $time_units = array( "MINUTE", "HOUR", "DAY" );

            $range = ( isset( $_GET['range'] ) && in_array( $_GET['range'], $valid_ranges ) ) ? $_GET['range'] : 'last7days';
            $time_quantity = ( isset( $_GET['time_quantity'] ) && filter_var( $_GET['time_quantity'], FILTER_VALIDATE_INT ) ) ? $_GET['time_quantity'] : 24;
            $time_unit = ( isset( $_GET['time_unit'] ) && in_array( strtoupper( $_GET['time_unit'] ), $time_units ) ) ? $_GET['time_unit'] : 'hour';

            $admin_options = WPP_Settings::get( 'admin_options' );
            $admin_options['stats']['range'] = $range;
            $admin_options['stats']['time_quantity'] = $time_quantity;
            $admin_options['stats']['time_unit'] = $time_unit;
            $this->options = $admin_options;

            update_option( 'wpp_settings_config', $this->options );

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

        add_filter('wpp_query_join', function($join, $options){

            global $wpdb;
            $dates = null;

            if ( isset( $_GET['dates']) ) {

                $dates = explode( " ~ ", $_GET['dates'] );

                if (
                    !is_array( $dates )
                    || empty( $dates )
                    || !WPP_Helper::is_valid_date( $dates[0] )
                ) {
                    $dates = null;
                } else {
                    if (
                        !isset( $dates[1] )
                        || !WPP_Helper::is_valid_date( $dates[1] )
                    ) {
                        $dates[1] = $dates[0];
                    }

                    $end_date = $dates[1];
                    $start_date = $dates[0];
                }

            }

            if ( $dates ) {
                return "INNER JOIN (SELECT SUM(pageviews) AS pageviews, view_date, postid FROM `{$wpdb->prefix}popularpostssummary` WHERE view_datetime BETWEEN '{$dates[0]} 00:00:00' AND '{$dates[1]} 23:59:59' GROUP BY postid) v ON p.ID = v.postid";
            }

            $now = WPP_Helper::now();

            // Determine time range
            switch( $options['range'] ){
                case "last24hours":
                case "daily":
                    $interval = "24 HOUR";
                    break;

                case "today":
                    $hours = date( 'H', strtotime($now) );
                    $minutes = $hours * 60 + (int) date( 'i', strtotime($now) );
                    $interval = "{$minutes} MINUTE";
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
                        isset( $options['time_unit'] )
                        && in_array( strtoupper( $options['time_unit'] ), $time_units )
                        && isset( $options['time_quantity'] )
                        && filter_var( $options['time_quantity'], FILTER_VALIDATE_INT )
                        && $options['time_quantity'] > 0
                    ) {
                        $interval = "{$options['time_quantity']} " . strtoupper( $options['time_unit'] );
                    }

                    break;

                default:
                    $interval = "1 DAY";
                    break;
            }

            return "INNER JOIN (SELECT SUM(pageviews) AS pageviews, view_date, postid FROM `{$wpdb->prefix}popularpostssummary` WHERE view_datetime > DATE_SUB('{$now}', INTERVAL {$interval}) GROUP BY postid) v ON p.ID = v.postid";

        }, 1, 2);
        $most_viewed = new WPP_query( $args );
        $posts = $most_viewed->get_posts();
        remove_all_filters('wpp_query_join', 1);

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
                    <a href="<?php echo get_permalink( $post->id ); ?>"><?php echo sanitize_text_field( $post->title ); ?></a>
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
        <p style="text-align: center;"><?php _e("Looks like traffic to your site is a little light right now. <br />Spread the word and come back later!", "wordpress-popular-posts"); ?></p>
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

        add_filter('wpp_query_join', function($join, $options){

            global $wpdb;
            $dates = null;

            if ( isset( $_GET['dates']) ) {

                $dates = explode( " ~ ", $_GET['dates'] );

                if (
                    !is_array( $dates )
                    || empty( $dates )
                    || !WPP_Helper::is_valid_date( $dates[0] )
                ) {
                    $dates = null;
                } else {
                    if (
                        !isset( $dates[1] )
                        || !WPP_Helper::is_valid_date( $dates[1] )
                    ) {
                        $dates[1] = $dates[0];
                    }

                    $end_date = $dates[1];
                    $start_date = $dates[0];
                }

            }

            if ( $dates ) {
                return "INNER JOIN (SELECT comment_post_ID, COUNT(comment_post_ID) AS comment_count, comment_date_gmt FROM `{$wpdb->comments}` WHERE comment_date_gmt BETWEEN '{$dates[0]} 00:00:00' AND '{$dates[1]} 23:59:59' AND comment_approved = '1' GROUP BY comment_post_ID) c ON p.ID = c.comment_post_ID";
            }

            $now = WPP_Helper::now();

            // Determine time range
            switch( $options['range'] ){
                case "last24hours":
                case "daily":
                    $interval = "24 HOUR";
                    break;

                case "today":
                    $hours = date( 'H', strtotime($now) );
                    $minutes = $hours * 60 + (int) date( 'i', strtotime($now) );
                    $interval = "{$minutes} MINUTE";
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
                        isset( $options['time_unit'] )
                        && in_array( strtoupper( $options['time_unit'] ), $time_units )
                        && isset( $options['time_quantity'] )
                        && filter_var( $options['time_quantity'], FILTER_VALIDATE_INT )
                        && $options['time_quantity'] > 0
                    ) {
                        $interval = "{$options['time_quantity']} " . strtoupper( $options['time_unit'] );
                    }

                    break;

                default:
                    $interval = "1 DAY";
                    break;
            }

            return "INNER JOIN (SELECT comment_post_ID, COUNT(comment_post_ID) AS comment_count, comment_date_gmt FROM `{$wpdb->comments}` WHERE comment_date_gmt > DATE_SUB('{$now}', INTERVAL {$interval}) AND comment_approved = '1' GROUP BY comment_post_ID) c ON p.ID = c.comment_post_ID";

        }, 1, 2);
        $most_commented = new WPP_query( $args );
        $posts = $most_commented->get_posts();
        remove_all_filters('wpp_query_join', 1);

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
                    <a href="<?php echo get_permalink( $post->id ); ?>"><?php echo sanitize_text_field( $post->title ); ?></a>
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
        <p style="text-align: center;"><?php _e("Looks like traffic to your site is a little light right now. <br />Spread the word and come back later!", "wordpress-popular-posts"); ?></p>
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

            if ( isset($_wp_admin_css_colors[ $color_scheme ]) && isset($_wp_admin_css_colors[ $color_scheme ]->colors) ) {
                return $_wp_admin_css_colors[ $color_scheme ]->colors;
            }

        }

        // Fallback, just in case
        return array( '#333', '#999', '#881111', '#a80000' );

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
                $key = get_option( "wpp_rand" );

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
        $key = get_option( "wpp_rand" );

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

        $wpp_transients = get_option( 'wpp_transients' );

        if ( $wpp_transients && is_array( $wpp_transients ) && !empty( $wpp_transients ) ) {

            for ( $t=0; $t < count( $wpp_transients ); $t++ )
                delete_transient( $wpp_transients[$t] );

            update_option( 'wpp_transients', array() );

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
        $this->upgrade_site();
    } // end upgrade_check

    /**
     * Upgrades single site.
     *
     * @since 4.0.7
     */
    private function upgrade_site() {
        // Get WPP version
        $wpp_ver = get_option( 'wpp_ver' );

        if ( !$wpp_ver ) {
            add_option( 'wpp_ver', $this->version );
        } elseif ( version_compare( $wpp_ver, $this->version, '<' ) ) {
            $this->upgrade();
        }
    }

    /**
     * On plugin upgrade, performs a number of actions: update WPP database tables structures (if needed),
     * run the setup wizard (if needed), and some other checks.
     *
     * @since	2.4.0
     * @access  private
     * @global	object	wpdb
     */
    private function upgrade() {

        $now = WPP_Helper::now();

        // Keep the upgrade process from running too many times
        if ( $wpp_update = get_option('wpp_update') ) {

            $from_time = strtotime( $wpp_update );
            $to_time = strtotime( $now );
            $difference_in_minutes = round( abs( $to_time - $from_time ) / 60, 2 );

            // Upgrade flag is still valid, abort
            if ( $difference_in_minutes <= 15 )
                return;

            // Upgrade flag expired, delete it and continue
            delete_option( 'wpp_update' );

        }

        add_option( 'wpp_update', $now );

        global $wpdb;

        // Set table name
        $prefix = $wpdb->prefix . "popularposts";

        // Update data table structure and indexes
        $dataFields = $wpdb->get_results( "SHOW FIELDS FROM {$prefix}data;" );
        foreach ( $dataFields as $column ) {
            if ( "day" == $column->Field ) {
                $wpdb->query( "ALTER TABLE {$prefix}data ALTER COLUMN day DROP DEFAULT;" );
            }

            if ( "last_viewed" == $column->Field ) {
                $wpdb->query( "ALTER TABLE {$prefix}data ALTER COLUMN last_viewed DROP DEFAULT;" );
            }
        }

        // Update summary table structure and indexes
        $summaryFields = $wpdb->get_results( "SHOW FIELDS FROM {$prefix}summary;" );
        foreach ( $summaryFields as $column ) {
            if ( "last_viewed" == $column->Field ) {
                $wpdb->query( "ALTER TABLE {$prefix}summary CHANGE last_viewed view_datetime datetime NOT NULL, ADD KEY view_datetime (view_datetime);" );
            }

            if ( "view_date" == $column->Field ) {
                $wpdb->query( "ALTER TABLE {$prefix}summary ALTER COLUMN view_date DROP DEFAULT;" );
            }

            if ( "view_datetime" == $column->Field ) {
                $wpdb->query( "ALTER TABLE {$prefix}summary ALTER COLUMN view_datetime DROP DEFAULT;" );
            }
        }

        $summaryIndexes = $wpdb->get_results( "SHOW INDEX FROM {$prefix}summary;" );
        foreach( $summaryIndexes as $index ) {
            if ( 'ID_date' == $index->Key_name ) {
                $wpdb->query( "ALTER TABLE {$prefix}summary DROP INDEX ID_date;" );
            }

            if ( 'last_viewed' == $index->Key_name ) {
                $wpdb->query( "ALTER TABLE {$prefix}summary DROP INDEX last_viewed;" );
            }
        }

        // Validate the structure of the tables, create missing tables / fields if necessary
        WPP_Activator::track_new_site();

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
        update_option( 'wpp_ver', $this->version );

        // Remove upgrade flag
        delete_option( 'wpp_update' );

    } // end __upgrade

} // End WPP_Admin class
