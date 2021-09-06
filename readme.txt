=== WordPress Popular Posts ===
Contributors: hcabrera
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=hcabrerab%40gmail%2ecom&lc=GB&item_name=WordPress%20Popular%20Posts%20Plugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG_global%2egif%3aNonHosted
Tags: popular, posts, widget, popularity, top
Requires at least: 4.9
Tested up to: 5.8
Requires PHP: 5.4
Stable tag: 5.4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A highly customizable, easy-to-use popular posts widget!

== Description ==

WordPress Popular Posts is a highly customizable widget that displays your most popular posts.

= Main Features =
* **Multi-widget capable** - You can have several widgets of WordPress Popular Posts on your blog, each with its own settings!
* **Time Range** - List those posts of your blog that have been the most popular ones within a specific time range (eg. last 24 hours, last 7 days, last 30 days, etc)!
* **Custom Post-type support** - Want to show other stuff than just posts and pages, eg. Popular *Products*? [You can](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#i-want-to-have-a-popular-list-of-my-custom-post-type-how-can-i-do-that)!
* **Thumbnails!** - Display a thumbnail of your posts! (*see the [FAQ section](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#how-does-wordpress-popular-posts-pick-my-posts-thumbnails) for more details*.)
* **Statistics dashboard** - See how your popular posts are doing directly from your admin area.
* **Sorting options** - Order your popular list by comments, views (default) or average views per day!
* **Custom themes** - Out of the box, WordPress Popular Posts includes some themes so you can style your popular posts list (see [Widget Themes](https://github.com/cabrerahector/wordpress-popular-posts/wiki/6.-Styling-the-list#themes) for more details).
* **Use your own layout!** - WPP is flexible enough to let you customize the look and feel of your popular posts! (see [customizing WPP's HTML markup](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#how-can-i-use-my-own-html-markup-with-your-plugin) and [How to style WordPress Popular Posts](https://github.com/cabrerahector/wordpress-popular-posts/wiki/6.-Styling-the-list) for more.)
* **Advanced caching features!** - WordPress Popular Posts includes a few options to make sure your site's performance stays as good as ever! (see [Performance](https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance) for more details.)
* **REST API Support** - Embed your popular posts in your (web) app! (see [REST API Endpoints](https://github.com/cabrerahector/wordpress-popular-posts/wiki/8.-REST-API-Endpoints) for more.)
* **Disqus support** - Sort your popular posts by Disqus comments count!
* **Polylang & WPML 3.2+ support** - Show the translated version of your popular posts!
* **WordPress Multisite support** - Each site on the network can have its own popular posts list!

= Other Features =
* **Shortcode support** - Use the [wpp] shortcode to showcase your most popular posts on pages, too! For usage and instructions, please refer to the [Installation section](https://wordpress.org/plugins/wordpress-popular-posts/#installation).
* **Template tags** - Don't feel like using widgets? No problem! You can still embed your most popular entries on your theme using the `wpp_get_mostpopular()` template tag. Additionally, the `wpp_get_views()` template tag allows you to retrieve the views count for a particular post. For usage and instructions, please refer to the [Installation section](https://wordpress.org/plugins/wordpress-popular-posts/#installation).
* **Localization** - [Translate WPP into your own language](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#i-want-to-translate-your-plugin-into-my-language--help-you-update-a-translation-what-do-i-need-to-do).
* **[WP-PostRatings](https://wordpress.org/plugins/wp-postratings/) support** - Show your visitors how your readers are rating your posts!

= Support the Project! =

If you'd like to support my work and efforts to creating and maintaining more open source projects your donations and messages of support mean a lot!

[Ko-fi](https://ko-fi.com/cabrerahector) | [Buy me a coffee](https://www.buymeacoffee.com/cabrerahector) | [PayPal Me](https://paypal.me/cabrerahector)

**WordPress Popular Posts** is now also on [GitHub](https://github.com/cabrerahector/wordpress-popular-posts)!

Looking for a **Recent Posts** widget just as featured-packed as WordPress Popular Posts? **Try [Recently](https://wordpress.org/plugins/recently/)**!

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
3. If you have a plugin that minifies JavaScript (JS) installed on your site please read this FAQ: [Is WordPress Popular Posts compatible with plugins that minify/bundle JavaScript code?](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#is-wordpress-popular-posts-compatible-with-plugins-that-minifybundle-javascript-code)
4. If you have a security / firewall plugin installed on your site, make sure you [allow WPP access to the REST API](https://wordpress.org/support/topic/wpp-does-not-count-properly/#post-10411163) so it can start tracking your site.
5. Go to Appearance > Editor. Under "Templates", click on `header.php` and make sure that the `<?php wp_head(); ?>` tag is present (should be right before the closing `</head>` tag).
6. (Optional but highly recommended) Are you running a medium/high traffic site? If so, it might be a good idea to check [these suggestions](https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance) to make sure your site's performance stays up to par.

That's it!

= USAGE =

WordPress Popular Posts can be used in three different ways:

1. As a [widget](https://wordpress.org/support/article/wordpress-widgets/): simply drag and drop it into your theme's sidebar and configure it.
2. As a template tag: you can place it anywhere on your theme with [`wpp_get_mostpopular()`](https://github.com/cabrerahector/wordpress-popular-posts/wiki/2.-Template-tags#wpp_get_mostpopular).
3. Via [shortcode](https://github.com/cabrerahector/wordpress-popular-posts/wiki/1.-Using-WPP-on-posts-&-pages), so you can embed it inside a post or a page.

Make sure to stop by the **[Wiki](https://github.com/cabrerahector/wordpress-popular-posts/wiki)** as well, you'll find even more info there!

== Frequently Asked Questions ==

The FAQ section has been moved [here](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ).

== Screenshots ==

1. The WordPress Popular Posts Widget.
2. The WordPress Popular Posts Widget on theme's sidebar.
3. Dashboard widget.
4. Statistics panel.

== Changelog ==

= 5.4.2 =

- Fixes a rare issue that prevented some users from seeing the Statistics chart (thanks Zsolt!)

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-4-widget-block-improvements-plus-prep-work-for-csp-support/#5.4.2)

= 5.4.1 =

**If you're using a caching plugin flushing its cache after upgrading to this version is highly recommended.**

- Widget block: fixes WPP block loading block editor JS files on the front end.
- Adds filter hook to disable Block editor support.
- Improves logic of filter hooks `wpp_excerpt_more` and `wpp_title_more`.
- The Parameters section has been moved to the Wiki (see https://git.io/JEOrX).

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-4-widget-block-improvements-plus-prep-work-for-csp-support/#5.4.1)

= 5.4.0 =

**If you're using a caching plugin flushing its cache after upgrading to this version is highly recommended.**

- The widget block is no longer an experimental feature and is now available to everyone (but it's still a WIP).
- Widget block: adds AJAX support to prevent caching plugins from caching your popular posts block.
- Widget block: adds WPML/Polylang support.
- Widget block: fixes widget heading not displaying.
- Widget themes: allow themes to detect the current post so it can be styled differently.
- Fixes `wpp_excerpt_more` filter hook not working (props to SchweizerSchoggi!)
- Adds filter hook `wpp_title_more` to allow customization of the prefix added to shortened post titles.
- Removes inline JavaScript code from WPP's dashboard in preparation for [WordPress' CSP adoption](https://core.trac.wordpress.org/ticket/51407).

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-4-widget-block-improvements-plus-prep-work-for-csp-support/)

[Full Changelog](https://github.com/cabrerahector/wordpress-popular-posts/blob/master/changelog.md)

== Credits ==

* Flame graphic by freevector/Vecteezy.com.

== Upgrade Notice ==
= 5.4.0 =
If you're using a caching plugin flushing its cache after upgrading to this version is highly recommended.