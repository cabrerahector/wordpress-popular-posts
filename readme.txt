=== Wordpress Popular Posts ===
Contributors: hcabrera
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=dadslayer%40gmail%2ecom&lc=GB&item_name=Wordpress%20Popular%20Posts%20Plugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG_global%2egif%3aNonHosted
Tags: popular, posts, popular posts, widget, seo, wordpress, custom post type
Requires at least: 2.8
Tested up to: 3.4.1
Stable tag: 2.3.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

With Wordpress Popular Posts, you can show your visitors what are the most popular entries on your blog.

== Description ==

Wordpress Popular Posts is a highly customizable widget that displays the most popular posts on your blog.

= Main Features =
* **Multi-widget capable**. That is, you can have several widgets of Wordpress Popular Posts on your blog - each with its own settings!
* **Time Range** - list those posts of your blog that have been the most popular ones within a specific time range (eg. last 24 hours, last 7 days, last 30 days, etc.)!
* **Custom Post-type support**. Wanna show other stuff than just posts and pages?
* Display a **thumbnail** of your posts! (*see the [FAQ section](http://wordpress.org/extend/plugins/wordpress-popular-posts/faq/) for technical requirements*).
* Use **your own layout**! Control how your most popular posts are shown on your theme.
* Check the **statistics** on your most popular posts from wp-admin.

= Other Features =
* **Shortcode support** - use the [wpp] shortcode to showcase your most popular posts on pages, too! For usage and instructions, please refer to the [installation section](http://wordpress.org/extend/plugins/wordpress-popular-posts/installation/).
* **Template tags** - Don't feel like using widgets? No problem! You can still embed your most popular entries on your theme using the *wpp_get_mostpopular()* template tag. Additionally, the *wpp_gets_views()* template tag allows you to retrieve the views count for a particular post. For usage and instructions, please refer to the [installation section](http://wordpress.org/extend/plugins/wordpress-popular-posts/installation/).
* **Localizable** to your own language (*See the [FAQ section](http://wordpress.org/extend/plugins/wordpress-popular-posts/faq/) for more info*).
* **[WP-PostRatings](http://wordpress.org/extend/plugins/wp-postratings/) support**. Show your visitors how your readers are rating your posts!
* **Automatic maintenance** - Wordpress Popular Posts will wipe out from its cache automatically all those posts that have not been viewed more than 30 days from the current date, keeping just the popular ones on the list! This ensures that your cache table will remain as compact as possible! (You can also clear it manually if you like, [look here for instructions](http://wordpress.org/extend/plugins/wordpress-popular-posts/faq/)!).

= Notice =

From version 2.0 and on, Wordpress Popular Posts requires Wordpress 2.8 at least in order to function correctly. If you're not running Wordpress 2.8 (or newer) please use [Wordpress Popular Posts v.1.5.1](http://downloads.wordpress.org/plugin/wordpress-popular-posts.1.5.1.zip) instead.

Also, if you are upgrading from any version prior to Wordpress Popular Posts 1.4.6, please [update to 1.4.6](http://downloads.wordpress.org/plugin/wordpress-popular-posts.1.4.6.zip) first!

== Installation ==

1. Download the plugin and extract its contents.
2. Upload the `wordpress-popular-posts` folder to the `/wp-content/plugins/` directory.
3. Activate **Wordpress Popular Posts** plugin through the 'Plugins' menu in WordPress.
4. In your admin console, go to Appeareance > Widgets, drag the Wordpress Popular Posts widget to wherever you want it to be and click on Save.
5. (optional) Go to Appeareance > Editor. On "Theme Files", click on `header.php` and make sure that the `<?php wp_head(); ?>` tag is present (should be right before the closing `</head>` tag).

That's it!

= Shortcode =

If you want to use Wordpress Popular Posts on your pages (a "Hall of Fame" page, for example) please use the shortcode `[wpp]`. Parameters are **optional**, however you can use them if needed. You can find the complete list of the parameters via *wp-admin > Settings > Wordpress Popular Posts > FAQ*.

**Usage:**

`[wpp]`

`[wpp attribute='value']`

Example:

`[wpp range=daily stats_views=1 order_by=views wpp_start=<ol> wpp_end=</ol>]`



= Template Tags =


***wpp_get_mostpopular***

Due to the fact that some themes are not widget-ready, or that some blog users don't like widgets at all, there's another choice: the **wpp_get_mostpopular** template tag. With it, you can embed the most popular posts of your blog on your site's sidebar without using a widget. This function also accepts parameters (optional) so you can customize the look and feel of the listing.


**Usage:**

Without any parameters:

`<?php if (function_exists('wpp_get_mostpopular')) wpp_get_mostpopular(); ?>`


Using parameters:


`<?php if (function_exists('wpp_get_mostpopular')) wpp_get_mostpopular("range=weekly&order_by=comments"); ?>`


For a complete list of parameters, please go to *wp-admin > Settings > Wordpress Popular Posts > FAQ*.


***wpp_get_views()***

The **wpp_get_views** template tag retrieves the total views count of a single post. It only accepts one parameter: the post ID (eg. echo wpp_get_views(15)). If the function doesn't get passed a post ID when called, it'll return false instead.

**Usage:**

`<?php if (function_exists('wpp_get_views')) { echo wpp_get_views( get_the_ID() ); } ?>`

== Frequently Asked Questions ==

= I need help with your plugin! What should I do? =
First thing to do is read both FAQ and Installation sections, they should address most of the questions you might have about this plugin (and even more info can be found via *wp-admin > Settings > Wordpress Popular Posts > FAQ*). If you're having problems with WPP, my suggestion would be try disabling all other plugins first and then re-enable each one to make sure there are no conflicts. Checking the [Support Forum](http://wordpress.org/support/plugin/wordpress-popular-posts) is also a good idea as chances are that someone else has already posted something about it (and if not, you are always welcome to create a new thread). **Remember:** *read first*. It'll save you (and me) time.

= -FUNCTIONALITY- =

= I'm getting "Sorry. No data so far". What's up with that? =
There are a number of reasons that might explain why you are seeing this message: no one has seen or commented on your posts/pages since Wordpress Popular Posts activation, you should give it some time; your current theme does not have the [wp_header()](http://codex.wordpress.org/Theme_Development#Plugin_API_Hooks) tag in its &lt;head&gt; section, required by my plugin to keep track of what your visitors are viewing on your site; Wordpress Popular Posts was unable to create the necessary DB tables to work, make sure your hosting has granted you permission to create / update / modify tables in the database.

= Wordpress Popular Posts is not counting my own visits, why? =
Wordpress Popular Posts won't count views generated by logged in users. If your blog requires readers to be logged in to access its contents, [this tutorial](http://wordpress.org/support/topic/398760) is for you.

= I'm unable to activate the "Display post thumbnail" option. Why? =
Make sure that: your host is running **PHP 4.3 or higher**; the **GD library** is installed and [enabled by your host](http://wordpress.org/support/topic/289778#post-1366038).

= How does Wordpress Popular Posts select my posts' thumbnails? =
By default, Wordpress Popular Posts will try and use the [Featured Image](http://codex.wordpress.org/Post_Thumbnails) you have selected for each of your posts and use it to create a thumbnail. If none is set, a "No thumbnail" image will be used instead. You can also tell Wordpress Popular Posts to get the thumbnails from the first image found on each post, or specify the path of your thumbnails using a [custom field](http://codex.wordpress.org/Custom_Fields). To do so, simply go to *wp-admin > Settings > Wordpress Popular Posts > Tools* and pick the choice of your preference.

= I'm seeing a "No thumbnail" image, where's my post thumbnail? =
Make sure you have assigned one to your posts (either by [attaching an image to your post](http://codex.wordpress.org/Using_Image_and_File_Attachments#Attachment_to_a_Post), selected one using the [Featured Images functionality](http://codex.wordpress.org/Post_Thumbnails#Enabling_Support_for_Post_Thumbnails)), or assigned one using a custom field and told Wordpress Popular Posts what the custom field name is in *wp-admin > Settings > Wordpress Popular Posts > Tools*. Otherwise, my plugin will show this image by default.

= The thumbnail images are broken. What happened? =
Check that the cache subfolder exists (wordpress-popular-posts/cache/) and it's *writable* (chmodd it to 777 if you're unsure about this). Also, Timthumb uses the [PHP GD library](http://php.net/manual/en/book.image.php) to generate images. If it's not installed/enabled, [Timthumb will fail and thumbnails won't be generated](http://wordpress.org/support/topic/289778#post-1366038).

= Can I embed my most popular posts in any other ways than via sidebar widgets? =
Yes. You have two other ways to achieve this: via **shortcode**: [wpp] (so you can embed it directly on your posts / pages), or via **template tag**: wpp_get_mostpopular().

= Where can I find the list of parameters accepted by the wpp_get_mostpopular() template tag / [wpp] shortcode? =
You can find it via *wp-admin > Settings > Wordpress Popular Posts > FAQ*, under the section **"List of parameters accepted by wpp_get_mostpopular() and the [wpp] shortcode"**.

= I want to have a popular list of my custom post type. How can I do that? =
Simply add your custom post type to the Post Type field in the widget (or, if yo're using the template tag / shortcode, use the post_type parameter).

= How can I use my own HTML markup with your plugin? =
Wordpress Popular Posts is flexible enough to let you use your own HTML markup. To do so, simply activate the *Use custom HTML markup* option and set your desired configuration; or if you're using the template tag / shortcode, you can find the equivalent parameters in the section mentioned above.

= I would like to clear all data gathered by Wordpress Popular Posts and start over. How can I do that? =
If you go to *wp-admin > Settings > Wordpress Popular Posts > Tools*, you'll find two buttons that should do what you need: **Clear cache** and **Clear all data**. The first one just wipes out what's in cache (Last 24 hours, Last 7 Days, Last 30 Days), keeping the historical data (All-time) intact. The latter wipes out everything from Wordpress Popular Posts data tables - even the historical data. Note that this **cannot be undone** so proceed with caution.

= Can Wordpress Popular Posts run on Wordpress Multisite? =
While **it's not officially supported**, other users have reported that my plugin runs fine on Wordpress Multisite. According to what they have said, you need to install this plugin using the Network Activation feature.

= -CSS AND STYLESHEETS- =

= Does your plugin include any CSS stylesheets? =
Yes, *but* there are no predefined styles (well, almost). Wordpress Popular Posts will first look into your current theme's folder for the wpp.css file and use it if found so that any custom CSS styles made by you are not overwritten, otherwise will use the one bundled with the plugin.

= How can I style my list to look like that other site / this way? =
Since this plugin does not include any predefined designs, it's up to you to style your most popular posts list as you like. You might need to hire someone for this if you don't know HTML/CSS. Asking questions about styling / CSS at the [Support Forum](http://wordpress.org/support/plugin/wordpress-popular-posts) might not get an answer from me, either.

= Each time Wordpress Popular Posts gets updated, the stylesheet gets reset. =
You need to copy your custom wpp.css file to your theme's folder, otherwise my plugin will use the one bundled with it by default.

= I want to remove WPP's stylesheet. How can I do that? =
Simply add the following code to your theme's functions.php file: `<?php wp_dequeue_style('wordpress-popular-posts') ?>` (or disable the stylesheet via *wp-admin > Settings > Wordpress Popular Posts > Tools*).

= -OTHER STUFF THAT YOU SHOULD KNOW- =

= I want your plugin to have X or Y functionality. Can it be done? =
If it fits the nature of my plugin and it sounds like something other users would like to have, there's a pretty good chance that I will implement it (specially if you actually provide some sample code with useful comments). 

= ETA for your next release? =
Updates will come depending on my work projects (I'm a full-time web developer) and the amount of time I have on my hands. So please, don't ask for ETAs.

= I want to translate your plugin into my language / help you update a translation. What do I need to do? =
There's a PO file included with Wordpress Popular Posts. If your language is not already supported by my plugin, you can use a [gettext](http://www.gnu.org/software/gettext/) editor like [Poedit](http://www.poedit.net/) to translate all texts into your language. If you want to, you can send me your resulting PO and MO files to *hcabrerab at gmail dot com* so I can include them on the next release of my plugin.

= I posted a question on the Support Forum and got no answer from the developer. Why is that? =
Chances are that your question has been already answered either in the [Support Forum](http://wordpress.org/support/plugin/wordpress-popular-posts) or here in the FAQ section, so I will simply ignore your thread. It could also happen that maybe I just haven't read your post so please be patient (in the meanwhile, search the [Support Forum](http://wordpress.org/support/plugin/wordpress-popular-posts) for an answer).

= Is there any other way to contact you? =
For the time being, the [Support Forum](http://wordpress.org/support/plugin/wordpress-popular-posts) is the only way to contact me. Do not, please, do not use my email to get in touch with me *unless I authorize you to do so*.

== Screenshots ==

1. Widgets Control Panel.
2. Wordpress Popular Posts Widget.
3. Wordpress Popular Posts Widget on theme's sidebar.
4. Wordpress Popular Posts Stats panel.

== Changelog ==
= 2.3.2 =
* The ability to enable / disable the Ajax Update has been removed. It introduced a random bug that doubled the views count of some posts / pages. Will be added back when a fix is ready.
* Fixed a bug preventing the cat parameter from excluding categories (widget was not affected by this).
* FAQ section (Settings / Wordpress Popular Posts / FAQ) updated.
* Added french translation. (Thanks, Le Raconteur!)

= 2.3.1 =
* Fixed bug caused by the sorter function when there are multiple instances of the widget.
* Added check for new options in the get_popular_posts function.
* Added plugin version check to handle upgrades.
* Fixed bug preventing some site from fetching images from subdomains or external sites.
* Fixed bug that prevented excluding more than one category using the Category filter.

= 2.3.0 =
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


= 2.2.1 =
* Quick update to fix error with All-time combined with views breaking the plugin.

= 2.2.0 =
* Featured Image is generated for the user automatically if not present and if there's an image attached to the post.
* Range feature Today option changed. Replaced with Last 24 hours.
* Category exclusion query simplified. Thanks to almergabor for the suggestion!
* Fixed bug caused by selecting Avg. Views and All-Time that prevented WPP from getting any data from the BD. Thanks Janseo!
* Updated the get_summary function to strip out shortcodes from excerpt as well.
* Fixed bug in the truncate function affecting accented characters. Thanks r3df!
* Fixed bug keeping db tables from being created. Thanks northlake!
* Fixed bug on the shortcode which was showing pages even if turned off. Thanks danpkraus!

= 2.1.7 =
* Added stylesheet detection. If wpp.css is on theme's folder, will use that instead the one bundled with the plugin.

= 2.1.6 =
* Added DB character set and collate detection.
* Fixed excerpt translation issue when the qTrans plugin is present. Thanks r3df!.
* Fixed thumbnail dimensions issue.
* Fixed widget page link.
* Fixed widget title encoding bug.
* Fixed deprecated errors on load_plugin_textdomain and add_submenu_page.

= 2.1.5 =
* Dropped TimThumb support in favor of Wordpress's Featured Image function.

= 2.1.4 =
* Added italian localization. Thanks Gianni!
* Added charset detection.
* Fixed bug preventing HTML View / Visual View on Edit Post page from working.

= 2.1.1 =
* Fixed bug preventing widget title from being saved.
* Fixed bug affecting blogs with Wordpress installed somewhere else than domain's root.
* Added htmlentities to post titles.
* Added default thumbnail image if none is found in the post.

= 2.1.0 =
* Title special HTML entities bug fixed.
* Thumbnail feature improved! Wordpress Popular Posts now supports The Post Thumbnail feature. You can choose whether to select your own thumbnails, or let Wordpress Popular Posts create them for you!
* Shortcode bug fixed. Thanks Krokkodriljo!
* Category exclusion feature improved. Thanks raamdev!

= 2.0.3 =
* Added a Statistics Dashboard to Admin panel so users can view what's popular directly from there.
* Users can now select a different date format.
* get_mostpopular() function deprecated. Replaced with wpp_get_mostpopular().
* Cache maintenance bug fixed.
* Several UI enhancements were applied to this version.

= 2.0.2 =
* "Keep text format and links" feature introduced. If selected, formatting tags and hyperlinks won't be removed from excerpt.
* Post title excerpt html entities bug fixed. It was causing the excerpt function to display more characters than the requested by user.
* Several shortcode bugs fixed (range, order_by, do_pattern, pattern_form were not working as expected).

= 2.0.1 =
* Post title excerpt now includes html entities. Characters like ÅÄÖ should display properly now.
* Post excerpt has been improved. Now it supports the following HTML tags: a, b, i, strong, em.
* Template tag wpp_get_views() added. Retrieves the views count of a single post.
* Template tag get_mostpopular() re-added. Parameter support included.
* Shortcode bug fixed (range was always "daily" no matter what option was being selected by the user).

= 2.0.0 =
* Plugin rewritten to support Multi-Widget capabilities
* Cache table implemented
* Shortcode support added
* Category exclusion feature added
* Ajax update added - plugin is now compatible with caching plugins such as WP Super Cache
* Thumbnail feature improved - some bugs were fixed, too
* Maintenance page added

= 1.5.1 =
* Widget bug fixed

= 1.5.0 =
* Database improvements implemented
* WP-PostRatings support added
* Thumbnail feature added

= 1.4.6 =
* Bug in get_mostpopular function affected comments on single.php
* "Show pageviews" option bug fixed
* Added "content formatting tags" functionality

= 1.4.5 =
* Added new localizable strings
* Fixed Admin page coding bug that was affecting the styling of WPP

= 1.4.4 =
* HTML Markup customizer added
* Removed some unnessesary files

= 1.4.3 =
* Korean and Swedish are supported

= 1.4.2 =
* Code snippet bug found

= 1.4.1 =
* Found database bug affecting only new installations

= 1.4 =
* Massive code enhancement
* CSS bugs fixed
* Features added: Time Range; author and date (stats tag); separate settings for Widget and Code Snippet

= 1.3.2 =
* Permalink bug fixed

= 1.3.1 =
* Admin panel styling bug fixed

= 1.3 =
* Added an Admin page for a better management of the plugin
* New sorting options (sort posts by comment count, by pageviews, or by average daily views) added

= 1.2 =
* Added extra functionalities to Wordpress Popular Post plugin core

= 1.1  =
* Fixed comment count bug

= 1.0 =
* Public release

== Upgrade Notice ==
From version 2.0 and on, Wordpress Popular Posts requires Wordpress 2.8 at least in order to function correctly. If you're not running Wordpress 2.8 (or newer) please use Wordpress Popular Posts v.1.5.1 instead. Also, if you are upgrading from any version prior to Wordpress Popular Posts 1.4.6, please update to 1.4.6 first!