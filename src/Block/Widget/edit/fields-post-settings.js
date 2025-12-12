const { Fragment } = wp.element;
const { CheckboxControl, SelectControl, TextControl } = wp.components;
const { __ } = wp.i18n;

export function PostSettingsFields({ attributes, setAttributes, imgSizes }) {
    const {
        shorten_title,
        title_length,
        title_by_words,
        display_post_excerpt,
        excerpt_format,
        excerpt_length,
        excerpt_by_words,
        display_post_thumbnail,
        thumbnail_build,
        thumbnail_width,
        thumbnail_height,
        thumbnail_size,
        rating
    } = attributes;

    const labelShortenTitle = __('Shorten title', 'wordpress-popular-posts');
    const labelShortenTitleTo = __('Shorten title to', 'wordpress-popular-posts');
    const labelDisplayPostExcerpt = __('Display post excerpt', 'wordpress-popular-posts');
    const labelKeepFormatAndLinks = __('Keep text format and links', 'wordpress-popular-posts');
    const labelExcerptLength = __('Excerpt length', 'wordpress-popular-posts');
    const labelDisplayThumbnail = __('Display post thumbnail', 'wordpress-popular-posts');
    const labelThumbnailWidth = __('Thumbnail width', 'wordpress-popular-posts');
    const labelThumbnailHeight = __('Thumbnail height', 'wordpress-popular-posts');
    const labelSizeInPX = __('Size in px units (pixels)', 'wordpress-popular-posts');
    const labelDisplayRating = _wordpress_popular_posts.can_show_rating ? __('Display post rating', 'wordpress-popular-posts') : '';

    const trimStyleOptions = [
        { label: __('characters', 'wordpress-popular-posts'), value: 0 },
        { label: __('words', 'wordpress-popular-posts'), value: 1 },
    ];

    const thumbnailBuildOptions = [
        { label: __('Set size manually', 'wordpress-popular-posts'), value: 'manual' },
        { label: __('Use predefined size', 'wordpress-popular-posts'), value: 'predefined' },
    ];

    const sizes = [];

    if ( imgSizes ) {
        for ( const size in imgSizes ) {
            if ( imgSizes.hasOwnProperty(size) ) {
                sizes.push({ label: size, value: size });
            }
        }
    }

    const onShortenTitleChange = (value) => {
        if ( value === false )
            setAttributes({ title_length: 0, title_by_words: 0, shorten_title: value });
        else
            setAttributes({ shorten_title: value, title_length: 25 });
    };

    const onTitleLengthChange = (value) => {
        setAttributes({ title_length: parseInt(value, 10) });
    };

    const onTitleLengthByWordsChange = (value) => {
        setAttributes({ title_by_words: parseInt(value, 10) });
    };

    const onDisplayExcerptChange = (value) => {
        if ( value === false )
            setAttributes({ excerpt_length: 0, excerpt_by_words: 0, display_post_excerpt: value, excerpt_format: false });
        else
            setAttributes({ display_post_excerpt: value, excerpt_length: 55 });
    };

    const onKeepFormatAndLinksChange = (value) => {
        setAttributes({ excerpt_format: value });
    };

    const onExcerptLengthChange = (value) => {
        setAttributes({ excerpt_length: parseInt(value, 10) });
    };

    const onExcerptLengthByWordsChange = (value) => {
        setAttributes({ excerpt_by_words: Number(value) });
    };

    const onDisplayThumbnailChange = (value) => {
        if ( value === false )
            setAttributes({ thumbnail_width: 0, thumbnail_height: 0, display_post_thumbnail: value, thumbnail_build: 'manual' });
        else
            setAttributes({ thumbnail_width: 75, thumbnail_height: 75, display_post_thumbnail: value });
    };

    const onThumbnailDimChange = (dim, value) => {
        value = parseInt(value, 10);
        setAttributes(( 'width' === dim ? { thumbnail_width: value } : { thumbnail_height: value } ));
    };

    const onThumbnailBuildChange = (value) => {
        if ( imgSizes ) {
            const sizesArr = Object.keys(imgSizes).map((s) => ({ label: s, value: s }));

            if ( 'predefined' === value ) {
                const fallback = 0;
                const fallbackValue = sizesArr[fallback] ? sizesArr[fallback].value : '';

                if ( fallbackValue && imgSizes[fallbackValue] ) {
                    setAttributes({
                        thumbnail_width: imgSizes[fallbackValue].width,
                        thumbnail_height: imgSizes[fallbackValue].height,
                        thumbnail_size: fallbackValue
                    });
                }
            } else {
                setAttributes({
                    thumbnail_width: 75,
                    thumbnail_height: 75,
                    thumbnail_size: ''
                });
            }
        }

        setAttributes({ thumbnail_build: value });
    };

    const onThumbnailSizeChange = (value) => {
        if ( imgSizes && imgSizes[value] ) {
            setAttributes({
                thumbnail_width: imgSizes[value].width,
                thumbnail_height: imgSizes[value].height,
                thumbnail_size: value
            });
        }
    };

    return <Fragment>
        <p className='not-a-legend'><strong>{__('Posts settings', 'wordpress-popular-posts')}</strong></p>
        <CheckboxControl
            label={labelShortenTitle}
            checked={shorten_title}
            onChange={onShortenTitleChange}
        />
        { shorten_title &&
            <div className='option-subset'>
                <TextControl
                    label={labelShortenTitleTo}
                    type='number'
                    value={title_length}
                    min='1'
                    onChange={onTitleLengthChange}
                />
                <SelectControl
                    value={title_by_words}
                    options={trimStyleOptions}
                    onChange={onTitleLengthByWordsChange}
                />
            </div>
        }
        <CheckboxControl
            label={labelDisplayPostExcerpt}
            checked={display_post_excerpt}
            onChange={onDisplayExcerptChange}
        />
        { display_post_excerpt && 
            <div className='option-subset'>
                <CheckboxControl
                    label={labelKeepFormatAndLinks}
                    checked={excerpt_format}
                    onChange={onKeepFormatAndLinksChange}
                />
                <TextControl
                    label={labelExcerptLength}
                    type='number'
                    value={excerpt_length}
                    min='1'
                    onChange={onExcerptLengthChange}
                />
                <SelectControl
                    value={excerpt_by_words}
                    options={trimStyleOptions}
                    onChange={onExcerptLengthByWordsChange}
                />
            </div>
        }
        <CheckboxControl
            label={labelDisplayThumbnail}
            checked={display_post_thumbnail}
            onChange={onDisplayThumbnailChange}
        />
        { display_post_thumbnail && 
            <div className='option-subset'>
                <SelectControl
                    value={thumbnail_build}
                    options={thumbnailBuildOptions}
                    onChange={onThumbnailBuildChange}
                />
                { 'manual' === thumbnail_build &&
                    <Fragment>
                        <TextControl
                            label={labelThumbnailWidth}
                            type='number'
                            help={labelSizeInPX}
                            value={thumbnail_width}
                            min='1'
                            onChange={(value) => onThumbnailDimChange('width', value)}
                        />
                        <TextControl
                            label={labelThumbnailHeight}
                            type='number'
                            help={labelSizeInPX}
                            value={thumbnail_height}
                            min='1'
                            onChange={(value) => onThumbnailDimChange('height', value)}
                        />
                    </Fragment>
                }
                { 'predefined' === thumbnail_build &&
                    <Fragment>
                        <SelectControl
                            value={thumbnail_size}
                            options={sizes}
                            onChange={onThumbnailSizeChange}
                        />
                    </Fragment>
                }
            </div>
        }
        { _wordpress_popular_posts.can_show_rating &&
            <CheckboxControl
                label={labelDisplayRating}
                checked={rating}
                onChange={(value) => setAttributes({ rating: value })}
            />
        }
    </Fragment>;
}