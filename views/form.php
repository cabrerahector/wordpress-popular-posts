<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title', 'wordpress-popular-posts'); ?>:</label>  <small>[<a href="<?php echo admin_url('options-general.php?page=wordpress-popular-posts&tab=faq'); ?>" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small> <br />
    <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e('Show up to', 'wordpress-popular-posts'); ?>:</label> <small>[<a href="<?php echo admin_url('options-general.php?page=wordpress-popular-posts&tab=faq'); ?>" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
    <input id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" value="<?php echo $instance['limit']; ?>"  class="widefat" style="width:50px!important" /> <?php _e('posts', 'wordpress-popular-posts'); ?>
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'range' ); ?>"><?php _e('Time Range', 'wordpress-popular-posts'); ?>:</label> <small>[<a href="<?php echo admin_url('options-general.php?page=wordpress-popular-posts&tab=faq'); ?>" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
    <select id="<?php echo $this->get_field_id( 'range' ); ?>" name="<?php echo $this->get_field_name( 'range' ); ?>" class="widefat">
        <option value="daily" <?php if ( 'daily' == $instance['range'] ) echo 'selected="selected"'; ?>><?php _e('Last 24 hours', 'wordpress-popular-posts'); ?></option>
        <option value="weekly" <?php if ( 'weekly' == $instance['range'] ) echo 'selected="selected"'; ?>><?php _e('Last 7 days', 'wordpress-popular-posts'); ?></option>
        <option value="monthly" <?php if ( 'monthly' == $instance['range'] ) echo 'selected="selected"'; ?>><?php _e('Last 30 days', 'wordpress-popular-posts'); ?></option>
        <option value="all" <?php if ( 'all' == $instance['range'] ) echo 'selected="selected"'; ?>><?php _e('All-time', 'wordpress-popular-posts'); ?></option>
    </select>
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'order_by' ); ?>"><?php _e('Sort posts by', 'wordpress-popular-posts'); ?>:</label> <small>[<a href="<?php echo admin_url('options-general.php?page=wordpress-popular-posts&tab=faq'); ?>" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small> <br />
    <select id="<?php echo $this->get_field_id( 'order_by' ); ?>" name="<?php echo $this->get_field_name( 'order_by' ); ?>" class="widefat">
        <option value="comments" <?php if ( 'comments' == $instance['order_by'] ) echo 'selected="selected"'; ?>><?php _e('Comments', 'wordpress-popular-posts'); ?></option>
        <option value="views" <?php if ( 'views' == $instance['order_by'] ) echo 'selected="selected"'; ?>><?php _e('Total views', 'wordpress-popular-posts'); ?></option>
        <option value="avg" <?php if ( 'avg' == $instance['order_by'] ) echo 'selected="selected"'; ?>><?php _e('Avg. daily views', 'wordpress-popular-posts'); ?></option>
    </select>
</p>

<fieldset style="width:214px; padding:5px;"  class="widefat">
    <legend><?php _e('Posts settings', 'wordpress-popular-posts'); ?></legend>
    
    <div style="display:<?php if ($this->postRating) : ?>block<?php else: ?>none<?php endif; ?>">
        <input type="checkbox" class="checkbox" <?php echo ($instance['rating']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'rating' ); ?>" name="<?php echo $this->get_field_name( 'rating' ); ?>" /> <label for="<?php echo $this->get_field_id( 'rating' ); ?>"><?php _e('Display post rating', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo admin_url('options-general.php?page=wordpress-popular-posts&tab=faq'); ?>" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
    </div>
    
    <input type="checkbox" class="checkbox" <?php echo ($instance['shorten_title']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'shorten_title-active' ); ?>" name="<?php echo $this->get_field_name( 'shorten_title-active' ); ?>" /> <label for="<?php echo $this->get_field_id( 'shorten_title-active' ); ?>"><?php _e('Shorten title', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo admin_url('options-general.php?page=wordpress-popular-posts&tab=faq'); ?>" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
    <div style="display:<?php if ($instance['shorten_title']['active']) : ?>block<?php else: ?>none<?php endif; ?>">
        <br />
        <label for="<?php echo $this->get_field_id( 'shorten_title-length' ); ?>"><?php _e('Shorten title to', 'wordpress-popular-posts'); ?> <input id="<?php echo $this->get_field_id( 'shorten_title-length' ); ?>" name="<?php echo $this->get_field_name( 'shorten_title-length' ); ?>" value="<?php echo $instance['shorten_title']['length']; ?>" class="widefat" style="width:50px!important" /></label><br />
        <label for="<?php echo $this->get_field_id( 'shorten_title-words' ); ?>"><input type="radio" name="<?php echo $this->get_field_name( 'shorten_title-words' ); ?>" value="0" <?php echo (!isset($instance['shorten_title']['words']) || !$instance['shorten_title']['words']) ? 'checked="checked"' : ''; ?> /> <?php _e('characters', 'wordpress-popular-posts'); ?></label><br />
        <label for="<?php echo $this->get_field_id( 'shorten_title-words' ); ?>"><input type="radio" name="<?php echo $this->get_field_name( 'shorten_title-words' ); ?>" value="1" <?php echo (isset($instance['shorten_title']['words']) && $instance['shorten_title']['words']) ? 'checked="checked"' : ''; ?> /> <?php _e('words', 'wordpress-popular-posts'); ?></label><br /><br />
    </div>
                    
    <input type="checkbox" class="checkbox" <?php echo ($instance['post-excerpt']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'post-excerpt-active' ); ?>" name="<?php echo $this->get_field_name( 'post-excerpt-active' ); ?>" /> <label for="<?php echo $this->get_field_id( 'post-excerpt-active' ); ?>"><?php _e('Display post excerpt', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo admin_url('options-general.php?page=wordpress-popular-posts&tab=faq'); ?>" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
    <div style="display:<?php if ($instance['post-excerpt']['active']) : ?>block<?php else: ?>none<?php endif; ?>">
        <fieldset class="widefat">
            <legend><?php _e('Excerpt Properties', 'wordpress-popular-posts'); ?></legend><br />
            &nbsp;&nbsp;<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'post-excerpt-format' ); ?>" name="<?php echo $this->get_field_name( 'post-excerpt-format' ); ?>" <?php echo ($instance['post-excerpt']['keep_format']) ? 'checked="checked"' : ''; ?> /> <label for="<?php echo $this->get_field_id( 'post-excerpt-format' ); ?>"><?php _e('Keep text format and links', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo admin_url('options-general.php?page=wordpress-popular-posts&tab=faq'); ?>" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br /><br />
            &nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'post-excerpt-length' ); ?>"><?php _e('Excerpt length', 'wordpress-popular-posts'); ?>: <input id="<?php echo $this->get_field_id( 'post-excerpt-length' ); ?>" name="<?php echo $this->get_field_name( 'post-excerpt-length' ); ?>" value="<?php echo $instance['post-excerpt']['length']; ?>" class="widefat" style="width:50px!important" /></label><br  />
            
            &nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'post-excerpt-words' ); ?>"><input type="radio" name="<?php echo $this->get_field_name( 'post-excerpt-words' ); ?>" value="0" <?php echo (!isset($instance['post-excerpt']['words']) || !$instance['post-excerpt']['words']) ? 'checked="checked"' : ''; ?> /> <?php _e('characters', 'wordpress-popular-posts'); ?></label><br />
            &nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'post-excerpt-words' ); ?>"><input type="radio" name="<?php echo $this->get_field_name( 'post-excerpt-words' ); ?>" value="1" <?php echo (isset($instance['post-excerpt']['words']) && $instance['post-excerpt']['words']) ? 'checked="checked"' : ''; ?> /> <?php _e('words', 'wordpress-popular-posts'); ?></label><br /><br />
            
        </fieldset>
        <br />
    </div> 
                   
</fieldset>
<br />

<fieldset class="widefat">
    <legend><?php _e('Filters', 'wordpress-popular-posts'); ?>: <small>[<a href="<?php echo admin_url('options-general.php?page=wordpress-popular-posts&tab=faq'); ?>" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small></legend><br />
    
    &nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php _e('Post type(s)', 'wordpress-popular-posts'); ?>:</label><br />
    &nbsp;&nbsp;<input id="<?php echo $this->get_field_id( 'post_type' ); ?>" name="<?php echo $this->get_field_name( 'post_type' ); ?>" value="<?php echo $instance['post_type']; ?>" class="widefat" style="width:150px" /><br /><br />
    &nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'pid' ); ?>"><?php _e('Post(s) ID(s) to exclude', 'wordpress-popular-posts'); ?>:</label><br />
    &nbsp;&nbsp;<input id="<?php echo $this->get_field_id( 'pid' ); ?>" name="<?php echo $this->get_field_name( 'pid' ); ?>" value="<?php echo $instance['pid']; ?>" class="widefat" style="width:150px" /><br /><br />
    &nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'cat' ); ?>"><?php _e('Category(ies) ID(s)', 'wordpress-popular-posts'); ?>:</label><br />
    &nbsp;&nbsp;<input id="<?php echo $this->get_field_id( 'cat' ); ?>" name="<?php echo $this->get_field_name( 'cat' ); ?>" value="<?php echo $instance['cat']; ?>" class="widefat" style="width:150px" /><br /><br />
    &nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'uid' ); ?>"><?php _e('Author(s) ID(s)', 'wordpress-popular-posts'); ?>:</label><br />
    &nbsp;&nbsp;<input id="<?php echo $this->get_field_id( 'uid' ); ?>" name="<?php echo $this->get_field_name( 'uid' ); ?>" value="<?php echo $instance['author']; ?>" class="widefat" style="width:150px" /><br /><br />
    
</fieldset>
<br />

<fieldset class="widefat" style="padding:5px;">
    <legend><?php _e('Thumbnail settings', 'wordpress-popular-posts'); ?></legend>
    
    <input type="checkbox" class="checkbox" <?php echo ($instance['thumbnail']['active'] && $this->thumbnailing) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'thumbnail-active' ); ?>" name="<?php echo $this->get_field_name( 'thumbnail-active' ); ?>" /> <label for="<?php echo $this->get_field_id( 'thumbnail-active' ); ?>"><?php _e('Display post thumbnail', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo admin_url('options-general.php?page=wordpress-popular-posts&tab=faq'); ?>" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
    <div style="display:<?php if ($instance['thumbnail']['active']) : ?>block<?php else: ?>none<?php endif; ?>">
        <label for="<?php echo $this->get_field_id( 'thumbnail-width' ); ?>"><?php _e('Width', 'wordpress-popular-posts'); ?>:</label> 
        <input id="<?php echo $this->get_field_id( 'thumbnail-width' ); ?>" name="<?php echo $this->get_field_name( 'thumbnail-width' ); ?>" value="<?php echo $instance['thumbnail']['width']; ?>"  class="widefat" style="width:30px!important" <?php echo ($this->thumbnailing) ? '' : 'disabled="disabled"' ?> /> <?php _e('px', 'wordpress-popular-posts'); ?> <br />
        <label for="<?php echo $this->get_field_id( 'thumbnail-height' ); ?>"><?php _e('Height', 'wordpress-popular-posts'); ?>:</label> 
        <input id="<?php echo $this->get_field_id( 'thumbnail-height' ); ?>" name="<?php echo $this->get_field_name( 'thumbnail-height' ); ?>" value="<?php echo $instance['thumbnail']['height']; ?>"  class="widefat" style="width:30px!important" <?php echo ($this->thumbnailing) ? '' : 'disabled="disabled"' ?> /> <?php _e('px', 'wordpress-popular-posts'); ?><br />
    </div>
    
</fieldset>

<br />
<fieldset style="width:214px; padding:5px;"  class="widefat">
    <legend><?php _e('Stats Tag settings', 'wordpress-popular-posts'); ?></legend>
    
    <input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['comment_count']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'comment_count' ); ?>" name="<?php echo $this->get_field_name( 'comment_count' ); ?>" /> <label for="<?php echo $this->get_field_id( 'comment_count' ); ?>"><?php _e('Display comment count', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo admin_url('options-general.php?page=wordpress-popular-posts&tab=faq'); ?>" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />  
                  
    <input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['views']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'views' ); ?>" name="<?php echo $this->get_field_name( 'views' ); ?>" /> <label for="<?php echo $this->get_field_id( 'views' ); ?>"><?php _e('Display views', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo admin_url('options-general.php?page=wordpress-popular-posts&tab=faq'); ?>" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
    
    <input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['author']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'author' ); ?>" name="<?php echo $this->get_field_name( 'author' ); ?>" /> <label for="<?php echo $this->get_field_id( 'author' ); ?>"><?php _e('Display author', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo admin_url('options-general.php?page=wordpress-popular-posts&tab=faq'); ?>" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
    
    <input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['date']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'date' ); ?>" name="<?php echo $this->get_field_name( 'date' ); ?>" /> <label for="<?php echo $this->get_field_id( 'date' ); ?>"><?php _e('Display date', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo admin_url('options-general.php?page=wordpress-popular-posts&tab=faq'); ?>" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small>
    <div style="display:<?php if ($instance['stats_tag']['date']['active']) : ?>block<?php else: ?>none<?php endif; ?>">
        <fieldset class="widefat">
            <legend><?php _e('Date Format', 'wordpress-popular-posts'); ?></legend>
            <label title='F j, Y'><input type='radio' name='<?php echo $this->get_field_name( 'date_format' ); ?>' value='F j, Y' <?php echo ($instance['stats_tag']['date']['format'] == 'F j, Y') ? 'checked="checked"' : ''; ?> /><?php echo date_i18n('F j, Y', time()); ?></label><br />
            <label title='Y/m/d'><input type='radio' name='<?php echo $this->get_field_name( 'date_format' ); ?>' value='Y/m/d' <?php echo ($instance['stats_tag']['date']['format'] == 'Y/m/d') ? 'checked="checked"' : ''; ?> /><?php echo date_i18n('Y/m/d', time()); ?></label><br />
            <label title='m/d/Y'><input type='radio' name='<?php echo $this->get_field_name( 'date_format' ); ?>' value='m/d/Y' <?php echo ($instance['stats_tag']['date']['format'] == 'm/d/Y') ? 'checked="checked"' : ''; ?> /><?php echo date_i18n('m/d/Y', time()); ?></label><br />
            <label title='d/m/Y'><input type='radio' name='<?php echo $this->get_field_name( 'date_format' ); ?>' value='d/m/Y' <?php echo ($instance['stats_tag']['date']['format'] == 'd/m/Y') ? 'checked="checked"' : ''; ?> /><?php echo date_i18n('d/m/Y', time()); ?></label><br />
        </fieldset>
    </div>
    
    <br /><input type="checkbox" class="checkbox" <?php echo ($instance['stats_tag']['category']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>" /> <label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e('Display category', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo admin_url('options-general.php?page=wordpress-popular-posts&tab=faq'); ?>" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
    
</fieldset>
<br />

<fieldset style="width:214px; padding:5px;"  class="widefat">
    <legend><?php _e('HTML Markup settings', 'wordpress-popular-posts'); ?></legend>
    
    <input type="checkbox" class="checkbox" <?php echo ($instance['markup']['custom_html']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'custom_html' ); ?>" name="<?php echo $this->get_field_name( 'custom_html' ); ?>" /> <label for="<?php echo $this->get_field_id( 'custom_html' ); ?>"><?php _e('Use custom HTML Markup', 'wordpress-popular-posts'); ?></label> <small>[<a href="<?php echo admin_url('options-general.php?page=wordpress-popular-posts&tab=faq'); ?>" title="<?php _e('What is this?', 'wordpress-popular-posts'); ?>">?</a>]</small><br />
    <div style="display:<?php if ($instance['markup']['custom_html']) : ?>block<?php else: ?>none<?php endif; ?>">
        <br />
        <p style="font-size:11px"><label for="<?php echo $this->get_field_id( 'title-start' ); ?>"><?php _e('Before / after title', 'wordpress-popular-posts'); ?>:</label> <br />
        <input type="text" id="<?php echo $this->get_field_id( 'title-start' ); ?>" name="<?php echo $this->get_field_name( 'title-start' ); ?>" value="<?php echo $instance['markup']['title-start']; ?>" class="widefat" style="width:80px!important" <?php echo ($instance['markup']['custom_html']) ? '' : 'disabled="disabled"' ?> /> <input type="text" id="<?php echo $this->get_field_id( 'title-end' ); ?>" name="<?php echo $this->get_field_name( 'title-end' ); ?>" value="<?php echo $instance['markup']['title-end']; ?>" class="widefat" style="width:80px!important" <?php echo ($instance['markup']['custom_html']) ? '' : 'disabled="disabled"' ?> /></p>
        
        <p style="font-size:11px"><label for="<?php echo $this->get_field_id( 'wpp_start' ); ?>"><?php _e('Before / after Popular Posts', 'wordpress-popular-posts'); ?>:</label> <br />
        <input type="text" id="<?php echo $this->get_field_id( 'wpp-start' ); ?>" name="<?php echo $this->get_field_name( 'wpp-start' ); ?>" value="<?php echo $instance['markup']['wpp-start']; ?>" class="widefat" style="width:80px!important" <?php echo ($instance['markup']['custom_html']) ? '' : 'disabled="disabled"' ?> /> <input type="text" id="<?php echo $this->get_field_id( 'wpp-end' ); ?>" name="<?php echo $this->get_field_name( 'wpp-end' ); ?>" value="<?php echo $instance['markup']['wpp-end']; ?>" class="widefat" style="width:80px!important" <?php echo ($instance['markup']['custom_html']) ? '' : 'disabled="disabled"' ?> /></p>
        
        <p style="font-size:11px"><label for="<?php echo $this->get_field_id( 'post-html' ); ?>"><?php _e('Post HTML Markup', 'wordpress-popular-posts'); ?>:</label> <br />
        <textarea class="widefat" rows="10" id="<?php echo $this->get_field_id( 'post-html' ); ?>" name="<?php echo $this->get_field_name( 'post-html' ); ?>"><?php echo $instance['markup']['post-html']; ?></textarea>
    </div>
    
</fieldset>