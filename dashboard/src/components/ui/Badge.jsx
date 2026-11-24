import { CheckCircle, Clock, PencilSimpleLine, WarningCircle } from '../../lib/icons';

// A status is a soft tinted pill: a small icon and the word, in one colour.
const tones = {
  green: 'bg-[var(--color-success-soft)] text-[var(--color-success-ink)]',
  amber: 'bg-[var(--color-warn-soft)] text-[var(--color-warn-ink)]',
  red: 'bg-[var(--color-danger-soft)] text-[var(--color-danger-ink)]',
  blue: 'bg-[var(--color-blue-soft)] text-[var(--color-blue-ink)]',
  grey: 'bg-[var(--color-paper-3)] text-[var(--color-neutral)]',
};

const variants = {
  default: { tone: 'grey' },
  primary: { tone: 'blue' },
  success: { tone: 'green', icon: CheckCircle },
  warning: { tone: 'amber', icon: WarningCircle },
  danger: { tone: 'red', icon: WarningCircle },
  published: { tone: 'green', icon: CheckCircle },
  draft: { tone: 'grey', icon: PencilSimpleLine },
  pending: { tone: 'amber', icon: Clock },
};

const Badge = ({ variant = 'default', className = '', children, ...props }) => {
  const { tone, icon: Icon } = variants[variant] || variants.default;

  return (
    <span
      className={`inline-flex h-6 items-center gap-1.5 whitespace-nowrap rounded-full pl-2 pr-2.5 text-xs font-medium ${tones[tone]} ${className}`}
      {...props}
    >
      {Icon && <Icon className="h-3.5 w-3.5 shrink-0" />}
      <span className="first-letter:uppercase">{children}</span>
    </span>
  );
};

export default Badge;
