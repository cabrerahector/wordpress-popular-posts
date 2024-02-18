<?php
if ( 'tools' == $current ) {

    if ( ! current_user_can('edit_others_posts') ) {
        echo '<p style="text-align: center;">' . esc_html(__('Sorry, you do not have enough permissions to do this. Please contact the site administrator for support.', 'wordpress-popular-posts')) . '</p>';
    }
    else {
        ?>
        <div id="wpp_tools">
            <h3 class="wmpp-subtitle"><?php esc_html_e('Thumbnails', 'wordpress-popular-posts'); ?></h3>

            <form action="" method="post" id="wpp_thumbnail_options" name="wpp_thumbnail_options">
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row"><label for="thumb_default"><?php esc_html_e('Default thumbnail', 'wordpress-popular-posts'); ?>:</label></th>
                            <td>
                                <?php
                                $fallback_thumbnail_url = trim($this->config['tools']['thumbnail']['default']);

                                if ( ! $fallback_thumbnail_url ) {
                                    $fallback_thumbnail_url = $this->thumbnail->get_default_url();
                                }

                                $fallback_thumbnail_url = str_replace(
                                    parse_url($fallback_thumbnail_url, PHP_URL_SCHEME) . ':',
                                    '',
                                    $fallback_thumbnail_url
                                );
                                ?>
                                <div id="thumb-review">
                                    <img src="<?php echo esc_url($fallback_thumbnail_url); ?>" alt="" />
                                </div>

                                <input id="upload_thumb_button" type="button" class="button" value="<?php esc_attr_e('Change thumbnail', 'wordpress-popular-posts'); ?>">
                                <input id="reset_thumb_button" type="button" class="button" value="<?php esc_attr_e('Reset thumbnail', 'wordpress-popular-posts'); ?>">
                                <input type="hidden" id="upload_thumb_src" name="upload_thumb_src" value="">

                                <p class="description"><?php esc_html_e('This image will be displayed when no thumbnail is available', 'wordpress-popular-posts'); ?>.</p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="thumb_source"><?php esc_html_e('Pick image from', 'wordpress-popular-posts'); ?>:</label></th>
                            <td>
                                <select name="thumb_source" id="thumb_source">
                                    <option <?php if ($this->config['tools']['thumbnail']['source'] == 'featured') { ?>selected="selected"<?php } ?> value="featured"><?php esc_html_e('Featured image', 'wordpress-popular-posts'); ?></option>
                                    <option <?php if ($this->config['tools']['thumbnail']['source'] == 'first_image') { ?>selected="selected"<?php } ?> value="first_image"><?php esc_html_e('First image on post', 'wordpress-popular-posts'); ?></option>
                                    <option <?php if ($this->config['tools']['thumbnail']['source'] == 'first_attachment') { ?>selected="selected"<?php } ?> value="first_attachment"><?php esc_html_e('First attachment', 'wordpress-popular-posts'); ?></option>
                                    <option <?php if ($this->config['tools']['thumbnail']['source'] == 'custom_field') { ?>selected="selected"<?php } ?> value="custom_field"><?php esc_html_e('Custom field', 'wordpress-popular-posts'); ?></option>
                                </select>
                                <br />
                                <p class="description"><?php esc_html_e('Tell WordPress Popular Posts where it should get thumbnails from', 'wordpress-popular-posts'); ?>.</p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="thumb_lazy_load"><?php esc_html_e('Lazy load', 'wordpress-popular-posts'); ?>:</label> <small>[<a href="https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance#lazy-loading" target="_blank" title="<?php esc_attr_e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small></th>
                            <td>
                                <select name="thumb_lazy_load" id="thumb_lazy_load">
                                    <option <?php if ( ! $this->config['tools']['thumbnail']['lazyload'] ) { ?>selected="selected"<?php } ?> value="0"><?php esc_html_e('No', 'wordpress-popular-posts'); ?></option>
                                    <option <?php if ( $this->config['tools']['thumbnail']['lazyload'] ) { ?>selected="selected"<?php } ?> value="1"><?php esc_html_e('Yes', 'wordpress-popular-posts'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr valign="top" <?php if ($this->config['tools']['thumbnail']['source'] != 'custom_field') { ?>style="display: none;"<?php } ?> id="row_custom_field">
                            <th scope="row"><label for="thumb_field"><?php esc_html_e('Custom field name', 'wordpress-popular-posts'); ?>:</label></th>
                            <td>
                                <input type="text" id="thumb_field" name="thumb_field" value="<?php echo esc_attr($this->config['tools']['thumbnail']['field']); ?>" size="10" <?php if ($this->config['tools']['thumbnail']['source'] != 'custom_field') { ?>style="display: none;"<?php } ?> />
                            </td>
                        </tr>
                        <tr valign="top" <?php if ($this->config['tools']['thumbnail']['source'] != 'custom_field') { ?>style="display: none;"<?php } ?> id="row_custom_field_resize">
                            <th scope="row"><label for="thumb_field_resize"><?php esc_html_e('Resize image from Custom field?', 'wordpress-popular-posts'); ?>:</label></th>
                            <td>
                                <select name="thumb_field_resize" id="thumb_field_resize">
                                    <option <?php if ( ! $this->config['tools']['thumbnail']['resize'] ) { ?>selected="selected"<?php } ?> value="0"><?php esc_html_e('No, use image as is', 'wordpress-popular-posts'); ?></option>
                                    <option <?php if ($this->config['tools']['thumbnail']['resize'] == 1 ) { ?>selected="selected"<?php } ?> value="1"><?php esc_html_e('Yes', 'wordpress-popular-posts'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <?php
                        $wp_upload_dir = wp_get_upload_dir();
                        if ( is_dir($wp_upload_dir['basedir'] . '/wordpress-popular-posts') ) :
                            ?>
                            <tr valign="top">
                                <th scope="row"></th>
                                <td>
                                    <input type="button" name="wpp-reset-image-cache" id="wpp-reset-image-cache" class="button-secondary" value="<?php esc_attr_e('Empty image cache', 'wordpress-popular-posts'); ?>">
                                    <p class="description"><?php esc_html_e("Use this button to clear WPP's thumbnails cache", 'wordpress-popular-posts'); ?>.</p>
                                </td>
                            </tr>
                            <?php
                        endif;
                        ?>
                        <tr valign="top">
                            <td colspan="2">
                                <input type="hidden" name="section" value="thumb">
                                <input type="submit" class="button-primary action" id="btn_th_ops" value="<?php esc_attr_e('Apply', 'wordpress-popular-posts'); ?>" name="">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php wp_nonce_field('wpp-update-thumbnail-options', 'wpp-update-thumbnail-options-token'); ?>
            </form>
            <br />
            <p style="display: <?php echo ( current_user_can('manage_options') ) ? 'block' : 'none'; ?>; float:none; clear:both;">&nbsp;</p>

            <?php if ( current_user_can('manage_options') ) : ?>
                <h3 class="wmpp-subtitle"><?php esc_html_e('Data', 'wordpress-popular-posts'); ?></h3>

                <form action="" method="post" id="wpp_ajax_options" name="wpp_ajax_options">
                    <table class="form-table">
                        <tbody>
                            <tr valign="top">
                                <th scope="row"><label for="log_option"><?php esc_html_e('Log views from', 'wordpress-popular-posts'); ?>:</label></th>
                                <td>
                                    <select name="log_option" id="log_option">
                                        <option <?php if ($this->config['tools']['log']['level'] == 0) { ?>selected="selected"<?php } ?> value="0"><?php esc_html_e('Visitors only', 'wordpress-popular-posts'); ?></option>
                                        <option <?php if ($this->config['tools']['log']['level'] == 2) { ?>selected="selected"<?php } ?> value="2"><?php esc_html_e('Logged-in users only', 'wordpress-popular-posts'); ?></option>
                                        <option <?php if ($this->config['tools']['log']['level'] == 1) { ?>selected="selected"<?php } ?> value="1"><?php esc_html_e('Everyone', 'wordpress-popular-posts'); ?></option>
                                    </select>
                                    <br />
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><label for="log_limit"><?php esc_html_e('Log limit', 'wordpress-popular-posts'); ?>:</label></th>
                                <td>
                                    <select name="log_limit" id="log_limit">
                                        <option <?php if ($this->config['tools']['log']['limit'] == 0) { ?>selected="selected"<?php } ?> value="0"><?php esc_html_e('Disabled', 'wordpress-popular-posts'); ?></option>
                                        <option <?php if ($this->config['tools']['log']['limit'] == 1) { ?>selected="selected"<?php } ?> value="1"><?php esc_html_e('Keep data for', 'wordpress-popular-posts'); ?></option>
                                    </select>

                                    <label for="log_expire_time"<?php echo ($this->config['tools']['log']['limit'] == 0) ? ' style="display: none;"' : ''; ?>>
                                        <input type="text" id="log_expire_time" name="log_expire_time" value="<?php echo esc_attr($this->config['tools']['log']['expires_after']); ?>" size="3"> <?php esc_html_e('day(s)', 'wordpress-popular-posts'); ?>
                                    </label>

                                    <p class="description"<?php echo ($this->config['tools']['log']['limit'] == 0) ? ' style="display: none;"' : ''; ?>><?php esc_html_e('Data older than the specified time frame will be automatically discarded', 'wordpress-popular-posts'); ?>.</p>

                                    <br <?php echo (1 == $this->config['tools']['log']['limit']) ? 'style="display: none;"' : ''; ?>/>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><label for="ajax"><?php esc_html_e('Load popular posts list via AJAX', 'wordpress-popular-posts'); ?>:</label></th>
                                <td>
                                    <select name="ajax" id="ajax">
                                        <option <?php if (! $this->config['tools']['ajax']) { ?>selected="selected"<?php } ?> value="0"><?php esc_html_e('Disabled', 'wordpress-popular-posts'); ?></option>
                                        <option <?php if ($this->config['tools']['ajax']) { ?>selected="selected"<?php } ?> value="1"><?php esc_html_e('Enabled', 'wordpress-popular-posts'); ?></option>
                                    </select>

                                    <br />
                                    <p class="description"><?php esc_html_e('If you are using a caching plugin such as WP Super Cache, enabling this feature will keep the popular list from being cached by it', 'wordpress-popular-posts'); ?>.</p>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><label for="cache"><?php esc_html_e('Data Caching', 'wordpress-popular-posts'); ?>:</label> <small>[<a href="https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance#caching-db-queries-results" target="_blank" title="<?php esc_attr_e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small></th>
                                <td>
                                    <select name="cache" id="cache">
                                        <option <?php if ( ! $this->config['tools']['cache']['active'] ) { ?>selected="selected"<?php } ?> value="0"><?php esc_html_e('Never cache', 'wordpress-popular-posts'); ?></option>
                                        <option <?php if ( $this->config['tools']['cache']['active'] ) { ?>selected="selected"<?php } ?> value="1"><?php esc_html_e('Enable caching', 'wordpress-popular-posts'); ?></option>
                                    </select>

                                    <br />
                                    <p class="description"><?php esc_html_e('WPP can cache the popular list for a specified amount of time. Recommended for large / high traffic sites', 'wordpress-popular-posts'); ?>.</p>
                                </td>
                            </tr>
                            <tr valign="top" <?php if ( ! $this->config['tools']['cache']['active'] ) { ?>style="display: none;"<?php } ?> id="cache_refresh_interval">
                                <th scope="row"><label for="cache_interval_value"><?php esc_html_e('Refresh cache every', 'wordpress-popular-posts'); ?>:</label></th>
                                <td>
                                    <input name="cache_interval_value" type="text" id="cache_interval_value" value="<?php echo ( isset($this->config['tools']['cache']['interval']['value']) ) ? (int) $this->config['tools']['cache']['interval']['value'] : 1; ?>" class="small-text">
                                    <select name="cache_interval_time" id="cache_interval_time">
                                        <option <?php if ($this->config['tools']['cache']['interval']['time'] == 'minute') { ?>selected="selected"<?php } ?> value="minute"><?php esc_html_e('Minute(s)', 'wordpress-popular-posts'); ?></option>
                                        <option <?php if ($this->config['tools']['cache']['interval']['time'] == 'hour') { ?>selected="selected"<?php } ?> value="hour"><?php esc_html_e('Hour(s)', 'wordpress-popular-posts'); ?></option>
                                        <option <?php if ($this->config['tools']['cache']['interval']['time'] == 'day') { ?>selected="selected"<?php } ?> value="day"><?php esc_html_e('Day(s)', 'wordpress-popular-posts'); ?></option>
                                        <option <?php if ($this->config['tools']['cache']['interval']['time'] == 'week') { ?>selected="selected"<?php } ?> value="week"><?php esc_html_e('Week(s)', 'wordpress-popular-posts'); ?></option>
                                        <option <?php if ($this->config['tools']['cache']['interval']['time'] == 'month') { ?>selected="selected"<?php } ?> value="month"><?php esc_html_e('Month(s)', 'wordpress-popular-posts'); ?></option>
                                        <option <?php if ($this->config['tools']['cache']['interval']['time'] == 'year') { ?>selected="selected"<?php } ?> value="month"><?php esc_html_e('Year(s)', 'wordpress-popular-posts'); ?></option>
                                    </select>
                                    <br />
                                    <p class="description" style="display: none;" id="cache_too_long"><?php esc_html_e('Really? That long?', 'wordpress-popular-posts'); ?></p>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><label for="sampling"><?php esc_html_e('Data Sampling', 'wordpress-popular-posts'); ?>:</label> <small>[<a href="https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance#data-sampling" target="_blank" title="<?php esc_attr_e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small></th>
                                <td>
                                    <select name="sampling" id="sampling">
                                        <option <?php if ( ! $this->config['tools']['sampling']['active'] ) { ?>selected="selected"<?php } ?> value="0"><?php esc_html_e('Disabled', 'wordpress-popular-posts'); ?></option>
                                        <option <?php if ( $this->config['tools']['sampling']['active'] ) { ?>selected="selected"<?php } ?> value="1"><?php esc_html_e('Enabled', 'wordpress-popular-posts'); ?></option>
                                    </select>

                                    <br />
                                    <?php
                                    $description = sprintf(
                                        __('By default, WordPress Popular Posts stores in database every single visit your site receives. For small / medium sites this is generally OK, but on large / high traffic sites the constant writing to the database may have an impact on performance. With <a href="%1$s" target="_blank">data sampling</a>, WordPress Popular Posts will store only a subset of your traffic and report on the tendencies detected in that sample set (for more, <a href="%2$s" target="_blank">please read here</a>)', 'wordpress-popular-posts'),
                                        'http://en.wikipedia.org/wiki/Sample_%28statistics%29',
                                        'https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance#data-sampling'
                                    );
                                    ?>
                                    <p class="description"><?php echo $description; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>.</p>
                                </td>
                            </tr>
                            <tr valign="top" <?php if ( ! $this->config['tools']['sampling']['active'] ) { ?>style="display: none;"<?php } ?>>
                                <th scope="row"><label for="sample_rate"><?php esc_html_e('Sample Rate', 'wordpress-popular-posts'); ?>:</label></th>
                                <td>
                                    <input name="sample_rate" type="text" id="sample_rate" value="<?php echo ( isset($this->config['tools']['sampling']['rate']) ) ? (int) $this->config['tools']['sampling']['rate'] : 100; ?>" class="small-text">
                                    <br />
                                    <p class="description"><?php echo sprintf(esc_html__('A sampling rate of %d is recommended for large / high traffic sites. For lower traffic sites, you should lower the value.', 'wordpress-popular-posts'), 100); ?></p>
                                </td>
                            </tr>
                            <tr valign="top">
                                <td colspan="2">
                                    <input type="hidden" name="section" value="data">
                                    <input type="submit" class="button-primary action" id="btn_ajax_ops" value="<?php esc_attr_e('Apply', 'wordpress-popular-posts'); ?>" name="">
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <?php wp_nonce_field('wpp-update-data-options', 'wpp-update-data-options-token'); ?>
                </form>
                <br />
                <p style="display: block; float:none; clear: both;">&nbsp;</p>
            <?php endif; ?>

            <h3 class="wmpp-subtitle"><?php esc_html_e('Miscellaneous', 'wordpress-popular-posts'); ?></h3>

            <form action="" method="post" id="wpp_link_options" name="wpp_link_options">
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row"><label for="link_target"><?php esc_html_e('Open links in', 'wordpress-popular-posts'); ?>:</label></th>
                            <td>
                                <select name="link_target" id="link_target">
                                    <option <?php if ($this->config['tools']['link']['target'] == '_self') { ?>selected="selected"<?php } ?> value="_self"><?php esc_html_e('Current window', 'wordpress-popular-posts'); ?></option>
                                    <option <?php if ($this->config['tools']['link']['target'] == '_blank') { ?>selected="selected"<?php } ?> value="_blank"><?php esc_html_e('New tab/window', 'wordpress-popular-posts'); ?></option>
                                </select>
                                <br />
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="css"><?php esc_html_e("Use plugin's stylesheet", 'wordpress-popular-posts'); ?>:</label></th>
                            <td>
                                <select name="css" id="css">
                                    <option <?php if ($this->config['tools']['css']) { ?>selected="selected"<?php } ?> value="1"><?php esc_html_e('Enabled', 'wordpress-popular-posts'); ?></option>
                                    <option <?php if (! $this->config['tools']['css']) { ?>selected="selected"<?php } ?> value="0"><?php esc_html_e('Disabled', 'wordpress-popular-posts'); ?></option>
                                </select>
                                <br />
                                <p class="description"><?php esc_html_e('By default, the plugin includes a stylesheet called wpp.css which you can use to style your popular posts listing. If you wish to use your own stylesheet or do not want it to have it included in the header section of your site, use this.', 'wordpress-popular-posts'); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="experimental_features"><?php esc_html_e('Enable experimental features', 'wordpress-popular-posts'); ?>:</label></th>
                            <td>
                                <input type="checkbox" class="checkbox" id="experimental_features" name="experimental_features" <?php echo ($this->config['tools']['experimental']) ? 'checked="checked"' : ''; ?>>
                            </td>
                        </tr>
                        <tr valign="top">
                            <td colspan="2">
                                <input type="hidden" name="section" value="misc">
                                <input type="submit" class="button-primary action" value="<?php esc_attr_e('Apply', 'wordpress-popular-posts'); ?>" name="">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php wp_nonce_field('wpp-update-misc-options', 'wpp-update-misc-options-token'); ?>
            </form>
            <br />
            <p style="display: block; float: none; clear: both;">&nbsp;</p>

            <?php if ( current_user_can('manage_options') ) : ?>
                <div style="margin-top: 2em;">
                    <p><?php esc_html_e('WordPress Popular Posts maintains data in two separate tables: one for storing the most popular entries on a daily basis (from now on, "cache"), and another one to keep the All-time data (from now on, "historical data" or just "data"). If for some reason you need to clear the cache table, or even both historical and cache tables, please use the buttons below to do so.', 'wordpress-popular-posts'); ?></p>
                    <p><input type="button" name="wpp-reset-cache" id="wpp-reset-cache" class="button-secondary" value="<?php esc_attr_e('Empty cache', 'wordpress-popular-posts'); ?>"> <label for="wpp-reset-cache"><small><?php esc_html_e('Use this button to manually clear entries from WPP cache only', 'wordpress-popular-posts'); ?></small></label></p>
                    <p><input type="button" name="wpp-reset-all" id="wpp-reset-all" class="button-secondary" value="<?php esc_attr_e('Clear all data', 'wordpress-popular-posts'); ?>"> <label for="wpp-reset-all"><small><?php esc_html_e('Use this button to manually clear entries from all WPP data tables', 'wordpress-popular-posts'); ?></small></label></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
