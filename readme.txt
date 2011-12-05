=== Wordpress Popular Posts ===
Contributors: hcabrera
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=dadslayer%40gmail%2ecom&lc=GB&item_name=Wordpress%20Popular%20Posts%20Plugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG_global%2egif%3aNonHosted
Tags: popular, posts, widget, seo, wordpress
Requires at least: 2.8
Tested up to: 3.2.1
Stable tag: 2.2.1

With Wordpress Popular Posts, you can show your visitors what are the most popular entries on your blog.

== Description ==

Wordpress Popular Posts is a highly customizable sidebar widget that displays the most popular posts on your blog. You can use it as a template tag, too!

**IMPORTANT NOTICES:**

Due to critical security issues, from Wordpress Popular Posts v.2.1.5 and on **TimThumb support has been dropped**. Please upgrade as soon as possible since your site may be under risk of being exploited.

From **version 2.0** and on, **Wordpress Popular Posts** requires at least **Wordpress 2.8** in order to function correctly. If you are not running Wordpress 2.8 or can't update your blog right now, please don't upgrade to/install version 2.x!

Also, if you are upgrading from any version prior to 1.4.6, please [update to 1.4.6](http://downloads.wordpress.org/plugin/wordpress-popular-posts.1.4.6.zip) before moving to 2.x!

**What's new**

* Include a **thumbnail** of your posts! (*see the [FAQ section](http://wordpress.org/extend/plugins/wordpress-popular-posts/faq/) for technical requirements*)
* From version 2.0.3 and on, Wordpress Popular Posts includes a **Dashboard panel** where you can monitor what are the most popular posts on your site directly from your wp-admin area!
* Wordpress Popular Posts is **multi-widget** capable! Install multiple instances of Wordpress Popular Posts on your sidebars, each with its own unique settings!
* **Shortcode support!** - from version 2.0, you can showcase your most popular posts on pages, too!
* **Category exclusion** - Want to exclude certain categories from the listing? Use the *Exclude Category* option!
* **Automatic maintenance** - Wordpress Popular Posts will wipe out from its cache automatically all those posts that have not been viewed more than 30 days from the current date, keeping the popular ones on the list! This ensures that your cache table will remain as compact as possible! (You can also clear it manually if you like, [look here for instructions](http://wordpress.org/extend/plugins/wordpress-popular-posts/faq/)!).
* **Template tags** - Don't feel like using widgets? No problem! You can still embed your most popular entries on your theme using the **wpp_get_mostpopular()** template tag. Additionally, the **wpp_gets_views()** template tag allows you to retrieve the views count for a particular post. For usage and instructions, please refer to the [instalation section](http://wordpress.org/extend/plugins/wordpress-popular-posts/installation/).

**Other features**

* Use **your own layout**! Control how your most popular posts are shown on your theme.
* *Wordpress Popular Posts can be localized*! Languages included on this release: *English* (default), *Italian* and *Spanish*. Wanna know how to translate Wordpress Popular Posts into your language? See the [FAQ section](http://wordpress.org/extend/plugins/wordpress-popular-posts/faq/) for more!
* *Time Range* - list your most popular posts within a specific time range (eg. popular posts of the last 24 hours, this week's popular posts, etc.)!
* [WP-PostRatings](http://wordpress.org/extend/plugins/wp-postratings/) support added!  Show your visitors how your readers are rating your posts!
* Wanna show your readers a sneak peak of your most popular entries? Wordpress Popular Posts can include excerpts, too!
* List your posts either by **comment count**, **views** or **average daily views**. Sorted by **comment count** by default.
* You can also list those pages of your blog (About, Services, Archives, etc.) that are getting a lot of attention from your readers. Enabled by default.

== Installation ==

1. Download the plugin and extract its contents.
2. Upload the `wordpress-popular-posts` folder to the `/wp-content/plugins/` directory.
3. Activate **Wordpress Popular Posts** plugin through the 'Plugins' menu in WordPress.
4. In your admin console, go to Appeareance > Widgets, drag the Wordpress Popular Posts widget to wherever you want it to be and click on Save.
5. (optional) Go to Appeareance > Editor. On "Theme Files", click on `header.php` and make sure that the `<?php wp_head(); ?>` tag is present (should be right before the closing `</head>` tag).

That's it!

= Using Wordpress Popular Posts on Pages =

If you want to use Wordpress Popular Posts on your pages (a "Hall of Fame" page, for example) please use the shortcode `[wpp]`. Attributes are **optional**, however you can use them if needed. You can find a complete list of the attributes Wordpress Popular Posts currently supports at your *wp-admin > Settings > Wordpress Popular Posts* page.

**Usage:**

`[wpp]`

`[wpp attribute='value']`

Example:

`[wpp range=today stats_views=1 order_by=views wpp_start=<ol> wpp_end=</ol>]`



= Template Tags =


***wpp_get_mostpopular***

Due to the fact that some themes are not widget-ready, or that some blog users don't like widgets at all, there's another choice: the **wpp_get_mostpopular** template tag. With it, you can embed the most popular posts of your blog on your site's sidebar without using a widget. This function also accepts parameters (optional) so you can customize the look and feel of the listing.


**Usage:**

Without any parameters:

`<?php if (function_exists('wpp_get_mostpopular')) wpp_get_mostpopular(); ?>`


Using parameters:


`<?php if (function_exists('wpp_get_mostpopular')) wpp_get_mostpopular("range=weekly&order_by=comments"); ?>`


For a complete list of parameters (also known as "attributes"), please check your *wp-admin > Settings > Wordpress Popular Posts* page.


***wpp_get_views()***

The **wpp_get_views** template tag retrieves the views count of a single post since the plugin was installed. It only accepts one parameter: the post ID (eg. echo wpp_get_views(15)). If the function doesn't get passed a post ID when called, it'll return false instead.

**Usage:**

`<?php if (function_exists('wpp_get_views')) { echo wpp_get_views( get_the_ID() ); } ?>`

== Frequently Asked Questions ==

* *I'm getting "Sorry. No data so far". What's up with that?*
There are a number of reasons that might explain why you are seeing this message: Wordpress Popular Posts won't count views generated by logged in users (if your blog requires readers to be logged in to access its contents, [this tutorial](http://wordpress.org/support/topic/398760) is for you); your current theme does not have the [wp_header()](http://codex.wordpress.org/Theme_Development#Plugin_API_Hooks) tag in its &lt;head&gt; section, required by my plugin to keep track of what your visitors are viewing on your site; no one has seen your posts/pages since Wordpress Popular Posts activation, you should give it some time. Wordpress Popular Posts works based on views, mainly. Whenever a post gets a view, WPP will register it on its cache table. Only those posts registered by my plugin will be listed. It doesn't really make much difference if a post has got a lot of comments or not if it hasn't been cached by my plugin - it still needs to be viewed by someone/people in order to rank as popular.

* *I'm unable to activate the "Display post thumbnail" option. Why?*
Make sure that: your host is running **PHP 4.3 or higher**; the **GD library** is installed and [enabled by your host](http://wordpress.org/support/topic/289778#post-1366038); your theme has been enabled to use the [Featured Images functionality](http://codex.wordpress.org/Post_Thumbnails#Enabling_Support_for_Post_Thumbnails).

* *I'm seeing a "No thumbnail" image, where's my post thumbnail?*
Make sure you have assigned one to your posts (either by [attaching an image to your post](http://codex.wordpress.org/Using_Image_and_File_Attachments#Attachment_to_a_Post) or selected one using the [Featured Images functionality](http://codex.wordpress.org/Post_Thumbnails#Enabling_Support_for_Post_Thumbnails)), otherwise my plugin will show this image by default.

* *Can I embed my most popular posts in any other ways than via sidebar widgets?*
Yes. You have two other ways to achieve this: via **shortcode** [wpp] (so you can embed it directly on your posts / pages), or via **template tag**.

* *What are the parameters that the wpp_get_mostpopular() template tag and the [wpp] shortcode accept?*
You can find a complete list of parameters via wp-admin > Settings > Wordpress Popular Posts under the section "What attributes does Wordpress Popular Posts shortcode [wpp] have?".

* *I would like to clear Wordpress Popular Posts cache and start over. How can I do that?*
If you go to *wp-admin > Settings > Wordpress Popular Posts*, you'll find two buttons that should do what you need: **Clear cache** and **Clear all data**. The first one just wipes out what's in cache, keeping the historical data intact (All-time). The latter, wipes out everything from Wordpress Popular Posts data tables - even the historical data. Note that this **cannot be undone**.

* *Does your plugin include any css stylesheets?*
Yes, *but* there are no predefined styles (well, almost). Wordpress Popular Posts will first look into your theme's folder for the wpp.css file and use it if found so that any custom CSS styles are not overwritten, otherwise will use the one bundled with the plugin. It's up to you to style your most popular posts list as you like. You might need a hand with that if you don't know HTML/CSS, though.

* *Each time Wordpress Popular Posts gets updated, the stylesheet gets reset.*
You need to copy your custom wpp.css to your theme's folder, otherwise my plugin will use the one 

* *Would you help me style my list, please?*
For a small donation, sure why not?

* *I want to remove WPP's stylesheet. How can I do that?*
Simply add the following code to yout theme's functions.php file:
`add_action('wp_head', 'remove_wpp_stylesheet', 1); function remove_wpp_stylesheet() { global $wp_widget_factory; remove_action( 'wp_head', array($wp_widget_factory->widgets['WordpressPopularPosts'], 'wpp_print_stylesheet') ); }`

* *I want your plugin to have x or y functionality. Would you do it for me?*
I usually accept suggestions, yes. However, if it doesn't fit the nature of my plugin (to list popular posts) or requires something that might affect other users' experiences, chances are that I won't implement it. However, I could cook up a customized version of Wordpress Popular Posts just for you if you really, really need that special feature/capability ... but it won't be for free.

* *I want to translate your plugin into my language / help you update a PO file. What do I need to do?*
There's a PO file included with Wordpress Popular Posts. If your language is not already supported by my plugin, you can use a [gettext](http://www.gnu.org/software/gettext/) editor like [Poedit](http://www.poedit.net/) to translate all definitions into your language. If you want to, you can send me your resulting PO and MO files to *me at cabrerahector dot com* so I can include them on the next release of my plugin.

* *Help! I'm having some issues with your plugin! What should I do?*
Please don't, and read my words carefully, don't use my email address to contact me for support (unless I authorize you to do so). It'll surely be of more help for other people running into similar issues if you posted your doubts/questions/suggestions on the [Wordpress Popular Posts Support forums](http://wordpress.org/tags/wordpress-popular-posts?forum_id=10) (please be as descriptive as possible)!

== Screenshots ==

1. Widgets Control Panel.
2. Wordpress Popular Posts Widget.
3. Wordpress Popular Posts Widget on Kubrik Theme's sidebar.
4. Wordpress Popular Posts Stats panel.

== Changelog ==
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
* Post title excerpt now includes html entities. Characters like &Aring;&Auml;&Ouml; should display properly now.
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

Due to critical security issues, from Wordpress Popular Posts v.2.1.5 TimThumb support has been dropped. Please upgrade as soon as possible since your site may be under risk of being exploited.