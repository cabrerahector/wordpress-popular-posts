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
