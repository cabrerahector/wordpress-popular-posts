<?php
if ( 'stats' == $current ) {
    $chart_data = json_decode(
        $this->get_chart_data(
            $this->config['stats']['range'],
            strtoupper($this->config['stats']['time_unit']),
            $this->config['stats']['time_quantity']
        ),
        true
    );
    ?>

    <?php if ( current_user_can('edit_others_posts') ) : ?>
        <button type="button" id="wpp-stats-config-btn" class="button-secondary action right"><span class="dashicons dashicons-admin-generic"></span><span class="screen-reader-text"><?php esc_html_e('Open Settings', 'wordpress-popular-posts'); ?></span></button>

        <div id="wpp-stats-config" class="wpp-lightbox" role="dialog" aria-label="<?php esc_attr_e('Stats Settings', 'wordpress-popular-posts'); ?>">
            <form action="" method="post" id="wpp_stats_options" name="wpp_stats_options">
                <label for="stats_type"><?php esc_html_e('Post type', 'wordpress-popular-posts'); ?>:</label>
                <input type="text" id="stats_type" name="stats_type" value="<?php echo esc_attr($this->config['stats']['post_type']); ?>" size="15" required>

                <label for="stats_limits"><?php esc_html_e('Limit', 'wordpress-popular-posts'); ?>:</label>
                <input type="text" id="stats_type" name="stats_limit" value="<?php echo esc_attr($this->config['stats']['limit']); ?>" size="5" required>

                <label for="stats_freshness">
                    <input type="checkbox" class="checkbox" <?php echo ($this->config['stats']['freshness']) ? 'checked="checked"' : ''; ?> id="stats_freshness" name="stats_freshness"> <small><?php esc_html_e('Display only posts published within the selected Time Range', 'wordpress-popular-posts'); ?></small>
                </label>

                <div class="clear"></div>
                <br /><br />

                <input type="hidden" name="section" value="stats">
                <button type="submit" class="button-primary action"><?php esc_html_e('Apply', 'wordpress-popular-posts'); ?></button>
                <button type="button" class="button-secondary action right"><?php esc_html_e('Cancel'); ?></button>

                <?php wp_nonce_field('wpp-update-stats-options', 'wpp-update-stats-options-token'); ?>
            </form>
        </div>
    <?php endif; ?>

    <div id="wpp-stats-range" class="wpp-lightbox wpp-tabs" role="dialog" aria-label="<?php esc_attr_e('Custom Time Range Settings', 'wordpress-popular-posts'); ?>">
        <form action="" method="post">
            <div class="wpp-lightbox-tabs wpp-tabs-container" role="tablist">
                <button type="button" role="tab" aria-selected="true" aria-controls="custom-time-range" id="tab-1" tabindex="0"><?php esc_html_e('Custom Time Range', 'wordpress-popular-posts'); ?></button>
                <button type="button" role="tab" aria-selected="false" aria-controls="custom-date-range" id="tab-2" tabindex="-1"><?php esc_html_e('Date Range', 'wordpress-popular-posts'); ?></button>
            </div>

            <div class="wpp-lightbox-tab-content active wpp-tabs-panel" id="custom-time-range" role="tabpanel" tabindex="0" aria-labelledby="tab-1">
                <input type="text" id="stats_range_time_quantity" name="stats_range_time_quantity" value="<?php echo esc_attr($this->config['stats']['time_quantity']); ?>">

                <select id="stats_range_time_unit" name="stats_range_time_unit">
                    <option <?php if ($this->config['stats']['time_unit'] == 'minute') { ?>selected="selected"<?php } ?> value="minute"><?php esc_html_e('Minute(s)', 'wordpress-popular-posts'); ?></option>
                    <option <?php if ($this->config['stats']['time_unit'] == 'hour') { ?>selected="selected"<?php } ?> value="hour"><?php esc_html_e('Hour(s)', 'wordpress-popular-posts'); ?></option>
                    <option <?php if ($this->config['stats']['time_unit'] == 'day') { ?>selected="selected"<?php } ?> value="day"><?php esc_html_e('Day(s)', 'wordpress-popular-posts'); ?></option>
                </select>
            </div>

            <div class="wpp-lightbox-tab-content wpp-tabs-panel" id="custom-date-range" role="tabpanel" tabindex="0" aria-labelledby="tab-2">
                <input type="text" id="stats_range_date" name="stats_range_date" value="" placeholder="<?php esc_attr_e('Select a date...', 'wordpress-popular-posts'); ?>" />
            </div>

            <div class="clear"></div>
            <br />

            <button type="submit" class="button-primary action">
                <?php esc_html_e('Apply', 'wordpress-popular-posts'); ?>
            </button>
            <button type="button" class="button-secondary action right">
                <?php esc_html_e('Cancel'); ?>
            </button>
        </form>
    </div>

    <div id="wpp-chart-wrapper">
        <h4><?php echo wp_kses_post($chart_data['totals']['label_summary']); ?></h4>
        <h5><?php echo esc_html($chart_data['totals']['label_date_range']); ?></h5>

        <ul class="wpp-header-nav" id="wpp-time-ranges">
            <li <?php echo ('today' == $this->config['stats']['range']) ? ' class="current"' : ''; ?>>
                <button type="button" class="<?php echo ('today' == $this->config['stats']['range']) ? 'button-primary' : 'button-secondary'; ?>" data-range="today"><?php esc_html_e('Today', 'wordpress-popular-posts'); ?></button>
            </li>
            <li <?php echo ('last24hours' == $this->config['stats']['range']) ? ' class="current"' : ''; ?>>
                <button type="button" class="<?php echo ('last24hours' == $this->config['stats']['range'] ) ? 'button-primary' : 'button-secondary'; ?>" data-range="last24hours" title="<?php esc_attr_e('Last 24 hours', 'wordpress-popular-posts'); ?>">24h</button>
            </li>
            <li <?php echo ('last7days' == $this->config['stats']['range']) ? ' class="current"' : ''; ?>>
                <button type="button" class="<?php echo ('last7days' == $this->config['stats']['range'] ) ? 'button-primary' : 'button-secondary'; ?>" data-range="last7days" title="<?php esc_attr_e('Last 7 days', 'wordpress-popular-posts'); ?>">7d</button>
            </li>
            <li <?php echo ('last30days' == $this->config['stats']['range']) ? ' class="current"' : ''; ?>>
                <button type="button" class="<?php echo ('last30days' == $this->config['stats']['range']) ? 'button-primary' : 'button-secondary'; ?>" data-range="last30days" title="<?php esc_attr_e('Last 30 days', 'wordpress-popular-posts'); ?>">30d</button>
            </li>
            <li <?php echo ('custom' == $this->config['stats']['range']) ? ' class="current"' : ''; ?>>
                <button type="button" class="<?php echo ('custom' == $this->config['stats']['range']) ? 'button-primary' : 'button-secondary'; ?>" data-range="custom"><?php esc_html_e('Custom', 'wordpress-popular-posts'); ?></button>
            </li>
        </ul>

        <div id="wpp-chart">
            <p><?php echo sprintf( __('Err... A nice little chart is supposed to be here, instead you are seeing this because your browser is too old. <br /> Please <a href="%s" target="_blank">get a better browser</a>.', 'wordpress-popular-posts'), 'https://browsehappy.com/'); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
        </div>
    </div>

    <div id="wpp-listing" class="wpp-content wpp-tabs">
        <div class="wpp-tabs-container" role="tablist">
            <button type="button" role="tab" aria-selected="true" aria-controls="most-viewed-list" id="tab-list-1" tabindex="0" title="<?php esc_attr_e('See your most viewed posts within the selected time range', 'wordpress-popular-posts'); ?>"><span class="wpp-icon-eye"></span><span><?php esc_html_e('Most viewed', 'wordpress-popular-posts'); ?></span></button>
            <button type="button" role="tab" aria-selected="false" aria-controls="most-commented-list" id="tab-list-2" tabindex="-1" title="<?php esc_attr_e('See your most commented posts within the selected time range', 'wordpress-popular-posts'); ?>"><span class="wpp-icon-comment"></span><span><?php esc_html_e('Most commented', 'wordpress-popular-posts'); ?></span></button>
            <button type="button" role="tab" aria-selected="false" aria-controls="trending-list" id="tab-list-3" tabindex="-1" title="<?php esc_attr_e('See your most viewed posts within the last hour', 'wordpress-popular-posts'); ?>"><span class="wpp-icon-rocket"></span><span><?php esc_html_e('Trending now', 'wordpress-popular-posts'); ?></span></button>
            <button type="button" role="tab" aria-selected="false" aria-controls="hof-list" id="tab-list-4" tabindex="-1" title="<?php esc_attr_e('See your most viewed posts of all time', 'wordpress-popular-posts'); ?>"><span class="wpp-icon-award"></span><span><?php esc_html_e('Hall of Fame', 'wordpress-popular-posts'); ?></span></button>
        </div>

        <div class="wpp-tabs-panel active" id="most-viewed-list" role="tabpanel" tabindex="0" aria-labelledby="tab-list-1">
            <span class="spinner"></span>
        </div>

        <div class="wpp-tabs-panel" id="most-commented-list" role="tabpanel" tabindex="0" aria-labelledby="tab-list-2">
            <span class="spinner"></span>
        </div>

        <div class="wpp-tabs-panel" id="trending-list" role="tabpanel" tabindex="0" aria-labelledby="tab-list-3">
            <span class="spinner"></span>
        </div>

        <div class="wpp-tabs-panel" id="hof-list" role="tabpanel" tabindex="0" aria-labelledby="tab-list-4">
            <?php
            $args = [
                'range' => 'all',
                'post_type' => $this->config['stats']['post_type'],
                'order_by' => 'views',
                'limit' => $this->config['stats']['limit'],
                'stats_tag' => [
                    'comment_count' => 1,
                    'views' => 1,
                    'date' => [
                        'active' => 1
                    ]
                ]
            ];
            $query = new \WordPressPopularPosts\Query($args);
            $popular_posts = $query->get_posts();

            $this->render_list($popular_posts, 'hof');
            ?>
        </div>
    </div>
    <?php
}
