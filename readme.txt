=== Optimality ===
Contributors: optimality
Tags: comments, compress, css, database, html, image, javascript, JS, lossless, meta tags, minify, open graph, optimization, optimize, revisions, schema markup, seo, share, sitemap, smo, social, spam, stylesheet
Requires at least: 4.0
Tested up to: 4.8
Stable tag: 4.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Optimizes website\'s content delivery, images, database, permalink structure, search engines and social media markup.

== Description ==

Optimality will optimize your website for users, search engines and social networks. The plugin was created for personal projects and is already working on high traffic websites, it does more with less code and less overhead. Here is what it has to offer:

= Content Delivery Optimization =
* Remove unnecessary meta tags from the head section of HTML.
* Reduce DNS lookup time by pre-resolving all external domains.
* Remove comments, unnecessary whitespace and empty nodes from HTML.
* Combine stylesheets, flatten imports, remove comments and cache.
* Serve popular CSS libraries from content delivery networks.
* Combine scripts, defer loading, remove comments and cache.
* Serve popular JS libraries from content delivery networks.
* Strip metadata and compress thumbnail images (requires ImageMagick).
* Canonicalize image URLs, add 'srcset' attributes and serve from CDN.

= Database Optimization =
* Delete expired transients from the database.
* Delete automatic post drafts from the database.
* Delete post revision history from the database.
* Delete orphaned post meta from the database.
* Delete orphaned relationships from the database.
* Delete trash/spam comments from the database.
* Delete pingbacks/trackbacks from the database.
* Delete orphaned meta data from the database.

= Website Structure Optimization =
* Remove the mandatory base from the permalinks of categories.
* Redirect author archive pages to the home page of the website.
* Redirect image attachment pages to the URL of the parent page.
* Redirect ?replytocom=id to #comment-id in comment replies.

= Search Engines Optimization =
* Configure title and description templates for search engines.
* Generate proper schema.org markup for every page type.
* Create an XML sitemap of pages, posts, categories, tags, images, etc.

= Social Media Optimization =
* Configure title and description templates for social networks.
* Generate proper Open Graph markup for every page type.
* Generate proper Twitter markup for every page type.

= General Features =
* Small (132kB) and fast (no noticeable overhead).
* Not invasive - doesnt pollute your admin area.
* Leaves no traces if you choose to try and then deinstall.

= Important =
It hasn't been tested on _multisites_ yet. So if you own a multisite some features might work not as advertised.

== Screenshots ==
1. Content delivery optimization options.
2. Database optimization options.
3. Website structure optimization options.
4. Search engines optimization options.
5. Social media optimization options.

== Installation ==
1. Upload plugin folder to the '/wp-content/plugins/' directory,
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Select the desired options on the Settings page of the plugin.

== Changelog ==

= 0.2.0 =
* Feature: Extended the mapping of popular JS/CSS libraries
* Feature: +10% reduction in filesize of PNG thumbnails

= 0.1.0 =
* Initial release
