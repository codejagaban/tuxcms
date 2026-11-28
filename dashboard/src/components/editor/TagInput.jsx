import { useId, useState } from 'react';
import { X } from '../../lib/icons';

/**
 * A list of tags typed one at a time. Enter or a comma adds what has been
 * typed; tags already used on other posts are offered as it is typed.
 */
export default function TagInput({ value, onChange, suggestions = [], label = 'Tags', max = 20 }) {
  const [draft, setDraft] = useState('');
  const listId = useId();
  const inputId = useId();
  const taken = new Set(value.map((tag) => tag.toLowerCase()));

  /** Add one or more typed tags in a single change. */
  const add = (...raws) => {
    setDraft('');
    const next = [...value];
    const seen = new Set(taken);
    for (const raw of raws) {
      const tag = raw.trim().replace(/\s+/g, ' ').slice(0, 50);
      if (!tag || seen.has(tag.toLowerCase()) || next.length >= max) continue;
      // A tag already in use keeps the spelling it was first given.
      const known = suggestions.find((existing) => existing.toLowerCase() === tag.toLowerCase());
      next.push(known || tag);
      seen.add(tag.toLowerCase());
    }
    if (next.length !== value.length) onChange(next);
  };

  const remove = (tag) => onChange(value.filter((existing) => existing !== tag));

  return (
    <div>
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
        list={listId}
        value={draft}
        maxLength={50}
        disabled={value.length >= max}
        onChange={(event) => {
          const next = event.target.value;
          if (!next.includes(',')) { setDraft(next); return; }
          // Everything before the last comma is done; what follows is still being typed.
          const parts = next.split(',');
          const rest = parts.pop();
          add(...parts);
          setDraft(rest.trimStart());
        }}
        onKeyDown={(event) => {
          if (event.key === 'Enter') { event.preventDefault(); add(draft); }
          if (event.key === 'Backspace' && !draft && value.length) remove(value[value.length - 1]);
        }}
        onBlur={() => add(draft)}
        placeholder={value.length >= max ? `Up to ${max} tags` : 'Add a tag'}
        className="field-control"
      />
      <datalist id={listId}>
        {suggestions.filter((tag) => !taken.has(tag.toLowerCase())).map((tag) => <option key={tag} value={tag} />)}
      </datalist>
    </div>
  );
}
