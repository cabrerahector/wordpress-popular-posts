import { Controls } from './controls';
import { MainFields } from './fields-main';
import { FilterFields } from './fields-filters';
import { PostSettingsFields } from './fields-post-settings';
import { StatsTagFields } from './fields-stats-tag';
import { HTMLMarkupFields } from './fields-html-markup';

const { apiFetch, serverSideRender: ServerSideRender } = wp;
const { useBlockProps } = wp.blockEditor;
const { Disabled, Spinner } = wp.components;
const { Fragment, useEffect, useState } = wp.element;
const endpoint = 'wordpress-popular-posts/v1';

export function WPPWidgetBlockEdit({ attributes, setAttributes, isSelected, className, name }) {
    const blockProps = useBlockProps({
        className: [
            ( className || '' ),
            ( attributes._editMode ? 'in-edit-mode' : 'in-preview-mode' ),
            ( isSelected ? 'is-selected' : '' ),
        ].filter(Boolean).join(' '),
    });

    const { _editMode: editMode } = attributes;

    const [error, setError] = useState(null);
    const [themes, setThemes] = useState(null);
    const [imgSizes, setImgSizes] = useState(null);
    const [taxonomies, setTaxonomies] = useState(null);

    const getThemes = () => {
        apiFetch({ path: endpoint + '/themes' })
        .then(
            ( res ) => setThemes(res),
            ( err ) => {
                setError(err);
                setThemes(null);
            }
        );
    };

    const getImageSizes = () => {
        apiFetch({ path: endpoint + '/thumbnails' })
        .then(
            ( res ) => setImgSizes(res),
            ( err ) => {
                setError(err);
                setImgSizes(null);
            }
        );
    };

    const getTaxonomies = () => {
        apiFetch({ path: endpoint + '/taxonomies' })
        .then(
            ( res ) => {
                if ( res ) {
                    let tax = attributes.tax ? attributes.tax.split(';') : [],
                        term_id = attributes.term_id ? attributes.term_id.split(';') : [];

                    if ( tax.length && tax.length === term_id.length ) {
                        let selected_taxonomies = {};

                        for ( let t = 0; t < tax.length; t++ ) {
                            selected_taxonomies[tax[t]] = term_id[t];
                        }

                        for ( const tName in res ) {
                            res[tName]._terms = (typeof selected_taxonomies[tName] !== 'undefined') ? selected_taxonomies[tName] : '';
                        }
                    }
                }

                setTaxonomies(res);
            },
            ( err ) => {
                setError(err);
                setTaxonomies(null);
            }
        );
    };

    // Fetch on mount
    useEffect(() => {
        getThemes();
        getImageSizes();
        getTaxonomies();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    if ( ! taxonomies || ! themes || ! imgSizes ) {
        return <Spinner />;
    }

    let classes = className || '';
    classes += editMode ? ' in-edit-mode' : ' in-preview-mode';
    classes += isSelected ? ' is-selected' : '';

    return ([
        <Controls attributes={attributes} setAttributes={setAttributes} key="wpp-widget-controls" />,
        <div {... blockProps} key="wpp-widget-edit">
            { editMode &&
                <Fragment>
                    <MainFields attributes={attributes} setAttributes={setAttributes} />
                    <FilterFields attributes={attributes} setAttributes={setAttributes} taxonomies={taxonomies} setTaxonomies={setTaxonomies} />
                    <PostSettingsFields attributes={attributes} setAttributes={setAttributes} imgSizes={imgSizes} />
                    <StatsTagFields attributes={attributes} setAttributes={setAttributes} taxonomies={taxonomies} />
                    <HTMLMarkupFields attributes={attributes} setAttributes={setAttributes} themes={themes} />
                </Fragment>
            }
            { ! editMode &&
                <Disabled>
                    <ServerSideRender
                        block={name}
                        className={className}
                        attributes={attributes}
                        urlQueryArgs={{isSelected: isSelected}}
                    />
                </Disabled>
            }
        </div>
    ]);
}