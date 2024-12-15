=== Disable XML-RPC-API ===

Plugin Name: Disable XML-RPC-API
Plugin URI: https://neatma.com
Contributors: aminnz,neatmarketing
Description: Simple plugin to disable XML-RPC API and X-Pingback for faster and more secure website.
Tags: disable xml-rpc, xmlrpc, disable xmlrpc, pingback, stop brute force attacks
Tested up to: 6.7
Requires at least: 5.0
Author: Neatma
Author URI: https://neatma.com
Donate link: http://neatma.com/wpsg-plugin
License: GPLv2

A simple and lightweight plugin to disable XML-RPC API, X-Pingback and pingback-ping in WordPress 3.5+ for a faster and more secure website

== Description ==

Protect your website from xmlrpc brute-force attacks,DOS and DDOS attacks, this plugin disables the XML-RPC and trackbacks-pingbacks on your WordPress website.

**PLUGIN FEATURES**
(These are options you can enable or disable each one)

* Disable access to xmlrpc.php file using .httacess file 
* Automatically change htaccess file permission to read-only (0444)
* Disable X-pingback to minimize CPU usage 
* Disable selected methods from XML-RPC
* Remove pingback-ping link from header
* Disable trackbacks and pingbacks to avoid spammers and hackers
* Rename XML-RPC slug to whatever you want
* Black list IPs for XML-RPC
* White list IPs for XML-RPC
* Some options to speed-up your wordpress website
* Disable JSON REST API
* Hide WordPress Version
* Disable built-in WordPress file editor
* Disable wlw manifest
* And some other options



**What is XMLRPC**

XML-RPC, or XML Remote Procedure Call is a protocol which uses XML to encode its calls and HTTP as a transport mechanism.
Beginning in WordPress 3.5, XML-RPC is enabled by default. Additionally, the option to disable/enable XML-RPC was removed. For various reasons, site owners may wish to disable this functionality. This plugin provides an easy way to do so.

**Why you should disable XML-RPC**
*Xmlrpc has two main weaknesses*

* Brute force attacks:
 Attackers try to login to WordPress using xmlrpc.php with as many username/password combinations as they can enter. A method within xmlrpc.php allows the attacker to use a single command (system.multicall) to guess hundreds of passwords. Daniel Cid at Sucuri described it well in October 2015: “With only 3 or 4 HTTP requests, the attackers could try thousands of passwords, bypassing security tools that are designed to look and block brute force attempts.”
* Denial of Service Attacks via Pingback:
 Back in 2013, attackers sent Pingback requests through xmlrpc.php of approximately 2500 WordPress sites to “herd (these sites) into a voluntary botnet,” according to Gur Schatz at Incapsula. “This gives any attacker a virtually limitless set of IP addresses to Distribute a Denial of Service attack across a network of over 100 million WordPress sites, without having to compromise them.”



== Installation ==

1. Upload the disable-xml-rpc directory to the `/wp-content/plugins/` directory in your WordPress installation
2. Activate the plugin through the 'Plugins' menu in WordPress
3. XML-RPC-API is now disabled!

To re-enable XML-RPC, just deactivate the plugin through the 'Plugins' menu.

== Frequently Asked Questions ==

= Is there an admin interface for this plugin? =

Yes, You can find the "XML-RPC Security" in your admin menu.

= How do I know if the plugin is working? =

There are three easy methods for checking if XML-RPC is off:
1. Easiest way is going to this url: http://yourdomain/xmlrpc.php enter your domain name instead of 'yourdomain' if you see "Access forbidden!" or "403 error" it's working.
2. First, try using an XML-RPC client, like the official WordPress mobile apps. The WordPress mobile app should tell you that "XML-RPC services are disabled on this site" if the plugin is activated.
3. Or you can try the XML-RPC Validator, written by Danilo Ercoli of the Automattic Mobile Team - the tool is available at [http://xmlrpc.eritreo.it/](http://xmlrpc.eritreo.it/) with a blog post about it at [http://daniloercoli.com/2012/05/15/wordpress-xml-rpc-endpoint-validator/](http://daniloercoli.com/2012/05/15/wordpress-xml-rpc-endpoint-validator/). Keep in mind that you want the validator to fail and tell you that XML-RPC services are disabled.

= Something doesn't seem to be working correctly =

If the plugin is activated, but XML-RPC appears to still be working ... OR ... the plugin is deactivated, but XML-RPC is not working, then it's possible that another plugin or theme function is affecting the plugin functions.

== Screenshots ==
screenshot-1.png
screenshot-2.jpg
== Changelog ==

= 1.0.0 =
* Initial release

= 1.0.1 =
* Fix bugs

= 1.0.5 =
* Remove pingback link tag in header
* Add ability to fix htaccess file permission

= 1.0.6 =
* Fix warnings for htaccess permission

= 1.0.7 =
* Fix blank page when using W3 Total Cache and some other cache plugins

= 1.0.8 =
* Fix code conflict with Autoptimize plugin

= 1.0.9 =
* Wordpress 5.7 compatible
* Fix some issues

= 2.0.0 =
* Fix code conflict with some other plugin
* Fix hiding data in WooCommerce Product Tabs

= 2.1.0 =
*Major Update
*Add "XML-RPC Security"settings menu
*Add some new features
*Fix plugin deactivation bug

= 2.1.1 =
* Add new feature fix hotlinks
* Change notif timing

= 2.1.2 = 
* Add an option to disable auto change htaccess permission
* Fix "DISALLOW_FILE_EDIT" warning
* Wordpress 5.8 compatibility

= 2.1.3 =
* Fix compatibility issue with WordPress 5.9
* Fix htaccess cleaning function 

= 2.1.4 =
* Fix some minor bugs
* Refactor the entire codes 
* Add a fallback function for situations htaccess is not working

= 2.1.4.2 =
* Hotfix for error on update 

= 2.1.4.3 =
* Hotfix for error on removing v metadata 

= 2.1.4.4 =
* Fix warning undefined variable $htaccess_code when disable hotlink fix is off
* Fix warning Undefined array key “plugins” on PHP 8+

= 2.1.4.5 =
* Fix removing vpingback header issue in the last major update
* Update tested up to wp 6.1

= 2.1.4.7 =
* Fix issues on vuninstallation hook
* Minor improvements on admin review notification 

= 2.1.4.8 =
* Fix bug v wp reset API option 

= 2.1.4.9 =
* Update Jetpack default whitelist IPs
* Fix bug with update actions function
* Keep enabling WP RSS in default settings
* Test with WordPress 6.3 and update tested up to

= 2.1.5 =
* Hotfix for .htaccess error and disabling the admin notices

= 2.1.6 =
* Clean Up the plugin codes (remove unnecessary codes)
* Add VaultPress IPs to JetPack allowlist
* Test compatibility with WordPress 6.6.1

= 2.1.7 =
* Improve disable xmlrpc fallback method 
* Test compatibility with WordPress 6.7.1