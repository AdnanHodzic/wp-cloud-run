import Container from "@/layout/Container";
import PageWrap from "@/layout/PageWrap";
import HeaderPlaceholder from "./HeaderPlaceholder";

const DashboardPlaceholder = () => {
  return (
    <PageWrap>
      <HeaderPlaceholder />

      {/* Main Content */}
      <div className="bg-muted">
        <Container className="py-8">
          {/* Cache Metrics Placeholder */}
          <div className="grid md:grid-cols-4 gap-4 mb-6">
            {[...Array(4)].map((_, i) => (
              <div key={i} className="bg-background rounded-lg border p-4">
                <div className="flex items-center justify-between mb-3">
                  <div className="w-16 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
                  <div className="w-6 h-6 bg-muted-foreground/50 rounded animate-pulse"></div>
                </div>
                <div className="w-20 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
              </div>
            ))}
          </div>

          {/* Mobile Sidebar Actions Placeholder */}
          <div className="grid gap-6 lg:hidden mb-6">
            {/* Sidebar Actions */}
            <div className="bg-background rounded-lg border">
              <div className="p-4 border-b">
                <div className="w-16 h-5 bg-muted-foreground/50 rounded animate-pulse"></div>
              </div>
              <div className="p-4">
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2 gap-3">
                  <div className="h-10 bg-muted-foreground/50 rounded animate-pulse col-span-1  sm:col-span-2 lg:col-span-1 xl:col-span-2"></div>
                  <div className="h-10 bg-muted-foreground/50 rounded animate-pulse"></div>
                  <div className="h-10 bg-muted-foreground/50 rounded animate-pulse"></div>
                </div>
              </div>
            </div>

            {/* Sidebar System Status */}
            <div className="bg-background rounded-lg border">
              <div className="p-4 border-b">
                <div className="w-32 h-5 bg-muted-foreground/50 rounded animate-pulse"></div>
              </div>
              <div className="p-4 space-y-3">
                {[...Array(3)].map((_, i) => (
                  <div key={i} className="flex items-center justify-between">
                    <div className="w-24 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
                    <div className="w-16 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
                  </div>
                ))}
              </div>
            </div>
          </div>

          {/* Main Grid Layout */}
          <div className="grid lg:grid-cols-12 gap-6">
            {/* Activity Log - Main Content */}
            <div className="lg:col-span-8">
              <div className="bg-background rounded-lg border">
                <div className="p-4 border-b flex items-center justify-between">
                  <div className="w-24 h-5 bg-muted-foreground/50 rounded animate-pulse"></div>
                  <div className="flex items-center gap-2">
                    <div className="w-8 h-8 bg-muted-foreground/50 rounded animate-pulse"></div>
                    <div className="w-16 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
                  </div>
                </div>
                <div className="p-0 divide-y divide-muted-foreground/10">
                  {[...Array(10)].map((_, i) => (
                    <div key={i} className="px-4 py-3 flex items-center text-sm animate-pulse">
                      <div className="size-1.5 rounded-full bg-muted-foreground/50 mr-3" />
                      <div className="flex-1 h-4 bg-muted-foreground/30 rounded w-2/3" />
                      <div className="h-3 w-14 bg-muted-foreground/30 rounded ml-4" />
                    </div>
                  ))}
                </div>
              </div>
            </div>

            {/* Sidebar - Desktop */}
            <div className="hidden lg:block lg:col-span-4 space-y-6">
              {/* Sidebar Actions */}
              <div className="bg-background rounded-lg border">
                <div className="p-4 border-b">
                  <div className="w-16 h-5 bg-muted-foreground/50 rounded animate-pulse"></div>
                </div>
                <div className="p-4">
                  <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2 gap-3">
                    <div className="h-10 bg-muted-foreground/50 rounded animate-pulse col-span-1 sm:col-span-2 lg:col-span-1 xl:col-span-2"></div>
                    <div className="h-10 bg-muted-foreground/50 rounded animate-pulse"></div>
                    <div className="h-10 bg-muted-foreground/50 rounded animate-pulse"></div>
                  </div>
                </div>
              </div>

              {/* Sidebar System Status */}
              <div className="bg-background rounded-lg border">
                <div className="p-4 border-b">
                  <div className="w-32 h-5 bg-muted-foreground/50 rounded animate-pulse"></div>
                </div>
                <div className="p-4 space-y-3">
                  {[...Array(3)].map((_, i) => (
                    <div key={i} className="flex items-center justify-between">
                      <div className="w-24 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
                      <div className="w-16 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </Container>
      </div>
    </PageWrap>
  );
};

export default DashboardPlaceholder; 