<?php
/**
 * Hooks into WPRocket to exclude wpp(.min).js 
 * from its JS optimizations
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/Compatibility
 * @author     Hector Cabrera <me@cabrerahector.com>
 */

namespace WordPressPopularPosts\Compatibility\WPRocket;

use WordPressPopularPosts\Compatibility\Compat;

class WPRocket extends Compat
{
    /**
     * Registers filters to exclude wpp(.min).jss from WPRocket's JS optimizations.
     */
    public function init()
    {
        add_filter('rocket_exclude_js', [$this, 'exclude_from_js_optimization']);
        add_filter('rocket_exclude_defer_js', [$this, 'exclude_from_js_optimization']);
        add_filter('rocket_delay_js_exclusions', [$this, 'exclude_from_js_optimization']);
        add_filter('rocket_cdn_reject_files', [$this, 'exclude_from_js_optimization']);
    }

    /**
     * Removes wpp(.min).js from the optimization list.
     *
     * @param  array  An array of files to optimize
     * @return array  The modified array of files to optimize
     */
    public function exclude_from_js_optimization($scripts) {
        if ( is_array($scripts) ) {
            $scripts[] = '(.*)/wordpress-popular-posts/assets/js/(.*).js';
        }

        return $scripts;
    }
}
