import { cn } from "@/lib/utils"
import { __ } from "@wordpress/i18n";
import { useState } from "@wordpress/element";

type LogoWordmarkProps = {
  hideText?: boolean;
  text?: string;
  className?: string;
}

const LogoWordmark = ({ hideText = false, text = '', className = "" }: LogoWordmarkProps) => {
  const [isLoaded, setIsLoaded] = useState(false);
  return (
    <div className={cn('flex items-center gap-2', className)}>
      <img
        onLoad={() => {
          setIsLoaded(true);
        }}
        src={window.SPCDash.logoURL}
        alt={__('Super Page Cache Logo', 'wp-cloudflare-page-cache')}
        className={cn("size-8 starting:opacity-0 transition-opacity duration-300", {
          'hidden': !isLoaded,
        })}
      />
      {!isLoaded && <span className="size-8 bg-foreground/10 rounded-md animate-pulse"></span>}
      {!hideText && <h1 className="text-lg font-semibold">{text || __('Super Page Cache', 'wp-cloudflare-page-cache')}</h1>}
    </div>
  )
}

export default LogoWordmark;