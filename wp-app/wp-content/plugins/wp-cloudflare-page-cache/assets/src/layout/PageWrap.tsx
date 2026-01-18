import { cn } from "@/lib/utils";
import { useAppStore } from "@/store/store";

const PageWrap = ({ children, className = '' }: { children: React.ReactNode, className?: string }) => {
  const { darkMode } = useAppStore();

  return (
    <div className={cn('antialiased bg-muted grow spc-dash', className, {
      'dark': darkMode,
    })}>
      {children}
    </div>
  );
};

export default PageWrap;