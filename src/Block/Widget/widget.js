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
            default: 55
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
        theme: {
            type: 'string',
            default: ''
        },
    },
    supports: {
    },

    edit: WPPWidgetBlockEdit,

    save: () => {
        return null;
    }
});
