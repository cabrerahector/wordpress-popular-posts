=== WordPress Popular Posts ===
Contributors: hcabrera
Donate link: https://ko-fi.com/cabrerahector
Tags: popular, posts, widget, popularity, top
Requires at least: 5.3
Tested up to: 6.3.1
Requires PHP: 7.2
Stable tag: 6.3.3
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

[Buy me a coffee](https://ko-fi.com/cabrerahector)

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

= 6.3.3 =

- Security enhancements (props to the Patchstack team!)
- Updates dependencies.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-3-new-shortcode-to-display-views-count-php-8-compatibility-improvements/#6.3.3)

= 6.3.2 =

- Fixes a PHP notice coming from the [wpp] shortcode.
- Removes legacy AJAX code from the plugin.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-3-new-shortcode-to-display-views-count-php-8-compatibility-improvements/#6.3.2)

= 6.3.1 =

**If you're using a caching plugin on your website, clearing its cache after installing / updating to this version is highly recommended.**

- Improves compatibility with newer versions of PHP 8 (thanks ispreview and dimal for the heads-up!)
- Introduces new shortcode to render views count (see [Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-3-new-shortcode-to-display-views-count-php-8-compatibility-improvements/) for more details).
- Shares post_id value with render_image filter hook for more flexibility.
- Fixes a fatal error when the PHP extension mbstring is not installed.
- Updates wpp.min.js.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-3-new-shortcode-to-display-views-count-php-8-compatibility-improvements/)

= 6.2.1 =

- Fixes an issue where the [wpp] shortcode would get stuck at the loading animation.

= 6.2.0 =

**If you're using a caching plugin on your website, clearing its cache after installing / updating to this version is highly recommended.**

- The [wpp] shortcode has now the ability to load itself via AJAX.
- Fixes an issue where for certain server configurations the popular posts list would output garbled text.
- Fixes an issue where PHP would throw notices due to the usage of HTML5 tags.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-2-shortcode-is-now-page-caching-friendly-other-minor-fixes/)

= 6.1.4 =

**If you're using a caching plugin on your website, clearing its cache after installing / updating to this version is highly recommended.**

- Fixes an issue where the [[wpp]](https://github.com/cabrerahector/wordpress-popular-posts/wiki/1.-Using-WPP-on-posts-&-pages#the-wpp-shortcode) shortcode might output empty paragraphs under certain conditions.
- Reverts "uglification" of wpp.min.js which caused popular post list(s) not to load under certain conditions.
- Widget's deprecation notice has been reworded for clarity.
- Updates dependencies + minor code cleanup.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-1-0-improved-php-8-1-support-plus-minor-enhancements/#6.1.4)

= 6.1.3 =

- **Hotfix**: Fixes rare PHP fatal error in Admin.php (props to winetravelista and scotttripatrek!)

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-1-0-improved-php-8-1-support-plus-minor-enhancements/#6.1.3)

= 6.1.2 =

**If you're using a caching plugin on your website, clearing its cache after installing / updating to this version is highly recommended.**

- **Deprecation Notice:** The WordPress Popular Posts "classic" widget is going away! If you're using the classic widget please replace it with the [WordPress Popular Posts block](https://github.com/cabrerahector/wordpress-popular-posts/wiki/1.-Using-WPP-on-posts-&-pages#the-wordpress-popular-posts-block) or the [[wpp]](https://github.com/cabrerahector/wordpress-popular-posts/wiki/1.-Using-WPP-on-posts-&-pages#the-wpp-shortcode) shortcode as soon as possible. See release notes for more details.
- Plugin now uses the [Tax_Query](https://developer.wordpress.org/reference/classes/wp_tax_query/) class to filter popular posts by taxonomy, pretty much similar to how WP_Query does it.
- Fixes an issue where selecting the Tiny theme would override the heading of the popular posts list.
- Fixes an issue where taxonomy links would render an extra whitespace for some browsers.
- Fixes issue where when using a theme (eg. Cards) post titles would be unintentionally truncated.
- Adds decoding=async property to WPP's thumbnail.
- General PHPCS/WPCS code improvements.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-1-0-improved-php-8-1-support-plus-minor-enhancements/#6.1.2)

= 6.1.1 =

- Fixes a rare PHP fatal error that can occur during plugin activation.
- Block: adds links to documentation within the block form for ease of access.
- Updates dependencies.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-1-0-improved-php-8-1-support-plus-minor-enhancements/#6.1.1)

= 6.1.0 =

- Improves PHP 8.1 support.
- Adds new [filter to modify the post date](https://github.com/cabrerahector/wordpress-popular-posts/wiki/3.-Filters#wpp_the_date).
- Adds check to prevent the misuse of the Data Sampling feature (props to the JPCERT/CC team for reporting this issue).
- Updates dependencies.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-1-0-improved-php-8-1-support-plus-minor-enhancements/)

[Full Changelog](https://github.com/cabrerahector/wordpress-popular-posts/blob/master/changelog.md)

== Credits ==

* Flame graphic by freevector/Vecteezy.com.

== Upgrade Notice ==
= 5.5.1 =
If you're using a caching plugin flushing its cache after upgrading to this version is highly recommended.