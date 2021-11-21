import { escape_html, unescape_html } from '../utils';

const { serverSideRender: ServerSideRender } = wp;
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
            taxonomies: null
        }
    }

    componentDidMount()
    {
        const { attributes } = this.props;

        this.getThemes();
        this.getImageSizes();
        this.getTaxonomies();

        this.setState({ editMode: attributes._editMode });
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
        const { attributes } = this.props;

        wp.apiFetch({ path: endpoint + '/taxonomies' })
        .then(
            ( taxonomies ) => {
                if ( taxonomies ) {
                    let tax = attributes.tax.split(';'),
                        term_id = attributes.term_id.split(';');

                    if ( tax.length && tax.length == term_id.length ) {
                        let selected_taxonomies = {};

                        for( var t = 0; t < tax.length; t++ ) {
                            selected_taxonomies[tax[t]] = term_id[t];
                        }

                        for( const tax in taxonomies ) {
                            taxonomies[tax]._terms = 'undefined' != typeof selected_taxonomies[tax] ? selected_taxonomies[tax] : '';
                        }
                    }
                }

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
        const { setAttributes } = this.props;
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
                        label={ this.state.editMode ? __('Preview', 'wordpress-popular-posts') : __('Edit', 'wordpress-popular-posts') }
                        icon={ this.state.editMode ? "format-image" : "edit" }
                        onClick={onPreviewChange}
                    />
                </Toolbar>
            </BlockControls>
        );
    }

    getMainFields()
    {
        const { attributes, setAttributes } = this.props;

        function onTitleChange(value)
        {
            value = escape_html(unescape_html(value));
            setAttributes({ title: value });
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

        return <Fragment>
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
                    {label: __('Avg. daily views', 'wordpress-popular-posts'), value: 'avg'},
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
        </Fragment>;
    }

    getFiltersFields()
    {
        const { attributes, setAttributes } = this.props;
        const _self = this;

        function onPostTypeChange(value)
        {
            let new_value = value.replace(/[^a-z0-9-_\,]+/gi, '');
            setAttributes({ post_type: new_value });
        }

        function onPostIDExcludeChange(value)
        {
            let new_value = value.replace(/[^0-9\,]/g, '');
            setAttributes({ pid: new_value });
        }

        function onAuthorChange(value)
        {
            let new_value = value.replace(/[^0-9\,]/g, '');
            setAttributes({ author: new_value });
        }

        function onTaxChange(taxonomy_name, terms)
        {
            let taxonomies = _self.state.taxonomies;

            terms = terms.replace(/[^0-9-\,]/g, '');

            if ( taxonomies && 'undefined' != typeof taxonomies[taxonomy_name] ) {
                taxonomies[taxonomy_name]._terms = terms;
                _self.setState({ taxonomies: taxonomies });
            }
        }

        function onTaxBlur(taxonomy_name)
        {
            let taxonomies = _self.state.taxonomies;

            if ( taxonomies && 'undefined' != typeof taxonomies[taxonomy_name] ) {
                let terms_arr = taxonomies[taxonomy_name]._terms.split(',');

                // Remove invalid values
                if ( terms_arr.length )
                    terms_arr = terms_arr.map((term) => term.trim())
                        .filter((term) => '' != term && '-' != term);

                // Remove duplicates
                if ( terms_arr.length )
                    terms_arr = Array.from(new Set(terms_arr));

                taxonomies[taxonomy_name]._terms = terms_arr.join(',');

                _self.setState({ taxonomies });

                let tax = '',
                    term_id = '';

                for ( let key in _self.state.taxonomies ) {
                    if ( _self.state.taxonomies.hasOwnProperty(key) ) {

                        if ( ! _self.state.taxonomies[key]._terms.length )
                            continue;

                        tax += key + ';';
                        term_id += _self.state.taxonomies[key]._terms + ';';
                    }
                }

                // Remove trailing semicolon
                if ( tax && term_id ) {
                    tax = tax.replace(new RegExp(';$'), '');
                    term_id = term_id.replace(new RegExp(';$'), '');
                }

                setAttributes({ tax: tax, term_id: term_id });
            }
        }

        let taxonomies = [];

        if ( this.state.taxonomies ) {
            for( const tax in this.state.taxonomies ) {
                taxonomies.push(
                    {
                        name: this.state.taxonomies[tax].name,
                        label: this.state.taxonomies[tax].labels.singular_name + ' (' + this.state.taxonomies[tax].name + ')',
                        terms: this.state.taxonomies[tax]._terms
                    }
                );
            }
        }

        return <Fragment>
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
            { taxonomies && taxonomies.filter((tax) => 'post_format' != tax.name).map((tax) =>
                {
                    return (
                        <TextControl
                            label={tax.label}
                            help={__('Term IDs must be comma separated, prefix a minus sign to exclude.', 'wordpress-popular-posts')}
                            value={tax.terms}
                            onChange={(terms) => onTaxChange(tax.name, terms)}
                            onBlur={() => onTaxBlur(tax.name)}
                        />
                    );
                }
            )}
        </Fragment>;
    }

    getPostSettingsFields()
    {
        const { attributes, setAttributes } = this.props;
        const _self = this;

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

        function onThumbnailDimChange(dim, value)
        {
            let width = Number.isInteger(Number(value)) && Number(value) >= 0 ? value : 0;
            setAttributes(( 'width' == dim ? { thumbnail_width: Number(width) } : { thumbnail_height: Number(width) } ));
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

        return <Fragment>
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
                                onChange={(value) => onThumbnailDimChange('width', value)}
                            />
                            <TextControl
                                label={__('Thumbnail height', 'wordpress-popular-posts')}
                                help={__('Size in px units (pixels)', 'wordpress-popular-posts')}
                                value={attributes.thumbnail_height}
                                onChange={(value) => onThumbnailDimChange('height', value)}
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
        </Fragment>;
    }

    getStatsTagFields()
    {
        const { attributes, setAttributes } = this.props;

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

        return <Fragment>
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
                            { label: __('WordPress Date Format', 'wordpress-popular-posts'), value: 'wp_date_format' },
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
        </Fragment>;
    }

    getHTMLMarkupFields()
    {
        const { attributes, setAttributes } = this.props;
        const _self = this;

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

        return <Fragment>
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
        </Fragment>;
    }

    render()
    {
        if ( ! this.state.taxonomies || ! this.state.themes || ! this.state.imgSizes )
            return <Spinner />;

        const { isSelected, className, attributes } = this.props;

        let classes = className;
        classes += this.state.editMode ? ' in-edit-mode' : ' in-preview-mode';
        classes += isSelected ? ' is-selected' : '';

        return ([
            this.getBlockControls(),
            <div className={classes}>
                { this.state.editMode &&
                    <Fragment>
                        {this.getMainFields()}
                        {this.getFiltersFields()}
                        {this.getPostSettingsFields()}
                        {this.getStatsTagFields()}
                        {this.getHTMLMarkupFields()}
                    </Fragment>
                }
                { ! this.state.editMode &&
                    <Disabled>
                        <ServerSideRender
                            block={this.props.name}
                            className={className}
                            attributes={attributes}
                            urlQueryArgs={{isSelected: isSelected}}
                        />
                    </Disabled>
                }
            </div>
        ]);
    }
}
