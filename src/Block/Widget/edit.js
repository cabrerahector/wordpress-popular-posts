import { sanitize_text_field } from '../utils';

const { ServerSideRender } = wp.editor;
const { Component, Fragment } = wp.element;
const { BlockControls } = wp.blockEditor;
const { Button, CheckboxControl, Disabled, SelectControl, Spinner, TextareaControl, TextControl, Toolbar } = wp.components;
const { __ } = wp.i18n;
const endpoint = 'wordpress-popular-posts/v1';

export class WPPWidgetBlockEdit extends Component
{
    constructor(props)
    {
        super(props);

        this.state = {
            error: null,
            editMode: true,
            themes: null,
            imgSizes: null,
            taxonomies: null,
            loading: true
        }
    }

    componentDidMount()
    {
        const { attributes } = this.props;

        this.getThemes();
        this.getImageSizes();
        this.getTaxonomies();

        this.setState({ editMode: attributes._editMode, loading: false });
    }

    getThemes()
    {
        wp.apiFetch({ path: endpoint + '/themes' })
        .then(
            ( themes ) => {
                this.setState({
                    themes
                });
            },
            ( error ) => {
                this.setState({
                    error,
                    themes: null
                });
            }
        );
    }

    getImageSizes()
    {
        wp.apiFetch({ path: endpoint + '/thumbnails' })
        .then(
            ( imgSizes ) => {
                this.setState({
                    imgSizes
                });
            },
            ( error ) => {
                this.setState({
                    error,
                    imgSizes: null
                });
            }
        );
    }

    getTaxonomies()
    {
        wp.apiFetch({ path: endpoint + '/taxonomies' })
        .then(
            ( taxonomies ) => {
                this.setState({
                    taxonomies
                });
            },
            ( error ) => {
                this.setState({
                    error,
                    taxonomies: null
                });
            }
        );
    }

    getBlockControls()
    {
        const { attributes, setAttributes } = this.props;
        const _self = this;

        function onPreviewChange()
        {
            let editMode = ! _self.state.editMode;
            _self.setState({ editMode: editMode });
            setAttributes({ _editMode: editMode });
        }

        return (
            <BlockControls>
                <Toolbar>
                    <Button
                        label={ this.state.editMode ? __('Preview', 'wordpress-popular-posts') : __('Preview', 'wordpress-popular-posts') }
                        icon={ this.state.editMode ? "format-image" : "edit" }
                        onClick={onPreviewChange}
                    />
                </Toolbar>
            </BlockControls>
        );
    }

    render()
    {
        if ( this.state.loading && ! this.state.taxonomies && ! this.state.themes && ! this.state.imgSizes )
            return <Spinner />;

        const { isSelected, className, attributes, setAttributes } = this.props;
        const _self = this;

        function onTitleChange(value)
        {
            setAttributes({ title: sanitize_text_field(value) });
        }

        function onLimitChange(value)
        {
            let limit = Number.isInteger(Number(value)) && Number(value) > 0 ? value : 10;
            setAttributes({ limit: Number(limit) });
        }

        function onOrderByChange(value)
        {
            setAttributes({ order_by: value });
        }

        function onTimeRangeChange(value)
        {
            setAttributes({ range: value });
        }

        function onTimeQuantityChange(value) {
            let qty = Number.isInteger(Number(value)) && Number(value) > 0 ? value : 24;
            setAttributes({ time_quantity: Number(qty) });
        }

        function onTimeUnitChange(value) {
            setAttributes({ time_unit: value });
        }

        function onFreshnessChange(value)
        {
            setAttributes({ freshness: value });
        }

        function onPostTypeChange(value)
        {
            setAttributes({ post_type: sanitize_text_field(value) });
        }

        function onPostIDExcludeChange(value)
        {
            //let new_value = value.replace(/[^0-9-\,]/, '');
            let new_value = value.replace(/[^0-9\,]/, '');
            setAttributes({ pid: new_value });
        }

        function onAuthorChange(value)
        {
            let new_value = value.replace(/[^0-9\,]/, '');
            setAttributes({ pid: new_value });
        }

        function onShortenTitleChange(value) {
            if ( false == value ) 
                setAttributes({ title_length: 0, title_by_words: 0, shorten_title: value });
            else
                setAttributes({ shorten_title: value, title_length: 25 });
        }

        function onTitleLengthChange(value)
        {
            let length = Number.isInteger(Number(value)) && Number(value) >= 0 ? value : 0;
            setAttributes({ title_length: Number(length) });
        }

        function onDisplayExcerptChange(value) {
            if ( false == value )
                setAttributes({ excerpt_length: 0, excerpt_by_words: 0, display_post_excerpt: value });
            else
                setAttributes({ display_post_excerpt: value, excerpt_length: 55 });
        }

        function onExcerptLengthChange(value)
        {
            let length = Number.isInteger(Number(value)) && Number(value) >= 0 ? value : 0;
            setAttributes({ excerpt_length: Number(length) });
        }

        function onDisplayThumbnailChange(value) {
            if ( false == value )
                setAttributes({ thumbnail_width: 0, thumbnail_height: 0, display_post_thumbnail: value });
            else
                setAttributes({ thumbnail_width: 75, thumbnail_height: 75, display_post_thumbnail: value });
        }

        function onThumbnailWidthChange(value)
        {
            let width = Number.isInteger(Number(value)) && Number(value) >= 0 ? value : 0;
            setAttributes({ thumbnail_width: Number(width) });
        }

        function onThumbnailHeightChange(value)
        {
            let height = Number.isInteger(Number(value)) && Number(value) >= 0 ? value : 0;
            setAttributes({ thumbnail_height: Number(height) });
        }

        function onThumbnailBuildChange(value)
        {
            if ( 'predefined' == value ) {
                let fallback = 0;

                setAttributes({
                    thumbnail_width: _self.state.imgSizes[sizes[fallback].value].width,
                    thumbnail_height: _self.state.imgSizes[sizes[fallback].value].height,
                    thumbnail_size: sizes[fallback].value
                });
            }
            setAttributes({ thumbnail_build: value });
        }

        function onThumbnailSizeChange(value) {
            setAttributes({
                thumbnail_width: _self.state.imgSizes[value].width,
                thumbnail_height: _self.state.imgSizes[value].height,
                thumbnail_size: value
            });
        }

        function onThemeChange(value)
        {
            if ( 'undefined' != typeof _self.state.themes[value] ) {
                let config = _self.state.themes[value].json.config;

                setAttributes({
                    shorten_title: config.shorten_title.active,
                    title_length: config.shorten_title.title_length,
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
                setAttributes({ theme: value });
            }
        }

        let classes = className;
        classes += this.state.editMode ? ' in-edit-mode' : '';
        classes += isSelected ? ' is-selected' : '';

        let themes = [
            {
                label: __('None', 'wordpress-popular-posts'),
                value: ''
            },
        ];

        if ( this.state.themes ) {
            for( const theme in this.state.themes ) {
                themes.push(
                    {
                        label: this.state.themes[theme].json.name,
                        value: theme
                    },
                );
            }
        }

        let sizes = [];

        if ( this.state.imgSizes ) {
            for( const size in this.state.imgSizes ) {
                sizes.push(
                    {
                        label: size,
                        value: size
                    },
                );
            }
        }

        let taxonomies = [];

        if ( this.state.taxonomies ) {
            for( const tax in this.state.taxonomies ) {
                taxonomies.push(
                    {
                        label: this.state.taxonomies[tax].labels.singular_name + ' (' + this.state.taxonomies[tax].name + ')',
                        value: this.state.taxonomies[tax].name
                    },
                );
            }
        }

        return ([
            this.getBlockControls(),
            <div className={classes}>
                { this.state.editMode &&
                    <Fragment>
                        <TextControl
                            label={__('Title', 'wordpress-popular-posts')}
                            value={attributes.title}
                            onChange={onTitleChange}
                        />
                        <TextControl
                            label={__('Limit', 'wordpress-popular-posts')}
                            value={attributes.limit}
                            onChange={onLimitChange}
                        />
                        <SelectControl
                            label={__('Sort posts by', 'wordpress-popular-posts')}
                            value={attributes.order_by}
                            options={[
                                {label: __('Total views', 'wordpress-popular-posts'), value: 'views'},
                                {label: __('Comments', 'wordpress-popular-posts'), value: 'comments'}
                            ]}
                            onChange={onOrderByChange}
                        />
                        <SelectControl
                            label={__('Time Range', 'wordpress-popular-posts')}
                            value={attributes.range}
                            options={[
                                {label: __('Last 24 Hours', 'wordpress-popular-posts'), value: 'last24hours'},
                                {label: __('Last 7 days', 'wordpress-popular-posts'), value: 'last7days'},
                                {label: __('Last 30 days', 'wordpress-popular-posts'), value: 'last30days'},
                                {label: __('All-time', 'wordpress-popular-posts'), value: 'all'},
                                {label: __('Custom', 'wordpress-popular-posts'), value: 'custom'},
                            ]}
                            onChange={onTimeRangeChange}
                        />
                        { 'custom' == attributes.range &&
                            <div className='option-subset'>
                                <TextControl
                                    label={__('Time Quantity', 'wordpress-popular-posts')}
                                    value={attributes.time_quantity}
                                    onChange={onTimeQuantityChange}
                                />
                                <SelectControl
                                    label={__('Time Unit', 'wordpress-popular-posts')}
                                    value={attributes.time_unit}
                                    options={[
                                        {label: __('Minute(s)', 'wordpress-popular-posts'), value: 'minute'},
                                        {label: __('Hour(s)', 'wordpress-popular-posts'), value: 'hour'},
                                        {label: __('Day(s)', 'wordpress-popular-posts'), value: 'day'}
                                    ]}
                                    onChange={onTimeUnitChange}
                                />
                            </div>
                        }
                        <CheckboxControl
                            label={__('Display only posts published within the selected Time Range', 'wordpress-popular-posts')}
                            checked={attributes.freshness}
                            onChange={onFreshnessChange}
                        />
                        <p className='not-a-legend'><strong>{__('Filters', 'wordpress-popular-posts')}</strong></p>
                        <TextControl
                            label={__('Post type(s)', 'wordpress-popular-posts')}
                            help={__('Post types must be comma separated.', 'wordpress-popular-posts')}
                            value={attributes.post_type}
                            onChange={onPostTypeChange}
                        />
                        <TextControl
                            label={__('Post ID(s) to exclude', 'wordpress-popular-posts')}
                            help={__('IDs must be comma separated.', 'wordpress-popular-posts')}
                            value={attributes.pid}
                            onChange={onPostIDExcludeChange}
                        />
                        <TextControl
                            label={__('Author ID(s)', 'wordpress-popular-posts')}
                            help={__('IDs must be comma separated.', 'wordpress-popular-posts')}
                            value={attributes.author}
                            onChange={onAuthorChange}
                        />
                        <p className='not-a-legend'><strong>{__('Posts settings', 'wordpress-popular-posts')}</strong></p>
                        <CheckboxControl
                            label={__('Shorten title', 'wordpress-popular-posts')}
                            checked={attributes.shorten_title}
                            onChange={onShortenTitleChange}
                        />
                        { attributes.shorten_title &&
                            <div className='option-subset'>
                                <TextControl
                                    label={__('Shorten title to', 'wordpress-popular-posts')}
                                    value={attributes.title_length}
                                    onChange={onTitleLengthChange}
                                />
                                <SelectControl
                                    value={attributes.title_by_words}
                                    options={[
                                        { label: __('characters', 'wordpress-popular-posts'), value: 0 },
                                        { label: __('words', 'wordpress-popular-posts'), value: 1 },
                                    ]}
                                    onChange={(value) => setAttributes({ title_by_words: Number(value) })}
                                />
                            </div>
                        }
                        <CheckboxControl
                            label={__('Display post excerpt', 'wordpress-popular-posts')}
                            checked={attributes.display_post_excerpt}
                            onChange={onDisplayExcerptChange}
                        />
                        { attributes.display_post_excerpt && 
                            <div className='option-subset'>
                                <CheckboxControl
                                    label={__('Keep text format and links', 'wordpress-popular-posts')}
                                    checked={attributes.excerpt_format}
                                    onChange={(value) => setAttributes({ excerpt_format: value })}
                                />
                                <TextControl
                                    label={__('Excerpt length', 'wordpress-popular-posts')}
                                    value={attributes.excerpt_length}
                                    onChange={onExcerptLengthChange}
                                />
                                <SelectControl
                                    value={attributes.excerpt_by_words}
                                    options={[
                                        { label: __('characters', 'wordpress-popular-posts'), value: 0 },
                                        { label: __('words', 'wordpress-popular-posts'), value: 1 },
                                    ]}
                                    onChange={(value) => setAttributes({ excerpt_by_words: Number(value) })}
                                />
                            </div>
                        }
                        <CheckboxControl
                            label={__('Display post thumbnail', 'wordpress-popular-posts')}
                            checked={attributes.display_post_thumbnail}
                            onChange={onDisplayThumbnailChange}
                        />
                        { attributes.display_post_thumbnail && 
                            <div className='option-subset'>
                                <SelectControl
                                    value={attributes.thumbnail_build}
                                    options={[
                                        { label: __('Set size manually', 'wordpress-popular-posts'), value: 'manual' },
                                        { label: __('Use predefined size', 'wordpress-popular-posts'), value: 'predefined' },
                                    ]}
                                    onChange={onThumbnailBuildChange}
                                />
                                { 'manual' == attributes.thumbnail_build &&
                                    <Fragment>
                                        <TextControl
                                            label={__('Thumbnail width', 'wordpress-popular-posts')}
                                            help={__('Size in px units (pixels)', 'wordpress-popular-posts')}
                                            value={attributes.thumbnail_width}
                                            onChange={onThumbnailWidthChange}
                                        />
                                        <TextControl
                                            label={__('Thumbnail height', 'wordpress-popular-posts')}
                                            help={__('Size in px units (pixels)', 'wordpress-popular-posts')}
                                            value={attributes.thumbnail_height}
                                            onChange={onThumbnailHeightChange}
                                        />
                                    </Fragment>
                                }
                                { 'predefined' == attributes.thumbnail_build &&
                                    <Fragment>
                                        <SelectControl
                                            value={attributes.thumbnail_size}
                                            options={sizes}
                                            onChange={onThumbnailSizeChange}
                                        />
                                    </Fragment>
                                }
                            </div>
                        }
                        <p className='not-a-legend'><strong>{__('Stats Tag settings', 'wordpress-popular-posts')}</strong></p>
                        <CheckboxControl
                            label={__('Display comments count', 'wordpress-popular-posts')}
                            checked={attributes.stats_comments}
                            onChange={(value) => setAttributes({ stats_comments: value })}
                        />
                        <CheckboxControl
                            label={__('Display views', 'wordpress-popular-posts')}
                            checked={attributes.stats_views}
                            onChange={(value) => setAttributes({ stats_views: value })}
                        />
                        <CheckboxControl
                            label={__('Display author', 'wordpress-popular-posts')}
                            checked={attributes.stats_author}
                            onChange={(value) => setAttributes({ stats_author: value })}
                        />
                        <CheckboxControl
                            label={__('Display date', 'wordpress-popular-posts')}
                            checked={attributes.stats_date}
                            onChange={(value) => setAttributes({ stats_date: value })}
                        />
                        { attributes.stats_date && 
                            <div className='option-subset'>
                                <SelectControl
                                    label={__('Date Format', 'wordpress-popular-posts')}
                                    value={attributes.stats_date_format}
                                    options={[
                                        { label: __('Relative', 'wordpress-popular-posts'), value: 'relative' },
                                        { label: __('Month Day, Year', 'wordpress-popular-posts'), value: 'F j, Y' },
                                        { label: __('yyyy/mm/dd', 'wordpress-popular-posts'), value: 'Y/m/d' },
                                        { label: __('mm/dd/yyyy', 'wordpress-popular-posts'), value: 'm/d/Y' },
                                        { label: __('dd/mm/yyyy', 'wordpress-popular-posts'), value: 'd/m/Y' },
                                    ]}
                                    onChange={(value) => setAttributes({ stats_date_format: value })}
                                />
                            </div>
                        }
                        <CheckboxControl
                            label={__('Display taxonomy', 'wordpress-popular-posts')}
                            checked={attributes.stats_taxonomy}
                            onChange={(value) => setAttributes({ stats_taxonomy: value })}
                        />
                        { attributes.stats_taxonomy && 
                            <div className='option-subset'>
                                <SelectControl
                                    label={__('Taxonomy', 'wordpress-popular-posts')}
                                    value={attributes.taxonomy}
                                    options={taxonomies}
                                    onChange={(value) => setAttributes({ taxonomy: value })}
                                />
                            </div>
                        }
                        <p className='not-a-legend'><strong>{__('HTML Markup settings', 'wordpress-popular-posts')}</strong></p>
                        <CheckboxControl
                            label={__('Use custom HTML Markup', 'wordpress-popular-posts')}
                            checked={attributes.custom_html}
                            onChange={(value) => setAttributes({ custom_html: value })}
                        />
                        { attributes.custom_html &&
                            <div className='option-subset'>
                                <TextareaControl
                                    rows="1"
                                    label={__('Before title', 'wordpress-popular-posts')}
                                    value={attributes.header_start}
                                    onChange={(value) => setAttributes({ header_start: value })}
                                />
                                <TextareaControl
                                    rows="1"
                                    label={__('After title', 'wordpress-popular-posts')}
                                    value={attributes.header_end}
                                    onChange={(value) => setAttributes({ header_end: value })}
                                />
                                <TextareaControl
                                    rows="1"
                                    label={__('Before popular posts', 'wordpress-popular-posts')}
                                    value={attributes.wpp_start}
                                    onChange={(value) => setAttributes({ wpp_start: value })}
                                />
                                <TextareaControl
                                    rows="1"
                                    label={__('After popular posts', 'wordpress-popular-posts')}
                                    value={attributes.wpp_end}
                                    onChange={(value) => setAttributes({ wpp_end: value })}
                                />
                                <TextareaControl
                                    label={__('Post HTML markup', 'wordpress-popular-posts')}
                                    value={attributes.post_html}
                                    onChange={(value) => setAttributes({ post_html: value })}
                                />
                            </div>
                        }
                        <SelectControl
                            label={__('Theme', 'wordpress-popular-posts')}
                            value={attributes.theme}
                            options={themes}
                            onChange={onThemeChange}
                        />
                    </Fragment>
                }
                { ! this.state.editMode &&
                    <Disabled>
                        <ServerSideRender
                        block={this.props.name}
                        className={className}
                        attributes={{
                            title: attributes.title,
                            limit: attributes.limit,
                            offset: attributes.offset,
                            order_by: attributes.order_by,
                            range: attributes.range,
                            time_quantity: attributes.time_quantity,
                            time_unit: attributes.time_unit,
                            freshness: attributes.freshness,
                            post_type: attributes.post_type,
                            pid: attributes.pid,
                            author: attributes.author,
                            title_length: attributes.title_length,
                            title_by_words: attributes.title_by_words,
                            excerpt_format: attributes.excerpt_format,
                            excerpt_length: attributes.excerpt_length,
                            excerpt_by_words: attributes.excerpt_by_words,
                            thumbnail_build: attributes.thumbnail_build,
                            thumbnail_width: attributes.thumbnail_width,
                            thumbnail_height: attributes.thumbnail_height,
                            stats_comments: attributes.stats_comments,
                            stats_views: attributes.stats_views,
                            stats_author: attributes.stats_author,
                            stats_date: attributes.stats_date,
                            stats_date_format: attributes.stats_date_format,
                            stats_taxonomy: attributes.stats_taxonomy,
                            taxonomy: attributes.taxonomy,
                            custom_html: attributes.custom_html,
                            header_start: attributes.header_start,
                            header_end: attributes.header_end,
                            wpp_start: attributes.wpp_start,
                            wpp_end: attributes.wpp_end,
                            post_html: attributes.post_html,
                            theme: attributes.theme
                        }} />
                    </Disabled>
                }
            </div>
        ]);
    }
}
