<?php
$current_sidebar_data = $this->get_sidebar_data();
$current_sidebar = $current_sidebar_data ? $current_sidebar_data['id'] : null;
?>
<!-- Widget title -->
<p>
    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'wordpress-popular-posts'); ?>:</label> <small>[<a href="https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#what-does-title-do" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>" target="_blank">?</a>]</small> <br />
    <input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
</p>

<!-- Limit -->
<p>
    <label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Show up to', 'wordpress-popular-posts'); ?>:</label><br />
    <input type="text" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" value="<?php echo $instance['limit']; ?>" class="widefat" style="width:50px!important" /> <?php _e('posts', 'wordpress-popular-posts'); ?>
</p>

<!-- Order by -->
<p>
    <label for="<?php echo $this->get_field_id('order_by'); ?>"><?php _e('Sort posts by', 'wordpress-popular-posts'); ?>:</label><br />
    <select id="<?php echo $this->get_field_id('order_by'); ?>" name="<?php echo $this->get_field_name('order_by'); ?>" class="widefat">
        <option value="comments" <?php if ('comments' == $instance['order_by'] ) echo 'selected="selected"'; ?>><?php _e('Comments', 'wordpress-popular-posts'); ?></option>
        <option value="views" <?php if ('views' == $instance['order_by'] ) echo 'selected="selected"'; ?>><?php _e('Total views', 'wordpress-popular-posts'); ?></option>
        <option value="avg" <?php if ('avg' == $instance['order_by'] ) echo 'selected="selected"'; ?>><?php _e('Avg. daily views', 'wordpress-popular-posts'); ?></option>
    </select>
</p>

<!-- Filters -->
<br /><hr /><br />

<legend><strong><?php _e('Filters', 'wordpress-popular-posts'); ?></strong></legend><br />

<label for="<?php echo $this->get_field_id('range'); ?>"><?php _e('Time Range', 'wordpress-popular-posts'); ?>:</label><br />
<select id="<?php echo $this->get_field_id('range'); ?>" name="<?php echo $this->get_field_name('range'); ?>" class="widefat" style="margin-bottom:5px;">
    <option value="daily" <?php if ('daily' == $instance['range'] || 'last24hours' == $instance['range'] ) echo 'selected="selected"'; ?>><?php _e('Last 24 hours', 'wordpress-popular-posts'); ?></option>
    <option value="weekly" <?php if ('weekly' == $instance['range'] || 'last7days' == $instance['range'] ) echo 'selected="selected"'; ?>><?php _e('Last 7 days', 'wordpress-popular-posts'); ?></option>
    <option value="monthly" <?php if ('monthly' == $instance['range'] || 'last30days' == $instance['range'] ) echo 'selected="selected"'; ?>><?php _e('Last 30 days', 'wordpress-popular-posts'); ?></option>
    <option value="all" <?php if ('all' == $instance['range'] ) echo 'selected="selected"'; ?>><?php _e('All-time', 'wordpress-popular-posts'); ?></option>
    <option value="custom" <?php if ('custom' == $instance['range'] ) echo 'selected="selected"'; ?>><?php _e('Custom', 'wordpress-popular-posts'); ?></option>
</select><br />

<div style="display: <?php echo ('custom' == $instance['range'] ) ? "block" : "none"; ?>">
    <input type="text" id="<?php echo $this->get_field_id('time_quantity'); ?>" name="<?php echo $this->get_field_name('time_quantity'); ?>" value="<?php echo $instance['time_quantity']; ?>" style="display: inline; float: left; width: 50px!important;" />

    <select id="<?php echo $this->get_field_id('time_unit'); ?>" name="<?php echo $this->get_field_name('time_unit'); ?>" style="margin-bottom: 5px;">
        <option <?php if ($instance['time_unit'] == "minute") {?>selected="selected"<?php } ?> value="minute"><?php _e("Minute(s)", 'wordpress-popular-posts'); ?></option>
        <option <?php if ($instance['time_unit'] == "hour") {?>selected="selected"<?php } ?> value="hour"><?php _e("Hour(s)", 'wordpress-popular-posts'); ?></option>
        <option <?php if ($instance['time_unit'] == "day") {?>selected="selected"<?php } ?> value="day"><?php _e("Day(s)", 'wordpress-popular-posts'); ?></option>
    </select>
</div>
<div class="clearfix"></div>

<input type="checkbox" class="checkbox" <?php echo ($instance['freshness']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id('freshness'); ?>" name="<?php echo $this->get_field_name('freshness'); ?>" /> <label for="<?php echo $this->get_field_id('freshness'); ?>"><small><?php _e('Display only posts published within the selected Time Range', 'wordpress-popular-posts'); ?></small></label><br /><br />

<label for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Post type(s)', 'wordpress-popular-posts'); ?>:</label> <small>[<a href="https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#what-is-post-type-for" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>" target="_blank">?</a>]</small>
<input type="text" id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>" value="<?php echo $instance['post_type']; ?>" class="widefat" /><br /><br />

<label for="<?php echo $this->get_field_id('pid'); ?>"><?php _e('Post ID(s) to exclude', 'wordpress-popular-posts'); ?>:</label>
<input type="text" id="<?php echo $this->get_field_id('pid'); ?>" name="<?php echo $this->get_field_name('pid'); ?>" value="<?php echo $instance['pid']; ?>" class="widefat" /><br /><br />

<label for="<?php echo $this->get_field_id('tax_id'); ?>"><?php _e('Taxonomy', 'wordpress-popular-posts'); ?>:</label> <small>[<a href="https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#what-is-taxonomy-for" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>" target="_blank">?</a>]</small><br style="margin-bottom: 0.5rem" />
<?php
$selected_taxonomies = explode(';', $instance['taxonomy']);
$selected_terms = explode(';', $instance['term_id']);
$tax_filter = [];

if ( ! empty($selected_taxonomies) ) {
    foreach( $selected_taxonomies as $index => $selected_taxonomy ) {
        if ( isset($selected_terms[$index]) ) {
            $tax_filter[$selected_taxonomy] = $selected_terms[$index];
        }
    }
}

// Taxonomy filter
if ( $taxonomies = get_taxonomies(['public' => true], 'objects') ) {
    foreach ( $taxonomies as $taxonomy ) {
        if ( 'post_format' == $taxonomy->name )
            continue;
        echo '<label><input type="checkbox" name="' . $this->get_field_name('taxonomy') . '[names][]" value="' . $taxonomy->name . '"' . ( isset($tax_filter[$taxonomy->name]) ? ' checked' : '') . '> ' . $taxonomy->labels->singular_name . ' <small>('. $taxonomy->name .')</small></label><br>';
        echo '<input type="text" name="' . $this->get_field_name('taxonomy') . '[terms][' . $taxonomy->name . ']" value="' . ( isset($tax_filter[$taxonomy->name]) ? esc_attr($tax_filter[$taxonomy->name]) : '') . '" class="widefat" style="margin-top: 4px;" /><br />';
        /* translators: %s here represents the singular name of the taxonomy (eg. Category) */
        $taxonomy_instructions = __('%s IDs, separated by comma (prefix a minus sign to exclude)', 'wordpress-popular-posts');
        echo '<small>' . sprintf($taxonomy_instructions, $taxonomy->labels->singular_name) . '</small><br /><br />';
    }
}
?>

<label for="<?php echo $this->get_field_id('uid'); ?>"><?php _e('Author ID(s)', 'wordpress-popular-posts'); ?>:</label> <small>[<a href="https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#what-is-author-ids-for" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>" target="_blank">?</a>]</small>
<input type="text" id="<?php echo $this->get_field_id('uid'); ?>" name="<?php echo $this->get_field_name('uid'); ?>" value="<?php echo $instance['author']; ?>" class="widefat" /><br /><br />

<!-- Post features -->
<br /><hr /><br />

<legend><strong><?php _e('Posts settings', 'wordpress-popular-posts'); ?></strong></legend>
<br />

<div style="display:<?php if ( function_exists('the_ratings_results') ) : ?>block<?php else: ?>none<?php endif; ?>;">
    <input type="checkbox" class="checkbox" <?php echo ($instance['rating']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id('rating'); ?>" name="<?php echo $this->get_field_name('rating'); ?>" /> <label for="<?php echo $this->get_field_id('rating'); ?>"><?php _e('Display post rating', 'wordpress-popular-posts'); ?></label> <small>[<a href="https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#what-does-display-post-rating-do" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>" target="_blank">?</a>]</small>
</div>

<input type="checkbox" class="checkbox" <?php echo ($instance['shorten_title']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id('shorten_title-active'); ?>" name="<?php echo $this->get_field_name('shorten_title-active'); ?>" /> <label for="<?php echo $this->get_field_id('shorten_title-active'); ?>"><?php _e('Shorten title', 'wordpress-popular-posts'); ?></label><br />

<div style="display:<?php if ($instance['shorten_title']['active']) : ?>block<?php else: ?>none<?php endif; ?>; width:90%; margin:10px 0; padding:3% 5%; background:#f5f5f5;">
    <label for="<?php echo $this->get_field_id('shorten_title-length'); ?>"><?php _e('Shorten title to', 'wordpress-popular-posts'); ?> <input type="text" id="<?php echo $this->get_field_id('shorten_title-length'); ?>" name="<?php echo $this->get_field_name('shorten_title-length'); ?>" value="<?php echo $instance['shorten_title']['length']; ?>" class="widefat" style="width:50px!important" /></label><br />
    <label><input type="radio" name="<?php echo $this->get_field_name('shorten_title-words'); ?>" value="0" <?php echo (!isset($instance['shorten_title']['words']) || !$instance['shorten_title']['words']) ? 'checked="checked"' : ''; ?> /> <?php _e('characters', 'wordpress-popular-posts'); ?></label><br />
    <label><input type="radio" name="<?php echo $this->get_field_name('shorten_title-words'); ?>" value="1" <?php echo (isset($instance['shorten_title']['words']) && $instance['shorten_title']['words']) ? 'checked="checked"' : ''; ?> /> <?php _e('words', 'wordpress-popular-posts'); ?></label>
</div>

<input type="checkbox" class="checkbox" <?php echo ($instance['post-excerpt']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id('post-excerpt-active'); ?>" name="<?php echo $this->get_field_name('post-excerpt-active'); ?>" /> <label for="<?php echo $this->get_field_id('post-excerpt-active'); ?>"><?php _e('Display post excerpt', 'wordpress-popular-posts'); ?></label><br />

<div style="display:<?php if ($instance['post-excerpt']['active']) : ?>block<?php else: ?>none<?php endif; ?>; width:90%; margin:10px 0; padding:3% 5%; background:#f5f5f5;">
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('post-excerpt-format'); ?>" name="<?php echo $this->get_field_name('post-excerpt-format'); ?>" <?php echo ($instance['post-excerpt']['keep_format']) ? 'checked="checked"' : ''; ?> /> <label for="<?php echo $this->get_field_id('post-excerpt-format'); ?>"><?php _e('Keep text format and links', 'wordpress-popular-posts'); ?></label><br /><br />
    <label for="<?php echo $this->get_field_id('post-excerpt-length'); ?>"><?php _e('Excerpt length', 'wordpress-popular-posts'); ?>: <input type="text" id="<?php echo $this->get_field_id('post-excerpt-length'); ?>" name="<?php echo $this->get_field_name('post-excerpt-length'); ?>" value="<?php echo $instance['post-excerpt']['length']; ?>" class="widefat" style="width:50px!important" /></label><br  />

    <label><input type="radio" name="<?php echo $this->get_field_name('post-excerpt-words'); ?>" value="0" <?php echo (!isset($instance['post-excerpt']['words']) || !$instance['post-excerpt']['words']) ? 'checked="checked"' : ''; ?> /> <?php _e('characters', 'wordpress-popular-posts'); ?></label><br />
    <label><input type="radio" name="<?php echo $this->get_field_name('post-excerpt-words'); ?>" value="1" <?php echo (isset($instance['post-excerpt']['words']) && $instance['post-excerpt']['words']) ? 'checked="checked"' : ''; ?> /> <?php _e('words', 'wordpress-popular-posts'); ?></label>
</div>

<input type="checkbox" class="checkbox" <?php echo ($instance['thumbnail']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id('thumbnail-active'); ?>" name="<?php echo $this->get_field_name('thumbnail-active'); ?>" /> <label for="<?php echo $this->get_field_id('thumbnail-active'); ?>"><?php _e('Display post thumbnail', 'wordpress-popular-posts'); ?></label>

<div style="display:<?php if ($instance['thumbnail']['active']) : ?>block<?php else: ?>none<?php endif; ?>; width:90%; margin:10px 0; padding:3% 5%; background:#f5f5f5;">
    <label><input type='radio' id='thumbnail-predefined-size' name='<?php echo $this->get_field_name('thumbnail-size-source'); ?>' value='predefined' <?php echo ($instance['thumbnail']['build'] == 'predefined') ? 'checked="checked"' : ''; ?> /><?php _e('Use predefined size', 'wordpress-popular-posts'); ?></label><br />

    <select id="<?php echo $this->get_field_id('thumbnail-size'); ?>" name="<?php echo $this->get_field_name('thumbnail-size'); ?>" class="widefat" style="margin:5px 0;">
        <?php
        foreach ( $this->thumbnail->get_sizes() as $name => $attr ) :
            echo '<option value="' . $name . '"' . ( ($instance['thumbnail']['build'] == 'predefined' && $attr['width'] == $instance['thumbnail']['width'] && $attr['height'] == $instance['thumbnail']['height'] ) ? ' selected="selected"' : '') . '>' . $name . ' (' . $attr['width'] . ' x ' . $attr['height'] . ( $attr['crop'] ? ', hard crop' : ', soft crop') . ')</option>';
        endforeach;
        ?>
    </select>

    <hr />

    <label><input type='radio' id='thumbnail-manual-size' name='<?php echo $this->get_field_name('thumbnail-size-source'); ?>' value='manual' <?php echo ($instance['thumbnail']['build'] == 'manual') ? 'checked="checked"' : ''; ?> /><?php _e('Set size manually', 'wordpress-popular-posts'); ?></label><br />

    <label for="<?php echo $this->get_field_id('thumbnail-width'); ?>"><?php _e('Width', 'wordpress-popular-posts'); ?>:</label>
    <input type="text" id="<?php echo $this->get_field_id('thumbnail-width'); ?>" name="<?php echo $this->get_field_name('thumbnail-width'); ?>" value="<?php echo $instance['thumbnail']['width']; ?>" class="widefat" style="margin:3px 0; width:50px!important" /> px<br />

    <label for="<?php echo $this->get_field_id('thumbnail-height'); ?>"><?php _e('Height', 'wordpress-popular-posts'); ?>:</label>
    <input type="text" id="<?php echo $this->get_field_id('thumbnail-height'); ?>" name="<?php echo $this->get_field_name('thumbnail-height'); ?>" value="<?php echo $instance['thumbnail']['height']; ?>" class="widefat" style="width:50px!important" /> px
</div><br />

<!-- Stats tag options -->
<br /><hr /><br />

<legend><strong><?php _e('Stats Tag settings', 'wordpress-popular-posts'); ?></strong></legend><br />

<input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['comment_count']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id('comment_count'); ?>" name="<?php echo $this->get_field_name('comment_count'); ?>" /> <label for="<?php echo $this->get_field_id('comment_count'); ?>"><?php _e('Display comment count', 'wordpress-popular-posts'); ?></label><br />

<input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['views']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id('views'); ?>" name="<?php echo $this->get_field_name('views'); ?>" /> <label for="<?php echo $this->get_field_id('views'); ?>"><?php _e('Display views', 'wordpress-popular-posts'); ?></label><br />

<input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['author']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id('author'); ?>" name="<?php echo $this->get_field_name('author'); ?>" /> <label for="<?php echo $this->get_field_id('author'); ?>"><?php _e('Display author', 'wordpress-popular-posts'); ?></label><br />

<input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['date']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id('date'); ?>" name="<?php echo $this->get_field_name('date'); ?>" /> <label for="<?php echo $this->get_field_id('date'); ?>"><?php _e('Display date', 'wordpress-popular-posts'); ?></label><br />

<div style="display:<?php if ($instance['stats_tag']['date']['active']) : ?>block<?php else: ?>none<?php endif; ?>; width:90%; margin:10px 0; padding:3% 5%; background:#f5f5f5;">
    <legend><strong><?php _e('Date Format', 'wordpress-popular-posts'); ?></strong></legend><br />

    <label title='m/d/Y'><input type='radio' name='<?php echo $this->get_field_name('date_format'); ?>' value='relative' <?php echo ($instance['stats_tag']['date']['format'] == 'relative') ? 'checked="checked"' : ''; ?> /><?php _e('Relative', 'wordpress-popular-posts'); ?></label><br />
    <label title='<?php echo get_option('date_format'); ?>'><input type='radio' name='<?php echo $this->get_field_name('date_format'); ?>' value='<?php echo get_option('date_format'); ?>' <?php echo ($instance['stats_tag']['date']['format'] == get_option('date_format')) ? 'checked="checked"' : ''; ?> /><?php echo date_i18n(get_option('date_format'), time()); ?></label> <small>(<a href="<?php echo admin_url('options-general.php'); ?>" title="<?php _e('WordPress Date Format', 'wordpress-popular-posts'); ?>" target="_blank"><?php _e('WordPress Date Format', 'wordpress-popular-posts'); ?></a>)</small><br />
    <label title='F j, Y'><input type='radio' name='<?php echo $this->get_field_name('date_format'); ?>' value='F j, Y' <?php echo ($instance['stats_tag']['date']['format'] == 'F j, Y') ? 'checked="checked"' : ''; ?> /><?php echo date_i18n('F j, Y', time()); ?></label><br />
    <label title='Y/m/d'><input type='radio' name='<?php echo $this->get_field_name('date_format'); ?>' value='Y/m/d' <?php echo ($instance['stats_tag']['date']['format'] == 'Y/m/d') ? 'checked="checked"' : ''; ?> /><?php echo date_i18n('Y/m/d', time()); ?></label><br />
    <label title='m/d/Y'><input type='radio' name='<?php echo $this->get_field_name('date_format'); ?>' value='m/d/Y' <?php echo ($instance['stats_tag']['date']['format'] == 'm/d/Y') ? 'checked="checked"' : ''; ?> /><?php echo date_i18n('m/d/Y', time()); ?></label><br />
    <label title='d/m/Y'><input type='radio' name='<?php echo $this->get_field_name('date_format'); ?>' value='d/m/Y' <?php echo ($instance['stats_tag']['date']['format'] == 'd/m/Y') ? 'checked="checked"' : ''; ?> /><?php echo date_i18n('d/m/Y', time()); ?></label>
</div>

<input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['taxonomy']['active'] || $instance['stats_tag']['category']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id('stats_taxonomy'); ?>" name="<?php echo $this->get_field_name('stats_taxonomy'); ?>" /> <label for="<?php echo $this->get_field_id('stats_taxonomy'); ?>"><?php _e('Display taxonomy', 'wordpress-popular-posts'); ?></label><br />
<?php
if ( $taxonomies ) {
?>
<div style="display:<?php if ($instance['stats_tag']['taxonomy']['active'] || $instance['stats_tag']['category']) : ?>block<?php else: ?>none<?php endif; ?>; width:90%; margin:10px 0; padding:3% 5%; background:#f5f5f5;">
<?php
    foreach ( $taxonomies  as $taxonomy ) {
        if ('post_format' == $taxonomy->name )
            continue;

        echo '<label><input type="radio" name="' . $this->get_field_name('stats_taxonomy_name') . '" value="' . $taxonomy->name . '"' . ( ( $instance['stats_tag']['taxonomy']['name'] == $taxonomy->name ) ? ' checked' : '') . '> ' . $taxonomy->labels->singular_name . '</label><br>';
    }
?>
</div>
<?php
}
?>

<!-- HTML Markup options -->
<br /><hr /><br />

<legend><strong><?php _e('HTML Markup settings', 'wordpress-popular-posts'); ?></strong></legend><br />

<input type="checkbox" class="checkbox" <?php echo ($instance['markup']['custom_html']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id('custom_html'); ?>" name="<?php echo $this->get_field_name('custom_html'); ?>" /> <label for="<?php echo $this->get_field_id('custom_html'); ?>"><?php _e('Use custom HTML Markup', 'wordpress-popular-posts'); ?></label> <small>[<a href="https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#what-does-use-custom-html-markup-do" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>" target="_blank">?</a>]</small><br />

<div style="display:<?php if ($instance['markup']['custom_html']) : ?>block<?php else: ?>none<?php endif; ?>; width:90%; margin:10px 0; padding:3% 5%; background:#f5f5f5;">
    <?php
    if (
        $current_sidebar
        && ! $instance['markup']['custom_html']
    ) {
        $wpp_title_start = htmlspecialchars($current_sidebar_data['before_title'], ENT_QUOTES);
        $wpp_title_end = htmlspecialchars($current_sidebar_data['after_title'], ENT_QUOTES);
    } else {
        $wpp_title_start = $instance['markup']['title-start'];
        $wpp_title_end = $instance['markup']['title-end'];
    }
    ?>
    <p style="font-size:11px"><label for="<?php echo $this->get_field_id('title-start'); ?>"><?php _e('Before / after title', 'wordpress-popular-posts'); ?>:</label> <br />
    <input type="text" id="<?php echo $this->get_field_id('title-start'); ?>" name="<?php echo $this->get_field_name('title-start'); ?>" value="<?php echo $wpp_title_start; ?>" class="widefat" style="width:49%!important" /> <input type="text" id="<?php echo $this->get_field_id('title-end'); ?>" name="<?php echo $this->get_field_name('title-end'); ?>" value="<?php echo $wpp_title_end; ?>" class="widefat" style="width:49%!important" /></p>

    <p style="font-size:11px"><label for="<?php echo $this->get_field_id('wpp-start'); ?>"><?php _e('Before / after Popular Posts', 'wordpress-popular-posts'); ?>:</label> <br />
    <input type="text" id="<?php echo $this->get_field_id('wpp-start'); ?>" name="<?php echo $this->get_field_name('wpp-start'); ?>" value="<?php echo esc_attr($instance['markup']['wpp-start']); ?>" class="widefat" style="width:49%!important" /> <input type="text" id="<?php echo $this->get_field_id('wpp-end'); ?>" name="<?php echo $this->get_field_name('wpp-end'); ?>" value="<?php echo $instance['markup']['wpp-end']; ?>" class="widefat" style="width:49%!important" /></p>

    <p style="font-size:11px"><label for="<?php echo $this->get_field_id('post-html'); ?>"><?php _e('Post HTML Markup', 'wordpress-popular-posts'); ?>:</label> <br />
    <textarea class="widefat" rows="10" id="<?php echo $this->get_field_id('post-html'); ?>" name="<?php echo $this->get_field_name('post-html'); ?>"><?php echo $instance['markup']['post-html']; ?></textarea>
</div>

<!-- Theme -->
<br /><hr /><br />

<legend style="display: inline;"><strong><?php _e('Theme', 'wordpress-popular-posts'); ?></strong></legend><small>(<?php printf(__('see a <a href="%s">list of supported browsers</a>'), 'https://caniuse.com/#feat=shadowdomv1'); ?>)</small><br /><br />

<?php
$registered_themes = $this->themer->get_themes();
ksort($registered_themes);
?>

<select id="<?php echo $this->get_field_id('theme'); ?>" name="<?php echo $this->get_field_name('theme'); ?>" class="widefat" style="margin-bottom: 5px;"<?php echo ( ! $current_sidebar ) ? ' disabled="disabled"' : ''; ?>>
    <option value="" <?php if ( '' == $instance['theme']['name'] || ! $current_sidebar ) echo 'selected="selected"'; ?>><?php _e("None", 'wordpress-popular-posts'); ?></option>
    <?php foreach ($registered_themes as $theme => $data) : ?>
    <option value="<?php echo esc_attr($theme); ?>" <?php if ( $theme == $instance['theme']['name'] && $current_sidebar ) echo 'selected="selected"'; ?>><?php echo esc_html($data['json']['name']); ?></option>
    <?php endforeach; ?>
</select>
<input type="hidden" id="<?php echo $this->get_field_id('theme-applied'); ?>" name="<?php echo $this->get_field_name('theme-applied'); ?>" value="<?php echo ($instance['theme']['applied'] && $current_sidebar) ? 1 : 0; ?>" />

<?php if ( ! $current_sidebar ) : ?>
    <p style="color: red;"><?php _e('Please save this widget (or reload this page) to enable WPP themes.', 'wordpress-popular-posts'); ?></p>
<?php endif; ?>

<br /><br />
