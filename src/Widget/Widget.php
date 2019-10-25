<?php

namespace WordPressPopularPosts\Widget;

use WordPressPopularPosts\Helper;
use WordPressPopularPosts\Query;

class Widget extends \WP_Widget {

    /**
     * Default options.
     *
     * @since   5.0.0
     * @var     array
     */
    private $defaults = [];

    /**
     * Administrative settings.
     *
     * @since   2.3.3
     * @var	    array
     */
    private $admin_options = [];

    /**
     * Image object.
     *
     * @since   5.0.0
     * @var     WordPressPopularPosts\Image
     */
    private $thumbnail;

    /**
     * Output object.
     *
     * @var     \WordPressPopularPosts\Output
     * @access  private
     */
    private $output;

    /**
     * Translate object.
     *
     * @var     \WordPressPopularPosts\Translate    $translate
     * @access  private
     */
    private $translate;

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
     * @since   1.0.0
     * @param   array                            $options
     * @param   array                            $config
     * @param   \WordPressPopularPosts\Output    $output
     * @param   \WordPressPopularPosts\Image     $image
     * @param   \WordPressPopularPosts\Translate $translate
     * @param   \WordPressPopularPosts\Themer    $themer
     */
    public function __construct(array $options, array $config, \WordPressPopularPosts\Output $output, \WordPressPopularPosts\Image $thumbnail, \WordPressPopularPosts\Translate $translate, \WordPressPopularPosts\Themer $themer)
    {
        // Create the widget
        parent::__construct(
            'wpp',
            'WordPress Popular Posts',
            [
                'classname'     =>  'popular-posts',
                'description'   =>  __('The most Popular Posts on your blog.', 'wordpress-popular-posts')
            ]
        );

        $this->defaults = $options;
        $this->admin_options = $config;
        $this->output = $output;
        $this->thumbnail = $thumbnail;
        $this->translate = $translate;
        $this->themer = $themer;
    }

    /**
     * Widget hooks.
     *
     * @since   5.0.0
     */
    public function hooks()
    {
        // Register the widget
        add_action('widgets_init', [$this, 'register']);
    }

    /**
     * Registers the widget.
     *
     * @since   5.0.0
     */
    public function register()
    {
        register_widget($this);
    }

    /**
     * Outputs the content of the widget.
     *
     * @since   1.0.0
     * @param   array   $args       The array of form elements.
     * @param   array   $instance   The current instance of the widget.
     */
    public function widget($args, $instance)
    {
        /**
         * @var string $name
         * @var string $id
         * @var string $description
         * @var string $class
         * @var string $before_widget
         * @var string $after_widget
         * @var string $before_title
         * @var string $after_title
         * @var string $widget_id
         * @var string $widget_name
         */
        extract($args, EXTR_SKIP);

        $instance = Helper::merge_array_r(
            $this->defaults,
            (array) $instance
        );

        $markup = ( $instance['markup']['custom_html'] || has_filter('wpp_custom_html') || has_filter('wpp_post') )
              ? 'custom'
              : 'regular';

        echo "\n" . $before_widget . "\n";

        // Has user set a title?
        if ( '' != $instance['title'] ) {
            $title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);

            if (
                $instance['markup']['custom_html']
                && $instance['markup']['title-start'] != ""
                && $instance['markup']['title-end'] != ""
            ) {
                echo htmlspecialchars_decode($instance['markup']['title-start'], ENT_QUOTES) . $title . htmlspecialchars_decode($instance['markup']['title-end'], ENT_QUOTES);
            } else {
                echo $before_title . $title . $after_title;
            }
        }

        // Expose Widget ID for customization
        $instance['widget_id'] = $widget_id;

        // Get posts
        if ( $this->admin_options['tools']['ajax'] && ! is_customize_preview() ) {

            if ( empty($before_widget) || ! preg_match('/id="[^"]*"/', $before_widget) ) {
            ?>
            <p><?php printf(__('Error: cannot ajaxify WordPress Popular Posts on this sidebar. It\'s missing the <em>id</em> attribute on before_widget (see <a href="%s" target="_blank" rel="nofollow">register_sidebar</a> for more)', 'wordpress-popular-posts'), 'https://codex.wordpress.org/Function_Reference/register_sidebar'); ?>.</p>
            <?php
            } 
            else {
            ?>
            <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function() {
                    var wpp_widget_container = document.getElementById('<?php echo $widget_id; ?>');

                    if ( 'undefined' != typeof WordPressPopularPosts ) {
                        WordPressPopularPosts.get(
                            wpp_params.ajax_url + '/widget/<?php echo $this->number; ?>',
                            'is_single=<?php echo Helper::is_single(); ?><?php echo (function_exists('PLL')) ? '&lang=' . $this->translate->get_current_language() : ''; ?>',
                            function(response){
                                wpp_widget_container.innerHTML += JSON.parse(response).widget;

                                let sr = wpp_widget_container.querySelector('.popular-posts-sr');

                                if ( sr ) {
                                    WordPressPopularPosts.theme(sr);
                                }

                                var event = null;

                                if ( 'function' === typeof(Event) ) {
                                    event = new Event("wpp-onload", {"bubbles": true, "cancelable": false});
                                } /* Fallback for older browsers */
                                else {
                                    if ( document.createEvent ) {
                                        event = document.createEvent('Event');
                                        event.initEvent("wpp-onload", true, false);
                                    }
                                }

                                if ( event ) {
                                    wpp_widget_container.dispatchEvent(event);
                                }
                            }
                        );
                    }
                });
            </script>
            <?php
            }
        } else {
            $this->get_popular($instance);
        }

        echo "\n" . $after_widget . "\n";
    }

    /**
     * Generates the administration form for the widget.
     *
     * @since   1.0.0
     * @param   array   $instance   The array of keys and values for the widget.
     */
    public function form($instance)
    {
        $instance = Helper::merge_array_r(
            $this->defaults,
            (array) $instance
        );
        require plugin_dir_path(__FILE__) . '/form.php';
    }

    /**
     * Processes the widget's options to be saved.
     *
     * @since   1.0.0
     * @param   array   $new_instance   The previous instance of values before the update.
     * @param   array   $old_instance   The new instance of values to be generated via the update.
     * @return  array   $instance       Updated instance.
     */
    public function update($new_instance, $old_instance)
    {
        if ( empty($old_instance) ) {
            $old_instance = $this->defaults;
        } else {
            $old_instance = Helper::merge_array_r(
                $this->defaults,
                (array) $old_instance
            );
        }

        $instance = $old_instance;

        $instance['title'] = htmlspecialchars(stripslashes_deep(strip_tags($new_instance['title'])), ENT_QUOTES);
        $instance['limit'] = ( Helper::is_number($new_instance['limit']) && $new_instance['limit'] > 0 )
          ? $new_instance['limit']
          : 10;
        $instance['range'] = $new_instance['range'];
        $instance['time_quantity'] = ( Helper::is_number($new_instance['time_quantity']) && $new_instance['time_quantity'] > 0 )
          ? $new_instance['time_quantity']
          : 24;
        $instance['time_unit'] = $new_instance['time_unit'];
        $instance['order_by'] = $new_instance['order_by'];

        // FILTERS
        // user did not set a post type name, so we fall back to default
        $instance['post_type'] = ( '' == $new_instance['post_type'] )
          ? 'post,page'
          : $new_instance['post_type'];

        $instance['freshness'] = isset($new_instance['freshness']);

        // Post / Page / CTP filter
        $ids = array_filter(explode(",", rtrim(preg_replace('|[^0-9,]|', '', $new_instance['pid']), ",")), 'is_numeric');
        // Got no valid IDs, clear
        if ( empty($ids) ) {
            $instance['pid'] = '';
        }
        else {
            $instance['pid'] = implode(",", $ids);
        }

        // Taxonomy filter
        $taxonomies = $new_instance['taxonomy'];

        if ( isset($taxonomies['names']) ) {
            // Remove taxonomies that don't have any valid term IDs
            foreach( $taxonomies['terms'] as $taxonomy => $terms ) {
                $taxonomies['terms'][$taxonomy] = array_filter(
                    explode(",", trim(preg_replace('|[^0-9,-]|', '', $taxonomies['terms'][$taxonomy]), ", ")),
                    'is_numeric'
                );

                if (
                    empty($taxonomies['terms'][$taxonomy])
                    || ! in_array($taxonomy, $taxonomies['names'])
                ) {
                    unset($taxonomies['terms'][$taxonomy]);
                } else {
                    $taxonomies['terms'][$taxonomy] = implode(',', $taxonomies['terms'][$taxonomy]);
                }
            }

            if ( ! empty($taxonomies['terms']) ) {
                $instance['taxonomy'] = implode(';', array_keys($taxonomies['terms']));
                $instance['term_id'] = implode(';', array_values($taxonomies['terms']));
            }
        } // Discard everything
        else {
            $instance['taxonomy'] = '';
            $instance['term_id'] = '';
        }

        // Author filter
        $ids = array_filter(explode(",", rtrim(preg_replace('|[^0-9,]|', '', $new_instance['uid']), ",")), 'is_numeric');
        // Got no valid IDs, clear
        if ( empty($ids) ) {
            $instance['author'] = '';
        }
        else {
            $instance['author'] = implode( ",", $ids );
        }

        $instance['shorten_title']['words'] = $new_instance['shorten_title-words'];
        $instance['shorten_title']['active'] = isset($new_instance['shorten_title-active']);
        $instance['shorten_title']['length'] = ( Helper::is_number($new_instance['shorten_title-length']) && $new_instance['shorten_title-length'] > 0 )
          ? $new_instance['shorten_title-length']
          : 25;

        $instance['post-excerpt']['keep_format'] = isset($new_instance['post-excerpt-format']);
        $instance['post-excerpt']['words'] = $new_instance['post-excerpt-words'];
        $instance['post-excerpt']['active'] = isset($new_instance['post-excerpt-active']);
        $instance['post-excerpt']['length'] = ( Helper::is_number($new_instance['post-excerpt-length']) && $new_instance['post-excerpt-length'] > 0 )
          ? $new_instance['post-excerpt-length']
          : 55;

        $instance['thumbnail']['active'] = isset($new_instance['thumbnail-active']);
        $instance['thumbnail']['build'] = $new_instance['thumbnail-size-source'];
        $instance['thumbnail']['width'] = 75;
        $instance['thumbnail']['height'] = 75;

        // Use predefined thumbnail sizes
        if ( 'predefined' == $new_instance['thumbnail-size-source'] ) {
            $default_thumbnail_sizes = $this->thumbnail->get_sizes();
            $size = $default_thumbnail_sizes[$new_instance['thumbnail-size']];

            $instance['thumbnail']['width'] = $size['width'];
            $instance['thumbnail']['height'] = $size['height'];
            $instance['thumbnail']['crop'] = $size['crop'];
        } // Set thumbnail size manually
        else {
            if ( Helper::is_number($new_instance['thumbnail-width']) && Helper::is_number($new_instance['thumbnail-height']) ) {
                $instance['thumbnail']['width'] = $new_instance['thumbnail-width'];
                $instance['thumbnail']['height'] = $new_instance['thumbnail-height'];
                $instance['thumbnail']['crop'] = true;
            }
        }

        $instance['rating'] = isset($new_instance['rating']);
        $instance['stats_tag']['comment_count'] = isset($new_instance['comment_count']);
        $instance['stats_tag']['views'] = isset($new_instance['views']);
        $instance['stats_tag']['author'] = isset($new_instance['author']);
        $instance['stats_tag']['date']['active'] = isset($new_instance['date']);
        $instance['stats_tag']['date']['format'] = empty($new_instance['date_format'])
          ? 'F j, Y'
          : $new_instance['date_format'];

        $instance['stats_tag']['taxonomy']['active'] = isset($new_instance['stats_taxonomy']);
        $instance['stats_tag']['taxonomy']['name'] = isset($new_instance['stats_taxonomy_name']) ? $new_instance['stats_taxonomy_name'] : 'category';
        $instance['stats_tag']['category'] = isset($new_instance['stats_taxonomy'] ); // Deprecated in 4.0.0!

        $instance['markup']['custom_html'] = isset($new_instance['custom_html']);
        $instance['markup']['wpp-start'] = empty($new_instance['wpp-start'])
          ? ! $old_instance['markup']['custom_html'] && $instance['markup']['custom_html'] ? htmlspecialchars('<ul class="wpp-list">', ENT_QUOTES) : ''
          : htmlspecialchars($new_instance['wpp-start'], ENT_QUOTES);

        $instance['markup']['wpp-end'] = empty($new_instance['wpp-end'])
          ? ! $old_instance['markup']['custom_html'] && $instance['markup']['custom_html'] ? htmlspecialchars('</ul>', ENT_QUOTES) : ''
          : htmlspecialchars($new_instance['wpp-end'], ENT_QUOTES);

        $instance['markup']['post-html'] = empty($new_instance['post-html'])
          ? htmlspecialchars('<li>{thumb} {title} {stats}</li>', ENT_QUOTES)
          : htmlspecialchars($new_instance['post-html'], ENT_QUOTES);

        $instance['markup']['title-start'] = empty($new_instance['title-start'])
          ? ! $old_instance['markup']['custom_html'] && $instance['markup']['custom_html'] ? '<h2>' : ''
          : htmlspecialchars($new_instance['title-start'], ENT_QUOTES);

        $instance['markup']['title-end'] = empty($new_instance['title-end'])
          ? ! $old_instance['markup']['custom_html'] && $instance['markup']['custom_html'] ? '</h2>' : '' :
          htmlspecialchars($new_instance['title-end'], ENT_QUOTES);

        $instance['theme'] = [
            'name' => isset($new_instance['theme']) ? $new_instance['theme'] : '',
            'applied' => isset($new_instance['theme']) ? (bool) $new_instance['theme-applied'] : false
        ];

        if ( ! isset($new_instance['theme']) || $old_instance['theme']['name'] != $new_instance['theme'] ) {
            $instance['theme']['applied'] = false;
        }

        $theme = $instance['theme']['name'] ? $this->themer->get_theme($instance['theme']['name']) : null;

        if (
            is_array($theme)
            && isset($theme['json'])
            && isset($theme['json']['config'])
            && is_array($theme['json']['config'])
            && ! $instance['theme']['applied']
        ) {
            $instance = Helper::merge_array_r(
                $instance,
                $theme['json']['config']
            );
            $instance['markup']['custom_html'] = true;
            $instance['theme']['applied'] = true;

            $current_sidebar_data = $this->get_sidebar_data();

            if ( $current_sidebar_data ) {
                $instance['markup']['title-start'] = htmlspecialchars($current_sidebar_data['before_title'], ENT_QUOTES);
                $instance['markup']['title-end'] = htmlspecialchars($current_sidebar_data['after_title'], ENT_QUOTES);
            }
        }

        return $instance;
    }

    /**
     * Returns HTML list.
     *
     * @since   2.3.3
     */
    public function get_popular($instance = null)
    {
        if ( is_array($instance) && ! empty($instance) ) {
            // Return cached results
            if ( $this->admin_options['tools']['cache']['active'] ) {

                $key = md5(json_encode($instance));
                $popular_posts = \WordPressPopularPosts\Cache::get($key);

                if ( false === $popular_posts ) {
                    $popular_posts = new Query($instance);

                    $time_value = $this->admin_options['tools']['cache']['interval']['value']; // eg. 5
                    $time_unit = $this->admin_options['tools']['cache']['interval']['time']; // eg. 'minute'

                    // No popular posts found, check again in 1 minute
                    if ( ! $popular_posts->get_posts() ) {
                        $time_value = 1;
                        $time_unit = 'minute';
                    }

                    \WordPressPopularPosts\Cache::set(
                        $key,
                        $popular_posts,
                        $time_value,
                        $time_unit
                    );
                }

            } // Get popular posts
            else {
                $popular_posts = new Query($instance);
            }

            $this->output->set_data($popular_posts->get_posts());
            $this->output->set_public_options($instance);
            $this->output->build_output();

            echo ( $this->admin_options['tools']['cache']['active'] ? '<!-- cached -->' : '' );
            $this->output->output();
        }
    }

    /**
     * Returns data on the current sidebar.
     *
     * @since   5.0.0
     * @access  private
     * @return  array|null
     */
    private function get_sidebar_data()
    {
        global $wp_registered_sidebars;
        $sidebars = wp_get_sidebars_widgets();

        foreach ( (array) $sidebars as $sidebar_id => $sidebar ) {
            if ( in_array($this->id, (array) $sidebar, true ) )
                return $wp_registered_sidebars[$sidebar_id];
        }

        return null;
    }
}
