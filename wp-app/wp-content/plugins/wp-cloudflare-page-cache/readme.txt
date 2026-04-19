=== Super Page Cache ===
Contributors: themeisle, salvatorefresta, isaumya
Tags: cloudflare, caching, performance, page caching, pagespeed
Requires at least: 5.3
Requires PHP: 7.4
Tested up to: 6.9
Stable tag: 5.2.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Boost PageSpeed, SEO, and Core Web Vitals with full page caching, JS/CSS optimization, media optimization, and Cloudflare CDN.

== Description ==

Super Page Cache is a powerful full page cache plugin for WordPress that caches static files (CSS, JS, images) and HTML webpages at both server disk-level and the global Cloudflare CDN. Get enterprise-level performance with zero configuration required.

Super Page Cache improves site speed by enabling server-side caching, cache preloading, local Google Fonts, and removing unused CSS and JavaScript, boosting Core Web Vitals and load times.

Works right out of the box with or without Cloudflare. Powerful disk caching saves files locally on your server. To use Cloudflare CDN, just enter your API Key or Token. The intuitive dashboard includes detailed settings and built-in documentation, though most users won't need to change the defaults.

https://youtu.be/SYhoaL_fUY0?si=94atnvwHRF5r_U3U

### **⚡ Quick Links**

- [Documentation](https://docs.themeisle.com/collection/2199-super-page-cache) → Complete setup and configuration guide
- [Support Forum](https://wordpress.org/support/plugin/wp-cloudflare-page-cache/) → Community help and expert support
- [YouTube Videos](https://youtube.com/playlist?list=PLmRasCVwuvpSJuwaV7kDiuXhxl08zbZr5&si=Gem626AyPpNenDF3) → Step-by-step visual guides for every feature
- [Go Pro](https://themeisle.com/plugins/super-page-cache-pro/?utm_source=wordpressorg&utm_medium=readme&utm_campaign=quicklinks) → Advanced features and priority support

### **🚀 How Does The Plugin Work?**

**Disk Caching (Works Without Cloudflare):** Server-level caching saves HTML pages and static files directly to your server, loading pages instantly without any CDN.

**Cloudflare CDN (Optional):** Connect your Cloudflare account to leverage **[200+ edge locations](https://www.cloudflare.com/network/)** using modern Cache Rules. Works with **FREE Cloudflare Plan** - no paid account required. Achieve higher Pingdom and GTmetrix scores with edge delivery.

Supports **Free, Pro, and Enterprise** Cloudflare plans with auto-purging, cron jobs, and integrated metrics.

### **🔥 Core Features (Free Version)**

#### 🚀 Complete Caching Solution

- **Disk Caching:** Lightning-fast server caching with Cloudflare fallback
- **CDN Integration:** Optional Cloudflare CDN with 200+ edge locations
- **Cache Controls:** Exclude cookies, query params, URIs, AMP, feeds, REST API
- **Cache Lifespan:** Custom expiration or never-expire settings
- **Header Management:** Preserve critical response headers
- **Cache Buster:** Logged-in users never see cached content
- **Performance Metrics:** Track cache effectiveness

#### ⚡ Performance Optimization

- **Google Fonts:** Combine and serve locally for better Web Vitals
- **Lazy Loading:** Images, videos, iframes, background images
- **Lazy Load Exclusions:** By keywords, URL patterns, or CSS classes
- **Assets Manager:** Enable/disable CSS/JS per page
- **Browser Caching:** Auto-configure `.htaccess` rules

#### 🛠️ Advanced Cache Management

- **Auto-Purging:** Clear cache on content updates including related pages
- **Granular Purge:** HTML only or entire cache with assets
- **Preloader:** From sitemaps, menus, or recent posts (manual/CRON)
- **Background Processing:** Uses `Action Scheduler`
- **Queue-Based Purging:** Prevent server overload
- **Backend Bypass:** Prevent admin dashboard caching
- **Per-Page Control:** Exclude via editor metabox
- **Toolbar Purging:** Instant purge from WP toolbar

#### 🔧 Developer & Power User Features

- **Redesigned Dashboard:** Intuitive navigation with grouped settings
- **Built-in Documentation:** Tips within the dashboard
- **Cache Backends:** `advanced-cache.php` or cURL-based
- **Defer JS Filter:** `spc_defer_script` to exclude scripts
- **Role Permissions:** Control who can purge cache
- **Cloudflare API:** All plans via API Key or Token
- **Export/Import:** Settings as JSON
- **Preserve on Deactivation:** Retain settings for staging

#### 🗄️ Database Optimization

- **Database Cleanup:** Remove revisions, auto-drafts, trash, spam, transients
- **Scheduled Maintenance:** Daily, weekly, or monthly
- **Table Optimization:** `SQL OPTIMIZE TABLE` for defragmentation
- **Selective Cleanup:** Choose which data types to clean

#### 🌐 Universal Compatibility

- **Premium Hosts:** Kinsta, WP Engine, SpinupWP, and more
- **eCommerce:** WooCommerce, Easy Digital Downloads
- **Server Caches:** Varnish, OPcache auto-purge
- **Prefetch:** Internal links via [Instant.page](https://instant.page)

### **💎 Super Page Cache PRO with Advanced Performance Features**

- **Ignore Marketing Parameters:** Better cache hit rate (UTM, affiliate, tracking codes)
- **Defer JavaScript:** Eliminate render-blocking JS. [Learn more](https://docs.themeisle.com/article/2058-defer-js)
- **Delay JavaScript:** Load JS on interaction only. [Learn more](https://docs.themeisle.com/article/2057-delay-js)
- **Advanced Lazy Loading:** Viewport detection, above-the-fold optimization
- **Advanced Exclusions:** Exclude specific JS/pages from caching
- **Priority Support:** Faster email response times

**[Upgrade to Pro](https://themeisle.com/plugins/super-page-cache-pro/?utm_source=wordpressorg&utm_medium=readme&utm_campaign=spc-pro-featrues)**

### **🎯 Usage Note**

Disable page caching in other plugins (WP Rocket, LiteSpeed Cache, W3 Total Cache). Safe to use alongside [Optimole](https://wordpress.org/plugins/optimole-wp/), Autoptimize, Perfmatters, ShortPixel for asset optimization.

### **⭐ What Users Say**

> ★★★★★ "Response times dropped from 400ms+ to below 200ms. Almost the same as my static S3 site—using the free version!" – @jurijs0

> ★★★★★ "A game-changer for our website's performance! Noticeable improvement in loading times, boosted engagement and SEO." – @brandnexusstudios

[Leave a review](https://wordpress.org/support/plugin/wp-cloudflare-page-cache/reviews/#new-post) | [Follow us on Twitter](https://twitter.com/themeisle) | [YouTube](https://www.youtube.com/@Themeisle)

---

Super Page Cache is backed by [Themeisle](https://themeisle.com/), powering 1M+ WordPress sites with 450+ five-star [Trustpilot](https://www.trustpilot.com/review/themeisle.com) ratings.

== Installation ==

FROM YOUR WORDPRESS DASHBOARD

1. Visit "Plugins" > Add New
2. Search for Super Page Cache
3. Activate Super Page Cache from your Plugins page.

FROM WORDPRESS.ORG

1. Download Super Page Cache
2. Upload the "wp-cloudflare-super-page-cache" directory to your "/wp-content/plugins/" directory, using ftp, sftp, scp etc.
3. Activate Super Page Cache from your Plugins page.

== Frequently Asked Questions ==

= How is this plugin different from Cloudflare APO? =

Cloudflare APO uses Workers & KV Storage to push cache to all edges instantly. It costs $5/month for free accounts (free for paid accounts).

Our plugin enables FREE account users to leverage **Cloudflare's Cache Rules** with more features, functionality, and third-party plugin integration than APO offers. [Read a detailed comparison](https://wordpress.org/support/topic/automatic-platform-optimization-for-wordpress/#post-13486593).

= How do I know if everything is working properly? =

Check HTTP response headers in Incognito mode:

- **x-wp-cf-super-cache**: `cache` = active, `no-cache` = disabled for this page
- **x-wp-cf-super-cache-active**: `1` = page placed in Cloudflare cache
- **cf-cache-status**: `HIT` = served from cache, `MISS` = refresh page, `BYPASS` = excluded, `EXPIRED` = cache expired

= Do you allow to bypass the cache for logged in users even on free plan? =

Yes. This is the main purpose of this plugin.

= What is the swcfpc query variable I see on every internal link when I'm logged in? =

It is a cache buster. Allows you, while logged in, to bypass the Cloudflare cache for pages that could be cached.

= Do you automatically clean up the cache on website changes? =

Yes, you can enable this option from the settings page.

= Can I restore all Cloudflare settings as before the plugin activation? =

Yes, there is a reset button.

= What happens if I delete the plugin? =

Disable the plugin first to restore Cloudflare settings, then delete. The plugin removes all stored data to keep your WordPress installation clean.

= What happens to the browser caching settings on Cloudflare? =

You will not be able to use them anymore. However, there is an option to enable browser caching rules.

= Does it work with multisite? =

Yes but it must be installed separately for each website in the network as each site requires an ad-hoc configuration and may also be part of different Cloudflare accounts.

= Can I use this plugin together with other performance plugins such as Autoptimize, WP Rocket or W3 Total Cache? =

Yes, you can. Read the FAQ section in the plugin settings page for further information

= I have more questions or something is not working, what can I do? =

Check the FAQ tab in plugin settings first. If needed, enable log mode and send us the log file with steps to reproduce. Ensure you're using the latest version.


== Changelog ==

#####   Version 5.2.4 (2026-03-19)

* Improved error handling in  by logging and safely aborting if the  file cannot be parsed.
* Enhanced cache URL logic to handle missing  or , logging issues and preventing invalid cache entries.
* Hardening Security 
* Fixed usage of  in  for more reliable directory path generation.
* Adjusted module loading order for  in  to ensure proper initialization.  
* Updated cache purge logic to handle post status transitions both to and from publish/private, improving cache consistency.




[See changelog for all versions](https://store.themeisle.com/changelog/Super%20Page%20Cache%20Pro).



== Screenshots ==

1. Enable Caching in One Click
2. Cache Management with Activity Log
3. Asset Manager
4. Cloudflare (CDN & Edge Caching)
5. Lazy Loading
6. Database Optimization
7. Import and Export Settings
