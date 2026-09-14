# TuxCMS dashboard design system

TuxCMS is a publishing workbench for website owners and content editors. The interface prioritises finding content, editing it, checking its state, and publishing it with confidence.

## Direction

- Genre: modern-minimal
- Macrostructure: Workbench
- Navigation: compact operational side rail on desktop, modal sheet on mobile
- Tone: precise, calm, utilitarian
- Theme: custom achromatic paper and ink
- Signature: a persistent publishing-state strip that makes “what is live?” answerable at a glance
- Enrichment: none; utility and real content carry the interface

## Principles

1. Publishing state is visible before secondary metadata.
2. Controls are direct and terse. Labels describe actions, not aspirations.
3. Surfaces are separated by tone and spacing, not decorative shadows.
4. Icons are bare Phosphor marks with one consistent weight.
5. The website preview keeps the active frontend theme intact and isolated.
6. Database, API, authentication, and publishing contracts remain unchanged.

## Typography

HK Grotesk is the display and navigation voice. Satoshi is the body and control voice. Both are self-hosted from the existing Crystal theme assets. The scale uses a 1.25 ratio, with no more than five sizes in one view.

## Colour

All palette values are OKLCH and slightly warm-tinted. “Black and white” is expressed as warm paper and ink, not pure `#000` and `#fff`. The only chromatic token is the keyboard focus ring. Destructive actions use a restrained dark red.

## Interaction

- Touch targets are at least 44px.
- Focus is immediate and visible.
- Hover states are tonal and only applied for fine pointers.
- Pressed states move inward by 1px; controls never jump upward.
- Content remains visible without animation.
- Loading keeps labels readable and uses inline progress for actions.

## Responsive behaviour

The side rail becomes a mobile sheet below 60rem. Tables become compact record cards below 40rem. The editor inspector becomes a full-width lower panel on narrow screens. Required checks: 320px, 375px, 414px, and 768px with no horizontal scroll.

## Exports

### CSS custom properties

The canonical export is [`tokens.css`](tokens.css).

### Tailwind v4

```css
@theme inline {
  --color-paper: var(--color-paper);
  --color-paper-2: var(--color-paper-2);
  --color-ink: var(--color-ink);
  --color-muted: var(--color-muted);
  --color-rule: var(--color-rule);
  --font-sans: var(--font-body); /* Native system UI for compact, highly legible controls and body copy. */
}
```

### DTCG

```json
{
  "color": {
    "paper": { "$type": "color", "$value": "oklch(97.8% 0.006 95)" },
    "ink": { "$type": "color", "$value": "oklch(18% 0.01 95)" },
    "muted": { "$type": "color", "$value": "oklch(46% 0.008 95)" },
    "rule": { "$type": "color", "$value": "oklch(76% 0.009 95)" }
  },
  "dimension": {
    "control-height": { "$type": "dimension", "$value": { "value": 44, "unit": "px" } }
  }
}
```

### shadcn variables

```css
:root {
  --background: var(--color-paper);
  --foreground: var(--color-ink);
  --card: var(--color-paper);
  --card-foreground: var(--color-ink);
  --primary: var(--color-accent);
  --primary-foreground: var(--color-accent-ink);
  --border: var(--color-rule-2);
  --ring: var(--color-focus);
}
```
