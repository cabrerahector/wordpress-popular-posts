<p><strong>Important notice for administrators:</strong> The WP Popular Posts "classic" widget has been removed.</p>
<p>This widget has reached end-of-life as of version 7.0. Please follow the <a href="https://cabrerahector.com/wordpress/migrating-from-the-classic-popular-posts-widget/" rel="nofollow">Migration Guide</a> to switch to either the <a href="https://github.com/cabrerahector/wordpress-popular-posts/wiki/1.-Using-WPP-on-posts-&-pages#the-wordpress-popular-posts-block" rel="nofollow">WP Popular Posts block</a> or the <a href="https://github.com/cabrerahector/wordpress-popular-posts/wiki/1.-Using-WPP-on-posts-&-pages#the-wpp-shortcode" rel="nofollow">wpp shortcode</a>.</p>
<p>If you decide on migrating to the <a href="https://github.com/cabrerahector/wordpress-popular-posts/wiki/1.-Using-WPP-on-posts-&-pages#the-wpp-shortcode" rel="nofollow">wpp shortcode</a>, the one below has the same settings as your classic widget:</p>

<?php
// possible values
$time_units = ['minute', 'hour', 'day', 'week', 'month'];
$range_values = ['daily', 'last24hours', 'weekly', 'last7days', 'monthly', 'last30days', 'all', 'custom'];
$order_by_values = ['comments', 'views', 'avg'];

$wpp_shortcode = '[wpp';

if ( $instance['title'] ) {
    $wpp_shortcode .= " header='" . strip_tags($instance['title']) . "'"; // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- We want the behavior of strip_tags;
}

$wpp_shortcode .= " post_type='" . (empty($instance['post_type']) ? 'post' : esc_html($instance['post_type'])) . "'";

$wpp_shortcode .= " limit=" . (( \WordPressPopularPosts\Helper::is_number($instance['limit']) && $instance['limit'] > 0 ) ? $instance['limit'] : 10);
$wpp_shortcode .= " range='" . (( in_array($instance['range'], $range_values) ) ? $instance['range'] : 'last24hours') . "'";

if ( 'custom' === $instance['range'] ) {
    $wpp_shortcode .= " time_quantity=" . (( ! empty($instance['time_quantity']) && \WordPressPopularPosts\Helper::is_number($instance['time_quantity']) && $instance['time_quantity'] > 0 ) ? $instance['time_quantity'] : 24);
    $wpp_shortcode .= " time_unit='" . (( in_array($instance['time_unit'], $time_units) ) ? $instance['time_unit'] : 'hour') . "'";
}

if ( $instance['freshness'] ) {
    $wpp_shortcode .= " freshness=1";
}

$wpp_shortcode .= " order_by='" . (( in_array($instance['order_by'], $order_by_values) ) ? $instance['order_by'] : 'views') . "'";

if ( $instance['pid'] ) {
    $pid = rtrim(preg_replace('|[^0-9,]|', '', $instance['pid']), ',');

    if ( $pid ) {
        $IDs_to_exclude = array_filter(explode(',', $pid), 'is_numeric');

        if ( $IDs_to_exclude ) {
            $wpp_shortcode .= " pid='" . implode(',', $IDs_to_exclude) . "'";
        }
    }
}

if ( $instance['author'] ) {
    $author = rtrim(preg_replace('|[^0-9,]|', '', $instance['author']), ',');

    if ( $author ) {
        $IDs_to_include = array_filter(explode(',', $author), 'is_numeric');

        if ( $IDs_to_include ) {
            $wpp_shortcode .= " author='" . implode(',', $IDs_to_include) . "'";
        }
    }
}

if ( $instance['cat'] ) {
    $cat = rtrim(preg_replace('|[^0-9,-]|', '', $instance['cat']), ',');

    if ( $cat ) {
        $cat_IDs = array_filter(explode(',', $cat), 'is_numeric');

        if ( $cat_IDs ) {
            $wpp_shortcode .= " cat='" . implode(',', $cat_IDs) . "'";
        }
    }
}
elseif ( $instance['term_id'] ) {
    $term_id = rtrim(preg_replace('|[^0-9,;-]|', '', $instance['term_id']), ',');

    if ( $term_id ) {
        $term_id_chunks = explode(';', $term_id);

        foreach( $term_id_chunks as $index => $chunk ) {
            $term_id_chunks[$index] = array_filter(explode(',', $chunk), 'is_numeric');
            $term_id_chunks[$index] = implode(',', $term_id_chunks[$index]);
        }

        $term_id_chunks = array_filter($term_id_chunks);

        if ( $term_id_chunks ) {
            $term_id_chunks = implode(';', $term_id_chunks);

            $wpp_shortcode .= " term_id='" . $term_id_chunks . "'";

            if ( $instance['taxonomy'] ) {
                $taxonomy_slugs = array_map('sanitize_title', explode(';', $instance['taxonomy']));
                $wpp_shortcode .= " taxonomy='" . implode(';', $taxonomy_slugs) . "'";
            } else {
                $wpp_shortcode .= " taxonomy='category'";
            }
        }
    }
}

if (
    $instance['shorten_title']['active']
    && \WordPressPopularPosts\Helper::is_number($instance['shorten_title']['length'])
    && $instance['shorten_title']['length'] > 0
) {
    $wpp_shortcode .= " title_length=" . $instance['shorten_title']['length'];

    if ( $instance['shorten_title']['words'] ) {
        $wpp_shortcode .= " title_by_words=1";
    }
}

if (
    $instance['post-excerpt']['active']
    && \WordPressPopularPosts\Helper::is_number($instance['post-excerpt']['length'])
    && $instance['post-excerpt']['length'] > 0
) {
    $wpp_shortcode .= " excerpt_length=" . $instance['post-excerpt']['length'];

    if ( $instance['post-excerpt']['words'] ) {
        $wpp_shortcode .= " excerpt_by_words=1";
    }

    if ( $instance['post-excerpt']['keep_format'] ) {
        $wpp_shortcode .= " excerpt_format=1";
    }
}

if (
    $instance['thumbnail']['active']
    && \WordPressPopularPosts\Helper::is_number($instance['thumbnail']['width'])
    && $instance['thumbnail']['width'] > 0
    && \WordPressPopularPosts\Helper::is_number($instance['thumbnail']['height'])
    && $instance['thumbnail']['height'] > 0
) {
    $wpp_shortcode .= " thumbnail_width=" . $instance['thumbnail']['width'];
    $wpp_shortcode .= " thumbnail_height=" . $instance['thumbnail']['height'];

    if ( 'predefined' === $instance['thumbnail']['build'] ) {
        $wpp_shortcode .= " thumbnail_build='predefined'";
    }
}

if ( $instance['rating'] ) {
    $wpp_shortcode .= " rating=1";
}

if ( $instance['stats_tag']['comment_count'] ) {
    $wpp_shortcode .= " stats_comments=1";
}

if ( ! $instance['stats_tag']['views'] ) {
    $wpp_shortcode .= " stats_views=0";
}

if ( $instance['stats_tag']['author'] ) {
    $wpp_shortcode .= " stats_author=1";
}

if ( $instance['stats_tag']['date']['active'] ) {
    $wpp_shortcode .= " stats_date=1";
    $wpp_shortcode .= " stats_date_format='" . esc_html($instance['stats_tag']['date']['format']) . "'";
}

if ( $instance['stats_tag']['taxonomy']['active'] ) {
    $wpp_shortcode .= " stats_taxonomy=1";
}

if ( $instance['markup']['custom_html'] ) {
    $wpp_shortcode .= " header_start='" . \WordPressPopularPosts\Helper::sanitize_html($instance['markup']['title-start'], $instance) . "'";
    $wpp_shortcode .= " header_end='" . \WordPressPopularPosts\Helper::sanitize_html($instance['markup']['title-end'], $instance) . "'";
    $wpp_shortcode .= " wpp_start='" . \WordPressPopularPosts\Helper::sanitize_html($instance['markup']['wpp-start'], $instance) . "'";
    $wpp_shortcode .= " wpp_end='" . \WordPressPopularPosts\Helper::sanitize_html($instance['markup']['wpp-end'], $instance) . "'";
    $wpp_shortcode .= " post_html='" . \WordPressPopularPosts\Helper::sanitize_html($instance['markup']['post-html'], $instance) . "'";
}

if ( $instance['theme']['name'] ) {
    // On the new Widgets screen $new_instance['theme'] is
    // an array for some reason, let's grab the theme name
    // from the array and move on
    if ( is_array($instance['theme']['name']) ) {
        $instance['theme']['name'] = $instance['theme']['name']['name'];
    }

    $wpp_shortcode .= " theme='" . sanitize_title($instance['theme']['name']) . "'";
}

$wpp_shortcode .= ']';

echo htmlentities($wpp_shortcode, ENT_NOQUOTES, 'UTF-8') . '<br /><br />';
