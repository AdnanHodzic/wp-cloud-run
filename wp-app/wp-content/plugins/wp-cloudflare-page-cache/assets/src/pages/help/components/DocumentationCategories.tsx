import { ChevronRight, Database, Globe, Rocket, Settings, Shield, Wrench } from "lucide-react";
import { __, sprintf } from "@wordpress/i18n";
import { cn } from "@/lib/utils";

const { help } = window.SPCDash;

const DocumentationCategories = () => {

  const colors = [
    'text-green-600 bg-green-600/20',
    'text-orange-600 bg-orange-600/20',
    'text-blue-600 bg-blue-600/20',
    'text-purple-600 bg-purple-600/20',
    'text-indigo-600 bg-indigo-600/20',
    'text-red-600 bg-red-600/20',
  ];

  const iconMap = {
    'rocket': Rocket,
    'wrench': Wrench,
    'settings': Settings,
    'globe': Globe,
    'database': Database,
    'shield': Shield,
  }

  const categories = help.categories;

  return (
    <div className="mb-12">
      <h3 className="text-xl font-semibold mb-6">{__('Documentation Categories', 'wp-cloudflare-page-cache')}</h3>
      <div className="grid md:grid-cols-3 gap-4">

        {categories.map((category, index) => {

          const Icon = iconMap[category.icon as keyof typeof iconMap] || Settings;

          return (
            <a href={category.url} target="_blank" className="group bg-card rounded-lg border p-5 hover:shadow-md transition-shadow cursor-pointer" key={index} rel="noreferrer">
              <div className={cn("w-10 h-10 rounded-lg flex items-center justify-center mb-4", colors[index % colors.length])}>
                <Icon className="w-5 h-5" />
              </div>

              <h4 className="font-medium text-foreground mb-2">{category.title}</h4>

              <p className="text-sm text-muted-foreground mb-3">{category.description}</p>

              <div className="flex items-center justify-between">
                <span className="text-xs text-muted-foreground">
                  {category.article_count && (
                    <>
                      {/* translators: %d is the number of articles */ sprintf(__('%d articles', 'wp-cloudflare-page-cache'), category.article_count)}
                    </>
                  )}
                </span>
                <ChevronRight className="w-4 h-4 text-muted-foreground group-hover:translate-x-1 transition-transform" />
              </div>
            </a>
          );
        })}
      </div>
    </div>
  )
}

export default DocumentationCategories; 