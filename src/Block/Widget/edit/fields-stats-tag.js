const { Fragment } = wp.element;
const { CheckboxControl, SelectControl } = wp.components;
const { __ } = wp.i18n;

export function StatsTagFields({ attributes, setAttributes, taxonomies }) {
    const {
        stats_author,
        stats_comments,
        stats_date,
        stats_date_format,
        stats_taxonomy,
        stats_views,
        taxonomy
    } = attributes;

    const labelDisplayCommentsCount = __('Display comments count', 'wordpress-popular-posts');
    const labelDisplayViews = __('Display views', 'wordpress-popular-posts');
    const labelDisplayAuthor = __('Display author', 'wordpress-popular-posts');
    const labelDisplayDate = __('Display date', 'wordpress-popular-posts');
    const labelDateFormat = __('Date Format', 'wordpress-popular-posts');
    const dateFormatOptions = [
        { label: __('Relative', 'wordpress-popular-posts'), value: 'relative' },
        { label: __('Month Day, Year', 'wordpress-popular-posts'), value: 'F j, Y' },
        { label: __('yyyy/mm/dd', 'wordpress-popular-posts'), value: 'Y/m/d' },
        { label: __('mm/dd/yyyy', 'wordpress-popular-posts'), value: 'm/d/Y' },
        { label: __('dd/mm/yyyy', 'wordpress-popular-posts'), value: 'd/m/Y' },
        { label: __('WordPress Date Format', 'wordpress-popular-posts'), value: 'wp_date_format' },
    ];
    const labelDisplayTaxonomy = __('Display taxonomy', 'wordpress-popular-posts');
    const labelTaxonomy = __('Taxonomy', 'wordpress-popular-posts');

    const taxList = [];

    if ( taxonomies ) {
        for ( const t in taxonomies ) {
            if ( taxonomies.hasOwnProperty(t) ) {
                taxList.push({
                    label: `${taxonomies[t].labels.singular_name} (${taxonomies[t].name})`,
                    value: taxonomies[t].name
                });
            }
        }
    }

    return <Fragment>
        <p className='not-a-legend'><strong>{__('Stats Tag settings', 'wordpress-popular-posts')}</strong></p>
        <CheckboxControl
            label={labelDisplayCommentsCount}
            checked={stats_comments}
            onChange={(value) => setAttributes({ stats_comments: value })}
        />
        <CheckboxControl
            label={labelDisplayViews}
            checked={stats_views}
            onChange={(value) => setAttributes({ stats_views: value })}
        />
        <CheckboxControl
            label={labelDisplayAuthor}
            checked={stats_author}
            onChange={(value) => setAttributes({ stats_author: value })}
        />
        <CheckboxControl
            label={labelDisplayDate}
            checked={stats_date}
            onChange={(value) => setAttributes({ stats_date: value })}
        />
        { stats_date && 
            <div className='option-subset'>
                <SelectControl
                    label={labelDateFormat}
                    value={stats_date_format}
                    options={dateFormatOptions}
                    onChange={(value) => setAttributes({ stats_date_format: value })}
                />
            </div>
        }
        <CheckboxControl
            label={labelDisplayTaxonomy}
            checked={stats_taxonomy}
            onChange={(value) => setAttributes({ stats_taxonomy: value })}
        />
        { stats_taxonomy && 
            <div className='option-subset'>
                <SelectControl
                    label={labelTaxonomy}
                    value={taxonomy}
                    options={taxList}
                    onChange={(value) => setAttributes({ taxonomy: value })}
                />
            </div>
        }
    </Fragment>;
}