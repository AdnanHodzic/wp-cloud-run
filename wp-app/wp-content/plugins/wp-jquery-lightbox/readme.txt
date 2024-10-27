=== LightPress Lightbox ===
Contributors: pandaboxwp
Tags: lightbox, photo, image, gallery
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 2.3.3
Requires PHP: 7.0
License: GPLv2 or later

Simple, lightweight lightbox plugin for WordPress. Formerly the WP JQuery Lightbox.

== Description ==

LightPress Lightbox is a popular, very lightweight lightbox plugin for WordPress. It is active 40,000 websites. After activation, settings will appear under 'Lightbox' on your website's main admin menu, and images and galleries will open in a nice lightbox. 

To make images open in the lightbox, you need to set images to "Link To:" media file. For image blocks, this setting is on the toolbar at the top of the block. For galleries, this setting is on the sidebar by other block options.

**LIGHTBOX DEMOS**

**[Free Lightbox Demo](https://lightpress.io/free-lightbox/)**  
**[Pro Lightbox Demo - Light Theme](https://lightpress.io/pro-lightbox/)**  
**[Pro Lightbox Demo - Dark Theme](https://lightpress.io/pro-lightbox-dark/)**  

**FREE LIGHTBOX FEATURES**
- Automatic detection of images and galleries
- Customize colors of lightbox and navigation
- Classic and modern navigation styles
- Option to show info bar
- Option to show title or alt text
- Option to show download button
- Slideshow options
- Keyboard navigation
- And more!

Links:  
-> **[Demos](https://lightpress.io/free-lightbox/)**  
-> **[Docs](https://lightpress.io/free-lightbox-documentation/)**  
-> **[Free Support](https://wordpress.org/support/plugin/wp-jquery-lightbox/)**  

**PRO LIGHTBOX FEATURES**

LightPress Pro is a premium extension for this lightbox plugin that offers:
- Priority support via email directly from devs
- *Powerful, Modern Pro Lighbox*
- Beautiful, modern full-browser and full-screen ligthbox
- Dozens of customizations options
- Thumbnails within the lightbox
- Toolbar with controls for thumbnails, zoom, slideshow, and more
- Open videos (Youtube, Vimeo, self-hosted)
- Opens PDF files
- Opens content from external web pages
- Create easy modals and popup boxes
- Open image maps
- Flawless on mobile devices
- Hyper fast performance

-> **[VIEW PRO LIGHTBOX DEMOS](https://lightpress.io/pro-lightbox/)**  
-> **[GET PRO SUPPORT](https://lightpress.io/contact/)**  

**Background and Thanks**

Special thanks to [Ulf Benjaminsson](http://www.ulfbenjaminsson.com), who created the original WP JQuery Lightbox plugin and maintained it for many years.

== Screenshots ==

1. Default frontend appearance.

== Installation ==

1. Upload the `wp-jquery-lightbox`-folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Check out the jQuery Lightbox-panel in your admin interface for usage details and configuration.

== Changelog ==

= 2.3.3 =
* Update Pro landing and pricing
* Fix invalid invalid infobar CSS
* Update WordPress tested version

= 2.3.2 (2024-07-30) =
* Fix Animation Duration setting
* Fix top navbar box shadow
* Show optin/reviews to all users

= 2.3.1 (2024-07-08) =
* Fix JavaScript error
* Fix license checks

= 2.3.0 (2024-07-08) =
* Add block editor lightbox panel
* Add lightbox panel display options
* Add email opt in modal
* Add landing page for LightPress Pro

= 2.2.0 (2024-06-20) =
* Add review requests
* Add option to disable core lightbox
* Change how plugin is instantiated

= 2.1.0 (2024-06-19) =
* Major file reorganization
* Fix image inserting not working in classic editor
* Update plugin banner and icon

= 2.0.0 (2024-06-18) =
* Rename plugin to LightPress
* Add core LightPress plugin class
* Add new settings screen
* Old plugin file can be loaded by setting JQLB_LEGACY constant to true 

= 1.5.5 (2024-06-03) =
* Update WP tested version
* Improve JS enqueueing
* Minor security fix (issue only affected authenticated users)

= 1.5.4 (2024-05-03) =
* Add plugin timestamp
* Update WP tested version

= 1.5.3 (2024-03-12) =
* Improve mobile panzoom behavior
* Remove nav for single images
* Option to use alt as title

= 1.5.2 (2024-03-12) =
* Fix new nav arrow size

= 1.5.1 (2024-03-12) =
* Replace dashicons with svg for nav arrows

= 1.5 (2024-03-07) =
* Added new nav arrow style options
* Added overlay color/opacity options
* Added border color/width options
* Added image shadow option
* Added option to hide infobar
* Improved styling

= 1.4.9.3 (2024-02-08) =
* Added pinch-to-zoom support for mobile
* Improved mobile styling

= 1.4.9.2 (2024-01-12) =
* Update author and contributors
* Update tested WordPress version
* Update minimum PHP version to 7.0

= 1.4.9.1 (2023-11-05) =
* Add webp support
* Remove donation links

= 1.4.9 (2023-11-03) =
* Updated readme author and description
* Fixed image centering on small screens
* Removed white space from end of lines
* Updated default settings for resize_on_demand and margin_size
* Capitalized slideshow links

= 1.4.8.4 (2023-10-24) = 
* Removed screenshots and banner image from repository

= 1.4.8.3 (2021-01-26) = 
* Added setting to control whether jQuery is loaded in the header or footer.

= 1.4.8.2 (2021-01-26) = 
* Migrated lightbox to modern jQuery (Thanks: [Joseph Rézeau aka papijo](https://www.rezeau.org/)!) 
* Improved support of Gutenberg editor's image captions (Thanks: [Joseph Rézeau aka papijo](https://www.rezeau.org/)!) 
* Tested up to WordPress 5.6 and PHP 7.4.12 

= 1.4.8.1 (2019-03-24) =
* Tested for WordPress 5.x and PHP 7.0

= 1.4.8 (2015-12-31) =
* Fixed translations not being loaded properly
* Fixed support for WordPress 4.4 

= 1.4.7 (2015-09-21) =
* Support for WordPress' new translation system.
* Updated translation for Portugese (Thanks: Pedro Mendonça)
* Added images and CSS for Portugese (Thanks: Pedro Mendonça)

= 1.4.6 (2015-03-20) =
* "Download link" now correctly open a download dialogue instead of displaying the file
* Title, caption and all control elements can now be turned on / off individually
* Added support for custom style sheets (just copy original to your theme's style folder, and hack away)
* Restructured the HTML slightly, making styling easier
* Added specific CSS selectors for all elements
* Fixed animation speed setting being ignored
* Dropped quirks support for Internet Explorer 8
* Removed help-text setting 

= 1.4.5 (2013-06-09) =
* Fit to screen edge on low rez displays (<500px)
* Added global setting to hide title & caption
* Reduced the amount of code running for each image shown
* Updated German translation (thanks [Niko23](http://wordpress.org/support/topic/translation-of-client-side-text?replies=5#post-4230050))
* Fix: swipe direction
* Fix: slideshow now waits for images to display before starting timer.
* Fix: support for latest jQuery
* Fix: WordPress encoding wrong quote mark in captions, causing duplicate texts to display.


= 1.4.1 (2013-02-03) =
* Added swipe gestures on touch devices
* Added slideshow 
* Added support for multiple sets in one post (use: [`group`](http://wordpress.org/extend/plugins/wp-jquery-lightbox/installation/) with the gallery-shortcode)
* Added support for arbitrary Download links via the [`data-download`-attribute](http://wordpress.org/extend/plugins/wp-jquery-lightbox/installation/)
* Added support to pick text from the image ALT-attribute (if title is not available) 
* Added translations: Norwegian Bokm&aring;l (thanks [Ole Martin Kristiansen](http://hvorerole.com/)!)
* Fix regexp to deal with urls like www.gif.org. (Thanks Yannick Berker!)
* Fix changed common IDs to avoid collisions. (you'll have to update your CSS now!) (Hat tip; Pollmann Frederic)
* Fix better support of Infinite Scroll and similar
* Fix toggling visible state on unrelated object, embed and select nodes. [mwolfe02](http://wordpress.org/support/topic/lightbox-shows-hidden-selectembedobject-blocks?replies=1)
* Fix leaky CSS on admin page

= 1.3.4.2 (2011-02-01) =
* Fallbacks for people using older jQuery

= 1.3.4.1 (2011-01-31) =
* Updated deprecated jQuery calls (thanks; David Naber)

= 1.3.4 (2011-12-29) =
* Maybe fix for [mixed HTTP/HTTPS sites](http://wordpress.org/support/topic/mixed-http-and-https-installation-problems)
* [Support for query params in image links](http://wordpress.org/support/topic/automatic-lightbox-functionarity-failes-to-work-with-image-links?replies=2#post-2302997)
* [Fixed button messup in Firefox](http://wordpress.org/support/topic/plugin-wp-jquery-lightbox-broken-in-firefox?replies=9)
* Fixed depth fight with the default twentyeleven theme header
* [Fixed admin bar covering the lightbox](http://wordpress.org/support/topic/plugin-wp-jquery-lightbox-show-download-link-not-working?replies=4#post-2400784)
* Added info on how to disable lightbox for specific links (bogus rel-attribute)
* Added translations: [Romanian](http://wordpress.org/support/topic/wp-jquery-lightbox-romanian-translation?replies=1), [French](http://wordpress.org/support/topic/plugin-wp-jquery-lightbox-french-translation?replies=2#post-2187626) and [Hebrew](www.sagive.co.il)
* Updated: Russian language icons (thanks; Ilya Gorenburg), [Japanese translation](http://wordpress.org/support/topic/plugin-wp-jquery-lightbox-japanese-translation-for-133?replies=1)

= 1.3.3 (2011-06-12) =
* Fixes [for Internet Explorer](http://wordpress.org/support/topic/plugin-wp-jquery-lightbox-jquery-version-and-ie-issues) (A million thanks to [David Grayston](http://www.grayston.net/2011/internet-explorer-v8-and-opacity-issues/#more-342)!).
* Fix for [mixed HTTP/HTTPS installations](http://wordpress.org/support/topic/mixed-http-and-https-installation-problems).
* Added setting to have margins to screen edge.
* Added setting to put info & navigation on top.
* Added setting for help text.
* Added Japanese translation ([Thanks redcocker](http://wordpress.org/support/topic/plugin-wp-jquery-lightbox-japanese-translation)).

= 1.3.2 (2011-05-16) =
* Added support for auto-lightboxing comments too
* Added Russian and Czech translations (incl. images). Thanks again Denis!
* Added Polish translation ([thanks Fisiu](http://wordpress.org/support/topic/plugin-wp-jquery-lightbox-polish-localization?replies=1))
* Fixed fallbacks to avoid [breakage over JavaScript optimizers](http://wordpress.org/support/topic/122-worked-13-does-not-work-for-me?replies=19#post-2091734).
* Known issues: [1.3.x is garbage on IE7 and IE8](http://wordpress.org/support/topic/plugin-wp-jquery-lightbox-jquery-version-and-ie-issues?replies=3). I've got no clue and no time - **help appreciated!**

= 1.3.1 (2011-05-02) =
* Forgot to include languages files! :)

= 1.3 (2011-05-01) =
* Improved scaling to make maximum use of display area.
* Supports orientation / resize changes - Lightbox reflows with site. 
* Added translation support (Thanks; [Martin S](http://sukimashita.com/) & Denis N. Voituk).
* Added option to display download link.
* Added support for disabling all animations (set duration to 0).
* Fixed "duration" not having an effect.
* [For Developers: public method to apply Lightbox to any string.](http://wordpress.org/extend/plugins/wp-jquery-lightbox/installation/)

= 1.2.2 (2011-04-14) =
* Use WordPress bundled jQuery instead of forcing the Google CDN
* Fixed the settings link on the Plugins page

= 1.2.1 (2010-10-24) =
* [Use only caption if title is identical](http://wordpress.org/support/topic/plugin-wp-jquery-lightbox-title-captions-bug-found-solved-and-fix-proposed?replies=8#post-1748874)
* Removed a forgotten debug call

= 1.2 (2010-10-12) = 
* Added support for Media Library titles and captions.
* Minified the javascript (8.6KB vs 17.8KB)
* Minified the CSS (2.0KB vs 2.7KB)

= 1.1 (2010-10-09) = 
* Honors empty captions. 
* Fixed typos on admin page.
* [thanks, josephknight!](http://tinyurl.com/3677p6r)

= 1.0 (2010-04-11) = 
* Release.

== Upgrade Notice ==
= 1.4.8 =
Fixed support for WordPress 4.4, Fixed translations not being loaded properly 

= 1.4.7 =
New Portugese translation. Support for WordPress' new translation system.

= 1.4.6 =
Lots of bug fixes and performance improvements. New CSS styles!

= 1.4.5 (2013-06-09) =
Support latest jQuery. Better use of small screen estate, fixed buggy features (slideshow, swipes) and reduced system load. 

= 1.4.1 =
Swipe gestures, slideshow, sets and lots of fixes. Check your config screen after update!

= 1.3.4.2 (2011-02-01) =
* Fallbacks for people using older jQuery

= 1.3.4.1 (2011-01-31) =
Updated deprecated jQuery calls (thanks; David Naber)

= 1.3.4 =
Lots of fixes, translations and support for the Admin Bar and Twentyeleven theme.

= 1.3.3 =
Fixes for Internet Explorer. Added Japanese translation.

= 1.3.2 =
Support for auto-lightboxing comments. Added Polish, Russian and Czech languages.

= 1.3.1 =
Added missing files from previous release... Also: mobile support, larger display area. 

= 1.3 =
Enables larger viewing area, improved mobile experience and translations.

= 1.2.2 =
Use WordPress bundled jQuery instead of forcing the Google CDN

= 1.2.1 =
Removed forgotten debug call. Important upgrade!

= 1.2 =
Support caption and titles from the Media Library

= 1.1 =
Honors empty captions and fixes some typos.

= 1.0 =
First release.


== Frequently Asked Questions ==

= Trouble shooting: =
If you have problems with WP jQuery Lightbox, please make sure you try these steps before asking for help. If you ask for help and I find any of these steps would fix the problem, I will just link you back here to do the work yourself.

1. Make sure your site is not throwing any javascript errors. Use [your browsers javascript console](http://webmasters.stackexchange.com/questions/8525/how-to-open-the-javascript-console-in-different-browsers) to find out.

If you're running a custom theme:

1. Make sure you have [`wp_head();`](http://codex.wordpress.org/Function_Reference/wp_head) just before the closing `</head>` tag of your theme, or you will break many plugins, which generally use this hook to reference JavaScript files. 

1. For the same reason, always have `wp_footer();` just before the closing `</body>` tag of your theme.

1. Many JavaScript optimizers, combiners, minifiers, etc. conflict with [`wp_localize_script`](http://codex.wordpress.org/Function_Reference/wp_localize_script2), used to configure this plugin and many others.

* If you have problems with jQuery Lightbox; first disable all JavaScript-optimizing plugins. (Optimize Scripts, W3 Total Cache, WP Minify etc)

* If you develop JavaScript optimizers for WordPress, please play nice with the default API...

* [More info about this issue](http://wordpress.org/support/topic/122-worked-13-does-not-work-for-me?replies=19)

Lastly:

1. Disable all other plugins, one at a time. Try the lightbox between each.

1. Revert to the default theme. Did it help? Fix your theme.

1. Search the forums for similar symptoms. 

Still have problems? Post a link and describe what issue you're having, and tell us what of these steps you've already tried.

= Can the lightbox display the image description instead of the title? =
WP jQuery Lightbox grabs texts from three different HTML nodes;

1. `title`-attribute of link-to-image
1. `img` tag's `title` attribute (if 1 is empty)
1. `img` tag's `alt` attribute (if 1 and 2 is empty)
1. HTML content of `.gallery-caption` or `.wp-caption-text` (in that order)

Look at how the default TwentyTwelve theme uses these in its galleries and set up your theme so it outputs what you need in these places.

= I can see elements of my site through the overlay = 

It's a problem of [z-index](http://www.w3schools.com/Css/pr_pos_z-index.asp). Check [the z-index property](http://www.w3schools.com/Css/pr_pos_z-index.asp) for the problematic elements, and force them to be less than 100. (Thanks [dway](http://wordpress.org/support/topic/plugin-wp-jquery-lightbox-title-captions-bug-found-solved-and-fix-proposed?replies=20#post-2052340)!)

= How do I add images to a post? =

[Inserting Images into Posts and Pages](http://codex.wordpress.org/Inserting_Images_into_Posts_and_Pages)

= How do I create a thumbnail gallery? =

Upload images to a post (see previous question) and use [WordPress' built-in gallery shortcode](http://codex.wordpress.org/Gallery_Shortcode): `[gallery link="file"]`

Note the `link="file"` - this is crucial! By default the gallery will link your humbnails to a page displaying your image. With `link="file"` the thumbnails links 
directly to the image files - allowing Lightbox to function.

= Must fade-in and animation of all *box-scripts be so slow? =

WP-jQuery Lightbox lets you configure the animation duration and disable image resizing from the admin panel. Set duration to 0 to disable animations entirely.

= For developers: =
1. Always have `wp_footer();` just before the closing `</body>` tag of your theme, or you will break many plugins, which generally use this hook to reference JavaScript files. 
1. Apply lightbox to any content by running `jqlb_apply_lightbox($your_content, "any ID");` It returns a string with all image links lightboxed, grouped by `"any id"`.
1. Many JavaScript optimizers, combiners, minifiers, etc. conflict with [`wp_localize_script`](http://codex.wordpress.org/Function_Reference/wp_localize_script2), used to configure this plugin and many others.

* If you have problems with jQuery Lightbox; first disable all JavaScript-optimizing plugins. (Optimize Scripts, W3 Total Cache, WP Minify etc)
* If you develop JavaScript optimizers for WordPress, please play nice with the default API...
* [More info about this issue](http://wordpress.org/support/topic/122-worked-13-does-not-work-for-me?replies=19)

= Can I help you in any way? =

Indeed you can! 

Translations and help with implementing them would be nice.

== Additional Info ==
Copyright (C) 2010-2023 Ulf Benjaminsson.
Copyright (C) 2023-Present LightPress LLC.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA