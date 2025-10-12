=== WP Popular Posts ===
Contributors: hcabrera
Donate link: https://ko-fi.com/cabrerahector
Tags: popular, posts, widget, popularity, top
Requires at least: 5.9
Tested up to: 6.8.1
Requires PHP: 7.3
Stable tag: 7.3.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A highly customizable, easy-to-use popular posts plugin!

== Description ==

WP Popular Posts is a highly customizable plugin that displays your most popular posts.

= Main Features =
* **Multiple Popular Posts Lists** - You can have several Popular Posts lists on your blog, each with its own settings!
* **Time Range** - List those posts of your blog that have been the most popular ones within a specific time range (eg. last 24 hours, last 7 days, last 30 days, etc)!
* **Custom Post-type support** - Want to show other stuff than just posts and pages, eg. Popular *Products*? [You can](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#i-want-to-have-a-popular-list-of-my-custom-post-type-how-can-i-do-that)!
* **Thumbnails!** - Display a thumbnail of your posts! (*see the [FAQ section](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#how-does-wordpress-popular-posts-pick-my-posts-thumbnails) for more details*.)
* **Statistics dashboard** - See how your popular posts are doing directly from your admin area.
* **Sorting options** - Order your popular list by comments, views (default) or average views per day!
* **Custom themes** - Out of the box, WP Popular Posts includes some themes so you can style your popular posts list (see [Widget Themes](https://github.com/cabrerahector/wordpress-popular-posts/wiki/6.-Styling-the-list#themes) for more details).
* **Use your own layout!** - WPP is flexible enough to let you customize the look and feel of your popular posts! (see [customizing WPP's HTML markup](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#how-can-i-use-my-own-html-markup-with-your-plugin) and [How to style WP Popular Posts](https://github.com/cabrerahector/wordpress-popular-posts/wiki/6.-Styling-the-list) for more.)
* **Advanced caching features!** - WP Popular Posts includes a few options to make sure your site's performance stays as good as ever! (see [Performance](https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance) for more details.)
* **REST API Support** - Embed your popular posts in your (web) app! (see [REST API Endpoints](https://github.com/cabrerahector/wordpress-popular-posts/wiki/8.-REST-API-Endpoints) for more.)
* **Elementor support** - Are you building sites with Elementor? There's a popular posts widget for it too!
* **Disqus support** - Sort your popular posts by Disqus comments count!
* **Polylang & WPML 3.2+ support** - Show the translated version of your popular posts!
* **WordPress Multisite support** - Each site on the network can have its own popular posts list!

= Other Features =
* **Shortcode support** - Use the [wpp] shortcode to showcase your most popular posts on pages, too! For usage and instructions, please refer to the [Installation section](https://wordpress.org/plugins/wordpress-popular-posts/#installation).
* **Template tags** - Don't feel like using blocks? No problem! You can still embed your most popular entries on your theme using the `wpp_get_mostpopular()` template tag. Additionally, the `wpp_get_views()` template tag allows you to retrieve the views count for a particular post. For usage and instructions, please refer to the [Installation section](https://wordpress.org/plugins/wordpress-popular-posts/#installation).
* **Localization** - [Translate WPP into your own language](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#i-want-to-translate-your-plugin-into-my-language--help-you-update-a-translation-what-do-i-need-to-do).
* **[WP-PostRatings](https://wordpress.org/plugins/wp-postratings/) support** - Show your visitors how your readers are rating your posts!

= PSA: The classic WP Popular Posts widget has reached End-of-Life =

The classic WP Popular Posts widget doesn't work very well / at all with the [block-based Widgets editor](https://wordpress.org/documentation/article/block-based-widgets-editor/) introduced with WordPress 5.8.

This new Widgets editor expects [WordPress blocks](https://wordpress.org/documentation/article/blocks-list/) instead of regular WordPress widgets. If you're using the classic WP Popular Posts widget please replace it with the [WP Popular Posts block](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-3-improved-php-8-support-retina-display-support-and-more/#block-editor-support) instead - it has the same features and functionality as the "classic" widget so you won't be missing anything at all. See the [Migration Guide](https://cabrerahector.com/wordpress/migrating-from-the-classic-popular-posts-widget/) for more details.

Bjorn from wplearninglab.com was kind enough to create a video explaining how to use the new block for all of you visual learners:

[youtube https://www.youtube.com/watch?v=mtzk6yNEaFs]

If you cannot (or do not want to) use WordPress blocks on your website then please replace your classic widget with the [[wpp] shortcode](https://github.com/cabrerahector/wordpress-popular-posts/wiki/1.-Using-WPP-on-posts-&-pages#the-wpp-shortcode).

= Support the Project! =

If you'd like to support my work and efforts to creating and maintaining more open source projects your donations and messages of support mean a lot!

[Buy me a coffee](https://ko-fi.com/cabrerahector) | [PayPal](https://www.paypal.com/paypalme/cabrerahector)

**WP Popular Posts** is now also on [GitHub](https://github.com/cabrerahector/wordpress-popular-posts)!

Looking for a **Recent Posts** widget just as featured-packed as WP Popular Posts? **Try [Recently](https://wordpress.org/plugins/recently/)**!

== Installation ==

Please make sure your site meets the [minimum requirements](https://github.com/cabrerahector/wordpress-popular-posts#requirements) before proceeding.

= Automatic installation =

1. Log in into your WordPress dashboard.
2. Go to Plugins > Add New.
3. In the "Search Plugins" field, type in **WP Popular Posts** and hit Enter.
4. Find the plugin in the search results list and click on the "Install Now" button.

= Manual installation =

1. Download the plugin and extract its contents.
2. Upload the `wordpress-popular-posts` folder to the `/wp-content/plugins/` directory.
3. Activate the **WP Popular Posts** plugin through the "Plugins" menu in WordPress.

= Done! What's next? =

1. Please see the Usage section below to learn how to add a popular post list to your site. Once you're done, keep reading.
2. If you have a caching plugin installed on your site, flush its cache now so WPP can start tracking your site.
3. If you have a plugin that minifies JavaScript (JS) installed on your site please read this FAQ: [Is WP Popular Posts compatible with plugins that minify/bundle JavaScript code?](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#is-wordpress-popular-posts-compatible-with-plugins-that-minifybundle-javascript-code)
4. If you have a security / firewall plugin installed on your site, make sure you [allow WPP access to the REST API](https://wordpress.org/support/topic/wpp-does-not-count-properly/#post-10411163) so it can start tracking your site.
5. Go to Appearance > Editor > Theme File Editor. Under "Theme Files", click on "Theme Header" (`header.php`) and make sure that the `<?php wp_head(); ?>` tag is present (it should be somewhere before the closing `</head>` tag).
6. (Optional but highly recommended) Are you running a medium/high traffic site? If so, it might be a good idea to check [these suggestions](https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance) to make sure your site's performance stays up to par.

That's it!

= USAGE =

WP Popular Posts can be used in four different ways:

1. If you're using the [Block Editor](https://wordpress.org/support/article/wordpress-editor/) you can insert a WP Popular Posts block on your sidebar and even anywhere within your posts and pages.
2. As a template tag: you can place it anywhere on your theme with [`wpp_get_mostpopular()`](https://github.com/cabrerahector/wordpress-popular-posts/wiki/2.-Template-tags#wpp_get_mostpopular).
3. Via [shortcode](https://github.com/cabrerahector/wordpress-popular-posts/wiki/1.-Using-WPP-on-posts-&-pages), so you can embed it inside a post or a page.
4. If you're using [Elementor](https://wordpress.org/plugins/elementor/) on your site you can use the [WP Popular Posts widget for Elementor](https://cabrerahector.com/wordpress/wordpress-popular-posts-7-3-experimental-elementor-support/).

Make sure to stop by the **[Wiki](https://github.com/cabrerahector/wordpress-popular-posts/wiki)** as well, you'll find even more info there!

== Frequently Asked Questions ==

The FAQ section has been moved [here](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ).

== Screenshots ==

1. The WP Popular Posts block.
2. The WP Popular Posts block on theme's sidebar.
3. The WP Popular Posts widget for Elementor.
4. Dashboard widget.
5. Statistics panel.

== Changelog ==

= 7.3.3 =

- Hotfix: wpp shortcode no longer forces AJAX loading on.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-7-3-experimental-elementor-support/#7.3.3)

= 7.3.2 =

- Compatibility: Fixes a fatal error that can happen when using Polylang.
- Compatibility: Themes should now work with WP-Rocket.
- New notice about Pretty Permalinks being required has been added.
- Fixes shortcode getting stuck on the loading animation in Elementor's editor.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-7-3-experimental-elementor-support/#7.3.2)

= 7.3.1 =

- Fixes wpp_start/end being striped out if empty (props to ispreview!)
- Fixes posts not loading on the Stats screen for Author users (props to waheed61!)

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-7-3-experimental-elementor-support/#7.3.1)

= 7.3.0 =

**If you're using a caching plugin and/or a plugin that minifies JavaScript code it's highly recommended to clear its cache after installing / updating to this version.**

- Adds Elementor support: WordPress Popular Posts now includes a native Elementor widget! See [Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-7-3-experimental-elementor-support/) for more details.
- Adds [get_ids()](https://github.com/cabrerahector/wordpress-popular-posts/wiki/2.-Template-tags#wpp_get_ids) function to facilitate getting the IDs of your popular posts.
- Admin section usability improvements.
- The "Empty cache" and "Clear all data" buttons have been removed from the Admin section.
- Improves compatibility with PHP 8.4 (thanks ispreview for the heads-up!)
- Minimum required PHP version is now 7.3.
- Minor code changes & improvements.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-7-3-experimental-elementor-support/)

= 7.2.0 =

**If you're using a caching plugin on your website it's highly recommended to clear its cache after installing / updating to this version.**

- Fixes a security issue that allows unintended arbitrary shortcode execution (props to mikemyers and the Wordfence team!)
- Fixes an issue that would allow the _popularpoststransients table to store more data than intended.
- Adds ability to hook into WPP's script to perform certain actions before updating the views count of a post/page (see [Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-7-2-security-enhancements-ability-to-hook-into-wpp-min-js/#views-tracking-hook) for more details.)
- Deprecates pid parameter in favor of exclude.
- Minor code improvements.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-7-2-security-enhancements-ability-to-hook-into-wpp-min-js/)

= 7.1.0 =

**If you're using a caching plugin on your website it's highly recommended to clear its cache after installing / updating to this version.**

- Fixes a PHP fatal error that can happen on the block-based Widgets screen when using the "classic" widget (props to andymoonshine!)
- Fixes an issue where the shortcode didn't add the "current" CSS class to the current post.
- Fixes a PHP warning that can occur when the HTML output is empty (props to wpfed!)
- Adds plugin version to wpp.js URL for cache busting.
- Improves compatibility with WP Rocket.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-7-1-improved-wp-rocket-compatibility-plus-various-fixes/)

= 7.0.1 =

**If you're using a caching plugin on your website it's highly recommended to clear its cache after installing / updating to this version.**

- Improves compatibility with LiteSpeed Cache, Autoptimize, W3 Total Cache, and Speed Optimizer (formerly known as SiteGround Optimizer.)
- Fixes an issue where the popular posts list may not load on iOS browsers (props to Marlys Arnold and abid76!)
- Fixes an issue where get_views() might not return the expected value (props to robwkirby!)

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-7-0-classic-widget-is-no-more-webp-avif-support/#7.0.1)

= 7.0.0 =

- **Breaking Change:** The WordPress Popular Posts "classic" widget will stop working after this version! If you're using the classic widget please replace it with the [WordPress Popular Posts block](https://github.com/cabrerahector/wordpress-popular-posts/wiki/1.-Using-WPP-on-posts-&-pages#the-wordpress-popular-posts-block) or the [[wpp]](https://github.com/cabrerahector/wordpress-popular-posts/wiki/1.-Using-WPP-on-posts-&-pages#the-wpp-shortcode) shortcode as soon as possible. See release notes for more details.
- **Breaking Change:** The .widget CSS class has been removed from the WordPress Popular Posts block. If you were using it to style your popular posts block adjustments may be required.
- Native WebP / AVIF support: your thumbnails can now be created as .webp / .avif images without requiring third-party plugins. Check the release notes for more.
- New filter hook to dynamically change the headline of the popular post list (props to abid76!)
- PHPCS / WPCS improvements.
- Minor enhancements / fixes.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-7-0-classic-widget-is-no-more-webp-avif-support/)

[Full Changelog](https://github.com/cabrerahector/wordpress-popular-posts/blob/master/changelog.md)

== Credits ==

* Flame graphic by freevector/Vecteezy.com.

== Upgrade Notice ==
= 7.2.0 =
If you're using a caching plugin on your website it's highly recommended to clear its cache after installing / updating to this version.