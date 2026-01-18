import { cn } from "@/lib/utils";
import { Children, Fragment, isValidElement } from "@wordpress/element";

export const CardHeader = ({ children, className = "" }: { children: React.ReactNode, className?: string }) => {
  return <div className={`p-4 border-b ${className}`}>{children}</div>;
}

export const CardContent = ({ children, className = "" }: { children: React.ReactNode, className?: string }) => {
  return <div className={cn('p-4', className)}>{children}</div>;
}

export const CardFooter = ({ children, className = "" }: { children: React.ReactNode, className?: string }) => {
  return <div className={`p-4 border-t ${className}`}>{children}</div>;
}

const Card = ({ children, className = "" }: {
  children?: React.ReactNode,
  className?: string,
}) => {
  // Extract slot content from children
  let headerContent = null;
  let contentElements: React.ReactNode[] = [];
  let footerContent = null;

  Children.forEach(children, (child) => {
    if (isValidElement(child)) {
      const element = child;
      if (element.type === CardHeader) {
        headerContent = element;
      } else if (element.type === CardFooter) {
        footerContent = element;
      } else {
        // Any other content goes in the middle
        contentElements.push(child);
      }
    }
  });

  return (
    <div className={`bg-card rounded-lg border shadow-sm overflow-hidden ${className}`}>
      {headerContent}
      {contentElements.length > 0 && (contentElements.map((content, index) => <Fragment key={index}>{content}</Fragment>))}
      {footerContent}
    </div>
  );
}

export default Card; 