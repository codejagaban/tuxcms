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
          <label className="mb-2 text-sm font-medium text-neutral-700">
            {label}
          </label>
        )}
        <input
          ref={ref}
          type={type}
          className={`px-4 py-2.5 bg-white border rounded-md transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-1 ${
            error ? 'border-black' : 'border-neutral-300'
          } ${className}`}
          {...props}
        />
        {error && (
          <p className="mt-1.5 text-sm font-medium text-black">{error}</p>
        )}
      </div>
    );
  }
);

Input.displayName = 'Input';

export default Input;
