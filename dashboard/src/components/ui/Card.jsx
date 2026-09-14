import { forwardRef } from 'react';

const Card = forwardRef(
  ({ className = '', children, ...props }, ref) => {
    return (
      <div
        ref={ref}
        className={`bg-white rounded-md border border-neutral-200 ${className}`}
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
    <div className={`px-6 py-5 border-b border-neutral-200 ${className}`} {...props}>
      {children}
    </div>
  );
};

const CardTitle = ({ className = '', children, ...props }) => {
  return (
    <h3 className={`text-lg font-semibold tracking-[-0.02em] text-black ${className}`} {...props}>
      {children}
    </h3>
  );
};

const CardDescription = ({ className = '', children, ...props }) => {
  return (
    <p className={`text-sm leading-6 text-neutral-600 ${className}`} {...props}>
      {children}
    </p>
  );
};

const CardContent = ({ className = '', children, ...props }) => {
  return (
    <div className={`px-6 py-5 ${className}`} {...props}>
      {children}
    </div>
  );
};

const CardFooter = ({ className = '', children, ...props }) => {
  return (
    <div
      className={`px-6 py-4 border-t border-neutral-200 bg-neutral-50 rounded-b-md ${className}`}
      {...props}
    >
      {children}
    </div>
  );
};

export { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter };
export default Card;
