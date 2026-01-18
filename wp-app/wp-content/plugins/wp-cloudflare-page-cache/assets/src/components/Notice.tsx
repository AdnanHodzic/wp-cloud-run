import { cn } from "@/lib/utils";
import { CheckCircle, CircleAlert, Info, TriangleAlert, X } from "lucide-react";
import Button from "./Button";
import { __ } from "@wordpress/i18n";

type NoticeProps = {
  icon?: React.ElementType | null | 'disabled',
  type?: 'info' | 'warning' | 'error' | 'success' | 'neutral' | 'orange';
  className?: string;
  fillIcon?: boolean;
  description?: string | React.ReactNode;
  title?: string | React.ReactNode;
  children?: React.ReactNode | null;
  onDismiss?: () => void;
}

const Notice = ({ children = null, description = "", title = "", fillIcon = false, className = "", icon = null, type = 'neutral', onDismiss = null }: NoticeProps) => {
  const containerClasses = cn(  
    "border rounded-lg p-3 relative",
    {
      'bg-orange-50 border-orange-200 dark:bg-orange-900/20 dark:border-orange-700/30': type === 'orange',
      'bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-700/30': type === 'info',
      'bg-amber-50 border-amber-200 dark:bg-amber-900/20 dark:border-amber-700/30': type === 'warning',
      'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-700/30': type === 'error',
      'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-700/30': type === 'success',
      'bg-muted border-foreground-muted': type === 'neutral',
    },
    className
  );

  const iconClasses = cn(
    "size-5 mr-2 flex-shrink-0",
    {
      'text-orange-600 dark:text-orange-400': type === 'orange',
      'text-blue-600 dark:text-blue-400': type === 'info',
      'text-amber-600 dark:text-amber-400': type === 'warning',
      'text-red-600 dark:text-red-400': type === 'error',
      'text-green-600 dark:text-green-400': type === 'success',
      'text-gray-600 dark:text-gray-400': type === 'neutral',
    });

  const textClasses = cn(
    "text-sm",
    {
      'text-orange-800 dark:text-orange-300': type === 'orange',
      'text-blue-800 dark:text-blue-300': type === 'info',
      'text-amber-800 dark:text-amber-500': type === 'warning',
      'text-red-800 dark:text-red-300': type === 'error',
      'text-green-800 dark:text-green-300': type === 'success',
      'text-gray-800 dark:text-gray-300': type === 'neutral',
    });

  const titleClasses = cn(
    'font-semibold text-sm',
    {
      'text-orange-900 dark:text-orange-200': type === 'orange',
      'text-blue-900 dark:text-blue-200': type === 'info',
      'text-amber-900 dark:text-amber-200': type === 'warning',
      'text-red-900 dark:text-red-200': type === 'error',
      'text-green-900 dark:text-green-200': type === 'success',
      'text-gray-900 dark:text-gray-200': type === 'neutral',
    }
  )

  const iconMap = {
    warning: TriangleAlert,
    success: CheckCircle,
    error: CircleAlert,
    info: Info,
  }

  const Icon = icon === 'disabled' ? null : icon ? icon : iconMap[type] || null;

  return (
    <div className={containerClasses}>
      {!!onDismiss && (
        <Button
          variant="ghost"
          size="icon"
          onClick={onDismiss}
          className={cn("absolute top-1 right-1 rounded-lg hover:bg-black/10", textClasses)}
          icon={X}
        >
          <span className="sr-only">{__('Dismiss notice', 'wp-cloudflare_page_cache')}</span>
        </Button>
      )}
      <div className="flex items-start">
        {Icon && <Icon className={iconClasses} fill={fillIcon ? "currentColor" : "none"} />}
        <div className="grid gap-1">
          {title && <div className={titleClasses}>{title}</div>}
          {(description || children) && (
            <div className="space-y-2">
              {description && (
                <div className={textClasses}>
                  {typeof description === 'string' 
                    ? <span className="leading-relaxed" dangerouslySetInnerHTML={{ __html: description }} /> 
                    : <span className="leading-relaxed">{description}</span>
                  }
                </div>
              )}
              {children && (
                <div className={textClasses}>
                  {children}
                </div>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  )
}

export default Notice;