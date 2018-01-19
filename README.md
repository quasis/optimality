# Optimality

This is the official repository of Optimality, a WordPress plugin that optimizes websites for users, search engine robots and social networks.

[![Optimality Plugin](https://ps.w.org/optimality/assets/screenshot-1.png)](https://wordpress.org/plugins/optimality/)


## Features

### Content Delivery Optimization 
- Removes unnecessary meta tags from the head section of HTML.
- Reduces DNS lookup time by pre-resolving all external domains.
- Removes comments, unnecessary whitespace and empty nodes from HTML.
- Caches dynamic HTML content and serves it as static HTML files.
- Combines styles, flattens imports, removes comments and caches.
- Serves popular CSS libraries from content delivery networks.
- Combines scripts, defers loading, removes comments and caches.
- Serves popular JS libraries from content delivery networks.
- Strips metadata and compresses thumbnail images (requires ImageMagick).
- Canonicalizes image URLs, adds 'srcset' attributes and serves from CDN.

### Database Optimization
- Cleans expired transients from the database.
- Cleans automatic post drafts from the database.
- Cleans post revision history from the database.
- Cleans orphaned post meta from the database.
- Cleans orphaned relationships from the database.
- Cleans trash/spam comments from the database.
- Cleans pingbacks/trackbacks from the database.
- Cleans orphaned meta data from the database.

### Website Structure Optimization
- Removes the mandatory base from the permalinks of categories.
- Redirects author archive pages to the home page of the website.
- Redirects image attachment pages to the URL of the parent page.
- Redirects ?replytocom=id to #comment-id in comment replies.
- Redirects paginated comment pages to the parent page.
- Redirects 404 error pages one level up in the website hierarchy.

### Search Engines Optimization
- Provides title and description templates for search engines.
- Generates proper schema.org markup for every page type.
- Creates an XML sitemap of the website (including images).
- Pings Google and Bing once new page/post is published.

### Social Media Optimization
- Provides title and description templates for social networks.
- Generates proper Open Graph markup for every page type.
- Generates proper Twitter markup for every page type.

### General Features
- Small (32kB zipped) and fast (no noticeable overhead).
- Not invasive - doesnt pollute your admin area.
- Leaves no traces if you choose to try and then deinstall.

## Important

- The plugin hasn't been tested on _multisites_ yet. So if you own a multisite some features might work not as advertised.

- The plugin uses ImageMagick to compress thumbnails. The compression is fast and as good as that of Google's PageSpeed. Therefore, it is highly advisable to install ImageMagick PHP extension together with the plugin.

## Installation

1. Upload plugin folder to the '/wp-content/plugins/' directory,
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Select the desired options on the Settings page of the plugin.

## Contributing

Pull requests are most welcome. For major changes, please open an issue first to discuss what you would like to change.

## License

The Optimality plugin is licensed under the terms of the GPLv2 license and is available for free.
