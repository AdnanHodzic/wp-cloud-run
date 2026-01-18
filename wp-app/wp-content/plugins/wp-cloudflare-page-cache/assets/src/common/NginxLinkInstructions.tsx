import Button from "@/components/Button";
import { Drawer, DrawerClose, DrawerContent, DrawerHeader, DrawerPortal, DrawerTitle, DrawerTrigger } from "@/components/ui/drawer";
import { cn } from "@/lib/utils";
import { useSettingsStore } from "@/store/optionsStore";
import { useAppStore } from "@/store/store";
import { __, sprintf } from "@wordpress/i18n";

import { Copy, FileText, Server, X } from 'lucide-react';
import { toast } from "sonner";


import Separator from "@/common/Separator";
import Notice from "@/components/Notice";
import { ScrollArea } from "@/components/ui/scroll-area";
import Container from "@/layout/Container";
import { createInterpolateElement } from "@wordpress/element";

const NginxLinkInstructions = () => {
  const { darkMode } = useAppStore();
  const { isToggleOn, settings } = useSettingsStore();
  const { i18n } = window.SPCDash;

  const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
    toast.success(__('Copied to clipboard!', 'wp-cloudflare-page-cache'));
  };

  const cacheControlCode = sprintf(
    `map $upstream_http_x_wp_cf_super_cache_active $wp_cf_super_cache_active {
  default  'no-cache, no-store, must-revalidate, max-age=0';
  '1' 's-maxage=%s, max-age=%s';
}`, settings['cf_maxage'], settings['cf_browser_maxage']
  );

  const phpBlockCode =
    `more_clear_headers 'Pragma';
more_clear_headers 'Expires';
more_clear_headers 'Cache-Control';
add_header Cache-Control $wp_cf_super_cache_active;`;

  let browserCachingCode = '';

  if (isToggleOn('cf_bypass_sitemap')) {
    browserCachingCode += 'location ~* \\.(xml|xsl)$ { add_header Cache-Control "no-cache, no-store, must-revalidate, max-age=0"; expires -1; }\n';
  }
  if (isToggleOn('cf_bypass_file_robots')) {
    browserCachingCode += 'location /robots.txt { add_header Cache-Control "no-cache, no-store, must-revalidate, max-age=0"; expires -1; }\n';
  }
  if (isToggleOn('cf_browser_caching_htaccess')) {
    // Cache CSS/JS/PDF for 1 month
    browserCachingCode += 'location ~* \\.(css|js|pdf)$ { add_header Cache-Control "public, must-revalidate, proxy-revalidate, immutable, max-age=2592000, stale-while-revalidate=86400, stale-if-error=604800"; expires 30d; }\n';
    // Cache other static files for 1 year
    browserCachingCode += 'location ~* \\.(jpg|jpeg|png|gif|ico|eot|swf|svg|webp|avif|ttf|otf|woff|woff2|ogg|mp4|mpeg|avi|mkv|webm|mp3)$ { add_header Cache-Control "public, must-revalidate, proxy-revalidate, immutable, max-age=31536000, stale-while-revalidate=86400, stale-if-error=604800"; expires 365d; }\n';
  }
  browserCachingCode += 'location /wp-cron.php { add_header Cache-Control "no-cache, no-store, must-revalidate, max-age=0"; expires -1; }\n';

  return (
    <Drawer>
      <DrawerTrigger className="underline text-current cursor-pointer">
        {__('here', 'wp-cloudflare-page-cache')}
      </DrawerTrigger>

      <DrawerPortal>
        <DrawerContent className={cn("overflow-hidden", { 'dark': darkMode })}>
          <DrawerHeader className="border-b border-border bg-muted">
            <Container className="text-left flex items-center justify-between w-full">
              <DrawerTitle>
                <h2 className="flex items-center text-lg mb-0">
                  <Server className="size-6 text-orange-500 mr-3" />
                  {__('Nginx Instructions', 'wp-cloudflare-page-cache')}
                </h2>
              </DrawerTitle>

              <DrawerClose>
                <Button variant="ghost" size="icon" icon={X}>
                  <span className="sr-only">
                    {i18n.close}
                  </span>
                </Button>
              </DrawerClose>
            </Container>
          </DrawerHeader>

          <ScrollArea className="overflow-auto max-w-full max-h-[80vh] py-5">
            <Container className="py-0">
              {isToggleOn('cf_cache_control_htaccess') && (
                <>
                  <div className="flex items-center mb-3">
                    <FileText className="size-5 mr-2 text-blue-500" />
                    <h3 className="text-base">{__('Overwrite the cache-control header', 'wp-cloudflare-page-cache')}</h3>
                  </div>

                  <div className="space-y-4">
                    <p className="text-sm mb-2 text-muted-foreground">
                      {createInterpolateElement(
                        __('Edit the main Nginx configuration file, usually <code>/etc/nginx.conf</code>, and enter these rules immediately after opening the http block:', 'wp-cloudflare-page-cache'),
                        {
                          code: <code className="px-2 py-1 rounded bg-muted font-mono">/etc/nginx.conf</code>
                        }
                      )}
                    </p>

                    <div className="relative">
                      <Button
                        variant="default"
                        size="icon"
                        onClick={() => copyToClipboard(cacheControlCode)}
                        icon={Copy}
                        className="absolute top-2 right-2 z-10"
                      >
                        <span className="sr-only">
                          {__('Copy to clipboard', 'wp-cloudflare-page-cache')}
                        </span>
                      </Button>
                      <pre className="text-muted-foreground bg-muted border border-border break-all p-4 rounded-2xl overflow-x-auto text-sm whitespace-pre-wrap">
                        {cacheControlCode}
                      </pre>
                    </div>

                    <div>
                      <p className="text-sm mb-2 text-muted-foreground">
                        {__('Now open the configuration file of your domain and add the following rules inside the block that deals with the management of PHP pages:', 'wp-cloudflare-page-cache')}
                      </p>

                      <div className="relative">
                        <Button
                          variant="default"
                          size="icon"
                          onClick={() => copyToClipboard(phpBlockCode)}
                          className="absolute top-2 right-2 z-10"
                          icon={Copy}
                        >
                          <span className="sr-only">
                            {__('Copy to clipboard', 'wp-cloudflare-page-cache')}
                          </span>
                        </Button>
                        <pre className="text-muted-foreground bg-muted border border-border p-4 rounded-2xl overflow-y-auto text-sm whitespace-pre-wrap">
                          {phpBlockCode}
                        </pre>
                      </div>
                    </div>
                  </div>
                  <Separator className="my-5" />
                </>
              )}

              {/* Browser Caching Rules Section */}
              <div className="flex items-center mb-3">
                <FileText className="size-5 mr-2 text-blue-500" />
                <h3 className="text-base">{__('Browser caching rules', 'wp-cloudflare-page-cache')}</h3>
              </div>

              <div className="space-y-4">
                <div>
                  <p className="text-sm mb-2 text-muted-foreground">
                    {__('Open the configuration file of your domain and add the following rules:', 'wp-cloudflare-page-cache')}
                  </p>

                  <div className="relative">
                    <Button
                      variant="default"
                      size="icon"
                      onClick={() => copyToClipboard(browserCachingCode)}
                      className="absolute top-2 right-2 z-10"
                      icon={Copy}
                    >
                      <span className="sr-only">
                        {__('Copy to clipboard', 'wp-cloudflare-page-cache')}
                      </span>
                    </Button>
                    <pre className="text-muted-foreground bg-muted border border-border p-4 rounded-2xl overflow-y-auto text-sm whitespace-pre-wrap">
                      {browserCachingCode}
                    </pre>
                  </div>
                </div>
              </div>

              <Notice type="info" className="my-5" title={__('Save and restart Nginx.', 'wp-cloudflare-page-cache')} />

            </Container>
          </ScrollArea>
        </DrawerContent>
      </DrawerPortal>
    </Drawer >


  );
}

export default NginxLinkInstructions;