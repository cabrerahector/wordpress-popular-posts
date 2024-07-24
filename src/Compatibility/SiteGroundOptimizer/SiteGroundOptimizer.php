<?php
/**
 * Hooks into Speed Optimizer's (formerly known as SiteGround Optimizer) hook
 * to exclude wpp(.min).js from its JS optimization
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/Compatibility
 * @author     Hector Cabrera <me@cabrerahector.com>
 */

namespace WordPressPopularPosts\Compatibility\SiteGroundOptimizer;

use WordPressPopularPosts\Compatibility\Compat;

class SiteGroundOptimizer extends Compat
{
    /**
     * Registers filters to exclude wpp-js from SGO's JS optimization.
     */
    public function init()
    {
        if ( defined('SiteGround_Optimizer\VERSION') ) {
            add_filter('sgo_javascript_combine_exclude_ids', [$this, 'exclude_from_js_optimization']);
        }
    }

    /**
     * Adds wpp-js to the exclusions list.
     *
     * @param  array  An array of file ID exclusions
     * @return array  The modified array of exclusions
     */
    public function exclude_from_js_optimization($excluded) {
        $excluded[] = 'wpp-js';
        return $excluded;
    }
}
