import { forwardRef } from 'react';

const Card = forwardRef(
  ({ className = '', children, ...props }, ref) => {
    return (
      <div
        ref={ref}
        className={`work-surface ${className}`}
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
    <div className={`border-b border-[var(--color-rule-2)] px-6 py-5 ${className}`} {...props}>
      {children}
    </div>
  );
};

const CardTitle = ({ className = '', children, ...props }) => {
  return (
    <h3 className={`text-lg font-bold tracking-[-0.02em] text-[var(--color-ink)] ${className}`} {...props}>
      {children}
    </h3>
  );
};

const CardDescription = ({ className = '', children, ...props }) => {
  return (
    <p className={`text-sm leading-6 text-[var(--color-muted)] ${className}`} {...props}>
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
      className={`rounded-b-md border-t border-[var(--color-rule-2)] bg-[var(--color-paper-2)] px-6 py-4 ${className}`}
      {...props}
    >
      {children}
    </div>
  );
};

export { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter };
export default Card;
