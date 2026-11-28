// A day is kept as "YYYY-MM-DD" in the editor's own calendar, never as a
// moment, so no time zone can move it to the day before or after.
const pad = (n) => String(n).padStart(2, '0');

export const toDay = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;

export const fromDay = (day) => {
  if (!day) return undefined;
  const [y, m, d] = day.split('-').map(Number);
  return new Date(y, m - 1, d);
};

/** The day of a moment from the server, in the editor's own calendar. */
export const dayOf = (iso) => (iso ? toDay(new Date(iso)) : '');

/** Today, in the editor's own calendar. */
export const today = () => toDay(new Date());
