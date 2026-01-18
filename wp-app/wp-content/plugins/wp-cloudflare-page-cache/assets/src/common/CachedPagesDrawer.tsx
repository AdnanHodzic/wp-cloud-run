import Button from "@/components/Button";
import { Drawer, DrawerClose, DrawerContent, DrawerHeader, DrawerPortal, DrawerTitle, DrawerTrigger } from "@/components/ui/drawer";
import { ScrollArea } from "@/components/ui/scroll-area";
import Container from "@/layout/Container";
import { spcApi } from "@/lib/api";
import { cn } from "@/lib/utils";
import { useAppStore } from "@/store/store";
import { createInterpolateElement, useEffect, useState } from "@wordpress/element";
import { __, sprintf } from "@wordpress/i18n";
import { Calendar, CircleOff, Copy, ExternalLink, EyeIcon, FileText, RotateCcw, TriangleAlert, X } from 'lucide-react';
import { toast } from "sonner";

type CachedPage = {
  url: string;
  timestamp: number;
}

const ViewCachedPages = () => {
  const { darkMode } = useAppStore();
  const { i18n } = window.SPCDash;

  const [isLoading, setIsLoading] = useState(true);
  const [cachedPages, setCachedPages] = useState<CachedPage[]>([]);
  const [error, setError] = useState<string | null>(null);
  
  const getCachedPages = () => {
    setIsLoading(true);
    setError(null);
    setCachedPages([]);

    spcApi.getCachedPages().then((response) => {
      if (response.success && response.data) {
        setCachedPages(response.data as CachedPage[]);
      } else {
        setError(response.message);
      }
    }).finally(() => {
      setIsLoading(false);
    });
  }

  useEffect(() => {
    getCachedPages();
  }, []);

  return (
    <Drawer>
      <DrawerTrigger className="p-0 h-auto text-xs flex font-medium items-center gap-1 text-foreground/80 hover:text-foreground hover:underline">
        <EyeIcon className="size-4 text-muted-foreground"/>
        {__('View', 'wp-cloudflare-page-cache')}
      </DrawerTrigger>

      <DrawerPortal>
        <DrawerContent className={cn("overflow-hidden", { 'dark': darkMode })}>
          <DrawerHeader className="border-b border-border bg-muted">
            <Container className="text-left flex items-center justify-between w-full">
              <DrawerTitle>
                <div className="flex items-center gap-3">
                  <div className="p-2 bg-orange-400/10 border border-orange-400/30 rounded-lg">
                    <FileText className="size-5 text-orange-600" />
                  </div>
                  <div>
                    <h2 className="text-lg font-semibold text-foreground">
                      {__('Cached Pages', 'wp-cloudflare-page-cache')}
                    </h2>
                    <p className="text-sm text-muted-foreground">
                      {
                        // translators: %d is the number of pages in cache
                        sprintf(__('%d pages in cache', 'wp-cloudflare-page-cache'), cachedPages.length)
                      }
                    </p>
                  </div>
                </div>
              </DrawerTitle>

              <div>
                <Button
                  onClick={getCachedPages}
                  variant="ghost"
                  size="icon"
                  icon={RotateCcw}
                  loader={isLoading} 
                  disabled={isLoading}
                >
                  <span className="sr-only">{__('Refresh', 'wp-cloudflare-page-cache')}</span>
                </Button>
                <DrawerClose>
                  <Button variant="ghost" size="icon" icon={X}>
                    <span className="sr-only">
                      {i18n.close}
                    </span>
                  </Button>
                </DrawerClose>
              </div>
            </Container>
          </DrawerHeader>

          {(cachedPages.length > 0 && ! isLoading) && (
            <ScrollArea className="overflow-auto max-w-full max-h-[80vh] py-2">
              <Container className="py-0 divide-y divide-border w-full">
                {cachedPages.map((page) => (
                  <UrlRow key={page.url} url={page.url} timestamp={page.timestamp} />
                ))}
              </Container>
            </ScrollArea>
          )}

          {(cachedPages.length < 1 && ! isLoading && ! error) && (
            <EmptyState />
          )}

          { error && <ErrorState/>}

          {isLoading && <LoadingState />}
        </DrawerContent>
      </DrawerPortal>
    </Drawer >
  )
}

const UrlRow = ({url, timestamp}: {url: string, timestamp: number}) => {
  const date = new Date(timestamp * 1000);
  const formattedTimestamp = `${date.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })} \u2022 ${date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false })}`;

  const onCopy = () => {
    navigator.clipboard.writeText(url);
    toast.success(__('URL copied to clipboard', 'wp-cloudflare-page-cache'));
  }

  return (
    <div className="px-4 py-2 hover:bg-muted transition-colors group">
      <div className="flex items-center justify-between gap-4">
        <div className="flex-1 min-w-0 text-sm">
                  
          <div className="flex mb-2">
            <a href={url} className="font-mono text-foreground break-all hover:underline truncate" rel="noreferrer">{url}</a>
          </div>
                      
          <div className="flex items-center gap-2 text-foreground/80">
            <Calendar className="size-4 text-muted-foreground flex-shrink-0"/>
            <span>{formattedTimestamp}</span>
          </div>
                  
        </div>
        <div className="flex items-center gap-1">
          <Button 
            variant="ghost"
            size="icon"
            onClick={onCopy}
            icon={Copy}
          >
            <span className="sr-only">
              {__('Copy URL', 'wp-cloudflare-page-cache')}
            </span>
          </Button>
          <Button 
            variant="ghost"
            size="icon"
            href={url}
            target="_blank"
            icon={ExternalLink}
          >
            <span className="sr-only">
              {__('Open in new tab', 'wp-cloudflare-page-cache')}
            </span>
          </Button>
        </div>
      </div>
    </div>
  );
}

const ErrorState = () => {
  return (
    <Container className="flex items-center justify-center flex-col">
      <div className="p-8 text-center text-muted-foreground flex flex-col items-center gap-3">
        <TriangleAlert className="size-8 text-foreground/30" />
        <h4 className="text-lg text-foreground/80"> 
          {window.SPCDash.i18n.genericError}
        </h4>
      </div>
    </Container>
  )
}

const LoadingState = () => {
  return (
    <Container className="py-0 divide-y divide-border w-full">
      {Array.from({ length: 6 }).map((_, index) => (
        <div key={index} className="px-4 py-2.5 animate-pulse w-full">
          <div className="flex items-center justify-between gap-4">
            <div className="flex-1 min-w-0 text-sm">
              {/* URL skeleton */}
              <div className="mb-2">
                <div className="h-5 bg-muted-foreground/50 rounded w-3/4 max-w-md"/>
              </div>
                
              {/* Timestamp skeleton */}
              <div className="flex items-center gap-2">
                <div className="size-4 bg-muted-foreground/50 rounded flex-shrink-0"/>
                <div className="h-4 bg-muted-foreground/50 rounded w-32"/>
              </div>
            </div>
              
            {/* Action buttons skeleton */}
            <div className="flex items-center gap-1">
              <div className="size-9 bg-muted-foreground/50 rounded"/>
              <div className="size-9 bg-muted-foreground/50 rounded"/>
            </div>
          </div>
        </div>
      ))}
    </Container>
  )
}

const EmptyState = () => {
  return (
    <Container className="flex items-center justify-center flex-col">
      <div className="p-8 text-center text-muted-foreground flex flex-col items-center gap-3">
        <CircleOff className="size-8 text-foreground/30" />
        <h4 className="text-lg text-foreground/80">
          {__('No pages were cached yet.', 'wp-cloudflare-page-cache')}
        </h4>
        <p className="text-sm text-muted-foreground">
          {createInterpolateElement(
            __('Make sure that <strong>Disk Page Cache</strong> is enabled in the settings, or run the preloader to warm up cache.', 
              'wp-cloudflare-page-cache'), {
              strong: <strong/>
            })}
        </p>
      </div>
    </Container>
  );
}

export default ViewCachedPages;