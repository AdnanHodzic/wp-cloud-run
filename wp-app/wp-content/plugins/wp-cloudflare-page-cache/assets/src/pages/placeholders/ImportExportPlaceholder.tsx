import Container from "@/layout/Container";
import HeaderPlaceholder from "./HeaderPlaceholder";
import PageWrap from "@/layout/PageWrap";

const ImportExportPlaceholder = () => {
  return (
    <PageWrap>
      <HeaderPlaceholder />

      <Container className="py-8">
        <div className="flex flex-col lg:flex-row items-start gap-8">
          {/* Export Card Placeholder */}
          <div className="bg-card rounded-lg border shadow-sm overflow-hidden flex-1">
            <div className="p-6 flex items-center border-b bg-green-50 border-green-200 dark:bg-green-700/10 dark:border-green-800/50">
              <div className="w-10 h-10 bg-muted-foreground/50 rounded-lg animate-pulse mr-4" />
              <div>
                <div className="w-32 h-5 bg-muted-foreground/50 rounded animate-pulse mb-2" />
                <div className="w-48 h-4 bg-muted-foreground/50 rounded animate-pulse" />
              </div>
            </div>
            <div className="p-6 space-y-4">
              <div className="w-full h-4 bg-muted-foreground/50 rounded animate-pulse" />
              <div className="border rounded-lg p-4 mt-4">
                <div className="flex items-start gap-3">
                  <div className="w-5 h-5 bg-muted-foreground/50 rounded animate-pulse mt-0.5" />
                  <div className="flex-1">
                    <div className="w-full h-3 bg-muted-foreground/50 rounded animate-pulse" />
                    <div className="w-4/5 h-3 bg-muted-foreground/50 rounded animate-pulse mt-1" />
                  </div>
                </div>
              </div>
            </div>
            <div className="p-4 border-t">
              <div className="w-full h-10 bg-muted-foreground/50 rounded animate-pulse" />
            </div>
          </div>

          {/* Import Card Placeholder */}
          <div className="bg-card rounded-lg border shadow-sm overflow-hidden flex-1">
            <div className="p-6 flex items-center border-b bg-orange-50 border-orange-200 dark:bg-orange-700/10 dark:border-orange-800/50">
              <div className="w-10 h-10 bg-muted-foreground/50 rounded-lg animate-pulse mr-4" />
              <div>
                <div className="w-32 h-5 bg-muted-foreground/50 rounded animate-pulse mb-2" />
                <div className="w-48 h-4 bg-muted-foreground/50 rounded animate-pulse" />
              </div>
            </div>
            <div className="p-6 space-y-2">
              <div className="w-full h-4 bg-muted-foreground/50 rounded animate-pulse" />
              <div className="w-30 h-4 bg-muted-foreground/50 rounded animate-pulse" />

              {/* File Control Placeholder */}
              <div className="space-y-2 mt-4">
                <div className="border-2 border-dashed border-muted-foreground/30 rounded-lg p-6 text-center">
                  <div className="w-8 h-8 bg-muted-foreground/50 rounded animate-pulse mx-auto mb-2" />
                  <div className="w-40 h-4 bg-muted-foreground/50 rounded animate-pulse mx-auto mb-1" />
                </div>
              </div>

              {/* Warning Notice Placeholder */}
              <div className="border rounded-lg p-4 mt-4">
                <div className="flex items-start gap-3">
                  <div className="w-5 h-5 bg-muted-foreground/50 rounded animate-pulse mt-0.5" />
                  <div className="flex-1">
                    <div className="w-full h-3 bg-muted-foreground/50 rounded animate-pulse" />
                    <div className="w-4/5 h-3 bg-muted-foreground/50 rounded animate-pulse mt-1" />
                  </div>
                </div>
              </div>
            </div>
            <div className="p-4 border-t">
              <div className="w-full h-10 bg-muted-foreground/50 rounded animate-pulse" />
            </div>
          </div>
        </div>

        {/* Details Card Placeholder */}
        <div className="mt-8 bg-card rounded-lg border shadow-sm overflow-hidden">
          <div className="p-4 border-b bg-muted">
            <div className="flex items-center">
              <div className="w-5 h-5 bg-muted-foreground/50 rounded animate-pulse mr-2" />
              <div className="w-48 h-5 bg-muted-foreground/50 rounded animate-pulse" />
            </div>
          </div>
          <div className="p-6 grid md:grid-cols-2 gap-4">
            <div>
              <div className="w-32 h-5 bg-muted-foreground/50 rounded animate-pulse mb-3" />
              <div className="space-y-2">
                {[...Array(5)].map((_, i) => (
                  <div key={i} className="flex items-center">
                    <div className="w-2 h-2 bg-muted-foreground/50 rounded-full mr-3 animate-pulse" />
                    <div className="w-48 h-3 bg-muted-foreground/50 rounded animate-pulse" />
                  </div>
                ))}
              </div>
            </div>
            <div>
              <div className="w-36 h-5 bg-muted-foreground/50 rounded animate-pulse mb-3" />
              <div className="space-y-2">
                {[...Array(5)].map((_, i) => (
                  <div key={i} className="flex items-center">
                    <div className="w-2 h-2 bg-muted-foreground/50 rounded-full mr-3 animate-pulse" />
                    <div className="w-48 h-3 bg-muted-foreground/50 rounded animate-pulse" />
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </Container>
    </PageWrap >
  );
};

export default ImportExportPlaceholder;