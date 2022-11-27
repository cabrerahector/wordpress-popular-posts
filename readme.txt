=== WordPress Popular Posts ===
Contributors: hcabrera
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=hcabrerab%40gmail%2ecom&lc=GB&item_name=WordPress%20Popular%20Posts%20Plugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG_global%2egif%3aNonHosted
Tags: popular, posts, widget, popularity, top
Requires at least: 5.3
Tested up to: 6.1.1
Requires PHP: 7.2
Stable tag: 6.1.1
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

= PSA: do not use the classic WordPress Popular Posts widget with the new Widgets screen! =

The classic WordPress Popular Posts widget doesn't work very well / at all with the new Widgets screen introduced with WordPress 5.8.

This new Widgets screen expects WordPress blocks instead of regular WordPress widgets. If you're using the WordPress Popular Posts widget on your block-based Widgets screen please consider replacing it with the [WordPress Popular Posts block](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-3-improved-php-8-support-retina-display-support-and-more/#block-editor-support) instead - it has the same features as the "classic" widget and will likely end up replacing it entirely in the future.

Bjorn from wplearninglab.com was kind enough to create a video explaining how to use the new block for all of you visual learners:

[youtube https://www.youtube.com/watch?v=mtzk6yNEaFs]

If for some reason you prefer using the "classic" WordPress Popular Posts widget with WordPress 5.8 and beyond please install the [Classic Widgets](https://wordpress.org/plugins/classic-widgets/) plugin.

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

1. As a [widget](https://wordpress.org/support/article/wordpress-widgets/): simply drag and drop it into your theme's sidebar and configure it or, if you're using the [Block Editor](https://wordpress.org/support/article/wordpress-editor/), you can also add it to your posts and pages.
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

= 6.1.1 =

- Fixes a rare PHP fatal error that can occur during plugin activation.
- Block: adds links to documentation within the block form for ease of access.
- Updates dependencies.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-1-0-improved-php-8-1-support-plus-minor-enhancements/#minor-updates-and-hotfixes)

= 6.1.0 =

- Improves PHP 8.1 support.
- Adds new [filter to modify the post date](https://github.com/cabrerahector/wordpress-popular-posts/wiki/3.-Filters#wpp_the_date).
- Adds check to prevent the misuse of the Data Sampling feature (props to the JPCERT/CC team for reporting this issue).
- Updates dependencies.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-1-0-improved-php-8-1-support-plus-minor-enhancements/)

= 6.0.5 =

- Fixes yet another issue where excerpts may output broken HTML under certain conditions (thanks dxylott54!)
- Updates .pot file.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-0-php-5-support-dropped-minimum-supported-wordpress-changed/#6.0.5)

= 6.0.4 =

- Block: improves logic when toggling certain settings.
- Block: adds back option to show post rating.
- get_views() is now compatible with Polylang/WPML.
- Updates dependencies.
- Updates .pot file.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-0-php-5-support-dropped-minimum-supported-wordpress-changed/#6.0.4)

= 6.0.3 =

- WPCS updates.
- Fixes an issue where excerpts may output broken HTML under certain conditions (thanks ozboss1!)

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-0-php-5-support-dropped-minimum-supported-wordpress-changed/#6.0.3)

= 6.0.2 =

- Fixes issue with Stats dashboard not loading for Linux users (thanks agbuere!)

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-0-php-5-support-dropped-minimum-supported-wordpress-changed/#6.0.2)

= 6.0.1 =

- Security improvements.
- Fixes fatal error in Image class (thanks Senri Miura!)
- Fixes fatal error in "classic" widget when using widget themes (thanks Finn Jackson!)
- Updates ChartJS to version 3.8.0.
- Small improvements / fixes.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-0-php-5-support-dropped-minimum-supported-wordpress-changed/#minor-updates-and-hotfixes)

= 6.0.0 =

**This release introduces a couple of major changes so please review before updating.**

- Minimum required PHP version is now 7.2.
- Minimum required WordPress version is now 5.3.
- Breaking change: this version removes code that has been deprecated for a long time. See the release notes for more details.
- Widget block: fixes an issue where the length of the title when set via theme was being ignored.
- Widget block: fixes bug with thumbnail not rendering under certain circumstances (thanks the9mm!)
- Admin: the Statistics screen will now by default only lists posts. See the release notes for more details.
- Admin: only users with `edit_others_posts` capability (usually Editors and Administrators) will be able to access certain areas of WPP's dashboard.
- Admin: makes sure to escape params from `add_query_arg()`.
- Fixes an issue where widget themes stored in child theme's folder would not be recognized by the plugin.
- Small improvements / fixes.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-0-php-5-support-dropped-minimum-supported-wordpress-changed/)

[Full Changelog](https://github.com/cabrerahector/wordpress-popular-posts/blob/master/changelog.md)

== Credits ==

* Flame graphic by freevector/Vecteezy.com.

== Upgrade Notice ==
= 5.5.1 =
If you're using a caching plugin flushing its cache after upgrading to this version is highly recommended.