<?php
if ( basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__) ) {
    exit('Please do not load this page directly');
}

$wpp_tabs = [
    'stats' => __('Stats', 'wordpress-popular-posts'),
    'tools' => __('Tools', 'wordpress-popular-posts'),
    'params' => __('Parameters', 'wordpress-popular-posts'),
    'debug' => 'Debug'
];

// Set active tab
if ( isset($_GET['tab'] ) && isset($wpp_tabs[$_GET['tab']] ) ) {
    $current = $_GET['tab']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- $current will be equal to one of the registered tabs
} else {
    $current = 'stats';
}

// Update options on form submission
if ( isset($_POST['section']) ) {

    if ( 'stats' == $_POST['section'] ) {
        $current = 'stats';

        if ( isset($_POST['wpp-update-stats-options-token']) && wp_verify_nonce($_POST['wpp-update-stats-options-token'], 'wpp-update-stats-options') ) {
            $this->config['stats']['limit'] = ( \WordPressPopularPosts\Helper::is_number($_POST['stats_limit']) && $_POST['stats_limit'] > 0 ) ? (int) $_POST['stats_limit'] : 10;
            $this->config['stats']['post_type'] = empty($_POST['stats_type']) ? 'post' : sanitize_text_field($_POST['stats_type']);
            $this->config['stats']['freshness'] = isset($_POST['stats_freshness']);

            update_option('wpp_settings_config', $this->config);
            echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html(__('Settings saved.', 'wordpress-popular-posts')) . '</strong></p></div>';
        }
    }
    elseif ( 'misc' == $_POST['section'] ) {
        $current = 'tools';

        if ( isset($_POST['wpp-update-misc-options-token'] ) && wp_verify_nonce($_POST['wpp-update-misc-options-token'], 'wpp-update-misc-options') ) {
            $this->config['tools']['link']['target'] = sanitize_text_field($_POST['link_target']);
            $this->config['tools']['css'] = (bool) $_POST['css'];
            $this->config['tools']['experimental'] = isset($_POST['experimental_features']);

            update_option('wpp_settings_config', $this->config);
            echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html(__('Settings saved.', 'wordpress-popular-posts')) . '</strong></p></div>';
        }
    }
    elseif ( 'thumb' == $_POST['section'] ) {
        $current = 'tools';

        if ( isset($_POST['wpp-update-thumbnail-options-token']) && wp_verify_nonce($_POST['wpp-update-thumbnail-options-token'], 'wpp-update-thumbnail-options') ) {
            if (
                $_POST['thumb_source'] == 'custom_field'
                && ( ! isset($_POST['thumb_field']) || empty($_POST['thumb_field']) )
            ) {
                echo '<div class="notice notice-error"><p>' . esc_html(__('Please provide the name of your custom field.', 'wordpress-popular-posts')) . '</p></div>';
            }
            else {
                // thumbnail settings changed, flush transients
                if ( $this->config['tools']['cache']['active'] ) {
                    $this->flush_transients();
                }

                $this->config['tools']['thumbnail']['source'] = sanitize_text_field($_POST['thumb_source']);
                $this->config['tools']['thumbnail']['field'] = ( ! empty($_POST['thumb_field']) ) ? sanitize_text_field($_POST['thumb_field']) : 'wpp_thumbnail';
                $this->config['tools']['thumbnail']['default'] = ( ! empty($_POST['upload_thumb_src']) ) ? $_POST['upload_thumb_src'] : '';
                $this->config['tools']['thumbnail']['resize'] = (bool) $_POST['thumb_field_resize'];
                $this->config['tools']['thumbnail']['lazyload'] = (bool) $_POST['thumb_lazy_load'];

                update_option('wpp_settings_config', $this->config );
                echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html(__('Settings saved.', 'wordpress-popular-posts')) . '</strong></p></div>';
            }
        }
    }
    elseif ( 'data' == $_POST['section'] && current_user_can('manage_options') ) {
        $current = 'tools';

        if ( isset($_POST['wpp-update-data-options-token'] ) && wp_verify_nonce($_POST['wpp-update-data-options-token'], 'wpp-update-data-options') ) {
            $this->config['tools']['log']['level'] = (int) $_POST['log_option'];
            $this->config['tools']['log']['limit'] = (int) $_POST['log_limit'];
            $this->config['tools']['log']['expires_after'] = ( \WordPressPopularPosts\Helper::is_number($_POST['log_expire_time']) && $_POST['log_expire_time'] > 0 ) ? (int) $_POST['log_expire_time'] : 180;
            $this->config['tools']['ajax'] = (bool) $_POST['ajax'];

            // if any of the caching settings was updated, destroy all transients created by the plugin
            if (
                $this->config['tools']['cache']['active'] != $_POST['cache']
                || $this->config['tools']['cache']['interval']['time'] != $_POST['cache_interval_time']
                || $this->config['tools']['cache']['interval']['value'] != $_POST['cache_interval_value']
            ) {
                $this->flush_transients();
            }

            $this->config['tools']['cache']['active'] = (bool) $_POST['cache'];
            $this->config['tools']['cache']['interval']['time'] = $_POST['cache_interval_time']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $this->config['tools']['cache']['interval']['value'] = ( isset($_POST['cache_interval_value']) && \WordPressPopularPosts\Helper::is_number($_POST['cache_interval_value']) && $_POST['cache_interval_value'] > 0 ) ? (int) $_POST['cache_interval_value'] : 1;

            $this->config['tools']['sampling']['active'] = (bool) $_POST['sampling'];
            $this->config['tools']['sampling']['rate'] = ( isset($_POST['sample_rate']) && \WordPressPopularPosts\Helper::is_number($_POST['sample_rate']) && $_POST['sample_rate'] > 0 )
              ? (int) $_POST['sample_rate']
              : 100;

            update_option('wpp_settings_config', $this->config);
            echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html(__('Settings saved.', 'wordpress-popular-posts')) . '</strong></p></div>';
        }
    }

}
?>

<?php if ( current_user_can('edit_others_posts') ) : ?>
    <nav id="wpp-menu">
        <ul>
            <li <?php echo ('stats' == $current ) ? 'class="current"' : ''; ?>><a href="<?php echo esc_url(admin_url('options-general.php?page=wordpress-popular-posts&tab=stats')); ?>" title="<?php esc_attr_e('Stats', 'wordpress-popular-posts'); ?>"><span><?php esc_html_e('Stats', 'wordpress-popular-posts'); ?></span></a></li>
            <li <?php echo ('tools' == $current ) ? 'class="current"' : ''; ?>><a href="<?php echo esc_url(admin_url('options-general.php?page=wordpress-popular-posts&tab=tools')); ?>" title="<?php esc_attr_e('Tools', 'wordpress-popular-posts'); ?>"><span><?php esc_html_e('Tools', 'wordpress-popular-posts'); ?></span></a></li>
            <li <?php echo ('debug' == $current ) ? 'class="current"' : ''; ?>><a href="<?php echo esc_url(admin_url('options-general.php?page=wordpress-popular-posts&tab=debug')); ?>" title="Debug"><span>Debug</span></a></li>
        </ul>
    </nav>
<?php endif; ?>

<div class="wpp-wrapper wpp-section-<?php echo esc_attr($current); ?>">
    <div class="wpp-header">
        <h2>WordPress Popular Posts</h2>
        <h3><?php echo esc_html($wpp_tabs[$current]); ?></h3>
    </div>

    <?php
    // Stats
    require_once 'screen-stats.php';
    // Tools
    require_once 'screen-tools.php';
    // Debug
    require_once 'screen-debug.php';
    ?>
</div>
