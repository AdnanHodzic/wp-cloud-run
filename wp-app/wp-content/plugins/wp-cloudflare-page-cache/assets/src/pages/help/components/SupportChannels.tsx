import { BookOpen, ChevronRight, ExternalLink, LucideProps, MessageCircle, Users, Video } from "lucide-react";
import { __ } from "@wordpress/i18n";
import { cn } from "@/lib/utils";
import { useAppStore } from "@/store/store";
import Badge from "@/components/Badge";
import { LINKS } from "@/lib/constants";
import { ComponentType } from "react";

const SupportChannels = () => {
  const { validPro } = useAppStore();

  const colors = [
    'text-blue-700 bg-blue-700/10 border-blue-700/30 dark:bg-blue-700/20 dark:border-blue-700/40 dark:text-blue-300',
    'text-red-700 bg-red-700/10 border-red-700/30 dark:bg-red-700/20 dark:border-red-700/40 dark:text-red-300',
    'text-green-700 bg-green-700/10 border-green-700/30 dark:bg-green-700/20 dark:border-green-700/40 dark:text-green-300',
  ];

  let channels: {
    title: string;
    description: string;
    cta: string;
    url: string;
    icon: ComponentType<LucideProps>;
    popular?: boolean;
  }[] = [
    {
      title: __('Documentation', 'wp-cloudflare-page-cache'),
      description: __('Complete guides and references', 'wp-cloudflare-page-cache'),
      cta: __('Browse Documentation', 'wp-cloudflare-page-cache'),
      url: LINKS.DOCS,
      icon: BookOpen,
    },
    {
      title: __('Video Tutorials', 'wp-cloudflare-page-cache'),
      description: __('Step-by-step video guides', 'wp-cloudflare-page-cache'),
      cta: __('Watch Videos', 'wp-cloudflare-page-cache'),
      url: LINKS.YOUTUBE_PLAYLIST,
      icon: Video,
    },
  ];

  if (validPro) {
    colors.push('text-orange-700 bg-orange-700/10 border-orange-700/30 dark:bg-orange-700/20 dark:border-orange-700/40 dark:text-orange-300');
    channels.push({
      title: __('Community Forum', 'wp-cloudflare-page-cache'),
      description: __('Get help from other users', 'wp-cloudflare-page-cache'),
      cta: __('Join Discussion', 'wp-cloudflare-page-cache'),
      url: LINKS.WPORG_FORUM,
      icon: Users,
    });
    channels.push({
      title: __('Contact Support', 'wp-cloudflare-page-cache'),
      description: __('Direct help from our team', 'wp-cloudflare-page-cache'),
      cta: __('Send Message', 'wp-cloudflare-page-cache'),
      url: LINKS.DIRECT_SUPPORT,
      icon: MessageCircle,
    });
  } else {
    colors.push('text-purple-700 bg-purple-700/10 border-purple-700/30 dark:bg-purple-700/20 dark:border-purple-700/40 dark:text-purple-300');
    channels.push({
      title: __('WordPress.org Support', 'wp-cloudflare-page-cache'),
      description: __('Official plugin support forum', 'wp-cloudflare-page-cache'),
      cta: __('Visit Forum', 'wp-cloudflare-page-cache'),
      url: LINKS.WPORG_FORUM,
      icon: ExternalLink,
      popular: true,
    });
    channels.push({
      title: __('Feedback & Suggestions', 'wp-cloudflare-page-cache'),
      description: __('Share your thoughts and ideas', 'wp-cloudflare-page-cache'),
      cta: __('Give Feedback', 'wp-cloudflare-page-cache'),
      url: LINKS.SUGGEST_FEATURE,
      icon: MessageCircle,
    });
  }


  return (
    <div>
      <h3 className="text-xl font-semibold mb-6">{__('Get Support', 'wp-cloudflare-page-cache')}</h3>
      <div className="grid md:grid-cols-2 gap-4">

        {channels.map((channel, index) => (
          <a href={channel.url} target="_blank" className={cn("group relative border rounded-lg p-6 cursor-pointer relative hover:shadow-current", colors[index % colors.length])} key={index} rel="noreferrer">
            {channel.popular && (
              <Badge variant="success" className="absolute top-3 right-3">{__('Most Popular', 'wp-cloudflare-page-cache')}</Badge>
            )}

            <div className="flex items-center mb-4">
              <channel.icon className="w-6 h-6 mr-3 shrink-0" />

              <div className="grid gap-1">
                <h4 className="font-medium text-current">{channel.title}</h4>
                <p className="text-sm text-current opacity-90">{channel.description}</p>
              </div>

            </div>

            <span className="text-current flex gap-1 items-center text-sm font-semibold group-hover:underline">
              {channel.cta}
              <ChevronRight className="w-3 h-3 group-hover:translate-x-1 transition-transform" />
            </span>
          </a>
        ))}
      </div>
    </div>
  )
}

export default SupportChannels; 