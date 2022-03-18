<?php
namespace WordPressPopularPosts\Rest;

use WordPressPopularPosts\Translate;

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
     */
    public function __construct(array $config, Translate $translate)
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
     * Sets language/locale.
     *
     * @since   5.3.0
     */
    protected function set_lang(?string $lang)
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
