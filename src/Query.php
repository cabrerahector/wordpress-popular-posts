<?php
/**
 * Queries the database for popular posts.
 *
 * To use this class, you must pass it an array of parameters (mostly the same ones used with
 * the wpp_get_mostpopular() template tag).
 *
 * eg.: $popular_posts = new Query(['range' => 'last7days', 'order_by' => 'views', 'limit' => 5]);
 *
 * @since             4.0.0
 * @package           WordPressPopularPosts
 */

namespace WordPressPopularPosts;

class Query {
    /**
     * Database query string.
     *
     * @since    4.0.0
     * @access   private
     * @var      string      $query
     */
    private $query;

    /**
     * List of posts.
     *
     * @since    4.0.0
     * @access   private
     * @var      array      $posts
     */
    private $posts = [];

    /**
     * Plugin options.
     *
     * @since    4.0.0
     * @access   private
     * @var      array      $options
     */
    private $options;

    /**
     * Constructor.
     *
     * @since   4.0.0
     * @param   array   $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
        $this->build_query();
        $this->run_query();
    }

    /**
     * Sets class options.
     *
     * @since   5.0.0
     * @param   array   $options
     */
    public function set_options(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Builds the database query.
     *
     * @since    4.0.0
     * @access   private
     */
    private function build_query()
    {
        /**
         * @var wpdb $wpdb
         */
        global $wpdb;

        if ( isset($wpdb) ) {

            $this->options = Helper::merge_array_r(
                Settings::get('widget_options'),
                (array) $this->options
            );

            $now = new \DateTime(Helper::now(), new \DateTimeZone(Helper::get_timezone()));
            $args = [];
            $fields = "p.ID AS id, p.post_title AS title, p.post_author AS uid";
            $table = "";
            $join = "";
            $where = "WHERE 1 = 1";
            $groupby = "";
            $orderby = "";
            $limit = "LIMIT " . (filter_var($this->options['limit'], FILTER_VALIDATE_INT) && $this->options['limit'] > 0 ? $this->options['limit'] : 10) . (isset($this->options['offset']) && filter_var($this->options['offset'], FILTER_VALIDATE_INT) !== false && $this->options['offset'] >= 0 ? " OFFSET {$this->options['offset']}" : "");

            // Get post date
            if ( isset($this->options['stats_tag']['date']['active']) && $this->options['stats_tag']['date']['active'] ) {
                $fields .= ", p.post_date AS date";
            }

            // Get post excerpt $instance
            if ( isset($this->options['post-excerpt']['active']) && $this->options['post-excerpt']['active'] ) {
                $fields .= ", p.post_excerpt AS post_excerpt, p.post_content AS post_content";
            }

            // Get entries from these post types
            $post_types = ['post', 'page'];

            if ( isset($this->options['post_type']) && ! empty($this->options['post_type']) ) {

                $post_types = explode(",", $this->options['post_type']);
                $pt = '';
                $where .= " AND p.post_type IN(";

                foreach( $post_types as $post_type ) {
                    $pt .= "%s, ";
                    array_push($args, trim($post_type));
                }

                $where .= rtrim($pt, ", ") . ")";

            }
            else {
                $where .= " AND p.post_type IN('post', 'page')";
            }

            // Get entries from these authors
            if ( isset($this->options['author']) && ! empty($this->options['author']) ) {

                $author_IDs = explode(",", $this->options['author']);
                $uid = '';
                $where .= " AND p.post_author IN(";

                foreach( $author_IDs as $author_ID ) {
                    $uid .= "%d, ";
                    array_push($args, trim($author_ID));
                }

                $where .= rtrim($uid, ", ") . ")";

            }

            // Get / exclude entries from this taxonomies
            if (
                ( isset($this->options['taxonomy']) && ! empty($this->options['taxonomy']) ) &&
                ( ( isset($this->options['cat']) && ! empty($this->options['cat']) )
                || ( isset($this->options['term_id']) && ! empty($this->options['term_id']) ) )
            ) {

                if ( isset($this->options['cat']) && ! empty($this->options['cat']) ) {
                    $this->options['term_id'] = $this->options['cat'];
                }

                // Let's do some cleanup before attempting the filtering
                $this->options['taxonomy'] = trim($this->options['taxonomy'], ' ;');
                $this->options['term_id'] = trim($this->options['term_id'], ' ;');

                if (
                    $this->options['taxonomy'] 
                    && $this->options['term_id']
                ) {

                    $taxonomies = array_map('trim', explode(';', $this->options['taxonomy']));
                    $term_IDs_for_taxonomies = array_map('trim', explode(';', $this->options['term_id']));

                    // Apparently we have at least one taxonomy and matching term ID(s)
                    if ( count($taxonomies) && count($term_IDs_for_taxonomies) ) {

                        // Parameters mismatch: we either have too little taxonomies
                        // or too little term ID groups, let's trim the excess
                        if ( count($taxonomies) != count($term_IDs_for_taxonomies) ) {
                            // We have more taxonomies than term ID groups,
                            // let's remove some taxonomies
                            if ( count($taxonomies) > count($term_IDs_for_taxonomies) ) {
                                $taxonomies = array_slice($taxonomies, 0, count($term_IDs_for_taxonomies));
                            }
                            // We have more term ID groups than taxonomies,
                            // let's remove some term ID groups
                            else {
                                $term_IDs_for_taxonomies = array_slice($term_IDs_for_taxonomies, 0, count($taxonomies));
                            }
                        }

                        $registered_taxonomies = get_taxonomies(['public' => true]);

                        foreach ( $taxonomies as $index => $taxonomy ) {
                            // Invalid taxonomy, discard the taxonomy
                            if ( ! isset($registered_taxonomies[$taxonomy]) ) {
                                unset($taxonomies[$index]);
                                unset($term_IDs_for_taxonomies[$index]);
                            }
                        }

                        // If we still have something we can use 
                        // for filtering, let's use it
                        if (
                            ! empty($taxonomies)
                            && ! empty($term_IDs_for_taxonomies)
                        ) {

                            $term_IDs = array();
                            foreach( $term_IDs_for_taxonomies as $term_IDs_for_single_taxonomy ) {
                                $term_IDs[] = explode(",", $term_IDs_for_single_taxonomy);
                            }
                            $in_term_IDs_for_taxonomies = array();
                            $out_term_IDs_for_taxonomies = array();

                            foreach( $term_IDs as $term_IDs_for_single_taxonomy ) {
                                $in_term_IDs_for_taxonomy = [];
                                $out_term_IDs_for_taxonomy = [];

                                foreach ( $term_IDs_for_single_taxonomy as $term_ID ) {
                                    if ( $term_ID >= 0 )
                                        $in_term_IDs_for_taxonomy[] = trim($term_ID);
                                    else
                                        $out_term_IDs_for_taxonomy[] = trim($term_ID) * -1;
                                }

                                $in_term_IDs_for_taxonomies[] = $in_term_IDs_for_taxonomy;
                                $out_term_IDs_for_taxonomies[] = $out_term_IDs_for_taxonomy;
                            }

                            foreach( $taxonomies as $taxIndex => $taxonomy ) {
                                $in_term_IDs = $in_term_IDs_for_taxonomies[$taxIndex];
                                $out_term_IDs = $out_term_IDs_for_taxonomies[$taxIndex];

                                if ( ! empty($in_term_IDs) ) {
                                    $where .= " AND p.ID IN (
                                        SELECT object_id
                                        FROM `{$wpdb->term_relationships}` AS r
                                            JOIN `{$wpdb->term_taxonomy}` AS x ON x.term_taxonomy_id = r.term_taxonomy_id
                                        WHERE x.taxonomy = %s";

                                    array_push($args, $taxonomy);

                                    $inTID = '';

                                    foreach ($in_term_IDs as $in_term_ID) {
                                        $inTID .= "%d, ";
                                        array_push($args, $in_term_ID);
                                    }

                                    $where .= " AND x.term_id IN(" . rtrim($inTID, ", ") . ") )";
                                }

                                if ( ! empty($out_term_IDs) ) {

                                    $post_ids = get_posts(
                                        [
                                            'post_type' => $post_types,
                                            'posts_per_page' => -1,
                                            'tax_query' => [
                                                [
                                                    'taxonomy' => $taxonomy,
                                                    'field' => 'id',
                                                    'terms' => $out_term_IDs,
                                                ],
                                            ],
                                            'fields' => 'ids'
                                        ]
                                    );

                                    if ( is_array($post_ids) && ! empty($post_ids) ) {
                                        if ( isset($this->options['pid']) && ! empty($this->options['pid']) ) {
                                            $this->options['pid'] .= "," . implode(",", $post_ids);
                                        } else {
                                            $this->options['pid'] = implode(",", $post_ids);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Exclude these entries from the listing
            if ( isset($this->options['pid']) && ! empty($this->options['pid']) ) {
                $excluded_post_IDs = explode(",", $this->options['pid']);
                $xpid = '';
                $where .= " AND p.ID NOT IN(";

                foreach( $excluded_post_IDs as $excluded_post_ID ) {
                    $xpid .= "%d, ";
                    array_push($args, trim($excluded_post_ID));
                }

                $where .= rtrim($xpid, ", ") . ")";
            }

            $table = "`{$wpdb->posts}` p";

            // All-time range
            if ( "all" == $this->options['range'] ) {

                // Order by views count
                if ( "comments" != $this->options['order_by'] ) {

                    $join = "INNER JOIN `{$wpdb->prefix}popularpostsdata` v ON p.ID = v.postid";

                    // Order by views
                    if ( "views" == $this->options['order_by'] ) {
                        if ( ! isset($this->options['stats_tag']['views']) || $this->options['stats_tag']['views'] ) {
                            $fields .= ", v.pageviews";
                        }
                        $orderby = "ORDER BY v.pageviews DESC";
                    }
                    // Order by average views
                    else {
                        $fields .= ", ( v.pageviews/(IF ( DATEDIFF('{$now->format('Y-m-d')}', MIN(v.day)) > 0, DATEDIFF('{$now->format('Y-m-d')}', MIN(v.day)), 1) ) ) AS avg_views";
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
                    $where .= " AND p.comment_count > 0";
                    $orderby = "ORDER BY p.comment_count DESC";

                    // Display comment count
                    if ( isset($this->options['stats_tag']['comment_count']) && $this->options['stats_tag']['comment_count'] ) {
                        $fields .= ", p.comment_count";
                    }

                    // Display views count, too
                    if ( isset($this->options['stats_tag']['views']) && $this->options['stats_tag']['views'] ) {
                        $fields .= ", IFNULL(v.pageviews, 0) AS pageviews";
                        $join = "INNER JOIN `{$wpdb->prefix}popularpostsdata` v ON p.ID = v.postid";
                    }
                }
            }
            // Custom time range
            else {
                $start_date = clone $now;

                // Determine time range
                switch( $this->options['range'] ){
                    case "last24hours":
                    case "daily":
                        $start_date = $start_date->sub(new \DateInterval('P1D'));
                        $start_datetime = $start_date->format('Y-m-d H:i:s');
                        $views_time_range = "view_datetime >= '{$start_datetime}'";
                        break;
                    case "last7days":
                    case "weekly":
                        $start_date = $start_date->sub(new \DateInterval('P6D'));
                        $start_datetime = $start_date->format('Y-m-d');
                        $views_time_range = "view_date >= '{$start_datetime}'";
                        break;
                    case "last30days":
                    case "monthly":
                        $start_date = $start_date->sub(new \DateInterval('P29D'));
                        $start_datetime = $start_date->format('Y-m-d');
                        $views_time_range = "view_date >= '{$start_datetime}'";
                        break;
                    case "custom":
                        $time_units = ["MINUTE", "HOUR", "DAY", "WEEK", "MONTH"];

                        // Valid time unit
                        if (
                            isset($this->options['time_unit'])
                            && in_array(strtoupper($this->options['time_unit']), $time_units)
                            && isset($this->options['time_quantity'])
                            && filter_var($this->options['time_quantity'], FILTER_VALIDATE_INT)
                            && $this->options['time_quantity'] > 0
                        ) {
                            $time_quantity = $this->options['time_quantity'];
                            $time_unit = strtoupper($this->options['time_unit']);

                            if ( 'MINUTE' == $time_unit ) {
                                $start_date = $start_date->sub(new \DateInterval('PT' . (60 * $time_quantity) . 'S'));
                                $start_datetime = $start_date->format('Y-m-d H:i:s');
                                $views_time_range = "view_datetime >= '{$start_datetime}'";
                            } elseif ( 'HOUR' == $time_unit ) {
                                $start_date = $start_date->sub(new \DateInterval('PT' . ((60 * $time_quantity) - 1) . 'M59S'));
                                $start_datetime = $start_date->format('Y-m-d H:i:s');
                                $views_time_range = "view_datetime >= '{$start_datetime}'";
                            } elseif ( 'DAY' == $time_unit ) {
                                $start_date = $start_date->sub(new \DateInterval('P' . ($time_quantity - 1) . 'D'));
                                $start_datetime = $start_date->format('Y-m-d');
                                $views_time_range = "view_date >= '{$start_datetime}'";
                            } elseif ( 'WEEK' == $time_unit ) {
                                $start_date = $start_date->sub(new \DateInterval('P' . ((7 * $time_quantity) - 1) . 'D'));
                                $start_datetime = $start_date->format('Y-m-d');
                                $views_time_range = "view_date >= '{$start_datetime}'";
                            } else {
                                $start_date = $start_date->sub(new \DateInterval('P' . ((30 * $time_quantity) - 1) . 'D'));
                                $start_datetime = $start_date->format('Y-m-d');
                                $views_time_range = "view_date >= '{$start_datetime}'";
                            }
                        } // Invalid time unit, default to last 24 hours
                        else {
                            $start_date = $start_date->sub(new \DateInterval('P1D'));
                            $start_datetime = $start_date->format('Y-m-d H:i:s');
                            $views_time_range = "view_datetime >= '{$start_datetime}'";
                        }

                        break;
                    default:
                        $start_date = $start_date->sub(new \DateInterval('P1D'));
                        $start_datetime = $start_date->format('Y-m-d H:i:s');
                        $views_time_range = "view_datetime >= '{$start_datetime}'";
                        break;
                }

                // Get entries published within the specified time range
                if ( isset($this->options['freshness']) && $this->options['freshness'] ) {
                    $where .= " AND p.post_date >= '{$start_datetime}'";
                }

                // Order by views count
                if ( "comments" != $this->options['order_by'] ) {
                    // Order by views
                    if ( "views" == $this->options['order_by'] ) {
                        $fields .= ", v.pageviews";
                        $join = "INNER JOIN (SELECT SUM(pageviews) AS pageviews, postid FROM `{$wpdb->prefix}popularpostssummary` WHERE {$views_time_range} GROUP BY postid) v ON p.ID = v.postid";
                        $orderby = "ORDER BY pageviews DESC";
                    }
                    // Order by average views
                    else {
                        $fields .= ", v.avg_views";
                        $join = "INNER JOIN (SELECT SUM(pageviews)/(IF ( DATEDIFF('{$now->format('Y-m-d H:i:s')}', '{$start_datetime}') > 0, DATEDIFF('{$now->format('Y-m-d H:i:s')}', '{$start_datetime}'), 1) ) AS avg_views, postid FROM `{$wpdb->prefix}popularpostssummary` WHERE {$views_time_range} GROUP BY postid) v ON p.ID = v.postid";
                        $orderby = "ORDER BY avg_views DESC";
                    }

                    // Display comments count, too
                    if ( isset($this->options['stats_tag']['comment_count']) && $this->options['stats_tag']['comment_count'] ) {
                        $fields .= ", IFNULL(c.comment_count, 0) AS comment_count";
                        $join .= " LEFT JOIN (SELECT comment_post_ID, COUNT(comment_post_ID) AS comment_count FROM `{$wpdb->comments}` WHERE comment_date_gmt >= '{$start_datetime}' AND comment_approved = '1' GROUP BY comment_post_ID) c ON p.ID = c.comment_post_ID";
                    }
                }
                // Order by comments count
                else {
                    $fields .= ", c.comment_count";
                    $join = "INNER JOIN (SELECT COUNT(comment_post_ID) AS comment_count, comment_post_ID FROM `{$wpdb->comments}` WHERE comment_date_gmt >= '{$start_datetime}' AND comment_approved = '1' GROUP BY comment_post_ID) c ON p.ID = c.comment_post_ID";
                    $orderby = "ORDER BY comment_count DESC";

                    // Display views count, too
                    if ( isset($this->options['stats_tag']['views']) && $this->options['stats_tag']['views'] ) {
                        $fields .= ", v.pageviews";
                        $join .= " INNER JOIN (SELECT SUM(pageviews) AS pageviews, postid FROM `{$wpdb->prefix}popularpostssummary` WHERE {$views_time_range} GROUP BY postid) v ON p.ID = v.postid";
                    }
                }
            }

            // List only published, non password-protected posts
            $where .= " AND p.post_password = '' AND p.post_status = 'publish'";

            if ( !empty($args) ) {
                $where = $wpdb->prepare($where, $args);
            }

            $fields = apply_filters('wpp_query_fields', $fields, $this->options);
            $table = apply_filters('wpp_query_table', $table, $this->options);
            $join = apply_filters('wpp_query_join', $join, $this->options);
            $where = apply_filters('wpp_query_where', $where, $this->options);
            $groupby = apply_filters('wpp_query_group_by', $groupby, $this->options);
            $orderby = apply_filters('wpp_query_order_by', $orderby, $this->options);
            $limit = apply_filters('wpp_query_limit', $limit, $this->options);

            // Finally, build the query
            $query = "SELECT {$fields} FROM {$table} {$join} {$where} {$groupby} {$orderby} {$limit};";
            $this->query = $query;
        }
    }

    /**
     * Queries the database.
     *
     * @since    4.0.0
     * @access   private
     */
    private function run_query()
    {
        /**
         * @var wpdb $wpdb
         */
        global $wpdb;

        if ( isset($wpdb) && !empty($this->query) && !is_wp_error($this->query) ) {
            $this->posts = $wpdb->get_results($this->query);
        }
    }

    /**
     * Returns the query string.
     *
     * @since    4.0.0
     * @return   WP_Error|string   Query string on success, WP_Error on failure
     */
    public function get_query()
    {
        return $this->query;
    }

    /**
     * Returns the list of posts.
     *
     * @since    4.0.0
     * @return   array
     */
    public function get_posts()
    {
        return $this->posts;
    }
}
