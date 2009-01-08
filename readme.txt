=== Wordpress Popular Posts ===
Contributors: Ikki24
Donate link: http://rauru.com/
Tags: popular, posts, widget, seo, wordpress
Requires at least: 2.0.2
Tested up to: 2.7
Stable tag: 1.3

With Wordpress Popular Posts, you can show your visitors what are the most popular entries on your blog.

== Description ==

Wordpress Popular Posts  is a sidebar widget that displays the most popular posts on your blog.

**Features:**

* Wordpress popular posts is highly customizable. You can set its title (or leave it blank if you don't want to use any), how many entries to show, whether to display or not comments count and/or pageviews for each entry listed, and to show (or not) an excerpt of each post's title.
* **[NEW FEATURE]**: List your posts either by **comment count**, **pageviews** or **average daily views**. Sorted by **comment count** by default.
* **[NEW FEATURE]**: You can also list those pages of your blog (About, Services, Archives, etc.) that are getting a lot of attention from your readers. Enabled by default.
* **[NEW FEATURE]**: Wordpress Popular Posts now counts with an **Admin page** where you can manage all its settings. No more manual configuration!

[Version History](http://rauru.com/wordpress-popular-posts/)

== Installation ==

1. Download the plugin and extract its contents.
2. Upload the `wordpress-popular-posts` folder to the `/wp-content/plugins/` directory.
3. Activate **Wordpress Popular Posts** plugin through the 'Plugins' menu in WordPress.
4. In your admin console, go to Design > Widgets (or Presentation > Widgets for Wordpress 2.3 and lower), and drag the Wordpress Popular Posts widget to wherever you want it to be, and click Save Changes.
5. *optional* In your admin console, go to Design > Widgets (or Presentation > Widgets for Wordpress 2.3 and lower), click Edit on **Popular Posts** and customize it to your likings. Once you're done with it, click on Change and then on Save Changes. **[NEW FEATURE]** You can now also change its settings using WPP Admin page.

= Placing Wordpress Popular Posts in your templates =

If you want to use **Wordpress Popular Posts** somewhere else in your templates, simply place `<?php get_mostpopular(); ?>` where you want your listing to be displayed. Easy, huh?.

**USAGE:**

`<?php if (function_exists('get_mostpopular')) get_mostpopular(); ?>`

== Frequently Asked Questions ==

* *I've got posts with better stats (comments, pageviews, etc.) than those listed by your plugin. What's wrong?*

Every time a post is viewed by someone (except you), it is registered by *Wordpress Popular Posts* and its pageviews count is updated automatically. Chances are that it has not been viewed by anyone since you installed *Wordpress Popular Posts* on your blog - so don't worry, *Wordpress Popular Posts* will take note of it once someone checks any of your posts.

* *How can I style the small tag where comments count, pageviews, etc. (the "stats tag") are displayed?*

I have included a small stylesheet file called wpp.css that you can use to style the **stats tag** to your liking.

== Screenshots ==

No screenshots available at the moment.