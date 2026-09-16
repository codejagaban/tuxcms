import { useRef, useEffect } from 'react';

/**
 * Inline-editable text rendered directly on the preview (contentEditable).
 * Click it and type — changes flow up through onChange as plain text.
 *
 * The DOM is treated as uncontrolled while focused (so the caret never jumps)
 * and re-synced from `value` only when the value changes externally and the
 * element isn't being edited.
 */
const Editable = ({
  value,
  onChange,
  as: Tag = 'div',
  multiline = false,
  placeholder = 'Empty',
  className = '',
  ...rest
}) => {
  const ref = useRef(null);

  // Seed the initial text once.
  useEffect(() => {
    if (ref.current) ref.current.innerText = value ?? '';
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // Re-sync when the value changes from outside (undo, load, list edits)
  // but never while the user is actively typing in this element.
  useEffect(() => {
    const el = ref.current;
    if (el && document.activeElement !== el && el.innerText !== (value ?? '')) {
      el.innerText = value ?? '';
    }
  }, [value]);

  const handleInput = (e) => {
    onChange(e.currentTarget.innerText);
  };

  const handleKeyDown = (e) => {
    if (!multiline && e.key === 'Enter') {
      e.preventDefault();
      e.currentTarget.blur();
    }
    e.stopPropagation(); // don't let typing trigger editor-level shortcuts
  };

  return (
    <Tag
      ref={ref}
      contentEditable
      role="textbox"
      aria-multiline={multiline}
      aria-label={`Edit ${placeholder.toLowerCase()}`}
      suppressContentEditableWarning
      spellCheck={false}
      onInput={handleInput}
      onKeyDown={handleKeyDown}
      data-editable="true"
      data-empty={!value}
      data-placeholder={placeholder}
      className={`tux-editable ${className}`}
      {...rest}
    />
  );
};

export default Editable;
