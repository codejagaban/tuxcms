{{--
    Shopfront's own icons, drawn in the dashboard's manner. One house style
    throughout: a 24px grid, a 1.6 stroke with round ends, and one part washed
    in a tint of the icon's own colour, as Phosphor's duotone weight is. The
    colour comes from where the icon sits: ink, or one of the dashboard's four
    marker colours.

    The names cover every icon the editor offers for features, plus the ones
    the theme uses itself. An unknown name falls back to the box.
--}}
@php $name = $name ?? 'box'; @endphp
<svg class="glyph {{ $class ?? '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($name)
        @case('sparkles')
            <path class="glyph__lit" d="M10 3c.6 4.4 2.6 6.4 7 7-4.4.6-6.4 2.6-7 7-.6-4.4-2.6-6.4-7-7 4.4-.6 6.4-2.6 7-7z"/>
            <path d="M18 14.5c.3 1.9 1 2.6 2.9 2.9-1.9.3-2.6 1-2.9 2.9-.3-1.9-1-2.6-2.9-2.9 1.9-.3 2.6-1 2.9-2.9z"/>
            @break
        @case('zap')
            <path class="glyph__lit" d="M13.5 2.5 5 13.5h6l-1 8 8.5-11h-6l1-8z"/>
            @break
        @case('shield')
            <path d="M12 3l7.5 2.8v5.7c0 4.6-3.2 8.2-7.5 9.5-4.3-1.3-7.5-4.9-7.5-9.5V5.8L12 3z"/>
            <path class="glyph__lit" d="M12 7.2l4 1.5v3c0 2.5-1.7 4.4-4 5.2-2.3-.8-4-2.7-4-5.2v-3l4-1.5z"/>
            @break
        @case('star')
            <path class="glyph__lit" d="M12 3.2l2.7 5.5 6 .9-4.4 4.2 1 6-5.3-2.8-5.3 2.8 1-6-4.4-4.2 6-.9L12 3.2z"/>
            @break
        @case('compass')
            <circle cx="12" cy="12" r="9"/>
            <path class="glyph__lit" d="M12 5l2.6 7H9.4L12 5z"/>
            <path d="M9.4 12 12 19l2.6-7"/>
            @break
        @case('code')
            <path d="M8.5 7 3.5 12l5 5M15.5 7l5 5-5 5"/>
            <rect class="glyph__lit" x="10.75" y="7.5" width="2.5" height="9" rx="1.25" transform="rotate(16 12 12)"/>
            @break
        @case('layers')
            <path class="glyph__lit" d="M12 3.5 20.5 8 12 12.5 3.5 8 12 3.5z"/>
            <path d="M3.5 12 12 16.5l8.5-4.5M3.5 16 12 20.5l8.5-4.5"/>
            @break
        @case('pen-tool')
            <path d="M12 3l6 6.5-2.5 7.5h-7L6 9.5 12 3zM12 3v6.4M8.5 20.5h7"/>
            <circle class="glyph__lit" cx="12" cy="11.5" r="2"/>
            @break
        @case('gauge')
            <path d="M4 16.5a8 8 0 1 1 16 0M12 16.5l3.5-5.5"/>
            <circle class="glyph__lit" cx="12" cy="16.5" r="2.4"/>
            @break
        @case('users')
            <circle cx="16.5" cy="8.5" r="2.6"/>
            <path d="M15.2 13.8c2.7-.3 4.8 1.4 5.5 4.4"/>
            <circle class="glyph__lit" cx="9" cy="8.5" r="3.2"/>
            <path d="M3.5 19c.6-3.2 2.8-5 5.5-5s4.9 1.8 5.5 5"/>
            @break
        @case('mail')
            <rect x="3.5" y="5.5" width="17" height="13" rx="2.5"/>
            <path d="M4 7.5l8 5.5 8-5.5"/>
            <circle class="glyph__lit" cx="19.5" cy="5.5" r="2.6"/>
            @break
        @case('phone')
            <path d="M6.6 3.5h2.6l1.4 4.1-2 1.4a11.5 11.5 0 0 0 6.4 6.4l1.4-2 4.1 1.4v2.6a2.4 2.4 0 0 1-2.6 2.4A15.6 15.6 0 0 1 4.2 6.1a2.4 2.4 0 0 1 2.4-2.6z"/>
            <circle class="glyph__lit" cx="17.8" cy="6.2" r="2.4"/>
            @break
        @case('pin')
            <path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11z"/>
            <circle class="glyph__lit" cx="12" cy="10" r="2.5"/>
            @break
        @case('clock')
            <path class="glyph__lit" stroke="none" d="M12 12V3.5a8.5 8.5 0 0 1 7.36 12.75L12 12z"/>
            <circle cx="12" cy="12" r="8.5"/>
            <path d="M12 6.5V12l3.8 2.2"/>
            @break
        @case('briefcase')
            <rect x="3.5" y="7.5" width="17" height="12" rx="2.5"/>
            <path d="M9 7.5V6a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 6v1.5M3.5 12.5h7M13.5 12.5h7"/>
            <rect class="glyph__lit" x="10.25" y="11" width="3.5" height="3.2" rx="1"/>
            @break
        @case('coin')
            <circle cx="15" cy="9.5" r="6"/>
            <circle class="glyph__lit" cx="9.5" cy="14.5" r="6"/>
            <circle cx="9.5" cy="14.5" r="2.8"/>
            @break
        @case('calendar')
            <rect x="3.5" y="5" width="17" height="15" rx="2.5"/>
            <path d="M3.5 10h17M8 3v4M16 3v4"/>
            <rect class="glyph__lit" x="7" y="13" width="4" height="3.8" rx="1"/>
            @break
        @default
            <path class="glyph__lit" d="M12 3.5 19.5 7.5 12 11.5 4.5 7.5 12 3.5z"/>
            <path d="M4.5 7.5v9l7.5 4v-9M19.5 7.5v9l-7.5 4"/>
    @endswitch
</svg>
