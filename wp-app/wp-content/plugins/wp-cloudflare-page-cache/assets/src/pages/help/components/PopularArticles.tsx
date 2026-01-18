import { ChevronRight, Star } from "lucide-react";
import { __, sprintf } from "@wordpress/i18n";

const { help } = window.SPCDash;

const PopularArticles = () => {
  const articles = help.popular;

  return (
    <div className="mb-12">
      <div className="flex items-center mb-6">
        <Star className="w-5 h-5 mr-2 text-orange-500" />
        <h3 className="text-xl font-semibold">{__('Popular Articles', 'wp-cloudflare-page-cache')}</h3>
      </div>
      <div className="grid md:grid-cols-2 gap-4">
        {articles.map((article, index) => (
          <a href={article.url} target="_blank" className="group bg-card rounded-lg border p-5 hover:shadow-md transition-shadow cursor-pointer" key={index} rel="noreferrer">
            <h4 className="font-medium text-foreground mb-2">{article.title}</h4>
            <p className="text-sm text-muted-foreground mb-3">{article.content}</p>
            <div className="flex items-center justify-between">
              <span className="text-xs text-muted-foreground"> 
                {/* translators: %d is the number of minutes */ sprintf(__('%d min read', 'wp-cloudflare-page-cache'), article.read_time)}
              </span>
              <ChevronRight className="w-4 h-4 text-orange-600 dark:text-orange-500 group-hover:translate-x-1 transition-transform" />
            </div>
          </a>
        ))}
      </div>
    </div>
  )
}

export default PopularArticles; 