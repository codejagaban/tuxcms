const Spinner = ({ size = 'md', className = '' }) => {
  const sizes = {
    sm: 'h-4 w-4',
    md: 'h-6 w-6',
    lg: 'h-8 w-8',
    xl: 'h-12 w-12',
  };

  return (
    <div
      role="status"
      aria-label="Loading"
      className={`inline-block animate-spin rounded-full border-2 border-[var(--color-rule)] border-t-[var(--color-ink)] ${sizes[size]} ${className}`}
    />
  );
};

export default Spinner;
