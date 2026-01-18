#####   Version 5.2.2 (2026-01-12)

- Fixed translation updates no longer reappear after being successfully updated when using Super Page Cache.
- Fixed Database Optimization now respects the configured schedule and no longer deletes trashed posts immediately when the feature is enabled.
- Update dependencies

#####   Version 5.2.1 (2025-12-16)

- Removed duplicate Font Optimization cards from the Assets settings

####   Version 5.2.0 (2025-11-28)

- Introduced a [CSS Optimization](https://docs.themeisle.com/article/2367-css-optimizations) feature that leverages a browser-based script to observe which CSS is used in the page, extract critical CSS, and defer the rest — greatly improving load speed and Core Web Vitals (PRO).
- Updated the Activity Log UI to conditionally render content only when logging is enabled
- Improved log messages for cache purges
- Fixed dashboard display issues in RTL mode
- Added a dedicated log message when sitemap preloading is skipped due to the site being private

#####   Version 5.1.6 (2025-11-07)

- The Manage Assets button now enables the assets manager via API before redirecting.
- Reorganized the Assets and JavaScript Optimization sections in the settings for clarity.
- Updated dependencies to the latest versions.

#####   Version 5.1.5 (2025-08-27)

- Fixed the error preventing plugin from uninstallation
- Fixed issue related to problems updating htaccess file

#####   Version 5.1.4 (2025-08-07)

### Fixes
- **Fixed viewport prefetch using incorrect setting slug** to ensure the prefetch feature properly reads and applies user preferences.
- **Resolved fatal error when Keep settings on deactivation was disabled** by adding proper checks to prevent errors during plugin deactivation.
- **Fixed advanced cache functionality when wp-config.php is located above WordPress root** to support non-standard WordPress installations where the configuration file is placed one directory level up.
- **Updated internal dependencies** to latest versions for improved security and performance.

#####   Version 5.1.3 (2025-07-21)

### Fixes
- **Edge case where Cloudflare tab inputs were disabled** when zone id list is already saved to the website, but the user didn't connect the plugin

#####   Version 5.1.2 (2025-07-16)

### New Features
- **Use Cloudflare data in metrics box if available** to provide more accurate performance insights when Cloudflare integration is active.

### Fixes
- **Fixed log lines overflowing activity log card** to ensure proper display within the dashboard interface.
- **Prevent edge case fatal error where header might be array** by adding proper type checking.
- **Fixed migration of Enable Cloudflare CDN & Caching toggle** to ensure proper preservation of cache rule status during plugin updates.
- **Cached pages drawer loading state not adapted to dark mode** to maintain consistent visual appearance across light and dark themes.
- **Fixed terminology in Cloudflare connection flow** by referring to zone as domain for better user understanding during the connection setup process.
- **Updated description for auto-purge on plugin updates** to provide clearer information that the automatic cache clearing behavior happens when the plugin is updated.

#####   Version 5.1.1 (2025-07-15)

### New Features

- **Adds cached pages listing** when user has the `Purge HTML pages only` option enabled.

####   Version 5.1.0 (2025-07-14)

### New Features

- **Complete dashboard redesign** with improved user experience and intuitive navigation for easier plugin management.
- **Reorganized settings structure** that groups related options together, making configuration more straightforward.
- **Integrated documentation** directly within the dashboard, providing helpful tips and guidance without leaving your WordPress admin.
- **Basic metrics system** to track cache performance and give you insights into your site's caching effectiveness.
- **Font optimization features** including Google Fonts combining and local hosting options to improve loading speeds and reduce external dependencies.
- **New Assets Manager** with frontend modal interface for selectively disabling CSS/JS files based on different contexts, helping you eliminate unused scripts and optimize page performance. [Learn More](https://docs.themeisle.com/article/2277-how-to-use-the-asset-manager)
- **Database optimization tools** to clean up post revisions, auto-drafts, trashed content, spam comments, and transients with both manual and scheduled cleanup options.
- **Intelligent viewport-based lazy loading** that automatically detects above-the-fold images and loads them immediately while lazy loading below-the-fold content, optimizing for both mobile and desktop viewports. [Learn More](https://docs.themeisle.com/article/2278-seting-up-lazy-loading)
- **Improved background processing system** by migrating to Action Scheduler for more reliable background task handling.
- **Enhanced Defer JS functionality** with new `spc_defer_script` filter hook allowing developers to exclude specific scripts from being deferred, providing more granular control over JavaScript optimization.

### Fixes

- **Fixed AJAX cache bypass logic** to properly respect the bypass AJAX requests setting when enabled or disabled.
- **Improved POST request handling** to ensure dynamic AJAX operations using POST methods are never cached while allowing GET-based AJAX requests to be cached when appropriate.
- **Fixed PHP warning during wp-cron execution** that was generating Cannot modify header information warnings in error logs every time WordPress cron jobs ran.
- **Resolved undefined variable errors** in advanced-cache.php when cache configuration files become corrupted, preventing PHP warnings and ensuring more reliable cache operation.
- **Fixed undefined array key warning** for Cloudflare Worker settings that was generating PHP notices in the backend interface.
- **Improve wp-config.php file handling** to prevent accidental corruption of the WordPress configuration file in some edge cases that was causing 500 errors on some sites.
- **Fixed fatal error when updating settings** on hosting environments without OPcache enabled by adding proper function existence checks before calling OPcache functions.

#####   Version 5.0.12 (2025-05-23)

- Updated dependencies

#####   Version 5.0.11 (2025-04-09)

- Fix fatal error breaking plugin admin settings page

#####   Version 5.0.10 (2025-04-09)

- Fix for Keep Settings on the Deactivation setting to stay selected on Save

#####   Version 5.0.9 (2024-11-20)

- Fixes issue where auto-purge was not working when posts were updated

#####   Version 5.0.8 (2024-11-08)

- Fixed issue where Automatically preload purged pages option cannot be changed
- Fixed issue where settings could not be saved if on a previous version an invalid email was already saved

#####   Version 5.0.7 (2024-11-06)

fix: number inputs in the dashboard settings having a maximum limit of 100 by default

#####   Version 5.0.6 (2024-11-06)

- Fixed checkbox values not working as expected
- Fixed cache purge and preloader start via cronjob functionality
- Fix programatically purging cache having the wrong authentication parameters
- Added notices to clarify when JS and Media Optimizations work
- Simplified the settings interface and reworked the backend UI
- Tweak dashboard notices UI
- Only display legacy Cloudflare worker settings when the feature is enabled
- Removed HTML Disk Cache comment when WP_DEBUG is not defined and true
- Upgraded wp-background-processing to latest version
- Enable browser caching by default for new users
- Renamed static cache status header from X-WP-CF-Super-Cache to X-WP-SPC-Disk-Cache to avoid confusion

#####   Version 5.0.5 (2024-10-29)

- adds bypass cache reason header
- adds migration functionality for the cache rule when updating the plugin
- adds View Log button so you can view it straight on the website without downloading it
- updates Cloudflare integration for a higher, more efficient cache hit-rate with a transform rule that ignores query parameters
- adds capability of the static cache to ignore URL parameters and deliver cached pages
- improves default ignored cookies list and the way cookies are checked in CloudFlare when checking if cache should be bypassed for a more efficient cache hit-rate
- tweak cache rule to respect browser caching original TTL
- renames the Cloudflare cache rule to warn users about editing it and use the site URL in the name
- automatically run the cache preloader after setting up Cloudflare connection;
- fixes HIT/MISS/DISABLED/BYPASS cache header consistency
- fixes issue where module scripts were deferred
- improves stability and reliability of Cloudflare API interaction
- tweak settings defaults for new users
- hide legacy settings for users that don't have a page rule
- more explicit logging and error handling when something fails when using token permissions
- improve UX for cache test modal

#####   Version 5.0.4 (2024-09-16)

- Fix incorrect sidebar information
- Removes unneeded error logging

#####   Version 5.0.3 (2024-09-16)

- Fix wrong documentation link for media lazy loading setting
- Fix error when saving settings because of conflict with the wp_lazy_loading_enabled filter
- Fix cache test not taking into account legacy page rule
- Fix issue where license cannot be activated on pro version
- Fix issue with pro version requiring update even if at latest version

#####   Version 5.0.2 (2024-09-11)

- Use proper versions for backend scripts and styles
- Improve dashboard script dependency loading
- Improve dashboard settings organization and UI
- Fixed page cache setting not appearing in some instances
- Fixed Cloudflare cache toggle value not being consistent in the dashboard
- Updated Cloudflare cache rule to work regardless of the URL protocol
- Added media lazy loading feature
- Added javascript delayed loading feature [PRO]
- Added javascript defer feature [PRO]

#####   Version 5.0.1 (2024-08-28)

- Fixed critical error produced by the Cache status bar
- Fixed Test Cache functionality and reliability for Cloudflare
- Improved the domain rules for caching

####   Version 5.0.0 (2024-08-27)

- Super Page Cache for Cloudflare is now becoming Super Page Cache. While you can still enjoy the Cloudflare functionality, it is now optional. The plugin will work for your non-Cloudflare sites as well, with much more to come.
- Fixed compatibility issues with PHP 8.3
- Fixed an issue with the user survey

#####   Version 4.7.13 (2024-07-18)

- Fixed Zone ID selection for creating Cache Rule where the correct domain was not picked

#####   Version 4.7.12 (2024-07-15)

- Fixed fatal error when calling get_objects() method

#####   Version 4.7.11 (2024-07-10)

- Fixed an issue where the SpinupWP provider detection function wasn't working properly.
- Removed module preload for instant.page script as it breaks WordPress 6.5 Interactivity API
- Migrated to the new Page Rules

#####   Version 4.7.10 (2024-04-17)

### Improvements
- **Updated internal dependencies:** Enhanced performance and security.

#####   Version 4.7.9 (2024-04-01)

### Improvements
- **Updated internal dependencies**

#####   Version 4.7.8 (2024-03-29)

### Fixes
- Updated internal dependencies

#####   Version 4.7.7 (2024-03-07)

### Fixes

- NPS Survey added
- Updated dependencies

#####   Version 4.7.6 (2024-02-15)

### Fixes
- Enhanced security
- Updated dependencies

#####   Version 4.7.5 (2023-10-30)

- Added swcfpc_bypass_cache_metabox_post_types filter to ensure users can add their CPTs to the list of allowed post types for which the bypass cache meta box will be registered.
- Make sure that the Purge CF cache option is not shown for WC Subscription page

#####   Version 4.7.4 (2023-06-12)

- Making sure the log file shows the date and time in accordance with the Timezone settings set inside WordPress admin
- Making sure that the Purge CF Cache option is not showing up for the WooCommerce individual order items

#####   Version 4.7.3 (2023-02-02)


- Fixing PHP 8.1+ deprecated error notice ([reported here](https://wordpress.org/support/topic/php-8-1-deprecated-notices/#post-16294666))
- Making sure that the version query param is not added to the instantpage.min.js so that the modulepreload can work correctly

#####   Version 4.7.2 (2022-11-16)

- Loading an old version of the SweetAlert2 library that doesn't have the anti-Russian Malware added. ([Reported here](https://wordpress.org/support/topic/sites-infected-after-update/))

#####   Version 4.7.1 (2022-11-15)

* Fix upgrade routine to the latest version.

####   Version 4.7.0 (2022-11-15)

- New: Added two new filters i.e. `swcfpc_normal_fallback_cache_html` and `swcfpc_curl_fallback_cache_html`, to give users the ability to make changes to the generated fallback cache HTML before it gets saved in the disk. Idea requested [here](https://wordpress.org/support/topic/no-filter-for-cached-content/).
- Making sure that when a page/post is marked as private or password protected, the cache gets auto purged
- Added a list of query Parameters that are now ignored by default by both the plugin and worker code.
- Added support for the YASR premium version
- Updated sweetalert library to v11.4.26
- Making sure that when a page/post is marked as private or password protected, the cache gets auto purged
- Added urls as an argument to swcfpc_purge_urls, swcfpc_cf_purge_cache_by_urls_before, swcfpc_cf_purge_cache_by_urls_after action
- Making sure for the WP Rocket hook for after_rocket_clean_post, after_rocket_clean_files, rocket_rucss_complete_job_status only the URLs WP Rocket purged - also gets purged from Cloudflare
- Added option for Removing Cache Buster Option (Super Advanced Use Case)
- New: Adding filter swcfpc_normal_fallback_cache_html & swcfpc_curl_fallback_cache_html to make changes to the generated fallback cache HTML before it gets saved to the disk.

#####   Version 4.6.1 (2022-05-27)

 - Bugfix: Added missing selector in `backend.js`
 - Improvement: Updating the FAQ about properly using this plugin with WP Rocket - added hyperlinked to [this gist](https://gist.github.com/isaumya/d5990b036e0ed2ac55631995f862f4b8)
 - Improvement: Storing non-sensitive data as JSON instead of PHP to ensure a faster execution and also the system will be able to handle large sites with many URLs compared to the existing process i.e. storing JSON data in PHP and then asking PHP to read that and decode it.
 - Update compatibility with WordPress 6.0

####   Version 4.6.0 (2022-05-20)

- Bugfix: Removing the trailing slash from the `swcfpc_fallback_cache_remove_url_parameters()` when query parameters are removed from the URL. Previously it was creating double cache keys when the same URL is visited once without any query param and then with e.g. utm query param.
- Improvement: Added `swcfpc_fc_modify_current_url` filter for special use cases where the user wants to filter the `$current_uri` by themselves and remove the query params as they see fit.
- Improvement: Updating the worker code to ensure that static files are not processed by Worker and instead let the CF system handle the static file in accordance with the cache-control header. Also replaced the forEach() and every() loop with a much faster for() loop to improve the code performance, vastly reducing CPU usage.
- Bugfix: Make sure that the preloader runs only after all cache purging is complete
- Bugfix: Make sure that the purge_cache_on_post_edit() and wp_rocket_hooks() does not fire when nav menus are updated from the WP Nav Menu page
- Bugfix: Make sure that the unnecessary very parameter ?v= is not considered by the system
- Bugfix: Added AMP in the list of third-party query parameters for worker mode.
- Bugfix: Adding nocache_headers() for cronjob_preloader() and cronjob_purge_cache() function. Checking if header is not sent then add the nocache header
- Improve content copy across the plugin
- Adds full translation support 
- Adds Expert Service mention

#####   Version 4.5.8 (2022-02-09)

- Bugfix: Gutenberg editor permalink doesn't have the cache buster query string added
- Tested up to WordPress 5.9

#####   Version 4.5.7 (2021-11-02)

* Further optimized the worker code and extended the handled server response codes to cover edge case scenarios
* Improved worker update by making sure the worker code is updated when the plugin is updated

##### [Version 4.5.6](https://github.com/Codeinwp/WP-Cloudflare-Super-Page-Cache/compare/v4.5.5...v4.5.6) (2021-10-28)

* Remove serialize() & unserialize() from saving options data as WP already does it automatically. 
* Improve admin area loading scripts, making sure that on the backend the plugin scripts are not loaded where it is not needed for example customizer pages, oxygen visual page builder pages etc.
* Added a much more robust and updated version of the Worker Script which does the same thing as previously but now is more robust with multiple exception handling across every possible edge case and to ensure the worker script never throws an unhandled exception no matter what is thrown at it. Also now when a page cache is bypassed due to the default bypass cookie, in the response header under the x-wp-cf-super-cache-worker-bypass-reason it will show you the name of the default cookie for which the cache has been bypassed.
* Improve worker update by making sure when someone updates the plugin, the worker script is also gets updated.

##### [Version 4.5.5](https://github.com/Codeinwp/WP-Cloudflare-Super-Page-Cache/compare/v4.5.4...v4.5.5) (2021-08-05)

* Remove readonly from swcfpc_cf_apitoken_domain

##### [Version 4.5.4](https://github.com/Codeinwp/WP-Cloudflare-Super-Page-Cache/compare/v4.5.3...v4.5.4) (2021-08-04)

- Updated Sweetalert to v11.0.18
- Adding ability to add more custom URLs in the list of related urls and also giving the ability to remove the home page from the list of related URLs with the help of constants
- Fix default value call for swcfpc_post_related_url_init filter
- Mistakenly added filter under the action section in FAQ
- Making sure we are not loading our plugin scripts on WooFunnels Order Bumps Page as it uses super old sweetheart, it creates compatibility issues
- Added missing $screen var on add_toolbar_items
- Bugfix for AMP standard mode admin pages
- Fixing CF API Token usage bug
- Making sure that the API Token domain field is read-only and cannot be typed as the domain name in that field is system generated

##### [Version 4.5.3](https://github.com/Codeinwp/WP-Cloudflare-Super-Page-Cache/compare/v4.5.2...v4.5.3) (2021-05-25)

- Fixing a bug related to CRON job
- Fixing a bug related to not loading the admin pages properly when AMP plugin is installed and Standard mode is selected
- Getting rid of Cloudflare __cfid cookie check as Cloudflare has deprecated it
- Making sure Sweetalerat is loaded locally instead of jsdeliver & also proper version number is mention in the code
- Updating sweetalert to v11.0.5
- Fixing a minor bug in backend.js related to a page reload upon activation of page caching
- Adding proper wp rocket filters to disable WP Rocket page caching when using this plugin. The previously documented filter is deprecated.

##### [Version 4.5.2](https://github.com/Codeinwp/WP-Cloudflare-Super-Page-Cache/compare/v4.5.1...v4.5.2) (2021-05-13)

- Adds compatibility with Swift Performance Pro
- Preload instantpage.min.js as a Module and not as Script
- Fix is_404 incorrect call
- Bypass caching on password-protected pages
