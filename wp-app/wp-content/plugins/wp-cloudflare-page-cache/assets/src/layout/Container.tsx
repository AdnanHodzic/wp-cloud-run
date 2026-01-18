import { cn } from "@/lib/utils";

const Container = ({ children, className = '' }: { children: React.ReactNode, className?: string }) => {
  return <div className={cn('max-w-7xl mx-auto px-4 md:px-6', className)}>{children}</div>;
};

export default Container;