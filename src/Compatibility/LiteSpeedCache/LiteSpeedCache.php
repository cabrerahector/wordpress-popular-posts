<?php
/**
 * Hooks into LiteSpeedCache's hooks
 * to exclude wpp(.min).js from its JS optimization
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/Compatibility
 * @author     Hector Cabrera <me@cabrerahector.com>
 */

namespace WordPressPopularPosts\Compatibility\LiteSpeedCache;

use WordPressPopularPosts\Compatibility\Compat;

class LiteSpeedCache extends Compat
{
    /**
     * Registers filters to exclude wpp(.min).js from LSC's JS optimization.
     */
    public function init()
    {
        add_filter('litespeed_optimize_js_excludes', [$this, 'exclude_from_js_optimization']);
        add_filter('litespeed_optm_js_defer_exc', [$this, 'exclude_from_js_optimization']);
        add_filter('litespeed_optm_js_delay_inc', [$this, 'exclude_from_js_optimization']);
    }

    /**
     * Adds wpp(.min).js to the exclusions list.
     *
     * @param  array  An array of file exclusions
     * @return array  The modified array of exclusions
     */
    public function exclude_from_js_optimization($excluded) {
        $excluded[] = 'wpp.min.js';
        $excluded[] = 'wpp.js';

        return $excluded;
    }
}
