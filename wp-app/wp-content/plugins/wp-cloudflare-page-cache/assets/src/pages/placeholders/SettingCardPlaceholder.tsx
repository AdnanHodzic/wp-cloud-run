const SettingCardPlaceholder = () => (
  <div className="lg:col-span-8 grid gap-6 lg:gap-10 items-start">
    {/* Settings Content Skeleton */}
    <div className="bg-background rounded-lg border">
      <div className="divide-y divide-muted-foreground/10">
        {[...Array(5)].map((_, i) => (
          <div key={i} className="flex items-center justify-between p-6">
            <div className="space-y-2">
              <div className="w-48 h-5 bg-muted-foreground/50 rounded animate-pulse"></div>
              <div className="w-64 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
            </div>
            <div className="w-28 h-10 bg-muted-foreground/50 rounded-md animate-pulse"></div>
          </div>
        ))}
      </div>
    </div>
  </div>
);

export default SettingCardPlaceholder;