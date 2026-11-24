// A status is a small coloured dot beside plain text, not a filled label.
const Badge = ({ variant = 'default', className = '', children, ...props }) => {
  const dots = {
    default: 'bg-[var(--color-rule)]',
    primary: 'bg-[var(--color-ink)]',
    success: 'bg-[var(--color-success)]',
    warning: 'bg-[var(--color-warn)]',
    danger: 'bg-[var(--color-danger)]',
    published: 'bg-[var(--color-success)]',
    draft: 'bg-[var(--color-warn)]',
    pending: 'bg-[var(--color-warn)]',
  };

  return (
    <span
      className={`inline-flex items-center gap-2 whitespace-nowrap text-[13px] text-[var(--color-ink-2)] first-letter:uppercase ${className}`}
      {...props}
    >
      <span className={`h-2 w-2 shrink-0 rounded-full ${dots[variant] || dots.default}`} aria-hidden="true" />
      <span className="first-letter:uppercase">{children}</span>
    </span>
  );
};

export default Badge;
