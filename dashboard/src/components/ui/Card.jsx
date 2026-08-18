import { forwardRef } from 'react';

const Card = forwardRef(
  ({ className = '', children, ...props }, ref) => {
    return (
      <div
        ref={ref}
        className={`bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200 ${className}`}
        {...props}
      >
        {children}
      </div>
    );
  }
);

Card.displayName = 'Card';

const CardHeader = ({ className = '', children, ...props }) => {
  return (
    <div className={`px-6 py-4 border-b border-gray-200 ${className}`} {...props}>
      {children}
    </div>
  );
};

const CardTitle = ({ className = '', children, ...props }) => {
  return (
    <h3 className={`text-lg font-semibold text-gray-900 ${className}`} {...props}>
      {children}
    </h3>
  );
};

const CardDescription = ({ className = '', children, ...props }) => {
  return (
    <p className={`text-sm text-gray-600 ${className}`} {...props}>
      {children}
    </p>
  );
};

const CardContent = ({ className = '', children, ...props }) => {
  return (
    <div className={`px-6 py-4 ${className}`} {...props}>
      {children}
    </div>
  );
};

const CardFooter = ({ className = '', children, ...props }) => {
  return (
    <div
      className={`px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-lg ${className}`}
      {...props}
    >
      {children}
    </div>
  );
};

export { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter };
export default Card;
