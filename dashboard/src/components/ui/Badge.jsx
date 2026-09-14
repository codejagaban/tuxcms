const Badge = ({ variant = 'default', className = '', children, ...props }) => {
  const variants = {
    default: 'bg-neutral-100 text-neutral-700',
    primary: 'bg-black text-white',
    success: 'bg-black text-white',
    warning: 'bg-neutral-200 text-black',
    danger: 'bg-black text-white',
    published: 'bg-black text-white',
    draft: 'bg-neutral-200 text-neutral-700',
    pending: 'bg-neutral-200 text-black',
  };

  return (
    <span
      className={`inline-flex items-center px-2 py-1 rounded-sm text-[11px] font-semibold ${variants[variant]} ${className}`}
      {...props}
    >
      {children}
    </span>
  );
};

export default Badge;
