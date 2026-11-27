@php $items = data_get($d, 'items', []); @endphp
@if (count($items))
<section class="block" @tuxsection($section)>
    <div class="wrap split">
        <div>
            @if (filled($section->title))
                <h2 class="display head" @field($section, 'title')>{{ $section->title }}</h2>
            @endif
        </div>
        <dl class="tally">
            @foreach ($items as $i => $item)
                {{-- The label is the term and the figure its value, but the
                     figure is painted first. --}}
                <div class="tally__item">
                    <dt class="tally__of" @field($section, "data.items.{$i}.label")>{{ data_get($item, 'label') }}</dt>
                    <dd class="tally__n" @field($section, "data.items.{$i}.value")>{{ data_get($item, 'value') }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>
@endif
