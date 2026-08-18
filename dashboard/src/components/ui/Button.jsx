import { forwardRef } from 'react';
import { Loader2 } from 'lucide-react';

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
      'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-offset-2';

    const variants = {
      primary:
        'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500 shadow-sm hover:shadow-md',
      secondary:
        'bg-gray-200 text-gray-900 hover:bg-gray-300 focus:ring-gray-400 shadow-sm',
      danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 shadow-sm',
      ghost:
        'bg-transparent text-gray-700 hover:bg-gray-100 focus:ring-gray-300 border border-gray-300',
    };

    const sizes = {
      sm: 'px-3 py-1.5 text-sm gap-2',
      md: 'px-4 py-2 text-base gap-2',
      lg: 'px-6 py-3 text-lg gap-3',
    };

    return (
      <button
        ref={ref}
        disabled={isLoading || disabled}
        className={`${baseClasses} ${variants[variant]} ${sizes[size]} ${className}`}
        {...props}
      >
        {isLoading && <Loader2 className="h-4 w-4 animate-spin" />}
        {children}
      </button>
    );
  }
);

Button.displayName = 'Button';

export default Button;
