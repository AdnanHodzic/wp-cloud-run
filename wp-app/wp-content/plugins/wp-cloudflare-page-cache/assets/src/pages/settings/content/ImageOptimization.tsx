
import { __ } from '@wordpress/i18n';
import { ArrowRight, Brain, Globe, Smartphone, Star, Zap } from 'lucide-react';
import Card, { CardContent, CardHeader } from "@/components/Card";
import { useState } from '@wordpress/element';
import { cn } from "@/lib/utils";
import Notice from "@/components/Notice";
import Button from "@/components/Button";

export const ImageOptimization = () => {
  const [showImage, setShowImage] = useState(false);

  const onImageLoaded = () => {
    setShowImage(true);
  }
  const { logoURL, ratingByline, activeInstalls, thickboxURL, cta } = window.SPCDash.optimoleData;
  const features = [
    { icon: Zap, text: __('Real-time cloud-based image optimization', 'wp-cloudflare-page-cache') },
    { icon: Brain, text: __('Automatic Machine Learning compression', 'wp-cloudflare-page-cache') },
    { icon: Globe, text: __('Global CDN delivery from 450+ locations', 'wp-cloudflare-page-cache') },
    { icon: Smartphone, text: __('Device-specific optimization for perfect sizing', 'wp-cloudflare-page-cache') },
  ];

  return (
      <Card>
        <CardHeader className="flex items-center gap-4 bg-muted">

          <div className="flex-shrink-0">
            <img
              onLoad={onImageLoaded}
              src={logoURL}
              alt={__('Optimole Logo', 'wp-cloudflare-page-cache')}
              className={cn("size-16",
                showImage ? "opacity-100" : "opacity-0 transition-opacity duration-500",
              )} />
          </div>
          <div className="flex-1 grid gap-0">
            <h2 className="text-xl font-bold text-2xl mb-2 leading-tight">
              {__('Speed up your website with smart image optimization', 'wp-cloudflare-page-cache')}
            </h2>
            <span className="text-muted-foreground text-sm">
              {__('By the Super Page Cache Pro team', 'wp-cloudflare-page-cache')}
            </span>
          </div>

        </CardHeader>

        <CardContent>

          <Notice
            type="info"
            icon={Zap}
            fillIcon
            title={__('Images can account for 50% of your loading time!', 'wp-cloudflare-page-cache')}
          />


          <p className="text-muted-foreground text-sm my-6">
            {__('Optimole automatically optimizes your images in real-time, helping your website gain precious seconds while saving you time. With just one click, it intelligently optimizes and serves your images for the best user experience.', 'wp-cloudflare-page-cache')}
          </p>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            {features.map((feature, index) => (
              <div key={index} className="flex items-center gap-3 p-3 rounded-lg bg-muted hover:bg-muted/80 transition-colors">
                <feature.icon className="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" />
                <span className="text-sm">{feature.text}</span>
              </div>
            ))}
          </div>

          <div className="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl p-6 text-white">
            <div className="flex items-center justify-between mb-4">
              <div className="text-lg font-semibold">
                {__('Install Optimole - Smart WordPress Image Optimizer', 'wp-cloudflare-page-cache')}
              </div>
            </div>

            <div className="flex items-center gap-6 mb-4">
              <div className="flex items-center gap-2">
                <div className="flex items-center gap-1">
                  {[...Array(5)].map((_, i) => (
                    <Star key={i} className="w-4 h-4 fill-yellow-400 text-yellow-400" />
                  ))}
                </div>
                <span className="text-blue-100 text-sm">{ratingByline}</span>
              </div>
            </div>

            <div className="flex items-center justify-between">
              <span className="text-blue-100 text-sm font-medium">
                {activeInstalls}
              </span>

              <Button
                href={thickboxURL}
                target="_blank"
                size="lg"
                className="thickbox group bg-white border-white text-blue-600 hover:bg-blue-50 shadow-blue-500/60 hover:shadow-blue-500/60 transition-all shadow-lg hover:shadow-xl"
              >
                {cta}
                <ArrowRight className="size-4 group-hover:translate-x-1 transition-transform" />
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
  );
}

