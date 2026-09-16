import { forwardRef, useEffect, useRef } from 'react';
import { X } from '@phosphor-icons/react';

const Modal = forwardRef(
  ({ isOpen, onClose, className = '', children, ...props }, ref) => {
    const dialogRef = useRef(null);

    useEffect(() => {
      if (!isOpen) return undefined;
      const previousFocus = document.activeElement;
      const dialog = dialogRef.current;
      const focusable = () => [...(dialog?.querySelectorAll('button:not([disabled]), a[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])') || [])];
      focusable()[0]?.focus();

      const onKeyDown = (event) => {
        if (event.key === 'Escape') {
          event.preventDefault();
          onClose?.();
          return;
        }
        if (event.key !== 'Tab') return;
        const items = focusable();
        if (!items.length) return;
        const first = items[0];
        const last = items.at(-1);
        if (event.shiftKey && document.activeElement === first) {
          event.preventDefault();
          last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
          event.preventDefault();
          first.focus();
        }
      };

      document.addEventListener('keydown', onKeyDown);
      return () => {
        document.removeEventListener('keydown', onKeyDown);
        previousFocus?.focus?.();
      };
    }, [isOpen, onClose]);

    if (!isOpen) return null;

    return (
      <div className="fixed inset-0 z-50 grid place-items-center p-4">
        <div
          className="fixed inset-0 bg-black/50 transition-opacity"
          onClick={onClose}
        />
        <div
          ref={(node) => {
            dialogRef.current = node;
            if (typeof ref === 'function') ref(node);
            else if (ref) ref.current = node;
          }}
          role="dialog"
          aria-modal="true"
          aria-label={props['aria-label'] || 'Dialog'}
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
