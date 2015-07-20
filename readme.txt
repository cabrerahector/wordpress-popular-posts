=== WordPress Popular Posts ===
Contributors: hcabrera
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=hcabrerab%40gmail%2ecom&lc=GB&item_name=WordPress%20Popular%20Posts%20Plugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG_global%2egif%3aNonHosted
Tags: popular, posts, widget, popularity, top
Requires at least: 3.8
Tested up to: 4.2.2
Stable tag: 3.2.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress Popular Posts is a highly customizable widget that displays the most popular posts on your blog.

== Description ==

WordPress Popular Posts is a highly customizable widget that displays the most popular posts on your blog.

= Main Features =
* **Multi-widget capable**. That is, you can have several widgets of WordPress Popular Posts on your blog - each with its own settings!
* **Time Range** - list those posts of your blog that have been the most popular ones within a specific time range (eg. last 24 hours, last 7 days, last 30 days, etc.)!
* **Custom Post-type support**. Wanna show other stuff than just posts and pages?
* Display a **thumbnail** of your posts! (*see the [FAQ section](http://wordpress.org/extend/plugins/wordpress-popular-posts/faq/) for technical requirements*).
* Use **your own layout**! Control how your most popular posts are shown on your theme.
* **WPML** support!
* **WordPress Multisite** support!

= Other Features =
* Check the **statistics** on your most popular posts from wp-admin.
* Order your popular list by comments, views (default) or average views per day!
* **Shortcode support** - use the [wpp] shortcode to showcase your most popular posts on pages, too! For usage and instructions, please refer to the [installation section](http://wordpress.org/extend/plugins/wordpress-popular-posts/installation/).
* **Template tags** - Don't feel like using widgets? No problem! You can still embed your most popular entries on your theme using the *wpp_get_mostpopular()* template tag. Additionally, the *wpp_gets_views()* template tag allows you to retrieve the views count for a particular post. For usage and instructions, please refer to the [installation section](http://wordpress.org/extend/plugins/wordpress-popular-posts/installation/).
* **Localizable** to your own language (*See the [FAQ section](http://wordpress.org/extend/plugins/wordpress-popular-posts/faq/) for more info*).
* **[WP-PostRatings](http://wordpress.org/extend/plugins/wp-postratings/) support**. Show your visitors how your readers are rating your posts!

= Notices =
* Starting version 3.0.0, the way plugin tracks views count switched back to [AJAX](http://codex.wordpress.org/AJAX). The reason for this change is to prevent bots / spiders from inflating views count, so if you're using a caching plugin you should clear its cache after installing / upgrading the WordPress Popular Posts plugin so it can track your posts and pages normally.

**WordPress Popular Posts** is now also on [GitHub](https://github.com/cabrerahector/wordpress-popular-posts)!

== Installation ==

1. Download the plugin and extract its contents.
2. Upload the `wordpress-popular-posts` folder to the `/wp-content/plugins/` directory.
3. Activate **WordPress Popular Posts** plugin through the "Plugins" menu in WordPress.
4. In your admin console, go to Appearance > Widgets, drag the WordPress Popular Posts widget to wherever you want it to be and click on Save.
5. If you have a caching plugin installed on your site, flush its cache now so WPP can start tracking your site.
6. Go to Appearance > Editor. On "Theme Files", click on `header.php` and make sure that the `<?php wp_head(); ?>` tag is present (should be right before the closing `</head>` tag).
7. (optional, but recommended for large / high traffic sites) Enabling [Data Sampling](https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance#data-sampling) and/or [Caching](https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance#caching) is recommended. Check [here](https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance) for more.

That's it!

= USAGE =

WordPress Popular Posts can be used in three different ways:

1. As a [widget](http://codex.wordpress.org/WordPress_Widgets), simply drag and drop it into your theme's sidebar and configure it.
2. As a template tag, you can place it anywhere on your theme with [wpp_get_mostpopular()](https://github.com/cabrerahector/wordpress-popular-posts/wiki/2.-Template-tags#wpp_get_mostpopular).
3. Via [shortcode](https://github.com/cabrerahector/wordpress-popular-posts/wiki/1.-Using-WPP-on-posts-&-pages), so you can embed it inside a post or a page.

Make sure to stop by the **[Wiki](https://github.com/cabrerahector/wordpress-popular-posts/wiki)** as well, you'll find even more info there!

== Frequently Asked Questions ==

#### I need help with your plugin! What should I do?
First thing to do is read all the online documentation available ([Installation](http://wordpress.org/plugins/wordpress-popular-posts/installation/), [Usage](https://github.com/cabrerahector/wordpress-popular-posts#usage), [Wiki](https://github.com/cabrerahector/wordpress-popular-posts/wiki), and of course this section) as they should address most of the questions you might have about this plugin (and even more info can be found via *wp-admin > Settings > WordPress Popular Posts > FAQ*).

If you're having problems with WPP, my first suggestion would be try disabling all other plugins and then re-enable each one to make sure there are no conflicts. Also, try switching to a different theme and see if the issue persists. Checking the [Support Forum](http://wordpress.org/support/plugin/wordpress-popular-posts) and the [issue tracker](https://github.com/cabrerahector/wordpress-popular-posts/issues) is also a good idea as chances are that someone else has already posted something about it. **Remember:** *read first*. It'll save you (and me) time.

= -FUNCTIONALITY- =

= Why WordPress Popular Posts? =
The idea of creating this plugin came from the need to know how many people were actually reading each post. Unfortunately, WordPress doesn't keep views count of your posts anywhere. Because of that, and since I didn't find anything that would suit my needs, I ended up creating WordPress Popular Posts: a highly customizable, easy-to-use WordPress plugin with the ability to keep track of what's popular to showcase it to the visitors!

= How does the plugin count views / calculate the popularity of posts? =
Since WordPress doesn't store views count (only comments count), this plugin stores that info for you. When you sort your popular posts by *views*, WordPress Popular Posts will retrieve the views count it started caching from the time you first installed this plugin, and then rank the top posts according to the settings you have configured in the plugin. WordPress Popular Posts can also rank the popularity of your posts based on comments count as well.

= I'm getting "Sorry. No data so far". What's up with that? =
There are a number of reasons that might explain why you are seeing this message: no one has seen or commented on your posts/pages since WordPress Popular Posts activation, you should give it some time; your current theme does not have the [wp_head()](http://codex.wordpress.org/Theme_Development#Plugin_API_Hooks) tag in its &lt;head&gt; section, required by my plugin to keep track of what your visitors are viewing on your site; WordPress Popular Posts was unable to create the necessary DB tables to work, make sure your hosting has granted you permission to create / update / modify tables in the database; if you're using a caching plugin -such as W3 Total Cache- you need to clear its cache once right after installing/upgrading this plugin.

= My current theme does not support widgets (booooo!). Can I show my most popular posts in any other way? =
Yes, there are other choices: you can use the [wpp shortcode](https://github.com/cabrerahector/wordpress-popular-posts/wiki/1.-Using-WPP-on-posts-&-pages), which allows you to embed your popular listing directly in the content of your posts and/or pages; or you can use the [wpp_get_mostpopular() template tag](https://github.com/cabrerahector/wordpress-popular-posts/wiki/2.-Template-tags#wpp_get_mostpopular). Both options are highly customizable via parameters, check them out via *wp-admin > Settings > WordPress Popular Posts > Parameters*.

= WordPress Popular Posts is counting my own visits, why? =
By default the plugin will register every page view from all visitors, including logged-in users. If you don't want WPP to track your own page views, please go to *wp-admin > Settings > WordPress Popular Posts > Tools* and change the "*Log views from*" option from *Everyone* to your preferred choice.

= How can I use my own HTML markup with your plugin? =
If you're using the widget, simply activate the *Use custom HTML markup* option and set your desired configuration and *Content Tags* (see *wp-admin > Settings > WordPress Popular Posts > Parameters* for more); or if you're using the template tag / shortcode, use the *wpp_start*, *wpp_end* and *post_html* parameters (see *wp-admin > Settings > WordPress Popular Posts > Parameters* for more).

A more advanced way to customize the HTML markup is via [WordPress filters](http://code.tutsplus.com/articles/the-beginners-guide-to-wordpress-actions-and-filters--wp-27373 "The Beginner's guide to WordPress actions and filters") by hooking into *wpp_custom_html* or *wpp_post*. For details, please check the [Filters page](https://github.com/cabrerahector/wordpress-popular-posts/wiki/3.-Filters) on the Wiki.

= Where can I find the list of parameters accepted by the wpp_get_mostpopular() template tag / [wpp] shortcode? =
You can find it via *wp-admin > Settings > WordPress Popular Posts > Parameters*.

= I'm unable to activate the "Display post thumbnail" option. Why? =
Please check that either the [ImageMagick](http://www.php.net/manual/en/intro.imagick.php) or [GD](http://www.php.net/manual/en/intro.image.php) extension is installed and [enabled by your host](http://wordpress.org/support/topic/289778#post-1366038).

= How does WordPress Popular Posts pick my posts' thumbnails? =
WordPress Popular Posts has three different thumbnail options to choose from available at *wp-admin > Settings > WordPress Popular Posts > Tools*: *Featured Image* (default), *First image on post*, or [*custom field*](http://codex.wordpress.org/Custom_Fields). If no images are found, a default thumbnail will be displayed instead.

= I'm seeing a "No thumbnail" image, where's my post thumbnail? =
Make sure you have assigned one to your posts (see previous question).

= Is there any way I can change that ugly "No thumbnail" image for one of my own? =
Fortunately, yes. Go to *wp-admin > Settings > WordPress Popular Posts > Tools* and check under *Thumbnail source*. Ideally, the thumbnail you're going to use should be set already with your desired width and height - however, the uploader will give you other size options as configured by your current theme.

= I want to have a popular list of my custom post type. How can I do that? =
Simply add your custom post type to the Post Type field in the widget (or if you're using the template tag / shortcode, use the *post_type* parameter).

= I would like to clear all data gathered by WordPress Popular Posts and start over. How can I do that? =
If you go to *wp-admin > Settings > WordPress Popular Posts > Tools*, you'll find two buttons that should do what you need: **Clear cache** and **Clear all data**. The first one just wipes out what's in cache (Last 24 hours, Last 7 Days, Last 30 Days, etc.), keeping the historical data (All-time) intact. The latter wipes out everything from WordPress Popular Posts data tables - even the historical data. Note that **this cannot be undone** so proceed with caution.

= Can WordPress Popular Posts run on WordPress Multisite? =
Starting from version 3.0.0, WPP checks for WordPress Multisite. While I have not tested it, WPP should work just fine under WPMU (but if it doesn't, please let me know).

= -CSS AND STYLESHEETS- =

= Does your plugin include any CSS stylesheets? =
Yes, *but* there are no predefined styles (well, almost). WordPress Popular Posts will first look into your current theme's folder for the wpp.css file and use it if found so that any custom CSS styles made by you are not overwritten, otherwise will use the one bundled with the plugin.

= Each time WordPress Popular Posts is updated the wpp.css stylesheet gets reset and I lose all changes I made to it. How can I keep my custom CSS? =
Copy your modified wpp.css file to your theme's folder, otherwise my plugin will use the one bundled with it by default.

= How can I style my list to look like [insert your desired look here]? =
Since this plugin does not include any predefined designs, it's up to you to style your most popular posts list as you like (you might need to hire someone for this if you don't know HTML/CSS, though). However, I've gathered a few [examples](https://github.com/cabrerahector/wordpress-popular-posts/wiki/6.-Styling-the-list) that should get you started.

= I want to remove WPP's stylesheet from the header of my theme. How can I do that? =
You can disable the stylesheet via *wp-admin > Settings > WordPress Popular Posts > Tools*.

= -OTHER STUFF THAT YOU (PROBABLY) WANT TO KNOW- =

= Does WordPress Popular Posts support other languages than english? =
Yes, check the [Other Notes](http://wordpress.org/plugins/wordpress-popular-posts/other_notes/) section for more information.

= I want to translate your plugin into my language / help you update a translation. What do I need to do? =
First thing you need to do is get a [gettext](http://www.gnu.org/software/gettext/) editor like [Poedit](http://www.poedit.net/) to translate all texts into your language. You'll find several .PO files bundled with the plugin under the *lang* folder. If you're planning to add a new language, check this handy [guide](http://urbangiraffe.com/articles/translating-wordpress-themes-and-plugins/ "Translating WordPress Plugins & Themes"). If you're interested in sharing your translation with others (or just helped update a current translation), please [let me know](http://wordpress.org/support/plugin/wordpress-popular-posts).

= I want your plugin to have X or Y functionality. Can it be done? =
If it fits the nature of my plugin and it sounds like something other users would like to have too, there's a pretty good chance that I will implement it (and if you can provide some sample code with useful comments, much better).

= Your plugin seems to conflict with my current Theme / this other Plugin. Can you please help me? =
If the theme/plugin you're talking about is a free one that can be downloaded from somewhere, sure I can try and take a look into it. Premium themes/plugins are out of discussion though, unless you're willing to grant me access to your site (or get me a copy of this theme/plugin) so I can check it out.

= ETA for your next release? =
Updates will come depending on my work projects (I'm a full-time web developer) and the amount of time I have on my hands. Quick releases will happen only when/if critical bugs are spotted.

= I posted a question at the Support Forum and got no answer from the developer. Why is that? =
Chances are that your question has been already answered either at the [Support Forum](http://wordpress.org/support/plugin/wordpress-popular-posts), the [Installation section](http://wordpress.org/plugins/wordpress-popular-posts/installation/), the [Wiki](https://github.com/cabrerahector/wordpress-popular-posts/wiki) or even here in the FAQ section, so I've decided not to answer. It could also happen that I'm just busy at the moment and haven't been able to read your post yet, so please be patient.

= Is there any other way to contact you? =
For the time being, the [Support Forum](http://wordpress.org/support/plugin/wordpress-popular-posts) is the only way to contact me. Please do not use my email to get in touch with me *unless I authorize you to do so*.

== Screenshots ==

1. Widgets Control Panel.
2. WordPress Popular Posts Widget.
3. WordPress Popular Posts Widget on theme's sidebar.
4. WordPress Popular Posts Stats panel.

== Changelog ==
= 3.2.3 =
**If you're using a caching plugin, flushing its cache after installing / upgrading to this version is highly recommended.**

- Fixes a potential bug that might affect other plugins & themes (thanks @pippinsplugins).
- Defines INNODB as default storage engine.
- Adds the wpp-no-data CSS class to style the "Sorry, no data so far" message.
- Adds a new index to summary table.
- Updates plugin's documentation.
- Other small bug fixes and improvements.

= 3.2.2 =
**If you're using a caching plugin, flushing its cache after installing / upgrading to this version is recommended.**

* Moves sampling logic into Javascript (thanks, [@kurtpayne](https://github.com/kurtpayne)!)
* Simplifies category filtering logic.
* Fixes list sorting issue that some users were experimenting (thanks, sponker!)
* Widget uses stock thumbnails when using predefined size (some conditions apply).
* Adds the ability to enable / disable responsive support for thumbails.
* Renames wpp_update_views action hook to wpp_post_update_views, **update your code!**
* Adds wpp_pre_update_views action hook.
* Adds filter wpp_render_image.
* Drops support for get_mostpopular() template tag.
* Fixes empty HTML tags (thumbnail, stats).
* Removes Japanese, French and Norwegian Bokmal translation files from plugin.
* Many minor bug fixes / enhancements.

= 3.2.1 =
* Fixes missing HTML decoding for custom HTML in widget.
* Puts LIMIT clause back to the outer query.

= 3.2.0 =
* Adds check for jQuery.
* Fixes invalid parameter in htmlspecialchars().
* Switches AJAX update to POST method.
* Removes href attribute from link when popular post is viewed.
* Removes unnecesary ORDER BY clause in views/comments subquery.
* Fixes Javascript console not working under IE8 (thanks, @[raphaelsaunier](https://github.com/raphaelsaunier)!)
* Fixes WPML compatibility bug storing post IDs as 0.
* Removes wpp-upload.js since it was no longer in use.
* Fixes undefined default thumbnail image (thanks, Lea Cohen!)
* Fixes rating parameter returning false value.
* Adds Data Sampling (thanks, @[kurtpayne](https://github.com/kurtpayne)!)
* Minor query optimizations.
* Adds {date} (thanks, @[matsuoshi](https://github.com/matsuoshi)!) and {thumb_img} tags to custom html.
* Adds minute time option for caching.
* Adds wpp_data_sampling filter.
* Removes jQuery's DOM ready hook for AJAX views update.
* Adds back missing GROUP BY clause.
* Removes unnecesary HTML decoding for custom HTML (thanks, Lea Cohen!)
* Translates category name when WPML is detected.
* Adds list of available thumbnail sizes to the widget.
* Other minor bugfixes and improvements.

= 3.1.1 =
* Adds check for exif extension availability.
* Rolls back check for user's default thumbnail.

= 3.1.0 =
* Fixes invalid HTML title/alt attributes caused by encoding issues.
* Fixes issue with jQuery not loading properly under certain circumstances.
* Fixes issue with custom excerpts not showing up.
* Fixes undefined notices and removes an unused variable from widget_update().
* Fixes wrong variable reference in __image_resize().
* Adds charset to mb_substr when truncating excerpt.
* Sets default logging level to 1 (Everyone).
* Renders the category link with cat-id-[ID] CSS class.
* Replaces getimagesize() with exif_imagetype().
* Adds notice to move/copy wpp.css stylesheet into theme's directory to keep custom CSS styles across updates.
* Thumbail generation process has been refactored for efficiency.
* Thumbnails are now stored in a custom folder under Uploads.
* Drops support on Japanese and French languages since the translations were outdated.
* Other minor bug fixes and improvements.

= 3.0.3 =
* Fixes widget not saving 'freshness' setting.
* Adds HTMLentities conversion/deconversion on wpp_get_mostpopular().
* Improves thumbnail detection.
* Fixes a bug affecting the truncation of excerpts.
* Fixes yet another bug on wpp_get_views().
* Other minor changes.

= 3.0.2 =
* Fixes an introduced bug on wpp_get_views().
* Fixes bug where thumbnail size was cached for multiple instances.
* Adds back stylesheet detection.
* Removes unused widget.js file.
* Other minor bug fixes.

= 3.0.1 =
* Fixes bug on wpp_get_views.
* Sustitutes WP_DEBUG with custom debugging constant.
* Fixes bug that prevented disabling plugin's stylesheet.

= 3.0.0 =
* Starting from this version, the way plugin tracks views count switched back to [AJAX](http://codex.wordpress.org/AJAX) to prevent bots / spiders from inflating views count. If you're using a caching plugin you should clear its cache after installing / upgrading the WordPress Popular Posts plugin so it can track your posts and pages normally.
* Plugin refactoring based on [@tikaszvince](https://github.com/tikaszvince)'s work (many thanks, Vince!).
* Added WPML support.
* Added experimental WordPress Multisite support.
* Added bot detection.
* Added ability to filter posts by freshness.
* Added own data caching method.
* Added filters wpp_custom_html, wpp_post.
* Added action wpp_update_views.
* Dropped support on Dutch and Persian languages since the translations were outdated.
* Several other fixes and improvements.

= 2.3.5 =
* Fixed minor bugs on admin page.
* Fixed query bug preventing some results from being listed.
* Added a check to avoid using the terms tables if not necessary (eg. listing pages only).

= 2.3.4 =
* Added ability to shorten title/excerpt by number of words.
* Updated excerpt code, don't show it if empty.
* Added ability to set post_type on Stats page.
* Added check for is_preview() to avoid updating views count when editing and previewing a post / page (thanks, Partisk!).
* Added ability to change default thumbnail via admin (thanks for the suggestion, Martin!).
* Fixed bug in query when getting popular posts from category returning no results if it didn't have any post on the top viewed / commented.
* Added function for better handling changes/updates in settings.
* Updated get_summary() to use API functions instead querying directly to DB.
* Updated wpp_print_stylesheet() to get the wpp.css file from the right path (thanks, Martin!).
* Moved translations to lang folder.

= 2.3.3 =
* Minimum WordPress version requirement changed to 3.3.
* Minimum PHP version requirement changed to 5.2.0.
* Improved Custom HTML feature! It's more flexible now + new Content Tags added: {url}, {text_title}, {author}, {category}, {views}, {comments}!.
* Added ability to exclude posts by ID (similar to the category filter).
* Added ability to enable / disable logging visits from logged-in users.
* Added Category to the Stats Tag settings options.
* Added range parameter to wpp_get_views().
* Added numeric formatting to the wpp_get_views() function.
* When enabling the Display author option, author's name will link to his/her profile page.
* Fixed bad numeric formatting in Stats showing truncated views count.
* Fixed AJAX update feature (finally!). WPP works properly now when using caching plugins!
* Fixed WP Post Ratings not displaying on the list (and while it works, there are errors coming from the WP Post Ratings plugin itself: http://wordpress.org/support/topic/plugin-wp-postratings-undefined-indexes).
* Improved database queries for speed.
* Fixed bug preventing PostRating to show.
* Removed Timthumb (again) in favor of the updated get_img() function based on Victor Teixeira's vt_resize function.
* Cron now removes from cache all posts that have been trashed or eliminated.
* Added proper numeric formatting for views / comments count. (Thank you for the tip, dimagsv!)
* Added "the title filter fix" that affected some themes. (Thank you, jeremyers1!)
* Added dutch translation. (Thank you, Jeroen!)
* Added german translation. (Thank you, Martin!)

= 2.3.2 =
* The ability to enable / disable the Ajax Update has been removed. It introduced a random bug that doubled the views count of some posts / pages. Will be added back when a fix is ready.
* Fixed a bug preventing the cat parameter from excluding categories (widget was not affected by this).
* FAQ section (Settings / WordPress Popular Posts / FAQ) updated.
* Added french translation. (Thanks, Le Raconteur!)

= 2.3.1 =
* Fixed bug caused by the sorter function when there are multiple instances of the widget.
* Added check for new options in the get_popular_posts function.
* Added plugin version check to handle upgrades.
* Fixed bug preventing some site from fetching images from subdomains or external sites.
* Fixed bug that prevented excluding more than one category using the Category filter.

= 2.3.0 =
* Merged all pages into Settings/WordPress Popular Posts.
* Added new options to the WordPress Popular Posts Stats dashboard.
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
* Dropped TimThumb support in favor of WordPress's Featured Image function.

= 2.1.4 =
* Added italian localization. Thanks Gianni!
* Added charset detection.
* Fixed bug preventing HTML View / Visual View on Edit Post page from working.

= 2.1.1 =
* Fixed bug preventing widget title from being saved.
* Fixed bug affecting blogs with WordPress installed somewhere else than domain's root.
* Added htmlentities to post titles.
* Added default thumbnail image if none is found in the post.

= 2.1.0 =
* Title special HTML entities bug fixed.
* Thumbnail feature improved! WordPress Popular Posts now supports The Post Thumbnail feature. You can choose whether to select your own thumbnails, or let WordPress Popular Posts create them for you!
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
* Added extra functionalities to WordPress Popular Post plugin core

= 1.1  =
* Fixed comment count bug

= 1.0 =
* Public release

== Language support ==

All translations are community made: people who are nice enough to share their translations with me so I can distribute them with the plugin. If you spot an error, or feel like helping improve a translation, please check the [FAQ section](http://wordpress.org/plugins/wordpress-popular-posts/faq/ "FAQ section") for instructions.

* English (supported by Hector Cabrera).
* Spanish (supported by Hector Cabrera).
* German - 86% translated.

== Credits ==

* Flame graphic by freevector/Vecteezy.com.

== Upgrade Notice ==

= 3.2.3 =
If you're using a caching plugin, flushing its cache after upgrading is highly recommended.
