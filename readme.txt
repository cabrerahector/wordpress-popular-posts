=== Wordpress Popular Posts ===
Contributors: Ikki24
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=dadslayer%40gmail%2ecom&lc=GB&item_name=Wordpress%20Popular%20Posts%20Plugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG_global%2egif%3aNonHosted
Tags: popular, posts, widget, seo, wordpress
Requires at least: 2.3
Tested up to: 2.8.0
Stable tag: 1.5.1

With Wordpress Popular Posts, you can show your visitors what are the most popular entries on your blog with your own formatting.

== Description ==

Wordpress Popular Posts  is a sidebar widget that displays the most popular posts on your blog with your own formatting.

**Features:**

**WARNING: if you are upgrading from a version prior to 1.4.6, please [update to 1.4.6](http://downloads.wordpress.org/plugin/wordpress-popular-posts.1.4.6.zip) before moving to 1.5! **

* **[NEW FEATURE]**: Use **your own formatting**! Control how your most popular posts are going to be displayed on your templates (*optional*).
* **[NEW FEATURE]**: [WP-PostRatings](http://wordpress.org/extend/plugins/wp-postratings/) support added!
* **[NEW FEATURE]**: Database improvements - Wordpress Popular Posts will now use a lot less space to cache your most popular posts!
* **[NEW FEATURE]**: You can now include a **thumbnail** of your posts! (*optional*)
* Post excerpts feature is also available! (*optional*)
* *Wordpress Popular Posts is localized*! Currently supported languages: *English* (default), *Russian*, *Spanish*, *Swedish*, *Korean*, *Danish*, *Hebrew*, *French* and *German*.
* *Time Range* - list your most popular posts within a specific time range (eg. today's popular posts, this week's popular posts, etc.)!
* Wordpress popular posts is highly customizable. You can set its title (or leave it blank if you don't want to use any), how many entries to show, whether to display or not comments count and/or pageviews for each entry listed, and to show (or not) an excerpt of each post's title.
* List your posts either by **comment count**, **pageviews** or **average daily views**. Sorted by **comment count** by default.
* You can also list those pages of your blog (About, Services, Archives, etc.) that are getting a lot of attention from your readers. Enabled by default.
* Wordpress Popular Posts counts with an **Admin page** where you can manage all its settings. No manual configuration needed!

**Localization:**

* **Russian** | by [Aleksey Timkov](http://icellulars.net/) (*7% translated*) [(help localize!)](http://rauru.com/wordpress-popular-posts#localization)
* **Spanish** | by [H&eacute;ctor Cabrera](http://rauru.com/) (*100% translated!*)
* **Swedish** | by [.SE (The Internet Infrastructure Foundation)](http://iis.se/) (*49% translated*) [(help localize!)](http://rauru.com/wordpress-popular-posts#localization)
* **Korean** | by [Jong-In](http://incommunity.codex.kr/wordpress) (*42% translated*) [(help localize!)](http://rauru.com/wordpress-popular-posts#localization)
* **Danish** | by [Ulrik H&oslash;yer Kold](http://ulrikkold.dk) (*72% translated*) [(help localize!)](http://rauru.com/wordpress-popular-posts#localization)
* **Hebrew** | by Eyal Gursoy (*72% translated*) [(help localize!)](http://rauru.com/wordpress-popular-posts#localization)
* **French** | by [Martin Ransom](http://www.au-trescols.net)
* **German** | by [Jan Wildefeld](http://janwildefeld.de) (*71% translated*) [(help localize!)](http://rauru.com/wordpress-popular-posts#localization)

[Version History](http://rauru.com/wordpress-popular-posts#releases) | [Localization](http://rauru.com/wordpress-popular-posts#localization)

== Installation ==

1. Download the plugin and extract its contents.
2. Upload the `wordpress-popular-posts` folder to the `/wp-content/plugins/` directory.
3. Activate **Wordpress Popular Posts** plugin through the 'Plugins' menu in WordPress.
4. In your admin console, go to Design > Widgets (or Presentation > Widgets for Wordpress 2.3 and lower), and drag the Wordpress Popular Posts widget to wherever you want it to be, and click Save Changes.
5. *optional* In your admin console, go to Appearance > Widgets (or Presentation > Widgets for Wordpress 2.3 and lower), click Edit on **Popular Posts** and customize it to your likings. Once you're done with it, click on Change and then on Save Changes. **[NEW FEATURE]** You can now also change its settings using WPP Admin page.

= Placing Wordpress Popular Posts in your templates =

If you want to use **Wordpress Popular Posts** somewhere else in your templates, simply place `<?php get_mostpopular(); ?>` where you want your listing to be displayed. Easy, huh?.

**USAGE:**

`<?php if (function_exists('get_mostpopular')) get_mostpopular(); ?>`

== Frequently Asked Questions ==

* *My Popular Posts are being displayed with no formatting*

If you activated the option "Use custom HTML Markup", make sure you have filled in all fields and try again.

* *Thumbnails are not being displayed.*

Please make sure that: 1) your current host is running PHP 4.3 or higher, 2) your host has the GD library installed, 3) the cache subfolder (wordpress-popular-posts/scripts/cache/) is *writable* (chmodd it to 777 if you're unsure about this), and 4) your popular posts has images to display.

* *I'm getting a "Sorry. No data so far." message. What's wrong?*

Patience, my friend. One of two things is happening here:

1) (and more likely) Wordpress Popular Posts has not registered any views yet. Each time someone views your posts WPP will notice it (except when it's you). If you're getting this message it's because no one has checked your posts yet. Give it some time.

2) If you're using the code snippet, remember that it must be placed either in sidebar.php (usually it should be put in there), or in footer.php, or in header.php. Generally speaking, putting it somewhere else will prevent Wordpress Popular Posts from working as expected.

* *I've got posts with better stats (comments, pageviews, etc.) than those listed by your plugin. What's happening?*

Every time a post is viewed by someone (except you), it is registered by *Wordpress Popular Posts* and its pageviews count is updated automatically. Chances are that it has not been viewed by anyone since you installed *Wordpress Popular Posts* on your blog - so don't worry, *Wordpress Popular Posts* will take note of it once someone checks any of your posts.

* *How can I style the small tag where comments count, pageviews, etc. (the "stats tag") are displayed?*

I have included a small stylesheet file called wpp.css that you can use to style the **stats tag** to your liking.

* *I would like to help translate Wordpress Popular Posts into my language. What do I need to do?*

[Here](http://rauru.com/wordpress-popular-posts#localization) you will find all the necessary information about it. Many thanks in advance for your help!

== Screenshots ==

No screenshots available at the moment.