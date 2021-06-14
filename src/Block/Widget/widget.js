import icons from '../icons';
import { WPPWidgetBlockEdit } from './edit';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('wordpress-popular-posts/widget', {
    title: 'WordPress Popular Posts',
    category: 'widgets',
    icon: icons.flame,
    description: __('A highly customizable block that displays your most popular posts.', 'wordpress-popular-posts'),
    keywords: ['popular', 'posts', 'trending', 'popularity'],

    attributes: {
        _editMode: {
            type: 'boolean',
            default: true
        },
        title: {
            type: 'string',
        },
        limit: {
            type: 'number',
            default: 10
        },
        offset: {
            type: 'number',
            default: 0
        },
        order_by: {
            type: 'string',
            default: 'views'
        },
        range: {
            type: 'string',
            default: 'last24hours'
        },
        time_quantity: {
            type: 'number',
            default: 24
        },
        time_unit: {
            type: 'string',
            default: 'hour'
        },
        freshness: {
            type: 'boolean',
            default: false
        },
        /* filters */
        post_type: {
            type: 'string',
            default: 'post'
        },
        pid: {
            type: 'string',
            default: ''
        },
        author: {
            type: 'string',
            default: ''
        },
        tax: {
            type: 'string',
            default: ''
        },
        term_id: {
            type: 'string',
            default: ''
        },
        /* post settings */
        shorten_title: {
            type: 'boolean',
            default: false
        },
        title_length: {
            type: 'number',
            default: 0
        },
        title_by_words: {
            type: 'number',
            default: 0
        },
        display_post_excerpt: {
            type: 'boolean',
            default: false
        },
        excerpt_format: {
            type: 'boolean',
            default: false
        },
        excerpt_length: {
            type: 'number',
            default: 0
        },
        excerpt_by_words: {
            type: 'number',
            default: 0
        },
        display_post_thumbnail: {
            type: 'boolean',
            default: false
        },
        thumbnail_width: {
            type: 'number',
            default: 0
        },
        thumbnail_height: {
            type: 'number',
            default: 0
        },
        thumbnail_build: {
            type: 'string',
            default: 'manual'
        },
        thumbnail_size: {
            type: 'string',
            default: ''
        },
        /* stats tag settings */
        stats_comments: {
            type: 'boolean',
            default: false
        },
        stats_views: {
            type: 'boolean',
            default: true
        },
        stats_author: {
            type: 'boolean',
            default: false
        },
        stats_date: {
            type: 'boolean',
            default: false
        },
        stats_date_format: {
            type: 'string',
            default: 'F j, Y'
        },
        stats_taxonomy: {
            type: 'boolean',
            default: false
        },
        taxonomy: {
            type: 'string',
            default: ''
        },
        /* HTML markup settings */
        custom_html: {
            type: 'boolean',
            default: false
        },
        header_start: {
            type: 'string',
            default: '<h2>'
        },
        header_end: {
            type: 'string',
            default: '</h2>'
        },
        wpp_start: {
            type: 'string',
            default: '<ul class="wpp-list">'
        },
        wpp_end: {
            type: 'string',
            default: '</ul>'
        },
        post_html: {
            type: 'string',
            default: '<li>{thumb} {title} <span class="wpp-meta post-stats">{stats}</span></li>'
        },
        theme: {
            type: 'string',
            default: ''
        },
    },
    supports: {
        anchor: true,
        align: true,
        html: false
    },
    example: {
        attributes: {
            _editMode: false,
            title: 'Popular Posts',
            limit: 3,
            range: 'last7days',
            display_post_excerpt: true,
            excerpt_length: 75,
            display_post_thumbnail: true,
            thumbnail_width: 75,
            thumbnail_height: 75,
            stats_views: false,
            stats_taxonomy: true,
            custom_html: true,
            wpp_start: '<ul class="wpp-list wpp-cards">',
            post_html: '<li>{thumb_img} <div class="wpp-item-data"><div class="taxonomies">{taxonomy}</div>{title} <p class="wpp-excerpt">{excerpt}</p></div></li>',
            theme: 'cards'
        }
    },

    edit: WPPWidgetBlockEdit,

    save: () => {
        return null;
    }
});
