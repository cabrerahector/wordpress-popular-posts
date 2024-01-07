<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines hooks to enqueue the public-specific stylesheet and JavaScript.
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/Front
 * @author     Hector Cabrera <me@cabrerahector.com>
 */

namespace WordPressPopularPosts\Front;

use WordPressPopularPosts\{ Helper, Translate };

class Front {

    /**
     * Plugin options.
     *
     * @var     array      $config
     * @access  private
     */
    private $config;

    /**
     * Translate object.
     *
     * @var     \WordPressPopularPosts\Translate    $translate
     * @access  private
     */
    private $translate;

    /**
     * Construct.
     *
     * @since   5.0.0
     * @param   array                               $config     Admin settings.
     * @param   \WordPressPopularPosts\Translate    $translate  Translate class.
     */
    public function __construct(array $config, Translate $translate)
    {
        $this->config = $config;
        $this->translate = $translate;
    }

    /**
     * WordPress public-facing hooks.
     *
     * @since   5.0.0
     */
    public function hooks()
    {
        add_action('wp_head', [$this, 'inline_loading_css']);
        add_action('wp_head', [$this, 'enqueue_scripts'], 1);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Inserts CSS related to the loading animation into <head>
     *
     * @since   5.3.0
     */
    public function inline_loading_css()
    {
        $wpp_insert_loading_animation_styles = apply_filters('wpp_insert_loading_animation_styles', true);

        if ( $wpp_insert_loading_animation_styles ) :
            ?>
            <style id="wpp-loading-animation-styles">@-webkit-keyframes bgslide{from{background-position-x:0}to{background-position-x:-200%}}@keyframes bgslide{from{background-position-x:0}to{background-position-x:-200%}}.wpp-widget-placeholder,.wpp-widget-block-placeholder,.wpp-shortcode-placeholder{margin:0 auto;width:60px;height:3px;background:#dd3737;background:linear-gradient(90deg,#dd3737 0%,#571313 10%,#dd3737 100%);background-size:200% auto;border-radius:3px;-webkit-animation:bgslide 1s infinite linear;animation:bgslide 1s infinite linear}</style>
            <?php
        endif;
    }

    /**
     * Enqueues public facing styles.
     *
     * @since   5.0.0
     */
    public function enqueue_styles()
    {
        if ( $this->config['tools']['css'] ) {
            $theme_file = get_stylesheet_directory() . '/wpp.css';

            if ( @is_file($theme_file) ) {
                wp_enqueue_style('wordpress-popular-posts-css', get_stylesheet_directory_uri() . '/wpp.css', [], WPP_VERSION, 'all');
            } // Load stock stylesheet
            else {
                wp_enqueue_style('wordpress-popular-posts-css', plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/css/wpp.css', [], WPP_VERSION, 'all');
            }
        }
    }

    /**
     * Enqueues public facing scripts.
     *
     * @since   7.0.0
     */
    public function enqueue_scripts()
    {
        $is_single = 0;

        if (
            ( 0 == $this->config['tools']['log']['level'] && ! is_user_logged_in() )
            || ( 1 == $this->config['tools']['log']['level'] )
            || ( 2 == $this->config['tools']['log']['level'] && is_user_logged_in() )
        ) {
            $is_single = Helper::is_single();
        }

        $wpp_js_url = plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/wpp.' . (! defined('WP_DEBUG') || false === WP_DEBUG ? 'min.' : '') . 'js';

        wp_print_script_tag(
            [
                'id' => 'wpp-js',
                'src' => $wpp_js_url,
                'async' => true,
                'data-sampling' => (int) $this->config['tools']['sampling']['active'],
                'data-sampling-rate' => (int) $this->config['tools']['sampling']['rate'],
                'data-api-url' => esc_url_raw(rest_url('wordpress-popular-posts')),
                'data-post-id' => (int) $is_single,
                'data-token' => wp_create_nonce('wp_rest'),
                'data-lang' => function_exists('PLL') ? $this->translate->get_current_language() : 0,
                'data-debug' => (int) WP_DEBUG
            ]
        );
    }
}
