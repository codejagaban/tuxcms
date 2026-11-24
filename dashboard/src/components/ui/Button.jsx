import { forwardRef } from 'react';
import { CircleNotch } from '@phosphor-icons/react';

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
      'inline-flex items-center justify-center whitespace-nowrap font-semibold rounded-md transition-colors duration-150 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed active:translate-y-px';

    const variants = {
      primary:
        'bg-[var(--color-accent)] text-[var(--color-accent-ink)] hover:bg-[var(--color-accent-strong)]',
      secondary:
        'bg-[var(--color-paper-3)] text-[var(--color-ink)] hover:bg-[var(--color-rule-2)]',
      danger: 'bg-[var(--color-danger)] text-[var(--color-paper)] hover:brightness-90',
      dangerGhost:
        'bg-transparent text-[var(--color-danger)] hover:bg-[var(--color-danger-soft)] border border-[var(--color-rule-2)]',
      ghost:
        'bg-transparent text-[var(--color-neutral)] hover:bg-[var(--color-paper-3)] border border-[var(--color-rule-2)]',
    };

    const sizes = {
      sm: 'min-h-9 px-2.5 py-1.5 text-sm gap-2',
      md: 'min-h-10 px-4 py-2 text-sm gap-2',
      lg: 'min-h-11 px-6 py-3 text-lg gap-3',
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
