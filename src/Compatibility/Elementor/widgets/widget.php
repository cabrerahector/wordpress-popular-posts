<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

//use WordPressPopularPosts\{Image, Themer};

/**
 * Elementor WPP Widget.
 *
 * Elementor widget that inserts a WP Popular Posts list on your site.
 *
 * @since 7.3.0
 */
class Elementor_WPP_Widget extends \Elementor\Widget_Base {

    /**
     * Image object.
     *
     * @since 7.3.0
     * @var     \WordPressPopularPosts\Image
     * @access  private
     */
    private $thumbnail;

    /**
     * Available image sizes.
     *
     * @since 7.3.0
     * @var     array
     */
    private $available_sizes = [];

    /**
     * Themer object.
     *
     * @since 7.3.0
     * @var     \WordPressPopularPosts\Themer       $themer
     * @access  private
     */
    private $themer;

    /**
     * Construct.
     *
     * @param array                        $data Widget data. Default is an empty array.
     * @param array|null                   $args Optional. Widget default arguments. Default is null.
     * @param WordPressPopularPosts\Themer $themer
     */
    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);

        $this->thumbnail = $args['image'];
        $this->themer = $args['themer'];
        $this->available_sizes = $this->thumbnail->get_sizes(null);
    }

    /**
     * Get widget name.
     *
     * Retrieve WPP widget name.
     *
     * @since 7.3.0
     * @access public
     * @return string Widget name.
     */
    public function get_name(): string {
        return 'wordpress-popular-posts-ewidget';
    }

    /**
     * Get widget title.
     *
     * Retrieve WPP widget title.
     *
     * @since 7.3.0
     * @access public
     * @return string Widget title.
     */
    public function get_title(): string {
        return 'WP Popular Posts';
    }

    /**
     * Get widget icon.
     *
     * Retrieve WPP widget icon.
     *
     * @since 7.3.0
     * @access public
     * @return string Widget icon.
     */
    public function get_icon(): string {
        return 'wpp-eicon';
    }

    /**
     * Get widget categories.
     *
     * Retrieve the list of categories the WPP widget belongs to.
     *
     * @since 7.3.0
     * @access public
     * @return array Widget categories.
     */
    public function get_categories(): array {
        return ['general'];
    }

    /**
     * Get widget keywords.
     *
     * Retrieve the list of keywords the WPP widget belongs to.
     *
     * @since 7.3.0
     * @access public
     * @return array Widget keywords.
     */
    public function get_keywords(): array {
        return ['popular', 'posts', 'popularity', 'top'];
    }

    /**
     * Get custom help URL.
     *
     * Retrieve a URL where the user can get more information about the widget.
     *
     * @since 7.3.0
     * @access public
     * @return string Widget help URL.
     */
    public function get_custom_help_url(): string {
        return 'https://wordpress.org/support/plugin/wordpress-popular-posts/';
    }

    /**
     * Displays a custom CTA to offer upsales and whatnot.
     *
     * @since 7.3.0
     * @see https://developers.elementor.com/docs/widgets/widget-promotions/
     * @return array
     */
    protected function get_upsale_data(): array {
        return [];
    }

    /**
     * Get stack.
     *
     * Retrieve the widget stack of controls.
     *
     * @since 7.3.0
     * @param bool $with_common_controls Optional. Whether to include the common controls. Default is true.
     * @return array Widget stack of controls.
     */
    public function get_stack( $with_common_controls = true ) {
        return parent::get_stack(false);
    }

    /**
     * Whether the widget requires inner wrapper.
     *
     * Determine whether to optimize the DOM size.
     *
     * @since 7.3.0
     * @access protected
     * @return bool Whether to optimize the DOM size.
     */
    public function has_widget_inner_wrapper(): bool {
        return false;
    }

    /**
     * Whether the element returns dynamic content.
     *
     * Determine whether to cache the element output or not.
     *
     * @since 7.3.0
     * @access protected
     * @return bool Whether to cache the element output.
     */
    protected function is_dynamic_content(): bool {
        return true;
    }

    /**
     * Register WPP widget controls.
     *
     * Add input fields to allow the user to customize the widget settings.
     *
     * @since 7.3.0
     * @access protected
     */
    protected function register_controls(): void {
        require 'widget-controls.php';
    }

    /**
     * Render WPP widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 7.3.0
     * @access protected
     */
    protected function render(): void {
        $is_edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();
        $widget_id = $this->get_id();
        $settings = $this->parse_settings();

        /**
         * Allows to modify settings passed to wpp_get_mostpopular()
         *
         * @param array  $settings  WPP settings
         * @param string $widget_id Elementor Widget ID
         */
        $settings = apply_filters('wpp_elementor_widget_settings', $settings, $widget_id);

        /** While on edit mode... */
        if ( $is_edit_mode ) {
            /** ... disable AJAX loading */
            $settings['ajaxify'] = 0;

            /** ... display widget ID above the list when debug mode is on */
            if ( defined('WP_DEBUG') && WP_DEBUG ) {
                echo '<p style="margin: 0 0 1em; font-size: 10px; font-weight: 600;">[Widget ID:' . esc_html($widget_id) . ']</p>';
            }
        }

        wpp_get_mostpopular($settings);
    }

    /**
     * Parses and cleans up settings data from Elementor
     * before using it.
     *
     * @since 7.3.0
     * @access private
     */
    private function parse_settings() {
        $settings = $this->get_settings_for_display();

        $allowed_keys = [
            'header', 'limit', 'offset', 'range', 'time_unit', 'time_quantity', 'freshness', 'order_by', 'post_type', 'exclude', 'cat', 'taxonomy', 'term_id', 'author', 'title_length', 'title_by_words', 'excerpt_length', 'excerpt_format', 'excerpt_by_words',
            'thumbnail_width', 'thumbnail_height', 'thumbnail_build', 'rating', 'stats_comments', 'stats_views', 'stats_author', 'stats_date', 'stats_date_format', 'stats_category', 'stats_taxonomy', 'wpp_start', 'wpp_end', 'header_start', 'header_end', 'post_html',
            'theme', 'ajaxify'
        ];

        /** Handle taxonomies */
        $wpp_taxonomy_fields = array_filter($settings, function($key) {
            return str_starts_with($key, 'wpp_taxonomy_'); // str_starts_with() requires either WP 5.9+ or PHP 8+
        }, ARRAY_FILTER_USE_KEY);
        $wpp_taxonomies = [];

        if ( $wpp_taxonomy_fields ) {
            $slugs_arr = array_filter($settings, function($key) {
                return str_starts_with($key, 'wpp_taxonomy_slug_'); // str_starts_with() requires either WP 5.9+ or PHP 8+
            }, ARRAY_FILTER_USE_KEY);

            if ( $slugs_arr ) {
                $slugs = array_filter($slugs_arr, function($slug) {
                    return 0 !== $slug;
                });

                if ( $slugs ) {
                    $slugs = array_values($slugs);

                    foreach( $slugs as $slug ) {
                        if ( ! isset($wpp_taxonomy_fields['wpp_taxonomy_' . $slug . '_terms']) ) {
                            continue;
                        }

                        $terms = array_filter(
                            explode(
                                ',',
                                trim(preg_replace('|[^0-9,-]|', '', $wpp_taxonomy_fields['wpp_taxonomy_' . $slug . '_terms']), ', ')
                            ),
                            'is_numeric'
                        );

                        if ( ! empty($terms) ) {
                            $wpp_taxonomies[$slug] = implode(',', $terms);
                        }
                    }
                }
            }
        }

        if ( $wpp_taxonomies ) {
            $settings['taxonomy'] = implode(';', array_keys($wpp_taxonomies));
            $settings['term_id'] = implode(';', array_values($wpp_taxonomies));
        }

        /** Handle thumbnail selection */
        if ( isset($settings['thumbnail_size']) && isset($this->available_sizes[$settings['thumbnail_size']]) ) {
            $settings['thumbnail_width'] = $this->available_sizes[$settings['thumbnail_size']]['width'];
            $settings['thumbnail_height'] = $this->available_sizes[$settings['thumbnail_size']]['height'];
        }

        /** Let's remove all Elementor related settings */
        $settings = array_filter($settings, function($key) use ($allowed_keys) {
            return in_array($key, $allowed_keys);
        }, ARRAY_FILTER_USE_KEY);

        /** Handle toggles that are enabled by default */
        if ( ! $settings['stats_views'] ) {
            $settings['stats_views'] = '0';
        }

        /** Handle themes */
        $registered_themes = $this->themer->get_themes();

        if ( $settings['theme'] && isset($registered_themes[$settings['theme']]) ) {
            $theme_config = $registered_themes[$settings['theme']]['json']['config'];

            if (
                isset($theme_config['shorten_title'])
                && $theme_config['shorten_title']['active']
                && is_numeric($theme_config['shorten_title']['length'])
                && $theme_config['shorten_title']['length'] > 0
            ) {
                $settings['title_length'] = $theme_config['shorten_title']['length'];

                if ( $theme_config['shorten_title']['length'] ) {
                    $settings['title_by_words'] = 1;
                }
            }

            if (
                isset($theme_config['post-excerpt'])
                && $theme_config['post-excerpt']['active']
                && is_numeric($theme_config['post-excerpt']['length'])
                && $theme_config['post-excerpt']['length'] > 0
            ) {
                $settings['excerpt_length'] = $theme_config['post-excerpt']['length'];

                if ( $theme_config['post-excerpt']['keep_format'] ) {
                    $settings['excerpt_format'] = 1;
                }

                if ( $theme_config['post-excerpt']['words'] ) {
                    $settings['excerpt_by_words'] = 1;
                }
            }

            if (
                isset($theme_config['thumbnail'])
                && $theme_config['thumbnail']['active']
                && is_numeric($theme_config['thumbnail']['width'])
                && is_numeric($theme_config['thumbnail']['height'])
                && $theme_config['thumbnail']['width'] > 0
                && $theme_config['thumbnail']['height'] > 0
            ) {
                $settings['thumbnail_width'] = $theme_config['thumbnail']['width'];
                $settings['thumbnail_height'] = $theme_config['thumbnail']['height'];
                $settings['thumbnail_build'] = $theme_config['thumbnail']['build'] ?? 'manual';

                if ( $theme_config['post-excerpt']['keep_format'] ) {
                    $settings['excerpt_format'] = 1;
                }

                if ( $theme_config['post-excerpt']['words'] ) {
                    $settings['excerpt_by_words'] = 1;
                }
            }

            if ( isset($theme_config['rating']) && $theme_config['rating'] ) {
                $settings['rating'] = 1;
            }

            if ( isset($theme_config['stats_tag']['comment_count']) && $theme_config['stats_tag']['comment_count'] ) {
                $settings['stats_comments'] = 1;
            }

            if ( isset($theme_config['stats_tag']['views']) && $theme_config['stats_tag']['views'] ) {
                $settings['stats_views'] = 1;
            }

            if ( isset($theme_config['stats_tag']['author']) && $theme_config['stats_tag']['author'] ) {
                $settings['stats_author'] = 1;
            }

            if ( isset($theme_config['stats_tag']['date']) && $theme_config['stats_tag']['date']['active'] ) {
                $settings['stats_date'] = 1;

                if ( isset($theme_config['stats_tag']['date']['format']) && $theme_config['stats_tag']['date']['format'] ) {
                    $settings['stats_date_format'] = $theme_config['stats_tag']['date']['format'];
                }
            }

            if ( isset($theme_config['stats_tag']['category']) && $theme_config['stats_tag']['category'] ) {
                $settings['stats_category'] = 1;
            } else {
                if ( isset($theme_config['stats_tag']['taxonomy']) && $theme_config['stats_tag']['taxonomy']['active'] ) {
                    $settings['stats_taxonomy'] = 1;
                }
            }

            /**
             * @TODO
             * Allow displaying multiple taxonomies
             */

            $settings['wpp_start'] = $theme_config['markup']['wpp-start'];
            $settings['post_html'] = $theme_config['markup']['post-html'];
            $settings['wpp_end'] = $theme_config['markup']['wpp-end'];
        } else {
            $settings['theme'] = '';
        }

        return $settings;
    }
}
