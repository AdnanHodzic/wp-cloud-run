const AssetManagerSkeleton = () => {
  return (
    <div className="flex flex-col text-foreground rounded-xl shadow-2xl w-full max-w-[90vw] lg:max-w-[80vw] xl:max-w-[70vw] 2xl:max-w-[60vw] max-h-[90vh] overflow-hidden">
      {/* Header with gradient background */}
      <div className="flex items-center justify-between p-4 bg-gradient-to-r from-orange-500 to-red-500 text-white border-b border-orange-500">
        <div className="flex items-center gap-2">
          <div className="size-8 bg-white/20 rounded-lg flex items-center justify-center">
            <div className="size-5 bg-white/60 rounded" />
          </div>
          <div>
            <div className="h-6 w-32 bg-white/30 rounded" />
            <div className="h-4 w-48 bg-white/20 rounded mt-1" />
          </div>
        </div>
        <div className="size-9 bg-white/20 rounded" />
      </div>

      {/* Page info section */}
      <div className="p-3 bg-blue-50 border-b flex items-center gap-5">
        <div className="min-w-0 flex items-center gap-2">
          <div className="size-4 bg-blue-300 rounded" />
          <div className="min-w-0 flex flex-col">
            <div className="h-4 w-64 bg-blue-200 rounded" />
            <div className="h-3 w-80 bg-blue-100 rounded mt-1" />
          </div>
        </div>
        <div className="shrink-0 ml-auto flex items-center gap-1">
          <div className="h-4 w-20 bg-blue-200 rounded" />
          <div className="h-4 w-24 bg-blue-300 rounded" />
        </div>
      </div>

      {/* Tabs and search section */}
      <div className="p-3 border-b border-gray-200 bg-muted grid gap-2">
        <div className="flex items-center gap-2">
          <div className="flex">
            <div className="h-8 w-24 bg-blue-300 rounded-l-lg" />
            <div className="h-8 w-24 bg-gray-200 rounded-r-lg" />
          </div>
          <div className="grow">
            <div className="relative max-w-[500px]">
              <div className="absolute left-3 top-1/2 transform -translate-y-1/2 size-4 bg-gray-300 rounded" />
              <div className="w-full h-10 bg-gray-200 rounded-lg" />
            </div>
          </div>
          <div className="ml-auto text-sm">
            <div className="h-4 w-32 bg-gray-200 rounded" />
          </div>
        </div>
      </div>

      {/* Info box */}
      <div className="border p-3 relative bg-blue-50 border-blue-200">
        <div className="flex items-start">
          <div className="size-5 mr-2 flex-shrink-0 bg-blue-300 rounded" />
          <div className="grid gap-1">
            <div className="h-4 w-96 bg-blue-200 rounded" />
          </div>
        </div>
      </div>

      {/* Asset list */}
      <div className="grow overflow-y-auto">
        <div className="bg-background p-4 max-w-full">
          <div className="space-y-2">
            {/* Asset items */}
            {[...Array(5)].map((_, i) => (
              <div key={i} className="max-w-full rounded-lg border border-gray-200">
                <div className="p-3 flex items-center gap-3 max-w-full w-full">
                  <div className="flex items-center space-x-3 flex-1 min-w-0">
                    <div className="size-3 bg-gray-300 rounded" />
                    <div className="flex items-center flex-shrink-0">
                      <div className="size-4 bg-gray-300 rounded" />
                    </div>
                    <div className="text-left min-w-0 flex-1">
                      <div className="flex items-center space-x-2 min-w-0">
                        <div className="h-4 w-32 bg-gray-300 rounded" />
                        <div className="h-5 w-12 bg-gray-200 rounded" />
                        <div className="h-5 w-20 bg-gray-200 rounded" />
                      </div>
                      <div className="h-3 w-48 bg-gray-200 rounded mt-1" />
                    </div>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="h-4 w-32 bg-gray-200 rounded" />
                    <div className="size-5 bg-gray-300 rounded" />
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Legend */}
      <div className="px-4 py-2 bg-muted border-t border-gray-200">
        <div className="flex items-center space-x-3 text-muted-foreground text-xs font-medium">
          <div className="flex items-center gap-1">
            <div className="size-3 bg-green-300 rounded" />
            <div className="h-3 w-16 bg-gray-200 rounded" />
          </div>
          <div className="flex items-center gap-1">
            <div className="size-3 bg-red-300 rounded" />
            <div className="h-3 w-16 bg-gray-200 rounded" />
          </div>
          <div className="flex items-center gap-1">
            <div className="size-3 bg-yellow-300 rounded" />
            <div className="h-3 w-16 bg-gray-200 rounded" />
          </div>
          <div className="flex items-center space-x-1 ml-auto">
            <div className="size-4 bg-yellow-300 rounded" />
            <div className="h-3 w-48 bg-gray-200 rounded" />
          </div>
        </div>
      </div>

      {/* Footer buttons */}
      <div className="flex items-center justify-between p-4 border-t border-gray-200 bg-gray-50 relative gap-3">
        <div className="h-10 w-32 bg-gray-300 rounded-lg" />
        <div className="flex gap-3 ml-auto">
          <div className="h-10 w-20 bg-gray-300 rounded-lg" />
          <div className="h-10 w-28 bg-orange-300 rounded-lg" />
        </div>
      </div>
    </div>
  );
};

export default AssetManagerSkeleton;
