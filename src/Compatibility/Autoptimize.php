<?php
/**
 * Hooks into Autoptimize to exclude wpp(.min).js 
 * from its JS optimization
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/Front
 * @author     Hector Cabrera <me@cabrerahector.com>
 */

namespace WordPressPopularPosts\Compatibility;

use WordPressPopularPosts\Compatibility\Compat;

class Autoptimize extends Compat
{
    /**
     * Registers filters to exclude wpp(.min).jss from Autoptimize's JS optimization.
     */
    public function init()
    {
        add_filter('autoptimize_filter_js_exclude', [$this, 'exclude_from_js_optimization']);
    }

    /**
     * Adds wpp(.min).js to the exclusions list.
     *
     * @param  array  An array of file exclusions
     * @return array  The modified array of exclusions
     */
    public function exclude_from_js_optimization($excluded) {

        $files = explode(', ', $excluded);
        $files[] = 'wpp.min.js';
        $files[] = 'wpp.js';
        $files = array_filter($files);

        $excluded = implode(', ', $files);

        return $excluded;
    }
}
