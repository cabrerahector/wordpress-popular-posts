<?php

namespace WordPressPopularPosts\Widget;

use WordPressPopularPosts\{ Helper, Image, Output, Themer, Translate };
use WordPressPopularPosts\Traits\QueriesPosts;

class Widget extends \WP_Widget {

    use QueriesPosts;

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
     * @var     array
     */
    private $config = [];

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
    public function __construct(array $options, array $config, Output $output, Image $thumbnail, Translate $translate, Themer $themer)
    {
        // Create the widget
        parent::__construct(
            'wpp',
            'WP Popular Posts',
            [
                'classname'     =>  'popular-posts',
                'description'   =>  'The most Popular Posts on your blog.'
            ]
        );

        $this->defaults = $options;
        $this->config = $config;
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
        // Remove widget from Legacy Widget block
        add_filter('widget_types_to_hide_from_legacy_widget_block', [$this, 'remove_from_legacy_widget_block']);
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

        echo "\n" . $before_widget . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        $notice = '';

        if ( is_user_logged_in() && current_user_can('manage_options') ) {
            ob_start();
            ?>
            <style>
                .wpp-notice {
                    margin: 0 0 22px;
                    padding: 18px 22px;
                    background: #fcfcf7;
                    border: #ffff63 4px solid;
                }

                    .wpp-notice p {
                        color: #000 !important;
                    }

                    .wpp-notice p:nth-child(2n) {
                        margin: 0;
                        font-size: 0.85em;
                    }
            </style>
            <div class="wpp-notice">
                <p><strong>Important notice for administrators:</strong> The WP Popular Posts "classic" widget has been removed.</p>
                <p>This widget has reached end-of-life as of version 7.0. Please go to <strong>Appearance > Widgets > [Your Sidebar] > WP Popular Posts</strong> for instructions on migrating your popular posts list to either the WP Popular Posts block or the wpp shortcode.</p>
            </div>
            <?php
            $notice = ob_get_clean() . "\n";
        }

        echo $notice;

        echo "\n" . $after_widget . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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

        return $instance;
    }

    /**
     * Removes the standard widget from the Legacy Widget block.
     *
     * @param   array
     * @return  array
     */
    public function remove_from_legacy_widget_block(array $widget_types)
    {
        $widget_types[] = 'wpp';
        return $widget_types;
    }
}
