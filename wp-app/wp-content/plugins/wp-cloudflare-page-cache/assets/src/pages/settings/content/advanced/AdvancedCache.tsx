import NginxLinkInstructions from "@/common/NginxLinkInstructions";
import Tooltip from "@/common/Tooltip";
import Badge from "@/components/Badge";
import Card, { CardContent, CardHeader } from "@/components/Card";
import Notice from "@/components/Notice";
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from "@/components/ui/accordion";
import ControlsGroup from "@/pages/settings/controls/ControlsGroup";
import { useSettingsStore } from "@/store/optionsStore";
import { createInterpolateElement } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

const AdvancedCache = () => {
  const { isToggleOn } = useSettingsStore();

  const controls = [
    {
      id: 'cf_fallback_cache_curl',
      type: 'toggle',
      label: __('Use cURL', 'wp-cloudflare-page-cache'),
      description: __('Use cURL instead of WordPress advanced-cache.php to generate the Page page. It can increase the time it takes to generate the Page cache but improves compatibility with other performance plugins.', 'wp-cloudflare-page-cache'),
    },
    {
      id: 'cf_fallback_cache_ttl',
      type: 'number',
      min: 0,
      label: `${__('Cache Lifespan', 'wp-cloudflare-page-cache')} (${__('seconds', 'wp-cloudflare-page-cache')})`,
      description: __('Enter 0 for no expiration.', 'wp-cloudflare-page-cache'),
      hide: !isToggleOn('cf_fallback_cache'),
    },
    {
      id: 'cf_fallback_cache_save_headers',
      type: 'toggle',
      label: __('Save response headers', 'wp-cloudflare-page-cache'),
      description: `${__('Save response headers together with HTML code.', 'wp-cloudflare-page-cache')} ${__('The following response header will never be saved:', 'wp-cloudflare-page-cache')}`,
      hide: !isToggleOn('cf_fallback_cache'),
      children: <pre className="text-xs mt-1">cache-control, set-cookie, X-WP-CF-Super-Cache*</pre>
    },
    {
      id: 'cf_fallback_cache_prevent_cache_urls_without_trailing_slash',
      type: 'toggle',
      label: __('Prevent to cache URLs without trailing slash', 'wp-cloudflare-page-cache'),
      hide: !isToggleOn('cf_fallback_cache'),
    },
    {
      id: 'cf_auto_purge_on_comments',
      type: 'toggle',
      label: __('Auto-Purge Cache on Comment Activity', 'wp-cloudflare-page-cache'),
      description: __('Automatically purge single post cache when a new comment is inserted into the database or when a comment is approved or deleted', 'wp-cloudflare-page-cache'),
    }, 
    {
      id: 'cf_auto_purge_on_upgrader_process_complete',
      type: 'toggle',
      label: __('Auto-purge on Updates', 'wp-cloudflare-page-cache'),
      description: __('Automatically purge the cache when the plugin update process is complete', 'wp-cloudflare-page-cache'),
    }, 
    {
      id: 'cf_strip_cookies',
      type: 'toggle', 
      label: __('Strip response cookies', 'wp-cloudflare-page-cache'),
      description: __('Cloudflare will not cache when there are cookies in responses unless you strip out them to overwrite the behavior. If the cache does not work due to response cookies and you are sure that these cookies are not essential for the website to works, enable this option.', 'wp-cloudflare-page-cache'),
    },
    {
      id: 'cf_cache_control_htaccess',
      type: 'toggle',
      label: __('Overwrite the cache-control header for WordPress\'s pages using web server rules', 'wp-cloudflare-page-cache'),
      description: (
        <Badge variant="destructive">
          {__('Writes into .htaccess', 'wp-cloudflare-page-cache')}
        </Badge>
      ),
      children: <div className="grid gap-2 mt-2">
        <Notice description={__('This option is useful if you use Super Page Cache together with other performance plugins that could affect the Cloudflare cache with their cache-control headers. It works automatically if you are using Apache as web server or as backend web server.', 'wp-cloudflare-page-cache')} />
        <OverwriteHeaderDescription />
      </div>
    },
    {
      id: 'cf_purge_only_html',
      type: 'toggle',
      label: __('Purge HTML pages only', 'wp-cloudflare-page-cache'),
      description: __('Purge only the cached HTML pages instead of the whole cache (assets & pages).', 'wp-cloudflare-page-cache'),
    },
    {
      id: 'cf_disable_cache_purging_queue',
      type: 'toggle',
      label: __('Disable cache purging using queue', 'wp-cloudflare-page-cache'),
      description: __('By default this plugin purge the cache after 10 seconds from the purging action, to avoid a high number of purge requests in case of multiple events triggered by third party plugins. This is done using a classic WordPress scheduled event. If you notice any errors regarding the scheduled intervals, you can deactivate this mode by enabling this option.', 'wp-cloudflare-page-cache')
    },
    {
      id: 'advanced_exclude_dynamic_content',
      type: 'checkbox-group',
      label: __('Don\'t cache the following dynamic contents:', 'wp-cloudflare-page-cache'),
      controls: [
        {
          id: 'cf_bypass_404',
          type: 'checkbox',
          label: <div className="flex items-center">{__('404 Page', 'wp-cloudflare-page-cache')} <Tooltip><code>is_404</code></Tooltip> </div>,
        },
        {
          id: 'cf_bypass_single_post',
          type: 'checkbox',
          label: <div className="flex items-center">{__('Single posts', 'wp-cloudflare-page-cache')} <Tooltip><code>is_single</code></Tooltip> </div>,
        },
        {
          id: 'cf_bypass_pages',
          type: 'checkbox',
          label: <div className="flex items-center">{__('Pages', 'wp-cloudflare-page-cache')} <Tooltip><code>is_page</code></Tooltip> </div>,
        },
        {
          id: 'cf_bypass_front_page',
          type: 'checkbox',
          label: <div className="flex items-center">{__('Front Page', 'wp-cloudflare-page-cache')} <Tooltip><code>is_front_page</code></Tooltip> </div>,
        },
        {
          id: 'cf_bypass_home',
          type: 'checkbox',
          label: <div className="flex items-center">{__('Home', 'wp-cloudflare-page-cache')} <Tooltip><code>is_home</code></Tooltip> </div>,
        },
        {
          id: 'cf_bypass_archives',
          type: 'checkbox',
          label: <div className="flex items-center">{__('Archives', 'wp-cloudflare-page-cache')} <Tooltip><code>is_archive</code></Tooltip> </div>,
        },
        {
          id: 'cf_bypass_tags',
          type: 'checkbox',
          label: <div className="flex items-center">{__('Tags', 'wp-cloudflare-page-cache')} <Tooltip><code>is_tag</code></Tooltip> </div>,
        },
        {
          id: 'cf_bypass_category',
          type: 'checkbox',
          label: <div className="flex items-center">{__('Categories', 'wp-cloudflare-page-cache')} <Tooltip><code>is_category</code></Tooltip> </div>,
        },
        {
          id: 'cf_bypass_feeds',
          type: 'checkbox',
          label: <div className="flex items-center">{__('Feeds', 'wp-cloudflare-page-cache')} <Tooltip><code>is_feed</code></Tooltip> </div>,
        },
        {
          id: 'cf_bypass_search_pages',
          type: 'checkbox',
          label: <div className="flex items-center">{
            __('Search Pages', 'wp-cloudflare-page-cache')} <Tooltip><code>is_search</code></Tooltip> </div>,
        },
        {
          id: 'cf_bypass_author_pages',
          type: 'checkbox',
          label: <div className="flex items-center">{__('Author Pages', 'wp-cloudflare-page-cache')} <Tooltip><code>is_author</code></Tooltip> </div>,
        },
        {
          id: 'cf_bypass_amp',
          type: 'checkbox',
          label: <div className="flex items-center">{__('AMP pages', 'wp-cloudflare-page-cache')} <Tooltip><code>is_amp</code></Tooltip> </div>,
        },
        {
          id: 'cf_bypass_ajax',
          type: 'checkbox',
          label: <div className="flex items-center">{__('Ajax requests', 'wp-cloudflare-page-cache')} <Tooltip><code>is_ajax</code></Tooltip> </div>,
        },
        {
          id: 'cf_bypass_query_var',
          type: 'checkbox',
          label: <div className="flex items-center">{__('Pages with query args', 'wp-cloudflare-page-cache')} <Tooltip><code>is_query_var</code></Tooltip> </div>,
        },
        {
          id: 'cf_bypass_wp_json_rest',
          type: 'checkbox',
          label: <div className="flex items-center">{__('WP JSON endpoints', 'wp-cloudflare-page-cache')} <Tooltip><code>is_wp_json_rest</code></Tooltip> </div>,
        }
      ],
    },
    {
      id: 'advanced_exclude_dynamic_content',
      type: 'checkbox-group',
      label: __('Don\'t cache the following static contents:', 'wp-cloudflare-page-cache'),
      stack: true,
      description: (
        <>
          {createInterpolateElement(
            __('<strong>If you use Nginx:</strong> It is recommended to add the browser caching rules that you find in the instructions <button>here</button>.', 'wp-cloudflare-page-cache'),
            {
              strong: <strong />,
              button: <NginxLinkInstructions />
            }
          )}

          <br />

          <Badge variant="destructive" className="mt-3">
            {__('Writes into .htaccess', 'wp-cloudflare-page-cache')}
          </Badge>
        </>
      ),
      controls: [
        {
          id: 'cf_bypass_sitemap',
          type: 'checkbox',
          label: __('XML sitemaps', 'wp-cloudflare-page-cache'),
        },
        {
          id: 'cf_bypass_file_robots',
          type: 'checkbox',
          label: __('Robots.txt', 'wp-cloudflare-page-cache'),
        },
      ]
    },
    {
      id: 'cf_post_per_page',
      type: 'number',
      min: 1,
      max: 100,
      label: __('Posts per page', 'wp-cloudflare-page-cache'),
      description: __('Enter how many posts per page (or category) the theme shows to your users. It will be use to clean up the pagination on cache purge.', 'wp-cloudflare-page-cache'),
    },
  ];

  const browserCachingControls = [
    {
      id: 'cf_browser_caching_htaccess',
      type: 'toggle',
      label: __('Add browser caching rules for static assets', 'wp-cloudflare-page-cache'),
      description: (
        <>
          {createInterpolateElement(
            __('<strong>If you use Nginx:</strong> it is not possible for Super Page Cache to automatically change the settings to allow this option to work immediately. For it to work, update these settings and then follow the instructions <button>here</button>.'),
            {
              button: <NginxLinkInstructions />,
              strong: <strong />
            }
          )}
          <br />
          <Badge variant="destructive" className="mt-3">
            {__('Writes into .htaccess', 'wp-cloudflare-page-cache')}
          </Badge>
        </>
      ),
      children: (
        <Notice type="warning" className="mt-5">
          {__('If you are using Plesk, make sure you have disabled the options "Smart static files processing" and "Serve static files directly by Nginx" on "Apache & Nginx Settings" page of your Plesk panel or ask your hosting provider to update browser caching rules for you.', 'wp-cloudflare-page-cache')}
        </Notice>
      )
    },
  ]

  return (
    <>
      <Card>
        <CardHeader className="bg-muted">
          <h3 className="font-semibold text-base flex items-center">{__('Cache', 'wp-cloudflare-page-cache')}</h3>
        </CardHeader>

        <CardContent className="p-0">
          <ControlsGroup controls={controls} />
        </CardContent>
      </Card>

      <Card>
        <CardHeader className="bg-muted">
          <h3 className="font-semibold text-base flex items-center">{__('Browser Caching', 'wp-cloudflare-page-cache')}</h3>
        </CardHeader>

        <CardContent className="p-0">

          <div className="p-4">
            <Notice description={__('This option is useful if you want to use Super Page Cache to enable browser caching rules for assets such like images, CSS, scripts, etc. It works automatically if you use Apache as web server or as backend web server.', 'wp-cloudflare-page-cache')} />
          </div>

          <ControlsGroup controls={browserCachingControls} />
        </CardContent>
      </Card>
    </>
  )
}

const OverwriteHeaderDescription = () => (
  <div className="grid gap-1">
    <Accordion type="single" collapsible className="rounded-sm border overflow-hidden">
      <AccordionItem value="htaccess">
        <AccordionTrigger className="!m-0 bg-muted px-4 py-3 !text-sm">
          {__('Additional info', 'wp-cloudflare-page-cache')}
        </AccordionTrigger>

        <AccordionContent className="p-4 bg-muted/20">
          <Notice
            type="warning"
            title={__('Important Notes', 'wp-cloudflare-page-cache')}
            description={
              createInterpolateElement(__('This option is not essential as in most cases this plugin works out of the box. If the page cache does not work after a considerable number of attempts or you see that max-age and s-maxage values of <code>X-WP-CF-Super-Cache-Cache-Control</code> response header are not the same of the ones in <code>Cache-Control</code> response header, activate this option.', 'wp-cloudflare-page-cache'),
                {
                  code: <code />
                }
              )
            }
          />

          <div className="grid lg:grid-cols-2 gap-4 mt-4">
            <Notice
              type="success"
              title={`${__('Read here if you use Apache', 'wp-cloudflare-page-cache')} (.htaccess):`}
              description={
                createInterpolateElement(__('For overwriting to work, make sure that the rules added by Super Page Cache are placed at the bottom of the <code>.htaccess</code> file. If they are present <strong>BEFORE</strong> other caching rules of other plugins, move them to the bottom manually.', 'wp-cloudflare-page-cache'),
                  {
                    code: <code />,
                    strong: <strong />
                  }
                )
              }
            />

            <Notice
              type="info"
              title={`${__('Read here if you use Nginx', 'wp-cloudflare-page-cache')}:`}
              description={createInterpolateElement(
                __('It is not possible for Super Page Cache to automatically change the settings to allow this option to work immediately. For it to work, update these settings and then follow the instructions <button>here</button>.', 'wp-cloudflare-page-cache'),
                {
                  button: <NginxLinkInstructions />
                }
              )}
            />
          </div>
        </AccordionContent>
      </AccordionItem>
    </Accordion>
  </div >

)

export default AdvancedCache;
