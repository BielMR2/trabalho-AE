export const Loading = () => (
  <div className="animate-pulse flex gap-4 p-4 w-full max-w-sm" data-testid="loading">
    <div className="rounded-full bg-border h-10 w-10 shrink-0" />
    <div className="flex-1 space-y-3 py-1">
      <div className="h-3 bg-border rounded-[var(--radius-sm)] w-3/4" />
      <div className="space-y-2">
        <div className="grid grid-cols-3 gap-3">
          <div className="h-3 bg-border rounded-[var(--radius-sm)] col-span-2" />
          <div className="h-3 bg-border rounded-[var(--radius-sm)] col-span-1" />
        </div>
        <div className="h-3 bg-border rounded-[var(--radius-sm)] w-5/6" />
      </div>
    </div>
  </div>
);
