import { forwardRef } from 'react';
import { CircleNotch } from '../../lib/icons';

const Button = forwardRef(
  (
    {
      variant = 'primary',
      size = 'md',
      isLoading = false,
      disabled = false,
      className = '',
      children,
      ...props
    },
    ref
  ) => {
    const baseClasses =
      'inline-flex items-center justify-center whitespace-nowrap font-medium rounded-full transition-colors duration-150 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed active:translate-y-px';

    const variants = {
      primary:
        'bg-[var(--color-accent)] text-[var(--color-accent-ink)] hover:bg-[var(--color-accent-strong)]',
      secondary:
        'bg-[var(--color-paper)] text-[var(--color-ink)] border border-[var(--color-rule-2)] hover:bg-[var(--color-paper-3)]',
      danger: 'bg-[var(--color-danger)] text-[var(--color-paper)] hover:brightness-90',
      icon:
        'bg-transparent text-[var(--color-muted)] hover:bg-[var(--color-paper-3)] hover:text-[var(--color-ink)]',
      iconDanger:
        'bg-transparent text-[var(--color-muted)] hover:bg-[var(--color-danger-soft)] hover:text-[var(--color-danger)]',
      dangerGhost:
        'bg-transparent text-[var(--color-danger)] hover:bg-[var(--color-danger-soft)] border border-[var(--color-rule-2)]',
      ghost:
        'bg-[var(--color-paper)] text-[var(--color-ink-2)] hover:bg-[var(--color-paper-3)] border border-[var(--color-rule-2)]',
    };

    const sizes = {
      sm: 'min-h-8 px-3 py-1 text-[13px] gap-2',
      icon: 'h-8 w-8',
      md: 'min-h-9 px-4 py-1.5 text-sm gap-2',
      lg: 'min-h-11 px-5 py-2.5 text-base gap-2.5',
    };

    return (
      <button
        ref={ref}
        disabled={isLoading || disabled}
        className={`${baseClasses} ${variants[variant]} ${sizes[size]} ${className}`}
        {...props}
      >
        {isLoading && <CircleNotch className="h-4 w-4 animate-spin" weight="bold" />}
        {children}
      </button>
    );
  }
);

Button.displayName = 'Button';

export default Button;
