import { CircleHelp } from "lucide-react";
import { __ } from "@wordpress/i18n";
import { useAppStore } from "@/store/store";

const Hero = () => {
  const { validPro } = useAppStore();
  const byline = validPro ?
    __('Find answers in our documentation or get direct support', 'wp-cloudflare-page-cache') :
    __('Find answers in our documentation or get help from the WordPress.org community', 'wp-cloudflare-page-cache');

  return (
    <div className="text-center mb-12">
      <div className="w-16 h-16 bg-orange-600/10 rounded-full flex items-center justify-center mx-auto mb-6">
        <CircleHelp className="w-8 h-8 text-orange-600" />
      </div>

      <h2 className="text-2xl font-bold mb-3">{__('How can we help you?', 'wp-cloudflare-page-cache')}</h2>
      <p className="text-foreground/80">{byline}</p>
    </div>
  )
}

export default Hero; 