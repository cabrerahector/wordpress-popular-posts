<?php
/**
 * Queries the database for popular posts.
 *
 * To use this class, you must pass it an array of parameters (mostly the same ones used with
 * the wpp_get_mostpopular() template tag). The very minimum required parameters are 'range', 'order_by'
 * and 'limit'.
 *
 * eg.: $popular_posts = new WPP_Query( array('range' => 'last7days', 'order_by' => 'views', 'limit' => 5) );
 *
 * @since             4.0.0
 * @package           WordPressPopularPosts
 * @subpackage        WordPressPopularPosts/includes
 */

class WPP_Query {

    /*
     * Database query string.
     *
     * @since    4.0.0
     * @access   private
     * @var      string      $query
     */
    private $query;

    /*
     * List of posts.
     *
     * @since    4.0.0
     * @access   private
     * @var      array      $posts
     */
    private $posts = array();

    /*
     * Plugin options.
     *
     * @since    4.0.0
     * @access   private
     * @var      array      $options
     */
    private $options;

    /*
     * Constructor.
     *
     * @since    4.0.0
     * @param    array      $options
     */
    public function __construct( array $options = array() ){
        $this->options = $options;
        $this->build_query();
        $this->run_query();
    }

    /*
     * Builds the database query.
     *
     * @since    4.0.0
     * @access   private
     */
    private function build_query(){

        /*
         * @var wpdb $wpdb
         */
        global $wpdb;

        if ( isset($wpdb) ) {

            $this->options = WPP_Helper::merge_array_r(
                WPP_Settings::$defaults[ 'widget_options' ],
                (array) $this->options
            );

            $args = array();
            $fields = "p.ID AS id, p.post_title AS title, p.post_author AS uid";
            $table = "";
            $join = "";
            $where = "WHERE 1 = 1";
            $groupby = "";
            $orderby = "";
            $limit = "LIMIT " . ( filter_var($this->options['limit'], FILTER_VALIDATE_INT) && $this->options['limit'] > 0 ? $this->options['limit'] : 10 ) . ( isset($this->options['offset']) && filter_var($this->options['offset'], FILTER_VALIDATE_INT) !== false && $this->options['offset'] >= 0 ? " OFFSET {$this->options['offset']}" : "" );

            // Get post date
            if ( isset($this->options['stats_tag']['date']['active']) && $this->options['stats_tag']['date']['active'] ) {
                $fields .= ", p.post_date AS date";
            }

            // Get post excerpt $instance
            if ( isset($this->options['post-excerpt']['active']) && $this->options['post-excerpt']['active'] ) {
                $fields .= ", p.post_excerpt AS post_excerpt, p.post_content AS post_content";
            }

            // Get entries from these post types
            $post_types = array( 'post', 'page' );

            if ( isset($this->options['post_type']) && !empty($this->options['post_type']) ) {

                $post_types = explode( ",", $this->options['post_type'] );
                $pt = '';
                $where .= " AND p.post_type IN(";

                foreach( $post_types as $post_type ) {
                    $pt .= "%s, ";
                    array_push( $args, trim($post_type) );
                }

                $where .= rtrim($pt, ", ") . ")";

            }
            else {
                $where .= " AND p.post_type IN('post', 'page')";
            }

            // Get entries from these authors
            if ( isset($this->options['author']) && !empty($this->options['author']) ) {

                $author_IDs = explode( ",", $this->options['author'] );
                $uid = '';
                $where .= " AND p.post_author IN(";

                foreach( $author_IDs as $author_ID ) {
                    $uid .= "%d, ";
                    array_push( $args, trim($author_ID) );
                }

                $where .= rtrim($uid, ", ") . ")";

            }

            // Get / exclude entries from this taxonomy
            if (
                ( isset($this->options['cat']) && !empty($this->options['cat']) )
                || ( isset($this->options['term_id']) && !empty($this->options['term_id']) )
            ) {

                if ( isset($this->options['taxonomy']) && !empty($this->options['taxonomy']) ) {

                    $registered_taxonomies = get_taxonomies( array('public' => true) );

                    // Invalid taxonomy, fallback to "category"
                    if ( !isset($registered_taxonomies[$this->options['taxonomy']]) ) {
                        $this->options['taxonomy'] = 'category';
                    }

                } // Default to "category"
                else {
                    $this->options['taxonomy'] = 'category';
                }

                if ( isset($this->options['cat']) && !empty($this->options['cat']) ) {
                    $this->options['term_id'] = $this->options['cat'];
                }

                $term_IDs = explode( ",", $this->options['term_id'] );
                $in_term_IDs = array();
                $out_term_IDs = array();

                foreach( $term_IDs as $term_ID ) {

                    if ( $term_ID >= 0 )
                        $in_term_IDs[] = trim( $term_ID );
                    else
                        $out_term_IDs[] = trim( $term_ID ) * -1;

                }

                if ( !empty($in_term_IDs) ) {

                    $where .= " AND p.ID IN (
                    SELECT object_id
                    FROM `{$wpdb->term_relationships}` AS r
                         JOIN `{$wpdb->term_taxonomy}` AS x ON x.term_taxonomy_id = r.term_taxonomy_id
                    WHERE x.taxonomy = '{$this->options['taxonomy']}'";

                    $inTID = '';

                    foreach( $in_term_IDs as $in_term_ID ) {
                        $inTID .= "%d, ";
                        array_push( $args, $in_term_ID );
                    }

                    $where .= " AND x.term_id IN(" . rtrim($inTID, ", ") . ") )";

                }

                if ( !empty($out_term_IDs) ) {

                    $post_ids = get_posts(
                        array(
                            'post_type' => $post_types,
                            'posts_per_page' => -1,
                            'tax_query' => array(
                                array(
                                    'taxonomy' => $this->options['taxonomy'],
                                    'field' => 'id',
                                    'terms' => $out_term_IDs,
                                ),
                            ),
                            'fields' => 'ids'
                        )
                    );

                    if ( is_array($post_ids) && !empty($post_ids) ) {

                        if ( isset($this->options['pid']) && !empty($this->options['pid']) ) {
                            $this->options['pid'] .= "," . implode( ",", $post_ids );
                        }
                        else {
                            $this->options['pid'] = implode( ",", $post_ids );
                        }

                    }

                }

            }

            // Exclude these entries from the listing
            if ( isset($this->options['pid']) && !empty($this->options['pid']) ) {

                $excluded_post_IDs = explode( ",", $this->options['pid'] );
                $xpid = '';
                $where .= " AND p.ID NOT IN(";

                foreach( $excluded_post_IDs as $excluded_post_ID ) {
                    $xpid .= "%d, ";
                    array_push( $args, trim($excluded_post_ID) );
                }

                $where .= rtrim($xpid, ", ") . ")";

            }

            // All-time range
            if ( "all" == $this->options['range'] ) {

                // Order by views count
                if ( "comments" != $this->options['order_by'] ) {

                    $table = "`{$wpdb->prefix}popularpostsdata` v";
                    $join = "LEFT JOIN `{$wpdb->posts}` p ON v.postid = p.ID";

                    // Order by views
                    if ( "views" == $this->options['order_by'] ) {

                        if ( !isset($this->options['stats_tag']['views']) || $this->options['stats_tag']['views'] ) {
                            $fields .= ", v.pageviews";
                        }

                        $orderby = "ORDER BY v.pageviews DESC";

                    }
                    // Order by average views
                    else {

                        $now = current_time( 'mysql' );

                        $fields .= ", ( v.pageviews/(IF ( DATEDIFF('{$now}', MIN(v.day)) > 0, DATEDIFF('{$now}', MIN(v.day)), 1) ) ) AS avg_views";
                        $groupby = "GROUP BY v.postid";
                        $orderby = "ORDER BY avg_views DESC";

                    }

                    // Display comments count, too
                    if ( isset($this->options['stats_tag']['comment_count']) && $this->options['stats_tag']['comment_count'] ) {
                        $fields .= ", p.comment_count";
                    }

                }
                // Order by comments count
                else {

                    $table = "`{$wpdb->posts}` p";
                    $where .= " AND p.comment_count > 0";
                    $orderby = "ORDER BY p.comment_count DESC";

                    // Display comment count
                    if ( isset($this->options['stats_tag']['comment_count']) && $this->options['stats_tag']['comment_count'] ) {
                        $fields .= ", p.comment_count";
                    }

                    // Display views count, too
                    if ( isset($this->options['stats_tag']['views']) && $this->options['stats_tag']['views'] ) {
                        $fields .= ", IFNULL(v.pageviews, 0) AS pageviews";
                        $join = "LEFT JOIN `{$wpdb->prefix}popularpostsdata` v ON p.ID = v.postid";
                    }

                }

            }
            // Custom time range
            else {

                $now = current_time( 'mysql' );

                // Determine time range
                switch( $this->options['range'] ){
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
                        $time_units = array( "MINUTE", "HOUR", "DAY", "WEEK", "MONTH" );
                        $interval = "24 HOUR";

                        // Valid time unit
                        if (
                            isset( $this->options['time_unit'] )
                            && in_array( strtoupper( $this->options['time_unit'] ), $time_units )
                            && isset( $this->options['time_quantity'] )
                            && filter_var( $this->options['time_quantity'], FILTER_VALIDATE_INT )
                            && $this->options['time_quantity'] > 0
                        ) {
                            $interval = "{$this->options['time_quantity']} " . strtoupper( $this->options['time_unit'] );
                        }

                        break;

                    default:
                        $interval = "24 HOUR";
                        break;
                }

                // Get entries published within the specified time range
                if ( isset($this->options['freshness']) && $this->options['freshness'] ) {
                    $where .= " AND p.post_date > DATE_SUB('{$now}', INTERVAL {$interval})";
                }

                // Order by views count
                if ( "comments" != $this->options['order_by'] ) {

                    $table = "`{$wpdb->prefix}popularpostssummary` v";
                    $join = "LEFT JOIN `{$wpdb->posts}` p ON v.postid = p.ID";
                    $where .= " AND v.view_datetime > DATE_SUB('{$now}', INTERVAL {$interval})";
                    $groupby = "GROUP BY v.postid";

                    // Order by views
                    if ( "views" == $this->options['order_by'] ) {

                        if ( !isset($this->options['stats_tag']['views']) || $this->options['stats_tag']['views'] ) {
                            $fields .= ", SUM(v.pageviews) AS pageviews";
                            $orderby = "ORDER BY pageviews DESC";
                        }
                        else {
                            $orderby = "ORDER BY SUM(v.pageviews) DESC";
                        }

                    }
                    // Order by average views
                    else {
                        $fields .= ", ( SUM(v.pageviews)/(IF ( DATEDIFF('{$now}', DATE_SUB('{$now}', INTERVAL {$interval})) > 0, DATEDIFF('{$now}', DATE_SUB('{$now}', INTERVAL {$interval})), 1) ) ) AS avg_views";
                        $orderby = "ORDER BY avg_views DESC";
                    }

                    // Display comments count, too
                    if ( isset($this->options['stats_tag']['comment_count']) && $this->options['stats_tag']['comment_count'] ) {
                        $fields .= ", IFNULL(c.comment_count, 0) AS comment_count";
                        $join .= " LEFT JOIN (SELECT comment_post_ID, COUNT(comment_post_ID) AS comment_count FROM `{$wpdb->comments}` WHERE comment_date_gmt > DATE_SUB('{$now}', INTERVAL {$interval}) AND comment_approved = 1 GROUP BY comment_post_ID) c ON p.ID = c.comment_post_ID";
                    }

                }
                // Order by comments count
                else {

                    $table = "`{$wpdb->comments}` c";
                    $join = "LEFT JOIN {$wpdb->posts} p ON c.comment_post_ID = p.ID";
                    $where .= " AND c.comment_date_gmt > DATE_SUB('{$now}', INTERVAL {$interval}) AND c.comment_approved = 1";
                    $groupby = "GROUP BY c.comment_post_ID";

                    // Display comment count
                    if ( isset($this->options['stats_tag']['comment_count']) && $this->options['stats_tag']['comment_count'] ) {
                        $fields .= ", COUNT(c.comment_post_ID) AS comment_count";
                        $orderby = "ORDER BY comment_count DESC";
                    }
                    else {
                        $orderby = "ORDER BY COUNT(c.comment_post_ID) DESC";
                    }

                    // Display views count, too
                    if ( isset($this->options['stats_tag']['views']) && $this->options['stats_tag']['views'] ) {
                        $fields .= ", IFNULL(v.pageviews, 0) AS pageviews";
                        $join .= " LEFT JOIN (SELECT postid, SUM(pageviews) AS pageviews FROM `{$wpdb->prefix}popularpostssummary` WHERE view_datetime > DATE_SUB('{$now}', INTERVAL {$interval}) GROUP BY postid) v ON p.ID = v.postid";
                    }

                }

            }

            // List only published, non password-protected posts
            $where .= " AND p.post_password = '' AND p.post_status = 'publish'";

            if ( !empty($args) ) {
                $where = $wpdb->prepare( $where, $args );
            }

            $fields = apply_filters( 'wpp_query_fields', $fields, $this->options );
            $table = apply_filters( 'wpp_query_table', $table, $this->options );
            $join = apply_filters( 'wpp_query_join', $join, $this->options );
            $where = apply_filters( 'wpp_query_where', $where, $this->options, $args );
            $groupby = apply_filters( 'wpp_query_group_by', $groupby, $this->options );
            $orderby = apply_filters( 'wpp_query_order_by', $orderby, $this->options );
            $limit = apply_filters( 'wpp_query_limit', $limit, $this->options );

            // Finally, build the query
            $query = "SELECT {$fields} FROM {$table} {$join} {$where} {$groupby} {$orderby} {$limit};";
            //$this->query = ( !empty($args) && !has_filter('wpp_query_where') ) ? $wpdb->prepare( $query, $args ) : $query;
            $this->query = $query;

        }

    }

    /*
     * Queries the database.
     *
     * @since    4.0.0
     * @access   private
     */
    private function run_query(){

        /*
         * @var wpdb $wpdb
         */
        global $wpdb;

        if ( isset($wpdb) && !empty($this->query) && !is_wp_error($this->query) ) {
            $this->posts = $wpdb->get_results( $this->query );
        }

    }

    /*
     * Returns the query string.
     *
     * @since    4.0.0
     * @return   WP_Error|string   Query string on success, WP_Error on failure
     */
    public function get_query(){
        return $this->query;
    }

    /*
     * Returns the list of posts.
     *
     * @since    4.0.0
     * @return   array
     */
    public function get_posts(){
        return $this->posts;
    }

} // end WPP_Query class
