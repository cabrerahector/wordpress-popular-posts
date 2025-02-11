<?php
/* General Controls */
$this->start_controls_section(
    'general_section',
    [
        'label' => esc_html__('General', 'wordpress-popular-posts'),
        'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
    ]
);

$this->add_control(
    'header',
    [
        'type' => \Elementor\Controls_Manager::TEXT,
        'label' => esc_html__('Title', 'wordpress-popular-posts'),
        'label_block' => true,
        'placeholder' => esc_html__('Enter your title', 'wordpress-popular-posts'),
        'ai' => [
            'active' => false,
        ],
    ]
);

$this->add_control(
    'limit',
    [
        'type' => \Elementor\Controls_Manager::NUMBER,
        'min' => 1,
        'max' => 100,
        'step' => 1,
        'default' => 10,
        'label' => esc_html__('Limit', 'wordpress-popular-posts'),
    ]
);

$this->add_control(
    'order_by',
    [
        'label' => esc_html__('Sort posts by', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::SELECT,
        'default' => 'views',
        'options' => [
            'views' => esc_html__('Total views', 'wordpress-popular-posts'),
            'avg'  => esc_html__('Avg. daily views', 'wordpress-popular-posts'),
            'comments' => esc_html__('Comments', 'wordpress-popular-posts')
        ]
    ]
);

$this->add_control(
    'range',
    [
        'label' => esc_html__('Time Range', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::SELECT,
        'default' => 'last24hours',
        'options' => [
            'last24hours' => esc_html__('Last 24 Hours', 'wordpress-popular-posts'),
            'last7days'  => esc_html__('Last 7 days', 'wordpress-popular-posts'),
            'last30days' => esc_html__('Last 30 days', 'wordpress-popular-posts'),
            'all' => esc_html__('All-time', 'wordpress-popular-posts'),
            'custom' => esc_html__('Custom', 'wordpress-popular-posts')
        ]
    ]
);

$this->add_control(
    'time_quantity',
    [
        'type' => \Elementor\Controls_Manager::NUMBER,
        'min' => 1,
        'max' => 100,
        'step' => 1,
        'default' => 24,
        'label' => esc_html__('Time Quantity', 'wordpress-popular-posts'),
        'condition' => [
            'range' => 'custom'
        ],
    ]
);

$this->add_control(
    'time_unit',
    [
        'label' => esc_html__('Time Unit', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::SELECT,
        'default' => 'hour',
        'options' => [
            'minute' => esc_html__('Minute(s)', 'wordpress-popular-posts'),
            'hour'  => esc_html__('Hour(s)', 'wordpress-popular-posts'),
            'day' => esc_html__('Day(s)', 'wordpress-popular-posts')
        ],
        'condition' => [
            'range' => 'custom'
        ],
    ]
);

$this->add_control(
    'freshness',
    [
        'label' => esc_html__('Display only posts published within the selected Time Range', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::SWITCHER,
        'return_value' => '1',
        'default' => '0',
    ]
);

$this->end_controls_section();

/* Filters */
$taxonomies = get_taxonomies(['public' => true], 'objects');

$this->start_controls_section(
    'filters_section',
    [
        'label' => esc_html__('Filters', 'wordpress-popular-posts'),
        'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
    ]
);

$this->add_control(
    'post_type',
    [
        'type' => \Elementor\Controls_Manager::TEXT,
        'label' => esc_html__('Post Type(s)', 'wordpress-popular-posts'),
        'label_block' => true,
        'description' => esc_html__('Post types must be comma separated.', 'wordpress-popular-posts'),
        'default' => 'post',
        'ai' => [
            'active' => false,
        ],
    ]
);

$this->add_control(
    'exclude',
    [
        'type' => \Elementor\Controls_Manager::TEXT,
        'label' => esc_html__('Post ID(s) to exclude', 'wordpress-popular-posts'),
        'label_block' => true,
        'description' => esc_html__('IDs must be comma separated.', 'wordpress-popular-posts'),
        'ai' => [
            'active' => false,
        ],
    ]
);

$this->add_control(
    'author',
    [
        'type' => \Elementor\Controls_Manager::TEXT,
        'label' => esc_html__('Author ID(s)', 'wordpress-popular-posts'),
        'label_block' => true,
        'description' => esc_html__('IDs must be comma separated.', 'wordpress-popular-posts'),
        'ai' => [
            'active' => false,
        ],
    ]
);

if ( $taxonomies ) {
    foreach( $taxonomies as $taxonomy ) {
        if ( 'post_format' == $taxonomy->name ) {
            continue;
        }

        $label = $taxonomy->labels->singular_name . ' ('. $taxonomy->name .')';

        $this->add_control(
            'wpp_taxonomy_slug_' . $taxonomy->name,
            [
                'label' => esc_html($label),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => $taxonomy->name,
                'default' => 0,
            ]
        );

        $this->add_control(
            'wpp_taxonomy_' . $taxonomy->name . '_terms',
            [
                'type' => \Elementor\Controls_Manager::TEXT,
                'label' => esc_html__('Term ID(s)', 'wordpress-popular-posts'),
                'label_block' => true,
                'description' => esc_html__('Term IDs must be comma separated, prefix a minus sign to exclude.', 'wordpress-popular-posts'),
                'ai' => [
                    'active' => false,
                ]
            ]
        );
    }
}

$this->end_controls_section();

/* Posts settings */
$this->start_controls_section(
    'posts_settings_section',
    [
        'label' => esc_html__('Posts settings', 'wordpress-popular-posts'),
        'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        'condition' => [
            'theme' => ''
        ],
    ]
);

if ( function_exists('the_ratings_results') ) :
    $this->add_control(
        'rating',
        [
            'label' => esc_html__('Display post rating', 'wordpress-popular-posts'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => '1',
            'default' => '0',
        ]
    );
endif;

$this->add_control(
    'shorten_title',
    [
        'label' => esc_html__('Shorten title', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::SWITCHER,
        'return_value' => '1',
        'default' => '0',
    ]
);

$this->add_control(
    'title_length',
    [
        'label' => esc_html__('Shorten title to', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::NUMBER,
        'min' => 1,
        'max' => 999,
        'step' => 1,
        'default' => 25,
        'condition' => [
            'shorten_title' => '1'
        ],
    ]
);

$this->add_control(
    'title_by_words',
    [
        'type' => \Elementor\Controls_Manager::SELECT,
        'default' => '0',
        'options' => [
            '0' => esc_html__('characters', 'wordpress-popular-posts'),
            '1'  => esc_html__('words', 'wordpress-popular-posts')
        ],
        'condition' => [
            'shorten_title' => '1'
        ],
    ]
);

$this->add_control(
    'display_post_excerpt',
    [
        'label' => esc_html__('Display post excerpt', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::SWITCHER,
        'return_value' => '1',
        'default' => '0',
    ]
);

$this->add_control(
    'excerpt_format',
    [
        'label' => esc_html__('Keep text format and links', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::SWITCHER,
        'return_value' => '1',
        'default' => '0',
        'condition' => [
            'display_post_excerpt' => '1'
        ],
    ]
);

$this->add_control(
    'excerpt_length',
    [
        'label' => esc_html__('Excerpt length', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::NUMBER,
        'min' => 1,
        'step' => 1,
        'default' => 55,
        'condition' => [
            'display_post_excerpt' => '1'
        ],
    ]
);

$this->add_control(
    'excerpt_by_words',
    [
        'type' => \Elementor\Controls_Manager::SELECT,
        'default' => '0',
        'options' => [
            '0' => esc_html__('characters', 'wordpress-popular-posts'),
            '1'  => esc_html__('words', 'wordpress-popular-posts')
        ],
        'condition' => [
            'display_post_excerpt' => '1'
        ],
    ]
);

$this->add_control(
    'display_post_thumbnail',
    [
        'label' => esc_html__('Display post thumbnail', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::SWITCHER,
        'return_value' => '1',
        'default' => '0',
    ]
);

$this->add_control(
    'thumbnail_build',
    [
        'type' => \Elementor\Controls_Manager::SELECT,
        'label_block' => true,
        'default' => 'manual',
        'options' => [
            'manual' => esc_html__('Set size manually', 'wordpress-popular-posts'),
            'predefined'  => esc_html__('Use predefined size', 'wordpress-popular-posts')
        ],
        'condition' => [
            'display_post_thumbnail' => '1'
        ],
    ]
);

$this->add_control(
    'thumbnail_width',
    [
        'label' => esc_html__('Thumbnail width', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::NUMBER,
        'min' => 1,
        'step' => 1,
        'default' => 75,
        'description' => esc_html__('Size in px units (pixels)', 'wordpress-popular-posts'),
        'condition' => [
            'display_post_thumbnail' => '1',
            'thumbnail_build' => 'manual'
        ],
    ]
);

$this->add_control(
    'thumbnail_height',
    [
        'label' => esc_html__('Thumbnail height', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::NUMBER,
        'min' => 1,
        'step' => 1,
        'default' => 75,
        'description' => esc_html__('Size in px units (pixels)', 'wordpress-popular-posts'),
        'condition' => [
            'display_post_thumbnail' => '1',
            'thumbnail_build' => 'manual'
        ],
    ]
);

$size_options = [];

if ( $this->available_sizes ) {
    foreach($this->available_sizes as $slug => $data) {
        $slug = sanitize_title($slug);
        $size_options[$slug] = $slug;
    }
}

if ( $size_options ) {
    $this->add_control(
        'thumbnail_size',
        [
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => 'thumbnail',
            'options' => $size_options,
            'condition' => [
                'display_post_thumbnail' => '1',
                'thumbnail_build' => 'predefined'
            ],
        ]
    );
}

$this->end_controls_section();

/* Stats Tag settings */
$this->start_controls_section(
    'stats_settings_section',
    [
        'label' => esc_html__('Stats Tag settings', 'wordpress-popular-posts'),
        'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        'condition' => [
            'theme' => ''
        ],
    ]
);

$this->add_control(
    'stats_comments',
    [
        'label' => esc_html__('Display comments count', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::SWITCHER,
        'return_value' => 1,
        'default' => 0,
    ]
);

$this->add_control(
    'stats_views',
    [
        'label' => esc_html__('Display views', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::SWITCHER,
        'return_value' => '1',
        'default' => '1',
    ]
);

$this->add_control(
    'stats_author',
    [
        'label' => esc_html__('Display author', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::SWITCHER,
        'return_value' => '1',
        'default' => '0',
    ]
);

$this->add_control(
    'stats_date',
    [
        'label' => esc_html__('Display date', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::SWITCHER,
        'return_value' => '1',
        'default' => '0',
    ]
);

$this->add_control(
    'stats_date_format',
    [
        'label' => esc_html__('Date Format', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::SELECT,
        'default' => 'F j, Y',
        'options' => [
            'relative' => esc_html__('Relative', 'wordpress-popular-posts'),
            'F j, Y'  => esc_html__('Month Day, Year', 'wordpress-popular-posts'),
            'Y/m/d' => esc_html__('yyyy/mm/dd', 'wordpress-popular-posts'),
            'm/d/Y' => esc_html__('mm/dd/yyyy', 'wordpress-popular-posts'),
            'd/m/Y'  => esc_html__('dd/mm/yyyy', 'wordpress-popular-posts'),
            'wp_date_format' => esc_html__('WordPress Date Format', 'wordpress-popular-posts')
        ],
        'condition' => [
            'stats_date' => '1'
        ],
    ]
);

$this->end_controls_section();

/* HTML */
$this->start_controls_section(
    'html_section',
    [
        'label' => esc_html__('HTML Markup settings', 'wordpress-popular-posts'),
        'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
    ]
);

$registered_themes = $this->themer->get_themes();
ksort($registered_themes);

$theme_options = [
    '' => 'Default'
];

foreach( $registered_themes as $slug => $data ) {
    $theme_options[$slug] = esc_html($data['json']['name']);
}

$this->add_control(
    'theme',
    [
        'label' => esc_html__('Theme', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::SELECT,
        'default' => '',
        'options' => $theme_options
    ]
);

$this->add_control(
    'theme_customization_note',
    [
        'type' => \Elementor\Controls_Manager::ALERT,
        'alert_type' => 'warning',
        'content' => '<a href="https://github.com/cabrerahector/wordpress-popular-posts/wiki/6.-Styling-the-list#customizing-an-existing-theme">' . esc_html__('How do I customize this theme?', 'wordpress-popular-posts') . '</a>',
        'condition' => [
            'theme!' => ''
        ],
    ]
);

$this->add_control(
    'header_start',
    [
        'label' => esc_html__('Before title', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::TEXTAREA,
        'rows' => 5,
        'default' => '<h2>',
        'ai' => [
            'active' => false,
        ],
        'condition' => [
            'theme' => ''
        ],
    ]
);

$this->add_control(
    'header_end',
    [
        'label' => esc_html__('After title', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::TEXTAREA,
        'rows' => 5,
        'default' => '</h2>',
        'ai' => [
            'active' => false,
        ],
        'condition' => [
            'theme' => ''
        ],
    ]
);

$this->add_control(
    'wpp_start',
    [
        'label' => esc_html__('Before popular posts', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::TEXTAREA,
        'rows' => 5,
        'default' => '<ul class="wpp-list">',
        'ai' => [
            'active' => false,
        ],
        'condition' => [
            'theme' => ''
        ],
    ]
);

$this->add_control(
    'wpp_end',
    [
        'label' => esc_html__('After popular posts', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::TEXTAREA,
        'rows' => 5,
        'default' => '</ul>',
        'ai' => [
            'active' => false,
        ],
        'condition' => [
            'theme' => ''
        ],
    ]
);

$this->add_control(
    'post_html',
    [
        'label' => esc_html__('Post HTML markup', 'wordpress-popular-posts'),
        'type' => \Elementor\Controls_Manager::TEXTAREA,
        'rows' => 10,
        'default' => '<li class="{current_class}">{thumb} {title} <span class="wpp-meta post-stats">{stats}</span><p class="wpp-excerpt">{excerpt}</p></li>',
        'description' => '<a href="https://github.com/cabrerahector/wordpress-popular-posts/wiki/2.-Template-tags#content-tags" target="_blank">' . esc_html__('Content Tags List', 'wordpress-popular-posts') . '</a>',
        'ai' => [
            'active' => false,
        ],
        'condition' => [
            'theme' => ''
        ],
    ]
);

$this->end_controls_section();
