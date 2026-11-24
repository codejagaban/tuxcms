// Placeholders shown while a screen's data loads. They hold the shape of what
// is coming, so the page does not jump when it arrives.
const Skeleton = ({ className = '' }) => (
  <span aria-hidden="true" className={`skeleton block rounded-sm ${className}`} />
);

// A bordered list of rows: tables, enquiries, recent pages.
const ListSkeleton = ({ rows = 6, className = '' }) => (
  <div role="status" aria-label="Loading" className={`work-surface overflow-hidden ${className}`}>
    <div className="flex h-10 items-center gap-6 border-b border-[var(--color-rule-2)] bg-[var(--color-paper-2)] px-4">
      <Skeleton className="h-3 w-16" />
      <Skeleton className="h-3 w-12" />
      <Skeleton className="hidden h-3 w-14 sm:block" />
    </div>
    {Array.from({ length: rows }, (_, index) => (
      <div
        key={index}
        className={`flex h-12 items-center gap-6 px-4 ${index ? 'border-t border-[var(--color-rule-2)]' : ''}`}
      >
        <Skeleton className={`h-3.5 ${['w-40', 'w-56', 'w-32', 'w-48'][index % 4]}`} />
        <Skeleton className="hidden h-3.5 w-24 sm:block" />
        <Skeleton className="ml-auto h-3.5 w-16" />
      </div>
    ))}
  </div>
);

// A grid of tiles: the media library.
const GridSkeleton = ({ tiles = 8, className = '' }) => (
  <div role="status" aria-label="Loading" className={`grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4 ${className}`}>
    {Array.from({ length: tiles }, (_, index) => (
      <div key={index} className="work-surface overflow-hidden">
        <Skeleton className="aspect-[4/3] w-full rounded-none" />
        <div className="space-y-2 p-3">
          <Skeleton className="h-3.5 w-3/4" />
          <Skeleton className="h-3 w-1/3" />
        </div>
      </div>
    ))}
  </div>
);

// Stacked form fields: settings and search.
const FormSkeleton = ({ fields = 5, className = '' }) => (
  <div role="status" aria-label="Loading" className={`work-surface space-y-5 p-5 ${className}`}>
    <Skeleton className="h-4 w-36" />
    {Array.from({ length: fields }, (_, index) => (
      <div key={index} className="space-y-2">
        <Skeleton className="h-3 w-24" />
        <Skeleton className="h-10 w-full rounded-md" />
      </div>
    ))}
  </div>
);

// The whole-window state while the app starts or checks the sign-in.
const BootScreen = () => (
  <div role="status" aria-label="Loading" className="boot">
    <div className="boot__mark">TUX</div>
    <div className="boot__track"><span className="boot__bar" /></div>
  </div>
);

export { Skeleton, ListSkeleton, GridSkeleton, FormSkeleton, BootScreen };
export default Skeleton;
