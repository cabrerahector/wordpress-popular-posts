<?php
if ( basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__) )
    exit( 'Please do not load this page directly' );

$tabs = array(
    'stats' => __( 'Stats', 'wordpress-popular-posts' ),
    'tools' => __( 'Tools', 'wordpress-popular-posts' ),
    'params' => __( 'Parameters', 'wordpress-popular-posts' ),
    'debug' => 'Debug'
);

// Set active tab
if ( isset( $_GET['tab'] ) && isset( $tabs[$_GET['tab']] ) )
    $current = $_GET['tab'];
else
    $current = 'stats';

// Update options on form submission
if ( isset($_POST['section']) ) {

    if ( "stats" == $_POST['section'] ) {

        $current = 'stats';

        if ( isset( $_POST['wpp-admin-token'] ) && wp_verify_nonce( $_POST['wpp-admin-token'], 'wpp-update-stats-options' ) ) {

            //$this->options['stats']['order_by'] = $_POST['stats_order'];
            $this->options['stats']['limit'] = ( WPP_Helper::is_number( $_POST['stats_limit'] ) && $_POST['stats_limit'] > 0 ) ? $_POST['stats_limit'] : 10;
            $this->options['stats']['post_type'] = empty( $_POST['stats_type'] ) ? "post,page" : $_POST['stats_type'];
            $this->options['stats']['freshness'] = empty( $_POST['stats_freshness'] ) ? false : $_POST['stats_freshness'];

            update_option( 'wpp_settings_config', $this->options );
            echo "<div class=\"notice notice-success is-dismissible\"><p><strong>" . __( 'Settings saved.', 'wordpress-popular-posts' ) . "</strong></p></div>";

        }

    }
    elseif ( "misc" == $_POST['section'] ) {

        $current = 'tools';

        if ( isset( $_POST['wpp-admin-token'] ) && wp_verify_nonce( $_POST['wpp-admin-token'], 'wpp-update-misc-options' ) ) {

            $this->options['tools']['link']['target'] = $_POST['link_target'];
            $this->options['tools']['css'] = $_POST['css'];

            update_option( 'wpp_settings_config', $this->options );
            echo "<div class=\"notice notice-success is-dismissible\"><p><strong>" . __( 'Settings saved.', 'wordpress-popular-posts' ) . "</strong></p></div>";

        }
    }
    elseif ( "thumb" == $_POST['section'] ) {

        $current = 'tools';

        if ( isset( $_POST['wpp-admin-token'] ) && wp_verify_nonce( $_POST['wpp-admin-token'], 'wpp-update-thumbnail-options' ) ) {

            if (
                $_POST['thumb_source'] == "custom_field"
                && ( !isset( $_POST['thumb_field'] ) || empty( $_POST['thumb_field'] ) )
            ) {
                echo '<div id="wpp-message" class="error fade"><p>'.__( 'Please provide the name of your custom field.', 'wordpress-popular-posts' ).'</p></div>';
            } else {

                $this->options['tools']['thumbnail']['source'] = $_POST['thumb_source'];
                $this->options['tools']['thumbnail']['field'] = ( !empty( $_POST['thumb_field']) ) ? $_POST['thumb_field'] : "wpp_thumbnail";
                $this->options['tools']['thumbnail']['default'] = ( !empty( $_POST['upload_thumb_src']) ) ? $_POST['upload_thumb_src'] : "";
                $this->options['tools']['thumbnail']['resize'] = $_POST['thumb_field_resize'];

                update_option( 'wpp_settings_config', $this->options );
                echo "<div class=\"notice notice-success is-dismissible\"><p><strong>" . __( 'Settings saved.', 'wordpress-popular-posts' ) . "</strong></p></div>";

            }

        }

    }
    elseif ( "data" == $_POST['section'] ) {

        $current = 'tools';

        if ( isset( $_POST['wpp-admin-token'] ) && wp_verify_nonce( $_POST['wpp-admin-token'], 'wpp-update-data-options' ) ) {

            $this->options['tools']['log']['level'] = $_POST['log_option'];
            $this->options['tools']['log']['limit'] = $_POST['log_limit'];
            $this->options['tools']['log']['expires_after'] = ( WPP_Helper::is_number( $_POST['log_expire_time'] ) && $_POST['log_expire_time'] > 0 )
              ? $_POST['log_expire_time']
              : $this->default_user_settings['tools']['log']['expires_after'];
            $this->options['tools']['ajax'] = $_POST['ajax'];

            // if any of the caching settings was updated, destroy all transients created by the plugin
            if (
                $this->options['tools']['cache']['active'] != $_POST['cache']
                || $this->options['tools']['cache']['interval']['time'] != $_POST['cache_interval_time']
                || $this->options['tools']['cache']['interval']['value'] != $_POST['cache_interval_value']
            ) {
                $this->flush_transients();
            }

            $this->options['tools']['cache']['active'] = $_POST['cache'];
            $this->options['tools']['cache']['interval']['time'] = $_POST['cache_interval_time'];
            $this->options['tools']['cache']['interval']['value'] = ( isset( $_POST['cache_interval_value'] ) && WPP_Helper::is_number( $_POST['cache_interval_value'] ) && $_POST['cache_interval_value'] > 0 )
              ? $_POST['cache_interval_value']
              : 1;

            $this->options['tools']['sampling']['active'] = $_POST['sampling'];
            $this->options['tools']['sampling']['rate'] = ( isset( $_POST['sample_rate'] ) && WPP_Helper::is_number( $_POST['sample_rate'] ) && $_POST['sample_rate'] > 0 )
              ? $_POST['sample_rate']
              : 100;

            update_option( 'wpp_settings_config', $this->options );
            echo "<div class=\"notice notice-success is-dismissible\"><p><strong>" . __( 'Settings saved.', 'wordpress-popular-posts' ) . "</strong></p></div>";

        }
    }

}

if ( $this->options['tools']['css'] && !file_exists( get_stylesheet_directory() . '/wpp.css' ) ) {
    echo '<div id="wpp-message" class="update-nag">'. __( 'Any changes made to WPP\'s default stylesheet will be lost after every plugin update. In order to prevent this from happening, please copy the wpp.css file (located at wp-content/plugins/wordpress-popular-posts/style) into your theme\'s directory', 'wordpress-popular-posts' ) .'.</div>';
}

$rand = md5( uniqid(rand(), true) );

if ( !$wpp_rand = get_option("wpp_rand") ) {
    add_option( "wpp_rand", $rand );
} else {
    update_option( "wpp_rand", $rand );
}

?>
<script type="text/javascript">
    // TOOLS
    function confirm_reset_cache() {
        if ( confirm("<?php /*translators: Special characters (such as accents) must be replaced with Javascript Octal codes (eg. \341 is the Octal code for small a with acute accent) */ _e( "This operation will delete all entries from WordPress Popular Posts' cache table and cannot be undone.", 'wordpress-popular-posts'); ?> \n\n" + "<?php /*translators: Special characters (such as accents) must be replaced with Javascript Octal codes (eg. \341 is the Octal code for small a with acute accent) */ _e( "Do you want to continue?", 'wordpress-popular-posts' ); ?>") ) {
            jQuery.post(
                ajaxurl,
                {
                    action: 'wpp_clear_data',
                    token: '<?php echo get_option("wpp_rand"); ?>',
                    clear: 'cache'
                }, function(data){
                    var response = "";

                    switch( data ) {
                        case "1":
                            response = "<?php /*translators: Special characters (such as accents) must be replaced with Javascript Octal codes (eg. \341 is the Octal code for small a with acute accent) */ _e( 'Success! The cache table has been cleared!', 'wordpress-popular-posts' ); ?>";
                            break;

                        case "2":
                            response = "<?php /*translators: Special characters (such as accents) must be replaced with Javascript Octal codes (eg. \341 is the Octal code for small a with acute accent) */ _e( 'Error: cache table does not exist.', 'wordpress-popular-posts' ); ?>";
                            break;

                        case "3":
                            response = "<?php /*translators: Special characters (such as accents) must be replaced with Javascript Octal codes (eg. \341 is the Octal code for small a with acute accent) */ _e( 'Invalid action.', 'wordpress-popular-posts' ); ?>";
                            break;

                        case "4":
                            response = "<?php /*translators: Special characters (such as accents) must be replaced with Javascript Octal codes (eg. \341 is the Octal code for small a with acute accent) */ _e( 'Sorry, you do not have enough permissions to do this. Please contact the site administrator for support.', 'wordpress-popular-posts' ); ?>";
                            break;

                        default:
                            response = "<?php /*translators: Special characters (such as accents) must be replaced with Javascript Octal codes (eg. \341 is the Octal code for small a with acute accent) */ _e( 'Invalid action.', 'wordpress-popular-posts' ); ?>";
                            break;
                    }

                    alert( response );
                }
            );
        }
    }

    function confirm_reset_all() {
        if ( confirm("<?php /*translators: Special characters (such as accents) must be replaced with Javascript Octal codes (eg. \341 is the Octal code for small a with acute accent) */ _e( "This operation will delete all stored info from WordPress Popular Posts' data tables and cannot be undone.", 'wordpress-popular-posts'); ?> \n\n" + "<?php /*translators: Special characters (such as accents) must be replaced with Javascript Octal codes (eg. \341 is the Octal code for small a with acute accent) */ _e("Do you want to continue?", 'wordpress-popular-posts'); ?>")) {
            jQuery.post(
                ajaxurl,
                {
                    action: 'wpp_clear_data',
                    token: '<?php echo get_option("wpp_rand"); ?>',
                    clear: 'all'
                }, function(data){
                    var response = "";

                    switch( data ) {
                        case "1":
                            response = "<?php /*translators: Special characters (such as accents) must be replaced with Javascript Octal codes (eg. \341 is the Octal code for small a with acute accent) */ _e( 'Success! All data have been cleared!', 'wordpress-popular-posts' ); ?>";
                            break;

                        case "2":
                            response = "<?php /*translators: Special characters (such as accents) must be replaced with Javascript Octal codes (eg. \341 is the Octal code for small a with acute accent) */ _e( 'Error: one or both data tables are missing.', 'wordpress-popular-posts' ); ?>";
                            break;

                        case "3":
                            response = "<?php /*translators: Special characters (such as accents) must be replaced with Javascript Octal codes (eg. \341 is the Octal code for small a with acute accent) */ _e( 'Invalid action.', 'wordpress-popular-posts' ); ?>";
                            break;

                        case "4":
                            response = "<?php /*translators: Special characters (such as accents) must be replaced with Javascript Octal codes (eg. \341 is the Octal code for small a with acute accent) */ _e( 'Sorry, you do not have enough permissions to do this. Please contact the site administrator for support.', 'wordpress-popular-posts' ); ?>";
                            break;

                        default:
                            response = "<?php /*translators: Special characters (such as accents) must be replaced with Javascript Octal codes (eg. \341 is the Octal code for small a with acute accent) */ _e( 'Invalid action.', 'wordpress-popular-posts' ); ?>";
                            break;
                    }

                    alert( response );
                }
            );
        }
    }

    function confirm_clear_image_cache() {
        if ( confirm("<?php /*translators: Special characters (such as accents) must be replaced with Javascript Octal codes (eg. \341 is the Octal code for small a with acute accent) */ _e("This operation will delete all cached thumbnails and cannot be undone.", 'wordpress-popular-posts'); ?> \n\n" + "<?php /*translators: Special characters (such as accents) must be replaced with Javascript Octal codes (eg. \341 is the Octal code for small a with acute accent) */ _e( "Do you want to continue?", 'wordpress-popular-posts' ); ?>") ) {
            jQuery.post(
                ajaxurl,
                {
                    action: 'wpp_clear_thumbnail',
                    token: '<?php echo get_option("wpp_rand"); ?>'
                }, function(data){
                    var response = "";

                    switch( data ) {
                        case "1":
                            response = "<?php /*translators: Special characters (such as accents) must be replaced with Javascript Octal codes (eg. \341 is the Octal code for small a with acute accent) */ _e( 'Success! All files have been deleted!', 'wordpress-popular-posts' ); ?>";
                            break;

                        case "2":
                            response = "<?php /*translators: Special characters (such as accents) must be replaced with Javascript Octal codes (eg. \341 is the Octal code for small a with acute accent) */ _e( 'The thumbnail cache is already empty!', 'wordpress-popular-posts' ); ?>";
                            break;

                        case "3":
                            response = "<?php /*translators: Special characters (such as accents) must be replaced with Javascript Octal codes (eg. \341 is the Octal code for small a with acute accent) */ _e( 'Invalid action.', 'wordpress-popular-posts' ); ?>";
                            break;

                        case "4":
                            response = "<?php /*translators: Special characters (such as accents) must be replaced with Javascript Octal codes (eg. \341 is the Octal code for small a with acute accent) */ _e( 'Sorry, you do not have enough permissions to do this. Please contact the site administrator for support.', 'wordpress-popular-posts' ); ?>";
                            break;

                        default:
                            response = "<?php /*translators: Special characters (such as accents) must be replaced with Javascript Octal codes (eg. \341 is the Octal code for small a with acute accent) */ _e( 'Invalid action.', 'wordpress-popular-posts' ); ?>";
                            break;
                    }

                    alert( response );
                }
            );
        }
    }
</script>

<nav id="wpp-menu">
    <ul>
        <li><a href="#" title="<?php esc_attr_e( 'Menu' ); ?>"><span><?php _e( 'Menu' ); ?></span></a></li>
        <li<?php echo ( 'stats' == $current ) ? ' class="current"' : ''; ?>><a href="<?php echo admin_url( 'options-general.php?page=wordpress-popular-posts&tab=stats' ); ?>" title="<?php esc_attr_e( 'Stats', 'wordpress-popular-posts' ); ?>"><span><?php _e( 'Stats', 'wordpress-popular-posts' ); ?></span></a></li>
        <li<?php echo ( 'tools' == $current ) ? ' class="current"' : ''; ?>><a href="<?php echo admin_url( 'options-general.php?page=wordpress-popular-posts&tab=tools' ); ?>" title="<?php esc_attr_e( 'Tools', 'wordpress-popular-posts' ); ?>"><span><?php _e( 'Tools', 'wordpress-popular-posts' ); ?></span></a></li>
        <li<?php echo ( 'params' == $current ) ? ' class="current"' : ''; ?>><a href="<?php echo admin_url( 'options-general.php?page=wordpress-popular-posts&tab=params' ); ?>" title="<?php esc_attr_e( 'Parameters', 'wordpress-popular-posts' ); ?>"><span><?php _e( 'Parameters', 'wordpress-popular-posts' ); ?></span></a></li>
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

        $chart_data = $this->get_chart_data( $this->options['stats']['range'] );

    ?>

    <a href="#" id="wpp-stats-config-btn" class="dashicons dashicons-admin-generic"></a>

    <div id="wpp-stats-config" class="wpp-lightbox">

        <form action="" method="post" id="wpp_stats_options" name="wpp_stats_options">

            <label for="stats_type"><?php _e("Post type", 'wordpress-popular-posts'); ?>:</label>
            <input type="text" name="stats_type" value="<?php echo esc_attr( $this->options['stats']['post_type'] ); ?>" size="15" />

            <label for="stats_limits"><?php _e("Limit", 'wordpress-popular-posts'); ?>:</label>
            <input type="text" name="stats_limit" value="<?php echo $this->options['stats']['limit']; ?>" size="5" />

            <label for="stats_freshness"><input type="checkbox" class="checkbox" <?php echo ($this->options['stats']['freshness']) ? 'checked="checked"' : ''; ?> id="stats_freshness" name="stats_freshness" /> <small><?php _e('Display only posts published within the selected Time Range', 'wordpress-popular-posts'); ?></small></label>

            <div class="clear"></div>
            <br /><br />

            <input type="hidden" name="section" value="stats" />
            <button type="submit" class="button-primary action"><?php _e("Apply", 'wordpress-popular-posts'); ?></button>
            <button class="button-secondary action right"><?php _e("Cancel"); ?></button>

            <?php wp_nonce_field( 'wpp-update-stats-options', 'wpp-admin-token' ); ?>

        </form>

    </div>

    <div id="wpp-stats-range" class="wpp-lightbox">

        <form action="" method="post">

            <ul class="wpp-lightbox-tabs">
                <li class="active"><a href="#"><?php _e('Custom Time Range', 'wordpress-popular-posts'); ?></a></li>
                <li><a href="#"><?php _e('Date Range', 'wordpress-popular-posts'); ?></a></li>
            </ul>

            <div class="wpp-lightbox-tab-content wpp-lightbox-tab-content-active" id="custom-time-range">

                <input type="text" id="stats_range_time_quantity" name="stats_range_time_quantity" value="<?php echo $this->options['stats']['time_quantity']; ?>">

                <select id="stats_range_time_unit" name="stats_range_time_unit">
                    <option <?php if ($this->options['stats']['time_unit'] == "minute") {?>selected="selected"<?php } ?> value="minute"><?php _e("Minute(s)", 'wordpress-popular-posts'); ?></option>
                    <option <?php if ($this->options['stats']['time_unit'] == "hour") {?>selected="selected"<?php } ?> value="hour"><?php _e("Hour(s)", 'wordpress-popular-posts'); ?></option>
                    <option <?php if ($this->options['stats']['time_unit'] == "day") {?>selected="selected"<?php } ?> value="day"><?php _e("Day(s)", 'wordpress-popular-posts'); ?></option>
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
            <li <?php echo ( 'daily' == $this->options['stats']['range'] || 'today' == $this->options['stats']['range'] ) ? ' class="current"' : ''; ?>><a href="#" data-range="today" title="<?php esc_attr_e( 'Today', 'wordpress-popular-posts' ); ?>"><?php _e( 'Today', 'wordpress-popular-posts' ); ?></a></li>
            <li <?php echo ( 'daily' == $this->options['stats']['range'] || 'last24hours' == $this->options['stats']['range'] ) ? ' class="current"' : ''; ?>><a href="#" data-range="last24hours" title="<?php esc_attr_e( 'Last 24 hours', 'wordpress-popular-posts' ); ?>">24h</a></li>
            <li <?php echo ( 'weekly' == $this->options['stats']['range'] || 'last7days' == $this->options['stats']['range'] ) ? ' class="current"' : ''; ?>><a href="#" data-range="last7days" title="<?php esc_attr_e( 'Last 7 days', 'wordpress-popular-posts' ); ?>">7d</a></li>
            <li <?php echo ( 'monthly' == $this->options['stats']['range'] || 'last30days' == $this->options['stats']['range'] ) ? ' class="current"' : ''; ?>><a href="#" data-range="last30days" title="<?php esc_attr_e( 'Last 30 days', 'wordpress-popular-posts' ); ?>">30d</a></li>
            <li <?php echo ( 'custom' == $this->options['stats']['range'] ) ? ' class="current"' : ''; ?>><a href="#"  data-range="custom" title="<?php esc_attr_e( 'Custom', 'wordpress-popular-posts' ); ?>"><?php _e( 'Custom', 'wordpress-popular-posts' ); ?></a></li>
        </ul>

        <div id="wpp-chart">
            <p><?php echo sprintf( __('Err... A nice little chart is supposed to be here, instead you are seeing this because your browser is too old. <br /> Please <a href="%s" target="_blank">get a better browser</a>.', 'wordpress-popular-posts'), 'https://browsehappy.com/'); ?></p>

            <?php $this->print_chart_script( $chart_data, 'wpp-chart' ); ?>
        </div>
    </div>
    <?php
    } // End stats chart
    ?>

    <div id="wpp-listing" class="wpp-content"<?php echo ( 'stats' == $current ) ? '' : ' style="display: none;"'; ?>>
        <ul class="wpp-tabbed-nav">
            <li class="active"><a href="#" title="<?php esc_attr_e( 'Most viewed', 'wordpress-popular-posts' ); ?>"><span class="fa fa-eye"></span><span><?php _e( 'Most viewed', 'wordpress-popular-posts' ); ?></span></a></li>
            <li><a href="#" title="<?php esc_attr_e( 'Most commented', 'wordpress-popular-posts' ); ?>"><span class="fa fa-comment-o"></span><span><?php _e( 'Most commented', 'wordpress-popular-posts' ); ?></span></a></li>
            <li><a href="#" title="<?php esc_attr_e( 'Trending now', 'wordpress-popular-posts' ); ?>"><span class="fa fa-rocket"></span><span><?php _e( 'Trending now', 'wordpress-popular-posts' ); ?></span></a></li>
            <li><a href="#" title="<?php esc_attr_e( 'Hall of Fame', 'wordpress-popular-posts' ); ?>"><span class="fa fa-trophy"></span><span><?php _e( 'Hall of Fame', 'wordpress-popular-posts' ); ?></span></a></li>
        </ul>

        <div class="wpp-tab-content wpp-tab-content-active">
            <?php $this->get_most_viewed(); ?>
        </div>

        <div class="wpp-tab-content">
            <?php $this->get_most_commented(); ?>
        </div>

        <div class="wpp-tab-content">
            <?php
            $args = array(
                'range' => 'custom',
                'time_unit' => 'HOUR',
                'time_quantity'=> 1,
                'post_type' => $this->options['stats']['post_type'],
                'order_by' => 'views',
                'limit' => $this->options['stats']['limit'],
                'stats_tag' => array(
                    'comment_count' => 1,
                    'views' => 1,
                    'date' => array(
                        'active' => 1
                    )
                )
            );
            $trending = new WPP_query( $args );
            $posts = $trending->get_posts();

            if (
                is_array( $posts )
                && !empty( $posts )
            ) {
            ?>
            <ol class="popular-posts-list">
            <?php
                foreach ( $posts as $post ) { ?>
                <li>
                    <p>
                        <a href="<?php echo get_permalink( $post->id ); ?>"><?php echo sanitize_text_field( $post->title ); ?></a>
                        <br />
                        <span><?php printf( _n( '1 view', '%s views', $post->pageviews, 'wordpress-popular-posts' ), number_format_i18n( $post->pageviews ) ); ?>, <?php printf( _n( '1 comment', '%s comments', $post->comment_count, 'wordpress-popular-posts' ), number_format_i18n( $post->comment_count ) ); ?></span>
                        <small> &mdash; <a href="<?php echo get_permalink( $post->id ); ?>"><?php _e("View"); ?></a> | <a href="<?php echo get_edit_post_link( $post->id ); ?>"><?php _e("Edit"); ?></a></small>
                    </p>
                </li>
                <?php
                }
            ?>
            </ol>
            <?php
            }
            else {
            ?>
            <p style="text-align: center;"><?php _e("Looks like traffic to your site is a little light right now. <br />Spread the word and come back later!", "wordpress-popular-posts"); ?></p>
            <?php
            }
            ?>
        </div>
        <div class="wpp-tab-content">
            <?php
            $args = array(
                'range' => 'all',
                'post_type' => $this->options['stats']['post_type'],
                'order_by' => 'views',
                'limit' => $this->options['stats']['limit'],
                'stats_tag' => array(
                    'comment_count' => 1,
                    'views' => 1,
                    'date' => array(
                        'active' => 1
                    )
                )
            );
            $hof = new WPP_query( $args );
            $posts = $hof->get_posts();

            if (
                is_array( $posts )
                && !empty( $posts )
            ) {
            ?>
            <ol class="popular-posts-list">
            <?php
                foreach ( $posts as $post ) { ?>
                <li>
                    <p>
                        <a href="<?php echo get_permalink( $post->id ); ?>"><?php echo sanitize_text_field( $post->title ); ?></a>
                        <br />
                        <span><?php printf( _n( '1 view', '%s views', $post->pageviews, 'wordpress-popular-posts' ), number_format_i18n( $post->pageviews ) ); ?>, <?php printf( _n( '1 comment', '%s comments', $post->comment_count, 'wordpress-popular-posts' ), number_format_i18n( $post->comment_count ) ); ?></span>
                        <small> &mdash; <a href="<?php echo get_permalink( $post->id ); ?>"><?php _e("View"); ?></a> | <a href="<?php echo get_edit_post_link( $post->id ); ?>"><?php _e("Edit"); ?></a></small>
                    </p>
                </li>
                <?php
                }
            ?>
            </ol>
            <?php
            }
            else {
            ?>
            <p style="text-align: center;"><?php _e("Looks like traffic to your site is a little light right now. <br />Spread the word and come back later!", "wordpress-popular-posts"); ?></p>
            <?php
            }
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
                            <div id="thumb-review">
                                <img src="<?php echo ( $this->options['tools']['thumbnail']['default'] ) ? str_replace( parse_url( $this->options['tools']['thumbnail']['default'], PHP_URL_SCHEME ) . ':', '', $this->options['tools']['thumbnail']['default'] ) : plugins_url() . '/wordpress-popular-posts/public/images/no_thumb.jpg'; ?>" alt="" border="0" />
                            </div>
                            <input id="upload_thumb_button" type="button" class="button" value="<?php _e( "Change thumbnail", 'wordpress-popular-posts' ); ?>" />
                            <input type="hidden" id="upload_thumb_src" name="upload_thumb_src" value="" />
                            <p class="description"><?php _e("This image will be displayed when no thumbnail is available", 'wordpress-popular-posts'); ?>.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="thumb_source"><?php _e("Pick image from", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <select name="thumb_source" id="thumb_source">
                                <option <?php if ($this->options['tools']['thumbnail']['source'] == "featured") {?>selected="selected"<?php } ?> value="featured"><?php _e("Featured image", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->options['tools']['thumbnail']['source'] == "first_image") {?>selected="selected"<?php } ?> value="first_image"><?php _e("First image on post", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->options['tools']['thumbnail']['source'] == "first_attachment") {?>selected="selected"<?php } ?> value="first_attachment"><?php _e("First attachment", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->options['tools']['thumbnail']['source'] == "custom_field") {?>selected="selected"<?php } ?> value="custom_field"><?php _e("Custom field", 'wordpress-popular-posts'); ?></option>
                            </select>
                            <br />
                            <p class="description"><?php _e("Tell WordPress Popular Posts where it should get thumbnails from", 'wordpress-popular-posts'); ?>.</p>
                        </td>
                    </tr>
                    <tr valign="top" <?php if ($this->options['tools']['thumbnail']['source'] != "custom_field") {?>style="display:none;"<?php } ?> id="row_custom_field">
                        <th scope="row"><label for="thumb_field"><?php _e("Custom field name", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <input type="text" id="thumb_field" name="thumb_field" value="<?php echo esc_attr( $this->options['tools']['thumbnail']['field'] ); ?>" size="10" <?php if ($this->options['tools']['thumbnail']['source'] != "custom_field") {?>style="display:none;"<?php } ?> />
                        </td>
                    </tr>
                    <tr valign="top" <?php if ($this->options['tools']['thumbnail']['source'] != "custom_field") {?>style="display:none;"<?php } ?> id="row_custom_field_resize">
                        <th scope="row"><label for="thumb_field_resize"><?php _e("Resize image from Custom field?", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <select name="thumb_field_resize" id="thumb_field_resize">
                                <option <?php if ( !$this->options['tools']['thumbnail']['resize'] ) {?>selected="selected"<?php } ?> value="0"><?php _e("No, I will upload my own thumbnail", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ( $this->options['tools']['thumbnail']['resize'] == 1 ) {?>selected="selected"<?php } ?> value="1"><?php _e("Yes", 'wordpress-popular-posts'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <?php
                    $wp_upload_dir = wp_get_upload_dir();
                    if ( is_dir( $wp_upload_dir['basedir'] . "/" . 'wordpress-popular-posts' ) ) :
                    ?>
                    <tr valign="top">
                        <th scope="row"></th>
                        <td>
                            <input type="button" name="wpp-reset-cache" id="wpp-reset-cache" class="button-secondary" value="<?php _e("Empty image cache", 'wordpress-popular-posts'); ?>" onclick="confirm_clear_image_cache()" />
                            <p class="description"><?php _e("Use this button to clear WPP's thumbnails cache", 'wordpress-popular-posts'); ?>.</p>
                        </td>
                    </tr>
                    <?php
                    endif;
                    ?>
                    <tr valign="top">
                        <td colspan="2">
                            <input type="hidden" name="section" value="thumb" />
                            <input type="submit" class="button-primary action" id="btn_th_ops" value="<?php _e("Apply", 'wordpress-popular-posts'); ?>" name="" />
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php wp_nonce_field( 'wpp-update-thumbnail-options', 'wpp-admin-token' ); ?>
        </form>
        <br />
        <p style="display:block; float:none; clear:both">&nbsp;</p>

        <h3 class="wmpp-subtitle"><?php _e("Data", 'wordpress-popular-posts'); ?></h3>
        <form action="" method="post" id="wpp_ajax_options" name="wpp_ajax_options">
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="log_option"><?php _e("Log views from", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <select name="log_option" id="log_option">
                                <option <?php if ($this->options['tools']['log']['level'] == 0) {?>selected="selected"<?php } ?> value="0"><?php _e("Visitors only", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->options['tools']['log']['level'] == 2) {?>selected="selected"<?php } ?> value="2"><?php _e("Logged-in users only", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->options['tools']['log']['level'] == 1) {?>selected="selected"<?php } ?> value="1"><?php _e("Everyone", 'wordpress-popular-posts'); ?></option>
                            </select>
                            <br />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="log_limit"><?php _e("Log limit", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <select name="log_limit" id="log_limit">
                                <option <?php if ($this->options['tools']['log']['limit'] == 0) {?>selected="selected"<?php } ?> value="0"><?php _e("Disabled", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->options['tools']['log']['limit'] == 1) {?>selected="selected"<?php } ?> value="1"><?php _e("Keep data for", 'wordpress-popular-posts'); ?></option>
                            </select>

                            <label for="log_expire_time"<?php echo ($this->options['tools']['log']['limit'] == 0) ? ' style="display:none;"' : ''; ?>><input type="text" id="log_expire_time" name="log_expire_time" value="<?php echo esc_attr( $this->options['tools']['log']['expires_after'] ); ?>" size="3" /> <?php _e("day(s)", 'wordpress-popular-posts'); ?></label>

                            <p class="description"<?php echo ($this->options['tools']['log']['limit'] == 0) ? ' style="display:none;"' : ''; ?>><?php _e("Data older than the specified time frame will be automatically discarded", 'wordpress-popular-posts'); ?>.</p>

                            <br<?php echo ($this->options['tools']['log']['limit'] == 1) ? ' style="display:none;"' : ''; ?> />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="ajax"><?php _e("Ajaxify widget", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <select name="ajax" id="ajax">
                                <option <?php if (!$this->options['tools']['ajax']) {?>selected="selected"<?php } ?> value="0"><?php _e("Disabled", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->options['tools']['ajax']) {?>selected="selected"<?php } ?> value="1"><?php _e("Enabled", 'wordpress-popular-posts'); ?></option>
                            </select>

                            <br />
                            <p class="description"><?php _e("If you are using a caching plugin such as WP Super Cache, enabling this feature will keep the popular list from being cached by it", 'wordpress-popular-posts'); ?>.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="cache"><?php _e("Data Caching", 'wordpress-popular-posts'); ?>:</label> <small>[<a href="https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance#caching" target="_blank" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small></th>
                        <td>
                            <select name="cache" id="cache">
                                <option <?php if ( !$this->options['tools']['cache']['active'] ) { ?>selected="selected"<?php } ?> value="0"><?php _e("Never cache", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ( $this->options['tools']['cache']['active'] ) { ?>selected="selected"<?php } ?> value="1"><?php _e("Enable caching", 'wordpress-popular-posts'); ?></option>
                            </select>

                            <br />
                            <p class="description"><?php _e("WPP can cache the popular list for a specified amount of time. Recommended for large / high traffic sites", 'wordpress-popular-posts'); ?>.</p>
                        </td>
                    </tr>
                    <tr valign="top" <?php if ( !$this->options['tools']['cache']['active'] ) { ?>style="display:none;"<?php } ?> id="cache_refresh_interval">
                        <th scope="row"><label for="cache_interval_value"><?php _e("Refresh cache every", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <input name="cache_interval_value" type="text" id="cache_interval_value" value="<?php echo ( isset($this->options['tools']['cache']['interval']['value']) ) ? (int) $this->options['tools']['cache']['interval']['value'] : 1; ?>" class="small-text">
                            <select name="cache_interval_time" id="cache_interval_time">
                                <option <?php if ($this->options['tools']['cache']['interval']['time'] == "minute") {?>selected="selected"<?php } ?> value="minute"><?php _e("Minute(s)", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->options['tools']['cache']['interval']['time'] == "hour") {?>selected="selected"<?php } ?> value="hour"><?php _e("Hour(s)", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->options['tools']['cache']['interval']['time'] == "day") {?>selected="selected"<?php } ?> value="day"><?php _e("Day(s)", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->options['tools']['cache']['interval']['time'] == "week") {?>selected="selected"<?php } ?> value="week"><?php _e("Week(s)", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->options['tools']['cache']['interval']['time'] == "month") {?>selected="selected"<?php } ?> value="month"><?php _e("Month(s)", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ($this->options['tools']['cache']['interval']['time'] == "year") {?>selected="selected"<?php } ?> value="month"><?php _e("Year(s)", 'wordpress-popular-posts'); ?></option>
                            </select>
                            <br />
                            <p class="description" style="display:none;" id="cache_too_long"><?php _e("Really? That long?", 'wordpress-popular-posts'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="sampling"><?php _e("Data Sampling", 'wordpress-popular-posts'); ?>:</label> <small>[<a href="https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance#data-sampling" target="_blank" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small></th>
                        <td>
                            <select name="sampling" id="sampling">
                                <option <?php if ( !$this->options['tools']['sampling']['active'] ) { ?>selected="selected"<?php } ?> value="0"><?php _e("Disabled", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ( $this->options['tools']['sampling']['active'] ) { ?>selected="selected"<?php } ?> value="1"><?php _e("Enabled", 'wordpress-popular-posts'); ?></option>
                            </select>

                            <br />
                            <p class="description"><?php echo sprintf( __('By default, WordPress Popular Posts stores in database every single visit your site receives. For small / medium sites this is generally OK, but on large / high traffic sites the constant writing to the database may have an impact on performance. With <a href="%1$s" target="_blank">data sampling</a>, WordPress Popular Posts will store only a subset of your traffic and report on the tendencies detected in that sample set (for more, <a href="%2$s" target="_blank">please read here</a>)', 'wordpress-popular-posts'), 'http://en.wikipedia.org/wiki/Sample_%28statistics%29', 'https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance#data-sampling' ); ?>.</p>
                        </td>
                    </tr>
                    <tr valign="top" <?php if ( !$this->options['tools']['sampling']['active'] ) { ?>style="display:none;"<?php } ?>>
                        <th scope="row"><label for="sample_rate"><?php _e("Sample Rate", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <input name="sample_rate" type="text" id="sample_rate" value="<?php echo ( isset($this->options['tools']['sampling']['rate']) ) ? (int) $this->options['tools']['sampling']['rate'] : 100; ?>" class="small-text">
                            <br />
                            <p class="description"><?php echo sprintf( __("A sampling rate of %d is recommended for large / high traffic sites. For lower traffic sites, you should lower the value", 'wordpress-popular-posts'), WPP_Settings::$defaults['admin_options']['tools']['sampling']['rate'] ); ?>.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <td colspan="2">
                            <input type="hidden" name="section" value="data" />
                            <input type="submit" class="button-primary action" id="btn_ajax_ops" value="<?php _e("Apply", 'wordpress-popular-posts'); ?>" name="" />
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php wp_nonce_field( 'wpp-update-data-options', 'wpp-admin-token' ); ?>
        </form>
        <br />
        <p style="display:block; float:none; clear:both">&nbsp;</p>

        <h3 class="wmpp-subtitle"><?php _e("Miscellaneous", 'wordpress-popular-posts'); ?></h3>
        <form action="" method="post" id="wpp_link_options" name="wpp_link_options">
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="link_target"><?php _e("Open links in", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <select name="link_target" id="link_target">
                                <option <?php if ( $this->options['tools']['link']['target'] == '_self' ) {?>selected="selected"<?php } ?> value="_self"><?php _e("Current window", 'wordpress-popular-posts'); ?></option>
                                <option <?php if ( $this->options['tools']['link']['target'] == '_blank' ) {?>selected="selected"<?php } ?> value="_blank"><?php _e("New tab/window", 'wordpress-popular-posts'); ?></option>
                            </select>
                            <br />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="css"><?php _e("Use plugin's stylesheet", 'wordpress-popular-posts'); ?>:</label></th>
                        <td>
                            <select name="css" id="css">
                                <option <?php if ($this->options['tools']['css']) {?>selected="selected"<?php } ?> value="1"><?php _e("Enabled", 'wordpress-popular-posts'); ?></option>
                                <option <?php if (!$this->options['tools']['css']) {?>selected="selected"<?php } ?> value="0"><?php _e("Disabled", 'wordpress-popular-posts'); ?></option>
                            </select>
                            <br />
                            <p class="description"><?php _e("By default, the plugin includes a stylesheet called wpp.css which you can use to style your popular posts listing. If you wish to use your own stylesheet or do not want it to have it included in the header section of your site, use this.", 'wordpress-popular-posts'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <td colspan="2">
                            <input type="hidden" name="section" value="misc" />
                            <input type="submit" class="button-primary action" value="<?php _e("Apply", 'wordpress-popular-posts'); ?>" name="" />
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php wp_nonce_field( 'wpp-update-misc-options', 'wpp-admin-token' ); ?>
        </form>
        <br />
        <p style="display:block; float:none; clear:both">&nbsp;</p>

        <br /><br />

        <p><?php _e('WordPress Popular Posts maintains data in two separate tables: one for storing the most popular entries on a daily basis (from now on, "cache"), and another one to keep the All-time data (from now on, "historical data" or just "data"). If for some reason you need to clear the cache table, or even both historical and cache tables, please use the buttons below to do so.', 'wordpress-popular-posts') ?></p>
        <p><input type="button" name="wpp-reset-cache" id="wpp-reset-cache" class="button-secondary" value="<?php _e("Empty cache", 'wordpress-popular-posts'); ?>" onclick="confirm_reset_cache()" /> <label for="wpp-reset-cache"><small><?php _e('Use this button to manually clear entries from WPP cache only', 'wordpress-popular-posts'); ?></small></label></p>
        <p><input type="button" name="wpp-reset-all" id="wpp-reset-all" class="button-secondary" value="<?php _e("Clear all data", 'wordpress-popular-posts'); ?>" onclick="confirm_reset_all()" /> <label for="wpp-reset-all"><small><?php _e('Use this button to manually clear entries from all WPP data tables', 'wordpress-popular-posts'); ?></small></label></p>
    </div>
    <!-- End tools -->

    <!-- Start params -->
    <div id="wpp_params" <?php echo ( "params" == $current ) ? '' : ' style="display: none;"'; ?>>
        <div>
            <p><?php printf( __('With the following parameters you can customize the popular posts list when using either the <a href="%1$s">wpp_get_mostpopular() template tag</a> or the <a href="%2$s">[wpp] shortcode</a>.', 'wordpress-popular-posts'),
                'https://github.com/cabrerahector/wordpress-popular-posts/wiki/2.-Template-tags#wpp_get_mostpopular',
                'https://github.com/cabrerahector/wordpress-popular-posts/wiki/1.-Using-WPP-on-posts-&-pages'
            ); ?></p>
            <br />

            <div style="overflow-x: auto;">
                <table cellspacing="0" class="wp-list-table">
                    <thead>
                        <tr>
                            <th class="manage-column column-title"><?php _e('Parameter', 'wordpress-popular-posts'); ?></th>
                            <th class="manage-column column-title"><?php _e('What it does ', 'wordpress-popular-posts'); ?></th>
                            <th class="manage-column column-title"><?php _e('Possible values', 'wordpress-popular-posts'); ?></th>
                            <th class="manage-column column-title"><?php _e('Defaults to', 'wordpress-popular-posts'); ?></th>
                            <th class="manage-column column-title"><?php _e('Example', 'wordpress-popular-posts'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>header</strong></td>
                            <td><?php _e('Sets a heading for the list', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('None', 'wordpress-popular-posts'); ?></td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'header' => 'Popular Posts'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp header='Popular Posts']<br /><br /></td>
                        </tr>
                        <tr class="alternate">
                            <td><strong>header_start</strong></td>
                            <td><?php _e('Set the opening tag for the heading of the list', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                            <td>&lt;h2&gt;</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'header' => 'Popular Posts', <br />&nbsp;&nbsp;&nbsp;&nbsp;'header_start' => '&lt;h3 class="title"&gt;',<br />&nbsp;&nbsp;&nbsp;&nbsp;'header_end' => '&lt;/h3&gt;'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp header='Popular Posts' header_start='&lt;h3 class="title"&gt;' header_end='&lt;/h3&gt;']<br /><br /></td>
                        </tr>
                        <tr>
                            <td><strong>header_end</strong></td>
                            <td><?php _e('Set the closing tag for the heading of the list', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                            <td>&lt;/h2&gt;</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'header' => 'Popular Posts', <br />&nbsp;&nbsp;&nbsp;&nbsp;'header_start' => '&lt;h3 class="title"&gt;',<br />&nbsp;&nbsp;&nbsp;&nbsp;'header_end' => '&lt;/h3&gt;'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp header='Popular Posts' header_start='&lt;h3 class="title"&gt;' header_end='&lt;/h3&gt;']<br /><br /></td>
                        </tr>
                        <tr class="alternate">
                            <td><strong>limit</strong></td>
                            <td><?php _e('Sets the maximum number of popular posts to be shown on the listing', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Positive integer', 'wordpress-popular-posts'); ?></td>
                            <td>10</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'limit' => 5<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp limit=5]<br /><br /></td>
                        </tr>
                        <tr>
                            <td><strong>range</strong></td>
                            <td><?php _e('Tells WordPress Popular Posts to retrieve the most popular entries within the time range specified by you', 'wordpress-popular-posts'); ?></td>
                            <td>"last24hours", "last7days", "last30days", "all", "custom"</td>
                            <td>last24hours</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'range' => 'last7days'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp range='last7days']<br /><br /></td>
                        </tr>
                        <tr class="alternate">
                            <td><strong>time_quantity</strong></td>
                            <td><?php _e('Especifies the number of time units of the custom time range', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Positive integer', 'wordpress-popular-posts'); ?></td>
                            <td>24</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'range' => 'custom',<br />&nbsp;&nbsp;&nbsp;&nbsp;'time_quantity' => 1,<br />&nbsp;&nbsp;&nbsp;&nbsp;'time_unit' => 'hour'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp range='custom' time_quantity=1 time_unit='hour']<br /><br /></td>
                        </tr>
                        <tr>
                            <td><strong>time_unit</strong></td>
                            <td><?php _e('Especifies the time unit of the custom time range', 'wordpress-popular-posts'); ?></td>
                            <td>minute, hour, day, week, month</td>
                            <td>hour</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'range' => 'custom',<br />&nbsp;&nbsp;&nbsp;&nbsp;'time_quantity' => 1,<br />&nbsp;&nbsp;&nbsp;&nbsp;'time_unit' => 'hour'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp range='custom' time_quantity=1 time_unit='hour']<br /><br /></td>
                        </tr>
                        <tr class="alternate">
                            <td><strong>freshness</strong></td>
                            <td><?php _e('Tells WordPress Popular Posts to retrieve the most popular entries published within the time range specified by you', 'wordpress-popular-posts'); ?></td>
                            <td>1 (true), 0 (false)</td>
                            <td>0</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'range' => 'weekly',<br />&nbsp;&nbsp;&nbsp;&nbsp;'freshness' => 1<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp range='last7days' freshness=1]<br /><br /></td>
                        </tr>
                        <tr>
                            <td><strong>order_by</strong></td>
                            <td><?php _e('Sets the sorting option of the popular posts', 'wordpress-popular-posts'); ?></td>
                            <td>"comments", "views", "avg" <?php _e('(for average views per day)', 'wordpress-popular-posts'); ?></td>
                            <td>views</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'order_by' => 'comments'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp order_by='comments']<br /><br /></td>
                        </tr>
                        <tr class="alternate">
                            <td><strong>post_type</strong></td>
                            <td><?php _e('Defines the type of posts to show on the listing', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                            <td>post</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'post_type' => 'post,page,your-custom-post-type'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp post_type='post,page,your-custom-post-type']<br /><br /></td>
                        </tr>
                        <tr>
                            <td><strong>pid</strong></td>
                            <td><?php _e('If set, WordPress Popular Posts will exclude the specified post(s) ID(s) form the listing.', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('None', 'wordpress-popular-posts'); ?></td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'pid' => '60,25,31'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp pid='60,25,31']<br /><br /></td>
                        </tr>
                        <tr class="alternate">
                            <td><strong>cat</strong></td>
                            <td><?php _e('If set, WordPress Popular Posts will retrieve all entries that belong to the specified category ID(s). If a minus sign is used, entries associated to the category will be excluded instead.', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('None', 'wordpress-popular-posts'); ?></td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'cat' => '1,55,-74'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp cat='1,55,-74']<br /><br /></td>
                        </tr>
                        <tr>
                            <td><strong>taxonomy</strong></td>
                            <td><?php _e('If set, WordPress Popular Posts will filter posts by a given taxonomy.', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('None', 'wordpress-popular-posts'); ?></td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'taxonomy' => 'post_tag',<br />&nbsp;&nbsp;&nbsp;&nbsp;'term_id' => '118,75,15'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp taxonomy='post_tag' term_id='118,75,15']<br /><br /></td>
                        </tr>
                        <tr class="alternate">
                            <td><strong>term_id</strong></td>
                            <td><?php _e('If set, WordPress Popular Posts will retrieve all entries that belong to the specified term ID(s). If a minus sign is used, entries associated to the term(s) will be excluded instead.', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('None', 'wordpress-popular-posts'); ?></td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'taxonomy' => 'post_tag',<br />&nbsp;&nbsp;&nbsp;&nbsp;'term_id' => '118,75,15'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp taxonomy='post_tag' term_id='118,75,15']<br /><br /></td>
                        </tr>
                        <tr>
                            <td><strong>author</strong></td>
                            <td><?php _e('If set, WordPress Popular Posts will retrieve all entries created by specified author(s) ID(s).', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('None', 'wordpress-popular-posts'); ?></td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'author' => '75,8,120'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp author='75,8,120']<br /><br /></td>
                        </tr>
                        <tr class="alternate">
                            <td><strong>title_length</strong></td>
                            <td><?php _e('If set, WordPress Popular Posts will shorten each post title to "n" characters whenever possible', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Positive integer', 'wordpress-popular-posts'); ?></td>
                            <td>25</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'title_length' => 25<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp title_length=25]<br /><br /></td>
                        </tr>
                        <tr>
                            <td><strong>title_by_words</strong></td>
                            <td><?php _e('If set to 1, WordPress Popular Posts will shorten each post title to "n" words instead of characters', 'wordpress-popular-posts'); ?></td>
                            <td>1 (true), (0) false</td>
                            <td>0</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'title_by_words' => 1,<br />&nbsp;&nbsp;&nbsp;&nbsp;'title_length' => 25<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp title_by_words=1 title_length=25]<br /><br /></td>
                        </tr>
                        <tr class="alternate">
                            <td><strong>excerpt_length</strong></td>
                            <td><?php _e('If set, WordPress Popular Posts will build and include an excerpt of "n" characters long from the content of each post listed as popular', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Positive integer', 'wordpress-popular-posts'); ?></td>
                            <td>0</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'excerpt_length' => 55,<br />&nbsp;&nbsp;&nbsp;&nbsp;'post_html' => '&lt;li&gt;{thumb} {title} &lt;span class="wpp-excerpt"&gt;{summary}&lt;/span&gt;&lt;/li&gt;'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp excerpt_length=25 post_html='&lt;li&gt;{thumb} {title} &lt;span class="wpp-excerpt"&gt;{summary}&lt;/span&gt;&lt;/li&gt;']<br /><br /></td>
                        </tr>
                        <tr>
                            <td><strong>excerpt_format</strong></td>
                            <td><?php _e('If set, WordPress Popular Posts will maintaing all styling tags (strong, italic, etc) and hyperlinks found in the excerpt', 'wordpress-popular-posts'); ?></td>
                            <td>1 (true), (0) false</td>
                            <td>0</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'excerpt_format' => 1,<br />&nbsp;&nbsp;&nbsp;&nbsp;'excerpt_length' => 55,<br />&nbsp;&nbsp;&nbsp;&nbsp;'post_html' => '&lt;li&gt;{thumb} {title} &lt;span class="wpp-excerpt"&gt;{summary}&lt;/span&gt;&lt;/li&gt;'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp excerpt_format=1 excerpt_length=25 post_html='&lt;li&gt;{thumb} {title} &lt;span class="wpp-excerpt"&gt;{summary}&lt;/span&gt;&lt;/li&gt;']<br /><br /></td>
                        </tr>
                        <tr class="alternate">
                            <td><strong>excerpt_by_words</strong></td>
                            <td><?php _e('If set to 1, WordPress Popular Posts will shorten the excerpt to "n" words instead of characters', 'wordpress-popular-posts'); ?></td>
                            <td>1 (true), (0) false</td>
                            <td>0</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'excerpt_by_words' => 1,<br />&nbsp;&nbsp;&nbsp;&nbsp;'excerpt_length' => 55,<br />&nbsp;&nbsp;&nbsp;&nbsp;'post_html' => '&lt;li&gt;{thumb} {title} &lt;span class="wpp-excerpt"&gt;{summary}&lt;/span&gt;&lt;/li&gt;'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp excerpt_by_words=1 excerpt_length=55 post_html='&lt;li&gt;{thumb} {title} &lt;span class="wpp-excerpt"&gt;{summary}&lt;/span&gt;&lt;/li&gt;']<br /><br /></td>
                        </tr>
                        <tr>
                            <td><strong>thumbnail_width</strong></td>
                            <td><?php _e('If set, and if your current server configuration allows it, you will be able to display thumbnails of your posts. This attribute sets the width for thumbnails', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Positive integer', 'wordpress-popular-posts'); ?></td>
                            <td>0</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'thumbnail_width' => 30,<br />&nbsp;&nbsp;&nbsp;&nbsp;'thumbnail_height' => 30<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp thumbnail_width=30 thumbnail_height=30]<br /><br /></td>
                        </tr>
                        <tr class="alternate">
                            <td><strong>thumbnail_height</strong></td>
                            <td><?php _e('If set, and if your current server configuration allows it, you will be able to display thumbnails of your posts. This attribute sets the height for thumbnails', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Positive integer', 'wordpress-popular-posts'); ?></td>
                            <td>0</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'thumbnail_width' => 30,<br />&nbsp;&nbsp;&nbsp;&nbsp;'thumbnail_height' => 30<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp thumbnail_width=30 thumbnail_height=30]<br /><br /></td>
                        </tr>
                        <tr>
                            <td><strong>rating</strong></td>
                            <td><?php _e('If set, and if the WP-PostRatings plugin is installed and enabled on your blog, WordPress Popular Posts will show how your visitors are rating your entries', 'wordpress-popular-posts'); ?></td>
                            <td>1 (true), (0) false</td>
                            <td>0</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'rating' => 1,<br />&nbsp;&nbsp;&nbsp;&nbsp;'post_html' => '&lt;li&gt;{thumb} {title} {rating}&lt;/li&gt;'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp rating=1 post_html='&lt;li&gt;{thumb} {title} {rating}&lt;/li&gt;']<br /><br /></td>
                        </tr>
                        <tr class="alternate">
                            <td><strong>stats_comments</strong></td>
                            <td><?php _e('If set, WordPress Popular Posts will show how many comments each popular post has got during the specified time range', 'wordpress-popular-posts'); ?></td>
                            <td>1 (true), 0 (false)</td>
                            <td>0</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'stats_comments' => 1<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp stats_comments=1]<br /><br /></td>
                        </tr>
                        <tr>
                            <td><strong>stats_views</strong></td>
                            <td><?php _e('If set, WordPress Popular Posts will show how many views each popular post has got during the specified time range', 'wordpress-popular-posts'); ?></td>
                            <td>1 (true), (0) false</td>
                            <td>1</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'stats_views' => 0<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp stats_views=0]<br /><br /></td>
                        </tr>
                        <tr class="alternate">
                            <td><strong>stats_author</strong></td>
                            <td><?php _e('If set, WordPress Popular Posts will show who published each popular post on the list', 'wordpress-popular-posts'); ?></td>
                            <td>1 (true), (0) false</td>
                            <td>0</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'stats_author' => 1<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp stats_author=1]<br /><br /></td>
                        </tr>
                        <tr>
                            <td><strong>stats_date</strong></td>
                            <td><?php _e('If set, WordPress Popular Posts will display the date when each popular post on the list was published', 'wordpress-popular-posts'); ?></td>
                            <td>1 (true), (0) false</td>
                            <td>0</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'stats_date' => 1<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp stats_date=1]<br /><br /></td>
                        </tr>
                        <tr class="alternate">
                            <td><strong>stats_date_format</strong></td>
                            <td><?php _e('Sets the date format', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                            <td>0</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'stats_date' => 1,<br />&nbsp;&nbsp;&nbsp;&nbsp;'stats_date_format' => 'F j, Y'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp stats_date=1 stats_date_format='F j, Y']<br /><br /></td>
                        </tr>
                        <tr>
                            <td><strong>stats_category</strong></td>
                            <td><?php _e('If set, WordPress Popular Posts will display the categories associated to each entry', 'wordpress-popular-posts'); ?></td>
                            <td>1 (true), (0) false</td>
                            <td>0</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'stats_category' => 1, <br />&nbsp;&nbsp;&nbsp;&nbsp;'post_html' => '&lt;li&gt;{thumb} &lt;a href="{url}"&gt;{text_title}&lt;/a&gt; {category}&lt;/li&gt;'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp stats_taxonomy=1 post_html='&lt;li&gt;{thumb} &lt;a href="{url}"&gt;{text_title}&lt;/a&gt; {category}&lt;/li&gt;']<br /><br /></td>
                        </tr>
                        <tr class="alternate">
                            <td><strong>stats_taxonomy</strong></td>
                            <td><?php _e('If set, WordPress Popular Posts will display the taxonomies associated to each entry', 'wordpress-popular-posts'); ?></td>
                            <td>1 (true), (0) false</td>
                            <td>0</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'stats_taxonomy' => 1, <br />&nbsp;&nbsp;&nbsp;&nbsp;'post_html' => '&lt;li&gt;{thumb} &lt;a href="{url}"&gt;{text_title}&lt;/a&gt; {taxonomy}&lt;/li&gt;'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp stats_taxonomy=1 post_html='&lt;li&gt;{thumb} &lt;a href="{url}"&gt;{text_title}&lt;/a&gt; {taxonomy}&lt;/li&gt;']<br /><br /></td>
                        </tr>
                        <tr>
                            <td><strong>wpp_start</strong></td>
                            <td><?php _e('Sets the opening tag for the listing', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                            <td>&lt;ul&gt;</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'wpp_start' => '&lt;ol&gt;',<br />&nbsp;&nbsp;&nbsp;&nbsp;'wpp_end' => '&lt;/ol&gt;'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp wpp_start='&lt;ol&gt;' wpp_end='&lt;/ol&gt;']<br /><br /></td>
                        </tr>
                        <tr class="alternate">
                            <td><strong>wpp_end</strong></td>
                            <td><?php _e('Sets the closing tag for the listing', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string', 'wordpress-popular-posts'); ?></td>
                            <td>&lt;/ul&gt;</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'wpp_start' => '&lt;ol&gt;',<br />&nbsp;&nbsp;&nbsp;&nbsp;'wpp_end' => '&lt;/ol&gt;'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp wpp_start='&lt;ol&gt;' wpp_end='&lt;/ol&gt;']<br /><br /></td>
                        </tr>
                        <tr>
                            <td><strong>post_html</strong></td>
                            <td><?php _e('Sets the HTML structure of each post', 'wordpress-popular-posts'); ?></td>
                            <td><?php _e('Text string, custom HTML', 'wordpress-popular-posts'); ?>.<br /><br /><strong><?php _e('Available Content Tags', 'wordpress-popular-posts'); ?>:</strong> <br /><br /><em>{thumb}</em> (<?php _e('returns thumbnail linked to post/page, requires thumbnail_width & thumbnail_height', 'wordpress-popular-posts'); ?>)<br /><br /> <em>{thumb_img}</em> (<?php _e('returns thumbnail image without linking to post/page, requires thumbnail_width & thumbnail_height', 'wordpress-popular-posts'); ?>)<br /><br /> <em>{thumb_url}</em> (<?php _e('returns thumbnail url, requires thumbnail_width & thumbnail_height', 'wordpress-popular-posts'); ?>)<br /><br /> <em>{title}</em> (<?php _e('returns linked post/page title', 'wordpress-popular-posts'); ?>)<br /><br /> <em>{pid}</em> (<?php _e('returns the post/page ID', 'wordpress-popular-posts'); ?>)<br /><br /> <em>{summary}</em> (<?php _e('returns post/page excerpt, and requires excerpt_length to be greater than 0', 'wordpress-popular-posts'); ?>)<br /><br /> <em>{stats}</em> (<?php _e('returns the default stats tags', 'wordpress-popular-posts'); ?>)<br /><br /> <em>{rating}</em> (<?php _e('returns post/page current rating, requires WP-PostRatings installed and enabled', 'wordpress-popular-posts'); ?>)<br /><br /> <em>{score}</em> (<?php _e('returns post/page current rating as an integer, requires WP-PostRatings installed and enabled', 'wordpress-popular-posts'); ?>)<br /><br /> <em>{url}</em> (<?php _e('returns the URL of the post/page', 'wordpress-popular-posts'); ?>)<br /><br /> <em>{text_title}</em> (<?php _e('returns post/page title, no link', 'wordpress-popular-posts'); ?>)<br /><br /> <em>{author}</em> (<?php _e('returns linked author name, requires stats_author=1', 'wordpress-popular-posts'); ?>)<br /><br /> <em>{category}</em> (<?php _e('returns linked category name, requires stats_category=1', 'wordpress-popular-posts'); ?>)<br /><br /> <em>{taxonomy}</em> (<?php _e('returns linked taxonomy names, requires stats_taxonomy=1', 'wordpress-popular-posts'); ?>)<br /><br /> <em>{views}</em> (<?php _e('returns views count only, no text', 'wordpress-popular-posts'); ?>)<br /><br /> <em>{comments}</em> (<?php _e('returns comments count only, no text, requires stats_comments=1', 'wordpress-popular-posts'); ?>)<br /><br /> <em>{date}</em> (<?php _e('returns post/page date, requires stats_date=1', 'wordpress-popular-posts'); ?>)</td>
                            <td>&lt;li&gt;{thumb} {title} &lt;span class="wpp-meta post-stats"&gt;{stats}&lt;/span&gt;&lt;/li&gt;</td>
                            <td><strong><?php _e('With wpp_get_mostpopular():', 'wordpress-popular-posts'); ?></strong><br /><br />&lt;?php<br />$args = array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'post_html' => '&lt;li&gt;{thumb} &lt;a href="{url}"&gt;{text_title}&lt;/a&gt;&lt;/li&gt;'<br />);<br /><br />wpp_get_mostpopular( $args );<br />?&gt;<br /><br /><hr /><br /><strong><?php _e('With the [wpp] shortcode:', 'wordpress-popular-posts'); ?></strong><br /><br />[wpp post_html='&lt;li&gt;{thumb} &lt;a href="{url}"&gt;{text_title}&lt;/a&gt;&lt;/li&gt;']<br /><br /></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- End params -->

    <!-- Start debug -->
    <?php
    global $wpdb, $wp_version;

    $my_theme = wp_get_theme();

    $site_plugins = get_plugins();
    $plugin_names = array();

    foreach( $site_plugins as $main_file => $plugin_meta ) :
        if ( !is_plugin_active( $main_file ) )
            continue;
        $plugin_names[] = sanitize_text_field( $plugin_meta['Name'] . ' ' . $plugin_meta['Version'] );
    endforeach;
    ?>
    <div id="wpp_debug" <?php echo ( "debug" == $current ) ? '' : ' style="display: none;"'; ?>>
        <p><strong>PHP version:</strong> <?php echo phpversion(); ?></p>
        <p><strong>PHP extensions:</strong> <?php echo implode( ', ', get_loaded_extensions() ); ?></p>
        <p><strong>Database version:</strong> <?php echo $wpdb->get_var( "SELECT VERSION();" ); ?></p>
        <p><strong>InnoDB availability:</strong> <?php echo $wpdb->get_var( "SELECT SUPPORT FROM INFORMATION_SCHEMA.ENGINES WHERE ENGINE = 'InnoDB';" ); ?></p>
        <p><strong>WordPress version:</strong> <?php echo $wp_version; ?></p>
        <p><strong>Multisite:</strong> <?php echo ( function_exists( 'is_multisite' ) && is_multisite() ) ? 'Yes' : 'No'; ?></p>
        <p><strong>External object cache:</strong> <?php echo ( wp_using_ext_object_cache() ) ? 'Yes' : 'No'; ?></p>
        <p><strong>WPP_CACHE_VIEWS:</strong> <?php echo ( defined( 'WPP_CACHE_VIEWS' ) && WPP_CACHE_VIEWS ) ? 'Yes' : 'No'; ?></p>
        <p><strong>Active plugins:</strong> <?php echo implode( ', ', $plugin_names ); ?></p>
        <p><strong>Theme:</strong> <?php echo $my_theme->get( 'Name' ) . ' (' . $my_theme->get( 'Version' ) . ') by ' . $my_theme->get( 'Author' ); ?></p>
    </div>
    <!-- End debug -->

</div>