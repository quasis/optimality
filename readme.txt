=== Optimality ===
Contributors: optimality
Tags: minify, optimization, optimize, compress, speed, cache, performance, aggregate, minimize, minification, pagespeed, seo, comments, css, database, html, image, javascript, JS, lossless, meta tags, open graph, revisions, schema markup, share, sitemap, smo, social, spam, stylesheet
Requires at least: 4.0
Tested up to: 4.8
Stable tag: 4.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Optimizes website's content delivery, images, database, permalink structure, search engines' and social networks' markup.

== Description ==

The plugin optimizes your website for users, search engine robots and social networks. Your website will load much faster, robots will find the markup they are looking for and social networks will show attractive snippets of your content.

Plugin's functionality can be roughly divided into 5 major categories: Content Delivery Optimization (CDO), Database Optimization (DBO), Website Structure Optimization (WSO), Search Engine Optimization (SEO) and Social Media Optimization (SMO).

= Content Delivery Optimization =
* Removes unnecessary meta tags from the head section of HTML.
* Reduces DNS lookup time by pre-resolving all external domains.
* Removes comments, unnecessary whitespace and empty nodes from HTML.
* Combines stylesheets, flattens imports, removes comments and caches.
* Serves popular CSS libraries from content delivery networks.
* Combines scripts, defers loading, removes comments and caches.
* Serves popular JS libraries from content delivery networks.
* Strips metadata and compresses thumbnail images (requires ImageMagick).
* Canonicalizes image URLs, adds 'srcset' attributes and serves from CDN.

= Database Optimization =
* Cleans expired transients from the database.
* Cleans automatic post drafts from the database.
* Cleans post revision history from the database.
* Cleans orphaned post meta from the database.
* Cleans orphaned relationships from the database.
* Cleans trash/spam comments from the database.
* Cleans pingbacks/trackbacks from the database.
* Cleans orphaned meta data from the database.

= Website Structure Optimization =
* Removes the mandatory base from the permalinks of categories.
* Redirects author archive pages to the home page of the website.
* Redirects image attachment pages to the URL of the parent page.
* Redirects ?replytocom=id to #comment-id in comment replies.

= Search Engines Optimization =
* Provides title and description templates for search engines.
* Generates proper schema.org markup for every page type.
* Creates an XML sitemap of pages, posts, categories, tags, images, etc.

= Social Media Optimization =
* Provides title and description templates for social networks.
* Generates proper Open Graph markup for every page type.
* Generates proper Twitter markup for every page type.

= General Features =
* Small (132kB) and fast (no noticeable overhead).
* Not invasive - doesnt pollute your admin area.
* Leaves no traces if you choose to try and then deinstall.

= Important =
* The plugin hasn't been tested on _multisites_ yet. So if you own a multisite some features might work not as advertised.
* The plugin uses ImageMagick to compress thumbnails. The compression is fast and as good as that of Google's PageSpeed. Therefore, it is highly advisable to install ImageMagick PHP extension together with the plugin.

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
