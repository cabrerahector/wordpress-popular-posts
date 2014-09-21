Changelog
=========
#### 3.1.0 ####
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
* Drops support on Japanese language since the translations were outdated.

#### 3.0.3 ####
* Fixes widget not saving 'freshness' setting.
* Adds HTMLentities conversion/deconversion on wpp_get_mostpopular().
* Improves thumbnail detection.
* Fixes a bug affecting the truncation of excerpts.
* Fixes yet another bug on wpp_get_views().
* Other minor changes.

#### 3.0.2 ####
* Fixes an introduced bug on wpp_get_views().
* Fixes bug where thumbnail size was cached for multiple instances.
* Adds back stylesheet detection.
* Removes unused widget.js file.
* Other minor bug fixes.

#### 3.0.1 ####
* Fixes bug on wpp_get_views.
* Sustitutes WP_DEBUG with custom debugging constant.
* Fixes bug that prevented disabling plugin's stylesheet.

#### 3.0.0 ####
* Plugin refactoring based on [@tikaszvince](https://github.com/tikaszvince)'s work (many thanks, Vince!).
* Added WPML support.
* Added experimental Wordpress Multisite support.
* Added bot detection.
* Added ability to filter posts by freshness.
* Added own data caching method.
* Added filters wpp_custom_html, wpp_post.
* Added action wpp_update_views.
* Dropped support on Dutch and Persian languages since the translations were outdated.
* Several other fixes and improvements.

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