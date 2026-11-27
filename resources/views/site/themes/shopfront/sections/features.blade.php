@php $items = data_get($d, 'items', []); @endphp
@if (count($items))
<section class="block">
    <div class="wrap split">
        <div>
            @if (filled($section->title))
                <h2 class="display head">{{ $section->title }}</h2>
            @endif
            @if (filled($section->content))
                <p class="lede">{{ $section->content }}</p>
            @endif
        </div>
        <ul @class(['trades', 'trades--single' => count($items) < 4])>
            @foreach ($items as $item)
                <li class="trade">
                    @include('site.partials.icon', ['name' => data_get($item, 'icon', 'box'), 'class' => ''])
                    <h3 class="trade__name">{{ data_get($item, 'title') }}</h3>
                    @if (filled(data_get($item, 'description')))
                        <p class="trade__what">{{ data_get($item, 'description') }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif
