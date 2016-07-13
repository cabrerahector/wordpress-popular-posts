=== WordPress Popular Posts ===
Contributors: hcabrera
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=hcabrerab%40gmail%2ecom&lc=GB&item_name=WordPress%20Popular%20Posts%20Plugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG_global%2egif%3aNonHosted
Tags: popular, posts, widget, popularity, top
Requires at least: 3.8
Tested up to: 4.6
Stable tag: 3.3.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A highly customizable, easy-to-use popular posts widget!

== Description ==

WordPress Popular Posts is a highly customizable widget that displays the most popular posts on your blog.

= Main Features =
* **Multi-widget capable**. That is, you can have several widgets of WordPress Popular Posts on your blog - each with its own settings!
* **Time Range** - list those posts of your blog that have been the most popular ones within a specific time range (eg. last 24 hours, last 7 days, last 30 days, etc.)!
* **Custom Post-type support**. Wanna show other stuff than just posts and pages?
* Display a **thumbnail** of your posts! (*see the [FAQ section](http://wordpress.org/extend/plugins/wordpress-popular-posts/faq/) for technical requirements*).
* Use **your own layout**! WPP is flexible enough to let you customize the look and feel of your popular posts! (see [customizing WPP's HTML markup](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#how-can-i-use-my-own-html-markup-with-your-plugin) and [styling the list](https://github.com/cabrerahector/wordpress-popular-posts/wiki/6.-Styling-the-list) for more).
* **WPML** support!
* **WordPress Multisite** support!

= Other Features =
* Check the **statistics** on your most popular posts from the dashboard.
* Order your popular list by comments, views (default) or average views per day!
* **Shortcode support** - use the [wpp] shortcode to showcase your most popular posts on pages, too! For usage and instructions, please refer to the [installation section](http://wordpress.org/extend/plugins/wordpress-popular-posts/installation/).
* **Template tags** - Don't feel like using widgets? No problem! You can still embed your most popular entries on your theme using the *wpp_get_mostpopular()* template tag. Additionally, the *wpp_get_views()* template tag allows you to retrieve the views count for a particular post. For usage and instructions, please refer to the [installation section](http://wordpress.org/extend/plugins/wordpress-popular-posts/installation/).
* **Localizable** to your own language (*See the [FAQ section](http://wordpress.org/extend/plugins/wordpress-popular-posts/faq/) for more info*).
* **[WP-PostRatings](http://wordpress.org/extend/plugins/wp-postratings/) support**. Show your visitors how your readers are rating your posts!

**WordPress Popular Posts** is now also on [GitHub](https://github.com/cabrerahector/wordpress-popular-posts)!

== Installation ==

Please make sure your site meets the [minimum requirements](https://github.com/cabrerahector/wordpress-popular-posts#requirements) before proceeding.

= Automatic installation =

1. Log in into your WordPress dashboard.
2. Go to Plugins > Add New.
3. In the "Search Plugins" field, type in **WordPress Popular Posts** and hit Enter.
4. Find the plugin in the search results list and click on the "Install Now" button.

= Manual installation =

1. Download the plugin and extract its contents.
2. Upload the `wordpress-popular-posts` folder to the `/wp-content/plugins/` directory.
3. Activate the **WordPress Popular Posts** plugin through the "Plugins" menu in WordPress.

= Done! What's next? =

1. Go to Appearance > Widgets, drag and drop the **WordPress Popular Posts** widget to your sidebar. Once you're done configuring it, hit the Save button.
2. If you have a caching plugin installed on your site, flush its cache now so WPP can start tracking your site.
3. Go to Appearance > Editor. Under "Templates", click on `header.php` and make sure that the `<?php wp_head(); ?>` tag is present (should be right before the closing `</head>` tag).
4. (Optional, but highly recommended for large / high traffic sites) Enabling [Data Sampling](https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance#data-sampling) and/or [Caching](https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance#caching) might be a good idea. Check [here](https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance) for more.

That's it!

= USAGE =

WordPress Popular Posts can be used in three different ways:

1. As a [widget](http://codex.wordpress.org/WordPress_Widgets): simply drag and drop it into your theme's sidebar and configure it.
2. As a template tag: you can place it anywhere on your theme with [wpp_get_mostpopular()](https://github.com/cabrerahector/wordpress-popular-posts/wiki/2.-Template-tags#wpp_get_mostpopular).
3. Via [shortcode](https://github.com/cabrerahector/wordpress-popular-posts/wiki/1.-Using-WPP-on-posts-&-pages), so you can embed it inside a post or a page.

Make sure to stop by the **[Wiki](https://github.com/cabrerahector/wordpress-popular-posts/wiki)** as well, you'll find even more info there!

== Frequently Asked Questions ==

The FAQ section has been moved [here](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ).

== Screenshots ==

1. Widgets Control Panel.
2. WordPress Popular Posts Widget.
3. WordPress Popular Posts Widget on theme's sidebar.
4. WordPress Popular Posts Stats panel.

== Changelog ==
= 3.3.4 =
- Attempt to convert tables to InnoDB during upgrade if other engine is being used.
- Adds a check to prevent the upgrade process from running too many times.
- Minor improvements and bug fixes.
- Documentation updated.

= 3.3.3 =
- Fixes potential XSS exploit in WPP's admin dashboard.
- Adds filter to set which post types should be tracked by WPP ([details](https://github.com/cabrerahector/wordpress-popular-posts/wiki/3.-Filters#wpp_trackable_post_types)).
- Adds ability to select first attached image as thumbnail source (thanks, [@serglopatin](https://github.com/serglopatin)!)

= 3.3.2 =
- Fixes warning message: 'stream does not support seeking in...'
- Removes excerpt HTML encoding.
- Passes widget ID to the instance variable for customization.
- Adds CSS class current.
- Documentation cleanup.
- Other minor bug fixes / improvements.

= 3.3.1 =
- Fixes undefined index notice.
- Makes sure legacy tables are deleted on plugin upgrade.

= 3.3.0 =
- Adds the ability to limit the amount of data logged by WPP (see Settings > WordPress Popular Posts > Tools for more).
- Adds Polylang support (thanks, [@Chouby](https://github.com/Chouby)!)
- Removes post data from DB on deletion.
- Fixes whitespaces from post_type argument (thanks, [@getdave](https://github.com/getdave)!)
- WPP now handles SSL detection for images.
- Removes legacy datacache and datacache_backup tables.
- Adds Settings page advertisement support.
- FAQ section has been moved over to Github.

= 3.2.3 =
**If you're using a caching plugin, flushing its cache after installing / upgrading to this version is highly recommended.**

- Fixes a potential bug that might affect other plugins & themes (thanks @pippinsplugins).
- Defines INNODB as default storage engine.
- Adds the wpp-no-data CSS class to style the "Sorry, no data so far" message.
- Adds a new index to summary table.
- Updates plugin's documentation.
- Other small bug fixes and improvements.

= 3.2.2 =
**If you're using a caching plugin, flushing its cache after installing / upgrading to this version is recommended.**

* Moves sampling logic into Javascript (thanks, [@kurtpayne](https://github.com/kurtpayne)!)
* Simplifies category filtering logic.
* Fixes list sorting issue that some users were experimenting (thanks, sponker!)
* Widget uses stock thumbnails when using predefined size (some conditions apply).
* Adds the ability to enable / disable responsive support for thumbails.
* Renames wpp_update_views action hook to wpp_post_update_views, **update your code!**
* Adds wpp_pre_update_views action hook.
* Adds filter wpp_render_image.
* Drops support for get_mostpopular() template tag.
* Fixes empty HTML tags (thumbnail, stats).
* Removes Japanese, French and Norwegian Bokmal translation files from plugin.
* Many minor bug fixes / enhancements.

See [full changelog](https://github.com/cabrerahector/wordpress-popular-posts/blob/master/changelog.md).

== Credits ==

* Flame graphic by freevector/Vecteezy.com.

== Upgrade Notice ==
= 3.3.4 =
If you're using a caching plugin, clearing its cache before upgrading to v.3.3.4 is recommended.