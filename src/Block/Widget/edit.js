import { sanitize_text_field } from '../utils';

const { ServerSideRender } = wp.editor;
const { Component, Fragment } = wp.element;
const { BlockControls } = wp.blockEditor;
const { CheckboxControl, SelectControl, TextControl, Toolbar, Button, Disabled } = wp.components;
const { __ } = wp.i18n;
const endpoint = ''

export class WPPWidgetBlockEdit extends Component
{
    constructor(props)
    {
        super(props);

        this.state = {
            error: null,
            editMode: true,
            themes: null
        }
    }

    componentDidMount()
    {
        const { attributes } = this.props;
        this.setState({ editMode: attributes._editMode });
        this.getThemes();
    }

    getThemes()
    {
        wp.apiFetch({ path: 'wordpress-popular-posts/v1/themes' })
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
        const { isSelected, className, attributes, setAttributes } = this.props;

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

        function onThemeChange(value)
        {
            setAttributes({ theme: value });
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
                            <Fragment>
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
                            </Fragment>
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
                        <p className='not-a-legend'><strong>{__('HTML Markup settings', 'wordpress-popular-posts')}</strong></p>
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
                            theme: attributes.theme
                        }} />
                    </Disabled>
                }
            </div>
        ]);
    }
}
