import { cn } from "@/lib/utils";
import { ExternalLinkIcon } from "lucide-react";

type ExternalLinkProps = {
  url: string;
  children?: React.ReactNode;
  className?: string;
};

const ExternalLink = ({ url, children, className = "" }: ExternalLinkProps) => (
  <a className={cn("inline-flex gap-0.5 items-center underline text-foreground hover:text-muted-foreground", className)} href={url} target="_blank" rel="noreferrer">
    {children}

    <ExternalLinkIcon size={12} />
  </a>
);

export default ExternalLink;