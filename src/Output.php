<?php
/**
 * This class formats the HTML output of every popular posts listing.
 *
 *
 * @package    WordPressPopularPosts
 * @author     Hector Cabrera <me@cabrerahector.com>
 */

namespace WordPressPopularPosts;

class Output {

    /**
     * Popular posts data.
     *
     * @since   4.0.0
     * @var     string
     */
    private $data;

    /**
     * HTML output.
     *
     * @since   4.0.0
     * @var     string
     */
    private $output;

    /**
     * Widget / shortcode settings.
     *
     * @since   4.0.0
     * @var     array
     */
    private $public_options = [];

    /**
     * Administrative settings.
     *
     * @since   2.3.3
     * @var     array
     */
    private $admin_options = [];

    /**
     * Default excerpt 'more' string.
     *
     * @since   4.2.1
     * @var     string
     */
    private $more;

    /**
     * Image object
     *
     * @since   4.0.2
     * @var     WordPressPopularPosts\Image
     */
    private $thumbnail;

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
     * Constructor.
     *
     * @since   4.0.0
     * @param   array                           $public_options
     * @param   array                           $admin_options
     * @param   WordPressPopularPosts\Image     $thumbnail
     * @param   WordPressPopularPosts\Translate $translate
     * @param   \WordPressPopularPosts\Themer    $themer
     */
    public function __construct(array $public_options, array $admin_options, Image $thumbnail, Translate $translate, \WordPressPopularPosts\Themer $themer)
    {
        $this->public_options = $public_options;
        $this->admin_options = $admin_options;
        $this->thumbnail = $thumbnail;
        $this->translate = $translate;
        $this->themer = $themer;

        $this->more = '...';

        // Allow customization of the more (...) string
        if ( has_filter('wpp_excerpt_more') )
            $this->more = apply_filters('wpp_excerpt_more', $this->more);
    }

    /**
     * Sets data.
     *
     * @since   5.0.0
     * @param   array
     */
    public function set_data(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Sets public options.
     *
     * @since   5.0.0
     * @param   array
     */
    public function set_public_options(array $public_options = [])
    {
        $this->public_options = Helper::merge_array_r(
            Settings::get('widget_options'),
            $public_options
        );
    }

    /**
     * Output the HTML.
     *
     * @since   4.0.0
     */
    public function output()
    {
        echo $this->get_output();
    }

    /**
     * Return the HTML.
     *
     * @since   4.0.0
     * @return  string
     */
    public function get_output()
    {
        $this->output = "\n" . ( WP_DEBUG ? '<!-- WordPress Popular Posts v' . WPP_VERSION . ( $this->admin_options['tools']['cache']['active'] ? ' - cached' : '' ) . ' -->' : '' ) . "\n" . $this->output;
        return $this->output;
    }

    /**
     * Build the HTML output.
     *
     * @since	4.0.0
     */
    public function build_output()
    {
        // Got some posts, format 'em!
        if ( ! empty($this->data) ) {

            $this->output = '';

            // Allow WP themers / coders access to raw data
            // so they can build their own output
            if ( has_filter('wpp_custom_html') ) {
                $this->output .= apply_filters('wpp_custom_html', $this->data, $this->public_options);
                return;
            }

            // Has user set a title?
            if (
                '' != $this->public_options['title']
                && '' != $this->public_options['markup']['title-start']
                && '' != $this->public_options['markup']['title-end']
            ) {
                $id_base = isset($this->public_options['id_base']) ? $this->public_options['id_base'] : null;
                $this->public_options['title'] = apply_filters('widget_title', $this->public_options['title'], $this->public_options, $id_base);
                $this->public_options['title'] = htmlspecialchars_decode($this->public_options['markup']['title-start'], ENT_QUOTES) . $this->public_options['title'] . htmlspecialchars_decode($this->public_options['markup']['title-end'], ENT_QUOTES);

                $this->output .= $this->public_options['title'] . "\n";
            }

            if (
                isset($this->public_options['theme']['name'])
                && $this->public_options['theme']['name']
            ) {
                $this->output .= '<div class="popular-posts-sr">';

                if ( @file_exists(get_template_directory() . '/wordpress-popular-posts/themes/' . $this->public_options['theme']['name'] . '/style.css') ) {
                    $theme_stylesheet = get_template_directory() . '/wordpress-popular-posts/themes/' . $this->public_options['theme']['name'] . '/style.css';
                } else {
                    $theme_stylesheet = $this->themer->get_theme($this->public_options['theme']['name'])['path'] . '/style.css';
                }

                $theme_css_rules = wp_strip_all_tags(file_get_contents($theme_stylesheet), true);
                $additional_styles = '';

                if ( has_filter('wpp_additional_theme_styles') ) {
                    $additional_styles = wp_strip_all_tags(apply_filters('wpp_additional_theme_styles', '', $this->public_options['theme']['name']), true);

                    if ( $additional_styles )
                        $additional_styles = ' /* additional rules */ ' . $additional_styles;
                }

                $this->output .= '<style>' . $theme_css_rules . $additional_styles . '</style>';
            }

            /* Open HTML wrapper */
            // Output a custom wrapper
            if (
               isset($this->public_options['markup']['custom_html'])
               && $this->public_options['markup']['custom_html']
               && isset($this->public_options['markup']['wpp-start'])
               && isset($this->public_options['markup']['wpp-end'])
            ){
                $this->output .= "\n" . htmlspecialchars_decode($this->public_options['markup']['wpp-start'], ENT_QUOTES) ."\n";
            }
            // Output the default wrapper
            else {

                $classes = "wpp-list";

                if ( $this->public_options['thumbnail']['active'] )
                    $classes .= " wpp-list-with-thumbnails";

                $this->output .= "\n" . "<ul class=\"{$classes}\">" . "\n";

            }

            $position = 0;

            // Format each post
            foreach( $this->data as $post_object ) {
                $position++;
                $this->output .= $this->render_post($post_object, $position);
            }

            /* Close HTML wrapper */
            // Output a custom wrapper
            if (
               isset($this->public_options['markup']['custom_html'])
               && $this->public_options['markup']['custom_html']
               && isset($this->public_options['markup']['wpp-start'])
               && isset($this->public_options['markup']['wpp-end'])
            ){
                $this->output .= "\n" . htmlspecialchars_decode($this->public_options['markup']['wpp-end'], ENT_QUOTES) ."\n";
            }
            // Output default wrapper
            else {
                $this->output .= "</ul>" . "\n";
            }

            if (
                isset($this->public_options['theme']['name'])
                && $this->public_options['theme']['name']
            ) {
                $this->output .= "</div>";
            }

        }
        // Got nothing to show, give 'em the old "Sorry. No data so far." message!
        else {
            $this->output = apply_filters('wpp_no_data', "<p class=\"wpp-no-data\">" . __('Sorry. No data so far.', 'wordpress-popular-posts') . "</p>");
        }
    }

    /**
     * Build the HTML markup for a single post.
     *
     * @since   4.0.0
     * @access  private
     * @param   object   $post_object
     * @param   integer  $position
     * @return  string
     */
    private function render_post(\stdClass $post_object, $position = 1)
    {
        $is_single = $this->is_single();
        $post = '';
        $post_id = $post_object->id;
        $trid = $this->translate->get_object_id(
            $post_object->id,
            get_post_type($post_object->id)
        );

        if ( $post_id != $trid ) {
            $post_id = $trid;
        }

        $is_current_post = ( $is_single && ($is_single == $post_id || $is_single == $post_object->id) ) ? true : false;

        // Permalink
        $permalink = $this->get_permalink($post_object, $post_id);

        // Post title (and title attribute)
        $post_title_attr = esc_attr(wp_strip_all_tags($this->get_title($post_object, $post_id)));
        $post_title = $this->get_title($post_object, $post_id);

        if ( $this->public_options['shorten_title']['active'] ) {
            $length = ( filter_var($this->public_options['shorten_title']['length'], FILTER_VALIDATE_INT) && $this->public_options['shorten_title']['length'] > 0 )
              ? $this->public_options['shorten_title']['length']
              : 25;

            $post_title = Helper::truncate($post_title, $length, $this->public_options['shorten_title']['words'], $this->more);
        }

        // Thumbnail
        $post_thumbnail = $this->get_thumbnail($post_id);

        // Post excerpt
        $post_excerpt = $this->get_excerpt($post_object, $post_id);

        // Post rating
        $post_rating = $this->get_rating($post_object);

        /**
         * Post meta
         */

        // Post date
        $post_date = $this->get_date($post_object);

        // Post taxonomies
        $post_taxonomies = $this->get_taxonomies($post_id);

        // Post author
        $post_author = $this->get_author($post_object, $post_id);

        // Post views count
        $post_views = $this->get_pageviews($post_object);

        // Post comments count
        $post_comments = $this->get_comments($post_object);

        // Post meta
        $meta_arr = $this->get_metadata(
            $post_object,
            $post_id,
            $post_date,
            $post_taxonomies,
            $post_author,
            $post_views,
            $post_comments
        );

        if (
            is_array($meta_arr)
            && ! empty($meta_arr)
            && "views" == $this->public_options['order_by']
        ) {
            $keys = ['views', 'comments', 'author', 'date', 'taxonomy'];
            $new_meta_arr = [];

            foreach($keys as $key) {
                if ( isset($meta_arr[$key]))
                    $new_meta_arr[$key] = $meta_arr[$key];
            }

            if ( ! empty($new_meta_arr) )
                $meta_arr = $new_meta_arr;
        }

        $post_meta_separator = apply_filters('wpp_post_meta_separator', ' | ');
        $post_meta = join($post_meta_separator, $meta_arr);

        $prettify_numbers = apply_filters('wpp_pretiffy_numbers', true);

        // Build custom HTML output
        if ( $this->public_options['markup']['custom_html'] ) {
            $data = [
                'id' => $post_id,
                'title' => '<a href="' . $permalink . '" ' . ($post_title_attr !== $post_title ? 'title="' . $post_title_attr . '" ' : '' ) . 'class="wpp-post-title" target="' . $this->admin_options['tools']['link']['target'] . '">' . $post_title . '</a>',
                'title_attr' => $post_title_attr,
                'summary' => $post_excerpt,
                'stats' => $post_meta,
                'img' => ( ! empty($post_thumbnail) ) ? '<a href="' . $permalink . '" ' . ($post_title_attr !== $post_title ? 'title="' . $post_title_attr . '" ' : '' ) . 'target="' . $this->admin_options['tools']['link']['target'] . '">' . $post_thumbnail . '</a>' : '',
                'img_no_link' => $post_thumbnail,
                'url' => $permalink,
                'text_title' => $post_title,
                'taxonomy' => $post_taxonomies,
                'taxonomy_copy' => isset($meta_arr['taxonomy']) ? $meta_arr['taxonomy'] : null,
                'author' => ( ! empty($post_author) ) ? '<a href="' . get_author_posts_url($post_object->uid != $post_id ? get_post_field('post_author', $post_id) : $post_object->uid ) . '">' . $post_author . '</a>' : '',
                'author_copy' => isset($meta_arr['author']) ? $meta_arr['author'] : null,
                'views' => ( $this->public_options['order_by'] == "views" || $this->public_options['order_by'] == "comments" ) ? ($prettify_numbers ? Helper::prettify_number($post_views) : number_format_i18n($post_views)) : ($prettify_numbers ? Helper::prettify_number($post_views, 2) : number_format_i18n($post_views, 2)),
                'views_copy' => isset($meta_arr['views']) ? $meta_arr['views'] : null,
                'comments' => $prettify_numbers ? Helper::prettify_number($post_comments) : number_format_i18n($post_comments),
                'comments_copy' => isset($meta_arr['comments']) ? $meta_arr['comments'] : null,
                'date' => $post_date,
                'date_copy' => isset($meta_arr['date']) ? $meta_arr['date'] : null,
                'total_items' => count($this->data),
                'item_position' => $position
            ];
            $post = $this->format_content(htmlspecialchars_decode($this->public_options['markup']['post-html'], ENT_QUOTES), $data, $this->public_options['rating']). "\n";
        } // Use the "stock" HTML output
        else {
            $wpp_post_class = [];

            if ( $is_current_post ) {
                $wpp_post_class[] = "current";
            }

            // Allow themers / plugin developer
            // to add custom classes to each post
            $wpp_post_class = apply_filters("wpp_post_class", $wpp_post_class, $post_id);

            $post_thumbnail = ( ! empty($post_thumbnail) )
                ? "<a href=\"{$permalink}\" " . ($post_title_attr !== $post_title ? "title=\"{$post_title_attr}\" " : "") . "target=\"{$this->admin_options['tools']['link']['target']}\">{$post_thumbnail}</a>\n"
                : "";

            $post_excerpt = ( ! empty($post_excerpt) )
                ? " <span class=\"wpp-excerpt\">{$post_excerpt}</span>\n"
                : "";

            $post_meta = ( ! empty($post_meta) )
                ? " <span class=\"wpp-meta post-stats\">{$post_meta}</span>\n"
                : '';

            $post_rating = ( ! empty($post_rating) )
                ? " <span class=\"wpp-rating\">{$post_rating}</span>\n"
                : "";

            $post =
                "<li" . ( ( is_array($wpp_post_class) && ! empty($wpp_post_class) ) ? ' class="' . esc_attr(implode(" ", $wpp_post_class)) . '"' : '') . ">\n"
                . $post_thumbnail
                . "<a href=\"{$permalink}\" " . ($post_title_attr !== $post_title ? "title=\"{$post_title_attr}\" " : "") . "class=\"wpp-post-title\" target=\"{$this->admin_options['tools']['link']['target']}\">{$post_title}</a>\n"
                . $post_excerpt
                . $post_meta
                . $post_rating
                . "</li>\n";
        }

        return apply_filters('wpp_post', $post, $post_object, $this->public_options);
    }

    /**
     * Return the processed post/page title.
     *
     * @since   3.0.0
     * @access  private
     * @param   object   $post_object
     * @param   integer  $post_id
     * @return  string
     */
    private function get_title(\stdClass $post_object, $post_id)
    {
        if ( $post_object->id != $post_id ) {
            $title = get_the_title($post_id);
        } else {
            $title = $post_object->title;
        }

        // Run the_title filter so core/plugin title hooks can
        // be applied to the post title
        $title = apply_filters('the_title', $title, $post_object->id);

        return apply_filters('wpp_the_title', $title, $post_object->id, $post_id);
    }

    /**
     * Return the permalink.
     * 
     * @since   4.0.12
     * @access  private
     * @param   object   $post_object
     * @param   integer  $post_id
     * @return  string
     */
    private function get_permalink(\stdClass $post_object, $post_id) {
        if ( $post_object->id != $post_id ) {
            return get_permalink($post_id);
        }

        return get_permalink($post_object->id);
    }

    /**
     * Return the processed thumbnail.
     *
     * @since   3.0.0
     * @access  private
     * @param   int     $post_id
     * @return  string
     */
    private function get_thumbnail($post_id)
    {
        $thumbnail = '';

        if ( $this->public_options['thumbnail']['active'] ) {
            $thumbnail = $this->thumbnail->get(
                $post_id,
                [
                    $this->public_options['thumbnail']['width'],
                    $this->public_options['thumbnail']['height']
                ],
                $this->admin_options['tools']['thumbnail']['source'],
                $this->public_options['thumbnail']['crop'],
                $this->public_options['thumbnail']['build']
            );
        }

        return $thumbnail;
    }

    /**
     * Return post excerpt.
     *
     * @since   3.0.0
     * @access  private
     * @param   object  $post_object
     * @param   integer $post_id
     * @return  string
     */
    private function get_excerpt(\stdClass $post_object, $post_id)
    {
        $excerpt = '';

        if ( $this->public_options['post-excerpt']['active'] ) {

            if ( $post_object->id != $post_id ) {
                $the_post = get_post($post_id);

                $excerpt = ( empty($the_post->post_excerpt) )
                  ? $the_post->post_content
                  : $the_post->post_excerpt;
            }
            else {
                $excerpt = ( empty($post_object->post_excerpt) )
                  ? $post_object->post_content
                  : $post_object->post_excerpt;
            }

            // remove caption tags
            $excerpt = preg_replace("/\[caption.*\[\/caption\]/", "", $excerpt);

            // remove Flash objects
            $excerpt = preg_replace("/<object[0-9 a-z_?*=\":\-\/\.#\,\\n\\r\\t]+/smi", "", $excerpt);

            // remove iframes
            $excerpt = preg_replace("/<iframe.*?\/iframe>/i", "", $excerpt);

            // remove WP shortcodes
            $excerpt = strip_shortcodes($excerpt);

            // remove style/script tags
            $excerpt = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $excerpt);

            // remove HTML tags if requested
            if ( $this->public_options['post-excerpt']['keep_format'] ) {
                $excerpt = strip_tags($excerpt, '<a><b><i><em><strong>');
            } else {
                $excerpt = strip_tags($excerpt);

                // remove URLs, too
                $excerpt = preg_replace('_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iuS', '', $excerpt);
            }

        }

        // Balance tags, if needed
        if ( '' !== $excerpt ) {

            $excerpt = Helper::truncate($excerpt, $this->public_options['post-excerpt']['length'], $this->public_options['post-excerpt']['words'], $this->more);

            if ( $this->public_options['post-excerpt']['keep_format'] )
                $excerpt = force_balance_tags($excerpt);
        }

        return $excerpt;
    }

    /**
     * Return post rating.
     *
     * @since   3.0.0
     * @access  private
     * @param   object  $post_object
     * @return  string
     */
    private function get_rating(\stdClass $post_object)
    {
        $rating = '';

        if ( function_exists('the_ratings_results') && $this->public_options['rating'] ) {
            $rating = the_ratings_results($post_object->id);
        }

        return $rating;
    }

    /**
     * Get post date.
     *
     * @since   3.0.0
     * @access  private
     * @param   object  $post_object
     * @return  string
     */
    private function get_date(\stdClass $post_object)
    {
        $date = '';

        if ( $this->public_options['stats_tag']['date']['active'] ) {
            $date = ( 'relative' == $this->public_options['stats_tag']['date']['format'] )
                ? sprintf(__('%s ago', 'wordpress-popular-posts'), human_time_diff(strtotime($post_object->date), current_time('timestamp')))
                : date_i18n($this->public_options['stats_tag']['date']['format'], strtotime($post_object->date));
        }

        return $date;
    }

    /**
     * Get post taxonomies.
     *
     * @since   3.0.0
     * @access  private
     * @param   integer $post_id
     * @return  string
     */
    private function get_taxonomies($post_id)
    {
        $post_tax = '';

        if (
            (isset($this->public_options['stats_tag']['category']) && $this->public_options['stats_tag']['category']) 
            || $this->public_options['stats_tag']['taxonomy']['active']
        ) {

            $taxonomy = 'category';

            if (
                $this->public_options['stats_tag']['taxonomy']['active']
                && ! empty($this->public_options['stats_tag']['taxonomy']['name'])
            ) {
                $taxonomy = $this->public_options['stats_tag']['taxonomy']['name'];
            }

            $terms = wp_get_post_terms($post_id, $taxonomy);

            if ( ! is_wp_error($terms) ) {
                // Usage: https://wordpress.stackexchange.com/a/46824
                if ( has_filter('wpp_post_exclude_terms') ) {
                    $args = apply_filters('wpp_post_exclude_terms', []);
                    $terms = wp_list_filter($terms, $args, 'NOT');
                }

                $terms = apply_filters('wpp_post_terms', $terms);

                if (
                    is_array($terms) 
                    && ! empty($terms)
                ) {
                    $taxonomy_separator = apply_filters('wpp_taxonomy_separator', ', ');

                    foreach ($terms as $term) {
                        $term_link = get_term_link($term);

                        if ( is_wp_error($term_link) )
                            continue;

                        $term_link = $this->translate->url($term_link, $this->translate->get_current_language());
                        $post_tax .= "<a href=\"{$term_link}\" class=\"wpp-taxonomy {$taxonomy} {$taxonomy}-{$term->term_id}\">{$term->name}</a>" . $taxonomy_separator;
                    }
                }
            }

            if ( '' != $post_tax )
                $post_tax = rtrim($post_tax, $taxonomy_separator);

        }

        return $post_tax;
    }

    /**
     * Get post author.
     *
     * @since   3.0.0
     * @access  private
     * @param   object  $post_object
     * @param   integer $post_id
     * @return  string
     */
    private function get_author(\stdClass $post_object, $post_id)
    {
        $author = ( $this->public_options['stats_tag']['author'] )
          ? get_the_author_meta('display_name', $post_object->uid != $post_id ? get_post_field('post_author', $post_id) : $post_object->uid)
          : "";

        return $author;
    }

    /**
     * Return post views count.
     *
     * @since   3.0.0
     * @access  private
     * @param   object  $post_object
     * @return  int|float
     */
    private function get_pageviews(\stdClass $post_object)
    {
        $pageviews = 0;

        if (
            (
                $this->public_options['order_by'] == "views"
                || $this->public_options['order_by'] == "avg"
                || $this->public_options['stats_tag']['views']
            )
            && ( isset($post_object->pageviews) || isset($post_object->avg_views) )
        ) {
            $pageviews = ( $this->public_options['order_by'] == "views" || $this->public_options['order_by'] == "comments" )
            ? $post_object->pageviews
            : $post_object->avg_views;
        }

        return $pageviews;
    }

    /**
     * Return post comment count.
     *
     * @since   3.0.0
     * @access  private
     * @param   object  $post_object
     * @return  int
     */
    private function get_comments(\stdClass $post_object)
    {
        $comments = ( ( $this->public_options['order_by'] == "comments" || $this->public_options['stats_tag']['comment_count'] ) && isset($post_object->comment_count) )
          ? $post_object->comment_count
          : 0;

        return $comments;
    }

    /**
     * Return post metadata.
     *
     * @since   3.0.0
     * @access  private
     * @param   object  $post_object
     * @param   integer $post_id
     * @return  array
     */
    //private function get_metadata(\stdClass $post_object, $post_id)
    private function get_metadata(\stdClass $post_object, $post_id, $date, $post_tax, $author, $pageviews, $comments)
    {
        $stats = [];

        $prettify_numbers = apply_filters('wpp_pretiffy_numbers', true);

        // comments
        if ( $this->public_options['stats_tag']['comment_count'] ) {
            $comments_text = sprintf(
                _n('%s comment', '%s comments', $comments, 'wordpress-popular-posts'),
                $prettify_numbers ? Helper::prettify_number($comments) : number_format_i18n($comments)
            );

            $stats['comments'] = '<span class="wpp-comments">' . $comments_text . '</span>';
        }

        // views
        if ( $this->public_options['stats_tag']['views'] ) {
            if ( $this->public_options['order_by'] == 'avg' ) {
                $views_text = sprintf(
                    _n('%s view per day', '%s views per day', $pageviews, 'wordpress-popular-posts'),
                    $prettify_numbers ? Helper::prettify_number($pageviews, 2) : number_format_i18n($pageviews, (fmod($pageviews, 1) !== 0.0 ? 2 : 0))
                );
            }
            else {
                $views_text = sprintf(
                    _n('%s view', '%s views', $pageviews, 'wordpress-popular-posts'),
                    $prettify_numbers ? Helper::prettify_number($pageviews) : number_format_i18n($pageviews)
                );
            }

            $stats['views'] = '<span class="wpp-views">' . $views_text . "</span>";
        }

        // author
        if ( $this->public_options['stats_tag']['author'] ) {
            $author_url = get_author_posts_url($post_object->uid != $post_id ? get_post_field('post_author', $post_id) : $post_object->uid);
            $display_name = '<a href="' . $this->translate->url($author_url, $this->translate->get_current_language()) . '">' . $author . '</a>';
            $stats['author'] = '<span class="wpp-author">' . sprintf(__('by %s', 'wordpress-popular-posts'), $display_name) . '</span>';
        }

        // date
        if ( $this->public_options['stats_tag']['date']['active'] ) {
            $stats['date'] = '<span class="wpp-date">' . ( 'relative' == $this->public_options['stats_tag']['date']['format'] ? sprintf(__('posted %s', 'wordpress-popular-posts'), $date) : sprintf(__('posted on %s', 'wordpress-popular-posts'), $date) ) . '</span>';
        }

        // taxonomy
        if ( ($this->public_options['stats_tag']['category'] || $this->public_options['stats_tag']['taxonomy']['active']) && $post_tax != '' ) {
            $stats['taxonomy'] = '<span class="wpp-category">' . sprintf(__('under %s', 'wordpress-popular-posts'), $post_tax) . '</span>';
        }

        return $stats;
    }

    /**
     * Parse content tags.
     *
     * @since   1.4.6
     * @access  private
     * @param   string  HTML string with content tags
     * @param   array   Post data
     * @param   bool    Used to display post rating (if functionality is available)
     * @return  string
     */
    private function format_content($string, $data, $rating) {

        if ( empty($string) || ( empty($data) || ! is_array($data) ) )
            return false;

        $params = [];
        $pattern = '/\{(pid|excerpt|summary|meta|stats|title|title_attr|image|thumb|thumb_img|thumb_url|rating|score|url|text_title|author|author_copy|taxonomy|taxonomy_copy|category|category_copy|views|views_copy|comments|comments_copy|date|date_copy|total_items|item_position)\}/i';
        preg_match_all($pattern, $string, $matches);

        array_map('strtolower', $matches[0]);

        if ( in_array("{pid}", $matches[0]) ) {
            $string = str_replace("{pid}", $data['id'], $string);
        }

        if ( in_array("{title}", $matches[0]) ) {
            $string = str_replace("{title}", $data['title'], $string);
        }

        if ( in_array("{title_attr}", $matches[0]) ) {
            $string = str_replace("{title_attr}", $data['title_attr'], $string);
        }

        if ( in_array("{meta}", $matches[0]) || in_array("{stats}", $matches[0]) ) {
            $string = str_replace(["{meta}", "{stats}"], $data['stats'], $string);
        }

        if ( in_array("{excerpt}", $matches[0]) || in_array("{summary}", $matches[0]) ) {
            $string = str_replace(["{excerpt}", "{summary}"], $data['summary'], $string);
        }

        if ( in_array("{image}", $matches[0]) || in_array("{thumb}", $matches[0]) ) {
            $string = str_replace(["{image}", "{thumb}"], $data['img'], $string);
        }

        if ( in_array("{thumb_img}", $matches[0]) ) {
            $string = str_replace("{thumb_img}", $data['img_no_link'], $string);
        }

        if ( in_array("{thumb_url}", $matches[0]) && ! empty($data['img_no_link']) ) {
            $dom = new \DOMDocument;

            if ( $dom->loadHTML($data['img_no_link']) ) {
                $img_tag = $dom->getElementsByTagName('img');

                if ( $img_tag->length ) {
                    foreach( $img_tag as $node ) {
                        if ( $node->hasAttribute('src') || $node->hasAttribute('data-img-src') ) {
                            $src = $node->hasAttribute('src') ? $node->getAttribute('src') : $node->getAttribute('data-img-src');
                            $string = str_replace("{thumb_url}", $src, $string);
                        }
                    }
                }
            }
        }

        // WP-PostRatings check
        if ( $rating ) {
            if ( function_exists('the_ratings_results') && in_array("{rating}", $matches[0]) ) {
                $string = str_replace("{rating}", the_ratings_results($data['id']), $string);
            }

            if ( function_exists('expand_ratings_template') && in_array("{score}", $matches[0]) ) {
                $string = str_replace("{score}", expand_ratings_template('%RATINGS_SCORE%', $data['id']), $string);
                // removing the redundant plus sign
                $string = str_replace('+', '', $string);
            }
        }

        if ( in_array("{url}", $matches[0]) ) {
            $string = str_replace("{url}", $data['url'], $string);
        }

        if ( in_array("{text_title}", $matches[0]) ) {
            $string = str_replace("{text_title}", $data['text_title'], $string);
        }

        if ( in_array("{author}", $matches[0]) ) {
            $string = str_replace("{author}", $data['author'], $string);
        }

        if ( in_array("{author_copy}", $matches[0]) ) {
            $string = str_replace("{author_copy}", $data['author_copy'], $string);
        }

        if ( in_array("{taxonomy}", $matches[0]) || in_array("{category}", $matches[0]) ) {
            $string = str_replace(["{taxonomy}", "{category}"], $data['taxonomy'], $string);
        }

        if ( in_array("{taxonomy_copy}", $matches[0]) || in_array("{category_copy}", $matches[0]) ) {
            $string = str_replace(["{taxonomy_copy}", "{category_copy}"], $data['taxonomy_copy'], $string);
        }

        if ( in_array("{views}", $matches[0]) ) {
            $string = str_replace("{views}", $data['views'], $string);
        }

        if ( in_array("{views_copy}", $matches[0]) ) {
            $string = str_replace("{views_copy}", $data['views_copy'], $string);
        }

        if ( in_array("{comments}", $matches[0]) ) {
            $string = str_replace("{comments}", $data['comments'], $string);
        }

        if ( in_array("{comments_copy}", $matches[0]) ) {
            $string = str_replace("{comments_copy}", $data['comments_copy'], $string);
        }

        if ( in_array("{date}", $matches[0]) ) {
            $string = str_replace("{date}", $data['date'], $string);
        }

        if ( in_array("{date_copy}", $matches[0]) ) {
            $string = str_replace("{date_copy}", $data['date_copy'], $string);
        }

        if ( in_array("{total_items}", $matches[0]) ) {
            $string = str_replace("{total_items}", $data['total_items'], $string);
        }

        if ( in_array("{item_position}", $matches[0]) ) {
            $string = str_replace("{item_position}", $data['item_position'], $string);
        }

        return apply_filters("wpp_parse_custom_content_tags", $string, $data['id']);
    }

    /**
     * Checks whether we're currently seeing a single post/page/CPT.
     *
     * @since   5.0.0
     * @return  int
     */
    public function is_single()
    {
        return apply_filters('wpp_is_single', Helper::is_single());
    }
}
