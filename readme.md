# WordPress Popular Posts

A highly customizable WordPress widget to display the most popular posts on your blog.

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

WordPress Popular Posts (from now on, just *WPP*) is a highly customizable [plugin](http://wordpress.org/plugins/wordpress-popular-posts/) to showcase the most commented / viewed entries on your [WordPress](http://wordpress.org/) powered site.


## Features

* **Multi-widget capable**. That is, you can have several widgets of WordPress Popular Posts on your blog - each with its own settings!
* **Time Range** - list those posts of your blog that have been the most popular ones within a specific time range (eg. last 24 hours, last 7 days, last 30 days, etc.)!
* **WPML support**.
* **WordPress Multiuser support**.
* **Custom Post-type support**. Wanna show other stuff than just posts and pages?
* Display a **thumbnail** of your popular posts! (see [technical requirements](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#im-unable-to-activate-the-display-post-thumbnail-option-why)).
* Use **your own layout**! [Control how your most popular posts are shown on your theme](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#how-can-i-use-my-own-html-markup-with-your-plugin).
* Check the **statistics** on your most popular posts from wp-admin.
* Order your popular list by comments, views (default) or average views per day!
* **Shortcode support** - use the `[wpp]` shortcode to showcase your most popular posts on pages, too! (see "[Using WPP on posts & pages](https://github.com/cabrerahector/wordpress-popular-posts/wiki/1.-Using-WPP-on-posts-&-pages)").
* **Template tags** - Don't feel like using widgets? No problem! You can still embed your most popular entries on your theme using the `wpp_get_mostpopular()` template tag. Additionally, the `wpp_gets_views()` template tag allows you to retrieve the views count for a particular post. For usage and instructions, please refer to the [Template tags page](https://github.com/cabrerahector/wordpress-popular-posts/wiki/2.-Template-tags).
* **Localizable** to your own language (See [here](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#i-want-to-translate-your-plugin-into-my-language--help-you-update-a-translation-what-do-i-need-to-do) for more info).
* **[WP-PostRatings](http://wordpress.org/extend/plugins/wp-postratings/) support**. Show your visitors how your readers are rating your posts!


## Requirements

* WordPress 3.8 or above.
* PHP 5.2+ or above.
* Either the [ImageMagik](http://www.php.net/manual/en/intro.imagick.php) or [GD](http://www.php.net/manual/en/intro.image.php) library installed and enabled on your server (not really required, but needed to create thumbnails).


## Installation

1. [Download the plugin](http://wordpress.org/plugins/wordpress-popular-posts/) and extract its contents.
2. Upload the `wordpress-popular-posts` folder to the `/wp-content/plugins/` directory.
3. Activate **WordPress Popular Posts** plugin through the 'Plugins' menu in WordPress.
4. In your admin console, go to Appeareance > Widgets, drag the WordPress Popular Posts widget to wherever you want it to be and click on Save.
5. (optional) Go to Appeareance > Editor. On "Theme Files", click on `header.php` and make sure that the `<?php wp_head(); ?>` tag is present (should be right before the closing `</head>` tag).
6. (optional, but recommended for large / high traffic sites) Enabling [Data Sampling](https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance#data-sampling) and/or [Caching](https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance#caching) is recommended. Check [here](https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance) for more.


## Usage

WPP can be used as a [WordPress Widget](http://codex.wordpress.org/WordPress_Widgets), which means you can place it on any of your theme's sidebars (and it even supports multiple instances!). However, you can also embed it directly in posts / pages via [shortcode](https://github.com/cabrerahector/wordpress-popular-posts/wiki/1.-Using-WPP-on-posts-&-pages); or anywhere on your theme using the [wpp_get_mostpopular()](https://github.com/cabrerahector/wordpress-popular-posts/wiki/2.-Template-tags#wpp_get_mostpopular) template tag.

... and there's even more on the **[Wiki](https://github.com/cabrerahector/wordpress-popular-posts/wiki)** section, so make sure to stop by!


## Support

Before submitting an issue, please:

1. Read the documentation, it's there for a reason. Links: [Requirements](https://github.com/cabrerahector/wordpress-popular-posts#requirements) | [Installation](https://github.com/cabrerahector/wordpress-popular-posts#installation) | [Wiki](https://github.com/cabrerahector/wordpress-popular-posts/wiki) | [Frequently asked questions](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ).
2. If it's a bug, please check the [issue tracker](https://github.com/cabrerahector/wordpress-popular-posts/issues) first make sure no one has reported it already.

When submitting an issue, please answer the following questions:

1. WordPress version?
2. WPP version?
3. Are you using the widget or the shortcode/template tag?
4. Describe what the issue is (include steps to reproduce it, if necessary).


## Contributing

* If you have any ideas/suggestions/bug reports, and if there's not an issue filed for it already (see [issue tracker](https://github.com/cabrerahector/wordpress-popular-posts/issues), please [create an issue](https://github.com/cabrerahector/wordpress-popular-posts/issues/new) so I can keep track of it.
* Developers can send [pull requests](https://help.github.com/articles/using-pull-requests) to suggest fixes / improvements to the source.
* Want to translate WPP to your language or update a current translation? Check if it's [already supported](https://github.com/cabrerahector/wordpress-popular-posts/tree/master/lang) or download [this file](https://github.com/cabrerahector/wordpress-popular-posts/blob/master/lang/wordpress-popular-posts.po) to translate the strings (see "[I want to translate your plugin into my language / help you update a translation. What do I need to do?](https://github.com/cabrerahector/wordpress-popular-posts/wiki/5.-FAQ#i-want-to-translate-your-plugin-into-my-language--help-you-update-a-translation-what-do-i-need-to-do)" for more).


## License

[GNU General Public License version 2 or later](http://www.gnu.org/licenses/gpl-2.0.html)

Copyright (C) 2015  Héctor Cabrera - http://cabrerahector.com

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