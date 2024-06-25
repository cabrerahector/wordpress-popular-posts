<?php
namespace WordPressPopularPosts\Rest;

use WordPressPopularPosts\{ Output, Translate };
use WordPressPopularPosts\Traits\QueriesPosts;

class WidgetEndpoint extends Endpoint {

    use QueriesPosts;

    /**
     * Output object.
     *
     * @var     \WordPressPopularPosts\Output       $output
     * @access  private
     */
    protected $output;

    /**
     * Initializes class.
     *
     * @param   array
     * @param   \WordPressPopularPosts\Translate
     * @param   \WordPressPopularPosts\Output
     */
    public function __construct(array $config, Translate $translate, Output $output)
    {
        $this->config = $config;
        $this->translate = $translate;
        $this->output = $output;
    }

    /**
     * Registers the endpoint(s).
     *
     * @since   5.3.0
     */
    public function register()
    {
        $version = '2';
        $namespace = 'wordpress-popular-posts/v' . $version;

        register_rest_route($namespace, '/widget/', [
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'get_widget_block'],
                'permission_callback' => '__return_true',
                'args'                => $this->get_widget_params(),
            ]
        ]);
    }

    /**
     * Retrieves a popular posts widget for display.
     *
     * @since 5.4.0
     *
     * @param \WP_REST_Request $request Full details about the request.
     * @return \WP_Error|\WP_REST_Response Response object on success, or WP_Error object on failure.
     */
    public function get_widget_block($request)
    {
        $instance = $request->get_params();

        $is_single = $request->get_param('is_single');
        $lang = $request->get_param('lang');

        // Multilang support
        $this->set_lang($lang);

        $popular_posts = $this->maybe_query($instance);

        if ( is_numeric($is_single) && $is_single > 0 ) {
            add_filter('wpp_is_single', function($id) use ($is_single) {
                return $is_single;
            });
        }

        $this->output->set_data($popular_posts->get_posts());
        $this->output->set_public_options($instance);
        $this->output->build_output();

        return [
            'widget' => ( $this->config['tools']['cache']['active'] ? '<!-- cached -->' : '' ) . $this->output->get_output()
        ];
    }

    /**
     * Retrieves the query params for getting a widget instance.
     *
     * @since 4.1.0
     *
     * @return array Query parameters for getting a widget instance.
     */
    public function get_widget_params()
    {
        return [
            'is_single' => [
                'type' => 'integer',
                'default' => null,
                'sanitize_callback' => 'absint'
            ],
            'lang' => [
                'type' => 'string',
                'default' => null,
                'sanitize_callback' => 'sanitize_text_field'
            ],
        ];
    }
}
