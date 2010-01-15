=== Wordpress Popular Posts ===
Contributors: Ikki24
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=dadslayer%40gmail%2ecom&lc=GB&item_name=Wordpress%20Popular%20Posts%20Plugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG_global%2egif%3aNonHosted
Tags: popular, posts, widget, seo, wordpress
Requires at least: 2.8
Tested up to: 2.9.1
Stable tag: 2.0.0

With Wordpress Popular Posts, you can show your visitors what are the most popular entries on your blog.

== Description ==

Wordpress Popular Posts  is a sidebar widget that displays the most popular posts on your blog.

**IMPORTANT ANNOUNCEMENT:** from **version 2.0** and on, **Wordpress Popular Posts** requires at least **Wordpress 2.8** in order to function correctly. If you are not running Wordpress 2.8 or can't update your blog right now, please don't upgrade to/install version 2.x!

**WARNING:** if you are upgrading from any version prior to 1.4.6, please [update to 1.4.6](http://downloads.wordpress.org/plugin/wordpress-popular-posts.1.4.6.zip) before moving to 2.x!

**What's new**

* Wordpress Popular Posts is now **multi-widget** capable! Install multiple instances of Wordpress Popular Posts on your sidebars, each with its own unique settings!
* **Shortcode support!** - from version 2.0, you can showcase your most popular posts on pages, too!
* **[WP-Cache](http://wordpress.org/extend/plugins/wp-cache/)** and **[WP Super Cache](http://wordpress.org/extend/plugins/wp-super-cache/)** are now supported! From version 2.0 and on, Wordpress Popular Posts is fully compatible with **caching plugins**!
* **Category exclusion** - Want to exclude certain categories from the listing? Use the *Exclude Category* option!
* **[WP-PostRatings](http://wordpress.org/extend/plugins/wp-postratings/)** support added!  Show your visitors how your readers are rating your posts!
* **Database improvements** - Wordpress Popular Posts will now use a lot less space to cache your most popular posts!
* Use **your own layout**! Control how your most popular posts are shown on your templates.
* You can now include a **thumbnail** of your posts! (*see the [FAQ section](http://wordpress.org/extend/plugins/wordpress-popular-posts/faq/) for technical requirements*)

** Other features **

* Post excerpts feature is also available!
* *Wordpress Popular Posts is localized*! Languages included on this release: *English* (default) and *Spanish*. Wanna help with translations? See the [FAQ section](http://wordpress.org/extend/plugins/wordpress-popular-posts/faq/) for more!
* *Time Range* - list your most popular posts within a specific time range (eg. today's popular posts, this week's popular posts, etc.)!
* Wordpress popular posts is highly customizable. You can set its title (or leave it blank if you don't want to use any), decide how many entries to show, whether to display or not comments count and/or pageviews for each entry listed, etc.
* List your posts either by **comment count**, **views** or **average daily views**. Sorted by **comment count** by default.
* You can also list those pages of your blog (About, Services, Archives, etc.) that are getting a lot of attention from your readers. Enabled by default.

== Installation ==

1. Download the plugin and extract its contents.
2. Upload the `wordpress-popular-posts` folder to the `/wp-content/plugins/` directory.
3. Activate **Wordpress Popular Posts** plugin through the 'Plugins' menu in WordPress.
4. In your admin console, go to Appeareance > Widgets, drag the Wordpress Popular Posts widget to wherever you want it to be and click on Save.

That's it!

= Using Wordpress Popular Posts on Pages =

If you want to use Wordpress Popular Posts on your pages (a "Hall of Fame" page, for example) please use the shortcode `[wpp]`. Attributes are **optional**, however you can use them if needed. You can find a complete list of the attributes Wordpress Popular Posts currently supports at your *wp-admin > Settings > Wordpress Popular Posts* page.

**Usages:**

`[wpp]`

`[wpp attribute='value']`

== Frequently Asked Questions ==

* *I'm getting "Sorry. No data so far". What's up with that?*
Chances are that no one has seen your posts / pages yet. If you're logged in into wp-admin, your views are not counted either since you're the site's administrator.

* *I'm unable to activate the "Display post thumbnail" option. Why?*
You should check that: your host is running **PHP 4.3+**; the GD library is installed and enabled by your host; your "wordpress-popular-posts/scripts/cache" directory **exists** and is **writable**; there are images on your posts.

* *Does your plugin include any css stylesheets?*
Yes, *but* it's empty. It's there for you to style your most popular posts list as you like. You might need an expert for that if you don't know html/css, though.

* *Would you help me style my list, please?*
For a small donation, sure why not?

* *I want your plugin to have x or y functionality. Would you do it for me?*
I usually accept suggestions, yes. However, if it doesn't fit the nature of my plugin (to list popular posts) or requires something that might affect other users' experiences, chances are that I won't implement it. However, I could cook up a customized version of Wordpress Popular Posts just for you if you really, really need that special feature/capability ... but it won't be for free.

* *I want to translate your plugin into my language / help you update a PO file. What do I need to do?*
There's a PO file included with Wordpress Popular Posts. If your language is not already supported by my plugin, you can use a [gettext](http://www.gnu.org/software/gettext/) editor like [Poedit](http://www.poedit.net/) to translate all definitions into your language. If you want to, you can send me your resulting PO and MO files to yo at soyunduro dot com so I can include them on the next release of my plugin.

* *Help! I'm having some issues with your plugin! What should I do?*
Please don't, and read my words carefully, don't use my email address to contact me for support. It would be better for me and others using this plugin if you posted your questions on the [Wordpress Popular Posts Support forums](http://wordpress.org/tags/wordpress-popular-posts?forum_id=10). It'll surely be helpful for other users running into similar issues!

== Screenshots ==

1. Widgets Control Panel.
2. Wordpress Popular Posts Widget.
3. Wordpress Popular Posts Widget on Kubrik Theme's sidebar.

== Changelog ==

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

From version 2.x and on, Wordpress Popular Posts requires at least Wordpress 2.8 in order to function properly. If you can't move to Wordpress 2.8 or newer right now, please install version 1.5.1.