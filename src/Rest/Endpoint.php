<?php
namespace WordPressPopularPosts\Rest;

use WordPressPopularPosts\Query;

abstract class Endpoint extends \WP_REST_Controller {

    /**
     * Plugin options.
     *
     * @var     array      $config
     * @access  private
     */
    protected $config;

    /**
     * Translate object.
     *
     * @var     \WordPressPopularPosts\Translate    $translate
     * @access  private
     */
    protected $translate;

    /**
     * Initializes class.
     *
     * @param   array
     * @param   \WordPressPopularPosts\Translate
     * @param   \WordPressPopularPosts\Output
     */
    public function __construct(array $config, \WordPressPopularPosts\Translate $translate)
    {
        $this->config = $config;
        $this->translate = $translate;
    }

    /**
     * Registers the endpoint(s).
     *
     * @since   5.3.0
     */
    abstract public function register();

    /**
     * Gets Query object from cache if it exists,
     * otherwise a new Query object will be
     * instantiated and returned.
     *
     * @since   5.0.3
     * @param   array
     * @return  Query
     */
    protected function maybe_query(array $params)
    {
        // Return cached results
        if ( $this->config['tools']['cache']['active'] ) {
            $key = 'wpp_' . md5(json_encode($params));
            $query = \WordPressPopularPosts\Cache::get($key);

            if ( false === $query ) {
                $query = new Query($params);

                $time_value = $this->config['tools']['cache']['interval']['value'];
                $time_unit = $this->config['tools']['cache']['interval']['time'];

                // No popular posts found, check again in 1 minute
                if ( ! $query->get_posts() ) {
                    $time_value = 1;
                    $time_unit = 'minute';
                }

                \WordPressPopularPosts\Cache::set(
                    $key,
                    $query,
                    $time_value,
                    $time_unit
                );
            }
        } // Get real-time popular posts
        else {
            $query = new Query($params);
        }

        return $query;
    }

    /**
     * Sets language/locale.
     *
     * @since   5.3.0
     */
    protected function set_lang($lang)
    {
        // Multilang support
        if ( $lang ) {
            $current_locale = get_locale();
            $locale = null;

            // Polylang support
            if ( function_exists('PLL') ) {
                $lang_object = PLL()->model->get_language($lang);
                $locale = ( $lang_object && isset($lang_object->locale) ) ? $lang_object->locale : null;
            }

            // Reload locale if needed
            if ( $locale && $locale != $current_locale ) {
                $this->translate->set_current_language($lang);
                unload_textdomain('wordpress-popular-posts');
                load_textdomain('wordpress-popular-posts', WP_LANG_DIR . '/plugins/wordpress-popular-posts-' . $locale . '.mo');
            }
        }
    }
}
