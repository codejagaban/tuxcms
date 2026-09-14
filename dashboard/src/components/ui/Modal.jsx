import { forwardRef } from 'react';
import { X } from '@phosphor-icons/react';

const Modal = forwardRef(
  ({ isOpen, onClose, className = '', children, ...props }, ref) => {
    if (!isOpen) return null;

    return (
      <div className="fixed inset-0 z-50 grid place-items-center p-4">
        <div
          className="fixed inset-0 bg-black/50 transition-opacity"
          onClick={onClose}
        />
        <div
          ref={ref}
          role="dialog"
          aria-modal="true"
          className={`relative z-50 max-h-[90dvh] overflow-y-auto rounded-lg border border-[var(--color-rule-2)] bg-[var(--color-paper)] shadow-[0_12px_28px_oklch(18%_0.01_95_/_0.14)] ${className}`}
          {...props}
        >
          {children}
        </div>
      </div>
    );
  }
);

Modal.displayName = 'Modal';

const ModalHeader = ({ className = '', children, onClose, ...props }) => {
  return (
    <div
      className={`flex items-center justify-between border-b border-[var(--color-rule-2)] px-6 py-4 ${className}`}
      {...props}
    >
      {children}
      {onClose && (
        <button
          onClick={onClose}
          className="icon-button -mr-2"
          aria-label="Close dialog"
        >
          <X className="h-5 w-5" weight="bold" />
        </button>
      )}
    </div>
  );
};

const ModalTitle = ({ className = '', children, ...props }) => {
  return (
    <h2 className={`text-lg font-bold text-[var(--color-ink)] ${className}`} {...props}>
      {children}
    </h2>
  );
};

const ModalContent = ({ className = '', children, ...props }) => {
  return (
    <div className={`px-6 py-4 ${className}`} {...props}>
      {children}
    </div>
  );
};

const ModalFooter = ({ className = '', children, ...props }) => {
  return (
    <div
      className={`flex items-center justify-end gap-2 rounded-b-lg border-t border-[var(--color-rule-2)] bg-[var(--color-paper-2)] px-6 py-4 ${className}`}
      {...props}
    >
      {children}
    </div>
  );
};

export { Modal, ModalHeader, ModalTitle, ModalContent, ModalFooter };
export default Modal;
