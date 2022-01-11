<?php
if ( basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__) )
    exit('Please do not load this page directly');

$tabs = [
    'stats' => __('Stats', 'wordpress-popular-posts'),
    'tools' => __('Tools', 'wordpress-popular-posts'),
    'params' => __('Parameters', 'wordpress-popular-posts'),
    'debug' => 'Debug'
];

// Set active tab
if ( isset($_GET['tab'] ) && isset($tabs[$_GET['tab']] ) )
    $current = $_GET['tab'];
else
    $current = 'stats';

// Update options on form submission
if ( isset($_POST['section']) ) {

    if ( "stats" == $_POST['section'] ) {
        $current = 'stats';

        if ( isset($_POST['wpp-update-stats-options-token']) && wp_verify_nonce($_POST['wpp-update-stats-options-token'], 'wpp-update-stats-options') ) {
            $this->config['stats']['limit'] = ( \WordPressPopularPosts\Helper::is_number($_POST['stats_limit']) && $_POST['stats_limit'] > 0 ) ? $_POST['stats_limit'] : 10;
            $this->config['stats']['post_type'] = empty($_POST['stats_type']) ? "post,page" : $_POST['stats_type'];
            $this->config['stats']['freshness'] = empty($_POST['stats_freshness']) ? false : $_POST['stats_freshness'];

            update_option('wpp_settings_config', $this->config);
            echo "<div class=\"notice notice-success is-dismissible\"><p><strong>" . __('Settings saved.', 'wordpress-popular-posts') . "</strong></p></div>";
        }
    }
    elseif ( "misc" == $_POST['section'] ) {
        $current = 'tools';

        if ( isset($_POST['wpp-update-misc-options-token'] ) && wp_verify_nonce($_POST['wpp-update-misc-options-token'], 'wpp-update-misc-options') ) {
            $this->config['tools']['link']['target'] = $_POST['link_target'];
            $this->config['tools']['css'] = $_POST['css'];
            $this->config['tools']['experimental'] = empty($_POST['experimental_features']) ? false : $_POST['experimental_features'];

            update_option('wpp_settings_config', $this->config);
            echo "<div class=\"notice notice-success is-dismissible\"><p><strong>" . __('Settings saved.', 'wordpress-popular-posts') . "</strong></p></div>";
        }
    }
    elseif ( "thumb" == $_POST['section'] ) {
        $current = 'tools';

        if ( isset($_POST['wpp-update-thumbnail-options-token']) && wp_verify_nonce($_POST['wpp-update-thumbnail-options-token'], 'wpp-update-thumbnail-options') ) {
            if (
                $_POST['thumb_source'] == "custom_field"
                && ( ! isset($_POST['thumb_field']) || empty($_POST['thumb_field']) )
            ) {
                echo '<div id="wpp-message" class="error fade"><p>' . __('Please provide the name of your custom field.', 'wordpress-popular-posts') . '</p></div>';
            }
            else {
                // thumbnail settings changed, flush transients
                if ( $this->config['tools']['cache']['active'] ) {
                    $this->flush_transients();
                }

                $this->config['tools']['thumbnail']['source'] = $_POST['thumb_source'];
                $this->config['tools']['thumbnail']['field'] = ( ! empty($_POST['thumb_field']) ) ? $_POST['thumb_field'] : "wpp_thumbnail";
                $this->config['tools']['thumbnail']['default'] = ( ! empty($_POST['upload_thumb_src']) ) ? $_POST['upload_thumb_src'] : "";
                $this->config['tools']['thumbnail']['resize'] = $_POST['thumb_field_resize'];
                $this->config['tools']['thumbnail']['lazyload'] = (bool) $_POST['thumb_lazy_load'];

                update_option('wpp_settings_config', $this->config );
                echo "<div class=\"notice notice-success is-dismissible\"><p><strong>" . __('Settings saved.', 'wordpress-popular-posts') . "</strong></p></div>";
            }
        }
    }
    elseif ( "data" == $_POST['section'] && current_user_can('manage_options') ) {
        $current = 'tools';

        if ( isset($_POST['wpp-update-data-options-token'] ) && wp_verify_nonce($_POST['wpp-update-data-options-token'], 'wpp-update-data-options') ) {
            $this->config['tools']['log']['level'] = $_POST['log_option'];
            $this->config['tools']['log']['limit'] = $_POST['log_limit'];
            $this->config['tools']['log']['expires_after'] = ( \WordPressPopularPosts\Helper::is_number($_POST['log_expire_time']) && $_POST['log_expire_time'] > 0 )
              ? $_POST['log_expire_time']
              : 180;
            $this->config['tools']['ajax'] = $_POST['ajax'];

            // if any of the caching settings was updated, destroy all transients created by the plugin
            if (
                $this->config['tools']['cache']['active'] != $_POST['cache']
                || $this->config['tools']['cache']['interval']['time'] != $_POST['cache_interval_time']
                || $this->config['tools']['cache']['interval']['value'] != $_POST['cache_interval_value']
            ) {
                $this->flush_transients();
            }

            $this->config['tools']['cache']['active'] = $_POST['cache'];
            $this->config['tools']['cache']['interval']['time'] = $_POST['cache_interval_time'];
            $this->config['tools']['cache']['interval']['value'] = ( isset($_POST['cache_interval_value']) && \WordPressPopularPosts\Helper::is_number($_POST['cache_interval_value']) && $_POST['cache_interval_value'] > 0 )
              ? $_POST['cache_interval_value']
              : 1;

            $this->config['tools']['sampling']['active'] = $_POST['sampling'];
            $this->config['tools']['sampling']['rate'] = ( isset($_POST['sample_rate']) && \WordPressPopularPosts\Helper::is_number($_POST['sample_rate']) && $_POST['sample_rate'] > 0 )
              ? $_POST['sample_rate']
              : 100;

            update_option('wpp_settings_config', $this->config);
            echo "<div class=\"notice notice-success is-dismissible\"><p><strong>" . __('Settings saved.', 'wordpress-popular-posts') . "</strong></p></div>";
        }
    }

}
?>

<nav id="wpp-menu">
    <ul>
        <li><a href="#" title="<?php esc_attr_e('Menu'); ?>"><span><?php _e('Menu'); ?></span></a></li>
        <li <?php echo ('stats' == $current ) ? 'class="current"' : ''; ?>><a href="<?php echo admin_url('options-general.php?page=wordpress-popular-posts&tab=stats'); ?>" title="<?php esc_attr_e('Stats', 'wordpress-popular-posts'); ?>"><span><?php _e('Stats', 'wordpress-popular-posts'); ?></span></a></li>
        <li <?php echo ('tools' == $current ) ? 'class="current"' : ''; ?>><a href="<?php echo admin_url('options-general.php?page=wordpress-popular-posts&tab=tools'); ?>" title="<?php esc_attr_e('Tools', 'wordpress-popular-posts'); ?>"><span><?php _e('Tools', 'wordpress-popular-posts'); ?></span></a></li>
        <li <?php echo ('debug' == $current ) ? 'class="current"' : ''; ?>><a href="<?php echo admin_url('options-general.php?page=wordpress-popular-posts&tab=debug'); ?>" title="Debug"><span>Debug</span></a></li>
    </ul>
</nav>

<div class="wpp-wrapper wpp-section-<?php echo $current; ?>">
    <div class="wpp-header">
        <h2>WordPress Popular Posts</h2>
        <h3><?php echo $tabs[$current]; ?></h3>
    </div>

    <?php
    // Stats chart
    if ( 'stats' == $current ) {
        $chart_data = json_decode(
            $this->get_chart_data($this->config['stats']['range'], strtoupper($this->config['stats']['time_unit']), $this->config['stats']['time_quantity']),
            true
        );
    ?>

    <a href="#" id="wpp-stats-config-btn" class="dashicons dashicons-admin-generic"></a>

    <div id="wpp-stats-config" class="wpp-lightbox">
        <form action="" method="post" id="wpp_stats_options" name="wpp_stats_options">
            <label for="stats_type"><?php _e("Post type", 'wordpress-popular-posts'); ?>:</label>
            <input type="text" name="stats_type" value="<?php echo esc_attr($this->config['stats']['post_type']); ?>" size="15">

            <label for="stats_limits"><?php _e("Limit", 'wordpress-popular-posts'); ?>:</label>
            <input type="text" name="stats_limit" value="<?php echo $this->config['stats']['limit']; ?>" size="5">

            <label for="stats_freshness"><input type="checkbox" class="checkbox" <?php echo ($this->config['stats']['freshness']) ? 'checked="checked"' : ''; ?> id="stats_freshness" name="stats_freshness"> <small><?php _e('Display only posts published within the selected Time Range', 'wordpress-popular-posts'); ?></small></label>

            <div class="clear"></div>
            <br /><br />

            <input type="hidden" name="section" value="stats">
            <button type="submit" class="button-primary action"><?php _e("Apply", 'wordpress-popular-posts'); ?></button>
            <button class="button-secondary action right"><?php _e("Cancel"); ?></button>

            <?php wp_nonce_field('wpp-update-stats-options', 'wpp-update-stats-options-token'); ?>
        </form>
    </div>

    <div id="wpp-stats-range" class="wpp-lightbox">
        <form action="" method="post">
            <ul class="wpp-lightbox-tabs">
                <li class="active"><a href="#"><?php _e('Custom Time Range', 'wordpress-popular-posts'); ?></a></li>
                <li><a href="#"><?php _e('Date Range', 'wordpress-popular-posts'); ?></a></li>
            </ul>

            <div class="wpp-lightbox-tab-content wpp-lightbox-tab-content-active" id="custom-time-range">
                <input type="text" id="stats_range_time_quantity" name="stats_range_time_quantity" value="<?php echo $this->config['stats']['time_quantity']; ?>">

                <select id="stats_range_time_unit" name="stats_range_time_unit">
                    <option <?php if ($this->config['stats']['time_unit'] == "minute") { ?>selected="selected"<?php } ?> value="minute"><?php _e("Minute(s)", 'wordpress-popular-posts'); ?></option>
                    <option <?php if ($this->config['stats']['time_unit'] == "hour") { ?>selected="selected"<?php } ?> value="hour"><?php _e("Hour(s)", 'wordpress-popular-posts'); ?></option>
                    <option <?php if ($this->config['stats']['time_unit'] == "day") { ?>selected="selected"<?php } ?> value="day"><?php _e("Day(s)", 'wordpress-popular-posts'); ?></option>
                </select>
            </div>

            <div class="wpp-lightbox-tab-content" id="custom-date-range">
                <input type="text" id="stats_range_date" name="stats_range_date" value="" placeholder="<?php esc_attr_e('Select a date...', 'wordpress-popular-posts'); ?>" readonly>
            </div>

            <div class="clear"></div>
            <br />

            <button type="submit" class="button-primary action">
                <?php _e("Apply", 'wordpress-popular-posts'); ?>
            </button>
            <button class="button-secondary action right">
                <?php _e("Cancel"); ?>
            </button>
        </form>
    </div>

    <div id="wpp-chart-wrapper">
        <h4><?php echo $chart_data['totals']['label_summary']; ?></h4>
        <h5><?php echo $chart_data['totals']['label_date_range']; ?></h5>

        <ul class="wpp-header-nav" id="wpp-time-ranges">
            <li <?php echo ('daily' == $this->config['stats']['range'] || 'today' == $this->config['stats']['range'] ) ? ' class="current"' : ''; ?>><a href="#" data-range="today" title="<?php esc_attr_e('Today', 'wordpress-popular-posts'); ?>"><?php _e('Today', 'wordpress-popular-posts'); ?></a></li>
            <li <?php echo ('daily' == $this->config['stats']['range'] || 'last24hours' == $this->config['stats']['range'] ) ? ' class="current"' : ''; ?>><a href="#" data-range="last24hours" title="<?php esc_attr_e('Last 24 hours', 'wordpress-popular-posts'); ?>">24h</a></li>
            <li <?php echo ('weekly' == $this->config['stats']['range'] || 'last7days' == $this->config['stats']['range'] ) ? ' class="current"' : ''; ?>><a href="#" data-range="last7days" title="<?php esc_attr_e('Last 7 days', 'wordpress-popular-posts'); ?>">7d</a></li>
            <li <?php echo ('monthly' == $this->config['stats']['range'] || 'last30days' == $this->config['stats']['range'] ) ? ' class="current"' : ''; ?>><a href="#" data-range="last30days" title="<?php esc_attr_e('Last 30 days', 'wordpress-popular-posts'); ?>">30d</a></li>
            <li <?php echo ('custom' == $this->config['stats']['range'] ) ? ' class="current"' : ''; ?>><a href="#"  data-range="custom" title="<?php esc_attr_e('Custom', 'wordpress-popular-posts'); ?>"><?php _e('Custom', 'wordpress-popular-posts'); ?></a></li>
        </ul>

        <div id="wpp-chart">
            <p><?php echo sprintf( __('Err... A nice little chart is supposed to be here, instead you are seeing this because your browser is too old. <br /> Please <a href="%s" target="_blank">get a better browser</a>.', 'wordpress-popular-posts'), 'https://browsehappy.com/'); ?></p>
        </div>
    </div>
    <?php
    } // End stats chart
    ?>

    <div id="wpp-listing" class="wpp-content"<?php echo ('stats' == $current ) ? '' : ' style="display: none;"'; ?>>
        <ul class="wpp-tabbed-nav">
            <li class="active"><a href="#" title="<?php esc_attr_e('See your most viewed posts within the selected time range', 'wordpress-popular-posts'); ?>"><span class="wpp-icon-eye"></span><span><?php _e('Most viewed', 'wordpress-popular-posts'); ?></span></a></li>
            <li><a href="#" title="<?php esc_attr_e('See your most commented posts within the selected time range', 'wordpress-popular-posts'); ?>"><span class="wpp-icon-comment"></span><span><?php _e('Most commented', 'wordpress-popular-posts'); ?></span></a></li>
            <li><a href="#" title="<?php esc_attr_e('See your most viewed posts within the last hour', 'wordpress-popular-posts'); ?>"><span class="wpp-icon-rocket"></span><span><?php _e('Trending now', 'wordpress-popular-posts'); ?></span></a></li>
            <li><a href="#" title="<?php esc_attr_e('See your most viewed posts of all time', 'wordpress-popular-posts'); ?>"><span class="wpp-icon-award"></span><span><?php _e('Hall of Fame', 'wordpress-popular-posts'); ?></span></a></li>
        </ul>

        <div class="wpp-tab-content wpp-tab-content-active">
            <span class="spinner"></span>
        </div>

        <div class="wpp-tab-content">
            <span class="spinner"></span>
        </div>

        <div class="wpp-tab-content">
            <span class="spinner"></span>
        </div>

        <div class="wpp-tab-content">
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
            $hof = new \WordPressPopularPosts\Query($args);
            $posts = $hof->get_posts();

            $this->render_list($posts, 'hof');
            ?>
        </div>
    </div>

    <!-- Start tools -->
    <div id="wpp_tools" <?php echo ( "tools" == $current ) ? '' : ' style="display: none;"'; ?>>
        <h3 class="wmpp-subtitle"><?php _e("Thumbnails", 'wordpress-popular-posts'); ?></h3>

        <form action="" method="post" id="wpp_thumbnail_options" name="wpp_thumbnail_options">
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="thumb_default"><?php _e("Default thumbnail", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <?php
                            $fallback_thumbnail_url = trim($this->config['tools']['thumbnail']['default']);

                            if ( ! $fallback_thumbnail_url )
                                $fallback_thumbnail_url = $this->thumbnail->get_default_url();

                            $fallback_thumbnail_url = str_replace(
                                parse_url(
                                    $fallback_thumbnail_url
                                    , PHP_URL_SCHEME
                                ) . ':',
                                '',
                                $fallback_thumbnail_url
                            );
                            ?>
                            <div id="thumb-review">
                                <img src="<?php echo esc_url($fallback_thumbnail_url); ?>" alt="" />
                            </div>

                            <input id="upload_thumb_button" type="button" class="button" value="<?php _e("Change thumbnail", 'wordpress-popular-posts'); ?>">
                            <input type="hidden" id="upload_thumb_src" name="upload_thumb_src" value="">

                            <p class="description"><?php _e("This image will be displayed when no thumbnail is available", 'wordpress-popular-posts'); ?>.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="thumb_source"><?php _e("Pick image from", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <select name="thumb_source" id="thumb_source">
                                <option <?php if ($this->config['tools']['thumbnail']['source'] == "featured") { ?>selected="selected"<?php } ?> value="featured"><?php _e("Featured image", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->config['tools']['thumbnail']['source'] == "first_image") { ?>selected="selected"<?php } ?> value="first_image"><?php _e("First image on post", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->config['tools']['thumbnail']['source'] == "first_attachment") { ?>selected="selected"<?php } ?> value="first_attachment"><?php _e("First attachment", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->config['tools']['thumbnail']['source'] == "custom_field") { ?>selected="selected"<?php } ?> value="custom_field"><?php _e("Custom field", 'wordpress-popular-posts'); ?></option>
                            </select>
                            <br />
                            <p class="description"><?php _e("Tell WordPress Popular Posts where it should get thumbnails from", 'wordpress-popular-posts'); ?>.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="thumb_lazy_load"><?php _e("Lazy load", 'wordpress-popular-posts'); ?>:</label> <small>[<a href="https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance#lazy-loading" target="_blank" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small></th>
                        <td>
                            <select name="thumb_lazy_load" id="thumb_lazy_load">
                                <option <?php if ( ! $this->config['tools']['thumbnail']['lazyload'] ) { ?>selected="selected"<?php } ?> value="0"><?php _e("No", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ( $this->config['tools']['thumbnail']['lazyload'] ) { ?>selected="selected"<?php } ?> value="1"><?php _e("Yes", 'wordpress-popular-posts'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top" <?php if ($this->config['tools']['thumbnail']['source'] != "custom_field") { ?>style="display: none;"<?php } ?> id="row_custom_field">
                        <th scope="row"><label for="thumb_field"><?php _e("Custom field name", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <input type="text" id="thumb_field" name="thumb_field" value="<?php echo esc_attr($this->config['tools']['thumbnail']['field']); ?>" size="10" <?php if ($this->config['tools']['thumbnail']['source'] != "custom_field") { ?>style="display: none;"<?php } ?> />
                        </td>
                    </tr>
                    <tr valign="top" <?php if ($this->config['tools']['thumbnail']['source'] != "custom_field") { ?>style="display: none;"<?php } ?> id="row_custom_field_resize">
                        <th scope="row"><label for="thumb_field_resize"><?php _e("Resize image from Custom field?", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <select name="thumb_field_resize" id="thumb_field_resize">
                                <option <?php if ( !$this->config['tools']['thumbnail']['resize'] ) { ?>selected="selected"<?php } ?> value="0"><?php _e("No, use image as is", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->config['tools']['thumbnail']['resize'] == 1 ) { ?>selected="selected"<?php } ?> value="1"><?php _e("Yes", 'wordpress-popular-posts'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <?php
                    $wp_upload_dir = wp_get_upload_dir();
                    if ( is_dir($wp_upload_dir['basedir'] . "/" . 'wordpress-popular-posts') ) :
                    ?>
                    <tr valign="top">
                        <th scope="row"></th>
                        <td>
                            <input type="button" name="wpp-reset-image-cache" id="wpp-reset-image-cache" class="button-secondary" value="<?php _e("Empty image cache", 'wordpress-popular-posts'); ?>">
                            <p class="description"><?php _e("Use this button to clear WPP's thumbnails cache", 'wordpress-popular-posts'); ?>.</p>
                        </td>
                    </tr>
                    <?php
                    endif;
                    ?>
                    <tr valign="top">
                        <td colspan="2">
                            <input type="hidden" name="section" value="thumb">
                            <input type="submit" class="button-primary action" id="btn_th_ops" value="<?php _e("Apply", 'wordpress-popular-posts'); ?>" name="">
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php wp_nonce_field('wpp-update-thumbnail-options', 'wpp-update-thumbnail-options-token'); ?>
        </form>
        <br />
        <p style="display: <?php echo ( current_user_can('manage_options') ) ? 'block' : 'none'; ?>; float:none; clear:both;">&nbsp;</p>

        <h3 class="wmpp-subtitle" style="display: <?php echo ( current_user_can('manage_options') ) ? 'block' : 'none'; ?>"><?php _e("Data", 'wordpress-popular-posts'); ?></h3>

        <form action="" method="post" id="wpp_ajax_options" name="wpp_ajax_options" style="display: <?php echo ( current_user_can('manage_options') ) ? 'block' : 'none'; ?>">
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="log_option"><?php _e("Log views from", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <select name="log_option" id="log_option">
                                <option <?php if ($this->config['tools']['log']['level'] == 0) { ?>selected="selected"<?php } ?> value="0"><?php _e("Visitors only", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->config['tools']['log']['level'] == 2) { ?>selected="selected"<?php } ?> value="2"><?php _e("Logged-in users only", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->config['tools']['log']['level'] == 1) { ?>selected="selected"<?php } ?> value="1"><?php _e("Everyone", 'wordpress-popular-posts'); ?></option>
                            </select>
                            <br />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="log_limit"><?php _e("Log limit", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <select name="log_limit" id="log_limit">
                                <option <?php if ($this->config['tools']['log']['limit'] == 0) { ?>selected="selected"<?php } ?> value="0"><?php _e("Disabled", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->config['tools']['log']['limit'] == 1) { ?>selected="selected"<?php } ?> value="1"><?php _e("Keep data for", 'wordpress-popular-posts'); ?></option>
                            </select>

                            <label for="log_expire_time"<?php echo ($this->config['tools']['log']['limit'] == 0) ? ' style="display: none;"' : ''; ?>>
                                <input type="text" id="log_expire_time" name="log_expire_time" value="<?php echo esc_attr($this->config['tools']['log']['expires_after']); ?>" size="3"> <?php _e("day(s)", 'wordpress-popular-posts'); ?>
                            </label>

                            <p class="description"<?php echo ($this->config['tools']['log']['limit'] == 0) ? ' style="display: none;"' : ''; ?>><?php _e("Data older than the specified time frame will be automatically discarded", 'wordpress-popular-posts'); ?>.</p>

                            <br <?php echo (1 == $this->config['tools']['log']['limit']) ? 'style="display: none;"' : ''; ?>/>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="ajax"><?php _e("Ajaxify widget", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <select name="ajax" id="ajax">
                                <option <?php if (! $this->config['tools']['ajax']) { ?>selected="selected"<?php } ?> value="0"><?php _e("Disabled", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->config['tools']['ajax']) { ?>selected="selected"<?php } ?> value="1"><?php _e("Enabled", 'wordpress-popular-posts'); ?></option>
                            </select>

                            <br />
                            <p class="description"><?php _e("If you are using a caching plugin such as WP Super Cache, enabling this feature will keep the popular list from being cached by it", 'wordpress-popular-posts'); ?>.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="cache"><?php _e("Data Caching", 'wordpress-popular-posts'); ?>:</label> <small>[<a href="https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance#caching-db-queries-results" target="_blank" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small></th>
                        <td>
                            <select name="cache" id="cache">
                                <option <?php if ( ! $this->config['tools']['cache']['active'] ) { ?>selected="selected"<?php } ?> value="0"><?php _e("Never cache", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ( $this->config['tools']['cache']['active'] ) { ?>selected="selected"<?php } ?> value="1"><?php _e("Enable caching", 'wordpress-popular-posts'); ?></option>
                            </select>

                            <br />
                            <p class="description"><?php _e("WPP can cache the popular list for a specified amount of time. Recommended for large / high traffic sites", 'wordpress-popular-posts'); ?>.</p>
                        </td>
                    </tr>
                    <tr valign="top" <?php if ( ! $this->config['tools']['cache']['active'] ) { ?>style="display: none;"<?php } ?> id="cache_refresh_interval">
                        <th scope="row"><label for="cache_interval_value"><?php _e("Refresh cache every", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <input name="cache_interval_value" type="text" id="cache_interval_value" value="<?php echo ( isset($this->config['tools']['cache']['interval']['value']) ) ? (int) $this->config['tools']['cache']['interval']['value'] : 1; ?>" class="small-text">
                            <select name="cache_interval_time" id="cache_interval_time">
                                <option <?php if ($this->config['tools']['cache']['interval']['time'] == "minute") { ?>selected="selected"<?php } ?> value="minute"><?php _e("Minute(s)", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->config['tools']['cache']['interval']['time'] == "hour") { ?>selected="selected"<?php } ?> value="hour"><?php _e("Hour(s)", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->config['tools']['cache']['interval']['time'] == "day") { ?>selected="selected"<?php } ?> value="day"><?php _e("Day(s)", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->config['tools']['cache']['interval']['time'] == "week") { ?>selected="selected"<?php } ?> value="week"><?php _e("Week(s)", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->config['tools']['cache']['interval']['time'] == "month") { ?>selected="selected"<?php } ?> value="month"><?php _e("Month(s)", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->config['tools']['cache']['interval']['time'] == "year") { ?>selected="selected"<?php } ?> value="month"><?php _e("Year(s)", 'wordpress-popular-posts'); ?></option>
                            </select>
                            <br />
                            <p class="description" style="display: none;" id="cache_too_long"><?php _e("Really? That long?", 'wordpress-popular-posts'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="sampling"><?php _e("Data Sampling", 'wordpress-popular-posts'); ?>:</label> <small>[<a href="https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance#data-sampling" target="_blank" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small></th>
                        <td>
                            <select name="sampling" id="sampling">
                                <option <?php if ( !$this->config['tools']['sampling']['active'] ) { ?>selected="selected"<?php } ?> value="0"><?php _e("Disabled", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ( $this->config['tools']['sampling']['active'] ) { ?>selected="selected"<?php } ?> value="1"><?php _e("Enabled", 'wordpress-popular-posts'); ?></option>
                            </select>

                            <br />
                            <p class="description"><?php echo sprintf( __('By default, WordPress Popular Posts stores in database every single visit your site receives. For small / medium sites this is generally OK, but on large / high traffic sites the constant writing to the database may have an impact on performance. With <a href="%1$s" target="_blank">data sampling</a>, WordPress Popular Posts will store only a subset of your traffic and report on the tendencies detected in that sample set (for more, <a href="%2$s" target="_blank">please read here</a>)', 'wordpress-popular-posts'), 'http://en.wikipedia.org/wiki/Sample_%28statistics%29', 'https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance#data-sampling'); ?>.</p>
                        </td>
                    </tr>
                    <tr valign="top" <?php if ( ! $this->config['tools']['sampling']['active'] ) { ?>style="display: none;"<?php } ?>>
                        <th scope="row"><label for="sample_rate"><?php _e("Sample Rate", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <input name="sample_rate" type="text" id="sample_rate" value="<?php echo ( isset($this->config['tools']['sampling']['rate']) ) ? (int) $this->config['tools']['sampling']['rate'] : 100; ?>" class="small-text">
                            <br />
                            <p class="description"><?php echo sprintf(__("A sampling rate of %d is recommended for large / high traffic sites. For lower traffic sites, you should lower the value", 'wordpress-popular-posts'), 100); ?>.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <td colspan="2">
                            <input type="hidden" name="section" value="data">
                            <input type="submit" class="button-primary action" id="btn_ajax_ops" value="<?php _e("Apply", 'wordpress-popular-posts'); ?>" name="">
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php wp_nonce_field('wpp-update-data-options', 'wpp-update-data-options-token'); ?>
        </form>
        <br />
        <p style="display: block; float:none; clear: both;">&nbsp;</p>

        <h3 class="wmpp-subtitle"><?php _e("Miscellaneous", 'wordpress-popular-posts'); ?></h3>

        <form action="" method="post" id="wpp_link_options" name="wpp_link_options">
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="link_target"><?php _e("Open links in", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <select name="link_target" id="link_target">
                                <option <?php if ($this->config['tools']['link']['target'] == '_self') { ?>selected="selected"<?php } ?> value="_self"><?php _e("Current window", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->config['tools']['link']['target'] == '_blank') { ?>selected="selected"<?php } ?> value="_blank"><?php _e("New tab/window", 'wordpress-popular-posts'); ?></option>
                            </select>
                            <br />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="css"><?php _e("Use plugin's stylesheet", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <select name="css" id="css">
                                <option <?php if ($this->config['tools']['css']) { ?>selected="selected"<?php } ?> value="1"><?php _e("Enabled", 'wordpress-popular-posts'); ?></option>
                                <option <?php if (!$this->config['tools']['css']) { ?>selected="selected"<?php } ?> value="0"><?php _e("Disabled", 'wordpress-popular-posts'); ?></option>
                            </select>
                            <br />
                            <p class="description"><?php _e("By default, the plugin includes a stylesheet called wpp.css which you can use to style your popular posts listing. If you wish to use your own stylesheet or do not want it to have it included in the header section of your site, use this.", 'wordpress-popular-posts'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="experimental_features"><?php _e("Enable experimental features", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <input type="checkbox" class="checkbox" id="experimental_features" name="experimental_features" <?php echo ($this->config['tools']['experimental']) ? 'checked="checked"' : ''; ?>>
                        </td>
                    </tr>
                    <tr valign="top">
                        <td colspan="2">
                            <input type="hidden" name="section" value="misc">
                            <input type="submit" class="button-primary action" value="<?php _e("Apply", 'wordpress-popular-posts'); ?>" name="">
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php wp_nonce_field('wpp-update-misc-options', 'wpp-update-misc-options-token'); ?>
        </form>
        <br />
        <p style="display: block; float: none; clear: both;">&nbsp;</p>

        <div style="display: <?php echo ( current_user_can('manage_options') ) ? 'block' : 'none'; ?>">
            <br /><br />

            <p><?php _e('WordPress Popular Posts maintains data in two separate tables: one for storing the most popular entries on a daily basis (from now on, "cache"), and another one to keep the All-time data (from now on, "historical data" or just "data"). If for some reason you need to clear the cache table, or even both historical and cache tables, please use the buttons below to do so.', 'wordpress-popular-posts') ?></p>
            <p><input type="button" name="wpp-reset-cache" id="wpp-reset-cache" class="button-secondary" value="<?php _e("Empty cache", 'wordpress-popular-posts'); ?>"> <label for="wpp-reset-cache"><small><?php _e('Use this button to manually clear entries from WPP cache only', 'wordpress-popular-posts'); ?></small></label></p>
            <p><input type="button" name="wpp-reset-all" id="wpp-reset-all" class="button-secondary" value="<?php _e("Clear all data", 'wordpress-popular-posts'); ?>"> <label for="wpp-reset-all"><small><?php _e('Use this button to manually clear entries from all WPP data tables', 'wordpress-popular-posts'); ?></small></label></p>
        </div>
    </div>
    <!-- End tools -->

    <!-- Start debug -->
    <?php
    global $wpdb, $wp_version;

    $my_theme = wp_get_theme();
    $site_plugins = get_plugins();
    $plugin_names = [];
    $performance_nag = get_option('wpp_performance_nag');

    if ( ! $performance_nag ) {
        $performance_nag = [
            'status' => 0,
            'last_checked' => null
        ];
    }

    switch($performance_nag['status']) {
        case 0:
            $performance_nag_status = 'Inactive';
            break;
        case 1:
            $performance_nag_status = 'Active';
            break;
        case 2:
            $performance_nag_status = 'Remind me later';
        break;
        case 3:
            $performance_nag_status = 'Dismissed';
            break;
        default:
            $performance_nag_status = 'Inactive';
            break;
    }

    foreach( $site_plugins as $main_file => $plugin_meta ) :
        if ( ! is_plugin_active($main_file) )
            continue;
        $plugin_names[] = sanitize_text_field($plugin_meta['Name'] . ' ' . $plugin_meta['Version']);
    endforeach;
    ?>
    <div id="wpp_debug" <?php echo ( "debug" == $current ) ? '' : ' style="display: none;"'; ?>>
        <h3>Plugin Configuration</h3>
        <p><strong>Performance Nag:</strong> <?php echo $performance_nag_status; ?></p>
        <p><strong>Log Limit:</strong> <?php echo ( $this->config['tools']['log']['limit'] ) ? 'Yes, keep data for ' . $this->config['tools']['log']['expires_after'] . ' days' : 'No'; ?></p>
        <p><strong>Log Views From:</strong> <?php echo ( 0 == $this->config['tools']['log']['level'] ) ? 'Visitors only' : ( (2 == $this->config['tools']['log']['level']) ? 'Logged-in users only' : 'Everyone' ); ?></p>
        <p><strong>Data Caching:</strong> <?php echo ( $this->config['tools']['cache']['active'] ) ? 'Yes, ' . $this->config['tools']['cache']['interval']['value'] . ' ' . $this->config['tools']['cache']['interval']['time'] : 'No'; ?></p>
        <p><strong>Data Sampling:</strong> <?php echo ( $this->config['tools']['sampling']['active'] ) ? 'Yes, with a rate of ' . $this->config['tools']['sampling']['rate'] : 'No'; ?></p>
        <p><strong>External object cache:</strong> <?php echo ( wp_using_ext_object_cache() ) ? 'Yes' : 'No'; ?></p>
        <p><strong>WPP_CACHE_VIEWS:</strong> <?php echo ( defined('WPP_CACHE_VIEWS') && WPP_CACHE_VIEWS ) ? 'Yes' : 'No'; ?></p>

        <br />

        <h3>System Info</h3>
        <p><strong>PHP version:</strong> <?php echo phpversion(); ?></p>
        <p><strong>PHP extensions:</strong> <?php echo implode(', ', get_loaded_extensions()); ?></p>
        <p><strong>Database version:</strong> <?php echo $wpdb->get_var("SELECT VERSION();"); ?></p>
        <p><strong>InnoDB availability:</strong> <?php echo $wpdb->get_var("SELECT SUPPORT FROM INFORMATION_SCHEMA.ENGINES WHERE ENGINE = 'InnoDB';"); ?></p>
        <p><strong>WordPress version:</strong> <?php echo $wp_version; ?></p>
        <p><strong>Multisite:</strong> <?php echo ( function_exists('is_multisite') && is_multisite() ) ? 'Yes' : 'No'; ?></p>
        <p><strong>Active plugins:</strong> <?php echo implode(', ', $plugin_names); ?></p>
        <p><strong>Theme:</strong> <?php echo $my_theme->get('Name') . ' (' . $my_theme->get('Version') . ') by ' . $my_theme->get('Author'); ?></p>
    </div>
    <!-- End debug -->
</div>