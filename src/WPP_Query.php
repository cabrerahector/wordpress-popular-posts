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
 */

class WPP_Query extends \WordPressPopularPosts\Query {
    /**
     * Constructor.
     *
     * @since   4.0.0
     * @param   array   $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        trigger_error('WPP_Query will be deprecated soon. Please use \WordPressPopularPosts\Query instead.', E_USER_NOTICE);
    }
}
