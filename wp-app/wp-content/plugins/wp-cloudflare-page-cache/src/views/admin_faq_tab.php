<div class="main_section_header first_section">
	<h3><?php _e( 'Common questions', 'wp-cloudflare-page-cache' ); ?></h3>
</div>

<div class="swcfpc_faq_accordion">

	<h3 class="swcfpc_faq_question"><?php _e( 'How it works?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'Super Page Cache generate static HTML version of the webpages inside your site. This is a great option and can increase your site speed dramatically.', 'wp-cloudflare-page-cache' ); ?></p>
		<p><?php _e( 'Our page cache system works way better than any other disk cache system and plugins out there in the market. In short now you don\'t need to install any other caching plugin in conjunction with Super Page Cache, as the plugin can now handle Cloudflare caching and disk caching.', 'wp-cloudflare-page-cache' ); ?></p>

	</div>

	<h3 class="swcfpc_faq_question"><?php _e( 'Can I use this plugin to take advantage of Cloudflare\'s Cache Everything rule, bypassing the cache for logged in users even if I\'m using the free plan?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'Yes. This is the main purpose of this plugin.', 'wp-cloudflare-page-cache' ); ?></p>

	</div>


	<h3 class="swcfpc_faq_question"><?php _e( 'What is the <strong>swcfpc=1</strong> parameter I see to every internal links when I\'m logged in?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'It is a cache buster. Allows you, while logged in, to bypass the Cloudflare cache for pages that could be cached.', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'It is added to all internal links for logged in users only. It is disabled in Worker mode.', 'wp-cloudflare-page-cache' ); ?></p>

	</div>


	<h3 class="swcfpc_faq_question"><?php _e( 'Can I restore all Cloudflare settings as before the plugin activation?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'Yes you can by click on <strong>Reset all</strong> button.', 'wp-cloudflare-page-cache' ); ?></p>

	</div>

	<h3 class="swcfpc_faq_question"><?php _e( 'Why all the pages have "BYPASS" as the cf-cache-status?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'Cloudflare does not add in cache pages with cookies because they can have dynamic contents. If you want to force this behavior, strip out cookies by enabling the option <strong>Strip response cookies on pages that should be cached.</strong>', 'wp-cloudflare-page-cache' ); ?></p>

	</div>

	<h3 class="swcfpc_faq_question"><?php _e( 'What is Preloader and how it works?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'The preloader is a simple crawler that is started in the background and aims to preload pages so that they are cached immediately.', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'Once the preloader is enabled, you need to specify which preloading logic among those available to use. You can choose a combination of options (sitemaps, WordPress menus, recent published posts, etc ..).', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'It is also possible to automatically start the preloader when the cache is cleared.', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'In order to avoid overloading the server, only one preloader will be started at once. It is therefore not possible to start more than one preloader at the same time. Furthermore, between one preload and the other there will be a waiting time of 2 seconds.', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'If you want to run the preloader at middle of the night when you have low users, you can run the preloader over CRON job as well.', 'wp-cloudflare-page-cache' ); ?></p>

	</div>


	<h3 class="swcfpc_faq_question"><?php _e( 'What is the difference between Purge Cache and Force purge everything actions?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'If the <strong>Purge HTML pages only</strong> option is enabled, clicking on the <strong>PURGE CACHE</strong> button only HTML pages already in cache will be deleted .', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'If for some reason you need to delete static files (such as CSS, images and scripts) from Cloudflare\'s cache, you can do so by clicking on <strong>Force purge everything</strong>', 'wp-cloudflare-page-cache' ); ?></p>

	</div>


</div>


<!-- COMMON ISSUES FAQs -->
<div class="main_section_header">
	<h3><?php _e( 'Common issues', 'wp-cloudflare-page-cache' ); ?></h3>
</div>

<div class="swcfpc_faq_accordion">

	<h3 class="swcfpc_faq_question"><?php _e( 'Error: Invalid request headers (err code: 6003 )', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'This is a Cloudflare authentication error. <strong>If you chose the API Key as the authentication mode</strong>, make sure you have entered the correct email address associated with your Cloudflare account and the correct Global API key (not your Cloudflare password!).', 'wp-cloudflare-page-cache' ); ?></p>
		<p><?php _e( '<strong>If you are chose the API Token as the authentication mode</strong>, make sure you have entered the correct token, with all the required permissions, and the domain name exactly as it appears in your Cloudflare account.', 'wp-cloudflare-page-cache' ); ?></p>
		<p><?php _e( 'Also make sure you haven\'t entered the API Token instead of the API key or vice versa', 'wp-cloudflare-page-cache' ); ?></p>

	</div>


	<h3 class="swcfpc_faq_question"><?php _e( 'Error: Page Rule validation failed: See messages for details. (err code: 1004 )', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'Login to Cloudflare, click on your domain and go to Page Rules section. Check if a <strong>Cache Everything</strong> page rule already exists for your domain. If yes, delete it. Now from the settings page of Super Page Cache, disable and re-enable the cache.', 'wp-cloudflare-page-cache' ); ?></p>

	</div>


	<h3 class="swcfpc_faq_question"><?php _e( 'Error: Actor \'com.cloudflare.api.token.\' requires permission \'com.cloudflare.api.account.zone.list\' to list zones (err code: 0 )', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'If you are using an <strong>API Token</strong>, check that you entered the domain name <strong>exactly</strong> as on Cloudflare', 'wp-cloudflare-page-cache' ); ?></p>

	</div>


	<h3 class="swcfpc_faq_question"><?php _e( 'PHP Warning: Cannot modify header information - headers already sent in /wp-content/advanced-cache.php', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'Maybe you have some extra newline or space in other PHP files executed before advanced-cache.php such like must-use plugins or wp-config.php.', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'Check those files. Try the following:', 'wp-cloudflare-page-cache' ); ?></p>

		<ol>
			<li><?php _e( 'If you have any code inside mu-plugin take them out of that folder. Check your server error log and test to see if you are getting the header errors still.', 'wp-cloudflare-page-cache' ); ?></li>
			<li><?php _e( 'If not then check the codes inside the PHP files and see if any of them has an extra newline or space at the end of the script. If they have delete those new lines and spaces and test.', 'wp-cloudflare-page-cache' ); ?></li>
			<li><?php _e( 'If still doesn\'t work check if any of the scripts inside mu-plugins have print, echo, printf, vprintf etc. in the code. For more details check:', 'wp-cloudflare-page-cache' ); ?>
				<a href="https://stackoverflow.com/a/8028987/2308992" target="_blank"
				   rel="external nofollow noopener noreferrer">https://stackoverflow.com/a/8028987/2308992</a>
			</li>
		</ol>

		<p><?php _e( 'In short, the problem is not coming from this plugin but some <strong>mu-plugin</strong> is sending the header before advanced-cache.php can. That\'s causing the issue. We have thoroughly tested this.', 'wp-cloudflare-page-cache' ); ?></p>

	</div>


	<h3 class="swcfpc_faq_question"><?php _e( 'Custom login page does not redirect when you login', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'Exclude the custom login URL by adding it into the textarea near the option <strong>Prevent the following URIs to be cached</strong>, then save, purge the cache, wait 30 seconds and retry.', 'wp-cloudflare-page-cache' ); ?></p>

	</div>

</div>


<!-- THIRD PARTY INTEGRATIONS FAQs -->
<div class="main_section_header">
	<h3><?php _e( 'Third-party integrations', 'wp-cloudflare-page-cache' ); ?></h3>
</div>


<div class="swcfpc_faq_accordion">

	<h3 class="swcfpc_faq_question"><?php _e( 'Does it work with Litespeed Server?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'Yes but if you are using a LiteSpeed Server version lower than 6.0RC1, disable the option <strong>Overwrite the cache-control header for WordPress\'s pages using web server rules</strong>. You can keep this option enabled for Litespeed Server versions equal or higher then 6.0RC1', 'wp-cloudflare-page-cache' ); ?></p>

	</div>

	<h3 class="swcfpc_faq_question"><?php _e( 'How does this plugin work with Kinsta Cache?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'If you are using a Kinsta hosting, you can integrate this plugin to work with Kinsta Server Level Caching and to ensure when you Purge Cache via this plugin, it not only purges the cache on Cloudflare but also on Kinsta Cache. You can enable this feature by going to the "Third Party" tab of the plugin settings and enabling the "Automatically purge the Kinsta cache when Cloudflare cache is purged" option. It is also recommended that if you are taking advantage of the Kinsta Server Caching (Recommended), please ensure that the <string>Fallback Cache</strong> system provided by this plugin is turned OFF.', 'wp-cloudflare-page-cache' ); ?></p>
		<p><?php _e( 'After purging the cache from our plugin, it gets deleted from both Kinsta & Cloudflare Cache. Now when you visit the webpage for the first time after purging the cache, you will get cache response headers as MISS/EXPIRED etc. for both Cloudflare and Kinsta cache.', 'wp-cloudflare-page-cache' ); ?></p>
		<p><?php _e( 'At this point after receiving the first request both Cloudflare & Kinsta caches the page on their own platforms respectively. But do note that when Cloudflare is caching the first request at this point the <code>x-kinsta-cache</code> header is of status MISS. For the second request when Cloudflare serves the page from it\'s own CDN Edge servers without sending the request to the origin server, it keeps showing the <code>x-kinsta-cache</code> cache header as MISS. Because when Cloudflare cached the page the response header was MISS but Kinsta caching system has already cached it upon receiving the first request.', 'wp-cloudflare-page-cache' ); ?></p>
		<p><?php _e( 'Now if you purge the Cloudflare cache only (on Cloudflare dashboard) or enable development mode in the Cloudflare dashboard so that the request doesn\'t get served from Cloudflare CDN, you will see the <code>x-kinsta-cache</code> showing as HIT, because this time the request is going to your origin server and you are seeing the updated response headers that is being served by the server.', 'wp-cloudflare-page-cache' ); ?></p>

	</div>


	<h3 class="swcfpc_faq_question"><?php _e( 'Can I use this plugin together with Litespeed Cache?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'Yes you can! You have only to disable its page caching functionality. To do this:', 'wp-cloudflare-page-cache' ); ?></p>
		<ol>
			<li><?php _e( 'Go to <strong>Litespeed Cache > Cache</strong>', 'wp-cloudflare-page-cache' ); ?></li>
			<li><?php _e( 'Click on <strong>OFF</strong> near <strong>Enable Cache</strong>', 'wp-cloudflare-page-cache' ); ?></li>
			<li><?php _e( 'Click on <strong>Save Changes</strong> button', 'wp-cloudflare-page-cache' ); ?></li>
		</ol>

		<p>Then:</p>

		<ol>
			<li><?php _e( 'Enter to the settings page of this plugin', 'wp-cloudflare-page-cache' ); ?></li>
			<li><?php _e( 'Click on <strong>Third Party</strong> tab', 'wp-cloudflare-page-cache' ); ?></li>
			<li><?php _e( 'Scroll to <strong>Litespeed Cache Settings</strong> section', 'wp-cloudflare-page-cache' ); ?></li>
			<li><?php _e( 'Enable the option <strong>Automatically purge the cache when LiteSpeed Cache flushs all caches</strong>', 'wp-cloudflare-page-cache' ); ?></li>
			<li><?php _e( 'Purge the cache', 'wp-cloudflare-page-cache' ); ?></li>
		</ol>

	</div>

	<h3 class="swcfpc_faq_question"><?php _e( 'Can I use this plugin together with other page caching plugins such like Cache Enabler, WP Super Cache and WP Fastest Cache?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'No. The support for these plugin was removed because you can use the fallback cache option of this plugin if you want to use a standard page cache behind Cloudflare.', 'wp-cloudflare-page-cache' ); ?></p>
		<p><?php _e( 'In order to avoid conflicts, it is strongly recommended to use only this plugin as page caching system.', 'wp-cloudflare-page-cache' ); ?></p>

	</div>


	<h3 class="swcfpc_faq_question"><?php _e( 'Can I use this plugin together with Varnish Cache?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'Yes but you don\'t need it. If you want to use a standard page cache behind Cloudflare, enable the fallback cache option of this plugin.', 'wp-cloudflare-page-cache' ); ?></p>
		<p><?php _e( 'In order to avoid conflicts, it is strongly recommended to use only this plugin as page caching system.', 'wp-cloudflare-page-cache' ); ?></p>

	</div>

</div>


<!-- CACHE FAQs -->
<div class="main_section_header">
	<h3><?php _e( 'Cache questions and issues', 'wp-cloudflare-page-cache' ); ?></h3>
</div>

<div class="swcfpc_faq_accordion">

	<h3 class="swcfpc_faq_question"><?php _e( 'WP Admin or WP Admin Bar is being cached', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'This should never happen. If it happens, it is because the value of the <strong>Cache-Control</strong> response header is different from that of the <strong>X-WP-CF-Super-Cache-Cache-Control</strong> response header (make sure it is the same).', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'If you are using <strong>LiteSpeed Server version lower than 6.0RC1</strong>, make sure the <strong>Overwrite the cache-control header for WordPress\'s pages using web server rules</strong> option is <strong>disabled</strong>. If not, disable it, clear your cache and try again. You can keep this option enabled for Litespeed Server versions equal or higher then 6.0RC1', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'If you are not using LiteSpeed Server and you are using this plugin together with other performance plugins, enable the <strong>Overwrite the cache-control header for WordPress\'s pages using web server rules</strong> option, clear the cache and try again.', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'If this doesn\'t work, you can always choose to activate the <strong>Force cache bypassing for backend with an additional Cloudflare page rule</strong> option or to change the caching mode by activating the <strong>Worker mode</strong> option.', 'wp-cloudflare-page-cache' ); ?></p>

	</div>


	<h3 class="swcfpc_faq_question"><?php _e( 'Why changes are never showed when visiting the website?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'First of all enable the log mode and check if in the log file, after clicking on the update button on the edit page, you see the information about the cache purging.', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'If so, good news: the plugin is working correctly. If not, open a ticket on the support forum.', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'If you have enabled the <strong>Page cache</strong>, make sure you have also enabled the option <strong>Automatically purge the Page cache when Cloudflare cache is purged</strong>.', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'If you are using <strong>Varnish cache</strong>, make sure you have also enabled the option <strong>Automatically purge Varnish cache when the cache is purged</strong>.', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'Disable any other page caching plugins or services. Only use this plugin to cache HTML pages.', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'If you still don\'t see the changes despite everything, the problem is to be found elsewhere. For example wrong configuration of wp-config.php.', 'wp-cloudflare-page-cache' ); ?></p>

	</div>


	<h3 class="swcfpc_faq_question"><?php _e( 'URLs with swcfpc=1 parameter getting indexed', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'In very rare cases, it may happen that some third-party plugin stores the cache buster parameter in the database, and therefore this is then also displayed to users who are not logged in and therefore to search engine bots.', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'If this happened on your site, enable the <strong>SEO redirect</strong> inside the plugin settings page under the <strong>Advanced</strong> tab. This will auto redirect any URLs which has <em>swcfpc=1</em> in it to it\'s normal URL when any non-logged in user clicks on that link, avoiding duplicate content problems.', 'wp-cloudflare-page-cache' ); ?></p>

	</div>


	<h3 class="swcfpc_faq_question"><?php _e( 'I am seeing ?swcfpc=1 at the front end URLs even when I\'m not logged in', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'Some page builders might copy the admin side links and show that on the front end for all users. This happens because these page builders do not follow the standard WordPress coding guidelines to fetch URLs and instead copy hard code URLs. If you are facing this issue, you can easily fix this by enabling the <strong>SEO redirect</strong> option inside the plugin settings page under the <strong>Advanced</strong> tab. This will auto redirect any URLs which has <em>swcfpc=1</em> in it to it\'s normal URL when any non-logged in user clicks on that link.', 'wp-cloudflare-page-cache' ); ?></p>

	</div>


	<h3 class="swcfpc_faq_question"><?php _e( 'Even after enabling the plugin I\'m seeing CF Cache Status DYNAMIC for all the pages', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'There are a couple of things that can cause this issue and tell Cloudflare not to cache everything. If you are facing this issue, please check the following this:', 'wp-cloudflare-page-cache' ); ?></p>

		<ul>
			<li><?php _e( 'Make sure that <strong>Development Mode</strong> is NOT enabled for your domain inside Cloudflare.', 'wp-cloudflare-page-cache' ); ?></li>
			<li><?php _e( 'Make sure you have the orange cloud icon enabled inside the Cloudflare DNS settings for your main domain A record and WWW record.', 'wp-cloudflare-page-cache' ); ?></li>
			<li><?php _e( 'Make sure you do not have any other page rules that might conflict with the Cache Everything rule', 'wp-cloudflare-page-cache' ); ?></li>
			<li><?php _e( 'Make sure you do not have any Cloudflare Worker code deployed that might overwrite the normal cache policies', 'wp-cloudflare-page-cache' ); ?></li>
			<li><?php _e( 'Make sure you don\'t have any plugins which might be adding a lot of unnecessary Cookies in your request header for no reason. If you have any cookie consent plugin or any similar things, try disabling those plugins and check again. You can also enable the <strong>Strip response cookies on pages that should be cached</strong> option under the <strong>Cache</strong> tab to see if this resolves your issue. If it does, that means there are plugin which is injecting cookies into your site header and when Cloudflare sees these Cookies, it think that the page has dynamic content, so it doesn\'t cache everything.', 'wp-cloudflare-page-cache' ); ?></li>
		</ul>

	</div>


	<h3 class="swcfpc_faq_question"><?php _e( 'Should I enable the cURL mode for Disk Cache?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'In most cases you don\'t need to enable the cURL mode for fallback cache. If you don\'t enable the cURL mode, the plugin will use the standard WordPress <code>advanced-cache.php</code> method to generate the Page cache. This system works well in almost all the cases, also this cache generation mechanism is very fast and don\'t eat much server resource. On the other hand the cURL mode is useful in some edge cases where the <code>advanced-cache.php</code> mode of fallback cache is unable to generate proper HTML for the page. This is rare, but the cURL option is given just for these edge cases.', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'One of the benefit of the cURL mode is that as it uses server level cRUL instead of <code>advanced-cache.php</code> to generate the page cache, the cache files comes out very stable and without any issues. But then again if your enable the cURL mode, that means cURL will fetch every page of your website (which are not excluded from fallback cache) to generate the Page cache and each cURL request is going to increase some server load. So, if you have a small to medium site with not many pages, you can definitely use the cURL mode of fallback cache. But for large high traffic website, unless you have more than enough server resource to handle so many  cURL requests, we will recommend stick to using the default <code>advanced-cache.php</code> option which works flawlessly anyway.', 'wp-cloudflare-page-cache' ); ?></p>

	</div>


	<h3 class="swcfpc_faq_question"><?php _e( 'What\'s the benefit of using Cloudflare worker over using page rules?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'Cloudflare Workers is an amazing technology which allows us to run complicated logical operations inside the Cloudflare edges. So, basically before Cloudflare picks up the request, it passes through the Cloudflare worker and then we can programmatically tell Cloudflare what to do and what not to do. This gives us great control over each and every request and how Cloudflare should handle them.', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'The Page Rule option of <strong>Cache Everything</strong> works perfectly in almost every cases but in some situations due to some server config or other reasons, the headers that this plugin sets for each requests, does not get respected by the server and gets stripped out. In those edge case scenarios Cloudflare Worker mode can be really helpful over the page rules mode.', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'So, in terms of speed, you might not see a difference but the Worker mode is there just for the cases when the page rule mode is not working correctly across the whole website (frontend & backend).', 'wp-cloudflare-page-cache' ); ?></p>

	</div>


	<h3 class="swcfpc_faq_question"><?php _e( 'Isn\'t Cloudflare Workers are chargeable?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'Yes & No. It depends on how many visitors your site have.  Cloudflare Workers have a free plan which allows <strong>100,000 requests/day</strong> for FREE. But if your site has more requests than that per day, then you need to opt for the paid plan of <strong>$5/month</strong> which will give you <strong>10 Million Requests/month</strong> and after that <strong>$0.50 per additional 1 Million requests</strong>.', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'Please note that, all requests first get intercepted by Cloudflare Worker before Cloudflare decide what to do with that request, so whether your requests gets served from Cloudflare CDN Cache or from origin, it will be counted towards your Worker usage limit.', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'If you have a small to medium site, you can easily use Cloudflare Worker without hesitating about payment as you will not get pass the free quota, but as you grow, and if you still want to use the Cloudflare Workers, you might have to pay for it. Cloudflare Workers are so much more and has so much power that if you are truly taking advantage of the Cloudflare Workers, your can do a lot of cool things. So, in short if you have a big high traffic site and you don\'t want to pay extra for the Cloudflare Workers, you should just stick with the Page Rules option.', 'wp-cloudflare-page-cache' ); ?></p>

	</div>


	<h3 class="swcfpc_faq_question"><?php _e( 'How is the Worker Code is deployed to my Cloudflare Account?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'After you enter the Cloudflare API details, we push our worker code using Cloudflare API to your Cloudflare account. You can find our Cloudflare Worker code inside the plugin\'s <code>/assets/js/worker_template.js</code> path.', 'wp-cloudflare-page-cache' ); ?></p>

	</div>


	<h3 class="swcfpc_faq_question"><?php _e( 'Can I use this plugin with WP CLI?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'Yes. Commands list: <strong>wp cfcache</strong>', 'wp-cloudflare-page-cache' ); ?></p>

	</div>

</div>


<!-- ADVANCED FAQs -->
<div class="main_section_header">
	<h3><?php _e( 'Advanced questions', 'wp-cloudflare-page-cache' ); ?></h3>
</div>

<div class="swcfpc_faq_accordion">

	<h3 class="swcfpc_faq_question"><?php _e( 'How to use the <strong>Remove Cache Buster Query Parameter</strong> option? Is there any implementation guides?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'That is a super advanced option to use Cache Everything Cloudflare page rules without the swcfpc cache buster query parameter. This option is only for super advanced users who are confortable adding custom rules in their Cloudflare dashbord. If you are that person, this option probably will not be a good fit for you.', 'wp-cloudflare-page-cache' ); ?></p>
		<p><?php _e( 'Also when using this option please keep in mind that some rules can only be implemented in Cloudflare Business and Enterprise account users. So, if you are a CLoudflare Free or Pro plan users, you might not be able to implement some rules.', 'wp-cloudflare-page-cache' ); ?></p>
		<p><?php _e( 'Please check <strong><a href="https://gist.github.com/isaumya/af10e4855ac83156cc210b7148135fa2" target="_blank" rel="external noopener noreferrer">this implementation guide</a></strong> which comes all types of Cloudflare accounts before enabling this option.', 'wp-cloudflare-page-cache' ); ?></p>
		<p><?php _e( 'Without implementioned these rules properly if you enable this option, it will break the cache functionality of your website.', 'wp-cloudflare-page-cache' ); ?></p>

	</div>

	<h3 class="swcfpc_faq_question"><?php _e( 'Can I change <strong>swcfpc</strong> with another one?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'Yes you can by adding the PHP constant <strong>SWCFPC_CACHE_BUSTER</strong> to your wp-config.php', 'wp-cloudflare-page-cache' ); ?></p>

	</div>

	<h3 class="swcfpc_faq_question"><?php _e( 'Can I configure this plugin using PHP constants?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'Yes you can use the following PHP constants:', 'wp-cloudflare-page-cache' ); ?></p>

		<ul>
			<li>
				<strong>SWCFPC_CACHE_BUSTER</strong>, <?php _e( 'cache buster name. Default: swcfpc', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li>
				<strong>SWCFPC_CF_API_ZONE_ID</strong>, <?php _e( 'Cloudflare zone ID', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li>
				<strong>SWCFPC_CF_API_KEY</strong>, <?php _e( 'API Key to use', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li>
				<strong>SWCFPC_CF_API_EMAIL</strong>, <?php _e( 'Cloudflare email to use (API Key authentication mode)', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li>
				<strong>SWCFPC_CF_API_TOKEN</strong>, <?php _e( 'API Token to use', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li>
				<strong>SWCFPC_PRELOADER_MAX_POST_NUMBER</strong>, <?php _e( 'max pages to preload. Default: 50', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li>
				<strong>SWCFPC_CF_WOKER_ENABLED</strong>, <?php _e( 'true or false', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li>
				<strong>SWCFPC_CF_WOKER_ID</strong>, <?php _e( 'CF Worker ID', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li>
				<strong>SWCFPC_CF_WOKER_ROUTE_ID</strong>, <?php _e( 'route ID', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li>
				<strong>SWCFPC_CF_WOKER_FULL_PATH</strong>, <?php _e( 'full path to worker template to use', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li>
				<strong>SWCFPC_CURL_TIMEOUT</strong>, <?php _e( 'timeout in seconds for cURL calls. Default: 10', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li>
				<strong>SWCFPC_PURGE_CACHE_LOCK_SECONDS</strong>, <?php _e( 'time in seconds for cache purge lock. Default: 10', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li>
				<strong>SWCFPC_PURGE_CACHE_CRON_INTERVAL</strong>, <?php _e( 'time interval in seconds for the purge cache cronjob. Default: 10', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li>
				<strong>SWCFPC_HOME_PAGE_SHOWS_POSTS</strong>, <?php _e( 'if the front page a.k.a. the home page of the website showing latest posts. Default: true (bool)', 'wp-cloudflare-page-cache' ); ?>
			</li>
		</ul>

	</div>

	<h3 class="swcfpc_faq_question"><?php _e( 'What hooks can I use?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p>Actions:</p>

		<ul>
			<li><strong>swcfpc_purge_all</strong>, no
				arguments. <?php _e( 'Fired when whole caches are purged.', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li><strong>swcfpc_purge_urls</strong>, 1 argument:
				$urls. <?php _e( 'Fired when caches for specific URLs are purged.', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li>
				<strong>swcfpc_cf_purge_whole_cache_before</strong>, <?php _e( 'no arguments. Fired before purge the Cloudflare whole cache.', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li>
				<strong>swcfpc_cf_purge_whole_cache_after</strong>, <?php _e( 'no arguments. Fired after the Cloudflare whole cache is purged.', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li><strong>swcfpc_cf_purge_cache_by_urls_before</strong>, 1 argument:
				$urls. <?php _e( 'no arguments. Fired before purge the Cloudflare cache for specific URLs only.', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li><strong>swcfpc_cf_purge_cache_by_urls_after</strong>, 1 argument:
				$urls. <?php _e( 'no arguments. Fired after the Cloudflare cache for specific URLs only is purged.', 'wp-cloudflare-page-cache' ); ?>
			</li>
		</ul>

		<p>Filters:</p>

		<ul>
			<li>
				<strong>swcfpc_bypass_cache_metabox_post_types</strong>, <?php _e( '$allowed_post_types (Array). You can use this filter to ensure that the bypass cache metabox is also shown for your own custom post types. Example code link: https://wordpress.org/support/topic/disable-page-caching-for-specific-pages-with-cpt/#post-16824221', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li>
				<strong>swcfpc_fc_modify_current_url</strong>, <?php _e( '$current_uri (string). You can use this filter to modify the url that will be used by the fallback cache. For example you can remove many query strings from the url. Please note that this filter will return the URL without the trailing slash.', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li>
				<strong>swcfpc_cache_bypass</strong>, <?php _e( 'one arguments. Return true to bypass the cache.', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li>
				<strong>swcfpc_post_related_url_init</strong>, <?php _e( '$listofurls (array), $postId. Fired when creating the initial array that holds the list of related urls to be purged when a post is updated. Show return array of full URLs (e.g. https://example.com/some-example/) that you want to include in the related purge list.', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li>
				<strong>swcfpc_normal_fallback_cache_html</strong>, <?php _e( '[One Arguments] : $html. This filter is fired before storing the page HTML to fallback cache. So, this gives you the ability to make changes to the HTML that gets saved within the fallback cache. This filter is fired when the fallback cache is generated normally via the advanced-cache.php file.', 'wp-cloudflare-page-cache' ); ?>
			</li>
			<li>
				<strong>swcfpc_curl_fallback_cache_html</strong>, <?php _e( '[One Arguments] : $html. This filter is fired before storing the page HTML to fallback cache. So, this gives you the ability to make changes to the HTML that gets saved within the fallback cache. This filter is fired when the fallback cache is generated normally via cURL method.', 'wp-cloudflare-page-cache' ); ?>
			</li>
		</ul>

	</div>


	<h3 class="swcfpc_faq_question"><?php _e( 'Can I use my own worker code along with the default worker code this plugin uses?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'Unfortunately, Cloudflare allows one worker per route. So, as long as our worker is setup in the main route, you cannot use your own worker code in the same route. But you can take advantage of <code>SWCFPC_CF_WOKER_FULL_PATH </code> PHP constant to provide the full path of your own custom JavaScript file.', 'wp-cloudflare-page-cache' ); ?></p>

		<p><?php _e( 'In this way you can take a look at inside the plugin\'s <code>/assets/js/worker_template.js</code> path and see the Worker code we are using by default. Then you can copy that worker template file in your computer and extend it to add more features and conditionality that you might need in your project. Once you are done with your Worker code, you can simply point your custom Worker template JavaScript file inside <code>wp-config.php</code> using the <code>SWCFPC_CF_WOKER_FULL_PATH </code> PHP constant and the plugin will use your Worker file to create the worker in your website route instead of using the default Worker code. Here is an example of how to use the PHP constant inside your <code>wp-config.php</code>. Please make sure you provide the absolute path of your custom Worker file.', 'wp-cloudflare-page-cache' ); ?></p>

		<pre>define('SWCFPC_CF_WOKER_FULL_PATH', '/home/some-site/public/wp-content/themes/your-theme/assets/js/my-custom-cf-worker.js');</pre>

		<p><strong style="color:#c0392b">Please
				note</strong> <?php _e( 'that for 99.999% of users the default Worker code will work perfectly if they choose to use the Worker mode over the Page Rule mode. This option will be provided <strong>only for Super Advanced Knowledgeable Users</strong> who know exactly what they are doing and which will lead to what. General users should <strong>avoid</strong> tinkering with the Worker Code as this might break your website if you don\'t know what you are doing.', 'wp-cloudflare-page-cache' ); ?>
		</p>

	</div>

	<h3 class="swcfpc_faq_question"><?php _e( 'Can I purge the cache programmatically?', 'wp-cloudflare-page-cache' ); ?></h3>
	<div class="swcfpc_faq_answer">

		<p><?php _e( 'Yes. To purge the whole cache use the following PHP command:', 'wp-cloudflare-page-cache' ); ?></p>

		<pre>do_action("swcfpc_purge_cache");</pre>

		<p><?php _e( 'To purge the cache for a subset of URLs use the following PHP command:', 'wp-cloudflare-page-cache' ); ?></p>

		<pre>do_action("swcfpc_purge_cache", array("https://example.com/some-page/", "https://example.com/other-page/"));</pre>

	</div>

</div>

