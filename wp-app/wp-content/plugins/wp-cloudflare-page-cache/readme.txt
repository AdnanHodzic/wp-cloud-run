=== Super Page Cache ===
Contributors: themeisle, salvatorefresta, isaumya
Tags: cloudflare, caching ,performance, page caching, pagespeed
Requires at least: 5.3
Requires PHP: 7.4
Tested up to: 6.9
Stable tag: 5.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Boost PageSpeed, SEO, and Core Web Vitals with full page caching, JS/CSS optimization, media optimization, and Cloudflare CDN.

== Description ==

Super Page Cache is a powerful full page cache plugin for WordPress, making your WordPress site blazing fast by caching not only static files (CSS, JS, images) but also HTML webpages, both at server disk-level and to the global Cloudflare CDN. With our completely redesigned dashboard and advanced optimization features, you get enterprise-level performance with zero configuration required.

This plugin works right out of the box. You can use it with or without Cloudflare. It offers powerful disk caching by saving files locally on your server, even without using a CDN. If you want to take advantage of the Cloudflare CDN, just enter your Cloudflare API Key or API Token, and the plugin will handle the rest. For advanced users, the intuitive dashboard features detailed settings and built-in documentation. Most users don’t need to make any changes because it runs smoothly with the default settings.

https://youtu.be/SYhoaL_fUY0?si=94atnvwHRF5r_U3U

### **⚡ Quick Links**

- [Documentation](https://docs.themeisle.com/collection/2199-super-page-cache) → Complete setup and configuration guide
- [Support Forum](https://wordpress.org/support/plugin/wp-cloudflare-page-cache/) → Community help and expert support
- [YouTube Videos](https://youtube.com/playlist?list=PLmRasCVwuvpSJuwaV7kDiuXhxl08zbZr5&si=Gem626AyPpNenDF3) → Step-by-step visual guides for every feature
- [Go Pro](https://themeisle.com/plugins/super-page-cache-pro/?utm_source=wordpressorg&utm_medium=readme&utm_campaign=quicklinks) → Advanced features and priority support

### **🚀 How Does The Plugin Work?**

Super Page Cache provides blazing-fast performance through dual caching layers: intelligent disk caching that works completely standalone, plus optional Cloudflare CDN integration for global edge caching.

Together, these layers dramatically improve loading speed and real-world performance metrics, including Google’s Core Web Vitals.

#### Disk Caching (Works Without Cloudflare)
The plugin provides powerful server-level disk caching by saving cached HTML pages and static files directly to your server. This works completely independently and delivers significant speed improvements even without any CDN integration. Your pages load instantly from locally cached files, dramatically reducing server processing time and ensuring a true full page cache that serves complete HTML, not just static assets.

#### Cloudflare WordPress Caching (Optional Integration)
When you connect your Cloudflare account, the plugin leverages **Cloudflare's modern Cache Rules** (replacing outdated page rules and worker methods) to intelligently cache your content across Cloudflare's global network. This takes full advantage of the **FREE Cloudflare Plan** - no paid account required.

#### Why This Matters for Your Website Speed
Unlike most caching plugins that only provide disk caching (serving cached webpages from your web server), Super Page Cache caches your webpages and static files in the **Cloudflare CDN, one of the world's [fastest CDN networks](https://www.cdnperf.com/cdn-compare?type=performance&location=world&cdn=akamai-cdn,aws-cloudfront-cdn,azure-cdn,bunnycdn,cachefly,cdn-net,cdn77,cdnetworks,cloudflare-cdn,dorabase,fastly-cdn,g-core-labs-cdn,google-cloud-cdn,keycdn,nusec-cdn,ovh-cdn,stackpath-cdn,verizon-edgecast-cdn)**.

With more than **[200 CDN edge locations](https://www.cloudflare.com/network/)** provided by Cloudflare, your webpage will be served from the nearest CDN location to the visitor, rather than sending requests to your web server, which might be on the other side of the world. This significantly reduces website loading speed by leveraging the Cloudflare CDN for both static files and HTML webpages.

⚡️ Websites powered by Super Page Cache consistently achieve higher performance grades on testing tools like Pingdom and GTmetrix, thanks to faster response times and edge delivery.

#### ✅ Works Out of the Box (With or Without Cloudflare)
You don't need to configure anything. Just activate the plugin, and it works instantly with intelligent disk caching and default settings. Our completely redesigned dashboard with integrated documentation makes customization easier than ever when you need it.

#### ☁️ Designed for Cloudflare (All Plans Supported)
Whether you use the **Free, Pro,** or **Enterprise** Cloudflare plan, the plugin integrates seamlessly using modern Cache Rules and lets you:

- Automatically purge Cloudflare cache on content updates
- Leverage intelligent cache rules optimized for dynamic WordPress sites
- Purge Cloudflare cache via cron jobs or manually from the WP admin toolbar
- Control caching behavior to avoid conflicts with other plugins
- Track cache performance with integrated metrics

### **🔥 Core Features (Free Version)**

#### 🚀 Complete Caching Solution

- **Server-Level Disk Caching:** Lightning-fast page caching directly on your server that works completely standalone, with intelligent fallback when Cloudflare cache is missed or expired.
- **Global CDN Integration:** Optional Cloudflare CDN integration with 200+ edge locations worldwide using modern Cache Rules for ultra-low latency delivery.
- **Complete Cache Controls:** Exclude specific cookies, query parameters, URIs, and content types like AMP, feeds, or REST API from being cached.
- **Flexible Cache Lifespan:** Define custom cache expiration values or set pages never to expire.
- **Response Header Management:** Preserve critical response headers in both Cloudflare and fallback disk caches, unlike other plugins that strip them.
- **Smart Cache Buster:** Ensure logged-in users and editors never see cached content using intelligent cache-busting techniques.
- **Cache Performance Metrics:** Basic metrics system to track cache performance and give you insights into your site's caching effectiveness.

#### ⚡ Performance Optimization

- **Google Fonts Optimization:** Super Page Cache helps you optimize Google Fonts by combining requests and serving them locally. This reduces external dependencies and improves both privacy and Web Vitals scores.
- **Lazy Loading System:** Built-in lazy loading for images, videos, iframes, and background images to improve load performance.
- **Flexible Lazy Load Exclusions:** Exclude specific media using keywords, URL patterns, or CSS class detection.
- **Advanced Assets Manager:** Enable or disable specific CSS and JavaScript files based on page context with an intuitive frontend modal interface, helping you eliminate unused scripts and optimize page performance.
- **Browser Caching Rules:** Automatically configure `.htaccess` rules for long-lived caching of static assets like images, scripts, and stylesheets.

#### 🛠️ Advanced Cache Management

Take full control of your cache with smart purging, intelligent preloading, and reliable background processing. Every update is handled seamlessly without slowing down your site.

- **Smart Auto-Purging:** Automatically clear relevant cache entries when posts, pages, or custom post types are updated, including related content.
- **Granular Purge Options:** Choose to purge only HTML pages or the entire cache, including assets.
- **Intelligent Preloader:** Preload content from sitemaps, menus (top, main, footer), or the last 20 updated posts. Supports manual or scheduled (`CRON`) triggers.
- **Enhanced Background Processing:** Improved background processing system using `Action Scheduler` for more reliable background task handling.
- **Queue-Based Purging:** Prevent server overload by staggering purge operations for high-frequency events.
- **Force Cache Bypassing for Backend:** Add additional cache rules to prevent caching of the admin dashboard in rare edge cases.
- **Per-Page Cache Control:** Easily exclude individual posts or pages from caching using a built-in metabox in the editor.
- **Toolbar Cache Purging:** Instantly purge cache from the WordPress admin toolbar for convenience.

#### 🔧 Developer & Power User Features

Fine-tune caching behavior with advanced filters, role-based permissions, and API integrations. Includes export/import tools and built-in documentation for maximum flexibility.

- **Redesigned Dashboard Experience:** Complete dashboard redesign with improved user experience, intuitive navigation, and reorganized settings structure that groups related options together.
- **Integrated Documentation:** Helpful tips and guidance directly within the dashboard, providing context without leaving your WordPress admin.
- **Multiple Cache Backends:** Choose between WordPress’s `advanced-cache.php` or a cURL-based approach for compatibility with other performance plugins.
- **Enhanced Defer JS Control:** New `spc_defer_script` filter hook allowing developers to exclude specific scripts from being deferred, providing more granular control over JavaScript optimization.
- **Role-Based Permissions:** Control which user roles can manually purge the cache.
- **API Integration with Cloudflare:** Works with Cloudflare Free, Pro, Business, or Enterprise using either API Key or Token authentication with modern Cache Rules.
- **Export/Import Plugin Settings:** Save or transfer plugin settings as a downloadable JSON file.
- **Preserve Settings on Deactivation:** Optionally retain all plugin settings when deactivated for smoother migrations or staging workflows.

#### 🗄️ Database Optimization

Super Page Cache includes built-in database optimization tools to keep your WordPress site lean and fast. Regularly cleaning up revisions, spam, and transients ensures better overall performance.

- **Automated Database Cleanup:** Remove post revisions, auto-drafts, trashed items, spam comments, and expired transients with both manual and scheduled cleanup options.
- **Scheduled Maintenance:** Run cleanups automatically on a daily, weekly, or monthly basis.
- **Table Optimization:** Run `SQL OPTIMIZE TABLE` commands to defragment and reclaim space.
- **Selective Cleanup Options:** Select precisely which data types to clean based on your site's specific needs.

With one click, you can clean your database and remove unnecessary data that slows down WordPress over time.

#### 🌐 Universal Compatibility

Works with all major WordPress themes, hosting providers, and plugins like WooCommerce and Easy Digital Downloads, plus advanced server caches such as Varnish and OPcache.

- **Premium Host Integration:** Seamless compatibility with top-tier hosts like Kinsta, WP Engine, and SpinupWP.
- **eCommerce Ready:** Fully integrated with WooCommerce and Easy Digital Downloads.
- **Varnish & OPcache Support:** Automatically purge server-level caches when Cloudflare cache is cleared.
- **Prefetch Optimization:** Supports automatic prefetching of internal links in the viewport or on hover using [Instant.page](https://instant.page) for lightning-fast navigation.

### **💎 Super Page Cache PRO with Advanced Performance Features**

Take your website speed to the next level with powerful PRO features designed for maximum performance optimization:

- **Ignore Marketing Parameters:** Significantly increases cache hit rate by ignoring common marketing and tracking parameters in URLs, treating them as the same page for caching purposes. Perfect for sites with `UTM` parameters, affiliate links, and tracking codes.
- **Defer JavaScript:** Eliminate render-blocking JavaScript on your site and improve load times by deferring JavaScript execution until after the page has loaded. [Learn more](https://docs.themeisle.com/article/2058-defer-js).
- **Delay JavaScript:** Make your website faster by loading JavaScript files only when the user interacts with the page (e.g., scrolling, clicking, or touching). This dramatically improves initial page load speed. [Learn more](https://docs.themeisle.com/article/2057-delay-js).
- **Advanced Lazy Loading System:** Choose between native lazy loading or a custom system with enhanced viewport detection and precise above-the-fold image detection. Automatically loads critical images immediately while lazy loading below-the-fold content, optimized for both mobile and desktop viewports without impacting user experience.
- **Advanced Exclusion Controls:** Fine-tune your caching strategy by excluding specific JavaScript files and pages that shouldn't be cached, ensuring critical functionality works flawlessly while maximizing cache efficiency.
- **Priority Support:** Get priority email support and faster response times for any technical questions or issues.

**Ready to unlock maximum performance? [Upgrade to Pro](https://themeisle.com/plugins/super-page-cache-pro/?utm_source=wordpressorg&utm_medium=readme&utm_campaign=spc-pro-featrues)**

### **🎯 Important Usage Guidelines**

#### Caching Plugin Compatibility
If you're using Super Page Cache with other page caching plugins (WP Rocket, LiteSpeed Cache, W3 Total Cache, etc.), please disable the page caching feature on those plugins. Super Page Cache will handle all page caching to avoid conflicts.

You can safely use the following plugins alongside Super Page Cache for complete optimization: [Optimole](https://wordpress.org/plugins/optimole-wp/), Autoptimize, Perfmatters, ShortPixel, or WP Rocket for static asset optimization.

### **⭐ Here's What Our Users Are Saying**

> ★★★★★  
> **Super Page Cache has been a game-changer**  
> "Super Page Cache has been a game-changer for our website's performance! We've seen a noticeable improvement in loading times, which has directly boosted our user engagement and SEO rankings. It's incredibly effective and surprisingly easy to set up. Highly recommend for anyone looking to seriously speed up their WordPress site!"  
> – @brandnexusstudios

---

> ★★★★★  
> **Insane Speed Boost – For Free!!! Thank You!!!**  
> "With this plugin, I managed to drop response times on my WordPress website from 400ms+ to consistently below 200ms. It has almost the same metrics as my static website on S3!!! Amazing—and this is using the free version!!! So you get all of this for free!!!!!"  
> – @jurijs0

---

> ★★★★★  
> **A powerful and beautiful plugin**  
> "I like it a lot, I really appreciate your great work. Fortunately, I've been using it and it's the best. I'll continue using it for a long time. Thank you."  
> – @bazkaesnwyllt

---

> ★★★★★  
> **Does exactly what it promises**  
> "One of the great things about using WordPress is the availability of plugins to handle a wide variety of jobs. The best ones–like this one–do that simply and effectively. Couldn't be happier with the increased speed of loading cached pages. Highly recommended."  
> – @skipvia

### **🏆 Why Choose Super Page Cache?**

#### Works Standalone or with Global CDN

Unlike plugins that require external services, Super Page Cache delivers powerful disk caching that works completely standalone. You can also optionally integrate with Cloudflare CDN, leveraging 200+ edge locations worldwide for even faster performance.

#### Cost-Effective Solution 

Get enterprise-level caching performance using Cloudflare's free plan or our advanced local disk caching. No need for expensive premium CDN subscriptions or enterprise tools.

#### Zero Configuration Required

The plugin works perfectly out of the box with intelligent defaults. Our redesigned dashboard includes built-in documentation to help you customize settings whenever needed.

#### Continuous Development

We’re actively building and improving Super Page Cache. Your support and feedback keep us going. If you enjoy the plugin, we’d love to hear your thoughts — [leave a review](https://wordpress.org/support/plugin/wp-cloudflare-page-cache/reviews/#new-post).

### **📚 Resources & Community**

#### RESOURCES
- [Knowledge Base](https://docs.themeisle.com/)
- [Support Forum](https://wordpress.org/support/plugin/wp-cloudflare-page-cache/)
- [Feature Request](mailto:friends@themeisle.com)

#### JOIN OUR COMMUNITY
- [Follow us on Twitter](https://twitter.com/themeisle)
- [Join us on Facebook](https://www.facebook.com/themeisle/)
- [Subscribe to our YouTube channel](https://www.youtube.com/@Themeisle)

### **🚀 Ready to Speed Up Your WordPress Site?**

Stop settling for slow loading times. Join thousands of WordPress users who’ve already transformed their sites with Super Page Cache’s powerful caching system and advanced optimization features.

**Get started today** and experience the difference intelligent caching can make for your website’s performance.

---

#### Super Page Cache is backed by Themeisle
  
[Themeisle](https://themeisle.com/) is a trusted provider of premium WordPress themes and plugins, powering over 1 million WordPress sites worldwide, with 450+ five-star ratings on [Trustpilot](https://www.trustpilot.com/review/themeisle.com).

== Installation ==

FROM YOUR WORDPRESS DASHBOARD

1. Visit "Plugins" > Add New
2. Search for Super Page Cache
3. Activate Super Page Cache  from your Plugins page.

FROM WORDPRESS.ORG

1. Download Super Page Cache
2. Upload the "wp-cloudflare-super-page-cache" directory to your "/wp-content/plugins/" directory, using ftp, sftp, scp etc.
3. Activate Super Page Cache from your Plugins page.

== Frequently Asked Questions ==

= How this plugin is different from Cloudflare APO? =

Cloudflare have launched Automatic Platform Optimization (APO) feature in 2020 which works with the default Cloudflare WordPress plugin. APO works by taking advantage of Cloudflare Workers & KV Storage. As APO uses KV to store the cached content, one of the feature it has is that when something is cached via APO, it instantly get pushed to all the Cloudflare edges around the world, even though no requests has came from that region.

Our plugin is created to ensure even the Cloudflare FREE account users can take full advantage of Cloudflare CDN caching, leveraging **Cloudflare's Cache Rules**.

That being said, CF APO costs 5$/month for free account holders and it is free for paid account users. But still it lacks many features, functionality and third-party plugin integration compared to our plugin. The feature and integration provided by our plugin is simply unmatched by APO. Currently we can't push the cache everywhere like APO but we are planning to do something similar in near future. If you are still curious, [read this thread](https://wordpress.org/support/topic/automatic-platform-optimization-for-wordpress/#post-13486593) where you will find a detailed comparison with Cloudflare APO vs this plugin.

= How do I know if everything is working properly? =

To verify that everything is working properly, I invite you to check the HTTP response headers of the displayed page in Incognito mode (browse in private). Super Page Cache returns two headers:

**x-wp-cf-super-cache**

If its value is **cache**, Super Page Cache is active on the displayed page and the page cache is enabled. If **no-cache**, Super Page Cache is active but the page cache is disabled for the displayed page.

**x-wp-cf-super-cache-active**

This header is present only if the previous header has the value **cache**.

If its value is **1**, the displayed page should have been placed in the Cloudflare cache.

To find out if the page is returned from the cache, Cloudflare sets its header called **cf-cache-status**.

If its value is **HIT**, the page has been returned from cache.

If **MISS**, the page was not found in cache. Refresh the page.

If **BYPASS**, the page was excluded from Super Page Cache for Cloudflare.

If **EXPIRED**, the page was cached but the cache has expired.

= Do you allow to bypass the cache for logged in users even on free plan? =

Yes. This is the main purpose of this plugin.

= What is the swcfpc query variabile I see to every internal links when I'm logged in? =

It is a cache buster. Allows you, while logged in, to bypass the Cloudflare cache for pages that could be cached.

= Do you automatically clean up the cache on website changes? =

Yes, you can enable this option from the settings page.

= Can I restore all Cloudflare settings as before the plugin activation? =

Yes, there is a reset button.

= What happens if I delete the plugin? =

I advise you to disable the plugin before deleting it, to allow you to restore all the information on Cloudflare. Then you can proceed with the elimination. This plugin will delete all the data stored into the database so that your Wordpress installation remains clean.

= What happens to the browser caching settings on Cloudflare? =

You will not be able to use them anymore. However, there is an option to enable browser caching rules

= Does it work with multisite? =

Yes but it must be installed separately for each website in the network as each site requires an ad-hoc configuration and may also be part of different Cloudflare accounts.

= Can I use this plugin together with other performance plugins such like Autoptimize, WP Rocket or W3 Total Cache? =

Yes, you can. Read the FAQ section in the plugin settings page for further information

= I have more questions or Something is not working, what can I do? =

First check the questions mentioned in the FAQ tab inside the plugin settings page, as you will find most of the questions already answered there. If that still doesn't help, Enable the log mode and send us the log file and the steps to reproduce the issue. Make sure you are using the latest version of the plugin.


== Changelog ==

#####   Version 5.2.2 (2026-01-12)

- Fixed translation updates no longer reappear after being successfully updated when using Super Page Cache.
- Fixed Database Optimization now respects the configured schedule and no longer deletes trashed posts immediately when the feature is enabled.
- Update dependencies




[See changelog for all versions](https://store.themeisle.com/changelog/Super%20Page%20Cache%20Pro).



== Screenshots ==

1. Enable Caching in One Click
2. Cache Management with Activity Log
3. Asset Manager
4. Cloudflare (CDN & Edge Caching)
5. Lazy Loading
6. Database Optimization
7. Import and Export Settings
