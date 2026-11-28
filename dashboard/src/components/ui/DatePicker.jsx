import { useEffect, useId, useRef, useState } from 'react';
import { DayPicker } from 'react-day-picker';
import { enGB } from 'react-day-picker/locale';
import { CalendarBlank, CaretLeft, CaretRight } from '../../lib/icons';
import { fromDay, toDay } from '../../lib/days';

const spelled = (day) => fromDay(day)?.toLocaleDateString(undefined, { day: 'numeric', month: 'long', year: 'numeric' });

const classNames = {
  root: 'date-picker__calendar',
  months: 'relative',
  month_caption: 'flex h-9 items-center px-1.5 text-sm font-semibold text-[var(--color-ink)]',
  nav: 'absolute right-0 top-0 flex gap-0.5',
  button_previous: 'date-picker__step',
  button_next: 'date-picker__step',
  month_grid: 'mt-1 w-full border-collapse',
  weekdays: '',
  weekday: 'h-8 w-9 text-center text-[11px] font-medium text-[var(--color-muted)]',
  week: '',
  day: 'date-picker__day p-0 text-center',
  day_button: 'date-picker__day-button',
  today: 'date-picker__day--today',
  selected: 'date-picker__day--selected',
  outside: 'date-picker__day--outside',
  disabled: 'date-picker__day--disabled',
};

/**
 * A date, chosen from a calendar. The field shows the day written out; a
 * click opens the month, with the arrows (and the arrow keys) to move
 * through it, Today, and Clear when empty is allowed.
 */
export default function DatePicker({ value, onChange, label, placeholder = 'Choose a date', clearable = true }) {
  const [open, setOpen] = useState(false);
  const [month, setMonth] = useState(() => fromDay(value) || new Date());
  const box = useRef(null);
  const trigger = useRef(null);
  const panelId = useId();

  // A click anywhere else, or Escape, closes the calendar.
  useEffect(() => {
    if (!open) return undefined;
    const away = (event) => { if (!box.current?.contains(event.target)) setOpen(false); };
    const escape = (event) => {
      if (event.key === 'Escape') { setOpen(false); trigger.current?.focus(); }
    };
    document.addEventListener('mousedown', away);
    document.addEventListener('keydown', escape);
    return () => { document.removeEventListener('mousedown', away); document.removeEventListener('keydown', escape); };
  }, [open]);

  const choose = (day) => {
    onChange(day);
    setOpen(false);
    trigger.current?.focus();
  };

  const toggle = () => {
    // Opening shows the month of the chosen day, or this month.
    if (!open) setMonth(fromDay(value) || new Date());
    setOpen((current) => !current);
  };

  return (
    <div ref={box} className="relative">
      <button
        ref={trigger}
        type="button"
        onClick={toggle}
        aria-haspopup="dialog"
        aria-expanded={open}
        aria-controls={open ? panelId : undefined}
        aria-label={`${label}: ${value ? spelled(value) : 'not set'}`}
        className="field-control date-picker__field"
      >
        <CalendarBlank className="h-[18px] w-[18px] shrink-0 text-[var(--color-muted)]" />
        <span className={value ? 'text-[var(--color-ink)]' : 'text-[var(--color-muted)]'}>{value ? spelled(value) : placeholder}</span>
      </button>

      {open && (
        <div id={panelId} role="dialog" aria-label={label} className="date-picker__panel glass-panel enter-drop">
          <DayPicker
            mode="single"
            locale={enGB}
            autoFocus
            month={month}
            onMonthChange={setMonth}
            selected={fromDay(value)}
            onSelect={(date) => date && choose(toDay(date))}
            showOutsideDays
            fixedWeeks
            classNames={classNames}
            components={{
              Chevron: ({ orientation }) => (orientation === 'left'
                ? <CaretLeft className="h-4 w-4" weight="bold" />
                : <CaretRight className="h-4 w-4" weight="bold" />),
            }}
          />
          <div className="mt-2 flex items-center justify-between border-t border-[var(--color-rule-2)] pt-2">
            <button type="button" className="date-picker__action" onClick={() => choose(toDay(new Date()))}>Today</button>
            {clearable && value && (
              <button type="button" className="date-picker__action text-[var(--color-muted)]" onClick={() => choose('')}>Clear</button>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
