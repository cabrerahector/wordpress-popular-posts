const { Fragment } = wp.element;
const { TextControl } = wp.components;
const { __ } = wp.i18n;

export function FilterFields({ attributes, setAttributes, taxonomies, setTaxonomies }) {
    const {
        post_type,
        pid,
        author,
    } = attributes;

    const labelPostType = __('Post type(s)', 'wordpress-popular-posts');
    const labelPostTypeHelp = __('Post types must be comma separated.', 'wordpress-popular-posts');
    const labelPostIDsToExclude = __('Post ID(s) to exclude', 'wordpress-popular-posts');
    const labelAuthorIDs = __('Author ID(s)', 'wordpress-popular-posts');
    const labelCommaSeparatedIDs = __('IDs must be comma separated.', 'wordpress-popular-posts');
    const labelCommaSeparatedAndMinusSign = __('Term IDs must be comma separated, prefix a minus sign to exclude.', 'wordpress-popular-posts');

    const taxFields = [];

    if ( taxonomies ) {
        for ( const t in taxonomies ) {
            if ( taxonomies.hasOwnProperty(t) ) {
                if ( 'post_format' === taxonomies[t].name ) {
                    continue;
                }

                taxFields.push({
                    name: taxonomies[t].name,
                    label: `${taxonomies[t].labels.singular_name} (${taxonomies[t].name})`,
                    terms: taxonomies[t]._terms
                });
            }
        }
    }

    const onPostTypeChange = (value) => {
        const new_value = value.replace(/[^a-z0-9-_\,]+/gi, '');
        setAttributes({ post_type: new_value });
    };

    const onPostIDExcludeChange = (value) => {
        const new_value = value.replace(/[^0-9\,]/g, '');
        setAttributes({ pid: new_value });
    };

    const onAuthorChange = (value) => {
        const new_value = value.replace(/[^0-9\,]/g, '');
        setAttributes({ author: new_value });
    };

    const onTaxChange = (taxonomy_name, terms) => {
        const localTaxonomies = taxonomies;
        terms = terms.replace(/[^0-9-\,]/g, '');

        if ( localTaxonomies && typeof localTaxonomies[taxonomy_name] !== 'undefined' ) {
            localTaxonomies[taxonomy_name]._terms = terms;
            setTaxonomies(Object.assign({}, localTaxonomies));
        }
    };

    const onTaxBlur = (taxonomy_name) => {
        const localTaxonomies = taxonomies;

        if ( localTaxonomies && typeof localTaxonomies[taxonomy_name] !== 'undefined' ) {
            let terms_arr = (localTaxonomies[taxonomy_name]._terms || '').split(',');

            // Remove invalid values
            if ( terms_arr.length )
                terms_arr = terms_arr.map((term) => term.trim()).filter((term) => term !== '' && term !== '-');

            // Remove duplicates
            if ( terms_arr.length )
                terms_arr = Array.from(new Set(terms_arr));

            localTaxonomies[taxonomy_name]._terms = terms_arr.join(',');

            setTaxonomies(Object.assign({}, localTaxonomies));

            let tax = '',
                term_id = '';

            for ( let key in localTaxonomies ) {
                if ( localTaxonomies.hasOwnProperty(key) ) {
                    if ( ! localTaxonomies[key]._terms.length )
                        continue;

                    tax += key + ';';
                    term_id += localTaxonomies[key]._terms + ';';
                }
            }

            if ( tax && term_id ) {
                tax = tax.replace(/;$/, '');
                term_id = term_id.replace(/;$/, '');
            }

            setAttributes({ tax: tax, term_id: term_id });
        }
    };

    return <Fragment>
        <p className='not-a-legend'><strong>{__('Filters', 'wordpress-popular-posts')}</strong></p>
        <TextControl
            label={labelPostType}
            help={labelPostTypeHelp}
            value={post_type}
            onChange={onPostTypeChange}
        />
        <TextControl
            label={labelPostIDsToExclude}
            help={labelCommaSeparatedIDs}
            value={pid}
            onChange={onPostIDExcludeChange}
        />
        <TextControl
            label={labelAuthorIDs}
            help={labelCommaSeparatedIDs}
            value={author}
            onChange={onAuthorChange}
        />
        { taxFields && taxFields.map((tax) =>
            {
                return (
                    <TextControl
                        key={tax.name}
                        label={tax.label}
                        help={labelCommaSeparatedAndMinusSign}
                        value={tax.terms}
                        onChange={(terms) => onTaxChange(tax.name, terms)}
                        onBlur={() => onTaxBlur(tax.name)}
                    />
                );
            }
        )}
    </Fragment>;
}