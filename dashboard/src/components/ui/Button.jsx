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
      'inline-flex min-h-11 items-center justify-center whitespace-nowrap font-semibold rounded-md transition-colors duration-150 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed active:translate-y-px';

    const variants = {
      primary:
        'bg-[var(--color-ink)] text-[var(--color-paper)] hover:bg-[var(--color-ink-2)]',
      secondary:
        'bg-[var(--color-paper-3)] text-[var(--color-ink)] hover:bg-[var(--color-rule-2)]',
      danger: 'bg-[var(--color-danger)] text-[var(--color-paper)] hover:brightness-90',
      ghost:
        'bg-transparent text-[var(--color-neutral)] hover:bg-[var(--color-paper-3)] border border-[var(--color-rule-2)]',
    };

    const sizes = {
      sm: 'px-3 py-2 text-sm gap-2',
      md: 'px-4 py-2 text-sm gap-2',
      lg: 'px-6 py-3 text-lg gap-3',
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
