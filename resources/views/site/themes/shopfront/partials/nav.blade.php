@php use App\Support\Site; @endphp
{{-- Navigation comes from the page tree. Both menus are <details>, so they
     open with no JavaScript on a fully static site. --}}
<header class="bar">
    <div class="wrap bar__in">
        <a href="/" class="bar__name">
            @if ($logo = Site::logo())
                <img src="{{ $logo }}" alt="{{ Site::name() }}">
            @else
                {{ Site::name() }}
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
