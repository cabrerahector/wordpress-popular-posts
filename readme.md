# WordPress Popular Posts

A highly customizable plugin that displays your most popular posts.

----
## Table of contents
 
* [Description](https://github.com/cabrerahector/wordpress-popular-posts#description)
* [Features](https://github.com/cabrerahector/wordpress-popular-posts#features)
* [Requirements](https://github.com/cabrerahector/wordpress-popular-posts#requirements)
* [Installation](https://github.com/cabrerahector/wordpress-popular-posts#installation)
* [Usage](https://github.com/cabrerahector/wordpress-popular-posts#usage)
* [Support](https://github.com/cabrerahector/wordpress-popular-posts#support)
* [Contributing](https://github.com/cabrerahector/wordpress-popular-posts#contributing)
* [Changelog](https://github.com/cabrerahector/wordpress-popular-posts/blob/master/changelog.md)
* [License](https://github.com/cabrerahector/wordpress-popular-posts#license)


## Description

WordPress Popular Posts (from now on, just *WPP*) is a highly customizable [plugin](https://wordpress.org/plugins/wordpress-popular-posts/) to showcase the most commented / viewed entries on your [WordPress](https://wordpress.org/) powered site.


## Features

* **Multiple Popular Posts Lists** - You can have several Popular Posts lists on your blog, each with its own settings!
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
* **Shortcode support** - Use the [wpp] shortcode to showcase your most popular posts on pages, too! For usage and instructions, please refer to the [Usage section](https://github.com/cabrerahector/wordpress-popular-posts/#usage).
* **Template tags** - Don't feel like using blocks? No problem! You can still embed your most popular entries on your theme using the `wpp_get_mostpopular()` template tag. Additionally, the `wpp_get_views()` template tag allows you to retrieve the views count for a particular post. For usage and instructions, please refer to the [Usage section](https://github.com/cabrerahector/wordpress-popular-posts/#usage).
* **Localization** - [Translate WPP into your own language](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#i-want-to-translate-your-plugin-into-my-language--help-you-update-a-translation-what-do-i-need-to-do).
* **[WP-PostRatings](https://wordpress.org/plugins/wp-postratings/) support** - Show your visitors how your readers are rating your posts!

Looking for a **Recent Posts** widget just as featured-packed as WordPress Popular Posts? **Try [Recently](https://wordpress.org/plugins/recently/)**!


## Requirements

* WordPress 5.7 or newer.
* PHP 7.2 or newer.
* Mbstring PHP Extension.
* Since WordPress Popular Posts writes constantly to the database to keep track of page views, [InnoDB](https://en.wikipedia.org/wiki/InnoDB) support is required.


## Installation

### Automatic installation ###

1. Log in into your WordPress dashboard.
2. Go to Plugins > Add New.
3. In the "Search Plugins" field, type in **WordPress Popular Posts** and hit Enter.
4. Find the plugin in the search results list and click on the "Install Now" button.

### Manual installation ###

1. Download the plugin and extract its contents.
2. Upload the `wordpress-popular-posts` folder to the `/wp-content/plugins/` directory.
3. Activate the **WordPress Popular Posts** plugin through the "Plugins" menu in WordPress.

### Done! What's next? ###

1. Please see the Usage section below to learn how to add a popular post list to your site. Once you're done, keep reading.
2. If you have a caching plugin installed on your site, flush its cache now so WPP can start tracking your site.
3. If you have a plugin that minifies JavaScript (JS) installed on your site please read this FAQ: [Is WordPress Popular Posts compatible with plugins that minify/bundle JavaScript code?](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#is-wordpress-popular-posts-compatible-with-plugins-that-minifybundle-javascript-code)
4. If you have a security / firewall plugin installed on your site, make sure you [allow WPP access to the REST API](https://wordpress.org/support/topic/wpp-does-not-count-properly/#post-10411163) so it can start tracking your site.
5. Go to Appearance > Editor > Theme File Editor. Under "Theme Files", click on "Theme Header" (`header.php`) and make sure that the `<?php wp_head(); ?>` tag is present (it should be somewhere before the closing `</head>` tag).
6. (Optional but highly recommended) Are you running a medium/high traffic site? If so, it might be a good idea to check [these suggestions](https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance) to make sure your site's performance stays up to par.

That's it!


## Usage

WordPress Popular Posts can be used in three different ways:

1. If you're using the [Block Editor](https://wordpress.org/support/article/wordpress-editor/) you can insert a WordPress Popular Posts block on your sidebar (eg. via Appearance > Widgets) and even anywhere within your posts and pages.
2. As a template tag: you can place it anywhere on your theme with [`wpp_get_mostpopular()`](https://github.com/cabrerahector/wordpress-popular-posts/wiki/2.-Template-tags#wpp_get_mostpopular).
3. Via [shortcode](https://github.com/cabrerahector/wordpress-popular-posts/wiki/1.-Using-WPP-on-posts-&-pages), so you can embed it inside a post or a page.

... and there's even more on the **[Wiki](https://github.com/cabrerahector/wordpress-popular-posts/wiki)** section, so make sure to stop by!


## Support

Before submitting an issue, please:

1. Read the documentation, it's there for a reason. Links: [Requirements](https://github.com/cabrerahector/wordpress-popular-posts#requirements) | [Installation](https://github.com/cabrerahector/wordpress-popular-posts#installation) | [Wiki](https://github.com/cabrerahector/wordpress-popular-posts/wiki) | [Frequently asked questions](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ).
2. If it's a bug, please check the [issue tracker](https://github.com/cabrerahector/wordpress-popular-posts/issues) first make sure no one has reported it already.

When submitting an issue, please make sure to include the following:

1. WordPress version.
2. WPP version.
3. Are you using the block or the shortcode/template tag?
4. Describe what the issue is (include steps to reproduce it, if necessary).


## Contributing

* If you'd like to support my work and efforts to creating and maintaining more open source projects your donations and messages of support mean a lot! [Buy me a coffee](https://ko-fi.com/cabrerahector) | [PayPal](https://www.paypal.com/paypalme/cabrerahector)
* If you have any ideas/suggestions/bug reports, and if there's not an issue filed for it already (see [issue tracker](https://github.com/cabrerahector/wordpress-popular-posts/issues)), please [create an issue](https://github.com/cabrerahector/wordpress-popular-posts/issues/new/choose) so I can keep track of it.
* Developers can send [pull requests](https://help.github.com/articles/using-pull-requests) to suggest fixes / improvements to the source. See [Contributing](https://github.com/cabrerahector/wordpress-popular-posts/blob/master/.github/contributing.md) for more details.
* Want to translate WPP into your language or update a current translation? Check if it's [already supported](https://translate.wordpress.org/projects/wp-plugins/wordpress-popular-posts/) or download [this POT file](https://github.com/cabrerahector/wordpress-popular-posts/tree/master/i18n) to translate the strings (see [I want to translate your plugin into my language / help you update a translation. What do I need to do?](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#i-want-to-translate-your-plugin-into-my-language--help-you-update-a-translation-what-do-i-need-to-do) for more).


## License

[GNU General Public License version 2 or later](http://www.gnu.org/licenses/gpl-2.0.html)

Copyright (C) 2008-2025  Héctor Cabrera - https://cabrerahector.com

The WordPress Popular Posts plugin is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

The WordPress Popular Posts plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with the WordPress Popular Posts plugin; if not, see [http://www.gnu.org/licenses](http://www.gnu.org/licenses/).
