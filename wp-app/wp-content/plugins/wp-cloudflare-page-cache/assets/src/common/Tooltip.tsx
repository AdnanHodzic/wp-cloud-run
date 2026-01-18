import { Info } from "lucide-react";
import { Tooltip as TooltipUI, TooltipContent, TooltipTrigger } from "@/components/ui/tooltip";
import { cn } from "@/lib/utils";

type TooltipProps = {
  children?: React.ReactNode;
  className?: string;
  text?: string;
  icon?: React.ElementType | null;
};

const Tooltip = ({ children, className = "", icon = null, text = "" }: TooltipProps) => {
  if (!children) return null;

  const Icon = icon ? icon : Info;

  return (
    <TooltipUI>
      <TooltipTrigger>
        <Icon size={16} className={cn("text-muted-foreground ml-1", className)} />
        {text && <span className="sr-only">{text}</span>}
      </TooltipTrigger>
      <TooltipContent>
        {children}
      </TooltipContent>
    </TooltipUI>)
}

export default Tooltip;