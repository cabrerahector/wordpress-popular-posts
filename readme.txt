=== Wordpress Popular Posts ===
Contributors: Ikk24
Donate link: http://rauru.com/
Tags: popular, posts, widget, seo
Requires at least: 2.0.2
Tested up to: 2.6.2
Stable tag: 1.0

Adds a widget to show the most popular posts in your Wordpress blog.

== Description ==

Wordpress Popular Posts is a sidebar widget to show the most popular posts in your blog. Many options has been included in it so you can customize it to your likings.

Features:

* Widget's title is by default **Popular Posts**. However, you can change it to whatever you want.

* Wordpress Popular posts will display up to 10 entries by default. This setting can be changed as well.

* It can also display comment count for each entry. Enabled by default.

* If you want to, you can also limit the amount of characters displayed  for each entry listed. If activated, you can also limit the excerpt to display a certain number of characters (25 by default). Disabled by default.

== Installation ==

1. Download the plugin and extract its contents.
2. Upload the `wordpress-popular-posts` folder to the `/wp-content/plugins/` directory.
3. Activate **Wordpress Popular Posts** plugin through the 'Plugins' menu in WordPress.
4. In your admin console, go to Design > Widgets (or Presentation > Widgets for Wordpress 2.3 and lower), and drag the Wordpress Popular Posts widget to wherever you want it to be, and click Save Changes.
5. *optional* In your admin console, go to Design > Widgets (or Presentation > Widgets for Wordpress 2.3 and lower), click Edit on **Popular Posts** and customize it to your likings. Once you're done with it, click on Change and then on Save Changes.

= Placing Wordpress Popular Posts in your templates =


If you want to use Wordpress Popular Posts in your templates and not as a widget, simply place `<?php get_mostpopular('Title', number of posts); ?>`, where *title* is how you want to name your popular posts list, and *number of posts* is the amount of entries you want to display on your list (eg. `<?php get_mostpopular('Popular Posts', 10); ?>`).

== Frequently Asked Questions ==

No FAQs so far.

== Screenshots ==

No screenshots available at the moment.