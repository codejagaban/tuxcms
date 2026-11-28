@php
    use App\Support\Site;
    // The name set as the dashboard sets its own: the first part heavy, tight
    // and in capitals, and a trailing run of capitals (TuxCMS's "CMS") small
    // beside it. A name with no such run is set whole.
    $name = Site::name();
    preg_match('/^(.+?)\s*([A-Z]{2,})$/u', $name, $parts);
    [$mark, $tail] = $parts ? [$parts[1], $parts[2]] : [$name, null];
@endphp
{{-- Navigation comes from the page tree. Both menus are <details>, so they
     open with no JavaScript on a fully static site. --}}
<header class="bar">
    <div class="wrap bar__in">
        <a href="/" class="bar__name" aria-label="{{ $name }}, front page">
            @if ($logo = Site::logo())
                <img src="{{ $logo }}" alt="{{ $name }}">
            @else
                <span class="bar__mark" aria-hidden="true">{{ $mark }}</span>
                @if ($tail)
                    <span class="bar__tail" aria-hidden="true">{{ $tail }}</span>
                @endif
            @endif
        </a>

        @if (! empty($navigation))
            <nav class="bar__links" aria-label="Main">
                @foreach ($navigation as $item)
                    @if (empty($item['children']))
                        <a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a>
                    @else
                        <details class="bar__group">
                            <summary @class(['is-here' => $item['active']])>{{ $item['label'] }}@include('site.themes.shopfront.partials.caret')</summary>
                            <div class="bar__drop">
                                <a href="{{ $item['url'] }}">All {{ strtolower($item['label']) }}</a>
                                @foreach ($item['children'] as $child)
                                    <a href="{{ $child['url'] }}" @if ($child['active'] ?? false) aria-current="page" @endif>{{ $child['label'] }}</a>
                                @endforeach
                            </div>
                        </details>
                    @endif
                @endforeach
            </nav>

            <details class="bar__menu">
                <summary>Menu @include('site.themes.shopfront.partials.caret')</summary>
                <nav aria-label="Main">
                    @foreach ($navigation as $item)
                        <a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a>
                        @foreach ($item['children'] as $child)
                            <a href="{{ $child['url'] }}" class="is-child" @if ($child['active'] ?? false) aria-current="page" @endif>{{ $child['label'] }}</a>
                        @endforeach
                    @endforeach
                </nav>
            </details>
        @endif
    </div>
</header>
