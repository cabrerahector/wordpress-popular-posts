Changelog
=========

## 6.1.4 ##

**If you're using a caching plugin on your website, clearing its cache after installing / updating to this version is highly recommended.**

- Fixes an issue where the [[wpp]](https://github.com/cabrerahector/wordpress-popular-posts/wiki/1.-Using-WPP-on-posts-&-pages#the-wpp-shortcode) shortcode might output empty paragraphs under certain conditions.
- Reverts "uglification" of wpp.min.js which caused popular post list(s) not to load under certain conditions.
- Widget's deprecation notice has been reworded for clarity.
- Updates dependencies + minor code cleanup.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-1-0-improved-php-8-1-support-plus-minor-enhancements/#6.1.4)

## 6.1.3 ##

- **Hotfix**: Fixes rare PHP fatal error in Admin.php (props to winetravelista and scotttripatrek!)

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-1-0-improved-php-8-1-support-plus-minor-enhancements/#6.1.3)

## 6.1.2 ##

**If you're using a caching plugin on your website, clearing its cache after installing / updating to this version is highly recommended.**

- **Deprecation Notice:** The WordPress Popular Posts "classic" widget is going away! If you're using the classic widget please replace it with the [WordPress Popular Posts block](https://github.com/cabrerahector/wordpress-popular-posts/wiki/1.-Using-WPP-on-posts-&-pages#the-wordpress-popular-posts-block) or the [[wpp]](https://github.com/cabrerahector/wordpress-popular-posts/wiki/1.-Using-WPP-on-posts-&-pages#the-wpp-shortcode) shortcode as soon as possible. See release notes for more details.
- Plugin now uses the [Tax_Query](https://developer.wordpress.org/reference/classes/wp_tax_query/) class to filter popular posts by taxonomy, pretty much similar to how WP_Query does it.
- Fixes an issue where selecting the Tiny theme would override the heading of the popular posts list.
- Fixes an issue where taxonomy links would render an extra whitespace for some browsers.
- Fixes issue where when using a theme (eg. Cards) post titles would be unintentionally truncated.
- Adds decoding=async property to WPP's thumbnail.
- General PHPCS/WPCS code improvements.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-1-0-improved-php-8-1-support-plus-minor-enhancements/#6.1.2)

## 6.1.1 ##

- Fixes a rare PHP fatal error that can occur during plugin activation.
- Block: adds links to documentation within the block form for ease of access.
- Updates dependencies.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-1-0-improved-php-8-1-support-plus-minor-enhancements/#6.1.1)

## 6.1.0 ##

- Improves PHP 8.1 support.
- Adds new [filter to modify the post date](https://github.com/cabrerahector/wordpress-popular-posts/wiki/3.-Filters#wpp_the_date).
- Adds check to prevent the misuse of the Data Sampling feature (props to the JPCERT/CC team for reporting this issue).
- Updates dependencies.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-1-0-improved-php-8-1-support-plus-minor-enhancements/)

## 6.0.5 ##

- Fixes yet another issue where excerpts may output broken HTML under certain conditions (thanks dxylott54!)
- Updates .pot file.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-0-php-5-support-dropped-minimum-supported-wordpress-changed/#6.0.5)

## 6.0.4 ##

- Block: improves logic when toggling certain settings.
- Block: adds back option to show post rating.
- get_views() is now compatible with Polylang/WPML.
- Updates dependencies.
- Updates .pot file.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-0-php-5-support-dropped-minimum-supported-wordpress-changed/#6.0.4)

## 6.0.3 ##

- WPCS updates.
- Fixes an issue where excerpts may output broken HTML under certain conditions (thanks ozboss1!)

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-0-php-5-support-dropped-minimum-supported-wordpress-changed/#6.0.3)

## 6.0.2 ##

- Fixes issue with Stats dashboard not loading for Linux users (thanks agbuere!)

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-0-php-5-support-dropped-minimum-supported-wordpress-changed/#6.0.2)

## 6.0.1 ##

- Security improvements.
- Fixes fatal error in Image class (thanks Senri Miura!)
- Fixes fatal error in "classic" widget when using widget themes (thanks Finn Jackson!)
- Updates ChartJS to version 3.8.0.
- Small improvements / fixes.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-6-0-php-5-support-dropped-minimum-supported-wordpress-changed/#minor-updates-and-hotfixes)

## 6.0.0 ##

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

## 5.5.1 ##

**If you're using a caching plugin flushing its cache after upgrading to this version is highly recommended.**

- Fixes an issue where the WordPress Popular Posts block would, under specific conditions, output code that may break the page layout (thanks shoelaced and themosaurus for the report!)
- Fixes minor variable declaration bug in wpp.js.min.
- Cleans up inline style tag (thanks gabu69 and @lflorent for the suggestions!)
- Other minor fixes & changes.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-5-qol-improvements-and-php-5-announcement/#minor-updates-and-hotfixes)

## 5.5.0 ##

- Adds the Sort by Average Daily Views option from the classic widget to the WordPress Popular Posts block.
- Adds the WordPress Date Format option from the classic widget to the WordPress Popular Posts block (please see release notes for details).
- Adds new Content Tags: author_name and author_url.
- Improves cached data garbage collection.
- Other minor performances improvements and fixes.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-5-qol-improvements-and-php-5-announcement/)

## 5.4.2 ##

- Fixes a rare issue that prevented some users from seeing the Stats chart (thanks Zsolt!)

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-4-widget-block-improvements-plus-prep-work-for-csp-support/#5.4.1)

## 5.4.1 ##

**If you're using a caching plugin flushing its cache after upgrading to this version is highly recommended.**

- Widget block: fixes WPP block loading block editor JS files on the front end.
- Adds filter hook to disable Block editor support.
- Improves logic of filter hooks `wpp_excerpt_more` and `wpp_title_more`.
- The Parameters section has been moved to the Wiki (see https://git.io/JEOrX).

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-4-widget-block-improvements-plus-prep-work-for-csp-support/#5.4.1)

## 5.4.0 ##

**If you're using a caching plugin flushing its cache after upgrading to this version is highly recommended.**

- The widget block is no longer an experimental feature and is now available to everyone (but it's still a WIP).
- Widget block: adds AJAX support to prevent caching plugins from caching your popular posts block.
- Widget block: adds WPML/Polylang support.
- Widget block: fixes widget heading not displaying.
- Widget themes: allow themes to detect the current post so it can be styled differently.
- Fixes `wpp_excerpt_more` filter hook not working (props to SchweizerSchoggi!)
- Adds filter hook `wpp_title_more` to allow customization of the prefix added to shortened post titles.
- Removes inline JavaScript code from WPP's dashboard in preparation for [WordPress' CSP adoption](https://core.trac.wordpress.org/ticket/51407).

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-4-widget-block-improvements-plus-prep-work-for-csp-support/)

## 5.3.6 ##

- Fixes a potential authenticated stored XSS vulnerability (props to pluginvulnerabilities.com).
- Fixes widget title not being displayed under certain conditions (thanks Okoth1 and others for reporting this issue!)
- Fixes a PHP notice generated by the widget block on WordPress 5.8.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-3-improved-php-8-support-retina-display-support-and-more/#5.3.6)

## 5.3.5 ##

- Reverts security enhancements (not the XSS one though) which caused garbled output for some websites.
- Fixes a PHP Fatal Error affecting some setups.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-3-improved-php-8-support-retina-display-support-and-more/#5.3.5)

## 5.3.4 ##

- Experimental Gutenberg support! This version introduces the first WPP block. It's not enabled by default though, see the Release Notes for more.
- Fixes a potential authenticated stored XSS vulnerability (props to Visse for reporting it!)
- Overall security enhancements (props to Visse for assisting with this!)
- Other minor improvements / fixes.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-3-improved-php-8-support-retina-display-support-and-more/#5.3.4)

## 5.3.3 ##

- Fixes a potential XSS vulnerability (props to Yu Iwama of Secure Sky Technology Inc. and the JPCERT/CC Vulnerability Coordination Group).
- Fixes a potential code injection vulnerability (props to Jerome & NinTechNet).
- Fixes a srcset bug that affects specific PHP locales (props to fredel).
- Fixes a srcset not loading images due to improper SSL/HTTPS configuration (props to aj4h).
- Updates ChartJS to version 2.9.4.

If you're using a caching plugin flushing its cache after upgrading to this version is highly recommended.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-3-improved-php-8-support-retina-display-support-and-more/#minor-updates-and-hotfixes)

## 5.3.2 ##

- `wpp_get_views()`: fixed an issue where the function would return 0 views under certain conditions (thanks to everyone who helped with this!)

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-3-improved-php-8-support-retina-display-support-and-more/#minor-updates-and-hotfixes)

## 5.3.1 ##

- `wpp_get_views()`: restores previous behavior where when no time range was set the function would return total views count (thanks narolles!)
- The WPP widget will now be loaded via AJAX by default (this affects new installs only.)

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-3-improved-php-8-support-retina-display-support-and-more/#minor-updates-and-hotfixes)

## 5.3.0 ##

- Improves compatibility with PHP 8.
- Allows to override widget theme stylesheets.
- Each post can have its own thumbnail now when using WPP with WPML/Polylang.
- Improved Polylang support.
- Adds a loading animation when using the widget with the Ajaxify widget option enabled.
- Fixes an issue where the plugin wouldn't generate thumbnails when filenames contains Unicode characters.
- The /popular-posts REST API endpoint now correctly translate posts when using WPML/Polylang.
- `wpp_get_views()` can now return views count from custom time ranges.
- Post thumbnails will now look sharper on retina displays!
- Other minor improvements / fixes.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-3-improved-php-8-support-retina-display-support-and-more/)

## 5.2.4 ##

- Fixes PHP notices affecting Block Editor users on WordPress 5.5.
- Fixes a rare PHP warning message that pops up randomly when the Pageviews Cache is enabled.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-2-is-here#hotfixes-and-minor-updates)

## 5.2.3 ##

**If you're using a caching plugin, flushing its cache right after installing / upgrading to this version is required.**

- Fixes a compatibility issue with WordPress 5.5.
- Widget themes: various fixes for better IE11 compatibility.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-2-is-here#hotfixes-and-minor-updates)

## 5.2.2 ##

**If you're using a caching plugin, flushing its cache right after installing / upgrading to this version is required.**

- Fixes compatibility issue with plugins that minify HTML code.
- Updates installation instructions.
- Other minor improvements.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-2-is-here#hotfixes-and-minor-updates)

## 5.2.1 ##

**If you're using a caching plugin, flushing its cache right after installing / upgrading to this version is required.**

- Fixes fatal PHP error triggered on some server setups.
- Makes sure non-ajaxified themed widgets are properly moved into the ShadowRoot.
- Fixes declaration of the wpp_params variable.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-2-is-here#hotfixes-and-minor-updates)

## 5.2.0 ##

**If you're using a caching plugin, flushing its cache right after installing / upgrading to this version is required.**

- JavaScript based Lazy Loading superseded by Native Lazing Loading.
- Improved Pageviews Cache.
- Views/comments count will be prettified now!
- Fixed a few layout issues found in widget themes.
- Improved compatibility with Content Security Policy (CSP).
- Added support for ACF images.
- Other minor improvements and fixes.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-2-is-here)

## 5.1.0 ##

- The /popular-posts GET API endpoint is now being cached as well.
- Added a new Content Tag: title_attr.
- Added a new [filter hook to filter popular posts terms](https://github.com/cabrerahector/wordpress-popular-posts/wiki/3.-Filters#wpp_post_terms).
- Minor code improvements.

## 5.0.2 ##

- A performance notice will be displayed for mid/high traffic sites (see #239).
- Fixed an issue with text_title content tag not being shortened (see #241).
- Added a link to the Debug screen to the plugin's dashboard for ease of access.
- Other minor improvements/changes.

## 5.0.1 ##

**If you're using a caching plugin, flushing its cache right after installing / upgrading to this version is recommended.**

- Fixed a compatibility issue with the newly introduced [widget themes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-0-multiple-taxonomy-support-themes-thumbnail-lazy-loading-and-more/#themes) feature. If you're using a theme with your popular posts widget you'll need to reapply it for it to get the latest changes (go to Appearance > Widgets > WordPress Popular Posts, select a different theme then hit Save, finally switch back to your preferred theme and hit Save again.)
- Fixed two date related issues.
- Minor styling improvements to widget themes Cards, Cards Compact, Cardview and Cardview Compact.
- Removes bold styling from post title on the stock design (wpp.css).
- Improves data caching logic.

## 5.0.0 ##

**If you're using a caching plugin, flushing its cache right after installing / upgrading to this version is required.**

- Code has been refactored to use more modern PHP practices! This will help make WordPress Popular Posts more maintainable and easier to extend.
- WordPress Popular Posts now requires PHP 5.4 or newer and WordPress 4.7 or newer.
- The `WPP_Query` class has been deprecated. Use `WordPressPopularPosts\Query` instead.
- Added ability to filter posts by multiple taxonomies (thanks [blackWhitePanda](https://github.com/blackWhitePanda)!)
- New Dashboard Widget: Trending Now.
- Added 10 new themes for the widget!
- Added ability to lazy load thumbnails (enabled by default).
- Improved support for WPML and Polylang.
- Authors and Editors can now access the Stats dashboard too!
- Fixed translation issues affecting russian and similar languages.
- New Content Tags: total_items and item_position.
- Many minor bug fixes/improvements.

[Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-5-0-multiple-taxonomy-support-themes-thumbnail-lazy-loading-and-more/) | [Full Changelog](https://github.com/cabrerahector/wordpress-popular-posts/compare/4.2.2...5.0.0).

## 4.2.2 ##

- Hotfix: don't typehint scalars, breaks plugin on PHP 5.

## 4.2.1 ##

- Adds [filter to set thumbnail compression quality](https://github.com/cabrerahector/wordpress-popular-posts/wiki/3.-Filters#wpp_thumbnail_compression_quality).
- Adds [filter to change the ending string of the excerpt](https://github.com/cabrerahector/wordpress-popular-posts/wiki/3.-Filters#wpp_excerpt_more) generated by WPP.
- When using multilingual plugins, return the author of the translation instead of the author of the original post.
- Fixes a PHP warning message generated by an unimplemented method in the REST API controller.
- Minor code improvements.

## 4.2.0 ##

**If you're using a caching plugin, flushing its cache right after installing / upgrading to this version is required.**

- **Breaking change**: Database query performance improvements (thanks Stofa!), plugin should be significantly faster for most people out there. Developers: if you're hooking into the `WPP_Query` class to customize the query, you will have to review it as this change will likely break your custom query.
- **Persistent object caching support**: WPP can now store views count in-memory, reducing greatly the number of database writes which is good for performance!
- Adds filter hook [wpp_parse_custom_content_tags](https://github.com/cabrerahector/wordpress-popular-posts/wiki/3.-Filters#wpp_parse_custom_content_tags).
- Adds filter hook [wpp_taxonomy_separator](https://github.com/cabrerahector/wordpress-popular-posts/wiki/3.-Filters#wpp_taxonomy_separator).
- You can now also pass arrays when using the parameters `post_type`, `cat`, `term_id`, `pid` or `author` (see [issue 169](https://github.com/cabrerahector/wordpress-popular-posts/issues/169#issuecomment-419667083) for details).
- The plugin will use language packs from wordpress.org from now on.
- Minor fixes and improvements.

Check the [Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-4-2-is-all-about-speed/) for more details!

## 4.1.2 ##

- Enables [Data Caching](https://github.com/cabrerahector/wordpress-popular-posts/wiki/7.-Performance#caching) by default (new installs only).
- The Parameters section (Settings > WordPress Popular Posts > Parameters) is now mobile-friendly.
- Updated the documentation in the Parameters section.
- Refactored WPP's caching mechanism into its own class.
- Removed unused code.

## 4.1.1 ##

**If you're using a caching plugin, flushing its cache right after installing / upgrading to this version is highly recommended.**

- Improves compatibility with Cloudflare's Rocket Loader.
- Code cleanup.
- Fixes a minor bug (plugin returning the wrong excerpt when a translation plugin is used).
- Bumps minimum required PHP version to 5.3.

## 4.1.0 ##

**If you’re using a caching plugin, flushing its cache right after installing / upgrading to this version is highly recommended.**

- Adds support for the REST API.
- Adds At-a-Glance stats.
- Adds Today time range to Stats section.
- Drops jQuery dependency on front-end (faster loading times!)
- The plugin will no longer display debugging information unless WP_DEBUG is set to true.
- Many minor bug fixes and improvements.

See the [Release notes](https://cabrerahector.com/wordpress/wordpress-popular-posts-4-1-is-here/) for more details!

## 4.0.13 ##

- Improvements to WPP's upgrade process.
- Fixes ALT text missing from IMG tags.

## 4.0.12 ##

- Fixes bug where WPP didn't return the right URL when using Polylang / WPML.
- Fixes a compatibility issue with Yoast SEO (and potentially other plugins as well).
- Improves compatibility with MySQL 5.7+.
- Other minor fixes and improvements.

## 4.0.11 ##

**If you're using a caching plugin, flushing its cache after installing / upgrading to this version is highly recommended.**

- Fixes reference to tracking script.

## 4.0.10 ##

**If you're using a caching plugin, flushing its cache after installing / upgrading to this version is highly recommended.**

- Renames tracking script to prevent issues with ad blockers (props @Damienov).
- Widget: fixes caching (props @zu2).
- Exposes offset parameter to wpp shortcode / `wpp_get_mostpopular` template tag.

## 4.0.9 ##

- Widget: fixes Author ID field not saving/updating.
- Fixes WPP data caching (props @zu2).
- Dashboard: updates Content Tags' documentation.
- Main POT file updated.
- Other minor bug fixes & improvements.

## 4.0.8 ##

- Multisite: plugin can now be installed individually on each site.
- Multisite: improved upgrade process.
- Dashboard: adds multisite check to Debug screen.
- Dashboard: have the Debug screen display active plugins only.
- Improves compatibility with Beaver Builder.
- Adds onload event to ajax widget (props @cawa-93).
- Other minor bug fixes.

## 4.0.6 ##

- Improves compatibility with Multisite.
- Fixes a bug that prevented upgrade process from running on MU (props @gregsullivan!)
- Improves compatibility with Beaver Builder.

## 4.0.5 ##

- Fixes the taxonomy filter for Custom Post Types.
- Updates summary table structure and indexes.
- Adds back ability to use custom wpp.css from theme.
- Dashboard: adds a Debug screen to help with support inquiries.
- Other minor bug fixes and improvements.

## 4.0.3 ##

**This is a hotfix release.**

- Dashboard: escapes post titles to prevent potential XSS (props Delta!)
- Restores ability to use a custom default thumbnail.

## 4.0.2 ##

**This is a hotfix release.**

- Dashboard: fixes thumbnail picker on HTTPS.
- Adds the `wpp_custom_html` filter back.

## 4.0.1 ##

**This is a hotfix release.**

- Fixes a warning message triggered on old PHP versions.
- Fixes undefined `default_thumbnail_sizes` warning message.
- Removes a hardcoded table prefix causing issues on sites that uses a different prefix than the stock one.

## 4.0.0 ##

**If you're using a caching plugin, flushing its cache after installing / upgrading to this version is highly recommended.**

- Plugin code refactored!
- Dashboard section redesigned (now mobile-friendly, too!)
- New Statistics chart and other goodies.
- Adds ability to pick a Custom Time Range!
- Adds ability to filter posts by other taxonomies than just categories!
- Adds Relative Date Format.
- Fixes broken views tracking caused by changeset 41508 https://core.trac.wordpress.org/changeset/41508 (props hykw!)
- Improves PHP7+ compatibility.
- Improves compatibility with WP-SpamShield, WooCommerce, Polylang and WPML.
- Drops qTranslate support (that plugin has been long removed from WordPress.org anyways.)
- New content tags added: {img_url}, {taxonomy}.
- New filters: `wpp_post_class`, `wpp_post_exclude_terms`.
- French and German translation files became too outdated and so support has been dropped for now (want to help? Contact me!)
- Tons of minor bug fixes and improvements.

Also, see [Release notes](https://cabrerahector.com/development/wordpress-popular-posts-4-0-is-finally-out/).

## 3.3.4 ##
- Attempt to convert tables to InnoDB during upgrade if other engine is being used.
- Adds a check to prevent the upgrade process from running too many times.
- Minor improvements and bug fixes.
- Documentation updated.

## 3.3.3 ##
- Fixes potential XSS exploit in WPP's admin dashboard.
- Adds filter to set which post types should be tracked by WPP (details).
- Adds ability to select first attached image as thumbnail source (thanks, [@serglopatin](https://github.com/serglopatin)!)

## 3.3.2 ##
- Fixes warning message: 'stream does not support seeking in...'
- Removes excerpt HTML encoding.
- Passes widget ID to the instance variable for customization.
- Adds CSS class current.
- Documentation cleanup.
- Other minor bug fixes / improvements.

## 3.3.1 ##
- Fixes undefined index notice.
- Makes sure legacy tables are deleted on plugin upgrade.

## 3.3.0 ##
- Adds the ability to limit the amount of data logged by WPP (see Settings > WordPress Popular Posts > Tools for more).
- Adds Polylang support (thanks, [@Chouby](https://github.com/Chouby)!)
- Removes post data from DB on deletion.
- Fixes whitespaces from post_type argument (thanks, [@getdave](https://github.com/getdave)!)
- WPP now handles SSL detection for images.
- Removes legacy datacache and datacache_backup tables.
- Adds Settings page advertisement support.
- FAQ section has been moved over to Github.

## 3.2.3 ##
**If you're using a caching plugin, flushing its cache after installing / upgrading to this version is highly recommended.**

- Fixes a potential bug that might affect other plugins & themes (thanks @pippinsplugins).
- Defines INNODB as default storage engine.
- Adds the wpp-no-data CSS class to style the "Sorry, no data so far" message.
- Adds a new index to summary table.
- Updates plugin's documentation.
- Other small bug fixes and improvements.

## 3.2.2 ##
**If you're using a caching plugin, flushing its cache after installing / upgrading to this version is recommended.**

* Moves sampling logic into Javascript (thanks, [@kurtpayne](https://github.com/kurtpayne)!)
* Simplifies category filtering logic.
* Fixes list sorting issue that some users were experimenting (thanks, sponker!)
* Widget uses stock thumbnails when using predefined size (some conditions apply).
* Adds the ability to enable / disable responsive support for thumbails.
* Renames `wpp_update_views` action hook to `wpp_post_update_views`, **update your code!**
* Adds `wpp_pre_update_views` action hook.
* Adds filter `wpp_render_image`.
* Drops support for `get_mostpopular()` template tag.
* Fixes empty HTML tags (thumbnail, stats).
* Removes Japanese, French and Norwegian Bokmal translation files from plugin.
* Many minor bug fixes / enhancements.

## 3.2.1 ##
* Fixes missing HTML decoding for custom HTML in widget.
* Puts LIMIT clause back to the outer query.

## 3.2.0 ##
* Adds check for jQuery.
* Fixes invalid parameter in htmlspecialchars().
* Switches AJAX update to POST method.
* Removes href attribute from link when popular post is viewed.
* Removes unnecesary ORDER BY clause in views/comments subquery.
* Fixes Javascript console not working under IE8 (thanks, @raphaelsaunier!)
* Fixes WPML compatibility bug storing post IDs as 0.
* Removes wpp-upload.js since it was no longer in use.
* Fixes undefined default thumbnail image (thanks, Lea Cohen!)
* Fixes rating parameter returning false value.
* Adds Data Sampling (thanks, @kurtpayne!)
* Minor query optimizations.
* Adds {date} (thanks, @matsuoshi!) and {thumb_img} tags to custom html.
* Adds minute time option for caching.
* Adds `wpp_data_sampling` filter.
* Removes jQuery's DOM ready hook for AJAX views update.
* Adds back missing GROUP BY clause.
* Removes unnecesary HTML decoding for custom HTML (thanks, Lea Cohen!)
* Translates category name when WPML is detected.
* Adds list of available thumbnail sizes to the widget.
* Other minor bugfixes and improvements.

## 3.1.1 ##
* Adds check for exif extension availability.
* Rolls back check for user's default thumbnail.

## 3.1.0 ##
* Fixes invalid HTML title/alt attributes caused by encoding issues.
* Fixes issue with jQuery not loading properly under certain circumstances.
* Fixes issue with custom excerpts not showing up.
* Fixes undefined notices and removes an unused variable from `widget_update()`.
* Fixes wrong variable reference in `__image_resize()`.
* Adds charset to `mb_substr` when truncating excerpt.
* Sets default logging level to 1 (Everyone).
* Renders the category link with cat-id-[ID] CSS class.
* Replaces `getimagesize()` with `exif_imagetype()`.
* Adds notice to move/copy wpp.css stylesheet into theme's directory to keep custom CSS styles across updates.
* Thumbail generation process has been refactored for efficiency.
* Thumbnails are now stored in a custom folder under Uploads.
* Drops support on Japanese and French languages since the translations were outdated.
* Other minor bug fixes and improvements.

## 3.0.3 ##
* Fixes widget not saving 'freshness' setting.
* Adds HTMLentities conversion/deconversion on `wpp_get_mostpopular()`.
* Improves thumbnail detection.
* Fixes a bug affecting the truncation of excerpts.
* Fixes yet another bug on `wpp_get_views()`.
* Other minor changes.

## 3.0.2 ##
* Fixes an introduced bug on `wpp_get_views()`.
* Fixes bug where thumbnail size was cached for multiple instances.
* Adds back stylesheet detection.
* Removes unused widget.js file.
* Other minor bug fixes.

## 3.0.1 ##
* Fixes bug on `wpp_get_views`.
* Sustitutes WP_DEBUG with custom debugging constant.
* Fixes bug that prevented disabling plugin's stylesheet.

## 3.0.0 ##
* Plugin refactoring based on [@tikaszvince](https://github.com/tikaszvince)'s work (many thanks, Vince!).
* Added WPML support.
* Added experimental Wordpress Multisite support.
* Added bot detection.
* Added ability to filter posts by freshness.
* Added own data caching method.
* Added filters `wpp_custom_html`, `wpp_post`.
* Added action `wpp_update_views`.
* Dropped support on Dutch and Persian languages since the translations were outdated.
* Several other fixes and improvements.

## 2.3.7 ##
* Fixed category excluding/including bug.

## 2.3.6 ##
* Added ability to set links' target attribute (thanks, Pedro!).
* Added sanitization for external thumbnail filenames to avoid weird characters.
* Added a new content tag, {score}, to display the post rating as a simple integer (thanks, Artem!).
* Added japanese and persian translations (thanks kjmtsh and Tatar).
* Added wpp-list class to the UL tag, this should help style the popular list better.
* Added plugin version to `wp_enqueue_*` calls.
* Updated thumbnail feature to handle external images.
* Updated wpp.css with text floating next to thumbnails - this sets a predefined style for the plugin for the first time.
* Removed unnecesary wpp-thumbnail class from link tag, the image already has it.
* Fixed typo in `wpp_update_warning`. From v2.3.3, minimun Wordpress version required is 3.3.
* Fixed minor bugs.

## 2.3.5 ##

* Fixed minor bugs on admin page.
* Fixed query bug preventing some results from being listed.
* Added a check to avoid using the terms tables if not necessary (eg. listing pages only).

## 2.3.4 ##

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

## 2.3.3 ##

* Minimum Wordpress version requirement changed to 3.3.
* Minimum PHP version requirement changed to 5.2.0.
* Improved Custom HTML feature! It's more flexible now + new Content Tags added: {url}, {text_title}, {author}, {category}, {views}, {comments}!.
* Added ability to exclude posts by ID (similar to the category filter).
* Added ability to enable / disable logging visits from logged-in users.
* Added Category to the Stats Tag settings options.
* Added range parameter to `wpp_get_views()`.
* Added numeric formatting to the `wpp_get_views()` function.
* When enabling the Display author option, author's name will link to his/her profile page.
* Fixed bad numeric formatting in Stats showing truncated views count.
* Fixed AJAX update feature (finally!). WPP works properly now when using caching plugins!
* Fixed WP Post Ratings not displaying on the list (and while it works, there are errors coming from the WP Post Ratings plugin itself: http://wordpress.org/support/topic/plugin-wp-postratings-undefined-indexes).
* Improved database queries for speed.
* Fixed bug preventing PostRating to show.
* Removed Timthumb (again) in favor of the updated `get_img()` function based on Victor Teixeira's `vt_resize` function.
* Cron now removes from cache all posts that have been trashed or eliminated.
* Added proper numeric formatting for views / comments count. (Thank you for the tip, dimagsv!)
* Added "the title filter fix" that affected some themes. (Thank you, jeremyers1!)
* Added dutch translation. (Thank you, Jeroen!)
* Added german translation. (Thank you, Martin!)

## 2.3.2 ##

* The ability to enable / disable the Ajax Update has been removed. It introduced a random bug that doubled the views count of some posts / pages. Will be added back when a fix is ready.
* Fixed a bug preventing the cat parameter from excluding categories (widget was not affected by this).
* FAQ section (Settings / Wordpress Popular Posts / FAQ) updated.
* Added french translation. (Thanks, Le Raconteur!)

## 2.3.1 ##

* Fixed bug caused by the sorter function when there are multiple instances of the widget.
* Added check for new options in the `get_popular_posts` function.
* Added plugin version check to handle upgrades.
* Fixed bug preventing some site from fetching images from subdomains or external sites.
* Fixed bug that prevented excluding more than one category using the Category filter.

## 2.3.0 ##

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

## 2.2.1 ##

* Quick update to fix error with All-time combined with views breaking the plugin.

## 2.2.0 ##

* Featured Image is generated for the user automatically if not present and if there's an image attached to the post.
* Range feature Today option changed. Replaced with Last 24 hours.
* Category exclusion query simplified. Thanks to almergabor for the suggestion!
* Fixed bug caused by selecting Avg. Views and All-Time that prevented WPP from getting any data from the BD. Thanks Janseo!
* Updated the `get_summary` function to strip out shortcodes from excerpt as well.
* Fixed bug in the truncate function affecting accented characters. Thanks r3df!
* Fixed bug keeping db tables from being created. Thanks northlake!
* Fixed bug on the shortcode which was showing pages even if turned off. Thanks danpkraus!

## 2.1.7 ##

* Added stylesheet detection. If wpp.css is on theme's folder, will use that instead the one bundled with the plugin.

## 2.1.6 ##

* Added DB character set and collate detection.
* Fixed excerpt translation issue when the qTrans plugin is present. Thanks r3df!.
* Fixed thumbnail dimensions issue.
* Fixed widget page link.
* Fixed widget title encoding bug.
* Fixed deprecated errors on `load_plugin_textdomain` and `add_submenu_page`.

## 2.1.5 ##

* Dropped TimThumb support in favor of Wordpress's Featured Image function.

## 2.1.4 ##

* Added italian localization. Thanks Gianni!
* Added charset detection.
* Fixed bug preventing HTML View / Visual View on Edit Post page from working.

## 2.1.1 ##
* Fixed bug preventing widget title from being saved.
* Fixed bug affecting blogs with Wordpress installed somewhere else than domain's root.
* Added htmlentities to post titles.
* Added default thumbnail image if none is found in the post.

## 2.1.0 ##

* Title special HTML entities bug fixed.
* Thumbnail feature improved! Wordpress Popular Posts now supports The Post Thumbnail feature. You can choose whether to select your own thumbnails, or let Wordpress Popular Posts create them for you!
* Shortcode bug fixed. Thanks Krokkodriljo!
* Category exclusion feature improved. Thanks raamdev!

## 2.0.3 ##

* Added a Statistics Dashboard to Admin panel so users can view what's popular directly from there.
* Users can now select a different date format.
* `get_mostpopular()` function deprecated. Replaced with `wpp_get_mostpopular()`.
* Cache maintenance bug fixed.
* Several UI enhancements were applied to this version.

## 2.0.2 ##

* "Keep text format and links" feature introduced. If selected, formatting tags and hyperlinks won't be removed from excerpt.
* Post title excerpt html entities bug fixed. It was causing the excerpt function to display more characters than the requested by user.
* Several shortcode bugs fixed (range, order_by, do_pattern, pattern_form were not working as expected).

## 2.0.1 ##

* Post title excerpt now includes html entities. Characters like `���` should display properly now.
* Post excerpt has been improved. Now it supports the following HTML tags: a, b, i, strong, em.
* Template tag `wpp_get_views()` added. Retrieves the views count of a single post.
* Template tag `get_mostpopular()` re-added. Parameter support included.
* Shortcode bug fixed (range was always "daily" no matter what option was being selected by the user).

## 2.0.0 ##

* Plugin rewritten to support Multi-Widget capabilities
* Cache table implemented
* Shortcode support added
* Category exclusion feature added
* Ajax update added - plugin is now compatible with caching plugins such as WP Super Cache
* Thumbnail feature improved - some bugs were fixed, too
* Maintenance page added

## 1.5.1 ##

* Widget bug fixed

## 1.5.0 ##

* Database improvements implemented
* WP-PostRatings support added
* Thumbnail feature added

## 1.4.6 ##

* Bug in `get_mostpopular` function affected comments on single.php
* "Show pageviews" option bug fixed
* Added "content formatting tags" functionality

## 1.4.5 ##

* Added new localizable strings
* Fixed Admin page coding bug that was affecting the styling of WPP

## 1.4.4 ##

* HTML Markup customizer added
* Removed some unnessesary files

## 1.4.3 ##

* Korean and Swedish are supported

## 1.4.2 ##

* Code snippet bug found

## 1.4.1 ##

* Found database bug affecting only new installations

## 1.4 ##

* Massive code enhancement
* CSS bugs fixed
* Features added: Time Range; author and date (stats tag); separate settings for Widget and Code Snippet

## 1.3.2 ##

* Permalink bug fixed

## 1.3.1 ##

* Admin panel styling bug fixed

## 1.3 ##

* Added an Admin page for a better management of the plugin
* New sorting options (sort posts by comment count, by pageviews, or by average daily views) added

## 1.2 ##

* Added extra functionalities to Wordpress Popular Post plugin core

## 1.1  ##

* Fixed comment count bug

## 1.0 ##

* Public release