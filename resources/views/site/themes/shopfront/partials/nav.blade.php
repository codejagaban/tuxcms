@php use App\Support\Site; @endphp
{{-- Navigation comes from the page tree. Both menus are <details>, so they
     open with no JavaScript on a fully static site. --}}
<header class="fascia">
    <div class="wrap">
        <div class="fascia__board">
            <a href="/" class="fascia__name">
                @if ($logo = Site::logo())
                    <img src="{{ $logo }}" alt="{{ Site::name() }}">
                @else
                    {{ Site::name() }}
                @endif
            </a>

            @if (! empty($navigation))
                <nav class="fascia__links" aria-label="Main">
                    @foreach ($navigation as $item)
                        @if (empty($item['children']))
                            <a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a>
                        @else
                            <details class="fascia__group">
                                <summary @class(['is-here' => $item['active']])>{{ $item['label'] }}</summary>
                                <div class="fascia__drop">
                                    <a href="{{ $item['url'] }}">All {{ strtolower($item['label']) }}</a>
                                    @foreach ($item['children'] as $child)
                                        <a href="{{ $child['url'] }}" @if ($child['active'] ?? false) aria-current="page" @endif>{{ $child['label'] }}</a>
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    @endforeach
                </nav>

                <details class="fascia__menu">
                    <summary>Menu</summary>
                    <nav aria-label="Main">
                        @foreach ($navigation as $item)
                            <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                            @foreach ($item['children'] as $child)
                                <a href="{{ $child['url'] }}" class="is-child">{{ $child['label'] }}</a>
                            @endforeach
                        @endforeach
                    </nav>
                </details>
            @endif
        </div>
    </div>
</header>
