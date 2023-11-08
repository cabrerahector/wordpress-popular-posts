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
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_filter('script_loader_tag', [$this, 'convert_inline_js_into_json'], 10, 3);
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
     * Enqueues public facing assets.
     *
     * @since   5.0.0
     */
    public function enqueue_assets()
    {
        // Enqueue WPP's stylesheet.
        if ( $this->config['tools']['css'] ) {
            $theme_file = get_stylesheet_directory() . '/wpp.css';

            if ( @is_file($theme_file) ) {
                wp_enqueue_style('wordpress-popular-posts-css', get_stylesheet_directory_uri() . '/wpp.css', [], WPP_VERSION, 'all');
            } // Load stock stylesheet
            else {
                wp_enqueue_style('wordpress-popular-posts-css', plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/css/wpp.css', [], WPP_VERSION, 'all');
            }
        }

        // Enqueue WPP's library.
        $is_single = 0;

        if (
            ( 0 == $this->config['tools']['log']['level'] && ! is_user_logged_in() )
            || ( 1 == $this->config['tools']['log']['level'] )
            || ( 2 == $this->config['tools']['log']['level'] && is_user_logged_in() )
        ) {
            $is_single = Helper::is_single();
        }

        $wpp_js = ( defined('WP_DEBUG') && WP_DEBUG )
            ? plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/wpp.js'
            : plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/wpp.min.js';

        wp_register_script('wpp-js', $wpp_js, [], WPP_VERSION, false);
        $params = [
            'sampling_active' => (int) $this->config['tools']['sampling']['active'],
            'sampling_rate' => (int) $this->config['tools']['sampling']['rate'],
            'ajax_url' => esc_url_raw(rest_url('wordpress-popular-posts/v1/popular-posts')),
            'api_url' => esc_url_raw(rest_url('wordpress-popular-posts')),
            'ID' => (int) $is_single,
            'token' => wp_create_nonce('wp_rest'),
            'lang' => function_exists('PLL') ? $this->translate->get_current_language() : 0,
            'debug' => (int) WP_DEBUG
        ];
        wp_enqueue_script('wpp-js');
        wp_add_inline_script('wpp-js', json_encode($params), 'before');
    }

    /**
     * Converts inline script tag into type=application/json.
     *
     * This function mods the original script tag as printed
     * by WordPress which contains the data for the wpp_params
     * object into a JSON script. This improves compatibility
     * with Content Security Policy (CSP).
     *
     * @since   5.2.0
     * @param   string  $tag
     * @param   string  $handle
     * @param   string  $src
     * @return  string  $tag
     */
    public function convert_inline_js_into_json(string $tag, string $handle, string $src)
    {
        if ( 'wpp-js' === $handle ) {
            // id attribute found, replace it
            if ( false !== strpos($tag, 'wpp-js-js-before') ) {
                $tag = str_replace('wpp-js-js-before', 'wpp-json', $tag);
            } // id attribute missing, let's add it
            else {
                $pos = strpos($tag, '>');
                $tag = substr_replace($tag, ' id="wpp-json">', $pos, 1);
            }

            // type attribute found, replace it
            if ( false !== strpos($tag, 'type') ) {
                $pos = strpos($tag, 'text/javascript');

                if ( false !== $pos ) {
                    $tag = substr_replace($tag, 'application/json', $pos, strlen('text/javascript'));
                }
            } // type attribute missing, let's add it
            else {
                $pos = strpos($tag, '>');
                $tag = substr_replace($tag, ' type="application/json">', $pos, 1);
            }

            /**
             * Remove CDATA block added automatically by WordPress 6.4
             * to themes that don't support HTML5 script tags.
             */
            $is_html5 = current_theme_supports('html5', 'script');

            if ( ! $is_html5 ) {
                $tag = str_replace('/* <![CDATA[ */', '', $tag);
                $tag = str_replace('/* ]]> */', '', $tag);
            }
        }

        return $tag;
    }
}
