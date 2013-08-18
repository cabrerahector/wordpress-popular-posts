# Wordpress Popular Posts

A highly customizable Wordpress widget to display the most popular posts on your blog.

----
## Table of contents
 
* [Description](https://github.com/cabrerahector/wordpress-popular-posts#description)
* [Features](https://github.com/cabrerahector/wordpress-popular-posts#features)
* [Requirements](https://github.com/cabrerahector/wordpress-popular-posts#requirements)
* [Installation](https://github.com/cabrerahector/wordpress-popular-posts#installation)
* [Usage](https://github.com/cabrerahector/wordpress-popular-posts#usage)
* [Frequently asked questions](https://github.com/cabrerahector/wordpress-popular-posts#frequently-asked-questions)
* [Support](https://github.com/cabrerahector/wordpress-popular-posts#support)
* [Changelog](https://github.com/cabrerahector/wordpress-popular-posts#changelog)
* [Contributing](https://github.com/cabrerahector/wordpress-popular-posts#contributing)
* [License](https://github.com/cabrerahector/wordpress-popular-posts#license)


## Description

Wordpress Popular Posts (from now on, just *WPP*) is a [plugin](http://codex.wordpress.org/Plugins) to showcase the most commented / viewed entries on your [Wordpress](http://wordpress.org/) powered blog/site.


## Features

* **Multi-widget capable**. That is, you can have several widgets of Wordpress Popular Posts on your blog - each with its own settings!
* **Time Range** - list those posts of your blog that have been the most popular ones within a specific time range (eg. last 24 hours, last 7 days, last 30 days, etc.)!
* **Custom Post-type support**. Wanna show other stuff than just posts and pages?
* Display a **thumbnail** of your posts! (*see [technical requirements](https://github.com/cabrerahector/wordpress-popular-posts#im-unable-to-activate-the-display-post-thumbnail-option-why) *).
* Use **your own layout**! Control how your most popular posts are shown on your theme. *Updated! See [changelog](https://github.com/cabrerahector/wordpress-popular-posts#changelog) for more!*
* Check the **statistics** on your most popular posts from wp-admin.
* Order your popular list by comments, views (default) or average views per day!
* **Shortcode support** - use the [wpp] shortcode to showcase your most popular posts on pages, too! For usage and instructions, please refer to the [usage section](https://github.com/cabrerahector/wordpress-popular-posts#usage).
* **Template tags** - Don't feel like using widgets? No problem! You can still embed your most popular entries on your theme using the `wpp_get_mostpopular()` template tag. Additionally, the `wpp_gets_views()` template tag allows you to retrieve the views count for a particular post. For usage and instructions, please refer to the [usage section](https://github.com/cabrerahector/wordpress-popular-posts#usage).
* **Localizable** to your own language (*See [here](https://github.com/cabrerahector/wordpress-popular-posts#i-want-to-translate-your-plugin-into-my-language--help-you-update-a-translation-what-do-i-need-to-do) for more info*).
* **[WP-PostRatings](http://wordpress.org/extend/plugins/wp-postratings/) support**. Show your visitors how your readers are rating your posts!
* **Automatic maintenance** - Wordpress Popular Posts will wipe out from its cache automatically all those posts that have not been viewed more than 30 days from the current date, keeping just the popular ones on the list! This ensures that your cache table will remain as compact as possible! (You can also clear it manually if you like, [look here for instructions](https://github.com/cabrerahector/wordpress-popular-posts#i-would-like-to-clear-all-data-gathered-by-wordpress-popular-posts-and-start-over-how-can-i-do-that)!).


## Requirements

* Wordpress 3.3 or above.
* PHP 5.2+ or above.
* Either the [ImageMagik](http://www.php.net/manual/en/intro.imagick.php) or [GD](http://www.php.net/manual/en/intro.image.php) library installed and enabled on your server (not really required, but needed to create thumbnails).


## Installation

1. [Download the plugin](http://wordpress.org/extend/plugins/wordpress-popular-posts/) and extract its contents.
2. Upload the `wordpress-popular-posts` folder to the `/wp-content/plugins/` directory.
3. Activate **Wordpress Popular Posts** plugin through the 'Plugins' menu in WordPress.
4. In your admin console, go to Appeareance > Widgets, drag the Wordpress Popular Posts widget to wherever you want it to be and click on Save.
5. (optional) Go to Appeareance > Editor. On "Theme Files", click on `header.php` and make sure that the `<?php wp_head(); ?>` tag is present (should be right before the closing `</head>` tag).


## Usage

WPP's main feature is the ability to use it as a widget, which is ideal for themes that supports this feature since it can be used on any of your sidebars / footer. However, this plugin can also be used via shortcode or using a WPP's custom template tag:

###SHORTCODE###

If you want to use Wordpress Popular Posts on your pages (a "Hall of Fame" page, for example) please use the shortcode `[wpp]`. By default, it'll list the **most viewed posts** (up to 10) in the last 24 hours. However, you can change the output and the time range by passing parameters to the shortcode (**optional**). You can find the full list of available parameters via *wp-admin > Settings > Wordpress Popular Posts > FAQ*.

**Example:**

`[wpp range=daily stats_views=1 order_by=views]`


###TEMPLATE TAGS###

=`wpp_get_mostpopular`=

With the `wpp_get_mostpopular` template tag you can embed the most popular posts of your blog on your site's sidebar without using a widget. Optionally, you can pass some parameters to this function so you can customize your popular posts (for a complete list of parameters, please go to *wp-admin > Settings > Wordpress Popular Posts > FAQ*).

**Warning:** other users have reported that using this template tag on PHP widgets such as [Linkable Title HTML and PHP widget](http://wordpress.org/extend/plugins/linkable-title-html-and-php-widget/) and others might not render the PHP code correctly, making the `wpp_get_mostpopular` template tag fail and return "Sorry, no data so far". I suggest using it directly on your theme's sidebar.php file to avoid issues.


**Usage:**

Without any parameters, it will list the **most viewed posts** (up to 10) in the last 24 hours:

`<?php if (function_exists('wpp_get_mostpopular')) wpp_get_mostpopular(); ?>`


Using parameters:

`<?php if (function_exists('wpp_get_mostpopular')) wpp_get_mostpopular("range=weekly&order_by=comments"); ?>`

=`wpp_get_views()`=

The `wpp_get_views` template tag retrieves the views count of a single post/page. It accepts two parameters: the *post ID* (required), and *time range* (optional). If *time range* isn't provided the function will retrieve the total amount of views, otherwise it'll return the number of views received within the selected time range.

**Usage:**

`<?php if (function_exists('wpp_get_views')) { echo wpp_get_views( get_the_ID() ); } ?>`
`<?php if (function_exists('wpp_get_views')) { echo wpp_get_views( 15, 'weekly' ); } ?>`


## Frequently asked questions

#### I need help with your plugin! What should I do? ####
First thing to do is read the [installation](https://github.com/cabrerahector/wordpress-popular-posts#installation) and [usage](https://github.com/cabrerahector/wordpress-popular-posts#usage) sections (and this section as well) as they should address most of the questions you might have about this plugin (and even more info can be found via *wp-admin > Settings > Wordpress Popular Posts > FAQ*). If you're having problems with WPP, my first suggestion would be try disabling all other plugins and then re-enable each one to make sure there are no conflicts. Also, try switching to a different theme and see if the issue persists. Checking the [Support Forum](http://wordpress.org/support/plugin/wordpress-popular-posts) and the [issue tracker](https://github.com/cabrerahector/wordpress-popular-posts/issues) is also a good idea as chances are that someone else has already posted something about it. **Remember:** *read first*. It'll save you (and me) time.

### -FUNCTIONALITY- ###

#### Why Wordpress Popular Posts? ####
The idea of creating this plugin came from the need to know how many people were actually reading each post. Unfortunately, Wordpress doesn't keep views count of your posts anywhere. Because of that, and since I didn't find anything that would suit my needs, I ended up creating Wordpress Popular Posts: a highly customizable, easy-to-use Wordpress plugin with the ability to keep track of what's popular to showcase it to the visitors!

#### How does the plugin count views / calculate the popularity of posts? ####
Since Wordpress doesn't store views count (only comments count), this plugin stores that info for you. When you sort your popular posts by *views*, Wordpress Popular Posts will retrieve the views count it started caching from the time you first installed this plugin, and then rank the top posts according to the settings you have configured in the plugin. Wordpress Popular Posts can also rank the popularity of your posts based on comments count as well.

#### I'm getting "Sorry. No data so far". What's up with that? ####
There are a number of reasons that might explain why you are seeing this message: no one has seen or commented on your posts/pages since Wordpress Popular Posts activation, you should give it some time; your current theme does not have the [wp_head()](http://codex.wordpress.org/Theme_Development#Plugin_API_Hooks) tag in its &lt;head&gt; section, required by my plugin to keep track of what your visitors are viewing on your site; Wordpress Popular Posts was unable to create the necessary DB tables to work, make sure your hosting has granted you permission to create / update / modify tables in the database.

#### My current theme does not support widgets (booooo!). Can I show my most popular posts in any other way? ####
Yes, there are other choices: you can use the [wpp shortcode](https://github.com/cabrerahector/wordpress-popular-posts#shortcode), which allows you to embed your popular listing directly in the content of your posts and/or pages; or you can use the [`wpp_get_mostpopular()` template tag](https://github.com/cabrerahector/wordpress-popular-posts#template-tags). Both options are highly customizable via parameters, check them out via *wp-admin > Settings > Wordpress Popular Posts > FAQ*.

#### Wordpress Popular Posts is not counting my own visits, why? ####
By default, Wordpress Popular Posts won't count views generated by logged in users. If your blog requires readers to be logged in to access its contents (or just want WPP to count your own views) please go to *wp-admin > Settings > Wordpress Popular Posts > Tools* and set *Log views from* to *Everyone*.

#### I'm unable to activate the "Display post thumbnail" option. Why? ####
Requirements have changed as of Wordpress Popular Posts 2.3.3. **PHP 5.2+** and **Wordpress 3.0.0** are the minimum requirements to enable thumbnails. Wordpress Popular Posts 2.3.2 and below require **PHP 4.3 or higher**. Also, the **GD library** must be installed and [enabled by your host](http://wordpress.org/support/topic/289778#post-1366038).

#### How does Wordpress Popular Posts pick my posts' thumbnails? ####
Wordpress Popular Posts has three different thumbnail options to choose from available at *wp-admin > Settings > Wordpress Popular Posts > Tools*: *Featured Image* (default), *First image on post*, or [*custom field*](http://codex.wordpress.org/Custom_Fields). If no images are found, a "No thumbnail" image will be used instead.

#### I'm seeing a "No thumbnail" image, where's my post thumbnail? ####
Make sure you have assigned one to your posts (see previous question).

#### Is there any way I can change that ugly "No thumbnail" image for one of my own? ####
Fortunately, yes. Go to *wp-admin > Settings > Wordpress Popular Posts > Tools* and check under *Thumbnail source*. Ideally, the thumbnail you're going to use should be set already with your desired width and height - however, the uploader will give you other size options as configured by your current theme.

#### Where can I find the list of parameters accepted by the wpp_get_mostpopular() template tag / [wpp] shortcode? ####
You can find it via *wp-admin > Settings > Wordpress Popular Posts > FAQ*, under the section *"List of parameters accepted by wpp_get_mostpopular() and the [wpp] shortcode"*.

#### I want to have a popular list of my custom post type. How can I do that? ####
Simply add your custom post type to the Post Type field in the widget (or if you're using the template tag / shortcode, use the *post_type* parameter).

#### How can I use my own HTML markup with your plugin? ####
Wordpress Popular Posts is flexible enough to let you use your own HTML markup. If you're using the widget, simply activate the *Use custom HTML markup* option and set your desired configuration and *Content Tags* (see *wp-admin > Settings > Wordpress Popular Posts > FAQ* under *List of parameters accepted by `wpp_get_mostpopular()` and the [wpp] shortcode* for more); or if you're using the template tag / shortcode, use the *wpp_start*, *wpp_end* and *post_html* parameters (see details in the section mentioned before).

#### I would like to clear all data gathered by Wordpress Popular Posts and start over. How can I do that? ####
If you go to *wp-admin > Settings > Wordpress Popular Posts > Tools*, you'll find two buttons that should do what you need: **Clear cache** and **Clear all data**. The first one just wipes out what's in cache (Last 24 hours, Last 7 Days, Last 30 Days), keeping the historical data (All-time) intact. The latter wipes out everything from Wordpress Popular Posts data tables - even the historical data. Note that this **cannot be undone** so proceed with caution.

#### Can Wordpress Popular Posts run on Wordpress Multisite? ####
While **it's not officially supported**, users have reported that my plugin runs fine on Wordpress Multisite. According to what they have said, you need to install this plugin using the *Network Activation* feature. Note that there are features that *might* not work as expected (eg. thumbnails) as I have never tested this plugin under WP Multisite.

### -CSS AND STYLESHEETS- ###

#### Does your plugin include any CSS stylesheets? ####
Yes, *but* there are no predefined styles (well, almost). Wordpress Popular Posts will first look into your current theme's folder for the wpp.css file and use it if found so that any custom CSS styles made by you are not overwritten, otherwise will use the one bundled with the plugin.

#### Each time Wordpress Popular Posts is updated the wpp.css stylesheet gets reset and I lose all changes I made to it. How can I keep my custom CSS? ####
Copy your modified wpp.css file to your theme's folder, otherwise my plugin will use the one bundled with it by default.

#### How can I style my list to look like [insert your desired look here]? ####
Since this plugin does not include any predefined designs, it's up to you to style your most popular posts list as you like. You might need to hire someone for this if you don't know HTML/CSS, though.

#### I want to remove WPP's stylesheet. How can I do that? ####
Simply add the following code to your theme's functions.php file: `<?php wp_dequeue_style('wordpress-popular-posts') ?>` (or disable the stylesheet via *wp-admin > Settings > Wordpress Popular Posts > Tools*).

### -OTHER STUFF THAT YOU (PROBABLY) WANT TO KNOW- ###

#### I want to translate your plugin into my language / help you update a translation. What do I need to do? ####
There's a PO file included with Wordpress Popular Posts. If your language is not already supported by my plugin, you can use a [gettext](http://www.gnu.org/software/gettext/) editor like [Poedit](http://www.poedit.net/) to translate all texts into your language. If you want to, you can send me your resulting PO and MO files to *hcabrerab at gmail dot com* so I can include them on the next release of my plugin (and would be really grateful if you can also help keep it updated on future releases).

#### I want your plugin to have X or Y functionality. Can it be done? ####
If it fits the nature of my plugin and it sounds like something others would like to have, there's a pretty good chance that I will implement it (and if you actually provide some sample code with useful comments, much better hehe).

#### Your plugin seems to conflict with my current Theme / this other Plugin. Can you please help me? ####
If the theme/plugin you're talking about is a free one and it's available to the public, sure I can try and take a look into it. Premium themes/plugins are out of discussion, though (unless you're willing to buy them for me so I can test them, and even that way I won't provide any guarantees since I'm doing this for free :P).

#### ETA for your next release? ####
Updates will come depending on my work projects (I'm a full-time web developer) and the amount of time I have on my hands. Quick releases will happen only when/if critical bugs are spotted.

#### I posted a question at the Support Forum and got no answer from the developer. Why is that? ####
Chances are that your question has been already answered either at the [Support Forum](http://wordpress.org/support/plugin/wordpress-popular-posts), the [Installation section](https://github.com/cabrerahector/wordpress-popular-posts#installation), or here in the FAQ section. So, since you chose not to read these sections I will simply ignore your posts as well. It could also happen that I'm just busy at the moment and haven't been able to read your post yet, so please be patient (in the meanwhile, search the [Support Forum](http://wordpress.org/support/plugin/wordpress-popular-posts) and the [issue tracker](https://github.com/cabrerahector/wordpress-popular-posts/issues) for an answer).

#### Is there any other way to contact you? ####
For the time being, the [Support Forum](http://wordpress.org/support/plugin/wordpress-popular-posts) is the only way to contact me. Please do not use my email to get in touch with me *unless I authorize you to do so*.


## Support

Before submitting an issue, please:

1. Read the documentation, it's there for a reason. Links: [Installation](https://github.com/cabrerahector/wordpress-popular-posts#installation) | [Frequently asked questions](https://github.com/cabrerahector/wordpress-popular-posts#frequently-asked-questions).
2. If the bug actually exists, check the [issue tracker](https://github.com/cabrerahector/wordpress-popular-posts/issues) to make sure there's no existing issue reporting the bug you just found.

When submitting an issue, please answer the following questions:

1. Wordpress version?
2. WPP version?
3. Are you using the widget or the shortcode/template tag?
4. Describe what the issue is (include steps to reproduce it, if necessary).


## Changelog

#### 2.3.7 ####
* Fixed category excluding/including bug.

#### 2.3.6 ####
* Added ability to set links' target attribute (thanks, Pedro!).
* Added sanitization for external thumbnail filenames to avoid weird characters.
* Added a new content tag, {score}, to display the post rating as a simple integer (thanks, Artem!).
* Added japanese and persian translations (thanks kjmtsh and Tatar).
* Added wpp-list class to the UL tag, this should help style the popular list better.
* Added plugin version to wp_enqueue_* calls.
* Updated thumbnail feature to handle external images.
* Updated wpp.css with text floating next to thumbnails - this sets a predefined style for the plugin for the first time.
* Removed unnecesary wpp-thumbnail class from link tag, the image already has it.
* Fixed typo in wpp_update_warning. From v2.3.3, minimun Wordpress version required is 3.3.
* Fixed minor bugs.

#### 2.3.5 ####

* Fixed minor bugs on admin page.
* Fixed query bug preventing some results from being listed.
* Added a check to avoid using the terms tables if not necessary (eg. listing pages only).

#### 2.3.4 ####

* Added ability to shorten title/excerpt by number of words.
* Updated excerpt code, don't show it if empty.
* Added ability to set post_type on Stats page.
* Added check for `is_preview()` to avoid updating views count when editing and previewing a post / page (thanks, Partisk!).
* Added ability to change default thumbnail via admin (thanks for the suggestion, Martin!).
* Fixed bug in query when getting popular posts from category returning no results if it didn't have any post on the top viewed / commented.
* Added function for better handling changes/updates in settings.
* Updated `get_summary()` to use API functions instead querying directly to DB.
* Updated `wpp_print_stylesheet()` to get the wpp.css file from the right path (thanks, Martin!).
* Moved translations to lang folder.

#### 2.3.3 ####

* Minimum Wordpress version requirement changed to 3.3.
* Minimum PHP version requirement changed to 5.2.0.
* Improved Custom HTML feature! It's more flexible now + new Content Tags added: {url}, {text_title}, {author}, {category}, {views}, {comments}!.
* Added ability to exclude posts by ID (similar to the category filter).
* Added ability to enable / disable logging visits from logged-in users.
* Added Category to the Stats Tag settings options.
* Added range parameter to wpp_get_views().
* Added numeric formatting to the `wpp_get_views()` function.
* When enabling the Display author option, author's name will link to his/her profile page.
* Fixed bad numeric formatting in Stats showing truncated views count.
* Fixed AJAX update feature (finally!). WPP works properly now when using caching plugins!
* Fixed WP Post Ratings not displaying on the list (and while it works, there are errors coming from the WP Post Ratings plugin itself: http://wordpress.org/support/topic/plugin-wp-postratings-undefined-indexes).
* Improved database queries for speed.
* Fixed bug preventing PostRating to show.
* Removed Timthumb (again) in favor of the updated `get_img()` function based on Victor Teixeira's vt_resize function.
* Cron now removes from cache all posts that have been trashed or eliminated.
* Added proper numeric formatting for views / comments count. (Thank you for the tip, dimagsv!)
* Added "the title filter fix" that affected some themes. (Thank you, jeremyers1!)
* Added dutch translation. (Thank you, Jeroen!)
* Added german translation. (Thank you, Martin!)

#### 2.3.2 ####

* The ability to enable / disable the Ajax Update has been removed. It introduced a random bug that doubled the views count of some posts / pages. Will be added back when a fix is ready.
* Fixed a bug preventing the cat parameter from excluding categories (widget was not affected by this).
* FAQ section (Settings / Wordpress Popular Posts / FAQ) updated.
* Added french translation. (Thanks, Le Raconteur!)

#### 2.3.1 ####

* Fixed bug caused by the sorter function when there are multiple instances of the widget.
* Added check for new options in the get_popular_posts function.
* Added plugin version check to handle upgrades.
* Fixed bug preventing some site from fetching images from subdomains or external sites.
* Fixed bug that prevented excluding more than one category using the Category filter.

#### 2.3.0 ####

* Merged all pages into Settings/Wordpress Popular Posts.
* Added new options to the Wordpress Popular Posts Stats dashboard.
* Added check for static homepages to avoid printing ajax script there.
* Database queries re-built from scratch for optimization.
* Added the ability to remove / enable plugin's stylesheet from the admin.
* Added the ability to enable / disable ajax update from the admin.
* Added the ability to set thumbnail's source from the admin.
* Timthumb support re-added.
* Added support for custom post type (Thanks, Brad Williams!).
* Improved the category filtering feature.
* Added the ability to get popular posts from given author IDs.

#### 2.2.1 ####

* Quick update to fix error with All-time combined with views breaking the plugin.

#### 2.2.0 ####

* Featured Image is generated for the user automatically if not present and if there's an image attached to the post.
* Range feature Today option changed. Replaced with Last 24 hours.
* Category exclusion query simplified. Thanks to almergabor for the suggestion!
* Fixed bug caused by selecting Avg. Views and All-Time that prevented WPP from getting any data from the BD. Thanks Janseo!
* Updated the get_summary function to strip out shortcodes from excerpt as well.
* Fixed bug in the truncate function affecting accented characters. Thanks r3df!
* Fixed bug keeping db tables from being created. Thanks northlake!
* Fixed bug on the shortcode which was showing pages even if turned off. Thanks danpkraus!

#### 2.1.7 ####

* Added stylesheet detection. If wpp.css is on theme's folder, will use that instead the one bundled with the plugin.

#### 2.1.6 ####

* Added DB character set and collate detection.
* Fixed excerpt translation issue when the qTrans plugin is present. Thanks r3df!.
* Fixed thumbnail dimensions issue.
* Fixed widget page link.
* Fixed widget title encoding bug.
* Fixed deprecated errors on load_plugin_textdomain and add_submenu_page.

#### 2.1.5 ####

* Dropped TimThumb support in favor of Wordpress's Featured Image function.

#### 2.1.4 ####

* Added italian localization. Thanks Gianni!
* Added charset detection.
* Fixed bug preventing HTML View / Visual View on Edit Post page from working.

#### 2.1.1 ####
* Fixed bug preventing widget title from being saved.
* Fixed bug affecting blogs with Wordpress installed somewhere else than domain's root.
* Added htmlentities to post titles.
* Added default thumbnail image if none is found in the post.

#### 2.1.0 ####

* Title special HTML entities bug fixed.
* Thumbnail feature improved! Wordpress Popular Posts now supports The Post Thumbnail feature. You can choose whether to select your own thumbnails, or let Wordpress Popular Posts create them for you!
* Shortcode bug fixed. Thanks Krokkodriljo!
* Category exclusion feature improved. Thanks raamdev!

#### 2.0.3 ####

* Added a Statistics Dashboard to Admin panel so users can view what's popular directly from there.
* Users can now select a different date format.
* `get_mostpopular()` function deprecated. Replaced with `wpp_get_mostpopular()`.
* Cache maintenance bug fixed.
* Several UI enhancements were applied to this version.

#### 2.0.2 ####

* "Keep text format and links" feature introduced. If selected, formatting tags and hyperlinks won't be removed from excerpt.
* Post title excerpt html entities bug fixed. It was causing the excerpt function to display more characters than the requested by user.
* Several shortcode bugs fixed (range, order_by, do_pattern, pattern_form were not working as expected).

#### 2.0.1 ####

* Post title excerpt now includes html entities. Characters like `ÅÄÖ` should display properly now.
* Post excerpt has been improved. Now it supports the following HTML tags: a, b, i, strong, em.
* Template tag `wpp_get_views()` added. Retrieves the views count of a single post.
* Template tag `get_mostpopular()` re-added. Parameter support included.
* Shortcode bug fixed (range was always "daily" no matter what option was being selected by the user).

#### 2.0.0 ####

* Plugin rewritten to support Multi-Widget capabilities
* Cache table implemented
* Shortcode support added
* Category exclusion feature added
* Ajax update added - plugin is now compatible with caching plugins such as WP Super Cache
* Thumbnail feature improved - some bugs were fixed, too
* Maintenance page added

#### 1.5.1 ####

* Widget bug fixed

#### 1.5.0 ####

* Database improvements implemented
* WP-PostRatings support added
* Thumbnail feature added

#### 1.4.6 ####

* Bug in get_mostpopular function affected comments on single.php
* "Show pageviews" option bug fixed
* Added "content formatting tags" functionality

#### 1.4.5 ####

* Added new localizable strings
* Fixed Admin page coding bug that was affecting the styling of WPP

#### 1.4.4 ####

* HTML Markup customizer added
* Removed some unnessesary files

#### 1.4.3 ####

* Korean and Swedish are supported

#### 1.4.2 ####

* Code snippet bug found

#### 1.4.1 ####

* Found database bug affecting only new installations

#### 1.4 ####

* Massive code enhancement
* CSS bugs fixed
* Features added: Time Range; author and date (stats tag); separate settings for Widget and Code Snippet

#### 1.3.2 ####

* Permalink bug fixed

#### 1.3.1 ####

* Admin panel styling bug fixed

#### 1.3 ####

* Added an Admin page for a better management of the plugin
* New sorting options (sort posts by comment count, by pageviews, or by average daily views) added

#### 1.2 ####

* Added extra functionalities to Wordpress Popular Post plugin core

#### 1.1  ####

* Fixed comment count bug

#### 1.0 ####

* Public release


## Contributing

* If you have any ideas/suggestions/bug reports, and if there's not an issue filed for it already (see [issue tracker](https://github.com/cabrerahector/wordpress-popular-posts/issues), please [create an issue](https://github.com/cabrerahector/wordpress-popular-posts/issues/new) so I can keep track of it.
* Developers can send [pull requests](https://help.github.com/articles/using-pull-requests) to suggest fixes / improvements to the source.
* Want to translate WPP to your language or update a current translation? Check if it's [already supported](https://github.com/cabrerahector/wordpress-popular-posts/tree/master/lang) or download [this file](https://github.com/cabrerahector/wordpress-popular-posts/blob/master/lang/wordpress-popular-posts.po) to translate the strings (see the [FAQ](https://github.com/cabrerahector/wordpress-popular-posts#frequently-asked-questions) section under *I want to translate your plugin into my language / help you update a translation. What do I need to do?* for more).


## License

[GNU General Public License version 2](http://www.gnu.org/licenses/gpl-2.0.html)

Copyright (C) 2013  Héctor Cabrera - http://cabrerahector.com

The Wordpress Popular Posts plugin is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

The Wordpress Popular Posts plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with the Wordpress Popular Posts plugin; if not, see [http://www.gnu.org/licenses](http://www.gnu.org/licenses/).