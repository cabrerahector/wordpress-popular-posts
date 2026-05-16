<?php
/**
 * Renders a "Views" column on post lists.
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/Admin
 * @author     Hector Cabrera <me@cabrerahector.com>
 */

namespace WordPressPopularPosts\Admin;

class ListColumnTotalViews {

    /**
     * Post types array.
     *
     * @since  7.4.0
     * @var    array
     * @access private
     */
    private $post_types;

    /**
     * Admin options.
     *
     * @since   7.4.0
     * @var     array
     * @access  private
     */
    private $config;

    /**
     * Construct.
     *
     * @param   array   $config   Admin options.
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        if ( $this->config['tools']['views_column']['post_types'] ) {
            $registered_post_types = get_post_types(['public' => true], 'names');

            $this->post_types = array_values(
                array_intersect(
                    array_map('trim', explode(',', $this->config['tools']['views_column']['post_types'])),
                    $registered_post_types
                )
            );
        }

        $this->hooks();
    }

    /**
     * Registers all the required admin hooks.
     *
     * @since   7.4.0
     */
    public function hooks()
    {
        if ( $this->post_types ) {
            foreach($this->post_types as $post_type) {
                add_filter('manage_' . $post_type . '_posts_columns', [$this, 'add_views_column']);
                add_action('manage_' . $post_type . '_posts_custom_column', [$this, 'views_column'], 10, 2);
                add_filter('manage_edit-' . $post_type . '_sortable_columns', [$this, 'sortable_columns']);
            }

            add_filter('posts_join', [$this, 'query_join'], 10, 2);
            add_filter('posts_fields', [$this, 'query_fields'], 10, 2);
            add_filter('posts_orderby', [$this, 'order_items_by'], 10, 2);

            add_action('admin_head', [$this, 'views_column_width']);
        }
    }

    /**
     * Adds Views column to item list.
     *
     * @since   7.4.0
     * @param   array   $columns
     * @return  array
     */
    public function add_views_column(array $columns)
    {
        $columns['pageviews'] = __('Views', 'wordpress-popular-posts');
        return $columns;
    }

    /**
     * Displays the actual views count of each post in the list.
     *
     * @since   7.4.0
     * @param   array   $column_name
     * @param   int     $post_id
     */
    public function views_column(string $column_name, int $post_id)
    {
        if ( $column_name === 'pageviews' ) {
            echo wpp_get_views($post_id);
        }
    }

    /**
     * Makes the Views column sortable.
     *
     * @since   7.4.0
     * @param   array   $columns
     * @return  array
     */
    public function sortable_columns(array $columns)
    {
        $columns['pageviews'] = 'pageviews';
        return $columns;
    }

    /**
     * Adds the pageviews field to the query.
     *
     * @since   7.4.0
     * @param   string  $fields
     * @param   object  $wp_query
     * @return  string
     */
    public function query_fields(string $fields, $wp_query)
    {
        global $pagenow, $wpdb;

        if (
            is_admin() 
            && 'edit.php' === $pagenow 
            && isset($wp_query->query['post_type'])
            && in_array($wp_query->query['post_type'], $this->post_types)
        ) {
            $fields .= ", IFNULL({$wpdb->prefix}popularpostsdata.pageviews, 0) AS pageviews";
        }

        return $fields;
    }

    /**
     * Joins WPP's data table.
     *
     * @since   7.4.0
     * @param   string  $join
     * @param   object  $wp_query
     * @return  string
     */
    public function query_join(string $join, $wp_query)
    {
        global $pagenow, $wpdb;

        if (
            is_admin() 
            && 'edit.php' === $pagenow 
            && isset($wp_query->query['post_type'])
            && in_array($wp_query->query['post_type'], $this->post_types)
        ) {
            $join .= "LEFT JOIN {$wpdb->prefix}popularpostsdata ON {$wpdb->posts}.ID = {$wpdb->prefix}popularpostsdata.postid ";
        }

        return $join;
    }

    /**
     * Sort items by pageviews on demand.
     *
     * @since   7.4.0
     * @param   string  $orderby_statement
     * @param   object  $wp_query
     * @return  string
     */
    public function order_items_by(string $orderby_statement, $wp_query)
    {
        global $pagenow;

        if (
            is_admin() 
            && 'edit.php' === $pagenow 
            && isset($wp_query->query['post_type'])
            && in_array($wp_query->query['post_type'], $this->post_types)
            && ( isset($wp_query->query['orderby']) && 'pageviews' === $wp_query->query['orderby'] )
        ) {
            $orderby_statement = 'pageviews ' . ( isset($wp_query->query['order']) && $wp_query->query['order'] === 'asc' ? 'ASC' : 'DESC' );
        }

        return $orderby_statement;
    }

    /**
     * Adjust the width of the Views column.
     *
     * @since   7.4.0
     */
    public function views_column_width()
    {
        echo '<style>.column-pageviews { width: 150px; }</style>';
    }
}
