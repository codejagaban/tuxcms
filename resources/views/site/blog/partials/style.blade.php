{{-- The blog's own few rules. Type and colour come from the theme: its body
     font and ink are inherited, and a theme that names --display, --mono,
     --muted, --rule, --paper, --gutter or --radius has those used too. Every
     rule is scoped under .blog so nothing leaks into the theme. --}}
<style>
    .blog { --blog-muted: var(--muted, color-mix(in oklab, currentColor 62%, transparent)); --blog-rule: var(--rule, color-mix(in oklab, currentColor 14%, transparent)); --blog-radius: var(--radius-s, 0.625rem); width: min(100% - 2 * var(--gutter, 1.25rem), 46rem); margin-inline: auto; padding-block: clamp(2.5rem, 7vw, 5rem) clamp(4rem, 9vw, 7rem); }
    .blog a { color: inherit; }
    .blog__title { margin: 0; font-family: var(--display, inherit); font-size: clamp(2.4rem, 6vw, 4rem); font-weight: 500; line-height: 1.04; letter-spacing: -0.035em; text-wrap: balance; }
    .blog__lead { margin: 1.25rem 0 0; font-size: clamp(1.1rem, 1.6vw, 1.3rem); line-height: 1.55; color: var(--blog-muted); }
    .blog__meta { margin: 1.5rem 0 0; font-size: 0.9375rem; color: var(--blog-muted); }
    .blog__meta a { text-decoration: none; }
    .blog__meta a:hover { color: var(--ink, currentColor); text-decoration: underline; text-underline-offset: 0.2em; }
    .blog__back { display: inline-block; margin-bottom: 1.75rem; font-size: 0.9375rem; color: var(--blog-muted); text-decoration: none; }
    .blog__back:hover { color: var(--ink, currentColor); }

    .blog__tags { display: flex; flex-wrap: wrap; gap: 0.5rem 1.25rem; margin: 1.75rem 0 0; padding: 0; list-style: none; font-size: 0.9375rem; }
    .blog__tags a { color: var(--blog-muted); text-decoration: none; }
    .blog__tags a:hover, .blog__tags a[aria-current] { color: var(--ink, currentColor); }
    .blog__tags a[aria-current] { font-weight: 600; }

    .blog__list { display: grid; gap: clamp(2.75rem, 6vw, 4rem); margin: clamp(2.5rem, 6vw, 4rem) 0 0; padding: 0; list-style: none; }
    .blog__cover { display: block; width: 100%; aspect-ratio: 16 / 9; object-fit: cover; border-radius: var(--blog-radius); margin-bottom: 1.25rem; }
    .blog__when { margin: 0; font-size: 0.875rem; color: var(--blog-muted); }
    .blog__name { margin: 0.5rem 0 0; font-family: var(--display, inherit); font-size: clamp(1.5rem, 3vw, 1.9rem); font-weight: 500; line-height: 1.15; letter-spacing: -0.025em; text-wrap: balance; }
    .blog__name a { text-decoration: none; }
    .blog__name a:hover { text-decoration: underline; text-decoration-thickness: 1px; text-underline-offset: 0.18em; }
    .blog__summary { margin: 0.75rem 0 0; color: var(--blog-muted); }

    .blog__turn { display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; gap: 1rem; margin-top: clamp(3rem, 7vw, 4.5rem); padding-top: 1.5rem; border-top: 1px solid var(--blog-rule); font-size: 0.9375rem; }
    .blog__turn a { font-weight: 500; text-decoration: none; }
    .blog__turn a:hover { text-decoration: underline; text-underline-offset: 0.2em; }
    .blog__turn span:last-child { text-align: right; }
    .blog__turn-where { color: var(--blog-muted); }

    .blog__figure { margin: clamp(2rem, 5vw, 3rem) 0 0; }
    .blog__figure img { width: 100%; height: auto; border-radius: var(--blog-radius); }

    .blog__body { margin-top: clamp(2rem, 5vw, 3rem); font-size: 1.0625rem; line-height: 1.7; overflow-wrap: anywhere; }
    .blog__body > * { margin: 0; }
    .blog__body > * + * { margin-top: 1.15em; }
    .blog__body h2, .blog__body h3, .blog__body h4 { font-family: var(--display, inherit); font-weight: 500; letter-spacing: -0.02em; line-height: 1.2; }
    .blog__body h2 { margin-top: 2em; font-size: 1.65rem; }
    .blog__body h3 { margin-top: 1.75em; font-size: 1.3rem; }
    .blog__body h4 { margin-top: 1.5em; font-size: 1.1rem; }
    .blog__body a { text-decoration: underline; text-decoration-thickness: 1px; text-underline-offset: 0.2em; }
    .blog__body ul, .blog__body ol { padding-left: 1.35rem; }
    .blog__body ul { list-style: disc; }
    .blog__body ol { list-style: decimal; }
    .blog__body li + li { margin-top: 0.4em; }
    .blog__body blockquote { padding: 0.25rem 0 0.25rem 1.25rem; border-left: 3px solid var(--blog-rule); font-size: 1.15rem; }
    .blog__body img { width: 100%; height: auto; border-radius: var(--blog-radius); }
    .blog__body hr { height: 1px; border: 0; background: var(--blog-rule); margin-block: 2.25em; }
    .blog__body code { font-family: var(--mono, ui-monospace, monospace); font-size: 0.9em; }
    .blog__body :not(pre) > code { padding: 0.1em 0.35em; border-radius: 0.3em; background: var(--blog-rule); }
    .blog__body pre { overflow-x: auto; padding: 1rem 1.25rem; border-radius: var(--blog-radius); background: var(--paper, color-mix(in oklab, currentColor 6%, transparent)); border: 1px solid var(--blog-rule); line-height: 1.55; }

    .blog__onward { display: grid; gap: 1.5rem; margin-top: clamp(3rem, 7vw, 4.5rem); padding-top: 1.75rem; border-top: 1px solid var(--blog-rule); }
    @media (min-width: 40rem) { .blog__onward { grid-template-columns: 1fr 1fr; } .blog__onward > :last-child { text-align: right; } }
    .blog__onward a { text-decoration: none; }
    .blog__onward small { display: block; font-size: 0.875rem; color: var(--blog-muted); }
    .blog__onward strong { display: block; margin-top: 0.25rem; font-family: var(--display, inherit); font-weight: 500; font-size: 1.15rem; line-height: 1.25; }
    .blog__onward a:hover strong { text-decoration: underline; text-decoration-thickness: 1px; text-underline-offset: 0.18em; }
</style>
