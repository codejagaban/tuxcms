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
          <label className="mb-2 text-sm font-medium text-gray-700">
            {label}
          </label>
        )}
        <input
          ref={ref}
          type={type}
          className={`px-4 py-2 border rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-0 focus:border-transparent ${
            error ? 'border-red-500' : 'border-gray-300'
          } ${className}`}
          {...props}
        />
        {error && (
          <p className="mt-1 text-sm text-red-600">{error}</p>
        )}
      </div>
    );
  }
);

Input.displayName = 'Input';

export default Input;
