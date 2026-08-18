{{--
    Inline stroke icons on a 24px grid, matching the set the editor offers.
    Kept inline so the static build has no icon-font or sprite dependency.
    Unknown names fall back to the neutral `box` mark.
--}}
@php $name = $name ?? 'box'; @endphp
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
     class="{{ $class ?? 'h-5 w-5' }}" aria-hidden="true" focusable="false">
    @switch($name)
        @case('sparkles')
            <path d="M12 3l1.9 4.6L18.5 9.5l-4.6 1.9L12 16l-1.9-4.6L5.5 9.5l4.6-1.9z"/>
            <path d="M18 15l.8 2L21 17.8l-2.2.8L18 21l-.8-2.4L15 17.8l2.2-.8z"/>
            @break
        @case('zap')
            <path d="M13 2L4 14h7l-1 8 9-12h-7z"/>
            @break
        @case('shield')
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            @break
        @case('star')
            <path d="M12 3l2.9 5.9 6.6.9-4.8 4.6 1.2 6.5L12 17.8 6.1 20.9l1.2-6.5L2.5 9.8l6.6-.9z"/>
            @break
        @case('compass')
            <circle cx="12" cy="12" r="9"/>
            <path d="M15.5 8.5l-2 5-5 2 2-5z"/>
            @break
        @case('code')
            <path d="M9 18l-6-6 6-6"/>
            <path d="M15 6l6 6-6 6"/>
            @break
        @case('layers')
            <path d="M12 3l9 5-9 5-9-5z"/>
            <path d="M3 13l9 5 9-5"/>
            @break
        @case('pen-tool')
            <path d="M12 3l7 7-9 9-5 2 2-5z"/>
            <circle cx="12.5" cy="10.5" r="1.5"/>
            @break
        @case('gauge')
            <path d="M3.5 17a9 9 0 1 1 17 0"/>
            <path d="M12 17l4-5"/>
            @break
        @case('users')
            <circle cx="9" cy="8" r="3.5"/>
            <path d="M2.5 20a6.5 6.5 0 0 1 13 0"/>
            <path d="M17 5.2a3.5 3.5 0 0 1 0 6.6"/>
            <path d="M18 14.4a6.5 6.5 0 0 1 3.5 5.6"/>
            @break
        @default
            <path d="M12 3l8 4.5v9L12 21l-8-4.5v-9z"/>
            <path d="M4 7.5l8 4.5 8-4.5"/>
            <path d="M12 12v9"/>
    @endswitch
</svg>
