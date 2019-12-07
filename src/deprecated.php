<?php
/**
 * Deprecated functions.
 */

/**
 * Template tag - gets popular posts. Deprecated in 2.0.3, use wpp_get_mostpopular instead.
 *
 * @since   1.0
 * @param   mixed   $args
 */
function get_mostpopular($args = NULL) {
    trigger_error( 'The get_mostpopular() template tag has been deprecated since 2.0.3. Please use wpp_get_mostpopular() instead.', E_USER_WARNING );
}

/**
 * Deprecated classes.
 */

/**
 * Queries the database for popular posts. Deprecated since 5.0.0, use \WordPressPopularPosts\Query instead.
 *
 * To use this class, you must pass it an array of parameters (mostly the same ones used with
 * the wpp_get_mostpopular() template tag). The very minimum required parameters are 'range', 'order_by'
 * and 'limit'.
 *
 * eg.: $popular_posts = new WPP_Query( array('range' => 'last7days', 'order_by' => 'views', 'limit' => 5) );
 *
 * @since   4.0.0
 * @package WordPressPopularPosts
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
        trigger_error('The WPP_Query class has been deprecated since 5.0.0. Please use \WordPressPopularPosts\Query instead.', E_USER_NOTICE);
    }
}
