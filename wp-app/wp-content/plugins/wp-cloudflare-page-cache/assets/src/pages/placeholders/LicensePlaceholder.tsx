import PageWrap from "@/layout/PageWrap";
import HeaderPlaceholder from "./HeaderPlaceholder";

const LicensePlaceholder = () => {
  return (
    <PageWrap>
      <HeaderPlaceholder />

      {/* Main Content */}
      <div className="max-w-2xl mx-auto px-6 py-8 space-y-6">
        {/* LicenseCard Skeleton */}
        <div className="bg-background rounded-lg border p-6">
          <div className="flex items-center justify-between mb-4">
            <div className="w-32 h-6 bg-muted-foreground/50 rounded animate-pulse"></div>
            <div className="w-16 h-6 bg-muted-foreground/50 rounded-full animate-pulse"></div>
          </div>
          <div className="space-y-3">
            <div className="w-full h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
            <div className="w-3/4 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
            <div className="w-1/2 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
          </div>
          <div className="mt-6 pt-4 border-t">
            <div className="flex items-center justify-between">
              <div className="w-24 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
              <div className="w-20 h-8 bg-muted-foreground/50 rounded animate-pulse"></div>
            </div>
          </div>
        </div>

        {/* PurchaseCard Skeleton */}
        <div className="bg-background rounded-lg border p-6">
          <div className="flex items-center justify-between mb-4">
            <div className="w-28 h-6 bg-muted-foreground/50 rounded animate-pulse"></div>
            <div className="w-16 h-6 bg-muted-foreground/50 rounded-full animate-pulse"></div>
          </div>
          <div className="space-y-3 mb-6">
            <div className="w-full h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
            <div className="w-2/3 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
          </div>
          <div className="space-y-3">
            <div className="flex items-center justify-between">
              <div className="w-20 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
              <div className="w-16 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
            </div>
            <div className="flex items-center justify-between">
              <div className="w-24 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
              <div className="w-20 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
            </div>
            <div className="flex items-center justify-between">
              <div className="w-16 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
              <div className="w-12 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
            </div>
          </div>
          <div className="mt-6 pt-4 border-t">
            <div className="w-full h-10 bg-muted-foreground/50 rounded-lg animate-pulse"></div>
          </div>
        </div>
      </div>
    </PageWrap>
  );
};

export default LicensePlaceholder; 