=== Reading progressbar ===
Contributors: audrasjb,whodunitagency,maxpertici
Donate link: https://www.paypal.me/audrasjb
Tags: reading, progressbar, progress, scroll, read, bar, position, indicator, template, lecture, barre, suivi
Requires at least: 4.9
Tested up to: 6.4
Requires PHP: 5.6
Stable tag: 1.3
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A reading position indicator that you can use where you want: top, bottom or custom position in differents templates or post types.

== Description ==

Get a reading position indicator as a progress bar and show it where you want: top, bottom or custom position in differents templates or post types.

Very lightweight, it uses `<progress>` HTML5 element and few lines of javascript.

Options: 

*   Progress bar’s height
*   Foreground color
*   Background color
*   Position (top, bottom, custom)
*   Class/ID of HTML element to fix the bar (if custom position selected)
*   Page templates
*   Post types (natives or custom)

**Credits** : 

*   [Jean-Baptiste Audras](http://jeanbaptisteaudras.com/ "Jean-Baptiste Audras"), project manager at [Whodunit](http://www.whodunit.fr/ "Whodunit WordPress Agency") and WordPress developer. @audrasjb on [Twitter](https://twitter.com/audrasjb "@audrasjb on Twitter") or [Github](https://github.com/audrasjb "@audrasjb on Github").

== Installation ==

1. Install the plugin and activate.
2. Go to Settings &gt; Reading Progressbar to manage plugin’s options.

== Frequently Asked Questions ==

= Does it works on WordPress network/multisite? =

Sorry but this plugin doesn’t supports network/multisite installation in it's free/public version. 
It will be fixed on version 2.0. Contact me if you want to help or ask for more informations.

== Screenshots ==

1. Plugin option page.
2. Reading Progressbar in custom fixed position on Twenty Seventeen Theme.

== Changelog ==

= 1.3 = 
* Performance enhancement for better support for Google Core Web Vitals: enqueue stylesheets and scripts only when necessary.

= 1.2.3 = 
* Fixed a jQuery error and adding WP 5.7 compatibility. Props @maxpertici.

= 1.2.2 = 
* Removed an unwanted console.log.

= 1.2.1 = 
* Gutenberg and WordPress 5.0/5.1 compatibility. Works fine.

= 1.2 =
* Add german translation and fixed a JS bug while loading the progressbar value. We need to load the whole page before display the bar :)

= 1.1 =
* i18n ready.

= 1.0 =
* Plugin initial commit. Works fine :)