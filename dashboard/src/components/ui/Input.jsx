import { forwardRef } from 'react';

const Input = forwardRef(
  (
    {
      label,
      error,
      type = 'text',
      className = '',
      containerClassName = '',
      ...props
    },
    ref
  ) => {
    return (
      <div className={`flex flex-col ${containerClassName}`}>
        {label && (
          <label className="mb-1.5 text-sm font-medium text-[var(--color-ink-2)]">
            {label}
          </label>
        )}
        <input
          ref={ref}
          type={type}
          aria-invalid={error ? 'true' : undefined}
          className={`field-control ${
            error ? 'border-[var(--color-danger)]' : ''
          } ${className}`}
          {...props}
        />
        {error && (
          <p className="mt-1.5 min-h-[1lh] text-sm font-medium text-[var(--color-danger)]">{error}</p>
        )}
      </div>
    );
  }
);

Input.displayName = 'Input';

export default Input;
