import { motion as Motion } from 'motion/react';
import { List, SquaresFour } from '../../lib/icons';

const VIEWS = [['list', 'List', List], ['gallery', 'Gallery', SquaresFour]];

/**
 * Round buttons with a highlight that slides to the one chosen. By default
 * the choice of list or gallery; `options` lists others as [value, label, Icon].
 */
export default function ViewSwitch({ view, onChange, id, options = VIEWS, label: groupLabel = 'View', suffix = ' view' }) {
  return (
    <div role="radiogroup" aria-label={groupLabel} className="flex shrink-0 gap-0.5 rounded-full bg-[var(--color-paper-3)] p-0.5">
      {options.map(([value, label, Icon]) => (
        <button
          key={value}
          type="button"
          role="radio"
          aria-checked={view === value}
          aria-label={`${label}${suffix}`}
          title={`${label}${suffix}`}
          onClick={() => onChange(value)}
          className={`relative grid h-10 w-11 place-items-center rounded-full transition-colors duration-200 ${
            view === value ? 'text-[var(--color-ink)]' : 'text-[var(--color-muted)] hover:text-[var(--color-ink)]'
          }`}
        >
          {view === value && (
            <Motion.span
              layoutId={id}
              className="absolute inset-0 rounded-full bg-[var(--color-paper)] shadow-[var(--glass-chip-edge)]"
              transition={{ type: 'spring', stiffness: 460, damping: 40 }}
            />
          )}
          <Icon className="relative h-[18px] w-[18px]" />
        </button>
      ))}
    </div>
  );
}
