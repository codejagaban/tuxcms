const Badge = ({ variant = 'default', className = '', children, ...props }) => {
  const variants = {
    default: 'bg-[var(--color-paper-3)] text-[var(--color-ink-2)]',
    primary: 'bg-[var(--color-accent)] text-[var(--color-accent-ink)]',
    success: 'bg-[var(--color-accent-soft)] text-[var(--color-accent-strong)]',
    warning: 'bg-[var(--color-warn-soft)] text-[var(--color-warn)]',
    danger: 'bg-[var(--color-danger-soft)] text-[var(--color-danger)]',
    published: 'bg-[var(--color-accent-soft)] text-[var(--color-accent-strong)]',
    draft: 'bg-[var(--color-warn-soft)] text-[var(--color-warn)]',
    pending: 'bg-[var(--color-warn-soft)] text-[var(--color-warn)]',
  };

  return (
    <span
      className={`inline-flex items-center px-2 py-1 rounded-sm text-[11px] font-semibold ${variants[variant]} ${className}`}
      {...props}
    >
      {children}
    </span>
  );
};

export default Badge;
