import { escape_html, unescape_html } from '../../utils';

const { Fragment } = wp.element;
const { CheckboxControl, SelectControl, TextControl } = wp.components;
const { __ } = wp.i18n;

export function MainFields({ attributes, setAttributes }) {
    const {
        title,
        limit,
        order_by,
        range,
        time_quantity,
        time_unit,
        freshness
    } = attributes;

    const labelTitle = __('Title', 'wordpress-popular-posts');
    const labelLimit = __('Limit', 'wordpress-popular-posts');
    const labelSortBy = __('Sort posts by', 'wordpress-popular-posts');
    const labelTimeRange = __('Time Range', 'wordpress-popular-posts');
    const labelTimeQuantity = __('Time Quantity', 'wordpress-popular-posts');
    const labelTimeUnit = __('Time Unit', 'wordpress-popular-posts');
    const labelFreshness = __('Display only posts published within the selected Time Range', 'wordpress-popular-posts');

    const sortingOptions = [
        {label: __('Total views', 'wordpress-popular-posts'), value: 'views'},
        {label: __('Avg. daily views', 'wordpress-popular-posts'), value: 'avg'},
        {label: __('Comments', 'wordpress-popular-posts'), value: 'comments'}
    ];

    const timeRangeOptions = [
        {label: __('Last 24 Hours', 'wordpress-popular-posts'), value: 'last24hours'},
        {label: __('Last 7 days', 'wordpress-popular-posts'), value: 'last7days'},
        {label: __('Last 30 days', 'wordpress-popular-posts'), value: 'last30days'},
        {label: __('All-time', 'wordpress-popular-posts'), value: 'all'},
        {label: __('Custom', 'wordpress-popular-posts'), value: 'custom'},
    ];

    const timeUnitOptions = [
        {label: __('Minute(s)', 'wordpress-popular-posts'), value: 'minute'},
        {label: __('Hour(s)', 'wordpress-popular-posts'), value: 'hour'},
        {label: __('Day(s)', 'wordpress-popular-posts'), value: 'day'}
    ];

    const onTitleChange = (value) => {
        setAttributes({ title: escape_html(unescape_html(value)) });
    };

    const onLimitChange = (value) => {
        setAttributes({ limit: parseInt(value, 10) });
    };

    const onOrderByChange = (value) => {
        setAttributes({ order_by: value });
    };

    const onTimeRangeChange = (value) => {
        setAttributes({ range: value });
    };

    const onTimeQuantityChange = (value) => {
        setAttributes({ time_quantity: parseInt(value, 10) });
    };

    const onTimeUnitChange = (value) => {
        setAttributes({ time_unit: value });
    };

    const onFreshnessChange = (value) => {
        setAttributes({ freshness: value });
    };

    return <Fragment>
        <TextControl
            label={labelTitle}
            value={title}
            onChange={onTitleChange}
        />
        <TextControl
            label={labelLimit}
            type='number'
            value={limit}
            min='1'
            onChange={onLimitChange}
        />
        <SelectControl
            label={labelSortBy}
            value={order_by}
            options={sortingOptions}
            onChange={onOrderByChange}
        />
        <SelectControl
            label={labelTimeRange}
            value={range}
            options={timeRangeOptions}
            onChange={onTimeRangeChange}
        />
        { range === 'custom' &&
            <div className='option-subset'>
                <TextControl
                    label={labelTimeQuantity}
                    type='number'
                    value={time_quantity}
                    min='1'
                    onChange={onTimeQuantityChange}
                />
                <SelectControl
                    label={labelTimeUnit}
                    value={time_unit}
                    options={timeUnitOptions}
                    onChange={onTimeUnitChange}
                />
            </div>
        }
        <CheckboxControl
            label={labelFreshness}
            checked={freshness}
            onChange={onFreshnessChange}
        />
    </Fragment>;
}