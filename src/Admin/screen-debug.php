<?php
if ( 'debug' == $current ) {
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
    <div id="wpp_debug">
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
    <?php
}
