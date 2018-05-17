=== WordPress Popular Posts ===
Contributors: hcabrera
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=hcabrerab%40gmail%2ecom&lc=GB&item_name=WordPress%20Popular%20Posts%20Plugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG_global%2egif%3aNonHosted
Tags: popular, posts, widget, popularity, top
Requires at least: 4.7
Tested up to: 4.9.6
Requires PHP: 5.2
Stable tag: 4.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A highly customizable, easy-to-use popular posts widget!

== Description ==

WordPress Popular Posts is a highly customizable widget that displays your most popular posts.

= Main Features =
* **Multi-widget capable** - You can have several widgets of WordPress Popular Posts on your blog, each with its own settings!
* **Time Range** - List those posts of your blog that have been the most popular ones within a specific time range (eg. last 24 hours, last 7 days, last 30 days, etc)!
* **Custom Post-type support** - Wanna show other stuff than just posts and pages?
* **Thumbnails!** - Display a thumbnail of your posts! (*see the [FAQ section](http://wordpress.org/extend/plugins/wordpress-popular-posts/faq/) for technical requirements*.)
* **Statistics dashboard** - See how your popular posts are doing directly from your admin area.
* **Sorting options** - Order your popular list by comments, views (default) or average views per day!
* **Use your own layout!** - WPP is flexible enough to let you customize the look and feel of your popular posts! (see [customizing WPP's HTML markup](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#how-can-i-use-my-own-html-markup-with-your-plugin) and [How to style WordPress Popular Posts](https://github.com/cabrerahector/wordpress-popular-posts/wiki/6.-Styling-the-list) for more.)
* **Disqus support** - Sort your popular posts by Disqus comments count!
* **Polylang & WPML 3.2+ support** - Show the translated version of your popular posts!
* **WordPress Multisite support** - Each site on the network can have its own popular posts!

= Other Features =
* **Shortcode support** - Use the [wpp] shortcode to showcase your most popular posts on pages, too! For usage and instructions, please refer to the [installation section](http://wordpress.org/extend/plugins/wordpress-popular-posts/installation/).
* **Template tags** - Don't feel like using widgets? No problem! You can still embed your most popular entries on your theme using the *wpp_get_mostpopular()* template tag. Additionally, the *wpp_get_views()* template tag allows you to retrieve the views count for a particular post. For usage and instructions, please refer to the [installation section](http://wordpress.org/extend/plugins/wordpress-popular-posts/installation/).
* **Localization** - Translate WPP to your own language (*See the [FAQ section](http://wordpress.org/extend/plugins/wordpress-popular-posts/faq/) for more info*).
* **[WP-PostRatings](http://wordpress.org/extend/plugins/wp-postratings/) support** - Show your visitors how your readers are rating your posts!

**WordPress Popular Posts** is now also on [GitHub](https://github.com/cabrerahector/wordpress-popular-posts)!

Looking for a Recent Posts widget just as featured-packed as WordPress Popular Posts? **Try [Recently](https://wordpress.org/plugins/recently/)**!

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
= 4.1.0 =

**If you're using a caching plugin, flushing its cache right after installing / upgrading to this version is highly recommended.**

- Adds support for the REST API.
- Adds At-a-Glance stats.
- Adds Today time range to Stats section.
- Drops jQuery dependency on front-end (faster loading times!)
- The plugin will no longer display debugging information unless WP_DEBUG is set to true.
- Many minor bug fixes and improvements.

See the [Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-4-1-is-here/) for more details!

= 4.0.13 =

- Improvements to WPP's upgrade process.
- Fixes ALT text missing from IMG tags.

= 4.0.12 =

- Fixes bug where WPP didn't return the right URL when using Polylang / WPML.
- Fixes a compatibility issue with Yoast SEO (and potentially other plugins as well).
- Improves compatibility with MySQL 5.7+.
- Other minor fixes and improvements.

See [full changelog](https://github.com/cabrerahector/wordpress-popular-posts/blob/master/changelog.md).

== Credits ==

* Flame graphic by freevector/Vecteezy.com.

== Upgrade Notice ==
= 4.0.11 =
If you're using a caching plugin, flushing its cache after upgrading to this version is highly recommended.