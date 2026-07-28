const { Fragment } = wp.element;
const { CheckboxControl, SelectControl, TextareaControl } = wp.components;
const { __ } = wp.i18n;

export function HTMLMarkupFields({ attributes, setAttributes, themes }) {
    const {
        display_post_thumbnail,
        custom_html,
        header_start,
        header_end,
        wpp_start,
        wpp_end,
        post_html,
        theme
    } = attributes;

    const defaultHTML = {
        theme: '',
        wpp_start: `<ul class="wpp-list${display_post_thumbnail ? ' wpp-list-with-thumbnails' : ''}">`,
        post_html: '<li class="{current_class}">{thumb} {title} <span class="wpp-meta post-stats">{stats}</span></li>',
        wpp_end: '</ul>'
    };

    const labelUseCustomHTML = __('Use custom HTML Markup', 'wordpress-popular-posts');
    const labelBeforeTitle = __('Before title', 'wordpress-popular-posts');
    const labelAfterTitle = __('After title', 'wordpress-popular-posts');
    const labelBeforePosts = __('Before popular posts', 'wordpress-popular-posts');
    const labelAfterPosts = __('After popular posts', 'wordpress-popular-posts');
    const labelPostHTMLMarkup = __('Post HTML markup', 'wordpress-popular-posts');
    const labelContentTagsList = __('Content Tags List', 'wordpress-popular-posts');
    const labelTheme = __('Theme', 'wordpress-popular-posts');

    const themeOptions = [
        { label: __('None', 'wordpress-popular-posts'), value: '' }
    ];

    if ( themes ) {
        for ( const t in themes ) {
            if ( themes.hasOwnProperty(t) ) {
                themeOptions.push({ label: themes[t].json.name, value: t });
            }
        }
    }

    const onUseCustomHTMLChange = (isChecked) => {
        setAttributes({
            custom_html: isChecked,
            ...(!isChecked && defaultHTML)
        });
    };

    const onBeforeTitleChange = (value) => {
        setAttributes({ header_start: value });
    };

    const onAfterTitleChange = (value) => {
        setAttributes({ header_end: value });
    };

    const onBeforePostsChange = (value) => {
        setAttributes({ wpp_start: value });
    };

    const onAfterPostsChange = (value) => {
        setAttributes({ wpp_end: value });
    };

    const onPostHTMLMarkupChange = (value) => {
        setAttributes({ post_html: value });
    };

    const onThemeChange = (value) => {
        if ( themes && typeof themes[value] !== 'undefined' ) {
            const config = themes[value].json.config;

            setAttributes({
                shorten_title: config.shorten_title.active,
                title_length: config.shorten_title.length,
                title_by_words: config.shorten_title.words ? 1 : 0,
                display_post_excerpt: config['post-excerpt'].active,
                excerpt_format: config['post-excerpt'].format,
                excerpt_length: config['post-excerpt'].length,
                excerpt_by_words: config['post-excerpt'].words ? 1 : 0,
                display_post_thumbnail: config.thumbnail.active,
                thumbnail_build: config.thumbnail.build,
                thumbnail_width: config.thumbnail.width,
                thumbnail_height: config.thumbnail.height,
                stats_comments: config.stats_tag.comment_count,
                stats_views: config.stats_tag.views,
                stats_author: config.stats_tag.author,
                stats_date: config.stats_tag.date.active,
                stats_date_format: config.stats_tag.date.format,
                stats_taxonomy: config.stats_tag.taxonomy.active,
                taxonomy: config.stats_tag.taxonomy.name,
                custom_html: true,
                wpp_start: config.markup['wpp-start'],
                wpp_end: config.markup['wpp-end'],
                post_html: config.markup['post-html'],
                theme: value
            });
        } else {
            setAttributes(defaultHTML);
        }
    };

    return <Fragment>
        <p className='not-a-legend'><strong>{__('HTML Markup settings', 'wordpress-popular-posts')}</strong> <small>(<a href="https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#how-can-i-use-my-own-html-markup-with-your-plugin" target="_blank">{__('What is this?', 'wordpress-popular-posts')}</a>)</small></p>
        <CheckboxControl
            label={labelUseCustomHTML}
            checked={custom_html}
            onChange={onUseCustomHTMLChange}
        />
        { custom_html &&
            <div className='option-subset'>
                <TextareaControl
                    rows="1"
                    label={labelBeforeTitle}
                    value={header_start}
                    onChange={onBeforeTitleChange}
                />
                <TextareaControl
                    rows="1"
                    label={labelAfterTitle}
                    value={header_end}
                    onChange={onAfterTitleChange}
                />
                <TextareaControl
                    rows="1"
                    label={labelBeforePosts}
                    value={wpp_start}
                    onChange={onBeforePostsChange}
                />
                <TextareaControl
                    rows="1"
                    label={labelAfterPosts}
                    value={wpp_end}
                    onChange={onAfterPostsChange}
                />
                <TextareaControl
                    label={labelPostHTMLMarkup}
                    value={post_html}
                    onChange={onPostHTMLMarkupChange}
                />
                <small><a href="https://github.com/cabrerahector/wordpress-popular-posts/wiki/2.-Template-tags#content-tags" target="_blank">{labelContentTagsList}</a></small>
            </div>
        }
        <SelectControl
            label={labelTheme}
            value={theme}
            options={themeOptions}
            onChange={onThemeChange}
        />
    </Fragment>;
}