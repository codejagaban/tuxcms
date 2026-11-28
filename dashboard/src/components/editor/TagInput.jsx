import { useEffect, useId, useMemo, useRef, useState } from 'react';
import { Plus, X } from '../../lib/icons';

const clean = (raw) => raw.trim().replace(/\s+/g, ' ').slice(0, 50);

/**
 * The tags on a post, chosen from a dropdown.
 *
 * Clicking the field opens every tag already used on other posts, with how
 * many posts carry it. Typing narrows the list, and a name nobody has used
 * yet is offered as "Create". The arrow keys move through the list, Enter
 * picks, Escape closes; a comma or Enter on typed words adds them as they
 * are.
 *
 * `suggestions` are { name, posts_count } as the tags endpoint gives them.
 */
export default function TagInput({ value, onChange, suggestions = [], label = 'Tags', max = 20 }) {
  const [draft, setDraft] = useState('');
  const [open, setOpen] = useState(false);
  const [active, setActive] = useState(0);
  const inputRef = useRef(null);
  const listRef = useRef(null);
  const baseId = useId();
  const inputId = `${baseId}-input`;
  const listId = `${baseId}-list`;
  const taken = useMemo(() => new Set(value.map((tag) => tag.toLowerCase())), [value]);
  const full = value.length >= max;

  // What the list offers: unused tags that contain what has been typed, then
  // a new tag when the words match none of them exactly.
  const options = useMemo(() => {
    const typed = clean(draft);
    const needle = typed.toLowerCase();
    const known = suggestions
      .filter((tag) => !taken.has(tag.name.toLowerCase()))
      .filter((tag) => !needle || tag.name.toLowerCase().includes(needle))
      .map((tag) => ({ kind: 'known', name: tag.name, count: tag.posts_count }));
    const exact = suggestions.some((tag) => tag.name.toLowerCase() === needle);
    const fresh = typed && !exact && !taken.has(needle) ? [{ kind: 'new', name: typed }] : [];
    return [...known, ...fresh];
  }, [draft, suggestions, taken]);

  /** Add one or more tags in a single change. */
  const add = (...raws) => {
    setDraft('');
    const next = [...value];
    const seen = new Set(taken);
    for (const raw of raws) {
      const tag = clean(raw);
      if (!tag || seen.has(tag.toLowerCase()) || next.length >= max) continue;
      // A tag already in use keeps the spelling it was first given.
      const known = suggestions.find((existing) => existing.name.toLowerCase() === tag.toLowerCase());
      next.push(known ? known.name : tag);
      seen.add(tag.toLowerCase());
    }
    if (next.length !== value.length) onChange(next);
    setActive(0);
  };

  const remove = (tag) => {
    onChange(value.filter((existing) => existing !== tag));
    inputRef.current?.focus();
  };

  const pick = (option) => {
    add(option.name);
    inputRef.current?.focus();
  };

  const showList = open && !full && options.length > 0;
  // The highlight stays inside the list as typing shortens it.
  const current = Math.min(active, Math.max(options.length - 1, 0));
  const activeId = showList ? `${listId}-${current}` : undefined;

  // Keep the highlighted option in sight as the arrow keys move it.
  useEffect(() => {
    if (!open) return;
    listRef.current?.querySelector('[data-active="true"]')?.scrollIntoView({ block: 'nearest' });
  }, [current, open]);

  const onKeyDown = (event) => {
    if (event.key === 'ArrowDown') {
      event.preventDefault();
      if (!open) { setOpen(true); return; }
      setActive(options.length ? (current + 1) % options.length : 0);
    } else if (event.key === 'ArrowUp') {
      event.preventDefault();
      if (!open) { setOpen(true); return; }
      setActive(options.length ? (current - 1 + options.length) % options.length : 0);
    } else if (event.key === 'Enter') {
      event.preventDefault();
      if (showList && options[current]) pick(options[current]);
      else add(draft);
    } else if (event.key === 'Escape') {
      if (open) { event.preventDefault(); setOpen(false); }
    } else if (event.key === 'Backspace' && !draft && value.length) {
      remove(value[value.length - 1]);
    } else if (event.key === 'Tab') {
      setOpen(false);
    }
  };

  return (
    <div className="relative">
      <label htmlFor={inputId} className="sr-only">{label}</label>
      {value.length > 0 && (
        <ul className="mb-2.5 flex flex-wrap gap-1.5" aria-label={label}>
          {value.map((tag) => (
            <li key={tag} className="tag-token">
              <span>{tag}</span>
              <button type="button" onClick={() => remove(tag)} aria-label={`Remove ${tag}`} title="Remove">
                <X className="h-3.5 w-3.5" weight="bold" />
              </button>
            </li>
          ))}
        </ul>
      )}
      <input
        id={inputId}
        ref={inputRef}
        role="combobox"
        aria-expanded={showList}
        aria-controls={listId}
        aria-autocomplete="list"
        aria-activedescendant={activeId}
        autoComplete="off"
        value={draft}
        maxLength={50}
        disabled={full}
        onFocus={() => setOpen(true)}
        onClick={() => setOpen(true)}
        onBlur={() => { setOpen(false); add(draft); }}
        onChange={(event) => {
          const next = event.target.value;
          setOpen(true);
          setActive(0);
          if (!next.includes(',')) { setDraft(next); return; }
          // Everything before the last comma is done; what follows is still being typed.
          const parts = next.split(',');
          const rest = parts.pop();
          add(...parts);
          setDraft(rest.trimStart());
        }}
        onKeyDown={onKeyDown}
        placeholder={full ? `Up to ${max} tags` : suggestions.length ? 'Choose or create a tag' : 'Add a tag'}
        className="field-control"
      />
      {showList && (
        <ul
          id={listId}
          ref={listRef}
          role="listbox"
          aria-label={`${label} to add`}
          className="tag-menu glass-panel enter-drop"
          // Keep the focus in the field while the list is used with a pointer.
          onMouseDown={(event) => event.preventDefault()}
        >
          {options.map((option, index) => (
            <li
              key={`${option.kind}:${option.name}`}
              id={`${listId}-${index}`}
              role="option"
              aria-selected={index === current}
              data-active={index === current}
              onMouseEnter={() => setActive(index)}
              onClick={() => pick(option)}
              className="tag-menu__option"
            >
              {option.kind === 'new' ? (
                <>
                  <Plus className="h-4 w-4 shrink-0 text-[var(--color-muted)]" weight="bold" />
                  <span className="min-w-0 flex-1 truncate">Create “{option.name}”</span>
                </>
              ) : (
                <>
                  <span className="min-w-0 flex-1 truncate">{option.name}</span>
                  <span className="shrink-0 text-xs text-[var(--color-muted)]">{option.count} {option.count === 1 ? 'post' : 'posts'}</span>
                </>
              )}
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
