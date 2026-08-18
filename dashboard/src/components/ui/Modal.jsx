import { forwardRef } from 'react';
import { X } from 'lucide-react';

const Modal = forwardRef(
  ({ isOpen, onClose, className = '', children, ...props }, ref) => {
    if (!isOpen) return null;

    return (
      <div className="fixed inset-0 z-50 flex items-center justify-center">
        <div
          className="fixed inset-0 bg-black/50 transition-opacity"
          onClick={onClose}
        />
        <div
          ref={ref}
          className={`relative bg-white rounded-lg shadow-lg z-50 max-h-[90vh] overflow-y-auto ${className}`}
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
      className={`flex items-center justify-between px-6 py-4 border-b border-gray-200 ${className}`}
      {...props}
    >
      {children}
      {onClose && (
        <button
          onClick={onClose}
          className="p-1 text-gray-500 hover:text-gray-700 transition-colors"
        >
          <X className="h-5 w-5" />
        </button>
      )}
    </div>
  );
};

const ModalTitle = ({ className = '', children, ...props }) => {
  return (
    <h2 className={`text-lg font-semibold text-gray-900 ${className}`} {...props}>
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
      className={`px-6 py-4 border-t border-gray-200 bg-gray-50 flex items-center justify-end gap-2 rounded-b-lg ${className}`}
      {...props}
    >
      {children}
    </div>
  );
};

export { Modal, ModalHeader, ModalTitle, ModalContent, ModalFooter };
export default Modal;
