<?php
/**
 * Hooks into W3TotalCache to exclude wpp(.min).js 
 * from its JS optimization
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/Front
 * @author     Hector Cabrera <me@cabrerahector.com>
 */

namespace WordPressPopularPosts\Compatibility\W3TotalCache;

use WordPressPopularPosts\Compatibility\Compat;

class W3TotalCache extends Compat
{
    /**
     * Registers filter to exclude wpp(.min).jss from W3TotalCache's JS optimization.
     */
    public function init()
    {
        add_filter('w3tc_minify_js_script_tags', [$this, 'exclude_from_js_optimization']);
    }

    /**
     * Adds wpp(.min).js to the exclusions list.
     *
     * @param  array  An array of file exclusions
     * @return array  The modified array of exclusions
     */
    public function exclude_from_js_optimization($scripts) {

        if ( is_array($scripts) ) {
            $scripts = array_filter($scripts, function($script) {
                return (false === strpos($script, 'wpp-js') && false === strpos($script, 'wpp.js') && false === strpos($script, 'wpp.min.js'));
            });
            // Reset keys
            $scripts = array_values($scripts);
        }

        return $scripts;
    }
}
