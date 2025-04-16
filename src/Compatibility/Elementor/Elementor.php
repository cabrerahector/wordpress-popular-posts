<?php
/**
 * Integrates WPP with Elementor.
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/Compatibility
 * @author     Hector Cabrera <me@cabrerahector.com>
 */

namespace WordPressPopularPosts\Compatibility\Elementor;

use WordPressPopularPosts\Compatibility\Compat;
use WordPressPopularPosts\{Image, Themer};

class Elementor extends Compat
{
    /**
     * Image object.
     *
     * @var     \WordPressPopularPosts\Image
     * @access  private
     */
    private $thumbnail;

    /**
     * Themer object.
     *
     * @var     \WordPressPopularPosts\Themer       $themer
     * @access  private
     */
    private $themer;

    /**
     * Construct.
     *
     * @param array $settings
     */
    public function __construct($settings, Image $image, Themer $themer)
    {
        $this->thumbnail = $image;
        $this->themer = $themer;
    }

    /**
     * Registers various WPPxElementor things.
     *
     * @since 7.3.0
     */
    public function init()
    {
        if ( defined('ELEMENTOR_VERSION') && version_compare(ELEMENTOR_VERSION, '3.5.0', '>=') ) {
            // Registers flame icon
            add_action('elementor/editor/after_enqueue_scripts', [$this, 'elementor_icon_css']);
            // Registers WPP widget
            add_action('elementor/widgets/register', [$this, 'register_widget']);
            // Disable [wpp] shortcode AJAX loading in editor mode
            add_filter('shortcode_atts_wpp', [$this, 'disable_ajax_in_editor'], 10, 4);
        }
    }

    /**
     * Registers WPP's icon for Elementor.
     *
     * @since 7.3.0
     */
    public function elementor_icon_css() {
        $icon_file = esc_url(plugin_dir_url(dirname(__FILE__, 3))) . 'assets/images/flame.svg';

        echo '<style>
            .wpp-eicon {
                display: inline-block;
                width: 24px;
                height: 24px;
                background: url("' . $icon_file . '") center center /contain no-repeat;
            }
        </style>';
    }

    /**
     * Registers WPP widget.
     *
     * @since 7.3.0
     * @param  \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
     */
    public function register_widget($widgets_manager) {
        $registered_themes = $this->themer->get_themes();
        ksort($registered_themes);

        require_once(__DIR__ . '/widgets/widget.php');
        $widgets_manager->register(
            new \Elementor_WPP_Widget(
                [],
                [
                    'image' => $this->thumbnail,
                    'themer' => $this->themer
                ]
            )
        );
    }

    /**
     * Disables [wpp] AJAX loading in editor mode.
     *
     * @since 7.4.0
     * @param array  $out       The output array of shortcode attributes.
     * @param array  $pairs     The supported attributes and their defaults.
     * @param array  $atts      The user defined shortcode attributes.
     * @param string $shortcode The shortcode name.
     */
    public function disable_ajax_in_editor(array $out, array $pairs, array $atts, string $shortcode) {
        if (
            'wpp' === $shortcode &&
            \Elementor\Plugin::$instance->editor->is_edit_mode()
        ) {
            $out['ajaxify'] = 0;
        }

        return $out;
    }
}
